<?php
require_once '../../views/includes/header.php';
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Chamadas</h4>
    <a class="btn btn-primary" href="./index.php">
      <i class="fas fa-plus"></i> Nova Chamada
    </a>
  </div>

  <div id="tabelaChamadas" class="table-responsive">
    <!-- A tabela será preenchida via AJAX -->
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    listarChamadas();
  });

  function listarChamadas() {
    fetch('chamadas_helper.php?acao=listar')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          let tabela = `
            <table id="tabelaChamadasDataTable" class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Data</th>
                  <th>Classe</th>
                  <th>Oferta</th>
                  <th>Bíblias</th>
                  <th>Revistas</th>
                  <th>Visitantes</th>
                  <th>Trimestre</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>`;

          data.chamadas.forEach(chamada => {
            const dataObj = new Date(chamada.data);
            
            // Corrigir a data para o fuso horário correto
            const dataFormatada = corrigirData(dataObj);

            tabela += `
              <tr>
                <td>${chamada.id}</td>
                <td>${dataFormatada}</td>
                <td>${chamada.classe_nome}</td>
                <td>${chamada.oferta_classe}</td>
                <td>${chamada.total_biblias}</td>
                <td>${chamada.total_revistas}</td>
                <td>${chamada.total_visitantes}</td>
                <td>${chamada.trimestre}</td>
                <td>
                  <a href="visualizar_chamada.php?id=${chamada.id}" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a>
                  <a href="editar_chamada.php?id=${chamada.id}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                </td>
              </tr>`;
          });

          tabela += '</tbody></table>';
          document.getElementById('tabelaChamadas').innerHTML = tabela;

          $('#tabelaChamadasDataTable').DataTable({
            language: {
              sProcessing: "Processando...",
              sLengthMenu: "Mostrar _MENU_ registros",
              sZeroRecords: "Nenhuma chamada encontrada",
              sInfo: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
              sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
              sInfoFiltered: "(filtrado de _MAX_ registros no total)",
              sSearch: "Pesquisar:",
              oPaginate: {
                sFirst: "Primeiro",
                sPrevious: "Anterior",
                sNext: "Próximo",
                sLast: "Último"
              }
            }
          });

        } else {
          document.getElementById('tabelaChamadas').innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
        }
      })
      .catch(() => {
        document.getElementById('tabelaChamadas').innerHTML = '<div class="alert alert-danger">Erro ao carregar as chamadas.</div>';
      });
  }

  function corrigirData(dataObj) {
    // Subtrair 1 dia para corrigir o fuso horário (no caso de UTC para horário local)
    dataObj.setDate(dataObj.getDate() + 1);
    return dataObj.toLocaleDateString('pt-BR'); // Formato 'dd/mm/yyyy'
  }

  function deletarChamada(id) {
    Swal.fire({
      title: 'Excluir chamada?',
      text: "Essa ação não poderá ser desfeita!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(`chamadas_helper.php?acao=deletar&id=${id}`, { method: 'POST' })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire('Excluída!', data.message, 'success');
              listarChamadas();
            } else {
              Swal.fire('Erro!', data.message, 'error');
            }
          })
          .catch(() => Swal.fire('Erro!', 'Erro ao tentar excluir a chamada.', 'error'));
      }
    });
  }
</script>

<?php
require_once '../../views/includes/footer.php';
?>