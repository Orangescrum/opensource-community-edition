<script setup>
import { computed, ref } from "vue";
import { useTaskStore } from "@/store/useTaskStore";

import { openTask } from "@/utils/taskLink";

const store = useTaskStore();

// Anchor month. `new Date()` at setup is fine — this is view state, not data.
const cursor = ref(startOfMonth(new Date()));

function startOfMonth(d) {
    return new Date(d.getFullYear(), d.getMonth(), 1);
}

const monthLabel = computed(() =>
    cursor.value.toLocaleDateString(undefined, { month: "long", year: "numeric" })
);

const WEEKDAYS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

// Group tasks by due date (YYYY-MM-DD).
const byDate = computed(() => {
    const map = {};
    store.visible.forEach((t) => {
        if (!t.due) return;
        (map[t.due] ??= []).push(t);
    });
    return map;
});

const undated = computed(() => store.visible.filter((t) => !t.due));

// Build a 6-row grid starting on Monday.
const cells = computed(() => {
    const first = cursor.value;
    const offset = (first.getDay() + 6) % 7; // Monday = 0
    const start = new Date(first);
    start.setDate(first.getDate() - offset);

    const out = [];
    const todayIso = isoOf(new Date());
    for (let i = 0; i < 42; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const iso = isoOf(d);
        out.push({
            iso,
            day: d.getDate(),
            inMonth: d.getMonth() === first.getMonth(),
            isToday: iso === todayIso,
            tasks: byDate.value[iso] ?? [],
        });
    }
    return out;
});

function isoOf(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

function step(delta) {
    cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + delta, 1);
}

function today() {
    cursor.value = startOfMonth(new Date());
}

</script>

<template>
    <div class="cal">
        <div class="cal__bar">
            <button type="button" class="cal__nav" aria-label="Previous month" @click="step(-1)">
                <v-icon icon="mdi-chevron-left" size="18" />
            </button>
            <h2 class="cal__month">{{ monthLabel }}</h2>
            <button type="button" class="cal__nav" aria-label="Next month" @click="step(1)">
                <v-icon icon="mdi-chevron-right" size="18" />
            </button>
            <button type="button" class="cal__today" @click="today">Today</button>
        </div>

        <div class="cal__grid" role="grid">
            <div v-for="w in WEEKDAYS" :key="w" class="cal__wd tv-label">{{ w }}</div>

            <div
                v-for="cell in cells"
                :key="cell.iso"
                class="cal__cell"
                :class="{ 'is-out': !cell.inMonth, 'is-today': cell.isToday }"
            >
                <span class="cal__date">{{ cell.day }}</span>
                <ul class="cal__tasks">
                    <li
                        v-for="t in cell.tasks"
                        :key="t.id"
                        class="cal__task tv-rail"
                        :class="`st-${t.status}`"
                        :title="t.title"
                        @click="openTask(t, $event)"
                    >
                        <span class="cal__task-txt">{{ t.ref }} {{ t.title }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="undated.length" class="cal__undated">
            <span class="tv-label">No due date</span>
            <ul class="cal__undated-list">
                <li
                    v-for="t in undated"
                    :key="t.id"
                    class="cal__task tv-rail"
                    :class="`st-${t.status}`"
                    @click="openTask(t, $event)"
                >
                    <span class="cal__task-txt">{{ t.ref }} {{ t.title }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.cal {
    padding: 16px 20px;
}

.cal__bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-block-end: 12px;
}

.cal__month {
    font-size: 16px;
    font-weight: 600;
    min-inline-size: 190px;
}

.cal__nav {
    display: grid;
    place-items: center;
    inline-size: 30px;
    block-size: 30px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    cursor: pointer;
}

.cal__nav:hover {
    background: var(--tv-sub);
}

.cal__today {
    margin-inline-start: 4px;
    block-size: 30px;
    padding: 0 12px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
}

.cal__grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius-lg);
    overflow: hidden;
}

.cal__wd {
    padding: 8px 10px;
    background: var(--tv-sub);
    border-block-end: 1px solid var(--tv-rule);
}

.cal__cell {
    min-block-size: 96px;
    padding: 6px 6px 8px;
    border-inline-end: 1px solid var(--tv-rule);
    border-block-end: 1px solid var(--tv-rule);
    background: var(--tv-paper);
}

.cal__cell:nth-child(7n + 1) {
    /* first column has no left border already; nothing needed */
}

.cal__cell.is-out {
    background: var(--tv-sub);
    color: var(--tv-faint);
}

.cal__date {
    font-size: var(--tv-size-meta);
    font-variant-numeric: tabular-nums;
    color: var(--tv-muted);
}

.cal__cell.is-today .cal__date {
    display: inline-grid;
    place-items: center;
    inline-size: 20px;
    block-size: 20px;
    border-radius: 50%;
    background: var(--tv-brand);
    color: #fff;
    font-weight: 600;
}

.cal__tasks {
    list-style: none;
    margin: 4px 0 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.cal__task {
    padding: 2px 6px 2px 8px;
    border-radius: 3px;
    background: var(--tv-sub);
    font-size: var(--tv-size-label);
    cursor: pointer;
    overflow: hidden;
}

.cal__task:hover {
    background: var(--tv-sub-2);
}

.cal__task-txt {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cal__undated {
    margin-block-start: 16px;
}

.cal__undated-list {
    list-style: none;
    margin: 8px 0 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.cal__undated-list .cal__task {
    max-inline-size: 260px;
}
</style>
