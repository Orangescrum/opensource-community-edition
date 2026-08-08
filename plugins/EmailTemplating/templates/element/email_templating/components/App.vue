<template>
    <v-app class="emt-embedded-app">
        <v-main>
            <v-container fluid class="pa-0">
                <email-templates-list v-if="currentView === 'list'" @edit="openEdit" @common="openCommon"
                    @email-config="openEmailConfig" @help="openHelp"></email-templates-list>
                <email-template-edit v-else-if="currentView === 'edit'" :template-key="activeKey" @back="goList"
                    @help="openHelp" @tokens="openTokens"></email-template-edit>
                <common-settings v-else-if="currentView === 'common'" @back="goList" @help="openHelp"></common-settings>
                <email-config v-else-if="currentView === 'email_config'" @back="goList"></email-config>
                <email-template-help v-else-if="currentView === 'help'" :return-to="helpReturnTo"
                    @back="closeHelp" @tokens="openTokens"></email-template-help>
                <email-token-reference v-else-if="currentView === 'tokens'" :return-to="tokensReturnTo"
                    @back="closeTokens"></email-token-reference>
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import EmailTemplatesList from "./views/EmailTemplatesList.vue";
import EmailTemplateEdit from "./views/EmailTemplateEdit.vue";
import CommonSettings from "./views/CommonSettings.vue";
import EmailConfig from "./views/EmailConfig.vue";
import EmailTemplateHelp from "./views/EmailTemplateHelp.vue";
import EmailTokenReference from "./views/EmailTokenReference.vue";

const config = window.EMAIL_TEMPLATING_CONFIG || {};
const activeKey = ref(config.initialKey || null);
const showCommon = ref(false);
const showEmailConfig = ref(false);
const showHelp = ref(false);
const showTokens = ref(false);
/**
 * Where the user was before they opened Help / Tokens. Drives the back-link
 * label and destination so closing the overlay restores the original view.
 * Shape: { view: 'list' | 'common' | 'edit' | 'help', key: string | null, label: string }
 */
const helpReturnTo = ref(null);
const tokensReturnTo = ref(null);

const currentView = computed(() => {
    if (showTokens.value) return "tokens";
    if (showHelp.value) return "help";
    if (showEmailConfig.value) return "email_config";
    if (activeKey.value) return "edit";
    if (showCommon.value) return "common";
    return "list";
});

const baseTitle = typeof document !== "undefined" ? document.title : "";

watch(
    [currentView, activeKey],
    ([view, key]) => {
        if (typeof document === "undefined") return;
        let prefix = "Email Templates";
        if (view === "common") prefix = "Common Settings — Email Templates";
        else if (view === "email_config") prefix = "Email Configuration — Email Templates";
        else if (view === "edit" && key) prefix = `${key} — Email Templates`;
        else if (view === "help") prefix = "Help — Email Templates";
        else if (view === "tokens") prefix = "Dynamic keywords reference — Email Templates";
        document.title = baseTitle ? `${prefix} | ${baseTitle.split(" | ").pop()}` : prefix;
    },
    { immediate: true }
);

function openEdit(key) {
    activeKey.value = key;
    showCommon.value = false;
    showEmailConfig.value = false;
    showHelp.value = false;
    showTokens.value = false;
    window.history.pushState({ key }, "", `?key=${encodeURIComponent(key)}`);
}

function openCommon() {
    showCommon.value = true;
    activeKey.value = null;
    showEmailConfig.value = false;
    showHelp.value = false;
    showTokens.value = false;
    window.history.pushState({ view: "common" }, "", "?view=common");
}

function openEmailConfig() {
    showEmailConfig.value = true;
    activeKey.value = null;
    showCommon.value = false;
    showHelp.value = false;
    showTokens.value = false;
    window.history.pushState({ view: "email_config" }, "", "?view=email_config");
}

function snapshotCurrent(opts) {
    // Capture the current view as a return-to descriptor.
    if (activeKey.value) {
        return { view: "edit", key: activeKey.value, label: opts.editLabel };
    }
    if (showCommon.value) {
        return { view: "common", key: null, label: opts.commonLabel };
    }
    if (showHelp.value) {
        return { view: "help", key: null, label: opts.helpLabel };
    }
    return { view: "list", key: null, label: opts.listLabel };
}

function openHelp() {
    helpReturnTo.value = snapshotCurrent({
        editLabel: "Back to template",
        commonLabel: "Back to common settings",
        helpLabel: "Back to help",
        listLabel: "Back to list",
    });
    showHelp.value = true;
    activeKey.value = null;
    showCommon.value = false;
    showEmailConfig.value = false;
    showTokens.value = false;
    window.history.pushState({ view: "help" }, "", "?view=help");
}

function closeHelp() {
    const to = helpReturnTo.value;
    showHelp.value = false;
    restoreReturnTo(to);
    helpReturnTo.value = null;
}

function openTokens() {
    tokensReturnTo.value = snapshotCurrent({
        editLabel: "Back to template",
        commonLabel: "Back to common settings",
        helpLabel: "Back to help",
        listLabel: "Back to list",
    });
    showTokens.value = true;
    showHelp.value = false;
    activeKey.value = null;
    showCommon.value = false;
    showEmailConfig.value = false;
    window.history.pushState({ view: "tokens" }, "", "?view=tokens");
}

function closeTokens() {
    const to = tokensReturnTo.value;
    showTokens.value = false;
    restoreReturnTo(to);
    tokensReturnTo.value = null;
}

function restoreReturnTo(to) {
    if (to?.view === "edit" && to.key) {
        activeKey.value = to.key;
        showCommon.value = false;
        showEmailConfig.value = false;
        showHelp.value = false;
        window.history.pushState({ key: to.key }, "", `?key=${encodeURIComponent(to.key)}`);
    } else if (to?.view === "common") {
        activeKey.value = null;
        showCommon.value = true;
        showEmailConfig.value = false;
        showHelp.value = false;
        window.history.pushState({ view: "common" }, "", "?view=common");
    } else if (to?.view === "help") {
        activeKey.value = null;
        showCommon.value = false;
        showEmailConfig.value = false;
        showHelp.value = true;
        window.history.pushState({ view: "help" }, "", "?view=help");
    } else {
        activeKey.value = null;
        showCommon.value = false;
        showEmailConfig.value = false;
        showHelp.value = false;
        window.history.pushState({}, "", window.location.pathname);
    }
}

function goList() {
    activeKey.value = null;
    showCommon.value = false;
    showEmailConfig.value = false;
    showHelp.value = false;
    showTokens.value = false;
    helpReturnTo.value = null;
    tokensReturnTo.value = null;
    window.history.pushState({}, "", window.location.pathname);
}

function syncFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const key = params.get("key");
    const view = params.get("view");
    activeKey.value = key || null;
    showCommon.value = view === "common";
    showEmailConfig.value = view === "email_config" || view === "smtp";
    showHelp.value = view === "help";
    showTokens.value = view === "tokens";
    // Deep-linked into help/tokens without a tracked origin (page load, popstate)
    // → fall back to "Back to list" so the back link always works.
    if (showHelp.value && !helpReturnTo.value) {
        helpReturnTo.value = { view: "list", key: null, label: "Back to list" };
    }
    if (showTokens.value && !tokensReturnTo.value) {
        tokensReturnTo.value = { view: "list", key: null, label: "Back to list" };
    }
}

onMounted(() => {
    syncFromUrl();
    window.addEventListener("popstate", syncFromUrl);
});
</script>

<style>
#emailTemplatingApp {
    all: initial;
    display: block;
    width: 100%;
    font-family: 'Inter', sans-serif;
    /* Prevent horizontal layout shift when content height varies enough to
     * toggle the page scrollbar between templates / form states. */
    scrollbar-gutter: stable;
    /* Host wrapper `.task_listing` adds 10px padding-top. Our sticky header
     * sticks to the scroll container's top (above that padding), so without
     * this compensation the header snaps up 10px the moment the user scrolls.
     * Eat the host padding here so natural and stuck positions coincide. */
    margin-top: -10px;
}

#emailTemplatingApp *,
#emailTemplatingApp *::before,
#emailTemplatingApp *::after {
    box-sizing: border-box;
}

#emailTemplatingApp .emt-embedded-app {
    background: transparent !important;
    position: static !important;
    font-family: 'Inter', sans-serif !important;
}

#emailTemplatingApp .v-application__wrap {
    min-height: auto !important;
}

#emailTemplatingApp .v-main {
    padding: 0 !important;
}

#emailTemplatingApp .v-btn {
    text-transform: none !important;
    letter-spacing: 0 !important;
    font-weight: 500 !important;
}

#emailTemplatingApp .v-icon {
    font-family: "Material Design Icons" !important;
}

/* Override Vuetify's bundled Roboto with the host app's Inter font.
 * Scoped to the Vuetify component surfaces and utility classes so
 * monospace declarations on code / .tpl-key / .emt-row-key / .token-code
 * still win the cascade. */
#emailTemplatingApp .v-application,
#emailTemplatingApp .v-btn,
#emailTemplatingApp .v-input,
#emailTemplatingApp .v-field,
#emailTemplatingApp .v-label,
#emailTemplatingApp .v-list,
#emailTemplatingApp .v-list-item,
#emailTemplatingApp .v-card,
#emailTemplatingApp .v-chip,
#emailTemplatingApp .v-table,
#emailTemplatingApp .v-snackbar,
#emailTemplatingApp .v-overlay,
#emailTemplatingApp .text-h1,
#emailTemplatingApp .text-h2,
#emailTemplatingApp .text-h3,
#emailTemplatingApp .text-h4,
#emailTemplatingApp .text-h5,
#emailTemplatingApp .text-h6,
#emailTemplatingApp .text-subtitle-1,
#emailTemplatingApp .text-subtitle-2,
#emailTemplatingApp .text-body-1,
#emailTemplatingApp .text-body-2,
#emailTemplatingApp .text-button,
#emailTemplatingApp .text-caption,
#emailTemplatingApp .text-overline,
#emailTemplatingApp button,
#emailTemplatingApp input,
#emailTemplatingApp select,
#emailTemplatingApp textarea {
    font-family: 'Inter', sans-serif !important;
}

/* v-menu / v-dialog / v-snackbar teleport outside #emailTemplatingApp;
 * reach them at the body level. */
.v-overlay__content,
.v-overlay__content .v-list,
.v-overlay__content .v-list-item,
.v-snackbar__content {
    font-family: 'Inter', sans-serif !important;
}

#emailTemplatingApp h1,
#emailTemplatingApp h2,
#emailTemplatingApp h3,
#emailTemplatingApp h4,
#emailTemplatingApp h5,
#emailTemplatingApp h6 {
    margin: 0;
    font-family: 'Inter', sans-serif !important;
}
</style>
