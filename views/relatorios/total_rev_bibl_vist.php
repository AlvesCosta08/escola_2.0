<?php
// Incluir o arquivo de conexão PDO
require_once '../../config/conexao.php';
require_once '../../includes/header.php';

// Consultar os dados da view correta
$query = "SELECT 
            congregacao_id, 
            classe_id, 
            trimestre, 
            total_biblias, 
            total_revistas, 
            total_visitantes, 
            total_ofertas 
          FROM resumo_chamadas
          ORDER BY congregacao_id, trimestre"; // Adicionado ORDER BY para ordenação
$stmt = $pdo->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fechar a conexão com o banco
$pdo = null;
?>

<body>
    <div class="container">
        <h2>Relatório de Congregação por Trimestre</h2>
        
        <table id="congregacaoTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Congregação ID</th>
                    <th>Classe ID</th>
                    <th>Trimestre</th>
                    <th>Bíblias</th>
                    <th>Revistas</th>
                    <th>Visitantes</th>
                    <th>Ofertas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['congregacao_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['classe_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['trimestre']); ?></td>
                        <td><?php echo number_format($row['total_biblias'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($row['total_revistas'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($row['total_visitantes'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($row['total_ofertas'], 2, ',', '.'); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#congregacaoTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
                responsive: true,
                order: [[0, 'asc']],
                paging: true,
                pageLength: 10, // Definindo o número de linhas por página
                searching: true
            });
        });
    </script>
<?php require_once '../../includes/footer.php'; ?>
