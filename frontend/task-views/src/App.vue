<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import PageNav from "@/components/PageNav.vue";
import ViewSwitcher from "@/components/ViewSwitcher.vue";
import TaskToolbar from "@/components/TaskToolbar.vue";
import ViewsPage from "@/pages/ViewsPage.vue";
import KanbanPage from "@/pages/KanbanPage.vue";
import CalendarPage from "@/pages/CalendarPage.vue";
import OverviewPage from "@/pages/OverviewPage.vue";
import SubtasksPage from "@/pages/SubtasksPage.vue";
import MyWorksPage from "@/pages/MyWorksPage.vue";

const store = useTaskStore();

/**
 * Which page this mount renders — set per-route by the host template
 * (TASK_VIEWS_CONFIG.page). One bundle serves every task tab.
 */
const PAGES = {
    views: ViewsPage,
    kanban: KanbanPage,
    calendar: CalendarPage,
    overview: OverviewPage,
    subtasks: SubtasksPage,
    myworks: MyWorksPage,
};

const page = computed(() => {
    const p = window.TASK_VIEWS_CONFIG?.page;
    return PAGES[p] ? p : "views";
});
const pageComponent = computed(() => PAGES[page.value]);

// The filter toolbar and view switcher only belong to list-shaped pages.
/*
 * Calendar was left out, so it silently ignored the filters every other page
 * shares — the store filters the same set of tasks it draws from.
 */
const showToolbar = computed(() =>
    ["views", "subtasks", "myworks", "kanban", "calendar"].includes(page.value)
);
const showViewSwitcher = computed(() => page.value === "views");

/** Escape hatch to the old list, supplied by the host template. */
const legacyList = computed(() => window.TASK_VIEWS_CONFIG?.legacyList ?? null);

/**
 * Opens the app's own Create Task modal rather than reimplementing it. The
 * markup is rendered globally by templates/element/popup.php and creatask()
 * lives in script_v1.js — so the form, validation and save path are identical
 * to the Task List.
 *
 * Resolved at click time, not at setup: script_v1.js is deferred, so
 * window.creatask does not exist yet while this component initialises. Testing
 * it up front hid the button permanently.
 */
/*
 * Task groups use the app's own create form (addEditMilestone -> the
 * milestones/ajax_new_milestone popup), not a reimplementation — same reasoning
 * as Create task above: one form, one validation path, one save.
 *
 * Resolved on click rather than gated by a computed: window.addEditMilestone is
 * not reactive, so a computed evaluates once at mount and stays wrong if
 * script_v1.js has not run yet.
 */
function createTaskGroup() {
    if (typeof window.addEditMilestone === "function") {
        window.addEditMilestone("", "", "", "", "", "");
    }
}

function createTask() {
    if (typeof window.creatask === "function") {
        window.creatask();
        return;
    }
    // Scripts still loading — fall back to the page that definitely has it.
    window.location.href = `${window.TASK_VIEWS_CONFIG?.baseUrl ?? "/"}dashboard#/tasks`;
}

/*
 * The app's own popups (task group, create task) live outside Vue and finish by
 * telling the list to reload. Exposing one function keeps that contract small.
 */
onMounted(() => {
    window.taskViewsRefresh = () => store.refresh();
});

onBeforeUnmount(() => {
    if (window.taskViewsRefresh) delete window.taskViewsRefresh;
});

/*
 * Success feedback. A brief toast is right for "that worked" — it needs no
 * action and should not stay in the way.
 *
 * Failures do NOT come through here. A rolled-back edit is a message the user
 * has to read and act on, and a toast at the foot of the page, gone in three
 * and a half seconds, is the wrong place for it (public issue #2). Those go to
 * the banner below, which sits directly above the rows and waits to be
 * dismissed.
 */
const toast = ref(null);
let toastTimer = null;

function showToast(text, tone) {
    toast.value = { text, tone };
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (toast.value = null), 3500);
}

watch(() => store.notice, (n) => { if (n) { showToast(n, "ok"); store.notice = null; } });

function reload() {
    store.load();
}

onMounted(() => {
    store.page = page.value;

    // Filter preset from an Overview tile (?f=overdue|unassigned|completed).
    const preset = new URLSearchParams(window.location.search).get("f");
    if (preset === "overdue") store.due = ["overdue"];
    else if (preset === "unassigned") store.assignee = ["Unassigned"];
    else if (preset === "completed") store.status = ["closed"];
    else if (preset?.startsWith("status:")) store.status = [preset.slice(7)];
    else if (preset?.startsWith("priority:")) store.priority = [preset.slice(9)];

    // Subtask View opens grouped by task group — the tree is easier to read
    // when it is already divided the way the work is organised.
    if (page.value === "subtasks") store.groupBy = "taskGroup";
    store.load();

    /*
     * The Create Task popup is legacy jQuery living outside this app, so it
     * cannot reach the store. It raises this event after a successful save
     * (script_v1.js, alongside the legacy refreshTasks flag) and we reload.
     */
    document.addEventListener("orangescrum:task-saved", reload);
});

onBeforeUnmount(() => {
    document.removeEventListener("orangescrum:task-saved", reload);
});
</script>

<template>
    <div class="tv-app">
        <!-- The page's single bar: navigation left, contextual actions right.
             No page-title row — the active tab already says where you are. -->
        <header class="tv-head">
            <PageNav />
            <div class="tv-head__right">
                <ViewSwitcher v-if="showViewSwitcher" />
                <button
                    type="button"
                    class="tv-secondary"
                    @click="createTaskGroup"
                >
                    <v-icon icon="mdi-plus" size="14" aria-hidden="true" />
                    <span>New task group</span>
                </button>
                <v-btn
                    class="tv-create"
                    color="primary"
                    variant="flat"
                    density="comfortable"
                    prepend-icon="mdi-plus"
                    @click="createTask"
                >
                    Create task
                </v-btn>
                <v-menu v-if="legacyList" location="bottom end" offset="4">
                    <template #activator="{ props: menu }">
                        <button
                            v-bind="menu"
                            type="button"
                            class="tv-more"
                            title="More"
                            aria-label="More"
                        >
                            <v-icon icon="mdi-dots-horizontal" size="16" aria-hidden="true" />
                        </button>
                    </template>
                    <div class="tv-pop">
                        <a :href="legacyList.url" class="tv-pop__row">
                            <v-icon icon="mdi-table-arrow-left" size="14" aria-hidden="true" />
                            {{ legacyList.label }}
                        </a>
                    </div>
                </v-menu>
            </div>
        </header>

        <TaskToolbar v-if="showToolbar" />

        <!-- Directly above the list, so it is beside the row that was edited
             rather than at the foot of the page. It stays until dismissed. -->
        <div v-if="store.saveError" class="tv-savefail" role="alert">
            <v-icon icon="mdi-alert-circle-outline" size="15" aria-hidden="true" />
            <span>{{ store.saveError }}</span>
            <button
                type="button"
                class="tv-savefail__x"
                aria-label="Dismiss"
                @click="store.dismissSaveError()"
            >
                <v-icon icon="mdi-close" size="13" />
            </button>
        </div>

        <transition name="tv-toast">
            <div v-if="toast" class="tv-toast" :class="`tv-toast--${toast.tone}`" role="status">
                <v-icon :icon="toast.tone === 'error' ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline'" size="16" aria-hidden="true" />
                {{ toast.text }}
            </div>
        </transition>

        <main class="tv-body">
            <div v-if="store.loading" class="tv-state tv-meta">Loading tasks…</div>

            <div v-else-if="store.error" class="tv-state">
                <p>{{ store.error }}</p>
                <button type="button" class="tv-retry" @click="store.load()">Try again</button>
            </div>

            <div v-else-if="!store.tasks.length" class="tv-state">
                <p class="tv-meta">No tasks yet.</p>
                <button type="button" class="tv-retry" @click="createTask">
                    Create the first task
                </button>
            </div>

            <component :is="pageComponent" v-else />
        </main>
    </div>
</template>

<style scoped>
/* Sits flush inside the host page chrome — no card border or radius, which
   read as a widget floating in grey space rather than as a page. */
.tv-app {
    display: flex;
    flex-direction: column;
    min-block-size: 100%;
    background: var(--tv-paper);
}

/* The single bar. Navigation and the page's actions share one row, so there is
   exactly one horizontal rule between the chrome and the content. */
.tv-head {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-inline: 20px;
    border-block-end: 1px solid var(--tv-rule);
}

.tv-head__right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: none;
    margin-inline-start: auto;
}

.tv-body {
    flex: 1;
    min-block-size: 0;
}

.tv-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 56px 16px;
    text-align: center;
    color: var(--tv-muted);
}

.tv-retry {
    padding: 7px 14px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    font-weight: 500;
    color: var(--tv-ink);
    cursor: pointer;
}

.tv-retry:hover {
    border-color: var(--tv-brand);
}

.tv-legacy {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0 9px;
    block-size: 28px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-muted);
    text-decoration: none;
    white-space: nowrap;
}

.tv-legacy:hover {
    border-color: var(--tv-rule-strong);
    background: var(--tv-sub);
    color: var(--tv-ink);
}

/* Painted from --tv-brand rather than Vuetify's compiled primary so it tracks
   a theme change in the page, not just the value read when the app booted.
   White label matches the app's own primary buttons. */
.tv-savefail {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: #fdecea;
    border-block-end: 1px solid #f5c6cb;
    font-size: var(--tv-size-meta);
    color: #8a1f16;
}

.tv-savefail__x {
    margin-inline-start: auto;
    display: grid;
    place-items: center;
    inline-size: 22px;
    block-size: 22px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: inherit;
    cursor: pointer;
}

.tv-toast {
    position: fixed;
    inset-block-end: 24px;
    inset-inline-start: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: var(--tv-radius);
    background: var(--tv-ink);
    color: #fff;
    font-size: var(--tv-size-body);
    box-shadow: var(--tv-shadow-pop);
    z-index: 60;
}

.tv-toast--error {
    background: #b3261e;
}

.tv-toast-enter-active,
.tv-toast-leave-active {
    transition: opacity 0.2s, transform 0.2s;
}

.tv-toast-enter-from,
.tv-toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(8px);
}

.tv-more {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    inline-size: 28px;
    block-size: 28px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    color: var(--tv-muted);
    cursor: pointer;
}

.tv-more:hover {
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}

/* The "..." menu content. Same class names as the CompactRow/FacetFilter
   popups, but those styles are scoped to their own components, so the card
   must be styled here too or the menu renders as unstyled floating text. */
.tv-pop {
    min-inline-size: 180px;
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
    border-radius: var(--tv-radius);
    background: transparent;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
    text-decoration: none;
    cursor: pointer;
}

.tv-pop__row:hover {
    background: var(--tv-sub);
}

.tv-secondary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    /* Matches .tv-legacy and the Create task button either side of it. */
    height: 28px;
    padding: 0 12px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink-2);
    white-space: nowrap;
    cursor: pointer;
}

.tv-secondary:hover {
    border-color: var(--tv-brand);
    color: var(--tv-ink);
}

.tv-create {
    text-transform: none;
    letter-spacing: 0;
    font-weight: 600;
    font-size: var(--tv-size-meta);
    background: var(--tv-brand) !important;
    color: #fff !important;
}

.tv-create:hover {
    filter: brightness(0.93);
}
</style>
