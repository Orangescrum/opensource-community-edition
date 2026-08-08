<script setup>
import { computed, ref, watch } from "vue";

/**
 * Type-to-confirm dialog.
 *
 * The confirm button stays disabled until the exact word is typed, so a
 * destructive action can't be triggered by a stray Enter or a mis-aimed click
 * on a dialog that appeared under the cursor.
 */
const open = defineModel({ type: Boolean, required: true });

const props = defineProps({
    /** The word the user must type, e.g. "archive" or "delete". */
    word: { type: String, required: true },
    title: { type: String, required: true },
    body: { type: String, default: "" },
    confirmLabel: { type: String, required: true },
    /** Red confirm button for irreversible actions. */
    danger: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
});

const emit = defineEmits(["confirm"]);

const typed = ref("");
const matches = computed(() => typed.value.trim().toLowerCase() === props.word.toLowerCase());

// Never carry a satisfied box into the next confirmation.
watch(open, (isOpen) => { if (!isOpen) typed.value = ""; });

function submit() {
    if (!matches.value || props.busy) return;
    open.value = false;
    emit("confirm");
}
</script>

<template>
    <v-dialog v-model="open" max-width="420">
        <div class="ct">
            <h2 class="ct__title">{{ title }}</h2>
            <p v-if="body" class="ct__body tv-meta">{{ body }}</p>

            <label class="ct__label tv-meta">
                Type <strong>{{ word }}</strong> to confirm
                <input
                    v-model="typed"
                    type="text"
                    class="ct__input"
                    autocomplete="off"
                    spellcheck="false"
                    :aria-label="`Type ${word} to confirm`"
                    @keydown.enter.prevent="submit"
                />
            </label>

            <div class="ct__actions">
                <button type="button" class="ct__btn" @click="open = false">Cancel</button>
                <button
                    type="button"
                    class="ct__btn"
                    :class="danger ? 'ct__btn--danger' : 'ct__btn--primary'"
                    :disabled="!matches || busy"
                    @click="submit"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </v-dialog>
</template>

<style scoped>
.ct {
    padding: 20px;
    background: var(--tv-paper);
    border-radius: var(--tv-radius);
}

.ct__title {
    margin: 0 0 6px;
    font-size: var(--tv-size-lead);
    font-weight: 600;
    color: var(--tv-ink);
}

.ct__body {
    margin: 0 0 14px;
}

.ct__label {
    display: block;
    margin-block-end: 18px;
}

.ct__input {
    display: block;
    inline-size: 100%;
    margin-block-start: 6px;
    padding: 7px 10px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font: inherit;
    color: var(--tv-ink);
}

.ct__input:focus {
    outline: 2px solid var(--tv-brand);
    outline-offset: -2px;
}

.ct__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.ct__btn {
    height: 32px;
    padding: 0 14px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: var(--tv-radius);
    background: var(--tv-paper);
    font-size: var(--tv-size-meta);
    font-weight: 500;
    color: var(--tv-ink-2);
    cursor: pointer;
}

.ct__btn--primary {
    border-color: var(--tv-brand);
    background: var(--tv-brand);
    color: #fff;
}

.ct__btn--danger {
    border-color: #b3261e;
    background: #b3261e;
    color: #fff;
}

.ct__btn:disabled {
    opacity: 0.5;
    cursor: default;
}
</style>
