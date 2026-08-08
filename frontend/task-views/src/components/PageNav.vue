<script setup>
/**
 * Task navigation — the page's ONLY nav bar.
 *
 * Items come from the host page (TASK_VIEWS_CONFIG.nav) so the links keep
 * PHP's role checks, translations and HTTP_ROOT. Rendered with Vuetify's
 * v-tabs for the slider indicator and keyboard support. Deliberately without
 * show-arrows: hiding tabs behind a scroller made whole pages undiscoverable.
 */
const items = window.TASK_VIEWS_CONFIG?.nav ?? [];
const current = window.TASK_VIEWS_CONFIG?.page ?? "views";
</script>

<template>
    <v-tabs
        :model-value="current"
        class="tv-tabs"
        density="comfortable"
        color="primary"
        slider-color="primary"
        aria-label="Task navigation"
    >
        <v-tab
            v-for="item in items"
            :key="item.key"
            :value="item.key"
            :href="item.url"
            class="tv-tabs__tab"
        >
            <v-icon :icon="item.icon" size="17" start aria-hidden="true" />
            {{ item.label }}
        </v-tab>
    </v-tabs>
</template>

<style scoped>
.tv-tabs {
    min-inline-size: 0;
    flex: 1 1 auto;
}

/* Vuetify's defaults are sized for app bars; tighten to a page-level nav. */
.tv-tabs :deep(.v-tab) {
    min-inline-size: 0;
    padding-inline: 12px;
    font-size: var(--tv-size-body);
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
    color: var(--tv-muted);
    opacity: 1;
}

.tv-tabs :deep(.v-tab:hover) {
    color: var(--tv-ink);
}

.tv-tabs :deep(.v-tab--selected) {
    color: var(--tv-ink);
    font-weight: 600;
}

.tv-tabs :deep(.v-tab .v-icon) {
    opacity: 0.85;
}

.tv-tabs :deep(.v-tab__slider) {
    block-size: 2px;
}
</style>
