<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmacaoLabel">Confirmar Exclusão</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex align-items-center mb-3">
          <div class="me-3">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
          </div>
          <div>
            <p class="mb-0 fs-5">Esta operação não poderá ser desfeita.</p>
            <p class="text-muted mb-0" id="modalMensagem">Tem certeza que deseja excluir este item?</p>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <!-- Botão de confirmação agora é um botão, não um link -->
        <button type="button" id="btnConfirmarExclusao" class="btn btn-danger">Excluir</button>
      </div>
    </div>
  </div>
</div>

<!-- Formulário oculto para submissão POST -->
<form id="formExclusao" method="POST" action="" style="display: none;">
  <input type="hidden" name="_method" value="DELETE"> <!-- Opcional: para simular método DELETE se o backend suportar -->
</form>

<!-- Script para gerenciar o modal de confirmação -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Adicionar Bootstrap Icons se ainda não estiver incluído
  if (!document.querySelector('link[href*="bootstrap-icons"]')) {
    const iconLink = document.createElement('link');
    iconLink.rel = 'stylesheet';
    iconLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css';
    document.head.appendChild(iconLink);
  }

  const modalElement = document.getElementById('modalConfirmacao');
  const modalMensagem = document.getElementById('modalMensagem');
  const btnConfirmarExclusao = document.getElementById('btnConfirmarExclusao');
  const formExclusao = document.getElementById('formExclusao');
  let urlExclusao = ''; // Variável para armazenar a URL de exclusão

  // Função para configurar os botões de exclusão nas tabelas
  function configurarBotoesExclusaoTabela() {
    const botoesExcluirTabela = document.querySelectorAll('.btn-danger[href*="action=delete"]');

    botoesExcluirTabela.forEach(botao => {
      // Remover evento onclick antigo, se houver
      botao.removeAttribute('onclick');

      // Adicionar novo evento de clique para abrir o modal
      botao.addEventListener('click', function(e) {
        e.preventDefault(); // Impedir a navegação padrão do link

        urlExclusao = this.getAttribute('href'); // Armazenar a URL
        const tipo = urlExclusao.match(/type=([^&]*)/)[1];

        // Personalizar mensagem baseada no tipo
        let mensagem = 'Tem certeza que deseja excluir este item?';
        switch(tipo) {
          case 'usuarios':
            mensagem = 'Tem certeza que deseja excluir este usuário?';
            break;
          case 'locais':
            mensagem = 'Tem certeza que deseja excluir este local?';
            break;
          case 'tipos':
            mensagem = 'Tem certeza que deseja excluir este tipo?';
            break;
          case 'setores':
            mensagem = 'Tem certeza que deseja excluir este setor?';
            break;
        }

        // Atualizar mensagem do modal
        modalMensagem.textContent = mensagem;

        // Abrir o modal
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      });
    });
  }

  // Adicionar evento de clique ao botão de confirmação DENTRO do modal
  btnConfirmarExclusao.addEventListener('click', function() {
    if (urlExclusao) {
      // Configurar o formulário oculto para enviar a requisição POST
      formExclusao.action = urlExclusao;
      formExclusao.submit();
    }
  });

  // Configurar botões quando o DOM estiver pronto
  configurarBotoesExclusaoTabela();

  // Observador para reconfigurar botões se o conteúdo da tabela for carregado dinamicamente
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.addedNodes.length) {
        configurarBotoesExclusaoTabela(); // Reconfigurar se novos nós forem adicionados
      }
    });
  });

  // Observar mudanças nas tabelas
  const tabelas = document.querySelectorAll('.table tbody'); // Observar o corpo da tabela é mais eficiente
  tabelas.forEach(tabelaBody => {
    observer.observe(tabelaBody, { childList: true, subtree: true });
  });

});
</script>
