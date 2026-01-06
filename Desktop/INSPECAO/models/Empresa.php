<?php
/**
 * Modelo para a entidade Empresa
 */

class Empresa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter todas as empresas
     * 
     * @return array Lista de empresas
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM empresas ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter empresas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter empresa por ID
     * 
     * @param int $id ID da empresa
     * @return array|false Dados da empresa ou false se não encontrada
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM empresas WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter empresa por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar nova empresa
     * 
     * @param array $dados Dados da empresa
     * @return int|false ID da empresa criada ou false em caso de erro
     */
    public function create($dados) {
        try {
            $stmt = $this->db->prepare("INSERT INTO empresas (nome) VALUES (:nome)");
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar empresa existente
     * 
     * @param int $id ID da empresa
     * @param array $dados Dados atualizados da empresa
     * @return bool Sucesso ou falha
     */
    public function update($id, $dados) {
        try {
            $stmt = $this->db->prepare("UPDATE empresas SET nome = :nome WHERE id = :id");
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir empresa
     * 
     * @param int $id ID da empresa
     * @return bool Sucesso ou falha
     */
    public function delete($id) {
        try {
            // Verificar se há registros vinculados antes de excluir
            if ($this->possuiRegistrosVinculados($id)) {
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM empresas WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao excluir empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se o nome da empresa já existe
     * 
     * @param string $nome Nome da empresa
     * @param int|null $excluirId ID da empresa a ser excluída da verificação (para edição)
     * @return bool True se o nome já existe, false caso contrário
     */
    public function nomeExiste($nome, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM empresas WHERE nome = :nome";
            if ($excluirId !== null) {
                $sql .= " AND id != :id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            
            if ($excluirId !== null) {
                $stmt->bindParam(':id', $excluirId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar existência de nome de empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se a empresa possui registros vinculados
     * 
     * @param int $id ID da empresa
     * @return bool True se possui registros vinculados, false caso contrário
     */
    public function possuiRegistrosVinculados($id) {
        try {
            // Verificar usuários vinculados
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE empresa_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                return true;
            }
            
            // Verificar inspeções vinculadas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM inspecoes WHERE empresa_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                return true;
            }
            
            // Verificar projetos vinculados
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM projetos WHERE empresa_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao verificar registros vinculados à empresa: " . $e->getMessage());
            return true; // Por segurança, assume que há registros vinculados em caso de erro
        }
    }
    
    /**
     * Obter o próximo número de inspeção para uma empresa
     * 
     * @param int $empresaId ID da empresa
     * @return int Próximo número de inspeção
     */
    public function getProximoNumeroInspecao($empresaId) {
        try {
            $stmt = $this->db->prepare("SELECT MAX(numero_inspecao) FROM inspecoes WHERE empresa_id = :empresa_id");
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $ultimoNumero = $stmt->fetchColumn();
            
            return $ultimoNumero ? $ultimoNumero + 1 : 1;
        } catch (PDOException $e) {
            error_log("Erro ao obter próximo número de inspeção: " . $e->getMessage());
            return 1; // Em caso de erro, retorna 1 como número inicial
        }
    }
}
