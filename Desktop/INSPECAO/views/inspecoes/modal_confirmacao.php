<!-- Formulário oculto para submissão POST -->
<form id="formExclusao" method="POST" action="" style="display: none;">
  <input type="hidden" name="_method" value="DELETE">
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('modalConfirmacao');
  const modalMensagem = document.getElementById('modalMensagem');
  const btnConfirmarExclusao = document.getElementById('btnConfirmarExclusao');
  const formExclusao = document.getElementById('formExclusao');
  let urlExclusao = '';

  // Mensagens específicas por tipo
  function obterMensagemPorTipo(tipo) {
    let base = 'Tem certeza que deseja excluir ';
    switch (tipo) {
      case 'usuarios':
        return base + 'este usuário? Esta operação não poderá ser desfeita.';
      case 'locais':
        return base + 'este local? Esta operação não poderá ser desfeita.';
      case 'tipos':
        return base + 'este tipo? Esta operação não poderá ser desfeita.';
      case 'setores':
        return base + 'este setor? Esta operação não poderá ser desfeita.';
      default:
        return 'Tem certeza que deseja excluir este item? Esta operação não poderá ser desfeita.';
    }
  }

  // Configura os botões de exclusão
  function configurarBotoesExclusaoTabela() {
    const botoesExcluir = document.querySelectorAll('.btn-danger[href*="action=delete"]');
    botoesExcluir.forEach(botao => {
      botao.removeAttribute('onclick');
      botao.addEventListener('click', function (e) {
        e.preventDefault();
        urlExclusao = this.getAttribute('href');
        const tipo = urlExclusao.match(/type=([^&]*)/)?.[1] || '';
        modalMensagem.textContent = obterMensagemPorTipo(tipo);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      });
    });
  }

  // Confirma a exclusão
  btnConfirmarExclusao.addEventListener('click', function () {
    if (urlExclusao) {
      formExclusao.action = urlExclusao;
      formExclusao.submit();
    }
  });

  configurarBotoesExclusaoTabela();

  // Atualiza botões em DOM dinâmico
  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (mutation.addedNodes.length) {
        configurarBotoesExclusaoTabela();
      }
    });
  });

  document.querySelectorAll('.table tbody').forEach(tabelaBody => {
    observer.observe(tabelaBody, { childList: true, subtree: true });
  });
});
</script>
