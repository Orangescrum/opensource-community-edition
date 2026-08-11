<script setup>
import { useTaskStore } from "@/store/useTaskStore";
import { useListColumns } from "@/composables/useListColumns";

/**
 * Column header for the CompactRow-based lists (Subtask View, My Works).
 *
 * The table view got its headers for free from <thead>; the grid-based lists
 * had none, so those columns were unlabelled and unsortable. This renders the
 * same labels over the same track sizes (.tv-lrow), and drives the same
 * store.toggleSort the table uses — so sort state is shared across views.
 */
const props = defineProps({
    /** Rows this header governs — drives the select-all checkbox state. */
    rows: { type: Array, default: () => [] },
    /** Subtask view supplies expand/collapse; My Works has nothing to put here. */
    expandable: { type: Boolean, default: false },
    allExpanded: { type: Boolean, default: false },
});

const emit = defineEmits(["toggle-expand-all"]);

const store = useTaskStore();
const { shows, listStyle } = useListColumns();

const COLS = [
    { key: "id", label: "Task", cls: "tv-lcol--id" },
    { key: "title", label: "Title", cls: "tv-lcol--title" },
    { key: "type", label: "Type", cls: "tv-lcol--type" },
    { key: "assignee", label: "Assignee", cls: "tv-lcol--assignee" },
    { key: "status", label: "Status", cls: "tv-lcol--status" },
    { key: "priority", label: "Priority", cls: "tv-lcol--pri" },
];

function sortIcon(key) {
    if (store.sortBy !== key) return "mdi-unfold-more-horizontal";
    return store.sortDir === "asc" ? "mdi-arrow-up" : "mdi-arrow-down";
}

const allPicked = () => props.rows.length > 0 && props.rows.every((t) => store.selected.has(t.id));
const somePicked = () => props.rows.some((t) => store.selected.has(t.id)) && !allPicked();

function toggleAll() {
    const next = new Set(store.selected);
    if (allPicked()) props.rows.forEach((t) => next.delete(t.id));
    else props.rows.forEach((t) => next.add(t.id));
    store.selected = next;
}
</script>

<template>
    <div class="tv-lrow tv-lhead lh" :style="listStyle">
        <span class="lh__exp">
            <button
                v-if="expandable"
                type="button"
                class="lh__expbtn"
                :aria-label="allExpanded ? 'Collapse all' : 'Expand all'"
                :title="allExpanded ? 'Collapse all' : 'Expand all'"
                @click="emit('toggle-expand-all')"
            >
                <v-icon :icon="allExpanded ? 'mdi-unfold-less-horizontal' : 'mdi-unfold-more-horizontal'" size="15" />
            </button>
        </span>

        <label class="lh__pick">
            <input
                type="checkbox"
                aria-label="Select all tasks"
                :checked="allPicked()"
                :indeterminate.prop="somePicked()"
                @change="toggleAll"
            />
        </label>

        <span v-for="c in COLS.filter((x) => shows(x.key))" :key="c.key" class="tv-lcol" :class="c.cls">
            <button type="button" class="tv-sortbtn tv-label" @click="store.toggleSort(c.key)">
                {{ c.label }}
                <v-icon :icon="sortIcon(c.key)" size="13" aria-hidden="true" />
            </button>
        </span>

        <span class="tv-label lh__actions">Actions</span>
    </div>
</template>

<style scoped>
.lh__exp,
.lh__pick {
    display: grid;
    place-items: center;
}

.lh__pick input {
    inline-size: 15px;
    block-size: 15px;
    accent-color: var(--tv-brand);
    cursor: pointer;
}

.lh__expbtn {
    display: grid;
    place-items: center;
    inline-size: 22px;
    block-size: 22px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-muted);
    cursor: pointer;
}

.lh__expbtn:hover {
    background: var(--tv-sub-2);
    color: var(--tv-ink);
}

.lh__actions {
    text-align: end;
}
</style>
