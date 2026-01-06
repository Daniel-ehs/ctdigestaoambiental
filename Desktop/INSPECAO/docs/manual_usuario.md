# Manual do Usuário - Sistema de Gerenciamento de Inspeções de Segurança

Este manual contém instruções detalhadas sobre como utilizar o Sistema de Gerenciamento de Inspeções de Segurança, desenvolvido para facilitar o registro, acompanhamento e gestão de inspeções de segurança.

## Índice

1. [Acesso ao Sistema](#1-acesso-ao-sistema)
2. [Dashboard](#2-dashboard)
3. [Gerenciamento de Inspeções](#3-gerenciamento-de-inspeções)
4. [Planos de Ação](#4-planos-de-ação)
5. [Projetos](#5-projetos)
6. [Relatórios](#6-relatórios)
7. [Cadastros](#7-cadastros)
8. [Configurações de Usuário](#8-configurações-de-usuário)

## 1. Acesso ao Sistema

### 1.1. Login

1. Acesse o URL do sistema através do navegador
2. Na tela de login, insira seu email e senha
3. Clique no botão "Entrar"

![Tela de Login](../assets/images/docs/login.png)

### 1.2. Recuperação de Senha

Em caso de esquecimento da senha, entre em contato com o administrador do sistema para redefinição.

### 1.3. Alteração de Senha

1. Após fazer login, clique no seu nome no canto superior direito
2. Selecione "Alterar Senha"
3. Insira sua senha atual e a nova senha
4. Clique em "Salvar"

## 2. Dashboard

O dashboard é a tela inicial do sistema, exibindo indicadores e gráficos importantes.

### 2.1. Indicadores

- **Total de Inspeções**: Número total de inspeções registradas
- **Em Aberto**: Inspeções que ainda não foram concluídas
- **Concluídas**: Inspeções que já foram resolvidas
- **Prazo Vencido**: Inspeções em aberto com prazo expirado

### 2.2. Gráficos

- **Apontamentos por Setor**: Gráfico de barras mostrando a distribuição de inspeções por setor
- **Apontamentos por Tipo**: Gráfico de pizza mostrando a distribuição de inspeções por tipo de apontamento

### 2.3. Ações Rápidas

Botões de acesso rápido para as principais funcionalidades:
- Nova Inspeção
- Novo Projeto
- Relatório Semanal
- Gerenciar Inspeções

## 3. Gerenciamento de Inspeções

### 3.1. Listagem de Inspeções

1. Acesse o menu "Inspeções"
2. Utilize os filtros disponíveis para refinar a busca:
   - Setor
   - Local
   - Tipo de Apontamento
   - Status
   - Data
   - Semana do Ano
3. Clique em "Aplicar Filtros" para atualizar a lista

### 3.2. Registro de Nova Inspeção

1. Na tela de listagem de inspeções, clique no botão "Nova Inspeção"
2. Preencha o formulário com os dados da inspeção:
   - Data do Apontamento
   - Setor
   - Local
   - Apontamento (descrição da situação encontrada)
   - Tipo de Apontamento
   - Risco/Consequência
   - Foto do Local (opcional)
   - Resolução Proposta
   - Responsável
   - Prazo de Resolução
3. Clique em "Salvar"

### 3.3. Visualização de Inspeção

1. Na listagem de inspeções, clique no ícone de olho na coluna "Ações"
2. Visualize todos os detalhes da inspeção, incluindo fotos e histórico

### 3.4. Edição de Inspeção

1. Na listagem de inspeções, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

### 3.5. Exclusão de Inspeção

1. Na listagem de inspeções, clique no ícone de lixeira na coluna "Ações"
2. Confirme a exclusão na janela de confirmação

**Observação**: Não é possível excluir inspeções que já possuem planos de ação associados.

## 4. Planos de Ação

### 4.1. Listagem de Planos de Ação

1. Acesse o menu "Planos de Ação"
2. Utilize os filtros disponíveis para refinar a busca
3. Visualize os planos de ação registrados

### 4.2. Criação de Plano de Ação

1. Na listagem de inspeções, clique no ícone de tarefas na coluna "Ações"
2. Visualize os detalhes da inspeção
3. Preencha o formulário do plano de ação:
   - Descrição da Ação Tomada
   - Foto do Depois (obrigatório)
4. Clique em "Salvar e Gerar PDF"

### 4.3. Visualização de Plano de Ação

1. Na listagem de planos de ação, clique no ícone de olho na coluna "Ações"
2. Visualize todos os detalhes do plano de ação, incluindo fotos antes e depois

### 4.4. Geração de PDF

1. Na visualização do plano de ação, clique no botão "Gerar PDF"
2. O sistema gerará um PDF com todos os detalhes do plano de ação
3. Faça o download do PDF gerado

## 5. Projetos

### 5.1. Listagem de Projetos

1. Acesse o menu "Projetos"
2. Utilize os filtros disponíveis para refinar a busca
3. Visualize os projetos registrados

### 5.2. Criação de Projeto

1. Na tela de listagem de projetos, clique no botão "Novo Projeto"
2. Preencha o formulário com os dados do projeto:
   - Fonte
   - Descrição
   - Prazo
   - Status
   - Observação
3. Clique em "Salvar"

### 5.3. Edição de Projeto

1. Na listagem de projetos, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

### 5.4. Conclusão de Projeto

1. Na listagem de projetos, clique no ícone de verificação na coluna "Ações"
2. Insira a data de conclusão
3. Clique em "Concluir"

### 5.5. Cancelamento de Projeto

1. Na listagem de projetos, clique no ícone de cancelamento na coluna "Ações"
2. Confirme o cancelamento na janela de confirmação

## 6. Relatórios

### 6.1. Relatório Semanal

1. Acesse o menu "Relatórios"
2. Clique em "Gerar Relatório Semanal"
3. Selecione os parâmetros do relatório:
   - Semana
   - Setor (opcional)
   - Local (opcional)
4. Clique em "Gerar Relatório"
5. Faça o download do PDF gerado

O relatório semanal é gerado em formato PDF, em orientação paisagem, com fotos à esquerda e detalhes à direita, conforme especificado nos requisitos.

## 7. Cadastros

### 7.1. Setores

#### 7.1.1. Listagem de Setores

1. Acesse o menu "Cadastros" > "Setores"
2. Visualize os setores cadastrados

#### 7.1.2. Criação de Setor

1. Na tela de listagem de setores, clique no botão "Novo Setor"
2. Preencha o formulário:
   - Nome
   - Descrição (opcional)
   - Ativo (sim/não)
3. Clique em "Salvar"

#### 7.1.3. Edição de Setor

1. Na listagem de setores, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

#### 7.1.4. Exclusão de Setor

1. Na listagem de setores, clique no ícone de lixeira na coluna "Ações"
2. Confirme a exclusão na janela de confirmação

**Observação**: Não é possível excluir setores que possuem locais associados.

### 7.2. Locais

#### 7.2.1. Listagem de Locais

1. Acesse o menu "Cadastros" > "Locais"
2. Visualize os locais cadastrados

#### 7.2.2. Criação de Local

1. Na tela de listagem de locais, clique no botão "Novo Local"
2. Preencha o formulário:
   - Nome
   - Setor
   - Descrição (opcional)
   - Ativo (sim/não)
3. Clique em "Salvar"

#### 7.2.3. Edição de Local

1. Na listagem de locais, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

#### 7.2.4. Exclusão de Local

1. Na listagem de locais, clique no ícone de lixeira na coluna "Ações"
2. Confirme a exclusão na janela de confirmação

**Observação**: Não é possível excluir locais que possuem inspeções associadas.

### 7.3. Tipos de Apontamento

#### 7.3.1. Listagem de Tipos

1. Acesse o menu "Cadastros" > "Tipos de Apontamento"
2. Visualize os tipos cadastrados

#### 7.3.2. Criação de Tipo

1. Na tela de listagem de tipos, clique no botão "Novo Tipo"
2. Preencha o formulário:
   - Nome
   - Descrição (opcional)
   - Cor
   - Ativo (sim/não)
3. Clique em "Salvar"

#### 7.3.3. Edição de Tipo

1. Na listagem de tipos, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

#### 7.3.4. Exclusão de Tipo

1. Na listagem de tipos, clique no ícone de lixeira na coluna "Ações"
2. Confirme a exclusão na janela de confirmação

**Observação**: Não é possível excluir tipos que possuem inspeções associadas.

### 7.4. Usuários (Apenas para Administradores)

#### 7.4.1. Listagem de Usuários

1. Acesse o menu "Cadastros" > "Usuários"
2. Visualize os usuários cadastrados

#### 7.4.2. Criação de Usuário

1. Na tela de listagem de usuários, clique no botão "Novo Usuário"
2. Preencha o formulário:
   - Nome
   - Email
   - Senha
   - Nível de Acesso (Administrador/Inspetor)
3. Clique em "Salvar"

#### 7.4.3. Edição de Usuário

1. Na listagem de usuários, clique no ícone de lápis na coluna "Ações"
2. Atualize os dados necessários
3. Clique em "Salvar"

#### 7.4.4. Exclusão de Usuário

1. Na listagem de usuários, clique no ícone de lixeira na coluna "Ações"
2. Confirme a exclusão na janela de confirmação

**Observação**: Não é possível excluir seu próprio usuário.

## 8. Configurações de Usuário

### 8.1. Alteração de Senha

1. Clique no seu nome no canto superior direito
2. Selecione "Alterar Senha"
3. Insira sua senha atual e a nova senha
4. Clique em "Salvar"

### 8.2. Logout

1. Clique no seu nome no canto superior direito
2. Selecione "Sair"
3. Você será redirecionado para a tela de login

## Suporte

Em caso de dúvidas ou problemas durante o uso do sistema, entre em contato com o suporte técnico.
