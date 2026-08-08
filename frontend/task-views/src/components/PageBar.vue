<script setup>
/**
 * The pagination footer every paged view shares. Hidden entirely while the
 * list fits on one page — a pager for one page is noise.
 */
const page = defineModel("page", { type: Number, required: true });
const perPage = defineModel("perPage", { type: Number, required: true });

defineProps({
    pageCount: { type: Number, required: true },
    /** Optional left-hand text, e.g. a selection count. */
    summary: { type: String, default: "" },
});
</script>

<template>
    <div v-if="pageCount > 1 || summary" class="pb">
        <span class="tv-meta">{{ summary }}</span>

        <div v-if="pageCount > 1" class="pb__pager">
            <label class="pb__per tv-meta">
                Rows per page
                <select v-model.number="perPage" aria-label="Rows per page">
                    <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
                </select>
            </label>

            <span class="tv-meta">Page {{ page }} of {{ pageCount }}</span>

            <div class="pb__steps">
                <button type="button" :disabled="page === 1" aria-label="First page" @click="page = 1">
                    <v-icon icon="mdi-page-first" size="15" />
                </button>
                <button type="button" :disabled="page === 1" aria-label="Previous page" @click="page--">
                    <v-icon icon="mdi-chevron-left" size="15" />
                </button>
                <button type="button" :disabled="page === pageCount" aria-label="Next page" @click="page++">
                    <v-icon icon="mdi-chevron-right" size="15" />
                </button>
                <button type="button" :disabled="page === pageCount" aria-label="Last page" @click="page = pageCount">
                    <v-icon icon="mdi-page-last" size="15" />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pb {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 20px;
    border-block-start: 1px solid var(--tv-rule);
}

.pb__pager {
    display: flex;
    align-items: center;
    gap: 16px;
}

.pb__per select {
    margin-inline-start: 6px;
    padding: 3px 6px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
}

.pb__steps {
    display: flex;
    gap: 2px;
}

.pb__steps button {
    display: grid;
    place-items: center;
    inline-size: 26px;
    block-size: 26px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    color: var(--tv-muted);
    cursor: pointer;
}

.pb__steps button:disabled {
    opacity: 0.4;
    cursor: default;
}

.pb__steps button:not(:disabled):hover {
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}
</style>
