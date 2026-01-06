<?php
// Incluir cabeçalho e verificar autenticação
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se FOTOS_ANTES_URL está definida
if (!defined('FOTOS_ANTES_URL')) {
    error_log("Erro: FOTOS_ANTES_URL não definida");
    echo '<div class="alert alert-danger">Erro: Constante FOTOS_ANTES_URL não definida.</div>';
    include 'views/templates/footer.php';
    exit;
}

// Verificar se $inspecao está definida
if (!isset($inspecao) || empty($inspecao)) {
    error_log("Erro: \$inspecao não definida ou vazia");
    echo '<div class="alert alert-danger">Erro: Dados da inspeção não encontrados.</div>';
    include 'views/templates/footer.php';
    exit;
}

// --- INÍCIO: LÓGICA ATUALIZADA PARA SUPORTAR MINIO ---
// Função auxiliar para verificar se é URL do S3/MinIO
function isS3Url($url) {
    if (empty($url)) {
        return false;
    }
    $s3Endpoint = getenv('S3_ENDPOINT');
    if (empty($s3Endpoint)) {
        return false;
    }
    $s3Host = parse_url($s3Endpoint, PHP_URL_HOST);
    $urlHost = parse_url($url, PHP_URL_HOST);
    return $s3Host === $urlHost;
}

// Construir caminho para a foto antes
$fotoAntesPath = null;
$temFotoAntes = false;

if (!empty($inspecao['foto_antes'])) {
    if (isS3Url($inspecao['foto_antes'])) {
        // É URL do MinIO, usar diretamente
        $fotoAntesPath = htmlspecialchars($inspecao['foto_antes']);
        $temFotoAntes = true;
        error_log("Foto Antes - URL do MinIO: $fotoAntesPath");
    } else {
        // É arquivo local, construir URL
        if (defined('FOTOS_ANTES_URL') && defined('FOTOS_ANTES_DIR')) {
            $foto_filename = basename($inspecao['foto_antes']);
            $fotoAntesPath = rtrim(FOTOS_ANTES_URL, '/') . '/' . htmlspecialchars($foto_filename);
            $full_path = FOTOS_ANTES_DIR . '/' . htmlspecialchars($foto_filename);
            $temFotoAntes = file_exists($full_path);
            error_log("Foto Antes - URL local: $fotoAntesPath");
            error_log("Foto Antes - Full Path: $full_path");
            error_log("Foto Antes - Exists: " . ($temFotoAntes ? 'Sim' : 'Não'));
        }
    }
}
// --- FIM: LÓGICA ATUALIZADA PARA SUPORTAR MINIO ---

// Log inicial
error_log("foto_antes inicial: " . var_export($inspecao['foto_antes'] ?? 'null', true));
?>

<!-- CSS Estilizado -->
<style>

/* --- CÓDIGO PARA ADICIONAR AO SEU CSS --- */
.textarea-container {
    position: relative;
}

.ai-correct-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.ai-correct-btn:hover {
    background: #52c41a;
    transform: scale(1.1);
    opacity: 1;
}

.ai-correct-btn .fa-spinner {
    display: none;
    animation: spin 1s linear infinite;
}

.ai-correct-btn.loading .fa-magic {
    display: none;
}

.ai-correct-btn.loading .fa-spinner {
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Reset e base */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333333;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* Barra superior */
.top-bar {
    background: #f5f6f5;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Card principal */
.card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(40, 167, 69, 0.2);
    margin: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: fadeIn 0.5s ease-out;
}

.card:hover {
    transform: translateY(-5px);
}

/* Cabeçalho do card */
.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
    text-align: center;
}

/* Corpo do card */
.card-body {
    padding: 2.5rem;
}

/* Seções */
.form-section {
    margin-bottom: 2.5rem;
    background: #f0f4f0;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.form-section h5 {
    color: #28a745;
    font-weight: 700;
    margin-bottom: 1rem;
}

/* Itens de dados */
.data-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    animation: fadeIn 0.6s ease-out forwards;
    opacity: 0;
    padding: 0.5rem;
    border-left: 3px solid transparent;
    transition: border-color 0.3s ease;
}

.data-item:hover {
    border-left-color: #28a745;
}

.data-item i {
    color: #52c41a;
    font-size: 1.3rem;
    margin-right: 1rem;
    margin-top: 0.3rem;
    transition: transform 0.3s ease;
}

.data-item:hover i {
    transform: scale(1.2);
}

.data-item strong {
    color: #333333;
    font-weight: 600;
    min-width: 220px;
    font-size: 1.1rem;
}

.data-item p, .data-item .badge {
    margin: 0;
    color: #555555;
    line-height: 1.7;
    font-size: 1rem;
}

.data-item .badge {
    font-size: 0.95rem;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #ffffff;
}

.data-item .badge:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
}

/* Imagem */
.inspection-image {
    border-radius: 10px;
    max-width: 300px;
    max-height: 200px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.inspection-image:hover {
    transform: scale(1.1);
}

/* Formulário */
.form-label {
    font-weight: 600;
    color: #333333;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

.form-label.required::after {
    content: '*';
    color: #dc3545;
    margin-left: 0.25rem;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Preview de imagem */
.img-preview-container {
    position: relative;
    max-width: 300px;
    margin-top: 1rem;
}

.img-preview-container img {
    border-radius: 10px;
    max-width: 100%;
    max-height: 200px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.img-preview-container img:hover {
    transform: scale(1.1);
}

/* Botões */
.btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.btn {
    position: relative;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 0 15px rgba(40, 167, 69, 0.3);
    border: none;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2), 0 0 20px rgba(40, 167, 69, 0.5);
    filter: brightness(1.2);
}

.btn:active {
    transform: scale(0.95);
}

/* Ripple effect */
.btn::after {
    content: '';
    position: absolute;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: transform 0.6s ease;
}

.btn:active::after {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0;
}

/* Animações */
@keyframes pop {
    0% { transform: scale(0.7); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.btn {
    animation: pop 0.4s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Alerta de erro */
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.5);
}

.lightbox.active {
    display: flex;
}

/* Responsividade */
@media (max-width: 768px) {
    .card {
        margin: 1rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-section {
        padding: 1rem;
    }
    .data-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .data-item strong {
        min-width: auto;
        margin-bottom: 0.5rem;
    }
    .inspection-image, .img-preview-container img {
        max-width: 100%;
    }
}

@media (max-width: 576px) {
    .top-bar {
        padding: 0.5rem 1rem;
    }
    .btn-group {
        gap: 5px;
    }
    .btn {
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Criar Plano de Ação para Inspeção #<?php echo htmlspecialchars($inspecao['numero_inspecao'] ?? $inspecao['id']); ?></h1>
    <div class="btn-group">
        <a href="index.php?route=inspecoes&action=view&id=<?php echo $inspecao['id']; ?>" class="btn btn-secondary" title="Voltar para Inspeção" style="--btn-index: 1;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Detalhes da Inspeção -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Detalhes da Inspeção</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Informações Gerais</h5>
            <div class="data-item" style="animation-delay: 0.1s;">
                <i class="fas fa-calendar-alt"></i>
                <strong>Data do Apontamento:</strong>
                <p><?php echo formatDate($inspecao['data_apontamento'] ?? ''); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.2s;">
                <i class="fas fa-calendar-week"></i>
                <strong>Semana/Ano:</strong>
                <p><?php echo htmlspecialchars($inspecao['semana_ano'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.3s;">
                <i class="fas fa-building"></i>
                <strong>Setor:</strong>
                <p><?php echo htmlspecialchars($inspecao['setor_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.4s;">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Local:</strong>
                <p><?php echo htmlspecialchars($inspecao['local_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.5s;">
                <i class="fas fa-tag"></i>
                <strong>Tipo:</strong>
                <p><span class="badge" style="background-color: <?php echo htmlspecialchars($inspecao['tipo_cor'] ?? '#6c757d'); ?>"><?php echo htmlspecialchars($inspecao['tipo_nome'] ?? 'N/A'); ?></span></p>
            </div>
        </div>
        
        <div class="form-section">
            <h5>Detalhes do Apontamento</h5>
            <div class="data-item" style="animation-delay: 0.6s;">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Apontamento:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['apontamento'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.7s;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Risco/Consequência:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['risco_consequencia'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.8s;">
                <i class="fas fa-user"></i>
                <strong>Responsável:</strong>
                <p><?php echo htmlspecialchars($inspecao['responsavel'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.9s;">
                <i class="fas fa-hourglass-end"></i>
                <strong>Prazo:</strong>
                <p><?php echo formatDate($inspecao['prazo'] ?? ''); ?></p>
            </div>
        </div>

        <div class="form-section">
            <h5>Foto da Situação Encontrada</h5>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 1.0s;">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-white">Foto</h6>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($temFotoAntes && $fotoAntesPath): ?>
                                <img src="<?php echo $fotoAntesPath; ?>" alt="Foto Antes" class="inspection-image" onclick="toggleLightbox('<?php echo $fotoAntesPath; ?>')">
                            <?php else: ?>
                                <span class="badge bg-secondary">Foto não disponível</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Formulário de Plano de Ação</h6>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?route=planos&action=store" enctype="multipart/form-data">
            <input type="hidden" name="inspecao_id" value="<?php echo htmlspecialchars($inspecao['id'] ?? ''); ?>">
            
            <div class="form-section">
                <h5>Dados do Plano de Ação</h5>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                        <label for="foto_depois" class="form-label required">Foto do Depois</label>
                        <input type="file" class="form-control" id="foto_depois" name="foto_depois" accept="image/*" required>
                        <div class="img-preview-container">
                            <img id="preview_foto_depois" src="#" alt="Preview" style="max-height: 200px; display: none;">
                        </div>
                    </div>
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                        <label for="descricao_acao" class="form-label required">Descrição da Ação Tomada</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="descricao_acao" name="descricao_acao" rows="5" required></textarea>
                            <button type="button" class="ai-correct-btn" id="ai-correct-btn" title="Corrigir com IA">
                                <i class="fas fa-magic"></i>
                                <i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary" style="--btn-index: 1;">
                    <i class="fas fa-save"></i> Salvar Plano de Ação
                </button>
                <a href="index.php?route=inspecoes&action=view&id=<?php echo $inspecao['id']; ?>" class="btn btn-secondary" style="--btn-index: 2;">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="toggleLightbox('')">
    <img id="lightbox-img" src="" alt="Imagem ampliada">
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview da foto depois
    const fotoDepoisInput = document.getElementById('foto_depois');
    const previewFotoDepois = document.getElementById('preview_foto_depois');
    
    if (fotoDepoisInput) {
        fotoDepoisInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFotoDepois.src = e.target.result;
                    previewFotoDepois.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Partículas de fundo
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#28a745' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: false },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#28a745', opacity: 0.4, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });
    }

    // Animação dos itens de dados
    const dataItems = document.querySelectorAll('.data-item');
    dataItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });
});

// Função para lightbox
function toggleLightbox(imageSrc) {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    
    if (imageSrc) {
        lightboxImg.src = imageSrc;
        lightbox.classList.add('active');
    } else {
        lightbox.classList.remove('active');
        lightboxImg.src = '';
    }
}
</script>

<?php include 'views/templates/footer.php'; ?>
