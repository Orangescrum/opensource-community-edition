<script setup>
import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { PRIORITIES, STATUSES } from "@/data/tasks";
import StatusBadge from "@/components/StatusBadge.vue";
import PriorityGlyph from "@/components/PriorityGlyph.vue";
import TaskTypeBadge from "@/components/TaskTypeBadge.vue";
import { openTask } from "@/utils/taskLink";
import { useListColumns } from "@/composables/useListColumns";

const props = defineProps({
    task: { type: Object, required: true },
    /** Nesting level; 0 is a top-level task. Drives the title indent. */
    depth: { type: Number, default: 0 },
    /** Last child of its parent — its rail stops at the elbow. */
    isLast: { type: Boolean, default: false },
    /** Per ancestor level, whether that ancestor was its parent's last child. */
    trail: { type: Array, default: () => [] },
    /** Rendered as a root here, but its parent lives in another group. */
    orphaned: { type: Boolean, default: false },
    /** Parent rows render a chevron; leaf rows render an empty spacer cell. */
    expandable: { type: Boolean, default: false },
    expanded: { type: Boolean, default: false },
    /** Count shown next to a parent so a collapsed row still says how many. */
    childCount: { type: Number, default: 0 },
});

const emit = defineEmits(["toggle"]);

const store = useTaskStore();
const { shows, listStyle } = useListColumns();

const parentRef = computed(() =>
    props.orphaned ? store.refByNumericId.get(props.task.parentId) : null
);

/**
 * The row is a link to the task, but the checkbox, chevron and actions menu
 * live inside it. Without this guard every click on those also navigated away.
 */
function open(e) {
    if (e.target.closest(".cr__nogo")) return;
    openTask(props.task, e);
}
</script>

<template>
    <div
        class="tv-lrow cr tv-rail"
        :class="[`st-${task.status}`, { 'cr--indent': depth > 0, 'is-picked': store.selected.has(task.id) }]"
        :style="listStyle"
        tabindex="0"
        role="button"
        @click="open"
        @keydown.enter="open"
    >
        <span class="cr__exp cr__nogo">
            <button
                v-if="expandable"
                type="button"
                class="cr__expbtn"
                :aria-expanded="expanded"
                :aria-label="`${expanded ? 'Collapse' : 'Expand'} subtasks of ${task.ref}`"
                @click.stop="emit('toggle')"
            >
                <v-icon :icon="expanded ? 'mdi-chevron-down' : 'mdi-chevron-right'" size="17" />
            </button>
        </span>

        <label class="cr__pick cr__nogo">
            <input
                type="checkbox"
                :checked="store.selected.has(task.id)"
                :aria-label="`Select ${task.ref}`"
                @click.stop
                @change="store.toggleSelect(task.id)"
            />
        </label>

        <span class="tv-id cr__id tv-lcol--id">{{ task.ref }}</span>

        <div
            class="cr__main tv-lcol--title"
            :style="depth ? { paddingInlineStart: depth * 18 + 'px' } : null"
        >
            <span v-if="depth > 0" class="cr__rails" aria-hidden="true">
                <span
                    v-for="level in depth"
                    :key="level"
                    class="cr__rail"
                    :class="{
                        'cr__rail--elbow': level === depth,
                        'cr__rail--stop': level === depth && isLast,
                        'cr__rail--spent': level < depth && trail[level - 1],
                    }"
                />
            </span>
            <span class="cr__title">{{ task.title }}</span>
            <span
                v-if="orphaned && parentRef"
                class="cr__parent tv-meta"
                :title="`Subtask of ${parentRef}, which is in another group`"
            >
                <v-icon icon="mdi-subdirectory-arrow-right" size="12" aria-hidden="true" />
                {{ parentRef }}
            </span>
            <span v-if="store.spansProjects && task.project" class="cr__proj tv-meta">
                {{ task.project }}
            </span>
            <span v-if="expandable && childCount" class="cr__count">{{ childCount }}</span>
        </div>

        <span v-if="shows('type')" class="cr__nogo tv-lcol--type"><TaskTypeBadge v-if="task.type" :task="task" /></span>
        <span v-if="shows('assignee')" class="cr__assignee tv-meta tv-lcol--assignee">{{ task.assignee }}</span>
        <span v-if="shows('status')" class="tv-lcol--status"><StatusBadge :value="task.status" :label="task.statusLabel" /></span>
        <span v-if="shows('priority')" class="tv-lcol--pri"><PriorityGlyph :value="task.priority" /></span>

        <span class="cr__actions cr__nogo">
            <v-menu location="bottom end" offset="2">
                <template #activator="{ props: menu }">
                    <button
                        v-bind="menu"
                        type="button"
                        class="cr__dots"
                        :aria-label="`Actions for ${task.ref}`"
                        @click.stop
                    >
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
                        @click="store.patch(task.id, 'status', s.value)"
                    >
                        <v-icon :icon="task.status === s.value ? 'mdi-check' : 'mdi-blank'" size="12" />
                        {{ s.label }}
                    </button>
                    <div class="tv-pop__rule" />
                    <div class="tv-pop__head tv-label">Priority</div>
                    <button
                        v-for="p in PRIORITIES"
                        :key="p.value"
                        type="button"
                        class="tv-pop__row"
                        @click="store.patch(task.id, 'priority', p.value)"
                    >
                        <v-icon :icon="task.priority === p.value ? 'mdi-check' : 'mdi-blank'" size="12" />
                        {{ p.label }}
                    </button>
                </div>
            </v-menu>
        </span>
    </div>
</template>

<style scoped>
.cr {
    min-block-size: 40px;
    padding-block: 4px;
    border-block-end: 1px solid var(--tv-rule);
    cursor: pointer;
}

.cr:hover {
    background: var(--tv-sub);
}

.cr.is-picked {
    background: var(--tv-brand-soft);
}

/* Children sit under their parent. The indent goes on the title, not the row,
   so the checkbox and chevron columns stay in line with the header above. */
.cr--indent .cr__id {
    color: var(--tv-faint);
}

/*
 * Tree rails: one per ancestor level, so a subtask can be traced back to the
 * task it sits under. The last child stops its rail at the elbow, which is
 * what makes the end of a branch readable.
 */
/* Absolute so the rails span the whole row rather than the height of the title
   text, which is what makes the branch read as one unbroken run. */
.cr__rails {
    position: absolute;
    /* Negative by the row's own padding-block, so a rail reaches the row's
       border box and meets the next row's rail with no seam. */
    inset-block: -4px;
    inset-inline-start: 0;
    display: flex;
}

.cr__rail {
    flex: 0 0 18px;
    align-self: stretch;
    position: relative;
    border-inline-start: 1px solid var(--tv-tree-guide);
}

.cr__rail--elbow::after {
    content: "";
    position: absolute;
    inset-block-start: 50%;
    inset-inline-start: 0;
    inline-size: 13px;
    border-block-start: 1px solid var(--tv-tree-guide);
}

/* No siblings below, so the vertical run ends where the elbow leaves. */
.cr__rail--stop {
    border-inline-start: none;
}

.cr__rail--stop::before {
    content: "";
    position: absolute;
    inset-block: 0 50%;
    inset-inline-start: 0;
    border-inline-start: 1px solid var(--tv-tree-guide);
}

/* That ancestor's branch already ended, so its lane stays empty — otherwise the
   run reads as "more siblings follow" when none do. */
.cr__rail--spent {
    border-inline-start-color: transparent;
}

.cr__exp,
.cr__pick {
    display: grid;
    place-items: center;
}

.cr__pick input {
    inline-size: 15px;
    block-size: 15px;
    accent-color: var(--tv-brand);
    cursor: pointer;
}

.cr__expbtn {
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

.cr__expbtn:hover {
    background: var(--tv-sub-2);
    color: var(--tv-ink);
}

.cr__id {
    font-size: var(--tv-size-meta);
}

.cr__main {
    min-inline-size: 0;
    display: flex;
    /* Stretched to the full row so the rails inside it can span it; the content
       is re-centred here because the grid no longer does it for this cell. */
    align-self: stretch;
    align-items: center;
    position: relative;
    gap: 8px;
}

.cr__proj {
    flex: 0 0 auto;
    color: var(--tv-faint);
    white-space: nowrap;
}

.cr__parent {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex: 0 0 auto;
    color: var(--tv-faint);
    white-space: nowrap;
}

.cr__title {
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cr__type {
    flex: none;
    font-size: var(--tv-size-label);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--tv-faint);
}

.cr__count {
    flex: none;
    padding: 0 6px;
    border-radius: 9px;
    background: var(--tv-sub-2);
    font-size: var(--tv-size-label);
    font-weight: 600;
    color: var(--tv-muted);
}

.cr__assignee {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cr__actions {
    text-align: end;
}

.cr__dots {
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

.cr__dots:hover {
    background: var(--tv-sub-2);
    color: var(--tv-ink);
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
</style>
