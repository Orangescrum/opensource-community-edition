import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";

/**
 * Column visibility for the two grid-based lists (Subtask View, My Works).
 *
 * The Columns control had no effect on these: CompactRow rendered every cell
 * and .tv-lrow declared a fixed nine-track grid, so unticking a column left it
 * on screen. Hiding the cell alone is not enough either — the grid keeps the
 * empty track and everything after it stays shifted — so the track list is
 * rebuilt to match and handed to the CSS as a custom property.
 *
 * The header and the rows both read it from here, which is what stops the two
 * drifting out of alignment.
 */

const TRACKS = {
    id: "84px",
    title: "minmax(200px, 1fr)",
    type: "132px",
    assignee: "132px",
    status: "148px",
    priority: "104px",
};

/** Grid order — the cell order in ListHeader and CompactRow. */
const ORDER = ["id", "title", "type", "assignee", "status", "priority"];

/** Below the breakpoint the stylesheet keeps only these two. */
const NARROW = { title: "minmax(0, 1fr)", status: "120px" };

export function useListColumns() {
    const store = useTaskStore();

    const shows = (key) => !store.hiddenColumns.has(key);

    const listStyle = computed(() => {
        const wide = ORDER.filter(shows).map((k) => TRACKS[k]);
        const narrow = Object.keys(NARROW).filter(shows).map((k) => NARROW[k]);

        return {
            "--tv-lrow-tracks": `26px 32px ${wide.join(" ")} 40px`,
            "--tv-lrow-tracks-narrow": `26px 32px ${narrow.join(" ")} 40px`,
        };
    });

    return { shows, listStyle };
}
