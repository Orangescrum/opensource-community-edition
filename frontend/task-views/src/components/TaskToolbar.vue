<script setup>
import { computed, ref } from "vue";
import { GROUP_BY_OPTIONS, useTaskStore } from "@/store/useTaskStore";
import { COLUMNS, PRIORITIES, STATUSES } from "@/data/tasks";
import { CREATED_OPTIONS, PRESETS } from "@/data/serverFilters";
import FacetFilter from "@/components/FacetFilter.vue";
import ConfirmTyped from "@/components/ConfirmTyped.vue";

const store = useTaskStore();
const toggleable = COLUMNS.filter((c) => !c.always);
const confirmArchive = ref(false);
const confirmDelete = ref(false);

/** Mirrors the store's dueBucket keys. */
const DUE_FILTERS = [
    { value: "overdue", label: "Overdue" },
    { value: "today", label: "Today" },
    { value: "tomorrow", label: "Tomorrow" },
    { value: "week", label: "This week" },
    { value: "month", label: "This month" },
    { value: "later", label: "Later" },
    { value: "none", label: "No due date" },
];

const groupByLabel = computed(() => {
    const active = GROUP_BY_OPTIONS.find((g) => g.value === store.groupBy);
    return store.groupBy ? `Group: ${active?.label}` : "Group by";
});

const presetLabel = computed(
    () => PRESETS.find((p) => p.value === store.preset)?.label ?? "All tasks",
);

const createdLabel = computed(() => {
    const active = CREATED_OPTIONS.find((c) => c.value === store.createdRange);
    return active ? `Created: ${active.label}` : "Created";
});
</script>

<template>
    <div class="tv-toolbar">
        <label class="tv-search">
            <v-icon icon="mdi-magnify" size="15" aria-hidden="true" />
            <input
                v-model="store.query"
                type="search"
                placeholder="Search tasks"
                aria-label="Search tasks"
            />
        </label>

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-ghost" :class="{ 'is-on': store.preset }">
                    <v-icon icon="mdi-filter-variant" size="15" aria-hidden="true" />
                    <span>{{ presetLabel }}</span>
                </button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="p in PRESETS"
                    :key="p.value"
                    type="button"
                    class="tv-pop__row"
                    :aria-pressed="store.preset === p.value"
                    @click="store.applyPreset(p.value)"
                >
                    <span class="tv-pop__box" :class="{ 'is-on': store.preset === p.value }">
                        <v-icon v-if="store.preset === p.value" icon="mdi-check" size="11" />
                    </span>
                    <span class="tv-pop__label">{{ p.label }}</span>
                </button>
            </div>
        </v-menu>

        <FacetFilter facet="status" label="Status" :options="STATUSES" />
        <FacetFilter facet="priority" label="Priority" :options="PRIORITIES" />
        <FacetFilter facet="type" label="Type" :options="store.typeOptions" />
        <FacetFilter facet="assignee" label="Assign to" :options="store.assigneeOptions" />
        <FacetFilter facet="taskGroup" label="Task group" :options="store.taskGroupOptions" />
        <FacetFilter facet="due" label="Due date" :options="DUE_FILTERS" />
        <FacetFilter facet="createdBy" label="Created by" :options="store.assigneeOptions" />
        <FacetFilter facet="commentedBy" label="Commented by" :options="store.assigneeOptions" />
        <FacetFilter
            v-if="store.labelOptions.length"
            facet="label"
            label="Label"
            :options="store.labelOptions"
        />

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button
                    v-bind="menu"
                    type="button"
                    class="tv-ghost"
                    :class="{ 'is-on': store.createdRange }"
                >
                    <v-icon icon="mdi-calendar-plus" size="15" aria-hidden="true" />
                    <span>{{ createdLabel }}</span>
                </button>
            </template>
            <div class="tv-pop">
                <button
                    type="button"
                    class="tv-pop__row"
                    :aria-pressed="!store.createdRange"
                    @click="store.setFilter('createdRange', '')"
                >
                    <span class="tv-pop__box" :class="{ 'is-on': !store.createdRange }">
                        <v-icon v-if="!store.createdRange" icon="mdi-check" size="11" />
                    </span>
                    <span class="tv-pop__label">Any time</span>
                </button>
                <button
                    v-for="c in CREATED_OPTIONS"
                    :key="c.value"
                    type="button"
                    class="tv-pop__row"
                    :aria-pressed="store.createdRange === c.value"
                    @click="store.setFilter('createdRange', c.value)"
                >
                    <span class="tv-pop__box" :class="{ 'is-on': store.createdRange === c.value }">
                        <v-icon v-if="store.createdRange === c.value" icon="mdi-check" size="11" />
                    </span>
                    <span class="tv-pop__label">{{ c.label }}</span>
                </button>
            </div>
        </v-menu>

        <button
            type="button"
            class="tv-ghost"
            :class="{ 'is-on': store.favourite }"
            :aria-pressed="store.favourite"
            @click="store.toggleFavourite()"
        >
            <v-icon :icon="store.favourite ? 'mdi-star' : 'mdi-star-outline'" size="15" />
            <span>Favourites</span>
        </button>

        <label class="tv-arch" :class="{ 'tv-arch--on': store.showArchived }">
            <input
                type="checkbox"
                :checked="store.showArchived"
                @change="store.setShowArchived($event.target.checked)"
            />
            Archived
        </label>

        <button
            v-if="store.activeFilterCount"
            type="button"
            class="tv-reset"
            @click="store.clearFilters()"
        >
            Reset
            <v-icon icon="mdi-close" size="13" aria-hidden="true" />
        </button>

        <v-menu v-if="store.supportsGrouping" location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-ghost" :class="{ 'is-on': store.groupBy }">
                    <v-icon icon="mdi-format-list-group" size="15" aria-hidden="true" />
                    <span>{{ groupByLabel }}</span>
                </button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="g in GROUP_BY_OPTIONS"
                    :key="g.value"
                    type="button"
                    class="tv-pop__row"
                    @click="store.setGroupBy(g.value)"
                >
                    <v-icon :icon="store.groupBy === g.value ? 'mdi-check' : 'mdi-blank'" size="12" />
                    {{ g.label }}
                </button>
            </div>
        </v-menu>

        <button
            type="button"
            class="tv-ghost tv-ghost--icon"
            :disabled="store.loading"
            title="Refresh"
            aria-label="Refresh"
            @click="store.refresh()"
        >
            <v-icon :icon="store.loading ? 'mdi-loading' : 'mdi-refresh'" :class="{ 'is-spinning': store.loading }" size="16" />
        </button>

        <span class="tv-toolbar__gap" />

        <span class="tv-toolbar__count tv-meta">
            {{ store.visible.length }}<template v-if="store.activeFilterCount"> of {{ store.tasks.length }}</template>
            {{ store.visible.length === 1 ? "task" : "tasks" }}
        </span>

        <!-- Column visibility means nothing on a board or a calendar. -->
        <v-menu v-if="store.supportsColumns" :close-on-content-click="false" location="bottom end" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-ghost">
                    <v-icon icon="mdi-tune-variant" size="15" aria-hidden="true" />
                    <span>Columns</span>
                </button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="c in toggleable"
                    :key="c.key"
                    type="button"
                    class="tv-pop__row"
                    :aria-pressed="!store.hiddenColumns.has(c.key)"
                    @click="store.toggleColumn(c.key)"
                >
                    <span class="tv-pop__box" :class="{ 'is-on': !store.hiddenColumns.has(c.key) }">
                        <v-icon v-if="!store.hiddenColumns.has(c.key)" icon="mdi-check" size="11" />
                    </span>
                    <span>{{ c.menuLabel ?? c.label }}</span>
                </button>
            </div>
        </v-menu>
    </div>

    <!-- Bulk bar. Appears only with a selection; the same actions in every
         view, because selection survives the switch. -->
    <div v-if="store.selected.size" class="tv-bulk">
        <span class="tv-bulk__n">{{ store.selected.size }} selected</span>

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-bulk__act">Set status</button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="s in STATUSES"
                    :key="s.value"
                    type="button"
                    class="tv-pop__row"
                    @click="store.patchSelected('status', s.value)"
                >
                    {{ s.label }}
                </button>
            </div>
        </v-menu>

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-bulk__act">Set priority</button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="p in PRIORITIES"
                    :key="p.value"
                    type="button"
                    class="tv-pop__row"
                    @click="store.patchSelected('priority', p.value)"
                >
                    {{ p.label }}
                </button>
            </div>
        </v-menu>

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-bulk__act">Move to project</button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="p in store.projectOptions"
                    :key="p.id"
                    type="button"
                    class="tv-pop__row"
                    @click="store.moveSelectedToProject(p.id)"
                >
                    {{ p.name }}
                </button>
            </div>
        </v-menu>

        <v-menu location="bottom start" offset="4">
            <template #activator="{ props: menu }">
                <button v-bind="menu" type="button" class="tv-bulk__act">Copy to project</button>
            </template>
            <div class="tv-pop">
                <button
                    v-for="p in store.projectOptions"
                    :key="p.id"
                    type="button"
                    class="tv-pop__row"
                    @click="store.copySelectedToProject(p.id)"
                >
                    {{ p.name }}
                </button>
            </div>
        </v-menu>

        <button
            v-if="store.showArchived"
            type="button"
            class="tv-bulk__act"
            @click="store.restoreSelected()"
        >
            Restore
        </button>

        <button v-else type="button" class="tv-bulk__act" @click="confirmArchive = true">
            Archive
        </button>

        <button type="button" class="tv-bulk__act tv-bulk__act--danger" @click="confirmDelete = true">
            Delete
        </button>

        <button type="button" class="tv-bulk__act" @click="store.clearSelection()">Clear</button>

        <span v-if="store.bulkBusy" class="tv-bulk__busy tv-meta">Working…</span>
    </div>

    <ConfirmTyped
        v-model="confirmArchive"
        word="archive"
        :title="`Archive ${store.selected.size} task(s)?`"
        body="They leave the active list but keep their history, comments and time logs, and can be restored. Subtasks are archived with their parent."
        confirm-label="Archive"
        :busy="store.bulkBusy"
        @confirm="store.archiveSelected()"
    />

    <ConfirmTyped
        v-model="confirmDelete"
        word="delete"
        :title="`Permanently delete ${store.selected.size} task(s)?`"
        body="This cannot be undone. Archive instead if you only want them out of the way — an archived task can be restored."
        confirm-label="Delete permanently"
        danger
        :busy="store.bulkBusy"
        @confirm="store.deleteSelected()"
    />

    <!-- A failed write already rolled the row back; this says why. -->
    <div v-if="store.saveError" class="tv-savefail">
        <v-icon icon="mdi-alert-circle-outline" size="15" aria-hidden="true" />
        <span>{{ store.saveError }}</span>
        <button type="button" class="tv-savefail__x" aria-label="Dismiss" @click="store.dismissSaveError()">
            <v-icon icon="mdi-close" size="13" />
        </button>
    </div>

</template>

<style scoped>
.tv-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 12px 20px;
}

.tv-toolbar__gap {
    flex: 1;
}

.tv-toolbar__count {
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    margin-inline-end: 4px;
}

.tv-search {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    block-size: 30px;
    padding: 0 10px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    color: var(--tv-faint);
    background: var(--tv-paper);
}

.tv-search:focus-within {
    border-color: var(--tv-brand);
    box-shadow: 0 0 0 3px var(--tv-brand-ring);
}

.tv-search input {
    inline-size: 208px;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
}

.tv-search input::-webkit-search-cancel-button {
    appearance: none;
}

.tv-reset,
.tv-ghost {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    block-size: 30px;
    padding: 0 10px;
    border: 1px solid transparent;
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-ink-2);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
}

.tv-ghost {
    border-color: var(--tv-rule-strong);
}

.tv-ghost.is-on {
    border-color: var(--tv-brand);
    background: var(--tv-brand-soft);
    color: var(--tv-ink);
}

.tv-savefail {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: #fdecea;
    border-block-end: 1px solid #f5c6cb;
    font-size: var(--tv-size-meta);
    color: #8a1f16;
}

.tv-savefail__x {
    margin-inline-start: auto;
    display: grid;
    place-items: center;
    inline-size: 22px;
    block-size: 22px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: inherit;
    cursor: pointer;
}

.tv-savefail__x:hover {
    background: rgba(138, 31, 22, 0.12);
}

.tv-reset:hover,
.tv-ghost:hover {
    background: var(--tv-sub);
}

.tv-arch {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 30px;
    padding: 0 10px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font-size: var(--tv-size-meta);
    color: var(--tv-ink-2);
    cursor: pointer;
    white-space: nowrap;
}

.tv-arch--on {
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}

.tv-arch input {
    margin: 0;
    accent-color: var(--tv-brand);
}

.tv-bulk {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: var(--tv-brand-soft);
    border-block-end: 1px solid var(--tv-rule);
}

.tv-bulk__n {
    font-size: var(--tv-size-meta);
    font-weight: 600;
    color: var(--tv-ink);
    font-variant-numeric: tabular-nums;
}

.tv-bulk__act {
    block-size: 26px;
    padding: 0 9px;
    border: 1px solid var(--tv-brand-ring);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.tv-bulk__act--danger {
    border-color: #e6b3ae;
    color: #8a1f16;
}

.tv-bulk__act--danger:hover {
    border-color: #c4453c;
    background: #fdecea;
    color: #8a1f16;
}

.tv-bulk__busy {
    margin-inline-start: 4px;
}

.tv-ghost--icon {
    padding: 0 8px;
}

.tv-ghost:disabled {
    opacity: 0.5;
    cursor: default;
}

.is-spinning {
    animation: tv-spin 0.9s linear infinite;
}

@keyframes tv-spin {
    to { transform: rotate(360deg); }
}

.tv-confirm {
    padding: 20px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
}

.tv-confirm__title {
    margin: 0 0 8px;
    font-size: var(--tv-size-title);
    font-weight: 600;
}

.tv-confirm__body {
    margin: 0 0 18px;
}

.tv-confirm__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.tg__btn {
    block-size: 32px;
    padding: 0 14px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    font-weight: 500;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.tg__btn:hover:not(:disabled) {
    background: var(--tv-sub);
}

.tg__btn--danger {
    border-color: #c4453c;
    background: #c4453c;
    color: #fff;
}

.tg__btn--danger:hover:not(:disabled) {
    background: #a83a32;
}

.tg__btn:disabled {
    opacity: 0.5;
    cursor: default;
}

.tv-bulk__act:hover {
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}

.tv-pop {
    min-inline-size: 176px;
    padding: 4px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    box-shadow: var(--tv-shadow-pop);
}

.tv-pop__row {
    display: flex;
    align-items: center;
    gap: 8px;
    inline-size: 100%;
    padding: 6px 8px;
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

.tv-pop__box {
    display: grid;
    place-items: center;
    inline-size: 15px;
    block-size: 15px;
    flex: none;
    border: 1px solid var(--tv-rule-strong);
    border-radius: 3px;
    color: #fff;
}

.tv-pop__box.is-on {
    background: var(--tv-brand);
    border-color: var(--tv-brand);
}
</style>
