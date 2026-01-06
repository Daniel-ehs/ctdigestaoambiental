<?php
/**
 * Modelo de Tipo de Apontamento
 */
class TipoApontamento {
    private $db;
    
    public function __construct() {
        require_once 'config/database.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter tipo de apontamento por ID
     */
    public function obterPorId($id) {
        $sql = "SELECT t.*, e.nome as empresa_nome
                FROM tipos_apontamento t
                LEFT JOIN empresas e ON t.empresa_id = e.id
                WHERE t.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar todos os tipos de apontamento
     */
    public function listar($apenasAtivos = true, $empresaId = null) {
        $sql = "SELECT t.*, e.nome as empresa_nome
                FROM tipos_apontamento t
                LEFT JOIN empresas e ON t.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if ($apenasAtivos) {
            $sql .= " AND t.ativo = 1";
        }
        
        if ($empresaId !== null) {
            $sql .= " AND (t.empresa_id = ? OR t.empresa_id IS NULL)";
            $params[] = $empresaId;
        }
        
        $sql .= " ORDER BY t.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo tipo de apontamento
     */
    public function criar($dados) {
        $sql = "INSERT INTO tipos_apontamento (nome, descricao, cor, ativo, empresa_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        $cor = isset($dados['cor']) ? $dados['cor'] : '#28a745';
        $empresaId = isset($dados['empresa_id']) ? $dados['empresa_id'] : null;
        
        if ($stmt->execute([
            $dados['nome'],
            $dados['descricao'] ?? null,
            $cor,
            $ativo,
            $empresaId
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualizar tipo de apontamento
     */
    public function atualizar($id, $dados) {
        $campos = [];
        $valores = [];
        
        if (isset($dados['nome'])) {
            $campos[] = "nome = ?";
            $valores[] = $dados['nome'];
        }
        if (isset($dados['descricao'])) {
            $campos[] = "descricao = ?";
            $valores[] = $dados['descricao'];
        }
        if (isset($dados['cor'])) {
            $campos[] = "cor = ?";
            $valores[] = $dados['cor'];
        }
        if (isset($dados['ativo'])) {
            $campos[] = "ativo = ?";
            $valores[] = $dados['ativo'];
        }
        if (isset($dados['empresa_id'])) {
            $campos[] = "empresa_id = ?";
            $valores[] = $dados['empresa_id'];
        }
        if (empty($campos)) {
            return false;
        }
        
        $valores[] = $id;
        
        $sql = "UPDATE tipos_apontamento SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Excluir tipo de apontamento
     */
    public function excluir($id) {
        $sql = "SELECT COUNT(*) FROM inspecoes WHERE tipo_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() > 0) {
            return $this->atualizar($id, ['ativo' => 0]);
        }
        
        $sql = "DELETE FROM tipos_apontamento WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Contar inspeções por tipo de apontamento para o gráfico.
     */
    public function contarInspecoes($empresaId = null) {
        // CORREÇÃO: Alterado alias 'count' para 'total' e ajustada a query.
        $sql = "SELECT t.nome, t.cor, COUNT(i.id) as total
                FROM tipos_apontamento t
                LEFT JOIN inspecoes i ON t.id = i.tipo_id";
        
        $params = [];
        $whereClauses = ["t.ativo = 1"];

        if ($empresaId !== null) {
            $whereClauses[] = "i.empresa_id = ?";
            $params[] = $empresaId;
        }
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $sql .= " GROUP BY t.id, t.nome, t.cor
                  HAVING total > 0
                  ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar se o tipo de apontamento possui inspeções vinculadas
     */
    public function possuiInspecoesVinculadas($id) {
        $sql = "SELECT COUNT(*) FROM inspecoes WHERE tipo_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Busca os IDs de tipos de apontamento com base em uma lista de nomes.
     * @param array $nomes Lista de nomes dos tipos
     * @return array
     */
    public function getIdsPorNomes(array $nomes) {
        if (empty($nomes)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($nomes), '?'));
        $sql = "SELECT id FROM tipos_apontamento WHERE nome IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($nomes);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}