# Projeto Fluxo de Caixa

*[Read in English](README.md)*

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
- [11. Deploy no Render](#11-deploy-no-render)

## 1. Objetivo do sistema

O projeto centraliza dois contextos:

- Financeiro: controle de lancamentos (entradas e saidas), tipos e centros de custo.
- Operacional: cadastro e consulta de produtos, com finalizacao de compra e registro de vendas.

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

5. Controle de estoque e movimentacoes:
- tela admin para registrar entradas e saidas por produto
- filtro por dia, semana ou mes
- resumo de quantidade de entradas e saidas
- resumo de valor de venda (saidas)
- estoque atual por produto com valor de venda

6. Fechamento de caixa:
- visualizacao de fechamentos por funcionario
- saldo final consolidado

7. Relatorios:
- visao geral por periodo
- resumo por centro de custo e por tipo
- exportacao CSV e PDF
- opcao de incluir auditoria e fechamento no relatorio

8. Configuracoes gerais do sistema:
- nome do sistema, dados da empresa e mensagem de rodape

9. Auditoria e logs:
- registro de acessos e alteracoes com data/hora e descricao

10. Perfil:
- atualizar dados e senha sem depender de outro admin

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

3. Historico de compras:
- CRUD de compras realizadas pelo funcionario

4. Fechamento de caixa:
- registra fundo de caixa e totais por forma de pagamento
- ao concluir, usuario e deslogado automaticamente

5. Perfil:
- atualizar dados e senha

Observacao: a finalizacao de compra gera registro na tabela `compras`.
## 3. Arquitetura e stack

- Backend: Laravel 9 (PHP 8.2+)
- Frontend server-side: Blade
- CSS/UI: Bootstrap 5 + Tailwind (via Vite)
- Build front-end: Vite
- Auth: Laravel Breeze
- PDF: barryvdh/laravel-dompdf
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

6. `movimentacao_produtos`
- `id_movimentacao` (PK)
- `id_produto`
- `tipo_movimentacao` (`entrada` ou `saida`)
- `quantidade`
- `valor_unitario_venda`
- `data_movimentacao`
- `observacao`

7. `compras`
- `id_compra` (PK)
- `id_user`
- `data_compra`
- `valor_total`
- `forma_pagamento`
- `dividir_valor`
- `parcelas`

8. `fechamento_caixas`
- `id_fechamento` (PK)
- `id_user`
- `data_fechamento`
- `saldo_inicial`
- `valor_dinheiro`
- `valor_cartao`
- `valor_pix`
- `valor_outros`
- `total_entradas`
- `total_saidas`
- `saldo_final`
- `observacoes`

9. `configuracoes`
- `id_configuracao` (PK)
- `nome_sistema`
- `nome_empresa`
- `email_contato`
- `telefone_contato`
- `endereco`
- `moeda`
- `mensagem_rodape`

10. `auditoria_logs`
- `id_log` (PK)
- `id_user`
- `acao`
- `descricao`
- `rota`
- `metodo`
- `url`
- `ip`
- `user_agent`
- `dados` (json)

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
- `GET /controle-financeiro`
- `GET /estoque`
- `POST /estoque/movimentar`
- `GET /fechamento-caixa`
- `GET /relatorios`
- `GET /relatorios/exportar/csv`
- `GET /relatorios/exportar/pdf`
- `GET /configuracoes`
- `POST /configuracoes/atualizar`
- `GET /auditoria`
- `GET|POST /usuario/*`
- `GET|POST /produto/*`
- `GET|POST /tipo/*`
- `GET|POST /centro-de-custo/*`
- `GET|POST /lancamento/*`
- `GET /perfil`
- `POST /perfil/atualizar`
- `POST /perfil/senha`

### Funcionario (`auth + funcionario`)

- `GET /leitor-produtos`
- `GET /leitor-produtos/finalizar-compra`
- `POST /leitor-produtos/finalizar-compra`
- `GET /leitor-produtos/historico`
- `GET|POST /leitor-produtos/historico/*`
- `GET /fechamento-caixa`
- `GET /perfil`
- `POST /perfil/atualizar`
- `POST /perfil/senha`

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

Inserir 100 produtos de teste:

```bash
mysql -u root -p fluxo_de_caixa < database/sql/produtos_teste_100.sql
```

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

### Erro: `SQLSTATE[42S02] Table 'movimentacao_produtos' doesn't exist`

Causa comum: migration de movimentacoes nao executada.

Solucao:

```bash
php artisan migrate
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

## 11. Deploy no Render

O projeto foi preparado para deploy no Render via Docker.

Arquivos adicionados para deploy:

- `Dockerfile`
- `.dockerignore`
- `render.yaml`
- `scripts/00-laravel-deploy.sh`

Passo a passo completo:

- veja [`DEPLOY_RENDER.md`](DEPLOY_RENDER.md)
</content>

## 12. Novas implementacoes operacionais

### Clientes

- cadastro, edicao e desativacao de clientes;
- CPF/CNPJ, inscricao estadual e indicador de contribuinte;
- telefone, e-mail e endereco completo;
- municipio, UF, CEP e codigo IBGE;
- selecao opcional do cliente durante a finalizacao da venda;
- suporte a consumidor nao identificado.

### Itens imutaveis da venda

Os produtos vendidos sao preservados em `compra_itens`. Cada item registra uma fotografia dos dados utilizados no momento da venda:

- produto e codigo;
- lote e GTIN/EAN;
- quantidade e unidade;
- valor unitario, desconto e valor total;
- NCM, CEST, CFOP e CST/CSOSN;
- origem da mercadoria.

A edicao posterior do cadastro de um produto nao altera os itens de vendas antigas.

### Concorrencia de estoque

O fechamento da compra utiliza transacao de banco e `lockForUpdate`. Antes de concluir a venda, o sistema bloqueia os produtos selecionados e valida novamente as quantidades, reduzindo o risco de estoque negativo em vendas simultaneas.

Na mesma transacao, o sistema:

1. Cria a compra.
2. Preserva os itens vendidos.
3. Registra as movimentacoes de saida.
4. Atualiza o estoque.

### Estorno formal de venda

- somente o administrador pode estornar;
- exige motivo entre 10 e 500 caracteres;
- nao apaga nem edita a venda original;
- devolve os produtos ao estoque;
- registra movimentacoes de entrada;
- altera o status da compra para `estornada`;
- vendas estornadas nao entram nos totais do fechamento de caixa;
- vendas com documento fiscal autorizado exigem cancelamento fiscal antes do estorno.

### Fechamento de caixa

Fechamentos concluidos nao podem mais ser editados ou excluidos. O administrador pode reabrir um fechamento com justificativa obrigatoria. O sistema preserva:

- usuario que realizou o fechamento;
- valores originais;
- administrador responsavel pela reabertura;
- data e hora da reabertura;
- motivo informado.

### Seguranca de rotas

- cadastro publico de usuarios desativado;
- usuarios administrados pela area restrita;
- historico de vendas dos funcionarios isolado por usuario;
- administrador com visao global das vendas;
- logout por POST;
- exclusoes administrativas por POST e token CSRF;
- historico de vendas sem edicao ou exclusao livre.

## 13. Base fiscal implementada

O sistema possui uma base estrutural para NF-e modelo 55 e NFC-e modelo 65.

### Produtos

- codigo de barras/GTIN;
- NCM e CEST;
- CFOP;
- CST/CSOSN;
- origem da mercadoria;
- unidade comercial e tributavel;
- aliquotas de ICMS, PIS, COFINS, IPI e FCP;
- CST de PIS, COFINS e IPI.

### Emitente

- razao social e CNPJ;
- inscricao estadual;
- regime tributario;
- CNAE;
- municipio, UF, CEP e codigo IBGE;
- ambiente de homologacao ou producao;
- serie e proximo numero de NF-e/NFC-e;
- CSC e identificador do CSC;
- dados do responsavel tecnico;
- identificacao do provedor fiscal.

### Documentos fiscais

- tabela de documentos fiscais vinculada a venda;
- chave de acesso, protocolo e retorno fiscal;
- XML de envio e XML autorizado;
- eventos fiscais;
- sequencias separadas por modelo, serie e ambiente;
- reserva transacional da numeracao;
- solicitacao idempotente por venda, modelo e ambiente;
- controle de tentativas e proxima tentativa;
- central administrativa com filtros, status e historico de eventos.

O status `aguardando_integracao` significa somente que a solicitacao e a numeracao foram registradas localmente. Ele nao significa que a nota foi transmitida ou autorizada pela SEFAZ.

## 14. Migrations recentes

- `2026_08_25_000000_add_fiscal_sales_foundation.php`: clientes, itens da compra, campos fiscais e documentos fiscais.
- `2026_08_25_010000_add_operational_fiscal_hardening.php`: tributos, reabertura de caixa, eventos e sequencias.
- `2026_08_25_020000_add_fiscal_request_control.php`: idempotencia, tentativas e controle da solicitacao fiscal.

Para atualizar uma instalacao existente:

```powershell
php artisan migrate
php artisan optimize:clear
```

## 15. Testes adicionados

Foram adicionados testes para:

- bloqueio do cadastro publico;
- persistencia dos itens da venda;
- baixa e movimentacao do estoque;
- estorno e devolucao dos produtos ao estoque;
- idempotencia da solicitacao fiscal;
- avanco unico da sequencia fiscal.

Os testes usam `RefreshDatabase`. Configure um banco exclusivo em `.env.testing`. Nunca execute a suite apontando para o banco de desenvolvimento ou producao.

```powershell
php artisan test
```

## 16. Pendencias para emissao fiscal real

A estrutura local nao substitui a autorizacao da SEFAZ. Ainda e necessario:

1. Definir UF, regime tributario e regras fiscais com o contador.
2. Escolher NF-e, NFC-e ou ambas.
3. Contratar uma API fiscal ou implementar comunicacao direta com a SEFAZ.
4. Configurar certificado digital A1 e senha em armazenamento seguro.
5. Configurar CSC e credenciais de homologacao para NFC-e.
6. Validar ICMS, PIS, COFINS, IPI, FCP e substituicao tributaria.
7. Gerar, validar, assinar e transmitir o XML oficial.
8. Tratar autorizacao, rejeicao, consulta, cancelamento e inutilizacao.
9. Implementar contingencia, fila de retentativas e reenvio.
10. Gerar DANFE, DANFE NFC-e e QR Code.
11. Enviar XML/DANFE por e-mail e aplicar retencao legal.
12. Homologar todos os cenarios na SEFAZ antes da producao.

Nunca salve certificados, senhas, CSC ou credenciais fiscais no repositorio Git.

## 17. Pendencias para comercializacao

- atualizar Laravel, PHP e dependencias para versoes suportadas;
- implementar permissoes granulares alem de admin e funcionario;
- adicionar isolamento multiempresa antes de oferecer como SaaS;
- concluir e homologar a integracao fiscal;
- configurar HTTPS e `APP_DEBUG=false`;
- aplicar limite de tentativas de login e cabecalhos de seguranca;
- configurar backup automatico e testar a restauracao;
- adicionar monitoramento de erros e disponibilidade;
- revisar LGPD, privacidade, retencao e resposta a incidentes;
- preparar licenca, contrato, SLA e processo de suporte;
- criar manual operacional e treinamento;
- executar testes de carga, seguranca e recuperacao;
- realizar piloto controlado antes da comercializacao.
