<?php

require_once __DIR__ . 
'/../models/Midia.php';
require_once __DIR__ . 
'/../models/PastaMidia.php';
require_once __DIR__ . 
'/../includes/session.php';
require_once __DIR__ . 
'/../includes/functions.php';

class MidiaController {
    private $midiaModel;
    private $pastaModel;
    
    public function __construct() {
        $this->midiaModel = new Midia();
        $this->pastaModel = new PastaMidia();
    }
    
    public function uploadVideo($file, $nome, $duracao, $pastaId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            $result = $this->midiaModel->uploadVideo($file, $nome, $duracao, $pastaId, $userId);
            
            if ($result['success']) {
                logActivity('video_uploaded', "Vídeo {$file['name']} enviado", $userId);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erro no upload de vídeo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function uploadImagem($file, $nome, $duracao = 10, $pastaId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Valida duração
            if ($duracao < 1 || $duracao > 300) {
                return [
                    'success' => false,
                    'message' => 'Duração deve estar entre 1 e 300 segundos.'
                ];
            }
            
            $result = $this->midiaModel->uploadImagem($file, $nome, $duracao, $pastaId, $userId);
            
            if ($result['success']) {
                logActivity('image_uploaded', "Imagem {$file['name']} enviada", $userId);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erro no upload de imagem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function addYouTube($url, $nome, $duracao = 0, $pastaId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome é obrigatório.'
                ];
            }
            
            if ($duracao < 0 || $duracao > 7200) { // Máximo 2 horas
                return [
                    'success' => false,
                    'message' => 'Duração deve estar entre 0 e 7200 segundos.'
                ];
            }
            
            $result = $this->midiaModel->addYouTube($url, $nome, $duracao, $pastaId, $userId);
            
            if ($result['success']) {
                logActivity('youtube_added', "YouTube {$nome} adicionado", $userId);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar YouTube: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function addLinkImagem($url, $nome, $duracao = 10, $pastaId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome é obrigatório.'
                ];
            }
            
            if ($duracao < 1 || $duracao > 300) {
                return [
                    'success' => false,
                    'message' => 'Duração deve estar entre 1 e 300 segundos.'
                ];
            }
            
            $result = $this->midiaModel->addLinkImagem($url, $nome, $duracao, $pastaId, $userId);
            
            if ($result['success']) {
                logActivity('link_image_added', "Link de imagem {$nome} adicionado", $userId);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar link de imagem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function update($id, $nome, $duracao = null, $pastaId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Verifica se a mídia existe
            $midia = $this->midiaModel->getById($id);
            if (!$midia) {
                return [
                    'success' => false,
                    'message' => 'Mídia não encontrada.'
                ];
            }
            
            // Verifica permissão (apenas o criador ou admin pode editar)
            $user = SessionManager::getUser();
            if ($user['tipo'] !== 'administrador' && $midia['usuario_criador_id'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para editar esta mídia.'
                ];
            }
            
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome é obrigatório.'
                ];
            }
            
            if ($duracao !== null && ($duracao < 1 || $duracao > 7200)) {
                return [
                    'success' => false,
                    'message' => 'Duração deve estar entre 1 e 7200 segundos.'
                ];
            }
            
            // Atualiza a mídia
            if ($this->midiaModel->update($id, $nome, $duracao, $pastaId)) {
                logActivity('midia_updated', "Mídia {$nome} atualizada", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Mídia atualizada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar mídia.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar mídia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function delete($id) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Verifica se a mídia existe
            $midia = $this->midiaModel->getById($id);
            if (!$midia) {
                return [
                    'success' => false,
                    'message' => 'Mídia não encontrada.'
                ];
            }
            
            // Verifica permissão (apenas o criador ou admin pode deletar)
            $user = SessionManager::getUser();
            if ($user['tipo'] !== 'administrador' && $midia['usuario_criador_id'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para deletar esta mídia.'
                ];
            }
            
            // Deleta a mídia
            if ($this->midiaModel->delete($id)) {
                logActivity('midia_deleted', "Mídia {$midia['nome']} deletada", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Mídia deletada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao deletar mídia.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao deletar mídia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function getAll($pastaId = null, $page = 1, $limit = 20) {
        try {
            $userId = SessionManager::getUserId();
            $user = SessionManager::getUser();
            
            $offset = ($page - 1) * $limit;
            
            // Administradores veem todas as mídias, gerentes apenas as suas
            if ($user['tipo'] === 'administrador') {
                $midias = $this->midiaModel->getAll($pastaId, null, $limit, $offset);
            } else {
                $midias = $this->midiaModel->getAll($pastaId, $userId, $limit, $offset);
            }
            
            return [
                'success' => true,
                'midias' => $midias,
                'page' => $page,
                'limit' => $limit
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar mídias: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar mídias.',
                'midias' => []
            ];
        }
    }
    
    public function getById($id) {
        try {
            $midia = $this->midiaModel->getById($id);
            
            if ($midia) {
                return [
                    'success' => true,
                    'midia' => $midia
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Mídia não encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar mídia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar mídia.'
            ];
        }
    }
    
    public function getCountByType() {
        try {
            $counts = $this->midiaModel->getCountByType();
            
            return [
                'success' => true,
                'counts' => $counts
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao contar mídias: " . $e->getMessage());
            return [
                'success' => false,
                'counts' => ['video' => 0, 'imagem' => 0, 'youtube' => 0, 'link_imagem' => 0]
            ];
        }
    }
    
    // Métodos para gerenciamento de pastas
    
    public function createPasta($nome, $descricao = '', $pastaPaiId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome da pasta é obrigatório.'
                ];
            }
            
            if (strlen($nome) > 100) {
                return [
                    'success' => false,
                    'message' => 'Nome da pasta deve ter no máximo 100 caracteres.'
                ];
            }
            
            // Verifica se já existe pasta com o mesmo nome no mesmo nível
            if ($this->pastaModel->nomeExists($nome, $pastaPaiId)) {
                return [
                    'success' => false,
                    'message' => 'Já existe uma pasta com este nome neste local.'
                ];
            }
            
            $pastaId = $this->pastaModel->create($nome, $descricao, $pastaPaiId);
            
            if ($pastaId) {
                logActivity('pasta_created', "Pasta {$nome} criada", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Pasta criada com sucesso.',
                    'pasta_id' => $pastaId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar pasta.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar pasta: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function updatePasta($id, $nome, $descricao = '', $pastaPaiId = null) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome da pasta é obrigatório.'
                ];
            }
            
            if (strlen($nome) > 100) {
                return [
                    'success' => false,
                    'message' => 'Nome da pasta deve ter no máximo 100 caracteres.'
                ];
            }
            
            // Verifica se a pasta existe
            $pasta = $this->pastaModel->getById($id);
            if (!$pasta) {
                return [
                    'success' => false,
                    'message' => 'Pasta não encontrada.'
                ];
            }
            
            // Verifica se já existe pasta com o mesmo nome no mesmo nível (excluindo a atual)
            if ($this->pastaModel->nomeExists($nome, $pastaPaiId, $id)) {
                return [
                    'success' => false,
                    'message' => 'Já existe uma pasta com este nome neste local.'
                ];
            }
            
            if ($this->pastaModel->update($id, $nome, $descricao, $pastaPaiId)) {
                logActivity('pasta_updated', "Pasta {$nome} atualizada", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Pasta atualizada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar pasta.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar pasta: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function deletePasta($id) {
        try {
            $userId = SessionManager::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ];
            }
            
            // Verifica se a pasta existe
            $pasta = $this->pastaModel->getById($id);
            if (!$pasta) {
                return [
                    'success' => false,
                    'message' => 'Pasta não encontrada.'
                ];
            }
            
            // Verifica se a pasta está vazia
            if ($pasta['total_midias'] > 0 || $pasta['total_subpastas'] > 0) {
                return [
                    'success' => false,
                    'message' => 'A pasta não está vazia. Mova ou delete o conteúdo antes de deletar a pasta.'
                ];
            }
            
            if ($this->pastaModel->delete($id)) {
                logActivity('pasta_deleted', "Pasta {$pasta['nome']} deletada", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Pasta deletada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao deletar pasta.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao deletar pasta: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function getPastas($pastaPaiId = null) {
        try {
            $pastas = $this->pastaModel->getAll($pastaPaiId);
            
            return [
                'success' => true,
                'pastas' => $pastas
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar pastas: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar pastas.',
                'pastas' => []
            ];
        }
    }
    
    public function getBreadcrumb($pastaId) {
        try {
            $breadcrumb = $this->pastaModel->getBreadcrumb($pastaId);
            
            return [
                'success' => true,
                'breadcrumb' => $breadcrumb
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao gerar breadcrumb: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar caminho da pasta.',
                'breadcrumb' => []
            ];
        }
    }
    
    public function getPastasFlatList() {
        try {
            $pastas = $this->pastaModel->getFlatList();
            
            return [
                'success' => true,
                'pastas' => $pastas
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao gerar lista de pastas: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar lista de pastas.',
                'pastas' => []
            ];
        }
    }
}

?>
?>
?>


