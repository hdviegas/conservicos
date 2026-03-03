#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

MYSQL_CONTAINER_NAME="${MYSQL_CONTAINER_NAME:-conservicos-mysql}"
ENV_FILE="${ROOT_DIR}/.env"

read_env_var() {
  local key="$1"
  local file="$2"
  local line
  line="$(awk -v k="${key}" 'index($0, k"=") == 1 { sub(/\r$/, "", $0); print; exit }' "${file}" 2>/dev/null || true)"
  if [[ -n "${line}" ]]; then
    printf '%s' "${line#*=}"
  fi
}

if ! docker ps --format '{{.Names}}' | rg -x "${MYSQL_CONTAINER_NAME}" >/dev/null; then
  echo "MySQL container '${MYSQL_CONTAINER_NAME}' is not running."
  echo "Start the stack first and try again."
  exit 1
fi

CURRENT_ROOT_PASSWORD="${MYSQL_ROOT_CURRENT_PASSWORD:-}"
if [[ -z "${CURRENT_ROOT_PASSWORD}" && -f "${ENV_FILE}" ]]; then
  CURRENT_ROOT_PASSWORD="$(read_env_var "MYSQL_ROOT_CURRENT_PASSWORD" "${ENV_FILE}")"
fi
if [[ -z "${CURRENT_ROOT_PASSWORD}" && -f "${ENV_FILE}" ]]; then
  CURRENT_ROOT_PASSWORD="$(read_env_var "MYSQL_ROOT_PASSWORD" "${ENV_FILE}")"
fi
if [[ -z "${CURRENT_ROOT_PASSWORD}" && -f "${ENV_FILE}" ]]; then
  CURRENT_ROOT_PASSWORD="$(read_env_var "DB_PASSWORD" "${ENV_FILE}")"
fi

if [[ -z "${CURRENT_ROOT_PASSWORD}" ]]; then
  read -rsp "Current MySQL root password (set MYSQL_ROOT_CURRENT_PASSWORD or MYSQL_ROOT_PASSWORD in .env to skip): " CURRENT_ROOT_PASSWORD
  echo ""
fi

if [[ -z "${CURRENT_ROOT_PASSWORD}" ]]; then
  echo "Root password is required."
  exit 1
fi

NEW_ROOT_PASSWORD="${MYSQL_ROOT_NEW_PASSWORD:-${CURRENT_ROOT_PASSWORD}}"
SQL_PASSWORD="${NEW_ROOT_PASSWORD//\'/\'\'}"

docker exec -i -e MYSQL_PWD="${CURRENT_ROOT_PASSWORD}" "${MYSQL_CONTAINER_NAME}" mysql -uroot <<SQL
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '${SQL_PASSWORD}';
ALTER USER 'root'@'%' IDENTIFIED BY '${SQL_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SELECT user, host FROM mysql.user WHERE user = 'root' ORDER BY host;
SQL

echo ""
echo "Done: root@'%' is present and granted."
