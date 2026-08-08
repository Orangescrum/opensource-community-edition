<script setup>
import { nextTick, ref } from "vue";

/**
 * Click (or Enter) to edit, Enter/blur to commit, Escape to revert.
 * Shared by the inline and sheet views so an edit feels the same in both.
 */
const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    type: { type: String, default: "text" }, // text | number | date | select
    options: { type: Array, default: () => [] }, // [{ value, label }]
    placeholder: { type: String, default: "—" },
    align: { type: String, default: "start" },
    ariaLabel: { type: String, default: undefined },
});

const emit = defineEmits(["update:modelValue"]);

const editing = ref(false);
const draft = ref(null);
const field = ref(null);

async function begin() {
    draft.value = props.modelValue ?? "";
    editing.value = true;
    await nextTick();
    field.value?.focus();
    if (props.type === "text") field.value?.select?.();
}

function commit() {
    if (!editing.value) return;
    editing.value = false;
    const raw = draft.value;
    const next = raw === "" ? null : props.type === "number" ? Number(raw) : raw;
    if (next !== props.modelValue) emit("update:modelValue", next);
}

function cancel() {
    editing.value = false;
}

function onSelect(value) {
    editing.value = false;
    if (value !== props.modelValue) emit("update:modelValue", value);
}

defineExpose({ begin });
</script>

<template>
    <!-- select: menu, no intermediate draft state -->
    <v-menu v-if="type === 'select'" location="bottom start" offset="2">
        <template #activator="{ props: menu }">
            <button v-bind="menu" type="button" class="tv-cell tv-cell--btn" :style="{ justifyContent: align }">
                <slot>{{ modelValue ?? placeholder }}</slot>
            </button>
        </template>
        <div class="tv-cellpop">
            <button
                v-for="o in options"
                :key="o.value"
                type="button"
                class="tv-cellpop__row"
                :class="{ 'is-on': o.value === modelValue }"
                @click="onSelect(o.value)"
            >
                <v-icon
                    :icon="o.value === modelValue ? 'mdi-check' : 'mdi-blank'"
                    size="12"
                    aria-hidden="true"
                />
                <span>{{ o.label }}</span>
            </button>
        </div>
    </v-menu>

    <!-- free text / number / date -->
    <div v-else class="tv-cellwrap">
        <input
            v-if="editing"
            ref="field"
            v-model="draft"
            :type="type"
            :aria-label="ariaLabel"
            class="tv-cell tv-cell--input"
            :style="{ textAlign: align === 'end' ? 'right' : 'left' }"
            @keydown.enter.prevent="commit"
            @keydown.esc.prevent="cancel"
            @blur="commit"
        />
        <button
            v-else
            type="button"
            class="tv-cell tv-cell--btn"
            :aria-label="ariaLabel"
            :style="{ justifyContent: align }"
            @click="begin"
            @keydown.enter.prevent="begin"
        >
            <slot>
                <span :class="{ 'is-empty': modelValue === null || modelValue === '' }">
                    {{ modelValue === null || modelValue === "" ? placeholder : modelValue }}
                </span>
            </slot>
        </button>
    </div>
</template>

<style scoped>
.tv-cellwrap {
    min-inline-size: 0;
}

.tv-cell {
    display: flex;
    align-items: center;
    inline-size: 100%;
    min-inline-size: 0;
    block-size: 24px;
    padding: 0 6px;
    border: 1px solid transparent;
    border-radius: 3px;
    background: transparent;
    font-size: inherit;
    font-family: inherit;
    color: inherit;
    text-align: start;
    cursor: text;
}

.tv-cell--btn > * {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tv-cell--btn:hover {
    border-color: var(--tv-rule-strong);
    background: var(--tv-paper);
}

.tv-cell--input {
    border-color: var(--tv-brand);
    background: var(--tv-paper);
    box-shadow: 0 0 0 3px var(--tv-brand-ring);
    outline: 0;
    cursor: text;
}

.is-empty {
    color: var(--tv-faint);
}

.tv-cellpop {
    min-inline-size: 168px;
    padding: 4px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    box-shadow: var(--tv-shadow-pop);
}

.tv-cellpop__row {
    display: flex;
    align-items: center;
    gap: 7px;
    inline-size: 100%;
    padding: 5px 8px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
    cursor: pointer;
    text-align: start;
}

.tv-cellpop__row:hover {
    background: var(--tv-sub);
}

.tv-cellpop__row.is-on {
    font-weight: 500;
}
</style>
