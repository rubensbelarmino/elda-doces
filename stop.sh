#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNTIME_DIR="$PROJECT_DIR/storage/runtime"
QUIET="${1:-}"

log() {
  if [ "$QUIET" != "--quiet" ]; then
    printf '%s\n' "$1"
  fi
}

log "Parando serviços do Elda Bolos e Doces..."

# 1. Se estiver na VPS com serviços systemd
if command -v systemctl >/dev/null 2>&1 && systemctl cat cloudflared.service >/dev/null 2>&1; then
  systemctl stop cloudflared 2>/dev/null || true
  systemctl stop nginx 2>/dev/null || true
  systemctl stop php8.4-fpm 2>/dev/null || systemctl stop php-fpm 2>/dev/null || true
  log "✓ Serviços systemd (Cloudflare Tunnel, Nginx, PHP-FPM) parados com sucesso."
  exit 0
fi

# 2. Ambiente local / standalone: Parar via arquivos PID e sinais
if [ -f /tmp/doce-nginx.pid ]; then
  nginx -s quit -c "$PROJECT_DIR/docker/nginx-local.conf" 2>/dev/null || true
  rm -f /tmp/doce-nginx.pid
fi

if [ -f "$RUNTIME_DIR/cloudflared.pid" ]; then
  PID=$(cat "$RUNTIME_DIR/cloudflared.pid")
  kill "$PID" 2>/dev/null || true
  rm -f "$RUNTIME_DIR/cloudflared.pid"
fi

if [ -f "$RUNTIME_DIR/php-9001.pid" ]; then
  PID=$(cat "$RUNTIME_DIR/php-9001.pid")
  kill "$PID" 2>/dev/null || true
  rm -f "$RUNTIME_DIR/php-9001.pid"
fi

if [ -f "$RUNTIME_DIR/php-9002.pid" ]; then
  PID=$(cat "$RUNTIME_DIR/php-9002.pid")
  kill "$PID" 2>/dev/null || true
  rm -f "$RUNTIME_DIR/php-9002.pid"
fi

# Limpeza por padrão de processo para garantir liberação das portas
pkill -f "php -.*-S 127.0.0.1:9001" 2>/dev/null || true
pkill -f "php -.*-S 127.0.0.1:9002" 2>/dev/null || true
pkill -f "nginx -c .*nginx-local.conf" 2>/dev/null || true
pkill -f "cloudflared tunnel .* elda" 2>/dev/null || true

log "✓ Todos os serviços foram parados com sucesso."
