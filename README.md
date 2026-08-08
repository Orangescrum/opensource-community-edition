<div align="center">

# Orangescrum — Community Edition

**Open-source, self-hosted project management & team collaboration.**
Plan projects, track tasks on lists & kanban boards, log time, run custom workflows, and report — on your own server, with no per-user fees.

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![CakePHP](https://img.shields.io/badge/CakePHP-4.6-D33C43?logo=cakephp&logoColor=white)](https://cakephp.org/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

[![GitHub Stars](https://img.shields.io/github/stars/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition/network/members)
[![GitHub Downloads](https://img.shields.io/github/downloads/Orangescrum/opensource-community-edition/total?logo=github&label=downloads)](https://github.com/Orangescrum/opensource-community-edition/releases)
[![GitHub Issues](https://img.shields.io/github/issues/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition/issues)

[Quick Start](#quick-start) · [Features](#features) · [Documentation](INSTALL.md) · [Contributing](CONTRIBUTING.md) · [License](#license)

</div>

---

Orangescrum is a project management and collaboration tool that helps teams plan, track and deliver work in one place. This is the **Community Edition** — free and open source under the GNU AGPL v3.0. There is **no licence key**, and **no limits** on users, projects or storage. You run it on your own infrastructure and your data never leaves it.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Configuration & Everyday Commands](#configuration--everyday-commands)
- [Backups](#backups)
- [Desktop App](#desktop-app)
- [Development Setup](#development-setup)
- [Documentation](#documentation)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)

## Features

**Projects & Tasks**
- Projects with members, roles and per-project workflows
- Tasks with priorities, due dates, assignees, labels and task types
- Subtasks and task groups (milestones)
- List, Kanban board, calendar and overview views

**Planning & Tracking**
- Custom statuses and workflows per project
- Time logging and timesheets
- Reports and analytics dashboards
- CSV import/export

**Collaboration**
- Comments, mentions and activity feeds
- File attachments on tasks
- Email notifications (SMTP)

**Administration**
- Self-service setup wizard (no licence key)
- Five built-in roles (Owner, Admin, User, Client, Guest)
- Company/workspace-scoped data isolation

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | CakePHP 4.6 · PHP 8.2+ |
| Database | PostgreSQL 16 |
| Frontend | Vue 3 (Vite) + AngularJS (legacy views) |
| Runtime | Docker & Docker Compose (Apache + PHP) |

## Quick Start

**Requirements:** [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows, macOS or Linux). Nothing else — PHP, PostgreSQL and the web server all run inside Docker.

```bash
git clone https://github.com/Orangescrum/opensource-community-edition.git
cd opensource-community-edition
docker compose up -d --build
```

Then open **http://localhost:8080** and follow the setup wizard: system check → database → mail → create your admin account.

That machine is now your Orangescrum server. Anyone on the same network reaches it at `http://<that-machine-name>:8080`.

## Configuration & Everyday Commands

To change the port, credentials or public URL, copy `.env.example` to `.env` and edit it **before** starting.

```bash
docker compose stop      # stop
docker compose up -d     # start again
docker compose logs -f   # view logs
docker compose pull && docker compose up -d   # update
```

## Backups

Your data lives in Docker volumes and survives stop/start/update. To back it up:

```bash
docker compose exec orangescrum-postgres pg_dump -U orangescrum orangescrum > backup.sql
```

## Desktop App

A Windows desktop client lives in [`desktop/`](desktop/). It connects to the server above — install it on each team member's machine and enter the server address once.

## Development Setup

Only needed if you intend to change the code. Requires PHP 8.2, Composer, PostgreSQL 16 and Node.js 18+.

```bash
composer install
# if config/app_local.php is missing afterwards:
composer run-script post-install-cmd --no-interaction
```

Set the database credentials in `config/.env`, then:

```bash
bin/cake migrations migrate
bin/cake migrations seed
```

Front-end bundles are built with Vite:

```bash
cd frontend/task-views && npm install && npm run build
```

## Documentation

Full instructions — configuration, manual (non-Docker) install, backups, reset and troubleshooting — are in the **[Installation Guide](INSTALL.md)**.

## Security

Please report security vulnerabilities responsibly and **privately** to **support@orangescrum.com** — do not open a public issue for security reports. See [SECURITY.md](SECURITY.md) for details.

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) — all contributors are asked to sign the Contributor Licence Agreement in [CLA.md](CLA.md).

## License

Copyright (c) 2026 Andolasoft Inc.

Orangescrum Community Edition is free software, licensed under the **GNU Affero General Public License v3.0 or later** (`AGPL-3.0-or-later`). The full licence text is in [LICENSE](LICENSE).

Because the licence is the *Affero* GPL, one obligation deserves a specific mention: **if you modify Orangescrum and let other people use it over a network, you must publish the corresponding source of your modified version to those users.** That applies to hosted and SaaS deployments, not only to redistributed copies.

**Need to use Orangescrum without these obligations, or want the Enterprise edition?** A commercial licence is available from Andolasoft Inc. — see [orangescrum.com](https://www.orangescrum.com/contact-us).

See [NOTICE](NOTICE) for copyright, third-party attributions and trademark terms. Bundled open-source dependencies and their individual licences are listed in `THIRD_PARTY_NOTICES.txt`.

> "Orangescrum" and the Orangescrum logo are trademarks of Andolasoft Inc. The AGPL grants rights to the software, not to the marks.

## Contact

Questions or support: **support@orangescrum.com** · [orangescrum.com](https://www.orangescrum.com)
