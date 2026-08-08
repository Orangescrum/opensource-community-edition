import fs from "fs";
import { createRequire } from "module";
import { resolve } from "path";
import { pathToFileURL } from "url";

function normalizeBool(value, defaultValue) {
    if (value === undefined || value === null || value === "") {
        return defaultValue;
    }

    const normalized = String(value).toLowerCase();
    return normalized === "1" || normalized === "true" || normalized === "yes";
}

function readEnvValue(...keys) {
    for (const key of keys) {
        const value = process.env[key];
        if (value !== undefined && value !== null && value !== "") {
            return value;
        }
    }
    return undefined;
}

/**
 * Per-plugin Vite settings. Single source of truth for slug, port, entry,
 * output prefix, and chunking. Add a new plugin here and it slots into the
 * Caddy proxy + sidecar pattern automatically.
 *
 * Slug convention: kebab-case, matches the Caddy path segment under /__vite/.
 */
export const PLUGIN_REGISTRY = {
};

/**
 * Caddy-fronted dev server: every plugin reaches the browser through
 *   {VITE_PROXY_PROTOCOL}://{VITE_PROXY_HOST}/__vite/<slug>/...
 * and Caddy reverse-proxies that path to vite-<slug>:<port> on the docker net.
 * Sidecar containers set USE_PROXY=1 so origin is omitted and HMR uses the
 * proxy's host/port.
 */
function readProxySettings() {
    const protocol = readEnvValue("VITE_PROXY_PROTOCOL") || "https";
    const dev = readEnvValue("DEV_DOMAIN") || "localhost";
    const host = readEnvValue("VITE_PROXY_HOST") || `v4.${dev}`;
    const portRaw = readEnvValue("VITE_PROXY_PORT");
    const port = portRaw ? parseInt(portRaw, 10) : protocol === "https" ? 443 : 80;
    return { protocol, host, port };
}

export function pluginProxyBasePath(slug) {
    return `/__vite/${slug}/`;
}

export function buildManualChunks(options = {}) {
    const {
        includeVuetify = true,
        includeVueRouter = true,
        includeMdi = true,
        includeAxios = true,
    } = options;

    // Match a package by its directory boundary so `node_modules/vue` doesn't
    // also swallow `vue-router`, `vuetify`, `vuedraggable`, etc. Substring
    // matching produced a Rollup circular-chunk warning (vendor -> vue-vendor
    // -> vendor) and shifted half of Vuetify into the wrong chunk, breaking
    // component rendering in the built bundle.
    const pkg = (id, name) =>
        id.includes(`node_modules/${name}/`) ||
        id.includes(`node_modules/${name}\\`); // win32 path-sep safety

    return (id) => {
        if (includeVuetify && pkg(id, "vuetify")) {
            return "vuetify";
        }

        if (includeVueRouter && pkg(id, "vue-router")) {
            return "vue-router";
        }

        if (pkg(id, "vue") || id.includes("node_modules/@vue/")) {
            return "vue-vendor";
        }

        if (includeMdi && id.includes("node_modules/@mdi/")) {
            return "mdi";
        }

        if (includeAxios && pkg(id, "axios")) {
            return "axios";
        }

        if (id.includes("node_modules/")) {
            return "vendor";
        }

        return undefined;
    };
}

export function buildViteServerConfig(options = {}) {
    const {
        envPrefix,
        defaultHost = "0.0.0.0",
        defaultDomain = "localhost",
        defaultProtocol = "https",
        defaultPort = 5173,
        defaultHttps = true,
        slug,
    } = options;

    const prefix = envPrefix ? `${envPrefix}_VITE_` : "";

    const host = readEnvValue("VITE_HOST", `${prefix}HOST`) || defaultHost;
    const domain = readEnvValue("VITE_DOMAIN", `${prefix}DOMAIN`) || defaultDomain;
    const protocol = readEnvValue("VITE_PROTOCOL", `${prefix}PROTOCOL`) || defaultProtocol;
    const port = parseInt(readEnvValue("VITE_PORT", `${prefix}PORT`) || String(defaultPort), 10);
    const strictPort = normalizeBool(
        readEnvValue("VITE_STRICT_PORT", `${prefix}STRICT_PORT`),
        false
    );

    const httpsEnabled = normalizeBool(
        readEnvValue("VITE_HTTPS", `${prefix}HTTPS`),
        defaultHttps
    );
    const useProxy = normalizeBool(
        readEnvValue("VITE_USE_PROXY", `${prefix}USE_PROXY`),
        false
    );

    const certKeyPath = readEnvValue("VITE_HTTPS_KEY", `${prefix}HTTPS_KEY`);
    const certPath = readEnvValue("VITE_HTTPS_CERT", `${prefix}HTTPS_CERT`);

    const hasCerts = certKeyPath && certPath && fs.existsSync(certKeyPath) && fs.existsSync(certPath);
    const useHttps = httpsEnabled && hasCerts && !useProxy;

    if (useProxy) {
        const proxy = readProxySettings();
        return {
            host,
            port,
            strictPort: true,
            allowedHosts: true,
            cors: true,
            watch: { ignored: defaultWatchIgnored() },
            hmr: {
                protocol: proxy.protocol === "https" ? "wss" : "ws",
                host: proxy.host,
                clientPort: proxy.port,
                ...(slug ? { path: pluginProxyBasePath(slug) } : {}),
            },
        };
    }

    return {
        host,
        port,
        strictPort,
        allowedHosts: [domain, host, "localhost", "127.0.0.1"],
        cors: true,
        watch: { ignored: defaultWatchIgnored() },
        ...(useHttps
            ? {
                  https: {
                      key: fs.readFileSync(certKeyPath),
                      cert: fs.readFileSync(certPath),
                  },
              }
            : {}),
        origin: `${protocol}://${domain}:${port}`,
    };
}

function defaultWatchIgnored() {
    return [
        "**/.git/**",
        "**/node_modules/**",
        "**/vendor/**",
        "**/webroot/js/**",
        "**/src/**",
        "**/config/**",
        "**/tests/**",
        "**/logs/**",
        "**/tmp/**",
        "**/*.php",
    ];
}

export function createPluginViteConfig(options) {
    const {
        pluginDir,
        basePath,
        devBasePath = "/",
        aliasPath,
        entryFile,
        outputPrefix,
        server = {},
        manualChunks = {},
    } = options;

    return {
        root: pluginDir,
        appType: "custom",
        base: process.env.NODE_ENV === "production" ? `/${basePath}/js/` : devBasePath,
        optimizeDeps: {
            entries: [resolve(pluginDir, entryFile)],
        },
        resolve: {
            alias: {
                "@": resolve(pluginDir, aliasPath),
            },
        },
        build: {
            outDir: resolve(pluginDir, "webroot", "js"),
            emptyOutDir: true,
            rollupOptions: {
                input: {
                    app: resolve(pluginDir, entryFile),
                },
                output: {
                    entryFileNames: `${outputPrefix}-app.js`,
                    chunkFileNames: `${outputPrefix}-[name]-[hash].js`,
                    assetFileNames: `${outputPrefix}-[name].[ext]`,
                    manualChunks: buildManualChunks(manualChunks),
                },
            },
            chunkSizeWarningLimit: 1000,
        },
        server: buildViteServerConfig(server),
    };
}

/**
 * Build a Vite config from the registry. The per-plugin vite.config.js becomes
 * a 3-line delegator that calls this with its slug + __dirname.
 *
 * Honours VITE_USE_PROXY=1 to put the dev server behind Caddy: dev base becomes
 * `/__vite/<slug>/`, HMR points at the proxy host/port, allowedHosts wide-open.
 */
export function createPluginConfigFromRegistry(slug, pluginDir) {
    const settings = PLUGIN_REGISTRY[slug];
    if (!settings) {
        const supported = Object.keys(PLUGIN_REGISTRY).join(", ");
        throw new Error(`Unknown plugin slug '${slug}'. Registered: ${supported}`);
    }

    const useProxy = normalizeBool(readEnvValue("VITE_USE_PROXY"), false);
    const devBasePath = useProxy ? pluginProxyBasePath(slug) : "/";
    const envPrefix = settings.plugin.toUpperCase();

    return createPluginViteConfig({
        pluginDir,
        basePath: settings.prodBasePath,
        devBasePath,
        aliasPath: settings.aliasPath,
        entryFile: settings.entryFile,
        outputPrefix: settings.outputPrefix,
        server: {
            envPrefix,
            defaultPort: settings.defaultPort,
            slug,
        },
        manualChunks: settings.manualChunks,
    });
}

export async function loadVuePluginFrom(pluginDir) {
    const require = createRequire(import.meta.url);
    const vuePluginPath = require.resolve("@vitejs/plugin-vue", {
        paths: [pluginDir],
    });
    const vuePluginModule = await import(pathToFileURL(vuePluginPath).href);
    return vuePluginModule.default;
}
