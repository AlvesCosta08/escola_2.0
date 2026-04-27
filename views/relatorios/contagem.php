<?php 
require_once '../../config/conexao.php';
require_once '../../includes/header.php';

?>

<body>

<div class="container">
    <h1 class="text-center mb-4">Relatório Trimestral por Congregação</h1>
    
    <div class="table-container">
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Classe</th>
                    <th>Congregação</th>
                    <th>Trimestre</th>
                    <th>Total de Bíblias</th>
                    <th>Total de Revistas</th>
                    <th>Total de Visitantes</th>
                    <th>Total de Ofertas</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aqui, o PHP vai preencher os dados da VIEW relatorio_trimestre_congregacao -->
                <?php
                // Conexão com o banco de dados
                include('../../config/conexao.php');

                // Consulta para obter os dados da VIEW
                $query = "SELECT * FROM relatorio_trimestre_congregacao";
                $result = $pdo->query($query);

                // Verificando se existem resultados
                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['classe_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['congregacao_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['trimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_biblias']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_revistas']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_visitantes']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_ofertas']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Nenhum dado encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>
