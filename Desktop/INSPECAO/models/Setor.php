<?php
/**
 * Modelo de Setor
 */
class Setor {
    private $db;
    
    public function __construct() {
        require_once 'config/database.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    // ... todos os seus outros métodos (obterPorId, listar, etc.) permanecem exatamente iguais ...

    public function obterPorId($id) {
        $sql = "SELECT s.*, e.nome as empresa_nome 
                FROM setores s
                LEFT JOIN empresas e ON s.empresa_id = e.id
                WHERE s.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function listar($apenasAtivos = true, $empresaId = null) {
        $sql = "SELECT s.*, e.nome as empresa_nome 
                FROM setores s
                LEFT JOIN empresas e ON s.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if ($apenasAtivos) {
            $sql .= " AND s.ativo = 1";
        }
        
        if ($empresaId !== null) {
            $sql .= " AND (s.empresa_id = ? OR s.empresa_id IS NULL)";
            $params[] = $empresaId;
        }
        
        $sql .= " ORDER BY s.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($dados) {
        $sql = "INSERT INTO setores (nome, descricao, ativo, empresa_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        $empresaId = isset($dados['empresa_id']) ? $dados['empresa_id'] : null;
        
        if ($stmt->execute([
            $dados['nome'],
            $dados['descricao'] ?? null,
            $ativo,
            $empresaId
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
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
        
        $sql = "UPDATE setores SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    public function excluir($id) {
        $sql = "SELECT COUNT(*) FROM locais WHERE setor_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() > 0) {
            return $this->atualizar($id, ['ativo' => 0]);
        }
        
        $sql = "DELETE FROM setores WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Contar inspeções por setor para o gráfico.
     */
    public function contarInspecoes($empresaId = null) {
        $sql = "SELECT s.nome, COUNT(i.id) as total
                FROM setores s
                LEFT JOIN inspecoes i ON s.id = i.setor_id";

        $params = [];
        $whereClauses = ["s.ativo = 1"];

        if ($empresaId !== null) {
            $whereClauses[] = "i.empresa_id = ?";
            $params[] = $empresaId;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // --- INÍCIO DA CORREÇÃO ---
        // A query agora agrupa apenas pelo NOME do setor, consolidando os resultados.
        $sql .= " GROUP BY s.nome
                  HAVING total > 0
                  ORDER BY total DESC";
        // --- FIM DA CORREÇÃO ---

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar se o setor possui locais vinculados
     */
    public function possuiLocaisVinculados($id) {
        $sql = "SELECT COUNT(*) FROM locais WHERE setor_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}