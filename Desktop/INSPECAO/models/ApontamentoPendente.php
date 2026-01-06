<?php
// models/ApontamentoPendente.php (Corrigido)

// O ideal é ter um autoloader, mas mantendo a estrutura original:
require_once __DIR__ . '/../config/database.php';

class ApontamentoPendente {
    private $db;

    public function __construct() {
        // A sua classe Database usa o padrão Singleton, então obtemos a instância assim
        $this->db = Database::getInstance();
    }

    /**
     * Cria um novo registro de apontamento pendente.
     * @param array $dados - Dados do formulário
     * @return string - O ID do último registro inserido
     */
    public function create($dados) {
        // A sua nova classe Database tem um método 'insert' muito útil!
        // Ele lida com a preparação e execução da consulta de forma segura.
        return $this->db->insert('apontamentos_pendentes', [
            'empresa_id' => $dados['empresa_id'],
            'setor_id' => $dados['setor_id'],
            'local_id' => $dados['local_id'],
            'apontamento' => $dados['apontamento'],
            'foto_apontamento' => $dados['foto_apontamento'],
            'contato_nome' => $dados['contato_nome'],
            'contato_info' => $dados['contato_info']
        ]);
    }
    
    /**
     * Busca todos os apontamentos com status 'pendente'.
     * @return array - Lista de apontamentos pendentes
     */
    public function getPendingApontamentos() {
        $sql = "
            SELECT ap.*, e.nome as empresa_nome, s.nome as setor_nome, l.nome as local_nome 
            FROM apontamentos_pendentes ap
            LEFT JOIN empresas e ON ap.empresa_id = e.id
            LEFT JOIN setores s ON ap.setor_id = s.id
            LEFT JOIN locais l ON ap.local_id = l.id
            WHERE ap.status = :status 
            ORDER BY ap.data_criacao DESC
        ";
        
        // Usamos o método 'select' da sua classe, passando os parâmetros diretamente.
        // Isso é mais seguro e evita o erro que você estava tendo.
        return $this->db->select($sql, [':status' => 'pendente']);
    }

    /**
     * Busca um apontamento pendente pelo seu ID.
     * @param int $id - O ID do apontamento
     * @return array|false - O apontamento ou false se não encontrado
     */
    public function getById($id) {
        $sql = "
            SELECT ap.*, e.nome as empresa_nome, s.nome as setor_nome, l.nome as local_nome 
            FROM apontamentos_pendentes ap
            LEFT JOIN empresas e ON ap.empresa_id = e.id
            LEFT JOIN setores s ON ap.setor_id = s.id
            LEFT JOIN locais l ON ap.local_id = l.id
            WHERE ap.id = :id
        ";
        
        // Usamos o método 'selectOne' para buscar um único registro.
        return $this->db->selectOne($sql, [':id' => $id]);
    }
    
    /**
     * Atualiza o status de um apontamento pendente.
     * @param int $id - O ID do apontamento
     * @param string $status - O novo status ('aprovado' ou 'rejeitado')
     * @param int $usuario_id - O ID do usuário que realizou a validação
     * @return bool - True se a atualização foi bem-sucedida, false caso contrário
     */
    public function updateStatus($id, $status, $usuario_id) {
        try {
            $sql = 'UPDATE apontamentos_pendentes SET status = :status, usuario_validacao_id = :usuario_id, data_validacao = NOW() WHERE id = :id';
            
            $params = [
                ':status' => $status,
                ':usuario_id' => $usuario_id,
                ':id' => $id
            ];
            
            // Usamos o método 'query' genérico para executar o UPDATE.
            $stmt = $this->db->query($sql, $params);
            
            // Retorna true se pelo menos uma linha foi afetada
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status do apontamento: " . $e->getMessage());
            return false;
        }
    }
}
?>
