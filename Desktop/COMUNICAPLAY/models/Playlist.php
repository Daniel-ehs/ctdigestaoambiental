<?php

require_once __DIR__ . 
'/../models/Database.php';
require_once __DIR__ . 
'/../includes/functions.php'; // Adicionado para usar convertPathToUrl

class Playlist {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($nome, $telaId, $dataInicio, $dataFim, $usuarioCriadorId) {
        try {
            $sql = "INSERT INTO playlists (nome, tela_id, data_inicio, data_fim, usuario_criador_id, data_criacao) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$nome, $telaId, $dataInicio, $dataFim, $usuarioCriadorId]);
            
            $playlistId = $this->db->getConnection()->lastInsertId();
            error_log("Playlist criada com ID: $playlistId");
            
            return $playlistId;
        } catch (Exception $e) {
            error_log("Erro ao criar playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $nome, $dataInicio, $dataFim) {
        try {
            $sql = "UPDATE playlists SET nome = ?, data_inicio = ?, data_fim = ? WHERE id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$nome, $dataInicio, $dataFim, $id]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            // Primeiro remove as mídias
            $this->clearMidias($id);
            
            // Depois remove a playlist
            $sql = "DELETE FROM playlists WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao deletar playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $sql = "SELECT p.*, t.nome as tela_nome, t.hash_unico as tela_hash,
                           u.nome as criador_nome
                    FROM playlists p
                    LEFT JOIN telas t ON p.tela_id = t.id
                    LEFT JOIN usuarios u ON p.usuario_criador_id = u.id
                    WHERE p.id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($usuarioId = null, $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT p.*, t.nome as tela_nome, t.hash_unico as tela_hash,
                           u.nome as criador_nome,
                           (SELECT COUNT(*) FROM playlist_midias WHERE playlist_id = p.id) as total_midias
                    FROM playlists p
                    LEFT JOIN telas t ON p.tela_id = t.id
                    LEFT JOIN usuarios u ON p.usuario_criador_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if ($usuarioId) {
                $sql .= " AND p.usuario_criador_id = ?";
                $params[] = $usuarioId;
            }
            
            $sql .= " ORDER BY p.data_criacao DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar playlists: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByTela($telaId, $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT p.*, u.nome as criador_nome,
                           (SELECT COUNT(*) FROM playlist_midias WHERE playlist_id = p.id) as total_midias
                    FROM playlists p
                    LEFT JOIN usuarios u ON p.usuario_criador_id = u.id
                    WHERE p.tela_id = ?
                    ORDER BY p.data_inicio DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT ? OFFSET ?";
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            
            if ($limit > 0) {
                $stmt->execute([$telaId, $limit, $offset]);
            } else {
                $stmt->execute([$telaId]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar playlists da tela: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPlaylistAtiva($telaId) {
        try {
            $agora = date('Y-m-d H:i:s');
            
            $sql = "SELECT p.*, (SELECT COUNT(*) FROM playlist_midias WHERE playlist_id = p.id) as total_midias
                    FROM playlists p
                    WHERE p.tela_id = ? 
                    AND p.data_inicio <= ? 
                    AND p.data_fim >= ?
                    ORDER BY p.data_inicio DESC
                    LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$telaId, $agora, $agora]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar playlist ativa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * MÉTODO getMidias() CORRIGIDO - CONVERTE CAMINHOS FÍSICOS EM URLs WEB
     */
    public function getMidias($playlistId) {
        try {
            // SQL corrigido baseado na estrutura real do banco
            $sql = "SELECT pm.midia_id, pm.ordem, pm.tempo_exibicao,
                           m.id, m.nome, m.tipo, m.caminho_arquivo, m.url_externa, m.miniatura, m.duracao
                    FROM playlist_midias pm
                    INNER JOIN midias m ON pm.midia_id = m.id
                    WHERE pm.playlist_id = ? AND m.ativo = 1
                    ORDER BY pm.ordem ASC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$playlistId]);
            
            $midias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Normaliza os dados e CORRIGE OS CAMINHOS
            foreach ($midias as &$midia) {
                // *** CORREÇÃO PRINCIPAL: CONVERTE CAMINHO FÍSICO EM URL WEB ***
                if ($midia["tipo"] === "video" && !empty($midia["caminho_arquivo"])) {
                    $midia["caminho"] = convertPathToUrl($midia["caminho_arquivo"]);
                } else if ($midia["tipo"] === "youtube" && !empty($midia["url_externa"])) {
                    // YouTube usa URL externa
                    $midia["caminho"] = $midia["url_externa"];
                    
                } else if (($midia["tipo"] === "imagem" || $midia["tipo"] === "link_imagem") && !empty($midia["caminho_arquivo"])) {
                    // Imagens também precisam de correção de caminho
                    $midia["caminho"] = convertPathToUrl($midia["caminho_arquivo"]);
                    
                } else {
                    // Fallback
                    $midia["caminho"] = $midia["caminho_arquivo"] ?? $midia["url_externa"] ?? '';
                }
                
                // Garante que o tipo está correto
                if (empty($midia["tipo"])) {
                    $midia["tipo"] = $this->detectarTipoMidia($midia);
                }
            }
            
            error_log("Mídias carregadas para playlist $playlistId: " . count($midias) . " (caminhos corrigidos)");
            return $midias;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar mídias da playlist: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * MÉTODO addMidia() CORRIGIDO - BASEADO NO TESTE QUE FUNCIONOU
     */
    public function addMidia($playlistId, $midiaId, $ordem, $tempoExibicao = 30) {
        try {
            error_log("addMidia() chamado: playlist=$playlistId, midia=$midiaId, ordem=$ordem, tempo=$tempoExibicao");
            
            // SQL exato que funcionou no teste manual
            $sql = "INSERT INTO playlist_midias (playlist_id, midia_id, ordem, tempo_exibicao) VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $resultado = $stmt->execute([$playlistId, $midiaId, $ordem, $tempoExibicao]);
            
            if ($resultado) {
                error_log("✅ Mídia $midiaId adicionada com sucesso à playlist $playlistId");
                return true;
            } else {
                error_log("❌ Falha ao adicionar mídia $midiaId à playlist $playlistId");
                $errorInfo = $stmt->errorInfo();
                error_log("Erro SQL: " . json_encode($errorInfo));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ ERRO em addMidia(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    public function removeMidia($playlistId, $midiaId) {
        try {
            $sql = "DELETE FROM playlist_midias WHERE playlist_id = ? AND midia_id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$playlistId, $midiaId]);
        } catch (Exception $e) {
            error_log("Erro ao remover mídia da playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function clearMidias($playlistId) {
        try {
            $sql = "DELETE FROM playlist_midias WHERE playlist_id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$playlistId]);
        } catch (Exception $e) {
            error_log("Erro ao limpar mídias da playlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Detecta o tipo de mídia baseado nos dados
     */
    private function detectarTipoMidia($midia) {
        if (!empty($midia["url_externa"])) {
            if (strpos($midia["url_externa"], 'youtube.com') !== false || strpos($midia["url_externa"], 'youtu.be') !== false) {
                return 'youtube';
            }
            return 'link_imagem';
        }
        
        if (!empty($midia["caminho_arquivo"])) {
            $extensao = strtolower(pathinfo($midia["caminho_arquivo"], PATHINFO_EXTENSION));
            
            if (in_array($extensao, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'])) {
                return 'video';
            }
            
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                return 'imagem';
            }
        }
        
        return 'desconhecido';
    }
    
    // MÉTODOS AUXILIARES
    
    public function getCount($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM playlists WHERE 1=1";
            $params = [];
            
            if (isset($filtros["usuario_id"])) {
                $sql .= " AND usuario_criador_id = ?";
                $params[] = $filtros["usuario_id"];
            }
            
            if (isset($filtros["tela_id"])) {
                $sql .= " AND tela_id = ?";
                $params[] = $filtros["tela_id"];
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result["total"] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar playlists: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getDuracaoTotal($playlistId) {
        try {
            $sql = "SELECT SUM(tempo_exibicao) as duracao_total 
                    FROM playlist_midias 
                    WHERE playlist_id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$playlistId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result["duracao_total"] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular duração total: " . $e->getMessage());
            return 0;
        }
    }
}

?>



