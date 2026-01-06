<?php
/**
 * Modelo de Inspeção
 */

class Inspecao
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obter inspeção por ID
     * @param int $id ID da inspeção
     * @return array|false Dados da inspeção ou false se não encontrada
     */
    public function obterPorId($id)
    {
        $sql = "SELECT i.*, 
                s.nome as setor_nome, 
                l.nome as local_nome, 
                t.nome as tipo_nome,
                t.cor as tipo_cor,
                u.nome as usuario_nome,
                e.nome as empresa_nome
                FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                JOIN tipos_apontamento t ON i.tipo_id = t.id
                JOIN usuarios u ON i.usuario_id = u.id
                LEFT JOIN empresas e ON i.empresa_id = e.id
                WHERE i.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Listar inspeções com filtros
     * @param array $filtros Filtros a aplicar
     * @param int $pagina Número da página
     * @param int $porPagina Itens por página
     * @return array Lista de inspeções
     */
    public function listar($filtros = [], $pagina = 1, $porPagina = 10)
    {
        $sql = "SELECT i.*, 
                s.nome as setor_nome, 
                l.nome as local_nome, 
                t.nome as tipo_nome,
                t.cor as tipo_cor,
                u.nome as usuario_nome,
                e.nome as empresa_nome
                FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                JOIN tipos_apontamento t ON i.tipo_id = t.id
                JOIN usuarios u ON i.usuario_id = u.id
                LEFT JOIN empresas e ON i.empresa_id = e.id
                WHERE 1=1";

        $params = [];

        // Aplicar filtros
        if (!empty($filtros['status'])) {
            if (is_array($filtros['status'])) {
                $placeholders = implode(',', array_fill(0, count($filtros['status']), '?'));
                $sql .= " AND i.status IN ({$placeholders})";
                $params = array_merge($params, $filtros['status']);
            } else {
                $sql .= " AND i.status = ?";
                $params[] = $filtros['status'];
            }
        }

        if (!empty($filtros['local_id'])) {
            $sql .= " AND i.local_id = ?";
            $params[] = $filtros['local_id'];
        }

        if (!empty($filtros['tipo_id'])) {
            $sql .= " AND i.tipo_id = ?";
            $params[] = $filtros['tipo_id'];
        }

        // --- CORREÇÃO: Bloco 'status' duplicado foi removido daqui ---

        // CORREÇÃO APLICADA AQUI
        if (!empty($filtros['ano']) && !empty($filtros['semana'])) {
            $sql .= " AND YEARWEEK(i.data_apontamento, 1) = ?";
            // Formata o ano e semana para o formato YYYYWW (ex: 202501)
            $params[] = $filtros['ano'] . str_pad($filtros['semana'], 2, '0', STR_PAD_LEFT);
        }

        if (!empty($filtros['empresa_id'])) {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $filtros['empresa_id'];
        }

        if (!empty($filtros['setor_nome'])) {
            $sql .= " AND s.nome = ?";
            $params[] = $filtros['setor_nome'];
        }

        if (!empty($filtros['tipo_nome'])) {
            $sql .= " AND t.nome = ?";
            $params[] = $filtros['tipo_nome'];
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND i.data_apontamento BETWEEN ? AND ?";
            $params[] = $filtros['data_inicio'];
            $params[] = $filtros['data_fim'];
        } elseif (!empty($filtros['data_inicio'])) {
            $sql .= " AND i.data_apontamento >= ?";
            $params[] = $filtros['data_inicio'];
        } elseif (!empty($filtros['data_fim'])) {
            $sql .= " AND i.data_apontamento <= ?";
            $params[] = $filtros['data_fim'];
        }

        // --- ADICIONADO: Lógica de busca geral do Código 2 ---
        if (!empty($filtros["termo_busca"])) {
            $termo = '%' . $filtros["termo_busca"] . '%';
            $sql .= " AND (i.apontamento LIKE ? OR s.nome LIKE ? OR l.nome LIKE ? OR i.data_apontamento LIKE ?)";
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
        }
        // --- FIM DA ADIÇÃO ---

        if (!empty($filtros['usuario_empresa_id']) && $filtros['usuario_nivel'] !== 'admin') {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $filtros['usuario_empresa_id'];
        }

        $sql .= " ORDER BY i.id DESC";

        if (!isset($filtros['relatorio_completo'])) {
            $offset = ($pagina - 1) * $porPagina;
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int) $porPagina;
            $params[] = (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Contar total de inspeções com filtros
     * @param array $filtros Filtros a aplicar
     * @return int Total de inspeções
     */
    public function contar($filtros = [])
    {
        $sql = "SELECT COUNT(*) FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                JOIN tipos_apontamento t ON i.tipo_id = t.id
                LEFT JOIN empresas e ON i.empresa_id = e.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['setor_id'])) {
            $sql .= " AND i.setor_id = ?";
            $params[] = $filtros['setor_id'];
        }

        if (!empty($filtros['local_id'])) {
            $sql .= " AND i.local_id = ?";
            $params[] = $filtros['local_id'];
        }

        if (!empty($filtros['tipo_id'])) {
            $sql .= " AND i.tipo_id = ?";
            $params[] = $filtros['tipo_id'];
        }

        if (!empty($filtros['status'])) {
            if (is_array($filtros['status'])) {
                $placeholders = implode(',', array_fill(0, count($filtros['status']), '?'));
                $sql .= " AND i.status IN ({$placeholders})";
                $params = array_merge($params, $filtros['status']);
            } else {
                $sql .= " AND i.status = ?";
                $params[] = $filtros['status'];
            }
        }

        // CORREÇÃO APLICADA AQUI TAMBÉM
        if (!empty($filtros['ano']) && !empty($filtros['semana'])) {
            $sql .= " AND YEARWEEK(i.data_apontamento, 1) = ?";
            // Formata o ano e semana para o formato YYYYWW (ex: 202501)
            $params[] = $filtros['ano'] . str_pad($filtros['semana'], 2, '0', STR_PAD_LEFT);
        }

        if (!empty($filtros['empresa_id'])) {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $filtros['empresa_id'];
        }

        if (!empty($filtros['setor_nome'])) {
            $sql .= " AND s.nome = ?";
            $params[] = $filtros['setor_nome'];
        }

        if (!empty($filtros['tipo_nome'])) {
            $sql .= " AND t.nome = ?";
            $params[] = $filtros['tipo_nome'];
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND i.data_apontamento BETWEEN ? AND ?";
            $params[] = $filtros['data_inicio'];
            $params[] = $filtros['data_fim'];
        } elseif (!empty($filtros['data_inicio'])) {
            $sql .= " AND i.data_apontamento >= ?";
            $params[] = $filtros['data_inicio'];
        } elseif (!empty($filtros['data_fim'])) {
            $sql .= " AND i.data_apontamento <= ?";
            $params[] = $filtros['data_fim'];
        }

        // --- ADICIONADO: Lógica de busca geral do Código 2 ---
        if (!empty($filtros["termo_busca"])) {
            $termo = '%' . $filtros["termo_busca"] . '%';
            $sql .= " AND (i.apontamento LIKE ? OR s.nome LIKE ? OR l.nome LIKE ? OR i.data_apontamento LIKE ?)";
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
        }
        // --- FIM DA ADIÇÃO ---

        if (!empty($filtros['usuario_empresa_id']) && $filtros['usuario_nivel'] !== 'admin') {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $filtros['usuario_empresa_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Criar nova inspeção
     * @param array $dados Dados da inspeção
     * @return int|false ID da inspeção criada ou false em caso de erro
     */
    public function criar($dados)
    {
        $empresaId = $dados['empresa_id'] ?? null;
        $numeroInspecao = 1;

        if ($empresaId) {
            require_once BASE_PATH . '/models/Empresa.php';
            $empresaModel = new Empresa();
            $numeroInspecao = $empresaModel->getProximoNumeroInspecao($empresaId);
        }

        $sql = "INSERT INTO inspecoes (
                    data_apontamento, semana_ano, setor_id, local_id, tipo_id, 
                    apontamento, risco_consequencia, foto_antes, resolucao_proposta, 
                    responsavel, prazo, usuario_id, empresa_id, numero_inspecao
                ) VALUES (?, WEEK(?, 1), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (
            $stmt->execute([
                $dados['data_apontamento'],
                $dados['data_apontamento'],
                $dados['setor_id'],
                $dados['local_id'],
                $dados['tipo_id'],
                $dados['apontamento'],
                $dados['risco_consequencia'] ?? null,
                $dados['foto_antes'] ?? null,
                $dados['resolucao_proposta'] ?? null,
                $dados['responsavel'] ?? null,
                $dados['prazo'] ?? null,
                $dados['usuario_id'],
                $empresaId,
                $numeroInspecao
            ])
        ) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualizar inspeção
     * @param int $id ID da inspeção
     * @param array $dados Dados da inspeção
     * @return bool
     */
    public function atualizar($id, $dados)
    {
        $campos = [];
        $valores = [];

        if (isset($dados['data_apontamento'])) {
            $campos[] = "data_apontamento = ?";
            $valores[] = $dados['data_apontamento'];
            $campos[] = "semana_ano = WEEK(?, 1)"; // Lógica do Código 1 mantida
            $valores[] = $dados['data_apontamento'];
        }

        if (isset($dados['setor_id'])) {
            $campos[] = "setor_id = ?";
            $valores[] = $dados['setor_id'];
        }

        if (isset($dados['local_id'])) {
            $campos[] = "local_id = ?";
            $valores[] = $dados['local_id'];
        }

        if (isset($dados['tipo_id'])) {
            $campos[] = "tipo_id = ?";
            $valores[] = $dados['tipo_id'];
        }

        if (isset($dados['apontamento'])) {
            $campos[] = "apontamento = ?";
            $valores[] = $dados['apontamento'];
        }

        if (isset($dados['risco_consequencia'])) {
            $campos[] = "risco_consequencia = ?";
            $valores[] = $dados['risco_consequencia'];
        }

        if (isset($dados['foto_antes'])) {
            $campos[] = "foto_antes = ?";
            $valores[] = $dados['foto_antes'];
        }

        if (isset($dados['resolucao_proposta'])) {
            $campos[] = "resolucao_proposta = ?";
            $valores[] = $dados['resolucao_proposta'];
        }

        if (isset($dados['responsavel'])) {
            $campos[] = "responsavel = ?";
            $valores[] = $dados['responsavel'];
        }

        if (isset($dados['prazo'])) {
            $campos[] = "prazo = ?";
            $valores[] = $dados['prazo'];
        }

        if (isset($dados['data_conclusao'])) {
            $campos[] = "data_conclusao = ?";
            $valores[] = $dados['data_conclusao'];
        }

        if (isset($dados['observacao'])) { // Lógica do Código 1 mantida
            $campos[] = "observacao = ?";
            $valores[] = $dados['observacao'];
        }

        if (isset($dados['empresa_id'])) {
            $campos[] = "empresa_id = ?";
            $valores[] = $dados['empresa_id'];
        }

        if (isset($dados['status'])) {
            $campos[] = "status = ?";
            $valores[] = $dados['status'];
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = $id;

        $sql = "UPDATE inspecoes SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($valores);
    }

    /**
     * Excluir inspeção
     * @param int $id ID da inspeção
     * @return bool
     */
    public function excluir($id)
    {
        // Lógica de segurança do Código 1 MANTIDA
        $sql = "SELECT COUNT(*) FROM planos_acao WHERE inspecao_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $sql = "DELETE FROM inspecoes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Concluir inspeção
     * @param int $id ID da inspeção
     * @param string $dataConclusao Data de conclusão
     * @return bool
     */
    public function concluir($id, $dataConclusao = null)
    {
        if ($dataConclusao === null) {
            $dataConclusao = date('Y-m-d');
        }

        return $this->atualizar($id, [
            'data_conclusao' => $dataConclusao,
            'status' => 'Concluído'
        ]);
    }

    /**
     * Obter estatísticas para dashboard
     * @param int|null $empresaId ID da empresa para filtrar estatísticas
     * @return array Estatísticas
     */
    public function obterEstatisticas($empresaId = null)
    {
        $stats = [];
        $params = [];
        $whereClause = "";

        if ($empresaId) {
            $whereClause = " WHERE empresa_id = ?";
            $params[] = $empresaId;
        }

        $sql = "SELECT COUNT(*) FROM inspecoes" . $whereClause;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['total'] = $stmt->fetchColumn();

        $sql = "SELECT COUNT(*) FROM inspecoes WHERE status = 'Em Aberto'";
        if ($empresaId) {
            $sql .= " AND empresa_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $empresaId ? $stmt->execute([$empresaId]) : $stmt->execute();
        $stats['em_aberto'] = $stmt->fetchColumn();

        $sql = "SELECT COUNT(*) FROM inspecoes WHERE status = 'Concluído'";
        if ($empresaId) {
            $sql .= " AND empresa_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $empresaId ? $stmt->execute([$empresaId]) : $stmt->execute();
        $stats['concluidas'] = $stmt->fetchColumn();

        $sql = "SELECT COUNT(*) FROM inspecoes WHERE status = 'Prazo Vencido'";
        if ($empresaId) {
            $sql .= " AND empresa_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $empresaId ? $stmt->execute([$empresaId]) : $stmt->execute();
        $stats['vencidas'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Obter inspeções por semana do ano
     * @param int $semana Número da semana
     * @param int $ano Ano (opcional, padrão: ano atual)
     * @param int|null $empresaId ID da empresa para filtrar
     * @return array Lista de inspeções
     */
    public function obterPorSemana($semana, $ano = null, $empresaId = null)
    {
        if ($ano === null) {
            $ano = date('Y');
        }

        $sql = "SELECT i.*, 
                s.nome as setor_nome, 
                l.nome as local_nome, 
                t.nome as tipo_nome,
                t.cor as tipo_cor,
                e.nome as empresa_nome
                FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                JOIN tipos_apontamento t ON i.tipo_id = t.id
                LEFT JOIN empresas e ON i.empresa_id = e.id
                WHERE YEARWEEK(i.data_apontamento, 1) = ?";

        $params = [$ano . str_pad($semana, 2, '0', STR_PAD_LEFT)];

        if ($empresaId) {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $empresaId;
        }

        $sql .= " ORDER BY s.nome, l.nome, i.data_apontamento";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obter todas as empresas que possuem inspeções
     * @return array Lista de empresas
     */
    public function obterEmpresasComInspecoes()
    {
        $sql = "SELECT DISTINCT e.id, e.nome
                FROM inspecoes i
                JOIN empresas e ON i.empresa_id = e.id
                ORDER BY e.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obter próximos vencimentos
     * @param int|null $empresaId ID da empresa (null para todas)
     * @param int $dias Número de dias para buscar vencimentos
     * @return array Lista de inspeções com vencimento próximo
     */
    public function obterProximosVencimentos($empresaId = null, $dias = 7)
    {
        $sql = "SELECT i.*, 
                s.nome as setor_nome, 
                l.nome as local_nome, 
                t.nome as tipo_nome,
                t.cor as tipo_cor,
                u.nome as usuario_nome,
                e.nome as empresa_nome,
                DATEDIFF(i.prazo, CURDATE()) as dias_restantes
                FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                JOIN tipos_apontamento t ON i.tipo_id = t.id
                JOIN usuarios u ON i.usuario_id = u.id
                LEFT JOIN empresas e ON i.empresa_id = e.id
                WHERE i.status = 'Em Aberto' 
                AND i.prazo >= CURDATE() AND i.prazo <= DATE_ADD(CURDATE(), INTERVAL ? DAY)";

        $params = [$dias];

        if ($empresaId) {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $empresaId;
        }

        $sql .= " ORDER BY i.prazo ASC, i.numero_inspecao ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Conta os riscos eliminados (status Concluído) em um mês específico.
     * @param int $ano Ano
     * @param int $mes Mês
     * @param array $tiposRiscoIds IDs dos tipos de apontamento a serem contados
     * @param int|null $empresaId ID da empresa para filtrar (opcional)
     * @return int
     */
    public function contarRiscosEliminadosMes($ano, $mes, $tiposRiscoIds, $empresaId = null)
    {
        if (empty($tiposRiscoIds)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($tiposRiscoIds), '?'));

        $sql = "SELECT COUNT(id) FROM inspecoes 
                WHERE status = 'Concluído'
                AND tipo_id IN ($placeholders)
                AND YEAR(data_conclusao) = ? 
                AND MONTH(data_conclusao) = ?";

        $params = $tiposRiscoIds;
        $params[] = $ano;
        $params[] = $mes;

        if ($empresaId !== null) {
            $sql .= " AND empresa_id = ?";
            $params[] = $empresaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Conta os riscos eliminados (status Concluído) acumulado no ano até um mês específico.
     * @param int $ano Ano
     * @param int $mes Mês
     * @param array $tiposRiscoIds IDs dos tipos de apontamento a serem contados
     * @param int|null $empresaId ID da empresa para filtrar (opcional)
     * @return int
     */
    public function contarRiscosEliminadosAnoAcumulado($ano, $mes, $tiposRiscoIds, $empresaId = null)
    {
        if (empty($tiposRiscoIds)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($tiposRiscoIds), '?'));

        $sql = "SELECT COUNT(id) FROM inspecoes 
                WHERE status = 'Concluído'
                AND tipo_id IN ($placeholders)
                AND YEAR(data_conclusao) = ? 
                AND MONTH(data_conclusao) <= ?";

        $params = $tiposRiscoIds;
        $params[] = $ano;
        $params[] = $mes;

        if ($empresaId !== null) {
            $sql .= " AND empresa_id = ?";
            $params[] = $empresaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna uma lista de anos únicos com base na data de apontamento para usar no filtro.
     * @return array
     */
    public function getAnosDisponiveis()
    {
        $sql = "SELECT DISTINCT YEAR(data_apontamento) as ano FROM inspecoes ORDER BY ano DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Listar inspeções para geração de PDF com filtros específicos.
     * @param array $filtros Filtros a aplicar (empresa_id, status_in)
     * @return array Lista de inspeções
     */
    public function listarParaPdf($filtros = [])
    {
        $sql = "SELECT i.numero_inspecao, i.apontamento, i.data_apontamento, i.prazo, i.status, 
                s.nome as setor_nome, 
                l.nome as local_nome
                FROM inspecoes i
                JOIN setores s ON i.setor_id = s.id
                JOIN locais l ON i.local_id = l.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros["empresa_id"])) {
            $sql .= " AND i.empresa_id = ?";
            $params[] = $filtros["empresa_id"];
        }

        if (!empty($filtros["status_in"]) && is_array($filtros["status_in"])) {
            $placeholders = implode(",", array_fill(0, count($filtros["status_in"]), "?"));
            $sql .= " AND i.status IN ($placeholders)";
            $params = array_merge($params, $filtros["status_in"]);
        }

        $sql .= " ORDER BY i.prazo ASC, i.numero_inspecao ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

}
