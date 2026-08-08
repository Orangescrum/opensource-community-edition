//! Orangescrum Community Edition — desktop shell.
//!
//! Thin Tauri wrapper around a self-hosted Orangescrum server. The shell owns
//! window chrome, session persistence and link routing; all application logic
//! stays on the server.

use std::fs;
use std::path::PathBuf;

use serde::{Deserialize, Serialize};
use tauri::{
    menu::{Menu, MenuItem, PredefinedMenuItem, Submenu},
    AppHandle, Manager, WebviewUrl, WebviewWindowBuilder,
};
use url::Url;

const SETUP_WINDOW: &str = "setup";
const MAIN_WINDOW: &str = "main";

#[derive(Default, Serialize, Deserialize)]
struct Config {
    server_url: Option<String>,
}

fn config_path(app: &AppHandle) -> Result<PathBuf, String> {
    let dir = app
        .path()
        .app_config_dir()
        .map_err(|e| format!("no config dir: {e}"))?;
    fs::create_dir_all(&dir).map_err(|e| format!("cannot create config dir: {e}"))?;
    Ok(dir.join("settings.json"))
}

fn load_config(app: &AppHandle) -> Config {
    config_path(app)
        .ok()
        .and_then(|p| fs::read_to_string(p).ok())
        .and_then(|s| serde_json::from_str(s.trim_start_matches('\u{feff}')).ok())
        .unwrap_or_default()
}

fn save_config(app: &AppHandle, cfg: &Config) -> Result<(), String> {
    let path = config_path(app)?;
    let body = serde_json::to_string_pretty(cfg).map_err(|e| e.to_string())?;
    fs::write(path, body).map_err(|e| format!("cannot write settings: {e}"))
}

/// Accept what a user would realistically type ("localhost:8091",
/// "https://work.example.com/") and return a normalised origin.
fn normalise(input: &str) -> Result<String, String> {
    let raw = input.trim().trim_end_matches('/');
    if raw.is_empty() {
        return Err("Enter your Orangescrum server address.".into());
    }
    let candidate = if raw.contains("://") {
        raw.to_string()
    } else {
        format!("http://{raw}")
    };
    let url = Url::parse(&candidate).map_err(|_| "That does not look like a valid address.".to_string())?;
    match url.scheme() {
        "http" | "https" => {}
        _ => return Err("Only http:// and https:// addresses are supported.".into()),
    }
    if url.host_str().is_none() {
        return Err("That address is missing a host name.".into());
    }
    Ok(url.as_str().trim_end_matches('/').to_string())
}

fn log_line(app: &AppHandle, msg: &str) {
    if let Ok(dir) = app.path().app_config_dir() {
        let _ = fs::create_dir_all(&dir);
        if let Ok(mut f) = fs::OpenOptions::new()
            .create(true)
            .append(true)
            .open(dir.join("shell.log"))
        {
            use std::io::Write;
            let ts = std::time::SystemTime::now()
                .duration_since(std::time::UNIX_EPOCH)
                .map(|d| d.as_secs())
                .unwrap_or(0);
            let _ = writeln!(f, "[{ts}] {msg}");
        }
    }
}

/// Hosts belong to the same installation when equal or in a subdomain
/// relation ("localhost" vs "oss.localhost") — the server's host-redirect
/// middleware bounces between exactly such pairs, and that redirect must be
/// followed in-app rather than kicked out to the system browser.
fn related_hosts(a: &str, b: &str) -> bool {
    a == b || a.ends_with(&format!(".{b}")) || b.ends_with(&format!(".{a}"))
}

fn open_main(app: &AppHandle, server: &str) -> Result<(), String> {
    let url = Url::parse(server).map_err(|e| e.to_string())?;

    // Reusing the label of a window that is still tearing down yields a dead,
    // blank webview, so drive an existing window rather than rebuilding it.
    if let Some(win) = app.get_webview_window(MAIN_WINDOW) {
        log_line(app, &format!("reusing main window -> {url}"));
        win.navigate(url).map_err(|e| format!("cannot navigate: {e}"))?;
        let _ = win.show();
        let _ = win.set_focus();
        return Ok(());
    }

    let base_host = url.host_str().unwrap_or("").to_string();
    let log_handle = app.clone();

    let win = WebviewWindowBuilder::new(app, MAIN_WINDOW, WebviewUrl::External(url))
        .title("Orangescrum")
        .inner_size(1440.0, 900.0)
        .min_inner_size(1024.0, 640.0)
        .resizable(true)
        .center()
        .on_navigation(move |target| {
            // Only a real http(s) link to another host belongs in the browser.
            // Everything else — javascript:, blob:, about:, data:, mailto: —
            // is the page driving itself; cancelling those breaks in-app
            // actions (the Create Task button is an href="javascript:void(0)")
            // and can leave the webview showing nothing.
            let external = matches!(target.scheme(), "http" | "https")
                && !related_hosts(target.host_str().unwrap_or(""), &base_host);

            if external {
                log_line(&log_handle, &format!("external {}", target.as_str()));
                let _ = open::that(target.as_str());
                return false;
            }

            log_line(&log_handle, &format!("nav {}", target.as_str()));
            true
        })
        .build()
        .map_err(|e| format!("cannot open window: {e}"))?;

    let _ = win.set_focus();
    Ok(())
}

fn open_setup(app: &AppHandle) -> Result<(), String> {
    let win = WebviewWindowBuilder::new(app, SETUP_WINDOW, WebviewUrl::App("index.html".into()))
        .title("Connect to Orangescrum")
        .inner_size(520.0, 460.0)
        .resizable(false)
        .center()
        .build()
        .map_err(|e| format!("cannot open setup window: {e}"))?;
    let _ = win.set_focus();
    Ok(())
}

#[tauri::command]
fn open_external(url: String) {
    if url.starts_with("http://") || url.starts_with("https://") {
        let _ = open::that(url);
    }
}

#[tauri::command]
fn get_server_url(app: AppHandle) -> Option<String> {
    load_config(&app).server_url
}

#[tauri::command]
async fn connect(app: AppHandle, url: String) -> Result<(), String> {
    let normalised = normalise(&url)?;
    save_config(
        &app,
        &Config {
            server_url: Some(normalised.clone()),
        },
    )?;
    open_main(&app, &normalised)?;
    if let Some(w) = app.get_webview_window(SETUP_WINDOW) {
        let _ = w.close();
    }
    Ok(())
}

/// Forget the stored server and return to the setup screen.
#[tauri::command]
async fn switch_server(app: AppHandle) -> Result<(), String> {
    save_config(&app, &Config { server_url: None })?;

    // Destroy the server window before showing setup, so reconnecting finds
    // the label free rather than racing a half-closed webview.
    if let Some(w) = app.get_webview_window(MAIN_WINDOW) {
        log_line(&app, "closing main window");
        let _ = w.destroy();
    }
    if app.get_webview_window(SETUP_WINDOW).is_none() {
        open_setup(&app)?;
    } else if let Some(w) = app.get_webview_window(SETUP_WINDOW) {
        let _ = w.set_focus();
    }
    Ok(())
}

fn build_menu(app: &AppHandle) -> tauri::Result<Menu<tauri::Wry>> {
    let file = Submenu::with_items(
        app,
        "File",
        true,
        &[
            &MenuItem::with_id(app, "switch", "Switch Server…", true, None::<&str>)?,
            &PredefinedMenuItem::separator(app)?,
            &PredefinedMenuItem::quit(app, Some("Quit"))?,
        ],
    )?;
    let view = Submenu::with_items(
        app,
        "View",
        true,
        &[
            &MenuItem::with_id(app, "reload", "Reload", true, Some("CmdOrCtrl+R"))?,
            &MenuItem::with_id(app, "devtools", "Developer Tools", true, Some("CmdOrCtrl+Shift+I"))?,
            &PredefinedMenuItem::fullscreen(app, None)?,
        ],
    )?;
    Menu::with_items(app, &[&file, &view])
}

pub fn run() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![get_server_url, connect, switch_server, open_external])
        .setup(|app| {
            let handle = app.handle().clone();

            let menu = build_menu(&handle)?;
            app.set_menu(menu)?;

            match load_config(&handle).server_url {
                Some(server) => open_main(&handle, &server).map_err(std::io::Error::other)?,
                None => open_setup(&handle).map_err(std::io::Error::other)?,
            }
            Ok(())
        })
        .on_menu_event(|app, event| match event.id().as_ref() {
            "switch" => {
                let handle = app.clone();
                tauri::async_runtime::spawn(async move {
                    let _ = switch_server(handle).await;
                });
            }
            "reload" => {
                if let Some(w) = app.get_webview_window(MAIN_WINDOW) {
                    let _ = w.eval("window.location.reload()");
                }
            }
            "devtools" => {
                if let Some(w) = app.get_webview_window(MAIN_WINDOW) {
                    w.open_devtools();
                } else if let Some(w) = app.get_webview_window(SETUP_WINDOW) {
                    w.open_devtools();
                }
            }
            _ => {}
        })
        .run(tauri::generate_context!())
        .expect("error while running Orangescrum desktop");
}
