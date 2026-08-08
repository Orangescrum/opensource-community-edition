# Security Policy

## Supported Versions

The latest release on the `main` branch receives security fixes. Please make
sure you are running the most recent version before reporting an issue.

| Version | Supported |
|---------|-----------|
| Latest `main` | ✅ |
| Older commits | ❌ |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues,
discussions, or pull requests.**

Instead, report them privately by email to **support@orangescrum.com** with the
subject line `SECURITY`. Where possible, include:

- A description of the vulnerability and its impact
- Steps to reproduce (proof-of-concept, affected endpoint/parameter)
- The version/commit you tested against
- Any suggested remediation

You will receive an acknowledgement of your report, and we will keep you
informed as we investigate and address the issue. Please give us a reasonable
opportunity to release a fix before any public disclosure.

## Scope

This policy covers the Orangescrum Community Edition code in this repository.
Deployment hardening (TLS termination, network isolation, OS patching, database
credentials, rate limiting at the proxy, backups) is the responsibility of the
operator; see the [Installation Guide](INSTALL.md) for configuration guidance.
