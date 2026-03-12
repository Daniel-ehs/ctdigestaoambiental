<?php

require_once __DIR__ . '/../controllers/MidiaController.php';

$midiaController = new MidiaController();

// Busca pastas para o select
$pastasResult = $midiaController->getPastasFlatList();
$pastas = $pastasResult['pastas'] ?? [];

$pageTitle = 'Adicionar Mídia';

$additionalJS = ['https://www.youtube.com/iframe_api'];

ob_start();
?>

<style>
/* Estilos modernos para a página de adicionar mídia */
.card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.5rem;
    color: white;
    border-radius: 16px 16px 0 0;
}

.card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.25rem;
}

.nav-tabs {
    border: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 0.5rem;
    margin-bottom: 2rem;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 8px;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    margin: 0 0.25rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-tabs .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.nav-tabs .nav-link:hover::before {
    left: 100%;
}

.nav-tabs .nav-link:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: #f8f9ff;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    color: white;
}

.form-text {
    color: #718096;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: white;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: #f8f9ff;
}

.file-input-area {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8f9fa;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.file-input-area:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.file-input-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-input-content {
    pointer-events: none;
}

.file-input-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.tab-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form sections */
.form-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.form-section-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title i {
    color: #667eea;
}

/* Success/Error states */
.form-success {
    border-color: #38a169 !important;
    background: #f0fff4 !important;
}

.form-error {
    border-color: #e53e3e !important;
    background: #fff5f5 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header {
        padding: 1rem;
    }
    
    .tab-content {
        padding: 1rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
}

/* Estilos específicos para o tipo Site */
.site-preview {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    background: #f8f9fa;
    margin-top: 1rem;
    display: none;
}

.site-preview.show {
    display: block;
    animation: fadeIn 0.3s ease-in;
}

.site-preview iframe {
    width: 100%;
    height: 200px;
    border: none;
    border-radius: 4px;
}

.url-validation {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.url-validation.valid {
    background: #f0fff4;
    color: #38a169;
    border: 1px solid #38a169;
}

.url-validation.invalid {
    background: #fff5f5;
    color: #e53e3e;
    border: 1px solid #e53e3e;
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
</style>

<div class="row fade-in">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Adicionar Nova Mídia
                </h5>
            </div>
            <div class="card-body">
                <!-- Tabs para diferentes tipos de mídia -->
                <ul class="nav nav-tabs" id="tipoMidiaTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="video-tab" data-bs-toggle="tab" data-bs-target="#video" type="button" role="tab" aria-controls="video" aria-selected="true">
                            <i class="bi bi-play-circle me-2"></i>
                            Vídeo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="imagem-tab" data-bs-toggle="tab" data-bs-target="#imagem" type="button" role="tab" aria-controls="imagem" aria-selected="false">
                            <i class="bi bi-image me-2"></i>
                            Imagem
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="youtube-tab" data-bs-toggle="tab" data-bs-target="#youtube" type="button" role="tab" aria-controls="youtube" aria-selected="false">
                            <i class="bi bi-youtube me-2"></i>
                            YouTube
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link" type="button" role="tab" aria-controls="link" aria-selected="false">
                            <i class="bi bi-link me-2"></i>
                            Link de Imagem
                        </button>
                    </li>
                    <!-- NOVA TAB PARA SITES -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab" aria-controls="site" aria-selected="false">
                            <i class="bi bi-globe me-2"></i>
                            Site/Página
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="tipoMidiaTabContent">
                    <!-- Tab Vídeo -->
                    <div class="tab-pane fade show active" id="video" role="tabpanel" aria-labelledby="video-tab">
                        <form id="formVideo" enctype="multipart/form-data">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-upload"></i>
                                    Upload de Vídeo
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="videoNome" class="form-label">Nome do Vídeo</label>
                                            <input type="text" class="form-control" id="videoNome" name="nome" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="videoFile" class="form-label">Arquivo de Vídeo</label>
                                            <div class="file-input-area">
                                                <input type="file" class="form-control" id="videoFile" name="video" accept="video/*" required>
                                                <div class="file-input-content">
                                                    <div class="file-input-icon">
                                                        <i class="bi bi-cloud-upload"></i>
                                                    </div>
                                                    <div class="fw-bold">Clique para selecionar o arquivo</div>
                                                    <div class="form-text">ou arraste e solte aqui</div>
                                                </div>
                                            </div>
                                            <div class="form-text mt-2">Formatos aceitos: MP4, AVI, MOV, WMV (máx. 150MB)</div>
                                            <div class="file-preview mt-2"></div>
                                            <div class="progress mt-2" style="height: 25px; display: none;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="videoDuracao" class="form-label">Duração (segundos)</label>
                                            <input type="number" class="form-control" id="videoDuracao" name="duracao" value="0" min="0" max="7200" readonly>
                                            <div class="form-text">A duração será preenchida automaticamente.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="videoPasta" class="form-label">Pasta</label>
                                            <select class="form-select" id="videoPasta" name="pasta_id">
                                                <option value="">Raiz</option>
                                                <?php foreach ($pastas as $pasta): ?>
                                                    <option value="<?= $pasta['id'] ?>"><?= str_repeat('&nbsp;&nbsp;', $pasta['nivel'] ?? 0) . sanitize($pasta['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="midias.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>
                                    Fazer Upload
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Imagem -->
                    <div class="tab-pane fade" id="imagem" role="tabpanel" aria-labelledby="imagem-tab">
                        <form id="formImagem" enctype="multipart/form-data">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-image"></i>
                                    Upload de Imagem
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="imagemNome" class="form-label">Nome da Imagem</label>
                                            <input type="text" class="form-control" id="imagemNome" name="nome" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="imagemFile" class="form-label">Arquivo de Imagem</label>
                                            <div class="file-input-area">
                                                <input type="file" class="form-control" id="imagemFile" name="imagem" accept="image/*" required>
                                                <div class="file-input-content">
                                                    <div class="file-input-icon">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                    <div class="fw-bold">Clique para selecionar a imagem</div>
                                                    <div class="form-text">ou arraste e solte aqui</div>
                                                </div>
                                            </div>
                                            <div class="form-text mt-2">Formatos aceitos: JPG, PNG, GIF (máx. 150MB)</div>
                                            <div class="file-preview mt-2"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="imagemDuracao" class="form-label">Duração (segundos)</label>
                                            <input type="number" class="form-control" id="imagemDuracao" name="duracao" value="10" min="1" max="300" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="imagemPasta" class="form-label">Pasta</label>
                                            <select class="form-select" id="imagemPasta" name="pasta_id">
                                                <option value="">Raiz</option>
                                                <?php foreach ($pastas as $pasta): ?>
                                                    <option value="<?= $pasta['id'] ?>"><?= str_repeat('&nbsp;&nbsp;', $pasta['nivel'] ?? 0) . sanitize($pasta['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="midias.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>
                                    Fazer Upload
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab YouTube -->
                    <div class="tab-pane fade" id="youtube" role="tabpanel" aria-labelledby="youtube-tab">
                        <form id="formYoutube" method="POST" action="controllers/MidiaController.php">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-youtube"></i>
                                    Adicionar Vídeo do YouTube
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="youtubeUrl" class="form-label">URL do YouTube</label>
                                            <input type="url" class="form-control" id="youtubeUrl" name="url" placeholder="https://www.youtube.com/watch?v=..." required>
                                            <div id="player" style="display: none;"></div> <!-- Player oculto para obter duração -->
                                        </div>
                                        <div class="mb-3">
                                            <label for="youtubeNome" class="form-label">Nome da Mídia</label>
                                            <input type="text" class="form-control" id="youtubeNome" name="nome" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="youtubeDuracao" class="form-label">Duração (segundos)</label>
                                            <input type="number" class="form-control" id="youtubeDuracao" name="duracao" value="0" min="0" max="7200" readonly>
                                            <div class="form-text">A duração será preenchida automaticamente.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="youtubePasta" class="form-label">Pasta</label>
                                            <select class="form-select" id="youtubePasta" name="pasta_id">
                                                <option value="">Raiz</option>
                                                <?php foreach ($pastas as $pasta): ?>
                                                    <option value="<?= $pasta['id'] ?>"><?= str_repeat('&nbsp;&nbsp;', $pasta['nivel'] ?? 0) . sanitize($pasta['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="midias.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Adicionar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Link de Imagem -->
                    <div class="tab-pane fade" id="link" role="tabpanel" aria-labelledby="link-tab">
                        <form id="formLinkImagem">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-link-45deg"></i>
                                    Adicionar por Link de Imagem
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="linkUrl" class="form-label">URL da Imagem</label>
                                            <input type="url" class="form-control" id="linkUrl" name="url" placeholder="https://exemplo.com/imagem.jpg" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="linkNome" class="form-label">Nome da Mídia</label>
                                            <input type="text" class="form-control" id="linkNome" name="nome" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="linkDuracao" class="form-label">Duração (segundos)</label>
                                            <input type="number" class="form-control" id="linkDuracao" name="duracao" value="10" min="1" max="300" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="linkPasta" class="form-label">Pasta</label>
                                            <select class="form-select" id="linkPasta" name="pasta_id">
                                                <option value="">Raiz</option>
                                                <?php foreach ($pastas as $pasta): ?>
                                                    <option value="<?= $pasta['id'] ?>"><?= str_repeat('&nbsp;&nbsp;', $pasta['nivel'] ?? 0) . sanitize($pasta['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="midias.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Adicionar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Site/Página -->
                    <div class="tab-pane fade" id="site" role="tabpanel" aria-labelledby="site-tab">
                        <form id="formSite">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-globe"></i>
                                    Adicionar Site/Página
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="siteUrl" class="form-label">URL do Site</label>
                                            <input type="url" class="form-control" id="siteUrl" name="url" placeholder="https://exemplo.com" required>
                                            <div id="urlValidation" class="url-validation"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="siteNome" class="form-label">Nome da Mídia</label>
                                            <input type="text" class="form-control" id="siteNome" name="nome" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="siteDuracao" class="form-label">Duração (segundos)</label>
                                            <input type="number" class="form-control" id="siteDuracao" name="duracao" value="30" min="5" max="600" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sitePasta" class="form-label">Pasta</label>
                                            <select class="form-select" id="sitePasta" name="pasta_id">
                                                <option value="">Raiz</option>
                                                <?php foreach ($pastas as $pasta): ?>
                                                    <option value="<?= $pasta['id'] ?>"><?= str_repeat('&nbsp;&nbsp;', $pasta['nivel'] ?? 0) . sanitize($pasta['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="site-preview" id="sitePreview">
                                    <h6>Pré-visualização:</h6>
                                    <iframe id="siteIframe" src="about:blank"></iframe>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="midias.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Adicionar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container">
</div>

<?php
$content = ob_get_clean();

// Inclui o template e passa o conteúdo
require_once __DIR__ . '/../views/template.php';
?>
<script>
$(document).ready(function() {
    // Ativa a primeira aba ao carregar a página
    $('#tipoMidiaTab button:first').tab('show');

    // Lógica para pré-visualização de URL de site
    $('#siteUrl').on('input', function() {
        var url = $(this).val();
        var $validationMessage = $('#urlValidation');
        var $sitePreview = $('#sitePreview');
        var $siteIframe = $('#siteIframe');

        if (isValidUrl(url)) {
            $validationMessage.removeClass('invalid').addClass('valid').text('URL válida.');
            $sitePreview.addClass('show');
            $siteIframe.attr('src', url);
        } else {
            $validationMessage.removeClass('valid').addClass('invalid').text('URL inválida.');
            $sitePreview.removeClass('show');
            $siteIframe.attr('src', 'about:blank');
        }
    });

    // Funções de validação (podem ser movidas para custom.js se forem globais)
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Função para detectar duração de vídeo
    function detectarDuracaoVideo(file, callback) {
        const video = document.createElement('video');
        video.preload = 'metadata';
        
        video.onloadedmetadata = function() {
            window.URL.revokeObjectURL(video.src);
            const duracao = Math.round(video.duration);
            callback(duracao);
        };
        
        video.onerror = function() {
            console.error('Erro ao carregar vídeo para detectar duração');
            callback(0);
        };
        
        video.src = URL.createObjectURL(file);
    }

    // Event listener para arquivo de vídeo
    $('#videoFile').on('change', function() {
        const file = this.files[0];
        if (file) {
            detectarDuracaoVideo(file, function(duracao) {
                $('#videoDuracao').val(duracao);
            });
        }
    });

    // Função para obter duração do YouTube
    function obterDuracaoYouTube(url, callback) {
        const videoId = extrairIdYouTube(url);
        if (!videoId) {
            callback(0);
            return;
        }
        
        // Usar YouTube Data API v3 (necessita chave API)
        // Como alternativa, vamos usar um método simples com iframe
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${videoId}?enablejsapi=1`;
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        
        // Simular duração padrão por enquanto (pode ser melhorado com API key)
        setTimeout(() => {
            document.body.removeChild(iframe);
            callback(0); // Retorna 0 para que o usuário possa ajustar manualmente
        }, 1000);
    }

    function extrairIdYouTube(url) {
        const regex = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/;
        const match = url.match(regex);
        return match ? match[1] : null;
    }

    // Event listener para URL do YouTube
    $('#youtubeUrl').on('blur', function() {
        const url = $(this).val();
        if (url) {
            obterDuracaoYouTube(url, function(duracao) {
                if (duracao > 0) {
                    $('#youtubeDuracao').val(duracao);
                }
            });
        }
    });

    // Funções de submit de formulários
    $("#formVideo").on("submit", function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitButton = $form.find("button[type=\"submit\"]");
        var $progressBar = $form.find(".progress");
        var $progressBarInner = $progressBar.find(".progress-bar");

        // Mostra a barra de progresso e desabilita o botão
        $progressBar.show();
        $submitButton.prop("disabled", true).addClass("loading");

        var formData = new FormData(this);

        $.ajax({
            url: '<?= SITE_URL ?>/public/api/midias/upload-video.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    ComunicaPlay.showAlert('success', response.message);
                    // Reset completo do formulário
                    $('#formVideo')[0].reset();
                    $('.file-preview').empty();
                    $('#videoDuracao').val('0');
                } else {
                    ComunicaPlay.showAlert('error', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                ComunicaPlay.showAlert('error', 'Erro ao fazer upload do vídeo: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : errorThrown));
            },
            complete: function() {
                // Esconde a barra de progresso e habilita o botão
                $progressBar.hide();
                $submitButton.prop("disabled", false).removeClass("loading");
            }
        });
    });

    $('#formImagem').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: '<?= SITE_URL ?>/public/api/midias/upload-imagem.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    ComunicaPlay.showAlert('success', response.message);
                    // Reset completo do formulário
                    $('#formImagem')[0].reset();
                    $('.file-preview').empty();
                } else {
                    ComunicaPlay.showAlert('error', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                ComunicaPlay.showAlert('error', 'Erro ao fazer upload da imagem: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : errorThrown));
            }
        });
    });

    $('#formYouTube').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '<?= SITE_URL ?>/public/api/midias/add-youtube.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    ComunicaPlay.showAlert('success', response.message);
                    // Reset completo do formulário
                    $('#formYouTube')[0].reset();
                    $('#youtubeDuracao').val('0');
                } else {
                    ComunicaPlay.showAlert('error', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                ComunicaPlay.showAlert('error', 'Erro ao adicionar vídeo do YouTube: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : errorThrown));
            }
        });
    });

    $('#formLinkImagem').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '<?= SITE_URL ?>/public/api/midias/add-link-imagem.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    ComunicaPlay.showAlert('success', response.message);
                    // Reset completo do formulário
                    $('#formLinkImagem')[0].reset();
                } else {
                    ComunicaPlay.showAlert('error', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                ComunicaPlay.showAlert('error', 'Erro ao adicionar link de imagem: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : errorThrown));
            }
        });
    });

    $('#formSite').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '<?= SITE_URL ?>/public/api/midias/add-site.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    ComunicaPlay.showAlert('success', response.message);
                    $('#formSite')[0].reset();
                    $('#sitePreview').removeClass('show');
                    $('#siteIframe').attr('src', 'about:blank');
                    $('#urlValidation').text('');
                } else {
                    ComunicaPlay.showAlert('error', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                ComunicaPlay.showAlert('error', 'Erro ao adicionar site/página: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : errorThrown));
            }
        });
    });
});
</script>



<style>
.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 10;
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    border-radius: 12px;
}

.upload-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
</style>




<script>
document.addEventListener("DOMContentLoaded", function() {
    // Variável para o player do YouTube
    let player;

    // Função para extrair o ID do vídeo do YouTube de uma URL
    function getYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    // Função para carregar o vídeo no player oculto
    function loadYouTubeVideo(videoId) {
        if (player) {
            player.loadVideoById(videoId);
        } else {
            // A API do YouTube IFrame Player já está incluída no cabeçalho
            // A função onYouTubeIframeAPIReady será chamada automaticamente
            window.onYouTubeIframeAPIReady = function() {
                player = new YT.Player("youtubePlayer", {
                    height: "0",
                    width: "0",
                    videoId: videoId,
                    events: {
                        "onReady": onPlayerReady,
                        "onStateChange": onPlayerStateChange
                    }
                });
            }
        }
    }

    // Evento chamado quando o player está pronto
    function onPlayerReady(event) {
        // O player está pronto, mas não fazemos nada aqui ainda
    }

    // Evento chamado quando o estado do player muda
    function onPlayerStateChange(event) {
        // Quando o vídeo começa a tocar (ou está em buffer), obtemos a duração
        if (event.data === YT.PlayerState.PLAYING || event.data === YT.PlayerState.BUFFERING) {
            const duration = player.getDuration();
            if (duration > 0) {
                document.getElementById("youtubeDuracao").value = Math.round(duration);
                // Pausamos o vídeo para não consumir recursos
                player.pauseVideo();
            }
        }
    }

    // Adiciona o listener ao campo de URL do YouTube
    const youtubeUrlInput = document.getElementById("youtubeUrl");
    if (youtubeUrlInput) {
        youtubeUrlInput.addEventListener("paste", function(e) {
            // Aguarda um pequeno instante para o valor ser colado no campo
            setTimeout(() => {
                const videoId = getYouTubeId(e.target.value);
                if (videoId) {
                    loadYouTubeVideo(videoId);
                }
            }, 100);
        });

        youtubeUrlInput.addEventListener("keyup", function(e) {
            const videoId = getYouTubeId(e.target.value);
            if (videoId) {
                loadYouTubeVideo(videoId);
            }
        });
    }

    // Lida com a submissão do formulário do YouTube via AJAX
    const formYoutube = document.getElementById("formYoutube");
    if (formYoutube) {
        formYoutube.addEventListener("submit", function(e) {
            e.preventDefault();

            const btn = document.getElementById("btnAdicionarYoutube");
            const btnOriginalText = btn.innerHTML;

            // Desativa o botão e mostra o spinner
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adicionando...`;

            const formData = new FormData(formYoutube);
            formData.append("action", "addYouTube");

            fetch("/public/api/midias/add-youtube.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostra mensagem de sucesso
                    showToast("success", data.message);
                    // Limpa o formulário
                    formYoutube.reset();
                    // Redireciona para a página de mídias após um pequeno atraso
                    setTimeout(() => {
                        window.location.href = "midias.php";
                    }, 1500);
                } else {
                    // Mostra mensagem de erro
                    showToast("error", data.message || "Ocorreu um erro.");
                }
            })
            .catch(error => {
                console.error("Erro na requisição:", error);
                showToast("error", "Erro de comunicação com o servidor.");
            })
            .finally(() => {
                // Reativa o botão e restaura o texto original
                btn.disabled = false;
                btn.innerHTML = btnOriginalText;
            });
        });
    }

    // Função para exibir toasts (notificações)
    function showToast(type, message) {
        const toastContainer = document.querySelector(".toast-container");
        if (!toastContainer) return;

        const toast = document.createElement("div");
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.role = "alert";
        toast.ariaLive = "assertive";
        toast.ariaAtomic = "true";

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove o toast do DOM após ser ocultado
        toast.addEventListener("hidden.bs.toast", function() {
            toast.remove();
        });
    }
});
</script>

