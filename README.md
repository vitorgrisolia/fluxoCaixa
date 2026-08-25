# Cash Flow Management System

*[Leia em português](README.pt-br.md)*

Laravel 9 web application for financial and operational control, with role-based authentication and authorization (`admin` and `employee`).

## Table of contents

- [1. System purpose](#1-system-purpose)
- [2. Features by role](#2-features-by-role)
- [3. Architecture and stack](#3-architecture-and-stack)
- [4. Database](#4-database)
- [5. Directory tree](#5-directory-tree)
- [6. Installation and setup](#6-installation-and-setup)
- [7. Default credentials](#7-default-credentials)
- [8. Main routes](#8-main-routes)
- [9. Useful SQL](#9-useful-sql)
- [10. Troubleshooting](#10-troubleshooting)
- [11. Deploy on Render](#11-deploy-on-render)
- [12. Fiscal foundation and sales safety](#12-fiscal-foundation-and-sales-safety)
- [13. Commercial readiness checklist](#13-commercial-readiness-checklist)
- [14. Implemented operational evolution](#14-implemented-operational-evolution)

## 1. System purpose

The project centralizes two contexts:

- Financial: management of transactions (income and expenses), types and cost centers.
- Operational: product registration and lookup, with purchase checkout and sales recording.

Access is segregated by user type:

- `admin`: full management.
- `employee`: product lookup and purchase flow.

## 2. Features by role

### Admin

1. Home with financial indicators:
- total transactions
- total income
- total expenses
- current balance
- latest transactions

2. User management:
- create user with `name`, `email`, `password`, `user_type`
- edit user (including type and optional password)
- delete user
- safeguards:
  - cannot delete the currently logged-in user
  - cannot delete the last remaining admin

3. Product management:
- fields: `id`, `name`, `batch`, `quantity`, `quantity_type`, `expiration_date`, `purchase_price`, `sale_price`
- `quantity_type`: `box` or `unit`
- edit and delete products
- expiration alerts:
  - expired
  - expires today
  - expires within 30 days
  - valid

4. Cash flow:
- CRUD for `types`
- CRUD for `cost centers`
- CRUD for `transactions`
- filter transactions by description and period
- optional file upload per transaction

5. Inventory and stock movement control:
- admin screen to record stock in/out per product
- filter by day, week or month
- summary of quantity in/out
- summary of sales value (outbound)
- current stock per product with sale value

6. Cash register closing:
- view closings by employee
- consolidated final balance

7. Reports:
- overview by period
- summary by cost center and by type
- CSV and PDF export
- option to include audit trail and closing data in the report

8. General system settings:
- system name, company data and footer message

9. Audit trail and logs:
- record of access and changes with timestamp and description

10. Profile:
- update own data and password without depending on another admin

### Employee

1. Product lookup:
- lists products with:
  - name
  - quantity
  - unit price (`purchase_price`)
  - line total (`purchase_price * quantity`)
- displays total purchase value

2. Checkout:
- payment methods:
  - PIX (Brazilian instant payment)
  - Cash
  - Debit card
  - Credit card
  - Bank slip (boleto)
  - Meal voucher
- "Split payment?" option (`yes` or `no`)
- installments from `1x` to `12x`
- installment amount calculated on screen
- success message displayed on confirmation

3. Purchase history:
- CRUD of purchases made by the employee

4. Cash register closing:
- records cash float and totals per payment method
- automatic logout upon completion

5. Profile:
- update own data and password

Note: checkout creates a record in the `compras` (purchases) table.

## 3. Architecture and stack

- Backend: Laravel 9 (PHP 8.2+)
- Server-side frontend: Blade
- CSS/UI: Bootstrap 5 + Tailwind (via Vite)
- Frontend build: Vite
- Auth: Laravel Breeze
- PDF: barryvdh/laravel-dompdf
- Database: MySQL/MariaDB

Access control:

- `admin` middleware (`App\Http\Middleware\AdminMiddleware`)
- `employee` middleware (`App\Http\Middleware\FuncionarioMiddleware`)

Login flow:

- `admin` users are redirected to `dashboard`
- `employee` users are redirected to `leitor-produtos` (product lookup)

## 4. Database

### Main tables

1. `users`
- `id_user` (PK)
- `nome` (name)
- `email`
- `password`
- `tipo_usuario` (`admin` or `funcionario`)

2. `tipos` (types)
- `id_tipo` (PK)
- `tipo`
- `deleted_at` (soft delete)

3. `centro_custos` (cost centers)
- `id_centro_custo` (PK)
- `id_tipo`
- `centro_custo`
- `deleted_at` (soft delete)

4. `lancamentos` (transactions)
- `id_lancamento` (PK)
- `id_user`
- `id_centro_custo`
- `dt_faturamento` (billing date)
- `descricao` (description)
- `observacoes` (notes)
- `valor` (amount)
- `arquivo` (file)
- `deleted_at` (soft delete)

5. `produtos` (products)
- `id_produto` (PK)
- `nome` (name)
- `lote` (batch)
- `quantidade` (quantity)
- `tipo_quantidade` (`caixa`/box or `unidade`/unit)
- `validade` (expiration date)
- `preco_compra` (purchase price)
- `preco_venda` (sale price)
- `deleted_at` (soft delete)

6. `movimentacao_produtos` (product stock movements)
- `id_movimentacao` (PK)
- `id_produto`
- `tipo_movimentacao` (`entrada`/in or `saida`/out)
- `quantidade` (quantity)
- `valor_unitario_venda` (unit sale value)
- `data_movimentacao` (movement date)
- `observacao` (note)

7. `compras` (purchases)
- `id_compra` (PK)
- `id_user`
- `data_compra` (purchase date)
- `valor_total` (total value)
- `forma_pagamento` (payment method)
- `dividir_valor` (split payment)
- `parcelas` (installments)

8. `fechamento_caixas` (cash register closings)
- `id_fechamento` (PK)
- `id_user`
- `data_fechamento` (closing date)
- `saldo_inicial` (opening balance)
- `valor_dinheiro` (cash amount)
- `valor_cartao` (card amount)
- `valor_pix` (PIX amount)
- `valor_outros` (other amount)
- `total_entradas` (total income)
- `total_saidas` (total expenses)
- `saldo_final` (final balance)
- `observacoes` (notes)

9. `configuracoes` (settings)
- `id_configuracao` (PK)
- `nome_sistema` (system name)
- `nome_empresa` (company name)
- `email_contato` (contact email)
- `telefone_contato` (contact phone)
- `endereco` (address)
- `moeda` (currency)
- `mensagem_rodape` (footer message)

10. `auditoria_logs` (audit logs)
- `id_log` (PK)
- `id_user`
- `acao` (action)
- `descricao` (description)
- `rota` (route)
- `metodo` (HTTP method)
- `url`
- `ip`
- `user_agent`
- `dados` (data, json)

### Included seeders

- `AdminUserSeeder`
- `FuncionarioUserSeeder` (employee user seeder)
- `DatabaseSeeder` (runs both seeders above)

## 5. Directory tree

Summary of the most relevant files:

```text
ProjetoFluxo_Caixa/
|-- app/
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- AuditoriaController.php
|   |   |   |-- CentroCustoController.php
|   |   |   |-- CompraFuncionarioController.php
|   |   |   |-- ConfiguracaoController.php
|   |   |   |-- ControleFinanceiroController.php
|   |   |   |-- EstoqueController.php
|   |   |   |-- FechamentoCaixaController.php
|   |   |   |-- HomeController.php
|   |   |   |-- HistoricoCompraController.php
|   |   |   |-- LancamentoController.php
|   |   |   |-- PerfilController.php
|   |   |   |-- ProdutoController.php
|   |   |   |-- RelatorioController.php
|   |   |   |-- TipoController.php
|   |   |   `-- UsuarioController.php
|   |   |-- Middleware/
|   |   |   |-- AuditLogMiddleware.php
|   |   |   |-- AdminMiddleware.php
|   |   |   `-- FuncionarioMiddleware.php
|   |   `-- Requests/
|   |       `-- Auth/
|   |-- Models/
|   |   |-- AuditoriaLog.php
|   |   |-- CentroCusto.php
|   |   |-- Compra.php
|   |   |-- ConfiguracaoSistema.php
|   |   |-- FechamentoCaixa.php
|   |   |-- Lancamento.php
|   |   |-- MovimentacaoProduto.php
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
|   |   |-- 2026_03_07_020000_add_lote_to_produtos_table.php
|   |   |-- 2026_03_09_030000_create_movimentacao_produtos_table.php
|   |   |-- 2026_03_12_000000_create_fechamento_caixas_table.php
|   |   |-- 2026_03_12_010000_create_compras_table.php
|   |   |-- 2026_03_12_020000_add_pagamentos_to_fechamento_caixas_table.php
|   |   |-- 2026_03_12_030000_create_configuracoes_table.php
|   |   |-- 2026_03_12_040000_create_auditoria_logs_table.php
|   |   `-- 2026_03_12_050000_add_descricao_to_auditoria_logs_table.php
|   |-- sql/
|   |   `-- produtos_teste_100.sql
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
|       |-- auditoria/
|       |-- centro/
|       |-- compra/
|       |   `-- historico/
|       |-- configuracoes/
|       |-- controleFinanceiro/
|       |-- estoque/
|       |-- fechamentoCaixa/
|       |-- home/
|       |-- lancamento/
|       |-- layouts/
|       |-- perfil/
|       |-- produto/
|       |-- relatorios/
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

## 6. Installation and setup

### 6.1 Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL or MariaDB running

### 6.2 Step by step

1. Enter the project folder:

```bash
cd ProjetoFluxo_Caixa
```

2. Install PHP dependencies:

```bash
composer install
```

3. Create the environment file:

Windows PowerShell:

```powershell
Copy-Item .env-example .env
```

Linux/macOS:

```bash
cp .env-example .env
```

4. Set database credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fluxo_de_caixa
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Create the database (if it doesn't exist yet) and run migrations:

```sql
CREATE DATABASE fluxo_de_caixa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate
```

7. Seed default users:

```bash
php artisan db:seed
```

8. Install front-end dependencies and start Vite:

```bash
npm install
npm run dev
```

9. In another terminal, start the Laravel server:

```bash
php artisan serve
```

10. Access:

- `http://127.0.0.1:8000`

## 7. Default credentials

Admin:

- Email: `admin@example.com`
- Password: `senha_admin`

Employee:

- Email: `funcionario@example.com`
- Password: `senha_funcionario`

## 8. Main routes

### Base routes

- `GET /` (redirects based on role)
- `GET|POST /login`
- `GET|POST /logout`

### Admin (`auth + admin`)

- `GET /dashboard`
- `GET /home`
- `GET /controle-financeiro` (financial control)
- `GET /estoque` (inventory)
- `POST /estoque/movimentar` (record stock movement)
- `GET /fechamento-caixa` (cash register closing)
- `GET /relatorios` (reports)
- `GET /relatorios/exportar/csv`
- `GET /relatorios/exportar/pdf`
- `GET /configuracoes` (settings)
- `POST /configuracoes/atualizar`
- `GET /auditoria` (audit trail)
- `GET|POST /usuario/*` (user management)
- `GET|POST /produto/*` (product management)
- `GET|POST /tipo/*` (type management)
- `GET|POST /centro-de-custo/*` (cost center management)
- `GET|POST /lancamento/*` (transaction management)
- `GET /perfil` (profile)
- `POST /perfil/atualizar`
- `POST /perfil/senha`

### Employee (`auth + funcionario`)

- `GET /leitor-produtos` (product lookup)
- `GET /leitor-produtos/finalizar-compra` (checkout)
- `POST /leitor-produtos/finalizar-compra`
- `GET /leitor-produtos/historico` (purchase history)
- `GET|POST /leitor-produtos/historico/*`
- `GET /fechamento-caixa` (cash register closing)
- `GET /perfil` (profile)
- `POST /perfil/atualizar`
- `POST /perfil/senha`

To list all routes:

```bash
php artisan route:list
```

## 9. Useful SQL

Delete user by ID:

```sql
DELETE FROM users WHERE id_user = 1;
```

Delete user by email:

```sql
DELETE FROM users WHERE email = 'usuario@exemplo.com';
```

Important:

- per application rules, deleting your own logged-in user and deleting the last admin are both blocked in the admin screen.
- running SQL directly against the database bypasses those rules.

Insert 100 test products:

```bash
mysql -u root -p fluxo_de_caixa < database/sql/produtos_teste_100.sql
```

## 10. Troubleshooting

### Error: `Could not open input file: artisan`

Common cause: command run outside the Laravel project folder.

Solution:

```powershell
cd C:\Users\griso\Documents\projetos\fluxoCaixa\ProjetoFluxo_Caixa
php artisan serve
```

### Error: `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`

Common cause:

- MySQL not running
- incorrect host/port in `.env`

Solution:

- start the MySQL service
- review `DB_HOST` and `DB_PORT`
- clear the config cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### Error: `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`

Common cause: incorrect username/password in `.env`.

Solution:

- update `DB_USERNAME` and `DB_PASSWORD`
- run:

```bash
php artisan config:clear
```

### Error: `SQLSTATE[HY000] [1049] Unknown database 'fluxo_de_caixa'`

Common cause: database not created yet.

Solution:

```sql
CREATE DATABASE fluxo_de_caixa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then:

```bash
php artisan migrate --seed
```

### Error: `SQLSTATE[42S02] Table 'movimentacao_produtos' doesn't exist`

Common cause: the stock movement migration hasn't been run.

Solution:

```bash
php artisan migrate
```

### Error: `Target class [HomeController] does not exist`

Checklist:

- file `app/Http/Controllers/HomeController.php` exists
- correct namespace: `App\Http\Controllers`
- import in `routes/web.php`:
  - `use App\Http\Controllers\HomeController;`

If needed:

```bash
composer dump-autoload
php artisan optimize:clear
```

## 11. Deploy on Render

The project is set up to deploy on Render via Docker.

Files added for deployment:

- `Dockerfile`
- `.dockerignore`
- `render.yaml`
- `scripts/00-laravel-deploy.sh`

Full step-by-step guide:

- see [`DEPLOY_RENDER.md`](DEPLOY_RENDER.md)

## 12. Fiscal foundation and sales safety

The project includes a foundation for future NF-e/NFC-e integration:

- immutable sale snapshots in `compra_itens`, including product, quantity, prices, discount, batch, GTIN and fiscal classification
- sale, inventory movement and stock reduction in the same database transaction
- row locking during checkout to prevent concurrent stock overselling
- fiscal product fields for barcode, NCM, CEST, CFOP, CST/CSOSN, origin, units and tax rates
- issuer settings for CNPJ, state registration, tax regime, municipality, state, environment, series and numbering
- customer registration and optional customer selection at checkout
- fiscal documents, events and transactionally locked numbering sequences
- one fiscal request per sale, model and environment, preventing duplicate requests
- read-only sales history; completed sales are reversed instead of edited or deleted
- stock restoration when a sale is reversed, with a mandatory reason
- authorized fiscal documents must be cancelled before their sale can be reversed
- immutable cash closing; an admin may reopen it only with a recorded reason
- public user registration disabled; accounts are managed by an admin
- logout and administrative destructive actions use POST requests and CSRF protection
- administrative fiscal document dashboard with status and event history

To update an existing environment:

```bash
php artisan migrate
php artisan optimize:clear
```

### Fiscal issuance still pending

The database structures do not authorize an invoice at SEFAZ. The status `aguardando_integracao` only means that a local request and number reservation exist. It does not mean that an invoice was transmitted or authorized.

Real issuance still requires:

1. Define the issuer state, tax regime and operation rules with an accountant.
2. Choose NF-e model 55, NFC-e model 65 or both.
3. Choose a fiscal API provider or implement direct SEFAZ communication.
4. Securely configure an A1 certificate, password, CSC and homologation credentials.
5. Validate ICMS, PIS, COFINS, IPI, FCP and tax substitution rules.
6. Generate, validate, sign, transmit and store authorized XML files.
7. Implement status queries, rejection handling, cancellation, number invalidation, contingency and retries.
8. Generate DANFE/DANFE NFC-e, NFC-e QR Code and authorized XML downloads.
9. Send fiscal documents by email and enforce the legal retention policy.
10. Complete SEFAZ homologation before enabling production.

Never commit certificates, passwords, CSC tokens or fiscal credentials. Use environment variables or a managed secret store.

## 13. Commercial readiness checklist

- upgrade Laravel, PHP and dependencies to supported versions
- add granular permissions beyond the current admin/employee roles
- add multi-company isolation before offering the application as SaaS
- run automated tests in an isolated test database for authentication, permissions, checkout, concurrency, inventory, cash closing, reports and fiscal workflows
- configure HTTPS, `APP_DEBUG=false`, secure headers and login rate limiting
- configure automated backups and periodically test restoration
- add error monitoring, availability checks, alerts and operational runbooks
- review LGPD requirements, privacy policy, terms, retention and incident procedures
- define licensing, support, updates, SLA and fiscal responsibility contracts
- prepare automated installation, operating documentation and user training
- perform load, security and disaster recovery tests
- conduct a controlled pilot before commercial production

Database tests use `RefreshDatabase`. Configure a dedicated database in `.env.testing`; never point the test suite to development or production data.

## 14. Implemented operational evolution

The migrations below add the fiscal and operational foundation:

- `2026_08_25_000000_add_fiscal_sales_foundation.php`
- `2026_08_25_010000_add_operational_fiscal_hardening.php`
- `2026_08_25_020000_add_fiscal_request_control.php`

Main new screens:

- **Customers:** registration, editing and deactivation
- **Sales:** global admin view, employee isolation, item snapshots and formal reversal
- **Cash closing:** immutable closing and justified admin reopening
- **Fiscal documents:** request registration, status filtering, numbering and event history

Automated coverage includes sale item persistence, stock reduction, reversal stock restoration and fiscal request idempotency. These tests must be run only against an isolated test database.
