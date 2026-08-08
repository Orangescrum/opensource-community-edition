import { computed, ref, watch } from "vue";

/**
 * Client-side pagination over a computed list of entries.
 *
 * Entries rather than tasks so grouped views can paginate what is actually on
 * screen — group bands count as rows; paginating tasks and then grouping each
 * page would put a band at the top of every page and split groups across them.
 */
export function usePagination(entries, initialPerPage = 25) {
    const page = ref(1);
    const perPage = ref(initialPerPage);

    const pageCount = computed(() =>
        Math.max(1, Math.ceil(entries.value.length / perPage.value)),
    );

    const rows = computed(() => {
        const start = (page.value - 1) * perPage.value;
        return entries.value.slice(start, start + perPage.value);
    });

    // Filtering can shrink the list under the cursor; never strand the user on
    // a page that no longer exists.
    watch([() => entries.value.length, perPage], () => {
        if (page.value > pageCount.value) page.value = pageCount.value;
    });

    return { page, perPage, pageCount, rows };
}

/**
 * Pagination for the grouped list views, keeping their nested group -> rows
 * rendering. Group bands count as one row each (matching the Table view), and
 * a group whose rows continue onto the next page repeats its header there so
 * rows never appear unlabeled.
 */
export function usePagedGroups(store, initialPerPage = 25) {
    // My Works pages its own subset, mirroring what grouped() renders.
    const flat = computed(() => (store.page === "myworks" ? store.mine : store.visible));

    const entryCount = computed(() => {
        if (!store.groupBy) return flat.value.length;
        return store.grouped.reduce(
            (n, g) => n + 1 + (g.collapsed ? 0 : g.rows.length),
            0,
        );
    });

    const page = ref(1);
    const perPage = ref(initialPerPage);
    const pageCount = computed(() =>
        Math.max(1, Math.ceil(entryCount.value / perPage.value)),
    );

    watch([entryCount, perPage], () => {
        if (page.value > pageCount.value) page.value = pageCount.value;
    });

    // A new grouping is a new reading order; stay at its beginning.
    watch(() => store.groupBy, () => { page.value = 1; });

    const groups = computed(() => {
        const start = (page.value - 1) * perPage.value;
        const end = start + perPage.value;

        if (!store.groupBy) {
            const rows = flat.value.slice(start, end);
            return rows.length || !flat.value.length
                ? [{ key: "", label: "", rows, collapsed: false }]
                : [];
        }

        const out = [];
        let at = 0;
        for (const g of store.grouped) {
            const bandAt = at;
            at += 1;
            const rowsFrom = at;
            const rowCount = g.collapsed ? 0 : g.rows.length;
            at += rowCount;

            const sliceFrom = Math.max(start, rowsFrom) - rowsFrom;
            const sliceTo = Math.min(end, rowsFrom + rowCount) - rowsFrom;
            const rows = sliceTo > sliceFrom ? g.rows.slice(sliceFrom, sliceTo) : [];
            const bandVisible = bandAt >= start && bandAt < end;
            // `total` keeps the header count honest: rows is only this
            // page's slice of the group.
            if (rows.length || bandVisible) out.push({ ...g, rows, total: g.rows.length });
            if (at >= end) break;
        }
        return out;
    });

    return { page, perPage, pageCount, groups, total: entryCount };
}
