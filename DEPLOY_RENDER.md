# Deploy no Render (Passo a passo)

Este projeto foi ajustado para deploy no Render via Docker (`Dockerfile` + `render.yaml`).

## 1) Suba o codigo no GitHub

1. Crie/atualize o repositorio no GitHub com os arquivos novos:
   - `Dockerfile`
   - `.dockerignore`
   - `scripts/00-laravel-deploy.sh`
   - `render.yaml`
2. Garanta que a branch principal (ex.: `main`) esteja atualizada.

## 2) Criar o deploy pelo Blueprint (recomendado)

1. Entre em [Render Dashboard](https://dashboard.render.com/).
2. Clique em `New` -> `Blueprint`.
3. Conecte seu repositorio e selecione a branch.
4. O Render vai ler o `render.yaml` e propor:
   - 1 Web Service (`fluxo-caixa-app`)
   - 1 Postgres (`fluxo-caixa-db`)
5. Clique em `Apply`.

## 3) Aguardar o primeiro deploy

Durante o startup, o script `scripts/00-laravel-deploy.sh` executa automaticamente:

1. `composer install` (sem dev)
2. `php artisan config:cache`
3. `php artisan view:cache`
4. `php artisan storage:link`
5. `php artisan migrate --force`

Se tudo estiver certo, a aplicacao sobe no dominio `*.onrender.com`.

## 4) Seed inicial (opcional)

Se quiser criar os usuarios padrao na primeira subida:

1. No service `fluxo-caixa-app`, abra `Environment`.
2. Altere `RUN_DB_SEED` para `true`.
3. Dispare um redeploy manual (`Manual Deploy` -> `Deploy latest commit`).
4. Depois de concluir, volte `RUN_DB_SEED` para `false`.

## 5) Ajustes recomendados apos o deploy

1. Ajuste `APP_URL` para a URL publica do servico (ex.: `https://fluxo-caixa-app.onrender.com`).
2. Se usar dominio customizado, atualize `APP_URL` para o dominio final.
3. Valide login, dashboard, relatorios e upload de arquivo.

## 6) Observacoes importantes

1. O Render usa filesystem efemero por padrao em planos free.
   - Arquivos enviados podem ser perdidos em novo deploy/restart.
2. Para persistir uploads, use:
   - Persistent Disk (plano pago), ou
   - armazenamento externo (ex.: S3).
3. Este setup esta configurado para Postgres no Render (`DB_CONNECTION=pgsql`).
   - Se quiser manter MySQL, troque variaveis de banco para uma instancia MySQL externa.

## 7) Alternativa: criar manualmente (sem Blueprint)

1. `New` -> `Web Service`.
2. Source: seu repositorio.
3. Runtime/Language: `Docker`.
4. Dockerfile path: `./Dockerfile`.
5. Crie um Postgres no Render.
6. Configure as env vars do app manualmente (as mesmas do `render.yaml`).
7. Deploy.
