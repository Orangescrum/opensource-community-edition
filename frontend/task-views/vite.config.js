import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// Builds into webroot/dist/task-views so the CakePHP template can load the
// bundle with a plain <script>. Kept out of PLUGIN_REGISTRY because tasks are
// core, not a plugin.
export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./src", import.meta.url)),
        },
    },
    base: "/dist/task-views/",
    build: {
        outDir: fileURLToPath(new URL("../../webroot/dist/task-views", import.meta.url)),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: fileURLToPath(new URL("./src/main.js", import.meta.url)),
            output: {
                entryFileNames: "task-views.js",
                chunkFileNames: "task-views-[name].js",
                assetFileNames: "task-views-[name][extname]",
                manualChunks(id) {
                    if (id.includes("node_modules/vuetify")) return "vuetify";
                    if (id.includes("node_modules/vue") || id.includes("node_modules/pinia")) return "vue";
                    return undefined;
                },
            },
        },
    },
    server: {
        port: 5182,
        // The font faces are declared in webroot/css/typography.css and served
        // by the app, not bundled here. Proxy them so the standalone dev
        // harness renders in the real typeface.
        proxy: {
            "/font": { target: "http://oss.localhost:8091", changeOrigin: true },
            "/css/typography.css": { target: "http://oss.localhost:8091", changeOrigin: true },
        },
    },
});
