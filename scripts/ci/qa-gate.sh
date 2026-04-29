#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

STRICT_MODE="${RD_QA_STRICT:-1}"
RUN_RESPONSIVE="${RD_QA_RUN_RESPONSIVE:-0}"
RUN_A11Y_SMOKE="${RD_QA_RUN_A11Y_SMOKE:-1}"
OUT_DIR="${ROOT_DIR}/artifacts/qa/gate"
REPORT_FILE="${OUT_DIR}/report.txt"
EVIDENCE_DIR="${OUT_DIR}/evidence"

mkdir -p "${OUT_DIR}" "${EVIDENCE_DIR}"

WARNINGS=0
FAILED_CHECK=""
REPORT_STATUS="FAIL"
STARTED_UTC="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
REQUIRED_CHECKS=()
OPTIONAL_CHECKS=()

write_report() {
  local finished_utc
  finished_utc="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  {
    echo "RDFA-46 QA DevOps Gate"
    echo "started_utc=${STARTED_UTC}"
    echo "finished_utc=${finished_utc}"
    echo "status=${REPORT_STATUS}"
    echo "strict_mode=${STRICT_MODE}"
    echo "run_responsive=${RUN_RESPONSIVE}"
    echo "run_a11y_smoke=${RUN_A11Y_SMOKE}"
    echo "warnings=${WARNINGS}"
    echo "failed_check=${FAILED_CHECK:-none}"
    echo "required_checks="
    for check in "${REQUIRED_CHECKS[@]}"; do
      echo "- ${check}"
    done
    echo "optional_checks="
    for check in "${OPTIONAL_CHECKS[@]}"; do
      echo "- ${check}"
    done
  } >"${REPORT_FILE}"
}
trap write_report EXIT

warn() {
  printf '[WARN] %s\n' "$1"
  WARNINGS=$((WARNINGS + 1))
}

run_required_check() {
  local label="$1"
  local evidence_file="$2"
  shift
  shift
  local log_file="${ROOT_DIR}/${evidence_file}"
  mkdir -p "$(dirname "${log_file}")"

  REQUIRED_CHECKS+=("${label}|${evidence_file}|${log_file}")
  printf '[RUN ] %s\n' "${label}"
  if "$@" >"${log_file}" 2>&1; then
    printf '[PASS] %s\n' "${label}"
    return 0
  fi

  printf '[FAIL] %s (siehe %s)\n' "${label}" "${log_file}"
  FAILED_CHECK="${label}"
  exit 1
}

run_required_check "PHP Lint" "artifacts/qa/gate/evidence/php-lint.log" bash scripts/ci/php-lint.sh
run_required_check "Route Smoke" "artifacts/qa/gate/evidence/route-smoke.log" bash scripts/ci/smoke-routes.sh

if [ "${RUN_A11Y_SMOKE}" = "1" ]; then
  run_required_check "Accessibility Smoke" "artifacts/qa/gate/evidence/accessibility-smoke.log" bash scripts/ci/accessibility-smoke.sh
else
  REQUIRED_CHECKS+=("Accessibility Smoke|skipped|n/a")
  warn 'Accessibility Smoke wurde per RD_QA_RUN_A11Y_SMOKE=0 uebersprungen.'
  if [ "${STRICT_MODE}" = "1" ]; then
    FAILED_CHECK="Accessibility Smoke (skipped in strict mode)"
    exit 1
  fi
fi

if [ "${RUN_RESPONSIVE}" = "1" ]; then
  OPTIONAL_CHECKS+=("Responsive Evidence|artifacts/qa/responsive/report.txt|artifacts/qa/responsive")
  printf '[RUN ] %s\n' "Responsive Evidence"
  if bash scripts/ci/responsive-evidence.sh >"${EVIDENCE_DIR}/responsive-evidence.log" 2>&1; then
    printf '[PASS] %s\n' "Responsive Evidence"
  else
    printf '[WARN] %s (siehe %s)\n' "Responsive Evidence" "${EVIDENCE_DIR}/responsive-evidence.log"
    WARNINGS=$((WARNINGS + 1))
  fi
else
  OPTIONAL_CHECKS+=("Responsive Evidence|skipped|n/a")
fi

REPORT_STATUS="PASS"

echo "QA Gate Result: PASS"
if [ "${WARNINGS}" -gt 0 ]; then
  echo "Hinweis: ${WARNINGS} Warnungen vorhanden."
fi
