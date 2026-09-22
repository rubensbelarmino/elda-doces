#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNTIME_DIR="$PROJECT_DIR/storage/runtime"
LOGS_DIR="$PROJECT_DIR/storage/logs"
PORT="${APP_PORT:-8128}"

mkdir -p "$LOGS_DIR" "$RUNTIME_DIR" /tmp/doce-nginx/{client,proxy,fastcgi,uwsgi,scgi}

# 1. Se estiver na VPS com systemd configurado
if command -v systemctl >/dev/null 2>&1 && systemctl cat cloudflared.service >/dev/null 2>&1; then
  printf 'Ambiente VPS detectado. Iniciando serviços do sistema...\n'
  systemctl start nginx php8.4-fpm cloudflared 2>/dev/null || systemctl start nginx php-fpm cloudflared 2>/dev/null || true
  printf '✓ Nginx, PHP-FPM e Cloudflare Tunnel iniciados em segundo plano via systemd.\n'
  printf '✓ Site online: https://elda-doces.com\n'
  printf 'Para parar os serviços, execute: ./stop.sh\n'
  exit 0
fi

# 2. Ambiente local / standalone
for dependency in php nginx; do
  command -v "$dependency" >/dev/null 2>&1 || { printf 'Dependência ausente: %s. Execute ./install.sh\n' "$dependency" >&2; exit 1; }
done

# Parar instâncias anteriores se existirem
if [ -f "$PROJECT_DIR/stop.sh" ]; then
  bash "$PROJECT_DIR/stop.sh" --quiet 2>/dev/null || true
fi

# Iniciar PHP Node 1 em segundo plano (desanexado com setsid)
setsid php -d upload_max_filesize=8M -d post_max_size=12M -S 127.0.0.1:9001 -t "$PROJECT_DIR/public" "$PROJECT_DIR/router.php" </dev/null >"$LOGS_DIR/php-9001.log" 2>&1 &
sleep 0.3
pgrep -f "php -.*-S 127.0.0.1:9001" | head -n 1 > "$RUNTIME_DIR/php-9001.pid" || true

# Iniciar PHP Node 2 em segundo plano (desanexado com setsid)
setsid php -d upload_max_filesize=8M -d post_max_size=12M -S 127.0.0.1:9002 -t "$PROJECT_DIR/public" "$PROJECT_DIR/router.php" </dev/null >"$LOGS_DIR/php-9002.log" 2>&1 &
sleep 0.3
pgrep -f "php -.*-S 127.0.0.1:9002" | head -n 1 > "$RUNTIME_DIR/php-9002.pid" || true

# Iniciar Cloudflare Tunnel se explicitamente solicitado
if [ "${CLOUDFLARE_TUNNEL:-0}" = "1" ] && command -v cloudflared >/dev/null 2>&1; then
  if [ -f "$HOME/.cloudflared/cert.pem" ]; then
    setsid cloudflared tunnel --edge-ip-version 4 --protocol http2 run --url http://127.0.0.1:8128 elda </dev/null >"$LOGS_DIR/cloudflared.log" 2>&1 &
  else
    setsid cloudflared tunnel --no-autoupdate --url http://127.0.0.1:8128 </dev/null >"$LOGS_DIR/cloudflared.log" 2>&1 &
  fi
  sleep 0.3
  pgrep -f "cloudflared tunnel" | head -n 1 > "$RUNTIME_DIR/cloudflared.pid" || true
  printf '✓ Cloudflare Tunnel iniciado em segundo plano.\n'
fi

# Iniciar Nginx em segundo plano (modo daemon padrão)
nginx -c "$PROJECT_DIR/docker/nginx-local.conf"

printf '✓ PHP Cluster (Nodes 9001 e 9002) ativos em segundo plano.\n'
printf '✓ Nginx ativo em segundo plano na porta %s.\n' "$PORT"
printf '\nElda Bolos e Doces rodando 100%% em segundo plano!\n'
printf 'Acesse localmente: http://127.0.0.1:%s\n' "$PORT"
printf 'Para parar todos os serviços a qualquer momento, execute: ./stop.sh\n'
