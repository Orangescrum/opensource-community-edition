<div align="center">

# Orangescrum — Community Edition

**Free and open-source self-hosted alternative to Jira, Asana, Trello, Monday.com, OpenProject and Redmine.**
Plan projects, track tasks on lists & kanban boards, log time, run custom workflows, and report — on your own server, with no per-user fees and no licence key.

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![CakePHP](https://img.shields.io/badge/CakePHP-4.6-D33C43?logo=cakephp&logoColor=white)](https://cakephp.org/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

[![GitHub Stars](https://img.shields.io/github/stars/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition)
[![GitHub Forks](https://img.shields.io/github/forks/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition/network/members)
[![GitHub Downloads](https://img.shields.io/github/downloads/Orangescrum/opensource-community-edition/total?logo=github&label=downloads)](https://github.com/Orangescrum/opensource-community-edition/releases)
[![GitHub Issues](https://img.shields.io/github/issues/Orangescrum/opensource-community-edition?logo=github)](https://github.com/Orangescrum/opensource-community-edition/issues)

[Quick Start](#quick-start) · [Features](#features) · [Compared with other open source tools](#orangescrum-vs-other-open-source-project-management-tools) · [FAQ](#faq) · [Documentation](INSTALL.md) · [Contributing](CONTRIBUTING.md) · [License](#license)

</div>

---

Orangescrum is a project management and collaboration tool that helps teams plan, track and deliver work in one place — an open source alternative to Jira, Asana, Trello and Monday.com for teams that would rather not pay per seat or hand their project data to someone else's cloud.

This is the **Community Edition** — free and open source under the GNU AGPL v3.0. There is **no licence key**, and **no limits** on users, projects or storage. You run it on your own infrastructure and your data never leaves it. That last point is why it is used by teams in healthcare, pharmaceuticals, finance and government.

## Table of Contents

- [Features](#features)
- [Orangescrum vs other open source project management tools](#orangescrum-vs-other-open-source-project-management-tools)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Configuration & Everyday Commands](#configuration--everyday-commands)
- [Backups](#backups)
- [Desktop App](#desktop-app)
- [Development Setup](#development-setup)
- [Documentation](#documentation)
- [Security](#security)
- [Contributing](#contributing)
- [FAQ](#faq)
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

## Orangescrum vs other open source project management tools

Every tool here is open source and can be self-hosted for free. The table is
about where each one is strongest, not which is best.

| | **Orangescrum CE** | Plane | OpenProject | Leantime | Taiga | Redmine |
|---|---|---|---|---|---|---|
| Licence | AGPL-3.0 | AGPL v3 | GPL v3 | AGPL v3 | MPL 2.0 | GPL v2 |
| Free user cap | **None** | None | None | **3** | None | None |
| Kanban board | Yes | Yes | Yes | Yes | Yes | Plugin |
| List and table views | Yes | Yes | Yes | Yes | Yes | Yes |
| Custom workflows | Yes | Yes | Yes | Partial | Yes | Yes |
| Milestones | Task groups | Modules | Yes | Milestones | Epics | Versions |
| Time tracking | **Yes** | Not built in | Yes | Yes | Plugin | Yes |
| Reports and dashboards | Yes | Partial | Yes | Partial | Partial | Partial |
| Gantt | Self-Hosted edition | Partial | Yes | Timeline | Not built in | Yes |
| Sprints | Self-Hosted edition | Yes | Yes | Not built in | Yes | Plugin |
| Wiki | Self-Hosted edition | Yes | Yes | Partial | Yes | Yes |
| REST API | Self-Hosted edition | REST | REST | REST | REST | REST |

**Self-Hosted edition** marks a feature that is not in the free Community
Edition but is in the paid Orangescrum Self-Hosted and Cloud editions —
[contact sales](https://www.orangescrum.com/contact-sales). The Community
Edition is self-hosted as well, so the label is about the edition, not about
who runs the server. There is a supported migration path from this edition to
those, so starting here is not a dead end.

Competitor rows checked 6 September 2026, Orangescrum rows 17 September 2026.
Projects move quickly, so check each vendor before you decide. A longer
write-up of the same six tools is at
[Top 6 open source project management software](https://blog.orangescrum.com/open-source-project-management-software/).

### Choose it when

Orangescrum Community Edition suits you when time on tasks matters as much as
the tasks: time tracking, billable hours and the reports over them are built in
and free, where Plane has no time tracking at all and Taiga needs a plugin. It
also has no user cap, so a team of forty costs the same as a team of four.

Look elsewhere when you need Gantt charts, sprints, a wiki or a REST API and
want them for free: OpenProject gives you all four under GPL, and Redmine gives
you Gantt plus a plugin ecosystem that has been going for twenty years. If you
would rather stay with Orangescrum, those four are in the paid Self-Hosted and
Cloud editions — [contact sales](https://www.orangescrum.com/contact-sales).

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

**Need to use Orangescrum without these obligations, or want the Enterprise edition?** A commercial licence is available from Andolasoft Inc. — see [contact sales](https://www.orangescrum.com/contact-sales).

See [NOTICE](NOTICE) for copyright, third-party attributions and trademark terms. Bundled open-source dependencies and their individual licences are listed in `THIRD_PARTY_NOTICES.txt`.

> "Orangescrum" and the Orangescrum logo are trademarks of Andolasoft Inc. The AGPL grants rights to the software, not to the marks.

## FAQ

**Is Orangescrum free?**
Yes. The Community Edition is free and open source under the GNU AGPL v3.0.
There is no licence key and no limit on users, projects or storage.

**Is Orangescrum a good open source alternative to Jira?**
For the core workflow most teams use Jira for — tasks, boards, custom statuses
and workflows, roles — yes, and without per-user licensing or Jira's paid Data
Center tier to self-host. Sprints and Gantt charts are not in the Community
Edition, so a team that runs formal Scrum should look at OpenProject, or at the
paid Orangescrum Self-Hosted edition —
[contact sales](https://www.orangescrum.com/contact-sales).

**Can I self-host it?**
Yes, that is the only way it runs. `docker compose up -d --build` and the setup
wizard, or a manual install with PHP and PostgreSQL. See
[INSTALL.md](INSTALL.md).

**Does it track time?**
Yes. Time logging, a timer, billable and non-billable hours, and CSV and PDF
export of the time log are all in the free edition. Timesheet approval is not.

**Is there a REST API?**
Not in the Community Edition. The paid Self-Hosted and Cloud editions have one
— [contact sales](https://www.orangescrum.com/contact-sales).

**What licence is it released under?**
GNU AGPL-3.0-or-later. If you modify it and let others use it over a network,
including as a hosted service, you must publish your modified source to those
users. A commercial licence without that obligation is available from
Andolasoft Inc.

**Who maintains it?**
Orangescrum is developed and maintained by Andolasoft Inc.

## Contact

Questions or support: **support@orangescrum.com** · [orangescrum.com](https://www.orangescrum.com)
