<?php

require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($nome, $email, $senha, $tipo = 'gerente') {
        try {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $this->db->getConnection()->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash, $tipo]);
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function authenticate($email, $senha) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($senha, $user['senha'])) {
                unset($user['senha']); // Remove a senha do retorno
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT id, nome, email, tipo, ativo, data_criacao FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch (Exception $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT id, nome, email, tipo, ativo, data_criacao FROM usuarios ORDER BY nome";
            
            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$limit, $offset]);
            } else {
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalCount() {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM usuarios");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erro ao contar usuários: " . $e->getMessage());
            return 0;
        }
    }
    
    public function update($id, $nome, $email, $tipo, $ativo = 1) {
        try {
            $stmt = $this->db->getConnection()->prepare("UPDATE usuarios SET nome = ?, email = ?, tipo = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $tipo, $ativo, $id]);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword($id, $novaSenha) {
        try {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            $stmt = $this->db->getConnection()->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->execute([$senhaHash, $id]);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->getConnection()->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->getConnection()->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->db->getConnection()->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
            }
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserTelas($userId) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT t.id, t.nome, t.descricao, t.status, t.ultima_verificacao 
                FROM telas t 
                INNER JOIN usuario_telas ut ON t.id = ut.tela_id 
                WHERE ut.usuario_id = ? AND t.ativo = 1
                ORDER BY t.nome
            ");
            $stmt->execute([$userId]);
            
            $telas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $telas;
        } catch (Exception $e) {
            error_log("Erro ao buscar telas do usuário: " . $e->getMessage());
            return [];
        }
    }
    
    public function associateToTela($userId, $telaId) {
        try {
            $stmt = $this->db->getConnection()->prepare("INSERT INTO usuario_telas (usuario_id, tela_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $telaId]);
        } catch (Exception $e) {
            error_log("Erro ao associar usuário à tela: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeFromTela($userId, $telaId) {
        try {
            $stmt = $this->db->getConnection()->prepare("DELETE FROM usuario_telas WHERE usuario_id = ? AND tela_id = ?");
            return $stmt->execute([$userId, $telaId]);
            

        } catch (Exception $e) {
            error_log("Erro ao remover associação usuário-tela: " . $e->getMessage());
            return false;
        }
    }
}

?>