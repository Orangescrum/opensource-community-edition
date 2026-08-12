<script setup>
import { ref } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { priorityMeta } from "@/data/tasks";

import { openTask } from "@/utils/taskLink";

const store = useTaskStore();

/**
 * Drag and drop between columns. Moving a card is the point of a board, so the
 * drop writes the new status through the same store.patch the other views use —
 * optimistic, with the store rolling back if the save fails.
 */
const dragging = ref(null);
const over = ref(null);

function onDragStart(task, event) {
    dragging.value = task.id;
    event.dataTransfer.effectAllowed = "move";
    // Firefox ignores a drag that carries no data.
    event.dataTransfer.setData("text/plain", task.id);
}

async function onDrop(col) {
    const id = dragging.value;
    dragging.value = null;
    over.value = null;
    if (!id) return;
    const task = store.tasks.find((t) => t.id === id);

    if (col.kind === "custom") {
        await store.moveToColumn(id, col);
    } else {
        await store.patch(id, "status", col.value);
    }
    // The store rolls the card back and sets saveError on failure; App.vue
    // surfaces both, so the drop is never silent either way.
    if (!store.saveError && task) {
        // The name, not the reference: "#16 moved to Closed" makes the reader
        // look the number up again (public issue #4). Long titles are cut so
        // the message stays on one line.
        const name = (task.title || "").trim() || task.ref;
        store.notice = `${name.length > 60 ? `${name.slice(0, 59)}…` : name} moved to ${col.label}`;
    }
}
</script>

<template>
    <div class="kb">
        <div class="kb__scroll">
            <section
                v-for="col in store.kanbanColumns"
                :key="col.value"
                class="kb__col"
                :class="{ 'is-over': over === col.value }"
                @dragover.prevent="over = col.value"
                @dragleave="over === col.value && (over = null)"
                @drop.prevent="onDrop(col)"
            >
                <header class="kb__head" :class="col.kind === 'custom' ? '' : `st-${col.value}`">
                    <span class="kb__dot" :style="col.color ? { background: col.color } : null" aria-hidden="true" />
                    <span class="kb__title">{{ col.label }}</span>
                    <span class="kb__count tv-meta">{{ store.kanbanBuckets[col.value].length }}</span>
                </header>

                <div class="kb__cards">
                    <article
                        v-for="t in store.kanbanBuckets[col.value]"
                        :key="t.id"
                        class="kb__card tv-rail"
                        :class="[`st-${t.status}`, { 'is-dragging': dragging === t.id }]"
                        tabindex="0"
                        role="button"
                        draggable="true"
                        @dragstart="onDragStart(t, $event)"
                        @dragend="dragging = null; over = null"
                        @click="openTask(t, $event)"
                        @keydown.enter="openTask(t, $event)"
                    >
                        <div class="kb__card-top">
                            <span class="tv-id">{{ t.ref }}</span>
                            <span class="kb__tag">{{ t.type }}</span>
                        </div>
                        <p class="kb__card-title">{{ t.title }}</p>
                        <div class="kb__card-foot">
                            <span class="tv-meta">{{ t.assignee }}</span>
                            <span class="kb__pri" :class="`pri-${t.priority}`">
                                <v-icon :icon="priorityMeta(t.priority).icon" size="13" aria-hidden="true" />
                            </span>
                        </div>
                    </article>

                    <p v-if="!store.kanbanBuckets[col.value].length" class="kb__empty tv-meta">
                        No tasks
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.kb {
    padding: 16px 20px;
}

.kb__scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-block-end: 8px;
}

.kb__col {
    flex: 0 0 288px;
    min-inline-size: 288px;
    background: var(--tv-sub);
    border-radius: var(--tv-radius-lg);
    display: flex;
    flex-direction: column;
    max-block-size: 72vh;
}

.kb__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-block-end: 1px solid var(--tv-rule);
}

.kb__dot {
    inline-size: 8px;
    block-size: 8px;
    border-radius: 50%;
    background: var(--rail-color, var(--tv-faint));
}

.kb__title {
    font-weight: 600;
    font-size: var(--tv-size-body);
}

.kb__count {
    margin-inline-start: auto;
    font-variant-numeric: tabular-nums;
}

.kb__cards {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow-y: auto;
}

.kb__col.is-over {
    background: var(--tv-brand-soft);
    outline: 2px dashed var(--tv-brand-ring);
    outline-offset: -2px;
}

.kb__card.is-dragging {
    opacity: 0.45;
}

.kb__card {
    cursor: grab;
}

.kb__card:active {
    cursor: grabbing;
}

.kb__card {
    background: var(--tv-paper);
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius);
    padding: 10px 12px 10px 14px;
    cursor: pointer;
    transition: box-shadow 120ms ease, border-color 120ms ease;
}

.kb__card:hover {
    border-color: var(--tv-rule-strong);
    box-shadow: 0 1px 4px rgba(20, 22, 26, 0.08);
}

.kb__card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.kb__tag {
    font-size: var(--tv-size-label);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--tv-faint);
}

.kb__card-title {
    margin: 6px 0 10px;
    font-size: var(--tv-size-body);
    font-weight: 500;
    line-height: 1.35;
}

.kb__card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.kb__pri.pri-high { color: var(--tv-ink); }
.kb__pri.pri-low { color: var(--tv-faint); }
.kb__pri.pri-medium { color: var(--tv-muted); }

.kb__empty {
    padding: 16px 8px;
    text-align: center;
}
</style>
