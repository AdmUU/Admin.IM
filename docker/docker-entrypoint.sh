#!/bin/bash
set +e

SHOULD_EXIT=0
MYSQL_EXIT_CODE=
ENV_FILE="/data/.env"

check_required_env() {
    local var_name="$1"
    local allow_empty="${2:-false}"

    if [ -z "${!var_name+x}" ]; then
        echo "[ERROR] ${var_name} environment variable is not set"
        sleep 60
        exit 1
    fi

    if [ "$allow_empty" = "false" ] && [ -z "${!var_name}" ]; then
        echo "[ERROR] ${var_name} environment variable cannot be empty"
        sleep 60
        exit 1
    fi
}

check_mysql_connection() {
    mysql -h"$ADM_DB_HOST" -u"$ADM_DB_USERNAME" -P"$ADM_DB_PORT" -p"$ADM_DB_PASSWORD" "$ADM_DB_DATABASE" -e "SELECT 1;"
}

check_redis_connection() {
    redis-cli -h "$ADM_REDIS_HOST" -p "$ADM_REDIS_PORT" <<EOF
AUTH "$ADM_REDIS_PASSWORD"
PING
EOF
}

cleanup() {
    echo "[ERROR] Received SIGTERM signal, cleaning up..."
    SHOULD_EXIT=1
}

check_required_env "ADM_DB_HOST"
check_required_env "ADM_DB_PORT"
check_required_env "ADM_DB_USERNAME"
check_required_env "ADM_DB_PASSWORD"
check_required_env "ADM_DB_DATABASE"
check_required_env "ADM_REDIS_HOST"
check_required_env "ADM_REDIS_PORT"
check_required_env "ADM_REDIS_PASSWORD" true

trap cleanup SIGTERM

mysql_connection_attempts=0
max_mysql_connection_attempts=60
while true; do
  if [ $SHOULD_EXIT -eq 1 ]; then
      echo "[ERROR] Received termination signal during MySQL connection attempts. Exiting..."
      exit 0
  fi

  if [ $mysql_connection_attempts -ge $max_mysql_connection_attempts ]; then
    echo "[ERROR] Failed to connect to MySQL database after $max_mysql_connection_attempts attempts. Exiting..."
    exit 1
  fi

  response=$(check_mysql_connection 2>&1)
  MYSQL_EXIT_CODE=$?

  if [ $MYSQL_EXIT_CODE -eq 0 ]; then
    echo "[INFO] MySQL database connection established."
    break
  fi

  if [ $mysql_connection_attempts -eq 0 ]; then
    echo "[ERROR] Unable to connect to MySQL server ($ADM_DB_USERNAME@$ADM_DB_HOST:$ADM_DB_PORT)" >&2
    echo $response >&2
  fi

  sleep 2

  mysql_connection_attempts=$((mysql_connection_attempts + 1))
done

redis_connection_attempts=0
max_redis_connection_attempts=30
while true; do
  if [ $SHOULD_EXIT -eq 1 ]; then
      echo "[ERROR] Received termination signal during Redis connection attempts. Exiting..."
      exit 0
  fi

  if [ $redis_connection_attempts -ge $max_redis_connection_attempts ]; then
    echo "[ERROR] Failed to connect to Redis after $max_redis_connection_attempts attempts. Exiting..." >&2
    exit 1
  fi

  response=$(check_redis_connection 2>&1)
  if echo "$response" | grep -q "OK" && echo "$response" | grep -q "PONG"; then
      echo "[INFO] Redis connection established."
      break
  fi

  if [ $redis_connection_attempts -eq 0 ]; then
    echo "[ERROR] Unable to connect to redis server ($ADM_REDIS_HOST:$ADM_REDIS_PORT)" >&2
    echo "$response" >&2
  fi

  sleep 2

  redis_connection_attempts=$((redis_connection_attempts + 1))
done

if [ ! -f "$ENV_FILE" ]; then
    echo "[INFO] Creating /data/.env from .env.example..."
    cp .env.example "$ENV_FILE"

fi

ADM_REDIS_DB=${ADM_REDIS_DB:-0}

sed -i "s#DB_HOST=.*#DB_HOST=${ADM_DB_HOST}#g" ${ENV_FILE}
sed -i "s#DB_PORT=.*#DB_PORT=${ADM_DB_PORT}#g" ${ENV_FILE}
sed -i "s#DB_DATABASE=.*#DB_DATABASE=${ADM_DB_DATABASE}#g" ${ENV_FILE}
sed -i "s#DB_USERNAME=.*#DB_USERNAME=${ADM_DB_USERNAME}#g" ${ENV_FILE}
sed -i "s#DB_PASSWORD=.*#DB_PASSWORD=${ADM_DB_PASSWORD}#g" ${ENV_FILE}
sed -i "s#REDIS_HOST=.*#REDIS_HOST=${ADM_REDIS_HOST}#g" ${ENV_FILE}
sed -i "s#REDIS_PORT=.*#REDIS_PORT=${ADM_REDIS_PORT}#g" ${ENV_FILE}
sed -i "s#REDIS_AUTH=.*#REDIS_AUTH=${ADM_REDIS_PASSWORD}#g" ${ENV_FILE}
sed -i "s#REDIS_DB=.*#REDIS_DB=${ADM_REDIS_DB}#g" ${ENV_FILE}

user_count=$(mysql -h"$ADM_DB_HOST" -u"$ADM_DB_USERNAME" -P"$ADM_DB_PORT" -p"$ADM_DB_PASSWORD" "$ADM_DB_DATABASE" -N -e "SELECT COUNT(*) FROM system_user;" 2>/dev/null || echo "0")
echo "[INFO] User count $user_count"

if [ ! -f "/data/INSTALL.LOCK" ] && [ "$user_count" -eq "0" ]; then
    php bin/hyperf.php mine:install -n
    touch "/data/INSTALL.LOCK"
fi

#composer dump-autoload -o > /dev/null 2>&1

exec "$@"