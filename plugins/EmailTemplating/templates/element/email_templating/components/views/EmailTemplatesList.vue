<template>
    <div class="emt-list">
        <div class="emt-header">
            <div class="emt-header-row">
                <div class="emt-header-text">
                    <h2 class="emt-title">Email Templates</h2>
                    <p class="emt-subtitle">
                        Customize the subject and body of every notification email sent from your company.
                        Templates not customized here use the shipped defaults.
                    </p>
                </div>
                <div class="emt-header-actions">
                    <v-text-field v-model="filter" placeholder="Search templates…" prepend-inner-icon="mdi-magnify"
                        variant="solo-filled" density="compact" flat hide-details clearable class="emt-search" />
                    <v-btn variant="text" prepend-icon="mdi-email-cog-outline" @click="$emit('email-config')"
                        class="emt-help-btn">Email config</v-btn>
                    <v-btn variant="text" prepend-icon="mdi-help-circle-outline" @click="$emit('help')"
                        class="emt-help-btn">Help</v-btn>
                    <v-menu offset-y location="bottom end">
                        <template #activator="{ props }">
                            <v-btn v-bind="props" icon="mdi-dots-vertical" variant="text" size="small"
                                aria-label="More actions" />
                        </template>
                        <v-list density="compact">
                            <v-list-item prepend-icon="mdi-download-outline" @click="doExport('overrides')"
                                :disabled="exporting">
                                <v-list-item-title>Export overrides</v-list-item-title>
                                <v-list-item-subtitle>Just this company's customizations</v-list-item-subtitle>
                            </v-list-item>
                            <v-list-item prepend-icon="mdi-download-multiple-outline" @click="doExport('all')"
                                :disabled="exporting">
                                <v-list-item-title>Export all templates</v-list-item-title>
                                <v-list-item-subtitle>Every template, defaults included</v-list-item-subtitle>
                            </v-list-item>
                            <v-list-item prepend-icon="mdi-upload-outline" @click="triggerImport"
                                :disabled="importing">
                                <v-list-item-title>Import overrides</v-list-item-title>
                                <v-list-item-subtitle>Upload a previously-exported JSON file</v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <input ref="importFileInput" type="file" accept="application/json,.json"
                        style="display:none" @change="onImportFileSelected" />
                </div>
            </div>
            <div v-if="!loading && customizedCount > 0" class="emt-summary">
                <v-chip size="small" color="success" variant="tonal" class="emt-summary-chip">
                    <v-icon start size="14">mdi-check-circle</v-icon>
                    {{ customizedCount }} customized
                </v-chip>
                <span class="emt-summary-rest">{{ totalCount - customizedCount }} using shipped defaults</span>
            </div>
        </div>

        <v-progress-linear v-if="loading" indeterminate color="primary" />

        <v-alert v-if="error" type="error" class="mb-4">{{ error }}</v-alert>

        <div v-if="!loading">
            <!-- General row: Common settings card -->
            <div v-if="showCommonSection" class="emt-category-block">
                <button class="emt-category" type="button" @click="toggle('General')">
                    <v-icon size="16" class="emt-category-chevron" :class="{ open: isOpen('General') }">
                        mdi-chevron-right
                    </v-icon>
                    <span class="emt-category-name">General</span>
                    <span class="emt-category-count">1</span>
                </button>
                <div v-if="isOpen('General')" class="emt-rows">
                    <div class="emt-row emt-row--shared" @click="$emit('common')">
                        <div class="emt-row-main">
                            <div class="emt-row-label">Common settings</div>
                            <div class="emt-row-key">common-settings</div>
                            <div class="emt-row-desc">
                                Brand color, sender name, sign-off, and logo shared across every template.
                            </div>
                        </div>
                        <div class="emt-row-status">
                            <span class="emt-badge emt-badge--shared">
                                <span class="emt-badge-dot"></span>Shared
                            </span>
                        </div>
                        <div class="emt-row-action">
                            <v-btn color="primary" size="small" variant="flat"
                                @click.stop="$emit('common')">Edit</v-btn>
                        </div>
                    </div>
                </div>
            </div>

            <div v-for="(items, category) in visibleGrouped" :key="category" class="emt-category-block">
                <button class="emt-category" type="button" @click="toggle(category)">
                    <v-icon size="16" class="emt-category-chevron" :class="{ open: isOpen(category) }">
                        mdi-chevron-right
                    </v-icon>
                    <span class="emt-category-name">{{ category }}</span>
                    <span class="emt-category-count">{{ items.length }}</span>
                </button>
                <div v-if="isOpen(category)" class="emt-rows">
                    <div v-for="row in items" :key="row.key" class="emt-row"
                        :class="{ 'emt-row--customized': row.customized && row.enabled }"
                        @click="$emit('edit', row.key)">
                        <div class="emt-row-main">
                            <div class="emt-row-label">{{ row.label }}</div>
                            <div class="emt-row-key">{{ row.key }}</div>
                            <div class="emt-row-desc">{{ row.description }}</div>
                        </div>
                        <div class="emt-row-status">
                            <span v-if="row.customized && row.enabled" class="emt-badge emt-badge--customized">
                                <span class="emt-badge-dot"></span>Customized
                            </span>
                            <span v-else-if="row.customized" class="emt-badge emt-badge--disabled">
                                <span class="emt-badge-dot"></span>Disabled
                            </span>
                            <span v-else class="emt-badge emt-badge--default">Default</span>
                        </div>
                        <div class="emt-row-action">
                            <v-btn color="primary" size="small" variant="flat"
                                @click.stop="$emit('edit', row.key)">Edit</v-btn>
                            <v-btn v-if="row.customized" size="small" variant="text" color="error"
                                @click.stop="confirmReset(row)">Reset</v-btn>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filter && totalVisibleAfterFilter === 0" class="emt-empty">
                <v-icon size="48" color="grey-lighten-1">mdi-magnify-close</v-icon>
                <div class="emt-empty-title">No templates match “{{ filter }}”</div>
                <div class="emt-empty-hint">Try searching by label, key, or description.</div>
                <v-btn variant="text" color="primary" size="small" @click="filter = ''">Clear search</v-btn>
            </div>
        </div>

        <v-dialog v-model="resetDialog" max-width="420">
            <v-card>
                <v-card-title>Reset to default?</v-card-title>
                <v-card-text>
                    Your customization for <strong>{{ resetTarget?.label }}</strong> will be deleted.
                    Future sends will use the shipped default template.
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="resetDialog = false">Cancel</v-btn>
                    <v-btn color="error" variant="flat" :loading="resetting" @click="doReset">Reset</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="importDialog" max-width="520" :persistent="importing">
            <v-card>
                <v-card-title>Import overrides</v-card-title>
                <v-card-text>
                    <div class="emt-import-summary">
                        <div><strong>File:</strong> {{ importFileName }}</div>
                        <div v-if="importParsed">
                            <strong>Templates in file:</strong> {{ importParsed.length }}
                        </div>
                        <div v-if="importParseError" class="text-error">{{ importParseError }}</div>
                    </div>
                    <v-alert v-if="importParsed && importParsed.length > 0" type="warning" variant="tonal"
                        density="compact" class="mt-3">
                        This will overwrite any existing customizations for the listed templates.
                    </v-alert>
                    <v-alert v-if="importResult" :type="importResult.skipped?.length ? 'warning' : 'success'"
                        variant="tonal" density="compact" class="mt-3">
                        Imported {{ importResult.written }} template{{ importResult.written === 1 ? '' : 's' }}.
                        <span v-if="importResult.skipped?.length">
                            Skipped {{ importResult.skipped.length }}:
                            <span v-for="(s, i) in importResult.skipped" :key="i">
                                {{ s.key || '(no key)' }} ({{ s.reason }})<span
                                    v-if="i < importResult.skipped.length - 1">, </span>
                            </span>
                        </span>
                    </v-alert>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" :disabled="importing" @click="closeImportDialog">
                        {{ importResult ? 'Close' : 'Cancel' }}
                    </v-btn>
                    <v-btn v-if="!importResult" color="primary" variant="flat" :loading="importing"
                        :disabled="!importParsed || importParsed.length === 0" @click="doImport">
                        Import
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snack" :timeout="2200">{{ snackText }}</v-snackbar>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

const config = window.EMAIL_TEMPLATING_CONFIG || {};

// Encode a template key for use as URL path segments. Keep "/" raw — Apache's
// AllowEncodedSlashes defaults to Off and rejects "%2F" in paths with a 404.
const encodeKey = (k) => String(k).split("/").map(encodeURIComponent).join("/");
const rows = ref([]);
const loading = ref(true);
const error = ref(null);
const filter = ref("");

const resetDialog = ref(false);
const resetTarget = ref(null);
const resetting = ref(false);

const snack = ref(false);
const snackText = ref("");

const exporting = ref(false);
const importing = ref(false);
const importFileInput = ref(null);
const importDialog = ref(false);
const importFileName = ref("");
const importParsed = ref(null);
const importParseError = ref("");
const importResult = ref(null);

const COLLAPSE_KEY = "emt:list:collapsed";
const collapsed = ref(new Set(loadCollapsed()));

function loadCollapsed() {
    try {
        const v = JSON.parse(localStorage.getItem(COLLAPSE_KEY) || "[]");
        return Array.isArray(v) ? v : [];
    } catch {
        return [];
    }
}

function persistCollapsed() {
    try {
        localStorage.setItem(COLLAPSE_KEY, JSON.stringify([...collapsed.value]));
    } catch { /* ignore quota errors */ }
}

function isOpen(category) {
    if (filter.value) return true;
    return !collapsed.value.has(category);
}

function toggle(category) {
    if (filter.value) return;
    if (collapsed.value.has(category)) collapsed.value.delete(category);
    else collapsed.value.add(category);
    collapsed.value = new Set(collapsed.value);
    persistCollapsed();
}

const grouped = computed(() => {
    const out = {};
    for (const r of rows.value) {
        (out[r.category] ||= []).push(r);
    }
    return Object.fromEntries(Object.entries(out).sort(([a], [b]) => a.localeCompare(b)));
});

function matchesFilter(row) {
    if (!filter.value) return true;
    const q = filter.value.trim().toLowerCase();
    if (!q) return true;
    return (
        (row.label || "").toLowerCase().includes(q) ||
        (row.key || "").toLowerCase().includes(q) ||
        (row.description || "").toLowerCase().includes(q)
    );
}

const visibleGrouped = computed(() => {
    const out = {};
    for (const [category, items] of Object.entries(grouped.value)) {
        const matched = items.filter(matchesFilter);
        if (matched.length) out[category] = matched;
    }
    return out;
});

const showCommonSection = computed(() => {
    if (!filter.value) return true;
    const q = filter.value.trim().toLowerCase();
    return (
        "common settings".includes(q) ||
        "common-settings".includes(q) ||
        "brand color sender sign-off logo shared".includes(q)
    );
});

const totalVisibleAfterFilter = computed(() => {
    const rowCount = Object.values(visibleGrouped.value).reduce((sum, arr) => sum + arr.length, 0);
    return rowCount + (showCommonSection.value ? 1 : 0);
});

const totalCount = computed(() => rows.value.length);
const customizedCount = computed(() => rows.value.filter((r) => r.customized).length);

async function load() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(config.apiListUrl);
        rows.value = data.rows || [];
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Failed to load templates";
    } finally {
        loading.value = false;
    }
}

function confirmReset(row) {
    resetTarget.value = row;
    resetDialog.value = true;
}

async function doReset() {
    if (!resetTarget.value) return;
    resetting.value = true;
    try {
        await axios.post(`${config.apiBaseUrl}/${encodeKey(resetTarget.value.key)}/reset`);
        resetDialog.value = false;
        snackText.value = `${resetTarget.value.label} reset to default`;
        snack.value = true;
        await load();
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Reset failed";
    } finally {
        resetting.value = false;
    }
}

async function doExport(scope = "overrides") {
    exporting.value = true;
    try {
        const params = scope === "all" ? { include: "all" } : {};
        const { data, headers } = await axios.get(`${config.apiBaseUrl}/export`, {
            params,
            responseType: "blob",
        });
        const disposition = headers["content-disposition"] || "";
        const match = disposition.match(/filename="?([^"]+)"?/i);
        const filename = match
            ? match[1]
            : `email-templates-${scope}-${Date.now()}.json`;
        const url = URL.createObjectURL(data);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        snackText.value = scope === "all" ? "All templates exported" : "Overrides exported";
        snack.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Export failed";
    } finally {
        exporting.value = false;
    }
}

function triggerImport() {
    importFileInput.value?.click();
}

function onImportFileSelected(event) {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file) return;

    importFileName.value = file.name;
    importParsed.value = null;
    importParseError.value = "";
    importResult.value = null;

    const reader = new FileReader();
    reader.onload = () => {
        try {
            const parsed = JSON.parse(String(reader.result || ""));
            const entries = Array.isArray(parsed?.overrides) ? parsed.overrides
                : Array.isArray(parsed) ? parsed
                    : null;
            if (!entries) {
                importParseError.value = "JSON must contain an `overrides` array or be an array.";
            } else {
                importParsed.value = entries;
            }
        } catch (e) {
            importParseError.value = `Invalid JSON: ${e.message}`;
        }
        importDialog.value = true;
    };
    reader.onerror = () => {
        importParseError.value = "Failed to read file.";
        importDialog.value = true;
    };
    reader.readAsText(file);
}

async function doImport() {
    if (!importParsed.value) return;
    importing.value = true;
    try {
        const { data } = await axios.post(`${config.apiBaseUrl}/import`, {
            overrides: importParsed.value,
        });
        importResult.value = data;
        await load();
    } catch (e) {
        importParseError.value = e.response?.data?.error || e.message || "Import failed";
    } finally {
        importing.value = false;
    }
}

function closeImportDialog() {
    importDialog.value = false;
    importParsed.value = null;
    importParseError.value = "";
    importResult.value = null;
    importFileName.value = "";
}

onMounted(load);
defineEmits(["edit", "common", "email-config", "help"]);
</script>

<style scoped>
.emt-list {
    padding: 0 4px;
}

.emt-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.emt-header-row {
    display: flex;
    align-items: flex-start;
    gap: 24px;
    flex-wrap: wrap;
}

.emt-header-text {
    flex: 1;
    min-width: 240px;
}

.emt-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.emt-search {
    width: 240px;
}

.emt-help-btn {
    font-size: 13px !important;
}

.emt-import-summary {
    font-size: 13px;
    color: #374151;
    line-height: 1.6;
    word-break: break-all;
}

.text-error {
    color: #b91c1c;
}

.emt-title {
    font-size: 22px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 6px !important;
    letter-spacing: -0.01em;
}

.emt-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
    max-width: 640px;
}

.emt-summary {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    font-size: 12px;
    color: #6b7280;
}

.emt-summary-chip {
    font-weight: 500;
}

.emt-summary-rest {
    font-variant-numeric: tabular-nums;
}

.emt-category-block {
    margin-bottom: 8px;
}

.emt-category {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 100%;
    padding: 10px 4px;
    background: none;
    border: 0;
    cursor: pointer;
    text-align: left;
    color: #4b5563;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-radius: 4px;
    transition: background 120ms ease;
}

.emt-category:hover {
    background: #f9fafb;
    color: #1f2937;
}

.emt-category:focus-visible {
    outline: 2px solid #1565C0;
    outline-offset: 1px;
}

.emt-category-chevron {
    transition: transform 160ms ease;
    color: #9ca3af;
}

.emt-category-chevron.open {
    transform: rotate(90deg);
}

.emt-category-name {
    flex: 0 0 auto;
}

.emt-category-count {
    font-size: 11px;
    font-weight: 500;
    color: #6b7280;
    background: #f3f4f6;
    padding: 1px 8px;
    border-radius: 10px;
    font-variant-numeric: tabular-nums;
    text-transform: none;
    letter-spacing: 0;
}

.emt-rows {
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fff;
    overflow: hidden;
    margin-bottom: 16px;
}

.emt-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 120ms ease;
}

.emt-row:last-child {
    border-bottom: 0;
}

.emt-row:hover {
    background: #f9fafb;
}

.emt-row--customized {
    background: linear-gradient(90deg, #f0fdf4 0, #f0fdf4 3px, transparent 3px);
}

.emt-row--customized:hover {
    background: linear-gradient(90deg, #dcfce7 0, #dcfce7 3px, #f9fafb 3px);
}

.emt-row--shared {
    background: linear-gradient(90deg, #eff6ff 0, #eff6ff 3px, transparent 3px);
}

.emt-row--shared:hover {
    background: linear-gradient(90deg, #dbeafe 0, #dbeafe 3px, #f9fafb 3px);
}

.emt-row-main {
    flex: 1;
    min-width: 0;
    display: grid;
    grid-template-columns: minmax(180px, 280px) 1fr;
    gap: 0 16px;
    align-items: baseline;
}

.emt-row-label {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
}

.emt-row-key {
    font-size: 11px;
    color: #9ca3af;
    font-family: ui-monospace, Menlo, monospace;
    grid-column: 1;
    line-height: 1.5;
}

.emt-row-desc {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.5;
    grid-column: 2;
    grid-row: 1 / span 2;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.emt-row-status {
    flex: 0 0 130px;
    display: flex;
    justify-content: flex-start;
}

.emt-row-action {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 4px;
}

.emt-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 12px;
    letter-spacing: 0.01em;
    line-height: 1;
    white-space: nowrap;
}

.emt-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.emt-badge--customized {
    background: #d1fae5;
    color: #065f46;
}

.emt-badge--customized .emt-badge-dot {
    background: #10b981;
}

.emt-badge--shared {
    background: #dbeafe;
    color: #1e40af;
}

.emt-badge--shared .emt-badge-dot {
    background: #3b82f6;
}

.emt-badge--disabled {
    background: #fef3c7;
    color: #92400e;
}

.emt-badge--disabled .emt-badge-dot {
    background: #f59e0b;
}

.emt-badge--default {
    background: #f3f4f6;
    color: #6b7280;
}

.emt-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 48px 16px;
    color: #6b7280;
}

.emt-empty-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.emt-empty-hint {
    font-size: 12px;
}

/* Stack on narrow widths */
@media (max-width: 720px) {
    .emt-row-main {
        grid-template-columns: 1fr;
    }

    .emt-row-desc {
        grid-column: 1;
        grid-row: auto;
    }

    .emt-row-status {
        flex: 0 0 auto;
    }
}
</style>
