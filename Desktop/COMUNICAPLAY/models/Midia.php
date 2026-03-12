<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../includes/functions.php';

class Midia {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($nome, $tipo, $caminhoArquivo = null, $urlExterna = null, $miniatura = null, $duracao = 10, $tamanhoArquivo = 0, $pastaId = null, $usuarioCriadorId) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO midias (nome, tipo, caminho_arquivo, url_externa, miniatura, duracao, tamanho_arquivo, pasta_id, usuario_criador_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$nome, $tipo, $caminhoArquivo, $urlExterna, $miniatura, $duracao, $tamanhoArquivo, $pastaId, $usuarioCriadorId])) {
                return $this->db->getConnection()->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar mídia: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($pastaId = null, $userId = null, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT m.*, p.nome as pasta_nome, u.nome as criador_nome 
                    FROM midias m 
                    LEFT JOIN pastas_midias p ON m.pasta_id = p.id 
                    LEFT JOIN usuarios u ON m.usuario_criador_id = u.id 
                    WHERE m.ativo = 1";
            
            $params = [];
            $types = "";
            
            if ($pastaId !== null) {
                if ($pastaId === 0) {
                    $sql .= " AND m.pasta_id IS NULL";
                } else {
                    $sql .= " AND m.pasta_id = ?";
                    $params[] = $pastaId;
                    $types .= "i";
                }
            }
            
            if ($userId !== null) {
                $sql .= " AND m.usuario_criador_id = ?";
                $params[] = $userId;
                $types .= "i";
            }
            
            $sql .= " ORDER BY m.nome";
            
            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            $midias = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $midias[] = $row;
            }
            
            return $midias;
        } catch (Exception $e) {
            error_log("Erro ao listar mídias: " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT m.*, p.nome as pasta_nome, u.nome as criador_nome 
                FROM midias m 
                LEFT JOIN pastas_midias p ON m.pasta_id = p.id 
                LEFT JOIN usuarios u ON m.usuario_criador_id = u.id 
                WHERE m.id = ? AND m.ativo = 1
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar mídia: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $nome, $duracao = null, $pastaId = null) {
        try {
            $sql = "UPDATE midias SET nome = ?";
            $params = [$nome];
            $types = "s";
            
            if ($duracao !== null) {
                $sql .= ", duracao = ?";
                $params[] = $duracao;
                $types .= "i";
            }
            
            if ($pastaId !== null) {
                $sql .= ", pasta_id = ?";
                $params[] = $pastaId === 0 ? null : $pastaId;
                $types .= "i";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Erro ao atualizar mídia: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            // Busca informações da mídia antes de deletar
            $midia = $this->getById($id);
            if (!$midia) {
                return false;
            }
            
            // Hard delete - remove do banco de dados
            $stmt = $this->db->getConnection()->prepare("DELETE FROM midias WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                // Remove arquivos físicos se existirem
                // Converte URLs públicas para caminhos físicos
                if ($midia['caminho_arquivo']) {
                    $caminhoFisico = str_replace([VIDEO_URL, IMAGE_URL], [VIDEO_PATH, IMAGE_PATH], $midia['caminho_arquivo']);
                    if (file_exists($caminhoFisico)) {
                        safeUnlink($caminhoFisico);
                    }
                }
                
                if ($midia['miniatura']) {
                    $caminhoFisicoThumbnail = str_replace(THUMBNAIL_URL, THUMBNAIL_PATH, $midia['miniatura']);
                    if (file_exists($caminhoFisicoThumbnail)) {
                        safeUnlink($caminhoFisicoThumbnail);
                    }
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao deletar mídia: " . $e->getMessage());
            return false;
        }
    }
    
    public function uploadVideo($file, $nome, $duracao, $pastaId = null, $usuarioCriadorId) {
        try {
            if (!isValidVideo($file)) {
                return ['success' => false, 'message' => 'Arquivo de vídeo inválido.'];
            }
            
            if ($file['size'] > UPLOAD_MAX_SIZE) {
                return ['success' => false, 'message' => 'Arquivo muito grande. Máximo permitido: ' . formatBytes(UPLOAD_MAX_SIZE)];
            }
            
            // Cria diretório se não existir
            createDirectoryIfNotExists(VIDEO_PATH);
            
            // Gera nome único para o arquivo
            $nomeArquivo = generateUniqueFileName($file['name']);
                    $caminhoCompleto = VIDEO_PATH . $nomeArquivo;
                    $caminhoPublico = VIDEO_URL . $nomeArquivo;
            
            // Move o arquivo
            if (move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
                // Cria a mídia no banco
                $midiaId = $this->create(
                    $nome,
                    "video",
                    $caminhoPublico,
                    null,
                    null,
                    $duracao,
                    $file["size"],
                    $pastaId,
                    $usuarioCriadorId
                );
                
                if ($midiaId) {
                    return [
                        'success' => true,
                        'message' => 'Vídeo enviado com sucesso.',
                        'midia_id' => $midiaId
                    ];
                } else {
                    safeUnlink($caminhoCompleto);
                    return ['success' => false, 'message' => 'Erro ao salvar informações do vídeo.'];
                }
            } else {
                return ['success' => false, 'message' => 'Erro ao fazer upload do arquivo.'];
            }
            
        } catch (Exception $e) {
            error_log("Erro no upload de vídeo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor.'];
        }
    }
    
    public function uploadImagem($file, $nome, $duracao = 10, $pastaId = null, $usuarioCriadorId) {
        try {
            if (!isValidImage($file)) {
                return ['success' => false, 'message' => 'Arquivo de imagem inválido.'];
            }
            
            if ($file['size'] > UPLOAD_MAX_SIZE) {
                return ['success' => false, 'message' => 'Arquivo muito grande. Máximo permitido: ' . formatBytes(UPLOAD_MAX_SIZE)];
            }
            
            // Cria diretórios se não existirem
            createDirectoryIfNotExists(IMAGE_PATH);
            createDirectoryIfNotExists(THUMBNAIL_PATH);
            
            // Gera nome único para o arquivo
            $nomeArquivo = generateUniqueFileName($file['name']);
            $caminhoCompleto = IMAGE_PATH . $nomeArquivo;
            $caminhoPublico = IMAGE_URL . $nomeArquivo;
            $caminhoThumbnail = THUMBNAIL_PATH . 'thumb_' . $nomeArquivo;
            $caminhoThumbnailPublico = THUMBNAIL_URL . 'thumb_' . $nomeArquivo;
            
            // Move o arquivo
            if (move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
                // Gera thumbnail
                $thumbnailGerada = generateImageThumbnail($caminhoCompleto, $caminhoThumbnail);
                
                // Cria a mídia no banco
                $midiaId = $this->create(
                    $nome,
                    'imagem',
                    $caminhoPublico,
                    null,
                    $thumbnailGerada ? $caminhoThumbnailPublico : null,
                    $duracao,
                    $file['size'],
                    $pastaId,
                    $usuarioCriadorId
                );
                
                if ($midiaId) {
                    return [
                        'success' => true,
                        'message' => 'Imagem enviada com sucesso.',
                        'midia_id' => $midiaId
                    ];
                } else {
                    safeUnlink($caminhoCompleto);
                    safeUnlink($caminhoThumbnail);
                    return ['success' => false, 'message' => 'Erro ao salvar informações da imagem.'];
                }
            } else {
                return ['success' => false, 'message' => 'Erro ao fazer upload do arquivo.'];
            }
            
        } catch (Exception $e) {
            error_log("Erro no upload de imagem: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor.'];
        }
    }
    
    public function addYouTube($url, $nome, $duracao = 0, $pastaId = null, $usuarioCriadorId) {
        try {
            $videoId = extractYouTubeId($url);
            if (!$videoId) {
                return ['success' => false, 'message' => 'URL do YouTube inválida.'];
            }
            
            // Cria diretório para thumbnails se não existir
            createDirectoryIfNotExists(THUMBNAIL_PATH);
            
            // Baixa thumbnail do YouTube
            $thumbnailUrl = getYouTubeThumbnail($videoId);
            $thumbnailPath = null;
            if ($thumbnailUrl) {
                $thumbnailPath = $thumbnailUrl; // Armazena a URL pública da thumbnail
            }
            
            // Cria a mídia no banco
            $midiaId = $this->create(
                $nome,
                'youtube',
                null,
                $url,
                $thumbnailPath,
                $duracao,
                0,
                $pastaId,
                $usuarioCriadorId
            );
            
            if ($midiaId) {
                return [
                    'success' => true,
                    'message' => 'Vídeo do YouTube adicionado com sucesso.',
                    'midia_id' => $midiaId
                ];
            } else {
                safeUnlink($thumbnailPath);
                return ['success' => false, 'message' => 'Erro ao salvar vídeo do YouTube.'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar YouTube: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor.'];
        }
    }
    
    public function addLinkImagem($url, $nome, $duracao = 10, $pastaId = null, $usuarioCriadorId) {
        try {
            if (!validateUrl($url)) {
                return ['success' => false, 'message' => 'URL inválida.'];
            }
            
            // Cria a mídia no banco
            $midiaId = $this->create(
                $nome,
                'link_imagem',
                null,
                $url,
                $url, // Usa a própria URL como thumbnail
                $duracao,
                0,
                $pastaId,
                $usuarioCriadorId
            );
            
            if ($midiaId) {
                return [
                    'success' => true,
                    'message' => 'Link de imagem adicionado com sucesso.',
                    'midia_id' => $midiaId
                ];
            } else {
                return ['success' => false, 'message' => 'Erro ao salvar link de imagem.'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar link de imagem: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor.'];
        }
    }
    
    public function getCountByType() {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    tipo,
                    COUNT(*) as count
                FROM midias 
                WHERE ativo = 1 
                GROUP BY tipo
            ");
            $stmt->execute();
            
            $counts = [
                'video' => 0,
                'imagem' => 0,
                'youtube' => 0,
                'link_imagem' => 0
            ];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $counts[$row['tipo']] = (int)$row['count'];
            }
            
            return $counts;
        } catch (Exception $e) {
            error_log("Erro ao contar mídias por tipo: " . $e->getMessage());
            return ['video' => 0, 'imagem' => 0, 'youtube' => 0, 'link_imagem' => 0];
        }
    }
    
    public function getMidiasByUser($userId, $pastaId = null) {
        try {
            $sql = "SELECT m.*, p.nome as pasta_nome 
                    FROM midias m 
                    LEFT JOIN pastas_midias p ON m.pasta_id = p.id 
                    LEFT JOIN usuarios u ON m.usuario_criador_id = u.id 
                    WHERE m.ativo = 1 AND m.usuario_criador_id = ?";
            
            $params = [$userId];
            $types = "i";
            
            if ($pastaId !== null) {
                if ($pastaId === 0) {
                    $sql .= " AND m.pasta_id IS NULL";
                } else {
                    $sql .= " AND m.pasta_id = ?";
                    $params[] = $pastaId;
                    $types .= "i";
                }
            }
            
            $sql .= " ORDER BY m.nome";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            $midias = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $midias[] = $row;
            }
            
            return $midias;
        } catch (Exception $e) {
            error_log("Erro ao buscar mídias do usuário: " . $e->getMessage());
            return [];
        }
    }
}

?>

