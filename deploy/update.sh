#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [ ! -f .env ]; then
  echo "ERROR: .env fehlt in $ROOT_DIR" >&2
  exit 1
fi

echo "== compose config check =="
docker compose -f docker-compose.live.yml config >/dev/null

echo "== build + up =="
docker compose -f docker-compose.live.yml up -d --build

echo "== status =="
docker ps --format '{{.Names}}|{{.Image}}|{{.Status}}|{{.Ports}}' | grep '^rddigital-' || true

echo "== next verification =="
echo "curl -I https://rddigital.de"
echo "curl -I https://www.rddigital.de"
echo "curl -I https://rddigital.de/kontakt"
