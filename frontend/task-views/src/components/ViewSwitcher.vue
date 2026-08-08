<script setup>
import { useTaskStore } from "@/store/useTaskStore";

const store = useTaskStore();

const VIEWS = [
    { value: "inline", label: "List", icon: "mdi-format-list-bulleted", hint: "One task per row, edit in place" },
    { value: "sheet", label: "Spreadsheet", icon: "mdi-grid", hint: "Dense grid, keyboard driven" },
    { value: "table", label: "Table", icon: "mdi-table", hint: "Filter, sort and act in bulk" },
];

function onKeydown(event, index) {
    const delta = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
    if (!delta) return;
    event.preventDefault();
    const next = VIEWS[(index + delta + VIEWS.length) % VIEWS.length];
    store.setView(next.value);
    event.currentTarget.parentElement.children[VIEWS.indexOf(next)].focus();
}
</script>

<template>
    <div class="tv-switch" role="tablist" aria-label="Task view">
        <button
            v-for="(v, i) in VIEWS"
            :key="v.value"
            role="tab"
            type="button"
            class="tv-switch__btn"
            :class="{ 'is-active': store.view === v.value }"
            :aria-selected="store.view === v.value"
            :tabindex="store.view === v.value ? 0 : -1"
            :title="v.hint"
            @click="store.setView(v.value)"
            @keydown="onKeydown($event, i)"
        >
            <v-icon :icon="v.icon" size="15" aria-hidden="true" />
            <span>{{ v.label }}</span>
        </button>
    </div>
</template>

<style scoped>
.tv-switch {
    display: inline-flex;
    padding: 2px;
    gap: 2px;
    background: var(--tv-sub-2);
    border-radius: var(--tv-radius-lg);
}

.tv-switch__btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 11px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-muted);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
}

.tv-switch__btn:hover:not(.is-active) {
    color: var(--tv-ink-2);
}

.tv-switch__btn.is-active {
    background: var(--tv-paper);
    color: var(--tv-ink);
    box-shadow: 0 1px 2px rgba(20, 22, 26, 0.08);
}
</style>
