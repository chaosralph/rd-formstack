#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

STRICT_MODE="${RD_QA_STRICT:-1}"
RUN_RESPONSIVE="${RD_QA_RUN_RESPONSIVE:-1}"
RUN_A11Y_SMOKE="${RD_QA_RUN_A11Y_SMOKE:-1}"
OUT_DIR="${ROOT_DIR}/artifacts/qa/gate"
REPORT_FILE="${OUT_DIR}/report.txt"

mkdir -p "${OUT_DIR}"

FAILED=0
WARNINGS=0

pass() {
  printf '[PASS] %s\n' "$1"
}

fail() {
  printf '[FAIL] %s\n' "$1"
  FAILED=1
}

warn() {
  printf '[WARN] %s\n' "$1"
  WARNINGS=$((WARNINGS + 1))
}

run_check() {
  local label="$1"
  shift
  printf '[RUN ] %s\n' "${label}"
  if "$@"; then
    pass "${label}"
  else
    fail "${label}"
  fi
}

run_check "Runtime Check" composer run check:runtime
run_check "PHP Lint" bash scripts/ci/php-lint.sh
run_check "Route Smoke" bash scripts/ci/smoke-routes.sh

run_check "Security Smoke: CSRF Hook" bash -c "grep -RIn --exclude-dir=.git 'Csrf::validate' src public >/dev/null"
run_check "Security Smoke: Prepared Statements" bash -c "grep -RIn --exclude-dir=.git -E 'prepare\\(|execute\\(' src >/dev/null"
run_check "Security Smoke: Output Escaping" bash -c "grep -RIn --exclude-dir=.git -E 'htmlspecialchars\\(|function e\\(' public/index.php >/dev/null"
run_check "Security Smoke: Dangerous Calls" bash -c "! find config public src -type f -name '*.php' -print0 | xargs -0 grep -nE '(eval\\(|shell_exec\\(|exec\\(|system\\(|passthru\\(|proc_open\\()' >/dev/null"

if [ "${RUN_RESPONSIVE}" = "1" ]; then
  run_check "Responsive Evidence" bash scripts/ci/responsive-evidence.sh
else
  warn 'Responsive Evidence wurde per RD_QA_RUN_RESPONSIVE=0 uebersprungen.'
fi

if [ "${RUN_A11Y_SMOKE}" = "1" ]; then
  run_check "Accessibility Smoke" bash scripts/ci/accessibility-smoke.sh
else
  warn 'Accessibility Smoke wurde per RD_QA_RUN_A11Y_SMOKE=0 uebersprungen.'
  if [ "${STRICT_MODE}" = "1" ]; then
    fail 'Strict Mode aktiv: Accessibility Smoke darf nicht fehlen.'
  fi
fi

{
  echo "RDFA-34 QA Gate"
  echo "generated_utc=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  echo "strict_mode=${STRICT_MODE}"
  echo "run_responsive=${RUN_RESPONSIVE}"
  echo "run_a11y_smoke=${RUN_A11Y_SMOKE}"
  echo "warnings=${WARNINGS}"
  echo "failed=${FAILED}"
} >"${REPORT_FILE}"

if [ "${FAILED}" -ne 0 ]; then
  echo "QA Gate Result: FAIL"
  exit 1
fi

echo "QA Gate Result: PASS"
if [ "${WARNINGS}" -gt 0 ]; then
  echo "Hinweis: ${WARNINGS} Warnungen vorhanden."
fi
