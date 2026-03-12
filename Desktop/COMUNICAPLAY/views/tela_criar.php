<?php

require_once __DIR__ . '/../controllers/TelaController.php';

$telaController = new TelaController();

$pageTitle = 'Nova Tela';

ob_start();
?>

<style>
/* Estilos modernos para a página de criação de telas */
.modern-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
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

.modern-form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
    background: #f7fafc;
}

.modern-form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.modern-form-select {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
    background: #f7fafc;
}

.modern-form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.modern-btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
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

.modern-btn-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.modern-btn-secondary:hover {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(160, 174, 192, 0.4);
}

.modern-alert {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    border-left: 4px solid #38a169;
}

.modern-alert-info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    border-left: 4px solid #4299e1;
}

.modern-section-title {
    color: #4a5568;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.modern-form-switch .form-check-input {
    width: 3rem;
    height: 1.5rem;
    border-radius: 1rem;
    background-color: #e2e8f0;
    border: none;
    transition: all 0.3s ease;
}

.modern-form-switch .form-check-input:checked {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.modern-form-switch .form-check-input:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modern-preview-container {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 16px;
    padding: 2rem;
    margin: 1rem 0;
}

.modern-badge {
    border-radius: 20px;
    padding: 0.4rem 0.8rem;
    font-weight: 500;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-badge-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.modern-badge-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.modern-badge-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.preview-screen {
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.preview-screen:hover {
    transform: scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.tips-list {
    list-style: none;
    padding: 0;
}

.tips-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.tips-list li:last-child {
    border-bottom: none;
}

.tips-list li::before {
    content: "💡";
    margin-right: 0.5rem;
}
</style>

<div class="row fade-in">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar Nova Tela
                </h5>
            </div>
            <div class="card-body p-4">
                <form id="formNovaTela">
                    <div class="row">
                        <!-- Informações Básicas -->
                        <div class="col-md-6">
                            <h6 class="modern-section-title">
                                <i class="bi bi-info-circle me-2"></i>
                                Informações Básicas
                            </h6>
                            
                            <div class="mb-3">
                                <label for="nome" class="form-label fw-semibold">Nome da Tela *</label>
                                <input type="text" class="form-control modern-form-control" id="nome" name="nome" required maxlength="100">
                                <div class="form-text">Máximo 100 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao" class="form-label fw-semibold">Descrição</label>
                                <textarea class="form-control modern-form-control" id="descricao" name="descricao" rows="3" placeholder="Descrição opcional da tela"></textarea>
                            </div>
                        </div>
                        
                        <!-- Configurações Técnicas -->
                        <div class="col-md-6">
                            <h6 class="modern-section-title">
                                <i class="bi bi-gear me-2"></i>
                                Configurações Técnicas
                            </h6>
                            
                            <div class="mb-3">
                                <label for="resolucao" class="form-label fw-semibold">Resolução</label>
                                <select class="form-select modern-form-select" id="resolucao" name="resolucao">
                                    <option value="1280x720">HD - 1280x720</option>
                                    <option value="1920x1080" selected>Full HD - 1920x1080</option>
                                    <option value="3840x2160">4K - 3840x2160</option>
                                </select>
                                <div class="form-text">Resolução da tela digital</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="localizacao" class="form-label fw-semibold">Localização</label>
                                <input type="text" class="form-control modern-form-control" id="localizacao" name="localizacao" placeholder="Ex: Recepção, Sala de Espera, Hall Principal">
                                <div class="form-text">Local onde a tela está instalada</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="com_moldura" class="form-label fw-semibold">Tipo de Exibição</label>
                                <div class="d-flex align-items-center">
                                    <span class="me-3">Sem moldura</span>
                                    <div class="form-check form-switch modern-form-switch">
                                        <input class="form-check-input" type="checkbox" id="com_moldura" name="com_moldura" value="1">
                                        <label class="form-check-label ms-2" for="com_moldura">
                                            Com moldura
                                        </label>
                                    </div>
                                </div>
                                <div class="form-text">Deslize para escolher se a tela terá moldura lateral com informações adicionais</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações Adicionais -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="modern-alert modern-alert-info">
                                <h6 class="alert-heading">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Dicas para Configuração
                                </h6>
                                <ul class="tips-list mb-0">
                                    <li><strong>Nome:</strong> Use um nome descritivo e único para facilitar a identificação</li>
                                    <li><strong>Resolução:</strong> Escolha a resolução que corresponde à sua tela física</li>
                                    <li><strong>Localização:</strong> Informe o local exato para facilitar a gestão</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="text-end">
                                <a href="telas.php" class="btn modern-btn modern-btn-secondary me-2">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn modern-btn modern-btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Criar Tela
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview da Tela -->
<div class="row mt-4 fade-in">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h6 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Preview Realista da Tela
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="modern-preview-container text-center">
                    <div id="telaPreview" class="preview-screen border rounded bg-dark text-white position-relative" style="max-width: 600px; margin: 0 auto; aspect-ratio: 16/9; overflow: hidden;">
                        <!-- Preview sem moldura -->
                        <div id="previewSemMoldura" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center p-3">
                            <div class="text-center">
                                <i class="bi bi-display text-primary" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 mb-2" id="previewNome">Nova Tela</h4>
                                <p class="mb-1 text-info" id="previewResolucao">1920x1080</p>
                                <small class="text-muted" id="previewLocalizacao">Localização não informada</small>
                                <div class="mt-3 p-2 bg-primary bg-opacity-25 rounded">
                                    <small>🎬 Área de Conteúdo (Tela Cheia)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview com moldura -->
                        <div id="previewComMoldura" class="w-100 h-100 d-none" style="display: grid; grid-template-columns: 200px 1fr; grid-template-rows: 1fr 30px;">
                            <!-- Sidebar -->
                            <div class="bg-primary d-flex flex-column justify-content-between p-2" style="grid-area: 1 / 1 / 2 / 2;">
                                <div class="text-center">
                                    <div class="bg-white bg-opacity-25 rounded p-1 mb-2">
                                        <small>📺 LOGO</small>
                                    </div>
                                    <hr class="my-2 opacity-50">
                                    <div class="text-start">
                                        <small class="d-block mb-1">📍 <span id="previewLocalizacaoMoldura">Local</span></small>
                                        <small class="d-block mb-1">🕐 <span id="previewHora">--:--</span></small>
                                        <small class="d-block">📅 <span id="previewData">--/--/----</span></small>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <small class="text-white-50">Informações</small>
                                </div>
                            </div>
                            
                            <!-- Área de conteúdo -->
                            <div class="bg-dark d-flex flex-column justify-content-center align-items-center p-2" style="grid-area: 1 / 2 / 2 / 3;">
                                <i class="bi bi-play-circle text-primary" style="font-size: 3rem;"></i>
                                <small class="mt-2">🎬 Área de Conteúdo</small>
                            </div>
                            
                            <!-- Ticker -->
                            <div class="bg-primary bg-opacity-75 d-flex align-items-center px-2" style="grid-area: 2 / 1 / 3 / 3;">
                                <small class="text-white">📰 Ticker de notícias e informações...</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2" id="previewMoldura">Sem moldura (tela cheia)</small>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="modern-badge modern-badge-secondary" id="previewTipo">Sem Moldura</span>
                            <span class="modern-badge modern-badge-info" id="previewResolucaoBadge">Full HD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview em tempo real
function atualizarPreview() {
    const nome = document.getElementById('nome').value || 'Nova Tela';
    const resolucao = document.getElementById('resolucao').value;
    const localizacao = document.getElementById('localizacao').value || 'Localização não informada';
    const comMoldura = document.getElementById('com_moldura').checked;
    
    // Atualiza informações básicas
    document.getElementById('previewNome').textContent = nome;
    document.getElementById('previewResolucao').textContent = resolucao;
    document.getElementById('previewLocalizacao').textContent = localizacao;
    document.getElementById('previewLocalizacaoMoldura').textContent = localizacao;
    
    // Atualiza hora e data em tempo real
    const agora = new Date();
    document.getElementById('previewHora').textContent = agora.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    document.getElementById('previewData').textContent = agora.toLocaleDateString('pt-BR');
    
    // Controla exibição dos previews
    const previewSemMoldura = document.getElementById('previewSemMoldura');
    const previewComMoldura = document.getElementById('previewComMoldura');
    
    if (comMoldura) {
        previewSemMoldura.classList.add('d-none');
        previewComMoldura.classList.remove('d-none');
        previewComMoldura.style.display = 'grid';
        document.getElementById('previewMoldura').textContent = 'Com moldura (sidebar com informações)';
        document.getElementById('previewTipo').textContent = 'Com Moldura';
        document.getElementById('previewTipo').className = 'modern-badge modern-badge-success';
    } else {
        previewComMoldura.classList.add('d-none');
        previewSemMoldura.classList.remove('d-none');
        document.getElementById('previewMoldura').textContent = 'Sem moldura (tela cheia)';
        document.getElementById('previewTipo').textContent = 'Sem Moldura';
        document.getElementById('previewTipo').className = 'modern-badge modern-badge-secondary';
    }
    
    // Atualiza badge de resolução
    const resolucaoBadge = document.getElementById('previewResolucaoBadge');
    switch(resolucao) {
        case '1280x720':
            resolucaoBadge.textContent = 'HD';
            break;
        case '1920x1080':
            resolucaoBadge.textContent = 'Full HD';
            break;
        case '3840x2160':
            resolucaoBadge.textContent = '4K';
            break;
    }
    
    // Atualiza aspect ratio baseado na resolução
    const preview = document.getElementById('telaPreview');
    if (resolucao === '1280x720' || resolucao === '1920x1080') {
        preview.style.aspectRatio = '16/9';
    } else if (resolucao === '3840x2160') {
        preview.style.aspectRatio = '16/9';
    }
}

// Event listeners para preview
document.getElementById('nome').addEventListener('input', atualizarPreview);
document.getElementById('resolucao').addEventListener('change', atualizarPreview);
document.getElementById('localizacao').addEventListener('input', atualizarPreview);
document.getElementById('com_moldura').addEventListener('change', atualizarPreview);

// Submissão do formulário
document.getElementById('formNovaTela').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validações básicas
    if (!formData.get('nome').trim()) {
        alert('Nome da tela é obrigatório.');
        return;
    }
    
    // Mostrar loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Criando...';
    
    fetch('api/telas/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tela criada com sucesso!');
            window.location.href = 'telas.php';
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao criar tela.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Inicializar preview
document.addEventListener('DOMContentLoaded', atualizarPreview);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>
