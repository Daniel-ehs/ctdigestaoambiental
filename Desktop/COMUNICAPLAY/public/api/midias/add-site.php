<?php

// CORREÇÃO: API simplificada usando inserção direta no modelo
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../models/Midia.php';

// Headers para debugging
header('Content-Type: application/json');

// Verifica se está logado
SessionManager::requireLogin();

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Log de debugging
    error_log("=== ADD-SITE DIRETO ===");
    error_log("POST data: " . json_encode($_POST));
    
    // Validar dados obrigatórios
    $url = trim($_POST['url'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $duracao = (int)($_POST['duracao'] ?? 30);
    $pastaId = !empty($_POST['pasta_id']) ? (int)$_POST['pasta_id'] : null;
    $descricao = trim($_POST['descricao'] ?? '');
    
    error_log("Dados processados - URL: $url, Nome: $nome, Duração: $duracao");
    
    // Validações
    if (empty($url)) {
        throw new Exception('URL é obrigatória');
    }
    
    if (empty($nome)) {
        throw new Exception('Nome é obrigatório');
    }
    
    if ($duracao < 5 || $duracao > 600) {
        throw new Exception('Duração deve estar entre 5 e 600 segundos');
    }
    
    // Validar URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('URL inválida');
    }
    
    $parsedUrl = parse_url($url);
    if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
        throw new Exception('URL deve usar protocolo HTTP ou HTTPS');
    }
    
    // Obter ID do usuário
    $userId = SessionManager::getUserId();
    if (!$userId) {
        throw new Exception('Usuário não autenticado');
    }
    
    error_log("Usuário ID: $userId");
    
    // Criar instância do modelo
    $midiaModel = new Midia();
    
    // Inserir diretamente usando o método create do modelo
    $midiaId = $midiaModel->create(
        $nome,           // nome
        'site',          // tipo
        null,            // caminho_arquivo (NULL para sites)
        $url,            // url_externa
        null,            // miniatura (NULL para sites)
        $duracao,        // duracao
        0,               // tamanho_arquivo (0 para sites)
        $pastaId,        // pasta_id
        $userId          // usuario_criador_id
    );
    
    error_log("Resultado da inserção - ID: $midiaId");
    
    if ($midiaId) {
        // Se temos descrição, atualizar
        if (!empty($descricao)) {
            require_once __DIR__ . '/../../../config/database.php';
            $pdo = Database::getInstance()->getConnection();
            
            $stmt = $pdo->prepare("UPDATE midias SET descricao = ? WHERE id = ?");
            $stmt->execute([$descricao, $midiaId]);
            
            error_log("Descrição atualizada para mídia ID: $midiaId");
        }
        
        // Log de atividade
        require_once __DIR__ . '/../../../includes/functions.php';
        logActivity('site_added', "Site {$nome} adicionado", $userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Site adicionado com sucesso!',
            'midia_id' => $midiaId
        ]);
    } else {
        throw new Exception('Erro ao inserir site no banco de dados');
    }
    
} catch (Exception $e) {
    error_log("ERRO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

error_log("=== FIM ADD-SITE DIRETO ===");

?>
