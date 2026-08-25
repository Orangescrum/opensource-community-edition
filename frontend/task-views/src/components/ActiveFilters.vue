<script setup>
import { useFilterFields } from "@/composables/useFilterFields";

/**
 * Filters live behind one menu now, so nothing on screen would say which are
 * applied. These chips are that: one per chosen value, each its own undo.
 */
const { chips, removeChip } = useFilterFields();
</script>

<template>
    <div v-if="chips.length" class="tv-af" role="group" aria-label="Active filters">
        <button
            v-for="chip in chips"
            :key="chip.id"
            type="button"
            class="tv-af__chip"
            :aria-label="`Remove filter ${chip.text}`"
            @click="removeChip(chip)"
        >
            <span>{{ chip.text }}</span>
            <v-icon icon="mdi-close" size="12" aria-hidden="true" />
        </button>
    </div>
</template>

<style scoped>
.tv-af {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    flex-wrap: wrap;
    padding: 0 20px 12px;
}

.tv-af__chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-inline-size: 260px;
    block-size: 24px;
    padding: 0 8px;
    border: 1px solid var(--tv-brand-ring);
    border-radius: var(--tv-radius);
    background: var(--tv-brand-soft);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink);
    cursor: pointer;
}

.tv-af__chip span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tv-af__chip .v-icon {
    flex: none;
    color: var(--tv-muted);
}

.tv-af__chip:hover {
    border-color: var(--tv-brand);
}

.tv-af__chip:hover .v-icon {
    color: var(--tv-ink);
}
</style>
