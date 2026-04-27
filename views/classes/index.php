<?php require_once '../../includes/header.php'; ?>

<div class="container mt-2">    
    <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
        <i class="fas fa-plus-circle"></i> <span><strong>Cadastrar Classe</strong></span>
    </button><br><br>

    <!-- Tabela DataTables -->
    <div id="tabelaContainer" class="table-responsive">
        <table class="table table-bordered table-hover" id="tabelaClasses">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="listaClasses">
                <!-- Classes serão carregadas aqui -->
            </tbody>
        </table>
    </div>

    <!-- Cartões para telas pequenas -->
    <div id="cartoesContainer" class="row d-md-none"></div>

</div>

<!-- Modal Cadastrar -->
<div class="modal" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCadastrarLabel">Cadastrar Classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastrarClasse">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal" id="modalEditarClasse" tabindex="-1" aria-labelledby="modalEditarClasseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title" id="modalEditarClasseLabel">Editar Classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarClasse">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="mb-3">
                        <label for="nomeEditar" class="form-label">Nome da Classe</label>
                        <input type="text" class="form-control" id="nomeEditar" name="nome" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script>
$(document).ready(function() {
    var table = $('#tabelaClasses').DataTable({
        "serverSide": false,
        "ajax": {
            url: '../../controllers/classe.php',
            type: 'POST',
            data: { acao: 'listar' },
            dataType: "json",
            beforeSend: function() {
                $('#loading').show(); // Exibe um loading ou uma animação enquanto os dados estão sendo carregados
            },
            success: function(response) {
                $('#loading').hide();
                if (response.sucesso) {
                    table.clear().rows.add(response.data).draw();
                    renderizarCartoes(response.data); // Exibe cartões para dispositivos móveis
                } else {
                    alert(response.mensagem);
                }
            },
            error: function(xhr, error, thrown) {
                $('#loading').hide();
                alert("Erro ao carregar dados: " + xhr.responseText);
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "nome" },
            {
                "data": "id",
                "render": function(data) {
                    return `
                        <button class='btn btn-warning btn-sm editar' data-id='${data}'>
                            <i class="fas fa-edit"></i> 
                        </button>
                        <button class='btn btn-danger btn-sm excluir' data-id='${data}'>
                            <i class="fas fa-trash-alt"></i> 
                        </button>`;
                }
            }
        ]
    });

    // Renderiza os alunos como cartões em dispositivos móveis
    function renderizarCartoes(classes) {
        const container = document.getElementById("cartoesContainer");
        container.innerHTML = "";
        classes.forEach(classe => {
            const card = document.createElement("div");
            card.className = "col-12 col-md-4 mb-3";
            card.innerHTML = `
                <div class="card shadow-lg border-0 rounded">
                    <div class="card-body">
                        <h5 class="card-title text-primary">${classe.nome}</h5>
                        <p class="card-text"><strong>ID:</strong> ${classe.id}</p>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarClasse" data-id="${classe.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm btnExcluir" data-id="${classe.id}">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // Salvar nova classe
    $(document).on('submit', '#formCadastrarClasse', function(e) {
        e.preventDefault();
        var nome = $('#nome').val();
        if (!nome.trim()) {
            alert("Nome da classe é obrigatório.");
            return;
        }
        $.post('../../controllers/classe.php', { acao: 'salvar', nome: nome }, function(response) {
            if (response.sucesso) {
                $('#modalCadastrar').modal('hide');
                table.ajax.reload();
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Editar classe
    $(document).on('click', '.editar', function() {
        let id = $(this).data('id');
        $.post('../../controllers/classe.php', { acao: 'buscar', id: id }, function(response) {
            if (response.sucesso) {
                $('#idEditar').val(response.data.id);
                $('#nomeEditar').val(response.data.nome);
                $('#modalEditarClasse').modal('show');
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Confirmar edição
    $(document).on('submit', '#formEditarClasse', function(e) {
        e.preventDefault();
        var nome = $('#nomeEditar').val();
        if (!nome.trim()) {
            alert("Nome da classe é obrigatório.");
            return;
        }
        var id = $('#idEditar').val();
        $.post('../../controllers/classe.php', { acao: 'salvar', id: id, nome: nome }, function(response) {
            if (response.sucesso) {
                $('#modalEditarClasse').modal('hide');
                table.ajax.reload();
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Excluir classe
    $(document).on('click', '.excluir', function() {
        let id = $(this).data('id');
        if (confirm("Tem certeza que deseja excluir esta classe?")) {
            $.post('../../controllers/classe.php', { acao: 'excluir', id: id }, function(response) {
                if (response.sucesso) {
                    table.ajax.reload();
                } else {
                    alert(response.mensagem);
                }
            }, 'json');
        }
    });
});
</script>
<?php require_once '../../includes/footer.php'; ?>