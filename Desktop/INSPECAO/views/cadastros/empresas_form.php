<?php
// Incluir cabeçalho e verificar autenticação
include_once BASE_PATH . '/views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se é administrador
if ($_SESSION['user_nivel'] !== 'admin') {
    setFlashMessage('error', 'Acesso negado. Apenas administradores podem gerenciar empresas.');
    redirect('index.php?route=dashboard');
}

// Determinar se é edição ou criação
$isEdit = isset($registro) && !empty($registro);
$titulo = $isEdit ? 'Editar Empresa' : 'Nova Empresa';
$acao = $isEdit ? "index.php?route=cadastros&type=empresas&action=update&id={$registro['id']}" : "index.php?route=cadastros&type=empresas&action=store";
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

/* Seções do formulário */
.form-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #28a745;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.form-section h5 {
    color: #28a745;
    font-weight: 700;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

/* Campos do formulário */
.form-label {
    color: #333333;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
    font-weight: bold;
}

.form-control, .form-select {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 0.75rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: #ffffff;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    background: #ffffff;
}

/* Animações */
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Botões */
.btn {
    position: relative;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 0 15px rgba(40, 167, 69, 0.3);
    border: none;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2), 0 0 20px rgba(40, 167, 69, 0.5);
    filter: brightness(1.1);
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

/* Animação para botões */
@keyframes pop {
    0% { transform: scale(0.7); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.btn {
    animation: pop 0.4s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
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
}

@media (max-width: 576px) {
    .top-bar {
        padding: 0.5rem 1rem;
        flex-direction: column;
        gap: 1rem;
    }
    .top-bar h1 {
        font-size: 1.5rem;
    }
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;"><?php echo $titulo; ?></h1>
    <a href="index.php?route=cadastros&type=empresas" class="btn btn-secondary" title="Voltar para Lista" style="--btn-index: 1;">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold"><?php echo $titulo; ?></h6>
    </div>
    <div class="card-body">
        <?php include_once BASE_PATH . '/views/components/flash_messages.php'; ?>
        <form action="<?php echo $acao; ?>" method="post">
            <!-- Informações Gerais -->
            <div class="form-section">
                <h5>Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                        <label for="nome" class="form-label required">Nome da Empresa</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $isEdit ? htmlspecialchars($registro['nome']) : ''; ?>" required>
                        <div class="form-text">Digite o nome completo da empresa</div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex justify-content-between">
                <a href="index.php?route=cadastros&type=empresas" class="btn btn-secondary" style="--btn-index: 1;">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success" style="--btn-index: 2;">
                    <i class="fas fa-save"></i> <?php echo $isEdit ? 'Atualizar' : 'Salvar'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once BASE_PATH . '/views/templates/footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Partículas de fundo
    particlesJS('particles', {
        particles: {
            number: { value: 50, density: { enable: true, value_area: 800 } },
            color: { value: '#28a745' },
            shape: { type: 'circle' },
            opacity: { value: 0.5, random: true },
            size: { value: 3, random: true },
            line_linked: { enable: true, distance: 150, color: '#52c41a', opacity: 0.4, width: 1 },
            move: { enable: true, speed: 2, direction: 'none', random: true }
        },
        interactivity: {
            detect_on: 'canvas',
            events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true },
            modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } }
        },
        retina_detect: true
    });

    // Verificar se particles.js carregou corretamente
    if (typeof particlesJS === 'undefined') {
        console.error('particles.js não foi carregado. Verifique a conexão com o CDN.');
    }
});
</script>

<!-- Fonte Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

