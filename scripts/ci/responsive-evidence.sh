#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

HOST="127.0.0.1"
PORT="8092"
BASE_URL="http://${HOST}:${PORT}"
OUT_DIR="${ROOT_DIR}/artifacts/qa/responsive"
LOG_DIR="${ROOT_DIR}/storage/logs"
SERVER_LOG="${LOG_DIR}/ci-responsive-server.log"
REPORT_FILE="${OUT_DIR}/report.txt"

mkdir -p "${OUT_DIR}" "${LOG_DIR}"

if ! command -v php >/dev/null 2>&1; then
  echo "php binary not found" >&2
  exit 1
fi

if ! command -v npx >/dev/null 2>&1; then
  echo "npx binary not found" >&2
  exit 1
fi

php -S "${HOST}:${PORT}" -t public >"${SERVER_LOG}" 2>&1 &
server_pid=$!
trap 'kill "${server_pid}" 2>/dev/null || true' EXIT

ready=0
for _ in $(seq 1 30); do
  if curl -s -o /dev/null "${BASE_URL}/"; then
    ready=1
    break
  fi
  sleep 1
done

if [ "${ready}" -ne 1 ]; then
  echo "PHP test server did not become ready on ${BASE_URL}" >&2
  exit 1
fi

for route in "/" "/kontakt"; do
  status_code="$(curl -s -o /dev/null -w '%{http_code}' "${BASE_URL}${route}")"
  if [ "${status_code}" != "200" ]; then
    echo "Route ${route} returned ${status_code}" >&2
    exit 1
  fi
done

npx --yes playwright screenshot --browser=chromium --viewport-size="360,800" --full-page "${BASE_URL}/" "${OUT_DIR}/home-360x800.png"
npx --yes playwright screenshot --browser=chromium --viewport-size="768,1024" --full-page "${BASE_URL}/" "${OUT_DIR}/home-768x1024.png"
npx --yes playwright screenshot --browser=chromium --viewport-size="1280,800" --full-page "${BASE_URL}/" "${OUT_DIR}/home-1280x800.png"
npx --yes playwright screenshot --browser=chromium --viewport-size="360,800" --full-page "${BASE_URL}/kontakt" "${OUT_DIR}/kontakt-360x800.png"

{
  echo "RDFA-29 Responsive Evidence"
  echo "generated_utc=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  echo "base_url=${BASE_URL}"
  echo "screenshots="
  echo "- home-360x800.png"
  echo "- home-768x1024.png"
  echo "- home-1280x800.png"
  echo "- kontakt-360x800.png"
} >"${REPORT_FILE}"

echo "Responsive evidence created in ${OUT_DIR}"
