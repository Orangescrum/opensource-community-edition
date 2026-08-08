<script setup>
import { useTaskStore } from "@/store/useTaskStore";
import { usePagedGroups } from "@/composables/usePagination";
import PageBar from "@/components/PageBar.vue";
import { PRIORITIES, STATUSES, formatDue, priorityMeta, statusMeta } from "@/data/tasks";
import EditableCell from "@/components/EditableCell.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import PriorityGlyph from "@/components/PriorityGlyph.vue";
import TaskTypeBadge from "@/components/TaskTypeBadge.vue";
import { taskHref, openTask } from "@/utils/taskLink";
import GroupHeader from "@/components/GroupHeader.vue";

const store = useTaskStore();
const pager = usePagedGroups(store);

/**
 * Header labels for the inline grid. The tracks are declared once in .iv__grid
 * and shared by the header and every row, so the two cannot drift.
 */
const COLS = [
    { key: "id", label: "Task", cls: "iv__h-id" },
    { key: "title", label: "Title", cls: null },
    { key: "status", label: "Status", cls: null },
    { key: "priority", label: "Priority", cls: null },
    { key: "due", label: "Due", cls: "iv__h-due" },
];

function sortIcon(key) {
    if (store.sortBy !== key) return "mdi-unfold-more-horizontal";
    return store.sortDir === "asc" ? "mdi-arrow-up" : "mdi-arrow-down";
}

</script>

<template>
    <div class="iv">
        <div class="iv__grid iv__head">
            <label class="iv__pick">
                <input
                    type="checkbox"
                    aria-label="Select all tasks"
                    :checked="store.allVisibleSelected"
                    :indeterminate.prop="store.someVisibleSelected"
                    @change="store.toggleSelectAllVisible()"
                />
            </label>
            <span v-for="c in COLS" :key="c.key" :class="c.cls">
                <button type="button" class="tv-sortbtn tv-label" @click="store.toggleSort(c.key)">
                    {{ c.label }}
                    <v-icon :icon="sortIcon(c.key)" size="13" aria-hidden="true" />
                </button>
            </span>
        </div>

        <template v-for="g in pager.groups.value" :key="g.key">
            <GroupHeader v-if="store.groupBy" :group="g" />
            <ul v-show="!g.collapsed" class="iv__list">
            <li
                v-for="t in g.rows"
                :key="t.id"
                class="iv__grid iv__row tv-rail"
                :class="[`st-${t.status}`, { 'is-picked': store.selected.has(t.id) }]"
            >
                <label class="iv__pick">
                    <input
                        type="checkbox"
                        :checked="store.selected.has(t.id)"
                        :aria-label="`Select ${t.ref}`"
                        @change="store.toggleSelect(t.id)"
                    />
                </label>

                <a v-if="taskHref(t)" :href="taskHref(t)" class="tv-id iv__id tv-link" @click="openTask(t, $event)">{{ t.ref }}</a>
                <span v-else class="tv-id iv__id">{{ t.ref }}</span>

                <div class="iv__main">
                    <EditableCell
                        :model-value="t.title"
                        :aria-label="`Title of ${t.ref}`"
                        @update:model-value="store.patch(t.id, 'title', $event)"
                    />
                    <div class="iv__sub">
                        <span v-if="store.spansProjects && t.project" class="iv__proj tv-meta">
                            {{ t.project }}
                        </span>
                        <TaskTypeBadge :task="t" />
                        <EditableCell
                            type="select"
                            :model-value="t.assignee"
                            :options="store.assigneeOptions"
                            :aria-label="`Assignee of ${t.ref}`"
                            @update:model-value="store.patch(t.id, 'assignee', $event)"
                        >
                            <span class="tv-meta">{{ t.assignee }}</span>
                        </EditableCell>
                    </div>
                </div>

                <div class="iv__field iv__field--status">
                    <EditableCell
                        type="select"
                        :model-value="t.status"
                        :options="STATUSES"
                        :aria-label="`Status of ${t.ref}`"
                        @update:model-value="store.patch(t.id, 'status', $event)"
                    >
                        <StatusBadge :value="t.status" :label="t.statusLabel" />
                    </EditableCell>
                </div>

                <div class="iv__field iv__field--pri">
                    <EditableCell
                        type="select"
                        :model-value="t.priority"
                        :options="PRIORITIES"
                        :aria-label="`Priority of ${t.ref}`"
                        @update:model-value="store.patch(t.id, 'priority', $event)"
                    >
                        <PriorityGlyph :value="t.priority" />
                    </EditableCell>
                </div>

                <div class="iv__field iv__field--due">
                    <EditableCell
                        type="date"
                        :model-value="t.due"
                        placeholder="No date"
                        :aria-label="`Due date of ${t.ref}`"
                        @update:model-value="store.patch(t.id, 'due', $event)"
                    >
                        <span :class="{ 'is-empty': !t.due }">{{ formatDue(t.due) }}</span>
                    </EditableCell>
                </div>
            </li>
            </ul>
        </template>

        <PageBar
            v-model:page="pager.page.value"
            v-model:per-page="pager.perPage.value"
            :page-count="pager.pageCount.value"
        />

        <p v-if="!store.visible.length" class="iv__empty">
            No tasks match these filters. Clear a filter to see more.
        </p>
    </div>
</template>

<style scoped>
.iv__list {
    margin: 0;
    padding: 0;
    list-style: none;
}

/* One track declaration, used by the header and every row. */
.iv__grid {
    display: grid;
    grid-template-columns: 34px 88px minmax(220px, 1fr) 140px 108px 92px;
    align-items: center;
    gap: 10px;
    padding: 4px 20px 4px 0;
}

.iv__head {
    min-block-size: 36px;
    background: var(--tv-sub);
    border-block-end: 1px solid var(--tv-rule);
}

.iv__row {
    min-block-size: var(--tv-row-inline);
    border-block-end: 1px solid var(--tv-rule);
}

.iv__row:hover {
    background: var(--tv-sub);
}

.iv__row.is-picked {
    background: var(--tv-brand-soft);
}

.iv__pick {
    display: grid;
    place-items: center;
    padding-inline-start: 13px;
}

.iv__pick input {
    inline-size: 15px;
    block-size: 15px;
    accent-color: var(--tv-brand);
    cursor: pointer;
}

.iv__id {
    font-size: var(--tv-size-meta);
}

.iv__main {
    min-inline-size: 0;
}

/* Task numbers restart per project, so when the list spans projects the row
   names its project. The number stays the app's own bare case_no. */
.iv__proj {
    flex: 0 0 auto;
    color: var(--tv-faint);
    white-space: nowrap;
}

.iv__sub {
    display: flex;
    align-items: center;
    gap: 4px;
    /* The title above sits inside an editable cell (1px border + 6px padding).
       Match that inset so the title and the meta beneath share one text edge. */
    padding-inline-start: 7px;
}

/* EditableCell's root is width:100% so it fills a grid cell. As a flex child
   that makes each cell claim the full row and settle at an equal share, which
   flung the type badge and assignee to opposite ends. Shrink to content here. */
.iv__sub :deep(.tv-cell) {
    inline-size: auto;
    max-inline-size: 100%;
    block-size: 20px;
}

.iv__main :deep(.tv-cell) {
    block-size: 22px;
    font-weight: 500;
}

.iv__type {
    font-size: var(--tv-size-label);
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--tv-faint);
}

.iv__field {
    min-inline-size: 0;
}

.is-empty {
    color: var(--tv-faint);
}

.iv__empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--tv-muted);
}

@media (max-width: 900px) {
    .iv__grid {
        grid-template-columns: 36px minmax(0, 1fr) 116px;
        row-gap: 2px;
    }

    .iv__id,
    .iv__h-id,
    .iv__h-due,
    .iv__field--due {
        display: none;
    }
}
</style>
