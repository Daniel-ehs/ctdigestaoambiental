<?php
// Incluir cabeçalho e verificar autenticação
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}
?>

<style>
/* Reset e base */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333333;
}

/* Estilo para a bolinha colorida do Tipo de Apontamento */
.tipo-bolinha {
    display: inline-block;
    width: 10px; /* Tamanho da bolinha */
    height: 10px; /* Tamanho da bolinha */
    border-radius: 50%; /* Transforma o span em um círculo */
    vertical-align: middle; /* Alinha a bolinha com o texto */
}

/* Estilo para reduzir a fonte da tabela de Próximos Vencimentos */
#tabela-vencimentos {
    font-size: 0.7rem; /* Fonte ainda menor, conforme solicitado. */
    white-space: nowrap;
    vertical-align: middle;
}
#tabela-vencimentos th,
#tabela-vencimentos td {
    padding: 0.3rem 0.5rem;  
}
#tabela-vencimentos tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}
#tabela-vencimentos tbody tr:hover {
    background-color: #e9ecef;
}

/* Barra superior */
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

/* Cabeçalho do card */
.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: white;
    padding: 0.75rem 1.25rem;
    font-weight: 600;
}

/* ================================================= */
/* ESTILOS DE BOTÕES MODERNIZADOS                    */
/* ================================================= */

/* Botões gerais */
.btn {
    position: relative;
    border-radius: 10px;
    border: none;
    transition: all 0.3s ease;
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 4px 8px rgba(0,0,0,0.15);
}

.btn:hover {
    transform: translateY(-2px) scale(1.05);
    filter: brightness(1.15);
}

.btn:active {
    transform: translateY(1px) scale(0.98);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2), 0 2px 4px rgba(0,0,0,0.15);
}

/* NOVAS CORES E GRADIENTES VIBRANTES */
.btn-teal {
    background-image: linear-gradient(135deg, #28e0ab 0%, #20c997 100%);
}
.btn-teal:hover {
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 6px 15px rgba(32, 201, 151, 0.4);
}

.btn-purple {
    background-image: linear-gradient(135deg, #8a5fcf 0%, #6f42c1 100%);
}
.btn-purple:hover {
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 6px 15px rgba(111, 66, 193, 0.4);
}

.btn-indigo {
    background-image: linear-gradient(135deg, #6a75ff 0%, #4f5bff 100%);
}
.btn-indigo:hover {
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 6px 15px rgba(79, 91, 255, 0.4);
}

.btn-orange {
    background-image: linear-gradient(135deg, #ff9a40 0%, #fd7e14 100%);
}
.btn-orange:hover {
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 6px 15px rgba(253, 126, 20, 0.4);
}

.btn-slate {
    background-image: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}
.btn-slate:hover {
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 6px 15px rgba(73, 80, 87, 0.4);
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
}

.acoes-rapidas .btn {
    font-size: 0.8rem;
    white-space: nowrap; 
    padding: 0.5rem 0.75rem;
}

/* Estilos originais dos cards de indicadores */
.bg-primary { background-color: #007bff !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-primary-dark { background-color: rgba(0, 0, 0, 0.15); }
.bg-warning-dark { background-color: rgba(0, 0, 0, 0.15); }
.bg-success-dark { background-color: rgba(0, 0, 0, 0.15); }
.bg-danger-dark { background-color: rgba(0, 0, 0, 0.15); }
.opacity-50 { opacity: 0.5; }

/* Responsividade */
@media (max-width: 576px) {
    .top-bar { padding: 0.5rem 1rem; }
}
@media (max-width: 767px) {
    .card-body form .col-md-4 { margin-top: 10px; }
}

/* ================================================= */
/* ESTILIZAÇÃO DO MODAL DE RELATÓRIO                 */
/* ================================================= */
#modalExcelAbertos .modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
#modalExcelAbertos .modal-header {
    background: linear-gradient(135deg, #fd7e14, #ff9a40);
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    border-bottom: none;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
#modalExcelAbertos .modal-header .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
#modalExcelAbertos .modal-body .form-label {
    font-weight: 600;
}
#modalExcelAbertos .modal-body .form-select {
    border-radius: 8px;
}
#modalExcelAbertos .modal-footer {
    border-top: none;
    padding: 1rem 1.5rem;
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Dashboard</h1>
</div>

<div class="container-fluid px-4">
    <div class="row mt-3 mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrar por Empresa
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="route" value="dashboard">
                        <div class="row">
                            <div class="col-md-8">
                                <select name="empresa_id" class="form-select">
                                    <option value="">Todas as Empresas</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?php echo $empresa['id']; ?>" <?php echo (isset($_GET['empresa_id']) && $_GET['empresa_id'] == $empresa['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($empresa['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row g-2">
        <!-- Cards de Indicadores -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h5 mb-0 font-weight-bold">Inspeções</div>
                            <div class="h2 mb-0 font-weight-bold"><?php echo $totalInspecoes ?? 0; ?></div>
                        </div>
                        <div><i class="fas fa-clipboard-list fa-3x opacity-50"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-primary-dark">
                    <a class="small text-white stretched-link" href="index.php?route=inspecoes<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>"></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h5 mb-0 font-weight-bold">Em Aberto</div>
                            <div class="h2 mb-0 font-weight-bold"><?php echo $emAberto ?? 0; ?></div>
                        </div>
                        <div><i class="fas fa-clock fa-3x opacity-50"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-warning-dark">
                    <a class="small text-white stretched-link" href="index.php?route=inspecoes&status=Em Aberto<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>"></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h5 mb-0 font-weight-bold">Concluídas</div>
                            <div class="h2 mb-0 font-weight-bold"><?php echo $concluidas ?? 0; ?></div>
                        </div>
                        <div><i class="fas fa-check-circle fa-3x opacity-50"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-success-dark">
                    <a class="small text-white stretched-link" href="index.php?route=inspecoes&status=Concluído<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>"></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h5 mb-0 font-weight-bold">Prazo Vencido</div>
                            <div class="h2 mb-0 font-weight-bold"><?php echo $prazoVencido ?? 0; ?></div>
                        </div>
                        <div><i class="fas fa-exclamation-triangle fa-3x opacity-50"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-danger-dark">
                    <a class="small text-white stretched-link" href="index.php?route=inspecoes&status=Prazo Vencido<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>"></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-chart-bar me-1"></i>Apontamentos por Setor</div>
                <div class="card-body"><canvas id="graficoSetores" width="100%" height="40"></canvas></div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-clock me-1"></i>Próximos Vencimentos (7 dias)</div>
                <div class="card-body">
                    <?php if (!empty($proximosVencimentos)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm" id="tabela-vencimentos">
                                <thead>
                                    <tr>
                                        <th>Nº</th><th>Empresa</th><th>Setor</th><th>Local</th><th>Tipo</th><th>Prazo</th><th>Dias</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximosVencimentos as $vencimento): ?>
                                    <tr class="<?php echo $vencimento['dias_restantes'] <= 1 ? 'table-danger' : ($vencimento['dias_restantes'] <= 3 ? 'table-warning' : ''); ?>" 
                                        onclick="window.location.href='index.php?route=inspecoes&action=view&id=<?php echo $vencimento['id']; ?>'">
                                        <td><?php echo htmlspecialchars($vencimento['numero_inspecao']); ?></td>
                                        <td><?php echo htmlspecialchars($vencimento['empresa_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($vencimento['setor_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($vencimento['local_nome']); ?></td>
                                        <td><span class="tipo-bolinha" style="background-color: <?php echo htmlspecialchars($vencimento['tipo_cor']); ?>" title="<?php echo htmlspecialchars($vencimento['tipo_nome']); ?>"></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($vencimento['prazo'])); ?></td>
                                        <td><span class="badge bg-<?php echo $vencimento['dias_restantes'] <= 1 ? 'danger' : ($vencimento['dias_restantes'] <= 3 ? 'warning' : 'info'); ?>"><?php echo $vencimento['dias_restantes']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>Nenhum vencimento nos próximos 7 dias!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-chart-pie me-1"></i>Apontamentos por Tipo</div>
                <div class="card-body"><canvas id="graficoTipos" width="100%" height="40"></canvas></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-bolt me-1"></i>Ações Rápidas</div>
                <div class="card-body">
                    <div class="row acoes-rapidas g-2"> 
                        <div class="col-12 col-md-4 col-lg">
                            <a href="index.php?route=inspecoes&action=create<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>" class="btn btn-teal btn-sm w-100"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Inspecionar</a>
                        </div>
                        <div class="col-12 col-md-4 col-lg">
                            <a href="index.php?route=projetos&action=create<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>" class="btn btn-purple btn-sm w-100"><i class="fa-solid fa-rocket me-2"></i>Novo Projeto</a>
                        </div>
                        <div class="col-12 col-md-4 col-lg">
                            <a href="index.php?route=relatorios&action=semanal<?php echo isset($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>" class="btn btn-indigo btn-sm w-100"><i class="fa-solid fa-calendar-week me-2"></i>Relatório</a>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <button type="button" class="btn btn-orange btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalExcelAbertos"><i class="fa-solid fa-bell me-2"></i>Em Aberto</button>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <a href="index.php?route=painel&action=placa<?php echo isset($_GET['empresa_id']) && !empty($_GET['empresa_id']) ? '&empresa_id=' . $_GET['empresa_id'] : ''; ?>" class="btn btn-slate btn-sm w-100"><i class="fa-solid fa-chart-pie me-2"></i>Painel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================================================= -->
<!-- MODAL PARA GERAR EXCEL (FUNCIONALIDADE CORRIGIDA) -->
<!-- ================================================= -->
<div class="modal fade" id="modalExcelAbertos" tabindex="-1" aria-labelledby="modalExcelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcelLabel"><i class="fas fa-file-excel me-2"></i>Relatório de Itens Pendentes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Selecione a empresa e os status para gerar o relatório.</p>
                <form id="formGerarExcel">
                    <div class="mb-3">
                        <label for="modal_empresa_id" class="form-label">Empresa</label>
                        <select class="form-select" id="modal_empresa_id" name="empresa_id" required>
                            <option value="" selected disabled>Selecione uma empresa...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?= htmlspecialchars($empresa['id']); ?>">
                                    <?= htmlspecialchars($empresa['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_status" class="form-label">Status dos Apontamentos</label>
                        <select class="form-select" id="modal_status" name="status[]" multiple required>
                            <option value="Em Aberto" selected>Em Aberto</option>
                            <option value="Prazo Vencido" selected>Prazo Vencido</option>
                        </select>
                        <div class="form-text">Segure Ctrl (ou Cmd em Mac) para selecionar/desselecionar.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGerarExcel" class="btn btn-success" disabled>
                    <i class="fas fa-download me-2"></i>Gerar Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Passar dados PHP para JavaScript
    const inspecoesPorSetorData = <?php echo json_encode($inspecoesPorSetor ?? []); ?>;
    const inspecoesPorTipoData = <?php echo json_encode($inspecoesPorTipo ?? []); ?>;

    // Partículas de fundo
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles', { /* ...configuração das partículas... */ });
    } else {
        console.error('particles.js não foi carregado.');
    }

    // --- Gráfico de Setores (Barras) ---
    const ctxSetores = document.getElementById('graficoSetores');
    if (ctxSetores && inspecoesPorSetorData && inspecoesPorSetorData.length > 0) {
        const labelsSetores = inspecoesPorSetorData.map(item => item.nome);
        const dataSetores = inspecoesPorSetorData.map(item => item.total);
        const backgroundColorsSetores = [
            'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(255, 206, 86, 0.7)', 
            'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)'
        ];
        const borderColorsSetores = backgroundColorsSetores.map(color => color.replace('0.7', '1'));
        new Chart(ctxSetores.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsSetores,
                datasets: [{
                    label: 'Número de Apontamentos',
                    data: dataSetores,
                    backgroundColor: backgroundColorsSetores.slice(0, labelsSetores.length),
                    borderColor: borderColorsSetores.slice(0, labelsSetores.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const setorNome = labelsSetores[elements[0].index];
                        window.location.href = `index.php?route=inspecoes&setor_nome=${encodeURIComponent(setorNome)}`;
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                }
            }
        });
    }
    
    // --- Gráfico de Tipos (Pizza) - CÓDIGO RESTAURADO ---
    const ctxTipos = document.getElementById('graficoTipos');
    if (ctxTipos && inspecoesPorTipoData && inspecoesPorTipoData.length > 0) {
        const labelsTipos = inspecoesPorTipoData.map(item => item.nome);
        const dataTipos = inspecoesPorTipoData.map(item => item.total);
        const backgroundColorsTipos = inspecoesPorTipoData.map(item => item.cor ? item.cor + 'B3' : 'rgba(150, 150, 150, 0.7)');
        const borderColorsTipos = inspecoesPorTipoData.map(item => item.cor ? item.cor : '#969696');
        new Chart(ctxTipos.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labelsTipos,
                datasets: [{
                    label: 'Número de Apontamentos',
                    data: dataTipos,
                    backgroundColor: backgroundColorsTipos,
                    borderColor: borderColorsTipos,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const tipoNome = labelsTipos[elements[0].index];
                        window.location.href = `index.php?route=inspecoes&tipo_nome=${encodeURIComponent(tipoNome)}`;
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                }
            }
        });
    }

    // ================================================================
    // LÓGICA CORRIGIDA PARA O MODAL DE GERAÇÃO DE EXCEL
    // ================================================================
    const modalEmpresaSelect = document.getElementById('modal_empresa_id');
    const modalStatusSelect = document.getElementById('modal_status');
    const btnGerarExcel = document.getElementById('btnGerarExcel');

    if (modalEmpresaSelect && btnGerarExcel && modalStatusSelect) {
        // Habilita ou desabilita o botão de gerar com base na seleção da empresa
        modalEmpresaSelect.addEventListener('change', function() {
            btnGerarExcel.disabled = this.value === '';
        });

        // Ação de clique para gerar o Excel
        btnGerarExcel.addEventListener('click', function() {
            const empresaId = modalEmpresaSelect.value;
            const statusOptions = modalStatusSelect.selectedOptions;
            const statusValues = Array.from(statusOptions).map(({ value }) => value);
            
            if (!empresaId) {
                alert('Por favor, selecione uma empresa.');
                return;
            }
            if (statusValues.length === 0) {
                alert('Por favor, selecione pelo menos um status.');
                return;
            }

	            // Constrói a URL com a action 'downloadExcel' e os status selecionados
	            let url = `index.php?route=inspecoes&action=downloadExcel&empresa_id=${empresaId}`;
            statusValues.forEach(status => {
                url += `&status[]=${encodeURIComponent(status)}`;
            });

            // Abre a URL em uma nova aba para iniciar o download
            window.open(url, '_blank');

            // Opcional: fechar o modal após clicar
            const modalEl = document.getElementById('modalExcelAbertos');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        });
    }
});
</script>

<?php include 'views/templates/footer.php'; ?>
