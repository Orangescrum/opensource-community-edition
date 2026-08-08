import "@/styles/vuetify-no-utilities.scss";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import "@mdi/font/css/materialdesignicons.css";

/**
 * Vuetify is here for behaviour — menus, overlays, focus trapping — not for
 * its look. Colours mirror tokens.css so anything Vuetify does paint agrees
 * with the rest of the page.
 */

/**
 * The app's active theme sets --primary on :root (custom_theme-*.css) and its
 * own primary buttons paint from it. Vuetify's theme wants a literal, so read
 * the value once at startup rather than hard-coding orange — otherwise this
 * app stays orange while the rest of the page follows the chosen theme.
 */
function appThemeColor(name, fallback) {
    if (typeof window === "undefined") return fallback;
    const value = getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();
    return value || fallback;
}
export default createVuetify({
    components,
    directives,
    defaults: {
        VMenu: { transition: "fade-transition" },
    },
    theme: {
        defaultTheme: "light",
        themes: {
            light: {
                colors: {
                    primary: appThemeColor("--primary", "#e2600d"),
                    surface: "#ffffff",
                    background: "#ffffff",
                    error: "#c4453c",
                    info: "#3d7edb",
                    success: "#2f8f5b",
                    warning: "#c77800",
                },
            },
        },
    },
});
