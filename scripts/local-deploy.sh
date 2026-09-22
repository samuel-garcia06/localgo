#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"
ENV_EXAMPLE_FILE="$ROOT_DIR/.env.example"
SQLITE_FILE="$ROOT_DIR/database/database.sqlite"
LOG_DIR="$ROOT_DIR/storage/logs"
LOCAL_PORT="8010"
VITE_PORT="5173"
REVERB_PORT="8081"
PUBLIC_HOST="127.0.0.1"
PUBLIC_URL=""
LOCAL_URL="http://localhost:${LOCAL_PORT}"

USE_SQLITE=0
RUN_SEED=1
RUN_BUILD=1
FRESH_MIGRATION=0
START_SERVICES=1

log() {
    printf '\n[%s] %s\n' "local-deploy" "$1"
}

fail() {
    printf '\n[%s] ERROR: %s\n' "local-deploy" "$1" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "No se encontro el comando '$1'. Instalalo y vuelve a ejecutar el script."
}

detect_local_ip() {
    local ip_address=""

    if command -v ip >/dev/null 2>&1; then
        ip_address="$(ip -4 route get 1.1.1.1 2>/dev/null | awk '{for (i = 1; i <= NF; i++) if ($i == "src") {print $(i + 1); exit}}')"
    fi

    if [[ -z "$ip_address" ]] && command -v hostname >/dev/null 2>&1; then
        ip_address="$(hostname -I 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' | grep -Ev '^(127|169\.254)\.' | head -n 1)"
    fi

    if [[ -z "$ip_address" ]]; then
        fail "No se pudo detectar la IP local. Comprueba que estas conectado a la red WiFi/LAN."
    fi

    printf '%s\n' "$ip_address"
}

start_background_process() {
    local label="$1"
    local log_file="$2"
    shift 2

    log "Arrancando ${label}"
    if command -v setsid >/dev/null 2>&1; then
        setsid -f "$@" >"$log_file" 2>&1 < /dev/null
        return
    fi

    nohup "$@" >"$log_file" 2>&1 < /dev/null &
    disown || true
}

wait_for_process() {
    local label="$1"
    local pattern="$2"
    local log_file="$3"
    local attempts="${4:-10}"

    for ((i = 1; i <= attempts; i++)); do
        if pgrep -f "$pattern" >/dev/null 2>&1; then
            log "${label} operativo"
            return 0
        fi

        sleep 1
    done

    if [[ -f "$log_file" ]]; then
        printf '\n[%s] Ultimas lineas de %s:\n' "local-deploy" "$log_file" >&2
        tail -n 20 "$log_file" >&2 || true
    fi

    fail "${label} no se ha quedado levantado."
}

wait_for_http() {
    local label="$1"
    local url="$2"
    local log_file="$3"
    local attempts="${4:-20}"

    for ((i = 1; i <= attempts; i++)); do
        if curl -fsS -o /dev/null "$url" >/dev/null 2>&1; then
            log "${label} operativo"
            return 0
        fi

        sleep 1
    done

    if [[ -f "$log_file" ]]; then
        printf '\n[%s] Ultimas lineas de %s:\n' "local-deploy" "$log_file" >&2
        tail -n 20 "$log_file" >&2 || true
    fi

    fail "${label} no responde en ${url}."
}

wait_for_tcp() {
    local label="$1"
    local host="$2"
    local port="$3"
    local log_file="$4"
    local attempts="${5:-20}"

    for ((i = 1; i <= attempts; i++)); do
        if (echo >"/dev/tcp/${host}/${port}") >/dev/null 2>&1; then
            log "${label} operativo"
            return 0
        fi

        sleep 1
    done

    if [[ -f "$log_file" ]]; then
        printf '\n[%s] Ultimas lineas de %s:\n' "local-deploy" "$log_file" >&2
        tail -n 20 "$log_file" >&2 || true
    fi

    fail "${label} no escucha en ${host}:${port}."
}

update_env_value() {
    local key="$1"
    local value="$2"

    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$key" "$value" >>"$ENV_FILE"
    fi
}

prepare_sqlite_env() {
    log "Configurando entorno local con SQLite"
    mkdir -p "$(dirname "$SQLITE_FILE")"
    touch "$SQLITE_FILE"

    update_env_value "APP_NAME" "localgo"

    update_env_value "DB_CONNECTION" "sqlite"
    update_env_value "DB_DATABASE" "$SQLITE_FILE"
    update_env_value "DB_HOST" "127.0.0.1"
    update_env_value "DB_PORT" "3306"
    update_env_value "DB_USERNAME" "root"
    update_env_value "DB_PASSWORD" ""

    update_env_value "CACHE_STORE" "file"
    update_env_value "SESSION_DRIVER" "file"
    update_env_value "QUEUE_CONNECTION" "sync"
}

prepare_network_env() {
    PUBLIC_URL="http://${PUBLIC_HOST}:${LOCAL_PORT}"

    log "Configurando URLs locales para red WiFi/LAN (${PUBLIC_HOST})"
    update_env_value "APP_URL" "$PUBLIC_URL"

    update_env_value "REVERB_SERVER_HOST" "0.0.0.0"
    update_env_value "REVERB_SERVER_PORT" "$REVERB_PORT"
    update_env_value "REVERB_HOST" "$PUBLIC_HOST"
    update_env_value "REVERB_PORT" "$REVERB_PORT"
    update_env_value "REVERB_SCHEME" "http"

    update_env_value "VITE_DEV_SERVER_HOST" "$PUBLIC_HOST"
    update_env_value "VITE_DEV_SERVER_URL" "http://${PUBLIC_HOST}:${VITE_PORT}"
    update_env_value "VITE_HMR_HOST" "$PUBLIC_HOST"
    update_env_value "VITE_HMR_PORT" "$VITE_PORT"
}

print_qr_if_available() {
    local url="$1"

    if command -v qrencode >/dev/null 2>&1; then
        printf '\nEscanea este QR desde el movil:\n'
        qrencode -t ANSIUTF8 "$url"
    else
        printf 'QR no disponible: instala "qrencode" si quieres generar el codigo automaticamente.\n'
    fi
}

print_firewall_hint() {
    printf 'Firewall: '

    if command -v ufw >/dev/null 2>&1 && ufw status 2>/dev/null | grep -qi 'Status: active'; then
        printf 'UFW parece estar activo. Si no puedes acceder desde el movil, permite los puertos %s, %s y %s.\n' "$LOCAL_PORT" "$VITE_PORT" "$REVERB_PORT"
        printf '          Ejemplo: sudo ufw allow %s/tcp\n' "$LOCAL_PORT"
        return
    fi

    if command -v firewall-cmd >/dev/null 2>&1 && firewall-cmd --state >/dev/null 2>&1; then
        printf 'firewalld parece estar activo. Si no puedes acceder desde el movil, permite los puertos %s, %s y %s.\n' "$LOCAL_PORT" "$VITE_PORT" "$REVERB_PORT"
        return
    fi

    printf 'no he detectado UFW/firewalld activo. Si no abre desde el movil, revisa el firewall del sistema/router.\n'
}

show_help() {
    cat <<'EOF'
Uso:
  bash scripts/local-deploy.sh [opciones]

Opciones:
  --sqlite     Fuerza configuracion local con SQLite.
  --fresh      Ejecuta migrate:fresh en lugar de migrate.
  --no-seed    No ejecuta seeders.
  --no-build   No compila assets con Vite.
  --no-start   Prepara el proyecto, pero no levanta servicios locales.
  --help       Muestra esta ayuda.

Ejemplos:
  bash scripts/local-deploy.sh
  bash scripts/local-deploy.sh --sqlite --fresh
  composer local-deploy
EOF
}

while (($# > 0)); do
    case "$1" in
        --sqlite)
            USE_SQLITE=1
            ;;
        --fresh)
            FRESH_MIGRATION=1
            ;;
        --no-seed)
            RUN_SEED=0
            ;;
        --no-build)
            RUN_BUILD=0
            ;;
        --no-start)
            START_SERVICES=0
            ;;
        --help|-h)
            show_help
            exit 0
            ;;
        *)
            fail "Opcion no reconocida: $1"
            ;;
    esac
    shift
done

cd "$ROOT_DIR"

require_command php
require_command composer
require_command npm
require_command nohup
require_command curl

PUBLIC_HOST="$(detect_local_ip)"
PUBLIC_URL="http://${PUBLIC_HOST}:${LOCAL_PORT}"

if [[ ! -f "$ENV_FILE" ]]; then
    log "Creando .env desde .env.example"
    cp "$ENV_EXAMPLE_FILE" "$ENV_FILE"
    USE_SQLITE=1
fi

if [[ $USE_SQLITE -eq 1 ]]; then
    prepare_sqlite_env
fi

prepare_network_env

log "Instalando dependencias PHP"
composer install

log "Instalando dependencias frontend"
if [[ -f "$ROOT_DIR/package-lock.json" ]]; then
    npm ci
else
    npm install
fi

log "Limpiando caches iniciales de Laravel"
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

if ! grep -q '^APP_KEY=base64:' "$ENV_FILE"; then
    log "Generando APP_KEY"
    php artisan key:generate
fi

log "Asegurando enlace de storage"
php artisan storage:link || true

if [[ $FRESH_MIGRATION -eq 1 ]]; then
    if [[ $RUN_SEED -eq 1 ]]; then
        log "Ejecutando migrate:fresh --seed"
        php artisan migrate:fresh --seed --force
    else
        log "Ejecutando migrate:fresh"
        php artisan migrate:fresh --force
    fi
else
    if [[ $RUN_SEED -eq 1 ]]; then
        log "Ejecutando migrate --seed"
        php artisan migrate --seed --force
    else
        log "Ejecutando migrate"
        php artisan migrate --force
    fi
fi

log "Limpiando caches finales de Laravel"
php artisan optimize:clear || true

if [[ $RUN_BUILD -eq 1 ]]; then
    log "Compilando assets"
    npm run build
fi

mkdir -p "$LOG_DIR"

if [[ $START_SERVICES -eq 1 ]]; then
    log "Deteniendo procesos locales previos"
    bash "$ROOT_DIR/scripts/local-stop.sh"

    start_background_process \
        "Laravel server" \
        "$LOG_DIR/local-artisan-serve.log" \
        php artisan serve --host=0.0.0.0 --port="$LOCAL_PORT"
    wait_for_http "Laravel server" "http://127.0.0.1:${LOCAL_PORT}" "$LOG_DIR/local-artisan-serve.log"

    start_background_process \
        "Queue listener" \
        "$LOG_DIR/local-queue-listen.log" \
        php artisan queue:listen --tries=1 --timeout=0
    wait_for_process "Queue listener" "php artisan queue:listen --tries=1 --timeout=0" "$LOG_DIR/local-queue-listen.log"

    start_background_process \
        "Laravel Pail" \
        "$LOG_DIR/local-pail.log" \
        php artisan pail --timeout=0
    wait_for_process "Laravel Pail" "php artisan pail --timeout=0" "$LOG_DIR/local-pail.log"

    start_background_process \
        "Laravel Reverb" \
        "$LOG_DIR/local-reverb.log" \
        php artisan reverb:start --host=0.0.0.0 --port="$REVERB_PORT"
    wait_for_tcp "Laravel Reverb" "127.0.0.1" "$REVERB_PORT" "$LOG_DIR/local-reverb.log"

    start_background_process \
        "Vite dev server" \
        "$LOG_DIR/local-vite.log" \
        npm run dev -- --host 0.0.0.0
    wait_for_http "Vite dev server" "http://127.0.0.1:${VITE_PORT}/@vite/client" "$LOG_DIR/local-vite.log"
fi

printf '\n====================================================\n'
printf 'LocalGo iniciado correctamente\n\n'
printf 'Accede desde este ordenador:\n'
printf '%s\n\n' "$LOCAL_URL"
printf 'Accede desde otros dispositivos:\n'
printf '%s\n\n' "$PUBLIC_URL"
printf 'Panel admin:\n'
printf '%s/admin\n\n' "$PUBLIC_URL"
printf 'Admin email: %s\n' "admin@localgo.local"
printf 'Admin password: %s\n' "password"
printf '\n'
print_firewall_hint
printf '\n'
print_qr_if_available "$PUBLIC_URL"
printf '====================================================\n'
printf '\n'
if [[ $START_SERVICES -eq 1 ]]; then
    printf 'Servicios arrancados en segundo plano.\n'
    printf 'Logs:\n'
    printf '  %s\n' "$LOG_DIR/local-artisan-serve.log"
    printf '  %s\n' "$LOG_DIR/local-queue-listen.log"
    printf '  %s\n' "$LOG_DIR/local-pail.log"
    printf '  %s\n' "$LOG_DIR/local-reverb.log"
    printf '  %s\n' "$LOG_DIR/local-vite.log"
    printf '\n'
    printf 'Para detenerlos:\n'
    printf '  bash scripts/local-stop.sh\n'
else
    printf 'Siguientes comandos recomendados:\n'
    printf '  php artisan serve --host=0.0.0.0 --port=%s\n' "$LOCAL_PORT"
    printf '  php artisan reverb:start --host=0.0.0.0 --port=%s\n' "$REVERB_PORT"
    printf '  php artisan queue:listen --tries=1 --timeout=0\n'
    printf '  npm run dev -- --host 0.0.0.0\n'
fi
printf '\n'
