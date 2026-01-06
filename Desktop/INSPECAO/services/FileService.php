<?php
/**
 * Classe para manipulação de arquivos e diretórios
 */

class FileService {
    /**
     * Criar diretório se não existir
     * 
     * @param string $dir Caminho do diretório
     * @return bool Sucesso ou falha
     */
    public function createDirectoryIfNotExists($dir) {
        if (!file_exists($dir)) {
            return mkdir($dir, 0755, true);
        }
        
        return true;
    }
    
    /**
     * Verificar se extensão de arquivo é permitida
     * 
     * @param string $filename Nome do arquivo
     * @param array $allowedExtensions Lista de extensões permitidas
     * @return bool Verdadeiro se extensão for permitida
     */
    public function isAllowedExtension($filename, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions);
    }
    
    /**
     * Gerar nome único para arquivo
     * 
     * @param string $originalName Nome original do arquivo
     * @return string Nome único gerado
     */
    public function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid('file_') . '_' . date('YmdHis') . '.' . $extension;
    }
    
    /**
     * Obter tamanho de arquivo formatado
     * 
     * @param int $bytes Tamanho em bytes
     * @return string Tamanho formatado
     */
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Listar arquivos em um diretório
     * 
     * @param string $dir Caminho do diretório
     * @param array $extensions Filtrar por extensões
     * @return array Lista de arquivos
     */
    public function listFiles($dir, $extensions = []) {
        $files = [];
        
        if (!file_exists($dir) || !is_dir($dir)) {
            return $files;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            
            if (is_file($path)) {
                $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                
                if (empty($extensions) || in_array($extension, $extensions)) {
                    $files[] = [
                        'name' => $item,
                        'path' => $path,
                        'size' => filesize($path),
                        'formatted_size' => $this->formatFileSize(filesize($path)),
                        'extension' => $extension,
                        'modified' => filemtime($path)
                    ];
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Excluir arquivo
     * 
     * @param string $path Caminho do arquivo
     * @return bool Sucesso ou falha
     */
    public function deleteFile($path) {
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        
        return false;
    }
}
