<script setup>
import { computed } from "vue";
import { useTaskStore } from "@/store/useTaskStore";
import { typeSlug } from "@/data/taskTypes";

/**
 * The task type, shown the way the legacy list shows it: the type's sprite icon
 * plus its name, and a menu to change it in place.
 *
 * `ttype_global tt_<slug>` are the app's own global classes (custom.css) — the
 * icons are one sprite sheet, so reusing the classes keeps every type's icon
 * identical to the old list instead of inventing a second icon set.
 */
const props = defineProps({
    task: { type: Object, required: true },
    /** Read-only where an inline menu would fight the surrounding control. */
    readonly: { type: Boolean, default: false },
});

const store = useTaskStore();

const slug = computed(() => typeSlug(props.task.type));
const options = computed(() => store.taskTypeOptions);

function pick(type) {
    if (type.name !== props.task.type) store.patch(props.task.id, "type", type.name);
}
</script>

<template>
    <span v-if="!task.type" />

    <span v-else-if="readonly || !options.length" class="ttype_global tv-type" :class="`tt_${slug}`">
        {{ task.type }}
    </span>

    <v-menu v-else location="bottom start" offset="2">
        <template #activator="{ props: menu }">
            <button
                v-bind="menu"
                type="button"
                class="tv-type tv-type--btn ttype_global"
                :class="`tt_${slug}`"
                :title="`Task type: ${task.type}`"
                @click.stop
            >
                {{ task.type }}
            </button>
        </template>
        <div class="tv-pop tv-pop--scroll">
            <button
                v-for="t in options"
                :key="t.id"
                type="button"
                class="tv-pop__row"
                @click="pick(t)"
            >
                <span class="ttype_global tv-type" :class="`tt_${typeSlug(t.name)}`">{{ t.name }}</span>
            </button>
        </div>
    </v-menu>
</template>

<style scoped>
/* .ttype_global is a global rule (custom.css) that supplies the sprite via
   ::before and a 20px left pad. Only the type-agnostic bits belong here. */
.tv-type {
    display: inline-block;
    inline-size: auto;
    max-inline-size: 100%;
    font-size: var(--tv-size-meta);
    color: var(--tv-ink-2);
    white-space: nowrap;
    line-height: 18px;
}

.tv-type--btn {
    border: 0;
    background: transparent;
    padding-block: 0;
    border-radius: var(--tv-radius);
    cursor: pointer;
    text-align: start;
}

.tv-type--btn:hover {
    color: var(--tv-brand);
}

.tv-pop {
    min-inline-size: 190px;
    padding: 4px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    box-shadow: var(--tv-shadow-pop);
}

/* 12 types is more than fits comfortably — matches the legacy menu, which
   scrolls at roughly the same point. */
.tv-pop--scroll {
    max-block-size: 320px;
    overflow-y: auto;
}

.tv-pop__row {
    display: flex;
    align-items: center;
    inline-size: 100%;
    padding: 5px 8px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    cursor: pointer;
    text-align: start;
}

.tv-pop__row:hover {
    background: var(--tv-sub);
}
</style>
