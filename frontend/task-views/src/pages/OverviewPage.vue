<script setup>
import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { statusMeta, priorityMeta } from "@/data/tasks";

const store = useTaskStore();
const o = computed(() => store.overview);

const base = window.TASK_VIEWS_CONFIG?.baseUrl ?? "/";

/*
 * Each tile drills into the matching list. "Assigned to me" IS the My Works
 * page; the rest land on the List with a filter preset (?f=), which App.vue
 * applies on load.
 */
const tiles = computed(() => [
    { label: "Total tasks", value: o.value.total, icon: "mdi-checkbox-multiple-marked-outline", href: `${base}task-views` },
    { label: "Assigned to me", value: o.value.mine, icon: "mdi-account-outline", href: `${base}task-myworks` },
    { label: "Overdue", value: o.value.overdue, icon: "mdi-alert-outline", tone: o.value.overdue ? "warn" : "", href: `${base}task-views?f=overdue` },
    { label: "Unassigned", value: o.value.unassigned, icon: "mdi-account-question-outline", href: `${base}task-views?f=unassigned` },
    { label: "Completed", value: o.value.done, icon: "mdi-check-circle-outline", tone: "good", href: `${base}task-views?f=completed` },
]);

function pct(n) {
    return o.value.total ? Math.round((n / o.value.total) * 100) : 0;
}
</script>

<template>
    <div class="ov">
        <div class="ov__tiles">
            <a v-for="t in tiles" :key="t.label" :href="t.href" class="ov__tile" :class="t.tone && `tone-${t.tone}`">
                <v-icon :icon="t.icon" size="20" aria-hidden="true" />
                <div class="ov__tile-body">
                    <span class="ov__tile-val">{{ t.value }}</span>
                    <span class="ov__tile-label tv-meta">{{ t.label }}</span>
                </div>
            </a>
        </div>

        <div class="ov__cols">
            <section class="ov__panel">
                <h3 class="tv-label ov__panel-head">By status</h3>
                <ul class="ov__bars">
                    <li v-for="row in o.byStatus" :key="row.key" class="ov__bar-row">
                        <a class="ov__bar-link" :href="`${base}task-views?f=status:${row.key}`">
                        <span class="ov__bar-label">
                            <span class="ov__swatch" :class="`st-${row.key}`" aria-hidden="true" />
                            {{ statusMeta(row.key).label }}
                        </span>
                        <span class="ov__track">
                            <span class="ov__fill" :class="`st-${row.key}`" :style="{ width: pct(row.n) + '%' }" />
                        </span>
                        <span class="ov__bar-n tv-meta">{{ row.n }}</span>
                        </a>
                    </li>
                </ul>
            </section>

            <section class="ov__panel">
                <h3 class="tv-label ov__panel-head">By priority</h3>
                <ul class="ov__bars">
                    <li v-for="row in o.byPriority" :key="row.key" class="ov__bar-row">
                        <a class="ov__bar-link" :href="`${base}task-views?f=priority:${row.key}`">
                        <span class="ov__bar-label">
                            <v-icon :icon="priorityMeta(row.key).icon" size="14" aria-hidden="true" />
                            {{ priorityMeta(row.key).label }}
                        </span>
                        <span class="ov__track">
                            <span class="ov__fill ov__fill--brand" :style="{ width: pct(row.n) + '%' }" />
                        </span>
                        <span class="ov__bar-n tv-meta">{{ row.n }}</span>
                        </a>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<style scoped>
.ov {
    padding: 20px;
}

.ov__tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}

.ov__tile {
    color: inherit;
    text-decoration: none;
    cursor: pointer;
}

.ov__tile:hover {
    border-color: var(--tv-brand);
}

.ov__tile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--tv-paper);
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius-lg);
    color: var(--tv-muted);
}

.ov__tile.tone-warn { color: var(--tv-st-canceled); }
.ov__tile.tone-good { color: var(--tv-st-done); }

.ov__tile-body {
    display: flex;
    flex-direction: column;
}

.ov__tile-val {
    font-size: 22px;
    font-weight: 600;
    color: var(--tv-ink);
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
}

.ov__cols {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 12px;
    margin-block-start: 16px;
}

.ov__panel {
    background: var(--tv-paper);
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius-lg);
    padding: 14px 16px;
}

.ov__panel-head {
    margin: 0 0 12px;
}

.ov__bars {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ov__bar-link {
    display: contents;
    color: inherit;
    text-decoration: none;
    cursor: pointer;
}

.ov__bar-row:hover .ov__bar-label {
    color: var(--tv-brand);
}

.ov__bar-row {
    display: grid;
    grid-template-columns: 128px 1fr 32px;
    align-items: center;
    gap: 10px;
}

.ov__bar-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: var(--tv-size-body);
    color: var(--tv-ink-2);
}

.ov__swatch {
    inline-size: 9px;
    block-size: 9px;
    border-radius: 2px;
    background: var(--rail-color, var(--tv-faint));
}

.ov__track {
    block-size: 8px;
    border-radius: 4px;
    background: var(--tv-sub-2);
    overflow: hidden;
}

.ov__fill {
    display: block;
    block-size: 100%;
    border-radius: 4px;
    background: var(--rail-color, var(--tv-faint));
    transition: width 200ms ease;
}

.ov__fill--brand {
    background: var(--tv-brand);
}

.ov__bar-n {
    text-align: end;
    font-variant-numeric: tabular-nums;
}
</style>
