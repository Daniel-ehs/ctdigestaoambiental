<?php

require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$userController = new UserController();

// Parâmetros de filtro e paginação
$page = (int)($_GET['page'] ?? 1);
$limit = 10;

// Busca usuários
$result = $userController->getAll($page, $limit);
$usuarios = $result['users'] ?? [];
$total = $result['total'] ?? 0;

$user = SessionManager::getUser();

$pageTitle = 'Gerenciamento de Usuários';
$breadcrumb = [];

$pageActions = '
    <button class="btn btn-primary" onclick="abrirModalCriarUsuario()">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Usuário
    </button>
';

ob_start();
?>

<style>
/* Estilos modernos para a página de usuários */
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

.modern-badge-status {
    border-radius: 20px;
    padding: 0.4rem 0.8rem;
    font-weight: 500;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-badge-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.modern-badge-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.modern-badge-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.modern-badge-primary {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
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

.modern-form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
    background: #f7fafc;
}

.modern-form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.modern-input-group .btn {
    border-radius: 0 8px 8px 0;
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

.user-avatar {
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

.stats-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

/* Bolinhas de Status e Tipo */
.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.status-dot:hover {
    transform: scale(1.3);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.status-dot.ativo {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    box-shadow: 0 0 8px rgba(72, 187, 120, 0.4);
}

.status-dot.inativo {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    box-shadow: 0 0 8px rgba(245, 101, 101, 0.4);
}

.tipo-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tipo-dot:hover {
    transform: scale(1.3);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.tipo-dot.gerente {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    box-shadow: 0 0 8px rgba(66, 153, 225, 0.4);
}

.tipo-dot.administrador {
    background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
    box-shadow: 0 0 8px rgba(159, 122, 234, 0.4);
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

.btn-password {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
}

.btn-password:hover {
    background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%) !important;
    box-shadow: 0 4px 12px rgba(237, 137, 54, 0.4) !important;
}

.btn-toggle {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%) !important;
}

.btn-toggle:hover {
    background: linear-gradient(135deg, #2f855a 0%, #276749 100%) !important;
    box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4) !important;
}

.btn-toggle.deactivate {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
}

.btn-toggle.deactivate:hover {
    background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%) !important;
    box-shadow: 0 4px 12px rgba(237, 137, 54, 0.4) !important;
}

.btn-delete {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%) !important;
    box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4) !important;
}

.btn-manage {
    background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%) !important;
    color: white !important;
    border: none !important;
}

.btn-manage:hover {
    background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%) !important;
    box-shadow: 0 4px 12px rgba(159, 122, 234, 0.4) !important;
    transform: translateY(-1px);
}

/* Centralização das bolinhas na tabela */
.dot-column {
    text-align: center !important;
    vertical-align: middle !important;
    width: 60px;
}

.tooltip-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

<div class="row fade-in">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Usuários Cadastrados
                </h5>
                <span class="modern-badge pulse-animation"><?= $total ?> usuários</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($usuarios)): ?>
                    <div class="modern-empty-state">
                        <i class="bi bi-people" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Nenhum usuário cadastrado</h4>
                        <p class="text-muted mb-4">Comece criando o primeiro usuário do sistema.</p>
                        <button class="modern-btn modern-btn-primary" onclick="abrirModalCriarUsuario()">
                            <i class="bi bi-plus-circle me-2"></i>
                            Criar Primeiro Usuário
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table table">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Data Criação</th>
                                    <th>Telas</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= sanitize($usuario['nome']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= sanitize($usuario['email']) ?></span>
                                        </td>
                                        <td class="dot-column">
                                            <div class="tooltip-custom">
                                                <span class="tipo-dot <?= $usuario['tipo'] ?>"></span>
                                                <span class="tooltip-text"><?= ucfirst($usuario['tipo']) ?></span>
                                            </div>
                                        </td>
                                        <td class="dot-column">
                                            <div class="tooltip-custom">
                                                <span class="status-dot <?= $usuario['ativo'] ? 'ativo' : 'inativo' ?>"></span>
                                                <span class="tooltip-text"><?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= formatDate($usuario['data_criacao']) ?></small>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-manage action-btn" 
                                                    onclick="gerenciarTelas(<?= $usuario['id'] ?>, '<?= sanitize($usuario['nome']) ?>')">
                                                <i class="bi bi-display me-1"></i> Gerenciar
                                            </button>
                                        </td>
                                        <td>
                                            <div class="modern-btn-group btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-edit action-btn" 
                                                        title="Editar"
                                                        onclick="editarUsuario(<?= $usuario['id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-password action-btn" 
                                                        title="Alterar Senha"
                                                        onclick="alterarSenha(<?= $usuario['id'] ?>, '<?= sanitize($usuario['nome']) ?>')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <?php if ($usuario['id'] !== $user['id']): ?>
                                                    <button type="button" 
                                                            class="btn btn-toggle action-btn <?= $usuario['ativo'] ? 'deactivate' : '' ?>" 
                                                            title="<?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>"
                                                            onclick="alterarStatus(<?= $usuario['id'] ?>, '<?= $usuario['ativo'] ? 'desativar' : 'ativar' ?>')">
                                                        <i class="bi bi-<?= $usuario['ativo'] ? 'pause' : 'play' ?>"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-delete action-btn" 
                                                            title="Excluir"
                                                            onclick="confirmarExclusao(<?= $usuario['id'] ?>, '<?= sanitize($usuario['nome']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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

<!-- Modal Criar/Editar Usuário -->
<div class="modal fade modern-modal" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioTitle">
                    <i class="bi bi-person-plus me-2"></i>
                    Criar Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" id="usuarioId" name="id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>
                            Nome *
                        </label>
                        <input type="text" class="form-control modern-form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1"></i>
                            Email *
                        </label>
                        <input type="email" class="form-control modern-form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3" id="senhaGroup">
                        <label for="senha" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>
                            Senha *
                        </label>
                        <div class="input-group modern-input-group">
                            <input type="password" class="form-control modern-form-control" id="senha" name="senha" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleSenhaCriar">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Mínimo de 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label fw-semibold">
                            <i class="bi bi-shield me-1"></i>
                            Tipo de Usuário *
                        </label>
                        <select class="form-control modern-form-control" id="tipo" name="tipo" required>
                            <option value="gerente">Gerente</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="ativoGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                            <label class="form-check-label fw-semibold" for="ativo">
                                <i class="bi bi-check-circle me-1"></i>
                                Usuário ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="modern-btn modern-btn-primary" onclick="salvarUsuario()">
                    <i class="bi bi-check-circle me-1"></i>
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Senha -->
<div class="modal fade modern-modal" id="modalSenha" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>
                    Alterar Senha
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSenha">
                    <input type="hidden" id="senhaUsuarioId" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>
                            Usuário
                        </label>
                        <input type="text" class="form-control modern-form-control" id="senhaUsuarioNome" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="novaSenha" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>
                            Nova Senha *
                        </label>
                        <div class="input-group modern-input-group">
                            <input type="password" class="form-control modern-form-control" id="novaSenha" name="novaSenha" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNovaSenha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Mínimo de 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmarSenha" class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1"></i>
                            Confirmar Nova Senha *
                        </label>
                        <div class="input-group modern-input-group">
                            <input type="password" class="form-control modern-form-control" id="confirmarSenha" name="confirmarSenha" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmarSenha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="salvarSenha()">
                    <i class="bi bi-key me-1"></i>
                    Alterar Senha
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gerenciar Telas -->
<div class="modal fade modern-modal" id="modalTelas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-display me-2"></i>
                    Gerenciar Telas do Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="telasUsuarioId">
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>
                        Usuário
                    </label>
                    <input type="text" class="form-control modern-form-control" id="telasUsuarioNome" readonly>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="bi bi-plus-circle me-1"></i>
                            Telas Disponíveis
                        </h6>
                        <div id="telasDisponiveis" class="border rounded-3 p-3" style="min-height: 200px; max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                            <div class="text-center text-muted">
                                <i class="bi bi-hourglass-split"></i>
                                Carregando...
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="bi bi-check-circle me-1"></i>
                            Telas Vinculadas
                        </h6>
                        <div id="telasVinculadas" class="border rounded-3 p-3" style="min-height: 200px; max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                            <div class="text-center text-muted">
                                <i class="bi bi-hourglass-split"></i>
                                Carregando...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Exclusão -->
<div class="modal fade modern-modal" id="modalConfirmarExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Tem certeza que deseja excluir este usuário?</h5>
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <p class="mb-1 text-muted small">Usuário:</p>
                        <p class="mb-0 fw-bold" id="nomeUsuarioExcluir"></p>
                    </div>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <input type="hidden" id="idUsuarioExcluir">
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoUsuario()">
                    <i class="bi bi-trash me-1"></i>
                    Excluir Usuário
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let editandoUsuario = false;

function abrirModalCriarUsuario() {
    editandoUsuario = false;
    document.getElementById('modalUsuarioTitle').textContent = 'Criar Usuário';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('senhaGroup').style.display = 'block';
    document.getElementById('senha').required = true;
    document.getElementById('ativoGroup').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

function editarUsuario(id) {
    editandoUsuario = true;
    document.getElementById('modalUsuarioTitle').textContent = 'Editar Usuário';
    document.getElementById('senhaGroup').style.display = 'none';
    document.getElementById('senha').required = false;
    document.getElementById('ativoGroup').style.display = 'block';
    
    // Buscar dados do usuário
    fetch(`/public/api/usuarios/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('usuarioId').value = data.user.id;
                document.getElementById('nome').value = data.user.nome;
                document.getElementById('email').value = data.user.email;
                document.getElementById('tipo').value = data.user.tipo;
                document.getElementById('ativo').checked = data.user.ativo == 1;
                
                const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
                modal.show();
            } else {
                mostrarAlerta('danger', data.message || 'Erro ao carregar dados do usuário');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarAlerta('danger', 'Erro ao carregar dados do usuário');
        });
}

function salvarUsuario() {
    const nome = document.getElementById('nome').value.trim();
    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value;
    const tipo = document.getElementById('tipo').value;
    const ativo = document.getElementById('ativo').checked;
    const id = document.getElementById('usuarioId').value;
    
    // Validações básicas
    if (!nome || !email || (!senha && !editandoUsuario) || !tipo) {
        mostrarAlerta('danger', 'Preencha todos os campos obrigatórios');
        return;
    }
    
    if (!editandoUsuario && senha.length < 6) {
        mostrarAlerta('danger', 'A senha deve ter pelo menos 6 caracteres');
        return;
    }
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarAlerta('danger', 'Digite um email válido');
        return;
    }
    
    const formData = new FormData();
    formData.append('nome', nome);
    formData.append('email', email);
    formData.append('tipo', tipo);
    
    if (editandoUsuario) {
        formData.append('id', id);
        formData.append('ativo', ativo ? '1' : '0');
    } else {
        formData.append('senha', senha);
    }
    
    const url = editandoUsuario ? '/public/api/usuarios/update.php' : '/public/api/usuarios/create.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAlerta('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarAlerta('danger', 'Erro ao salvar usuário');
    });
}

function alterarSenha(id, nome) {
    document.getElementById('formSenha').reset();
    document.getElementById('senhaUsuarioId').value = id;
    document.getElementById('senhaUsuarioNome').value = nome;
    
    const modal = new bootstrap.Modal(document.getElementById('modalSenha'));
    modal.show();
}

function salvarSenha() {
    const novaSenha = document.getElementById('novaSenha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;
    
    if (novaSenha !== confirmarSenha) {
        mostrarAlerta('danger', 'As senhas não coincidem');
        return;
    }
    
    if (novaSenha.length < 6) {
        mostrarAlerta('danger', 'A senha deve ter pelo menos 6 caracteres');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', document.getElementById('senhaUsuarioId').value);
    formData.append('novaSenha', novaSenha);
    
    fetch('/public/api/usuarios/reset-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalSenha')).hide();
        } else {
            mostrarAlerta('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarAlerta('danger', 'Erro ao alterar senha');
    });
}

function gerenciarTelas(userId, userName) {
    document.getElementById('telasUsuarioId').value = userId;
    document.getElementById('telasUsuarioNome').value = userName;
    
    // Carregar telas disponíveis e vinculadas
    carregarTelas(userId);
    
    const modal = new bootstrap.Modal(document.getElementById('modalTelas'));
    modal.show();
}

function carregarTelas(userId) {
    // Carregar telas disponíveis
    fetch('/public/api/telas/list.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('telasDisponiveis');
            if (data.success && data.telas.length > 0) {
                container.innerHTML = data.telas.map(tela => `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <span>${tela.nome}</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="vincularTela(${userId}, ${tela.id})">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center text-muted">Nenhuma tela disponível</div>';
            }
        });
    
    // Carregar telas vinculadas
    fetch(`/public/api/usuarios/telas.php?userId=${userId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('telasVinculadas');
            if (data.success && data.telas.length > 0) {
                container.innerHTML = data.telas.map(tela => `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-light">
                        <span>${tela.nome}</span>
                        <button class="btn btn-sm btn-outline-danger" onclick="desvincularTela(${userId}, ${tela.id})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center text-muted">Nenhuma tela vinculada</div>';
            }
        });
}

function vincularTela(userId, telaId) {
    const formData = new FormData();
    formData.append('userId', userId);
    formData.append('telaId', telaId);
    
    fetch('/public/api/usuarios/vincular-tela.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message);
            carregarTelas(userId);
        } else {
            mostrarAlerta('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarAlerta('danger', 'Erro ao vincular tela');
    });
}

function desvincularTela(userId, telaId) {
    const formData = new FormData();
    formData.append('userId', userId);
    formData.append('telaId', telaId);
    
    fetch('/public/api/usuarios/desvincular-tela.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message);
            carregarTelas(userId);
        } else {
            mostrarAlerta('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarAlerta('danger', 'Erro ao desvincular tela');
    });
}

function alterarStatus(id, acao) {
    if (confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('acao', acao);
        
        fetch('/public/api/usuarios/status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarAlerta('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarAlerta('danger', 'Erro ao alterar status do usuário');
        });
    }
}

function confirmarExclusao(id, nome) {
    document.getElementById('idUsuarioExcluir').value = id;
    document.getElementById('nomeUsuarioExcluir').textContent = nome;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    modal.show();
}

function confirmarExclusaoUsuario() {
    const id = document.getElementById('idUsuarioExcluir').value;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('/public/api/usuarios/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAlerta('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarAlerta('danger', 'Erro ao excluir usuário');
    });
}

function mostrarAlerta(tipo, mensagem) {
    const alertsContainer = document.getElementById('alerts-container');
    if (!alertsContainer) {
        // Se não existe o container de alertas, criar um temporário
        const tempContainer = document.createElement('div');
        tempContainer.style.position = 'fixed';
        tempContainer.style.top = '20px';
        tempContainer.style.right = '20px';
        tempContainer.style.zIndex = '9999';
        document.body.appendChild(tempContainer);
        
        const alertHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                <i class="bi bi-${tipo === 'success' ? 'check-circle' : (tipo === 'danger' ? 'exclamation-triangle' : 'info-circle')} me-2"></i>
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        tempContainer.innerHTML = alertHTML;
        
        // Remove o alerta após 5 segundos
        setTimeout(() => {
            tempContainer.remove();
        }, 5000);
        
        return;
    }
    
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            <i class="bi bi-${tipo === 'success' ? 'check-circle' : (tipo === 'danger' ? 'exclamation-triangle' : 'info-circle')} me-2"></i>
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertsContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Remove o alerta automaticamente após 5 segundos
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Funcionalidade para mostrar/esconder senhas
document.addEventListener('DOMContentLoaded', function() {
    // Toggle para senha de criação
    const toggleSenhaCriar = document.getElementById('toggleSenhaCriar');
    const senhaInput = document.getElementById('senha');
    
    if (toggleSenhaCriar && senhaInput) {
        toggleSenhaCriar.addEventListener('click', function() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
    
    // Toggle para nova senha
    const toggleNovaSenha = document.getElementById('toggleNovaSenha');
    const novaSenhaInput = document.getElementById('novaSenha');
    
    if (toggleNovaSenha && novaSenhaInput) {
        toggleNovaSenha.addEventListener('click', function() {
            const type = novaSenhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            novaSenhaInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
    
    // Toggle para confirmar senha
    const toggleConfirmarSenha = document.getElementById('toggleConfirmarSenha');
    const confirmarSenhaInput = document.getElementById('confirmarSenha');
    
    if (toggleConfirmarSenha && confirmarSenhaInput) {
        toggleConfirmarSenha.addEventListener('click', function() {
            const type = confirmarSenhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmarSenhaInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>

