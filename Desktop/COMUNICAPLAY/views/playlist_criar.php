<?php

require_once __DIR__ . '/../controllers/TelaController.php';
require_once __DIR__ . '/../controllers/MidiaController.php';

$telaController = new TelaController();
$midiaController = new MidiaController();

// Busca telas disponíveis
$telasResult = $telaController->getAll();
$telas = $telasResult['success'] ? $telasResult['telas'] : [];

// Busca mídias disponíveis
$midiasResult = $midiaController->getAll();
$midias = $midiasResult['success'] ? $midiasResult['midias'] : [];

$pageTitle = 'Nova Playlist';
$breadcrumb = [];

ob_start();
?>

<style>
/* Estilos modernos para a página de criar playlist */
.modern-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.modern-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.5rem;
    color: white;
    border-radius: 16px 16px 0 0;
}

.modern-card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.25rem;
}

.modern-btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.modern-btn:hover::before {
    left: 100%;
}

.modern-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.modern-btn-outline-primary {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    border: none;
}

.modern-btn-outline-primary:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
}

.modern-btn-outline-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
    border: none;
}

.modern-btn-outline-secondary:hover {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(160, 174, 192, 0.4);
}

.modern-btn-outline-info {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    color: white;
    border: none;
}

.modern-btn-outline-info:hover {
    background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
}

.form-label {
    font-weight: 500;
    color: #4a5568;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
}

.modern-card-header h6 {
    color: white !important;
}

.card-body h6 i {
    color: #667eea;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}

.text-success strong {
    color: #38a169;
}

.text-success i {
    color: #38a169;
}

.d-flex.align-items-center.justify-content-between.p-2 {
    background: #f7fafc;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.d-flex.align-items-center.justify-content-between.p-2:hover {
    background: #edf2f7;
    transform: translateY(-1px);
}

.d-flex.align-items-center.justify-content-between.p-2 .btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.d-flex.align-items-center.justify-content-between.p-2 .btn:hover {
    transform: scale(1.1);
}

.d-flex.align-items-center.justify-content-between.p-2 .btn-outline-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
}

.d-flex.align-items-center.justify-content-between.p-2 .btn-outline-danger:hover {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card modern-card">
            <div class="card-header modern-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar Nova Playlist
                </h5>
            </div>
            <div class="card-body">
                <form id="formNovaPlaylist">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Informações Básicas
                            </h6>
                            
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Playlist *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required 
                                       placeholder="Ex: Playlist Manhã">
                            </div>
                            
                            <div class="mb-3">
                                <label for="tela_id" class="form-label">Tela *</label>
                                <select class="form-select" id="tela_id" name="tela_id" required>
                                    <option value="">Selecione uma tela</option>
                                    <?php foreach ($telas as $tela): ?>
                                        <option value="<?= $tela['id'] ?>">
                                            <?= sanitize($tela['nome']) ?> 
                                            (<?= sanitize($tela['localizacao']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-calendar me-2"></i>
                                Agendamento
                            </h6>
                            
                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Data/Hora de Início *</label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" required>
                                <div class="form-text text-danger" style="font-size: 0.85em;">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Sem restrições de horário - pode ser definida para qualquer momento!
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="data_fim" class="form-label">Data/Hora de Fim *</label>
                                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" required>
                            </div>

                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-collection-play me-2"></i>
                                Mídias da Playlist
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card modern-card">
                                        <div class="card-header modern-card-header">
                                            <h6 class="mb-0">Mídias Disponíveis</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            <div id="midiasDisponiveis">
                                                <?php if (empty($midias)): ?>
                                                    <p class="text-muted text-center">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Nenhuma mídia disponível. 
                                                        <a href="midias.php">Adicione mídias primeiro</a>.
                                                    </p>
                                                <?php else: ?>
                                                    <?php foreach ($midias as $midia): ?>
                                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2" data-id="<?= $midia['id'] ?>">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <?php if ($midia['tipo'] === 'video'): ?>
                                                                        <i class="bi bi-play-circle text-primary"></i>
                                                                    <?php elseif ($midia['tipo'] === 'imagem'): ?>
                                                                        <i class="bi bi-image text-success"></i>
                                                                    <?php elseif ($midia['tipo'] === 'youtube'): ?>
                                                                        <i class="bi bi-youtube text-danger"></i>
                                                                    <?php else: ?>
                                                                        <i class="bi bi-link text-info"></i>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-medium"><?= sanitize($midia['nome']) ?></div>
                                                                    <small class="text-muted"><?= ucfirst($midia['tipo']) ?></small>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarMidia(<?= $midia['id'] ?>)">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card modern-card">
                                        <div class="card-header modern-card-header">
                                            <h6 class="mb-0">Mídias da Playlist</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            <div id="midiasPlaylist">
                                                <p class="text-muted text-center">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    Adicione mídias à playlist
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="playlists.php" class="btn modern-btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Voltar
                                </a>
                                <button type="submit" class="btn modern-btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Criar Playlist
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let midiasPlaylist = [];

// Adicionar mídia à playlist
function adicionarMidia(midiaId) {
    const midiaElement = document.querySelector(`[data-id="${midiaId}"]`);
    if (!midiaElement) return;
    
    // Verifica se já foi adicionada
    if (midiasPlaylist.includes(midiaId)) {
        alert('Esta mídia já foi adicionada à playlist.');
        return;
    }
    
    // Adiciona ao array
    midiasPlaylist.push(midiaId);
    
    // Clona o elemento para a playlist
    const clone = midiaElement.cloneNode(true);
    const button = clone.querySelector('button');
    button.className = 'btn btn-sm btn-outline-danger';
    button.innerHTML = '<i class="bi bi-trash"></i>';
    button.onclick = () => removerMidia(midiaId);
    
    // Adiciona à lista da playlist
    const playlistContainer = document.getElementById('midiasPlaylist');
    if (playlistContainer.children.length === 1 && playlistContainer.children[0].tagName === 'P') {
        playlistContainer.innerHTML = '';
    }
    playlistContainer.appendChild(clone);
    
    // Desabilita o botão original
    midiaElement.querySelector('button').disabled = true;
}

// Remover mídia da playlist
function removerMidia(midiaId) {
    // Remove do array
    midiasPlaylist = midiasPlaylist.filter(id => id !== midiaId);
    
    // Remove da lista visual
    const playlistContainer = document.getElementById('midiasPlaylist');
    const element = playlistContainer.querySelector(`[data-id="${midiaId}"]`);
    if (element) {
        element.remove();
    }
    
    // Se ficou vazio, mostra mensagem
    if (playlistContainer.children.length === 0) {
        playlistContainer.innerHTML = '<p class="text-muted text-center"><i class="bi bi-info-circle me-2"></i>Adicione mídias à playlist</p>';
    }
    
    // Reabilita o botão original
    const originalElement = document.querySelector(`#midiasDisponiveis [data-id="${midiaId}"] button`);
    if (originalElement) {
        originalElement.disabled = false;
    }
}

// Ativar agora (próxima 1 hora)
function ativarAgora() {
    const now = new Date();
    const agora = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
    const em1hora = new Date(now.getTime() + 3600000 - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
    
    document.getElementById('data_inicio').value = agora;
    document.getElementById('data_fim').value = em1hora;
}


// Ativar hoje (8h às 18h)
function ativarHoje() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    
    const inicio = `${year}-${month}-${day}T08:00`;
    const fim = `${year}-${month}-${day}T18:00`;
    
    document.getElementById('data_inicio').value = inicio;
    document.getElementById('data_fim').value = fim;
}

// Submissão do formulário
document.getElementById('formNovaPlaylist').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Adiciona as mídias ao FormData
    formData.append('midias', JSON.stringify(midiasPlaylist));
    
    // Validações básicas
    if (!formData.get('nome')) {
        alert('Nome da playlist é obrigatório.');
        return;
    }
    
    if (!formData.get('tela_id')) {
        alert('Selecione uma tela.');
        return;
    }
    
    if (!formData.get('data_inicio')) {
        alert('Data de início é obrigatória.');
        return;
    }
    
    if (!formData.get('data_fim')) {
        alert('Data de fim é obrigatória.');
        return;
    }
    
    // Validação de datas
    const dataInicio = new Date(formData.get('data_inicio'));
    const dataFim = new Date(formData.get('data_fim'));
    
    if (dataFim <= dataInicio) {
        alert('Data de fim deve ser posterior à data de início.');
        return;
    }
    
    // Mostrar loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Criando...';
    
    fetch('api/playlists/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Playlist criada com sucesso! Redirecionando...');
            window.location.href = 'playlists.php';
        } else {
            alert('❌ Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao criar playlist.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// ✅ SEM VALIDAÇÃO MIN - PERMITE QUALQUER HORÁRIO!
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Formulário carregado SEM restrições de horário!');
    
    // NÃO define min nos campos - permite qualquer horário
    // document.getElementById('data_inicio').min = ''; // REMOVIDO
    // document.getElementById('data_fim').min = '';     // REMOVIDO
    
    // Apenas configura que data fim deve ser >= data início
    document.getElementById('data_inicio').addEventListener('change', function() {
        const dataInicio = this.value;
        if (dataInicio) {
            document.getElementById('data_fim').min = dataInicio;
        }
    });
    
    // Preenche automaticamente com horário atual + 1 hora
    ativarAgora();
    
    console.log('✅ Campos de data configurados sem restrições!');
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>