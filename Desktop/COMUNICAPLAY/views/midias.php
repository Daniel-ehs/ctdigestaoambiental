<?php

require_once __DIR__ . '/../controllers/MidiaController.php';
require_once __DIR__ . '/../includes/functions.php';

$midiaController = new MidiaController();

// Parâmetros de filtro e paginação
$pastaId = $_GET['pasta'] ?? null;
$page = (int)($_GET['page'] ?? 1);
$limit = 12;

// Busca mídias
$result = $midiaController->getAll($pastaId, $page, $limit);
$midias = $result['midias'] ?? [];

// Busca pastas
$pastasResult = $midiaController->getPastas($pastaId);
$pastas = $pastasResult['pastas'] ?? [];

$pageTitle = 'Gerenciamento de Mídias';

$pageActions = '
    <div class="d-flex gap-3">
        <a href="midia_adicionar.php" class="modern-btn modern-btn-primary text-decoration-none">
            <i class="bi bi-plus-circle me-2"></i>
            Adicionar Mídia
        </a>
        <button type="button" class="modern-btn modern-btn-outline" data-bs-toggle="modal" data-bs-target="#modalNovaPasta">
            <i class="bi bi-folder-plus me-2"></i>
            Nova Pasta
        </button>
    </div>
';

ob_start();
?>

<style>
/* Estilos modernos para a página de mídias */
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
    color: white;
}

.modern-btn-outline {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.modern-btn-outline:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
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

/* Estilos específicos para mídias */
.media-thumbnail-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    border-radius: 12px 12px 0 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.media-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.media-card:hover .media-thumbnail {
    transform: scale(1.05);
}

/* Thumbnails pequenos para visualização em lista */
.media-thumbnail-small {
    width: 60px;
    height: 45px;
    border-radius: 8px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.media-thumbnail-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-icon {
    font-size: 1.5rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.media-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    font-size: 3rem;
    transition: all 0.3s ease;
}

.media-placeholder.youtube {
    background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
    color: white;
}

.media-placeholder.site {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.media-type-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.media-type-badge.youtube {
    background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
}

.media-type-badge.site {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.media-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.media-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

.media-card .card-body {
    padding: 1.25rem;
    background: white;
}

.media-card .card-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.media-meta {
    color: #718096;
    font-size: 0.875rem;
    line-height: 1.4;
}

.media-card .card-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 1rem 1.25rem;
}

.folder-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.folder-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

.folder-card .card-body {
    padding: 2rem 1.5rem;
    text-align: center;
    background: white;
}

.folder-card .bi-folder-fill {
    font-size: 3.5rem;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.folder-card .card-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.folder-actions {
    opacity: 0;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.folder-card:hover .folder-actions {
    opacity: 1;
}

.media-thumbnail-small {
    width: 50px;
    height: 50px;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.media-thumbnail-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    font-size: 1.5rem;
    border-radius: 8px;
}

/* Botões de ação modernos */
.action-btn {
    border: none !important;
    color: white !important;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border-radius: 8px !important;
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

.btn-view {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
}

.btn-view:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%) !important;
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4) !important;
}

.btn-edit {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%) !important;
}

.btn-edit:hover {
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

/* Botões de alternância de visualização */
.btn-group .btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
}

.btn-group .btn-outline-primary.active,
.btn-group .btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

/* Modal moderno */
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

/* Badges de tipo de mídia */
.type-badge {
    border-radius: 20px;
    padding: 0.4rem 0.8rem;
    font-weight: 500;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.type-badge.video {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.type-badge.imagem {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.type-badge.youtube {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.type-badge.link {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
    color: white;
}

.type-badge.site {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

/* Seção de título moderna */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem 0;
}

.section-title {
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    margin-left: 0.5rem;
}

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(10px);
    margin-bottom: 10px;
}

.toast.success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.toast.error {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.toast.info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Estilos específicos para iframe de sites */
.site-iframe {
    width: 100%;
    height: 400px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.site-preview-container {
    position: relative;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.site-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}
</style>

<div class="fade-in">
    <!-- Breadcrumb -->
    <?php if ($pastaId): ?>
        <?php 
        $breadcrumbResult = $midiaController->getBreadcrumb($pastaId);
        $breadcrumb = $breadcrumbResult['breadcrumb'] ?? [];
        ?>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="midias.php" class="text-decoration-none">
                        <i class="bi bi-house-door"></i> Início
                    </a>
                </li>
                <?php foreach ($breadcrumb as $item): ?>
                    <?php if ($item['id'] == $pastaId): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-folder"></i> <?= sanitize($item['nome']) ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="midias.php?pasta=<?= $item['id'] ?>" class="text-decoration-none">
                                <i class="bi bi-folder"></i> <?= sanitize($item['nome']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>

    <!-- Pastas -->
    <?php if (!empty($pastas)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="bi bi-folder"></i>
                        Pastas
                        <span class="section-badge"><?= count($pastas) ?></span>
                    </h5>
                </div>
                <div class="row">
                    <?php foreach ($pastas as $pasta): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="card folder-card h-100" onclick="window.location.href='midias.php?pasta=<?= $pasta['id'] ?>'">
                                <div class="card-body">
                                    <i class="bi bi-folder-fill"></i>
                                    <h6 class="card-title"><?= sanitize($pasta['nome']) ?></h6>
                                    <small class="text-muted">
                                        <?= $pasta['total_midias'] ?> mídia(s)
                                        <?php if ($pasta['total_subpastas'] > 0): ?>
                                            • <?= $pasta['total_subpastas'] ?> pasta(s)
                                        <?php endif; ?>
                                    </small>
                                    
                                    <div class="folder-actions">
                                        <button class="modern-btn modern-btn-outline btn-sm" onclick="event.stopPropagation(); editarPasta(<?= $pasta['id'] ?>, '<?= addslashes($pasta['nome']) ?>', '<?= addslashes($pasta['descricao'] ?? '') ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="modern-btn btn-delete btn-sm action-btn" onclick="event.stopPropagation(); excluirPasta(<?= $pasta['id'] ?>, '<?= addslashes($pasta['nome']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mídias -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-collection-play"></i>
                    Mídias
                    <span class="section-badge"><?= count($midias) ?></span>
                </h5>
                <div class="btn-group" role="group" aria-label="Opções de Visualização">
                    <button type="button" class="btn btn-outline-primary" id="btn-view-grid" title="Visualização em Grade">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btn-view-list" title="Visualização em Lista">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>

            <?php if (empty($midias)): ?>
                <div class="modern-empty-state">
                    <i class="bi bi-collection-play" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                    <h4>Nenhuma mídia encontrada</h4>
                    <p class="text-muted">Adicione sua primeira mídia para começar.</p>
                    <a href="midia_adicionar.php" class="modern-btn modern-btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Adicionar Mídia
                    </a>
                </div>
            <?php else: ?>
                <!-- Visualização em Grade -->
                <div id="grid-view" class="row">
                    <?php foreach ($midias as $midia): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card media-card h-100">
                                <div class="media-thumbnail-container">
                                    <?php if ($midia['tipo'] === 'youtube'): ?>
                                        <div class="media-placeholder youtube">
                                            <i class="bi bi-youtube"></i>
                                        </div>
                                        <div class="media-type-badge youtube">YouTube</div>
                                    <?php elseif ($midia['tipo'] === 'imagem'): ?>
                                        <?php if (!empty($midia['url_externa'])): ?>
                                            <img src="<?= sanitize($midia['url_externa']) ?>" alt="<?= sanitize($midia['nome']) ?>" class="media-thumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="media-placeholder" style="display: none;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="media-placeholder">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="media-type-badge">Imagem</div>
                                    <?php elseif ($midia['tipo'] === 'video'): ?>
                                        <div class="media-placeholder">
                                            <i class="bi bi-play-circle"></i>
                                        </div>
                                        <div class="media-type-badge">Vídeo</div>
                                    <?php elseif ($midia['tipo'] === 'site'): ?>
                                        <div class="media-placeholder site">
                                            <i class="bi bi-globe"></i>
                                        </div>
                                        <div class="media-type-badge site">Site</div>
                                    <?php else: ?>
                                        <div class="media-placeholder">
                                            <i class="bi bi-file-earmark"></i>
                                        </div>
                                        <div class="media-type-badge">Arquivo</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h6 class="card-title"><?= sanitize($midia['nome']) ?></h6>
                                    <div class="media-meta">
                                        <small class="text-muted">
                                            <span class="type-badge <?= strtolower($midia['tipo']) ?>">
                                                <?= ucfirst($midia['tipo']) ?>
                                            </span>
                                        </small>
                                        <?php if (!empty($midia['duracao'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-clock"></i> <?= formatDuration($midia['duracao']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <br><small class="text-muted">
                                            <i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($midia['data_criacao'])) ?>
                                        </small>
                                        <?php if ($midia['tipo'] === 'site' && !empty($midia['url_externa'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-link"></i> <?= parse_url($midia['url_externa'], PHP_URL_HOST) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <div class="modern-btn-group d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-view action-btn" onclick="visualizarMidia(<?= $midia['id'] ?>)" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-edit action-btn" onclick="editarMidia(<?= $midia['id'] ?>)" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-delete action-btn" onclick="excluirMidia(<?= $midia['id'] ?>, '<?= addslashes($midia['nome']) ?>')" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Visualização em Lista -->
                <div id="list-view" style="display: none;">
                    <div class="modern-card">
                        <div class="table-responsive">
                            <table class="table modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mídia</th>
                                        <th>Tipo</th>
                                        <th>Duração</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($midias as $midia): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="media-thumbnail-small me-3">
                                                        <?php if ($midia['tipo'] === 'youtube'): ?>
                                                            <div class="media-icon" style="background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%); color: white;">
                                                                <i class="bi bi-youtube"></i>
                                                            </div>
                                                        <?php elseif ($midia['tipo'] === 'imagem' && !empty($midia['url_externa'])): ?>
                                                            <img src="<?= sanitize($midia['url_externa']) ?>" alt="<?= sanitize($midia['nome']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <div class="media-icon" style="display: none;">
                                                                <i class="bi bi-image"></i>
                                                            </div>
                                                        <?php elseif ($midia['tipo'] === 'video'): ?>
                                                            <div class="media-icon">
                                                                <i class="bi bi-play-circle"></i>
                                                            </div>
                                                        <?php elseif ($midia['tipo'] === 'site'): ?>
                                                            <div class="media-icon" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white;">
                                                                <i class="bi bi-globe"></i>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="media-icon">
                                                                <i class="bi bi-file-earmark"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1"><?= sanitize($midia['nome']) ?></h6>
                                                        <small class="text-muted">ID: <?= $midia['id'] ?></small>
                                                        <?php if ($midia['tipo'] === 'site' && !empty($midia['url_externa'])): ?>
                                                            <br><small class="text-muted"><?= parse_url($midia['url_externa'], PHP_URL_HOST) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="type-badge <?= strtolower($midia['tipo']) ?>">
                                                    <?= ucfirst($midia['tipo']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($midia['duracao'])): ?>
                                                    <i class="bi bi-clock me-1"></i><?= formatDuration($midia['duracao']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($midia['data_criacao'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="modern-btn-group d-flex gap-2">
                                                    <button class="btn btn-sm btn-view action-btn" onclick="visualizarMidia(<?= $midia['id'] ?>)" title="Visualizar">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-edit action-btn" onclick="editarMidia(<?= $midia['id'] ?>)" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-delete action-btn" onclick="excluirMidia(<?= $midia['id'] ?>, '<?= addslashes($midia['nome']) ?>')" title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para visualizar mídia -->
<div class="modal fade modern-modal" id="modalVisualizarMidia" tabindex="-1" aria-labelledby="modalVisualizarMidiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarMidiaLabel">
                    <i class="bi bi-eye me-2"></i>
                    Visualizar Mídia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="conteudoMidia" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar exclusão de mídia -->
<div class="modal fade modern-modal" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarExclusaoLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a mídia <strong id="nomeMidiaExcluir"></strong>?</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarExclusao">
                    <i class="bi bi-trash me-2"></i>
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nova pasta -->
<div class="modal fade modern-modal" id="modalNovaPasta" tabindex="-1" aria-labelledby="modalNovaPastaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaPastaLabel">
                    <i class="bi bi-folder-plus me-2"></i>
                    Nova Pasta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNovaPasta">
                <div class="modal-body">
                    <input type="hidden" name="pasta_pai_id" value="<?= $pastaId ?>">
                    <div class="mb-3">
                        <label for="nomeNovaPasta" class="form-label">Nome da Pasta</label>
                        <input type="text" class="form-control" id="nomeNovaPasta" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricaoNovaPasta" class="form-label">Descrição (opcional)</label>
                        <textarea class="form-control" id="descricaoNovaPasta" name="descricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-folder-plus me-2"></i>
                        Criar Pasta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar pasta -->
<div class="modal fade modern-modal" id="modalEditarPasta" tabindex="-1" aria-labelledby="modalEditarPastaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPastaLabel">
                    <i class="bi bi-pencil me-2"></i>
                    Editar Pasta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarPasta">
                <div class="modal-body">
                    <input type="hidden" id="editarPastaId" name="id">
                    <div class="mb-3">
                        <label for="nomeEditarPasta" class="form-label">Nome da Pasta</label>
                        <input type="text" class="form-control" id="nomeEditarPasta" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricaoEditarPasta" class="form-label">Descrição (opcional)</label>
                        <textarea class="form-control" id="descricaoEditarPasta" name="descricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar exclusão de pasta -->
<div class="modal fade modern-modal" id="modalConfirmarExclusaoPasta" tabindex="-1" aria-labelledby="modalConfirmarExclusaoPastaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarExclusaoPastaLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a pasta <strong id="nomePastaExcluir"></strong>?</p>
                <p class="text-muted">Esta ação irá excluir a pasta e todo o seu conteúdo. Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarExclusaoPasta">
                    <i class="bi bi-trash me-2"></i>
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container para notificações toast -->
<div class="toast-container" id="toastContainer"></div>

<script>
// Variáveis globais
let midiaIdParaExcluir = null;
let pastaIdParaExcluir = null;

// Função para mostrar notificações toast
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    
    const toastHtml = `
        <div class="toast ${type}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    // Remove o elemento do DOM após ser ocultado
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// FUNÇÃO CORRIGIDA PARA VISUALIZAR MÍDIA - COM SUPORTE A SITES
function visualizarMidia(id) {
    // Validação robusta do ID
    if (!id || id === 'undefined' || id === undefined || id === null || id === '') {
        console.error('ID da mídia inválido:', id);
        showToast('Erro: ID da mídia inválido', 'error');
        return;
    }
    
    console.log('Visualizando mídia ID:', id);
    
    const modal = new bootstrap.Modal(document.getElementById('modalVisualizarMidia'));
    const conteudoMidia = document.getElementById('conteudoMidia');
    
    // Mostra loading
    conteudoMidia.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <span class="ms-3">Carregando mídia...</span>
        </div>
    `;
    
    modal.show();
    
    // Busca dados da mídia
    fetch(`/public/api/midias/get-midia.php?id=${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.midia) {
                const midia = data.midia;
                let conteudo = '';
                
                if (midia.tipo === 'youtube' && midia.url_externa) {
                    // Extrai ID do YouTube da URL
                    const youtubeId = midia.url_externa.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/);
                    if (youtubeId) {
                        conteudo = `
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/${youtubeId[1]}" 
                                        title="${midia.nome}" 
                                        allowfullscreen>
                                </iframe>
                            </div>
                        `;
                    } else {
                        conteudo = `
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                URL do YouTube inválida
                            </div>
                        `;
                    }
                } else if (midia.tipo === 'imagem' && midia.url_externa) {
                    conteudo = `
                        <img src="${midia.url_externa}" 
                             alt="${midia.nome}" 
                             class="img-fluid rounded"
                             style="max-height: 500px;">
                    `;
                } else if (midia.tipo === 'video' && midia.arquivo_local) {
                    conteudo = `
                        <video controls class="w-100" style="max-height: 500px;">
                            <source src="${midia.arquivo_local}" type="video/mp4">
                            Seu navegador não suporta o elemento de vídeo.
                        </video>
                    `;
                } else if (midia.tipo === 'site' && midia.url_externa) {
                    // NOVA FUNCIONALIDADE: Visualização de sites em iframe
                    conteudo = `
                        <div class="site-preview-container">
                            <div class="site-loading" id="siteLoading">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Carregando site...
                            </div>
                            <iframe src="${midia.url_externa}" 
                                    title="${midia.nome}" 
                                    class="site-iframe"
                                    onload="document.getElementById('siteLoading').style.display='none';"
                                    onerror="document.getElementById('siteLoading').innerHTML='<i class=\\"bi bi-exclamation-triangle me-2\\"></i>Erro ao carregar site';">
                            </iframe>
                        </div>
                        <div class="mt-3">
                            <h6><i class="bi bi-globe me-2"></i>${midia.nome}</h6>
                            <p class="text-muted mb-2">
                                <i class="bi bi-link me-1"></i>
                                <a href="${midia.url_externa}" target="_blank" class="text-decoration-none">
                                    ${midia.url_externa}
                                </a>
                            </p>
                            ${midia.descricao ? `<p class="text-muted">${midia.descricao}</p>` : ''}
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                Duração: ${midia.duracao} segundos
                            </small>
                        </div>
                    `;
                } else {
                    conteudo = `
                        <div class="text-center p-4">
                            <i class="bi bi-file-earmark display-1 text-muted"></i>
                            <h5 class="mt-3">${midia.nome}</h5>
                            <p class="text-muted">Tipo: ${midia.tipo}</p>
                            ${midia.url_externa ? `<a href="${midia.url_externa}" target="_blank" class="btn btn-primary">Abrir Link</a>` : ''}
                        </div>
                    `;
                }
                
                conteudoMidia.innerHTML = conteudo;
            } else {
                conteudoMidia.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        ${data.message || 'Erro ao carregar mídia'}
                    </div>
                `;
                showToast('Erro ao carregar mídia: ' + (data.message || 'Erro desconhecido'), 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            conteudoMidia.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Erro ao carregar mídia
                </div>
            `;
            showToast('Erro ao carregar mídia', 'error');
        });
}

// Função para editar mídia - USANDO A MESMA LÓGICA DO PLAYLIST
function editarMidia(id) {
    // Validação robusta do ID
    if (!id || id === 'undefined' || id === undefined || id === null || id === '') {
        console.error('ID da mídia inválido:', id);
        showToast('Erro: ID da mídia inválido', 'error');
        return;
    }
    
    console.log('Editando mídia ID:', id);
    window.location.href = 'midia_editar.php?id=' + id;
}

// Função para excluir mídia
function excluirMidia(id, nome) {
    // Validação robusta do ID
    if (!id || id === 'undefined' || id === undefined || id === null || id === '') {
        console.error('ID da mídia inválido:', id);
        showToast('Erro: ID da mídia inválido', 'error');
        return;
    }
    
    console.log('Preparando exclusão da mídia ID:', id, 'Nome:', nome);
    
    midiaIdParaExcluir = id;
    document.getElementById('nomeMidiaExcluir').textContent = nome || 'Mídia sem nome';
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    modal.show();
}

// Confirmar exclusão de mídia - CORRIGIDO
document.getElementById('confirmarExclusao').addEventListener('click', function() {
    // Validação robusta antes de excluir
    if (!midiaIdParaExcluir || midiaIdParaExcluir === 'undefined' || midiaIdParaExcluir === undefined || midiaIdParaExcluir === null) {
        console.error('ID da mídia para exclusão inválido:', midiaIdParaExcluir);
        showToast('Erro: ID da mídia inválido', 'error');
        return;
    }
    
    console.log('Excluindo mídia ID:', midiaIdParaExcluir);
    
    // Desabilita o botão e mostra loading
    const btnConfirmar = this;
    const textoOriginal = btnConfirmar.innerHTML;
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Excluindo...';
    
    // Usar FormData para máxima compatibilidade
    const formData = new FormData();
    formData.append('id', midiaIdParaExcluir);
    
    fetch('/public/api/midias/delete-midia.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        // Restaura o botão
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = textoOriginal;
        
        if (data.success) {
            showToast('Mídia deletada com sucesso!', 'success');
            
            // Fecha o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao'));
            modal.hide();
            
            // Recarrega a página após um pequeno delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Erro ao excluir mídia: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        
        // Restaura o botão
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = textoOriginal;
        
        showToast('Erro ao excluir mídia', 'error');
    });
});

// Função para criar nova pasta
document.getElementById('formNovaPasta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('../public/api/pastas/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Pasta criada com sucesso!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Erro ao criar pasta: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao criar pasta', 'error');
    });
});

// Função para editar pasta
function editarPasta(id, nome, descricao) {
    document.getElementById('editarPastaId').value = id;
    document.getElementById('nomeEditarPasta').value = nome;
    document.getElementById('descricaoEditarPasta').value = descricao || '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalEditarPasta'));
    modal.show();
}

// Submissão do formulário de editar pasta
document.getElementById('formEditarPasta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const pastaId = formData.get('id');
    
    fetch(`../public/api/pastas/update.php?id=${pastaId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Pasta atualizada com sucesso!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Erro ao atualizar pasta: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao atualizar pasta', 'error');
    });
});

// Função para excluir pasta
function excluirPasta(id, nome) {
    pastaIdParaExcluir = id;
    document.getElementById('nomePastaExcluir').textContent = nome;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusaoPasta'));
    modal.show();
}

// Confirmar exclusão de pasta
document.getElementById('confirmarExclusaoPasta').addEventListener('click', function() {
    if (pastaIdParaExcluir) {
        const btnConfirmar = this;
        const textoOriginal = btnConfirmar.innerHTML;
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Excluindo...';
        
        fetch(`../public/api/pastas/delete.php?id=${pastaIdParaExcluir}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = textoOriginal;
            
            if (data.success) {
                showToast('Pasta deletada com sucesso!', 'success');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusaoPasta'));
                modal.hide();
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast('Erro ao excluir pasta: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = textoOriginal;
            showToast('Erro ao excluir pasta', 'error');
        });
    }
});

// Lógica para alternar entre visualização de grade e lista
document.addEventListener('DOMContentLoaded', function() {
    const btnViewGrid = document.getElementById('btn-view-grid');
    const btnViewList = document.getElementById('btn-view-list');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    // Função para salvar a preferência do usuário
    function saveViewPreference(view) {
        localStorage.setItem('mediaView', view);
    }

    // Função para carregar a preferência do usuário
    function loadViewPreference() {
        return localStorage.getItem('mediaView') || 'grid'; // Padrão é grade
    }

    // Função para alternar a visualização
    function toggleView(view) {
        if (view === 'grid') {
            gridView.style.display = 'flex'; // Usar flex para manter o layout de colunas
            listView.style.display = 'none';
            btnViewGrid.classList.add('active');
            btnViewList.classList.remove('active');
        } else {
            gridView.style.display = 'none';
            listView.style.display = 'block';
            btnViewGrid.classList.remove('active');
            btnViewList.classList.add('active');
        }
        saveViewPreference(view);
    }

    // Event listeners para os botões
    btnViewGrid.addEventListener('click', function() {
        toggleView('grid');
    });

    btnViewList.addEventListener('click', function() {
        toggleView('list');
    });

    // Carregar a preferência ao carregar a página
    toggleView(loadViewPreference());
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/template.php';
?>
