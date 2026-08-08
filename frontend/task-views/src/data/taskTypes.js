/**
 * Task types.
 *
 * The list is already on the page: footer_inner.php emits `GLOBALS_TYPE`, the
 * same global the legacy task list reads to build its type menu. Taking it from
 * there means the two lists cannot disagree, and costs no extra request.
 */

/**
 * The icon is a sprite in custom.css keyed by a slug of the type name —
 * `getttformats()` in script_v1.js does exactly this. Reproduced rather than
 * called so the bundle has no hard dependency on legacy script load order.
 */
export function typeSlug(name) {
    return String(name ?? "").toLowerCase().split(" ").join("-");
}

export function taskTypes() {
    const raw = window.GLOBALS_TYPE;
    if (!raw) return [];

    return Object.values(raw)
        .map((row) => row?.Type ?? row?.Types ?? row?.type)
        .filter((t) => t?.id != null && t?.name)
        .map((t) => ({
            id: Number(t.id),
            name: String(t.name),
            shortName: String(t.short_name ?? ""),
            seq: Number(t.seq_order ?? 0),
        }))
        .sort((a, b) => a.seq - b.seq || a.name.localeCompare(b.name));
}
