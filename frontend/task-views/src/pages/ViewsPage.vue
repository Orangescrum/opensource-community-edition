<script setup>
import { useTaskStore } from "@/store/useTaskStore";
import InlineView from "@/views/InlineView.vue";
import SheetView from "@/views/SheetView.vue";
import TableView from "@/views/TableView.vue";

const store = useTaskStore();
const VIEWS = { inline: InlineView, sheet: SheetView, table: TableView };
</script>

<template>
    <!-- keep-alive so scroll position, the sheet's active cell and any
         in-progress edit survive a switch between inline/sheet/table -->
    <KeepAlive>
        <component :is="VIEWS[store.view]" />
    </KeepAlive>
</template>
