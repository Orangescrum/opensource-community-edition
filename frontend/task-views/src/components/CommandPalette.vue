<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { useCommandPalette } from "@/composables/useCommandPalette";
import { openTask } from "@/utils/taskLink";

const store = useTaskStore();
const { paletteOpen, closePalette, openHelp } = useCommandPalette();

const inputEl = ref(null);
const listEl = ref(null);
const query = ref("");
const activeIndex = ref(0);

/** Pages that actually honour the shared filters (mirrors App.vue's toolbar). */
const FILTERABLE = ["views", "subtasks", "myworks", "kanban", "calendar"];
const page = window.TASK_VIEWS_CONFIG?.page ?? "views";
const filterable = FILTERABLE.includes(page);

/** Navigate commands come from the host nav so they keep PHP's role checks. */
const navItems = computed(() =>
    (window.TASK_VIEWS_CONFIG?.nav ?? [])
        .filter((it) => it && it.url && it.key !== page)
        .map((it) => ({
            id: `nav-${it.key}`,
            icon: it.icon || "mdi-arrow-right",
            title: `Go to ${it.label}`,
            keywords: `navigate open ${it.label}`,
            run: () => { window.location.href = it.url; },
        })),
);

function runCreateTask() {
    if (typeof window.creatask === "function") window.creatask();
    else window.location.href = `${window.TASK_VIEWS_CONFIG?.baseUrl ?? "/"}dashboard#/tasks`;
}
function runCreateTaskGroup() {
    if (typeof window.addEditMilestone === "function") {
        window.addEditMilestone("", "", "", "", "", "");
    }
}

/** Static (non-task) command groups, gated by what the current page supports. */
const staticGroups = computed(() => {
    const groups = [{ key: "nav", label: "Navigate", items: navItems.value }];

    if (filterable) {
        groups.push({
            key: "filter",
            label: "Filters",
            items: [
                { id: "flt-mine", icon: "mdi-account-check-outline", title: "My tasks", keywords: "assigned to me mine", run: () => store.applyPreset("assigntome") },
                { id: "flt-unassigned", icon: "mdi-account-off-outline", title: "Unassigned tasks", keywords: "nobody none", run: () => { store.clearFilters(); store.assignee = ["Unassigned"]; store.scheduleLoad(); } },
                { id: "flt-overdue", icon: "mdi-alert-circle-outline", title: "Overdue tasks", keywords: "late due", run: () => store.applyPreset("overdue") },
                { id: "flt-high", icon: "mdi-flag-outline", title: "High priority", keywords: "priority urgent", run: () => store.applyPreset("highpriority") },
                { id: "flt-fav", icon: "mdi-star-outline", title: "Favourite tasks", keywords: "starred favorite", run: () => store.applyPreset("favourite") },
                { id: "flt-clear", icon: "mdi-filter-remove-outline", title: "Clear all filters", keywords: "reset remove", run: () => store.clearFilters() },
            ],
        });
    }

    groups.push({
        key: "create",
        label: "Create",
        items: [
            { id: "new-task", icon: "mdi-plus-box-outline", title: "New task", keywords: "create add", run: runCreateTask },
            { id: "new-group", icon: "mdi-folder-plus-outline", title: "New task group", keywords: "create add milestone group", run: runCreateTaskGroup },
        ],
    });

    groups.push({
        key: "help",
        label: "Help",
        items: [
            { id: "help-shortcuts", icon: "mdi-keyboard-outline", title: "Keyboard shortcuts", keywords: "help keys cheatsheet", run: () => openHelp() },
        ],
    });

    return groups;
});

function matches(item, q) {
    if (!q) return true;
    return `${item.title} ${item.keywords ?? ""}`.toLowerCase().includes(q);
}

/** Task results are a client-side search over the already-loaded rows. */
const taskItems = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return [];
    return store.tasks
        .filter((t) => `${t.ref} ${t.title} ${t.assignee}`.toLowerCase().includes(q))
        .slice(0, 8)
        .map((t) => ({
            id: `task-${t.id}`,
            icon: "mdi-checkbox-marked-circle-outline",
            title: `${t.ref} · ${t.title || "Untitled"}`,
            hint: t.statusLabel || t.status || "",
            run: () => openTask(t),
        }));
});

/** Visible groups: matching tasks lead, then the static groups filtered by query. */
const groups = computed(() => {
    const q = query.value.trim().toLowerCase();
    const out = [];
    if (taskItems.value.length) out.push({ key: "tasks", label: "Tasks", items: taskItems.value });
    for (const g of staticGroups.value) {
        const items = g.items.filter((it) => matches(it, q));
        if (items.length) out.push({ key: g.key, label: g.label, items });
    }
    let i = 0;
    for (const g of out) for (const it of g.items) it._index = i++;
    return out;
});

const flatItems = computed(() => groups.value.flatMap((g) => g.items));

watch(query, () => { activeIndex.value = 0; });

// Focus the search box when the palette opens. Vuetify's overlay otherwise
// leaves focus on <body>, so the first keystrokes would be lost; the short
// delay lets the overlay mount and its open transition settle first.
watch(paletteOpen, (isOpen) => {
    if (!isOpen) return;
    activeIndex.value = 0;
    setTimeout(() => inputEl.value?.focus(), 60);
});

function run(item) {
    if (!item?.run) return;
    closePalette();
    item.run();
}

function onKeydown(e) {
    const n = flatItems.value.length;
    if (e.key === "ArrowDown") {
        e.preventDefault();
        if (n) activeIndex.value = (activeIndex.value + 1) % n;
        scrollActiveIntoView();
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (n) activeIndex.value = (activeIndex.value - 1 + n) % n;
        scrollActiveIntoView();
    } else if (e.key === "Enter") {
        e.preventDefault();
        run(flatItems.value[activeIndex.value]);
    } else if (e.key === "Escape") {
        closePalette();
    }
}

function scrollActiveIntoView() {
    nextTick(() => {
        const el = document.getElementById(`tv-cmdk-item-${activeIndex.value}`);
        if (el) el.scrollIntoView({ block: "nearest" });
    });
}

function reset() {
    query.value = "";
    activeIndex.value = 0;
}
</script>

<template>
    <v-dialog
        v-model="paletteOpen"
        max-width="600"
        transition="fade-transition"
        @after-leave="reset"
    >
        <div class="tv-cmdk">
            <div class="tv-cmdk__search">
                <v-icon icon="mdi-magnify" size="20" class="tv-cmdk__searchicon" aria-hidden="true" />
                <input
                    ref="inputEl"
                    v-model="query"
                    class="tv-cmdk__input"
                    placeholder="Search tasks, jump to a view, run a command…"
                    spellcheck="false"
                    autofocus
                    aria-label="Search tasks and commands"
                    @keydown="onKeydown"
                >
                <span class="tv-cmdk__esc">Esc</span>
            </div>

            <div ref="listEl" class="tv-cmdk__list">
                <template v-for="group in groups" :key="group.key">
                    <div class="tv-cmdk__grouplabel">{{ group.label }}</div>
                    <button
                        v-for="item in group.items"
                        :id="`tv-cmdk-item-${item._index}`"
                        :key="item.id"
                        type="button"
                        class="tv-cmdk__item"
                        :class="{ 'is-active': item._index === activeIndex }"
                        @click="run(item)"
                        @mousemove="activeIndex = item._index"
                    >
                        <v-icon :icon="item.icon" size="18" class="tv-cmdk__itemicon" aria-hidden="true" />
                        <span class="tv-cmdk__itemtitle">{{ item.title }}</span>
                        <span v-if="item.hint" class="tv-cmdk__itemhint">{{ item.hint }}</span>
                    </button>
                </template>

                <div v-if="!flatItems.length" class="tv-cmdk__empty">
                    No matches for “{{ query }}”.
                </div>
            </div>

            <div class="tv-cmdk__foot">
                <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
                <span><kbd>↵</kbd> select</span>
                <span><kbd>esc</kbd> close</span>
                <span class="tv-cmdk__foothelp"><kbd>?</kbd> shortcuts</span>
            </div>
        </div>
    </v-dialog>
</template>

<style scoped>
.tv-cmdk {
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    overflow: hidden;
    box-shadow: var(--tv-shadow-pop);
    display: flex;
    flex-direction: column;
    max-block-size: 70vh;
    font-family: var(--tv-font);
}

.tv-cmdk__search {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-block-end: 1px solid var(--tv-rule);
    flex: none;
}

.tv-cmdk__searchicon {
    color: var(--tv-muted);
}

.tv-cmdk__input {
    flex: 1;
    border: 0;
    outline: none;
    background: transparent;
    font: inherit;
    font-size: var(--tv-size-title);
    color: var(--tv-ink);
}

.tv-cmdk__input::placeholder {
    color: var(--tv-muted);
}

.tv-cmdk__esc {
    flex: none;
    padding: 2px 6px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    font-size: 11px;
    color: var(--tv-muted);
}

.tv-cmdk__list {
    overflow-y: auto;
    padding: 6px;
}

.tv-cmdk__grouplabel {
    padding: 8px 10px 4px;
    font-size: var(--tv-size-label);
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--tv-muted);
}

.tv-cmdk__item {
    display: flex;
    align-items: center;
    gap: 10px;
    inline-size: 100%;
    padding: 8px 10px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    font: inherit;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
    text-align: start;
    cursor: pointer;
}

.tv-cmdk__item.is-active {
    background: var(--tv-brand-soft);
}

.tv-cmdk__itemicon {
    flex: none;
    color: var(--tv-ink-2);
}

.tv-cmdk__itemtitle {
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.tv-cmdk__itemhint {
    flex: none;
    font-size: var(--tv-size-meta);
    color: var(--tv-muted);
}

.tv-cmdk__empty {
    padding: 20px 12px;
    text-align: center;
    font-size: var(--tv-size-body);
    color: var(--tv-muted);
}

.tv-cmdk__foot {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px 14px;
    border-block-start: 1px solid var(--tv-rule);
    font-size: var(--tv-size-meta);
    color: var(--tv-muted);
    flex: none;
}

.tv-cmdk__foothelp {
    margin-inline-start: auto;
}

.tv-cmdk__foot kbd {
    display: inline-block;
    min-inline-size: 16px;
    padding: 1px 5px;
    margin-inline-end: 2px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: 4px;
    background: var(--tv-sub);
    font-family: var(--tv-font);
    font-size: 11px;
    line-height: 1.4;
    text-align: center;
    color: var(--tv-ink-2);
}
</style>
