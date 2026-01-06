<?php
/**
 * CORREÇÃO ESPECÍFICA DOS TRIGGERS DA TABELA INSPECOES
 * 
 * Este script corrige especificamente os 2 triggers identificados:
 * - before_inspecao_insert
 * - before_inspecao_update
 * 
 * EXECUTE VIA BROWSER: http://inspecao.ctdisystem.com.br/corrigir_triggers_inspecao.php
 */

// Incluir configurações
require_once "config/config.php";
require_once "config/database.php";

// Forçar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correção Específica dos Triggers da Tabela Inspecoes</h1>";
echo "<div style='font-family: monospace; background: #fff; padding: 20px;'>";

try {
    echo "<h2>1. CONECTANDO AO BANCO...</h2>";
    $db = Database::getInstance()->getConnection();
    echo "✓ Conexão estabelecida!<br><br>";
    
    echo "<h2>2. OBTENDO USUÁRIO ATUAL...</h2>";
    
    // Método alternativo para obter usuário atual
    $stmt = $db->query("SELECT SUBSTRING_INDEX(USER(), '@', 1) as username, SUBSTRING_INDEX(USER(), '@', -1) as hostname");
    $userInfo = $stmt->fetch();
    
    $username = $userInfo['username'];
    $hostname = $userInfo['hostname'];
    $novoDefiner = "`{$username}`@`{$hostname}`";
    
    echo "Usuário atual: <strong>{$username}@{$hostname}</strong><br>";
    echo "Novo definer: <strong>{$novoDefiner}</strong><br><br>";
    
    echo "<h2>3. CORRIGINDO TRIGGERS ESPECÍFICOS...</h2>";
    
    $triggersCorrigidos = 0;
    $erros = [];
    
    // Lista dos triggers problemáticos identificados
    $triggersProblematicos = [
        'before_inspecao_insert',
        'before_inspecao_update'
    ];
    
    foreach ($triggersProblematicos as $triggerName) {
        echo "<h3>Corrigindo trigger: <strong>{$triggerName}</strong></h3>";
        
        try {
            // Obter definição do trigger
            echo "- Obtendo definição do trigger...<br>";
            $stmt = $db->prepare("SHOW CREATE TRIGGER `{$triggerName}`");
            $stmt->execute();
            $triggerDef = $stmt->fetch();
            
            if (!$triggerDef) {
                echo "✗ Trigger {$triggerName} não encontrado!<br><br>";
                continue;
            }
            
            $createStatement = $triggerDef['SQL Original Statement'];
            echo "- Definição original obtida<br>";
            
            // Mostrar definição original (primeiras linhas)
            $linhas = explode("\n", $createStatement);
            echo "- Primeiras linhas da definição:<br>";
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 5px 0; border-left: 3px solid #ccc;'>";
            for ($i = 0; $i < min(3, count($linhas)); $i++) {
                echo htmlspecialchars($linhas[$i]) . "<br>";
            }
            echo "</div>";
            
            // Substituir o definer problemático
            echo "- Substituindo definer...<br>";
            $novoCreateStatement = preg_replace(
                '/DEFINER\s*=\s*`[^`]+`@`[^`]+`/',
                "DEFINER = {$novoDefiner}",
                $createStatement
            );
            
            // Verificar se a substituição funcionou
            if ($novoCreateStatement === $createStatement) {
                echo "⚠ Nenhuma substituição de definer foi feita (pode já estar correto)<br>";
            } else {
                echo "✓ Definer substituído com sucesso<br>";
            }
            
            // Dropar trigger existente
            echo "- Removendo trigger antigo...<br>";
            $db->exec("DROP TRIGGER IF EXISTS `{$triggerName}`");
            echo "✓ Trigger antigo removido<br>";
            
            // Recriar trigger com novo definer
            echo "- Recriando trigger com novo definer...<br>";
            $db->exec($novoCreateStatement);
            echo "✓ Trigger recriado com sucesso!<br>";
            
            $triggersCorrigidos++;
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; color: #155724;'>";
            echo "<strong>✓ TRIGGER {$triggerName} CORRIGIDO COM SUCESSO!</strong>";
            echo "</div>";
            
        } catch (Exception $e) {
            $erro = "Erro ao corrigir trigger {$triggerName}: " . $e->getMessage();
            $erros[] = $erro;
            
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; color: #721c24;'>";
            echo "<strong>✗ ERRO:</strong> {$erro}";
            echo "</div>";
        }
        
        echo "<br>";
    }
    
    echo "<h2>4. RESUMO DA CORREÇÃO:</h2>";
    echo "<div style='background: #e2f3ff; padding: 15px; border: 1px solid #b8daff;'>";
    echo "<strong>Triggers corrigidos:</strong> {$triggersCorrigidos} de " . count($triggersProblematicos) . "<br>";
    echo "<strong>Erros encontrados:</strong> " . count($erros) . "<br>";
    
    if (count($erros) > 0) {
        echo "<br><strong>Detalhes dos erros:</strong><br>";
        foreach ($erros as $erro) {
            echo "- {$erro}<br>";
        }
    }
    echo "</div><br>";
    
    if ($triggersCorrigidos > 0) {
        echo "<h2>5. TESTANDO INSERÇÃO APÓS CORREÇÃO...</h2>";
        
        try {
            $sql = "INSERT INTO inspecoes (
                        data_apontamento, semana_ano, setor_id, local_id, tipo_id, 
                        apontamento, risco_consequencia, foto_antes, resolucao_proposta, 
                        responsavel, prazo, usuario_id, empresa_id, numero_inspecao
                    ) VALUES (?, WEEK(?, 1), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            
            $parametros = [
                '2025-08-04',
                '2025-08-04',
                2,
                35,
                2,
                'Teste após correção de triggers - ' . date('Y-m-d H:i:s'),
                'Teste de risco',
                null,
                'Teste de resolução',
                'Responsável Teste',
                '2025-08-10',
                1,
                4,
                1
            ];
            
            echo "Executando teste de inserção...<br>";
            $resultado = $stmt->execute($parametros);
            
            if ($resultado) {
                $insertId = $db->lastInsertId();
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; color: #155724;'>";
                echo "<h3>🎉 SUCESSO TOTAL!</h3>";
                echo "<strong>Inspeção criada com ID: {$insertId}</strong><br>";
                echo "O problema foi completamente resolvido!";
                echo "</div>";
                
                // Limpar o registro de teste
                echo "<br>Removendo registro de teste...<br>";
                $db->exec("DELETE FROM inspecoes WHERE id = {$insertId}");
                echo "✓ Registro de teste removido<br>";
                
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; color: #721c24;'>";
                echo "<strong>✗ AINDA HÁ ERRO NA INSERÇÃO</strong><br>";
                $errorInfo = $stmt->errorInfo();
                echo "Erro: " . print_r($errorInfo, true);
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; color: #721c24;'>";
            echo "<strong>EXCEÇÃO no teste de inserção:</strong><br>";
            echo "Mensagem: " . $e->getMessage() . "<br>";
            echo "Código: " . $e->getCode() . "<br>";
            echo "</div>";
        }
    }
    
    echo "<h2>6. PRÓXIMOS PASSOS:</h2>";
    echo "<ol>";
    echo "<li><strong>Teste a aplicação</strong> criando uma inspeção normalmente</li>";
    echo "<li>Se funcionar: <strong>Remova este arquivo</strong> por segurança</li>";
    echo "<li>Se ainda der erro: <strong>Informe o resultado</strong> para análise adicional</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; color: #721c24;'>";
    echo "<h3>ERRO CRÍTICO DURANTE A CORREÇÃO:</h3>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}

echo "</div>";
?>

