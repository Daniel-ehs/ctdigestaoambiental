<?php
/**
 * Modelo de Local
 */

class Local {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter local por ID
     * 
     * @param int $id ID do local
     * @return array|false Dados do local ou false se não encontrado
     */
    public function obterPorId($id) {
        $sql = "SELECT l.*, s.nome as nome_setor, e.nome as empresa_nome
                FROM locais l
                JOIN setores s ON l.setor_id = s.id
                LEFT JOIN empresas e ON l.empresa_id = e.id
                WHERE l.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Listar todos os locais
     * 
     * @param bool $apenasAtivos Filtrar apenas locais ativos
     * @param int|null $setorId Filtrar por setor específico
     * @param int|null $empresaId Filtrar por empresa específica
     * @return array Lista de locais
     */
    public function listar($apenasAtivos = true, $setorId = null, $empresaId = null) {
        $sql = "SELECT l.*, s.nome as nome_setor, e.nome as empresa_nome
                FROM locais l
                JOIN setores s ON l.setor_id = s.id
                LEFT JOIN empresas e ON l.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if ($apenasAtivos) {
            $sql .= " AND l.ativo = 1";
        }
        
        if ($setorId !== null) {
            $sql .= " AND l.setor_id = ?";
            $params[] = $setorId;
        }
        
        if ($empresaId !== null) {
            $sql .= " AND (l.empresa_id = ? OR l.empresa_id IS NULL)";
            $params[] = $empresaId;
        }
        
        $sql .= " ORDER BY s.nome, l.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Criar novo local
     * 
     * @param array $dados Dados do local
     * @return int|false ID do local criado ou false em caso de erro
     */
    public function criar($dados) {
        $sql = "INSERT INTO locais (nome, setor_id, descricao, ativo, empresa_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        $empresaId = isset($dados['empresa_id']) ? $dados['empresa_id'] : null;
        
        if ($stmt->execute([
            $dados['nome'],
            $dados['setor_id'],
            $dados['descricao'] ?? null,
            $ativo,
            $empresaId
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualizar local
     * 
     * @param int $id ID do local
     * @param array $dados Dados do local
     * @return bool
     */
    public function atualizar($id, $dados) {
        $campos = [];
        $valores = [];
        
        if (isset($dados['nome'])) {
            $campos[] = "nome = ?";
            $valores[] = $dados['nome'];
        }
        
        if (isset($dados['setor_id'])) {
            $campos[] = "setor_id = ?";
            $valores[] = $dados['setor_id'];
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
        
        $sql = "UPDATE locais SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Excluir local
     * 
     * @param int $id ID do local
     * @return bool
     */
    public function excluir($id) {
        // Verificar se existem inspeções associadas
        $sql = "SELECT COUNT(*) FROM inspecoes WHERE local_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() > 0) {
            // Se existirem inspeções, apenas desativar o local
            return $this->atualizar($id, ['ativo' => 0]);
        }
        
        // Se não existirem inspeções, excluir o local
        $sql = "DELETE FROM locais WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Listar locais por setor
     * 
     * @param int $setorId ID do setor
     * @param bool $apenasAtivos Filtrar apenas locais ativos
     * @param int|null $empresaId Filtrar por empresa específica
     * @return array Lista de locais do setor
     */
    public function listarPorSetor($setorId, $apenasAtivos = true, $empresaId = null) {
        $sql = "SELECT l.*, e.nome as empresa_nome
                FROM locais l
                LEFT JOIN empresas e ON l.empresa_id = e.id
                WHERE l.setor_id = ?";
        
        $params = [$setorId];
        
        if ($apenasAtivos) {
            $sql .= " AND l.ativo = 1";
        }
        
        if ($empresaId !== null) {
            $sql .= " AND (l.empresa_id = ? OR l.empresa_id IS NULL)";
            $params[] = $empresaId;
        }
        
        $sql .= " ORDER BY l.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Contar inspeções por local
     * 
     * @param int|null $setorId Filtrar por setor específico
     * @param int|null $empresaId Filtrar por empresa específica
     * @return array Contagem de inspeções por local
     */
    public function contarInspecoes($setorId = null, $empresaId = null) {
        $sql = "SELECT l.id, l.nome, s.nome as setor, e.nome as empresa_nome, COUNT(i.id) as total 
                FROM locais l
                JOIN setores s ON l.setor_id = s.id
                LEFT JOIN empresas e ON l.empresa_id = e.id
                LEFT JOIN inspecoes i ON l.id = i.local_id
                WHERE l.ativo = 1";
        
        $params = [];
        
        if ($setorId !== null) {
            $sql .= " AND l.setor_id = ?";
            $params[] = $setorId;
        }
        
        if ($empresaId !== null) {
            $sql .= " AND (l.empresa_id = ? OR l.empresa_id IS NULL)";
            $params[] = $empresaId;
        }
        
        $sql .= " GROUP BY l.id, l.nome, s.nome, e.nome
                  ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar se o local possui inspeções vinculadas
     * 
     * @param int $id ID do local
     * @return bool True se possui inspeções vinculadas, false caso contrário
     */
    public function possuiInspecoesVinculadas($id) {
        $sql = "SELECT COUNT(*) FROM inspecoes WHERE local_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}
