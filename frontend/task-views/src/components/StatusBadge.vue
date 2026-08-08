<script setup>
import { computed } from "vue";
import { statusMeta } from "@/data/tasks";

const props = defineProps({
    value: { type: String, required: true },
    // Projects define their own workflow names ("Ready", "Resolve"). When the
    // row carries one, show it — `value` still drives the colour and filters.
    label: { type: String, default: null },
    dense: { type: Boolean, default: false },
});

const meta = computed(() => ({
    ...statusMeta(props.value),
    label: props.label || statusMeta(props.value).label,
}));
</script>

<template>
    <span class="tv-status" :class="[`st-${value}`, { 'is-dense': dense }]">
        <span class="tv-status__dot" aria-hidden="true" />
        <span class="tv-status__text">{{ meta.label }}</span>
    </span>
</template>

<style scoped>
.tv-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}

.tv-status__dot {
    inline-size: 7px;
    block-size: 7px;
    border-radius: 50%;
    flex: none;
    background: var(--rail-color, var(--tv-faint));
}

.tv-status__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--tv-ink-2);
}

.is-dense .tv-status__text {
    font-size: var(--tv-size-meta);
}
</style>
