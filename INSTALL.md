# Installation Guide — Orangescrum Community Edition

Orangescrum Community Edition is free and open source under the GNU AGPL v3.0.
There is **no licence key**, and no limit on users, projects or storage.

There are two ways to install it:

- **[Docker](#option-a-docker-recommended)** — recommended. Everything (PHP,
  PostgreSQL, web server) runs in containers. This is the fastest and most
  reliable path.
- **[Manual](#option-b-manual-install-without-docker)** — install PHP,
  PostgreSQL and a web server yourself. Use this only if you cannot run Docker.

Either way, you finish in the browser with a short **[setup wizard](#the-setup-wizard)**.

---

## Option A: Docker (recommended)

### Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows,
  macOS or Linux), or Docker Engine + the Compose plugin on a server.

Nothing else is needed on the host — PHP, PostgreSQL 16 and the web server all
run inside containers.

### 1. Get the code

```bash
git clone https://github.com/Orangescrum/opensource-community-edition.git
cd opensource-community-edition
```

### 2. (Optional) configure the environment

The defaults work out of the box. To change the port, public URL or database
credentials, copy the example file and edit it:

```bash
cp .env.example .env
```

Docker Compose reads `.env` automatically. See the
[configuration reference](#configuration-reference) for every value. The most
common one to change is the port:

```dotenv
APP_PORT=8080
FULL_BASE_URL=http://localhost:8080
```

> **Tip:** set a fixed `SECURITY_SALT` in `.env` before your first start (see
> the reference below). It keeps login sessions valid across rebuilds and
> updates — without it a rebuild can log everyone out.

### 3. Start it

```bash
docker compose up -d --build
```

The first run builds the image and starts two containers (the app and
PostgreSQL). Give it a minute; you can watch progress with
`docker compose logs -f`.

### 4. Finish in the browser

Open **http://localhost:8080** (or whatever `APP_PORT` you set) and complete the
**[setup wizard](#the-setup-wizard)**.

That machine is now your Orangescrum server. Anyone on the same network reaches
it at `http://<that-machine-name>:8080`.

---

## Option B: Manual install (without Docker)

### Requirements

- **PHP 8.2** or newer, with these extensions: `pdo`, `pdo_pgsql`, `openssl`,
  `mbstring`, `tokenizer`, `intl`, `json`, `xml`, `ctype`, `curl`, `gd`, `zip`.
- **PostgreSQL 16**.
- **Composer 2**.
- A web server (Apache or Nginx) with the document root pointing at `webroot/`.
- **Node.js 18+** — only if you intend to rebuild the front-end bundles; the
  committed bundles work as-is.

### Steps

1. **Get the code and install PHP dependencies:**

   ```bash
   git clone https://github.com/Orangescrum/opensource-community-edition.git
   cd opensource-community-edition
   composer install
   ```

   If `config/app_local.php` is missing afterwards:

   ```bash
   composer run-script post-install-cmd --no-interaction
   ```

2. **Create an empty PostgreSQL database** and a user that owns it, e.g.:

   ```sql
   CREATE USER orangescrum WITH PASSWORD 'orangescrum';
   CREATE DATABASE orangescrum OWNER orangescrum;
   ```

3. **Point your web server** document root at `webroot/` and ensure
   `logs/`, `tmp/` and `config/` are writable by the web-server user.

4. **Open the site in a browser** and complete the
   **[setup wizard](#the-setup-wizard)**. The wizard creates all tables, seeds
   the default data (roles, menus, task types, workflow statuses) and creates
   your admin account — enter the database credentials from step 2 when asked.

> The web wizard is the supported way to initialise the schema because it also
> toggles PostgreSQL identity columns around the seed step. Running
> `bin/cake migrations migrate` / `seed` by hand is a developer-only path and
> needs that toggling done manually.

---

## The setup wizard

However you installed, the browser wizard has the same steps:

1. **System check** — verifies your PHP version and required extensions. Fix any
   red item before continuing.
2. **Database** — enter the host, port, name, user and password. On Docker the
   defaults are pre-filled (`host = orangescrum-postgres`). The wizard creates
   the database if it does not exist. If it finds an **existing database with
   data**, it offers two choices:
   - **Keep data & upgrade** — reuse the existing tables.
   - **Erase & clean reinstall** — drop everything and install fresh.
3. **Confirm** — click to create the tables and seed the default data.
4. **Mail (SMTP)** — optional. Enter SMTP details for outgoing email (invites,
   notifications, password resets), or skip and configure it later.
5. **Create your account** — set up the first admin user. Done.

There is **no licence step** — the Community Edition has no key to enter.

---

## After installation

### Everyday commands (Docker)

```bash
docker compose stop                            # stop
docker compose up -d                           # start again
docker compose logs -f                         # view logs
docker compose pull && docker compose up -d    # update to a newer image
```

Your data lives in Docker volumes and survives stop / start / update.

### Backup and restore (Docker)

```bash
# Backup
docker compose exec orangescrum-postgres pg_dump -U orangescrum orangescrum > backup.sql

# Restore (into a running, empty database)
cat backup.sql | docker compose exec -T orangescrum-postgres psql -U orangescrum orangescrum
```

Also back up the uploaded-files volume (`orangescrum-app-files`) if you store
attachments.

### Desktop app

A Windows desktop client lives in [`desktop/`](desktop/). It connects to the
server above — install it on each team member's machine and enter the server
address once.

---

## Reinstalling / resetting

To wipe everything and start over:

- **Docker — full reset (removes all data and volumes):**

  ```bash
  docker compose down -v
  docker compose up -d --build
  ```

- **Keep the containers, wipe only the data:** re-run the wizard's Database step
  and choose **Erase & clean reinstall**.

---

## Configuration reference

All values are optional; the defaults below match `docker-compose.yml`. Set them
in `.env` (Docker) or as environment variables (manual installs).

| Variable | Default | Purpose |
|---|---|---|
| `APP_PORT` | `8080` | Host port the app is published on. |
| `FULL_BASE_URL` | `http://localhost:8080` | Public URL the browser uses. **Must** match the scheme/host/port you actually browse to. Behind an HTTPS proxy, set e.g. `https://app.example.com`. |
| `DB_NAME` | `orangescrum` | Database name. |
| `DB_USERNAME` | `orangescrum` | Database user. |
| `DB_PASSWORD` | `orangescrum` | Database password. **Change this for any real deployment.** |
| `SECURITY_SALT` | *(generated)* | Persisted salt for hashing / CSRF / sessions. Set a fixed value (`openssl rand -hex 32`) so sessions survive rebuilds. Leave blank to auto-generate a per-install salt. |
| `SESSION_COOKIE_SECURE` | `false` | Set `true` when serving over HTTPS. |
| `SESSION_COOKIE_NAME` | `ORANGESCRUM_SESSID` | Session cookie name. |
| `CSRF_COOKIE_NAME` | `orangescrum_csrf` | CSRF cookie name. |
| `CACHE_ENGINE` | `file` | Cache engine (`file`, or `redis`/`memcached` if configured). |

### Running behind an HTTPS reverse proxy

Set `FULL_BASE_URL=https://your-domain` and `SESSION_COOKIE_SECURE=true`, and
terminate TLS at your proxy (nginx/Traefik/Caddy) forwarding to the app's
published port.

---

## Troubleshooting

**"The database already contains tables" on the Database step.**
The target database is not empty (common when the Postgres volume persists from
a previous install). Choose **Erase & clean reinstall** to wipe and install
fresh, or **Keep data & upgrade** to reuse it. For a truly clean slate on
Docker, run `docker compose down -v` first.

**403 / "Missing or invalid CSRF cookie" after a reinstall.**
Reinstalling can regenerate the security salt, which invalidates the CSRF
cookies your browser still holds. Clear the site's cookies (or use a private
window) and reload. To prevent it, set a fixed `SECURITY_SALT` in `.env` so the
salt is stable across rebuilds.

**Port already in use.**
Another service is on `APP_PORT`. Set a different `APP_PORT` (and matching
`FULL_BASE_URL`) in `.env`, then `docker compose up -d`.

**System-check step shows a red extension.**
Install/enable the missing PHP extension (see the manual requirements list) and
reload the page. On Docker this should not happen — the image bundles them all.

**Blank page or assets not loading after an update.**
Hard-refresh the browser (the front-end bundles are cache-busted per release).
On Docker, make sure you ran `docker compose up -d --build` so the new image is
in use.

---

## Getting help

- Issues: <https://github.com/Orangescrum/opensource-community-edition/issues>
- Email: support@orangescrum.com
