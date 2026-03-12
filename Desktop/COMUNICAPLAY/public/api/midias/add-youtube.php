<?php

require_once __DIR__ . "/../../../includes/session.php";
require_once __DIR__ . "/../../../controllers/MidiaController.php";

// Verifica se está logado
SessionManager::requireLogin();

// Só aceita POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
    exit;
}

// Define header JSON
header("Content-Type: application/json");

try {
    $midiaController = new MidiaController();
    
    $url = $_POST["url"] ?? "";
    $nome = $_POST["nome"] ?? "";
    $duracao = (int)($_POST["duracao"] ?? 0);
    $pastaId = $_POST["pasta_id"] ?? null;
    
    // Converte string vazia para null
    if (empty($pastaId)) {
        $pastaId = null;
    }
    
    // Validações básicas
    if (empty($url)) {
        echo json_encode([
            "success" => false,
            "message" => "URL do YouTube é obrigatória."
        ]);
        exit;
    }
    
    if (empty($nome)) {
        echo json_encode([
            "success" => false,
            "message" => "Nome é obrigatório."
        ]);
        exit;
    }
    
    $result = $midiaController->addYouTube($url, $nome, $duracao, $pastaId);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de adicionar YouTube: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Erro interno do servidor"
    ]);
}


