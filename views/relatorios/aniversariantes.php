<?php
// Conectando ao banco de dados
require_once '../../config/conexao.php';
require_once '../../includes/header.php';

// Definir a localidade para português (Brasil)
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

// Consulta para pegar os aniversariantes do mês atual
$query = "SELECT nome, DAY(data_nascimento) AS dia 
          FROM alunos 
          WHERE MONTH(data_nascimento) = MONTH(CURRENT_DATE)";

$result = $pdo->query($query);

// Organizando os aniversariantes por dia
$aniversariantes = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $aniversariantes[$row['dia']][] = $row['nome'];
}

// Fechar a conexão com o banco
$pdo = null;
?>

<body>
    <div class="container">
        <!-- Tabela de Aniversariantes -->
        <h2>Calendário de Aniversariantes - <?php echo strftime('%B %Y'); ?></h2>
        <table id="aniversariantes" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Dia</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($aniversariantes as $dia => $nomes) {
                    foreach ($nomes as $nome) {
                        echo "<tr><td>$dia</td><td>$nome</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS e Dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

    <!-- Inicialização do DataTables -->
    <script>
        $(document).ready(function() {
            $('#aniversariantes').DataTable({
                dom: 'Bfrtip',
                buttons: ['pdf'],
                language: {
                    search: "Pesquisar:", // Personaliza o texto da busca
                    lengthMenu: "Exibir _MENU_ registros por página", // Personaliza a quantidade de registros por página
                    info: "Mostrando de _START_ até _END_ de _TOTAL_ registros", // Texto de informações
                    infoEmpty: "Nenhum registro encontrado", // Quando não houver resultados
                    zeroRecords: "Nenhum resultado encontrado", // Quando não encontrar resultados
                    paginate: {
                        first: "Primeira",
                        previous: "Anterior",
                        next: "Próxima",
                        last: "Última"
                    }
                }
            });
        });
    </script>
<?php
require_once '../../includes/footer.php';
?>