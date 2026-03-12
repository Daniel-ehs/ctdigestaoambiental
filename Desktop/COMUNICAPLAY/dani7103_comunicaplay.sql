-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 12/03/2026 às 14:16
-- Versão do servidor: 5.7.23-23
-- Versão do PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `dani7103_comunicaplay`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `midias`
--

CREATE TABLE `midias` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('video','imagem','youtube','link_imagem') COLLATE utf8_unicode_ci NOT NULL,
  `caminho_arquivo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_externa` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `miniatura` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duracao` int(11) DEFAULT '10',
  `tamanho_arquivo` bigint(20) DEFAULT NULL,
  `pasta_id` int(11) DEFAULT NULL,
  `usuario_criador_id` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `midias`
--

INSERT INTO `midias` (`id`, `nome`, `tipo`, `caminho_arquivo`, `url_externa`, `miniatura`, `duracao`, `tamanho_arquivo`, `pasta_id`, `usuario_criador_id`, `ativo`, `data_criacao`) VALUES
(12, 'PLACAR EHS (SITE)', '', NULL, 'https://inspecao.ehspro.com.br/index.php?route=reportar&empresa_id=1&ano=2025', NULL, 30, 0, 2, 2, 1, '2025-08-12 16:30:02'),
(19, 'CTDI', 'video', 'https://comunica.danielsantos.eng.br/assets/uploads/videos/CTDI_1756991300_68b98f440a3e9.mp4', '', NULL, 113, 81478841, 2, 2, 1, '2025-09-04 13:08:20'),
(83, 'Corrimão', 'video', 'https://comunica.danielsantos.eng.br/assets/uploads/videos/Escadas_1758547218_68d14d12bbcbc.mp4', NULL, NULL, 17, 697641, 7, 2, 1, '2025-09-22 13:20:18'),
(84, 'Celular', 'video', 'https://comunica.danielsantos.eng.br/assets/uploads/videos/0904_1758547556_68d14e6474bd6.mp4', NULL, NULL, 47, 24929662, 7, 2, 1, '2025-09-22 13:25:56'),
(85, 'CIPA', 'link_imagem', NULL, 'https://i.imgur.com/d9cFz9n.jpeg', 'https://i.imgur.com/i5BHJnd.png', 30, 0, 7, 2, 1, '2025-09-22 13:39:03'),
(88, 'Brigada', 'link_imagem', NULL, 'https://i.imgur.com/eRNXKZ6.jpeg', 'https://i.imgur.com/ehHCyRm.png', 30, 0, 7, 2, 1, '2025-09-22 13:51:25'),
(89, 'Fevereiro Roxo e Laranja', 'link_imagem', NULL, 'https://www.tcepi.tc.br/wp-content/uploads/2023/02/505f1857-1d76-4744-8051-01e85ef3f819-1536x843.jpg', 'https://i.imgur.com/6oCtVF2.png', 30, 0, 7, 2, 1, '2025-09-22 13:59:40'),
(90, 'AssédioMoral', 'link_imagem', NULL, 'https://i.imgur.com/jBaqumO.png', 'https://i.imgur.com/jBaqumO.png', 30, 0, 7, 2, 1, '2025-09-22 14:08:04'),
(101, 'Seja um Brigadista', 'link_imagem', NULL, 'https://i.imgur.com/rxCAxpK.png', 'https://i.imgur.com/rxCAxpK.png', 30, 0, 7, 2, 1, '2025-12-15 18:57:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pastas_midias`
--

CREATE TABLE `pastas_midias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `pasta_pai_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `pastas_midias`
--

INSERT INTO `pastas_midias` (`id`, `nome`, `descricao`, `pasta_pai_id`, `data_criacao`) VALUES
(2, 'Produção Cielo', 'Midias exibidas nas TVs da Cielo', NULL, '2025-08-04 12:15:12'),
(7, 'EHS', 'Arquivos de EHS', NULL, '2025-08-29 16:31:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `playlists`
--

CREATE TABLE `playlists` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `tela_id` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `usuario_criador_id` int(11) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `playlists`
--

INSERT INTO `playlists` (`id`, `nome`, `tela_id`, `data_inicio`, `data_fim`, `ativo`, `usuario_criador_id`, `data_criacao`, `data_atualizacao`) VALUES
(65, 'Daniel Santos', 16, '2025-09-21 02:53:00', '2040-12-30 03:53:00', 1, 2, '2025-09-21 05:54:07', '2025-09-21 05:54:07'),
(66, 'Cielo TV\'s', 5, '2025-09-22 11:16:00', '2050-12-30 12:16:00', 1, 2, '2025-09-22 14:17:57', '2025-09-22 14:17:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `playlist_midias`
--

CREATE TABLE `playlist_midias` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL,
  `midia_id` int(11) NOT NULL,
  `ordem` int(11) NOT NULL,
  `tempo_exibicao` int(11) NOT NULL,
  `data_adicao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `playlist_midias`
--

INSERT INTO `playlist_midias` (`id`, `playlist_id`, `midia_id`, `ordem`, `tempo_exibicao`, `data_adicao`) VALUES
(166, 66, 89, 1, 30, '2025-11-06 18:16:23'),
(167, 66, 85, 2, 30, '2025-11-06 18:16:23'),
(168, 66, 90, 3, 30, '2025-11-06 18:16:23'),
(169, 66, 83, 4, 17, '2025-11-06 18:16:23'),
(170, 66, 88, 5, 30, '2025-11-06 18:16:23'),
(171, 66, 84, 6, 47, '2025-11-06 18:16:23'),
(172, 66, 12, 7, 30, '2025-11-06 18:16:23'),
(173, 66, 19, 8, 113, '2025-11-06 18:16:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `telas`
--

CREATE TABLE `telas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `hash_unico` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('online','offline') COLLATE utf8_unicode_ci DEFAULT 'offline',
  `ultima_verificacao` timestamp NULL DEFAULT NULL,
  `resolucao` varchar(20) COLLATE utf8_unicode_ci DEFAULT '1920x1080',
  `localizacao` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `com_moldura` tinyint(1) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `telas`
--

INSERT INTO `telas` (`id`, `nome`, `descricao`, `hash_unico`, `status`, `ultima_verificacao`, `resolucao`, `localizacao`, `com_moldura`, `ativo`, `data_criacao`) VALUES
(5, 'TV Cielo', 'Talas de Informações da operação Cielo.', '5d3148a73d5a2aeca197ff4083359d73', 'online', '2026-03-01 10:43:13', '1920x1080', 'Cielo', 1, 1, '2025-08-04 12:13:27'),
(15, 'Teste Denilson', '', 'e32fd7797232b85fc092eebc8155e4ef', 'online', '2025-08-12 16:37:35', '1920x1080', '', 1, 0, '2025-08-05 19:30:06'),
(16, 'Sem Moldura', '', 'c509d02f34cc6abb57bac55110cc7a35', 'online', '2025-11-06 18:17:09', '1920x1080', '', 0, 1, '2025-08-07 12:02:10'),
(17, 'Teste De nova Tela', '', 'c5b90092321eb3e695f8e09b8f460cc5', 'offline', NULL, '1920x1080', 'Teste', 1, 0, '2025-09-21 03:48:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('administrador','gerente') COLLATE utf8_unicode_ci DEFAULT 'gerente',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `ativo`, `data_criacao`, `data_atualizacao`) VALUES
(2, 'Daniel Santos', 'dsantos@ctdi.com', '$2y$10$FMD8cKFOntgCJU87Fi/CLu5FevTLaJ.B.314qPde8nmuEKNDgHiVq', 'administrador', 1, '2025-08-02 23:34:04', '2025-08-29 16:16:05'),
(5, 'Lucas Albino', 'lalbino@ctdi.com', '$2y$10$fM8B7oAw8KXNsmCkZtqJRuYZexgdxT6DvJj0soVm0q3bMdcK5kleK', 'administrador', 1, '2025-08-08 18:55:58', '2025-08-29 16:14:53'),
(6, 'Bruno Barreto', 'b.barreto@ctdi.com', '$2y$10$wQuLRY79U1f7WRwxIYViDe5enKqVIaueOp3SYYN81i0P0mDBbAFMq', 'administrador', 1, '2025-11-06 19:08:27', '2025-11-06 19:09:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_telas`
--

CREATE TABLE `usuario_telas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tela_id` int(11) NOT NULL,
  `data_associacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `usuario_telas`
--

INSERT INTO `usuario_telas` (`id`, `usuario_id`, `tela_id`, `data_associacao`) VALUES
(3, 2, 13, '2025-08-05 14:57:58'),
(4, 2, 14, '2025-08-05 14:57:58'),
(5, 2, 5, '2025-08-05 14:57:59'),
(6, 4, 15, '2025-08-05 19:30:23');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_idx` (`email`),
  ADD KEY `ip_address_idx` (`ip_address`);

--
-- Índices de tabela `midias`
--
ALTER TABLE `midias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasta_id` (`pasta_id`),
  ADD KEY `usuario_criador_id` (`usuario_criador_id`);

--
-- Índices de tabela `pastas_midias`
--
ALTER TABLE `pastas_midias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasta_pai_id` (`pasta_pai_id`);

--
-- Índices de tabela `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tela_id` (`tela_id`),
  ADD KEY `usuario_criador_id` (`usuario_criador_id`);

--
-- Índices de tabela `playlist_midias`
--
ALTER TABLE `playlist_midias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playlist_id` (`playlist_id`),
  ADD KEY `midia_id` (`midia_id`);

--
-- Índices de tabela `telas`
--
ALTER TABLE `telas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_unico` (`hash_unico`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `usuario_telas`
--
ALTER TABLE `usuario_telas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `tela_id` (`tela_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `midias`
--
ALTER TABLE `midias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de tabela `pastas_midias`
--
ALTER TABLE `pastas_midias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de tabela `playlist_midias`
--
ALTER TABLE `playlist_midias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT de tabela `telas`
--
ALTER TABLE `telas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuario_telas`
--
ALTER TABLE `usuario_telas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `midias`
--
ALTER TABLE `midias`
  ADD CONSTRAINT `midias_ibfk_1` FOREIGN KEY (`pasta_id`) REFERENCES `pastas_midias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `midias_ibfk_2` FOREIGN KEY (`usuario_criador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pastas_midias`
--
ALTER TABLE `pastas_midias`
  ADD CONSTRAINT `pastas_midias_ibfk_1` FOREIGN KEY (`pasta_pai_id`) REFERENCES `pastas_midias` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`tela_id`) REFERENCES `telas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlists_ibfk_2` FOREIGN KEY (`usuario_criador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `playlist_midias`
--
ALTER TABLE `playlist_midias`
  ADD CONSTRAINT `playlist_midias_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_midias_ibfk_2` FOREIGN KEY (`midia_id`) REFERENCES `midias` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
