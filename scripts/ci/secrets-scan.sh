#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

TARGETS=("src" "public" "templates" "config" "scripts")
PATTERN='(api[_-]?key|secret[_-]?key|private[_-]?key|aws_access_key_id|aws_secret_access_key|BEGIN (RSA|EC|OPENSSH) PRIVATE KEY|xox[baprs]-|ghp_[A-Za-z0-9]{20,}|glpat-[A-Za-z0-9_-]{20,}|AIza[0-9A-Za-z\-_]{35})'

HITS="$(grep -RInE --exclude-dir='.git' --exclude='*.md' --exclude='*.log' --exclude='secrets-scan.sh' -- "${PATTERN}" "${TARGETS[@]}" || true)"

if [ -n "${HITS}" ]; then
  echo "FAIL: potential hardcoded secret patterns detected."
  echo "${HITS}"
  exit 1
fi

echo "OK: no potential hardcoded secret patterns detected."
