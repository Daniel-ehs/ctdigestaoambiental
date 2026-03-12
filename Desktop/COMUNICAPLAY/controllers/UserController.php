<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function create($nome, $email, $senha, $tipo = 'gerente') {
        try {
            // Validações
            if (empty($nome) || empty($email) || empty($senha)) {
                return [
                    'success' => false,
                    'message' => 'Nome, email e senha são obrigatórios.'
                ];
            }
            
            if (!validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido.'
                ];
            }
            
            if (!validatePassword($senha)) {
                return [
                    'success' => false,
                    'message' => 'A senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.'
                ];
            }
            
            if (!in_array($tipo, ['administrador', 'gerente'])) {
                return [
                    'success' => false,
                    'message' => 'Tipo de usuário inválido.'
                ];
            }
            
            // Verifica se o email já existe
            if ($this->userModel->emailExists($email)) {
                return [
                    'success' => false,
                    'message' => 'Este email já está em uso.'
                ];
            }
            
            // Cria o usuário
            $userId = $this->userModel->create($nome, $email, $senha, $tipo);
            
            if ($userId) {
                $currentUser = SessionManager::getUser();
                logActivity('user_created', "Usuário {$nome} criado", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Usuário criado com sucesso.',
                    'user_id' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar usuário. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function update($id, $nome, $email, $tipo, $ativo = 1) {
        try {
            // Validações
            if (empty($nome) || empty($email)) {
                return [
                    'success' => false,
                    'message' => 'Nome e email são obrigatórios.'
                ];
            }
            
            if (!validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido.'
                ];
            }
            
            if (!in_array($tipo, ['administrador', 'gerente'])) {
                return [
                    'success' => false,
                    'message' => 'Tipo de usuário inválido.'
                ];
            }
            
            // Verifica se o usuário existe
            $user = $this->userModel->getById($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ];
            }
            
            // Verifica se o email já existe (excluindo o usuário atual)
            if ($this->userModel->emailExists($email, $id)) {
                return [
                    'success' => false,
                    'message' => 'Este email já está em uso por outro usuário.'
                ];
            }
            
            // Atualiza o usuário
            if ($this->userModel->update($id, $nome, $email, $tipo, $ativo)) {
                $currentUser = SessionManager::getUser();
                logActivity('user_updated', "Usuário {$nome} atualizado", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar usuário. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function delete($id) {
        try {
            // Verifica se o usuário existe
            $user = $this->userModel->getById($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ];
            }
            
            // Não permite deletar o próprio usuário
            $currentUser = SessionManager::getUser();
            if ($currentUser['id'] == $id) {
                return [
                    'success' => false,
                    'message' => 'Você não pode deletar sua própria conta.'
                ];
            }
            
            // Deleta o usuário
            if ($this->userModel->delete($id)) {
                logActivity('user_deleted', "Usuário {$user['nome']} deletado", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Usuário deletado com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao deletar usuário. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function getAll($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $users = $this->userModel->getAll($limit, $offset);
            $total = $this->userModel->getTotalCount();
            
            return [
                'success' => true,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar usuários.',
                'users' => [],
                'total' => 0
            ];
        }
    }
    
    public function getById($id) {
        try {
            $user = $this->userModel->getById($id);
            
            if ($user) {
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar usuário.'
            ];
        }
    }
    
    public function associateToTela($userId, $telaId) {
        try {
            if ($this->userModel->associateToTela($userId, $telaId)) {
                $currentUser = SessionManager::getUser();
                logActivity('user_tela_associated', "Usuário {$userId} associado à tela {$telaId}", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Usuário associado à tela com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao associar usuário à tela.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao associar usuário à tela: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function removeFromTela($userId, $telaId) {
        try {
            if ($this->userModel->removeFromTela($userId, $telaId)) {
                $currentUser = SessionManager::getUser();
                logActivity('user_tela_removed', "Usuário {$userId} removido da tela {$telaId}", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Associação removida com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao remover associação.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao remover associação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function getUserTelas($userId) {
        try {
            $telas = $this->userModel->getUserTelas($userId);
            
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
    
    public function resetPassword($id, $newPassword) {
        try {
            if (!validatePassword($newPassword)) {
                return [
                    'success' => false,
                    'message' => 'A senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.'
                ];
            }
            
            $user = $this->userModel->getById($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ];
            }
            
            if ($this->userModel->updatePassword($id, $newPassword)) {
                $currentUser = SessionManager::getUser();
                logActivity('password_reset', "Senha do usuário {$user['nome']} foi redefinida", $currentUser['id']);
                
                return [
                    'success' => true,
                    'message' => 'Senha redefinida com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao redefinir senha.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao redefinir senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
}

?>

