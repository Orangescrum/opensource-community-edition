<script setup>
import { computed } from "vue";
import { priorityMeta } from "@/data/tasks";

const props = defineProps({
    value: { type: String, required: true },
    labelled: { type: Boolean, default: true },
});

const meta = computed(() => priorityMeta(props.value));
</script>

<template>
    <!-- Colour sits on the arrow, not a filled chip: Status owns the coloured-dot
         vocabulary one column over, and two badges side by side compete for the
         same glance. The arrow direction still carries the meaning without
         colour, so this stays readable for colour-blind users (WCAG 1.4.1) —
         colour reinforces the shape rather than replacing it. -->
    <span class="tv-pri" :class="`pri-${value}`">
        <v-icon :icon="meta.icon" size="14" :style="{ color: meta.color }" aria-hidden="true" />
        <span v-if="labelled" class="tv-pri__text">{{ meta.label }}</span>
        <span v-else class="tv-sr">{{ meta.label }}</span>
    </span>
</template>

<style scoped>
.tv-pri {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--tv-muted);
}

.tv-pri__text {
    white-space: nowrap;
}

/* The label stays neutral so the row carries one colour signal, not two.
   Weight is what separates the top of the scale from the rest. */
.pri-urgent .tv-pri__text {
    color: var(--tv-ink);
    font-weight: 600;
}

.pri-high .tv-pri__text {
    color: var(--tv-ink);
    font-weight: 500;
}

.pri-low .tv-pri__text {
    color: var(--tv-faint);
}

.tv-sr {
    position: absolute;
    inline-size: 1px;
    block-size: 1px;
    overflow: hidden;
    clip-path: inset(50%);
    white-space: nowrap;
}
</style>
