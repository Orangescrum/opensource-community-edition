<template>
    <div class="emt-edit">
        <v-progress-linear v-if="loading" indeterminate color="primary" />

        <div v-else>
            <div class="emt-header " >
                <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$emit('back')" class="back-btn">
                    Back to list
                </v-btn>
                <div class="emt-header-row">
                    <div class="emt-header-text">
                        <h2 class="emt-title">Common settings</h2>
                        <p class="emt-subtitle"
                            title="These defaults apply to every template; any field can still be overridden in an individual template's editor.">
                            <code>common-settings</code> — Defaults applied to every template that hasn't been
                            individually customized.
                        </p>
                    </div>
                    <div class="emt-actions">
                        <v-btn variant="text" :disabled="!isDirty" @click="resetForm">Discard</v-btn>
                        <v-btn variant="text" @click="$emit('back')">Cancel</v-btn>
                        <v-btn color="primary" variant="flat" :loading="saving" @click="save">Save</v-btn>
                        <v-menu location="bottom end">
                            <template #activator="{ props: menuProps }">
                                <v-btn v-bind="menuProps" variant="text" icon="mdi-dots-vertical" size="small"
                                    aria-label="More actions" />
                            </template>
                            <v-list density="compact">
                                <v-list-item prepend-icon="mdi-backup-restore" :disabled="!hasSavedRow"
                                    @click="resetDialog = true">
                                    <v-list-item-title>Reset to shipped defaults</v-list-item-title>
                                </v-list-item>
                                <v-list-item prepend-icon="mdi-help-circle-outline" @click="$emit('help')">
                                    <v-list-item-title>Help &amp; reference</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </div>

            <v-alert v-if="error" type="error" closable class="mb-4" @click:close="error = null">{{ error }}</v-alert>
            <v-alert v-if="savedAt" type="success" closable class="mb-4" @click:close="savedAt = null">
                Saved at {{ savedAt }}
            </v-alert>

            <v-row class="emt-body-row">
                <v-col cols="12" md="8" class="emt-form-col">
                    <div class="region-field mb-4">
                        <v-text-field v-model="form.sender_name" label="Sender name (display name on outgoing emails)"
                            placeholder="Acme Notifications" variant="outlined" density="compact" hide-details />
                    </div>

                    <div class="signoff-section mb-4">
                        <div class="signoff-label">Sign-off</div>
                        <div class="signoff-hint">Composed automatically — appears at the end of every email.</div>
                        <div class="signoff-fields">
                            <v-text-field v-model="form.signoff_greeting" label="Greeting"
                                placeholder="Thanks &amp; Regards" variant="outlined" density="compact" hide-details
                                class="mb-3" />
                            <v-text-field v-model="form.signoff_team" label="Team name (bold)"
                                placeholder="The Acme Team" variant="outlined" density="compact" hide-details
                                class="mb-3" />
                            <v-text-field v-model="form.signoff_tagline" label="Tagline (optional, smaller text)"
                                placeholder="Built with care · acme.example.com" variant="outlined"
                                density="compact" hide-details />
                        </div>
                        <div v-if="signoffHasContent" class="signoff-preview">
                            <template v-if="form.signoff_greeting">
                                {{ form.signoff_greeting }},<br>
                            </template>
                            <strong v-if="form.signoff_team">{{ form.signoff_team }}</strong>
                            <template v-if="form.signoff_tagline">
                                <br><span class="signoff-preview-tagline">{{ form.signoff_tagline }}</span>
                            </template>
                        </div>
                    </div>

                    <div class="region-field mb-4">
                        <div class="d-flex align-center" style="gap:12px;">
                            <v-text-field v-model="form.brand_color" label="Brand color"
                                :placeholder="defaults.brand_color" variant="outlined" density="compact"
                                hide-details style="max-width:200px;" />
                            <input type="color" :value="form.brand_color || defaults.brand_color"
                                @input="form.brand_color = $event.target.value" class="color-swatch" />
                            <span class="text-caption text-medium-emphasis">Header bar &amp; CTA button color across all
                                templates.</span>
                        </div>
                    </div>

                    <div class="region-field mb-4">
                        <v-text-field v-model="form.logo_url" label="Logo URL (optional)"
                            placeholder="https://acme.example.com/logo.png" variant="outlined" density="compact"
                            hint="Absolute https:// URL. Used in email headers where supported." persistent-hint />
                    </div>

                    <div class="region-field mb-4">
                        <v-switch v-model="form.include_header"
                            label="Include the Email Header template above every email" color="primary"
                            density="compact" hide-details class="emt-switch" />
                        <div class="emt-hint">Edit the content under <strong>Layout → Email Header</strong> in the
                            template list.
                        </div>
                    </div>

                    <div class="region-field mb-4">
                        <v-switch v-model="form.include_footer"
                            label="Include the Email Footer template below every email" color="primary"
                            density="compact" hide-details class="emt-switch" />
                        <div class="emt-hint">Edit the content under <strong>Layout → Email Footer</strong> in the
                            template list.
                        </div>
                    </div>
                </v-col>

                <v-col cols="12" md="4" class="emt-tokens-col">
                    <v-card border flat class="tokens-card">
                        <v-card-title class="tokens-title">How these apply</v-card-title>
                        <v-card-subtitle>
                            Resolution order at send time
                        </v-card-subtitle>
                        <v-divider />
                        <div class="how-list">
                            <div class="how-item">
                                <div class="how-step">1</div>
                                <div>
                                    <strong>Per-template override</strong>
                                    <div class="how-hint">If a field is changed inside a specific template's editor, it
                                        wins there.
                                    </div>
                                </div>
                            </div>
                            <div class="how-item">
                                <div class="how-step">2</div>
                                <div>
                                    <strong>Common settings</strong>
                                    <div class="how-hint">The values on this page — applied to every template that
                                        hasn't been
                                        customized.</div>
                                </div>
                            </div>
                            <div class="how-item">
                                <div class="how-step">3</div>
                                <div>
                                    <strong>Shipped defaults</strong>
                                    <div class="how-hint">Factory values used when neither of the above is set.</div>
                                </div>
                            </div>
                        </div>
                    </v-card>

                    <v-card class="pa-3 mt-4" variant="outlined">
                        <div class="text-caption text-medium-emphasis mb-2">Preview</div>
                        <div :style="{ background: form.brand_color || defaults.brand_color, color: '#fff', padding: '12px 16px', fontWeight: 600 }">
                            New task created
                        </div>
                        <div style="padding:16px;border:1px solid #e5e7eb;border-top:0;background:#fff;font-size:13px;line-height:1.5;">
                            <div>Hi [Recipient name],</div>
                            <div style="margin:12px 0;">[Sender name] posted a new task. Review the details in the app.</div>
                            <div v-html="previewSignoffHtml" style="margin-top:24px;padding-top:12px;border-top:1px solid #eef1f5;"></div>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </div>

        <v-dialog v-model="resetDialog" max-width="460">
            <v-card>
                <v-card-title>Reset common settings?</v-card-title>
                <v-card-text>
                    Your sender name, sign-off, brand color, and logo will be deleted.
                    Every template that hasn't been individually customized will fall back
                    to the shipped defaults.
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="resetDialog = false">Cancel</v-btn>
                    <v-btn color="error" variant="flat" :loading="resetting" @click="doReset">Reset</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snack" :timeout="2200">{{ snackText }}</v-snackbar>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { composeSignoff, parseSignoff } from "../signoff.js";
import { useCommonSettings } from "../store/settings.js";

defineEmits(["back", "help"]);

const config = window.EMAIL_TEMPLATING_CONFIG || {};

const {
    settings: commonSettings,
    defaults: commonDefaults,
    load: loadCommonSettings,
    save: saveCommonSettings,
    reset: resetCommonSettings,
} = useCommonSettings(config.apiBaseUrl);

const defaults = commonDefaults;

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const savedAt = ref(null);
const resetDialog = ref(false);
const resetting = ref(false);
const hasSavedRow = ref(false);

const form = reactive({
    sender_name: "",
    signoff_greeting: "",
    signoff_team: "",
    signoff_tagline: "",
    brand_color: "",
    logo_url: "",
    include_header: false,
    include_footer: false,
});
const initial = reactive({
    sender_name: "",
    signoff_greeting: "",
    signoff_team: "",
    signoff_tagline: "",
    brand_color: "",
    logo_url: "",
    include_header: false,
    include_footer: false,
});

const snack = ref(false);
const snackText = ref("");

const isDirty = computed(() =>
    form.sender_name !== initial.sender_name ||
    form.signoff_greeting !== initial.signoff_greeting ||
    form.signoff_team !== initial.signoff_team ||
    form.signoff_tagline !== initial.signoff_tagline ||
    form.brand_color !== initial.brand_color ||
    form.logo_url !== initial.logo_url ||
    form.include_header !== initial.include_header ||
    form.include_footer !== initial.include_footer
);

const signoffHasContent = computed(() =>
    !!(form.signoff_greeting || form.signoff_team || form.signoff_tagline)
);

const previewSignoffHtml = computed(() => {
    const composed = composeSignoff({
        greeting: form.signoff_greeting,
        team: form.signoff_team,
        tagline: form.signoff_tagline,
    });
    return composed || defaults.value.sender_signoff || "";
});

function syncFormFromStore() {
    const s = commonSettings.value || {};
    hasSavedRow.value = !!(s.sender_name || s.sender_signoff || s.brand_color || s.logo_url
        || s.include_header || s.include_footer);

    const defaultParsed = parseSignoff(defaults.value.sender_signoff || "");
    const savedParsed = parseSignoff(s.sender_signoff || "");

    form.sender_name = s.sender_name || "";
    form.brand_color = s.brand_color || defaults.value.brand_color || "";
    form.logo_url = s.logo_url || "";
    form.include_header = !!s.include_header;
    form.include_footer = !!s.include_footer;
    form.signoff_greeting = s.sender_signoff ? savedParsed.greeting : defaultParsed.greeting;
    form.signoff_team = s.sender_signoff ? savedParsed.team : defaultParsed.team;
    form.signoff_tagline = s.sender_signoff ? savedParsed.tagline : defaultParsed.tagline;

    Object.assign(initial, {
        sender_name: form.sender_name,
        signoff_greeting: form.signoff_greeting,
        signoff_team: form.signoff_team,
        signoff_tagline: form.signoff_tagline,
        brand_color: form.brand_color,
        logo_url: form.logo_url,
        include_header: form.include_header,
        include_footer: form.include_footer,
    });
}

async function load() {
    loading.value = true;
    try {
        await loadCommonSettings(true);
        syncFormFromStore();
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Failed to load common settings";
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = null;
    try {
        await saveCommonSettings({
            sender_name: form.sender_name,
            sender_signoff: composeSignoff({
                greeting: form.signoff_greeting,
                team: form.signoff_team,
                tagline: form.signoff_tagline,
            }),
            brand_color: form.brand_color,
            logo_url: form.logo_url,
            include_header: form.include_header,
            include_footer: form.include_footer,
        });
        syncFormFromStore();
        savedAt.value = new Date().toLocaleTimeString();
        snackText.value = "Common settings saved";
        snack.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Save failed";
    } finally {
        saving.value = false;
    }
}

async function doReset() {
    resetting.value = true;
    error.value = null;
    try {
        await resetCommonSettings();
        resetDialog.value = false;
        snackText.value = "Reset to shipped defaults";
        snack.value = true;
        savedAt.value = null;
        syncFormFromStore();
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Reset failed";
    } finally {
        resetting.value = false;
    }
}

function resetForm() {
    form.sender_name = initial.sender_name;
    form.signoff_greeting = initial.signoff_greeting;
    form.signoff_team = initial.signoff_team;
    form.signoff_tagline = initial.signoff_tagline;
    form.brand_color = initial.brand_color;
    form.logo_url = initial.logo_url;
    form.include_header = initial.include_header;
    form.include_footer = initial.include_footer;
    snackText.value = "Changes discarded";
    snack.value = true;
}

onMounted(load);
</script>

<style scoped>
/* Mirror EmailTemplateEdit.vue so the UX is identical. */
.emt-edit {
    padding: 0 4px;
    scrollbar-gutter: stable;
}

.emt-header {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #ffffff;
    margin: 0 -4px 16px -4px;
    padding: 12px 12px 14px 12px;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    min-height: 78px;
    box-sizing: border-box;
}

.emt-header-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.emt-header-text {
    flex: 1;
    min-width: 0;
}

.emt-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px !important;
    letter-spacing: -0.01em;
}

.emt-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.emt-subtitle code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 11px;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 3px;
}

.back-btn {
    margin-left: -8px;
    margin-bottom: 0;
    min-height: 28px !important;
    padding: 0 6px !important;
    font-size: 12px !important;
}

.emt-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
    justify-content: flex-end;
    padding-top: 0;
}

.emt-body-row {
    margin: 0 !important;
}

.emt-form-col {
    padding-right: 16px !important;
    min-width: 0;
}

.emt-tokens-col {
    position: sticky;
    top: 110px;
    align-self: flex-start;
    max-height: calc(100vh - 130px);
}

.tokens-card {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 130px);
    overflow: hidden;
}

.tokens-title {
    font-size: 14px !important;
    flex-shrink: 0;
}

.region-field {
    position: relative;
}

.textarea-wrap {
    position: relative;
}

.color-swatch {
    width: 48px;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    cursor: pointer;
    padding: 0;
    background: transparent;
}

.how-list {
    padding: 12px 16px 16px;
    overflow-y: auto;
}

.how-item {
    display: flex;
    gap: 12px;
    margin-bottom: 14px;
}

.how-item:last-child {
    margin-bottom: 0;
}

.how-step {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #4b5563;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.how-hint {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.5;
    margin-top: 2px;
}

.signoff-section {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 14px 16px;
    background: #fafafa;
}

.signoff-label {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 2px;
}

.signoff-hint {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 12px;
}

.signoff-fields {
    background: #fff;
    border-radius: 4px;
    padding: 12px 12px 0;
}

.signoff-preview {
    margin-top: 14px;
    padding: 14px 16px;
    background: #ffffff;
    border: 1px dashed #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    color: #1A1A2E;
    line-height: 1.5;
}

.signoff-preview-tagline {
    color: #6b7280;
    font-size: 12px;
}

/* Compact switch — tight vertical rhythm so the toggle sits flush with its hint. */
.emt-switch :deep(.v-input__control) {
    min-height: 28px;
}

.emt-switch :deep(.v-selection-control) {
    min-height: 28px;
}

.emt-switch :deep(.v-label) {
    font-size: 13px;
    opacity: 1;
    padding-left: 4px;
}

.emt-switch {
    margin: 4px 0 0 0;
    padding: 0;
}

.emt-hint {
    font-size: 12px;
    color: #6b7280;
    padding: 2px 0 0 48px;
    line-height: 1.4;
}
</style>
