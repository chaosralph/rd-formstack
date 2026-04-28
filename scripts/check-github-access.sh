#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

pass_count=0
fail_count=0

pass() {
  echo "PASS: $1"
  pass_count=$((pass_count + 1))
}

fail() {
  echo "FAIL: $1"
  fail_count=$((fail_count + 1))
}

check_cmd() {
  local cmd="$1"
  local label="$2"

  if command -v "$cmd" >/dev/null 2>&1; then
    pass "$label"
  else
    fail "$label"
  fi
}

check_cmd gh "GitHub CLI installiert"

if command -v gh >/dev/null 2>&1; then
  if gh auth status >/dev/null 2>&1; then
    pass "GitHub CLI Auth aktiv"
  else
    fail "GitHub CLI Auth aktiv"
  fi

  if gh api user >/dev/null 2>&1; then
    pass "GitHub API Basiszugriff (user)"
  else
    fail "GitHub API Basiszugriff (user)"
  fi

  if gh api /user/installations >/dev/null 2>&1; then
    pass "GitHub App Installationen lesbar"
  else
    fail "GitHub App Installationen lesbar"
  fi
fi

echo "---"
echo "Ergebnis: ${pass_count} PASS / ${fail_count} FAIL"

if [[ $fail_count -gt 0 ]]; then
  exit 1
fi

exit 0
