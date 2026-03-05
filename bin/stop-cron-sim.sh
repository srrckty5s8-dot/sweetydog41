#!/usr/bin/env bash
set -euo pipefail
ROOT="/data/.openclaw/workspace/sweetydog41"
PID_FILE="$ROOT/logs/cron-sim.pid"

if [[ ! -f "$PID_FILE" ]]; then
  echo "cron-sim non actif."
  exit 0
fi

PID="$(cat "$PID_FILE")"
if kill -0 "$PID" 2>/dev/null; then
  kill "$PID" || true
  echo "cron-sim arrêté (pid $PID)."
else
  echo "processus introuvable, nettoyage PID file."
fi
rm -f "$PID_FILE"
