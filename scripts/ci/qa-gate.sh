#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"
export LC_ALL=C
export LANG=C
export TZ=UTC

usage() {
  cat <<'USAGE'
Usage: bash scripts/ci/qa-gate.sh [options]

Options:
  --strict=0|1              Enable strict mode (default: env RD_QA_STRICT or 1)
  --run-a11y-smoke=0|1      Run accessibility smoke (default: env RD_QA_RUN_A11Y_SMOKE or 1)
  --run-responsive=0|1      Run responsive evidence (default: env RD_QA_RUN_RESPONSIVE or 0)
  -h, --help                Show this help
USAGE
}

STRICT_MODE="${RD_QA_STRICT:-1}"
RUN_RESPONSIVE="${RD_QA_RUN_RESPONSIVE:-0}"
RUN_A11Y_SMOKE="${RD_QA_RUN_A11Y_SMOKE:-1}"

for arg in "$@"; do
  case "$arg" in
    --strict=*)
      STRICT_MODE="${arg#*=}"
      ;;
    --run-a11y-smoke=*)
      RUN_A11Y_SMOKE="${arg#*=}"
      ;;
    --run-responsive=*)
      RUN_RESPONSIVE="${arg#*=}"
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "[FAIL] Unknown argument: ${arg}" >&2
      usage >&2
      exit 2
      ;;
  esac
done

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
CHECK_RESULTS=()

write_report() {
  local finished_utc
  finished_utc="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  {
    echo "RDFA-48 QA DevOps Gate"
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
    echo "check_results="
    for result in "${CHECK_RESULTS[@]}"; do
      echo "- ${result}"
    done
  } >"${REPORT_FILE}"
}
trap write_report EXIT

die_with_error() {
  local message="$1"
  FAILED_CHECK="$message"
  echo "[FAIL] ${message}" >&2
  exit 1
}

validate_binary_flag() {
  local name="$1"
  local value="$2"
  if [ "$value" != "0" ] && [ "$value" != "1" ]; then
    die_with_error "Invalid flag ${name}=${value} (allowed: 0|1)"
  fi
}

warn() {
  printf '[WARN] %s\n' "$1"
  WARNINGS=$((WARNINGS + 1))
}

run_required_check() {
  local label="$1"
  local evidence_file="$2"
  shift 2

  local log_file="${ROOT_DIR}/${evidence_file}"
  mkdir -p "$(dirname "${log_file}")"

  REQUIRED_CHECKS+=("${label}|${evidence_file}|${log_file}")
  printf '[RUN ] %s\n' "${label}"
  if "$@" >"${log_file}" 2>&1; then
    CHECK_RESULTS+=("${label}|PASS|${evidence_file}")
    printf '[PASS] %s\n' "${label}"
    return 0
  fi

  CHECK_RESULTS+=("${label}|FAIL|${evidence_file}")
  printf '[FAIL] %s (see %s)\n' "${label}" "${log_file}" >&2
  FAILED_CHECK="${label}"
  exit 1
}

check_required_script() {
  local path="$1"
  if [ ! -f "${path}" ]; then
    die_with_error "Required script missing: ${path}"
  fi
  if [ ! -x "${path}" ]; then
    die_with_error "Required script is not executable: ${path}"
  fi
}

validate_binary_flag "RD_QA_STRICT" "${STRICT_MODE}"
validate_binary_flag "RD_QA_RUN_A11Y_SMOKE" "${RUN_A11Y_SMOKE}"
validate_binary_flag "RD_QA_RUN_RESPONSIVE" "${RUN_RESPONSIVE}"
check_required_script "scripts/ci/php-lint.sh"
check_required_script "scripts/ci/db-write-prepare-guard.sh"
check_required_script "scripts/ci/smoke-routes.sh"
check_required_script "scripts/ci/header-host-regression.sh"
check_required_script "scripts/ci/accessibility-smoke.sh"
if [ "${RUN_RESPONSIVE}" = "1" ]; then
  check_required_script "scripts/ci/responsive-evidence.sh"
fi

run_required_check "PHP Lint" "artifacts/qa/gate/evidence/php-lint.log" bash scripts/ci/php-lint.sh
run_required_check "DB Write Prepare Guard" "artifacts/qa/gate/evidence/db-write-prepare-guard.log" bash scripts/ci/db-write-prepare-guard.sh
run_required_check "Route Smoke" "artifacts/qa/gate/evidence/route-smoke.log" bash scripts/ci/smoke-routes.sh
run_required_check "Header Host Regression" "artifacts/qa/gate/evidence/header-host-regression.log" bash scripts/ci/header-host-regression.sh

if [ "${RUN_A11Y_SMOKE}" = "1" ]; then
  run_required_check "Accessibility Smoke" "artifacts/qa/gate/evidence/accessibility-smoke.log" bash scripts/ci/accessibility-smoke.sh
else
  REQUIRED_CHECKS+=("Accessibility Smoke|skipped|n/a")
  CHECK_RESULTS+=("Accessibility Smoke|SKIP|n/a")
  warn 'Accessibility Smoke skipped via RD_QA_RUN_A11Y_SMOKE=0.'
  if [ "${STRICT_MODE}" = "1" ]; then
    FAILED_CHECK="Accessibility Smoke (skipped in strict mode)"
    exit 1
  fi
fi

if [ "${RUN_RESPONSIVE}" = "1" ]; then
  OPTIONAL_CHECKS+=("Responsive Evidence|artifacts/qa/gate/evidence/responsive-evidence.log|${EVIDENCE_DIR}/responsive-evidence.log")
  printf '[RUN ] %s\n' "Responsive Evidence"
  if bash scripts/ci/responsive-evidence.sh >"${EVIDENCE_DIR}/responsive-evidence.log" 2>&1; then
    CHECK_RESULTS+=("Responsive Evidence|PASS|artifacts/qa/gate/evidence/responsive-evidence.log")
    printf '[PASS] %s\n' "Responsive Evidence"
  else
    CHECK_RESULTS+=("Responsive Evidence|WARN|artifacts/qa/gate/evidence/responsive-evidence.log")
    printf '[WARN] %s (see %s)\n' "Responsive Evidence" "${EVIDENCE_DIR}/responsive-evidence.log"
    WARNINGS=$((WARNINGS + 1))
  fi
else
  OPTIONAL_CHECKS+=("Responsive Evidence|skipped|n/a")
  CHECK_RESULTS+=("Responsive Evidence|SKIP|n/a")
fi

REPORT_STATUS="PASS"

echo "QA Gate Result: PASS"
if [ "${WARNINGS}" -gt 0 ]; then
  echo "Note: ${WARNINGS} warning(s) present."
fi
