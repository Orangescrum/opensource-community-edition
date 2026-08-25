import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { PRIORITIES, STATUSES } from "@/data/tasks";
import { CREATED_OPTIONS } from "@/data/serverFilters";

/** Mirrors the store's dueBucket keys. */
export const DUE_FILTERS = [
    { value: "overdue", label: "Overdue" },
    { value: "today", label: "Today" },
    { value: "tomorrow", label: "Tomorrow" },
    { value: "week", label: "This week" },
    { value: "month", label: "This month" },
    { value: "later", label: "Later" },
    { value: "none", label: "No due date" },
];

/**
 * One description of every filter the toolbar offers, so the menu that sets a
 * filter and the chip strip that reports it cannot drift apart.
 *
 * `kind` decides how a field is read and written:
 *   multi  — an array facet, toggled through toggleFacet/clearFacet
 *   single — one value or none, written through setFilter
 *   toggle — a boolean, with its own setter because Archived also refetches
 */
export function useFilterFields() {
    const store = useTaskStore();

    const fields = computed(() => {
        const list = [
            { key: "status", label: "Status", icon: "mdi-progress-check", kind: "multi", options: STATUSES },
            { key: "priority", label: "Priority", icon: "mdi-flag-outline", kind: "multi", options: PRIORITIES },
            { key: "type", label: "Type", icon: "mdi-shape-outline", kind: "multi", options: store.typeOptions },
            { key: "assignee", label: "Assign to", icon: "mdi-account-outline", kind: "multi", options: store.assigneeOptions },
            { key: "taskGroup", label: "Task group", icon: "mdi-folder-outline", kind: "multi", options: store.taskGroupOptions },
            { key: "due", label: "Due date", icon: "mdi-calendar-outline", kind: "multi", options: DUE_FILTERS },
            { key: "createdBy", label: "Created by", icon: "mdi-account-edit-outline", kind: "multi", options: store.assigneeOptions },
            { key: "commentedBy", label: "Commented by", icon: "mdi-comment-text-outline", kind: "multi", options: store.assigneeOptions },
        ];

        if (store.labelOptions.length) {
            list.push({ key: "label", label: "Label", icon: "mdi-tag-outline", kind: "multi", options: store.labelOptions });
        }

        list.push(
            { key: "createdRange", label: "Created", icon: "mdi-calendar-plus", kind: "single", options: CREATED_OPTIONS },
            { key: "favourite", label: "Favourites", icon: "mdi-star-outline", kind: "toggle" },
            { key: "showArchived", label: "Archived", icon: "mdi-archive-outline", kind: "toggle" },
        );

        return list;
    });

    const labelFor = (field, value) =>
        field.options?.find((o) => String(o.value) === String(value))?.label ?? value;

    function countOf(field) {
        if (field.kind === "multi") return store[field.key].length;
        return store[field.key] ? 1 : 0;
    }

    /** What the field's row shows on the right: the choice, or how many. */
    function summaryOf(field) {
        const n = countOf(field);
        if (!n) return "";
        if (field.kind === "toggle") return "On";
        if (field.kind === "single") return labelFor(field, store[field.key]);
        return n === 1 ? labelFor(field, store[field.key][0]) : `${n} selected`;
    }

    function isChosen(field, value) {
        if (field.kind === "multi") return store[field.key].includes(value);
        return store[field.key] === value;
    }

    function choose(field, value) {
        if (field.kind === "multi") store.toggleFacet(field.key, value);
        else store.setFilter(field.key, store[field.key] === value ? "" : value);
    }

    function toggle(field) {
        if (field.key === "showArchived") store.setShowArchived(!store.showArchived);
        else if (field.key === "favourite") store.toggleFavourite();
        else store.setFilter(field.key, !store[field.key]);
    }

    function clear(field) {
        if (field.kind === "multi") store.clearFacet(field.key);
        else if (field.kind === "toggle") {
            if (store[field.key]) toggle(field);
        } else store.setFilter(field.key, "");
    }

    /** One chip per chosen value, so a multi-select reads as its own values. */
    const chips = computed(() =>
        fields.value.flatMap((field) => {
            if (field.kind === "multi") {
                return store[field.key].map((value) => ({
                    id: `${field.key}:${value}`,
                    field,
                    value,
                    text: `${field.label}: ${labelFor(field, value)}`,
                }));
            }
            if (!store[field.key]) return [];
            return [{
                id: field.key,
                field,
                value: null,
                text: field.kind === "toggle" ? field.label : `${field.label}: ${labelFor(field, store[field.key])}`,
            }];
        }),
    );

    /** Only what this menu owns — the search box and preset report themselves. */
    const activeCount = computed(() =>
        fields.value.reduce((n, field) => n + countOf(field), 0),
    );

    function removeChip(chip) {
        if (chip.field.kind === "multi") store.toggleFacet(chip.field.key, chip.value);
        else clear(chip.field);
    }

    function clearAll() {
        fields.value.forEach(clear);
    }

    return { fields, chips, activeCount, countOf, summaryOf, isChosen, choose, toggle, clear, removeChip, clearAll };
}
