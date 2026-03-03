# Deploy simples com Docker + Nginx no host

## 1) Preparar ambiente

1. Copie `.env.prod.example` para `.env`.
2. Ajuste ao menos:
   - `APP_KEY`
   - `APP_URL`
   - `MYSQL_ROOT_PASSWORD`
   - `DB_PASSWORD`
   - `DB_USERNAME` (recomendado manter `conservicos_app`)
   - `MYSQL_APP_USERNAME`/`MYSQL_APP_PASSWORD` (deve bater com `DB_USERNAME`/`DB_PASSWORD`)
3. Gere `APP_KEY` se necessário:

```bash
docker compose run --rm app php artisan key:generate --show
```

## 2) Subir stack de produção

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  up -d --force-recreate
```

## 2.1) Recriar banco do zero (sem dados)

Use este fluxo quando precisar garantir bootstrap limpo de MySQL:

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  down --remove-orphans

docker volume rm conservicos_mysql_data

docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  up -d mysql

docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  logs -f mysql
```

Depois de ver `ready for connections`, suba o restante:

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  up -d --build app nginx queue scheduler
```

## 3) Configurar Nginx do host

1. Copie `deploy/nginx/conservicos.conf` para:
   - `/etc/nginx/sites-available/conservicos.conf`
2. Ajuste `server_name`.
3. Crie symlink:

```bash
sudo ln -s /etc/nginx/sites-available/conservicos.conf /etc/nginx/sites-enabled/conservicos.conf
```

4. Teste e recarregue:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

## 4) TLS (recomendado)

Com Certbot (Nginx plugin):

```bash
sudo certbot --nginx -d app.seudominio.com.br
```

## 5) Atualização de versão

```bash
git pull
docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  up -d --build
```

## Observação sobre Redis

- Em produção, o Redis fica apenas na rede interna do Docker (`redis:6379`) e não publica porta no host.
- Isso evita conflito com qualquer Redis já instalado no servidor.

## Observação sobre MySQL root remoto

- Na criação inicial do volume (`mysql_data` vazio), o bootstrap do MySQL executa `docker/mysql/init/01-grant-root-remote.sh` e libera `root@'%'`.
- Em bancos já existentes, rode manualmente:

```bash
bash deploy/grant-mysql-root-remote.sh
```

## Credenciais recomendadas

- Use `DB_USERNAME`/`DB_PASSWORD` para a aplicação (usuário de app).
- Use `MYSQL_APP_USERNAME`/`MYSQL_APP_PASSWORD` para bootstrap do usuário da aplicação no primeiro start.
- Use `MYSQL_ROOT_PASSWORD` apenas para tarefas administrativas.
