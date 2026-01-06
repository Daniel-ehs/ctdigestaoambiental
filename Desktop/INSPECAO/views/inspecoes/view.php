<?php
// Incluir cabeçalho e verificar autenticação
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se a inspeção foi carregada pelo controlador
if (!isset($inspecao) || !$inspecao) {
    echo '<div class="alert alert-danger">Erro: Dados da inspeção não encontrados.</div>';
    include 'views/templates/footer.php';
    exit;
}

// Função para obter a classe do status
function getStatusClass($status) {
    switch ($status) {
        case 'Em Aberto':
            return 'info';
        case 'Concluído':
            return 'success';
        case 'Prazo Vencido':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

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
    cursor: pointer;
}

.form-section.collapsed .data-item {
    display: none;
}

.form-section h5 {
    color: #28a745;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.form-section h5 i {
    transition: transform 0.3s ease;
}

.form-section.collapsed h5 i {
    transform: rotate(-180deg);
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
    word-wrap: break-word;
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
.data-item img {
    border-radius: 10px;
    max-width: 300px;
    max-height: 200px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.data-item img:hover {
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

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
}

.btn.disabled {
    background: linear-gradient(135deg, #d3d3d3, #a9a9a9);
    cursor: not-allowed;
    pointer-events: none;
}

.btn:hover:not(.disabled) {
    transform: scale(1.1);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2), 0 0 20px rgba(40, 167, 69, 0.5);
    filter: brightness(1.2);
}

.btn:active:not(.disabled) {
    transform: scale(0.95);
}

/* Animações */
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Lightbox para imagem */
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
    .data-item img {
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

@media (max-width: 767px) {
    .top-bar {
        padding: 0.75rem;
    }
    .top-bar .btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .top-bar .btn i {
        font-size: 1rem;
        margin: 0;
    }
    .form-section .data-item {
        flex-direction: column !important;
        align-items: flex-start;
        gap: 5px;
    }
    .form-section .data-item strong {
        min-width: auto;
    }
    .form-section .data-item p {
        width: 100%;
    }
    .form-section .data-item img {
        max-width: 100%;
        margin-top: 5px;
    }
    .btn-group {
        flex-direction: column;
        width: 100%;
        gap: 10px;
    }
    .btn-group .btn {
       width: 100%;
    }
}
</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Inspeção #<?php echo htmlspecialchars($inspecao['numero_inspecao'] ?? $inspecao['id']); ?></h1>
    <a href="index.php?route=inspecoes" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 0;" aria-label="Voltar para a lista de inspeções">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Detalhes da Inspeção</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Informações Gerais <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.1s;">
            <i class="fas fa-hashtag"></i>
              <strong>Nº Inspeção:</strong>
            <p><?php echo htmlspecialchars($inspecao['numero_inspecao'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.2s;">
                <i class="fas fa-calendar-alt"></i>
                <strong>Data do Apontamento:</strong>
                <p><?php echo isset($inspecao['data_apontamento']) ? date('d/m/Y', strtotime($inspecao['data_apontamento'])) : 'N/A'; ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.3s;">
                <i class="fas fa-calendar-week"></i>
                <strong>Semana/Ano:</strong>
                <p><?php echo htmlspecialchars($inspecao['semana_ano']); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.4s;">
                <i class="fas fa-check-circle"></i>
                <strong>Status:</strong>
                <p><span class="badge bg-<?php echo getStatusClass($inspecao['status']); ?>"><?php echo htmlspecialchars($inspecao['status']); ?></span></p>
            </div>
        </div>

        <div class="form-section">
            <h5>Localização <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.5s;">
                <i class="fas fa-building"></i>
                <strong>Setor:</strong>
                <p><?php echo htmlspecialchars($inspecao['setor_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.6s;">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Local:</strong>
                <p><?php echo htmlspecialchars($inspecao['local_nome'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="form-section">
            <h5>Detalhes do Apontamento <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.7s;">
                <i class="fas fa-tag"></i>
                <strong>Tipo:</strong>
                <p><span class="badge" style="background-color: <?php echo htmlspecialchars($inspecao['tipo_cor'] ?? '#6c757d'); ?>"><?php echo htmlspecialchars($inspecao['tipo_nome'] ?? 'N/A'); ?></span></p>
            </div>
            <div class="data-item" style="animation-delay: 0.8s;">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Apontamento:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['apontamento'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.9s;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Risco:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['risco_consequencia'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.0s;">
                <i class="fas fa-lightbulb"></i>
                <strong>Resolução:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['resolucao_proposta'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.1s;">
                <i class="fas fa-image"></i>
                <strong>Foto:</strong>
                <p>
                    <?php
                    // --- INÍCIO: LÓGICA CORRIGIDA PARA MINIO ---
                    // A variável já contém o URL completo do MinIO. Basta usá-la.
                    if (!empty($inspecao['foto_antes'])) {
                        $foto_url = htmlspecialchars($inspecao['foto_antes']);
                        echo '<img src="' . $foto_url . '" alt="Foto Antes" onclick="toggleLightbox(\'' . $foto_url . '\')">';
                    } else {
                        echo '<span class="badge bg-secondary">N/A</span>';
                    }
                    // --- FIM: LÓGICA CORRIGIDA ---
                    ?>
                </p>
            </div>
        </div>

        <div class="form-section">
            <h5>Responsáveis e Prazos <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 1.2s;">
                <i class="fas fa-user"></i>
                <strong>Responsável:</strong>
                <p><?php echo htmlspecialchars($inspecao['responsavel'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.3s;">
                <i class="fas fa-hourglass-end"></i>
                <strong>Prazo:</strong>
                <p><?php echo isset($inspecao['prazo']) ? date('d/m/Y', strtotime($inspecao['prazo'])) : 'N/A'; ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.4s;">
                <i class="fas fa-calendar-check"></i>
                <strong>Conclusão:</strong>
                <p><?php echo isset($inspecao['data_conclusao']) ? date('d/m/Y', strtotime($inspecao['data_conclusao'])) : 'N/A'; ?></p>
            </div>
        </div>

        <div class="form-section">
            <h5>Registro <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 1.5s;">
                <i class="fas fa-user-edit"></i>
                <strong>Registrado por:</strong>
                <p><?php echo htmlspecialchars($inspecao['usuario_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.6s;">
                <i class="fas fa-clock"></i>
                <strong>Data de Registro:</strong>
                <p><?php echo isset($inspecao['data_registro']) ? date('d/m/Y H:i:s', strtotime($inspecao['data_registro'])) : 'N/A'; ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.7s;">
                <i class="fas fa-comment"></i>
                <strong>Observações:</strong>
                <p><?php echo nl2br(htmlspecialchars($inspecao['observacao'] ?? 'N/A')); ?></p>
            </div>
        </div>

        <div class="btn-group">
            <a href="index.php?route=inspecoes&action=edit&id=<?php echo $inspecao['id']; ?>" class="btn btn-warning" title="Editar" style="--btn-index: 1;" aria-label="Editar inspeção">
                <i class="fas fa-edit"></i> Editar
            </a>
            <?php
            // Este bloco foi corrigido no InspecaoController para passar a variável $planos_acao
            if(isset($planos_acao) && !empty($planos_acao)): ?>
                <a href="index.php?route=planos&action=view&id=<?php echo $planos_acao[0]['id']; ?>" class="btn btn-info" title="Ver Plano de Ação" style="--btn-index: 2;" aria-label="Ver plano de ação">
                    <i class="fas fa-tasks"></i> Ver Plano
                </a>
            <?php else: ?>
                <a href="index.php?route=planos&action=create&inspecao_id=<?php echo $inspecao['id']; ?>" class="btn btn-success <?php echo $inspecao['status'] === 'Concluído' ? 'disabled' : ''; ?>" title="Criar Plano de Ação" style="--btn-index: 2;" aria-label="Criar plano de ação">
                    <i class="fas fa-plus"></i> Criar Plano
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="lightbox" id="lightbox" onclick="toggleLightbox('')">
    <img id="lightbox-img" src="" alt="Imagem ampliada">
</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Partículas de fundo
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles', { particles: { number: { value: 50, density: { enable: true, value_area: 800 } }, color: { value: '#28a745' }, shape: { type: 'circle' }, opacity: { value: 0.5, random: true }, size: { value: 3, random: true }, line_linked: { enable: true, distance: 150, color: '#52c41a', opacity: 0.4, width: 1 }, move: { enable: true, speed: 2, direction: 'none', random: true } }, interactivity: { detect_on: 'canvas', events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true }, modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } } }, retina_detect: true });
    } else {
        console.error('particles.js não foi carregado.');
    }

    // Toggle seções
    document.querySelectorAll('.form-section').forEach(section => {
        section.querySelector('h5').addEventListener('click', () => {
            section.classList.toggle('collapsed');
        });
    });

    // Toggle lightbox
    window.toggleLightbox = function(src) {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        if (src) {
            img.src = src;
            lightbox.classList.add('active');
        } else {
            lightbox.classList.remove('active');
            img.src = '';
        }
    };
});
</script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<?php include 'views/templates/footer.php'; ?>

