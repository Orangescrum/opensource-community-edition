<script setup>
import { useCommandPalette } from "@/composables/useCommandPalette";

const { helpOpen } = useCommandPalette();

// ⌘ on Apple hardware, Ctrl elsewhere — the label should match the key the
// viewer actually presses.
const isMac = typeof navigator !== "undefined" &&
    /mac|iphone|ipad|ipod/i.test(navigator.platform || navigator.userAgent || "");
const mod = isMac ? "⌘" : "Ctrl";

const shortcuts = [
    { keys: [mod, "K"], label: "Open the command palette" },
    { keys: ["?"], label: "Show this shortcuts help" },
    { keys: ["↑", "↓"], label: "Move between results" },
    { keys: ["↵"], label: "Open / run the highlighted item" },
    { keys: ["Esc"], label: "Close the palette or this dialog" },
];
</script>

<template>
    <v-dialog v-model="helpOpen" max-width="420" transition="fade-transition">
        <div class="tv-keys">
            <div class="tv-keys__head">
                <v-icon icon="mdi-keyboard-outline" size="18" aria-hidden="true" />
                <span>Keyboard shortcuts</span>
                <button type="button" class="tv-keys__x" aria-label="Close" @click="helpOpen = false">
                    <v-icon icon="mdi-close" size="16" />
                </button>
            </div>
            <ul class="tv-keys__list">
                <li v-for="(s, i) in shortcuts" :key="i" class="tv-keys__row">
                    <span class="tv-keys__label">{{ s.label }}</span>
                    <span class="tv-keys__combo">
                        <kbd v-for="(k, j) in s.keys" :key="j">{{ k }}</kbd>
                    </span>
                </li>
            </ul>
        </div>
    </v-dialog>
</template>

<style scoped>
.tv-keys {
    background: var(--tv-paper);
    border-radius: var(--tv-radius-lg);
    box-shadow: var(--tv-shadow-pop);
    overflow: hidden;
    font-family: var(--tv-font);
}

.tv-keys__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-block-end: 1px solid var(--tv-rule);
    font-size: var(--tv-size-title);
    font-weight: 600;
    color: var(--tv-ink);
}

.tv-keys__x {
    margin-inline-start: auto;
    display: grid;
    place-items: center;
    inline-size: 24px;
    block-size: 24px;
    border: 0;
    border-radius: var(--tv-radius);
    background: transparent;
    color: var(--tv-muted);
    cursor: pointer;
}

.tv-keys__x:hover {
    background: var(--tv-sub);
    color: var(--tv-ink);
}

.tv-keys__list {
    list-style: none;
    margin: 0;
    padding: 8px;
}

.tv-keys__row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 8px;
}

.tv-keys__label {
    flex: 1;
    font-size: var(--tv-size-body);
    color: var(--tv-ink);
}

.tv-keys__combo {
    flex: none;
    display: inline-flex;
    gap: 4px;
}

.tv-keys__combo kbd {
    display: inline-block;
    min-inline-size: 18px;
    padding: 2px 6px;
    border: 1px solid var(--tv-rule-strong);
    border-radius: 4px;
    background: var(--tv-sub);
    font-family: var(--tv-font);
    font-size: 12px;
    line-height: 1.4;
    text-align: center;
    color: var(--tv-ink-2);
}
</style>
