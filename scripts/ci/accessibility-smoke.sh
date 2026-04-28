#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

HOST="127.0.0.1"
PORT="8094"
BASE_URL="http://${HOST}:${PORT}"
LOG_FILE="${ROOT_DIR}/storage/logs/ci-a11y-smoke-server.log"
TMP_HOME="$(mktemp)"
TMP_CONTACT="$(mktemp)"
trap 'rm -f "${TMP_HOME}" "${TMP_CONTACT}"' EXIT

mkdir -p "${ROOT_DIR}/storage/logs"
php -S "${HOST}:${PORT}" -t public >"${LOG_FILE}" 2>&1 &
server_pid=$!
trap 'kill "${server_pid}" 2>/dev/null || true; rm -f "${TMP_HOME}" "${TMP_CONTACT}"' EXIT
sleep 1

curl -fsS "${BASE_URL}/" >"${TMP_HOME}"
curl -fsS "${BASE_URL}/kontakt" >"${TMP_CONTACT}"

grep -q '<a class="skip-link" href="#main">' "${TMP_HOME}"
grep -q '<main id="main">' "${TMP_HOME}"
grep -q '<header class="site-header"' "${TMP_HOME}"
grep -q '<nav class="main-nav"' "${TMP_HOME}"
grep -q '<footer' "${TMP_HOME}"

for field in name company email phone message; do
  grep -q "<label for=\"${field}\"" "${TMP_CONTACT}"
  grep -q "id=\"${field}\"" "${TMP_CONTACT}"
done

grep -q ':focus-visible' public/assets/css/app.css

echo 'Accessibility smoke passed: skip-link, landmarks, labels, focus styles present.'
