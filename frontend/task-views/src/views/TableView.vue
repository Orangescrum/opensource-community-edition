<script setup>
import { computed, ref, watch } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import PageBar from "@/components/PageBar.vue";
import { formatDue, PRIORITIES, STATUSES, priorityMeta, statusMeta } from "@/data/tasks";
import StatusBadge from "@/components/StatusBadge.vue";
import PriorityGlyph from "@/components/PriorityGlyph.vue";
import TaskTypeBadge from "@/components/TaskTypeBadge.vue";
import { taskHref, openTask } from "@/utils/taskLink";

const store = useTaskStore();

const perPage = ref(25);
const page = ref(1);

/**
 * One flat stream of group bands and task rows, so pagination counts what is
 * actually on screen. Paginating the tasks and then grouping each page would
 * put a band at the top of every page and split a group across two of them.
 */
const entries = computed(() => {
    if (!store.groupBy) return store.visible.map((t) => ({ kind: "task", task: t }));

    return store.grouped.flatMap((g) => [
        { kind: "group", group: g },
        ...(g.collapsed ? [] : g.rows.map((t) => ({ kind: "task", task: t }))),
    ]);
});

const pageCount = computed(() => Math.max(1, Math.ceil(entries.value.length / perPage.value)));
const rows = computed(() => {
    const start = (page.value - 1) * perPage.value;
    return entries.value.slice(start, start + perPage.value);
});

/** +2 for the checkbox and actions columns that bracket store.columns. */
const colSpan = computed(() => store.columns.length + 2);

// Filtering can shrink the list under the cursor; never strand the user on a
// page that no longer exists.
watch([() => entries.value.length, perPage], () => {
    if (page.value > pageCount.value) page.value = pageCount.value;
});

/** The tasks on the current page (group bands excluded). */
const pageTasks = computed(() => rows.value.filter((e) => e.kind === "task").map((e) => e.task));
const pageAllSelected = computed(() =>
    pageTasks.value.length > 0 && pageTasks.value.every((t) => store.selected.has(t.id)));
const pageSomeSelected = computed(() =>
    !pageAllSelected.value && pageTasks.value.some((t) => store.selected.has(t.id)));

/*
 * Gmail-style select-all: the header checkbox takes the current page, and a
 * banner then offers the whole filtered set. The old box silently selected
 * every page while labelled "this page".
 */
function togglePage() {
    const next = new Set(store.selected);
    if (pageAllSelected.value) pageTasks.value.forEach((t) => next.delete(t.id));
    else pageTasks.value.forEach((t) => next.add(t.id));
    store.selected = next;
}

function selectAllFiltered() {
    store.selected = new Set(store.visible.map((t) => t.id));
}

function sortIcon(key) {
    if (store.sortBy !== key) return "mdi-unfold-more-horizontal";
    return store.sortDir === "asc" ? "mdi-arrow-up" : "mdi-arrow-down";
}

</script>

<template>
    <div class="tb">
        <div class="tb__scroll">
            <div
                v-if="pageAllSelected && store.selected.size < store.visible.length"
                class="tb__allbar"
            >
                All {{ pageTasks.length }} tasks on this page are selected.
                <button type="button" class="tb__alllink" @click="selectAllFiltered">
                    Select all {{ store.visible.length }} tasks
                </button>
            </div>

            <table class="tb__table">
                <thead>
                    <tr>
                        <th class="tb__pick" scope="col">
                            <input
                                type="checkbox"
                                aria-label="Select all tasks on this page"
                                :checked="pageAllSelected"
                                :indeterminate.prop="pageSomeSelected"
                                @change="togglePage()"
                            />
                        </th>
                        <th
                            v-for="c in store.columns"
                            :key="c.key"
                            scope="col"
                            :style="c.width ? { width: `${c.width}px` } : null"
                        >
                            <button
                                type="button"
                                class="tb__sort tv-label"
                                @click="store.toggleSort(c.key)"
                            >
                                {{ c.label }}
                                <v-icon :icon="sortIcon(c.key)" size="13" aria-hidden="true" />
                            </button>
                        </th>
                        <th class="tb__actions" scope="col"><span class="tv-sr">Actions</span></th>
                    </tr>
                </thead>

                <tbody>
                    <template v-for="e in rows" :key="e.kind === 'group' ? `g-${e.group.key}` : e.task.id">
                    <tr v-if="e.kind === 'group'" class="tb__grouprow">
                        <td :colspan="colSpan">
                            <button type="button" class="tb__groupbtn" @click="store.toggleGroup(e.group.key)">
                                <v-icon :icon="e.group.collapsed ? 'mdi-chevron-right' : 'mdi-chevron-down'" size="16" />
                                <span class="tb__grouplabel">{{ e.group.label }}</span>
                                <span class="tb__groupcount">{{ e.group.rows.length }}</span>
                            </button>
                        </td>
                    </tr>
                    <!-- Single-element v-for aliases the entry's task to `t`;
                         Vue has no block-scoped binding and the row body below
                         reads `t` throughout. -->
                    <tr
                        v-for="t in (e.kind === 'task' ? [e.task] : [])"
                        :key="t.id"
                        class="tv-rail"
                        :class="[`st-${t.status}`, { 'is-picked': store.selected.has(t.id) }]"
                    >
                        <td class="tb__pick">
                            <input
                                type="checkbox"
                                :aria-label="`Select ${t.ref}`"
                                :checked="store.selected.has(t.id)"
                                @change="store.toggleSelect(t.id)"
                            />
                        </td>

                        <td v-for="c in store.columns" :key="c.key" :class="{ 'is-num': c.key === 'estimate' }">
                            <template v-if="c.key === 'id'">
                                <a v-if="taskHref(t)" :href="taskHref(t)" class="tv-id tv-link" @click="openTask(t, $event)">{{ t.ref }}</a>
                                <span v-else class="tv-id">{{ t.ref }}</span>
                            </template>
                            <template v-else-if="c.key === 'title'">
                                <div class="tb__title">
                                    <a v-if="taskHref(t)" :href="taskHref(t)" class="tb__text tv-link" @click="openTask(t, $event)">{{ t.title }}</a>
                                    <span v-else class="tb__text">{{ t.title }}</span>
                                </div>
                            </template>
                            <template v-else-if="c.key === 'type'">
                                <TaskTypeBadge :task="t" />
                            </template>
                            <template v-else-if="c.key === 'status'">
                                <StatusBadge :value="t.status" :label="t.statusLabel" />
                            </template>
                            <template v-else-if="c.key === 'priority'">
                                <PriorityGlyph :value="t.priority" />
                            </template>
                            <template v-else-if="c.key === 'due'">
                                <span :class="{ 'is-empty': !t.due }">{{ formatDue(t.due) }}</span>
                            </template>
                            <template v-else-if="c.key === 'estimate'">
                                {{ t.estimate ?? "—" }}
                            </template>
                            <template v-else>
                                {{ t[c.key] }}
                            </template>
                        </td>

                        <td class="tb__actions">
                            <v-menu location="bottom end" offset="2">
                                <template #activator="{ props: menu }">
                                    <button v-bind="menu" type="button" class="tb__dots" :aria-label="`Actions for ${t.ref}`">
                                        <v-icon icon="mdi-dots-horizontal" size="16" />
                                    </button>
                                </template>
                                <div class="tv-pop">
                                    <div class="tv-pop__head tv-label">Status</div>
                                    <button
                                        v-for="s in STATUSES"
                                        :key="s.value"
                                        type="button"
                                        class="tv-pop__row"
                                        @click="store.patch(t.id, 'status', s.value)"
                                    >
                                        <v-icon :icon="t.status === s.value ? 'mdi-check' : 'mdi-blank'" size="12" />
                                        {{ s.label }}
                                    </button>
                                    <div class="tv-pop__rule" />
                                    <div class="tv-pop__head tv-label">Priority</div>
                                    <button
                                        v-for="p in PRIORITIES"
                                        :key="p.value"
                                        type="button"
                                        class="tv-pop__row"
                                        @click="store.patch(t.id, 'priority', p.value)"
                                    >
                                        <v-icon :icon="t.priority === p.value ? 'mdi-check' : 'mdi-blank'" size="12" />
                                        {{ p.label }}
                                    </button>
                                </div>
                            </v-menu>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <p v-if="!store.visible.length" class="tb__empty">
            No tasks match these filters. Clear a filter to see more.
        </p>

        <div class="tb__foot">
            <span class="tv-meta">
                {{ store.selected.size }} of {{ store.visible.length }} row(s) selected
            </span>

            <PageBar
                v-model:page="page"
                v-model:per-page="perPage"
                :page-count="pageCount"
            />
        </div>
    </div>
</template>

<style scoped>
.tb__allbar {
    padding: 8px 16px;
    text-align: center;
    font-size: var(--tv-size-meta);
    color: var(--tv-ink-2);
    background: var(--tv-brand-soft);
    border-block-end: 1px solid var(--tv-rule);
}

.tb__alllink {
    border: 0;
    background: none;
    color: var(--tv-brand);
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}

.tb__alllink:hover {
    text-decoration: underline;
}

.tb__scroll {
    overflow-x: auto;
}

.tb__table {
    inline-size: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.tb__table th {
    padding: 0 10px;
    block-size: 36px;
    text-align: start;
    background: var(--tv-sub);
    border-block-end: 1px solid var(--tv-rule);
    white-space: nowrap;
}

.tb__table td {
    padding: 0 10px;
    block-size: var(--tv-row-table);
    border-block-end: 1px solid var(--tv-rule);
    vertical-align: middle;
}

tbody tr:hover td {
    background: var(--tv-sub);
}

tbody tr.is-picked td {
    background: var(--tv-brand-soft);
}

.tb__pick {
    inline-size: 40px;
    padding-inline-start: 13px !important;
}

.tb__pick input {
    inline-size: 15px;
    block-size: 15px;
    accent-color: var(--tv-brand);
    cursor: pointer;
}

.tb__sort {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    color: inherit;
}

.tb__sort:hover {
    color: var(--tv-ink);
}

.tb__title {
    display: flex;
    align-items: center;
    gap: 8px;
    min-inline-size: 0;
}

.tb__tag {
    flex: none;
    padding: 1px 6px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: 3px;
    font-size: var(--tv-size-label);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--tv-muted);
}

.tb__text {
    min-inline-size: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.is-num {
    text-align: end;
}

.is-empty {
    color: var(--tv-faint);
}

.tb__actions {
    inline-size: 44px;
    text-align: end;
}

.tb__dots {
    display: grid;
    place-items: center;
    inline-size: 26px;
    block-size: 26px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-muted);
    cursor: pointer;
}

.tb__dots:hover {
    background: var(--tv-sub-2);
    color: var(--tv-ink);
}

.tb__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 12px 20px;
    border-block-start: 1px solid var(--tv-rule);
}

.tb__pager {
    display: flex;
    align-items: center;
    gap: 16px;
}

.tb__per {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.tb__per select {
    block-size: 28px;
    padding: 0 4px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    color: var(--tv-ink);
}

.tb__steps {
    display: inline-flex;
    gap: 2px;
}

.tb__steps button {
    display: grid;
    place-items: center;
    inline-size: 28px;
    block-size: 28px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    color: var(--tv-ink-2);
    cursor: pointer;
}

.tb__steps button:hover:not(:disabled) {
    background: var(--tv-sub);
}

.tb__steps button:disabled {
    opacity: 0.4;
    cursor: default;
}

.tv-pop {
    min-inline-size: 176px;
    padding: 4px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    box-shadow: var(--tv-shadow-pop);
}

.tv-pop__head {
    padding: 6px 8px 2px;
}

.tv-pop__row {
    display: flex;
    align-items: center;
    gap: 7px;
    inline-size: 100%;
    padding: 5px 8px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
    cursor: pointer;
    text-align: start;
}

.tv-pop__row:hover {
    background: var(--tv-sub);
}

.tv-pop__rule {
    block-size: 1px;
    margin: 4px 0;
    background: var(--tv-rule);
}

.tv-sr {
    position: absolute;
    inline-size: 1px;
    block-size: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}

.tb__grouprow td {
    padding: 0;
    background: var(--tv-sub-2);
    border-block-end: 1px solid var(--tv-rule);
}

.tb__groupbtn {
    display: flex;
    align-items: center;
    gap: 7px;
    inline-size: 100%;
    padding: 7px 10px;
    border: 0;
    background: transparent;
    font: inherit;
    color: inherit;
    cursor: pointer;
    text-align: start;
}

.tb__groupbtn:hover {
    background: var(--tv-rule);
}

.tb__grouplabel {
    font-size: var(--tv-size-meta);
    font-weight: 600;
    color: var(--tv-ink);
}

.tb__groupcount {
    padding: 0 6px;
    border-radius: 9px;
    background: var(--tv-paper);
    font-size: var(--tv-size-label);
    font-weight: 600;
    color: var(--tv-muted);
}

.tb__empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--tv-muted);
}
</style>
