<script setup>
import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";

const props = defineProps({
    facet: { type: String, required: true },
    label: { type: String, required: true },
    options: { type: Array, required: true },
});

const store = useTaskStore();
const chosen = computed(() => store[props.facet]);

/**
 * Rows carry no field for some facets — a task does not list who commented on
 * it, or its labels — so there is nothing to count. Showing 0 there would read
 * as "no matches" rather than "not counted".
 */
const countable = computed(() => store.tasks.some((t) => props.facet in t));

function countFor(value) {
    if (!countable.value) return "";
    return store.tasks.filter((t) => t[props.facet] === value).length;
}
</script>

<template>
    <v-menu :close-on-content-click="false" location="bottom start" offset="4">
        <template #activator="{ props: menu }">
            <button v-bind="menu" type="button" class="tv-facet" :class="{ 'is-on': chosen.length }">
                <v-icon icon="mdi-plus-circle-outline" size="14" aria-hidden="true" />
                <span>{{ label }}</span>
                <template v-if="chosen.length">
                    <span class="tv-facet__sep" aria-hidden="true" />
                    <span class="tv-facet__count">{{ chosen.length }}</span>
                </template>
            </button>
        </template>

        <div class="tv-pop" role="group" :aria-label="label">
            <button
                v-for="o in options"
                :key="o.value"
                type="button"
                class="tv-pop__row"
                :aria-pressed="chosen.includes(o.value)"
                @click="store.toggleFacet(facet, o.value)"
            >
                <span class="tv-pop__box" :class="{ 'is-on': chosen.includes(o.value) }">
                    <v-icon v-if="chosen.includes(o.value)" icon="mdi-check" size="11" />
                </span>
                <span class="tv-pop__label">{{ o.label }}</span>
                <span class="tv-pop__n">{{ countFor(o.value) }}</span>
            </button>

            <template v-if="chosen.length">
                <div class="tv-pop__rule" />
                <button type="button" class="tv-pop__clear" @click="store.clearFacet(facet)">
                    Clear {{ label.toLowerCase() }}
                </button>
            </template>
        </div>
    </v-menu>
</template>

<style scoped>
.tv-facet {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    block-size: 30px;
    padding: 0 10px;
    border: 1px dashed var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    color: var(--tv-ink-2);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
}

.tv-facet:hover {
    border-color: var(--tv-faint);
}

.tv-facet.is-on {
    border-style: solid;
    border-color: var(--tv-brand);
    background: var(--tv-brand-soft);
    color: var(--tv-ink);
}

.tv-facet__sep {
    inline-size: 1px;
    block-size: 14px;
    background: var(--tv-brand-ring);
}

.tv-facet__count {
    font-variant-numeric: tabular-nums;
    color: var(--tv-brand);
    font-weight: 600;
}

.tv-pop {
    min-inline-size: 208px;
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

.tv-pop__label {
    flex: 1;
}

.tv-pop__n {
    font-size: var(--tv-size-meta);
    color: var(--tv-faint);
    font-variant-numeric: tabular-nums;
}

.tv-pop__rule {
    block-size: 1px;
    margin: 4px 0;
    background: var(--tv-rule);
}

.tv-pop__clear {
    inline-size: 100%;
    padding: 6px 8px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    font-size: var(--tv-size-meta);
    color: var(--tv-ink-2);
    cursor: pointer;
}

.tv-pop__clear:hover {
    background: var(--tv-sub);
}
</style>
