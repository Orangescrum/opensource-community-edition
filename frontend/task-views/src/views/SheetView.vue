<script setup>
import { computed, nextTick, ref } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { usePagination } from "@/composables/usePagination";
import PageBar from "@/components/PageBar.vue";
import { PRIORITIES, STATUSES, formatDue, priorityMeta, statusMeta } from "@/data/tasks";
import { taskHref, openTask } from "@/utils/taskLink";

const store = useTaskStore();

/*
 * The grid is paginated like the other list views; the keyboard cursor works
 * within the current page, so row indexes below always address `sheetRows`.
 */
const sheetEntries = computed(() => store.visible.map((t) => ({ task: t })));
const pager = usePagination(sheetEntries);
const sheetRows = computed(() => pager.rows.value.map((e) => e.task));

// Type and assignee options come from the loaded data, so this has to be
// reactive rather than a module constant.
const CHOICES = computed(() => ({
    status: STATUSES,
    priority: PRIORITIES,
    type: store.typeOptions,
    assignee: store.assigneeOptions,
}));

const isChoice = (key) => Array.isArray(CHOICES.value[key]);

const cols = computed(() => store.columns.filter((c) => c.key !== "id"));

const row = ref(0);
const col = ref(0);
const editing = ref(false);
const draft = ref("");
const grid = ref(null);

/** Where the title input goes in a draft row (cols excludes the id column). */
const titleColIndex = computed(() => cols.value.findIndex((c) => c.key === "title"));

function focusFirstDraft() {
    nextTick(() => {
        const inputs = [...(grid.value?.querySelectorAll(".sh__newinput") || [])];
        (inputs.find((i) => !i.value) || inputs[0])?.focus();
    });
}

function addRows(n) {
    store.addNewRows(n);
    focusFirstDraft();
}

/**
 * Draft-row keys. stopPropagation is the point: the grid's onKey handler treats
 * any printable key as "start editing this cell" and calls preventDefault, which
 * otherwise swallows every character typed into a draft title.
 */
function onDraftKeydown(e, nr, idx) {
    e.stopPropagation();
    if (e.key === "Enter") {
        e.preventDefault();
        store.commitNewRow(nr.tempId, nr.title);
        nextTick(() => {
            const inputs = [...(grid.value?.querySelectorAll(".sh__newinput") || [])];
            (inputs[idx] || inputs[inputs.length - 1])?.focus();
        });
    } else if (e.key === "Escape") {
        e.preventDefault();
        store.removeNewRow(nr.tempId);
    }
}

/** Blur creates a filled row; an empty draft is left in place to fill later. */
function onDraftBlur(nr) {
    if ((nr.title || "").trim()) store.commitNewRow(nr.tempId, nr.title);
}

/**
 * Paste a column copied from Excel/Sheets/CSV straight into the grid: one task
 * per line. Excel and Sheets copy tab-separated, so the first tab-column is the
 * title; a line with no tab is taken whole, so a title containing commas is not
 * split. A single value with no rows falls through to the normal paste (e.g.
 * into a draft input). Ignored while a real cell is being edited.
 */
function onSheetPaste(e) {
    if (editing.value) return;
    const text = e.clipboardData?.getData("text/plain") ?? "";
    if (!/[\t\r\n]/.test(text)) return;
    e.preventDefault();
    const titles = text
        .split(/\r?\n/)
        .map((line) => line.split("\t")[0].trim())
        .filter(Boolean);
    if (titles.length) store.bulkCreateTasks(titles);
}

function cellId(r, c) {
    return `sheet-${r}-${c}`;
}

function setCursor(r, c) {
    row.value = r;
    col.value = c;
}

function valueOf(task, key) {
    if (key === "estimate") return task.estimate ?? "";
    return task[key] ?? "";
}

function sortIcon(key) {
    if (store.sortBy !== key) return "mdi-unfold-more-horizontal";
    return store.sortDir === "asc" ? "mdi-arrow-up" : "mdi-arrow-down";
}

function displayOf(task, key) {
    if (key === "due") return formatDue(task.due);
    if (key === "status") return task.statusLabel || statusMeta(task.status).label;
    if (key === "priority") return priorityMeta(task.priority).label;
    if (key === "type") return task.type ?? "";
    if (key === "estimate") return task.estimate ?? "";
    return task[key] ?? "";
}

async function focusCell(r, c) {
    row.value = Math.max(0, Math.min(r, sheetRows.value.length - 1));
    col.value = Math.max(0, Math.min(c, cols.value.length - 1));
    await nextTick();
    document.getElementById(cellId(row.value, col.value))?.focus();
}

async function beginEdit(seed = null) {
    const task = sheetRows.value[row.value];
    const key = cols.value[col.value].key;
    if (!task) return;

    /*
     * Choice columns used to cycle to the next value on Enter or double-click.
     * That wrote to the database on what is an exploratory gesture — opening a
     * cell to see its options silently changed the task. They open a dropdown
     * now, so nothing is saved until something is picked.
     */
    if (isChoice(key)) {
        if (!CHOICES.value[key].length) return;
        draft.value = String(valueOf(task, key));
        editing.value = true;
        await nextTick();
        grid.value?.querySelector(".sh__select")?.focus();
        return;
    }

    draft.value = seed ?? String(valueOf(task, key));
    editing.value = true;
    await nextTick();
    grid.value?.querySelector(".sh__input")?.focus();
}

/** A dropdown pick is the whole edit — commit straight away. */
function commitChoice(value) {
    const task = sheetRows.value[row.value];
    const key = cols.value[col.value].key;
    editing.value = false;
    if (task && value !== "" && value !== String(valueOf(task, key))) {
        store.patch(task.id, key, value);
    }
    focusCell(row.value, col.value);
}

function commit(advance = true) {
    if (!editing.value) return;
    const task = sheetRows.value[row.value];
    const key = cols.value[col.value].key;
    /*
     * v-model auto-casts <input type="number"> to a Number, so draft is not
     * always a string here — calling .trim() on it threw and the edit was lost
     * before it ever reached the store.
     */
    const raw = String(draft.value ?? "").trim();
    store.patch(task.id, key, raw === "" ? null : key === "estimate" ? Number(raw) : raw);
    editing.value = false;
    if (advance) focusCell(row.value + 1, col.value);
    else focusCell(row.value, col.value);
}

function onKey(e) {
    if (editing.value) {
        if (e.key === "Enter") { e.preventDefault(); commit(true); }
        else if (e.key === "Escape") { e.preventDefault(); editing.value = false; focusCell(row.value, col.value); }
        else if (e.key === "Tab") { e.preventDefault(); commit(false); focusCell(row.value, col.value + (e.shiftKey ? -1 : 1)); }
        return;
    }

    const moves = {
        ArrowDown: [1, 0], ArrowUp: [-1, 0], ArrowRight: [0, 1], ArrowLeft: [0, -1],
    };

    if (moves[e.key]) {
        e.preventDefault();
        const [dr, dc] = moves[e.key];
        focusCell(row.value + dr, col.value + dc);
    } else if (e.key === "Tab") {
        e.preventDefault();
        focusCell(row.value, col.value + (e.shiftKey ? -1 : 1));
    } else if (e.key === "Enter" || e.key === "F2") {
        e.preventDefault();
        beginEdit();
    } else if (e.key === "Backspace" || e.key === "Delete") {
        e.preventDefault();
        const task = sheetRows.value[row.value];
        const key = cols.value[col.value].key;
        if (!isChoice(key)) store.patch(task.id, key, null);
    } else if (e.key.length === 1 && !e.metaKey && !e.ctrlKey) {
        e.preventDefault();
        beginEdit(e.key);
    }
}
</script>

<template>
    <div class="sh">
        <div ref="grid" class="sh__scroll" @keydown="onKey" @paste="onSheetPaste">
            <table class="sh__table">
                <thead>
                    <tr>
                        <th class="sh__idcol tv-label" scope="col">Task</th>
                        <th
                            v-for="c in cols"
                            :key="c.key"
                            scope="col"
                            class="tv-label"
                            :style="c.width ? { width: `${c.width}px` } : null"
                        >
                            <button type="button" class="tv-sortbtn tv-label" @click="store.toggleSort(c.key)">
                                {{ c.label }}
                                <v-icon :icon="sortIcon(c.key)" size="13" aria-hidden="true" />
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(t, r) in sheetRows"
                        :key="t.id"
                        :class="[`st-${t.status}`, { 'is-picked': store.selected.has(t.id) }]"
                    >
                        <th scope="row" class="sh__idcol tv-id tv-rail" :class="`st-${t.status}`">
                            <a v-if="taskHref(t)" :href="taskHref(t)" class="tv-link" @click="openTask(t, $event)">{{ t.ref }}</a>
                            <template v-else>{{ t.ref }}</template>
                        </th>
                        <td
                            v-for="(c, ci) in cols"
                            :key="c.key"
                            class="sh__cell"
                            :class="{ 'is-active': r === row && ci === col, 'is-num': c.key === 'estimate' }"
                        >
                            <select
                                v-if="editing && r === row && ci === col && isChoice(c.key)"
                                class="sh__select"
                                :value="draft"
                                :aria-label="`${c.label} of ${t.ref}`"
                                @change="commitChoice($event.target.value)"
                                @blur="editing = false"
                                @keydown.esc.prevent="editing = false"
                            >
                                <option v-for="o in CHOICES[c.key]" :key="o.value" :value="o.value">
                                    {{ o.label }}
                                </option>
                            </select>
                            <input
                                v-else-if="editing && r === row && ci === col"
                                v-model="draft"
                                class="sh__input"
                                :type="c.key === 'estimate' ? 'number' : c.key === 'due' ? 'date' : 'text'"
                                @blur="commit(false)"
                            />
                            <!-- Cursor syncs on pointer *and* focus. Relying on
                                 focus alone leaves the cursor stale whenever the
                                 event does not fire, and beginEdit() would then
                                 act on a different row than the one clicked. -->
                            <div
                                v-else
                                :id="cellId(r, ci)"
                                class="sh__val"
                                tabindex="0"
                                role="gridcell"
                                :aria-label="`${c.label} of ${t.ref}`"
                                @focus="setCursor(r, ci)"
                                @mousedown="setCursor(r, ci)"
                                @dblclick="setCursor(r, ci); beginEdit()"
                            >
                                {{ displayOf(t, c.key) || "" }}
                            </div>
                        </td>
                    </tr>

                    <tr v-for="(nr, ni) in store.newRows" :key="nr.tempId" class="sh__newrow">
                        <th scope="row" class="sh__idcol tv-id tv-rail">
                            <span class="sh__newtag">New</span>
                        </th>
                        <td v-for="(c, ci) in cols" :key="c.key" class="sh__cell">
                            <input
                                v-if="ci === titleColIndex"
                                v-model="nr.title"
                                class="sh__newinput"
                                type="text"
                                :disabled="nr.saving"
                                :placeholder="nr.saving ? 'Saving…' : 'Type a task title, then Enter'"
                                @keydown="onDraftKeydown($event, nr, ni)"
                                @blur="onDraftBlur(nr)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="store.canAddRows" class="sh__addbar">
            <button type="button" class="sh__addbtn" @click="addRows(1)">
                <v-icon icon="mdi-plus" size="15" aria-hidden="true" /> Add row
            </button>
            <button type="button" class="sh__addbtn" @click="addRows(10)">
                <v-icon icon="mdi-plus" size="15" aria-hidden="true" /> Add 10 rows
            </button>
            <span class="sh__addhint">…or paste a column of titles from Excel / CSV</span>
        </div>

        <p v-if="!store.visible.length && !store.newRows.length" class="sh__empty">
            No tasks match these filters. Clear a filter to see more.
        </p>

        <PageBar
            v-model:page="pager.page.value"
            v-model:per-page="pager.perPage.value"
            :page-count="pager.pageCount.value"
        />
    </div>
</template>

<style scoped>
.sh__scroll {
    overflow: auto;
    max-block-size: 62vh;
    border-block-start: 1px solid var(--tv-rule);
}

.sh__table {
    inline-size: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: var(--tv-size-meta);
}

.sh__table th,
.sh__table td {
    block-size: var(--tv-row-sheet);
    padding: 0;
    border-block-end: 1px solid var(--tv-rule);
    border-inline-end: 1px solid var(--tv-rule);
    text-align: start;
    font-weight: inherit;
}

.sh__table thead th {
    position: sticky;
    inset-block-start: 0;
    z-index: 2;
    padding: 0 8px;
    background: var(--tv-sub);
    border-block-end: 1px solid var(--tv-rule-strong);
    white-space: nowrap;
}

.sh__idcol {
    inline-size: 96px;
    min-inline-size: 96px;
    padding-inline: 8px !important;
    white-space: nowrap;
    text-align: start;
    font-weight: 500;
    background: var(--tv-paper);
}

.sh__cell {
    position: relative;
}

.sh__val {
    display: flex;
    align-items: center;
    block-size: 100%;
    padding: 0 8px;
    outline: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: cell;
}

.is-num .sh__val,
.is-num .sh__input {
    justify-content: flex-end;
    text-align: end;
}

.sh__cell.is-active .sh__val {
    box-shadow: inset 0 0 0 2px var(--tv-brand);
    background: var(--tv-brand-soft);
}

/* Matches the cell box so opening a dropdown does not shift the grid. */
.sh__select {
    inline-size: 100%;
    block-size: 100%;
    padding: 0 4px;
    border: 0;
    outline: 2px solid var(--tv-brand);
    outline-offset: -2px;
    background: var(--tv-paper);
    font: inherit;
    color: inherit;
}

.sh__input {
    inline-size: 100%;
    block-size: 100%;
    padding: 0 7px;
    border: 0;
    outline: 2px solid var(--tv-brand);
    outline-offset: -2px;
    background: var(--tv-paper);
    font: inherit;
    color: inherit;
}

tbody tr:hover td {
    background: var(--tv-sub);
}

tbody tr.is-picked td {
    background: var(--tv-brand-soft);
}

.tv-sr {
    position: absolute;
    inline-size: 1px;
    block-size: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}

.sh__empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--tv-muted);
}

.sh__newrow td {
    background: var(--tv-brand-soft);
}

.sh__newtag {
    font-size: var(--tv-size-meta);
    font-weight: 600;
    color: var(--tv-brand);
}

.sh__newinput {
    inline-size: 100%;
    block-size: 100%;
    border: 0;
    padding: 0 8px;
    background: transparent;
    font: inherit;
    color: inherit;
    outline: 2px solid transparent;
    outline-offset: -2px;
}

.sh__newinput:focus {
    outline-color: var(--tv-brand);
    background: var(--tv-paper);
}

.sh__addbar {
    display: flex;
    gap: 8px;
    padding: 8px 12px;
    border-block-start: 1px solid var(--tv-rule);
}

.sh__addbtn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 28px;
    padding: 0 12px;
    border: 1px dashed var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.sh__addbtn:hover {
    border-style: solid;
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}

.sh__addhint {
    align-self: center;
    font-size: var(--tv-size-meta);
    color: var(--tv-muted);
}
</style>
