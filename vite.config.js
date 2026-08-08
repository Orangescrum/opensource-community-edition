import { resolve } from "path";
import { fileURLToPath } from "url";
import { createPluginViteConfig, loadVuePluginFrom } from "./config/vite.shared.js";

const pluginRegistry = {
    dashboard: {
        dir: ".",
        basePath: "js/dashboard",
        devBasePath: "/",
        aliasPath: "templates/element/dashboard",
        entryFile: "templates/element/dashboard/main.js",
        outputPrefix: "dashboard",
        server: {
            envPrefix: "DASHBOARD",
            defaultPort: 5178,
            defaultProtocol: "http",
            defaultHttps: false,
        },
        manualChunks: {
            includeVuetify: true,
            includeVueRouter: false,
            includeMdi: true,
            includeAxios: true,
        },
    },
};

function resolveTargetPlugin() {
    const target = (process.env.VITE_PLUGIN || "dashboard").toLowerCase();
    const settings = pluginRegistry[target];

    if (!settings) {
        const supported = Object.keys(pluginRegistry).join(", ");
        throw new Error(`Unknown VITE_PLUGIN '${target}'. Supported values: ${supported}`);
    }

    return settings;
}

export default async () => {
    const selected = resolveTargetPlugin();
    const configDir = resolve(fileURLToPath(new URL(".", import.meta.url)));
    const pluginDir = resolve(configDir, selected.dir);
    const vue = await loadVuePluginFrom(pluginDir);

    const config = createPluginViteConfig({
        pluginDir,
        basePath: selected.basePath,
        devBasePath: selected.devBasePath,
        aliasPath: selected.aliasPath,
        entryFile: selected.entryFile,
        outputPrefix: selected.outputPrefix,
        server: selected.server,
        manualChunks: selected.manualChunks,
    });

    // Override for main app (not a plugin)
    config.base = "/js/dashboard/";
    config.build.outDir = resolve(pluginDir, "webroot", "js", "dashboard");

    return {
        plugins: [vue()],
        ...config,
    };
};
