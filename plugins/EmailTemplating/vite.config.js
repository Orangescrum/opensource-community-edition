import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

export default defineConfig({
    plugins: [vue()],
    base: process.env.NODE_ENV === "production" ? "/email_templating/js/" : "/",
    resolve: {
        alias: {
            "@": resolve(__dirname, "templates/element/email_templating/components"),
        },
    },
    build: {
        outDir: "webroot/js",
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: resolve(
                    __dirname,
                    "templates/element/email_templating/components/main.js"
                ),
            },
            output: {
                entryFileNames: "email-templating-app.js",
                chunkFileNames: "email-templating-[name]-[hash].js",
                assetFileNames: "email-templating-[name].[ext]",
                manualChunks: (id) => {
                    if (id.includes("node_modules/vuetify")) {
                        return "vuetify";
                    }
                    if (
                        id.includes("node_modules/vue") ||
                        id.includes("node_modules/@vue")
                    ) {
                        return "vue-vendor";
                    }
                    if (id.includes("node_modules/@mdi")) {
                        return "mdi";
                    }
                    if (id.includes("node_modules/axios")) {
                        return "axios";
                    }
                    if (id.includes("node_modules")) {
                        return "vendor";
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
});
