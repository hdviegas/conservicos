# Deploy simples com Docker + Nginx no host

## 1) Preparar ambiente

1. Copie `.env.prod.example` para `.env.prod`.
2. Ajuste ao menos:
   - `APP_KEY`
   - `APP_URL`
   - `DB_PASSWORD`
   - `REDIS_HOST_PORT` (ex.: `6380`, se `6379` já estiver em uso no host)
3. Gere `APP_KEY` se necessário:

```bash
docker compose run --rm app php artisan key:generate --show
```

## 2) Subir stack de produção

```bash
docker compose \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  --env-file .env.prod \
  up -d --build
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
  --env-file .env.prod \
  up -d --build
```

## Observação sobre portas do Redis

- `REDIS_PORT` é a porta interna do container (normalmente `6379`) usada pela aplicação.
- `REDIS_HOST_PORT` é a porta publicada no host Linux (ex.: `6380`) para evitar conflito com outro Redis já instalado.
