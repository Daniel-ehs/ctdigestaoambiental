<?php
// Incluir cabeçalho e verificar autenticação
include "views/templates/header.php";

// Verificar se está autenticado
if (!isset($_SESSION["user_id"])) {
    redirect("index.php?route=login");
}

// Verificar se o projeto foi carregado pelo controlador
if (!isset($projeto) || !$projeto) {
    echo '<div class="alert alert-danger">Erro: Dados do projeto não encontrados.</div>';
    include 'views/templates/footer.php';
    exit;
}

// Função para obter a cor do status do projeto
function getProjectStatusColor($status) {
    switch ($status) {
        case 'Em Andamento':
            return '#007bff'; // Azul
        case 'Concluído':
            return '#28a745'; // Verde
        case 'Cancelado':
            return '#dc3545'; // Vermelho
        default:
            return '#6c757d'; // Cinza
    }
}
?>

<!-- CSS Idêntico ao de inspecoes/view.php -->
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}

.form-section h5 i {
    transition: transform 0.3s ease;
}

.form-section.collapsed .data-item {
    display: none;
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

.btn-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
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

@media (max-width: 768px) {
    .card { margin: 1rem; }
    .card-body { padding: 1.5rem; }
    .form-section { padding: 1rem; }
    .data-item { flex-direction: column; align-items: flex-start; }
    .data-item strong { min-width: auto; margin-bottom: 0.5rem; }
}

@media (max-width: 576px) {
    .top-bar { padding: 0.5rem 1rem; }
    .btn-group { gap: 5px; }
    .btn { width: 100%; padding: 0.75rem 1rem; font-size: 0.9rem; }
}
/* ================================================================= */
/* CSS para Responsividade dos Botões (Topo e Corpo do Card)     */
/* ================================================================= */

@media (max-width: 768px) {

    /* Ajusta o espaçamento da barra superior em telas menores */
    .top-bar {
        padding: 0.75rem;
    }

    /* SELETOR AGRUPADO:
      Aplica as mesmas regras para o botão na barra superior (.top-bar .btn)
      E para o botão de "Editar" no corpo do card (.card-body .btn-group .btn)
    */
    .top-bar .btn,
    .card-body .btn-group .btn {
        /* 1. Transforma os botões em quadrados compactos */
        width: 40px;
        height: 40px;
        padding: 0;

        /* 2. A "mágica" para esconder o texto ("Editar") */
        font-size: 0;

        /* 3. Centraliza o ícone perfeitamente */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* SELETOR AGRUPADO PARA ÍCONES:
      Aplica as mesmas regras para os ícones dentro dos botões que acabamos de estilizar.
    */
    .top-bar .btn i,
    .card-body .btn-group .btn i {
        /* 4. Devolve o tamanho da fonte APENAS para o ícone */
        font-size: 1rem;
        margin: 0;
    }
}
</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Detalhes do Projeto #<?php echo htmlspecialchars($projeto['id']); ?></h1>
    <a href="index.php?route=projetos" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 0;" aria-label="Voltar para a lista de projetos">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Informações do Projeto</h6>
    </div>
    <div class="card-body">
        <div class="form-section">
            <h5>Informações Gerais <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.1s;">
                <i class="fas fa-hashtag"></i>
                <strong>ID do Projeto:</strong>
                <p><?php echo htmlspecialchars($projeto['id']); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.2s;">
                <i class="fas fa-building"></i>
                <strong>Empresa:</strong>
                <p><?php echo htmlspecialchars($projeto['empresa_nome'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.3s;">
                <i class="fas fa-lightbulb"></i>
                <strong>Fonte / Origem:</strong>
                <p><?php echo htmlspecialchars($projeto['fonte'] ?? 'N/A'); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.4s;">
                <i class="fas fa-user-tie"></i>
                <strong>Registrado por:</strong>
                <p><?php echo htmlspecialchars($projeto['usuario_nome'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <div class="form-section">
            <h5>Descrição e Observações <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.5s;">
                <i class="fas fa-file-alt"></i>
                <strong>Descrição:</strong>
                <p><?php echo nl2br(htmlspecialchars($projeto['descricao'] ?? 'N/A')); ?></p>
            </div>
            <div class="data-item" style="animation-delay: 0.6s;">
                <i class="fas fa-comment-dots"></i>
                <strong>Observações:</strong>
                <p><?php echo nl2br(htmlspecialchars($projeto['observacao'] ?? 'N/A')); ?></p>
            </div>
        </div>
        
        <div class="form-section">
            <h5>Status e Prazos <i class="fas fa-chevron-down"></i></h5>
            <div class="data-item" style="animation-delay: 0.7s;">
                <i class="fas fa-info-circle"></i>
                <strong>Status:</strong>
                 <p><span class="badge" style="background-color: <?php echo getProjectStatusColor($projeto['status']); ?>;"><?php echo htmlspecialchars($projeto['status']); ?></span></p>
            </div>
             <div class="data-item" style="animation-delay: 0.8s;">
                <i class="fas fa-calendar-alt"></i>
                <strong>Data de Registro:</strong>
                <p><?php echo isset($projeto['data_registro']) ? formatDateTime($projeto['data_registro']) : 'N/A'; ?></p>
            </div>
             <div class="data-item" style="animation-delay: 0.9s;">
                <i class="fas fa-hourglass-end"></i>
                <strong>Prazo:</strong>
                <p><?php echo isset($projeto['prazo']) ? formatDate($projeto['prazo']) : 'N/A'; ?></p>
            </div>
            <div class="data-item" style="animation-delay: 1.0s;">
                <i class="fas fa-calendar-check"></i>
                <strong>Data de Conclusão:</strong>
                <p><?php echo isset($projeto['data_conclusao']) ? formatDate($projeto['data_conclusao']) : 'N/A'; ?></p>
            </div>
        </div>

        <div class="btn-group">
            <a href="index.php?route=projetos&action=edit&id=<?php echo $projeto['id']; ?>" class="btn btn-warning" title="Editar Projeto" style="--btn-index: 1;" aria-label="Editar Projeto">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa partículas de fundo
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

    // Funcionalidade para seções recolhíveis (collapsible)
    document.querySelectorAll('.form-section').forEach(section => {
        section.querySelector('h5').addEventListener('click', () => {
            section.classList.toggle('collapsed');
        });
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>
