# Projeto Fluxo de Caixa

Aplicacao web em Laravel 9 para controle financeiro e operacional, com autenticacao e autorizacao por perfil (`admin` e `funcionario`).

## Sumario

- [1. Objetivo do sistema](#1-objetivo-do-sistema)
- [2. Funcionalidades por perfil](#2-funcionalidades-por-perfil)
- [3. Arquitetura e stack](#3-arquitetura-e-stack)
- [4. Banco de dados](#4-banco-de-dados)
- [5. Arvore de diretorios](#5-arvore-de-diretorios)
- [6. Instalacao e execucao](#6-instalacao-e-execucao)
- [7. Credenciais padrao](#7-credenciais-padrao)
- [8. Rotas principais](#8-rotas-principais)
- [9. SQL util](#9-sql-util)
- [10. Troubleshooting](#10-troubleshooting)

## 1. Objetivo do sistema

O projeto centraliza dois contextos:

- Financeiro: controle de lancamentos (entradas e saidas), tipos e centros de custo.
- Operacional: cadastro e consulta de produtos, com simulacao de finalizacao de compra.

O acesso e segregado por tipo de usuario:

- `admin`: gerenciamento completo.
- `funcionario`: consulta de produtos e fluxo de compra.

## 2. Funcionalidades por perfil

### Admin

1. Home com indicadores financeiros:
- total de lancamentos
- total de entradas
- total de saidas
- saldo atual
- ultimos lancamentos

2. Cadastro de usuarios:
- criar usuario com `nome`, `email`, `senha`, `tipo_usuario`
- editar usuario (inclusive tipo e senha opcional)
- excluir usuario
- protecoes:
  - nao permite excluir o proprio usuario logado
  - nao permite excluir o ultimo admin

3. Cadastro de produtos:
- campos: `id`, `nome`, `lote`, `quantidade`, `tipo_quantidade`, `validade`, `preco_compra`, `preco_venda`
- `tipo_quantidade`: `caixa` ou `unidade`
- editar e excluir produtos
- alertas de vencimento:
  - vencido
  - vence hoje
  - vence em ate 30 dias
  - validade ok

4. Fluxo de caixa:
- CRUD de `tipos`
- CRUD de `centro de custo`
- CRUD de `lancamentos`
- filtro de lancamentos por descricao e periodo
- upload opcional de arquivo no lancamento

### Funcionario

1. Leitor de produtos:
- lista produtos com:
  - nome
  - quantidade
  - valor do produto (`preco_compra`)
  - total por item (`preco_compra * quantidade`)
- exibe valor total da compra

2. Finalizar compra:
- formas de pagamento:
  - PIX
  - Dinheiro
  - Cartao de debito
  - Cartao de credito
  - Boleto
  - Vale alimentacao
- opcao `Quer dividir valor?` (`sim` ou `nao`)
- parcelamento de `1x` ate `12x`
- calculo do valor por parcela na tela
- ao confirmar, sistema retorna mensagem de sucesso

Observacao: atualmente a finalizacao de compra nao gera pedido persistido em tabela propria.

## 3. Arquitetura e stack

- Backend: Laravel 9 (PHP 8.2+)
- Frontend server-side: Blade
- CSS/UI: Bootstrap 5 + Tailwind (via Vite)
- Build front-end: Vite
- Auth: Laravel Breeze
- Banco: MySQL/MariaDB

Controle de acesso:

- `admin` middleware (`App\Http\Middleware\AdminMiddleware`)
- `funcionario` middleware (`App\Http\Middleware\FuncionarioMiddleware`)

Fluxo de login:

- usuario `admin` redireciona para `dashboard`
- usuario `funcionario` redireciona para `leitor-produtos`

## 4. Banco de dados

### Tabelas principais

1. `users`
- `id_user` (PK)
- `nome`
- `email`
- `password`
- `tipo_usuario` (`admin` ou `funcionario`)

2. `tipos`
- `id_tipo` (PK)
- `tipo`
- `deleted_at` (soft delete)

3. `centro_custos`
- `id_centro_custo` (PK)
- `id_tipo`
- `centro_custo`
- `deleted_at` (soft delete)

4. `lancamentos`
- `id_lancamento` (PK)
- `id_user`
- `id_centro_custo`
- `dt_faturamento`
- `descricao`
- `observacoes`
- `valor`
- `arquivo`
- `deleted_at` (soft delete)

5. `produtos`
- `id_produto` (PK)
- `nome`
- `lote`
- `quantidade`
- `tipo_quantidade` (`caixa` ou `unidade`)
- `validade`
- `preco_compra`
- `preco_venda`
- `deleted_at` (soft delete)

### Seeders incluidos

- `AdminUserSeeder`
- `FuncionarioUserSeeder`
- `DatabaseSeeder` (executa os dois seeders acima)

## 5. Arvore de diretorios

Arvore resumida dos arquivos mais relevantes:

```text
ProjetoFluxo_Caixa/
|-- app/
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- CentroCustoController.php
|   |   |   |-- CompraFuncionarioController.php
|   |   |   |-- HomeController.php
|   |   |   |-- LancamentoController.php
|   |   |   |-- ProdutoController.php
|   |   |   |-- TipoController.php
|   |   |   `-- UsuarioController.php
|   |   |-- Middleware/
|   |   |   |-- AdminMiddleware.php
|   |   |   `-- FuncionarioMiddleware.php
|   |   `-- Requests/
|   |       `-- Auth/
|   |-- Models/
|   |   |-- CentroCusto.php
|   |   |-- Lancamento.php
|   |   |-- Produto.php
|   |   |-- Tipo.php
|   |   `-- User.php
|   `-- Mail/
|       |-- OlaLeblanc.php
|       `-- Teste.php
|-- bootstrap/
|-- config/
|-- database/
|   |-- migrations/
|   |   |-- 2014_10_12_000000_create_users_table.php
|   |   |-- 2022_09_19_170251_create_tipos_table.php
|   |   |-- 2022_09_19_170333_create_centro_custos_table.php
|   |   |-- 2022_09_19_170408_create_lancamentos_table.php
|   |   |-- 2026_03_07_000000_add_tipo_usuario_to_users_table.php
|   |   |-- 2026_03_07_010000_create_produtos_table.php
|   |   `-- 2026_03_07_020000_add_lote_to_produtos_table.php
|   `-- seeders/
|       |-- AdminUserSeeder.php
|       |-- DatabaseSeeder.php
|       `-- FuncionarioUserSeeder.php
|-- public/
|-- resources/
|   |-- css/
|   |-- js/
|   `-- views/
|       |-- auth/
|       |-- centro/
|       |-- compra/
|       |-- home/
|       |-- lancamento/
|       |-- layouts/
|       |-- produto/
|       |-- tipo/
|       `-- usuario/
|-- routes/
|   |-- auth.php
|   `-- web.php
|-- tests/
|-- .env-example
|-- artisan
|-- composer.json
|-- package.json
`-- README.md
```

## 6. Instalacao e execucao

### 6.1 Requisitos

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL ou MariaDB em execucao

### 6.2 Passo a passo

1. Entrar na pasta do projeto:

```bash
cd ProjetoFluxo_Caixa
```

2. Instalar dependencias PHP:

```bash
composer install
```

3. Criar arquivo de ambiente:

Windows PowerShell:

```powershell
Copy-Item .env-example .env
```

Linux/macOS:

```bash
cp .env-example .env
```

4. Ajustar credenciais do banco no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fluxo_de_caixa
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

5. Gerar chave da aplicacao:

```bash
php artisan key:generate
```

6. Criar banco (se ainda nao existir) e rodar migrations:

```sql
CREATE DATABASE fluxo_de_caixa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate
```

7. Popular usuarios padrao:

```bash
php artisan db:seed
```

8. Instalar dependencias front-end e subir Vite:

```bash
npm install
npm run dev
```

9. Em outro terminal, iniciar servidor Laravel:

```bash
php artisan serve
```

10. Acessar:

- `http://127.0.0.1:8000`

## 7. Credenciais padrao

Admin:

- Email: `admin@example.com`
- Senha: `senha_admin`

Funcionario:

- Email: `funcionario@example.com`
- Senha: `senha_funcionario`

## 8. Rotas principais

### Rotas base

- `GET /` (redireciona por perfil)
- `GET|POST /login`
- `GET|POST /logout`

### Admin (`auth + admin`)

- `GET /dashboard`
- `GET /home`
- `GET|POST /usuario/*`
- `GET|POST /produto/*`
- `GET|POST /tipo/*`
- `GET|POST /centro-de-custo/*`
- `GET|POST /lancamento/*`

### Funcionario (`auth + funcionario`)

- `GET /leitor-produtos`
- `GET /leitor-produtos/finalizar-compra`
- `POST /leitor-produtos/finalizar-compra`

Para listar todas as rotas:

```bash
php artisan route:list
```

## 9. SQL util

Deletar usuario por ID:

```sql
DELETE FROM users WHERE id_user = 1;
```

Deletar usuario por email:

```sql
DELETE FROM users WHERE email = 'usuario@exemplo.com';
```

Importante:

- pela regra da aplicacao, excluir o proprio usuario logado e excluir o ultimo admin e bloqueado via tela de admin.
- executando SQL direto no banco, essas regras nao sao aplicadas.

## 10. Troubleshooting

### Erro: `Could not open input file: artisan`

Causa comum: comando rodado fora da pasta do Laravel.

Solucao:

```powershell
cd C:\Users\griso\Documents\projetos\fluxoCaixa\ProjetoFluxo_Caixa
php artisan serve
```

### Erro: `SQLSTATE[HY000] [2002] Nenhuma conexao... recusou ativamente`

Causa comum:

- MySQL parado
- host/porta incorretos no `.env`

Solucao:

- iniciar servico MySQL
- revisar `DB_HOST` e `DB_PORT`
- limpar cache de configuracao:

```bash
php artisan config:clear
php artisan cache:clear
```

### Erro: `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`

Causa comum: usuario/senha incorretos no `.env`.

Solucao:

- atualizar `DB_USERNAME` e `DB_PASSWORD`
- executar:

```bash
php artisan config:clear
```

### Erro: `SQLSTATE[HY000] [1049] Unknown database 'fluxo_de_caixa'`

Causa comum: banco nao criado.

Solucao:

```sql
CREATE DATABASE fluxo_de_caixa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Depois:

```bash
php artisan migrate --seed
```

### Erro: `Target class [HomeController] does not exist`

Checklist:

- arquivo `app/Http/Controllers/HomeController.php` existe
- namespace correto: `App\Http\Controllers`
- import em `routes/web.php`:
  - `use App\Http\Controllers\HomeController;`

Se necessario:

```bash
composer dump-autoload
php artisan optimize:clear
```
