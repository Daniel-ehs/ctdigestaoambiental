<?php
/**
 * Modelo de Projeto
 */
class Projeto {
    private $db;
    
    public function __construct($db) {
        // CORRIGIDO: Agora ele usa a conexão vinda do Controller, como deveria.
        $this->db = $db;
    }
    
    /**
     * Obter projeto por ID
     * * @param int $id ID do projeto
     * @return array|false Dados do projeto ou false se não encontrado
     */
    public function obterPorId($id) {
        $sql = "SELECT p.*, u.nome as usuario_nome, e.nome as empresa_nome
                FROM projetos p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN empresas e ON p.empresa_id = e.id
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Listar projetos com filtros
     * * @param array $filtros Filtros a aplicar
     * @param int $pagina Número da página
     * @param int $porPagina Itens por página
     * @return array Lista de projetos
     */
    public function listar($filtros = [], $pagina = 1, $porPagina = 10) {
        $sql = "SELECT p.*, u.nome as usuario_nome, e.nome as empresa_nome
                FROM projetos p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN empresas e ON p.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros["status"])) {
            $sql .= " AND p.status = ?";
            $params[] = $filtros["status"];
        }
        
        if (!empty($filtros["empresa_id"])) {
            $sql .= " AND p.empresa_id = ?";
            $params[] = $filtros["empresa_id"];
        }
        
        if (!empty($filtros["prazo_inicio"]) && !empty($filtros["prazo_fim"])) {
            $sql .= " AND p.prazo BETWEEN ? AND ?";
            $params[] = $filtros["prazo_inicio"];
            $params[] = $filtros["prazo_fim"];
        } elseif (!empty($filtros["prazo_inicio"])) {
            $sql .= " AND p.prazo >= ?";
            $params[] = $filtros["prazo_inicio"];
        } elseif (!empty($filtros["prazo_fim"])) {
            $sql .= " AND p.prazo <= ?";
            $params[] = $filtros["prazo_fim"];
        }
        
        if (!empty($filtros["usuario_empresa_id"]) && $filtros["usuario_nivel"] !== "admin") {
            $sql .= " AND p.empresa_id = ?";
            $params[] = $filtros["usuario_empresa_id"];
        }
        
        $sql .= " ORDER BY p.status, p.prazo";
        
        $offset = ($pagina - 1) * $porPagina;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $porPagina;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Contar total de projetos com filtros
     */
    public function contar($filtros = []) {
        $sql = "SELECT COUNT(*) FROM projetos p 
                LEFT JOIN empresas e ON p.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros["status"])) {
            $sql .= " AND p.status = ?";
            $params[] = $filtros["status"];
        }
        if (!empty($filtros["empresa_id"])) {
            $sql .= " AND p.empresa_id = ?";
            $params[] = $filtros["empresa_id"];
        }
        if (!empty($filtros["prazo_inicio"]) && !empty($filtros["prazo_fim"])) {
            $sql .= " AND p.prazo BETWEEN ? AND ?";
            $params[] = $filtros["prazo_inicio"];
            $params[] = $filtros["prazo_fim"];
        }
        
        if (!empty($filtros["usuario_empresa_id"]) && $filtros["usuario_nivel"] !== "admin") {
            $sql .= " AND p.empresa_id = ?";
            $params[] = $filtros["usuario_empresa_id"];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Criar novo projeto
     */
    public function criar($dados) {
        $sql = "INSERT INTO projetos (
                    fonte, descricao, prazo, status, observacao, usuario_id, empresa_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $status = isset($dados["status"]) ? $dados["status"] : "Em Andamento";
        
        if ($stmt->execute([
            $dados["fonte"] ?? null,
            $dados["descricao"],
            $dados["prazo"] ?? null,
            $status,
            $dados["observacao"] ?? null,
            $dados["usuario_id"],
            $dados["empresa_id"] ?? null
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualizar projeto
     */
    public function atualizar($id, $dados) {
        $campos = [];
        $valores = [];
        
        if (isset($dados["fonte"])) { $campos[] = "fonte = ?"; $valores[] = $dados["fonte"]; }
        if (isset($dados["descricao"])) { $campos[] = "descricao = ?"; $valores[] = $dados["descricao"]; }
        if (isset($dados["prazo"])) { $campos[] = "prazo = ?"; $valores[] = $dados["prazo"]; }
        if (isset($dados["data_conclusao"])) { $campos[] = "data_conclusao = ?"; $valores[] = $dados["data_conclusao"]; }
        if (isset($dados["status"])) { $campos[] = "status = ?"; $valores[] = $dados["status"]; }
        if (isset($dados["observacao"])) { $campos[] = "observacao = ?"; $valores[] = $dados["observacao"]; }
        if (isset($dados["empresa_id"])) { $campos[] = "empresa_id = ?"; $valores[] = $dados["empresa_id"]; }
        
        if (empty($campos)) return false;
        
        $valores[] = $id;
        
        $sql = "UPDATE projetos SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Excluir projeto
     */
    public function excluir($id) {
        $sql = "DELETE FROM projetos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Concluir projeto
     */
    public function concluir($id, $dataConclusao = null) {
        if ($dataConclusao === null) {
            $dataConclusao = date("Y-m-d");
        }
        
        return $this->atualizar($id, [
            "data_conclusao" => $dataConclusao,
            "status" => "Concluído"
        ]);
    }
    
    /**
     * Cancelar projeto
     */
    public function cancelar($id) {
        return $this->atualizar($id, [
            "status" => "Cancelado"
        ]);
    }
    
    /**
     * Obter estatísticas de projetos
     */
    public function obterEstatisticas($empresaId = null) {
        $stats = [];
        $params = [];
        $whereClause = "";
        
        if ($empresaId) {
            $whereClause = " WHERE empresa_id = ?";
            $params[] = $empresaId;
        }
        
        $sql = "SELECT COUNT(*) FROM projetos" . $whereClause;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats["total"] = $stmt->fetchColumn();
        
        $statusList = ["Em Andamento", "Concluído", "Cancelado"];
        foreach ($statusList as $status) {
            $sqlStatus = "SELECT COUNT(*) FROM projetos WHERE status = ?" . ($empresaId ? " AND empresa_id = ?" : "");
            $paramsStatus = [$status];
            if ($empresaId) $paramsStatus[] = $empresaId;
            $stmt = $this->db->prepare($sqlStatus);
            $stmt->execute($paramsStatus);
            $stats[strtolower(str_replace(' ', '_', $status))] = $stmt->fetchColumn();
        }
        
        return $stats;
    }
    
    /**
     * Obter todas as empresas que possuem projetos
     */
    public function obterEmpresasComProjetos() {
        $sql = "SELECT DISTINCT e.id, e.nome
                FROM projetos p
                JOIN empresas e ON p.empresa_id = e.id
                ORDER BY e.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // --- NOVA FUNÇÃO ADICIONADA ---
    /**
     * Conta os projetos em andamento para o painel.
     * @param int $ano
     * @param int $mes
     * @param int|null $empresaId
     * @return int
     */
    public function contarProjetosEmAndamento($ano, $mes, $empresaId = null) {
        // Cria a data do último dia do mês/ano selecionado
        $dataLimite = date('Y-m-t', strtotime("$ano-$mes-01"));

        $sql = "SELECT COUNT(id) FROM projetos 
                WHERE status = 'Em Andamento' 
                AND data_registro <= ?";
        
        $params = [$dataLimite];

        if ($empresaId !== null) {
            $sql .= " AND empresa_id = ?";
            $params[] = $empresaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}