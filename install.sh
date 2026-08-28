#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

as_root() {
  if [ "$(id -u)" -eq 0 ]; then "$@"; elif command -v sudo >/dev/null 2>&1; then sudo "$@"; else
    printf 'Erro: este instalador precisa de root ou sudo para instalar dependências.\n' >&2
    exit 1
  fi
}

install_packages() {
  if command -v apt-get >/dev/null 2>&1; then
    as_root apt-get update
    as_root apt-get install -y php-cli php-fpm php-curl php-mbstring php-xml php-zip php-mysql nginx curl ca-certificates openssl
  elif command -v dnf >/dev/null 2>&1; then
    as_root dnf install -y php-cli php-fpm php-curl php-mbstring php-xml php-zip php-mysqlnd nginx curl ca-certificates openssl
  elif command -v apk >/dev/null 2>&1; then
    as_root apk add --no-cache php84 php84-fpm php84-curl php84-mbstring php84-xml php84-opcache php84-pdo_mysql nginx curl ca-certificates openssl
  else
    printf 'Sistema não suportado. Instale PHP 8+, Nginx, curl e openssl manualmente.\n' >&2
    exit 1
  fi
}

command -v php >/dev/null 2>&1 || install_packages
command -v nginx >/dev/null 2>&1 || install_packages
command -v curl >/dev/null 2>&1 || install_packages
php -r 'if (PHP_VERSION_ID < 80000) { fwrite(STDERR, "PHP 8.0 ou superior é necessário.\n"); exit(1); }'

mkdir -p "$PROJECT_DIR/storage"/{logs,mail,runtime,sessions}
chmod 700 "$PROJECT_DIR/storage" "$PROJECT_DIR/storage"/{runtime,sessions} 2>/dev/null || true

if [ ! -f "$PROJECT_DIR/.env" ]; then
  touch "$PROJECT_DIR/.env"
  if command -v openssl >/dev/null 2>&1; then
    app_key="$(openssl rand -hex 32)"
    printf "APP_KEY=%s\n" "$app_key" >> "$PROJECT_DIR/.env"
  fi
fi


php -l "$PROJECT_DIR/public/index.php" >/dev/null
php -l "$PROJECT_DIR/src/bootstrap.php" >/dev/null
chmod +x "$PROJECT_DIR/start.sh"
printf 'Instalação concluída. Inicie com: ./start.sh\n'
printf 'Acesse: http://127.0.0.1:8128 (ou http://IP-LOCAL:8128)\n'
