<?php 
// Adicione isso no início do arquivo para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/header.php'; 
?>

<body>
<div class="container mt-2">    
    <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao">
        <i class="fas fa-plus-circle"></i> <span><strong>Cadastrar Novo Aluno</strong></span>
    </button>
    <br><br>

    <!-- Indicador de Carregamento -->
    <div id="loadingIndicator" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <!-- Tabela DataTables -->
    <div id="tabelaContainer" class="table-responsive">
        <table id="tabelaAlunos" class="table table-bordered table-hover d-none d-md-table" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Data de Nascimento</th>
                    <th>Telefone</th>
                    <th>Classe</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Cartões para telas pequenas -->
    <div id="cartoesContainer" class="row d-md-none"></div>

    <!-- Modal de Cadastro e Edição -->
    <div id="modalCadastroEdicao" class="modal fade" tabindex="-1" aria-labelledby="modalCadastroEdicaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCadastroEdicaoLabel">Cadastrar/Editar Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCadastroEdicao">
                        <input type="hidden" id="id" name="id">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" id="nome" name="nome" class="form-control" placeholder="Nome completo" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" id="telefone" name="telefone" class="form-control" 
                                placeholder="(XX) XXXXX-XXXX">
                            <div class="invalid-feedback">Por favor, insira um telefone válido (XX) XXXXX-XXXX</div>
                        </div>

                        <div class="mb-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select id="classe_id" name="classe_id" class="form-control" required>
                                <option value="">Selecione uma classe</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvar">Gravar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<script>
$(document).ready(function() {
    let tabela = null;
    
    // Função para mostrar/ocultar loading
    function showLoading(show) {
        if (show) {
            $('#loadingIndicator').show();
        } else {
            $('#loadingIndicator').hide();
        }
    }
    
    // Inicializa a DataTable com tratamento de erro melhorado
    try {
        tabela = $('#tabelaAlunos').DataTable({
            ajax: {
                url: '../../controllers/aluno.php?acao=listar',
                dataSrc: function(json) {
                    if (json.status === 'success') {
                        return json.data || [];
                    } else {
                        console.error('Erro na resposta:', json);
                        exibirMensagem('erro', json.message || 'Erro ao carregar dados');
                        return [];
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX detalhado:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    exibirMensagem('erro', 'Erro ao carregar lista de alunos. Verifique o console.');
                    return [];
                }
            },
            columns: [
                { data: 'nome' },
                { 
                    data: 'data_nascimento',
                    render: function(data) {
                        return data ? moment(data).format('DD/MM/YYYY') : '-';
                    }
                },
                { data: 'telefone' },
                { data: 'classe' },
                {
                    data: 'id',
                    render: function(data) {
                        return `
                            <button class="btn btn-warning btn-sm btnEditar" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" data-id="${data}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-danger btn-sm btnExcluir" data-id="${data}">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </button>
                        `;
                    },
                    orderable: false
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            }
        });
    } catch(e) {
        console.error('Erro ao inicializar DataTable:', e);
        exibirMensagem('erro', 'Erro ao inicializar tabela');
    }

    // Função para carregar as classes
    function carregarClasses() {
        $.ajax({
            url: '../../controllers/classe.php?acao=listar',
            method: 'GET',
            dataType: 'json',
            timeout: 10000, // 10 segundos timeout
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var selectClasses = $('#classe_id');
                    selectClasses.empty();
                    selectClasses.append('<option value="">Selecione uma classe</option>');
                    
                    if (Array.isArray(response.data)) {
                        response.data.forEach(function(classe) {
                            selectClasses.append('<option value="' + classe.id + '">' + classe.nome + '</option>');
                        });
                    }
                } else {
                    console.error('Erro ao carregar classes:', response);
                    exibirMensagem('erro', 'Erro ao carregar classes: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX ao carregar classes:', error);
                exibirMensagem('erro', 'Erro ao carregar as classes: ' + error);
            }
        });
    }

    // Renderizar cartões
    function renderizarCartoes(alunos) {
        const container = document.getElementById("cartoesContainer");
        if (!container) return;
        
        container.innerHTML = "";
        
        if (!Array.isArray(alunos) || alunos.length === 0) {
            container.innerHTML = '<div class="col-12 text-center">Nenhum aluno encontrado</div>';
            return;
        }
        
        alunos.forEach(aluno => {
            const card = document.createElement("div");
            card.className = "col-12 mb-3";
            card.innerHTML = `
                <div class="card shadow-lg border-0 rounded">
                    <div class="card-body">
                        <h5 class="card-title text-primary">${escapeHtml(aluno.nome) || '-'}</h5>
                        <p class="card-text"><strong>Data de Nascimento:</strong> ${aluno.data_nascimento ? moment(aluno.data_nascimento).format('DD/MM/YYYY') : '-'}</p>
                        <p class="card-text"><strong>Telefone:</strong> ${escapeHtml(aluno.telefone) || '-'}</p>
                        <p class="card-text"><strong>Classe:</strong> ${escapeHtml(aluno.classe) || '-'}</p>
                        <button class="btn btn-warning btn-sm btnEditar" data-bs-toggle="modal" data-bs-target="#modalCadastroEdicao" data-id="${aluno.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm btnExcluir" data-id="${aluno.id}">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }
    
    // Função para escapar HTML (security)
    function escapeHtml(text) {
        if (!text) return text;
        return text.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Carregar alunos para cartões
    function carregarAlunosParaCartoes() {
        showLoading(true);
        $.ajax({
            url: '../../controllers/aluno.php?acao=listar',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                showLoading(false);
                if (response.status === 'success' && response.data) {
                    renderizarCartoes(response.data);
                } else {
                    console.error('Erro ao carregar alunos para cartões:', response);
                }
            },
            error: function(xhr, status, error) {
                showLoading(false);
                console.error('Erro AJAX ao carregar cartões:', error);
                exibirMensagem('erro', 'Erro ao carregar cartões: ' + error);
            }
        });
    }

    // Editar aluno
    $(document).on('click', '.btnEditar', function() {
        var alunoId = $(this).data('id');
        
        $('#modalCadastroEdicaoLabel').text('Editar Aluno');
        showLoading(true);
        
        $.ajax({
            url: '../../controllers/aluno.php?acao=buscar',
            method: 'GET',
            data: { id: alunoId },
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                showLoading(false);
                if (response.status === 'success' && response.data) {
                    $('#id').val(response.data.id);
                    $('#nome').val(response.data.nome);
                    $('#telefone').val(response.data.telefone);
                    $('#data_nascimento').val(response.data.data_nascimento);
                    $('#classe_id').val(response.data.classe_id);
                } else {
                    exibirMensagem('erro', 'Erro ao carregar dados do aluno: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function(xhr, status, error) {
                showLoading(false);
                exibirMensagem('erro', 'Erro ao carregar os dados do aluno: ' + error);
            }
        });
    });

    // Limpar formulário
    $('#modalCadastroEdicao').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btnEditar')) {
            $('#modalCadastroEdicaoLabel').text('Cadastrar Aluno');
            $('#formCadastroEdicao')[0].reset();
            $('#id').val('');
        }
        carregarClasses();
    });

    // Salvar aluno
    $('#btnSalvar').on('click', function() {
        var nome = $('#nome').val().trim();
        var telefone = $('#telefone').val().trim().replace(/\D/g, '');
        var dataNascimento = $('#data_nascimento').val().trim();
        var classeId = $('#classe_id').val();
        var alunoId = $('#id').val();

        // Validações
        if (!nome) {
            exibirMensagem('erro', 'Por favor, informe o nome do aluno.');
            $('#nome').focus();
            return;
        }
        
        if (telefone && telefone.length > 0 && telefone.length !== 10 && telefone.length !== 11) {
            exibirMensagem('erro', 'Telefone deve ter 10 ou 11 dígitos (deixe em branco se não tiver).');
            $('#telefone').focus();
            return;
        }
        
        if (!dataNascimento) {
            exibirMensagem('erro', 'Por favor, informe a data de nascimento.');
            $('#data_nascimento').focus();
            return;
        }
        
        if (!classeId) {
            exibirMensagem('erro', 'Por favor, selecione uma classe.');
            $('#classe_id').focus();
            return;
        }

        var url = alunoId ? '../../controllers/aluno.php?acao=editar' : '../../controllers/aluno.php?acao=salvar';
        
        showLoading(true);
        $('#btnSalvar').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: $('#formCadastroEdicao').serialize(),
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                showLoading(false);
                $('#btnSalvar').prop('disabled', false);
                
                if (response.status === "success") {
                    $('#modalCadastroEdicao').modal('hide');
                    if (tabela) tabela.ajax.reload(null, false);
                    carregarAlunosParaCartoes();
                    exibirMensagem('sucesso', response.message || 'Operação realizada com sucesso!');
                } else {
                    exibirMensagem('erro', response.message || 'Erro ao salvar aluno');
                }
            },
            error: function(xhr, status, error) {
                showLoading(false);
                $('#btnSalvar').prop('disabled', false);
                console.error('Erro ao salvar:', error);
                console.error('Resposta do servidor:', xhr.responseText);
                exibirMensagem('erro', 'Erro ao comunicar com o servidor: ' + error);
            }
        });
    });

    // Excluir aluno
    $(document).on('click', '.btnExcluir', function() {
        var alunoId = $(this).data('id');

        if (confirm('Você tem certeza que deseja excluir este aluno?')) {
            showLoading(true);
            $.ajax({
                url: '../../controllers/aluno.php?acao=excluir',
                method: 'POST',
                data: { id: alunoId },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    showLoading(false);
                    if (response.status === "success") {
                        if (tabela) tabela.ajax.reload(null, false);
                        carregarAlunosParaCartoes();
                        exibirMensagem('sucesso', response.message || 'Aluno excluído com sucesso!');
                    } else {
                        exibirMensagem('erro', response.message || 'Erro ao excluir aluno');
                    }
                },
                error: function(xhr, status, error) {
                    showLoading(false);
                    console.error('Erro ao excluir:', error);
                    exibirMensagem('erro', 'Erro ao comunicar com o servidor: ' + error);
                }
            });
        }
    });

    // Função para exibir mensagens
    function exibirMensagem(tipo, mensagem) {
        let classe = tipo === 'sucesso' ? 'alert-success' : 'alert-danger';
        let alerta = `
            <div class="alert ${classe} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('.alert').remove();
        $('body').append(alerta);
        
        setTimeout(() => { 
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Máscara de telefone
    $('#telefone').on('input', function(e) {
        let value = $(this).val().replace(/\D/g, '');
        let formattedValue = '';
        
        if (value.length > 0) {
            formattedValue = '(' + value.substring(0, 2);
        }
        if (value.length > 2) {
            formattedValue += ') ' + value.substring(2, Math.min(7, value.length));
        }
        if (value.length > 7) {
            formattedValue += '-' + value.substring(7, 11);
        }
        
        $(this).val(formattedValue);
        
        if (value.length === 0 || value.length === 10 || value.length === 11) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
    });

    // Converter nome para maiúsculas
    $('#nome').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Carregar dados iniciais
    carregarAlunosParaCartoes();
});
</script>

<?php require_once '../../includes/footer.php'; ?>