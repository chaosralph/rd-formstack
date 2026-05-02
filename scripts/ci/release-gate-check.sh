#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

echo "[RUN ] Release Gate: qa-gate strict"
bash scripts/ci/qa-gate.sh --strict=1

echo "[PASS] Release Gate checks passed."
echo "Reminder: Deployment remains blocked until explicit Owner/CTO approval."
