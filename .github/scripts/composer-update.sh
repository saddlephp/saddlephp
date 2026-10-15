#!/usr/bin/env bash
# Retry transient archive-download errors without retrying dependency conflicts.
set -euo pipefail

log=$(mktemp)
trap 'rm -f "$log"' EXIT

for attempt in 1 2 3; do
    if composer update --prefer-dist --no-interaction "$@" >"$log" 2>&1; then
        cat "$log"
        exit 0
    else
        status=$?
    fi
    cat "$log"

    if [ "$attempt" -eq 3 ] || ! grep -Eq 'HTTP(/[^[:space:]]+)?[[:space:]]+(429|502|503|504)([[:space:])]|$)' "$log"; then
        exit "$status"
    fi

    delay=$((attempt * 10))
    echo "Transient Composer download failure; retrying in ${delay}s (attempt $((attempt + 1))/3)."
    sleep "$delay"
done
