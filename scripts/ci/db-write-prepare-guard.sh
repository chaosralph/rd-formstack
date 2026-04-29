#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

WRITE_QUERY_HITS="$(grep -RInE --include='*.php' -- "->query\s*\(.*\b(INSERT|UPDATE|DELETE|ALTER|DROP|TRUNCATE|REPLACE)\b" src config public templates || true)"
WRITE_EXEC_HITS="$(grep -RInE --include='*.php' -- "->exec\s*\(.*" src config public templates || true)"

if [ -n "${WRITE_QUERY_HITS}" ] || [ -n "${WRITE_EXEC_HITS}" ]; then
  echo "FAIL: detected direct DB write patterns without explicit prepare() path."
  if [ -n "${WRITE_QUERY_HITS}" ]; then
    echo "--- query write hits ---"
    echo "${WRITE_QUERY_HITS}"
  fi
  if [ -n "${WRITE_EXEC_HITS}" ]; then
    echo "--- exec hits ---"
    echo "${WRITE_EXEC_HITS}"
  fi
  exit 1
fi

echo "OK: no direct DB write patterns via query/exec detected."
