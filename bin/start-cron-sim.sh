#!/usr/bin/env bash
set -euo pipefail
ROOT="/data/.openclaw/workspace/sweetydog41"
PID_FILE="$ROOT/logs/cron-sim.pid"
mkdir -p "$ROOT/logs"

if [[ -f "$PID_FILE" ]] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null; then
  echo "cron-sim déjà actif (pid $(cat "$PID_FILE"))."
  exit 0
fi

nohup "$ROOT/bin/cron-sim.sh" >/dev/null 2>&1 &
echo $! > "$PID_FILE"
echo "cron-sim démarré (pid $!)."
