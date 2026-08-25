#!/bin/sh
# Resolve a STABLE Security.salt before Apache serves a single request.
#
# Why this exists: the installer writes config/app_local.php (with a salt) only
# at the database step. Before that, no app_local.php exists and Security.salt
# falls back to env('SECURITY_SALT') — empty by default — so CSRF cookies minted
# by the pre-install wizard pages are signed with a different salt than the one
# the installer later randomises. CakePHP never refreshes an existing CSRF
# cookie on GET, so that stale cookie survives registration and every following
# POST fails _verifyToken() with "Missing or invalid CSRF cookie".
#
# Fixing it here (rather than hard-coding a salt in docker-compose) keeps the
# salt UNIQUE PER DEPLOYMENT and stable across install + restarts: it is
# generated once and persisted in the config volume.
set -e

SALT_FILE="/var/www/html/config/.security_salt"

# Prefer an explicit SECURITY_SALT (operator-provided via compose/env); else
# reuse the persisted per-deployment salt; else generate one and persist it.
if [ -z "$SECURITY_SALT" ]; then
    if [ ! -f "$SALT_FILE" ]; then
        php -r 'echo hash("sha256", random_bytes(64));' > "$SALT_FILE"
        chown www-data:www-data "$SALT_FILE" 2>/dev/null || true
        chmod 0600 "$SALT_FILE" 2>/dev/null || true
    fi
    SECURITY_SALT="$(cat "$SALT_FILE")"
fi
export SECURITY_SALT

# Expose it to mod_php on every request ($_SERVER, which CakePHP's env() reads
# first). The value is hex, so no escaping concerns.
printf 'SetEnv SECURITY_SALT "%s"\n' "$SECURITY_SALT" > /etc/apache2/conf-enabled/security-salt.conf

# Defensive chown for the oauth-keys volume (may pre-date the image's
# pre-creation step). `;` so Apache still starts if the chown is rejected.
chown www-data:www-data /var/www/html/config/oauth-keys 2>/dev/null || true

# Reassert the upload-directory guard. webroot/files is a named volume, so the
# copy that ships in the image never reaches an existing install: an upgrade
# cannot replace it. A stale copy carrying php_admin_flag makes Apache answer
# 500 for EVERY request under /files/ - no avatar, project logo or attachment
# is served (public issue #33). This file is ours, not an operator setting, so
# it is restored unconditionally.
PRISTINE_FILES_HTACCESS="/usr/local/share/orangescrum/files.htaccess"
if [ -f "$PRISTINE_FILES_HTACCESS" ]; then
    if ! cmp -s "$PRISTINE_FILES_HTACCESS" /var/www/html/webroot/files/.htaccess 2>/dev/null; then
        cp "$PRISTINE_FILES_HTACCESS" /var/www/html/webroot/files/.htaccess
        chown www-data:www-data /var/www/html/webroot/files/.htaccess 2>/dev/null || true
        echo "entrypoint: restored webroot/files/.htaccess"
    fi
fi

cron
exec apache2ctl -D FOREGROUND
