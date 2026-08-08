# Contributing to Orangescrum Community Edition

Thanks for your interest in contributing.

## Licensing and the CLA

This project is licensed under the **GNU Affero General Public License v3.0**.

Before your first contribution can be merged, you need to sign the
[Contributor License Agreement](CLA.md). This is required because Andolasoft Inc.
also distributes a commercially licensed edition sharing this codebase, and needs
permission to use contributions in both. **You keep the copyright to your work.**

To sign, add yourself to [`CONTRIBUTORS.md`](CONTRIBUTORS.md) in your first pull
request. If you are contributing as part of your employment, your employer may need
to sign a Corporate CLA — see [CLA.md](CLA.md#corporate-contributions).

## Before you start

For anything beyond a small fix, **open an issue first**. It avoids someone spending
a weekend on a change that does not fit the project's direction.

## Development setup

See [`docs/INSTALLATION_AND_UPGRADE_GUIDE.md`](docs/INSTALLATION_AND_UPGRADE_GUIDE.md).

Stack: CakePHP 4.6 (PHP 8.2+), PostgreSQL 16, Docker.

```bash
docker compose up -d
```

## Conventions

These are enforced in review:

- Follow existing CakePHP 4 conventions in the surrounding code
- **Migrations are schema only** — no `INSERT`/`UPDATE`/`DELETE` of reference data.
  Seed data belongs in `config/Seeds/`
- Plugin migrations go in `plugins/<Name>/config/Migrations/`, not `config/Migrations/`
- Use `SES_COMP` for company-scoped queries
- Let the code speak for itself. Comment only what the code cannot express — a
  non-obvious external constraint, or a workaround for a known upstream bug
- Do not commit ad-hoc SQL, debug scripts, binary documents, or sample data
- Temporary and scratch files belong in `tmp/` (gitignored)

## Tests

Automated test suites are maintained outside this public repository. When you make a
behaviour change, please describe how you verified it in your pull request and add or
update tests in whatever internal harness you use.

## Pull requests

1. Branch off `main`
2. Keep the change focused — one concern per PR
3. Verify your change and describe how in the PR
4. Describe **what** changed and **why**; link the issue
5. Confirm you have signed the CLA

## Reporting security issues

**Do not open a public issue for security vulnerabilities.** Email
[SECURITY CONTACT EMAIL] instead, and allow reasonable time for a fix before any
public disclosure.
