#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_DIR="${ROOT_DIR}"

missing=0

echo "[RDFA-22] Runtime Readiness Check"

echo "- Checking Git trusted directory"
if git config --global --get-all safe.directory | grep -Fxq "${REPO_DIR}"; then
  echo "  OK: safe.directory contains ${REPO_DIR}"
else
  echo "  FAIL: safe.directory missing ${REPO_DIR}"
  echo "  Hint: git config --global --add safe.directory ${REPO_DIR}"
  missing=1
fi

echo "- Checking PHP"
if command -v php >/dev/null 2>&1; then
  php -v | head -n 1
else
  echo "  FAIL: php not found"
  missing=1
fi

echo "- Checking Composer"
if command -v composer >/dev/null 2>&1; then
  composer --version
else
  echo "  FAIL: composer not found"
  missing=1
fi

echo "- Checking write permissions"
if touch "${REPO_DIR}/.runtime_write_test" 2>/dev/null; then
  rm -f "${REPO_DIR}/.runtime_write_test"
  echo "  OK: repository is writable"
else
  echo "  FAIL: repository is not writable"
  missing=1
fi

if [ "$missing" -eq 0 ]; then
  echo "\nResult: READY"
  exit 0
fi

echo "\nResult: BLOCKED"
exit 1
