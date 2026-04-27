<?php
include('../../config/conexao.php');
include('../../includes/header.php');

// Utilitários
function calcularPeriodoTrimestre(int $trimestre): array {
    $ano = date('Y');
    $mes_inicio = ($trimestre - 1) * 3 + 1;
    $mes_fim = $mes_inicio + 2;
    $data_inicio = "$ano-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
    $ultimo_dia = date("t", strtotime("$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-01"));
    $data_fim = "$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-$ultimo_dia";
    return [$data_inicio, $data_fim];
}

function nomeTrimestre($num) {
    return "{$num}º Trimestre";
}

// Filtros (sanitizados)
$congregacao_id = isset($_GET['congregacao_id']) ? intval($_GET['congregacao_id']) : null;
$trimestre = isset($_GET['trimestre']) ? intval($_GET['trimestre']) : null;
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

if (!empty($trimestre)) {
    [$data_inicio, $data_fim] = calcularPeriodoTrimestre($trimestre);
} else {
    $data_inicio = $data_inicio ?: date('Y-m-01');
    $data_fim = $data_fim ?: date('Y-m-d');
}

// Consulta SQL
$sql = "
SELECT 
    a.id AS aluno_id,
    a.nome AS aluno_nome,
    c.nome AS classe_nome,
    cg.nome AS congregacao_nome,
    CASE 
        WHEN MONTH(ch.data) BETWEEN 1 AND 3 THEN 1
        WHEN MONTH(ch.data) BETWEEN 4 AND 6 THEN 2
        WHEN MONTH(ch.data) BETWEEN 7 AND 9 THEN 3
        ELSE 4
    END AS trimestre,
    COUNT(DISTINCT ch.id) AS total_registros,
    COUNT(DISTINCT CASE WHEN p.presente = 'presente' THEN ch.id END) AS total_presencas,
    COUNT(DISTINCT CASE WHEN p.presente = 'ausente' THEN ch.id END) AS total_faltas
FROM alunos a
JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
JOIN classes c ON c.id = m.classe_id
JOIN congregacoes cg ON cg.id = m.congregacao_id
JOIN presencas p ON p.aluno_id = a.id
JOIN chamadas ch ON ch.id = p.chamada_id AND ch.classe_id = m.classe_id
WHERE ch.data BETWEEN :data_inicio AND :data_fim
  AND p.presente IN ('presente', 'ausente')
";

if (!empty($congregacao_id)) {
    $sql .= " AND cg.id = :congregacao_id";
}

$sql .= " GROUP BY a.id, trimestre ORDER BY a.nome, trimestre";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':data_inicio', $data_inicio);
$stmt->bindValue(':data_fim', $data_fim);
if (!empty($congregacao_id)) {
    $stmt->bindValue(':congregacao_id', $congregacao_id);
}
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Congregações para o filtro
$congs = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Consolidar ranking por aluno
$resumo = [];
foreach ($dados as $d) {
    $id = $d['aluno_id'];
    if (!isset($resumo[$id])) {
        $resumo[$id] = $d;
    } else {
        $resumo[$id]['total_presencas'] += $d['total_presencas'];
        $resumo[$id]['total_faltas'] += $d['total_faltas'];
        $resumo[$id]['total_registros'] += $d['total_registros'];
    }
}

$top_presencas = $resumo;
usort($top_presencas, fn($a, $b) => $b['total_presencas'] <=> $a['total_presencas']);
$top_presencas = array_slice($top_presencas, 0, 10);

$top_faltas = $resumo;
usort($top_faltas, fn($a, $b) => $b['total_faltas'] <=> $a['total_faltas']);
$top_faltas = array_slice($top_faltas, 0, 10);
?>

<body class="bg-light">

<div class="container-fluid py-4 px-3">
    <h4 class="mb-4 text-center">📊 Relatório Geral de Presenças por Trimestre</h4>

    <!-- Formulário de filtro -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <label>Congregação:</label>
            <select name="congregacao_id" class="form-select">
                <option value="">Todas</option>
                <?php foreach($congs as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($congregacao_id == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Trimestre:</label>
            <select name="trimestre" class="form-select">
                <option value="">Todos</option>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>>
                        <?= $i ?>º Trimestre
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-1 d-grid">
            <label>&nbsp;</label>
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </div>
    </form>

    <!-- Tabela de presenças -->
    <div class="table-responsive">
        <table id="tabela" class="table table-striped table-bordered nowrap" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>Aluno</th>
                    <th>Classe</th>
                    <th>Congregação</th>
                    <th>Trimestre</th>
                    <th>Presenças</th>
                    <th>Faltas</th>
                    <th>% Frequência</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dados as $d): 
                    $freq = $d['total_registros'] > 0 ? round($d['total_presencas'] / $d['total_registros'] * 100, 1) : 0;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($d['aluno_nome']) ?></td>
                        <td><?= htmlspecialchars($d['classe_nome']) ?></td>
                        <td><?= htmlspecialchars($d['congregacao_nome']) ?></td>
                        <td><?= nomeTrimestre($d['trimestre']) ?></td>
                        <td><span class="badge badge-presente"><?= $d['total_presencas'] ?></span></td>
                        <td><span class="badge badge-falta"><?= $d['total_faltas'] ?></span></td>
                        <td><span class="badge bg-info"><?= $freq ?>%</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Rankings -->
    <div class="row mt-5">
        <div class="col-md-6 mb-4">
            <h5 class="mb-3 text-success">🎯 Top 10 com Mais Presenças</h5>
            <ul class="list-group">
                <?php foreach($top_presencas as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <?= htmlspecialchars($p['aluno_nome']) ?> (<?= htmlspecialchars($p['classe_nome']) ?>)
                        <span class="badge bg-success rounded-pill"><?= $p['total_presencas'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6 mb-4">
            <h5 class="mb-3 text-danger">⚠️ Top 10 com Mais Faltas</h5>
            <ul class="list-group">
                <?php foreach($top_faltas as $f): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <?= htmlspecialchars($f['aluno_nome']) ?> (<?= htmlspecialchars($f['classe_nome']) ?>)
                        <span class="badge bg-danger rounded-pill"><?= $f['total_faltas'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- JS DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    $('#tabela').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        dom: 'Bfrtip',
        buttons: [
            { 
                extend: 'copyHtml5', 
                text: 'Copiar', 
                exportOptions: { columns: ':visible' } 
            },
            { 
                extend: 'excelHtml5', 
                text: 'Excel', 
                exportOptions: { columns: ':visible' } 
            },
            { 
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Relatório Geral de Presenças por Trimestre',
                exportOptions: { columns: ':visible' },
                customize: function (doc) {
                    // Estilo do cabeçalho
                    doc.styles.tableHeader = {
                        bold: true,
                        fontSize: 10,
                        color: 'white',
                        fillColor: '#343a40',
                        alignment: 'center'
                    };
                    // Estilo do título
                    doc.styles.title = {
                        fontSize: 14,
                        alignment: 'center',
                        bold: true
                    };
                    // Estilo padrão
                    doc.defaultStyle.fontSize = 9;

                    // Largura automática para todas as colunas
                    doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');

                    // Rodapé
                    doc.footer = function(currentPage, pageCount) {
                        return {
                            text: 'Página ' + currentPage.toString() + ' de ' + pageCount,
                            alignment: 'right',
                            margin: [0, 10, 20, 0],
                            fontSize: 8
                        };
                    };
                },
                columnStyles: {
                    4: { alignment: 'center' }, // Presenças
                    5: { alignment: 'center' }, // Faltas
                    6: { alignment: 'center' }  // % Frequência
                }
            },
            { 
                extend: 'print', 
                text: 'Imprimir', 
                exportOptions: { columns: ':visible' } 
            }
        ]
    });
});
</script>
<?php include('../../includes/footer.php'); ?>
