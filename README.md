# Sistema de Fluxo de Caixa

Aplicacao web para controle de fluxo de caixa com foco didatico em Laravel 9.

## Funcionalidades

- Cadastro de tipos.
- Cadastro de centros de custo.
- Cadastro de lancamentos (entrada e saida).
- Filtro de lancamentos por descricao e periodo.
- Autenticacao de usuarios (login, registro e recuperacao de senha).

## Tecnologias

- PHP 8.2+
- Laravel 9
- MySQL (ou MariaDB)
- Node.js + NPM
- Vite + Tailwind CSS

## Como rodar o projeto

1. Acesse a pasta do projeto:

```bash
cd ProjetoFluxo_Caixa
```

2. Instale as dependencias PHP:

```bash
composer install
```

3. Crie o arquivo de ambiente e ajuste as credenciais do banco:

```bash
copy .env.example .env
```

Edite no `.env` os campos `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`.

4. Gere a chave da aplicacao:

```bash
php artisan key:generate
```

5. Execute as migrations:

```bash
php artisan migrate
```

6. (Opcional) Crie os usuarios padrao (admin e funcionario):

```bash
php artisan db:seed
```

Credenciais criadas pelos seeders:

- Admin
  - Email: `admin@example.com`
  - Senha: `senha_admin`
- Funcionario
  - Email: `funcionario@example.com`
  - Senha: `senha_funcionario`

7. Instale as dependencias front-end:

```bash
npm install
```

8. Em um terminal, rode o front-end:

```bash
npm run dev
```

9. Em outro terminal, inicie o servidor Laravel:

```bash
php artisan serve
```

A aplicacao ficara disponivel em `http://127.0.0.1:8000`.

## Comandos uteis

```bash
php artisan test
npm run build
```

## Observacoes

- O `DatabaseSeeder` chama `AdminUserSeeder` e `FuncionarioUserSeeder`.
- Novos usuarios registrados pela tela de cadastro recebem `tipo_usuario = funcionario`.
- Tipos e centros de custo devem ser cadastrados pela interface antes dos primeiros lancamentos.
