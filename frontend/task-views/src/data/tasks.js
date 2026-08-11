import axios from "axios";

/**
 * Task domain vocabulary and the adapter over the app's task-list endpoint.
 *
 * The list comes from POST /requests/case_project — the same endpoint the
 * legacy task list uses, so filters, permissions and project scoping behave
 * identically. Its rows are wide and legacy-shaped; `toTask()` narrows them to
 * what these views render.
 */

/**
 * The product's own status vocabulary, not a generic one. These are the four
 * states the app offers for a project with no custom workflow (see
 * EasycasesController::ajaxChangeStatusOptions), and `legend` is what each one
 * writes.
 */
export const STATUSES = [
    { value: "new", label: "New", legend: 1, icon: "mdi-circle-outline" },
    { value: "in_progress", label: "In progress", legend: 2, icon: "mdi-timelapse" },
    { value: "resolved", label: "Resolved", legend: 5, icon: "mdi-check-circle-outline" },
    { value: "closed", label: "Closed", legend: 3, icon: "mdi-close-circle-outline" },
];

export function legendForStatus(value) {
    return STATUSES.find((s) => s.value === value)?.legend ?? null;
}

/*
 * Colour rides on the arrow rather than a filled chip: Status already owns the
 * coloured-dot vocabulary one column over, and two badges side by side compete.
 * The arrow direction carries the meaning on its own, so colour is reinforcement
 * rather than the only signal.
 */
export const PRIORITIES = [
    { value: "low", label: "Low", icon: "mdi-arrow-down", color: "#6e7681" },
    { value: "medium", label: "Medium", icon: "mdi-arrow-right", color: "#b8860b" },
    { value: "high", label: "High", icon: "mdi-arrow-up", color: "#d1242f" },
    { value: "urgent", label: "Urgent", icon: "mdi-chevron-double-up", color: "#a40e26" },
];

export const priorityColor = (value) =>
    PRIORITIES.find((p) => p.value === value)?.color ?? "inherit";

/**
 * `label` heads the column and is kept short because the track is narrow.
 * `menuLabel` is what the Columns menu shows: the menu has the room, and a
 * reader picking columns from a list needs the whole name — "Est." on its own
 * does not say what it is.
 */
export const COLUMNS = [
    { key: "id", label: "Task", width: 104, always: true },
    { key: "title", label: "Title", width: null, always: true },
    { key: "type", label: "Type", width: 132 },
    { key: "status", label: "Status", width: 148 },
    { key: "priority", label: "Priority", width: 108 },
    { key: "assignee", label: "Assignee", width: 132 },
    { key: "due", label: "Due", menuLabel: "Due date", width: 96 },
    { key: "estimate", label: "Est.", menuLabel: "Estimated hours", width: 68 },
];

/**
 * One due-date format for every view. The year is always shown: without it a
 * date a year out is indistinguishable from one this month.
 */
export function formatDue(iso) {
    if (!iso) return "No date";
    const d = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(d.getTime())) return "No date";

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const days = Math.round((d - today) / 86400000);

    /*
     * Relative only within a day either side. Past that it costs the reader
     * arithmetic ("17 days ago" — which date is that?), so an absolute date is
     * easier to read. The year is always shown so a date a year out cannot be
     * mistaken for one this month.
     */
    if (days === 0) return "Today";
    if (days === -1) return "Y'day";
    if (days === 1) return "Tomorrow";
    return d.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
}

export function statusMeta(value) {
    return STATUSES.find((s) => s.value === value) ?? STATUSES[0];
}

export function priorityMeta(value) {
    return PRIORITIES.find((p) => p.value === value) ?? PRIORITIES[1];
}

/** detChangepriority() in script_v1.js sends 0/1/2/3 for High/Medium/Low/Urgent. */
const PRIORITY_BY_CODE = { 0: "high", 1: "medium", 2: "low", 3: "urgent" };

/**
 * easycases.legend carries more states than status_masters.legend does: the
 * built-in flow writes 4 for "started" and 5 for "resolved" (see
 * EasycasesTable::actionOntask), while custom statuses come through as their
 * status_master_id (1/2/3). Both shapes have to land somewhere real — mapping
 * only 1/2/3 made every started and resolved task read as "New".
 */
const STATUS_BY_LEGEND = {
    1: "new",
    2: "in_progress",
    4: "in_progress",
    5: "resolved",
    3: "closed",
};

function normaliseDate(value) {
    if (!value) return null;
    const iso = String(value).slice(0, 10);
    return /^\d{4}-\d{2}-\d{2}$/.test(iso) ? iso : null;
}

/**
 * Legacy row -> view model.
 *
 * `status` is one of our five keys and drives the rail colour and filters;
 * `statusLabel` keeps the project's own status name ("Ready", "Resolve", …) for
 * display, because projects define their own workflow.
 */
/**
 * The endpoint stamps pjname/pjUniqid on only the first row of each project —
 * the legacy list renders the project as a band heading and the rest of the
 * rows inherit it visually. Rebuild the mapping so every task carries its own
 * project, which grouping and the task-group picker both need.
 */
function collectProjects(rows) {
    const byId = {};
    rows.forEach((row) => {
        const id = Number(row.project_id);
        if (!id || byId[id]) return;
        if (row.pjname || row.pjUniqid) {
            byId[id] = { name: row.pjname ?? "", uniqId: row.pjUniqid ?? null };
        }
    });
    return byId;
}

function toTask(row, statusNames, groupNames = {}, projects = {}) {
    const project = projects[Number(row.project_id)] ?? {};
    const custom = statusNames[row.custom_status_id];
    const inactive = String(row.isactive) === "0";
    const groupId = row.EasycaseMilestone?.milestone_id
        ? Number(row.EasycaseMilestone.milestone_id)
        : null;

    return {
        id: row.uniq_id ?? String(row.id),
        numericId: Number(row.id),
        ref: row.case_no ? `#${row.case_no}` : `#${row.id}`,
        title: row.title ?? "",
        created: row.dt_created ?? null,
        type: Array.isArray(row.csTdTyp) ? row.csTdTyp[1] ?? "" : "",
        /** Filtering by type takes an id; the row only shows the name. */
        typeId: Number(row.type_id) || null,
        createdById: Number(row.user_id) || null,
        favourite: Number(row.isFavourite) === 1,
        status: inactive ? "closed" : STATUS_BY_LEGEND[Number(row.legend)] ?? "new",
        statusLabel: inactive ? "Canceled" : custom ?? null,
        priority: PRIORITY_BY_CODE[Number(row.priority)] ?? "medium",
        // `Assigned` is the display name the rest of the app uses and is the
        // only one populated when a task is assigned to the current user
        // ("Me"); asgnName is blank in that case.
        assignee: row.Assigned || row.asgnName || row.usrName || "Unassigned",
        /** The id the change-assignee endpoint needs; the name alone cannot save. */
        assigneeId: Number(row.assign_to) || null,
        due: normaliseDate(row.due_date),
        /*
         * estimated_hours is stored in seconds (86400 = 24h). Everywhere else in
         * the app shows hours, so the raw value was leaking into the sheet as
         * "86400". Kept as a number so the cell stays numeric and sortable.
         */
        estimate: Math.round(((Number(row.estimated_hours) || 0) / 3600) * 100) / 100,
        project: row.pjname || project.name || "",
        projectId: Number(row.project_id) || null,
        projectUniqId: row.pjUniqid || project.uniqId || null,
        // Needed by changeCustomStatus, which identifies the task by all three.
        caseNo: row.case_no ?? null,
        customStatusId: Number(row.custom_status_id) || 0,
        // Task group (milestone). Absent on tasks that sit outside a group.
        taskGroupId: groupId,
        taskGroup: groupId ? groupNames[groupId] ?? "Task group" : null,
        parentId: row.parent_task_id ? Number(row.parent_task_id) : null,
        // `Assigned === 'Me'` is how the legacy list flags the current user's
        // own tasks; drives the My Works page.
        assignedToMe: row.Assigned === "Me",
        url: row.uniq_id ? `dashboard#/details/${row.uniq_id}` : null,
    };
}

/**
 * The scoped project's workflow statuses, ordered. Kanban columns come from
 * these when the project has a workflow; masterId says which legend each one
 * maps to (1 new, 2 in progress, 3 closed) for tasks not yet on the workflow.
 */
function collectCustomStatuses(payload) {
    const source = payload?.customStatusByProject;
    const groups = Array.isArray(source) ? source : Object.values(source ?? {});
    const byId = new Map();
    groups.forEach((group) => {
        const rows = Array.isArray(group) ? group : Object.values(group ?? {});
        rows.forEach((row) => {
            const item = row?.CustomStatus ?? row;
            if (item?.id != null && item?.name) {
                byId.set(Number(item.id), {
                    id: Number(item.id),
                    label: String(item.name),
                    masterId: Number(item.status_master_id) || 0,
                    color: item.color ? `#${String(item.color).replace(/^#/, "")}` : null,
                    progress: Number(item.progress) || 0,
                    seq: Number(item.seq) || 0,
                });
            }
        });
    });
    return [...byId.values()].sort((a, b) => a.seq - b.seq);
}

/** customStatusByProject arrives keyed by project; flatten to id -> name. */
function collectStatusNames(payload) {
    const names = {};
    const source = payload?.customStatusByProject;
    const groups = Array.isArray(source) ? source : Object.values(source ?? {});

    groups.forEach((group) => {
        const rows = Array.isArray(group) ? group : Object.values(group ?? {});
        rows.forEach((row) => {
            const item = row?.CustomStatus ?? row;
            if (item?.id != null && item?.name) names[item.id] = item.name;
        });
    });

    return names;
}

/**
 * The project the app is currently scoped to.
 *
 * #projFil is where the rest of the app keeps the top-bar project selection —
 * the legacy list reads the same field. Without this the new pages requested
 * every project regardless of what was selected.
 */
export function currentProject() {
    const el = document.getElementById("projFil");
    const value = (el?.value || "").trim();
    return value || "all";
}

/**
 * The endpoint names its sort fields differently from the views. Anything not
 * listed here has no server-side equivalent and is left to the client sort.
 */
const SERVER_SORT = {
    id: "caseno",
    ref: "caseno",
    created: "dt_created",
    title: "title",
    type: "type",
    status: "status",
    priority: "priority",
    assignee: "caseAt",
    due: "duedate",
    estimate: "estimatedhours",
};

/** Whether the endpoint can order by this column itself. */
export const hasServerSort = (sortBy) => Boolean(SERVER_SORT[sortBy]);

export async function fetchTasks({
    page = 1,
    project = currentProject(),
    archived = false,
    sortBy = null,
    sortDir = "desc",
    filters = {},
} = {}) {
    const body = new URLSearchParams({
        projFil: project,
        caseMenuFilters: "cases",
        casePage: String(page),
    });
    // Same list query, isactive = 0 — the branch the archive page already used.
    if (archived) body.set("inactive", "1");

    // Filtering server-side rather than over the loaded pages: the count comes
    // back matching the filter, and a project past the page cap stops hiding
    // rows that do match. See serverFilters.js for what can be expressed.
    Object.entries(filters).forEach(([key, value]) => body.set(key, value));

    // Sorting server-side keeps the ordering correct across the whole result
    // set, not just the pages already loaded.
    const serverSort = SERVER_SORT[sortBy];
    if (serverSort) {
        body.set("sortBy", serverSort);
        body.set("sortOrder", sortDir === "asc" ? "ASC" : "DESC");
    }

    const { data } = await axios.post("requests/case_project", body, {
        baseURL: window.TASK_VIEWS_CONFIG?.baseUrl ?? "/",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
    });

    const rows = Array.isArray(data?.caseAll) ? data.caseAll : Object.values(data?.caseAll ?? {});
    const statusNames = collectStatusNames(data);
    const groupNames = collectGroupNames(data);
    const projects = collectProjects(rows);

    return {
        tasks: rows.map((row) => toTask(row, statusNames, groupNames, projects)),
        total: Number(data?.caseCount) || rows.length,
        perPage: Number(data?.page_limit) || 30,
        taskGroups: Object.entries(groupNames).map(([id, name]) => ({ id: Number(id), name })),
        customStatuses: collectCustomStatuses(data),
        labels: collectLabels(data),
    };
}

/** Company/project labels, published by the endpoint for the label filter. */
function collectLabels(payload) {
    const rows = payload?.projectLabels;
    if (!Array.isArray(rows)) return [];
    return rows
        .map((r) => ({ id: Number(r.id), name: String(r.lbl_title ?? "") }))
        .filter((l) => l.id && l.name);
}

/**
 * Task-group names, keyed by milestone id. The endpoint spells this list
 * differently depending on scope (`milesto_names` for one project,
 * `all_milesto_names` across projects), and each entry may be a bare
 * id => name pair or a Milestone row.
 */
function collectGroupNames(payload) {
    const names = {};
    [payload?.milesto_names, payload?.all_milesto_names, payload?.milestones].forEach((source) => {
        if (!source) return;
        const entries = Array.isArray(source) ? source.entries() : Object.entries(source);
        for (const [key, value] of entries) {
            if (value == null) continue;
            if (typeof value === "string") {
                names[key] = value;
                continue;
            }
            const row = value.Milestone ?? value;
            if (row?.id != null && row?.title) names[row.id] = row.title;
            else if (row?.id != null && row?.name) names[row.id] = row.name;
        }
    });
    return names;
}
