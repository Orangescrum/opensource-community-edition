<script setup>
import { useTaskStore } from "@/store/useTaskStore";
import { usePagedGroups } from "@/composables/usePagination";
import PageBar from "@/components/PageBar.vue";
import CompactRow from "@/components/CompactRow.vue";
import ListHeader from "@/components/ListHeader.vue";
import GroupHeader from "@/components/GroupHeader.vue";

const store = useTaskStore();
const pager = usePagedGroups(store);
</script>

<template>
    <div class="mw">
        <ListHeader :rows="store.mine" />

        <template v-for="g in pager.groups.value" :key="g.key">
            <GroupHeader v-if="store.groupBy" :group="g" />
            <div v-show="!g.collapsed">
                <CompactRow v-for="t in g.rows" :key="t.id" :task="t" />
            </div>
        </template>

        <PageBar
            v-model:page="pager.page.value"
            v-model:per-page="pager.perPage.value"
            :page-count="pager.pageCount.value"
        />

        <p v-if="!store.mine.length" class="mw__empty tv-meta">
            Nothing assigned to you here. Enjoy the quiet.
        </p>
    </div>
</template>

<style scoped>
.mw__empty {
    padding: 48px 20px;
    text-align: center;
}
</style>
