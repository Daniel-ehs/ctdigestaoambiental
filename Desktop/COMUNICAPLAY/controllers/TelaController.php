<?php

require_once __DIR__ . '/../models/Tela.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

class TelaController {
    private $telaModel;
    
    public function __construct() {
        $this->telaModel = new Tela();
    }
    
    public function create($nome, $descricao = '', $resolucao = '1920x1080', $localizacao = '', $comMoldura = 0) {
        try {
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome da tela é obrigatório.'
                ];
            }
            
            if (strlen($nome) > 100) {
                return [
                    'success' => false,
                    'message' => 'Nome da tela deve ter no máximo 100 caracteres.'
                ];
            }
            
            // Valida resolução
            $resolucoesValidas = ['1280x720', '1920x1080', '3840x2160'];
            if (!in_array($resolucao, $resolucoesValidas)) {
                return [
                    'success' => false,
                    'message' => 'Resolução inválida.'
                ];
            }
            
            // Cria a tela
            $result = $this->telaModel->create($nome, $descricao, $resolucao, $localizacao, $comMoldura);
            
            if ($result) {
                $currentUser = SessionManager::getUser();
                logActivity('tela_created', "Tela {$nome} criada", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Tela criada com sucesso.',
                    'tela_id' => $result['id'],
                    'hash_unico' => $result['hash_unico']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar tela. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar tela: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function update($id, $nome, $descricao = '', $resolucao = '1920x1080', $localizacao = '', $comMoldura = 0) {
        try {
            // Validações
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome da tela é obrigatório.'
                ];
            }
            
            if (strlen($nome) > 100) {
                return [
                    'success' => false,
                    'message' => 'Nome da tela deve ter no máximo 100 caracteres.'
                ];
            }
            
            // Verifica se a tela existe
            $tela = $this->telaModel->getById($id);
            if (!$tela) {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
            // Valida resolução
            $resolucoesValidas = ['1280x720', '1920x1080', '3840x2160'];
            if (!in_array($resolucao, $resolucoesValidas)) {
                return [
                    'success' => false,
                    'message' => 'Resolução inválida.'
                ];
            }
            
            // Atualiza a tela
            if ($this->telaModel->update($id, $nome, $descricao, $resolucao, $localizacao, $comMoldura)) {
                $currentUser = SessionManager::getUser();
                logActivity('tela_updated', "Tela {$nome} atualizada", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Tela atualizada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar tela. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar tela: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function delete($id) {
        try {
            // Verifica se a tela existe
            $tela = $this->telaModel->getById($id);
            if (!$tela) {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
            // Deleta a tela
            if ($this->telaModel->delete($id)) {
                $currentUser = SessionManager::getUser();
                logActivity('tela_deleted', "Tela {$tela['nome']} deletada", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Tela deletada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao deletar tela. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao deletar tela: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function getAll($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $telas = $this->telaModel->getAll($limit, $offset);
            
            // Busca o total de telas
            $total = $this->telaModel->getTotalCount();
            
            return [
                'success' => true,
                'telas' => $telas,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar telas: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar telas.',
                'telas' => [],
                'total' => 0
            ];
        }
    }
    
    public function getById($id) {
        try {
            $tela = $this->telaModel->getById($id);
            
            if ($tela) {
                return [
                    'success' => true,
                    'tela' => $tela
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar tela: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar tela.'
            ];
        }
    }
    
    public function getByHash($hash) {
        try {
            $tela = $this->telaModel->getByHash($hash);
            
            if ($tela) {
                return [
                    'success' => true,
                    'tela' => $tela
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar tela por hash: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar tela.'
            ];
        }
    }
    
    public function regenerateHash($id) {
        try {
            $tela = $this->telaModel->getById($id);
            if (!$tela) {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
            $novoHash = $this->telaModel->regenerateHash($id);
            
            if ($novoHash) {
                $currentUser = SessionManager::getUser();
                logActivity('tela_hash_regenerated', "Hash da tela {$tela['nome']} regenerado", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Link da tela regenerado com sucesso.',
                    'novo_hash' => $novoHash
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao regenerar link da tela.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao regenerar hash: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function updateHeartbeat($hash) {
        try {
            if ($this->telaModel->updateHeartbeat($hash)) {
                return [
                    'success' => true,
                    'message' => 'Heartbeat atualizado.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar heartbeat: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function getStatusSummary() {
        try {
            $summary = $this->telaModel->getStatusSummary();
            
            return [
                'success' => true,
                'summary' => $summary
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar resumo de status: " . $e->getMessage());
            return [
                'success' => false,
                'summary' => ['total' => 0, 'online' => 0, 'offline' => 0]
            ];
        }
    }
    
    public function getTelasByUser($userId) {
        try {
            $telas = $this->telaModel->getTelasByUser($userId);
            
            return [
                'success' => true,
                'telas' => $telas
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar telas do usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar telas do usuário.',
                'telas' => []
            ];
        }
    }
    
    public function checkUserAccess($telaId, $userId) {
        try {
            // Administradores têm acesso a todas as telas
            $user = SessionManager::getUser();
            if ($user['tipo'] === 'administrador') {
                return true;
            }
            
            return $this->telaModel->hasUserAccess($telaId, $userId);
            
        } catch (Exception $e) {
            error_log("Erro ao verificar acesso: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPlaylistAtiva($telaId) {
        try {
            $playlist = $this->telaModel->getPlaylistAtiva($telaId);
            
            if ($playlist) {
                // Busca as mídias da playlist
                $midias = $this->telaModel->getPlaylistMidias($playlist['id']);
                $playlist['midias'] = $midias;
                
                return [
                    'success' => true,
                    'playlist' => $playlist
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Nenhuma playlist ativa encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar playlist ativa: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar playlist.'
            ];
        }
    }
    
    public function generatePlayUrl($hash, $comMoldura = null) {
        // Se com_moldura não foi fornecido, busca a informação da tela
        if ($comMoldura === null) {
            $tela = $this->telaModel->getByHash($hash);
            $comMoldura = $tela ? $tela['com_moldura'] : 0;
        }
        
        // Gera URL baseada na configuração de moldura
        if ($comMoldura) {
            return SITE_URL . "/public/play_moldura.php?hash=" . $hash;
        } else {
            return SITE_URL . "/public/play.php?hash=" . $hash;
        }
    }
}

?>

