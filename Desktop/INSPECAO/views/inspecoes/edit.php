<?php
// Incluir cabeçalho e verificar autenticação
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se os dados da inspeção, setores, locais e tipos foram carregados pelo controller
if (!isset($inspecao) || !$inspecao || !isset($setores) || !isset($locais) || !isset($tipos)) {
    echo '<div class="alert alert-danger">Erro: Dados necessários para edição não encontrados.</div>';
    include 'views/templates/footer.php';
    exit;
}

// Guardar IDs para usar no JavaScript
$setorIdInicial = $inspecao['setor_id'] ?? null;
$localIdInicial = $inspecao['local_id'] ?? null;
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

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1px solid #ced4da;
        padding: 0.75rem;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
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

    .ai-correct-btn {
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
        opacity: 0.8;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .ai-correct-btn:hover {
        background: #52c41a;
        transform: scale(1.1);
        opacity: 1;
    }

    .ai-correct-btn .fa-spinner {
        display: none;
    }

    .ai-correct-btn.loading .fa-magic {
        display: none;
    }

    .ai-correct-btn.loading .fa-spinner {
        display: inline-block;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
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
        0% {
            transform: scale(0.7);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .btn {
        animation: pop 0.4s ease-out forwards;
        animation-delay: calc(var(--btn-index) * 0.1s);
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

    @media (max-width: 767px) {
        .top-bar {
            padding: 0.75rem;
        }

        .top-bar .btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .top-bar .btn i {
            font-size: 1rem;
            margin: 0;
        }

        .form-section .col-md-4,
        .form-section .col-md-6,
        .form-section .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .card-body .btn-group {
            flex-direction: column;
            gap: 10px;
        }

        .card-body .btn-group .btn {
            width: 100%;
        }
    }
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Editar Inspeção
        #<?php echo htmlspecialchars($inspecao['numero_inspecao'] ?? $inspecao['id']); ?></h1>
    <a href="index.php?route=inspecoes" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 1;">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Formulário de Edição</h6>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?route=inspecoes&action=update&id=<?php echo $inspecao['id']; ?>"
            enctype="multipart/form-data">
            <!-- Informações Gerais -->
            <div class="form-section">
                <h5>Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="empresa_id" class="form-label required">Empresa</label>
                        <select class="form-select" id="empresa_id" name="empresa_id" required>
                            <option value="">Selecione uma empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id']; ?>" <?php echo ($empresa['id'] == $inspecao['empresa_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($empresa['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="data_apontamento" class="form-label required">Data do Apontamento</label>
                        <input type="date" class="form-control" id="data_apontamento" name="data_apontamento"
                            value="<?php echo htmlspecialchars($inspecao['data_apontamento']); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="setor_id" class="form-label required">Setor</label>
                        <select class="form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione um setor</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?php echo $setor['id']; ?>" <?php echo ($setor['id'] == $setorIdInicial) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($setor['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="local_id" class="form-label required">Local</label>
                        <select class="form-select" id="local_id" name="local_id" required>
                            <option value="">Selecione um setor primeiro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Apontamento -->
            <div class="form-section">
                <h5>Detalhes do Apontamento</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="apontamento" class="form-label required">Apontamento (Situação Encontrada)</label>
                        <div class="textarea-container">
                            <!-- CORREÇÃO: data-field="apontamento" -->
                            <textarea class="form-control" id="apontamento" name="apontamento" rows="3" required
                                data-field="apontamento"><?php echo htmlspecialchars($inspecao['apontamento']); ?></textarea>
                            <button type="button" class="ai-correct-btn" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo_id" class="form-label required">Tipo de Apontamento/Consequência</label>
                        <select class="form-select" id="tipo_id" name="tipo_id" required>
                            <option value="">Selecione um tipo</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>" data-color="<?php echo $tipo['cor']; ?>" <?php echo ($tipo['id'] == $inspecao['tipo_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="risco_consequencia" class="form-label">Risco/Consequência</label>
                        <div class="textarea-container">
                            <!-- CORREÇÃO: data-field="risco_consequencia" -->
                            <textarea class="form-control" id="risco_consequencia" name="risco_consequencia" rows="2"
                                data-field="risco_consequencia"><?php echo htmlspecialchars($inspecao['risco_consequencia'] ?? ''); ?></textarea>
                            <button type="button" class="ai-correct-btn" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foto e Resolução -->
            <div class="form-section">
                <h5>Foto e Resolução</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="foto_antes" class="form-label">Foto do Local (Substituir)</label>
                        <input type="file" class="form-control" id="foto_antes" name="foto_antes" accept="image/*">
                        <div class="img-preview-container">
                            <?php
                            // --- INÍCIO: LÓGICA CORRIGIDA PARA MINIO ---
                            // A variável já contém o URL completo do MinIO. Basta usá-la.
                            if (!empty($inspecao['foto_antes'])) {
                                $foto_url = htmlspecialchars($inspecao['foto_antes']);
                                echo '<p class="mt-2">Foto Atual:</p>';
                                echo '<img id="preview_foto_atual" src="' . $foto_url . '" alt="Foto Atual" onclick="toggleLightbox(\'' . $foto_url . '\')">';
                            } else {
                                echo '<p class="mt-2">Foto Atual: <span class="badge bg-secondary">N/A</span></p>';
                            }
                            // --- FIM: LÓGICA CORRIGIDA ---
                            ?>
                            <img id="preview_foto_antes" src="#" alt="Preview Nova Foto" style="display: none;">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="resolucao_proposta" class="form-label">Resolução/Medida Proposta/Ação Tomada</label>
                        <div class="textarea-container">
                            <!-- !!! CORREÇÃO AQUI !!! data-field="resolucao" mudou para data-field="resolucao_proposta" -->
                            <textarea class="form-control" id="resolucao_proposta" name="resolucao_proposta" rows="3"
                                data-field="resolucao_proposta"><?php echo htmlspecialchars($inspecao['resolucao_proposta'] ?? ''); ?></textarea>
                            <button type="button" class="ai-correct-btn" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsáveis e Prazos -->
            <div class="form-section">
                <h5>Responsáveis e Prazos</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="responsavel" class="form-label">Responsável/Pessoa Informada</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel"
                            value="<?php echo htmlspecialchars($inspecao['responsavel'] ?? ''); ?>"
                            placeholder="Nome do responsável">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="prazo" class="form-label">Prazo de Resolução</label>
                        <input type="date" class="form-control" id="prazo" name="prazo"
                            value="<?php echo htmlspecialchars($inspecao['prazo'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="form-section">
                <h5>Observações</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="observacao" class="form-label">Observações</label>
                        <div class="textarea-container">
                            <!-- CORREÇÃO: data-field="observacao" -->
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                data-field="observacao"><?php echo htmlspecialchars($inspecao['observacao'] ?? ''); ?></textarea>
                            <button type="button" class="ai-correct-btn" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <a href="index.php?route=inspecoes" class="btn btn-secondary" style="--btn-index: 1;" title="Cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" style="--btn-index: 2;" title="Salvar Alterações">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="toggleLightbox('')">
    <img id="lightbox-img" src="" alt="Imagem ampliada">
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Função para mostrar notificações
        function showNotification(message, isSuccess = true) {
            const notification = document.createElement('div');
            notification.className = `custom-notification ${isSuccess ? 'success' : ''}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Ativa a animação de entrada
            setTimeout(() => notification.classList.add('show'), 10);

            // Remove a notificação após 5 segundos
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 500);
            }, 5000);
        }

        // (!!! INÍCIO DA LÓGICA DE IA ATUALIZADA !!!)
        // Lógica para correção de texto com IA
        document.querySelectorAll(".ai-correct-btn").forEach(button => {
            button.addEventListener("click", async function () {
                // A lógica de encontrar o textarea está correta
                const textarea = this.previousElementSibling;
                const originalText = textarea.value;
                // O fieldName (targetId) vem do data-field, que corrigimos no HTML
                const fieldName = textarea.dataset.field;

                if (!originalText.trim()) {
                    showNotification("Por favor, digite algum texto para corrigir.", false);
                    return;
                }

                this.classList.add('loading');
                this.disabled = true;

                try {
                    // 1. Montar o corpo da requisição (igual ao create.php)
                    const body = new URLSearchParams();
                    body.append('text', originalText);
                    body.append('context', 'inspecao');
                    body.append('field', fieldName);

                    // 2. Coletar o contexto (igual ao create.php)
                    const allContext = {};
                    // Os IDs dos textareas no HTML são 'apontamento', 'risco_consequencia', etc.
                    const fields = ['apontamento', 'risco_consequencia', 'resolucao_proposta', 'observacao'];

                    for (const field of fields) {
                        if (field === fieldName) {
                            break; // Para de coletar quando chega no campo atual
                        }
                        const element = document.getElementById(field);
                        if (element && element.value.trim() !== '') {
                            allContext[field] = element.value;
                        }
                    }

                    // 3. Adicionar o contexto ao corpo (igual ao create.php)
                    for (const key in allContext) {
                        body.append(`all_context[${key}]`, allContext[key]);
                    }

                    // 4. Fazer a chamada fetch (igual ao create.php)
                    const response = await fetch("index.php?route=api&action=correctText", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: body.toString(),
                    });

                    const data = await response.json();

                    // 5. Tratar a resposta (igual ao create.php)
                    if (data.success) {
                        textarea.value = data.corrected;
                        showNotification("Texto corrigido com sucesso!", true);
                    } else {
                        showNotification("Erro ao corrigir o texto: " + data.message, false);
                        console.error("Erro da API:", data.response);
                    }
                } catch (error) {
                    showNotification("Erro ao conectar com o servidor. Verifique sua conexão ou tente novamente.", false);
                    console.error("Erro de rede ou servidor:", error);
                } finally {
                    this.classList.remove('loading');
                    this.disabled = false;
                }
            });
        });
        // (!!! FIM DA LÓGICA DE IA ATUALIZADA !!!)


        // Partículas de fundo
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles', { particles: { number: { value: 50, density: { enable: true, value_area: 800 } }, color: { value: '#28a745' }, shape: { type: 'circle' }, opacity: { value: 0.5, random: true }, size: { value: 3, random: true }, line_linked: { enable: true, distance: 150, color: '#52c41a', opacity: 0.4, width: 1 }, move: { enable: true, speed: 2, direction: 'none', random: true } }, interactivity: { detect_on: 'canvas', events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true }, modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } } }, retina_detect: true });
        } else {
            console.error('particles.js não foi carregado.');
        }

        // Lógica para lightbox
        window.toggleLightbox = function (src) {
            const lightbox = document.getElementById('lightbox');
            const img = document.getElementById('lightbox-img');
            if (src) {
                img.src = src;
                lightbox.classList.add('active');
            } else {
                lightbox.classList.remove('active');
                img.src = '';
            }
        };

        document.querySelectorAll('.img-preview-container img').forEach(img => {
            img.addEventListener('click', function () {
                if (this.src && !this.src.endsWith('#')) {
                    toggleLightbox(this.src);
                }
            });
        });

        // Lógica para carregar locais dinamicamente
        const setorSelect = document.getElementById('setor_id');
        const localSelect = document.getElementById('local_id');
        const empresaSelect = document.getElementById('empresa_id');
        const localIdInicial = <?php echo json_encode($localIdInicial); ?>;

        async function carregarLocais(setorId, localParaSelecionarId) {
            localSelect.innerHTML = '<option value="">Carregando...</option>';
            localSelect.disabled = true;

            if (!setorId) {
                localSelect.innerHTML = '<option value="">Selecione um setor primeiro</option>';
                return;
            }

            const empresaId = empresaSelect ? empresaSelect.value : null;
            let url = `index.php?route=api&action=getLocaisPorSetor&setor_id=${setorId}`;
            if (empresaId) {
                url += `&empresa_id=${empresaId}`;
            }

            try {
                const response = await fetch(url);
                const data = await response.json();

                localSelect.innerHTML = '<option value="">Selecione um local</option>';
                if (data.success && data.locais && data.locais.length > 0) {
                    data.locais.forEach(function (local) {
                        const option = document.createElement('option');
                        option.value = local.id;
                        option.textContent = local.nome;
                        if (local.id == localParaSelecionarId) {
                            option.selected = true;
                        }
                        localSelect.appendChild(option);
                    });
                    localSelect.disabled = false;
                } else {
                    localSelect.innerHTML = '<option value="">Nenhum local neste setor</option>';
                }
            } catch (error) {
                console.error('Erro na requisição para carregar locais:', error);
                localSelect.innerHTML = '<option value="">Erro ao carregar locais</option>';
            }
        }

        if (setorSelect && localSelect) {
            setorSelect.addEventListener('change', () => carregarLocais(setorSelect.value, null));
            if (empresaSelect) {
                empresaSelect.addEventListener('change', () => {
                    if (setorSelect.value) {
                        carregarLocais(setorSelect.value, null);
                    }
                });
            }
            if (setorSelect.value) {
                carregarLocais(setorSelect.value, localIdInicial);
            }
        }

        // Preview de imagem ao selecionar novo arquivo
        const fotoInput = document.getElementById('foto_antes');
        const fotoPreview = document.getElementById('preview_foto_antes');
        const fotoAtualPreview = document.getElementById('preview_foto_atual');

        if (fotoInput && fotoPreview) {
            fotoInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        fotoPreview.src = e.target.result;
                        fotoPreview.style.display = 'block';
                        if (fotoAtualPreview) fotoAtualPreview.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                } else {
                    fotoPreview.src = '#';
                    fotoPreview.style.display = 'none';
                    if (fotoAtualPreview) fotoAtualPreview.style.display = 'block';
                }
            });
        }
    });
</script>

<!-- Fonte Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<?php include 'views/templates/footer.php'; ?>