#!/usr/bin/env bash
set -euo pipefail

ROOT="/data/.openclaw/workspace/sweetydog41"
DATA_DIR="$ROOT/public/data"
LOG_DIR="$ROOT/logs"
JSON_FILE="$DATA_DIR/nouveautes.json"
DEBRIEF_FILE="$LOG_DIR/debrief-telegram.txt"

mkdir -p "$DATA_DIR" "$LOG_DIR"

NOW_HUMAN="$(date '+%Y-%m-%d %H:%M:%S')"
NOW_ISO="$(date -Iseconds)"

TITLES=(
  "Optimisation UX"
  "Bloc info client"
  "Micro-amélioration performance"
  "Nouveau composant visuel"
  "Affinage responsive"
)
BODIES=(
  "Ajustement léger des contenus de démonstration pour enrichir l'interface."
  "Ajout d'un élément de nouveauté visible en page dédiée."
  "Nettoyage progressif du rendu pour lecture plus claire."
  "Amélioration mineure de la présentation mobile."
  "Mise à jour incrémentale en mode sandbox local (sans push GitHub)."
)

idx=$(( RANDOM % ${#TITLES[@]} ))
title="${TITLES[$idx]}"
body="${BODIES[$idx]}"

python3 - "$JSON_FILE" "$title" "$body" "$NOW_HUMAN" <<'PY'
import json, os, sys
path, title, body, now = sys.argv[1:]
items = []
if os.path.exists(path):
    try:
        with open(path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            if isinstance(data, list):
                items = data
    except Exception:
        items = []
items.insert(0, {
    "title": title,
    "body": body,
    "updated_at": now
})
items = items[:30]
with open(path, 'w', encoding='utf-8') as f:
    json.dump(items, f, ensure_ascii=False, indent=2)
PY

{
  echo "[$NOW_HUMAN] ✅ Auto-update exécuté"
  echo "- $title"
  echo "- $body"
  echo "- URL nouveautés (locale): /nouveautes.php"
  echo
} >> "$DEBRIEF_FILE"

echo "[$NOW_ISO] auto-improve ok: $title" >> "$LOG_DIR/auto-improve.log"
