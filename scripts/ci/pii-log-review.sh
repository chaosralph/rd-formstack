#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
LOG_DIR="${ROOT_DIR}/storage/logs"
OUT_DIR="${ROOT_DIR}/artifacts/qa"
OUT_FILE="${OUT_DIR}/pii-log-review.txt"

mkdir -p "${OUT_DIR}"

if [ ! -d "${LOG_DIR}" ]; then
  echo "PII log review skipped: missing ${LOG_DIR}" >"${OUT_FILE}"
  echo "PASS: no log directory present"
  exit 0
fi

# Keep patterns strict enough to avoid noise while still flagging obvious leaks.
PATTERN='(^|[^A-Za-z])(authorization:|bearer[[:space:]]+[A-Za-z0-9._-]{16,}|password=|passwd=|api[_-]?key=|secret=|token=|set-cookie:|cookie:|private[_-]?key)([^A-Za-z]|$)'

if grep -RIniE --include='*.log' "${PATTERN}" "${LOG_DIR}" >"${OUT_FILE}"; then
  echo "FAIL: potential sensitive data markers found in logs"
  exit 1
fi

echo "PASS: no obvious sensitive-data markers found in storage/logs" >"${OUT_FILE}"
echo "PASS: PII log review"
