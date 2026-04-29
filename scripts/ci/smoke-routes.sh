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

routes=("/" "/leistungen" "/referenzen" "/kontakt" "/login" "/dms" "/sitemap.xml")
for route in "${routes[@]}"; do
  status_code="$(curl -s -o /dev/null -w '%{http_code}' "${BASE_URL}${route}")"
  if [ "${status_code}" != "200" ]; then
    echo "Route ${route} returned ${status_code}" >&2
    exit 1
  fi

  if [ "${route}" = "/sitemap.xml" ]; then
    content_type="$(curl -s -o /dev/null -D - "${BASE_URL}${route}" | tr -d '\r' | awk -F': ' 'tolower($1)=="content-type"{print tolower($2)}' | tail -n1)"
    if [[ "${content_type}" != *"application/xml"* ]]; then
      echo "Route ${route} returned unexpected content-type: ${content_type}" >&2
      exit 1
    fi
  else
    robots_meta="$(curl -s "${BASE_URL}${route}" | tr '\n' ' ' | sed -n 's/.*<meta name="robots" content="\([^"]*\)".*/\1/p')"
    if [ -z "${robots_meta}" ]; then
      echo "Route ${route} missing robots meta tag" >&2
      exit 1
    fi

    if [ "${route}" = "/login" ] || [ "${route}" = "/dms" ]; then
      if [[ "${robots_meta}" != noindex* ]]; then
        echo "Route ${route} expected noindex robots meta, got: ${robots_meta}" >&2
        exit 1
      fi
    else
      if [[ "${robots_meta}" != index* ]]; then
        echo "Route ${route} expected index robots meta, got: ${robots_meta}" >&2
        exit 1
      fi
    fi
  fi

  echo "smoke OK: ${route} (${status_code})"
done

echo "Smoke routes check passed for ${#routes[@]} routes."
