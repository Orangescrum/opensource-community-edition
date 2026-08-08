FROM ubuntu:noble

ENV DEBIAN_FRONTEND=noninteractive

# Install PHP 8 + Apache + required extensions + runtime tools
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apache2 libapache2-mod-php8.3 \
        php8.3 php8.3-cli \
        php8.3-mbstring php8.3-xml php8.3-mysql php8.3-intl \
        php8.3-zip php8.3-curl php8.3-gd php8.3-pgsql php8.3-sqlite3 \
        wkhtmltopdf imagemagick cron curl unzip vim composer && \
    apt-get autoremove -y && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache config
RUN a2enmod rewrite headers && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf 

COPY ./config/000-default.conf /etc/apache2/sites-available/000-default.conf
# COPY ./config/apache-overrides.conf /etc/apache2/conf-enabled/zz-overrides.conf
COPY ./config/php-custom-limits.ini /etc/php/8.3/apache2/conf.d/99-custom-limits.ini
COPY ./config/php-custom-limits.ini /etc/php/8.3/cli/conf.d/99-custom-limits.ini

# Ensure PHP session directory exists and is writable
RUN mkdir -p /var/lib/php/sessions /tmp/sessions && \
    chown -R www-data:www-data /var/lib/php/sessions /tmp/sessions && \
    chmod 1733 /var/lib/php/sessions

# Set working directory
WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

# Copy only Composer files first (to leverage caching)
COPY composer.json ./

RUN su www-data -s /bin/bash -c "composer install --no-dev --optimize-autoloader --no-interaction"
RUN su www-data -s /bin/bash -c "composer clear-cache"

# Copy rest of the source
COPY --chown=www-data:www-data . .

# Install CakePHP dependencies and run post-install scripts
RUN su www-data -s /bin/bash -c "composer run-script post-install-cmd --no-interaction" && \
    su www-data -s /bin/bash -c "composer dump-autoload"

# Generate consolidated third-party license notices for bundled OSS dependencies
RUN su www-data -s /bin/bash -c "php bin/generate-third-party-notices.php"

# Ensure CakePHP tmp directories exist and are writable
RUN mkdir -p tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions tmp/tests logs && \
    chown -R www-data:www-data tmp logs && \
    chmod -R 0775 tmp logs

# Pre-create the OAuth signing-key directory with www-data ownership. When a
# fresh named volume is mounted here (see docker-compose's orangescrum-oauth-keys
# volume), Docker copies these ownership/permissions into the new volume on
# first creation — so JwtKeyService::ensureKeys() can write the auto-generated
# RSA keypair without a manual chown step.
RUN mkdir -p /var/www/html/config/oauth-keys && \
    chown www-data:www-data /var/www/html/config/oauth-keys && \
    chmod 0750 /var/www/html/config/oauth-keys

# Cron setup — files use /etc/cron.d/ format with explicit `www-data`
# user column so jobs setuid to the Apache user before running and any
# tmp/ / logs/ writes share ownership with the web request path.
# crond reads /etc/cron.d/ directly at start — no `crontab -` install
# needed; that pattern caused jobs to land in root's user-crontab and
# break shared-state ownership (see docs/analysis/cron-runs-as-root-fix.md).
COPY ./config/cron/recurring_task_cron                               /etc/cron.d/recurring_task
COPY ./config/cron/queue_worker                                      /etc/cron.d/queue_worker

RUN chown root:root /etc/cron.d/recurring_task \
                    /etc/cron.d/queue_worker && \
    chmod 0644 /etc/cron.d/recurring_task \
               /etc/cron.d/queue_worker && \
    # Clear any residual root user-crontab carried over from a previous
    # build that used `{ cat ...; } | crontab -`. Without this, an image
    # derived from an older tagged base could still run the old root-owned
    # entries in parallel with /etc/cron.d/, doubling cron firings until
    # the layer is fully rebuilt.
    crontab -r 2>/dev/null || true

# Expose Apache HTTP port
EXPOSE 80

# Entrypoint resolves a stable per-deployment Security.salt (see the script)
# then launches cron + Apache in the foreground.
COPY --chmod=0755 ./scripts/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
CMD ["/usr/local/bin/docker-entrypoint.sh"]
