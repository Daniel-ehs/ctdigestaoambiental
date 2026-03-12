<?php

require_once __DIR__ . 
'/Database.php'
;

class PastaMidia {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($nome, $descricao = 
''
, $pastaPaiId = null) {
        try {
            $stmt = $this->db->getConnection()->prepare("INSERT INTO pastas_midias (nome, descricao, pasta_pai_id) VALUES (?, ?, ?)");
            $result = $stmt->execute([$nome, $descricao, $pastaPaiId]);
            
            if ($result) {
                return $this->db->getConnection()->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar pasta: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($pastaPaiId = null) {
        try {
            $sql = "SELECT p.*, 
                           COUNT(m.id) as total_midias,
                           (SELECT COUNT(*) FROM pastas_midias WHERE pasta_pai_id = p.id) as total_subpastas
                    FROM pastas_midias p 
                    LEFT JOIN midias m ON p.id = m.pasta_id AND m.ativo = 1 
                    WHERE 1=1";
            
            $params = [];
            
            if ($pastaPaiId === null) {
                $sql .= " AND p.pasta_pai_id IS NULL";
            } else {
                $sql .= " AND p.pasta_pai_id = ?";
                $params[] = $pastaPaiId;
            }
            
            $sql .= " GROUP BY p.id ORDER BY p.nome";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            $pastas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $pastas;
        } catch (Exception $e) {
            error_log("Erro ao listar pastas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT p.*, 
                       COUNT(m.id) as total_midias,
                       (SELECT COUNT(*) FROM pastas_midias WHERE pasta_pai_id = p.id) as total_subpastas,
                       pp.nome as pasta_pai_nome
                FROM pastas_midias p 
                LEFT JOIN midias m ON p.id = m.pasta_id AND m.ativo = 1 
                LEFT JOIN pastas_midias pp ON p.pasta_pai_id = pp.id
                WHERE p.id = ?
                GROUP BY p.id
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar pasta: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $nome, $descricao = 
''
, $pastaPaiId = null) {
        try {
            // Verifica se não está tentando ser pai de si mesmo
            if ($pastaPaiId == $id) {
                return false;
            }
            
            // Verifica se não está criando uma referência circular
            if ($pastaPaiId && $this->isCircularReference($id, $pastaPaiId)) {
                return false;
            }
            
            $stmt = $this->db->getConnection()->prepare("UPDATE pastas_midias SET nome = ?, descricao = ?, pasta_pai_id = ? WHERE id = ?");
            return $stmt->execute([$nome, $descricao, $pastaPaiId, $id]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar pasta: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            // Verifica se a pasta tem subpastas ou mídias
            $pasta = $this->getById($id);
            if (!$pasta) {
                return false;
            }
            
            if ($pasta[
'total_subpastas'
] > 0 || $pasta[
'total_midias'
] > 0) {
                return false; // Não pode deletar pasta que não está vazia
            }
            
            $stmt = $this->db->getConnection()->prepare("DELETE FROM pastas_midias WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao deletar pasta: " . $e->getMessage());
            return false;
        }
    }
    
    public function getBreadcrumb($pastaId) {
        try {
            $breadcrumb = [];
            $currentId = $pastaId;
            
            while ($currentId) {
                $stmt = $this->db->getConnection()->prepare("SELECT id, nome, pasta_pai_id FROM pastas_midias WHERE id = ?");
                $stmt->execute([$currentId]);
                
                $pasta = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$pasta) {
                    break;
                }
                
                array_unshift($breadcrumb, $pasta);
                $currentId = $pasta[
'pasta_pai_id'
];
            }
            
            return $breadcrumb;
        } catch (Exception $e) {
            error_log("Erro ao gerar breadcrumb: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTree($pastaPaiId = null, $level = 0) {
        try {
            $pastas = $this->getAll($pastaPaiId);
            $tree = [];
            
            foreach ($pastas as $pasta) {
                $pasta[
'level'
] = $level;
                $pasta[
'children'
] = $this->getTree($pasta[
'id'
], $level + 1);
                $tree[] = $pasta;
            }
            
            return $tree;
        } catch (Exception $e) {
            error_log("Erro ao gerar árvore de pastas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getFlatList($pastaPaiId = null, $prefix = 
''
) {
        try {
            $pastas = $this->getAll($pastaPaiId);
            $list = [];
            
            foreach ($pastas as $pasta) {
                $pasta[
'display_name'
] = $prefix . $pasta[
'nome'
];
                $list[] = $pasta;
                
                // Adiciona subpastas recursivamente
                $subpastas = $this->getFlatList($pasta[
'id'
], $prefix . $pasta[
'nome'
] . 
' / '
);
                $list = array_merge($list, $subpastas);
            }
            
            return $list;
        } catch (Exception $e) {
            error_log("Erro ao gerar lista plana de pastas: " . $e->getMessage());
            return [];
        }
    }
    
    public function moveMidia($midiaId, $novaPastaId) {
        try {
            $stmt = $this->db->getConnection()->prepare("UPDATE midias SET pasta_id = ? WHERE id = ?");
            return $stmt->execute([$novaPastaId, $midiaId]);
        } catch (Exception $e) {
            error_log("Erro ao mover mídia: " . $e->getMessage());
            return false;
        }
    }
    
    public function nomeExists($nome, $pastaPaiId = null, $excludeId = null) {
        try {
            $sql = "SELECT id FROM pastas_midias WHERE nome = ?";
            $params = [$nome];
            
            if ($pastaPaiId === null) {
                $sql .= " AND pasta_pai_id IS NULL";
            } else {
                $sql .= " AND pasta_pai_id = ?";
                $params[] = $pastaPaiId;
            }
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao verificar nome da pasta: " . $e->getMessage());
            return false;
        }
    }
    
    private function isCircularReference($pastaId, $novoPaiId) {
        try {
            $currentId = $novoPaiId;
            
            while ($currentId) {
                if ($currentId == $pastaId) {
                    return true; // Referência circular detectada
                }
                
                $stmt = $this->db->getConnection()->prepare("SELECT pasta_pai_id FROM pastas_midias WHERE id = ?");
                $stmt->execute([$currentId]);
                
                $pasta = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$pasta) {
                    break;
                }
                
                $currentId = $pasta[
'pasta_pai_id'
];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao verificar referência circular: " . $e->getMessage());
            return true; // Em caso de erro, assume que há referência circular por segurança
        }
    }
    
    public function getPath($pastaId) {
        try {
            $breadcrumb = $this->getBreadcrumb($pastaId);
            $path = [];
            
            foreach ($breadcrumb as $pasta) {
                $path[] = $pasta[
'nome'
];
            }
            
            return implode(
' / '
, $path);
        } catch (Exception $e) {
            error_log("Erro ao gerar caminho da pasta: " . $e->getMessage());
            return 
''
;
        }
    }
}

?>
