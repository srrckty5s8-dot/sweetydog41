#!/usr/bin/env bash
set -euo pipefail

ROOT="/data/.openclaw/workspace/sweetydog41"
LOG="$ROOT/logs/cron-sim.log"

mkdir -p "$ROOT/logs"

echo "[$(date -Iseconds)] cron-sim started" >> "$LOG"
while true; do
  "$ROOT/bin/auto-improve.sh" >> "$LOG" 2>&1 || true
  sleep 600
done
