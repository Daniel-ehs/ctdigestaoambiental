<?php

// --- Bloco de Segurança e Inclusão ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está autenticado
if (!isset($_SESSION["user_id"])) {
    redirect("index.php?route=login");
}

// Incluir header
include "views/templates/header.php";

// --- Processamento de Dados ---
$planos = $planos ?? [];
$setores = $setores ?? [];
$locais = $locais ?? [];
$empresas = $empresas ?? [];
$totalPlanos = $totalPlanos ?? 0;
$totalPaginas = $totalPaginas ?? 1;
$paginaAtual = $pagina ?? 1;

$filtroEmpresaId = htmlspecialchars($_GET["empresa_id"] ?? "");
$filtroSetorId = htmlspecialchars($_GET["setor_id"] ?? "");
$filtroLocalId = htmlspecialchars($_GET["local_id"] ?? "");
$filtroDataInicio = htmlspecialchars($_GET["data_inicio"] ?? "");
$filtroDataFim = htmlspecialchars($_GET["data_fim"] ?? "");

// --- Funções Helper para buscar Nomes ---
// Mantidas como no seu original para consistência
function getSetorNomeById($setorId, $setores) {
    if (empty($setorId) || !is_array($setores)) return 'N/A';
    foreach ($setores as $setor) {
        if (isset($setor['id']) && $setor['id'] == $setorId) {
            return $setor['nome'];
        }
    }
    return 'N/A';
}

function getLocalNomeById($localId, $locais) {
    if (empty($localId) || !is_array($locais)) return 'N/A';
    foreach ($locais as $local) {
        if (isset($local['id']) && $local['id'] == $localId) {
            return $local['nome'];
        }
    }
    return 'N/A';
}

// Ordenar planos por ID em ordem decrescente (mais novo primeiro)
if (isset($planos) && is_array($planos)) {
    usort($planos, function($a, $b) {
        return $b['id'] <=> $a['id'];
    });
}
?>

<!-- Adicionando Font Awesome para os ícones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- CSS (Original do usuário com adições para novas funcionalidades) -->
<style>
 body {
  background: #f5f6f5;
  font-family: 'Poppins', sans-serif;
  color: #333;
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
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
 }

 .card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1), 0 0 20px rgba(40,167,69,0.2);
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
  color: #fff;
  border-radius: 16px 16px 0 0;
  padding: 1.5rem;
  text-align: center;
 }

 .card-body {
  padding: 2rem;
 }

 .form-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
  font-size: 1rem;
 }

 .form-select, .form-control {
  border-radius: 10px;
  border: 1px solid #ced4da;
  padding: 0.75rem;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
 }
 
 .form-select:disabled {
    background-color: #e9ecef;
    opacity: 0.7;
 }

 .form-select:focus, .form-control:focus {
  border-color: #28a745;
  box-shadow: 0 0 8px rgba(40,167,69,0.3);
  outline: none;
 }

 .table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
 }

 .table th, .table td {
  padding: 0.75rem;
  border-bottom: 1px solid #dee2e6;
  font-size: 0.9rem;
  vertical-align: middle;
 }

 .table th {
  background: #f0f4f0;
  color: #28a745;
  font-weight: 600;
  text-align: left;
 }
 
 .table td {
    text-align: left;
 }

 .table tbody tr {
  background: #fff;
  transition: background 0.3s ease;
 }

 .table tbody tr:hover {
  background: #f0f4f0;
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
 
 /* Estilo para o círculo de status */
 .status-circle {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    cursor: help;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    vertical-align: middle;
    margin: 0 auto; /* Para centralizar na célula */
 }
 
 /* Adicionado para centralizar o conteúdo do status */
 .status-column {
    text-align: center;
 }
 
 .actions-column {
    min-width: 160px; /* Espaço para 4 botões */
    text-align: center;
 }

 /* Botões de ação */
 .btn-group {
  display: flex;
  gap: 5px;
  flex-wrap: nowrap;
  justify-content: center;
 }

 .btn-group .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border: none;
  font-size: 14px;
  line-height: 1;
  padding: 0;
 }
 
 .btn-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
 }
 
 .btn-primary {
  background: linear-gradient(135deg, #28a745, #52c41a);
  color: white;
 }

 .btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  color: white;
 }
 
 .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
 }

 .btn-group .btn:not(.disabled):hover {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 5px 10px rgba(0,0,0,0.15);
  filter: brightness(1.15);
 }

 @keyframes fadeIn {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
 }

 /* --- NOVO: Estilos para o Tooltip do Apontamento --- */
 .apontamento-cell {
    cursor: help; /* Indica que há informação extra na célula */
 }
 .apontamento-tooltip {
    position: absolute;
    display: none;
    background-color: rgba(40, 40, 40, 0.95);
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    z-index: 1001; /* Acima de outros elementos */
    max-width: 350px;
    font-size: 0.85rem;
    line-height: 1.4;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    pointer-events: none; /* Impede que o tooltip interfira com o mouse */
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0">Planos de Ação</h1>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Filtros</h6>
    </div>
    <div class="card-body">
        <?php include "views/components/flash_messages.php"; ?>
        <form method="GET" action="index.php">
            <input type="hidden" name="route" value="planos">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Todas</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa["id"] ?>" <?= $filtroEmpresaId == $empresa["id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($empresa["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="setor_id" class="form-label">Setor</label>
                    <select class="form-select" id="setor_id" name="setor_id" <?= empty($filtroEmpresaId) ? 'disabled' : '' ?>>
                        <option value=""><?= empty($filtroEmpresaId) ? 'Selecione uma Empresa' : 'Todos' ?></option>
                        <?php foreach ($setores as $setor): ?>
                            <option value="<?= $setor["id"] ?>" <?= $filtroSetorId == $setor["id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($setor["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="local_id" class="form-label">Local</label>
                    <select class="form-select" id="local_id" name="local_id" <?= empty($filtroSetorId) ? 'disabled' : '' ?>>
                        <option value=""><?= empty($filtroSetorId) ? 'Selecione um Setor' : 'Todos' ?></option>
                        <?php foreach ($locais as $local): ?>
                            <option value="<?= $local["id"] ?>" <?= $filtroLocalId == $local["id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($local["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= $filtroDataInicio ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= $filtroDataFim ?>">
                </div>
                <div class="col-md-9 mb-3 d-flex align-items-end justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="index.php?route=planos" class="btn btn-secondary">
                        <i class="fas fa-eraser"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Planos -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Planos de Ação (Total: <?= $totalPlanos ?? 0 ?>)</h6>
    </div>
    <div class="card-body">
        <?php if (empty($planos)): ?>
            <div class="alert alert-info">
                Nenhum plano de ação encontrado.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="dataTable">
                    <thead>
                        <tr>
                            <th>Plano</th>
                            <th>Inspeção</th>
                            <th>Empresa</th>
                            <th>Setor</th>
                            <th>Local</th>
                            <th>Apontamento</th>
                            <th>Data</th>
                            <th class="status-column">Status</th>
                            <th class="actions-column">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planos as $plano): ?>
                            <tr>
                                <td><?= $plano["id"] ?></td>
                                <td><?= htmlspecialchars($plano["numero_inspecao"] ?? $plano["inspecao_id"]) ?></td>
                                <td><?= htmlspecialchars($plano["empresa_nome"] ?? "N/A") ?></td>
                                <td><?= htmlspecialchars($plano["setor"] ?? "N/A") ?></td>
                                <td><?= htmlspecialchars($plano["local"] ?? "N/A") ?></td>
                                <!-- CÉLULA APONTAMENTO ALTERADA -->
                                <td class="apontamento-cell" data-full-apontamento="<?= htmlspecialchars($plano["apontamento"] ?? 'Sem apontamento.') ?>">
                                    <?= htmlspecialchars(substr($plano["apontamento"], 0, 25)) . (strlen($plano["apontamento"]) > 25 ? "..." : "") ?>
                                </td>
                                <td><?= date("d/m/Y", strtotime($plano["data_registro"])) ?></td>
                                <td class="status-column">
                                    <?php
                                    $status_cor = '#ffc107'; // Amarelo (Pendente)
                                    if ($plano["status"] === "Concluído") $status_cor = '#28a745'; // Verde
                                    if ($plano["status"] === "Prazo Vencido") $status_cor = '#dc3545'; // Vermelho
                                    ?>
                                    <span class="status-circle" style="background-color: <?= $status_cor ?>;" title="<?= htmlspecialchars($plano["status"]) ?>"></span>
                                </td>
                                <td class="actions-column">
                                    <div class="btn-group">
                                        <a href="index.php?route=planos&action=view&id=<?= $plano["id"] ?>" class="btn btn-info" title="Visualizar"><i class="fas fa-eye"></i></a>
                                        <a href="index.php?route=planos&action=pdf&id=<?= $plano["id"] ?>" class="btn btn-secondary" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                        <a href="index.php?route=inspecoes&action=view&id=<?= $plano["inspecao_id"] ?>" class="btn btn-primary" title="Inspeção"><i class="fas fa-clipboard-check"></i></a>
                                        
                                        <!-- Botão que abre o modal -->
                                        <button type="button" class="btn btn-danger btn-delete" data-id="<?= $plano['id'] ?>" title="Excluir Plano">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginação -->
            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination justify-content-center">
                        <?php
                        $urlBase = 'index.php?route=planos';
                        $queryParams = [];
                        if (!empty($filtroEmpresaId)) $queryParams['empresa_id'] = $filtroEmpresaId;
                        if (!empty($filtroSetorId)) $queryParams['setor_id'] = $filtroSetorId;
                        if (!empty($filtroLocalId)) $queryParams['local_id'] = $filtroLocalId;
                        if (!empty($filtroDataInicio)) $queryParams['data_inicio'] = $filtroDataInicio;
                        if (!empty($filtroDataFim)) $queryParams['data_fim'] = $filtroDataFim;
                        if (!empty($queryParams)) $urlBase .= '&' . http_build_query($queryParams);
                        $prevDisabled = ($paginaAtual <= 1) ? 'disabled' : '';
                        echo '<li class="page-item ' . $prevDisabled . '"><a class="page-link" href="' . $urlBase . '&pagina=' . ($paginaAtual - 1) . '">«</a></li>';
                        $startPage = max(1, $paginaAtual - 2);
                        $endPage = min($totalPaginas, $paginaAtual + 2);
                        if ($endPage - $startPage + 1 < 5) {
                            if ($startPage == 1) $endPage = min($totalPaginas, $startPage + 4);
                            elseif ($endPage == $totalPaginas) $startPage = max(1, $endPage - 4);
                        }
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $urlBase . '&pagina=1">1</a></li>';
                            if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $active = ($i == $paginaAtual) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $urlBase . '&pagina=' . $i . '">' . $i . '</a></li>';
                        }
                        if ($endPage < $totalPaginas) {
                            if ($endPage < $totalPaginas - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $urlBase . '&pagina=' . $totalPaginas . '">' . $totalPaginas . '</a></li>';
                        }
                        $nextDisabled = ($paginaAtual >= $totalPaginas) ? 'disabled' : '';
                        echo '<li class="page-item ' . $nextDisabled . '"><a class="page-link" href="' . $urlBase . '&pagina=' . ($paginaAtual + 1) . '">»</a></li>';
                        ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deletePlanoModal" tabindex="-1" aria-labelledby="deletePlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlanoModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Atenção: Esta ação é irreversível!
                <br>
                Tem certeza que deseja excluir este plano de ação?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deletePlanoForm" method="post" action="">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Div para o Tooltip -->
<div id="apontamentoTooltip" class="apontamento-tooltip"></div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function() {
    // Partículas de fundo (código original mantido)
    if (document.getElementById('particles')) {
      particlesJS('particles', { /* ... seu código de partículas ... */ });
    }

    // --- FILTROS EM CASCATA (Mantido) ---
    const empresaSelect = document.getElementById('empresa_id');
    const setorSelect = document.getElementById('setor_id');
    const localSelect = document.getElementById('local_id');
    
    empresaSelect.addEventListener('change', function() {
        const empresaId = this.value;
        localSelect.innerHTML = '<option value="">Selecione um Setor</option>';
        localSelect.disabled = true;
        if (empresaId) {
            setorSelect.innerHTML = '<option value="">Carregando...</option>';
            setorSelect.disabled = false;
            fetch(`index.php?route=api&action=getSetoresPorEmpresa&empresa_id=${empresaId}`)
                .then(response => response.json())
                .then(data => {
                    setorSelect.innerHTML = '<option value="">Todos</option>';
                    if (data.success && data.setores) {
                        data.setores.forEach(setor => {
                            const option = new Option(setor.nome, setor.id);
                            setorSelect.add(option);
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar setores:', error));
        } else {
            setorSelect.innerHTML = '<option value="">Selecione uma Empresa</option>';
            setorSelect.disabled = true;
        }
    });

    setorSelect.addEventListener('change', function() {
        const empresaId = empresaSelect.value;
        const setorId = this.value;
        localSelect.innerHTML = '<option value="">Selecione um Local</option>';
        if (setorId) {
            localSelect.disabled = false;
            localSelect.innerHTML = '<option value="">Carregando...</option>';
            fetch(`index.php?route=api&action=getLocaisPorSetor&empresa_id=${empresaId}&setor_id=${setorId}`)
                .then(response => response.json())
                .then(data => {
                    localSelect.innerHTML = '<option value="">Todos</option>';
                    if (data.success && data.locais) {
                        data.locais.forEach(local => {
                            const option = new Option(local.nome, local.id);
                            localSelect.add(option);
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar locais:', error));
        } else {
            localSelect.disabled = true;
        }
    });

    // --- LÓGICA DO MODAL (Mantida) ---
    const deleteModalElement = document.getElementById('deletePlanoModal');
    if (deleteModalElement) {
        const deleteModal = new bootstrap.Modal(deleteModalElement);
        const deleteForm = document.getElementById('deletePlanoForm');
        
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevenir ação padrão
                e.stopPropagation(); // Parar propagação do evento
                const planoId = this.getAttribute('data-id');
                if (deleteForm) {
                    deleteForm.action = `index.php?route=planos&action=delete&id=${planoId}`;
                }
                deleteModal.show();
            });
        });
    }

    // --- NOVA LÓGICA PARA O TOOLTIP DO APONTAMENTO (Página Planos de Ação) ---
    const planoTooltip = document.getElementById('apontamentoTooltip');
    const apontamentoCells = document.querySelectorAll('.apontamento-cell');

    apontamentoCells.forEach(cell => {
        // Evento para MOSTRAR e POSICIONAR o tooltip
        cell.addEventListener('mouseenter', (e) => {
            const fullText = cell.getAttribute('data-full-apontamento');
            if (fullText && planoTooltip) {
                planoTooltip.textContent = fullText;
                planoTooltip.style.display = 'block';
                planoTooltip.style.opacity = 1;
            }
        });

        // Evento para MOVER o tooltip com o mouse
        cell.addEventListener('mousemove', (e) => {
            if (planoTooltip) {
                planoTooltip.style.left = `${e.pageX + 15}px`;
                planoTooltip.style.top = `${e.pageY + 15}px`;
            }
        });
        
        // Evento para ESCONDER o tooltip
        cell.addEventListener('mouseleave', () => {
            if (planoTooltip) {
                planoTooltip.style.display = 'none';
                planoTooltip.style.opacity = 0;
            }
        });
    });
 });
</script>

<?php include "views/templates/footer.php"; ?>
