#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

HOST="127.0.0.1"
PORT="8096"
BASE_URL="http://${HOST}:${PORT}"
LOG_FILE="${ROOT_DIR}/storage/logs/ci-header-host-server.log"
TMP_HEADERS="$(mktemp -d)"
TMP_BODIES="$(mktemp -d)"

cleanup() {
  kill "${server_pid:-0}" 2>/dev/null || true
  rm -rf "${TMP_HEADERS}" "${TMP_BODIES}"
}
trap cleanup EXIT

mkdir -p "${ROOT_DIR}/storage/logs"
php -S "${HOST}:${PORT}" -t public >"${LOG_FILE}" 2>&1 &
server_pid=$!
sleep 1

assert_header_contains() {
  local header_file="$1"
  local header_name="$2"
  local expected_substr="$3"
  local actual

  actual="$(awk -F': ' -v key="${header_name}" 'tolower($1)==tolower(key){print $2}' "${header_file}" | tail -n1 | tr -d '\r')"
  if [ -z "${actual}" ]; then
    echo "Missing header ${header_name} in ${header_file}" >&2
    exit 1
  fi
  if [[ "${actual}" != *"${expected_substr}"* ]]; then
    echo "Header ${header_name} mismatch: expected to contain '${expected_substr}', got '${actual}'" >&2
    exit 1
  fi
}

assert_status_code() {
  local header_file="$1"
  local expected="$2"
  local actual
  actual="$(awk 'NR==1 {print $2}' "${header_file}")"
  if [ "${actual}" != "${expected}" ]; then
    echo "Unexpected status in ${header_file}: expected ${expected}, got ${actual}" >&2
    exit 1
  fi
}

fetch_route() {
  local route="$1"
  local header_file="$2"
  local body_file="$3"
  curl -fsS -D "${header_file}" -o "${body_file}" "${BASE_URL}${route}" >/dev/null
}

validate_security_headers() {
  local route="$1"
  local suffix="$2"
  local header_file="${TMP_HEADERS}/headers-${suffix}.txt"
  local body_file="${TMP_BODIES}/body-${suffix}.txt"

  fetch_route "${route}" "${header_file}" "${body_file}"
  assert_status_code "${header_file}" "200"
  assert_header_contains "${header_file}" "X-Content-Type-Options" "nosniff"
  assert_header_contains "${header_file}" "X-Frame-Options" "DENY"
  assert_header_contains "${header_file}" "Referrer-Policy" "strict-origin-when-cross-origin"
  assert_header_contains "${header_file}" "Content-Security-Policy" "default-src 'self'"
  assert_header_contains "${header_file}" "Content-Security-Policy" "frame-ancestors 'none'"
  assert_header_contains "${header_file}" "Content-Security-Policy" "form-action 'self'"
}

validate_host_regression() {
  local route="$1"
  local suffix="$2"
  local header_file="${TMP_HEADERS}/headers-host-${suffix}.txt"
  local body_file="${TMP_BODIES}/body-host-${suffix}.txt"

  curl -fsS \
    -H "Host: evil.example" \
    -H "X-Forwarded-Host: evil.example" \
    -D "${header_file}" \
    -o "${body_file}" \
    "${BASE_URL}${route}" >/dev/null

  assert_status_code "${header_file}" "200"
  assert_header_contains "${header_file}" "Content-Security-Policy" "form-action 'self'"

  if grep -qi '^Location: .*evil\.example' "${header_file}"; then
    echo "Host regression detected in Location header for ${route}" >&2
    exit 1
  fi
  if grep -qi 'evil\.example' "${body_file}"; then
    echo "Host regression detected in response body for ${route}" >&2
    exit 1
  fi
}

validate_security_headers "/" "home"
validate_security_headers "/kontakt" "kontakt"

sitemap_headers="${TMP_HEADERS}/headers-sitemap.txt"
sitemap_body="${TMP_BODIES}/body-sitemap.txt"
fetch_route "/sitemap.xml" "${sitemap_headers}" "${sitemap_body}"
assert_status_code "${sitemap_headers}" "200"
assert_header_contains "${sitemap_headers}" "Content-Type" "application/xml"

validate_host_regression "/" "home"
validate_host_regression "/kontakt" "kontakt"

echo "Header/Host regression checks passed."
