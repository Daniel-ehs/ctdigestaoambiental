<?php

require_once __DIR__ . '/../controllers/TelaController.php';

$telaController = new TelaController();

// Parâmetros de filtro e paginação
$page = (int)($_GET['page'] ?? 1);
$limit = 10;

// Busca telas
$result = $telaController->getAll($page, $limit);
$telas = $result['telas'] ?? [];
$total = $result['total'] ?? 0;

$user = SessionManager::getUser();

$pageTitle = 'Gerenciamento de Telas';

$pageActions = '
    <button class="modern-btn modern-btn-primary" onclick="window.location.href=\'tela_criar.php\'">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Tela
    </button>
';

ob_start();
?>

<style>
/* Estilos modernos para a página de telas */
.modern-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.modern-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.5rem;
    color: white;
    border-radius: 16px 16px 0 0;
}

.modern-card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.25rem;
}

.modern-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.modern-table {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.modern-table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    font-weight: 600;
    color: #495057;
    padding: 1rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border: none;
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9ff 0%, #fff5f5 100%);
    transform: scale(1.01);
}

.modern-table tbody td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.modern-btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.modern-btn:hover::before {
    left: 100%;
}

.modern-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.modern-btn-group .btn {
    border-radius: 6px;
    margin: 0 2px;
    transition: all 0.2s ease;
}

.modern-btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.modern-empty-state {
    padding: 4rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 16px;
    margin: 2rem 0;
}

.modern-empty-state i {
    background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modern-pagination .page-link {
    border: none;
    border-radius: 8px;
    margin: 0 2px;
    color: #667eea;
    font-weight: 500;
    transition: all 0.2s ease;
}

.modern-pagination .page-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-1px);
}

.modern-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.modern-modal .modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.modern-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.modern-modal .modal-body {
    padding: 2rem;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Bolinhas de Status para Telas */
.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.status-dot:hover {
    transform: scale(1.3);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.status-dot.online {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    box-shadow: 0 0 8px rgba(72, 187, 120, 0.4);
}

.status-dot.offline {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    box-shadow: 0 0 8px rgba(245, 101, 101, 0.4);
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    font-size: 0.875rem;
}

.status-online {
    color: #38a169;
}

.status-offline {
    color: #e53e3e;
}

/* Tooltips */
.tooltip-custom {
    position: relative;
    display: inline-block;
}

.tooltip-custom .tooltip-text {
    visibility: hidden;
    width: 120px;
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    color: white;
    text-align: center;
    border-radius: 8px;
    padding: 8px 12px;
    position: absolute;
    z-index: 1000;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s ease;
    font-size: 0.75rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.tooltip-custom .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #2d3748 transparent transparent transparent;
}

.tooltip-custom:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}

/* Botões de Ação Coloridos */
.action-btn {
    border: none !important;
    color: white !important;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-edit {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%) !important;
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4) !important;
}

.btn-view {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%) !important;
}

.btn-view:hover {
    background: linear-gradient(135deg, #2f855a 0%, #276749 100%) !important;
    box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4) !important;
}

.btn-delete {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%) !important;
    box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4) !important;
}

/* Avatar para telas */
.tela-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin-right: 0.75rem;
}

/* Centralização das bolinhas na tabela */
.dot-column {
    text-align: center !important;
    vertical-align: middle !important;
    width: 80px;
}
</style>

<div class="row fade-in">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-display me-2"></i>
                    Telas Cadastradas
                </h5>
                <span class="modern-badge pulse-animation"><?= $total ?> telas</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($telas)): ?>
                    <div class="modern-empty-state">
                        <i class="bi bi-display" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Nenhuma tela cadastrada</h4>
                        <p class="text-muted mb-4">Comece criando sua primeira tela digital.</p>
                        <button class="modern-btn modern-btn-primary" onclick="window.location.href='tela_criar.php'">
                            <i class="bi bi-plus-circle me-2"></i>
                            Criar Primeira Tela
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Localização</th>
                                    <th>Status</th>
                                    <th>Última Verificação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($telas as $tela): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="tela-avatar">
                                                    <i class="bi bi-display"></i>
                                                </div>
                                                <div>
                                                    <strong><?= sanitize($tela['nome']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= sanitize($tela['localizacao'] ?? 'Não informado') ?></span>
                                        </td>
                                        <td class="dot-column">
                                            <?php
                                            $status = getTelaStatus($tela['ultima_verificacao']);
                                            $statusClass = $status === 'online' ? 'status-online' : 'status-offline';
                                            $statusText = $status === 'online' ? 'Online' : 'Offline';
                                            ?>
                                            <div class="tooltip-custom">
                                                <span class="status-indicator <?= $statusClass ?>">
                                                    <span class="status-dot <?= $status ?>"></span>
                                                    <?= $statusText ?>
                                                </span>
                                                <span class="tooltip-text"><?= $statusText ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= $tela['ultima_verificacao'] ? formatDate($tela['ultima_verificacao']) : 'Nunca' ?></small>
                                        </td>
                                        <td>
                                            <div class="modern-btn-group btn-group btn-group-sm">
                                                <a href="tela_editar.php?id=<?= $tela['id'] ?>" 
                                                   class="btn btn-edit action-btn" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="<?= $telaController->generatePlayUrl($tela['hash_unico'], $tela['com_moldura']) ?>" 
                                                   class="btn btn-view action-btn" 
                                                   title="Visualizar" 
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-delete action-btn" 
                                                        title="Excluir"
                                                        onclick="confirmarExclusao(<?= $tela['id'] ?>, '<?= sanitize($tela['nome']) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($total > $limit): ?>
                        <div class="p-3">
                            <nav aria-label="Navegação de páginas">
                                <ul class="modern-pagination pagination justify-content-center">
                                    <?php
                                    $totalPages = ceil($total / $limit);
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    ?>
                                    
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>">
                                                <i class="bi bi-chevron-left"></i> Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>">
                                                Próximo <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade modern-modal" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Tem certeza que deseja excluir a tela?</h6>
                <div class="alert alert-warning">
                    <strong>Tela:</strong> <span id="nomeTelaExcluir"></span>
                </div>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Esta ação não poderá ser desfeita. A tela será removida permanentemente do sistema.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">
                    <i class="bi bi-trash me-2"></i>
                    Excluir Tela
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let telaParaExcluir = null;

function confirmarExclusao(id, nome) {
    telaParaExcluir = id;
    document.getElementById('nomeTelaExcluir').textContent = nome;
    
    // Abre o modal
    const modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
    modal.show();
}

// Evento do botão de confirmação no modal
document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
    if (telaParaExcluir) {
        excluirTela(telaParaExcluir);
    }
});

function excluirTela(id) {
    const btn = document.getElementById('btnConfirmarExclusao');
    const originalHtml = btn.innerHTML;
    
    // Mostrar loading
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Excluindo...';
    
    // Fazer requisição AJAX
    fetch('api/telas/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalExcluir'));
            modal.hide();
            
            // Mostrar sucesso e recarregar
            setTimeout(() => {
                alert('Tela excluída com sucesso!');
                location.reload();
            }, 300);
        } else {
            alert('Erro: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir tela.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>
