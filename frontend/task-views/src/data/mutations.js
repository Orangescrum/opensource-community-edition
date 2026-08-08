import axios from "axios";
import { legendForStatus } from "@/data/tasks";

/**
 * Writes. Reads live in tasks.js.
 *
 * These call the same endpoints the legacy task list calls, so permissions,
 * activity-feed entries and notification emails all behave identically — the
 * new views are another front end onto the existing behaviour, not a second
 * implementation of it.
 */

const form = (fields) => new URLSearchParams(fields);

function post(url, fields) {
    return axios.post(url, form(fields), {
        baseURL: window.TASK_VIEWS_CONFIG?.baseUrl ?? "/",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
    });
}

/**
 * Create a task from just a title, through the same endpoint the classic
 * list's "Quick Task" uses (EasycasesController::quickTask). The server fills
 * the rest of the defaults — status New, priority Medium, the project's default
 * assignee and the company's default task type — so a spreadsheet row needs
 * only a title to become a task.
 */
export async function quickCreateTask({ title, projectUniqId, taskGroupId = "" }) {
    const { data } = await post("easycases/quickTask", {
        title: (title ?? "").trim(),
        project_id: projectUniqId,
        type: "inline",
        mid: taskGroupId ? String(taskGroupId) : "",
        view_type: "",
        task_type: "",
        story_point: "",
        assign_to: "",
        estimated: "",
        due_date: "",
    });
    if (data?.error) throw new Error(data.msg || "Could not create task.");
    return data;
}

/**
 * EasycasesController::ajaxChangePriority expects 0 High / 1 Medium / 2 Low /
 * 3 Urgent. The number is a code, not a rank — 0 was already the highest, so
 * Urgent could be appended without renumbering the existing rows.
 */
const PRIORITY_CODE = { high: 0, medium: 1, low: 2, urgent: 3 };

export async function savePriority(task, value) {
    const code = PRIORITY_CODE[value];
    if (code === undefined) throw new Error(`Unknown priority "${value}"`);

    const { data } = await post("easycases/ajax_change_priority", {
        caseId: task.numericId,
        priority: code,
    });
    if (data?.err) throw new Error(data.msg || "Could not change priority.");
    return data;
}

/**
 * Two paths, because the app has two status models. A project with its own
 * workflow stores a custom_status_id and must go through changeCustomStatus so
 * the master status (and therefore the legend) is derived from the chosen row.
 * A project without one has no custom_status rows to pick, so the status *is*
 * the legend.
 */
export async function saveStatus(task, value) {
    if (task.customStatusId && task.statusOptions?.length) {
        const target = task.statusOptions.find((o) => o.value === value);
        if (!target) throw new Error(`Unknown status "${value}"`);
        const { data } = await post("easycases/changeCustomStatus", {
            id: task.numericId,
            no: task.caseNo ?? "",
            uniqid: task.id,
            statusid: target.id,
            masterid: target.masterId,
            is_sub: task.parentId ? 1 : 0,
            parent_task: 0,
        });
        if (data?.err) throw new Error(data.msg || "Could not change status.");
        return data;
    }

    const legend = legendForStatus(value);
    if (legend === null) throw new Error(`Unknown status "${value}"`);

    const { data } = await post("easycases/ajax_change_legend", {
        caseId: task.numericId,
        legend,
    });
    if (data?.err) throw new Error(data.msg || "Could not change status.");
    return data;
}

/**
 * Task type. EasycasesController::ajaxChangeStatus is the type setter despite
 * the name — it wants the type's id, short_name and name, exactly as the legacy
 * list's changestatus() sends them.
 */
export async function saveTaskType(task, type) {
    if (!type?.id) throw new Error("Unknown task type");

    const { data } = await post("easycases/ajax_change_status", {
        caseId: task.numericId,
        statusId: type.id,
        statusName: type.shortName,
        statusTitle: type.name,
    });
    if (data?.err) throw new Error(data.msg || "Could not change the task type.");
    return data;
}

/**
 * Bulk status, via the same action the legacy list's mass-action menu uses.
 * RequestsController::ajaXTaskMassAction takes an action name rather than a
 * target state, and needs the project filter it was invoked under.
 */
const MASS_ACTION = {
    new: "caseNew",
    in_progress: "caseStart",
    resolved: "caseResolve",
    closed: "caseId",
};

export async function saveStatusBulk(tasks, value) {
    const action = MASS_ACTION[value];
    if (!action) throw new Error(`Unknown status "${value}"`);

    // The endpoint is scoped to one project, so send one request per project.
    const byProject = new Map();
    tasks.forEach((t) => {
        if (!t.projectUniqId) return;
        if (!byProject.has(t.projectUniqId)) byProject.set(t.projectUniqId, []);
        byProject.get(t.projectUniqId).push(t.numericId);
    });
    if (!byProject.size) throw new Error("These tasks have no project to act on.");

    for (const [projFil, ids] of byProject) {
        const body = form({ statusid: action, projFil });
        ids.forEach((id) => body.append("caseid[]", id));
        const { data } = await axios.post("requests/ajaXTaskMassAction", body, {
            baseURL: window.TASK_VIEWS_CONFIG?.baseUrl ?? "/",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
        });
        if (data?.status !== "success") throw new Error("Could not change status for every task.");
    }
}

/** RequestsController::deleteBulkCase — ids and case numbers must line up. */
export async function deleteTasks(tasks) {
    const body = new URLSearchParams();
    tasks.forEach((t) => {
        body.append("id[]", t.numericId);
        body.append("cno[]", t.caseNo ?? "");
    });
    const projectIds = [...new Set(tasks.map((t) => t.projectId).filter(Boolean))];
    if (projectIds.length === 1) body.append("pid", projectIds[0]);

    const { data } = await axios.post("requests/delete_bulk_case", body, {
        baseURL: window.TASK_VIEWS_CONFIG?.baseUrl ?? "/",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
    });
    if (data && data.status === 0) throw new Error("Could not delete those tasks.");
    return data;
}

/**
 * Move or copy to another project. Both endpoints are per-source-project and
 * take parallel case_id/case_no lists, so tasks are batched by origin.
 */
async function relocate(endpoint, tasks, targetProjectId, extra = {}) {
    const byProject = new Map();
    tasks.forEach((t) => {
        if (!byProject.has(t.projectId)) byProject.set(t.projectId, []);
        byProject.get(t.projectId).push(t);
    });

    for (const [oldProjectId, group] of byProject) {
        const body = form({
            project_id: targetProjectId,
            old_project_id: oldProjectId ?? "",
            is_multiple: 1,
            ...extra,
        });
        group.forEach((t) => {
            body.append("case_id[]", t.numericId);
            body.append("case_no[]", t.caseNo ?? "");
        });
        const { data } = await axios.post(endpoint, body, {
            baseURL: window.TASK_VIEWS_CONFIG?.baseUrl ?? "/",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
        });
        const ok = data?.message === "success" || data?.success || data?.status === "success";
        if (!ok) throw new Error(data?.msg || "Could not move those tasks.");
    }
}

export function moveTasksToProject(tasks, targetProjectId) {
    return relocate("easycases/move_task_to_project", tasks, targetProjectId, { move_assignee: 0 });
}

export function copyTasksToProject(tasks, targetProjectId) {
    return relocate("easycases/copy_task_to_project", tasks, targetProjectId);
}

/** Project members, for the assignee filter and bulk assign. */
export async function fetchProjectMembers(projectUniqId) {
    if (!projectUniqId) return [];
    const { data } = await post("milestones/fetchTaskItemOptions", { projUniq: projectUniqId });
    const users = data?.custom_fields?.user_list ?? {};
    return Object.entries(users).map(([id, name]) => ({ id: Number(id), name: String(name) }));
}

/**
 * Task groups for one project, as {id, name}.
 *
 * The list endpoint only emits its milestone arrays when it is itself grouping
 * by task group, so it cannot be relied on for this; fetchTaskItemOptions is
 * the lookup the Create Task form already uses for the same dropdown.
 */
export async function fetchTaskGroups(projectUniqId) {
    if (!projectUniqId) return [];
    const { data } = await post("milestones/fetchTaskItemOptions", { projUniq: projectUniqId });
    const rows = data?.milestones ?? {};
    return Object.entries(rows).map(([id, name]) => ({ id: Number(id), name: String(name) }));
}


/**
 * Estimated hours. The endpoint parses what the task form sends — a plain hour
 * count ("24") or "h:mm" — and stores seconds itself, so hours go out as typed.
 */
export async function saveEstimate(task, hours) {
    const { data } = await post("easycases/ajax_change_est_hour", {
        caseId: task.numericId,
        estHour: hours === null || hours === "" ? 0 : hours,
    });
    if (data?.err) throw new Error(data.msg || "Could not save the estimate.");
    return data;
}

/** Assignee, by user id — the name shown in the cell is not enough to save. */
export async function saveAssignee(task, userId) {
    if (!userId) throw new Error("Unknown assignee");
    const { data } = await post("easycases/ajax_change_AssignTo", {
        caseId: task.numericId,
        assignId: userId,
    });
    if (data?.err) throw new Error(data.msg || "Could not change the assignee.");
    return data;
}

/**
 * Due date. `text` is what the legacy row shows next to the date and the
 * endpoint echoes it back; reason_id is 0 unless the company requires a reason
 * for due-date changes, which the full task form handles separately.
 */
export async function saveDueDate(task, date) {
    const { data } = await post("easycases/ajaxChangeDueDate", {
        caseId: task.numericId,
        duedt: date || "",
        text: date || "",
        reason_id: 0,
    });
    if (data?.success === "No") throw new Error(data.message || "Could not change the due date.");
    return data;
}

/**
 * Move a task to a workflow (custom) status — the kanban drop for projects
 * with their own workflow. Same endpoint the task detail uses, so the master
 * status and legend derive from the chosen row on the server.
 */
export async function saveCustomStatus(task, col) {
    const { data } = await post("easycases/changeCustomStatus", {
        id: task.numericId,
        no: task.caseNo ?? "",
        uniqid: task.id,
        statusid: col.id,
        masterid: col.masterId,
        is_sub: task.parentId ? 1 : 0,
        parent_task: 0,
    });
    if (data?.err) throw new Error(data.msg || "Could not change status.");
    return data;
}

/**
 * Archive — the reversible step before deletion. easycases/archive_case sets
 * isactive=0 and takes subtasks with it, exactly as the legacy list does.
 * Batched per source project, since it resolves the project from the ids.
 */
/**
 * Puts archived tasks back, grouped per project because the endpoint scopes
 * its permission check to one project — same shape as archiveTasks().
 */
export async function restoreTasks(tasks) {
    const byProject = new Map();
    tasks.forEach((t) => {
        if (!byProject.has(t.projectId)) byProject.set(t.projectId, []);
        byProject.get(t.projectId).push(t);
    });

    for (const [pid, group] of byProject) {
        const { data } = await post("easycases/restore_case", {
            id: group.map((t) => t.numericId).join(","),
            pid: pid ?? "",
        });
        if (data?.status !== "success") throw new Error(data?.msg || "Could not restore those tasks.");
    }
}

export async function archiveTasks(tasks) {
    const byProject = new Map();
    tasks.forEach((t) => {
        if (!byProject.has(t.projectId)) byProject.set(t.projectId, []);
        byProject.get(t.projectId).push(t);
    });

    for (const [pid, group] of byProject) {
        const { data } = await post("easycases/archive_case", {
            id: group.map((t) => t.numericId).join(","),
            cno: group.map((t) => t.caseNo ?? "").join(","),
            pid: pid ?? "",
            typ: "all",
        });
        if (data?.status !== "success") throw new Error("Could not archive those tasks.");
    }
}
