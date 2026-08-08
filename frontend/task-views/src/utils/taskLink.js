/**
 * Opening a task.
 *
 * The task-detail panel is the legacy slider (easycase.ajaxCaseDetails, from
 * dashboard_v1.js, rendered into #cnt_task_detail_kb). The Vue task pages load
 * that script too, so a task opens *in place* — no page load, and closing the
 * panel returns to the list you were on, because the legacy close handler
 * restores the `last_url` it recorded on open.
 *
 * The <a href> is kept as the element so middle-click and "open in new tab"
 * still work; those get the standalone URL and the full page load that comes
 * with it. Only a plain left-click is intercepted.
 */

/** Standalone URL for the task — the fallback and the new-tab target. */
export function taskHref(task) {
    if (!task?.url) return null;
    return `${window.TASK_VIEWS_CONFIG?.baseUrl ?? "/"}${task.url}`;
}

function canOpenInPlace() {
    return typeof window.easycase?.ajaxCaseDetails === "function";
}

/**
 * Returns true when the click was handled in place, so callers can suppress
 * their own navigation.
 */
export function openTask(task, event) {
    if (!task?.url) return false;

    // Let the browser do its thing for modified clicks — new tab, new window,
    // download — rather than hijacking them into the slider.
    if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button > 0)) {
        return false;
    }

    if (canOpenInPlace() && task.id) {
        event?.preventDefault();
        window.easycase.ajaxCaseDetails(task.id, "", "");
        return true;
    }

    const href = taskHref(task);
    if (href) window.location.href = href;
    return true;
}
