#!/usr/bin/env bash
set -euo pipefail
ROOT="${1:-/var/www/sports-live}"
SLUG="panel-$(openssl rand -hex 16).php"
TARGET="$ROOT/$SLUG"
cat > "$TARGET" <<'PHP'
<?php
declare(strict_types=1);
define('ARENA_ADMIN_ENTRY', true);
require __DIR__ . '/admin_core.php';
PHP
chown root:www-data "$TARGET"
chmod 640 "$TARGET"
echo
echo "Hidden admin URL:"
echo "https://arena4klive.xyz/$SLUG"
echo
echo "SAVE THIS URL. The loader is intentionally not stored in GitHub."
