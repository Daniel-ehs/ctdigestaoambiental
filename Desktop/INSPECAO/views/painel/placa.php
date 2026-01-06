<?php 
// Inclui o cabeçalho padrão do seu site
include 'views/templates/header.php'; 
?>

<style>
    #main-content {
        padding: 15px;
        background-color: #f0f2f5; 
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .filtros-container {
        width: 100%;
        max-width: 800px; 
        background-color: #fff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filtros-container form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }

    .filtros-container select, .filtros-container button {
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 0.9rem;
        font-family: 'Poppins', sans-serif;
    }

    .filtros-container button {
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .placa-container {
        width: 100%;
        max-width: 700px;
        background: #1d3557;
        color: #e0e1dd;
        border-radius: 15px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        /* REMOVIDO: aspect-ratio e overflow. A altura agora é automática. */
    }

    .placa-header {
        background-color: white;
        padding: 15px 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 4px solid #fca311;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .logo {
        max-height: 45px;
        object-fit: contain;
    }

    .placa-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        gap: 2rem; /* Espaçamento fixo e mais previsível */
        padding: 40px 20px;
    }

    .linha-texto {
        font-size: 1.1rem; /* AJUSTADO: Tamanho de fonte fixo e legível */
        font-weight: 700;
        line-height: 1.4;
        text-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    }

    .numero-destaque {
        font-size: 2.5rem; /* AJUSTADO: Tamanho de fonte fixo e legível */
        font-weight: 900;
        color: #fca311; 
        margin: 0 10px;
        display: inline-block;
        text-shadow: 0 0 15px rgba(252, 163, 17, 0.5);
    }
    
    .texto-azul {
        color: #a9d6e5;
        font-weight: normal;
    }

    .placa-footer {
        background-color: white;
        color: #1d3557;
        text-align: center;
        padding: 15px 25px;
        font-weight: 700;
        line-height: 1.4;
        border-top: 4px solid #fca311;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        font-size: 0.9rem; /* AJUSTADO: Tamanho de fonte fixo e legível */
    }
    
    /* Media queries para ajustar em telas menores */
    @media (max-width: 600px) {
        .linha-texto { font-size: 1rem; }
        .numero-destaque { font-size: 2rem; }
        .placa-footer { font-size: 0.8rem; }
        .placa-body { padding: 30px 15px; gap: 1.5rem; }
    }
</style>

<div id="main-content">
    <div class="filtros-container">
        <form method="GET" action="index.php">
            <input type="hidden" name="route" value="painel">
            <input type="hidden" name="action" value="placa">
            
            <div>
                <label for="empresa_id">Empresa:</label>
                <select name="empresa_id" id="empresa_id" onchange="this.form.submit()">
                    <option value="">Todas as Empresas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?php echo $empresa['id']; ?>" <?php echo ($empresaId == $empresa['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($empresa['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ano">Ano:</label>
                <select name="ano" id="ano" onchange="this.form.submit()">
                    <?php foreach ($anosDisponiveis as $a): ?>
                        <option value="<?php echo $a; ?>" <?php echo ($ano == $a) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="mes">Mês:</label>
                <select name="mes" id="mes" onchange="this.form.submit()">
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
        </form>
    </div>

    <div class="placa-container">
        <div class="placa-header">
            <img src="assets/images/logo.png" alt="Logo da Empresa" class="logo">
        </div>

        <div class="placa-body">
            <div class="linha-texto">
                ELIMINAMOS NESTE MÊS
                <span class="numero-destaque" id="riscos-mes" data-value="<?php echo $riscosEliminadosMes; ?>">0</span>
                RISCOS POTENCIAIS DE ACIDENTES EM NOSSA EMPRESA.
            </div>
            <div class="linha-texto">
                <span class="texto-azul">JÁ SÃO</span>
                <span class="numero-destaque" id="riscos-ano" data-value="<?php echo $riscosEliminadosAno; ?>">0</span>
                <span class="texto-azul">AO LONGO DESTE ANO.</span>
            </div>
            <div class="linha-texto">
                ESTAMOS DESENVOLVENDO
                <span class="numero-destaque" id="projetos-andamento" data-value="<?php echo $projetosEmAndamento; ?>">0</span>
                PROJETO(S) PREVENTIVO(S) A FIM DE MELHORAR NOSSO NÍVEL DE SEGURANÇA.
            </div>
        </div>

        <div class="placa-footer">
            Contribua com um ambiente seguro, pratique segurança.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function animateCountUp(element) {
        if (!element) return;
        const finalValue = parseInt(element.getAttribute('data-value'), 10);
        const duration = 1500;
        let startTime = null;

        function animationStep(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const easeOutProgress = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(easeOutProgress * finalValue);
            element.textContent = currentValue;
            if (progress < 1) {
                requestAnimationFrame(animationStep);
            }
        }
        requestAnimationFrame(animationStep);
    }

    animateCountUp(document.getElementById('riscos-mes'));
    animateCountUp(document.getElementById('riscos-ano'));
    animateCountUp(document.getElementById('projetos-andamento'));
});
</script>

<?php 
// Inclui o rodapé padrão do seu site
include 'views/templates/footer.php'; 
?>