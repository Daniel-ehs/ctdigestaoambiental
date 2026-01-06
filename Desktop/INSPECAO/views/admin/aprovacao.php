<?php
include 'views/templates/header.php';
?>

<style>
/* Estilos Globais */
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
    font-size: 0.9rem;
    text-align: center;
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
    text-align: center;
}

.table tbody td:nth-child(5) { /* Coluna Apontamento */
    text-align: left;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Botões */
.btn {
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 0 15px rgba(40, 167, 69, 0.3);
    border: none;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

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
    width: 28px; 
    height: 28px;
    border-radius: 8px;
    font-size: 12px; 
    line-height: 1;
    padding: 0;
}

.btn-group .btn:not(.disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
    filter: brightness(1.15);
}

.actions-column, .photo-column {
    width: 120px;
    text-align: center;
}

#modalImage {
    max-width: 100%;
    max-height: 75vh;
    border-radius: 8px;
}

/* Estilo para o tooltip */
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

/* --- ESTILIZAÇÃO DO MODAL DE APROVAÇÃO --- */
#approvalModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(40, 167, 69, 0.2);
}

#approvalModal .modal-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: white;
    border-radius: 16px 16px 0 0;
    border-bottom: none;
    padding: 1.5rem;
}

#approvalModal .modal-title {
    font-weight: 700;
}

#approvalModal .modal-body {
    background-color: #f5f6f5;
    padding: 2rem;
}

#approvalModal .modal-footer {
    background-color: #ffffff;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 16px 16px;
    padding: 1.5rem;
}

#approvalModal .form-section {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

#approvalModal .form-label {
    font-weight: 600;
    color: #333;
}

#approvalModal .form-label.required::after {
    content: '*';
    color: #dc3545;
    margin-left: 4px;
}

#approvalModal .form-control, 
#approvalModal .form-select {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 0.65rem 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#approvalModal .form-control:focus, 
#approvalModal .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.25);
}

#approvalModal .btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}
</style>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Aprovação de Apontamentos</h1>
</div>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-white">Apontamentos Aguardando Aprovação</h6>
        </div>
        <div class="card-body">
            <?php if (function_exists('displayFlashMessage')) displayFlashMessage(); ?>

            <?php if (!empty($apontamentos)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Setor</th>
                                <th>Local</th>
                                <th title="Descrição completa do apontamento">Apontamento</th>
                                <th class="photo-column">Foto</th>
                                <th>Contato</th>
                                <th>Data Criação</th>
                                <th class="actions-column">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apontamentos as $apontamento): ?>
                                <tr data-apontamento="<?php echo htmlspecialchars($apontamento['apontamento']); ?>">
                                    <td><?php echo htmlspecialchars($apontamento['id']); ?></td>
                                    <td><?php echo htmlspecialchars($apontamento['empresa_nome'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($apontamento['setor_nome'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($apontamento['local_nome'] ?? 'N/A'); ?></td>
                                    <td title="<?php echo htmlspecialchars($apontamento['apontamento']); ?>">
                                        <?php echo htmlspecialchars(substr($apontamento['apontamento'], 0, 50)) . (strlen($apontamento['apontamento']) > 50 ? '...' : ''); ?>
                                    </td>
                                    <td class="photo-column">
                                        <?php if (!empty($apontamento['foto_apontamento'])): ?>
                                            <a href="#" class="btn btn-info" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#photoModal" 
                                               data-photo-url="<?php echo SITE_URL . '/uploads/fotos_pendentes/' . htmlspecialchars($apontamento['foto_apontamento']); ?>"
                                               title="Ver Foto">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $contato = [];
                                            if (!empty($apontamento['contato_nome'])) $contato[] = htmlspecialchars($apontamento['contato_nome']);
                                            if (!empty($apontamento['contato_info'])) $contato[] = htmlspecialchars($apontamento['contato_info']);
                                            echo empty($contato) ? 'Anônimo' : implode(' / ', $contato);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(formatDate($apontamento['data_criacao'])); ?></td>
                                    <td class="actions-column">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success" title="Aprovar" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approvalModal"
                                                    onclick='openApprovalModal(<?php echo htmlspecialchars(json_encode($apontamento), ENT_QUOTES, "UTF-8"); ?>)'>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <a href="index.php?route=aprovacao&action=rejeitar&id=<?php echo $apontamento['id']; ?>" class="btn btn-danger" title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>Tudo certo por aqui!</h4>
                    <p class="text-muted">Não existem apontamentos pendentes para aprovação no momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para Visualização da Foto -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Foto do Apontamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid" alt="Foto do Apontamento">
            </div>
        </div>
    </div>
</div>

<!-- Modal para Aprovação de Apontamento -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Aprovar Apontamento - Criar Inspeção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm" method="post" action="index.php?route=inspecoes&action=store" enctype="multipart/form-data">
                    <input type="hidden" id="modal_apontamento_pendente_id" name="apontamento_pendente_id" value="">
                    
                    <div class="form-section mb-4">
                        <h6 class="text-primary mb-3">Informações Gerais</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="modal_empresa_id" class="form-label required">Empresa</label>
                                <select class="form-select" id="modal_empresa_id" name="empresa_id" required>
                                    <option value="">Selecione uma empresa</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                    <option value="<?php echo $empresa['id']; ?>"><?php echo htmlspecialchars($empresa['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modal_data_apontamento" class="form-label required">Data do Apontamento</label>
                                <input type="date" class="form-control" id="modal_data_apontamento" name="data_apontamento" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modal_setor_id" class="form-label required">Setor</label>
                                <select class="form-select" id="modal_setor_id" name="setor_id" required>
                                    <option value="">Selecione uma empresa primeiro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="modal_local_id" class="form-label required">Local</label>
                                <select class="form-select" id="modal_local_id" name="local_id" required>
                                    <option value="">Selecione um setor primeiro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h6 class="text-primary mb-3">Detalhes do Apontamento</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="modal_apontamento" class="form-label required">Apontamento (Situação Encontrada)</label>
                                <textarea class="form-control" id="modal_apontamento" name="apontamento" rows="3" required readonly></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_tipo_id" class="form-label required">Tipo de Apontamento</label>
                                <select class="form-select" id="modal_tipo_id" name="tipo_id" required>
                                    <option value="">Selecione um tipo</option>
                                    <?php 
                                    require_once BASE_PATH . '/models/TipoApontamento.php';
                                    $tipoModel = new TipoApontamento();
                                    $tipos = $tipoModel->listar();
                                    foreach ($tipos as $tipo): ?>
                                    <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_risco_consequencia" class="form-label">Risco/Consequência</label>
                                <textarea class="form-control" id="modal_risco_consequencia" name="risco_consequencia" rows="2" placeholder="Descreva o risco ou consequência"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h6 class="text-primary mb-3">Foto e Resolução</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto do Apontamento</label>
                                <div id="modal_foto_preview_container" class="mb-2">
                                     <img id="modal_foto_img" src="" alt="Foto do Apontamento" class="img-thumbnail" style="max-width: 200px; display: none;">
                                </div>
                                <input type="hidden" id="modal_foto_existente" name="foto_antes_existente" value="">
                                <input type="file" class="form-control" id="modal_foto_nova" name="foto_antes" accept="image/*">
                                <small id="foto_helper_text" class="text-muted">Anexe uma nova foto ou deixe em branco para usar a original.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_resolucao_proposta" class="form-label">Resolução Proposta</label>
                                <textarea class="form-control" id="modal_resolucao_proposta" name="resolucao_proposta" rows="3" placeholder="Descreva a resolução ou ação proposta"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h6 class="text-primary mb-3">Responsáveis e Prazos</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_responsavel" class="form-label">Responsável</label>
                                <input type="text" class="form-control" id="modal_responsavel" name="responsavel" placeholder="Nome do responsável">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_prazo" class="form-label">Prazo de Resolução</label>
                                <input type="date" class="form-control" id="modal_prazo" name="prazo">
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-0">
                        <h6 class="text-primary mb-3">Observações</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="modal_observacao" class="form-label">Observações</label>
                                <textarea class="form-control" id="modal_observacao" name="observacao" rows="3" placeholder="Adicione observações adicionais"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="approvalForm" class="btn btn-success">
                    <i class="fas fa-check"></i> Aprovar e Criar Inspeção
                </button>
            </div>
        </div>
    </div>
</div>

<div id="apontamentoTooltip" class="apontamento-tooltip"></div>


<?php include 'views/templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoModal = document.getElementById('photoModal');
    if (photoModal) {
        photoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const photoUrl = button.getAttribute('data-photo-url');
            const modalImage = photoModal.querySelector('#modalImage');
            modalImage.src = photoUrl;
        });
    }

    const tooltip = document.getElementById('apontamentoTooltip');
    const rows = document.querySelectorAll('#dataTable tbody tr');
    rows.forEach(row => {
        row.addEventListener('mousemove', function(e) {
            const apontamentoText = this.getAttribute('data-apontamento');
            if (!apontamentoText || !tooltip) return;

            const targetCell = e.target.closest('td');
            if (targetCell && (targetCell.classList.contains('actions-column') || targetCell.classList.contains('photo-column'))) {
                tooltip.style.display = 'none';
                tooltip.style.opacity = '0';
                return;
            }

            tooltip.innerHTML = apontamentoText;
            tooltip.style.left = (e.pageX + 15) + 'px';
            tooltip.style.top = (e.pageY + 15) + 'px';
            tooltip.style.display = 'block';
            tooltip.style.opacity = '1';
        });

        row.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.style.display = 'none';
                tooltip.style.opacity = '0';
            }
        });
    });

    const modalEmpresaSelect = document.getElementById('modal_empresa_id');
    const modalSetorSelect = document.getElementById('modal_setor_id');
    const novaFotoInput = document.getElementById('modal_foto_nova');
    const fotoPreviewImg = document.getElementById('modal_foto_img');

    modalEmpresaSelect.addEventListener('change', () => carregarSetoresPorEmpresaModal());
    modalSetorSelect.addEventListener('change', () => carregarLocaisPorSetorModal());

    novaFotoInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPreviewImg.src = e.target.result;
                fotoPreviewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});

async function carregarSetoresPorEmpresaModal() {
    const modalEmpresaSelect = document.getElementById('modal_empresa_id');
    const modalSetorSelect = document.getElementById('modal_setor_id');
    const modalLocalSelect = document.getElementById('modal_local_id');
    const empresaId = modalEmpresaSelect.value;
    
    modalSetorSelect.innerHTML = '<option value="">Carregando...</option>';
    modalSetorSelect.disabled = true;
    modalLocalSelect.innerHTML = '<option value="">Selecione um setor primeiro</option>';
    modalLocalSelect.disabled = true;

    if (!empresaId) {
        modalSetorSelect.innerHTML = '<option value="">Selecione uma empresa primeiro</option>';
        return; // Retorna aqui para não continuar a execução
    }

    try {
        const response = await fetch(`index.php?route=api&action=getSetoresPorEmpresa&empresa_id=${empresaId}`);
        const data = await response.json();
        modalSetorSelect.innerHTML = '<option value="">Selecione um setor</option>';
        if (data.success && data.setores && data.setores.length > 0) {
            data.setores.forEach(setor => {
                const option = document.createElement('option');
                option.value = setor.id;
                option.textContent = setor.nome;
                modalSetorSelect.appendChild(option);
            });
            modalSetorSelect.disabled = false;
        } else {
             modalSetorSelect.innerHTML = `<option value="">Nenhum setor encontrado</option>`;
        }
    } catch (error) {
        console.error('Erro ao carregar setores:', error);
        modalSetorSelect.innerHTML = '<option value="">Erro ao carregar</option>';
    }
}

async function carregarLocaisPorSetorModal() {
    const modalEmpresaSelect = document.getElementById('modal_empresa_id');
    const modalSetorSelect = document.getElementById('modal_setor_id');
    const modalLocalSelect = document.getElementById('modal_local_id');
    const empresaId = modalEmpresaSelect.value;
    const setorId = modalSetorSelect.value;
    
    modalLocalSelect.innerHTML = '<option value="">Carregando...</option>';
    modalLocalSelect.disabled = true;

    if (!setorId || !empresaId) {
        modalLocalSelect.innerHTML = '<option value="">Selecione um setor</option>';
        return; // Retorna aqui para não continuar a execução
    }

    try {
        const response = await fetch(`index.php?route=api&action=getLocaisPorSetor&setor_id=${setorId}&empresa_id=${empresaId}`);
        const data = await response.json();
        modalLocalSelect.innerHTML = '<option value="">Selecione um local</option>';
        if (data.success && data.locais && data.locais.length > 0) {
            data.locais.forEach(local => {
                const option = document.createElement('option');
                option.value = local.id;
                option.textContent = local.nome;
                modalLocalSelect.appendChild(option);
            });
            modalLocalSelect.disabled = false;
        } else {
            modalLocalSelect.innerHTML = `<option value="">Nenhum local encontrado</option>`;
        }
    } catch (error) {
        console.error('Erro ao carregar locais:', error);
        modalLocalSelect.innerHTML = '<option value="">Erro ao carregar</option>';
    }
}

async function openApprovalModal(apontamento) {
    document.getElementById('approvalForm').reset();
    
    // Preenche os campos simples
    document.getElementById('modal_apontamento_pendente_id').value = apontamento.id;
    document.getElementById('modal_apontamento').value = apontamento.apontamento;
    
    const dataCriacao = new Date(apontamento.data_criacao);
    document.getElementById('modal_data_apontamento').value = dataCriacao.toISOString().split('T')[0];

    // Lida com a imagem
    const fotoPreviewImg = document.getElementById('modal_foto_img');
    const fotoExistenteInput = document.getElementById('modal_foto_existente');
    const novaFotoInput = document.getElementById('modal_foto_nova');

    novaFotoInput.value = ''; 
    
    if (apontamento.foto_apontamento) {
        fotoPreviewImg.src = '<?php echo SITE_URL; ?>/uploads/fotos_pendentes/' + apontamento.foto_apontamento;
        fotoExistenteInput.value = apontamento.foto_apontamento;
        fotoPreviewImg.style.display = 'block';
    } else {
        fotoPreviewImg.style.display = 'none';
        fotoPreviewImg.src = '';
        fotoExistenteInput.value = '';
    }
    
    // Seleciona os elementos do DOM uma vez
    const modalEmpresaSelect = document.getElementById('modal_empresa_id');
    const modalSetorSelect = document.getElementById('modal_setor_id');
    const modalLocalSelect = document.getElementById('modal_local_id');

    // Inicia a sequência de carregamento e seleção
    try {
        // Passo 1: Definir a empresa
        modalEmpresaSelect.value = apontamento.empresa_id;
        
        // Passo 2: Carregar os setores e ESPERAR a conclusão
        await carregarSetoresPorEmpresaModal();
        
        // Passo 3: Definir o setor (agora que as opções existem)
        modalSetorSelect.value = apontamento.setor_id;
        
        // Passo 4: Carregar os locais e ESPERAR a conclusão
        await carregarLocaisPorSetorModal();
        
        // Passo 5: Definir o local (agora que as opções existem)
        modalLocalSelect.value = apontamento.local_id;

    } catch (error) {
        console.error("Falha ao configurar o modal de aprovação:", error);
        alert("Ocorreu um erro ao carregar os dados para aprovação. Verifique o console para mais detalhes.");
    }
}
</script>
