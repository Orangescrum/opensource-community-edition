<template>
    <div class="emt-edit">
        <v-progress-linear v-if="loading" indeterminate color="primary" />

        <div v-else>
            <div class="emt-header">
                <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$emit('back')" class="back-btn">
                    Back to list
                </v-btn>
                <div class="emt-header-row">
                    <div class="emt-header-text">
                        <div class="emt-title-row">
                            <h2 class="emt-title">{{ meta?.label }}</h2>
                            <span v-if="dirtyCount > 0" class="emt-diff-pill"
                                :title="`${dirtyCount} of ${totalEditableFields} fields differ from the shipped default`">
                                <span class="emt-diff-dot"></span>{{ dirtyCount }} of {{ totalEditableFields }} differ
                            </span>
                        </div>
                        <p class="emt-subtitle" :title="meta?.description">
                            <code>{{ templateKey }}</code>
                            <span v-if="meta?.description"> — {{ meta.description }}</span>
                        </p>
                    </div>
                    <div class="emt-actions">
                        <v-btn variant="outlined" prepend-icon="mdi-eye-outline" :loading="previewing"
                            @click="loadPreview">Preview</v-btn>
                        <v-btn variant="outlined" color="primary" prepend-icon="mdi-send-outline"
                            @click="testDialog = true">Send test</v-btn>
                        <v-btn variant="text" @click="$emit('back')">Cancel</v-btn>
                        <v-btn color="primary" variant="flat" :loading="saving" @click="save">Save</v-btn>
                        <v-menu location="bottom end">
                            <template #activator="{ props: menuProps }">
                                <v-btn v-bind="menuProps" variant="text" icon="mdi-dots-vertical" size="small"
                                    aria-label="More actions" />
                            </template>
                            <v-list density="compact">
                                <v-list-item prepend-icon="mdi-restore" :disabled="!hasSavedOverride && !isDirty"
                                    @click="resetDialog = true">
                                    <v-list-item-title>Reset all to defaults</v-list-item-title>
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
                <v-col cols="12" md="7" lg="7" class="emt-form-col">
                    <div class="region-field mb-4">
                        <v-text-field v-model="form.subject" label="Subject" :placeholder="defaultSubjectTemplate"
                            variant="outlined" density="compact" hide-details>
                            <template #append-inner>
                                <v-btn v-if="form.subject !== '' && form.subject !== defaultSubjectTemplate"
                                    icon="mdi-restore" variant="text" size="x-small" @click="resetSubject"
                                    title="Reset subject to default" />
                            </template>
                        </v-text-field>
                    </div>

                    <template v-for="(def, name) in meta?.regions || {}" :key="name">
                        <!-- Special structured editor for the signoff region: same UX as Common settings -->
                        <div v-if="def.type === 'signoff'" class="signoff-section mb-4">
                            <div class="signoff-head">
                                <div>
                                    <div class="signoff-label">{{ def.label || 'Sign-off' }}</div>
                                    <div class="signoff-hint">Composed automatically — appears at the end of this email.
                                    </div>
                                </div>
                                <v-btn v-if="isRegionDirty(name)" icon="mdi-restore" variant="text" size="x-small"
                                    @click="resetRegion(name)" :title="`Reset ${def.label || 'Sign-off'} to default`" />
                            </div>
                            <div class="signoff-fields">
                                <v-text-field :model-value="signoffParts[name]?.greeting || ''"
                                    @update:model-value="updateSignoffPart(name, 'greeting', $event)" label="Greeting"
                                    placeholder="Thanks &amp; Regards" variant="outlined" density="compact" hide-details
                                    class="mb-3" />
                                <v-text-field :model-value="signoffParts[name]?.team || ''"
                                    @update:model-value="updateSignoffPart(name, 'team', $event)"
                                    label="Team name (bold)" placeholder="The Orangescrum Team" variant="outlined"
                                    density="compact" hide-details class="mb-3" />
                                <v-text-field :model-value="signoffParts[name]?.tagline || ''"
                                    @update:model-value="updateSignoffPart(name, 'tagline', $event)"
                                    label="Tagline (optional, smaller text)" placeholder="Built with care"
                                    variant="outlined" density="compact" hide-details />
                            </div>
                            <div v-if="signoffHasContent(name)" class="signoff-preview">
                                <template v-if="signoffParts[name]?.greeting">
                                    {{ signoffParts[name].greeting }},<br>
                                </template>
                                <strong v-if="signoffParts[name]?.team">{{ signoffParts[name].team }}</strong>
                                <template v-if="signoffParts[name]?.tagline">
                                    <br><span class="signoff-preview-tagline">{{ signoffParts[name].tagline }}</span>
                                </template>
                            </div>
                        </div>

                        <!-- Boolean toggle region -->
                        <div v-else-if="def.type === 'switch'" class="region-field mb-4">
                            <v-switch :model-value="form.regions[name] === '1'"
                                @update:model-value="form.regions[name] = $event ? '1' : '0'"
                                :label="def.label || name" color="primary" density="compact" hide-details />
                        </div>

                        <!-- Standard text / textarea regions -->
                        <div v-else class="region-field mb-4">
                            <v-text-field v-if="def.type !== 'textarea'" v-model="form.regions[name]"
                                :label="def.label || name" :placeholder="def.default" variant="outlined"
                                density="compact" hide-details>
                                <template #append-inner>
                                    <v-btn v-if="isRegionDirty(name)" icon="mdi-restore" variant="text" size="x-small"
                                        @click="resetRegion(name)" :title="`Reset ${def.label || name} to default`" />
                                </template>
                            </v-text-field>
                            <div v-else class="textarea-wrap">
                                <v-textarea v-model="form.regions[name]" :label="def.label || name"
                                    :placeholder="def.default" variant="outlined" density="compact" rows="3" auto-grow
                                    hide-details />
                                <v-btn v-if="isRegionDirty(name)" icon="mdi-restore" variant="text" size="x-small"
                                    class="textarea-reset" @click="resetRegion(name)"
                                    :title="`Reset ${def.label || name} to default`" />
                            </div>
                        </div>
                    </template>

                    <v-switch v-model="form.is_enabled" color="primary" label="Enable this override" density="compact"
                        hide-details />
                </v-col>

                <v-col cols="12" md="5" lg="5" class="emt-tokens-col">
                    <v-expansion-panels v-model="expandedPanels" variant="accordion" class="right-panels">
                        <v-expansion-panel value="preview">
                            <v-expansion-panel-title>
                                Live preview
                                <v-progress-circular v-if="previewOpen && livePreviewLoading" indeterminate size="14"
                                    width="2" color="primary" class="ms-2" />
                                <v-spacer />
                                <span class="preview-subject-mini">{{ livePreviewSubject || '—' }}</span>
                            </v-expansion-panel-title>
                            <v-expansion-panel-text class="preview-panel-text">
                                <div class="preview-frame">
                                    <div v-if="livePreviewHtml" class="preview-iframe-wrap">
                                        <iframe :srcdoc="wrappedLivePreviewHtml" sandbox="" referrerpolicy="no-referrer"
                                            tabindex="-1" aria-hidden="true" class="preview-iframe" />
                                        <!-- Inert overlay swallows clicks/forms so the preview can't fire actions -->
                                        <div class="preview-shield" title="Preview is read-only" />
                                    </div>
                                    <div v-else class="preview-empty">Loading preview…</div>
                                </div>
                            </v-expansion-panel-text>
                        </v-expansion-panel>

                        <v-expansion-panel value="tokens">
                            <v-expansion-panel-title>
                                Available dynamic keywords
                                <v-spacer />
                                <span class="token-count">{{ tokenCount }}</span>
                            </v-expansion-panel-title>
                            <v-expansion-panel-text>
                                <div class="text-caption text-medium-emphasis mb-2">
                                    Click to copy. Use as <code>{{ tk('name') }}</code> in any field.
                                </div>
                                <v-list density="compact" class="token-list">
                                    <v-list-item v-for="(info, name) in meta?.tokens || {}" :key="name"
                                        @click="copyToken(name)" class="token-item">
                                        <v-list-item-title>
                                            <code class="token-code">{{ tk(name) }}</code>
                                            <v-chip v-if="info.raw" color="warning" size="x-small" variant="tonal"
                                                class="ms-2">raw</v-chip>
                                        </v-list-item-title>
                                        <v-list-item-subtitle v-if="info.label">{{ info.label }}</v-list-item-subtitle>
                                    </v-list-item>
                                </v-list>
                            </v-expansion-panel-text>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </v-col>
            </v-row>
        </div>

        <v-dialog v-model="previewDialog" max-width="800">
            <v-card>
                <v-card-title>Preview</v-card-title>
                <v-card-subtitle>{{ previewSubject }}</v-card-subtitle>
                <v-divider />
                <v-card-text style="padding:0;">
                    <div v-if="previewHtml" class="preview-iframe-wrap" style="height:520px;">
                        <iframe :srcdoc="wrappedPreviewHtml" sandbox="" referrerpolicy="no-referrer" tabindex="-1"
                            aria-hidden="true" style="width:100%; height:520px; border:0; display:block;" />
                        <div class="preview-shield" title="Preview is read-only" />
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="previewDialog = false">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="testDialog" max-width="440">
            <v-card>
                <v-card-title>Send test email</v-card-title>
                <v-card-text>
                    <v-text-field v-model="testTo" label="Recipient" type="email" variant="outlined"
                        density="compact" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="testDialog = false">Cancel</v-btn>
                    <v-btn color="primary" variant="flat" :loading="testing" @click="sendTest">Send</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="resetDialog" max-width="440">
            <v-card>
                <v-card-title>Reset to default?</v-card-title>
                <v-card-text>
                    All customization for <strong>{{ meta?.label }}</strong> will be discarded
                    and the template will return to the shipped defaults.
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="resetDialog = false">Cancel</v-btn>
                    <v-btn color="error" variant="flat" :loading="resetting" @click="resetAll">Reset</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snack" :timeout="2200">{{ snackText }}</v-snackbar>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import axios from "axios";
import { composeSignoff, parseSignoff } from "../signoff.js";
import { useCommonSettings } from "../store/settings.js";

const props = defineProps({ templateKey: { type: String, required: true } });
const emit = defineEmits(["back", "help"]);

const tk = (name) => `{{ ${name} }}`;
const config = window.EMAIL_TEMPLATING_CONFIG || {};

const {
    settings: commonSettings,
    load: loadCommonSettings,
} = useCommonSettings(config.apiBaseUrl);

// Encode a template key for use as URL path segments. Keep "/" raw — Apache's
// AllowEncodedSlashes defaults to Off and rejects "%2F" in paths with a 404.
const encodeKey = (k) => String(k).split("/").map(encodeURIComponent).join("/");

const loading = ref(true);
const saving = ref(false);
const previewing = ref(false);
const testing = ref(false);
const resetting = ref(false);
const error = ref(null);
const savedAt = ref(null);
const meta = ref(null);
const defaultSubjectTemplate = ref("");
const hasSavedOverride = ref(false);

const form = reactive({
    subject: "",
    is_enabled: true,
    regions: {},
});

// Per-region defaults captured at load time so we can reset individually.
const defaults = reactive({
    subject: "",
    regions: {},
});

const previewDialog = ref(false);
const previewHtml = ref("");
const previewSubject = ref("");

const expandedPanels = ref("preview");
const previewOpen = computed(() => expandedPanels.value === "preview");
const livePreviewHtml = ref("");
const livePreviewSubject = ref("");
const livePreviewLoading = ref(false);
let livePreviewTimer = null;
let livePreviewSeq = 0;

const tokenCount = computed(() => Object.keys(meta.value?.tokens || {}).length);

// Injected into every preview iframe. Disables <a> clicks so the user can't
// accidentally navigate the iframe away from the rendered email (sandbox="" blocks
// scripts/forms/popups but NOT in-frame link clicks — without this, clicking a
// CTA in the preview replaces the iframe content with the target URL).
const PREVIEW_INERT_STYLE = `<style>
a, a * { pointer-events: none !important; }
</style>`;

const wrappedLivePreviewHtml = computed(() =>
    livePreviewHtml.value ? PREVIEW_INERT_STYLE + livePreviewHtml.value : ""
);
const wrappedPreviewHtml = computed(() =>
    previewHtml.value ? PREVIEW_INERT_STYLE + previewHtml.value : ""
);

const testDialog = ref(false);
const testTo = ref("");

const resetDialog = ref(false);

const snack = ref(false);
const snackText = ref("");

const isDirty = computed(() => {
    if (form.subject !== defaults.subject) return true;
    for (const k of Object.keys(form.regions)) {
        if ((form.regions[k] ?? "") !== (defaults.regions[k] ?? "")) return true;
    }
    return false;
});

function isRegionDirty(name) {
    return (form.regions[name] ?? "") !== (defaults.regions[name] ?? "");
}

const totalEditableFields = computed(
    () => 1 + Object.keys(meta.value?.regions || {}).length
);

const dirtyCount = computed(() => {
    let n = 0;
    if (form.subject !== defaults.subject) n++;
    for (const k of Object.keys(form.regions)) {
        if ((form.regions[k] ?? "") !== (defaults.regions[k] ?? "")) n++;
    }
    return n;
});

// Per-region signoff sub-parts. Reactive cache keyed by region name; values are
// {greeting, team, tagline}. Kept in sync with form.regions[name] (the composed HTML).
const signoffParts = reactive({});

function rebuildSignoffParts() {
    if (!meta.value?.regions) return;
    for (const [name, def] of Object.entries(meta.value.regions)) {
        if (def.type === 'signoff') {
            signoffParts[name] = parseSignoff(form.regions[name] ?? "");
        }
    }
}

function updateSignoffPart(name, key, value) {
    if (!signoffParts[name]) signoffParts[name] = { greeting: "", team: "", tagline: "" };
    signoffParts[name][key] = value;
    form.regions[name] = composeSignoff(signoffParts[name]);
}

function signoffHasContent(name) {
    const p = signoffParts[name] || {};
    return !!(p.greeting || p.team || p.tagline);
}

// Live preview — debounced re-render on any form change.
async function refreshLivePreview() {
    if (loading.value || !previewOpen.value) return;
    const mySeq = ++livePreviewSeq;
    livePreviewLoading.value = true;
    try {
        const { data } = await axios.post(
            `${config.apiBaseUrl}/${encodeKey(props.templateKey)}/preview`,
            buildPayload()
        );
        // Drop stale responses (user kept typing while the request was in flight).
        if (mySeq !== livePreviewSeq) return;
        livePreviewSubject.value = data.subject || "";
        livePreviewHtml.value = data.body_html || "";
    } catch (e) {
        if (mySeq !== livePreviewSeq) return;
        livePreviewSubject.value = "(preview failed)";
        livePreviewHtml.value = "";
    } finally {
        if (mySeq === livePreviewSeq) {
            livePreviewLoading.value = false;
        }
    }
}

function scheduleLivePreview() {
    if (!previewOpen.value) return;
    if (livePreviewTimer) clearTimeout(livePreviewTimer);
    livePreviewTimer = setTimeout(refreshLivePreview, 350);
}

// Re-fetch immediately when the user expands the panel back open.
watch(previewOpen, (on) => { if (on) refreshLivePreview(); });

watch(
    () => [
        form.subject,
        JSON.stringify(form.regions),
        form.is_enabled,
        JSON.stringify(commonSettings.value),
    ],
    () => { scheduleLivePreview(); },
    { deep: false }
);

async function load() {
    loading.value = true;
    try {
        const { data } = await axios.get(`${config.apiBaseUrl}/${encodeKey(props.templateKey)}`);
        meta.value = data.meta;
        defaultSubjectTemplate.value = data.default?.subject_template || "";
        defaults.subject = data.default?.subject_template || "";
        defaults.regions = { ...(data.default?.regions || {}) };
        hasSavedOverride.value = !!data.override;
        if (data.test_recipient_default && !testTo.value) {
            testTo.value = data.test_recipient_default;
        }
        const regionKeys = Object.keys(data.meta?.regions || {});
        if (data.override) {
            form.subject = data.override.subject || defaults.subject;
            form.is_enabled = data.override.is_enabled ?? true;
            form.regions = {};
            for (const k of regionKeys) {
                form.regions[k] = data.override.regions?.[k] ?? defaults.regions[k] ?? "";
            }
        } else {
            form.subject = defaults.subject;
            form.is_enabled = true;
            form.regions = {};
            for (const k of regionKeys) {
                form.regions[k] = defaults.regions[k] ?? "";
            }
        }
        rebuildSignoffParts();
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Failed to load template";
    } finally {
        loading.value = false;
        // Initial preview fetch once the form is populated.
        refreshLivePreview();
    }
}

function buildPayload() {
    return {
        subject: form.subject,
        is_enabled: form.is_enabled,
        regions: form.regions,
    };
}

async function save() {
    saving.value = true;
    error.value = null;
    try {
        await axios.post(`${config.apiBaseUrl}/${encodeKey(props.templateKey)}`, buildPayload());
        savedAt.value = new Date().toLocaleTimeString();
        hasSavedOverride.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Save failed";
    } finally {
        saving.value = false;
    }
}

async function loadPreview() {
    previewing.value = true;
    try {
        const { data } = await axios.post(
            `${config.apiBaseUrl}/${encodeKey(props.templateKey)}/preview`,
            buildPayload()
        );
        previewSubject.value = data.subject;
        previewHtml.value = data.body_html;
        previewDialog.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Preview failed";
    } finally {
        previewing.value = false;
    }
}

async function sendTest() {
    if (!testTo.value) return;
    testing.value = true;
    try {
        const { data } = await axios.post(
            `${config.apiBaseUrl}/${encodeKey(props.templateKey)}/test-send`,
            { to: testTo.value }
        );
        testDialog.value = false;
        snackText.value = data.success
            ? `Sent (${data.path}, ${data.duration_ms}ms)`
            : `Failed: ${data.error || "unknown"}`;
        snack.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Test send failed";
    } finally {
        testing.value = false;
    }
}

async function resetAll() {
    resetting.value = true;
    try {
        if (hasSavedOverride.value) {
            await axios.post(`${config.apiBaseUrl}/${encodeKey(props.templateKey)}/reset`);
        }
        // Reload to pull fresh defaults, also resets local form state.
        resetDialog.value = false;
        await load();
        snackText.value = "Reset to default";
        snack.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Reset failed";
    } finally {
        resetting.value = false;
    }
}

function resetSubject() {
    form.subject = defaults.subject;
    snackText.value = "Subject reset to default";
    snack.value = true;
}

function resetRegion(name) {
    form.regions[name] = defaults.regions[name] ?? "";
    if (meta.value?.regions?.[name]?.type === 'signoff') {
        signoffParts[name] = parseSignoff(form.regions[name]);
    }
    const label = meta.value?.regions?.[name]?.label || name;
    snackText.value = `${label} reset to default`;
    snack.value = true;
}

function copyToken(name) {
    navigator.clipboard.writeText(tk(name));
    snackText.value = `Copied ${tk(name)}`;
    snack.value = true;
}

onMounted(async () => {
    await loadCommonSettings();
    await load();
});
</script>

<style scoped>
/* Header + tokens panel use page-level sticky; only the form column scrolls
 * (it's the tallest piece — anything else stays pinned in view).
 * scrollbar-gutter prevents horizontal jitter when the scrollbar appears. */
.emt-edit {
    padding: 0 4px;
    scrollbar-gutter: stable;
    contain: layout;
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
    /* Ensure consistent height across templates so subsequent rows never reflow */
    min-height: 78px;
    box-sizing: border-box;
}

.emt-body-row {
    margin: 0 !important;
}

.emt-form-col {
    padding-right: 16px !important;
    /* Reserve constant space — no horizontal jitter when the column's
     * content height changes the page's scrollbar presence. */
    min-width: 0;
}

.emt-tokens-col {
    position: sticky;
    top: 110px;
    align-self: flex-start;
    max-height: calc(100vh - 130px);
    overflow-y: auto;
    overflow-x: hidden;
    will-change: transform;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Group the two panels into one card — single outer border, shared divider */
.right-panels {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}

.right-panels :deep(.v-expansion-panel) {
    background: transparent;
    box-shadow: none;
    border: none;
    border-radius: 0;
}

.right-panels :deep(.v-expansion-panel:not(:last-child)) {
    border-bottom: 1px solid #e5e7eb;
}

.right-panels :deep(.v-expansion-panel-title) {
    padding: 12px 16px;
    min-height: 44px;
    font-size: 13px;
    font-weight: 500;
}

.right-panels :deep(.v-expansion-panel-text__wrapper) {
    padding: 0;
}

.preview-panel-text :deep(.v-expansion-panel-text__wrapper) {
    padding: 0 !important;
}

.preview-subject-mini {
    font-size: 11px;
    color: #6b7280;
    max-width: 60%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 8px;
}

.preview-frame {
    padding: 0;
    background: #f4f6f9;
}

/* overflow:hidden + iframe width: calc(100% + 18px) visually clips the
 * iframe's vertical scrollbar (we can't style the inner doc's bar). */
.preview-iframe-wrap {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.preview-iframe {
    width: calc(100% + 18px);
    height: 380px;
    border: 0;
    display: block;
    background: #fff;
}

/* Visual marker that the preview is read-only. The iframe's sandbox=""
 * already blocks scripts / forms / navigation, so the shield only needs to
 * exist for the cursor hint — it must NOT capture pointer events, otherwise
 * mouse-wheel can't reach the iframe and the user can't scroll content past
 * the iframe's fixed height. */
.preview-shield {
    position: absolute;
    inset: 0;
    background: transparent;
    cursor: not-allowed;
    pointer-events: none;
    z-index: 1;
}

.preview-empty {
    padding: 32px 16px;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
    background: #fff;
}

.token-count {
    margin-left: auto;
    margin-right: 8px;
    font-size: 11px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 10px;
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

.emt-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.emt-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 !important;
    letter-spacing: -0.01em;
}

.emt-diff-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 10px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 12px;
    line-height: 1;
    letter-spacing: 0.01em;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.emt-diff-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
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

.region-field {
    position: relative;
}

.textarea-wrap {
    position: relative;
}

.textarea-reset {
    position: absolute !important;
    top: 6px;
    right: 6px;
    z-index: 1;
}

.signoff-section {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 14px 16px;
    background: #fafafa;
    margin-bottom: 16px;
}

.signoff-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}

.signoff-label {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.signoff-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
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

/* Bound the list so all 17+ tokens are reachable without relying on the
 * (overlay/invisible) column-level scrollbar. Caption above the list is ~36px
 * and the two panel titles are ~88px combined — subtract from viewport. */
.token-list {
    padding: 12px 16px;
    max-height: calc(100vh - 260px);
    overflow-y: auto;
    scrollbar-width: thin;
}

.token-list::-webkit-scrollbar {
    width: 8px;
}

.token-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.token-list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.token-item {
    cursor: pointer;
    padding: 6px 12px !important;
    min-height: 36px !important;
}

.token-item:hover {
    background: #f9fafb;
}

.token-code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 11px;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 3px;
    color: #1565C0;
}
</style>
