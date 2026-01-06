<?php
/**
 * Serviço para upload e manipulação de imagens
 */

class ImageService {
    /**
     * Fazer upload de imagem
     * 
     * @param array $file Arquivo enviado via $_FILES
     * @param string $destDir Diretório de destino
     * @return string|false Nome do arquivo salvo ou false em caso de erro
     */
    public function upload($file, $destDir) {
        // Verificar se o arquivo foi enviado corretamente
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verificar extensão
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }
        
        // Gerar nome único para o arquivo
        $fileName = $this->generateUniqueFilename($extension);
        $destPath = $destDir . '/' . $fileName;
        
        // Mover arquivo para diretório de destino
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return false;
        }
        
        // Otimizar imagem
        $this->optimizeImage($destPath, $extension);
        
        return $fileName;
    }
    
    /**
     * Gerar nome único para arquivo
     * 
     * @param string $extension Extensão do arquivo
     * @return string Nome do arquivo
     */
    private function generateUniqueFilename($extension) {
        return uniqid('img_') . '_' . date('YmdHis') . '.' . $extension;
    }
    
    /**
     * Otimizar imagem
     * 
     * @param string $filePath Caminho do arquivo
     * @param string $extension Extensão do arquivo
     * @return bool Sucesso ou falha
     */
    private function optimizeImage($filePath, $extension) {
        // Carregar imagem conforme extensão
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
                
            case 'png':
                $image = imagecreatefrompng($filePath);
                break;
                
            case 'gif':
                $image = imagecreatefromgif($filePath);
                break;
                
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // Redimensionar se for muito grande
        $maxWidth = 1200;
        $maxHeight = 1200;
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > $maxWidth || $height > $maxHeight) {
            // Calcular novas dimensões mantendo proporção
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = intval($height * $maxWidth / $width);
            } else {
                $newHeight = $maxHeight;
                $newWidth = intval($width * $maxHeight / $height);
            }
            
            // Criar nova imagem redimensionada
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparência para PNG
            if ($extension === 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Liberar memória da imagem original
            imagedestroy($image);
            $image = $newImage;
        }
        
        // Salvar imagem otimizada
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $filePath, 85); // Qualidade 85%
                break;
                
            case 'png':
                imagepng($image, $filePath, 8); // Compressão 8
                break;
                
            case 'gif':
                imagegif($image, $filePath);
                break;
        }
        
        // Liberar memória
        imagedestroy($image);
        
        return true;
    }
    
    /**
     * Excluir imagem
     * 
     * @param string $fileName Nome do arquivo
     * @param string $dir Diretório
     * @return bool Sucesso ou falha
     */
    public function delete($fileName, $dir) {
        $filePath = $dir . '/' . $fileName;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
}
