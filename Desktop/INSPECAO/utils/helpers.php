<?php
/**
 * Funções auxiliares para o sistema
 */

/**
 * Redirecionar para URL
 * 
 * @param string $url URL para redirecionamento
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Definir mensagem flash
 * 
 * @param string $type Tipo da mensagem (success, error, warning, info)
 * @param string $message Texto da mensagem
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obter mensagem flash
 * 
 * @return array|null Mensagem flash ou null se não existir
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Verificar se extensão de arquivo é permitida
 * 
 * @param string $filename Nome do arquivo
 * @return bool Verdadeiro se extensão for permitida
 */
function isAllowedExtension($filename) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Gerar nome único para arquivo
 * 
 * @param string $originalName Nome original do arquivo
 * @return string Nome único gerado
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('file_') . '_' . date('YmdHis') . '.' . $extension;
}

/**
 * Formatar data
 * 
 * @param string $date Data no formato Y-m-d
 * @param string $format Formato de saída
 * @return string Data formatada
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Calcular número da semana do ano
 * 
 * @param string $date Data no formato Y-m-d
 * @return int Número da semana
 */
function getWeekNumber($date) {
    if (empty($date)) {
        return date('W');
    }
    
    $dateObj = new DateTime($date);
    return $dateObj->format('W');
}

/**
 * Calcular status de inspeção com base no prazo
 * 
 * @param string $prazo Data do prazo no formato Y-m-d
 * @param bool $concluido Indica se a inspeção foi concluída
 * @return string Status (Em Aberto, Concluído, Prazo Vencido)
 */
function calcularStatus($prazo, $concluido) {
    if ($concluido) {
        return 'Concluído';
    }
    
    if (empty($prazo)) {
        return 'Em Aberto';
    }
    
    $hoje = new DateTime();
    $dataPrazo = new DateTime($prazo);
    
    if ($hoje > $dataPrazo) {
        return 'Prazo Vencido';
    }
    
    return 'Em Aberto';
}

/**
 * Limitar texto
 * 
 * @param string $text Texto a ser limitado
 * @param int $length Comprimento máximo
 * @param string $append Texto a ser adicionado no final
 * @return string Texto limitado
 */
function limitText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $append;
}

/**
 * Verificar se usuário tem permissão de administrador
 * 
 * @return bool Verdadeiro se usuário for administrador
 */
function isAdmin() {
    return isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin';
}

/**
 * Sanitizar entrada de texto
 * 
 * @param string $text Texto a ser sanitizado
 * @return string Texto sanitizado
 */
function sanitizeInput($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Gerar URL para download de arquivo
 * 
 * @param string $path Caminho do arquivo
 * @return string URL para download
 */
function getDownloadUrl($path) {
    $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', BASE_PATH);
    $relativePath = str_replace(BASE_PATH, '', $path);
    
    return $basePath . $relativePath;
}

/**
 * Obter cor para status
 * 
 * @param string $status Status
 * @return string Classe CSS para cor
 */
function getStatusColor($status) {
    switch ($status) {
        case 'Concluído':
            return 'success';
            
        case 'Prazo Vencido':
            return 'danger';
            
        case 'Em Andamento':
            return 'primary';
            
        case 'Cancelado':
            return 'secondary';
            
        default:
            return 'info';
    }
}

/**
 * Formatar data e hora
 * 
 * @param string $datetime Data e hora no formato Y-m-d H:i:s ou similar
 * @param string $format Formato de saída desejado
 * @return string Data e hora formatada ou string vazia se inválido
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i:s') {
    if (empty($datetime)) {
        return '';
    }
    try {
        // Tenta criar o objeto DateTime
        $dateObj = new DateTime($datetime);
        // Retorna a data formatada
        return $dateObj->format($format);
    } catch (Exception $e) {
        // Em caso de erro (formato inválido), loga e retorna vazio
        error_log("Erro ao formatar data/hora no helper: " . $e->getMessage() . " Valor: " . $datetime);
        return ''; 
    }
}
