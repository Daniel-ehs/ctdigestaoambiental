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

/* Tabela */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
}

.table th {
    background: #f0f4f0;
    color: #28a745;
    font-weight: 700;
    font-size: 0.95rem;
}

.table tbody tr {
    background: #ffffff;
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: #f0f4f0;
    transform: scale(1.01);
}

.table tbody td {
    color: #333333;
    font-size: 0.85rem;
}

/* Animações */
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

/* Mensagem de tabela vazia */
.text-center {
    color: #555555;
    font-size: 1rem;
    padding: 2rem;
}

/* Botões gerais */
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

.btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
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

/* Animação para botões gerais */
@keyframes pop {
    0% { transform: scale(0.7); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.btn {
    animation: pop 0.4s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
}

/* Estilo para os botões de ação */
.btn-group {
    display: flex;
    gap: 3px;
    flex-wrap: nowrap;
    justify-content: center;
}

.btn-group .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
    border-radius: 5px;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 10px;
    line-height: 1;
}

/* Gradientes para cada cor */
.btn-primary {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

/* Estilo para botão desativado */
.btn.disabled, .btn:disabled {
    background: linear-gradient(135deg, #d3d3d3, #a9a9a9);
    color: white;
    cursor: not-allowed;
    pointer-events: none;
}

/* Efeitos de hover e clique */
.btn-group .btn:not(.disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
    filter: brightness(1.15);
}

.btn-group .btn:not(.disabled):active {
    transform: scale(0.95);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Animação de "pop" */
.btn-group .btn {
    animation: pop 0.3s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
}

/* Remove animação no hover */
.btn-group .btn:not(.disabled):hover {
    animation: none;
}

/* Ajuste para a coluna de ações */
.actions-column {
    min-width: 80px;
}

/* Ajustes para responsividade */
@media (max-width: 576px) {
    .btn-group {
        gap: 2px;
    }
    .btn-group .btn {
        width: 22px;
        height: 22px;
        font-size: 9px;
    }
    .actions-column {
        min-width: 70px;
    }
}

/* Responsividade geral */
@media (max-width: 768px) {
    .card {
        margin: 1rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .table th, .table td {
        padding: 0.5rem;
    }
}

@media (max-width: 576px) {
    .top-bar {
        padding: 0.5rem 1rem;
    }
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Cadastro de Empresas</h1>
    <a href="index.php?route=cadastros&type=empresas&action=create" class="btn btn-success" title="Adicionar Nova Empresa" style="--btn-index: 1;">
        <i class="fas fa-plus"></i> Adicionar Nova
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Lista de Empresas</h6>
    </div>
    <div class="card-body">
        <?php include_once BASE_PATH . '/views/components/flash_messages.php'; ?>
        <table class="table" id="empresasTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Data de Criação</th>
                    <th>Última Atualização</th>
                    <th class="actions-column">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Garantir que os dados estejam ordenados por ID em ordem crescente
                if (!empty($dados)) {
                    usort($dados, function($a, $b) {
                        return $a['id'] - $b['id'];
                    });
                }
                ?>
                <?php if (!empty($dados)): ?>
                    <?php foreach ($dados as $index => $empresa): ?>
                        <tr style="animation: fadeIn 0.6s ease-out forwards; animation-delay: <?php echo (0.1 * $index); ?>s;" data-id="<?php echo $empresa['id']; ?>">
                            <td><?= htmlspecialchars($empresa['id']) ?></td>
                            <td><?= htmlspecialchars($empresa['nome']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($empresa['created_at'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($empresa['updated_at'])) ?></td>
                            <td class="actions-column">
                                <div class="btn-group">
                                    <a href="index.php?route=cadastros&type=empresas&action=edit&id=<?= $empresa['id'] ?>" class="btn btn-sm btn-primary text-white" title="Editar Empresa" style="--btn-index: 1;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($empresa['id'] != 1): // Não permitir excluir a empresa Matriz ?>
                                    <a href="index.php?route=cadastros&type=empresas&action=delete&id=<?= $empresa['id'] ?>&name=<?= urlencode($empresa['nome']) ?>" class="btn btn-sm btn-danger" title="Excluir Empresa" style="--btn-index: 2;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-sm disabled" title="Não é possível excluir a empresa Matriz" style="--btn-index: 2;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma empresa cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'modal_confirmacao.php'; ?>
<?php include_once BASE_PATH . '/views/templates/footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

