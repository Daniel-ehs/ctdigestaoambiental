<?php
// Incluir cabeçalho e verificar autenticação
include_once BASE_PATH . '/views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se é edição ou criação
$isEdit = isset($registro);
$titulo = $isEdit ? 'Editar Local' : 'Novo Local';
$acao = $isEdit ? "index.php?route=cadastros&type=locais&action=update&id={$registro['id']}" : "index.php?route=cadastros&type=locais&action=store";
?>

<!-- CSS Estilizado (idêntico ao primeiro código da conversa anterior) -->
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
    border-color: #28a745; /* Verde principal */
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3); /* Brilho verde */
    transform: scale(1.02);
}

/* Textarea */
textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Checkbox */
.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.5rem;
    border-radius: 4px;
    border: 1px solid #ced4da;
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

.form-check-input:checked {
    background-color: #28a745; /* Verde principal */
    border-color: #28a745;
}

.form-check-label {
    font-size: 1rem;
    color: #333333;
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
</style>

<!-- Partículas de fundo -->
<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<!-- Barra superior -->
<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;"><?php echo $titulo; ?></h1>
    <a href="index.php?route=cadastros&type=locais" class="btn btn-secondary" title="Voltar para a lista" style="--btn-index: 1;">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Formulário de Local</h6>
    </div>
    <div class="card-body">
        <?php include_once BASE_PATH . '/views/components/flash_messages.php'; ?>
        <form action="<?php echo $acao; ?>" method="post">
            <!-- Informações Gerais -->
            <div class="form-section">
                <h5>Informações Gerais</h5>
                <div class="row">
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.1s;">
                        <label for="empresa_id" class="form-label required">Empresa</label>
                        <select class="form-select" id="empresa_id" name="empresa_id" required>
                            <option value="">Selecione uma empresa</option>
                            <?php 
                            // Carregar empresas se não estiverem carregadas
                            if (!isset($empresas)) {
                                require_once 'models/Empresa.php';
                                $empresaModel = new Empresa();
                                $empresas = $empresaModel->getAll();
                            }
                            foreach ($empresas as $empresa): ?>
                            <option value="<?php echo $empresa['id']; ?>" <?php echo ($isEdit && isset($registro['empresa_id']) && $registro['empresa_id'] == $empresa['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empresa['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.2s;">
                        <label for="setor_id" class="form-label required">Setor</label>
                        <select class="form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione uma empresa primeiro</option>
                            <?php if (isset($setores) && is_array($setores)): ?>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?php echo $setor['id']; ?>" data-empresa="<?php echo $setor['empresa_id'] ?? ''; ?>" <?php echo $isEdit && $registro['setor_id'] == $setor['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($setor['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                        <label for="nome" class="form-label required">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $isEdit ? htmlspecialchars($registro['nome']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <!-- Descrição e Status -->
            <div class="form-section">
                <h5>Descrição e Status</h5>
                <div class="row">
                    <div class="col-md-12 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.3s;">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreva o local"><?php echo $isEdit ? htmlspecialchars($registro['descricao'] ?? '') : ''; ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3" style="animation: fadeIn 0.6s ease-out forwards; animation-delay: 0.4s;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?php echo (!$isEdit || ($isEdit && $registro['ativo'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Ativo</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary" style="--btn-index: 1;" title="Salvar Local">
                    <i class="fas fa-save"></i> Salvar
                </button>
                <a href="index.php?route=cadastros&type=locais" class="btn btn-secondary" style="--btn-index: 2;" title="Cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Partículas de fundo
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

    // Verificar se particles.js carregou corretamente
    if (typeof particlesJS === 'undefined') {
        console.error('particles.js não foi carregado. Verifique a conexão com o CDN.');
    }

    // Filtrar setores por empresa
    const empresaSelect = document.getElementById('empresa_id');
    const setorSelect = document.getElementById('setor_id');
    
    if (empresaSelect && setorSelect) {
        // Armazenar todas as opções de setores
        const todasOpcoes = Array.from(setorSelect.options).slice(1); // Remove a primeira opção "Selecione..."
        
        empresaSelect.addEventListener('change', function() {
            const empresaId = this.value;
            
            // Limpar setores
            setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
            
            if (empresaId) {
                // Filtrar setores pela empresa selecionada
                todasOpcoes.forEach(function(opcao) {
                    if (opcao.dataset.empresa === empresaId) {
                        setorSelect.appendChild(opcao.cloneNode(true));
                    }
                });
            } else {
                setorSelect.innerHTML = '<option value="">Selecione uma empresa primeiro</option>';
            }
        });
        
        // Se estiver editando, filtrar setores na inicialização
        if (empresaSelect.value) {
            empresaSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>

<!-- Fonte Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<?php include_once BASE_PATH . '/views/templates/footer.php'; ?>