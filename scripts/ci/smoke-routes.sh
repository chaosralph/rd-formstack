#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

HOST="127.0.0.1"
PORT="8091"
BASE_URL="http://${HOST}:${PORT}"
LOG_FILE="${ROOT_DIR}/storage/logs/ci-smoke-server.log"

mkdir -p "${ROOT_DIR}/storage/logs"
php -S "${HOST}:${PORT}" -t public >"${LOG_FILE}" 2>&1 &
server_pid=$!
trap 'kill "${server_pid}" 2>/dev/null || true' EXIT
sleep 1

routes=("/" "/leistungen" "/referenzen" "/kontakt" "/login" "/dms")
for route in "${routes[@]}"; do
  status_code="$(curl -s -o /dev/null -w '%{http_code}' "${BASE_URL}${route}")"
  if [ "${status_code}" != "200" ]; then
    echo "Route ${route} returned ${status_code}" >&2
    exit 1
  fi
  echo "smoke OK: ${route} (${status_code})"
done

echo "Smoke routes check passed for ${#routes[@]} routes."
