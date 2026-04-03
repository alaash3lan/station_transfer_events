#!/bin/sh
set -e

# Run migrations
php artisan migrate --force

# Shorthand: "test" maps to "php artisan test"
if [ "$1" = "test" ]; then
    shift
    exec php artisan test "$@"
fi

# If arguments passed, run them instead of the default server
if [ $# -gt 0 ]; then
    exec "$@"
fi

# Default: start the server
exec php artisan serve --host=0.0.0.0 --port=8000
