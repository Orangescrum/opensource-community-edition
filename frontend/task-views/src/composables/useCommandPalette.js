import { ref } from "vue";

/**
 * Global keyboard entry points for the task views.
 *
 * State is module-level so every caller shares one palette and one help
 * overlay, and the document-level key listener is registered exactly once no
 * matter how many components call the composable.
 *
 *   Ctrl/Cmd + K  toggle the command palette
 *   ?             open the shortcuts help (bare keystroke, outside inputs)
 *
 * Esc is left to the Vuetify dialogs themselves.
 */
const paletteOpen = ref(false);
const helpOpen = ref(false);
let bound = false;

function isEditableTarget(el) {
    if (!el) return false;
    const tag = el.tagName;
    if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT") return true;
    return !!el.isContentEditable;
}

function onKeydown(e) {
    const k = (e.key || "").toLowerCase();

    // Ctrl/Cmd+K toggles the palette from anywhere, including from inside a
    // field, so it is always reachable. preventDefault keeps it off the
    // browser's own address-bar shortcut.
    if ((e.ctrlKey || e.metaKey) && k === "k") {
        e.preventDefault();
        paletteOpen.value = !paletteOpen.value;
        if (paletteOpen.value) helpOpen.value = false;
        return;
    }

    // "?" opens help, but only as a bare keystroke outside an editable field —
    // otherwise it would fire while someone types a question mark, and while
    // the palette's own search box is focused. Some layouts report Shift+"/"
    // as key "/" rather than "?", so accept both.
    const isHelpKey = k === "?" || (k === "/" && e.shiftKey);
    if (isHelpKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
        if (paletteOpen.value || isEditableTarget(document.activeElement)) return;
        e.preventDefault();
        helpOpen.value = !helpOpen.value;
    }
}

export function useCommandPalette() {
    if (!bound && typeof window !== "undefined") {
        bound = true;
        window.addEventListener("keydown", onKeydown);
    }
    return {
        paletteOpen,
        helpOpen,
        openPalette: () => { paletteOpen.value = true; helpOpen.value = false; },
        closePalette: () => { paletteOpen.value = false; },
        openHelp: () => { helpOpen.value = true; },
        closeHelp: () => { helpOpen.value = false; },
    };
}
