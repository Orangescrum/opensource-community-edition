<script setup>
import { useTaskStore } from "@/store/useTaskStore";
import SubtaskNode from "@/components/SubtaskNode.vue";
import ListHeader from "@/components/ListHeader.vue";
import GroupHeader from "@/components/GroupHeader.vue";

const store = useTaskStore();
</script>

<template>
    <div class="sub">
        <ListHeader
            :rows="store.subtaskRows"
            expandable
            :all-expanded="store.allParentsExpanded"
            @toggle-expand-all="store.toggleAllParents()"
        />

        <template v-for="g in store.subtaskSections" :key="g.key">
            <GroupHeader v-if="store.groupBy" :group="g" />
            <div v-show="!g.collapsed" class="sub__list">
                <SubtaskNode
                    v-for="(node, i) in g.nodes"
                    :key="node.task.id"
                    :node="node"
                    :is-last="i === g.nodes.length - 1"
                />
            </div>
        </template>

        <p v-if="!store.subtaskSections.length" class="sub__empty tv-meta">
            No tasks match these filters.
        </p>
    </div>
</template>

<style scoped>
.sub__empty {
    padding: 48px 20px;
    text-align: center;
}
</style>
