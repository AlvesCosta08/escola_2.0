<?php
require_once('../../config/conexao.php');
require_once('../../includes/header.php');

// --- Funções ---
function calcularPeriodoTrimestre(int $trimestre): array {
    $ano = date('Y');
    $mes_inicio = ($trimestre - 1) * 3 + 1;
    $mes_fim = $mes_inicio + 2;
    $data_inicio = "$ano-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
    $ultimo_dia = date("t", strtotime("$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-01"));
    $data_fim = "$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-$ultimo_dia";
    return [$data_inicio, $data_fim];
}

// --- Filtros ---
$congregacao_id = $_GET['congregacao_id'] ?? '';
$classe_id = $_GET['classe_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Inicializa variáveis
$alunos = [];
$top_presencas = [];
$top_faltas = [];
$trimestre_sem_dados = false;

// Definir período
if (!empty($trimestre)) {
    [$data_inicio, $data_fim] = calcularPeriodoTrimestre($trimestre);
    
    // Verificar se existe alguma chamada para este trimestre
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM chamadas WHERE data BETWEEN ? AND ?");
    $stmt->execute([$data_inicio, $data_fim]);
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        $trimestre_sem_dados = true;
    }
} else {
    $data_inicio = $data_inicio ?: date('Y-m-01');
    $data_fim = $data_fim ?: date('Y-m-d');
}

// --- Consulta principal (só executa se trimestre tiver dados ou se não for por trimestre) ---
if (!$trimestre_sem_dados) {
    $sql = "SELECT 
                a.id,
                a.nome AS aluno,
                c.nome AS classe,
                cg.nome AS congregacao,
                COUNT(CASE WHEN p.presente IN ('presente', 'justificado') THEN 1 END) AS presencas,
                COUNT(CASE WHEN p.presente = 'ausente' THEN 1 END) AS faltas,
                COUNT(p.id) AS total,
                CASE 
                    WHEN COUNT(p.id) > 0 THEN 
                        ROUND(COUNT(CASE WHEN p.presente IN ('presente', 'justificado') THEN 1 END) / COUNT(p.id) * 100, 1)
                    ELSE 0
                END AS frequencia
            FROM alunos a
            JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
            JOIN classes c ON c.id = m.classe_id
            JOIN congregacoes cg ON cg.id = m.congregacao_id
            LEFT JOIN presencas p ON p.aluno_id = a.id
            LEFT JOIN chamadas ch ON ch.id = p.chamada_id AND ch.classe_id = m.classe_id
                AND ch.data BETWEEN :inicio AND :fim
            WHERE 1=1";

    $params = [':inicio' => $data_inicio, ':fim' => $data_fim];

    if (!empty($congregacao_id)) {
        $sql .= " AND cg.id = :congregacao";
        $params[':congregacao'] = $congregacao_id;
    }

    if (!empty($classe_id)) {
        $sql .= " AND c.id = :classe";
        $params[':classe'] = $classe_id;
    }

    $sql .= " GROUP BY a.id ORDER BY frequencia DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rankings
    if (count($alunos) > 0) {
        $top_presencas = array_slice($alunos, 0, 5);
        $top_faltas = array_reverse(array_slice($alunos, -5, 5));
    }
}

// Dropdowns
$congs = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome")->fetchAll();
$classes = $pdo->query("SELECT id, nome FROM classes ORDER BY nome")->fetchAll();
?>


<body>
    <div class="container-fluid py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-check mr-2"></i>Relatório Trimestral
            </h1>
            <div class="d-flex">
                <button id="exportPdf" class="btn btn-danger btn-sm mr-2" <?= (count($alunos) == 0) ? 'disabled' : '' ?>>
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </button>
                <button id="exportExcel" class="btn btn-success btn-sm" <?= (count($alunos) == 0) ? 'disabled' : '' ?>>
                    <i class="fas fa-file-excel mr-1"></i>Excel
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter mr-1"></i> Filtros
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Congregação</label>
                        <select name="congregacao_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach($congs as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $congregacao_id == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Classe</label>
                        <select name="classe_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= $classe_id == $cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Trimestre</label>
                        <select name="trimestre" class="form-select">
                            <option value="">Selecione</option>
                            <option value="1" <?= $trimestre == 1 ? 'selected' : '' ?>>1º Trimestre</option>
                            <option value="2" <?= $trimestre == 2 ? 'selected' : '' ?>>2º Trimestre</option>
                            <option value="3" <?= $trimestre == 3 ? 'selected' : '' ?>>3º Trimestre</option>
                            <option value="4" <?= $trimestre == 4 ? 'selected' : '' ?>>4º Trimestre</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>" <?= $trimestre ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>" <?= $trimestre ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($trimestre_sem_dados): ?>
            <div class="alert alert-warning alert-empty">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                    <div>
                        <h5 class="alert-heading">Trimestre sem dados</h5>
                        <p class="mb-0">O trimestre selecionado (<?= $trimestre ?>º) ainda não possui registros de chamadas.</p>
                    </div>
                </div>
            </div>
        <?php elseif (count($alunos) == 0 && !$trimestre_sem_dados): ?>
            <div class="alert alert-info alert-empty">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x mr-3"></i>
                    <div>
                        <h5 class="alert-heading">Nenhum resultado encontrado</h5>
                        <p class="mb-0">Não foram encontrados registros para os filtros selecionados.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
        
            <!-- Cards Resumo -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Alunos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= count($alunos) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Média de Frequência
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= count($alunos) > 0 ? round(array_sum(array_column($alunos, 'frequencia')) / count($alunos), 1) : 0 ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-percent fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Período
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rankings -->
            <div class="row mb-4">
                <!-- Top Presenças -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-trophy mr-1"></i> Top 5 Presenças
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach($top_presencas as $i => $aluno): ?>
                                <div class="mb-3 ranking-item ranking-<?= $i+1 ?> p-3 rounded">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="font-weight-bold mb-1"><?= $i+1 ?>. <?= htmlspecialchars($aluno['aluno']) ?></h6>
                                        <span class="badge badge-presenca"><?= $aluno['presencas'] ?> presenças</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span><?= htmlspecialchars($aluno['classe']) ?></span>
                                        <span class="font-weight-bold"><?= $aluno['frequencia'] ?>%</span>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $aluno['frequencia'] ?>%" 
                                             aria-valuenow="<?= $aluno['frequencia'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Faltas -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Top 5 Faltas
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach($top_faltas as $i => $aluno): ?>
                                <div class="mb-3 ranking-item ranking-<?= $i+1 ?> p-3 rounded">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="font-weight-bold mb-1"><?= $i+1 ?>. <?= htmlspecialchars($aluno['aluno']) ?></h6>
                                        <span class="badge badge-falta"><?= $aluno['faltas'] ?> faltas</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span><?= htmlspecialchars($aluno['classe']) ?></span>
                                        <span class="font-weight-bold"><?= $aluno['frequencia'] ?>%</span>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $aluno['frequencia'] ?>%" 
                                             aria-valuenow="<?= $aluno['frequencia'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Alunos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-table mr-1"></i> Relação Completa de Alunos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelaAlunos" class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Aluno</th>
                                    <th>Classe</th>
                                    <th>Congregação</th>
                                    <th>Presenças</th>
                                    <th>Faltas</th>
                                    <th>Total</th>
                                    <th>Frequência</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($alunos as $aluno): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($aluno['aluno']) ?></td>
                                        <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                        <td><?= htmlspecialchars($aluno['congregacao']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-presenca"><?= $aluno['presencas'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-falta"><?= $aluno['faltas'] ?></span>
                                        </td>
                                        <td class="text-center"><?= $aluno['total'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 mr-2" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $aluno['frequencia'] ?>%" 
                                                         aria-valuenow="<?= $aluno['frequencia'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="badge badge-frequencia"><?= $aluno['frequencia'] ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
    $(document).ready(function() {
        <?php if (count($alunos) > 0): ?>
        // DataTable (só inicializa se houver dados)
        var table = $('#tabelaAlunos').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Relatorio_Presencas',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Relatorio_Presencas',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function(doc) {
                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            color: 'white',
                            fillColor: '#4e73df',
                            alignment: 'center'
                        };
                        doc.defaultStyle.fontSize = 9;
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            responsive: true,
            order: [[6, 'desc']],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 6 }
            ]
        });

        // Exportar PDF
        $('#exportPdf').click(function() {
            table.button('.buttons-pdf').trigger();
        });

        // Exportar Excel
        $('#exportExcel').click(function() {
            table.button('.buttons-excel').trigger();
        });
        <?php endif; ?>

        // Atualizar datas quando selecionar trimestre
        $('select[name="trimestre"]').change(function() {
            const trimestre = $(this).val();
            const anoAtual = new Date().getFullYear();
            
            if (trimestre) {
                let inicio, fim;
                
                switch(trimestre) {
                    case '1':
                        inicio = `${anoAtual}-01-01`;
                        fim = `${anoAtual}-03-31`;
                        break;
                    case '2':
                        inicio = `${anoAtual}-04-01`;
                        fim = `${anoAtual}-06-30`;
                        break;
                    case '3':
                        inicio = `${anoAtual}-07-01`;
                        fim = `${anoAtual}-09-30`;
                        break;
                    case '4':
                        inicio = `${anoAtual}-10-01`;
                        fim = `${anoAtual}-12-31`;
                        break;
                }
                
                $('input[name="data_inicio"]').val(inicio).prop('readonly', true);
                $('input[name="data_fim"]').val(fim).prop('readonly', true);
            } else {
                $('input[name="data_inicio"], input[name="data_fim"]').prop('readonly', false);
            }
        });
    });
    </script>
<?php require_once('../../includes/footer.php'); ?>
