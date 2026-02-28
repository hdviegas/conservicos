#!/bin/bash
set -euo pipefail

echo "[mysql-init] Ensuring root@'%' exists and is granted..."
SQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD//\'/\'\'}"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-SQL
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '${SQL_ROOT_PASSWORD}';
ALTER USER 'root'@'%' IDENTIFIED BY '${SQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL

echo "[mysql-init] root@'%' granted."
