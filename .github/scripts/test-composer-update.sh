#!/usr/bin/env bash
set -euo pipefail

script=$(cd "$(dirname "$0")" && pwd)/composer-update.sh
sandbox=$(mktemp -d)
trap 'rm -f "$sandbox/"*; rmdir "$sandbox"' EXIT
export RETRY_TEST_DIR="$sandbox"
export PATH="$sandbox:$PATH"

cat > "$sandbox/composer" <<'MOCK'
#!/usr/bin/env bash
set -eu
count=$(cat "$RETRY_TEST_DIR/count")
count=$((count + 1))
echo "$count" > "$RETRY_TEST_DIR/count"
printf '%s\n' "$@" > "$RETRY_TEST_DIR/args"
case "$RETRY_TEST_MODE" in
    success) exit 0 ;;
    recover) [ "$count" -lt 2 ] || exit 0 ;;
    conflict) echo 'Your requirements could not be resolved to an installable set of packages.'; exit 2 ;;
    forbidden) echo 'Download failed (HTTP/2 403 )'; exit 100 ;;
esac
echo 'The archive could not be downloaded (HTTP/2 429 )'
exit 100
MOCK
cat > "$sandbox/sleep" <<'MOCK'
#!/usr/bin/env bash
printf '%s\n' "$1" >> "$RETRY_TEST_DIR/delays"
MOCK
chmod +x "$sandbox/composer" "$sandbox/sleep"

check() {
    export RETRY_TEST_MODE="$1"
    echo 0 > "$sandbox/count"
    : > "$sandbox/delays"
    status=0
    bash "$script" --prefer-lowest --prefer-stable > "$sandbox/output" 2>&1 || status=$?
    [ "$status" -eq "$2" ]
    [ "$(cat "$sandbox/count")" -eq "$3" ]
    [ "$(paste -sd, "$sandbox/delays")" = "$4" ]
    printf '%s\n' update --prefer-dist --no-interaction --prefer-lowest --prefer-stable > "$sandbox/expected-args"
    cmp "$sandbox/expected-args" "$sandbox/args"
    echo "$1: passed"
}

check success 0 1 ''
check recover 0 2 '10'
check exhausted 100 3 '10,20'
check conflict 2 1 ''
check forbidden 100 1 ''
