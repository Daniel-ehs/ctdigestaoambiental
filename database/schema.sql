-- Sistema de Gerenciamento de Funcionários
-- Script para Instalação Limpa (Clean Install)
-- Use este script se quiser apagar tudo e recriar o banco do zero.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. LIMPEZA (Opcional, mas garante que não haverá erro de "already exists")
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS funcionario_postos;
DROP TABLE IF EXISTS funcionario_treinamentos;
DROP TABLE IF EXISTS funcionarios;
DROP TABLE IF EXISTS treinamentos;
DROP TABLE IF EXISTS usuario_empresas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS postos_trabalho;
DROP TABLE IF EXISTS empresas;
DROP TABLE IF EXISTS configuracoes;

DROP VIEW IF EXISTS vw_funcionarios_status;
DROP VIEW IF EXISTS vw_dashboard_contadores;
DROP PROCEDURE IF EXISTS sp_atualizar_status_treinamentos;
DROP TRIGGER IF EXISTS tr_funcionarios_audit_insert;
DROP TRIGGER IF EXISTS tr_funcionarios_audit_update;
DROP TRIGGER IF EXISTS tr_funcionarios_audit_delete;

SET FOREIGN_KEY_CHECKS = 1;

-- 2. CRIAÇÃO DAS TABELAS
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NULL,
    endereco TEXT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE postos_trabalho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    endereco TEXT NULL,
    empresa_id INT NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    hierarquia ENUM('visualizador', 'controlador', 'administrador', 'gerente') NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuario_empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_empresa (usuario_id, empresa_id)
) ENGINE=InnoDB;

CREATE TABLE treinamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    carga_horaria INT NOT NULL,
    prazo_validade INT NOT NULL COMMENT 'Validade em meses',
    descricao TEXT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    foto VARCHAR(255) NULL,
    aso_data DATE NOT NULL,
    aso_validade DATE NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE funcionario_treinamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    treinamento_id INT NOT NULL,
    data_realizacao DATE NOT NULL,
    data_validade DATE NOT NULL,
    certificado VARCHAR(255) NULL,
    status ENUM('valido', 'vencido', 'a_vencer') DEFAULT 'valido',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE,
    FOREIGN KEY (treinamento_id) REFERENCES treinamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE funcionario_postos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    posto_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE,
    FOREIGN KEY (posto_id) REFERENCES postos_trabalho(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tabela VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    acao ENUM('create', 'update', 'delete') NOT NULL,
    dados_anteriores JSON NULL,
    dados_novos JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. DADOS INICIAIS
INSERT INTO usuarios (id, nome, email, senha, hierarquia) VALUES 
(1, 'Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente');

INSERT INTO empresas (id, razao_social, cnpj, endereco) VALUES 
(1, 'Empresa Exemplo Ltda', '12.345.678/0001-90', 'Rua Exemplo, 123 - Centro - São Paulo/SP');

INSERT INTO postos_trabalho (id, nome, endereco, empresa_id) VALUES 
(1, 'Matriz', 'Rua Exemplo, 123 - Centro - São Paulo/SP', 1);

INSERT INTO usuario_empresas (usuario_id, empresa_id) VALUES (1, 1);

-- 4. VIEWS
CREATE VIEW vw_funcionarios_status AS
SELECT 
    f.id, f.nome, f.cpf, f.matricula, f.foto, f.aso_data, f.aso_validade, f.status,
    CASE 
        WHEN f.aso_validade < CURDATE() THEN 'vencido'
        WHEN f.aso_validade <= DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 'vence_15_dias'
        WHEN f.aso_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'vence_30_dias'
        ELSE 'valido'
    END as aso_status,
    (SELECT COUNT(*) FROM funcionario_treinamentos ft WHERE ft.funcionario_id = f.id AND ft.data_validade < CURDATE()) as treinamentos_vencidos,
    (SELECT COUNT(*) FROM funcionario_treinamentos ft WHERE ft.funcionario_id = f.id AND ft.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) as treinamentos_a_vencer,
    CASE 
        WHEN f.aso_validade < CURDATE() OR 
             (SELECT COUNT(*) FROM funcionario_treinamentos ft WHERE ft.funcionario_id = f.id AND ft.data_validade < CURDATE()) > 0 
        THEN 'inapto'
        ELSE 'apto'
    END as aptidao_trabalho
FROM funcionarios f
WHERE f.status = 'ativo';

CREATE VIEW vw_dashboard_contadores AS
SELECT 
    (SELECT COUNT(*) FROM funcionarios WHERE status = 'ativo') as total_funcionarios,
    (SELECT COUNT(*) FROM funcionarios WHERE status = 'ativo' AND aso_validade < CURDATE()) as asos_vencidos,
    (SELECT COUNT(*) FROM funcionarios WHERE status = 'ativo' AND aso_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) as asos_a_vencer,
    (SELECT COUNT(*) FROM funcionario_treinamentos WHERE data_validade < CURDATE()) as treinamentos_vencidos,
    (SELECT COUNT(*) FROM funcionario_treinamentos WHERE data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) as treinamentos_a_vencer,
    (SELECT COUNT(*) FROM empresas WHERE status = 'ativo') as total_empresas,
    (SELECT COUNT(*) FROM postos_trabalho WHERE status = 'ativo') as total_postos;
