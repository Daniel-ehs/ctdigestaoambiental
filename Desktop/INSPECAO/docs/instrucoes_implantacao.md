# Instruções de Implantação na HostGator

Este documento contém as instruções detalhadas para implantar o Sistema de Gerenciamento de Inspeções de Segurança em um servidor compartilhado da HostGator.

## Requisitos do Servidor

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Suporte a mod_rewrite (para URLs amigáveis)
- Extensões PHP: mysqli, gd, json, session, fileinfo

## Passos para Implantação

### 1. Preparação do Banco de Dados

1. Acesse o cPanel da sua conta HostGator
2. Localize a seção "Bancos de Dados" e clique em "MySQL Databases"
3. Crie um novo banco de dados:
   - Digite um nome para o banco de dados (ex: `inspecao_seguranca`)
   - Clique em "Create Database"
4. Crie um novo usuário para o banco de dados:
   - Digite um nome de usuário (ex: `inspecao_user`)
   - Digite uma senha forte
   - Clique em "Create User"
5. Adicione o usuário ao banco de dados:
   - Selecione o banco de dados e o usuário criados
   - Conceda todas as permissões (ALL PRIVILEGES)
   - Clique em "Add"
6. Anote as informações do banco de dados para uso posterior

### 2. Upload dos Arquivos

1. Acesse o cPanel da sua conta HostGator
2. Localize a seção "Arquivos" e clique em "Gerenciador de Arquivos"
3. Navegue até a pasta `public_html` (ou uma subpasta, se desejar)
4. Clique em "Upload" e selecione o arquivo ZIP do sistema
5. Após o upload, extraia o arquivo ZIP
6. Verifique se todos os arquivos foram extraídos corretamente

### 3. Configuração do Sistema

1. Navegue até a pasta `config` no gerenciador de arquivos
2. Edite o arquivo `database.php`:
   - Atualize as informações de conexão com o banco de dados:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'seu_banco_de_dados');
     define('DB_USER', 'seu_usuario');
     define('DB_PASS', 'sua_senha');
     ```
3. Edite o arquivo `config.php` se necessário:
   - Ajuste as configurações do sistema conforme necessário

### 4. Criação das Tabelas do Banco de Dados

1. Acesse o cPanel da sua conta HostGator
2. Localize a seção "Bancos de Dados" e clique em "phpMyAdmin"
3. Selecione o banco de dados criado anteriormente
4. Clique na aba "Importar"
5. Clique em "Escolher arquivo" e selecione o arquivo SQL fornecido (`database/inspecao_seguranca.sql`)
6. Clique em "Executar"

### 5. Configuração de Permissões

1. No gerenciador de arquivos, navegue até a pasta raiz do sistema
2. Crie as seguintes pastas se não existirem:
   - `uploads/fotos_antes`
   - `uploads/fotos_depois`
   - `uploads/pdfs`
3. Configure as permissões das pastas:
   - Clique com o botão direito em cada pasta
   - Selecione "Change Permissions"
   - Defina as permissões para 755 (drwxr-xr-x)

### 6. Configuração do .htaccess

1. Verifique se o arquivo `.htaccess` está presente na raiz do sistema
2. Se necessário, edite o arquivo para ajustar o caminho base:
   ```
   RewriteEngine On
   RewriteBase /
   # Se o sistema estiver em uma subpasta, use:
   # RewriteBase /nome_da_subpasta/
   ```

### 7. Teste de Acesso

1. Abra um navegador e acesse o URL do sistema
2. Você deve ver a tela de login
3. Use as credenciais padrão para acessar:
   - Email: admin@sistema.com
   - Senha: admin123
4. Após o primeiro acesso, altere a senha do administrador

## Solução de Problemas

### Erro de Conexão com o Banco de Dados

- Verifique se as informações de conexão estão corretas no arquivo `config/database.php`
- Confirme se o usuário do banco de dados tem as permissões necessárias

### Erro 500 Internal Server Error

- Verifique o arquivo `.htaccess` para garantir que está configurado corretamente
- Confirme se o mod_rewrite está habilitado no servidor

### Problemas com Upload de Imagens

- Verifique se as pastas de upload existem e têm as permissões corretas (755)
- Confirme se as extensões GD e Fileinfo do PHP estão habilitadas

### Problemas com Geração de PDF

- Verifique se a biblioteca TCPDF está instalada corretamente
- Confirme se a pasta `uploads/pdfs` existe e tem as permissões corretas

## Suporte

Em caso de dúvidas ou problemas durante a implantação, entre em contato com o suporte técnico.
