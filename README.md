# Sistema de Fluxo de Caixa

Aplicacao web em Laravel 9 para controle financeiro e operacao de produtos, com acessos separados por perfil (`admin` e `funcionario`).

## Visao Geral

O sistema possui dois ambientes principais:

- Painel administrativo (perfil `admin`)
- Painel operacional de leitura/finalizacao (perfil `funcionario`)

O controle de permissao e feito por middlewares de perfil.

## Perfis de Acesso

### Admin

Tem acesso completo a:

- Dashboard e Home
- Lancamentos (CRUD)
- Tipos (CRUD)
- Centro de custo (CRUD)
- Usuarios (CRUD com tipo de usuario)
- Produtos (CRUD com alerta de validade)

### Funcionario

Tem acesso somente a:

- Leitor de produtos (consulta)
- Finalizacao de compra

Sem acesso a CRUD administrativo.

## Modulos do Sistema

### 1. Usuarios (Admin)

Cadastro e manutencao de usuarios com:

- Nome
- E-mail
- Senha
- Tipo de usuario (`admin` ou `funcionario`)

Regras de protecao implementadas:

- Admin nao pode excluir o proprio usuario logado.
- Nao e permitido excluir o ultimo usuario admin.

### 2. Fluxo de Caixa (Admin)

Funcionalidades:

- Cadastro de tipos
- Cadastro de centros de custo
- Cadastro de lancamentos de entrada e saida
- Filtro por descricao e periodo

### 3. Produtos (Admin)

Cadastro de produtos com os campos:

- ID
- Nome
- Lote
- Quantidade
- Tipo de quantidade (`caixa` ou `unidade`)
- Validade do produto
- Preco de compra
- Preco de venda

Inclui:

- Edicao de produto
- Exclusao de produto
- Alertas de vencimento na listagem:
  - Vencido
  - Vence hoje
  - Vence em ate 30 dias
  - Validade ok

### 4. Leitor de Produtos (Funcionario)

Tela de consulta mostrando:

- Nome de cada produto
- Quantidade de cada produto
- Valor de cada produto (preco de compra)
- Total por item (`preco_compra * quantidade`)
- Valor total da compra

### 5. Finalizar Compra (Funcionario)

Fluxo disponivel a partir do leitor de produtos.

Permite selecionar forma de pagamento padrao de mercado:

- PIX
- Dinheiro
- Cartao de debito
- Cartao de credito
- Boleto
- Vale alimentacao

Opcao adicional:

- `Quer dividir valor?` (`sim` ou `nao`)
- Parcelamento de `1x` a `12x` para qualquer forma de pagamento
- Exibicao do valor por parcela na tela

Observacao:

- A finalizacao gera confirmacao na interface (mensagem), sem persistencia de pedido/compra em tabela especifica.

## Requisitos

- PHP 8.2+
- Composer
- MySQL ou MariaDB
- Node.js + NPM

## Stack Tecnica

- Laravel 9
- Laravel Breeze (autenticacao)
- Blade
- Bootstrap 5
- Vite
- Tailwind (dependencia instalada via Vite)

## Instalacao e Execucao

1. Acesse a pasta do projeto:

```bash
cd ProjetoFluxo_Caixa
```

2. Instale dependencias PHP:

```bash
composer install
```

3. Crie o arquivo de ambiente:

```bash
copy .env-example .env
```

4. Ajuste credenciais do banco no `.env`:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

5. Gere chave da aplicacao:

```bash
php artisan key:generate
```

6. Rode migrations:

```bash
php artisan migrate
```

7. Popule usuarios padrao:

```bash
php artisan db:seed
```

8. Instale dependencias front-end:

```bash
npm install
```

9. Rode o front-end:

```bash
npm run dev
```

10. Em outro terminal, inicie o servidor:

```bash
php artisan serve
```

Aplicacao em:

- `http://127.0.0.1:8000`

## Credenciais Padrao

### Admin

- Email: `admin@example.com`
- Senha: `senha_admin`

### Funcionario

- Email: `funcionario@example.com`
- Senha: `senha_funcionario`

## Redirecionamento por Perfil

- Admin: redireciona para `dashboard`
- Funcionario: redireciona para `leitor-produtos`

## Principais Rotas

### Funcionario

- `GET /leitor-produtos`
- `GET /leitor-produtos/finalizar-compra`
- `POST /leitor-produtos/finalizar-compra`

### Admin

- `GET /dashboard`
- `GET/POST /usuario/*`
- `GET/POST /produto/*`
- `GET/POST /tipo/*`
- `GET/POST /centro-de-custo/*`
- `GET/POST /lancamento/*`

## Comandos Uteis

```bash
php artisan route:list
php artisan optimize:clear
php artisan test
npm run build
```

## Troubleshooting

### Erro "Could not open input file: artisan"

Execute os comandos dentro de:

`C:\Users\griso\Documents\projetos\fluxoCaixa\ProjetoFluxo_Caixa`

### Erro de conexao com MySQL (HY000/2002)

- Verifique se o servico MySQL esta em execucao
- Confira `DB_HOST` e `DB_PORT`
- Confirme que o banco existe

### Erro "Unknown database"

Crie o banco informado em `DB_DATABASE` antes de rodar migration.

### Erro "Access denied for user"

Ajuste `DB_USERNAME` e `DB_PASSWORD` no `.env` e rode:

```bash
php artisan config:clear
```
