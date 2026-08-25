<script setup>
import { computed, ref, watch } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { useFilterFields } from "@/composables/useFilterFields";

const store = useTaskStore();
const { fields, activeCount, countOf, summaryOf, isChosen, choose, toggle, clear, clearAll } =
    useFilterFields();

const open = ref(false);
const drilled = ref(null);
const search = ref("");

const field = computed(() => fields.value.find((f) => f.key === drilled.value) ?? null);

/** Reopening should land on the field list, not wherever it was left. */
watch(open, (on) => {
    if (!on) {
        drilled.value = null;
        search.value = "";
    }
});

watch(drilled, () => {
    search.value = "";
});

/** Long people-lists are the reason this exists; short ones do not need it. */
const searchable = computed(() => (field.value?.options?.length ?? 0) > 8);

const options = computed(() => {
    const all = field.value?.options ?? [];
    const q = search.value.trim().toLowerCase();
    return q ? all.filter((o) => String(o.label).toLowerCase().includes(q)) : all;
});

function countFor(f, value) {
    if (!store.tasks.some((t) => f.key in t)) return "";
    return store.tasks.filter((t) => t[f.key] === value).length;
}
</script>

<template>
    <v-menu v-model="open" :close-on-content-click="false" location="bottom end" offset="4">
        <template #activator="{ props: menu }">
            <button
                v-bind="menu"
                type="button"
                class="tv-ghost"
                :class="{ 'is-on': activeCount }"
                :aria-label="activeCount ? `Filter, ${activeCount} active` : 'Filter'"
            >
                <v-icon icon="mdi-filter-variant" size="15" aria-hidden="true" />
                <span>Filter</span>
                <template v-if="activeCount">
                    <span class="tv-fm__sep" aria-hidden="true" />
                    <span class="tv-fm__n">{{ activeCount }}</span>
                </template>
            </button>
        </template>

        <div class="tv-pop tv-fm" role="group" aria-label="Filters">
            <!-- One field's values. -->
            <template v-if="field">
                <button type="button" class="tv-fm__back" @click="drilled = null">
                    <v-icon icon="mdi-chevron-left" size="15" aria-hidden="true" />
                    <span>{{ field.label }}</span>
                </button>

                <label v-if="searchable" class="tv-fm__find">
                    <v-icon icon="mdi-magnify" size="14" aria-hidden="true" />
                    <input v-model="search" type="search" :placeholder="`Find ${field.label.toLowerCase()}`" />
                </label>

                <div class="tv-fm__scroll">
                    <button
                        v-for="o in options"
                        :key="o.value"
                        type="button"
                        class="tv-pop__row"
                        :aria-pressed="isChosen(field, o.value)"
                        @click="choose(field, o.value)"
                    >
                        <span
                            class="tv-pop__box"
                            :class="{ 'is-on': isChosen(field, o.value), 'tv-fm__radio': field.kind === 'single' }"
                        >
                            <v-icon v-if="isChosen(field, o.value)" icon="mdi-check" size="11" />
                        </span>
                        <span class="tv-pop__label">{{ o.label }}</span>
                        <span class="tv-pop__n">{{ countFor(field, o.value) }}</span>
                    </button>

                    <p v-if="!options.length" class="tv-fm__empty">No matches</p>
                </div>

                <template v-if="countOf(field)">
                    <div class="tv-pop__rule" />
                    <button type="button" class="tv-pop__clear" @click="clear(field)">
                        Clear {{ field.label.toLowerCase() }}
                    </button>
                </template>
            </template>

            <!-- The field list. -->
            <template v-else>
                <div class="tv-fm__scroll">
                    <template v-for="f in fields" :key="f.key">
                        <button
                            v-if="f.kind === 'toggle'"
                            type="button"
                            class="tv-pop__row"
                            :aria-pressed="!!store[f.key]"
                            @click="toggle(f)"
                        >
                            <v-icon :icon="f.icon" size="15" class="tv-fm__icon" aria-hidden="true" />
                            <span class="tv-pop__label">{{ f.label }}</span>
                            <span class="tv-pop__box" :class="{ 'is-on': store[f.key] }">
                                <v-icon v-if="store[f.key]" icon="mdi-check" size="11" />
                            </span>
                        </button>

                        <button
                            v-else
                            type="button"
                            class="tv-pop__row"
                            @click="drilled = f.key"
                        >
                            <v-icon :icon="f.icon" size="15" class="tv-fm__icon" aria-hidden="true" />
                            <span class="tv-pop__label">{{ f.label }}</span>
                            <span v-if="countOf(f)" class="tv-fm__summary">{{ summaryOf(f) }}</span>
                            <v-icon icon="mdi-chevron-right" size="15" class="tv-fm__chev" aria-hidden="true" />
                        </button>
                    </template>
                </div>

                <template v-if="activeCount">
                    <div class="tv-pop__rule" />
                    <button type="button" class="tv-pop__clear" @click="clearAll()">
                        Clear all filters
                    </button>
                </template>
            </template>
        </div>
    </v-menu>
</template>

<style scoped>
/* The activator is a toolbar button, but .tv-ghost is a scoped style owned by
   TaskToolbar, so it needs its own copy to match the controls beside it. */
.tv-ghost {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    block-size: 30px;
    padding: 0 10px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-ink-2);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
}

.tv-ghost:hover {
    background: var(--tv-sub);
}

.tv-ghost.is-on {
    border-color: var(--tv-brand);
    background: var(--tv-brand-soft);
    color: var(--tv-ink);
}

/* The menu is teleported out of #taskViewsApp, and .tv-pop lives as a scoped
   block in each component that opens a menu, so this needs its own copy. */
.tv-pop {
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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    text-align: start;
}

.tv-pop__clear:hover {
    background: var(--tv-sub);
}

.tv-fm {
    inline-size: 268px;
}

.tv-fm__sep {
    inline-size: 1px;
    block-size: 14px;
    background: var(--tv-brand-ring);
}

.tv-fm__n {
    font-variant-numeric: tabular-nums;
    color: var(--tv-brand);
    font-weight: 600;
}

.tv-fm__scroll {
    max-block-size: 320px;
    overflow-y: auto;
}

.tv-fm__icon {
    flex: none;
    color: var(--tv-muted);
}

.tv-fm__chev {
    flex: none;
    color: var(--tv-faint);
}

.tv-fm__summary {
    max-inline-size: 96px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: var(--tv-size-meta);
    color: var(--tv-muted);
    font-weight: 500;
}

.tv-fm__back {
    display: flex;
    align-items: center;
    gap: 4px;
    inline-size: 100%;
    padding: 6px 8px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    font-size: var(--tv-size-body);
    font-weight: 600;
    color: var(--tv-ink);
    cursor: pointer;
    text-align: start;
}

.tv-fm__back:hover {
    background: var(--tv-sub);
}

.tv-fm__find {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 2px 4px 6px;
    padding: 0 8px;
    block-size: 28px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    color: var(--tv-faint);
}

.tv-fm__find:focus-within {
    border-color: var(--tv-brand);
}

.tv-fm__find input {
    inline-size: 100%;
    min-inline-size: 0;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
}

.tv-fm__find input::-webkit-search-cancel-button {
    appearance: none;
}

/* A single-choice field still reads as a radio, not a checkbox. */
.tv-fm__radio {
    border-radius: 50%;
}

.tv-fm__empty {
    margin: 0;
    padding: 8px;
    font-size: var(--tv-size-meta);
    color: var(--tv-muted);
    text-align: center;
}
</style>
