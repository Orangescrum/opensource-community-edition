import { defineStore } from "pinia";
import { COLUMNS, PRIORITIES, STATUSES, fetchTasks, hasServerSort, currentProject } from "@/data/tasks";
import {
    quickCreateTask,
    archiveTasks,
    restoreTasks,
    copyTasksToProject,
    deleteTasks,
    fetchProjectMembers,
    fetchTaskGroups,
    moveTasksToProject,
    savePriority,
    saveStatus,
    saveAssignee,
    saveCustomStatus,
    saveDueDate,
    saveEstimate,
    saveStatusBulk,
    saveTaskType,
} from "@/data/mutations";
import { taskTypes } from "@/data/taskTypes";
import { toServerFilters } from "@/data/serverFilters";

const STATUS_ORDER = ["new", "in_progress", "resolved", "closed"];
const PRIORITY_ORDER = ["low", "medium", "high", "urgent"];

/** The options offered by the Group by control, in menu order. */
export const GROUP_BY_OPTIONS = [
    { value: "", label: "None" },
    { value: "status", label: "Status" },
    { value: "priority", label: "Priority" },
    { value: "assignee", label: "Assignee" },
    { value: "taskGroup", label: "Task group" },
    { value: "project", label: "Project" },
    { value: "due", label: "Due date" },
];

const statusLabel = (t) =>
    t.statusLabel || STATUSES.find((s) => s.value === t.status)?.label || t.status;

const priorityLabel = (value) =>
    PRIORITIES.find((p) => p.value === value)?.label || value;

/**
 * Relative buckets rather than raw dates — "Overdue" and "This week" are what a
 * due-date grouping is actually asked for.
 */
function dueBucket(due) {
    if (!due) return { key: "none", label: "No due date" };

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const d = new Date(`${due}T00:00:00`);
    const days = Math.round((d - today) / 86400000);

    if (days < 0) return { key: "overdue", label: "Overdue" };
    if (days === 0) return { key: "today", label: "Today" };
    if (days === 1) return { key: "tomorrow", label: "Tomorrow" };
    if (days <= 7) return { key: "week", label: "This week" };
    if (days <= 30) return { key: "month", label: "This month" };
    return { key: "later", label: "Later" };
}

/**
 * The endpoint paginates, but filtering and sorting here are client-side — so
 * we pull every page up front, otherwise a filter would silently only search
 * the page you happen to be on. Capped so a very large project cannot spin.
 */
const MAX_PAGES = 20;

/**
 * Coalesces the refetch each filter change triggers. Module-level so it never
 * becomes reactive state.
 */
let loadTimer = null;

/** Monotonic id for unsaved spreadsheet draft rows. Module-level, never reactive. */
let draftSeq = 0;

/**
 * The legacy list remembers your sort across visits (cookies TASKSORTBY and
 * TASKSORTORDER, read by RequestsController::caseProject). These views forgot
 * it on every navigation, so a sort looked like it had not taken.
 */
const SORT_KEY = "orangescrum.taskViews.sort";

function loadSort() {
    try {
        const saved = JSON.parse(localStorage.getItem(SORT_KEY) || "null");
        if (saved?.sortBy) {
            return { sortBy: saved.sortBy, sortDir: saved.sortDir === "asc" ? "asc" : "desc" };
        }
    } catch {
        // A malformed or unavailable store just means the default sort.
    }

    return { sortBy: "created", sortDir: "desc" };
}

function saveSort(sortBy, sortDir) {
    try {
        localStorage.setItem(SORT_KEY, JSON.stringify({ sortBy, sortDir }));
    } catch {
        // Private browsing and full quotas are not worth failing a sort over.
    }
}

/**
 * One store behind all three views. Filters, sort, selection and column
 * visibility live here so switching view keeps your place — that is what makes
 * the three read as one product rather than three widgets.
 */
export const useTaskStore = defineStore("tasks", {
    state: () => ({
        tasks: [],
        loading: true,
        error: null,
        truncated: false,
        total: 0,

        view: "inline",
        /** Which task page this mount is, from TASK_VIEWS_CONFIG.page. */
        page: "views",

        query: "",
        status: [],
        priority: [],
        type: [],
        assignee: [],
        taskGroup: [],
        due: [],

        /** One of PRESETS — a saved combination of the filters below. */
        preset: "",
        /** A CREATED_OPTIONS key, or "" for any creation date. */
        createdRange: "",
        createdBy: [],
        commentedBy: [],
        label: [],
        favourite: false,

        /** Labels available to filter on, refreshed with the list. */
        labels: [],

        /**
         * Which filters the last request satisfied server-side. The rest are
         * re-applied client-side; applying a handled one twice is harmless, but
         * applying an unhandled one is the difference between a filter working
         * and silently doing nothing.
         */
        serverHandled: new Set(),

        /**
         * Archived tasks live behind a separate server query (isactive = 0),
         * not a client-side filter, so this reloads the list rather than
         * narrowing it.
         */
        showArchived: false,

        /*
         * Newest first by default, the same order the endpoint applies
         * (dt_created DESC), but restored from the last visit so a sort
         * survives navigation the way the legacy list's does. Sorting is sent
         * to the server, so the order holds across the whole result set rather
         * than only the loaded pages.
         */
        ...loadSort(),

        /**
         * Whether the last request's sort column had a server-side equivalent.
         * If it did, the rows arrive already ordered and re-sorting them here
         * would override it — the two orderings genuinely differ (the endpoint
         * sorts priority by its raw code, where 0 is High, and status by
         * `legend`, where Closed precedes Resolved).
         */
        serverSorted: false,

        selected: new Set(),
        hiddenColumns: new Set(),

        /**
         * Subtask view. Holds the parents the user has *collapsed* rather than
         * the ones expanded, so newly-loaded parents default to open — a tree
         * that starts fully closed reads as an empty page.
         */
        collapsedParents: new Set(),

        /** Group-by: "" is a flat list. Collapsed group keys live alongside it. */
        groupBy: "",
        collapsedGroups: new Set(),

        /** Task groups available to assign to, refreshed with the list. */
        taskGroups: [],

        /** The scoped project's workflow statuses, when it has a workflow. */
        customStatuses: [],

        /**
         * Project members as {id, name}. Assigning needs the id, and deriving it
         * from loaded tasks would only ever offer people who already have one.
         */
        projectMembers: [],

        /** Ids with a write in flight, and the last write failure. */
        saving: new Set(),
        saveError: null,
        /** One-shot success message for a toast; views set it after a save. */
        notice: null,
        bulkBusy: false,

        lastEditedId: null,

        /**
         * Unsaved draft rows the Spreadsheet appends for quick task entry. Each
         * is {tempId, title, saving}; a title + Enter/blur turns one into a real
         * task via quickCreateTask, and empty drafts are simply discarded.
         */
        newRows: [],
    }),

    getters: {
        columns: (s) => COLUMNS.filter((c) => c.always || !s.hiddenColumns.has(c.key)),

        /**
         * The single project new rows would be created in: the scoped project
         * (#projFil), or the one project the loaded rows all belong to. Null when
         * the list spans projects, since a quick task needs one target project.
         */
        projectScopeUniq(s) {
            const scoped = currentProject();
            if (scoped && scoped !== "all") return scoped;
            const ids = [...new Set(s.tasks.map((t) => t.projectUniqId).filter(Boolean))];
            return ids.length === 1 ? ids[0] : null;
        },

        /** Quick-add is offered only when there is a single project to add into. */
        canAddRows() {
            return Boolean(this.projectScopeUniq);
        },

        activeFilterCount: (s) =>
            s.status.length + s.priority.length + s.type.length +
            s.assignee.length + s.taskGroup.length + s.due.length +
            s.createdBy.length + s.commentedBy.length +
            s.label.length +
            (s.createdRange ? 1 : 0) + (s.favourite ? 1 : 0) +
            (s.preset ? 1 : 0) + (s.query.trim() ? 1 : 0),

        /**
         * Display name -> id, for the filters the endpoint takes by id. Built
         * from the authoritative lists where there is one, then topped up from
         * the loaded rows so a type or person the lists do not mention still
         * resolves rather than quietly falling back to a client-side pass.
         */
        filterIdMaps: (s) => {
            const typeIds = new Map(taskTypes().map((t) => [t.name, t.id]));
            const memberIds = new Map(s.projectMembers.map((m) => [m.name, m.id]));
            s.tasks.forEach((t) => {
                if (t.type && t.typeId && !typeIds.has(t.type)) typeIds.set(t.type, t.typeId);
                if (t.assignee && t.assigneeId && !memberIds.has(t.assignee)) {
                    memberIds.set(t.assignee, t.assigneeId);
                }
            });
            return { typeIds, memberIds };
        },

        labelOptions: (s) => s.labels.map((l) => ({ value: String(l.id), label: l.name })),

        /** Type and assignee lists come from the data — projects define their own. */
        typeOptions: (s) =>
            [...new Set(s.tasks.map((t) => t.type).filter(Boolean))]
                .sort()
                .map((v) => ({ value: v, label: v })),

        /**
         * Prefer the project's member list: names derived from loaded tasks only
         * ever offer people who already have one, so nobody new could be picked.
         */
        assigneeOptions: (s) => {
            const names = s.projectMembers.length
                ? s.projectMembers.map((m) => m.name)
                : s.tasks.map((t) => t.assignee);
            return [...new Set(names.filter(Boolean))]
                .sort()
                .map((v) => ({ value: v, label: v }));
        },

        /** The company's task types, from the page's own GLOBALS_TYPE. */
        taskTypeOptions: () => taskTypes(),

        taskGroupOptions: (s) =>
            s.taskGroups.map((g) => ({ value: String(g.id), label: g.name })),

        /**
         * Where Group by can actually apply. Offering the control on a page
         * that ignores it is worse than not offering it at all.
         *
         * Sheet is index-addressed (its cell cursor indexes straight into
         * `visible`), Subtask already groups by parent/child, and Kanban's
         * columns *are* a grouping — so all three opt out.
         */
        /**
         * Kanban columns. A project with its own workflow gets its workflow
         * statuses as columns; otherwise (or across projects, whose workflows
         * could differ) the four legend statuses.
         */
        kanbanColumns(s) {
            if (s.customStatuses.length && !this.spansProjects) {
                return s.customStatuses.map((c) => ({
                    kind: "custom",
                    value: `cs-${c.id}`,
                    id: c.id,
                    label: c.label,
                    masterId: c.masterId,
                    color: c.color,
                    progress: c.progress,
                }));
            }
            return STATUSES.map((st) => ({ kind: "legend", value: st.value, label: st.label }));
        },

        /**
         * column value -> tasks. A task not yet on the workflow (customStatusId
         * 0) lands on the column whose master matches its legend, so enabling a
         * workflow doesn't empty the board.
         */
        kanbanBuckets(s) {
            const cols = this.kanbanColumns;
            const map = Object.fromEntries(cols.map((c) => [c.value, []]));
            if (cols[0]?.kind !== "custom") {
                this.visible.forEach((t) => map[t.status]?.push(t));
                return map;
            }

            const byMaster = { new: 1, in_progress: 2, resolved: 2, closed: 3 };
            this.visible.forEach((t) => {
                let col = cols.find((c) => c.id === t.customStatusId);
                if (!col) {
                    const master = byMaster[t.status] ?? 1;
                    const fits = cols.filter((c) => c.masterId === master);
                    // Resolved shares master 2 with in-progress; the completed
                    // (progress 100) or last such column is the resolved one.
                    col = t.status === "resolved"
                        ? (fits.find((c) => c.progress >= 100) ?? fits[fits.length - 1])
                        : fits[0];
                }
                (map[(col ?? cols[0]).value] ?? map[cols[0].value]).push(t);
            });
            return map;
        },

        /** Column visibility applies to the row-and-column views only. */
        supportsColumns: (s) => s.page !== "kanban" && s.page !== "calendar" && s.page !== "overview",

        supportsGrouping(s) {
            if (s.page === "myworks" || s.page === "subtasks") return true;
            return s.page === "views" && s.view !== "sheet";
        },

        visible(s) {
            const q = s.query.trim().toLowerCase();

            /*
             * Only the filters the last request could not express. Re-running a
             * handled one here would be wasted work at best, and wrong at worst:
             * the preset filters rewrite status and priority, so matching the
             * raw state against the rows the server already narrowed would
             * empty the list.
             */
            const local = (key) => !s.serverHandled.has(key);
            const rows = s.tasks.filter((t) => {
                // Search stays client-side throughout: the endpoint's search
                // spans fields these views do not show.
                if (q && !`${t.ref} ${t.title} ${t.assignee}`.toLowerCase().includes(q)) return false;
                if (local("status") && s.status.length && !s.status.includes(t.status)) return false;
                if (local("priority") && s.priority.length && !s.priority.includes(t.priority)) return false;
                if (local("type") && s.type.length && !s.type.includes(t.type)) return false;
                if (local("assignee") && s.assignee.length && !s.assignee.includes(t.assignee)) return false;
                if (local("taskGroup") && s.taskGroup.length && !s.taskGroup.includes(String(t.taskGroupId ?? ""))) return false;
                if (local("due") && s.due.length && !s.due.includes(dueBucket(t.due).key)) return false;
                if (local("createdBy") && s.createdBy.length) {
                    const ids = s.createdBy.map((n) => s.projectMembers.find((m) => m.name === n)?.id);
                    if (!ids.includes(t.createdById)) return false;
                }
                // Favourites has no server-side equivalent on this endpoint,
                // so it always narrows the loaded pages only.
                if (s.favourite && !t.favourite) return false;
                if (local("preset")) {
                    if (s.preset === "open" && (t.status === "closed" || t.status === "resolved")) return false;
                    if (s.preset === "closed" && t.status !== "closed") return false;
                    if (s.preset === "assigntome" && !t.assignedToMe) return false;
                    if (s.preset === "overdue" && dueBucket(t.due).key !== "overdue") return false;
                    if (s.preset === "highpriority" && t.priority !== "high") return false;
                }
                if (s.preset === "favourite" && !t.favourite) return false;
                return true;
            });

            /*
             * The rows already arrived in the requested order. Re-sorting would
             * not merely be redundant — it would contradict the server, which
             * orders priority by its raw code (0 = High) and status by `legend`
             * (Closed before Resolved), and paginates on that order. Sorting
             * again here made the loaded page disagree with the pages behind it.
             */
            if (s.serverSorted) return rows;

            const dir = s.sortDir === "asc" ? 1 : -1;
            const rank = (t) => {
                switch (s.sortBy) {
                    case "status":
                        return STATUS_ORDER.indexOf(t.status);
                    case "priority":
                        return PRIORITY_ORDER.indexOf(t.priority);
                    case "estimate":
                        return t.estimate ?? 0;
                    case "id":
                    case "ref":
                        return t.numericId ?? 0;
                    case "created":
                        return t.created ? Date.parse(t.created) || 0 : 0;
                    default:
                        return null;
                }
            };

            return [...rows].sort((a, b) => {
                const ra = rank(a);
                if (ra !== null) return (ra - rank(b)) * dir;

                // Undated rows sink to the bottom in either direction — a null
                // due date is absence of information, not an early date.
                if (s.sortBy === "due") {
                    if (!a.due && !b.due) return 0;
                    if (!a.due) return 1;
                    if (!b.due) return -1;
                    return a.due.localeCompare(b.due) * dir;
                }

                return String(a[s.sortBy] ?? "").localeCompare(String(b[s.sortBy] ?? ""), undefined, {
                    numeric: true,
                }) * dir;
            });
        },

        /** The selected rows themselves, in list order. */
        selectedTasks() {
            return this.tasks.filter((t) => this.selected.has(t.id));
        },

        /** Projects present in the loaded rows — move/copy targets. */
        projectOptions() {
            /*
             * Move/Copy targets come from the page's PROJECTS global (every
             * project the user belongs to) — deriving them from loaded tasks
             * only ever offered the projects already on screen, so with the
             * list scoped to one project there was nowhere to copy to.
             */
            const seen = new Map();
            const globals = Array.isArray(window.PROJECTS) ? window.PROJECTS : [];
            globals.forEach((row) => {
                const p = row?.Project ?? row?.Projects ?? row;
                if (p?.id && p?.name) {
                    seen.set(Number(p.id), { id: Number(p.id), name: p.name, uniqId: p.uniq_id ?? null });
                }
            });
            this.tasks.forEach((t) => {
                if (t.projectId && !seen.has(t.projectId)) {
                    seen.set(t.projectId, { id: t.projectId, name: t.project || "Project", uniqId: t.projectUniqId });
                }
            });
            return [...seen.values()].sort((a, b) => a.name.localeCompare(b.name));
        },

        allVisibleSelected() {
            return this.visible.length > 0 && this.visible.every((t) => this.selected.has(t.id));
        },

        someVisibleSelected() {
            return this.visible.some((t) => this.selected.has(t.id)) && !this.allVisibleSelected;
        },

        /** Kanban: filtered/sorted tasks bucketed by status, empty columns kept. */
        byStatus() {
            const buckets = Object.fromEntries(STATUS_ORDER.map((s) => [s, []]));
            this.visible.forEach((t) => (buckets[t.status] ?? buckets.new).push(t));
            return buckets;
        },

        /** My Works: the current user's own tasks, honouring the active filters. */
        mine() {
            return this.visible.filter((t) => t.assignedToMe);
        },

        /**
         * Subtasks: top-level tasks with their children nested. A child whose
         * parent is filtered out still surfaces as its own top-level row so it
         * is never hidden.
         */
        /**
         * Builds a forest from an arbitrary set of tasks. A task whose parent is
         * absent from that set becomes a root — which is what lets a group show
         * a subtask whose parent lives in a different group.
         */
        buildTree() {
            return (tasks) => {
                const byId = new Map(tasks.map((t) => [t.numericId, t]));
                const childrenOf = new Map();
                tasks.forEach((t) => {
                    if (!t.parentId || !byId.has(t.parentId)) return;
                    if (!childrenOf.has(t.parentId)) childrenOf.set(t.parentId, []);
                    childrenOf.get(t.parentId).push(t);
                });

                // Subtasks nest arbitrarily deep (a subtask can have its own
                // subtasks), so this walks the tree rather than taking one level.
                // `seen` guards against a parent cycle in the data hanging the walk.
                const seen = new Set();
                const build = (task, depth) => {
                    if (seen.has(task.numericId)) return null;
                    seen.add(task.numericId);
                    const kids = (childrenOf.get(task.numericId) ?? [])
                        .map((c) => build(c, depth + 1))
                        .filter(Boolean);
                    return {
                        task,
                        depth,
                        children: kids,
                        expanded: kids.length > 0 && !this.collapsedParents.has(task.numericId),
                        /** Parent exists but sits outside this set — shown as a root here. */
                        orphaned: Boolean(task.parentId) && depth === 0,
                    };
                };

                return tasks
                    .filter((t) => !t.parentId || !byId.has(t.parentId))
                    .map((t) => build(t, 0))
                    .filter(Boolean);
            };
        },

        parentGroups() {
            return this.buildTree(this.visible);
        },

        /**
         * True when the loaded tasks come from more than one project. Task
         * numbers restart per project, so "#1" is only ambiguous here — this is
         * what decides whether rows need to name their project.
         */
        spansProjects: (s) =>
            new Set(s.tasks.map((t) => t.projectId).filter(Boolean)).size > 1,

        /** numericId -> ref, for naming a parent that sits outside this group. */
        refByNumericId(s) {
            const m = new Map();
            s.tasks.forEach((t) => m.set(t.numericId, t.ref));
            return m;
        },

        /**
         * The tree, divided into the active grouping. Roots decide the section
         * — a subtask stays under its parent rather than being torn into a
         * different bucket, which is the point of showing a tree at all.
         */
        /**
         * The tree, divided into the active grouping.
         *
         * Tasks are bucketed first and the tree is built per bucket, so a task
         * always lands in its own group: a subtask whose task group differs from
         * its parent's appears as a root of its own group rather than hiding
         * inside the parent's. A group header states a count, and a count that
         * omits matching tasks is simply wrong — the same contract filters have.
         */
        subtaskSections() {
            const flatten = (list) =>
                list.flatMap((n) => [n.task, ...flatten(n.children)]);

            if (!this.groupBy) {
                const nodes = this.parentGroups;
                return [{ key: "", label: "", nodes, rows: flatten(nodes), collapsed: false }];
            }

            const order = [];
            const byKey = new Map();
            this.visible.forEach((t) => {
                const { key, label } = this.bucketFor(t);
                if (!byKey.has(key)) {
                    byKey.set(key, { key, label, tasks: [] });
                    order.push(key);
                }
                byKey.get(key).tasks.push(t);
            });

            return order.map((k) => {
                const g = byKey.get(k);
                const nodes = this.buildTree(g.tasks);
                return {
                    key: g.key,
                    label: g.label,
                    nodes,
                    rows: flatten(nodes),
                    collapsed: this.collapsedGroups.has(g.key),
                };
            });
        },

        /** Depth-first flatten of the tree, honouring collapsed branches. */
        subtaskRows() {
            const out = [];
            const walk = (nodes) => {
                nodes.forEach((n) => {
                    out.push(n.task);
                    if (n.expanded) walk(n.children);
                });
            };
            // Walks the sections, not the ungrouped tree — this drives select-all
            // and the empty state, so it has to be exactly what is on screen.
            this.subtaskSections.forEach((g) => {
                if (!g.collapsed) walk(g.nodes);
            });
            return out;
        },

        /** True only when at least one node has children and none are collapsed. */
        allParentsExpanded() {
            let withKids = 0;
            let expanded = 0;
            const walk = (nodes) => {
                nodes.forEach((n) => {
                    if (n.children.length) {
                        withKids += 1;
                        if (n.expanded) expanded += 1;
                        walk(n.children);
                    }
                });
            };
            walk(this.parentGroups);
            return withKids > 0 && withKids === expanded;
        },

        /** Every node id that has children — used by expand/collapse all. */
        parentIdsWithChildren() {
            const ids = [];
            const walk = (nodes) => {
                nodes.forEach((n) => {
                    if (n.children.length) {
                        ids.push(n.task.numericId);
                        walk(n.children);
                    }
                });
            };
            walk(this.parentGroups);
            return ids;
        },

        /**
         * Group-by. Returns ordered buckets over the already-filtered, already-
         * sorted rows, so grouping composes with search, facets and sort rather
         * than replacing them. An empty groupBy yields a single unlabelled
         * bucket, which lets the views render one code path either way.
         */
        bucketFor() {
            return (t) => {
                switch (this.groupBy) {
                    case "status":
                        return { key: t.status, label: statusLabel(t) };
                    case "priority":
                        return { key: t.priority, label: priorityLabel(t.priority) };
                    case "assignee":
                        return { key: t.assignee || "Unassigned", label: t.assignee || "Unassigned" };
                    case "project":
                        return { key: String(t.projectId ?? ""), label: t.project || "No project" };
                    case "taskGroup":
                        // Tasks with no milestone belong to what the rest of the
                        // app calls the Default Task Group, so the bucket is
                        // named the same rather than "No task group".
                        return {
                            key: String(t.taskGroupId ?? ""),
                            label: t.taskGroup || "Default Task Group",
                        };
                    case "due":
                        return dueBucket(t.due);
                    default:
                        return { key: "", label: "" };
                }
            };
        },

        grouped() {
            // My Works groups its own subset, not the whole list.
            const rows = this.page === "myworks" ? this.mine : this.visible;
            if (!this.groupBy) return [{ key: "", label: "", rows, collapsed: false }];

            const bucketOf = this.bucketFor;

            const order = [];
            const byKey = new Map();
            rows.forEach((t) => {
                const { key, label } = bucketOf(t);
                if (!byKey.has(key)) {
                    byKey.set(key, { key, label, rows: [] });
                    order.push(key);
                }
                byKey.get(key).rows.push(t);
            });

            // Status and priority read best in their own scale order, not in
            // whichever order rows happened to arrive.
            const scale =
                this.groupBy === "status" ? STATUS_ORDER
                : this.groupBy === "priority" ? [...PRIORITY_ORDER].reverse()
                : null;
            if (scale) order.sort((a, b) => scale.indexOf(a) - scale.indexOf(b));

            return order.map((key) => ({
                ...byKey.get(key),
                collapsed: this.collapsedGroups.has(key),
            }));
        },

        /** Overview: headline counts over the filtered set. */
        overview() {
            const rows = this.visible;
            const count = (pred) => rows.filter(pred).length;
            // Local date, not toISOString() — that is UTC, so for anyone east
            // of it a task due today counted as overdue after early evening.
            const now = new Date();
            const todayIso = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
            return {
                total: rows.length,
                byStatus: STATUS_ORDER.map((s) => ({ key: s, n: count((t) => t.status === s) })),
                byPriority: PRIORITY_ORDER.map((p) => ({ key: p, n: count((t) => t.priority === p) })),
                overdue: count((t) => t.due && t.due < todayIso && t.status !== "closed" && t.status !== "resolved"),
                unassigned: count((t) => t.assignee === "Unassigned"),
                done: count((t) => t.status === "closed"),
                mine: count((t) => t.assignedToMe),
            };
        },
    },

    actions: {
        async load() {
            this.loading = true;
            this.error = null;
            this.truncated = false;

            try {
                const archived = this.showArchived;
                const sort = { sortBy: this.sortBy, sortDir: this.sortDir };
                const { params, handled } = toServerFilters(this, this.filterIdMaps);
                this.serverHandled = handled;
                this.serverSorted = hasServerSort(this.sortBy);

                const first = await fetchTasks({ page: 1, archived, ...sort, filters: params });
                let all = first.tasks;

                const pages = Math.ceil(first.total / (first.perPage || 30));
                const fetchTo = Math.min(pages, MAX_PAGES);

                for (let page = 2; page <= fetchTo; page++) {
                    const next = await fetchTasks({ page, archived, ...sort, filters: params });
                    if (!next.tasks.length) break;
                    all = all.concat(next.tasks);
                }

                this.tasks = all;
                this.total = first.total;
                this.truncated = pages > MAX_PAGES;
                this.taskGroups = first.taskGroups ?? [];
                this.customStatuses = first.customStatuses ?? [];
                this.labels = first.labels ?? [];
                /*
                 * Names come from a per-project lookup, because the list
                 * endpoint only returns them when it is itself grouping by task
                 * group. Normally fire-and-forget so the list paints straight
                 * away — but when the page is grouped by task group those names
                 * are the section headings, and painting a placeholder that
                 * flips a moment later is worse than waiting for them.
                 */
                if (this.groupBy === "taskGroup") {
                    await this.loadTaskGroups();
                } else {
                    this.loadTaskGroups();
                }
                this.loadProjectMembers();
            } catch (e) {
                this.error = e?.message || "Could not load tasks.";
                this.tasks = [];
            } finally {
                this.loading = false;
            }
        },

        setView(view) {
            this.view = view;
        },

        toggleSort(key) {
            if (this.sortBy === key) {
                this.sortDir = this.sortDir === "asc" ? "desc" : "asc";
            } else {
                this.sortBy = key;
                this.sortDir = "asc";
            }
            saveSort(this.sortBy, this.sortDir);
            // Re-query so the ordering covers every task, not just the pages
            // already in memory.
            this.load();
        },

        /** Append N blank draft rows to the Spreadsheet for quick task entry. */
        addNewRows(n = 1) {
            if (!this.canAddRows) return;
            for (let i = 0; i < n; i++) {
                this.newRows.push({ tempId: `draft-${(draftSeq += 1)}`, title: "", saving: false });
            }
        },

        removeNewRow(tempId) {
            this.newRows = this.newRows.filter((r) => r.tempId !== tempId);
        },

        clearNewRows() {
            this.newRows = [];
        },

        /**
         * Turn a draft row into a real task. An empty title just discards the
         * draft; a filled one is created through the same quick-task endpoint the
         * classic list uses, then the list is refreshed so the row arrives with
         * its real id, number and status. Coalesced so filling several rows in a
         * row is one refresh, not one per task.
         */
        async commitNewRow(tempId, title) {
            const row = this.newRows.find((r) => r.tempId === tempId);
            if (!row || row.saving) return;

            const text = (title ?? row.title ?? "").trim();
            if (!text) {
                this.removeNewRow(tempId);
                return;
            }

            const projectUniqId = this.projectScopeUniq;
            if (!projectUniqId) {
                this.saveError = "Pick a single project before adding tasks.";
                return;
            }

            row.saving = true;
            try {
                await quickCreateTask({ title: text, projectUniqId });
                this.removeNewRow(tempId);
                this.notice = "Task created";
                this.scheduleLoad();
            } catch (e) {
                row.saving = false;
                this.saveError = e?.message || "Could not create task.";
            }
        },

        /**
         * Create one task per title — how a pasted CSV/Excel column of titles
         * becomes tasks. Sequential so the server is not hammered, capped so a
         * runaway paste cannot spin, and refreshed once at the end.
         */
        async bulkCreateTasks(titles) {
            const projectUniqId = this.projectScopeUniq;
            if (!projectUniqId) {
                this.saveError = "Pick a single project before adding tasks.";
                return;
            }
            const clean = [...titles].map((t) => (t || "").trim()).filter(Boolean).slice(0, 200);
            if (!clean.length) return;

            this.bulkBusy = true;
            let created = 0;
            try {
                for (const title of clean) {
                    try {
                        await quickCreateTask({ title, projectUniqId });
                        created += 1;
                    } catch (e) {
                        this.saveError = e?.message || "Some rows could not be created.";
                    }
                }
                if (created) this.notice = `${created} task(s) created`;
                await this.load();
            } finally {
                this.bulkBusy = false;
            }
        },

        toggleFacet(facet, value) {
            const list = this[facet];
            const at = list.indexOf(value);
            if (at === -1) list.push(value);
            else list.splice(at, 1);
            this.scheduleLoad();
        },

        clearFacet(facet) {
            this[facet] = [];
            this.scheduleLoad();
        },

        /** Single-choice filters: created-date range, preset. */
        setFilter(key, value) {
            this[key] = value;
            this.scheduleLoad();
        },

        /**
         * A preset is a named combination, so it owns the filters it implies —
         * leaving a stale status selection under "Overdue" would read as the
         * preset not working.
         */
        applyPreset(value) {
            this.preset = this.preset === value ? "" : value;
            this.status = [];
            this.priority = [];
            this.due = [];
            this.favourite = false;
            this.scheduleLoad();
        },

        toggleFavourite() {
            this.favourite = !this.favourite;
        },

        /**
         * Filters now narrow the query rather than the loaded rows, so changing
         * one means refetching. Coalesced, because ticking three boxes in a
         * menu is one intent, not three round trips.
         */
        scheduleLoad() {
            clearTimeout(loadTimer);
            loadTimer = setTimeout(() => this.load(), 250);
        },

        clearFilters() {
            this.query = "";
            this.status = [];
            this.priority = [];
            this.type = [];
            this.assignee = [];
            this.taskGroup = [];
            this.due = [];
            this.preset = "";
            this.createdRange = "";
            this.createdBy = [];
            this.commentedBy = [];
            this.label = [];
            this.favourite = false;
            this.scheduleLoad();
        },

        toggleColumn(key) {
            if (this.hiddenColumns.has(key)) this.hiddenColumns.delete(key);
            else this.hiddenColumns.add(key);
            this.hiddenColumns = new Set(this.hiddenColumns);
        },

        toggleSelect(id) {
            if (this.selected.has(id)) this.selected.delete(id);
            else this.selected.add(id);
            this.selected = new Set(this.selected);
        },

        toggleSelectAllVisible() {
            const next = new Set(this.selected);
            if (this.allVisibleSelected) this.visible.forEach((t) => next.delete(t.id));
            else this.visible.forEach((t) => next.add(t.id));
            this.selected = next;
        },

        clearSelection() {
            this.selected = new Set();
        },

        toggleParent(numericId) {
            const next = new Set(this.collapsedParents);
            if (next.has(numericId)) next.delete(numericId);
            else next.add(numericId);
            this.collapsedParents = next;
        },

        toggleAllParents() {
            this.collapsedParents = this.allParentsExpanded
                ? new Set(this.parentIdsWithChildren)
                : new Set();
        },

        /**
         * Optimistic write. The row changes immediately and rolls back if the
         * server rejects it — a list where a status silently reverts on the next
         * refresh is worse than one that tells you it failed.
         *
         * Only status and priority persist; the other fields have no single-field
         * endpoint yet and stay local, as before.
         */
        async patch(id, field, value) {
            const task = this.tasks.find((t) => t.id === id);
            if (!task || task[field] === value) return;

            const previous = task[field];
            task[field] = value;
            this.lastEditedId = id;

            const save =
                field === "status" ? saveStatus
                : field === "priority" ? savePriority
                : field === "type" ? ((t, v) => saveTaskType(t, taskTypes().find((x) => x.name === v)))
                : field === "estimate" ? saveEstimate
                : field === "due" ? saveDueDate
                : field === "assignee"
                    ? ((t, v) => {
                        const member = this.projectMembers.find((m) => m.name === v);
                        if (!member) throw new Error(`Unknown assignee "${v}"`);
                        t.assigneeId = member.id;
                        return saveAssignee(t, member.id);
                    })
                : null;

            /*
             * A field with no save path used to return here, leaving the edited
             * value on screen and never persisting it — the change looked
             * accepted and vanished on refresh. Roll it back and say so instead.
             */
            if (!save) {
                task[field] = previous;
                this.saveError = `"${field}" cannot be edited here yet.`;
                return;
            }

            this.saving.add(id);
            this.saving = new Set(this.saving);
            try {
                await save(task, value);
                this.saveError = null;
            } catch (e) {
                task[field] = previous;
                this.saveError = e?.message || "Could not save that change.";
            } finally {
                this.saving.delete(id);
                this.saving = new Set(this.saving);
            }
        },

        /** Kanban drop onto a workflow column. Optimistic, rolls back on failure. */
        async moveToColumn(id, col) {
            const task = this.tasks.find((t) => t.id === id);
            if (!task || task.customStatusId === col.id) return;

            const prev = {
                customStatusId: task.customStatusId,
                status: task.status,
                statusLabel: task.statusLabel,
            };
            const byMaster = { 1: "new", 2: col.progress >= 100 ? "resolved" : "in_progress", 3: "closed" };
            task.customStatusId = col.id;
            task.status = byMaster[col.masterId] ?? task.status;
            task.statusLabel = col.label;

            this.saving.add(id);
            this.saving = new Set(this.saving);
            try {
                await saveCustomStatus(task, col);
                this.saveError = null;
            } catch (e) {
                Object.assign(task, prev);
                this.saveError = e?.message || "Could not move that task.";
            } finally {
                this.saving.delete(id);
                this.saving = new Set(this.saving);
            }
        },

        /**
         * Bulk status goes through the mass-action endpoint in one request per
         * project rather than N single-task writes — that is what the legacy
         * list does, and it keeps the activity feed to one entry per batch.
         * Priority has no bulk endpoint, so it still fans out.
         */
        async patchSelected(field, value) {
            if (field !== "status") {
                await Promise.all([...this.selected].map((id) => this.patch(id, field, value)));
                return;
            }

            const tasks = this.selectedTasks;
            const previous = tasks.map((t) => [t, t.status]);
            tasks.forEach((t) => (t.status = value));

            try {
                await saveStatusBulk(tasks, value);
                this.saveError = null;
            } catch (e) {
                previous.forEach(([t, was]) => (t.status = was));
                this.saveError = e?.message || "Could not change status.";
            }
        },

        /** Runs a bulk mutation over the selection, then refreshes the list. */
        async runBulk(fn, { clearSelection = true } = {}) {
            const tasks = this.selectedTasks;
            if (!tasks.length) return;

            this.bulkBusy = true;
            try {
                await fn(tasks);
                this.saveError = null;
                if (clearSelection) this.clearSelection();
                await this.load();
            } catch (e) {
                this.saveError = e?.message || "That bulk action failed.";
            } finally {
                this.bulkBusy = false;
            }
        },

        /**
         * Archive the selection — reversible, and the step delete now expects
         * to come first. The task leaves the active list but its history, time
         * logs and comments survive.
         */
        archiveSelected() {
            const n = this.selected.size;
            return this.runBulk(async (tasks) => {
                await archiveTasks(tasks);
                this.notice = `${n} task(s) archived`;
            });
        },

        /** Swaps the list between active and archived tasks. */
        async setShowArchived(on) {
            if (this.showArchived === on) return;
            this.showArchived = on;
            this.clearSelection();
            await this.load();
        },

        restoreSelected() {
            const n = this.selected.size;
            return this.runBulk(async (tasks) => {
                await restoreTasks(tasks);
                this.notice = `${n} task(s) restored`;
            });
        },

        deleteSelected() {
            const n = this.selected.size;
            return this.runBulk(async (tasks) => {
                await deleteTasks(tasks);
                this.notice = `${n} task(s) deleted permanently`;
            });
        },

        moveSelectedToProject(projectId) {
            return this.runBulk((tasks) => moveTasksToProject(tasks, projectId));
        },

        copySelectedToProject(projectId) {
            return this.runBulk((tasks) => copyTasksToProject(tasks, projectId));
        },

        /** Toolbar refresh — same idea as the legacy reloadTasks(). */
        async refresh() {
            await this.load();
            await this.loadTaskGroups();
            await this.loadProjectMembers();
        },

        dismissSaveError() {
            this.saveError = null;
        },

        setGroupBy(key) {
            this.groupBy = key;
        },

        toggleGroup(key) {
            const next = new Set(this.collapsedGroups);
            if (next.has(key)) next.delete(key);
            else next.add(key);
            this.collapsedGroups = next;
        },


        /**
         * Task-group names for every project present in the list, so the
         * "Group by task group" bands can be labelled.
         */
        async loadProjectMembers() {
            const projectIds = [
                ...new Set(this.tasks.map((t) => t.projectUniqId).filter(Boolean)),
            ];
            const lists = await Promise.all(
                projectIds.map((id) => fetchProjectMembers(id).catch(() => [])),
            );
            const byId = new Map();
            lists.flat().forEach((m) => byId.set(m.id, m));
            this.projectMembers = [...byId.values()];

            /*
             * The list endpoint labels the current user "Me", while the member
             * list gives their real name — so the assignee cell read "Me" and
             * matched none of the options offered next to it. Resolve from the
             * member list so the value and the choices share one vocabulary.
             */
            this.tasks.forEach((t) => {
                const member = byId.get(t.assigneeId);
                if (member) t.assignee = member.name;
            });
        },

        async loadTaskGroups() {
            const projectIds = [
                ...new Set(this.tasks.map((t) => t.projectUniqId).filter(Boolean)),
            ];
            const lists = await Promise.all(
                projectIds.map((id) => fetchTaskGroups(id).catch(() => [])),
            );
            const byId = new Map();
            lists.flat().forEach((g) => byId.set(g.id, g));
            this.taskGroups = [...byId.values()];

            const names = Object.fromEntries(this.taskGroups.map((g) => [g.id, g.name]));
            this.tasks.forEach((t) => {
                if (t.taskGroupId) t.taskGroup = names[t.taskGroupId] ?? t.taskGroup;
            });
        },
    },
});
