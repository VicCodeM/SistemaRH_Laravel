#!/bin/bash
# Script de deploy automático — se llama desde el webhook de GitHub

set -e

APP_DIR="/home/victor/sistemarh"
REPO="git@github.com:VicCodeM/SistemaRH_Laravel.git"
LOG="$APP_DIR/storage/logs/deploy.log"
TMP="/tmp/sistemarh_deploy"

# PATH completo para que php, git y vite sean encontrables
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export HOME="/home/victor"
export GIT_SSH_COMMAND="ssh -i /home/victor/.ssh/id_ed25519_github -o StrictHostKeyChecking=no"

echo "==============================" >> "$LOG"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy iniciado" >> "$LOG"

# Clonar main en temporal (forzar borrado aunque sea de otro usuario)
sudo rm -rf "$TMP" 2>/dev/null || rm -rf "$TMP" 2>/dev/null || true
git clone --branch main --depth 1 "$REPO" "$TMP" >> "$LOG" 2>&1

# Detectar si cambiaron dependencias antes de sobrescribir
COMPOSER_CHANGED=false
NPM_CHANGED=false

if ! diff -q "$TMP/composer.lock" "$APP_DIR/composer.lock" > /dev/null 2>&1; then
    COMPOSER_CHANGED=true
fi

if ! diff -q "$TMP/package.json" "$APP_DIR/package.json" > /dev/null 2>&1; then
    NPM_CHANGED=true
fi

# Sincronizar código (preservar .env, vendor, storage, build)
rsync -a \
    --exclude='.env' \
    --exclude='vendor/' \
    --exclude='storage/app/' \
    --exclude='storage/logs/' \
    --exclude='node_modules/' \
    --exclude='public/build/' \
    "$TMP/" "$APP_DIR/" >> "$LOG" 2>&1 || true

# Dependencias PHP si cambiaron
if [ "$COMPOSER_CHANGED" = true ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] composer.lock cambió — corriendo composer install" >> "$LOG"
    cd "$APP_DIR" && composer install --no-interaction --no-dev --optimize-autoloader >> "$LOG" 2>&1
fi

# Dependencias JS si cambiaron
if [ "$NPM_CHANGED" = true ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] package.json cambió — corriendo npm install" >> "$LOG"
    cd "$APP_DIR" && npm install >> "$LOG" 2>&1
fi

# Siempre recompilar assets (CSS/JS puede cambiar sin cambiar package.json)
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Compilando assets" >> "$LOG"
cd "$APP_DIR" && "$APP_DIR/node_modules/.bin/vite" build >> "$LOG" 2>&1

# Migraciones
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Migraciones" >> "$LOG"
cd "$APP_DIR" && php artisan migrate --force >> "$LOG" 2>&1

# Limpiar cache
cd "$APP_DIR" && php artisan view:clear >> "$LOG" 2>&1
cd "$APP_DIR" && php artisan config:clear >> "$LOG" 2>&1
cd "$APP_DIR" && php artisan cache:clear >> "$LOG" 2>&1
cd "$APP_DIR" && php artisan route:clear >> "$LOG" 2>&1

# Limpiar temporal
sudo rm -rf "$TMP" 2>/dev/null || rm -rf "$TMP" 2>/dev/null || true

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy completado OK" >> "$LOG"
echo "==============================" >> "$LOG"
