<script setup>
import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import CompactRow from "@/components/CompactRow.vue";

/**
 * One node of the subtask tree, plus its descendants.
 *
 * Recursive because subtasks nest without a fixed limit — a flat
 * parent-then-children render only ever showed the first level, so a subtask's
 * own subtasks were invisible.
 */
defineOptions({ name: "SubtaskNode" });

const props = defineProps({
    node: { type: Object, required: true },
    isLast: { type: Boolean, default: false },
    /** For each ancestor level, whether that ancestor was its parent's last child. */
    trail: { type: Array, default: () => [] },
});

/*
 * Roots draw no rail, so they contribute nothing to their children's trail.
 * Every deeper node hands its own lastness down as one more ancestor flag.
 */
const childTrail = computed(() =>
    props.node.depth === 0 ? [] : [...props.trail, props.isLast]
);

const store = useTaskStore();
</script>

<template>
    <CompactRow
        :task="node.task"
        :depth="node.depth"
        :is-last="isLast"
        :trail="trail"
        :orphaned="node.orphaned"
        :expandable="node.children.length > 0"
        :expanded="node.expanded"
        :child-count="node.children.length"
        @toggle="store.toggleParent(node.task.numericId)"
    />
    <template v-if="node.expanded">
        <SubtaskNode
            v-for="(child, i) in node.children"
            :key="child.task.id"
            :node="child"
            :is-last="i === node.children.length - 1"
            :trail="childTrail"
        />
    </template>
</template>
