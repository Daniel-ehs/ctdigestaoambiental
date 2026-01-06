<?php
// Incluir cabeçalho
include 'views/templates/header_publico.php';

// As funções formatDate e displayFlashMessage devem ser carregadas globalmente pelo index.php principal
// via require_once 'utils/helpers.php';

// Obter dados passados pelo controller (com verificação)
$empresas = $empresas ?? [];
$setores = $setores ?? [];
$anosDisponiveis = $anosDisponiveis ?? [];
$riscosEliminadosMes = $riscosEliminadosMes ?? 0;
$riscosEliminadosAno = $riscosEliminadosAno ?? 0;
$projetosEmAndamento = $projetosEmAndamento ?? 0;
$empresaId = $empresaId ?? null;
$ano = $ano ?? date('Y');
$mes = $mes ?? date('n');

?>

<style>
/* Estilos gerais para o corpo da página */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333333;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* Estilo para o contêiner principal */
.main-content-public {
    padding: 10px; /* Reduzido de 15px */
    background-color: #f0f2f5;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    min-height: auto; /* Alterado de 100vh para auto */
    position: relative;
    padding-top: 50px; /* Ajuste este valor conforme necessário */
}

/* Estilo para o botão de voltar */
.back-button {
    position: absolute;
    top: 10px; /* Reduzido de 20px */
    left: 10px; /* Reduzido de 20px */
    font-size: 1.5rem; /* Reduzido de 1.8rem */
    color: #6c757d;
    text-decoration: none;
    transition: all 0.2s ease;
    z-index: 10; /* <-- Adicione esta linha */
}

.back-button:hover {
    color: #343a40;
    transform: scale(1.1);
}

/* Estilos para os cards */
.card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(40, 167, 69, 0.2);
    margin: 0.5rem; /* Reduzido de 1rem */
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: fadeIn 0.5s ease-out;
    width: 100%;
    max-width: 800px; /* Ajuste para o tamanho do conteúdo */
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 1rem; /* Reduzido de 1.5rem */
    text-align: center;
}

.card-body {
    padding: 1.5rem; /* Reduzido de 2.5rem */
}

/* Estilos para os formulários e selects */
.form-label {
    font-weight: 600;
    color: #333333;
    margin-bottom: 0.3rem; /* Reduzido de 0.5rem */
    font-size: 1rem; /* Reduzido de 1.1rem */
}

.form-select, .form-control {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 0.5rem; /* Reduzido de 0.75rem */
    font-size: 0.9rem; /* Reduzido de 1rem */
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
}

.form-select:disabled {
    background-color: #e9ecef;
    opacity: 0.7;
}

.form-select:focus, .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

/* Estilos para botões */
.btn {
    position: relative;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 0 15px rgba(40, 167, 69, 0.3);
    border: none;
    font-size: 0.9rem; /* Reduzido de 1rem */
    padding: 0.6rem 1.2rem; /* Reduzido de 0.75rem 1.5rem */
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

/* Estilos específicos para a placa de estatísticas */
.placa-container {
    width: 100%;
    max-width: 600px;
    background: #1d3557;
    color: #f1faee;
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    margin-top: 10px; /* Reduzido de 20px */
    margin-left: auto;
    margin-right: auto;
}

.placa-header {
    background-color: white;
    padding: 10px 20px; /* Reduzido de 15px 25px */
    border-bottom: 4px solid #fca311;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    text-align: center;
}

.logo {
    max-height: 35px; /* Reduzido de 45px */
}

.placa-body {
    padding: 15px 10px; /* Reduzido de 30px 20px */
    text-align: center;
}

.linha-texto {
    font-size: 0.9rem; /* Reduzido de 1.0rem */
    font-weight: 700;
    margin-bottom: 0.8rem; /* Reduzido de 1.5rem */
    line-height: 1.4;
}

.linha-texto:last-child {
    margin-bottom: 0;
}

.numero-destaque {
    font-size: 1.8rem; /* Reduzido de 2.2rem */
    font-weight: 900;
    color: #fca311;
    margin: 0 5px; /* Reduzido de 0 8px */
}

.texto-azul {
    color: #a8dadc;
}

.placa-footer {
    background-color: white;
    color: #1d3557;
    padding: 10px 20px; /* Reduzido de 15px 25px */
    font-weight: 700;
    border-top: 4px solid #fca311;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
    text-align: center;
}

.action-buttons-container {
    margin-top: 10px; /* Reduzido de 20px */
}

.reportar-btn {
    background: #e63946;
    color: white;
    border: none;
    padding: 10px 20px; /* Reduzido de 15px 30px */
    font-size: 1rem; /* Reduzido de 1.2rem */
    font-weight: bold;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.reportar-btn:hover {
    background: #d62828;
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(230, 57, 70, 0.3);
}

/* Estilo para o modal de sucesso */
#successModal .modal-header {
    background-color: #28a745;
    color: white;
    border-bottom: none;
}

#successModal .modal-body {
    text-align: center;
    padding: 1.5rem; /* Reduzido de 2rem */
}

#successModal .fa-check-circle {
    font-size: 3rem; /* Reduzido de 4rem */
    color: #28a745;
    margin-bottom: 0.8rem; /* Reduzido de 1rem */
}

/* ========================================= */
/* CSS para Responsividade da Placa (VERSÃO REVISADA) */
/* ========================================= */

/* Estilos aplicados em telas com largura máxima de 600px */
@media (max-width: 600px) {

    /* Ajusta o corpo da placa para ser mais compacto */
    .placa-body {
        padding: 12px 10px; /* Preenchimento um pouco menor */
        gap: 0.5rem;        /* Espaço entre as linhas de texto bem reduzido */
    }

    /* Reduz a fonte do texto normal para melhor encaixe */
    .linha-texto {
        font-size: 0.8rem;  /* Reduzido de 0.9rem */
        line-height: 1.4;   
        margin-bottom: 0.5rem; /* Margem inferior ajustada */
    }

    /* Reduz o tamanho da fonte do número em destaque */
    .numero-destaque {
        font-size: 1.6rem;   /* Reduzido de 1.8rem para caber confortavelmente */
        margin: 0 4px;       
    }

    /* Compacta o cabeçalho e rodapé */
    .placa-header,
    .placa-footer {
        padding: 8px 12px;
    }

    /* Diminui o logo */
    .logo {
        max-height: 30px; 
    }

    /* Reduz a fonte do rodapé */
    .placa-footer {
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    /* Ajusta o botão principal de "Reportar" na versão mobile */
    .reportar-btn {
        width: 90%; /* Faz o botão ocupar quase toda a largura */
        max-width: 350px;
        font-size: 0.9rem;
        padding: 10px 15px;
        text-align: center;
    }

    /* Garante que os filtros no topo ocupem a largura toda e fiquem um embaixo do outro */
    .card-body .row .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="main-content-public">
    <a href="index.php?route=login" class="back-button" title="Voltar para o Login">
        <i class="fas fa-arrow-circle-left"></i>
    </a>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold">Filtros e Estatísticas</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="route" value="reportar">
                <div class="row">
                    <div class="col-md-4 mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="empresa_id_filtro" class="form-label">Unidade:</label>
                        <select name="empresa_id" id="empresa_id_filtro" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id']; ?>" <?php echo (($empresaId ?? null) == $empresa['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($empresa['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="ano" class="form-label">Ano:</label>
                        <select name="ano" id="ano" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($anosDisponiveis as $a): ?>
                                <option value="<?php echo $a; ?>" <?php echo ($ano == $a) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="mes" class="form-label">Mês:</label>
                        <select name="mes" id="mes" class="form-select" onchange="this.form.submit()">
                            <?php 
                                $meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
                                foreach ($meses as $index => $nomeMes) {
                                    $numMes = $index + 1;
                                    $selected = ($mes == $numMes) ? 'selected' : '';
                                    echo "<option value='{$numMes}' {$selected}>{$nomeMes}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (function_exists('getFlashMessage') && $flashMessage = getFlashMessage()): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?> alert-dismissible fade show" style="width: 100%; max-width: 800px; margin-bottom: 0.5rem;"> <!-- margin-bottom reduzido -->
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="placa-container">
                <div class="placa-header"><img src="assets/images/logo.png" alt="Logo da Empresa" class="logo"></div>
                <div class="placa-body">
                    <div class="linha-texto">ELIMINAMOS NESTE MÊS <span class="numero-destaque" id="riscos-mes" data-value="<?php echo $riscosEliminadosMes ?? 0; ?>">0</span> RISCOS POTENCIAIS DE ACIDENTES.</div>
                    <div class="linha-texto"><span class="texto-azul">JÁ SÃO</span><span class="numero-destaque" id="riscos-ano" data-value="<?php echo $riscosEliminadosAno ?? 0; ?>">0</span><span class="texto-azul">AO LONGO DESTE ANO.</span></div>
                    <div class="linha-texto">ESTAMOS DESENVOLVENDO <span class="numero-destaque" id="projetos-andamento" data-value="<?php echo $projetosEmAndamento ?? 0; ?>">0</span> PROJETO(S) PREVENTIVO(S).</div>
                </div>
                <div class="placa-footer">Contribua com um ambiente seguro, pratique segurança.</div>
            </div>
        </div>
    </div>

    <div class="action-buttons-container">
        <button class="reportar-btn" data-bs-toggle="modal" data-bs-target="#reportarModal"><i class="fas fa-bullhorn me-2"></i> Reportar uma Condição de Risco</button>
    </div>
</div>

<!-- Modal do Formulário de Reporte -->
<div class="modal fade" id="reportarModal" tabindex="-1" aria-labelledby="reportarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #e63946, #d62828); color: white;">
                <h5 class="modal-title" id="reportarModalLabel">Relatar Condição de Risco</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="index.php?route=reportar&action=store&<?php echo http_build_query(array_filter(['empresa_id' => $empresaId, 'ano' => $ano, 'mes' => $mes])); ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <p>A sua contribuição é anónima e fundamental para mantermos um ambiente de trabalho seguro. Se desejar, pode fornecer o seu contacto para que possamos dar um retorno.</p><hr>
                    <div class="mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="public_empresa_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                        <select class="form-select" id="public_empresa_id" name="empresa_id" required>
                            <option value="">Selecione a Empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id']; ?>"><?php echo htmlspecialchars($empresa['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="public_setor_id" class="form-label">Setor <span class="text-danger">*</span></label>
                        <select class="form-select" id="public_setor_id" name="setor_id" required disabled><option value="">Selecione a empresa primeiro</option></select>
                    </div>
                    <div class="mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="public_local_id" class="form-label">Local Específico <span class="text-danger">*</span></label>
                        <select class="form-select" id="public_local_id" name="local_id" required disabled><option value="">Selecione o setor primeiro</option></select>
                    </div>
                    <div class="mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="public_apontamento" class="form-label">Descreva a situação encontrada <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="public_apontamento" name="apontamento" rows="3" required placeholder="Ex: Fio desencapado próximo ao bebedouro, piso escorregadio na entrada, etc."></textarea> <!-- rows de 4 para 3 -->
                    </div>
                    <div class="mb-2"> <!-- mb-3 para mb-2 -->
                        <label for="public_foto" class="form-label">Anexar uma Foto (Opcional)</label>
                        <input class="form-control" type="file" id="public_foto" name="foto_apontamento" accept="image/*">
                    </div>
                    <hr><p class="text-muted" style="margin-bottom: 0.5rem;">Informações de Contato (Opcional)</p> <!-- margin-bottom reduzido -->
                    <div class="row">
                        <div class="col-md-6 mb-2"> <!-- mb-3 para mb-2 -->
                            <label for="public_contato_nome" class="form-label">O seu Nome</label>
                            <input type="text" class="form-control" id="public_contato_nome" name="contato_nome">
                        </div>
                        <div class="col-md-6 mb-2"> <!-- mb-3 para mb-2 -->
                            <label for="public_contato_info" class="form-label">O seu Email ou Telefone</label>
                            <input type="text" class="form-control" id="public_contato_info" name="contato_info">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background: #e63946; border-color: #e63946;">Enviar Relato</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Sucesso -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Obrigado!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <i class="fas fa-check-circle my-3"></i>
        <p class="fs-5">Agradecemos sua contribuição! Seu apontamento foi enviado para análise do departamento de segurança.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação dos números
    function animateCountUp(element) {
        if (!element) return;
        const finalValue = parseInt(element.getAttribute('data-value'), 10);
        if (isNaN(finalValue)) return;
        
        const duration = 1500; let startTime = null;
        function animationStep(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const easeOutProgress = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(easeOutProgress * finalValue);
            element.textContent = currentValue;
            if (progress < 1) requestAnimationFrame(animationStep);
        }
        requestAnimationFrame(animationStep);
    }
    document.querySelectorAll('.numero-destaque').forEach(animateCountUp);

    // Lógica dos selects em cascata
    const empresaSelect = document.getElementById('public_empresa_id');
    const setorSelect = document.getElementById('public_setor_id');
    const localSelect = document.getElementById('public_local_id');
    
    const todosSetores = <?php echo json_encode(array_values($setores ?? [])); ?>;

    empresaSelect.addEventListener('change', function() {
        const empresaId = this.value;
        setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
        setorSelect.disabled = true;
        localSelect.innerHTML = '<option value="">Selecione um setor primeiro</option>';
        localSelect.disabled = true;

        if (empresaId) {
            const setoresFiltrados = todosSetores.filter(setor => setor.empresa_id == empresaId);
            if(setoresFiltrados.length > 0) {
                 setoresFiltrados.forEach(setor => {
                    setorSelect.add(new Option(setor.nome, setor.id));
                });
                setorSelect.disabled = false;
            } else {
                 setorSelect.innerHTML = '<option value="">Nenhum setor encontrado</option>';
            }
        }
    });

    setorSelect.addEventListener('change', async function() {
        const setorId = this.value;
        localSelect.innerHTML = '<option value="">Carregando...</option>';
        localSelect.disabled = true;

        if (setorId) {
            try {
                const empresaId = empresaSelect.value; // Obter o ID da empresa selecionada
                const response = await fetch(`index.php?route=api&action=getLocaisPorSetor&setor_id=${setorId}&empresa_id=${empresaId}`);
                const data = await response.json();
                if (data.success && data.locais.length > 0) {
                    localSelect.innerHTML = '<option value="">Selecione o local</option>';
                    data.locais.forEach(local => {
                        localSelect.add(new Option(local.nome, local.id));
                    });
                    localSelect.disabled = false;
                } else {
                     localSelect.innerHTML = '<option value="">Nenhum local encontrado</option>';
                }
            } catch (error) {
                localSelect.innerHTML = '<option value="">Erro de conexão</option>';
            }
        }
    });

    // **LÓGICA DO MODAL DE SUCESSO**
    <?php
    // Verifica se a flag existe na sessão
    if (isset($_SESSION['show_success_modal']) && $_SESSION['show_success_modal']) {
        // Se existe, cria o código JavaScript para mostrar o modal
        echo "var successModal = new bootstrap.Modal(document.getElementById('successModal'));";
        echo "successModal.show();";
        // Importante: Limpa a flag da sessão para não mostrar o modal novamente
        unset($_SESSION['show_success_modal']);
    }
    ?>

    // Adiciona um "ouvinte" para o evento 'hidden.bs.modal' do modal de sucesso
    // Isso garante que a página seja recarregada APENAS quando o modal for fechado
    var successModalElement = document.getElementById('successModal');
    if (successModalElement) {
        successModalElement.addEventListener('hidden.bs.modal', function (event) {
            // Recarrega a página para limpar o formulário e os parâmetros da URL
            window.location.href = 'index.php?route=login';
        });
    }
});
</script>

<?php
// Inclui o rodapé público
if (file_exists('views/templates/footer_publico.php')) {
    include 'views/templates/footer_publico.php';
} else {
    // Fallback básico
    echo '</body></html>';
}
?>

