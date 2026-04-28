#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARTIFACT_DIR="${ROOT_DIR}/artifacts/infra-access"
SUMMARY_LOG="${ARTIFACT_DIR}/rdfa42-revalidation-summary.log"
RUNTIME_LOG="${ARTIFACT_DIR}/runtime-check-rdfa42.log"
LINT_LOG="${ARTIFACT_DIR}/php-lint-rdfa42.log"
SMOKE_LOG="${ARTIFACT_DIR}/smoke-routes-rdfa42.log"
ACCESS_LOG="${ARTIFACT_DIR}/github-access-rdfa42.log"

mkdir -p "${ARTIFACT_DIR}"

pass_count=0
fail_count=0

pass() {
  echo "PASS: $1" | tee -a "${SUMMARY_LOG}"
  pass_count=$((pass_count + 1))
}

fail() {
  echo "FAIL: $1" | tee -a "${SUMMARY_LOG}"
  fail_count=$((fail_count + 1))
}

: > "${SUMMARY_LOG}"

echo "[RDFA-42] CTO Re-Validation" | tee -a "${SUMMARY_LOG}"
echo "Timestamp: $(date -u +%Y-%m-%dT%H:%M:%SZ)" | tee -a "${SUMMARY_LOG}"

if bash "${ROOT_DIR}/scripts/check-runtime.sh" > "${RUNTIME_LOG}" 2>&1; then
  pass "Runtime readiness"
else
  fail "Runtime readiness"
fi

if bash "${ROOT_DIR}/scripts/ci/php-lint.sh" > "${LINT_LOG}" 2>&1; then
  pass "PHP lint"
else
  fail "PHP lint"
fi

if bash "${ROOT_DIR}/scripts/ci/smoke-routes.sh" > "${SMOKE_LOG}" 2>&1; then
  pass "Smoke routes"
else
  fail "Smoke routes"
fi

if bash "${ROOT_DIR}/scripts/check-github-access.sh" > "${ACCESS_LOG}" 2>&1; then
  pass "GitHub access"
else
  fail "GitHub access"
fi

echo "---" | tee -a "${SUMMARY_LOG}"
echo "Summary: ${pass_count} PASS / ${fail_count} FAIL" | tee -a "${SUMMARY_LOG}"
echo "Logs:" | tee -a "${SUMMARY_LOG}"
echo "- ${RUNTIME_LOG}" | tee -a "${SUMMARY_LOG}"
echo "- ${LINT_LOG}" | tee -a "${SUMMARY_LOG}"
echo "- ${SMOKE_LOG}" | tee -a "${SUMMARY_LOG}"
echo "- ${ACCESS_LOG}" | tee -a "${SUMMARY_LOG}"

if [[ ${fail_count} -gt 0 ]]; then
  exit 1
fi

exit 0
