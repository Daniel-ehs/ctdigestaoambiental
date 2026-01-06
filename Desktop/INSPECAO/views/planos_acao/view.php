<?php
// Incluir cabeçalho e verificar autenticação
include BASE_PATH . '/views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se os dados do plano foram carregados pelo controller
if (!isset($plano) || !$plano) {
    echo '<div class="alert alert-danger">Erro: Dados do plano de ação não encontrados.</div>';
    include BASE_PATH . '/views/templates/footer.php';
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

// Construir caminhos para as imagens
// Se a foto_antes for uma URL do S3, usar diretamente
// Caso contrário, construir URL local (compatibilidade com sistema antigo)
$fotoAntesPath = null;
if (!empty($plano['inspecao_foto_antes'])) {
    if (isS3Url($plano['inspecao_foto_antes'])) {
        // É URL do MinIO, usar diretamente
        $fotoAntesPath = htmlspecialchars($plano['inspecao_foto_antes']);
        error_log("Foto Antes - URL do MinIO: $fotoAntesPath");
    } else {
        // É arquivo local, construir URL
        if (defined('FOTOS_ANTES_URL')) {
            $fotoAntesPath = rtrim(FOTOS_ANTES_URL, '/') . '/' . htmlspecialchars(basename($plano['inspecao_foto_antes']));
            error_log("Foto Antes - URL local: $fotoAntesPath");
        }
    }
}

$fotoDepoisPath = null;
if (!empty($plano['foto_depois'])) {
    if (isS3Url($plano['foto_depois'])) {
        // É URL do MinIO, usar diretamente
        $fotoDepoisPath = htmlspecialchars($plano['foto_depois']);
        error_log("Foto Depois - URL do MinIO: $fotoDepoisPath");
    } else {
        // É arquivo local, construir URL
        if (defined('FOTOS_DEPOIS_URL')) {
            $fotoDepoisPath = rtrim(FOTOS_DEPOIS_URL, '/') . '/' . htmlspecialchars(basename($plano['foto_depois']));
            error_log("Foto Depois - URL local: $fotoDepoisPath");
        }
    }
}

// Verificar se as imagens existem (apenas para arquivos locais)
$temFotoAntes = false;
$temFotoDepois = false;

if ($fotoAntesPath) {
    if (isS3Url($plano['inspecao_foto_antes'])) {
        // Para URLs do S3, assumir que existem (a verificação seria feita via API)
        $temFotoAntes = true;
    } else {
        // Para arquivos locais, verificar se existem
        if (defined('FOTOS_ANTES_DIR')) {
            $fotoAntesFullPath = FOTOS_ANTES_DIR . '/' . basename($plano['inspecao_foto_antes']);
            $temFotoAntes = file_exists($fotoAntesFullPath);
            error_log("Foto Antes - Full Path: $fotoAntesFullPath, Exists: " . ($temFotoAntes ? 'Sim' : 'Não'));
        }
    }
}

if ($fotoDepoisPath) {
    if (isS3Url($plano['foto_depois'])) {
        // Para URLs do S3, assumir que existem
        $temFotoDepois = true;
    } else {
        // Para arquivos locais, verificar se existem
        if (defined('FOTOS_DEPOIS_DIR')) {
            $fotoDepoisFullPath = FOTOS_DEPOIS_DIR . '/' . basename($plano['foto_depois']);
            $temFotoDepois = file_exists($fotoDepoisFullPath);
            error_log("Foto Depois - Full Path: $fotoDepoisFullPath, Exists: " . ($temFotoDepois ? 'Sim' : 'Não'));
        }
    }
}
// --- FIM: LÓGICA ATUALIZADA PARA SUPORTAR MINIO ---
?>

<!-- CSS Estilizado -->
<style>
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
    .inspection-image {
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

/* CSS para Responsividade da Barra Superior (Mobile) */
@media (max-width: 768px) {
    .top-bar {
        padding: 0.75rem;
    }

    .top-bar .btn-group .btn {
        width: 40px;
        height: 40px;
        padding: 0;
        font-size: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .top-bar .btn-group .btn i {
        font-size: 1rem;
        margin: 0;
    }
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Plano de Ação para Inspeção #<?php echo htmlspecialchars($plano['numero_inspecao'] ?? $plano['inspecao_id']); ?></h1>
    <div class="btn-group">
        <a href="index.php?route=planos" class="btn btn-secondary" title="Voltar para Planos de Ação" style="--btn-index: 1;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <a href="index.php?route=planos&action=pdf&id=<?php echo $plano['id']; ?>" class="btn btn-primary" title="Gerar PDF" target="_blank" style="--btn-index: 2;">
            <i class="fas fa-file-pdf"></i> Gerar PDF
        </a>
    </div>
</div>

<!-- Detalhes da Inspeção Associada -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Inspeção Associada (#<?php echo htmlspecialchars($plano['numero_inspecao'] ?? $plano['inspecao_id']); ?>)</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Informações da Inspeção</h5>
            <div class="data-item" style="animation-delay: 0.1s;">
                <i class="fas fa-calendar-alt"></i>
                <strong>Data do Apontamento:</strong>
                <p><?php echo formatDate($plano['inspecao_data'] ?? ''); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.2s;">
                <i class="fas fa-building"></i>
                <strong>Setor:</strong>
                <p><?php echo htmlspecialchars($plano['setor_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.3s;">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Local:</strong>
                <p><?php echo htmlspecialchars($plano['local_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.4s;">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Apontamento:</strong>
                <p><?php echo nl2br(htmlspecialchars($plano['inspecao_apontamento'] ?? 'N/A')); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes do Plano de Ação -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Detalhes do Plano de Ação</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Informações do Plano</h5>
            <div class="data-item" style="animation-delay: 0.1s;">
            <i class="fas fa-calendar-check"></i>
            <strong>Data da Ação:</strong>
            <p><?php echo formatDateTime($plano['data_acao_criacao'] ?? ''); ?></p>
        </div>
            <div class="data-item" style="animation-delay: 0.3s;">
                <i class="fas fa-list-alt"></i>
                <strong>Descrição da Ação:</strong>
                <p><?php echo nl2br(htmlspecialchars($plano['descricao_acao'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.4s;">
                <i class="fas fa-user"></i>
                <strong>Registrado por:</strong>
                <p><?php echo htmlspecialchars($plano['usuario_nome'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Evidências Fotográficas -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Evidências Fotográficas</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Fotos</h5>
            <div class="row">
                <div class="col-md-6 text-center" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                    <h6>Antes</h6>
                    <?php if ($temFotoAntes && $fotoAntesPath): ?>
                        <img src="<?php echo $fotoAntesPath; ?>" alt="Foto Antes" class="inspection-image" onclick="toggleLightbox('<?php echo $fotoAntesPath; ?>')">
                    <?php else: ?>
                        <span class="badge bg-secondary">Foto "Antes" não disponível</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-center" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.2s;">
                    <h6>Depois</h6>
                    <?php if ($temFotoDepois && $fotoDepoisPath): ?>
                        <img src="<?php echo $fotoDepoisPath; ?>" alt="Foto Depois" class="inspection-image" onclick="toggleLightbox('<?php echo $fotoDepoisPath; ?>')">
                    <?php else: ?>
                        <span class="badge bg-secondary">Foto "Depois" não disponível</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="toggleLightbox('')">
    <img id="lightbox-img" src="" alt="Imagem ampliada">
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para inicializar o dropdown
    function initializeDropdown() {
        console.log('[Dropdown Debug] Tentando inicializar dropdown...');
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            console.log('[Dropdown Debug] Bootstrap JS detectado.');
            const dropdownElement = document.getElementById('userDropdown');
            if (dropdownElement) {
                console.log('[Dropdown Debug] Elemento userDropdown encontrado:', dropdownElement);
                try {
                    new bootstrap.Dropdown(dropdownElement);
                    console.log('[Dropdown Debug] Dropdown inicializado com sucesso.');
                } catch (e) {
                    console.error('[Dropdown Debug] Erro ao inicializar dropdown:', e);
                }
            } else {
                console.error('[Dropdown Debug] Elemento userDropdown não encontrado no DOM.');
            }
        } else {
            console.error('[Dropdown Debug] Bootstrap JavaScript ou Dropdown não disponível.');
        }
    }

    initializeDropdown();

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

<?php include BASE_PATH . '/views/templates/footer.php'; ?>
