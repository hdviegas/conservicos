#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

COMPOSE_ARGS=(-f docker-compose.yml)
if [[ -f docker-compose.prod.yml ]]; then
  COMPOSE_ARGS+=(-f docker-compose.prod.yml)
fi

if ! docker compose "${COMPOSE_ARGS[@]}" ps mysql >/dev/null 2>&1; then
  echo "Could not find mysql service via docker compose."
  echo "Ensure containers are created before running this script."
  exit 1
fi

ROOT_PASSWORD="$(
  docker compose "${COMPOSE_ARGS[@]}" exec -T mysql printenv MYSQL_ROOT_PASSWORD 2>/dev/null || true
)"

if [[ -z "${ROOT_PASSWORD}" ]]; then
  echo "MYSQL_ROOT_PASSWORD not found inside mysql container."
  echo "Check your compose/.env configuration and container status."
  exit 1
fi

SQL_PASSWORD="${ROOT_PASSWORD//\'/\'\'}"

docker compose "${COMPOSE_ARGS[@]}" exec -T -e MYSQL_PWD="${ROOT_PASSWORD}" mysql mysql -uroot <<SQL
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '${SQL_PASSWORD}';
ALTER USER 'root'@'%' IDENTIFIED BY '${SQL_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SELECT user, host FROM mysql.user WHERE user = 'root' ORDER BY host;
SQL

echo ""
echo "Done: root@'%' is present and granted."
