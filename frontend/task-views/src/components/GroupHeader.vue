<script setup>
import { useTaskStore } from "@/store/useTaskStore";

/**
 * The band that separates one group from the next when Group by is on.
 * Collapsible, because a grouping is only useful if you can fold the parts you
 * are not looking at.
 */
defineProps({
    group: { type: Object, required: true },
});

const store = useTaskStore();
</script>

<template>
    <div class="gh" role="button" tabindex="0" @click="store.toggleGroup(group.key)" @keydown.enter="store.toggleGroup(group.key)">
        <v-icon :icon="group.collapsed ? 'mdi-chevron-right' : 'mdi-chevron-down'" size="16" />
        <span class="gh__label">{{ group.label }}</span>
        <span class="gh__count">{{ group.total ?? group.rows.length }}</span>
    </div>
</template>

<style scoped>
.gh {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 20px;
    background: var(--tv-sub-2);
    border-block-end: 1px solid var(--tv-rule);
    cursor: pointer;
    user-select: none;
}

.gh:hover {
    background: var(--tv-rule);
}

.gh__label {
    font-size: var(--tv-size-meta);
    font-weight: 600;
    color: var(--tv-ink);
}

.gh__count {
    padding: 0 6px;
    border-radius: 9px;
    background: var(--tv-paper);
    font-size: var(--tv-size-label);
    font-weight: 600;
    color: var(--tv-muted);
}
</style>
