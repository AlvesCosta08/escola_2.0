<?php  
require_once '../config/conexao.php';
require_once '../auth/valida_sessao.php';
require_once '../functions/funcoes_chamadas.php';
require_once '../includes/header.php';

$estatisticas = obterEstatisticasChamadasMensais($pdo);
?>


<body>
 <!-- Navbar Original -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../assets/images/biblia.png" alt="Logo EBD" style="height: 40px; margin-right: 10px;">
      <span>Escola Bíblica</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="./alunos/index.php">Alunos</a></li>
        <li class="nav-item"><a class="nav-link" href="./classes/index.php">Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="./professores/index.php">Professores</a></li>
        <li class="nav-item"><a class="nav-link" href="./congregacao/index.php">Congregações</a></li>
        <li class="nav-item"><a class="nav-link" href="./matriculas/index.php">Matriculas</a></li>
        <li class="nav-item"><a class="nav-link" href="./usuario/index.php">Usuários</a></li>
        <li class="nav-item"><a class="nav-link active" href="./permissoes/index.php">Permissões</a></li>
        <li class="nav-item"><a class="nav-link" href="./relatorios/index.php">Relatórios</a></li>
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i></a>
        </li>
      </ul>
    </div>
  </div>
</nav>   
<div class="container mt-5">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="sorteio-tab" data-bs-toggle="tab" data-bs-target="#sorteio" type="button" role="tab">Realizar Sorteio</button>
      </li>
    </ul>
    
    <div class="tab-content" id="myTabContent">
      
      <!-- Aba de Sorteio -->
      <div class="tab-pane fade show active" id="sorteio" role="tabpanel">
        <div class="card shadow-lg">
          <div class="card-header bg-success text-white">
            <h4><i class="fas fa-gift me-2"></i>Sorteio</h4>
          </div>
          <div class="card-body">
            <form id="formSorteio">
              <div class="row mb-4">
                  <div class="col-md-4">
                      <label for="sorteio_congregacao" class="form-label">Congregação</label>
                      <select class="form-select" id="sorteio_congregacao" required>
                          <option value="">Selecione a Congregação</option>
                      </select>
                  </div>
                  <div class="col-md-4">
                      <label for="sorteio_classe" class="form-label">Classe</label>
                      <select class="form-select" id="sorteio_classe" required disabled>
                          <option value="">Selecione a Classe</option>
                      </select>
                  </div>
                  <div class="col-md-4">
                      <label for="sorteio_trimestre" class="form-label">Trimestre</label>
                      <select class="form-select" id="sorteio_trimestre" required>
                          <option value="">Selecione o Trimestre</option>
                          <option value="1">1º Trimestre</option>
                          <option value="2">2º Trimestre</option>
                          <option value="3">3º Trimestre</option>
                          <option value="4">4º Trimestre</option>
                      </select>
                  </div>
              </div>
              
              <div id="alunosContainer" style="display: none;">
                <div class="select-all-container">
                  <button type="button" id="btnSelecionarTodos" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-circle me-1"></i> Selecionar Todos
                  </button>
                  <button type="button" id="btnDesmarcarTodos" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="fas fa-times-circle me-1"></i> Desmarcar Todos
                  </button>
                  <span id="contadorSelecionados" class="badge bg-primary ms-2">0 selecionados</span>
                </div>
                
                <h5 class="mb-3">Alunos da Classe</h5>
                <div id="alunosLista" class="border rounded p-2"></div>
                
                <div class="text-center mt-3">
                  <button type="button" id="btnRealizarSorteio" class="btn btn-success btn-lg">
                    <i class="fas fa-random me-2"></i>Realizar Sorteio
                  </button>
                </div>
              </div>
              
              <div id="sorteioResultados" class="mt-4" style="display: none;">
                <h5 class="text-center mb-3">Resultado do Sorteio</h5>
                
                <div class="sorteio-animacao text-center mb-3" id="animacaoSorteio"></div>
                
                <div class="sorteio-resultado" id="resultadoSorteio">
                  <div class="ganhador-destaque" id="ganhadorNome"></div>
                  <div id="ganhadorDetalhes"></div>
                </div>
                
                <div class="card mt-3">
                  <div class="card-header">
                    <h6>Critérios do Sorteio</h6>
                  </div>
                  <div class="card-body">
                    <p>Foram considerados todos os alunos presentes na lista acima.</p>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Carrega congregações
    function carregarCongregacoes() {
        $.ajax({
            url: '../controllers/chamada.php',
            type: 'POST',
            data: { acao: 'getCongregacoes' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Selecione a Congregação</option>';
                    response.data.forEach(c => {
                        options += `<option value="${c.id}">${c.nome}</option>`;
                    });
                    $('#sorteio_congregacao').html(options);
                } else {
                    Swal.fire('Erro', 'Não foi possível carregar as congregações', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Erro ao carregar congregações';
                try {
                    const response = xhr.responseJSON;
                    if (response && response.message) {
                        errorMsg = response.message;
                    }
                } catch (e) {}
                Swal.fire('Erro', errorMsg, 'error');
            }
        });
    }

    // Quando muda a congregação no sorteio
    $('#sorteio_congregacao').change(function() {
        const congregacaoId = $(this).val();
        $('#sorteio_classe').prop('disabled', true).html('<option value="">Selecione a Classe</option>');
        $('#alunosContainer').hide();
        $('#sorteioResultados').hide();
        
        if (congregacaoId) {
            $.ajax({
                url: '../controllers/chamada.php',
                type: 'POST',
                data: { 
                    acao: 'getClassesByCongregacao',
                    congregacao_id: congregacaoId 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let options = '<option value="">Selecione a Classe</option>';
                        response.data.forEach(classe => {
                            options += `<option value="${classe.id}">${classe.nome}</option>`;
                        });
                        $("#sorteio_classe").html(options).prop('disabled', false);
                    } else {
                        $("#sorteio_classe").html('<option value="">Nenhuma classe disponível</option>').prop('disabled', true);
                        Swal.fire('Aviso', response.message || 'Nenhuma classe encontrada', 'info');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Erro ao carregar classes';
                    try {
                        const response = xhr.responseJSON;
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {}
                    Swal.fire('Erro', errorMsg, 'error');
                }
            });
        }
    });

    
// Quando muda a classe ou trimestre
$('#sorteio_classe, #sorteio_trimestre').change(function() {
    const classeId = $('#sorteio_classe').val();
    const congregacaoId = $('#sorteio_congregacao').val();
    const trimestre = $('#sorteio_trimestre').val();
    
    $('#alunosContainer').hide();
    $('#sorteioResultados').hide();
    
    if (!classeId || !congregacaoId || !trimestre) {
        return;
    }

    $.ajax({
        url: '../controllers/sorteio.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            acao: 'getAlunosParaSorteio',
            classe_id: classeId,
            congregacao_id: congregacaoId,
            trimestre: trimestre
        }),
        dataType: 'json',
        beforeSend: function() {
            $('#alunosLista').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Carregando alunos...</p></div>');
        },
        success: function(response) {
            if (response.status === 'success') {
                if (response.data && response.data.length > 0) {
                    let alunosHtml = '';
                    response.data.forEach(aluno => {
                        alunosHtml += 
                            `<div class="aluno-item" data-id="${aluno.id}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${aluno.nome}</strong>
                                        <div class="text-muted small">${aluno.classe_nome || 'Sem classe'}</div>
                                    </div>
                                    <i class="fas fa-user-check text-success" style="display: none;"></i>
                                </div>
                            </div>`;
                    });
                    
                    $('#alunosLista').html(alunosHtml);
                    $('#alunosContainer').show();
                    
                    $('.aluno-item').click(function() {
                        $(this).toggleClass('selected');
                        $(this).find('.fa-user-check').toggle();
                        atualizarContadorSelecionados();
                    });
                    
                    atualizarContadorSelecionados();
                } else {
                    $('#alunosLista').html(`
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            ${response.message || 'Nenhum aluno encontrado com os critérios atuais'}
                        </div>
                    `);
                    $('#alunosContainer').show(); // Mostra o container mesmo sem alunos
                }
            } else {
                $('#alunosLista').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ${response.message || 'Erro ao carregar alunos'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Erro ao carregar alunos';
            try {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {}
            $('#alunosLista').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ${errorMsg}
                </div>
            `);
        }
    });
});
    // Atualiza contador de selecionados
    function atualizarContadorSelecionados() {
        const totalSelecionados = $('.aluno-item.selected').length;
        $('#contadorSelecionados').text(totalSelecionados + ' selecionados');
    }

    // Selecionar todos
    $('#btnSelecionarTodos').click(function() {
        $('.aluno-item').addClass('selected');
        $('.aluno-item .fa-user-check').show();
        atualizarContadorSelecionados();
    });

    // Desmarcar todos
    $('#btnDesmarcarTodos').click(function() {
        $('.aluno-item').removeClass('selected');
        $('.aluno-item .fa-user-check').hide();
        atualizarContadorSelecionados();
    });

    // Realizar sorteio via backend
    $('#btnRealizarSorteio').click(function() {
        const alunosSelecionados = $('.aluno-item.selected');
        
        if (alunosSelecionados.length === 0) {
            Swal.fire('Atenção', 'Selecione pelo menos um aluno para o sorteio', 'warning');
            return;
        }

        const alunosIds = [];
        alunosSelecionados.each(function() {
            alunosIds.push($(this).data('id'));
        });

        const classeId = $('#sorteio_classe').val();
        const congregacaoId = $('#sorteio_congregacao').val();

        if (!classeId || !congregacaoId) {
            Swal.fire('Atenção', 'Selecione uma congregação e uma classe válidas', 'warning');
            return;
        }

        // Mostra área de resultados
        $('#sorteioResultados').show().css('opacity', 0).animate({opacity: 1}, 500);
        $('#resultadoSorteio').hide();
        $('#animacaoSorteio').html('<div class="animate__animated animate__flash">Sorteando...</div>');

        // Desabilita o botão durante a requisição
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Sorteando...');

        $.ajax({
            url: '../controllers/sorteio.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                acao: 'realizarSorteio',
                alunos_ids: alunosIds,
                classe_id: classeId,
                congregacao_id: congregacaoId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const ganhador = response.data.ganhador;
                    
                    $('#ganhadorNome').html(`<i class="fas fa-trophy me-2"></i>${ganhador.nome}`);
                    $('#ganhadorDetalhes').html(`
                        <p><i class="fas fa-users me-2"></i> Classe: ${ganhador.classe_nome || 'Não informada'}</p>
                        <p><i class="fas fa-award me-2"></i> Parabéns!</p>
                        <p><i class="fas fa-calendar me-2"></i> Sorteado em: ${response.data.data_sorteio}</p>
                    `);
                    
                    $('#resultadoSorteio').fadeIn(1000);
                    $('#animacaoSorteio').empty();
                } else {
                    Swal.fire('Erro', response.message || 'Erro desconhecido', 'error');
                    $('#sorteioResultados').hide();
                }
            },
            error: function(xhr) {
                let errorMsg = 'Erro ao conectar com o servidor';
                try {
                    const response = xhr.responseJSON;
                    if (response && response.message) {
                        errorMsg = response.message;
                    }
                } catch (e) {
                    console.error('Erro ao processar resposta:', e);
                }
                
                Swal.fire('Erro', errorMsg, 'error');
                $('#sorteioResultados').hide();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-random me-2"></i> Realizar Sorteio');
            }
        });
    });

    // Carrega congregações ao iniciar
    carregarCongregacoes();
});
</script>
<?php
require_once '../includes/footer.php';
?>