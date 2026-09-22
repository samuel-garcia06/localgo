#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

log() {
    printf '[local-stop] %s\n' "$1"
}

stop_pattern() {
    local label="$1"
    local pattern="$2"

    if pgrep -f "$pattern" >/dev/null 2>&1; then
        log "Deteniendo ${label}"
        pkill -f "$pattern" || true
    else
        log "No hay procesos activos para ${label}"
    fi
}

cd "$PROJECT_ROOT"

stop_pattern "Laravel server" "php artisan serve"
stop_pattern "Laravel Reverb" "php artisan reverb:start"
stop_pattern "Queue listener" "php artisan queue:listen"
stop_pattern "Laravel Pail" "php artisan pail"
stop_pattern "Vite dev server" "vite"
stop_pattern "Composer dev runner" "concurrently.*php artisan serve.*npm run dev"

log "Parada local completada"
