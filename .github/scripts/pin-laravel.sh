#!/usr/bin/env bash
# Pin one Laravel major (and the Testbench major built for it) for a CI run.
# The package itself declares a range; this narrows a single job to one point in it.
set -euo pipefail

case "$1" in
  11) testbench='9.*' ;;
  12) testbench='10.*' ;;
  13) testbench='11.*' ;;
  *) echo "::error::Unsupported Laravel major: $1" >&2; exit 1 ;;
esac

# Laravel 11 is end-of-life: every 11.x release carries the unpatched advisory
# PKSA-mdq4-51ck-6kdq, so Composer refuses to install any of them by default.
# Support is kept to ease upgrades, so these jobs opt out of the block.
if [ "$1" = '11' ]; then
  composer config policy.advisories.block false
fi

composer require "laravel/framework:$1.*" "orchestra/testbench:$testbench" --dev --no-update --no-interaction
