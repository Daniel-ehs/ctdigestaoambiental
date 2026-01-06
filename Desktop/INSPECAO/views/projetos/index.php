<?php
/**
 * View para listagem de projetos
 * ARQUIVO CORRIGIDO: Modais movidos para fora da tabela e adicionado tooltip de descrição na linha.
 */

// Verificar se usuário está autenticado
if (!isset($_SESSION["user_id"])) {
    redirect("index.php?route=login");
}

// Incluir header
include "views/templates/header.php";

// Obter filtros atuais
$filtroEmpresaId = htmlspecialchars($_GET["empresa_id"] ?? "");
$filtroStatus = htmlspecialchars($_GET["status"] ?? "");
$filtroPrazoInicio = htmlspecialchars($_GET["prazo_inicio"] ?? "");
$filtroPrazoFim = htmlspecialchars($_GET["prazo_fim"] ?? "");
$paginaAtual = $pagina;
?>

<!-- CSS -->
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
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.table th {
    background: #f0f4f0;
    color: #28a745;
    font-weight: 600;
    font-size: 1rem;
}

.table tbody tr {
    background: #fff;
    transition: background 0.3s ease;
}

.table tbody tr:hover {
    background: #f0f4f0;
}

/* NOVO: Estilo para a bolinha de status */
.status-dot {
    height: 15px;
    width: 15px;
    border-radius: 50%;
    display: inline-block;
    cursor: pointer;
    position: relative;
}

.status-dot::after {
    content: attr(data-status);
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s, visibility 0.2s;
    z-index: 10000;
}

.status-dot:hover::after {
    opacity: 1;
    visibility: visible;
}

/* NOVO: Estilo para o tooltip da descrição */
.row-tooltip {
    position: fixed;
    background-color: rgba(0, 0, 0, 0.85);
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    z-index: 10001;
    pointer-events: none;
    display: none;
    max-width: 400px;
    word-wrap: break-word;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.95rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: #fff;
    border: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #fff;
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #333;
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: #fff;
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: #fff;
    border: none;
}

.btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn:active {
    transform: scale(0.95);
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
    font-size: 10px;
    line-height: 1;
}

.btn-group .btn:not(.disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 5px 10px rgba(0,0,0,0.15);
    filter: brightness(1.15);
}

.btn-group .btn:not(.disabled):active {
    transform: scale(0.95);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group .btn {
    animation: pop 0.3s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
}

.btn-group .btn:not(.disabled):hover {
    animation: none;
}

.actions-column {
    min-width: 110px;
}

.pagination .page-link {
    border-radius: 8px;
    margin: 0 3px;
    color: #28a745;
    transition: all 0.3s ease;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #28a745, #52c41a);
    border-color: #28a745;
    color: #fff;
}

.pagination .page-link:hover {
    background: #f0f4f0;
    transform: scale(1.05);
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.modal-content {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.modal-header {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: #fff;
    border-radius: 10px 10px 0 0;
}

.modal-footer {
    border-top: none;
}

.modal {
    z-index: 9999;
}

@keyframes pop {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .card {
        margin: 1rem;
    }
    .card-body {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .top-bar {
        padding: 0.5rem 1rem;
    }
    .btn-group {
        gap: 2px;
    }
    .btn-group .btn {
        width: 22px;
        height: 22px;
        font-size: 9px;
    }
    .actions-column {
        min-width: 90px;
    }
}
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0">Projetos</h1>
    <a href="index.php?route=projetos&action=create<?php echo isset($_GET["empresa_id"]) ? "&empresa_id=" . $_GET["empresa_id"] : ""; ?>" class="btn btn-primary" title="Novo Projeto">
        <i class="fas fa-plus-circle"></i> Novo Projeto
    </a>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Filtros</h6>
    </div>
    <div class="card-body">
        <?php include "views/components/flash_messages.php"; ?>
        <form method="GET" action="index.php">
            <input type="hidden" name="route" value="projetos">
            <div class="row">
                <div class="col-md-3 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
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
                <div class="col-md-3 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.2s;">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="Em Andamento" <?= $filtroStatus == "Em Andamento" ? "selected" : "" ?>>Em Andamento</option>
                        <option value="Concluído" <?= $filtroStatus == "Concluído" ? "selected" : "" ?>>Concluído</option>
                        <option value="Cancelado" <?= $filtroStatus == "Cancelado" ? "selected" : "" ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                    <label for="prazo_inicio" class="form-label">Prazo Início</label>
                    <input type="date" class="form-control" id="prazo_inicio" name="prazo_inicio" value="<?= $filtroPrazoInicio ?>">
                </div>
                <div class="col-md-3 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.4s;">
                    <label for="prazo_fim" class="form-label">Prazo Fim</label>
                    <input type="date" class="form-control" id="prazo_fim" name="prazo_fim" value="<?= $filtroPrazoFim ?>">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary" style="--btn-index: 1;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="index.php?route=projetos" class="btn btn-secondary" style="--btn-index: 2;">
                    <i class="fas fa-eraser"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Projetos -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Projetos (Total: <?= $totalProjetos ?>)</h6>
    </div>
    <div class="card-body">
        <?php if (empty($projetos)): ?>
            <div class="alert alert-info">
                Nenhum projeto encontrado.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Descrição</th>
                            <th>Fonte</th>
                            <th>Prazo</th>
                            <th class="status-column">Status</th>
                            <th>Responsável</th>
                            <th class="actions-column">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projetos as $index => $projeto): ?>
                            <tr style="animation: fadeIn 0.6s ease-out forwards; animation-delay: <?= 0.1 * $index ?>s;" data-full-description="<?= htmlspecialchars($projeto['descricao']) ?>">
                                <td><?= $projeto["id"] ?></td>
                                <td><?= htmlspecialchars($projeto["empresa_nome"] ?? "N/A") ?></td>
                                <td><?= htmlspecialchars(substr($projeto["descricao"], 0, 10)) . (strlen($projeto["descricao"]) > 10 ? "..." : "") ?></td>
                                <td><?= htmlspecialchars($projeto["fonte"] ?? "-") ?></td>
                                <td><?= $projeto["prazo"] ? date("d/m/Y", strtotime($projeto["prazo"])) : "-" ?></td>
                                <td class="status-column text-center">
                                    <?php
                                    $status_color = '';
                                    switch ($projeto["status"]) {
                                        case "Em Andamento":
                                            $status_color = '#007bff'; // Azul
                                            break;
                                        case "Concluído":
                                            $status_color = '#28a745'; // Verde
                                            break;
                                        case "Cancelado":
                                            $status_color = '#dc3545'; // Vermelho
                                            break;
                                        default:
                                            $status_color = '#6c757d'; // Cinza
                                    }
                                    ?>
                                    <span class="status-dot" style="background-color: <?= $status_color ?>;" data-status="<?= htmlspecialchars($projeto["status"]) ?>"></span>
                                </td>
                                <td><?= htmlspecialchars($projeto["usuario_nome"]) ?></td>
                                <td class="actions-column">
                                    <div class="btn-group">
                                        <a href="index.php?route=projetos&action=view&id=<?= $projeto["id"] ?>" class="btn btn-info" title="Visualizar" style="--btn-index: 0;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?route=projetos&action=edit&id=<?= $projeto["id"] ?>" class="btn btn-warning" title="Editar" style="--btn-index: 1;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($projeto["status"] == "Em Andamento"): ?>
                                            <button type="button" class="btn btn-success" title="Concluir" data-bs-toggle="modal" data-bs-target="#concluirModal<?= $projeto["id"] ?>" style="--btn-index: 2;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" title="Cancelar" data-bs-toggle="modal" data-bs-target="#cancelarModal<?= $projeto["id"] ?>" style="--btn-index: 3;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#excluirModal<?= $projeto["id"] ?>" style="--btn-index: <?= $projeto["status"] == "Em Andamento" ? 4 : 2 ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <nav aria-label="Paginação de projetos">
                <ul class="pagination justify-content-center">
                    <?php if ($paginaAtual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?route=projetos&pagina=<?= $paginaAtual - 1 ?><?= $filtroEmpresaId ? "&empresa_id=" . $filtroEmpresaId : "" ?><?= $filtroStatus ? "&status=" . $filtroStatus : "" ?><?= $filtroPrazoInicio ? "&prazo_inicio=" . $filtroPrazoInicio : "" ?><?= $filtroPrazoFim ? "&prazo_fim=" . $filtroPrazoFim : "" ?>">Anterior</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i == $paginaAtual) ? "active" : "" ?>">
                            <a class="page-link" href="index.php?route=projetos&pagina=<?= $i ?><?= $filtroEmpresaId ? "&empresa_id=" . $filtroEmpresaId : "" ?><?= $filtroStatus ? "&status=" . $filtroStatus : "" ?><?= $filtroPrazoInicio ? "&prazo_inicio=" . $filtroPrazoInicio : "" ?><?= $filtroPrazoFim ? "&prazo_fim=" . $filtroPrazoFim : "" ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?route=projetos&pagina=<?= $paginaAtual + 1 ?><?= $filtroEmpresaId ? "&empresa_id=" . $filtroEmpresaId : "" ?><?= $filtroStatus ? "&status=" . $filtroStatus : "" ?><?= $filtroPrazoInicio ? "&prazo_inicio=" . $filtroPrazoInicio : "" ?><?= $filtroPrazoFim ? "&prazo_fim=" . $filtroPrazoFim : "" ?>">Próxima</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAIS DE AÇÃO                                          -->
<!-- ======================================================= -->
<?php if (!empty($projetos)): ?>
    <?php foreach ($projetos as $projeto): ?>
        <!-- Modal de Conclusão -->
        <div class="modal fade" id="concluirModal<?= $projeto["id"] ?>" tabindex="-1" aria-labelledby="concluirModalLabel<?= $projeto["id"] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="concluirModalLabel<?= $projeto["id"] ?>">Concluir Projeto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <form action="index.php?route=projetos&action=concluir&id=<?= $projeto["id"] ?>" method="post">
                        <div class="modal-body">
                            <p>Deseja realmente concluir este projeto?</p>
                            <div class="mb-3">
                                <label for="data_conclusao<?= $projeto["id"] ?>" class="form-label">Data de Conclusão</label>
                                <input type="date" class="form-control" id="data_conclusao<?= $projeto["id"] ?>" name="data_conclusao" value="<?= date("Y-m-d") ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Concluir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de Cancelamento -->
        <div class="modal fade" id="cancelarModal<?= $projeto["id"] ?>" tabindex="-1" aria-labelledby="cancelarModalLabel<?= $projeto["id"] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelarModalLabel<?= $projeto["id"] ?>">Cancelar Projeto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <form action="index.php?route=projetos&action=cancelar&id=<?= $projeto["id"] ?>" method="post">
                        <div class="modal-body">
                            <p>Deseja realmente cancelar este projeto?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                            <button type="submit" class="btn btn-danger">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de Exclusão -->
        <div class="modal fade" id="excluirModal<?= $projeto["id"] ?>" tabindex="-1" aria-labelledby="excluirModalLabel<?= $projeto["id"] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="excluirModalLabel<?= $projeto["id"] ?>">Excluir Projeto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <form action="index.php?route=projetos&action=delete&id=<?= $projeto["id"] ?>" method="post">
                        <div class="modal-body">
                            <p>Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- SCRIPTS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltip para descrição completa da linha
    const tooltip = document.createElement('div');
    tooltip.className = 'row-tooltip';
    document.body.appendChild(tooltip);

    const tableRows = document.querySelectorAll('#dataTable tbody tr');

    tableRows.forEach(row => {
        const fullDescription = row.dataset.fullDescription;
        if (!fullDescription) return;

        row.addEventListener('mousemove', (e) => {
            const targetCell = e.target.closest('td');

            // Verifica se o mouse está sobre uma coluna que deve desativar o tooltip
            if (targetCell && (targetCell.classList.contains('actions-column') || targetCell.classList.contains('status-column'))) {
                tooltip.style.display = 'none';
                return;
            }

            tooltip.style.display = 'block';
            tooltip.innerHTML = fullDescription;
            // Posiciona o tooltip perto do cursor do mouse
            tooltip.style.left = `${e.clientX + 15}px`;
            tooltip.style.top = `${e.clientY + 15}px`;
        });

        row.addEventListener('mouseleave', () => {
            tooltip.style.display = 'none';
        });
    });
});
</script>

<?php
// Incluir footer
include "views/templates/footer.php";
?>
