<?php
// Incluir cabeçalho e verificar autenticação
include "views/templates/header.php";

// Verificar se está autenticado
if (!isset($_SESSION["user_id"])) {
    redirect("index.php?route=login");
}

// Definir valores padrão ou obter de variáveis passadas (se houver)
$empresa_id = isset($_GET["empresa_id"]) ? $_GET["empresa_id"] : "";
$fonte = "";
$descricao = "";
$prazo = "";
$status = "Em Andamento"; // Padrão
$observacao = "";
?>

<style>
/* Reset e base */
body {
    background: #f5f6f5; /* Fundo branco suave */
    font-family: 'Poppins', sans-serif; /* Fonte moderna */
    color: #333333; /* Texto escuro para contraste */
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* --- INÍCIO: ESTILOS PARA O BOTÃO DE IA --- */
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* --- FIM: ESTILOS PARA O BOTÃO DE IA --- */

/* Barra superior */
.top-bar {
    background: #f5f6f5; /* Mesma cor de fundo da página */
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Card principal */
.card {
    background: #ffffff; /* Fundo branco */
    border-radius: 16px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(40, 167, 69, 0.2); /* Sombra verde suave */
    margin: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: fadeIn 0.5s ease-out;
}

.card:hover {
    transform: translateY(-5px); /* Elevação no hover */
}

/* Cabeçalho do card */
.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a); /* Gradiente verde */
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
    background: #f0f4f0; /* Verde claro para seções */
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.form-section h5 {
    color: #28a745; /* Verde principal */
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

.form-label.required::after {
    content: '*';
    color: #dc3545;
    margin-left: 0.25rem;
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
    border-color: #28a745; /* Verde principal */
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3); /* Brilho verde */
    transform: scale(1.02);
}

/* Textarea */
textarea.form-control {
    resize: vertical;
    min-height: 100px;
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
    background: linear-gradient(135deg, #28a745, #52c41a); /* Gradiente verde */
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

@media (max-width: 768px) {
    .card { margin: 1rem; }
    .card-body { padding: 1.5rem; }
    .form-section { padding: 1rem; }
    .btn { width: 100%; padding: 0.75rem; }
}

@media (max-width: 576px) {
    .top-bar { padding: 0.5rem 1rem; }
    .btn { font-size: 0.9rem; padding: 0.5rem 1rem; }
}
/* ================================================================= */
/* CSS para Responsividade de TODOS os Botões da Página          */
/* ================================================================= */

@media (max-width: 768px) {

    /* Ajusta o espaçamento da barra superior em telas menores */
    .top-bar {
        padding: 0.75rem;
    }

    /* SELETOR AGRUPADO:
       Aplica as mesmas regras para:
       1. O botão na barra superior (.top-bar .btn)
       2. Os botões de ação do formulário (.card-body .btn-group .btn)
    */
    .top-bar .btn,
    .card-body .btn-group .btn {
        /* 1. Transforma os botões em quadrados compactos */
        width: 40px;
        height: 40px;
        padding: 0;
        flex-shrink: 0; /* Previne que os botões encolham */

        /* 2. A "mágica" para esconder os textos ("Limpar", "Salvar") */
        font-size: 0;

        /* 3. Centraliza o ícone perfeitamente */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* SELETOR AGRUPADO PARA ÍCONES:
       Devolve o tamanho para os ícones dentro dos botões que estilizamos.
    */
    .top-bar .btn i,
    .card-body .btn-group .btn i {
        /* 4. Devolve o tamanho da fonte APENAS para o ícone */
        font-size: 1rem;
        margin: 0;
    }

    /* BÔNUS: Mantém os botões de Limpar e Salvar lado a lado */
    .card-body .btn-group {
        flex-wrap: nowrap;
    }
}
</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Novo Projeto</h1>
    <a href="index.php?route=projetos" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 1;">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Formulário de Criação</h6>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?route=projetos&action=store">
            <div class="form-section">
                <h5>Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                        <label for="empresa_id" class="form-label required">Empresa</label>
                        <select class="form-select" id="empresa_id" name="empresa_id" required>
                            <option value="">Selecione uma empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                            <option value="<?php echo $empresa["id"]; ?>" <?php echo ($empresa_id == $empresa["id"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($empresa["nome"]); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.2s;">
                        <label for="fonte" class="form-label">Fonte / Origem</label>
                        <input type="text" class="form-control" id="fonte" name="fonte" value="<?php echo htmlspecialchars($fonte); ?>" placeholder="Ex: Auditoria Interna, Reunião CIPA, etc.">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                        <label for="prazo" class="form-label">Prazo</label>
                        <input type="date" class="form-control" id="prazo" name="prazo" value="<?php echo htmlspecialchars($prazo); ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5>Descrição do Projeto</h5>
                <div class="row">
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.4s;">
                        <label for="descricao" class="form-label required">Descrição do Projeto</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Descreva o objetivo e o escopo do projeto" required><?php echo htmlspecialchars($descricao); ?></textarea>
                            <button type="button" class="ai-correct-btn" data-target="descricao" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5>Status e Observações</h5>
                <div class="row">
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.5s;">
                        <label for="status" class="form-label">Status Inicial</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Em Andamento" <?php echo ($status === "Em Andamento") ? "selected" : ""; ?>>Em Andamento</option>
                            <option value="Planejado" <?php echo ($status === "Planejado") ? "selected" : ""; ?>>Planejado</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.6s;">
                        <label for="observacao" class="form-label">Observações</label>
                        <div class="textarea-container">
                            <textarea class="form-control" id="observacao" name="observacao" rows="3" placeholder="Adicione observações adicionais"><?php echo htmlspecialchars($observacao); ?></textarea>
                            <button type="button" class="ai-correct-btn" data-target="observacao" title="Corrigir texto com IA">
                                <i class="fas fa-magic"></i><i class="fas fa-spinner"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="reset" class="btn btn-secondary" style="--btn-index: 1;" title="Limpar Formulário">
                    <i class="fas fa-undo"></i> Limpar
                </button>
                <button type="submit" class="btn btn-primary" style="--btn-index: 2;" title="Salvar Projeto">
                    <i class="fas fa-save"></i> Salvar Projeto
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
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

    // --- INÍCIO: JAVASCRIPT PARA CORREÇÃO COM IA ---
    document.querySelectorAll(".ai-correct-btn").forEach(button => {
        button.addEventListener("click", async function() {
            const targetId = this.dataset.target;
            const textarea = document.getElementById(targetId);
            const originalText = textarea.value;

            if (!originalText.trim()) {
                alert("Por favor, digite algum texto para corrigir.");
                return;
            }

            // Ativa o ícone de carregamento
            this.classList.add('loading');
            this.disabled = true;

            try {
                const response = await fetch("index.php?route=api&action=correctText", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `text=${encodeURIComponent(originalText)}`,
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();

                if (data.success) {
                    // Atualiza o campo de texto com o texto corrigido
                    textarea.value = data.corrected;
                } else {
                    alert("Erro ao corrigir o texto: " + (data.message || 'Erro desconhecido.'));
                }
            } catch (error) {
                console.error("Erro ao conectar com o servidor:", error);
                alert("Erro de conexão ao tentar corrigir o texto. Verifique o console para mais detalhes.");
            } finally {
                // Desativa o ícone de carregamento
                this.classList.remove('loading');
                this.disabled = false;
            }
        });
    });
    // --- FIM: JAVASCRIPT PARA CORREÇÃO COM IA ---
});
</script>

<?php include "views/templates/footer.php"; ?>
