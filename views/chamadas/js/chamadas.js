// Função para deletar uma chamada
function deletarChamada(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter essa ação!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('caminho_do_arquivo_php.php?acao=deletar&id=' + id, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Excluído!', 'A chamada foi excluída com sucesso.', 'success');
                    // Aqui você pode remover a linha da tabela ou atualizar a lista de chamadas
                    location.reload(); // Exemplo de recarregar a página para refletir as mudanças
                } else {
                    Swal.fire('Erro!', data.message, 'error');
                }
            })
            .catch(error => Swal.fire('Erro!', 'Erro ao excluir a chamada.', 'error'));
        }
    });
}
