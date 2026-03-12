<?php

require_once __DIR__ . 
'/../controllers/TelaController.php';
require_once __DIR__ . 
'/../controllers/MidiaController.php';

$telaController = new TelaController();
$midiaController = new MidiaController();

// Busca dados para o dashboard
$statusSummary = $telaController->getStatusSummary();
$midiasCounts = $midiaController->getCountByType();
$telas = $telaController->getAll();

$user = SessionManager::getUser();

// Se for gerente, busca apenas suas telas
if ($user["tipo"] === "gerente") {
    $telas = $telaController->getTelasByUser($user["id"]);
}

$pageTitle = 'Dashboard';
$breadcrumb = [];

ob_start();
?>

<style>
/* Estilos modernos para o dashboard */
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
    content: "";
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

.modern-btn-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.modern-btn-secondary:hover {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(160, 174, 192, 0.4);
}

.modern-btn-success {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    color: white;
}

.modern-btn-success:hover {
    background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
}

.modern-btn-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.modern-btn-info:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
}

.modern-btn-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
    color: white;
}

.modern-btn-warning:hover {
    background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(237, 137, 54, 0.4);
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

/* Cards de Estatísticas */
.modern-stats-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    height: 120px; /* Altura reduzida */
    position: relative;
    padding: 1rem; /* Padding reduzido */
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modern-stats-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 8px;
    height: 100%;
    border-radius: 16px 0 0 16px;
}

.modern-stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.modern-stats-card .stats-content {
    flex-grow: 1;
}

.modern-stats-card .stats-number {
    font-size: 2rem; /* Tamanho reduzido */
    font-weight: 700;
    color: #4a5568;
    margin-bottom: 0.25rem;
}

.modern-stats-card .stats-label {
    font-size: 0.8rem; /* Tamanho reduzido */
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-stats-card .stats-icon {
    font-size: 2.5rem; /* Tamanho reduzido */
    margin-left: 1rem;
}

/* Cores para os cards de estatísticas e ícones */
.modern-stats-card.primary::before { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.modern-stats-card.primary .stats-icon { color: #667eea; }

.modern-stats-card.success::before { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
.modern-stats-card.success .stats-icon { color: #48bb78; }

.modern-stats-card.danger::before { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }
.modern-stats-card.danger .stats-icon { color: #f56565; }

.modern-stats-card.info::before { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
.modern-stats-card.info .stats-icon { color: #4299e1; }

/* Detalhamento de Mídias */
.modern-media-type-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    margin-bottom: 0.75rem;
}

.modern-media-type-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modern-media-type-item i {
    font-size: 2.5rem;
    margin-right: 1rem;
    width: 40px;
    text-align: center;
    color: white !important;
}

.modern-media-type-item .h4 {
    margin-bottom: 0;
    font-weight: 600;
    color: #4a5568;
}

.modern-media-type-item small {
    display: block;
    color: #718096;
}

/* Cores específicas para cada tipo de mídia */
.modern-media-type-item.video {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.modern-media-type-item.video .h4,
.modern-media-type-item.video small {
    color: white;
}

.modern-media-type-item.image {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.modern-media-type-item.image .h4,
.modern-media-type-item.image small {
    color: white;
}

.modern-media-type-item.youtube {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
}

.modern-media-type-item.youtube .h4,
.modern-media-type-item.youtube small {
    color: white;
}

.modern-media-type-item.link {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.modern-media-type-item.link .h4,
.modern-media-type-item.link small {
    color: white;
}

/* Status das Telas */
.status-indicator {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.status-online {
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    color: #38a169;
}

.status-online .status-dot { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }

.status-offline {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    color: #e53e3e;
}

.status-offline .status-dot { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }

/* Ações Rápidas */
.modern-quick-action-btn {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #4a5568;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.modern-quick-action-btn:hover {
    background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
    color: #4a5568;
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.modern-quick-action-btn i {
    font-size: 3.5rem;
    margin-bottom: 0.75rem;
}

.modern-quick-action-btn span {
    font-weight: 600;
    font-size: 1.1rem;
}

/* Cores específicas para cada ação rápida */
.modern-quick-action-btn.upload i {
    color: #667eea;
}

.modern-quick-action-btn.playlist i {
    color: #48bb78;
}

.modern-quick-action-btn.screen i {
    color: #4299e1;
}

.modern-quick-action-btn.user i {
    color: #ed8936;
}

/* Animação de rotação para o ícone de refresh */
.spin {
    animation: spin 1.5s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>

<div class="row fade-in">
    <!-- Cards de Estatísticas -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="modern-stats-card primary">
            <div class="stats-content">
                <div class="stats-number" id="total-telas">
                    <?= $statusSummary["summary"]["total"] ?? 0 ?>
                </div>
                <div class="stats-label">Telas Cadastradas</div>
            </div>
            <i class="bi bi-display stats-icon"></i>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="modern-stats-card success">
            <div class="stats-content">
                <div class="stats-number" id="telas-online">
                    <?= $statusSummary["summary"]["online"] ?? 0 ?>
                </div>
                <div class="stats-label">Telas Online</div>
            </div>
            <i class="bi bi-wifi stats-icon"></i>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="modern-stats-card danger">
            <div class="stats-content">
                <div class="stats-number" id="telas-offline">
                    <?= $statusSummary["summary"]["offline"] ?? 0 ?>
                </div>
                <div class="stats-label">Telas Offline</div>
            </div>
            <i class="bi bi-wifi-off stats-icon"></i>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="modern-stats-card info">
            <div class="stats-content">
                <div class="stats-number">
                    <?= ($midiasCounts["counts"]["video"] ?? 0) + ($midiasCounts["counts"]["imagem"] ?? 0) + ($midiasCounts["counts"]["youtube"] ?? 0) + ($midiasCounts["counts"]["link_imagem"] ?? 0) ?>
                </div>
                <div class="stats-label">Total de Mídias</div>
            </div>
            <i class="bi bi-collection-play stats-icon"></i>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detalhamento de Mídias -->
    <div class="col-lg-6 mb-4">
        <div class="modern-card h-100">
            <div class="modern-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Mídias por Tipo
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="row flex-grow-1">
                    <div class="col-6 mb-3">
                        <div class="modern-media-type-item video">
                            <i class="bi bi-play-circle"></i>
                            <div>
                                <div class="h4 mb-0"><?= $midiasCounts["counts"]["video"] ?? 0 ?></div>
                                <small>Vídeos</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="modern-media-type-item image">
                            <i class="bi bi-image"></i>
                            <div>
                                <div class="h4 mb-0"><?= $midiasCounts["counts"]["imagem"] ?? 0 ?></div>
                                <small>Imagens</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="modern-media-type-item youtube">
                            <i class="bi bi-youtube"></i>
                            <div>
                                <div class="h4 mb-0"><?= $midiasCounts["counts"]["youtube"] ?? 0 ?></div>
                                <small>YouTube</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="modern-media-type-item link">
                            <i class="bi bi-link"></i>
                            <div>
                                <div class="h4 mb-0"><?= $midiasCounts["counts"]["link_imagem"] ?? 0 ?></div>
                                <small>Links</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-auto pt-3">
                    <a href="midias.php" class="btn modern-btn modern-btn-primary w-75">
                        <i class="bi bi-collection-play me-2"></i>
                        Gerenciar Mídias
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status das Telas -->
    <div class="col-lg-6 mb-4">
        <div class="modern-card h-100">
            <div class="modern-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-activity me-2"></i>
                    Status das Telas
                </h5>
                <button class="btn modern-btn modern-btn-secondary btn-sm" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="card-body d-flex flex-column">
                <?php if (empty($telas["telas"])): ?>
                    <div class="modern-empty-state py-4 flex-grow-1 d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-display" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Nenhuma tela cadastrada</p>
                        <?php if ($user["tipo"] === "administrador"): ?>
                            <a href="telas.php" class="btn modern-btn modern-btn-primary mt-3">
                                <i class="bi bi-plus-circle me-2"></i>
                                Cadastrar Tela
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive flex-grow-1">
                        <table class="modern-table table table-sm table-hover" id="telas-status">
                            <thead>
                                <tr>
                                    <th>Tela</th>
                                    <th>Status</th>
                                    <th>Última Verificação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($telas["telas"] as $tela): ?>
                                    <tr>
                                        <td>
                                            <strong><?= sanitize($tela["nome"]) ?></strong>
                                            <?php if (!empty($tela["localizacao"])): ?>
                                                <br><small class="text-muted"><?= sanitize($tela["localizacao"]) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = getTelaStatus($tela["ultima_verificacao"]);
                                            $statusClass = $status === "online" ? "status-online" : "status-offline";
                                            $statusText = $status === "online" ? "Online" : "Offline";
                                            ?>
                                            <span class="status-indicator <?= $statusClass ?>">
                                                <span class="status-dot"></span>
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $tela["ultima_verificacao"] ? formatDate($tela["ultima_verificacao"]) : 'Nunca' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-auto pt-3">
                        <?php if ($user["tipo"] === "administrador"): ?>
                            <a href="telas.php" class="btn modern-btn modern-btn-primary me-2">
                                <i class="bi bi-display me-2"></i>
                                Gerenciar Telas
                            </a>
                        <?php endif; ?>
                        <a href="playlists.php" class="btn modern-btn modern-btn-success">
                            <i class="bi bi-list-ul me-2"></i>
                            Gerenciar Playlists
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <div class="col">
                        <a href="midias.php" class="modern-quick-action-btn upload">
                            <i class="bi bi-cloud-upload"></i>
                            <span>Adicionar Mídia</span>
                        </a>
                    </div>
                    
                    <div class="col">
                        <a href="playlists.php" class="modern-quick-action-btn playlist">
                            <i class="bi bi-plus-circle"></i>
                            <span>Nova Playlist</span>
                        </a>
                    </div>
                    
                    <?php if ($user["tipo"] === "administrador"): ?>
                        <div class="col">
                            <a href="telas.php" class="modern-quick-action-btn screen">
                                <i class="bi bi-display"></i>
                                <span>Nova Tela</span>
                            </a>
                        </div>
                        
                        <div class="col">
                            <a href="usuarios.php" class="modern-quick-action-btn user">
                                <i class="bi bi-person-plus"></i>
                                <span>Novo Usuário</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// **NOVA FUNÇÃO**
async function refreshDashboard() {
    const btn = document.querySelector('button[onclick="refreshDashboard()"]');
    
    // Adiciona um indicador de carregamento ao botão
    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
    btn.disabled = true;

    try {
        // Envia um sinal para o servidor criar o "flag" de atualização.
        // O método POST é usado para evitar cache.
        await fetch('api/force_check.php', { method: 'POST' });
        
        // Espera 2 segundos para dar tempo das telas enviarem seus heartbeats
        setTimeout(() => {
            location.reload();
        }, 2000);

    } catch (error) {
        console.error("Erro ao forçar a atualização:", error);
        // Recarrega a página de qualquer maneira, mesmo se o sinal falhar
        location.reload();
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>
