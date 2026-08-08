# Orangescrum Desktop (prototype)

Windows-first desktop shell for a self-hosted Orangescrum Community Edition
server, built with [Tauri 2](https://tauri.app). The shell owns the window,
session persistence and link routing; all application logic stays on the server.

There is no offline mode — the app requires a reachable server, by design.

## Prerequisites

| Requirement | Notes |
| --- | --- |
| Node.js 18+ | already present if you build the Vue apps |
| Rust (stable) | **not yet installed** — see below |
| WebView2 runtime | ships with Windows 11; Tauri installs it on Windows 10 |

Install Rust once:

```bash
winget install --id Rustlang.Rustup -e
```

Then restart your shell so `cargo` is on `PATH`.

## Generate the icons

Tauri needs a real icon set before it will bundle. Generate one from the
existing Orangescrum logo (already copied to `src-tauri/icons/source-logo.png`):

```bash
npm run icon
```

## Run in development

```bash
npm install
npm run dev
```

First launch shows the connect screen. Enter the server address you normally use
in the browser — `http://` is assumed if you leave the scheme off. The address is
saved to the app config directory and reused on the next launch.

## Build an installer

```bash
npm run build
```

Produces an NSIS installer under `src-tauri/target/release/bundle/nsis/`. The
bundle is configured for a per-user install, so it needs no administrator
rights.

## What the shell does

- **Server switching** — File → Switch Server… clears the saved address and
  returns to the connect screen, so one build serves any number of installations.
- **Session persistence** — WebView2 keeps cookies in the app's own data
  directory, so logins survive a restart without touching the system browser.
- **Link routing** — same-origin navigation stays inside the app; anything
  pointing elsewhere opens in the system browser instead of trapping the user in
  a chrome-less window.
- **Reload** — Ctrl+R, since there is no browser toolbar.

## Known constraint: the host must match `SESSION_COOKIE_DOMAIN`

The server applies a host-redirect middleware (`src/Application.php`). When
`SESSION_COOKIE_DOMAIN` is set, any request whose host differs is 302'd to the
configured host. Point the desktop app at exactly that host — for example
`http://oss.localhost:8091` rather than `http://localhost:8091` — or the app will
follow a redirect on every navigation.

## Not yet implemented

Deliberately out of scope for the prototype, in rough priority order:

1. **Auto-update** — Tauri's updater plugin plus a signed release feed.
2. **Native downloads** — file downloads currently follow WebView2 defaults
   rather than a native save dialog.
3. **Desktop notifications** — bridging the in-app notification stream to the
   Windows notification centre.
4. **Tray icon and badge count** — unread/assigned-task count.
5. **Deep links** — an `orangescrum://` protocol handler.
6. **Code signing** — required before wide distribution, or SmartScreen will
   warn on every install.

## Licence

Copyright (c) 2026 Andolasoft Inc. Licensed under the GNU Affero General Public
License v3.0 or later, the same terms as the server. See `../LICENSE`.
