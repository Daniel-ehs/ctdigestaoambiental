<?php
// Incluir cabeçalho e verificar autenticação
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se os dados dos setores e tipos foram carregados pelo controller
if (!isset($setores) || !isset($tipos) || !isset($empresas)) {
    echo '<div class="alert alert-danger">Erro: Dados necessários para registro não encontrados.</div>';
    include 'views/templates/footer.php';
    exit;
}

?>

<!-- CSS Estilizado (IDÊNTICO AO CÓDIGO 1) -->
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

/* Formulário */
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
}

/* Campos do formulário */
.form-label {
    font-weight: 600;
    color: #333333;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

.form-label::after {
    content: '*';
    color: #dc3545;
    margin-left: 0.25rem;
    display: none;
}

.form-label.required::after {
    display: inline;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

/* Textarea */
textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Contêiner de textarea com ícone de correção */
.textarea-container {
    position: relative;
}

.textarea-container .ai-correct-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.textarea-container:not(:empty) .ai-correct-btn {
    opacity: 1;
}

.textarea-container .ai-correct-btn:hover {
    background: #52c41a;
    transform: scale(1.1);
}

.textarea-container .ai-correct-btn i.fa-spinner {
    display: none;
}

.textarea-container .ai-correct-btn.loading i.fa-magic {
    display: none;
}

.textarea-container .ai-correct-btn.loading i.fa-spinner {
    display: inline-block;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Preview de imagem */
.img-preview-container {
    position: relative;
    max-width: 300px;
    margin-top: 1rem;
}

.img-preview-container img {
    border-radius: 10px;
    max-width: 100%;
    max-height: 200px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.img-preview-container img:hover {
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

/* Animações */
@keyframes pop {
    0% { transform: scale(0.7); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.btn {
    animation: pop 0.4s ease-out forwards;
    animation-delay: calc(var(--btn-index) * 0.1s);
}

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

/* Notificação customizada */
.custom-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    opacity: 0;
    transform: translateX(100%);
    transition: opacity 0.5s ease, transform 0.5s ease;
}
.custom-notification.success {
    background-color: #d4edda;
    color: #155724;
}
.custom-notification.show {
    opacity: 1;
    transform: translateX(0);
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
    .btn {
        width: 100%;
        padding: 0.75rem;
    }
    .img-preview-container {
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
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}
/* ================================================= */
/* CSS para Responsividade da Página de Registro     */
/* ================================================= */

@media (max-width: 767px) {

    /* --- 1. Ajuste do Botão Voltar no Cabeçalho --- */
    
    .top-bar {
        /* Garante um pouco de espaço para o botão não ficar colado na borda */
        padding: 0.75rem;
    }

    .top-bar .btn {
        /* Transforma em um botão quadrado e compacto */
        width: 40px;
        height: 40px;
        padding: 0; /* Remove o padding para centralizar o ícone */

        /* Centraliza a seta perfeitamente dentro do botão */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .top-bar .btn i {
        /* Ajusta o tamanho do ícone da seta */
        font-size: 1rem;
        margin: 0;
    }

    /* --- 2. Ajuste do Formulário (Bônus) --- */

    /* Força todas as colunas do formulário a ocuparem 100% da largura */
    .form-section .col-md-4,
    .form-section .col-md-6,
    .form-section .col-md-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Ajusta o espaçamento do card e do formulário */
    .card {
        margin: 1rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-section {
        padding: 1rem;
    }

    /* Empilha os botões de "Limpar" e "Salvar" no final */
    .card-body .btn-group {
        flex-direction: column;
        gap: 10px;
    }

    .card-body .btn-group .btn {
        width: 100%; /* Faz os botões ocuparem a largura toda */
    }
}
</style>

<!-- Partículas de fundo (DO CÓDIGO 1) -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior (DO CÓDIGO 1) -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Registro de Inspeção</h1>
    <a href="index.php?route=inspecoes" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 1;">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<!-- HTML DO FORMULÁRIO (DO CÓDIGO 1) -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Formulário de Registro</h6>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?route=inspecoes&action=store" enctype="multipart/form-data">
            <!-- Informações Gerais -->
            <div class="form-section">
                <h5>Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                        <label for="empresa_id" class="form-label required">Empresa</label>
                        <select class="form-select" id="empresa_id" name="empresa_id" required>
                            <option value="">Selecione uma empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                            <option value="<?php echo $empresa['id']; ?>" <?php echo (isset($_GET['empresa_id']) && $_GET['empresa_id'] == $empresa['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empresa['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.2s;">
                        <label for="data_apontamento" class="form-label required">Data do Apontamento</label>
                        <input type="date" class="form-control" id="data_apontamento" name="data_apontamento" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                        <label for="setor_id" class="form-label required">Setor</label>
                        <select class="form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione uma empresa primeiro</option>
                            <?php foreach ($setores as $setor): ?>
                            <option value="<?php echo $setor['id']; ?>" data-empresa="<?php echo $setor['empresa_id'] ?? ''; ?>">
                                <?php echo htmlspecialchars($setor['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.4s;">
                        <label for="local_id" class="form-label required">Local</label>
                        <select class="form-select" id="local_id" name="local_id" required>
                            <option value="">Selecione um setor primeiro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Apontamento (DO CÓDIGO 1) -->
            <div class="form-section">
                <h5>Detalhes do Apontamento</h5>
                <div class="row">
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.5s;">
                        <label for="apontamento" class="form-label required">Apontamento (Situação Encontrada)</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="apontamento" name="apontamento" rows="3" placeholder="Descreva a situação encontrada" required></textarea>
                            <button type="button" class="ai-correct-btn" data-target="apontamento" title="Corrigir texto com IA"><i class="fas fa-magic"></i><i class="fas fa-spinner"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.6s;">
                        <label for="tipo_id" class="form-label required">Tipo de Apontamento/Consequência</label>
                        <select class="form-select" id="tipo_id" name="tipo_id" required>
                            <option value="">Selecione um tipo</option>
                            <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" data-color="<?php echo $tipo['cor']; ?>">
                                <?php echo htmlspecialchars($tipo['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.7s;">
                        <label for="risco_consequencia" class="form-label">Risco/Consequência</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="risco_consequencia" name="risco_consequencia" rows="2" placeholder="Descreva o risco ou consequência"></textarea>
                            <button type="button" class="ai-correct-btn" data-target="risco_consequencia" title="Corrigir texto com IA"><i class="fas fa-magic"></i><i class="fas fa-spinner"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foto e Resolução (DO CÓDIGO 1) -->
            <div class="form-section">
                <h5>Foto e Resolução</h5>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.8s;">
                        <label for="foto_antes" class="form-label">Foto do Local</label>
                        <input type="file" class="form-control" id="foto_antes" name="foto_antes" accept="image/*">
                        <div class="img-preview-container">
                            <img id="preview_foto_antes" src="#" alt="Preview Nova Foto" style="display: none;">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.9s;">
                        <label for="resolucao_proposta" class="form-label">Resolução/Medida Proposta/Ação Tomada</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="resolucao_proposta" name="resolucao_proposta" rows="3" placeholder="Descreva a resolução ou ação proposta"></textarea>
                            <button type="button" class="ai-correct-btn" data-target="resolucao_proposta" title="Corrigir texto com IA"><i class="fas fa-magic"></i><i class="fas fa-spinner"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsáveis e Prazos (DO CÓDIGO 1) -->
            <div class="form-section">
                <h5>Responsáveis e Prazos</h5>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 1.0s;">
                        <label for="responsavel" class="form-label">Responsável/Pessoa Informada</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel" placeholder="Nome do responsável">
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 1.1s;">
                        <label for="prazo" class="form-label">Prazo de Resolução</label>
                        <input type="date" class="form-control" id="prazo" name="prazo">
                    </div>
                </div>
            </div>

            <!-- Observações (DO CÓDIGO 1) -->
            <div class="form-section">
                <h5>Observações</h5>
                <div class="row">
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 1.2s;">
                        <label for="observacao" class="form-label">Observações</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="observacao" name="observacao" rows="3" placeholder="Adicione observações adicionais"></textarea>
                            <button type="button" class="ai-correct-btn" data-target="observacao" title="Corrigir texto com IA"><i class="fas fa-magic"></i><i class="fas fa-spinner"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="reset" class="btn btn-secondary" style="--btn-index: 1;" title="Limpar Formulário">
                    <i class="fas fa-undo"></i> Limpar
                </button>
                <button type="submit" class="btn btn-primary" style="--btn-index: 2;" title="Salvar Registro">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox (DO CÓDIGO 1) -->
<div class="lightbox" id="lightbox" onclick="toggleLightbox('')">
    <img id="lightbox-img" src="" alt="Imagem ampliada">
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Partículas de fundo (DO CÓDIGO 1)
    particlesJS('particles', {
        particles: {
            number: { value: 50, density: { enable: true, value_area: 800 } },
            color: { value: '#28a745' }, /* Verde principal */
            shape: { type: 'circle' },
            opacity: { value: 0.5, random: true },
            size: { value: 3, random: true },
            line_linked: { enable: true, distance: 150, color: '#52c41a', opacity: 0.4, width: 1 }, /* Verde claro */
            move: { enable: true, speed: 2, direction: 'none', random: true }
        },
        interactivity: {
            detect_on: 'canvas',
            events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true },
            modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } }
        },
        retina_detect: true
    });

    // **NOVO:** Função para mostrar notificação customizada (DO CÓDIGO 1)
    function showNotification(message, isError = true) {
        const notification = document.createElement('div');
        notification.className = 'custom-notification';
        if (!isError) {
            notification.classList.add('success');
        }
        notification.textContent = message;
        document.body.appendChild(notification);

        // Força a transição a acontecer
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Esconde e remove a notificação após 5 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            notification.addEventListener('transitionend', () => notification.remove());
        }, 5000);
    }

    // Lógica para correção de texto com IA (!!! LÓGICA DE CONTEXTO REVERTIDA !!!)
    document.querySelectorAll(".ai-correct-btn").forEach(button => {
        button.addEventListener("click", async function() {
            const targetId = this.dataset.target;
            const textarea = document.getElementById(targetId);
            const originalText = textarea.value;

            if (!originalText.trim()) {
                showNotification("Por favor, digite algum texto para corrigir.");
                return;
            }

            // **INÍCIO DA LÓGICA DE CONTEXTO (REVERTIDA)**
            // Agora o contexto será coletado para TODOS os campos, incluindo 'observacao'
            const fieldName = targetId;
            const allContext = {};

            // Os IDs dos campos são os mesmos do Código 1
            const fields = ['apontamento', 'risco_consequencia', 'resolucao_proposta'];
            
            // (!!! INÍCIO DA ALTERAÇÃO - REMOVENDO O 'IF' !!!)
            // Apenas coleta o contexto se o campo NÃO for 'observacao'
            // if (fieldName !== 'observacao') { // <--- REMOVA ESTA LINHA
                // Coleta o valor atual de todos os campos anteriores ao campo atual
                for (const field of fields) {
                    if (field === targetId) {
                        break; // Para de coletar quando chega no campo atual
                    }
                    const element = document.getElementById(field);
                    if (element && element.value.trim() !== '') {
                        allContext[field] = element.value;
                    }
                }
            // } // <--- REMOVA ESTA LINHA
            // (!!! FIM DA ALTERAÇÃO - REMOVENDO O 'IF' !!!)

            // **FIM DA LÓGICA DE CONTEXTO (REVERTIDA)**

            const spinner = this.querySelector(".fa-spinner");
            const magicIcon = this.querySelector(".fa-magic");
            spinner.style.display = "inline-block";
            magicIcon.style.display = "none";
            this.disabled = true;

            try {
                // Corpo da requisição agora envia os parâmetros 'context', 'field' e 'all_context'
                const body = new URLSearchParams();
                body.append('text', originalText);
                body.append('context', 'inspecao');
                body.append('field', fieldName);
                
                // Adiciona o contexto completo ao corpo da requisição
                for (const key in allContext) {
                    body.append(`all_context[${key}]`, allContext[key]);
                }
                
                const response = await fetch("index.php?route=api&action=correctText", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: body.toString(),
                });

                const data = await response.json();

                if (data.success) {
                    textarea.value = data.corrected;
                    showNotification("Texto corrigido com sucesso!", false); // Notificação de sucesso
                } else {
                    showNotification("Erro ao corrigir o texto: " + data.message);
                    console.error("Erro da API:", data.response);
                }
            } catch (error) {
                showNotification("Erro ao conectar com o servidor. Verifique sua conexão ou tente novamente.");
                console.error("Erro de rede ou servidor:", error);
            } finally {
                spinner.style.display = "none";
                magicIcon.style.display = "inline-block";
                this.disabled = false;
            }
        });
    });

    // Lógica para preview de imagem (DO CÓDIGO 1)
    const fotoAntesInput = document.getElementById('foto_antes');
    const previewFotoAntes = document.getElementById('preview_foto_antes');

    if (fotoAntesInput) {
        fotoAntesInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFotoAntes.src = e.target.result;
                    previewFotoAntes.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewFotoAntes.src = '#';
                previewFotoAntes.style.display = 'none';
            }
        });
    }

    // Lógica para lightbox (DO CÓDIGO 1)
    window.toggleLightbox = function(src) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        if (src) {
            lightboxImg.src = src;
            lightbox.classList.add('active');
        } else {
            lightbox.classList.remove('active');
            lightboxImg.src = '';
        }
    };

    // Event listener para abrir lightbox ao clicar na imagem de preview (DO CÓDIGO 1)
    if (previewFotoAntes) {
        previewFotoAntes.addEventListener('click', function() {
            if (this.src && this.src !== window.location.href + '#') { // Verifica se a imagem tem um src válido
                toggleLightbox(this.src);
            }
        });
    }

    // Lógica para carregar locais por setor (DO CÓDIGO 1)
    const empresaSelect = document.getElementById('empresa_id');
    const setorSelect = document.getElementById('setor_id');
    const localSelect = document.getElementById('local_id');

    // Função para carregar setores baseados na empresa selecionada
    function carregarSetoresPorEmpresa() {
        const empresaId = empresaSelect.value;
        setorSelect.innerHTML = '<option value="">Carregando setores...</option>';
        localSelect.innerHTML = '<option value="">Selecione um setor primeiro</option>';
        localSelect.disabled = true;

        if (!empresaId) {
            setorSelect.innerHTML = '<option value="">Selecione uma empresa primeiro</option>';
            setorSelect.disabled = true;
            return;
        }

        setorSelect.disabled = false;

        // Filtra os setores que pertencem à empresa selecionada
        const setoresFiltrados = <?php echo json_encode($setores); ?>.filter(setor => setor.empresa_id == empresaId);

        if (setoresFiltrados.length > 0) {
            setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
            setoresFiltrados.forEach(setor => {
                const option = document.createElement('option');
                option.value = setor.id;
                option.textContent = setor.nome;
                setorSelect.appendChild(option);
            });
        } else {
            setorSelect.innerHTML = '<option value="">Nenhum setor encontrado para esta empresa</option>';
        }
    }

    // Função para carregar locais baseados no setor selecionado
    async function carregarLocaisPorSetor() {
        const setorId = setorSelect.value;
        localSelect.innerHTML = '<option value="">Carregando locais...</option>';
        localSelect.disabled = true;

        if (!setorId) {
            localSelect.innerHTML = '<option value="">Selecione um setor primeiro</option>';
            return;
        }

        try {
            const response = await fetch(`index.php?route=api&action=getLocaisPorSetor&setor_id=${setorId}`);
            const data = await response.json();

            if (data.success) {
                localSelect.innerHTML = '<option value="">Selecione um local</option>';
                if (data.locais.length > 0) {
                    localSelect.disabled = false;
                    data.locais.forEach(local => {
                        const option = document.createElement('option');
                        option.value = local.id;
                        option.textContent = local.nome;
                        localSelect.appendChild(option);
                    });
                } else {
                    localSelect.innerHTML = '<option value="">Nenhum local encontrado para este setor</option>';
                }
            } else {
                console.error('Erro ao carregar locais:', data.message);
                localSelect.innerHTML = '<option value="">Erro ao carregar locais</option>';
            }
        } catch (error) {
            console.error('Erro de rede ao carregar locais:', error);
            localSelect.innerHTML = '<option value="">Erro de conexão</option>';
        }
    }

    // Event Listeners (DO CÓDIGO 1)
    empresaSelect.addEventListener('change', carregarSetoresPorEmpresa);
    setorSelect.addEventListener('change', carregarLocaisPorSetor);

    // Chamada inicial para carregar setores se uma empresa já estiver selecionada (ex: via GET) (DO CÓDIGO 1)
    if (empresaSelect.value) {
        carregarSetoresPorEmpresa();
    }

    // Se houver um setor pré-selecionado (ex: via GET), carregar locais (DO CÓDIGO 1)
    const initialSetorId = setorSelect.value;
    if (initialSetorId) {
        carregarLocaisPorSetor();
    }
});
</script>
