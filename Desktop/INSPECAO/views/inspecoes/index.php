<?php
// Incluir cabeçalho
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// As funções formatDate e displayFlashMessage devem ser carregadas globalmente pelo index.php principal
// via require_once 'utils/helpers.php';

// Obter dados passados pelo controller (com verificação)
$setores = $setores ?? [];
$locais = $locais ?? [];
$tipos = $tipos ?? [];
$empresas = $empresas ?? [];
$inspecoes = $inspecoes ?? [];
$totalPaginas = $totalPaginas ?? 1;
$totalInspecoes = $totalInspecoes ?? 0;
$pagina = $pagina ?? 1;
$filtros = $filtros ?? []; // Capturar filtros para paginação

// --- Funções Helper para buscar Nomes ---
function findSetorName($setorId, $setores) {
    foreach ($setores as $setor) {
        if (isset($setor['id']) && $setor['id'] == $setorId) {
            return $setor['nome'];
        }
    }
    return 'N/A'; 
}

function findLocalName($localId, $locais) {
    foreach ($locais as $local) {
        if (isset($local['id']) && $local['id'] == $localId) {
            return $local['nome'];
        }
    }
    return 'N/A';
}

$filtroEmpresaId = htmlspecialchars($_GET["empresa_id"] ?? "");
$filtroSetorId = htmlspecialchars($_GET["setor_id"] ?? "");
$filtroLocalId = htmlspecialchars($_GET["local_id"] ?? "");

?>

<style>
/* Estilos mantidos do seu código original, com pequenas adições para a nova lógica */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333333;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.top-bar {
    background: #f5f6f5;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 10;
}

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

.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
    text-align: center;
}

.card-body {
    padding: 2.5rem;
}

.form-label {
    font-weight: 600;
    color: #333333;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.form-select, .form-control {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
}

.form-select:disabled {
    background-color: #e9ecef;
    opacity: 0.7;
 }

.form-select:focus, .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.table th,
.table td {
    padding: 0.5rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
}

.table th {
    background: #f0f4f0;
    color: #28a745;
    font-weight: 700;
    font-size: 0.8rem;
}

.table tbody tr {
    background: #ffffff;
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: #f0f4f0;
    transform: scale(1.01);
    cursor: pointer; /* Indica interatividade na linha */
}

.table tbody td {
    color: #333333;
    font-size: 0.75rem;
}

.tipo-bolinha {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.tipo-bolinha:hover {
    transform: scale(1.2);
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
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

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

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
    padding: 0;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: white;
}

.btn.disabled, .btn:disabled {
    background: linear-gradient(135deg, #d3d3d3 0%, #a9a9a9 100%);
    color: white;
    cursor: not-allowed;
    pointer-events: none;
}

.btn-group .btn:not(.disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
    filter: brightness(1.15);
}

.actions-column {
    min-width: 130px;
    text-align: center;
}

/* Adicionando classes para as colunas de tipo e status para fácil seleção no JS */
.tipo-column, .status-column {
    text-align: center;
}

.apontamento-tooltip {
    position: absolute;
    display: none;
    background-color: rgba(40, 40, 40, 0.95);
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    z-index: 1001; 
    max-width: 350px;
    font-size: 0.85rem;
    line-height: 1.4;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    pointer-events: none; 
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}
/* =================================================================== */
/* CSS para transformar o botão "Nova Inspeção" em um ícone no mobile  */
/* =================================================================== */

@media (max-width: 767px) {

    /* 1. Esconde o TEXTO do botão, mas não o ícone */
    .top-bar a.btn {
        /* Truque: definimos o tamanho da fonte do botão como zero, o que esconde o texto */
        font-size: 0;
        width: auto;          /* Remove a largura fixa */
        height: auto;         /* Remove a altura fixa */
        border-radius: 10px;  /* Mantém a borda arredondada original */
        padding: 0.75rem;    /* Restaura o preenchimento original */
        display: inline-block; /* Volta para o display original */
    }

    /* 2. Devolve o tamanho da fonte APENAS para o ícone */
    .top-bar a.btn i {
        font-size: 1.2rem; /* Aumentei um pouco o tamanho do ícone para compensar a falta do texto */
        margin: 0;
    }

    /* Remove os estilos de flexbox que centralizavam no círculo */
    .top-bar a.btn {
        align-items: initial;
        justify-content: initial;
    }
}
</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Gerenciamento de Inspeções</h1>
    <a href="index.php?route=inspecoes&action=create" class="btn btn-primary" title="Nova Inspeção">
        <i class="fas fa-plus-circle"></i> Nova Inspeção
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Filtros</h6>
    </div>
    <div class="card-body">
        <form method="get" action="index.php">
            <input type="hidden" name="route" value="inspecoes">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Todas</option>
                        <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= htmlspecialchars($empresa['id']); ?>" <?= ($filtroEmpresaId == $empresa['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($empresa['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="setor_id" class="form-label">Setor</label>
                    <select class="form-select" id="setor_id" name="setor_id" <?= empty($filtroEmpresaId) ? 'disabled' : '' ?>>
                        <option value=""><?= empty($filtroEmpresaId) ? 'Selecione uma Empresa' : 'Todos' ?></option>
                        <?php foreach ($setores as $setor): ?>
                        <option value="<?= htmlspecialchars($setor['id']); ?>" <?= ($filtroSetorId == $setor['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($setor['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="local_id" class="form-label">Local</label>
                    <select class="form-select" id="local_id" name="local_id" <?= empty($filtroSetorId) ? 'disabled' : '' ?>>
                         <option value=""><?= empty($filtroSetorId) ? 'Selecione um Setor' : 'Todos' ?></option>
                        <?php foreach ($locais as $local): ?>
                        <option value="<?= htmlspecialchars($local['id']); ?>" <?= ($filtroLocalId == $local['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($local['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tipo_id" class="form-label">Tipo de Apontamento</label>
                    <select class="form-select" id="tipo_id" name="tipo_id">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['id']); ?>" <?= (isset($_GET['tipo_id']) && $_GET['tipo_id'] == $tipo['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($tipo['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="Em Aberto" <?= (isset($_GET['status']) && $_GET['status'] == 'Em Aberto') ? 'selected' : ''; ?>>Em Aberto</option>
                        <option value="Concluído" <?= (isset($_GET['status']) && $_GET['status'] == 'Concluído') ? 'selected' : ''; ?>>Concluído</option>
                        <option value="Prazo Vencido" <?= (isset($_GET['status']) && $_GET['status'] == 'Prazo Vencido') ? 'selected' : ''; ?>>Prazo Vencido</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary" title="Aplicar Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="index.php?route=inspecoes" class="btn btn-secondary" title="Limpar Filtros">
                        <i class="fas fa-eraser"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- NOVO: Card de Busca Global -->
<div class="card shadow mb-4">
    <div class="card-header">
        <i class="fas fa-search me-1"></i>
        Filtro Geral
    </div>
    <div class="card-body">
        <form action="index.php" method="GET">
            <input type="hidden" name="route" value="inspecoes">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Buscar por apontamento, setor, local, data (AAAA-MM-DD)..." name="busca" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if (!empty($_GET['busca'])): ?>
                    <a href="index.php?route=inspecoes" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<!-- FIM NOVO: Card de Busca Global -->

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Inspeções (Total: <?= $totalInspecoes; ?>)</h6>
    </div>
    <div class="card-body">
        <?php
        if (file_exists('views/components/flash_messages.php')) {
            include 'views/components/flash_messages.php';
        } elseif (function_exists('displayFlashMessage')) {
            displayFlashMessage();
        }
        ?>
        <div class="table-responsive">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Empresa</th>
                        <th>Data</th>
                        <th>Setor</th>
                        <th>Local</th>
                        <th>Apontamento</th>
                        <th>Tipo</th>
                        <th>Responsável</th>
                        <th>Prazo</th>
                        <th>Status</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody id="inspecoesTableBody">
                    <?php if (isset($inspecoes) && is_array($inspecoes) && !empty($inspecoes)): ?>
                        <?php foreach ($inspecoes as $index => $inspecao): ?>
                        <tr data-apontamento="<?= htmlspecialchars($inspecao['apontamento'] ?? ''); ?>">
                            <td><?= htmlspecialchars($inspecao['numero_inspecao'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($inspecao['empresa_nome'] ?? 'N/A'); ?></td>
                            <td><?= function_exists('formatDate') ? formatDate($inspecao['data_apontamento'] ?? null) : ($inspecao['data_apontamento'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($inspecao['setor_nome'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($inspecao['local_nome'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars(substr($inspecao['apontamento'] ?? '', 0, 25)); ?><?= strlen($inspecao['apontamento'] ?? '') > 25 ? '...' : '' ?></td>
                            <!-- --- CÉLULA TIPO ALTERADA PARA ADICIONAR CLASSE --- -->
                            <td class="tipo-column">
                                <?php if (isset($inspecao['tipo_nome']) && isset($inspecao['tipo_cor'])): ?>
                                <span class="tipo-bolinha" style="background-color: <?= htmlspecialchars($inspecao['tipo_cor'] ?? '#6c757d'); ?>" title="<?= htmlspecialchars($inspecao['tipo_nome']); ?>"></span>
                                <?php else: ?>
                                N/A
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($inspecao['responsavel_nome'] ?? $inspecao['responsavel'] ?? 'N/A'); ?></td>
                            <td><?= function_exists('formatDate') ? formatDate($inspecao['prazo'] ?? null) : ($inspecao['prazo'] ?? 'N/A'); ?></td>
                             <!-- --- CÉLULA STATUS ALTERADA PARA ADICIONAR CLASSE --- -->
                            <td class="status-column">
                                <?php
                                $statusCor = '#6c757d'; 
                                $statusTexto = $inspecao['status'] ?? 'N/A';
                                
                                if (isset($inspecao['status'])) {
                                    switch ($inspecao['status']) {
                                        case 'Em Aberto': $statusCor = '#ffc107'; break;
                                        case 'Concluído': $statusCor = '#28a745'; break;
                                        case 'Prazo Vencido': $statusCor = '#dc3545'; break;
                                    }
                                }
                                ?>
                                <span class="tipo-bolinha" style="background-color: <?= $statusCor; ?>" title="<?= htmlspecialchars($statusTexto); ?>"></span>
                            </td>
                            <td class="actions-column">
                                <div class="btn-group">
                                    <a href="index.php?route=inspecoes&action=view&id=<?= htmlspecialchars($inspecao['id'] ?? ''); ?>" class="btn btn-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?route=inspecoes&action=edit&id=<?= htmlspecialchars($inspecao['id'] ?? ''); ?>" class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?route=planos&action=create&inspecao_id=<?= htmlspecialchars($inspecao['id'] ?? ''); ?>" 
                                    class="btn btn-success text-white <?= ($inspecao['status'] ?? '') === 'Concluído' ? 'disabled' : ''; ?>" 
                                    title="Plano de Ação">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-delete" data-id="<?= htmlspecialchars($inspecao['id'] ?? ''); ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Nenhuma inspeção encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPaginas > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Navegação de página">
                <ul class="pagination">
                    <?php
                    $urlBase = 'index.php?route=inspecoes';
                    $queryParams = [];
                    if (!empty($filtros['empresa_id'])) $queryParams['empresa_id'] = $filtros['empresa_id'];
                    if (!empty($filtros['setor_id'])) $queryParams['setor_id'] = $filtros['setor_id'];
                    if (!empty($filtros['local_id'])) $queryParams['local_id'] = $filtros['local_id'];
                    if (!empty($filtros['tipo_id'])) $queryParams['tipo_id'] = $filtros['tipo_id'];
                    if (!empty($filtros['status'])) $queryParams['status'] = $filtros['status'];
                    if (!empty($filtros['data_inicio'])) $queryParams['data_inicio'] = $filtros['data_inicio'];
                    if (!empty($filtros['data_fim'])) $queryParams['data_fim'] = $filtros['data_fim'];
                    
                    if (!empty($queryParams)) {
                        $urlBase .= '&' . http_build_query($queryParams);
                    }
                    
                    $prevDisabled = ($pagina <= 1) ? 'disabled' : '';
                    echo '<li class="page-item ' . $prevDisabled . '"><a class="page-link" href="' . $urlBase . '&pagina=' . ($pagina - 1) . '">«</a></li>';
                    
                    $startPage = max(1, $pagina - 2);
                    $endPage = min($totalPaginas, $pagina + 2);
                    
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $urlBase . '&pagina=1">1</a></li>';
                        if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $active = ($i == $pagina) ? 'active' : '';
                        echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $urlBase . '&pagina=' . $i . '">' . $i . '</a></li>';
                    }
                    
                    if ($endPage < $totalPaginas) {
                        if ($endPage < $totalPaginas - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $urlBase . '&pagina=' . $totalPaginas . '">' . $totalPaginas . '</a></li>';
                    }
                    
                    $nextDisabled = ($pagina >= $totalPaginas) ? 'disabled' : '';
                    echo '<li class="page-item ' . $nextDisabled . '"><a class="page-link" href="' . $urlBase . '&pagina=' . ($pagina + 1) . '">»</a></li>';
                    ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir esta inspeção? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" action="">
                    <input type="hidden" id="deleteId" name="id" value="">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="apontamentoTooltip" class="apontamento-tooltip"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DO MODAL (Mantida) ---
    const deleteModalElement = document.getElementById('deleteModal');
    if (deleteModalElement) {
        const deleteModal = new bootstrap.Modal(deleteModalElement);
        const deleteForm = document.getElementById('deleteForm');
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                const id = this.getAttribute('data-id');
                if (deleteForm) {
                    deleteForm.action = 'index.php?route=inspecoes&action=delete&id=' + id;
                }
                deleteModal.show();
            });
        });
    }

    // --- LÓGICA DO BOTÃO PDF (Mantida) ---
    const empresaSelectPdf = document.getElementById("empresa_id");
    const btnGerarPdf = document.getElementById("btnGerarPdf");

    function togglePdfButton() {
        if (empresaSelectPdf && btnGerarPdf) {
            btnGerarPdf.disabled = (empresaSelectPdf.value === "");
        }
    }

    if (empresaSelectPdf && btnGerarPdf) {
        togglePdfButton();
        empresaSelectPdf.addEventListener("change", togglePdfButton);
        btnGerarPdf.addEventListener("click", function() {
            const empresaId = empresaSelectPdf.value;
            if (empresaId) {
                const pdfUrl = `index.php?route=inspecoes&action=gerarPdf&empresa_id=${empresaId}`;
                window.open(pdfUrl, "_blank");
            } else {
                alert("Por favor, selecione uma empresa para gerar o PDF.");
            }
        });
    }
    
    // --- FILTROS EM CASCATA (Mantido) ---
    const empresaSelectFiltro = document.getElementById('empresa_id');
    const setorSelectFiltro = document.getElementById('setor_id');
    const localSelectFiltro = document.getElementById('local_id');

    empresaSelectFiltro.addEventListener('change', function() {
        const empresaId = this.value;
        setorSelectFiltro.innerHTML = '<option value="">Selecione uma Empresa</option>';
        setorSelectFiltro.disabled = true;
        localSelectFiltro.innerHTML = '<option value="">Selecione um Setor</option>';
        localSelectFiltro.disabled = true;
        if (empresaId) {
            setorSelectFiltro.disabled = false;
            setorSelectFiltro.innerHTML = '<option value="">Carregando...</option>';
            fetch(`index.php?route=api&action=getSetoresPorEmpresa&empresa_id=${empresaId}`)
                .then(response => response.json())
                .then(data => {
                    setorSelectFiltro.innerHTML = '<option value="">Todos</option>';
                    if (data.success && data.setores) {
                        data.setores.forEach(setor => {
                            const option = new Option(setor.nome, setor.id);
                            setorSelectFiltro.add(option);
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar setores:', error));
        }
    });

    setorSelectFiltro.addEventListener('change', function() {
        const empresaId = empresaSelectFiltro.value;
        const setorId = this.value;
        localSelectFiltro.innerHTML = '<option value="">Selecione um Setor</option>';
        localSelectFiltro.disabled = true;
        if (setorId) {
            localSelectFiltro.disabled = false;
            localSelectFiltro.innerHTML = '<option value="">Carregando...</option>';
            fetch(`index.php?route=api&action=getLocaisPorSetor&empresa_id=${empresaId}&setor_id=${setorId}`)
                .then(response => response.json())
                .then(data => {
                    localSelectFiltro.innerHTML = '<option value="">Todos</option>';
                    if (data.success && data.locais) {
                        data.locais.forEach(local => {
                            const option = new Option(local.nome, local.id);
                            localSelectFiltro.add(option);
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar locais:', error));
        }
    });
    
    // --- LÓGICA DAS PARTÍCULAS (Mantida) ---
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles', { /* ...configuração das partículas... */ });
    }
    
    // --- LÓGICA FINAL ATUALIZADA PARA O TOOLTIP DO APONTAMENTO ---
    const tooltip = document.getElementById('apontamentoTooltip');
    const rows = document.querySelectorAll('#inspecoesTableBody tr');

    rows.forEach(row => {
        row.addEventListener('mousemove', (e) => {
            // Verifica se o cursor está sobre as colunas de Ações, Tipo ou Status
            const isOverProtectedColumn = e.target.closest('.actions-column') || 
                                          e.target.closest('.tipo-column') || 
                                          e.target.closest('.status-column');
            
            if (isOverProtectedColumn) {
                tooltip.style.display = 'none';
                tooltip.style.opacity = 0;
                return; // Interrompe a execução para não mostrar o tooltip
            }

            const apontamentoText = row.getAttribute('data-apontamento');
            if (apontamentoText && tooltip) {
                tooltip.textContent = apontamentoText;
                tooltip.style.display = 'block';
                tooltip.style.left = `${e.pageX + 15}px`;
                tooltip.style.top = `${e.pageY + 15}px`;
                tooltip.style.opacity = 1;
            }
        });
        
        // Evento para ESCONDER o tooltip quando o mouse sai da linha
        row.addEventListener('mouseleave', () => {
            if (tooltip) {
                tooltip.style.display = 'none';
                tooltip.style.opacity = 0;
            }
        });
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>
