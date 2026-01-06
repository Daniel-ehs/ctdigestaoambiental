<?php
/**
 * Modelo de Plano de Ação
 */
class PlanoAcao {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter plano de ação por ID
     * * @param int $id ID do plano de ação
     * @return array|false Dados do plano de ação ou false se não encontrado
     */
    public function obterPorId($id) {
        try {
            $sql = "SELECT p.*, 
                    i.apontamento, 
                    i.foto_antes AS inspecao_foto_antes, 
                    i.data_apontamento, 
                    i.status, 
                    i.empresa_id,
                    i.numero_inspecao,
                    i.semana_ano,
                    s.nome AS setor,
                    l.nome AS local,
                    u.nome AS usuario_nome,
                    e.nome AS empresa_nome
                    FROM planos_acao p
                    JOIN inspecoes i ON p.inspecao_id = i.id
                    JOIN setores s ON i.setor_id = s.id
                    JOIN locais l ON i.local_id = l.id
                    JOIN usuarios u ON p.usuario_id = u.id
                    LEFT JOIN empresas e ON i.empresa_id = e.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $plano = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plano) {
                error_log("PlanoAcao->obterPorId: Plano encontrado para id=$id: " . print_r($plano, true));
            } else {
                error_log("PlanoAcao->obterPorId: Nenhum plano encontrado para id=$id");
            }
            
            return $plano ?: false;
        } catch (PDOException $e) {
            error_log("PlanoAcao->obterPorId: Erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * **NOVO MÉTODO - CORREÇÃO DO ERRO**
     * Busca TODOS os planos de ação associados a um ID de inspeção.
     * Este é o método que a view estava tentando chamar e que causava o erro.
     * @param int $inspecaoId O ID da inspeção.
     * @return array Uma lista de planos de ação.
     */
    public function obterPorInspecaoId($inspecaoId) {
        try {
            // A consulta é a mesma, mas agora retornaremos todos os resultados
            $sql = "SELECT p.*, u.nome as usuario_nome 
                    FROM planos_acao p 
                    LEFT JOIN usuarios u ON p.usuario_id = u.id
                    WHERE p.inspecao_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inspecaoId]);
            // Usamos fetchAll() para obter todas as linhas correspondentes
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("PlanoAcao->obterPorInspecaoId: Erro: " . $e->getMessage());
            return []; // Retorna um array vazio em caso de erro, para não quebrar a view
        }
    }

    /**
     * Obter UM plano de ação por inspeção (mantido por compatibilidade, se necessário)
     * * @param int $inspecaoId ID da inspeção
     * @return array|false Dados do plano de ação ou false se não encontrado
     */
    public function obterUmPorInspecao($inspecaoId) {
        try {
            $sql = "SELECT p.*, 
                    i.apontamento, 
                    i.foto_antes AS inspecao_foto_antes, 
                    i.data_apontamento, 
                    i.status, 
                    i.empresa_id,
                    i.numero_inspecao,
                    i.semana_ano,
                    s.nome AS setor,
                    l.nome AS local,
                    u.nome AS usuario_nome,
                    e.nome AS empresa_nome
                    FROM planos_acao p
                    JOIN inspecoes i ON p.inspecao_id = i.id
                    JOIN setores s ON i.setor_id = s.id
                    JOIN locais l ON i.local_id = l.id
                    JOIN usuarios u ON p.usuario_id = u.id
                    LEFT JOIN empresas e ON i.empresa_id = e.id
                    WHERE p.inspecao_id = ? LIMIT 1"; // Adicionado LIMIT 1 para clareza
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inspecaoId]);
            $plano = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plano) {
                error_log("PlanoAcao->obterUmPorInspecao: Plano encontrado para inspecao_id=$inspecaoId");
            } else {
                error_log("PlanoAcao->obterUmPorInspecao: Nenhum plano encontrado para inspecao_id=$inspecaoId");
            }
            
            return $plano ?: false;
        } catch (PDOException $e) {
            error_log("PlanoAcao->obterUmPorInspecao: Erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Listar planos de ação com contagem total para paginação
     * * @param array $filtros Filtros a aplicar
     * @param int $pagina Número da página
     * @param int $porPagina Itens por página
     * @return array ['planos' => Lista de planos de ação, 'total' => Total de registros]
     */
    public function listar($filtros = [], $pagina = 1, $porPagina = 10) {
        $baseSql = "FROM planos_acao p
                    JOIN inspecoes i ON p.inspecao_id = i.id
                    JOIN setores s ON i.setor_id = s.id
                    JOIN locais l ON i.local_id = l.id
                    LEFT JOIN empresas e ON i.empresa_id = e.id
                    WHERE 1=1";
        
        $params = [];
        $whereClauses = "";
        
        // Aplicar filtros
        if (!empty($filtros["setor_id"])) {
            $whereClauses .= " AND i.setor_id = ?";
            $params[] = $filtros["setor_id"];
        }
        
        if (!empty($filtros["local_id"])) {
            $whereClauses .= " AND i.local_id = ?";
            $params[] = $filtros["local_id"];
        }
        
        if (!empty($filtros["empresa_id"])) {
            $whereClauses .= " AND i.empresa_id = ?";
            $params[] = $filtros["empresa_id"];
        }
        
        if (!empty($filtros["data_inicio"]) && !empty($filtros["data_fim"])) {
            $whereClauses .= " AND p.data_registro BETWEEN ? AND ?";
            $params[] = $filtros["data_inicio"] . " 00:00:00";
            $params[] = $filtros["data_fim"] . " 23:59:59";
        } elseif (!empty($filtros["data_inicio"])) {
            $whereClauses .= " AND p.data_registro >= ?";
            $params[] = $filtros["data_inicio"] . " 00:00:00";
        } elseif (!empty($filtros["data_fim"])) {
            $whereClauses .= " AND p.data_registro <= ?";
            $params[] = $filtros["data_fim"] . " 23:59:59";
        }
        
        // Filtro de acesso por empresa do usuário
        if (!empty($filtros['usuario_empresa_id']) && $filtros['usuario_nivel'] !== 'admin') {
            $whereClauses .= " AND i.empresa_id = ?";
            $params[] = $filtros['usuario_empresa_id'];
        }

        // SQL para contagem total
        $sqlTotal = "SELECT COUNT(p.id) " . $baseSql . $whereClauses;
        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $totalRegistros = $stmtTotal->fetchColumn();

        // SQL para buscar os dados paginados
        $sql = "SELECT p.*, 
                i.apontamento, 
                i.data_apontamento, 
                i.status, 
                i.empresa_id,
                i.numero_inspecao,
                i.semana_ano,
                s.nome AS setor,
                l.nome AS local,
                e.nome AS empresa_nome
                " . $baseSql . $whereClauses;
        
        // Ordenação
        $sql .= " ORDER BY p.data_registro DESC";
        
        // Paginação
        $offset = ($pagina - 1) * $porPagina;
        $sql .= " LIMIT ? OFFSET ?";
        $paramsPaginacao = array_merge($params, [$porPagina, $offset]);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($paramsPaginacao);
        $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'planos' => $planos,
            'total' => $totalRegistros
        ];
    }
    
    /**
     * Criar novo plano de ação
     * * @param array $dados Dados do plano de ação
     * @return int|false ID do plano de ação criado ou false em caso de erro
     */
    public function criar($dados) {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO planos_acao (inspecao_id, foto_depois, descricao_acao, usuario_id) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                $dados["inspecao_id"],
                $dados["foto_depois"] ?? null,
                $dados["descricao_acao"],
                $dados["usuario_id"]
            ]);
            
            $planoId = $this->db->lastInsertId();
            
            $sqlInspecao = "UPDATE inspecoes SET 
                            data_conclusao = CURRENT_DATE(), 
                            status = 'Concluído' 
                            WHERE id = ?";
            $stmtInspecao = $this->db->prepare($sqlInspecao);
            $stmtInspecao->execute([$dados["inspecao_id"]]);
            
            $this->db->commit();
            
            return $planoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("PlanoAcao->criar: Erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar plano de ação
     * * @param int $id ID do plano de ação
     * @param array $dados Dados do plano de ação
     * @return bool
     */
    public function atualizar($id, $dados) {
        $campos = [];
        $valores = [];
        
        if (isset($dados["foto_depois"])) {
            $campos[] = "foto_depois = ?";
            $valores[] = $dados["foto_depois"];
        }
        
        if (isset($dados["descricao_acao"])) {
            $campos[] = "descricao_acao = ?";
            $valores[] = $dados["descricao_acao"];
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $valores[] = $id;
        
        $sql = "UPDATE planos_acao SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Excluir plano de ação
     * * @param int $id ID do plano de ação
     * @return bool
     */
    public function excluir($id) {
        $sql = "SELECT inspecao_id FROM planos_acao WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $inspecaoId = $stmt->fetchColumn();
        
        if (!$inspecaoId) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            $sqlPlano = "DELETE FROM planos_acao WHERE id = ?";
            $stmtPlano = $this->db->prepare($sqlPlano);
            $stmtPlano->execute([$id]);
            
            $sqlInspecao = "UPDATE inspecoes SET 
                            data_conclusao = NULL, 
                            status = CASE 
                                WHEN prazo < CURRENT_DATE() THEN 'Prazo Vencido' 
                                ELSE 'Em Aberto' 
                            END 
                            WHERE id = ?";
            $stmtInspecao = $this->db->prepare($sqlInspecao);
            $stmtInspecao->execute([$inspecaoId]);
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("PlanoAcao->excluir: Erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se inspeção já possui plano de ação
     * * @param int $inspecaoId ID da inspeção
     * @return bool
     */
    public function existePlanoParaInspecao($inspecaoId) {
        $sql = "SELECT COUNT(*) FROM planos_acao WHERE inspecao_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inspecaoId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Obter todas as empresas que possuem planos de ação
     * * @return array Lista de empresas
     */
    public function obterEmpresasComPlanos() {
        $sql = "SELECT DISTINCT e.id, e.nome
                FROM planos_acao p
                JOIN inspecoes i ON p.inspecao_id = i.id
                JOIN empresas e ON i.empresa_id = e.id
                ORDER BY e.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
// ADICIONE ESTA NOVA FUNÇÃO AO SEU MODEL `PlanoAcao.php`
/**
 * Obter detalhes completos de um plano de ação, incluindo dados da inspeção associada.
 * @param int $id ID do plano de ação
 * @return array|false Dados completos ou false se não encontrado
 */
public function obterCompletoPorId($id) {
    $sql = "SELECT 
                pa.*, 
                pa.id as plano_id,
                pa.data_registro as data_acao_criacao, /* Renomeado para clareza */
                i.id as inspecao_id,
                i.numero_inspecao,
                i.data_apontamento as inspecao_data,
                i.apontamento as inspecao_apontamento,
                i.foto_antes as inspecao_foto_antes,
                i.empresa_id,
                s.nome as setor_nome,
                l.nome as local_nome,
                u.nome as usuario_nome
            FROM planos_acao pa
            JOIN inspecoes i ON pa.inspecao_id = i.id
            LEFT JOIN setores s ON i.setor_id = s.id
            LEFT JOIN locais l ON i.local_id = l.id
            LEFT JOIN usuarios u ON pa.usuario_id = u.id
            WHERE pa.id = ?";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>
