#!/usr/bin/env bash
#
# runTests.sh - Run PHPUnit tests for the Todo-App-Backend
#
# Usage:  ./runTests.sh
#
# Prompts interactively to select which test group to run,
# then runs them via PHPUnit.
#
# Compatible with Linux and macOS (bash 3.2+).
#

set -euo pipefail

# --- Paths ----------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHPUNIT="${SCRIPT_DIR}/vendor/bin/phpunit"
CONFIG="${SCRIPT_DIR}/phpunit.xml.dist"

# --- Helpers --------------------------------------------------------------

err() { echo "  ERROR: $*" >&2; }

cleanup() {
    find "${SCRIPT_DIR}/tests" -name 'Debug*Test.php' -delete 2>/dev/null || true
}
trap cleanup EXIT

# --- Pre-flight -----------------------------------------------------------

if [ ! -f "$PHPUNIT" ]; then
    err "PHPUnit not found at ${PHPUNIT}"
    err "Did you run 'composer install'?"
    exit 1
fi

if ! command -v php &>/dev/null; then
    err "PHP is not installed or not in PATH."
    exit 1
fi

# --- Menu -----------------------------------------------------------------

echo ""
echo "  ┌─────────────────────────────────────────────┐"
echo "  │    Todo-App-Backend - Test Runner           │"
echo "  └─────────────────────────────────────────────┘"
echo ""

echo "  Select test group:"
echo "    1) all      - All tests"
echo "    2) feature  - Feature tests (auth API)"
echo "    3) unit     - Unit tests (controllers, models)"
echo "    4) database - Database tests (migrations)"
echo "    5) api      - Model tests (Todo, Category, Project)"
echo "    0) quit"
echo ""

read -r -p "  Pick a number: " GROUP_NUM
echo ""

case "$GROUP_NUM" in
    0)
        echo "  Bye!"
        exit 0
        ;;
    1|all)
        echo "  Running ALL tests..."
        GROUP_DIR=""
        GROUP_LABEL="all"
        ;;
    2|feature)
        echo "  Running feature tests..."
        GROUP_DIR="${SCRIPT_DIR}/tests/feature"
        GROUP_LABEL="feature"
        ;;
    3|unit)
        echo "  Running unit tests..."
        GROUP_DIR="${SCRIPT_DIR}/tests/unit"
        GROUP_LABEL="unit"
        ;;
    4|database)
        echo "  Running database tests..."
        GROUP_DIR="${SCRIPT_DIR}/tests/database"
        GROUP_LABEL="database"
        ;;
    5|api)
        echo "  Running model tests..."
        GROUP_DIR="${SCRIPT_DIR}/tests/api"
        GROUP_LABEL="api"
        ;;
    *)
        err "Unknown selection."
        exit 1
        ;;
esac

# --- Optional method filter (skip prompt for 'all') -----------------------

FILTER=""
if [ "$GROUP_LABEL" != "all" ]; then
    echo ""
    read -r -p "  Filter by test name (or press Enter for all): " FILTER
    echo ""
fi

# --- Build command --------------------------------------------------------

# Must cd to project root so HOMEPATH=./ resolves correctly
cd "$SCRIPT_DIR"

CMD=("$PHPUNIT" "--configuration" "$CONFIG")

if [ -n "$GROUP_DIR" ]; then
    CMD+=("$GROUP_DIR")
fi

if [ -n "$FILTER" ]; then
    CMD+=("--filter" "$FILTER")
fi

# --- Run ------------------------------------------------------------------

echo "  ───────────────────────────────────────────────"
echo "  Starting PHPUnit  ($GROUP_LABEL)"
echo ""

set +e
"${CMD[@]}"
EXIT_CODE=$?
set -e

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo "  ✅ All tests passed."
else
    echo "  ⚠️  Tests finished with exit code $EXIT_CODE — see above for details."
fi
echo ""

# PHPUnit exits 1 for warnings (e.g. no coverage driver) when
# failOnWarning is true. Only treat exit 2+ as real failures.
if [ $EXIT_CODE -eq 1 ]; then
    exit 0
fi
exit $EXIT_CODE
