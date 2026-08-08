/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Translates the views' filter state into the parameters
 * RequestsController::applyCasefilters understands.
 *
 * Filtering client-side only ever narrowed the pages already loaded, so a
 * project past the page cap silently under-reported and the result count was
 * wrong. Sending the filters instead makes the server do the narrowing and the
 * count come back right.
 *
 * Not everything can go across: the endpoint has no way to express "no due
 * date" or the Urgent priority, and the list rows carry no label data. So each
 * mapper also reports whether it handled its filter — whatever it did not, the
 * store still applies locally.
 */

/** Vue status value -> easycases.legend. The endpoint expands 2 to 2-or-4. */
const STATUS_LEGEND = { new: 1, in_progress: 2, resolved: 5, closed: 3 };

/** The endpoint matches priority by label, and knows only these three. */
const PRIORITY_LABEL = { high: "High", medium: "Medium", low: "Low" };

/**
 * Created-date ranges, named as the endpoint names them. These are the ones it
 * actually implements — inventing "last quarter" here would just fall through
 * to its custom-range branch and be parsed as a date.
 */
export const CREATED_OPTIONS = [
    { value: "one", label: "Last hour" },
    { value: "24", label: "Last 24 hours" },
    { value: "today", label: "Today" },
    { value: "week", label: "Last week" },
    { value: "month", label: "Last month" },
    { value: "year", label: "Last year" },
];

/**
 * Presets, the same seven the legacy sidebar offers. Most are expressed through
 * the ordinary filters rather than caseMenuFilters, whose favourite branch is a
 * stub in this endpoint.
 */
export const PRESETS = [
    { value: "", label: "All tasks" },
    { value: "open", label: "Open tasks" },
    { value: "closed", label: "Closed tasks" },
    { value: "assigntome", label: "Assigned to me" },
    { value: "overdue", label: "Overdue" },
    { value: "highpriority", label: "High priority" },
    { value: "favourite", label: "Favourites" },
];

const pad = (n) => String(n).padStart(2, "0");
const ymd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

function offsetDays(days) {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() + days);
    return d;
}

/**
 * Due buckets are relative windows; the endpoint takes either one of two named
 * filters or an explicit from:to range, so every bucket but "no due date"
 * becomes a range. One bucket at a time — two disjoint windows are not a range.
 */
function dueParam(bucket) {
    switch (bucket) {
        case "overdue":
            return "overdue";
        case "today":
            return "24";
        case "tomorrow":
            return `${ymd(offsetDays(1))}:${ymd(offsetDays(1))}`;
        case "week":
            return `${ymd(offsetDays(2))}:${ymd(offsetDays(7))}`;
        case "month":
            return `${ymd(offsetDays(8))}:${ymd(offsetDays(30))}`;
        case "later":
            return `${ymd(offsetDays(31))}:${ymd(offsetDays(3650))}`;
        default:
            // "none" — absence of a due date is not a date range.
            return null;
    }
}

/**
 * @param {object} f      the store's filter state
 * @param {object} maps   {typeIds: Map<name,id>, memberIds: Map<name,id>}
 * @returns {{params: Object<string,string>, handled: Set<string>}}
 */
export function toServerFilters(f, maps = {}) {
    const params = {};
    const handled = new Set();
    const typeIds = maps.typeIds ?? new Map();
    const memberIds = maps.memberIds ?? new Map();

    /** Ids for a list of display names; null if any name is unknown to us. */
    const idsFor = (names, lookup) => {
        const ids = names.map((n) => lookup.get(n));
        return ids.every((id) => id != null) ? ids : null;
    };

    if (f.preset === "assigntome") {
        params.caseMenuFilters = "assigntome";
        handled.add("preset");
    }

    // ---- status -----------------------------------------------------------
    const status = f.preset === "open" ? ["new", "in_progress"]
        : f.preset === "closed" ? ["closed"]
            : f.status;
    if (status.length) {
        const legends = status.map((s) => STATUS_LEGEND[s]).filter(Boolean);
        if (legends.length === status.length) {
            params.caseStatus = legends.join("-");
            handled.add("status");
            if (f.preset === "open" || f.preset === "closed") handled.add("preset");
        }
    }

    // ---- priority ---------------------------------------------------------
    const priority = f.preset === "highpriority" ? ["high"] : f.priority;
    if (priority.length) {
        const labels = priority.map((p) => PRIORITY_LABEL[p]);
        // Urgent has no counterpart; sending the rest would drop its tasks.
        if (labels.every(Boolean)) {
            params.priFil = labels.join("-");
            handled.add("priority");
            if (f.preset === "highpriority") handled.add("preset");
        }
    }

    // ---- type -------------------------------------------------------------
    if (f.type.length) {
        const ids = idsFor(f.type, typeIds);
        if (ids) {
            params.caseTypes = ids.join("-");
            handled.add("type");
        }
    }

    // ---- assignee ---------------------------------------------------------
    if (f.assignee.length) {
        const real = f.assignee.filter((n) => n !== "Unassigned");
        const wantsUnassigned = real.length !== f.assignee.length;
        if (wantsUnassigned && !real.length) {
            params.caseAssignTo = "unassigned";
            handled.add("assignee");
        } else if (!wantsUnassigned) {
            const ids = idsFor(real, memberIds);
            if (ids) {
                params.caseAssignTo = ids.join("-");
                handled.add("assignee");
            }
        }
        // Unassigned mixed with named people is an OR the endpoint cannot take.
    }

    // ---- task group -------------------------------------------------------
    if (f.taskGroup.length) {
        params.caseTaskGroup = f.taskGroup.join("-");
        handled.add("taskGroup");
    }

    // ---- due date ---------------------------------------------------------
    const due = f.preset === "overdue" ? ["overdue"] : f.due;
    if (due.length === 1) {
        const param = dueParam(due[0]);
        if (param) {
            params.case_due_date = param;
            handled.add("due");
            if (f.preset === "overdue") handled.add("preset");
        }
    }

    // ---- created date -----------------------------------------------------
    if (f.createdRange) {
        params.case_date = f.createdRange;
        handled.add("createdRange");
    }

    // ---- created by / commented by ---------------------------------------
    if (f.createdBy.length) {
        const ids = idsFor(f.createdBy, memberIds);
        if (ids) {
            params.caseMember = ids.join("-");
            handled.add("createdBy");
        }
    }
    if (f.commentedBy.length) {
        const ids = idsFor(f.commentedBy, memberIds);
        if (ids) {
            params.caseComment = ids.join("-");
            handled.add("commentedBy");
        }
    }


    // ---- label ------------------------------------------------------------
    if (f.label.length) {
        params.caseLabel = f.label.join("-");
        handled.add("label");
    }

    // Favourites stays client-side: this endpoint's favourite branch is an
    // empty stub, but the rows do carry isFavourite, so the store can match on
    // it directly.

    return { params, handled };
}
