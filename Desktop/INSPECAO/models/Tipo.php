<?php
// models/Tipo.php (Corrigido)

require_once __DIR__ . '/../config/database.php';

class Tipo {
    private $db;

    public function __construct() {
        // Usando o método getInstance() para obter a conexão
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os tipos de apontamento ativos no banco de dados.
     * A tabela no seu SQL é 'tipos_apontamento'.
     * @return array
     */
    public function getAll() {
        // A consulta não precisa de parâmetros, então passamos um array vazio.
        $sql = "SELECT * FROM tipos_apontamento WHERE ativo = 1 ORDER BY nome";
        return $this->db->select($sql, []);
    }
}
?>
