<?php
/**
 * Modelo de Usuário
 */

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Autenticar usuário
     * 
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @return array|false Dados do usuário ou false se autenticação falhar
     */
    public function autenticar($email, $senha) {
        $sql = "SELECT u.*, e.nome as empresa_nome FROM usuarios u 
                LEFT JOIN empresas e ON u.empresa_id = e.id 
                WHERE u.email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Verificar senha usando password_verify
            if (password_verify($senha, $usuario['senha'])) {
                // Atualizar último acesso
                $this->atualizarUltimoAcesso($usuario['id']);
                
                // Remover senha dos dados retornados
                unset($usuario['senha']);
                return $usuario;
            }
        }
        
        return false;
    }
    
    /**
     * Atualizar último acesso do usuário
     * 
     * @param int $id ID do usuário
     * @return bool
     */
    private function atualizarUltimoAcesso($id) {
        $sql = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Obter usuário por ID
     * 
     * @param int $id ID do usuário
     * @return array|false Dados do usuário ou false se não encontrado
     */
    public function obterPorId($id) {
        $sql = "SELECT u.id, u.nome, u.email, u.nivel_acesso, u.data_criacao, u.ultimo_acesso, u.empresa_id, e.nome as empresa_nome 
                FROM usuarios u
                LEFT JOIN empresas e ON u.empresa_id = e.id
                WHERE u.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Listar todos os usuários
     * 
     * @return array Lista de usuários
     */
    public function listar() {
        $sql = "SELECT u.id, u.nome, u.email, u.nivel_acesso, u.data_criacao, u.ultimo_acesso, u.empresa_id, e.nome as empresa_nome 
                FROM usuarios u
                LEFT JOIN empresas e ON u.empresa_id = e.id
                ORDER BY u.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Criar novo usuário
     * 
     * @param array $dados Dados do usuário
     * @return int|false ID do usuário criado ou false em caso de erro
     */
    public function criar($dados) {
        // Verificar se email já existe
        if ($this->emailExiste($dados['email'])) {
            return false;
        }
        
        // Hash da senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, empresa_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['senha'],
            $dados['nivel_acesso'],
            $dados['empresa_id'] ?? null
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualizar usuário
     * 
     * @param int $id ID do usuário
     * @param array $dados Dados do usuário
     * @return bool
     */
    public function atualizar($id, $dados) {
        // Verificar se email já existe (exceto para o próprio usuário)
        if (isset($dados['email']) && $this->emailExiste($dados['email'], $id)) {
            return false;
        }
        
        // Preparar campos e valores para atualização
        $campos = [];
        $valores = [];
        
        if (isset($dados['nome'])) {
            $campos[] = "nome = ?";
            $valores[] = $dados['nome'];
        }
        
        if (isset($dados['email'])) {
            $campos[] = "email = ?";
            $valores[] = $dados['email'];
        }
        
        if (isset($dados['senha']) && !empty($dados['senha'])) {
            $campos[] = "senha = ?";
            $valores[] = password_hash($dados['senha'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        }
        
        if (isset($dados['nivel_acesso'])) {
            $campos[] = "nivel_acesso = ?";
            $valores[] = $dados['nivel_acesso'];
        }
        
        if (array_key_exists('empresa_id', $dados)) {
            $campos[] = "empresa_id = ?";
            $valores[] = $dados['empresa_id'];
        }
        
        if (empty($campos)) {
            return false;
        }
        
        // Adicionar ID ao final dos valores
        $valores[] = $id;
        
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Excluir usuário
     * 
     * @param int $id ID do usuário
     * @return bool
     */
    public function excluir($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Verificar se email já existe
     * 
     * @param string $email Email a verificar
     * @param int|null $excluirId ID a excluir da verificação (para atualizações)
     * @return bool
     */
    private function emailExiste($email, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $params = [$email];
        
        if ($excluirId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Verificar se o usuário possui inspeções vinculadas
     * 
     * @param int $id ID do usuário
     * @return bool
     */
    public function possuiInspecoesVinculadas($id) {
        $sql = "SELECT COUNT(*) FROM inspecoes WHERE usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetchColumn() > 0;
    }
}
