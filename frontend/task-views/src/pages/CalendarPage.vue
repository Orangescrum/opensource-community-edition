<script>
import { ref as moduleRef } from "vue";

/*
 * App.vue swaps this page for "Loading tasks…" on every load, so any filter
 * change remounts it. Kept outside setup so the open month and the date-only
 * filters survive that — changing a filter in October used to land you back
 * on the current month.
 */
const keptCursor = moduleRef(null);
const keptMyDays = moduleRef(new Set());
</script>

<script setup>
import { computed, ref } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { formatDue, statusMeta } from "@/data/tasks";
import { fetchWorkload } from "@/data/mutations";
import { PRESETS } from "@/data/serverFilters";

import { openTask } from "@/utils/taskLink";

const store = useTaskStore();

// Anchor month: view state, not data. Starts on the current month.
const cursor = keptCursor;
if (!cursor.value) cursor.value = startOfMonth(new Date());

function startOfMonth(d) {
    return new Date(d.getFullYear(), d.getMonth(), 1);
}

const monthLabel = computed(() =>
    cursor.value.toLocaleDateString(undefined, { month: "long", year: "numeric" })
);

const WEEKDAYS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

/**
 * The day a task sits on: its start date, or its due date when it has no
 * start date. Dragging moves the start date; the due date stays the deadline.
 */
const scheduledOn = (t) => t.start ?? t.due;

const byDate = computed(() => {
    const map = {};
    store.visible.forEach((t) => {
        const day = scheduledOn(t);
        if (!day) return;
        (map[day] ??= []).push(t);
    });
    return map;
});

const unscheduled = computed(() => store.visible.filter((t) => !scheduledOn(t)));

// Build a 6-row grid starting on Monday.
const cells = computed(() => {
    const first = cursor.value;
    const offset = (first.getDay() + 6) % 7; // Monday = 0
    const start = new Date(first);
    start.setDate(first.getDate() - offset);

    const out = [];
    const todayIso = isoOf(new Date());
    for (let i = 0; i < 42; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const iso = isoOf(d);
        out.push({
            iso,
            day: d.getDate(),
            inMonth: d.getMonth() === first.getMonth(),
            isToday: iso === todayIso,
            tasks: forDay(iso),
            // Before the date-only filter, so the toggle stays on a day it emptied.
            hasTasks: Boolean(byDate.value[iso]?.length),
        });
    }
    return out;
});

/*
 * A day keeps a fixed height that fits this many tasks, so one busy day can't
 * stretch its whole week row; past it the day's list scrolls, and the expand
 * icon opens the full detail.
 */
const MAX_VISIBLE = 5;

/** The day whose full list is open, or null. */
const dayIso = ref(null);
const dayOpen = computed({
    get: () => dayIso.value !== null,
    set: (open) => {
        if (!open) dayIso.value = null;
    },
});
// Read live from byDate so the list follows a reschedule made meanwhile.
const dayTasks = computed(() => (dayIso.value ? forDay(dayIso.value) : []));
const dayDone = computed(() => dayTasks.value.filter((t) => t.status === "resolved" || t.status === "closed").length);

/*
 * The task detail opens as the legacy slider underneath this dialog's overlay,
 * so the dialog has to go once the task is open — otherwise it sits on top of
 * the detail it just opened.
 */
function openFromDay(task, event) {
    if (openTask(task, event)) dayIso.value = null;
}

/*
 * My Task toggle. It is the "Assigned to me" preset — the same one the toolbar's
 * preset menu and the command palette set — so it filters server-side, shows
 * as a filter chip, is captured by saved views and is cleared by Reset like any
 * other filter. One store state means every day's toggle, the grid and the
 * Not scheduled list always agree.
 */
const MY_PRESET = "assigntome";
const myTasksOn = computed(() => store.preset === MY_PRESET);

/*
 * Offered when narrowing to your own tasks would change something: you can see
 * other people's tasks, or the list spans several people and you are one of
 * them. Read from the loaded rows rather than `visible`, so a search or filter
 * can't hide it — and kept while it is on, because then the rows are only
 * yours and would otherwise take away the way back.
 */
const canViewAll = Boolean(window.TASK_VIEWS_CONFIG?.permissions?.viewAllTasks);
const myToggleAvailable = computed(() => {
    if (myTasksOn.value || myDays.value.size || canViewAll) return true;
    const people = new Set(store.tasks.map((t) => t.assigneeId).filter(Boolean));
    return people.size > 1 && store.tasks.some((t) => t.assignedToMe);
});

/*
 * Dates narrowed to your tasks on their own, without the all-dates preset. This
 * only hides rows on those days, so it is view state for this visit, like the
 * open month: it is not sent to the server or saved in a view, and Reset or
 * applying a saved view clears it along with the filters.
 */
const myDays = keptMyDays;
store.$onAction(({ name }) => {
    if (name === "clearFilters" || name === "applySavedView") myDays.value = new Set();
});

function setMyDay(iso, on) {
    const next = new Set(myDays.value);
    if (on) next.add(iso);
    else next.delete(iso);
    myDays.value = next;
}

/** A day's tasks, narrowed to yours when that date has its own My Task filter. */
function forDay(iso) {
    const tasks = byDate.value[iso] ?? [];
    return myDays.value.has(iso) ? tasks.filter((t) => t.assignedToMe) : tasks;
}

/** "all" when the all-dates preset is on, "day" for a date-only filter, else null. */
function myScope(iso) {
    if (myTasksOn.value) return "all";
    return myDays.value.has(iso) ? "day" : null;
}

/** The toggle change waiting on the user's choice — {iso, on, scope} — or null. */
const myConfirm = ref(null);
const myConfirmOpen = computed({
    get: () => myConfirm.value !== null,
    set: (open) => {
        if (!open) myConfirm.value = null;
    },
});

function myToggleLabel(iso) {
    const scope = myScope(iso);
    if (scope === "all") return "My Task on for all dates: showing only tasks assigned to you";
    if (scope === "day") return `My Task on for ${longDate(iso)} only: showing only tasks assigned to you`;
    return "My Task: show only tasks assigned to you";
}

/** Another preset gets replaced, since presets are one at a time. */
const replacedPreset = computed(() =>
    store.preset && store.preset !== MY_PRESET
        ? PRESETS.find((p) => p.value === store.preset)?.label ?? null
        : null
);

function askMyTasks(iso) {
    const scope = myScope(iso);
    myConfirm.value = { iso, on: !scope, scope };
}

/** This date only: narrow (or restore) just this day; nothing is refetched. */
function confirmMyDay() {
    const { iso, on } = myConfirm.value;
    myConfirm.value = null;
    setMyDay(iso, on);
}

/*
 * Every date: the "Assigned to me" preset. setFilter rather than applyPreset:
 * the preset implies no status, priority or due filter, so the others the user
 * has set stay as they are. Date-only filters are dropped either way — on, the
 * preset already covers them; off, "all dates" means all of them.
 */
function confirmMyAll() {
    const on = myConfirm.value?.on;
    myConfirm.value = null;
    myDays.value = new Set();
    if (on) store.setFilter("preset", MY_PRESET);
    else if (myTasksOn.value) store.setFilter("preset", "");
}

/** Done reads as a tick, in progress as a filled dot, not started as an open circle. */
function statusGlyph(status) {
    if (status === "resolved" || status === "closed") return "mdi-check";
    if (status === "in_progress") return "mdi-circle-medium";
    return "mdi-circle-outline";
}

function isoOf(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

function step(delta) {
    cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + delta, 1);
}

function today() {
    cursor.value = startOfMonth(new Date());
}

/** "Fri, 9 Oct 2026" — the dialog names the exact day, not "Tomorrow". */
function longDate(iso) {
    return new Date(`${iso}T00:00:00`).toLocaleDateString(undefined, {
        weekday: "short",
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

/** Two-letter badge for the assignee; unassigned tasks get none. */
function initials(t) {
    if (!t.assigneeId) return "";
    return t.assignee
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
}

/** Opens the app's Create Task form with `iso` as the start date. */
function openCreate(iso) {
    if (typeof window.creatask !== "function") return;
    const d = new Date(`${iso}T00:00:00`);
    const text = d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
    window.creatask("", "", "", "", text);
}

/** A click on a day's empty space does the same as its + button. */
function createOn(cell, event) {
    if (event.target.closest(".cal__task, .cal__tools")) return;
    openCreate(cell.iso);
}

function shortName(t) {
    const name = (t.title || "").trim() || t.ref;
    return name.length > 60 ? `${name.slice(0, 59)}…` : name;
}

/*
 * Drag and drop reschedules. The rules are checked while hovering, so a day
 * shows whether it will take the task before it is let go; the server checks
 * them again on save.
 */
const dragging = ref(null);
const over = ref(null);
const dragToday = ref(null);

/** A short reason `task` can't go on `iso` (the cell hint), or null. */
function dropHint(task, iso) {
    if (iso < dragToday.value) return "Past date";
    if (task.due && iso > task.due) return "After due date";
    return null;
}

/** The full message for letting go over a day the task can't go on. */
function dropError(task, iso) {
    if (iso < dragToday.value) return "A task can't be scheduled on a past date.";
    if (task.due && iso > task.due) {
        return `This task is due on ${longDate(task.due)}; schedule it on or before its due date.`;
    }
    return null;
}

function onDragStart(task, event) {
    dragging.value = task;
    dragToday.value = isoOf(new Date());
    event.dataTransfer.effectAllowed = "move";
    // Firefox ignores a drag that carries no data.
    event.dataTransfer.setData("text/plain", task.id);
}

function onDragEnd() {
    dragging.value = null;
    over.value = null;
}

/*
 * Every day accepts the drop, valid or not, so letting go over a blocked day
 * says why instead of the task silently snapping back.
 */
function onDragOver(cell, event) {
    if (!dragging.value) return;
    event.preventDefault();
    event.dataTransfer.dropEffect = "move";
    over.value = cell.iso;
}

function onDragLeave(cell, event) {
    if (over.value === cell.iso && !event.currentTarget.contains(event.relatedTarget)) {
        over.value = null;
    }
}

function onDrop(cell) {
    const task = dragging.value;
    onDragEnd();
    if (!task || cell.iso === scheduledOn(task)) return;

    const error = dropError(task, cell.iso);
    if (error) {
        store.saveError = error;
        return;
    }
    // With no due date there is nothing to weigh the move against, so it saves
    // straight away.
    if (!task.due) {
        save(task, cell.iso);
        return;
    }
    ask(task, cell.iso);
}

async function save(task, iso) {
    const ok = await store.reschedule(task.id, iso);
    if (ok) store.notice = `${shortName(task)} scheduled for ${formatDue(iso)}`;
}

/*
 * A task with a due date changes only once the user confirms. For an assigned
 * task the dialog first shows what the assignee already has on the target day.
 */
const pending = ref(null);
const confirmOpen = computed({
    get: () => pending.value !== null,
    set: (open) => {
        if (!open && !pending.value?.saving) pending.value = null;
    },
});

async function ask(task, iso) {
    const entry = { task, iso, workload: null, loading: Boolean(task.assigneeId), error: null, saving: false };
    pending.value = entry;
    if (!task.assigneeId) return;

    let workload = null;
    let error = null;
    try {
        workload = await fetchWorkload(task, iso);
    } catch (e) {
        error = e?.message || "Could not check the assignee's workload.";
    }
    // Cancelled, or replaced by another drop, while this loaded.
    if (pending.value?.task !== task || pending.value?.iso !== iso) return;
    pending.value = { ...pending.value, workload, error, loading: false };
}

const workloadText = computed(() => {
    const w = pending.value?.workload;
    if (!w?.assigned) return null;
    if (!w.tasks) return "No other tasks assigned for this date.";
    const tasks = `${w.tasks} ${w.tasks === 1 ? "task" : "tasks"}`;
    const hours = `${w.hours} estimated ${w.hours === 1 ? "hour" : "hours"}`;
    return `${tasks} already assigned for this date, with ${hours} allocated.`;
});

async function confirmReschedule() {
    const entry = pending.value;
    if (!entry || entry.saving || entry.loading) return;
    pending.value = { ...entry, saving: true };
    await save(entry.task, entry.iso);
    pending.value = null;
}
</script>

<template>
    <div class="cal">
        <div class="cal__bar">
            <button type="button" class="cal__nav" aria-label="Previous month" @click="step(-1)">
                <v-icon icon="mdi-chevron-left" size="18" />
            </button>
            <h2 class="cal__month">{{ monthLabel }}</h2>
            <button type="button" class="cal__nav" aria-label="Next month" @click="step(1)">
                <v-icon icon="mdi-chevron-right" size="18" />
            </button>
            <button type="button" class="cal__today" @click="today">Today</button>
            <span class="cal__help tv-meta">Click a day to add a task; drag a task to another day to reschedule it.</span>
        </div>

        <div class="cal__grid" role="grid" :class="{ 'is-dragging': dragging }">
            <div v-for="w in WEEKDAYS" :key="w" class="cal__wd tv-label">{{ w }}</div>

            <div
                v-for="cell in cells"
                :key="cell.iso"
                class="cal__cell"
                :class="{
                    'is-out': !cell.inMonth,
                    'is-today': cell.isToday,
                    'is-blocked': dragging && dropHint(dragging, cell.iso),
                    'is-over': dragging && over === cell.iso,
                }"
                @dragover="onDragOver(cell, $event)"
                @dragleave="onDragLeave(cell, $event)"
                @drop.prevent="onDrop(cell)"
                @click="createOn(cell, $event)"
            >
                <div class="cal__head">
                    <span class="cal__day">
                        <span class="cal__date">{{ cell.day }}</span>
                        <button
                            v-if="myToggleAvailable && cell.hasTasks && !dragging"
                            type="button"
                            class="cal__tool cal__mine"
                            :class="{ 'is-on': myScope(cell.iso) }"
                            :aria-pressed="myScope(cell.iso) ? 'true' : 'false'"
                            :title="myToggleLabel(cell.iso)"
                            :aria-label="myToggleLabel(cell.iso)"
                            @click.stop="askMyTasks(cell.iso)"
                        >
                            <v-icon :icon="myScope(cell.iso) ? 'mdi-account-check' : 'mdi-account-check-outline'" size="14" />
                        </button>
                    </span>
                    <span v-if="!dragging" class="cal__tools">
                        <button
                            type="button"
                            class="cal__tool cal__add"
                            :title="`Add a task on ${longDate(cell.iso)}`"
                            :aria-label="`Add a task on ${longDate(cell.iso)}`"
                            @click.stop="openCreate(cell.iso)"
                        >
                            <v-icon icon="mdi-plus" size="16" />
                        </button>
                        <button
                            v-if="cell.tasks.length > MAX_VISIBLE"
                            type="button"
                            class="cal__tool cal__expand"
                            :title="`Expand for full detail: all ${cell.tasks.length} tasks on ${longDate(cell.iso)}`"
                            :aria-label="`Expand ${longDate(cell.iso)}`"
                            @click.stop="dayIso = cell.iso"
                        >
                            <v-icon icon="mdi-arrow-expand" size="14" />
                        </button>
                    </span>
                </div>
                <span v-if="dragging && over === cell.iso && dropHint(dragging, cell.iso)" class="cal__hint">
                    {{ dropHint(dragging, cell.iso) }}
                </span>
                <ul class="cal__tasks" :class="{ 'is-scrolling': cell.tasks.length > MAX_VISIBLE }">
                    <li
                        v-for="t in cell.tasks"
                        :key="t.id"
                        class="cal__task tv-rail"
                        :class="[`st-${t.status}`, { 'is-dragging': dragging?.id === t.id, 'is-saving': store.saving.has(t.id) }]"
                        :title="t.title"
                        draggable="true"
                        @dragstart="onDragStart(t, $event)"
                        @dragend="onDragEnd"
                        @click="openTask(t, $event)"
                    >
                        <span v-if="initials(t)" class="cal__avatar" :title="t.assignee">{{ initials(t) }}</span>
                        <span class="cal__task-txt"><span class="cal__ref">{{ t.ref }}</span> {{ t.title }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <v-dialog v-model="dayOpen" max-width="520">
            <div v-if="dayIso" class="cal-day">
                <div class="cal-day__head">
                    <h2 class="cal-day__title tv-label">{{ longDate(dayIso) }}</h2>
                    <button type="button" class="cal-day__collapse" aria-label="Collapse" title="Collapse" @click="dayIso = null">
                        <v-icon icon="mdi-arrow-collapse" size="16" />
                    </button>
                </div>

                <div class="cal-day__body">
                    <div class="cal-day__progress">
                        <div class="cal-day__progress-row">
                            <span>Completed</span>
                            <strong>{{ dayDone }} / {{ dayTasks.length }}</strong>
                        </div>
                        <div class="cal-day__bar" role="progressbar" :aria-valuenow="dayDone" aria-valuemin="0" :aria-valuemax="dayTasks.length">
                            <span :style="{ inlineSize: `${dayTasks.length ? (dayDone / dayTasks.length) * 100 : 0}%` }" />
                        </div>
                    </div>

                    <ul class="cal-day__list">
                        <li
                            v-for="t in dayTasks"
                            :key="t.id"
                            class="cal-day__item"
                            :class="`st-${t.status}`"
                            :title="t.title"
                            @click="openFromDay(t, $event)"
                        >
                            <v-icon class="cal-day__glyph" :icon="statusGlyph(t.status)" size="14" />
                            <span class="cal-day__ref">{{ t.ref }}</span>
                            <span class="cal-day__name">
                                <span class="cal-day__text">{{ t.title }}</span>
                                <span class="cal-day__meta">{{ t.assignee }}<template v-if="t.due"> · Due {{ formatDue(t.due) }}</template></span>
                            </span>
                            <span class="cal-day__status">{{ t.statusLabel || statusMeta(t.status).label }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </v-dialog>

        <div v-if="unscheduled.length" class="cal__undated">
            <span class="tv-label">Not scheduled</span>
            <ul class="cal__undated-list">
                <li
                    v-for="t in unscheduled"
                    :key="t.id"
                    class="cal__task tv-rail"
                    :class="[`st-${t.status}`, { 'is-dragging': dragging?.id === t.id }]"
                    draggable="true"
                    @dragstart="onDragStart(t, $event)"
                    @dragend="onDragEnd"
                    @click="openTask(t, $event)"
                >
                    <span class="cal__task-txt"><span class="cal__ref">{{ t.ref }}</span> {{ t.title }}</span>
                </li>
            </ul>
        </div>

        <v-dialog v-model="myConfirmOpen" max-width="440">
            <div v-if="myConfirm" class="cal-dlg">
                <div class="cal-dlg__head">
                    <h2 class="cal-dlg__title">
                        <template v-if="myConfirm.on">Show only your tasks?</template>
                        <template v-else-if="myConfirm.scope === 'day'">Show everyone's tasks on {{ longDate(myConfirm.iso) }}?</template>
                        <template v-else>Show everyone's tasks?</template>
                    </h2>
                    <button type="button" class="cal-dlg__close" aria-label="Close" title="Close" @click="myConfirm = null">
                        <v-icon icon="mdi-close" size="16" />
                    </button>
                </div>

                <!-- On: the user picks the scope. -->
                <template v-if="myConfirm.on">
                    <p class="cal-dlg__body cal-dlg__body--text">
                        Show only tasks assigned to you on <strong>{{ longDate(myConfirm.iso) }}</strong> only,
                        or on every date of the calendar and in Not scheduled.
                    </p>
                    <p class="cal-dlg__meta tv-meta">
                        <template v-if="replacedPreset">“Apply to all dates” replaces the “{{ replacedPreset }}” preset; your other filters stay applied.</template>
                        <template v-else>Your other filters stay applied.</template>
                    </p>
                    <div class="cal-dlg__actions">
                        <button type="button" class="cal-btn" @click="confirmMyDay">This date only</button>
                        <button type="button" class="cal-btn cal-btn--primary" @click="confirmMyAll">Apply to all dates</button>
                    </div>
                </template>

                <!-- Off, from a date-only filter. -->
                <template v-else-if="myConfirm.scope === 'day'">
                    <p class="cal-dlg__body cal-dlg__body--text">
                        <template v-if="myDays.size > 1">
                            Remove the My Task filter from this date only, or from all {{ myDays.size }} dates that have it.
                        </template>
                        <template v-else>The My Task filter will be removed from this date.</template>
                    </p>
                    <div class="cal-dlg__actions">
                        <button
                            v-if="myDays.size > 1"
                            type="button"
                            class="cal-btn"
                            @click="confirmMyAll"
                        >
                            Remove from all dates
                        </button>
                        <button type="button" class="cal-btn cal-btn--primary" @click="confirmMyDay">This date only</button>
                    </div>
                </template>

                <!--
                    Off, from the all-dates filter. Only your tasks were loaded,
                    so one day can't show everyone's without the rest.
                -->
                <template v-else>
                    <p class="cal-dlg__body cal-dlg__body--text">
                        The My Task filter is on for every date, so it is removed from every date of the calendar and from Not scheduled, not just this day.
                    </p>
                    <p class="cal-dlg__meta tv-meta">Your other filters stay applied.</p>
                    <div class="cal-dlg__actions">
                        <button type="button" class="cal-btn" @click="myConfirm = null">Cancel</button>
                        <button type="button" class="cal-btn cal-btn--primary" @click="confirmMyAll">Remove from all dates</button>
                    </div>
                </template>
            </div>
        </v-dialog>

        <v-dialog v-model="confirmOpen" max-width="440">
            <div v-if="pending" class="cal-dlg">
                <h2 class="cal-dlg__title">Reschedule “{{ shortName(pending.task) }}”?</h2>
                <p class="cal-dlg__body">
                    {{ pending.task.start ? longDate(pending.task.start) : "Not scheduled" }}
                    <v-icon icon="mdi-arrow-right" size="14" />
                    <strong>{{ longDate(pending.iso) }}</strong>
                </p>
                <p v-if="pending.task.due" class="cal-dlg__meta tv-meta">Due {{ longDate(pending.task.due) }}</p>

                <div v-if="pending.task.assigneeId" class="cal-dlg__load" aria-live="polite">
                    <span class="tv-label">Resource workload · {{ pending.task.assignee }}</span>
                    <p v-if="pending.loading" class="cal-dlg__load-txt tv-meta">Checking…</p>
                    <p v-else-if="pending.error" class="cal-dlg__load-txt cal-dlg__error">{{ pending.error }}</p>
                    <p v-else class="cal-dlg__load-txt">{{ workloadText }}</p>
                </div>
                <p v-else class="cal-dlg__meta tv-meta">Unassigned, so there is no workload to check.</p>

                <div class="cal-dlg__actions">
                    <button type="button" class="cal-btn" :disabled="pending.saving" @click="pending = null">Cancel</button>
                    <button
                        type="button"
                        class="cal-btn cal-btn--primary"
                        :disabled="pending.loading || pending.saving"
                        @click="confirmReschedule"
                    >
                        {{ pending.saving ? "Rescheduling…" : "Reschedule" }}
                    </button>
                </div>
            </div>
        </v-dialog>
    </div>
</template>

<style scoped>
.cal {
    padding: 16px 20px;
}

.cal__bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-block-end: 12px;
}

.cal__month {
    font-size: 16px;
    font-weight: 600;
    min-inline-size: 190px;
}

.cal__nav {
    display: grid;
    place-items: center;
    inline-size: 30px;
    block-size: 30px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    cursor: pointer;
}

.cal__nav:hover {
    background: var(--tv-sub);
}

.cal__today {
    margin-inline-start: 4px;
    block-size: 30px;
    padding: 0 12px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    font-size: var(--tv-size-meta);
    font-weight: 500;
    cursor: pointer;
}

.cal__grid {
    display: grid;
    /* minmax(0, …) so a long task title can't widen its column. */
    grid-template-columns: repeat(7, minmax(0, 1fr));
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius-lg);
    overflow: hidden;
}

.cal__wd {
    padding: 8px 10px;
    background: var(--tv-sub);
    border-block-end: 1px solid var(--tv-rule);
}

/*
 * Fixed height: 14px padding + 20px date + 4px gap + five 22px tasks with 3px
 * gaps (122px). The height doesn't depend on how many tasks the day has; a
 * longer list scrolls inside it.
 */
.cal__cell {
    display: flex;
    flex-direction: column;
    block-size: 160px;
    overflow: hidden;
    padding: 6px 6px 8px;
    border-inline-end: 1px solid var(--tv-rule);
    border-block-end: 1px solid var(--tv-rule);
    background: var(--tv-paper);
}

.cal__cell:nth-child(7n + 1) {
    /* first column has no left border already; nothing needed */
}

.cal__cell.is-out {
    background: var(--tv-sub);
    color: var(--tv-faint);
}

.cal__date {
    flex: none;
    line-height: 20px;
    font-size: var(--tv-size-meta);
    font-variant-numeric: tabular-nums;
    color: var(--tv-muted);
}

.cal__cell.is-today .cal__date {
    display: inline-grid;
    place-items: center;
    inline-size: 20px;
    block-size: 20px;
    border-radius: 50%;
    background: var(--tv-brand);
    color: #fff;
    font-weight: 600;
}

.cal__tasks {
    flex: none;
    list-style: none;
    margin: 4px 0 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.cal__task {
    padding: 2px 6px 2px 8px;
    border-radius: 3px;
    background: var(--tv-sub);
    /* Same type as a task-list row: 13px medium title, 12px medium number. */
    font-size: var(--tv-size-body);
    font-weight: 500;
    color: var(--tv-ink);
    cursor: pointer;
    overflow: hidden;
}

.cal__task:hover {
    background: var(--tv-sub-2);
}

.cal__task {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cal__avatar {
    flex: none;
    display: grid;
    place-items: center;
    inline-size: 16px;
    block-size: 16px;
    border-radius: 50%;
    background: var(--tv-brand-soft);
    color: var(--tv-brand);
    font-size: 9px;
    font-weight: 600;
    line-height: 1;
}

.cal__cell {
    cursor: cell;
}

.cal__ref {
    font-size: var(--tv-size-meta);
    font-weight: 500;
}

.cal__task-txt {
    min-inline-size: 0;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cal__undated {
    margin-block-start: 16px;
}

.cal__undated-list {
    list-style: none;
    margin: 8px 0 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.cal__undated-list .cal__task {
    max-inline-size: 260px;
}

.cal__help {
    margin-inline-start: auto;
}

.cal__cell {
    position: relative;
    transition: background-color 80ms;
}

/* While dragging, days the task can't go to recede; the hovered valid day lights up. */
.cal__grid.is-dragging .cal__cell.is-blocked {
    background: repeating-linear-gradient(
        -45deg,
        var(--tv-sub),
        var(--tv-sub) 6px,
        var(--tv-sub-2) 6px,
        var(--tv-sub-2) 12px
    );
    color: var(--tv-faint);
}

.cal__grid.is-dragging .cal__cell.is-over:not(.is-blocked) {
    background: var(--tv-brand-soft);
    box-shadow: inset 0 0 0 2px var(--tv-brand-ring);
}

.cal__grid.is-dragging .cal__cell.is-over.is-blocked {
    box-shadow: inset 0 0 0 2px #e8b4b0;
}

.cal__hint {
    position: absolute;
    inset-block-start: 6px;
    inset-inline-end: 6px;
    font-size: var(--tv-size-label);
    font-weight: 500;
    color: #b3261e;
}

.cal__grid .cal__tasks .cal__task {
    flex: none;
    block-size: 22px;
    line-height: 18px;
}

/* Exactly five rows tall (5 × 22px + 4 × 3px gaps); the rest scroll. */
.cal__grid .cal__tasks {
    max-block-size: 122px;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: thin;
    scrollbar-color: var(--tv-rule-strong) transparent;
}

/* Room for the scrollbar so it doesn't sit on top of the titles. */
.cal__grid .cal__tasks.is-scrolling {
    padding-inline-end: 2px;
}

.cal__head {
    flex: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    block-size: 20px;
}

/* + then expand, top right of the day. */
.cal__tools {
    display: flex;
    align-items: center;
    gap: 2px;
}

/* Same affordance as the expandable panels elsewhere: diagonal arrows, top right. */
.cal__tool {
    display: grid;
    place-items: center;
    inline-size: 20px;
    block-size: 20px;
    padding: 0;
    border: 0;
    border-radius: 3px;
    background: none;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.cal__tool:hover,
.cal__tool:focus-visible {
    background: var(--tv-sub-2);
    color: var(--tv-ink);
}

/*
 * Every day has a +, so it stays quiet until the day is hovered or the button
 * has focus; 42 full-strength icons would drown the dates.
 */
.cal__add {
    color: var(--tv-faint);
    opacity: 0.55;
    transition: opacity 80ms, color 80ms;
}

.cal__cell:hover .cal__add,
.cal__add:focus-visible {
    opacity: 1;
    color: var(--tv-brand);
}

/* Day number with the My Task toggle beside it. */
.cal__day {
    display: flex;
    align-items: center;
    gap: 2px;
}

/* Off: quiet like the +, until the day is hovered. On: filled brand chip on every day. */
.cal__mine {
    color: var(--tv-faint);
    opacity: 0.55;
    transition: opacity 80ms, color 80ms, background-color 80ms;
}

.cal__cell:hover .cal__mine,
.cal__mine:focus-visible {
    opacity: 1;
    color: var(--tv-brand);
}

.cal__mine.is-on {
    opacity: 1;
    background: var(--tv-brand-soft);
    color: var(--tv-brand);
    box-shadow: inset 0 0 0 1px var(--tv-brand-ring);
}

.cal__mine.is-on:hover,
.cal__mine.is-on:focus-visible {
    background: var(--tv-brand-soft);
    color: var(--tv-brand);
}

.cal-dlg__body--text {
    display: block;
    line-height: 1.5;
}

.cal-dlg__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.cal-dlg__close {
    flex: none;
    display: grid;
    place-items: center;
    inline-size: 26px;
    block-size: 26px;
    margin: -4px -6px 0 0;
    padding: 0;
    border: 0;
    border-radius: var(--tv-radius);
    background: none;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.cal-dlg__close:hover,
.cal-dlg__close:focus-visible {
    background: var(--tv-sub);
    color: var(--tv-ink);
}

.cal-day {
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    overflow: hidden;
}

.cal-day__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-block-end: 1px solid var(--tv-rule);
}

.cal-day__title {
    margin: 0;
    font-size: var(--tv-size-meta);
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--tv-ink-2);
}

.cal-day__collapse {
    display: grid;
    place-items: center;
    inline-size: 28px;
    block-size: 28px;
    padding: 0;
    border: 0;
    border-radius: var(--tv-radius);
    background: none;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.cal-day__collapse:hover,
.cal-day__collapse:focus-visible {
    background: var(--tv-sub);
    color: var(--tv-ink);
}

.cal-day__body {
    padding: 16px 18px 12px;
}

.cal-day__progress {
    padding-block-end: 14px;
    border-block-end: 1px solid var(--tv-rule);
}

.cal-day__progress-row {
    display: flex;
    justify-content: space-between;
    margin-block-end: 6px;
    font-size: var(--tv-size-meta);
    color: var(--tv-ink-2);
}

.cal-day__progress-row strong {
    color: var(--tv-ink);
    font-variant-numeric: tabular-nums;
}

.cal-day__bar {
    block-size: 6px;
    border-radius: 3px;
    background: var(--tv-sub-2);
    overflow: hidden;
}

.cal-day__bar span {
    display: block;
    block-size: 100%;
    border-radius: inherit;
    background: var(--tv-st-done);
    transition: inline-size 200ms;
}

.cal-day__list {
    list-style: none;
    margin: 0;
    padding: 6px 0 0;
    max-block-size: 55vh;
    overflow-y: auto;
}

.cal-day__item {
    display: grid;
    grid-template-columns: 16px auto minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 10px;
    padding: 8px 6px;
    border-radius: var(--tv-radius);
    cursor: pointer;
}

.cal-day__item:hover {
    background: var(--tv-sub);
}

/*
 * Spelled out here: the dialog is teleported out of #taskViewsApp, so the
 * shared .st-* rail colours don't reach it.
 */
.cal-day__glyph {
    color: var(--tv-muted);
}

.cal-day__item.st-new .cal-day__glyph { color: var(--tv-st-new); }
.cal-day__item.st-in_progress .cal-day__glyph { color: var(--tv-st-progress); }
.cal-day__item.st-resolved .cal-day__glyph { color: var(--tv-st-resolved); }
.cal-day__item.st-closed .cal-day__glyph { color: var(--tv-st-done); }

.cal-day__ref {
    font-size: var(--tv-size-meta);
    font-weight: 500;
    font-variant-numeric: tabular-nums;
    color: var(--tv-ink);
}

.cal-day__name {
    display: flex;
    flex-direction: column;
    min-inline-size: 0;
}

.cal-day__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: var(--tv-size-body);
    font-weight: 500;
    color: var(--tv-ink);
}

.cal-day__meta {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-muted);
}

.cal-day__status {
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-muted);
    white-space: nowrap;
}

.cal__task.is-dragging {
    opacity: 0.4;
}

.cal__task.is-saving {
    opacity: 0.6;
}

.cal-dlg {
    padding: 20px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius);
}

.cal-dlg__title {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--tv-ink);
    overflow-wrap: anywhere;
}

.cal-dlg__body {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    color: var(--tv-ink-2);
}

.cal-dlg__meta {
    margin: 4px 0 0;
}

.cal-dlg__load {
    margin-block-start: 14px;
    padding: 10px 12px;
    border: 1px solid var(--tv-rule);
    border-radius: var(--tv-radius);
    background: var(--tv-sub);
}

.cal-dlg__load-txt {
    margin: 4px 0 0;
}

.cal-dlg__error {
    font-size: var(--tv-size-meta);
    color: #b3261e;
}

.cal-dlg__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-block-start: 18px;
}

.cal-btn {
    height: 32px;
    padding: 0 14px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.cal-btn--primary {
    border-color: var(--tv-brand);
    background: var(--tv-brand);
    color: #fff;
}

.cal-btn:disabled {
    opacity: 0.5;
    cursor: default;
}
</style>
