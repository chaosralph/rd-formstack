#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

HOST="127.0.0.1"
PORT="8096"
BASE_URL="http://${HOST}:${PORT}"
OUT_DIR="${ROOT_DIR}/artifacts/qa/a11y-keyboard"
LOG_DIR="${ROOT_DIR}/storage/logs"
SERVER_LOG="${LOG_DIR}/ci-a11y-keyboard-server.log"
PW_LOG="${OUT_DIR}/playwright-output.log"
PW_JSON="${OUT_DIR}/report.json"

mkdir -p "${OUT_DIR}" "${LOG_DIR}"

php -S "${HOST}:${PORT}" -t public >"${SERVER_LOG}" 2>&1 &
server_pid=$!
trap 'kill "${server_pid}" 2>/dev/null || true' EXIT

ready=0
for _ in $(seq 1 30); do
  if curl -s -o /dev/null "${BASE_URL}/"; then
    ready=1
    break
  fi
  sleep 1
done

if [ "${ready}" -ne 1 ]; then
  echo "PHP test server did not become ready on ${BASE_URL}" >&2
  exit 1
fi

PW_SPEC="$(mktemp "${OUT_DIR}/pw-keyboard-XXXXXX.spec.js")"
trap 'kill "${server_pid}" 2>/dev/null || true; rm -f "${PW_SPEC}"' EXIT

cat >"${PW_SPEC}" <<'EOF'
const { test, expect } = require('@playwright/test');
const fs = require('fs');

const checks = [];
const screenshots = [];
const addCheck = (name, pass, details) => {
  checks.push({ name, pass, details });
  expect(pass, `${name}: ${details}`).toBeTruthy();
};

test('keyboard-focus smoke', async ({ page }) => {
  const baseUrl = process.env.BASE_URL;
  const outDir = process.env.OUT_DIR;

  await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

  await page.keyboard.press('Tab');
  const skipVisible = await page.locator('.skip-link').evaluate((el) => {
    const r = el.getBoundingClientRect();
    return r.top >= 0 && r.top < 50;
  });
  addCheck('skip-link visible on first Tab', skipVisible, 'Skip-Link wird beim ersten Tab sichtbar');
  await page.screenshot({ path: `${outDir}/01-skip-link-focus.png`, fullPage: true });
  screenshots.push('01-skip-link-focus.png');

  await page.keyboard.press('Enter');
  await page.waitForTimeout(150);
  addCheck('skip-link jump to #main', page.url().includes('#main'), `URL: ${page.url()}`);
  await page.screenshot({ path: `${outDir}/02-after-skip-enter.png`, fullPage: true });
  screenshots.push('02-after-skip-enter.png');

  await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
  const navCta = page.locator('nav.main-nav a.btn.btn-primary.btn-small');
  await navCta.focus();
  const navFocused = await navCta.evaluate((el) => document.activeElement === el);
  addCheck('nav CTA focusable', navFocused, 'Nav-CTA ist fokussierbar');
  await page.screenshot({ path: `${outDir}/03-nav-cta-focus.png`, fullPage: true });
  screenshots.push('03-nav-cta-focus.png');
  await navCta.press('Enter');
  await page.waitForTimeout(150);
  addCheck('nav CTA jump to kontakt', page.url().includes('#kontakt') || page.url().includes('/kontakt'), `URL: ${page.url()}`);

  const heroCta = page.locator('.hero-actions .btn.btn-primary');
  await heroCta.focus();
  const heroFocused = await heroCta.evaluate((el) => document.activeElement === el);
  addCheck('hero CTA focusable', heroFocused, 'Hero CTA ist fokussierbar');
  await page.screenshot({ path: `${outDir}/04-hero-cta-focus.png`, fullPage: true });
  screenshots.push('04-hero-cta-focus.png');
  await heroCta.press('Enter');
  await page.waitForTimeout(150);
  addCheck('hero CTA jump to kontakt', page.url().includes('#kontakt') || page.url().includes('/kontakt'), `URL: ${page.url()}`);

  for (const selector of ['#name', '#company', '#email', '#phone', '#message']) {
    const field = page.locator(selector);
    await field.focus();
    const focused = await field.evaluate((el) => document.activeElement === el);
    addCheck(`focusable ${selector}`, focused, `${selector} ist fokussierbar`);
  }
  await page.screenshot({ path: `${outDir}/05-contact-fields-focus.png`, fullPage: true });
  screenshots.push('05-contact-fields-focus.png');

  fs.writeFileSync(`${outDir}/report.json`, JSON.stringify({
    generated_utc: new Date().toISOString(),
    base_url: baseUrl,
    checks,
    screenshots
  }, null, 2));
});
EOF

BASE_URL="${BASE_URL}" OUT_DIR="${OUT_DIR}" npx --yes -p @playwright/test playwright test "${PW_SPEC}" --reporter=line >"${PW_LOG}" 2>&1

echo "A11y keyboard evidence created in ${OUT_DIR}"
