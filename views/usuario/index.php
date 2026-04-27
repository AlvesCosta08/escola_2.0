<?php require_once '../../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Gerenciamento de Usuários</h2>
    <button class="btn btn-success mt-4" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
    <i class="fas fa-plus-circle"></i> <span><strong>Cadastrar</strong></span>
    </button><br><br>

    <table class="table table-striped" id="tabelaUsuarios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Congregação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="listaUsuarios">
            <!-- Usuários serão carregados aqui -->
        </tbody>
    </table>
</div>

<!-- Modal Cadastrar -->
<div class="modal" id="modalCadastrar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastrarUsuario">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" required>
                    </div>
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-control" id="perfil" required>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuário</option>
                            <option value="professor">Professor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="congregacao_id" class="form-label">Congregação</label>
                        <select class="form-control" id="congregacao_id" required>
                            <!-- As opções serão carregadas dinamicamente -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    <input type="hidden" id="usuario_id_editar">
                    <div class="mb-3">
                        <label for="nome_editar" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome_editar" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_editar" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_editar" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha_editar" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha_editar">
                    </div>
                    <div class="mb-3">
                        <label for="perfil_editar" class="form-label">Perfil</label>
                        <select class="form-control" id="perfil_editar" required>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuário</option>
                            <option value="professor">Professor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="congregacao_id_editar" class="form-label">Congregação</label>
                        <select class="form-control" id="congregacao_id_editar" required>
                            <!-- As opções serão carregadas dinamicamente -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este usuário?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarExcluir">Excluir</button>
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
    // Função para listar os usuários
    function listarUsuarios() {
        $.post('../../controllers/usuario.php', {
            acao: 'listar'
        }, function(response) {
            if (response.sucesso) {
                let lista = '';
                response.data.forEach(usuario => {
                    lista += `<tr>
                                    <td>${usuario.id}</td>
                                    <td>${usuario.nome}</td>
                                    <td>${usuario.email}</td>
                                    <td>${usuario.perfil}</td>
                                    <td>${usuario.congregacao_nome}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editar" data-id="${usuario.id}">
                                            <i class="fas fa-edit"></i> 
                                        </button>
                                        <button class="btn btn-danger btn-sm excluir" data-id="${usuario.id}">
                                            <i class="fas fa-trash-alt"></i> 
                                        </button>
                                    </td>
                                </tr>`;
                });
                $('#listaUsuarios').html(lista);
            }
        }, 'json');
    }

    listarUsuarios();

    // Função para carregar as congregações nos modais
    function carregarCongregacoes(selectedId = '') {
        $.post('../../controllers/congregacao.php', {
            acao: 'listar'
        }, function(response) {
            if (response.sucesso) {
                let options = '<option value="">Selecione</option>';
                response.data.forEach(c => {
                    options +=
                        `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.nome}</option>`;
                });
                $('#congregacao_id').html(options);
                $('#congregacao_id_editar').html(options);
            } else {
                console.error("Erro ao carregar congregações:", response.mensagem);
            }
        }, 'json');
    }

    // Função para garantir que o modal de cadastro e edição carreguem as congregações
    $('#modalCadastrar, #modalEditar').on('show.bs.modal', function() {
        carregarCongregacoes();
    });

    // Função para editar um usuário
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        $.post('../../controllers/usuario.php', {
            acao: 'buscar',
            id: id
        }, function(response) {
            if (response.sucesso) {
                const usuario = response.data;
                $('#usuario_id_editar').val(usuario.id);
                $('#nome_editar').val(usuario.nome);
                $('#email_editar').val(usuario.email);
                $('#perfil_editar').val(usuario.perfil);
                $('#congregacao_id_editar').val(usuario.congregacao_id);
                $('#modalEditar').modal('show');
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Atualizar usuário
    $('#formEditarUsuario').submit(function(e) {
        e.preventDefault();
        const id = $('#usuario_id_editar').val();
        const nome = $('#nome_editar').val();
        const email = $('#email_editar').val();
        const senha = $('#senha_editar').val();
        const perfil = $('#perfil_editar').val();
        const congregacao_id = $('#congregacao_id_editar').val();

        // Envia os dados via AJAX para o controlador de edição
        $.post('../../controllers/usuario.php', {
            acao: 'editar', // Ação correta para edição
            id: id,
            nome: nome,
            email: email,
            senha: senha,
            perfil: perfil,
            congregacao_id: congregacao_id
        }, function(response) {
            if (response.sucesso) {
                alert(response.mensagem);
                $('#modalEditar').modal('hide');
                listarUsuarios(); // Atualiza a lista de usuários após a edição
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Cadastrar usuário
    $('#formCadastrarUsuario').submit(function(e) {
        e.preventDefault();
        const nome = $('#nome').val();
        const email = $('#email').val();
        const senha = $('#senha').val();
        const perfil = $('#perfil').val();
        const congregacao_id = $('#congregacao_id').val();

        $.post('../../controllers/usuario.php', {
            acao: 'salvar', // Ação corrigida para salvar
            nome: nome,
            email: email,
            senha: senha,
            perfil: perfil,
            congregacao_id: congregacao_id
        }, function(response) {
            if (response.sucesso) {
                alert(response.mensagem);
                $('#modalCadastrar').modal('hide');
                listarUsuarios();
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });

    // Exclusão de usuário
    $(document).on('click', '.excluir', function() {
        const id = $(this).data('id');
        $('#confirmarExcluir').data('id', id);
        $('#modalExcluir').modal('show');
    });

    // Confirmar exclusão
    $('#confirmarExcluir').click(function() {
        const id = $(this).data('id');
        $.post('../../controllers/usuario.php', {
            acao: 'excluir',
            id: id
        }, function(response) {
            if (response.sucesso) {
                alert(response.mensagem);
                $('#modalExcluir').modal('hide');
                listarUsuarios();
            } else {
                alert(response.mensagem);
            }
        }, 'json');
    });
});
</script>
<?php require_once '../../includes/footer.php'; ?>