#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARTIFACT_DIR="${ROOT_DIR}/artifacts/infra-access"
ACCESS_LOG="${ARTIFACT_DIR}/github-access-check-rdfa40.log"
PUSH_LOG="${ARTIFACT_DIR}/git-push-dry-run-rdfa40.log"
SUMMARY_LOG="${ARTIFACT_DIR}/rdfa40-unblock-summary.log"
TEST_REF="refs/heads/rdfa-40-access-check"

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

echo "[RDFA-40] Unblock Verification" | tee -a "${SUMMARY_LOG}"
echo "Timestamp: $(date -u +%Y-%m-%dT%H:%M:%SZ)" | tee -a "${SUMMARY_LOG}"

if bash "${ROOT_DIR}/scripts/check-github-access.sh" > "${ACCESS_LOG}" 2>&1; then
  pass "GitHub CLI access checks"
else
  fail "GitHub CLI access checks"
fi

if git -C "${ROOT_DIR}" push --dry-run origin "HEAD:${TEST_REF}" > "${PUSH_LOG}" 2>&1; then
  pass "Git push dry-run (${TEST_REF})"
else
  fail "Git push dry-run (${TEST_REF})"
fi

echo "---" | tee -a "${SUMMARY_LOG}"
echo "Summary: ${pass_count} PASS / ${fail_count} FAIL" | tee -a "${SUMMARY_LOG}"
echo "Logs:" | tee -a "${SUMMARY_LOG}"
echo "- ${ACCESS_LOG}" | tee -a "${SUMMARY_LOG}"
echo "- ${PUSH_LOG}" | tee -a "${SUMMARY_LOG}"

if [[ ${fail_count} -gt 0 ]]; then
  exit 1
fi

exit 0
