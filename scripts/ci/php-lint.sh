#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

if ! command -v php >/dev/null 2>&1; then
  echo "php binary not found" >&2
  exit 1
fi

mapfile -t files < <(find config public src -type f -name '*.php' | sort)

if [ "${#files[@]}" -eq 0 ]; then
  echo "No PHP files found for linting."
  exit 1
fi

for file in "${files[@]}"; do
  php -l "${file}" >/dev/null
  echo "lint OK: ${file}"
done

echo "Linted ${#files[@]} PHP files successfully."
