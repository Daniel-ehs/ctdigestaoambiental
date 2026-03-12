<?php

require_once __DIR__ . 
'/../config/config.php';

/**
 * Sanitiza uma string para exibição segura
 */
function sanitize($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida um email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida uma senha
 */
function validatePassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Gera um hash único
 */
function generateUniqueHash($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Formata bytes para exibição legível
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Formata data para exibição
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    return date($format, strtotime($date));
}

/**
 * Verifica se um arquivo é uma imagem válida
 */
function isValidImage($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $allowedExtensions = ALLOWED_IMAGE_TYPES;
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    // 1. Verifica a extensão do arquivo
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    // 2. Verifica o tipo MIME real do arquivo usando finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp' // Adicionado suporte a webp
    ];

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return false;
    }

    // 3. Verifica se é uma imagem válida usando getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    return $imageInfo !== false;
}

/**
 * Verifica se um arquivo é um vídeo válido
 */
function isValidVideo($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $allowedExtensions = ALLOWED_VIDEO_TYPES;
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    // 1. Verifica a extensão do arquivo
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }

    // 2. Verifica o tipo MIME real do arquivo usando finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimeTypes = [
        'video/mp4',
        'video/avi',
        'video/quicktime', // mov
        'video/x-msvideo', // avi
        'video/x-flv',     // flv
        'video/webm'
    ];

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return false;
    }

    // Para vídeos, a verificação de extensão e MIME type já é um bom indicativo.
    // Poderíamos adicionar uma verificação mais profunda com ffmpeg, mas isso exigiria
    // a instalação do ffmpeg no servidor e seria mais complexo.
    return true;
}

/**
 * Gera uma miniatura para uma imagem
 */
function generateImageThumbnail($sourcePath, $thumbnailPath, $width = 200, $height = 150) {
    try {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Cria a imagem source baseada no tipo
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp': // Adicionado suporte a webp
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Calcula as dimensões mantendo a proporção
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);
        
        // Cria a imagem thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserva transparência para PNG e WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensiona a imagem
        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        
        // Salva a thumbnail
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case 'image/png':
                $result = imagepng($thumbnail, $thumbnailPath, 8);
                break;
            case 'image/gif':
                $result = imagegif($thumbnail, $thumbnailPath);
                break;
            case 'image/webp': // Adicionado suporte a webp
                $result = imagewebp($thumbnail, $thumbnailPath, 85);
                break;
        }
        
        // Libera memória
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $result;
    } catch (Exception $e) {
        error_log("Erro ao gerar thumbnail: " . $e->getMessage());
        return false;
    }
}

/**
 * Extrai ID do vídeo do YouTube de uma URL
 */
function extractYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : false;
}

/**
 * Gera URL da thumbnail do YouTube
 */
function getYouTubeThumbnail($videoId) {
    return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
}

/**
 * Valida URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Cria diretório se não existir
 */
function createDirectoryIfNotExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * Remove arquivo com segurança
 */
function safeUnlink($filePath) {
    if (file_exists($filePath) && is_file($filePath)) {
        return unlink($filePath);
    }
    return true;
}

/**
 * Gera nome de arquivo único
 */
function generateUniqueFileName($originalName) {
    $fileInfo = pathinfo($originalName);
    $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';
    $baseName = isset($fileInfo['filename']) ? $fileInfo['filename'] : 'file';
    
    return $baseName . '_' . time() . '_' . uniqid() . $extension;
}

/**
 * Converte segundos para formato legível
 */
function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . 'm' . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . 's' : '');
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '') . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . 's' : '');
    }
}

/**
 * Verifica se uma string é JSON válido
 */
function isValidJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Log de atividades do sistema
 */
function logActivity($action, $details = '', $userId = null) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $userId,
        'action' => $action,
        'details' => IS_DEVELOPMENT ? $details : 'Detalhes ocultos em produção',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $logFile = __DIR__ . '/../logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Retorna status da tela baseado na última verificação
 */
function getTelaStatus($ultimaVerificacao) {
    if (empty($ultimaVerificacao)) {
        return 'offline';
    }
    
    $lastCheck = strtotime($ultimaVerificacao);
    $now = time();
    $diff = $now - $lastCheck;
    
    // Se a última verificação foi há mais de 10 minutos, considera offline
    return $diff <= 600 ? 'online' : 'offline';
}

/**
 * Sanitiza nome de arquivo
 */
function sanitizeFileName($filename) {
    // Remove caracteres especiais e espaços
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    // Remove múltiplos underscores consecutivos
    $filename = preg_replace('/_+/', '_', $filename);
    // Remove underscores do início e fim
    $filename = trim($filename, '_');
    
    return $filename;
}

/**
 * Converte caminho absoluto do servidor para URL relativa
 */
function convertPathToUrl($path) {
    if (empty($path)) {
        return '';
    }
    
    // Se já é uma URL externa, retorna como está
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    
    // Remove o caminho absoluto do servidor e converte para URL relativa
    $path = str_replace('/home2/dani7103/epi.danielsantos.eng.br/config/../', '', $path);
    $path = str_replace('/home2/dani7103/epi.danielsantos.eng.br/', '', $path);
    
    // Se o caminho já começa com assets/, adiciona apenas ../
    if (strpos($path, 'assets/') === 0) {
        return '../' . $path;
    }
    
    // Se não tem assets/, mas tem uploads/, adiciona o prefixo correto
    if (strpos($path, 'uploads/') !== false) {
        return '../assets/' . substr($path, strpos($path, 'uploads/'));
    }
    
    return $path;
}

/**
 * Gera URL correta para thumbnail
 */
function getThumbnailUrl($midia) {
    if (!empty($midia['miniatura'])) {
        return convertPathToUrl($midia['miniatura']);
    }
    
    // Para imagens, usa o próprio arquivo como thumbnail
    if ($midia['tipo'] === 'imagem' && !empty($midia['caminho_arquivo'])) {
        return convertPathToUrl($midia['caminho_arquivo']);
    }
    
    // Para YouTube, gera thumbnail se tiver URL externa
    if ($midia['tipo'] === 'youtube' && !empty($midia['url_externa'])) {
        $videoId = extractYouTubeId($midia['url_externa']);
        if ($videoId) {
            return getYouTubeThumbnail($videoId);
        }
    }
    
    // Para link de imagem, usa a URL externa
    if ($midia['tipo'] === 'link_imagem' && !empty($midia['url_externa'])) {
        return $midia['url_externa'];
    }
    
    return '';
}

/**
 * Gera URL correta para arquivo de mídia
 */
function getMediaUrl($midia) {
    if ($midia['tipo'] === 'youtube' || $midia['tipo'] === 'link_imagem') {
        return $midia['url_externa'] ?? '';
    }
    
    return convertPathToUrl($midia['caminho_arquivo'] ?? '');
}

/**
 * Obtém a duração de um vídeo usando ffprobe (parte do FFmpeg)
 */
function getVideoDuration($filePath) {
    if (!file_exists($filePath)) {
        return 0;
    }
    try {
        // Comando ffprobe para obter a duração em segundos
        $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
        $duration = shell_exec($command);
        return floor(floatval($duration));
    } catch (Exception $e) {
        error_log("Erro ao obter duração do vídeo com ffprobe: " . $e->getMessage());
        return 0;
    }
}

?>