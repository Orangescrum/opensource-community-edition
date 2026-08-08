<template>
    <div class="emt-token-ref-page">
        <div class="emt-header">
            <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$emit('back')" class="back-btn">
                {{ returnTo?.label || 'Back to list' }}
            </v-btn>
            <h2 class="emt-title">Dynamic keywords reference</h2>
            <p class="emt-subtitle">
                Every available dynamic keyword for every template, grouped by category. Keywords marked
                <em>raw</em> render their value as HTML without escaping — only the send-time
                pipeline supplies these; do not try to inject HTML into a raw keyword from the
                editor.
            </p>
        </div>

        <div class="emt-token-ref-toolbar">
            <v-text-field
                v-model="filter"
                density="compact"
                variant="outlined"
                hide-details
                clearable
                prepend-inner-icon="mdi-magnify"
                placeholder="Filter templates or dynamic keywords…"
                class="emt-token-ref-filter"
            />
            <div class="emt-token-ref-actions">
                <v-btn size="small" variant="text" @click="expandAll">Expand all</v-btn>
                <v-btn size="small" variant="text" @click="collapseAll">Collapse all</v-btn>
            </div>
        </div>

        <div v-if="loading" class="emt-token-ref-status">Loading dynamic keywords reference…</div>
        <div v-else-if="error" class="emt-token-ref-status emt-token-ref-error">{{ error }}</div>
        <div v-else-if="!filteredGroups.length" class="emt-token-ref-status">
            No templates or dynamic keywords match "{{ filter }}".
        </div>

        <div v-else class="emt-token-ref">
            <div v-for="group in filteredGroups" :key="group.category" class="emt-token-ref-group">
                <h4 class="emt-token-ref-cat">{{ group.category }}</h4>
                <details
                    v-for="tpl in group.templates"
                    :key="tpl.key"
                    :open="isOpen(tpl.key)"
                    class="emt-token-ref-tpl"
                    @toggle="onToggle(tpl.key, $event)"
                >
                    <summary>
                        <span class="emt-token-ref-tpl-label">{{ tpl.label }}</span>
                        <span class="emt-token-ref-tpl-key">{{ tpl.key }}</span>
                        <span class="emt-token-ref-tpl-count">
                            {{ Object.keys(tpl.tokens).length }}
                            keyword{{ Object.keys(tpl.tokens).length === 1 ? '' : 's' }}
                        </span>
                    </summary>
                    <div v-if="tpl.default_subject" class="emt-token-ref-subject">
                        <span class="emt-token-ref-subject-label">Default subject:</span>
                        <code>{{ tpl.default_subject }}</code>
                    </div>
                    <table v-if="Object.keys(tpl.tokens).length" class="emt-token-ref-table">
                        <thead>
                            <tr>
                                <th>Keyword</th>
                                <th>Description</th>
                                <th>Sample</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(info, name) in tpl.tokens" :key="name">
                                <td>
                                    <code class="emt-token-ref-token">{{ tokenLiteral(name) }}</code>
                                </td>
                                <td>
                                    {{ info.label || name }}
                                    <span v-if="info.raw" class="emt-token-ref-raw" title="Raw HTML — not escaped">raw</span>
                                </td>
                                <td class="emt-token-ref-sample">
                                    <span v-if="info.sample">{{ info.sample }}</span>
                                    <span v-else class="emt-token-ref-empty">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="emt-token-ref-empty">No dynamic keywords for this template.</p>
                </details>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

defineProps({
    returnTo: {
        type: Object,
        default: () => ({ view: "list", key: null, label: "Back to list" }),
    },
});
defineEmits(["back"]);

const config = window.EMAIL_TEMPLATING_CONFIG || {};
const rows = ref([]);
const loading = ref(true);
const error = ref(null);
const filter = ref("");
const openKeys = ref(new Set());

const OPEN_TOKEN = "{" + "{ ";
const CLOSE_TOKEN = " }" + "}";
const tokenLiteral = (name) => `${OPEN_TOKEN}${name}${CLOSE_TOKEN}`;

const tokenGroups = computed(() => {
    const order = [];
    const byCat = new Map();
    for (const row of rows.value) {
        const cat = row.category || "Other";
        if (!byCat.has(cat)) {
            byCat.set(cat, []);
            order.push(cat);
        }
        byCat.get(cat).push({
            key: row.key,
            label: row.label,
            tokens: row.tokens || {},
            default_subject: row.default_subject || "",
        });
    }
    return order.map((category) => ({ category, templates: byCat.get(category) }));
});

const filteredGroups = computed(() => {
    const q = filter.value.trim().toLowerCase();
    if (!q) return tokenGroups.value;
    return tokenGroups.value
        .map((g) => ({
            category: g.category,
            templates: g.templates.filter((t) => {
                if (
                    t.label.toLowerCase().includes(q) ||
                    t.key.toLowerCase().includes(q) ||
                    g.category.toLowerCase().includes(q)
                ) {
                    return true;
                }
                return Object.entries(t.tokens).some(([name, info]) => {
                    return (
                        name.toLowerCase().includes(q) ||
                        (info.label || "").toLowerCase().includes(q)
                    );
                });
            }),
        }))
        .filter((g) => g.templates.length);
});

function isOpen(key) {
    if (filter.value.trim()) return true;
    return openKeys.value.has(key);
}

function onToggle(key, evt) {
    if (filter.value.trim()) return;
    if (evt.target.open) openKeys.value.add(key);
    else openKeys.value.delete(key);
}

function expandAll() {
    for (const g of tokenGroups.value) {
        for (const t of g.templates) openKeys.value.add(t.key);
    }
    openKeys.value = new Set(openKeys.value);
}

function collapseAll() {
    openKeys.value = new Set();
}

onMounted(async () => {
    if (!config.apiListUrl) {
        error.value = "Dynamic keywords reference unavailable: API URL missing.";
        loading.value = false;
        return;
    }
    try {
        const { data } = await axios.get(config.apiListUrl);
        rows.value = Array.isArray(data?.rows) ? data.rows : [];
    } catch (err) {
        error.value = "Could not load dynamic keywords reference.";
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.emt-token-ref-page {
    padding: 0 4px 32px;
}

.emt-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.back-btn {
    margin-left: -8px;
    margin-bottom: 4px;
    min-height: 28px !important;
    padding: 0 6px !important;
    font-size: 12px !important;
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
    max-width: 720px;
}

.emt-token-ref-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.emt-token-ref-filter {
    max-width: 340px;
    flex: 1;
}

.emt-token-ref-actions {
    display: flex;
    gap: 4px;
}

.emt-token-ref-status {
    font-size: 13px;
    color: #6b7280;
    padding: 12px 0;
}

.emt-token-ref-error {
    color: #b91c1c;
}

.emt-token-ref-group {
    margin: 18px 0 8px;
}

.emt-token-ref-cat {
    font-size: 12px !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin: 0 0 8px !important;
    padding-bottom: 4px;
    border-bottom: 1px dashed #e5e7eb;
}

.emt-token-ref-tpl {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-bottom: 6px;
}

.emt-token-ref-tpl[open] {
    background: #fff;
}

.emt-token-ref-tpl > summary {
    cursor: pointer;
    list-style: none;
    padding: 8px 12px;
    display: flex;
    align-items: baseline;
    gap: 10px;
    font-size: 13px;
    color: #111827;
}

.emt-token-ref-tpl > summary::-webkit-details-marker {
    display: none;
}

.emt-token-ref-tpl > summary::before {
    content: "▸";
    font-size: 10px;
    color: #6b7280;
    transition: transform 120ms ease;
    display: inline-block;
}

.emt-token-ref-tpl[open] > summary::before {
    transform: rotate(90deg);
}

.emt-token-ref-tpl-label {
    font-weight: 600;
    color: #111827;
}

.emt-token-ref-tpl-key {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 11px;
    color: #6b7280;
}

.emt-token-ref-tpl-count {
    margin-left: auto;
    font-size: 11px;
    color: #6b7280;
}

.emt-token-ref-subject {
    padding: 8px 14px 0;
    font-size: 12px;
    color: #4b5563;
}

.emt-token-ref-subject-label {
    margin-right: 6px;
    font-weight: 500;
}

.emt-token-ref-subject code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 12px;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 3px;
    color: #1565C0;
}

.emt-token-ref-table {
    width: 100%;
    border-collapse: collapse;
    margin: 8px 0 12px;
    font-size: 12.5px;
}

.emt-token-ref-table thead th {
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    padding: 6px 12px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.emt-token-ref-table tbody td {
    padding: 6px 12px;
    vertical-align: top;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}

.emt-token-ref-table tbody tr:last-child td {
    border-bottom: 0;
}

.emt-token-ref-token {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 12px;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 3px;
    color: #1565C0;
}

.emt-token-ref-sample {
    color: #6b7280;
    font-family: ui-monospace, Menlo, monospace;
    font-size: 11.5px;
    max-width: 280px;
    word-break: break-word;
}

.emt-token-ref-empty {
    color: #9ca3af;
    font-style: italic;
}

.emt-token-ref-raw {
    display: inline-block;
    margin-left: 6px;
    padding: 0 5px;
    font-size: 10px;
    font-weight: 600;
    color: #92400e;
    background: #fef3c7;
    border-radius: 2px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    vertical-align: middle;
}
</style>
