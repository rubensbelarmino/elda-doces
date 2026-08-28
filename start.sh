#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORT="${APP_PORT:-8128}"
mkdir -p "$PROJECT_DIR/storage/logs" /tmp/doce-nginx/{client,proxy,fastcgi,uwsgi,scgi}

for dependency in php nginx; do
  command -v "$dependency" >/dev/null 2>&1 || { printf 'Dependência ausente: %s. Execute ./install.sh\n' "$dependency" >&2; exit 1; }
done

if [ "$PORT" != "8128" ]; then
  printf 'APP_PORT diferente de 8128 não é compatível com a configuração do Nginx local; usando 8128.\n' >&2
fi

cleanup() {
  kill "${CLOUDFLARED_PID:-}" "${PHP_ONE_PID:-}" "${PHP_TWO_PID:-}" 2>/dev/null || true
  nginx -s quit -c "$PROJECT_DIR/docker/nginx-local.conf" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

php -d upload_max_filesize=6M -d post_max_size=8M -S 127.0.0.1:9001 -t "$PROJECT_DIR/public" "$PROJECT_DIR/router.php" >"$PROJECT_DIR/storage/logs/php-9001.log" 2>&1 &
PHP_ONE_PID=$!
php -d upload_max_filesize=6M -d post_max_size=8M -S 127.0.0.1:9002 -t "$PROJECT_DIR/public" "$PROJECT_DIR/router.php" >"$PROJECT_DIR/storage/logs/php-9002.log" 2>&1 &
PHP_TWO_PID=$!

if [ "${CLOUDFLARE_TUNNEL:-0}" = "1" ]; then
  if ! command -v cloudflared >/dev/null 2>&1; then
    printf 'CLOUDFLARE_TUNNEL=1, mas cloudflared não foi encontrado.\n' >&2
    exit 1
  fi
  cloudflared tunnel --no-autoupdate --url http://127.0.0.1:8128 >"$PROJECT_DIR/storage/logs/cloudflared.log" 2>&1 &
  CLOUDFLARED_PID=$!
  printf 'Cloudflare Tunnel iniciado; consulte storage/logs/cloudflared.log pelo endereço público.\n'
fi

printf 'Elda Bolos e Doces disponível em http://127.0.0.1:8128\n'
nginx -c "$PROJECT_DIR/docker/nginx-local.conf" -g 'daemon off;'
