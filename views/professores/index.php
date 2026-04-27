<?php require_once '../../includes/header.php'; ?>

<div class="container mt-2">    
    <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
        <i class="fas fa-plus-circle"></i> <span><strong>Cadastrar</strong></span>
    </button><br><br>

    <table class="table table-bordered table-hover" id="tabelaProfessores">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="listaProfessores">
            <!-- Professores serão carregados aqui -->
        </tbody>
    </table>
</div>

<!-- Modal para Cadastro -->
<div class="modal fade" id="modalCadastrar" tabindex="-1" aria-labelledby="modalCadastrarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCadastrarProfessor">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCadastrarLabel">Cadastrar Professor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usuario_id">Usuário</label>
                        <select id="usuario_id" name="usuario_id" class="form-control">
                            <option value="">Selecione</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Edição -->
<div class="modal fade" id="modalEditarProfessor" tabindex="-1" aria-labelledby="modalEditarProfessorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditarProfessor">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title" id="modalEditarProfessorLabel">Editar Professor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idEditar" name="idEditar">
                    <div class="form-group">
                        <label for="usuario_idEditar">Usuário</label>
                        <select id="usuario_idEditar" name="usuario_idEditar" class="form-control">
                            <option value="">Selecione</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script>
    $(document).ready(function() {
        // Inicializando o DataTable
        var table = $('#tabelaProfessores').DataTable({
            "serverSide": false,
            "ajax": {
                url: '../../controllers/professores.php',
                type: 'POST',
                data: { acao: 'listar' },
                dataType: "json",
                success: function(response) {
                    if (response.sucesso) {
                        table.clear().rows.add(response.data).draw();
                    } else {
                        alert(response.mensagem);
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error("Erro no AJAX:", xhr.responseText);
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "usuario_nome" },
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
        
        // Função para carregar os usuários no select (Cadastro e Edição)
        function carregarUsuarios(selectedId = '') {
            $.post('../../controllers/usuario.php', { acao: 'listar' }, function(response) {
                if (response.sucesso) {
                    let options = '<option value="">Selecione</option>';
                    response.data.forEach(u => {
                        options += `<option value="${u.id}" ${u.id == selectedId ? 'selected' : ''}>${u.nome}</option>`;
                    });
                    $('#usuario_id').html(options);
                    $('#usuario_idEditar').html(options);
                } else {
                    alert(response.mensagem);
                }
            }, 'json');
        }

        // Carregar os usuários na página inicial
        carregarUsuarios();

        // Cadastro de Professor
        $("#formCadastrarProfessor").submit(function(e) {
            e.preventDefault();
            $.post('../../controllers/professores.php', {
                acao: 'salvar',
                usuario_id: $('#usuario_id').val()
            }, function(response) {
                alert(response.mensagem);
                if (response.sucesso) {
                    $('#modalCadastrar').modal('hide');
                    table.ajax.reload();
                }
            }, 'json');
        });

        // Editar Professor
        $("#formEditarProfessor").submit(function(e) {
            e.preventDefault();
            $.post('../../controllers/professores.php', {
                acao: 'editar',
                id: $('#idEditar').val(),
                usuario_id: $('#usuario_idEditar').val()
            }, function(response) {
                alert(response.mensagem);
                if (response.sucesso) {
                    $('#modalEditarProfessor').modal('hide');
                    table.ajax.reload();
                }
            }, 'json');
        });

        // Excluir Professor
        $('#tabelaProfessores').on('click', '.excluir', function() {
            let id = $(this).data('id');
            if (confirm("Tem certeza que deseja excluir este professor?")) {
                $.post('../../controllers/professores.php', { acao: 'excluir', id: id }, function(response) {
                    alert(response.mensagem);
                    if (response.sucesso) {
                        table.ajax.reload();
                    }
                }, 'json');
            }
        });

        // Editar um professor
        $('#tabelaProfessores').on('click', '.editar', function() {
            let id = $(this).data('id');
            $.post('../../controllers/professores.php', { acao: 'listar', id: id }, function(response) {
                if (response.sucesso) {
                    let professor = response.data[0];
                    $('#idEditar').val(professor.id);
                    $('#usuario_idEditar').val(professor.usuario_id);
                    $('#modalEditarProfessor').modal('show');
                } else {
                    alert(response.mensagem);
                }
            }, 'json');
        });
    });
</script>
<?php require_once '../../includes/footer.php'; ?>