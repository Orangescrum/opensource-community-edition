<template>
  <div class="db-card">
    <div v-if="title" class="db-card__header">
      <span class="db-drag-handle" :title="$t('Drag to reorder')">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><circle cx="5" cy="3" r="1.5"/><circle cx="11" cy="3" r="1.5"/><circle cx="5" cy="8" r="1.5"/><circle cx="11" cy="8" r="1.5"/><circle cx="5" cy="13" r="1.5"/><circle cx="11" cy="13" r="1.5"/></svg>
      </span>
      <div class="db-card__title">{{ title }}</div>
      <div v-if="$slots.actions" class="db-card__filters">
        <slot name="actions" />
      </div>
    </div>
    <template v-if="loading">
      <div class="db-card__skeleton">
        <div class="db-skeleton" style="width:80%;"></div>
        <div class="db-skeleton" style="width:60%;"></div>
        <div class="db-skeleton" style="width:90%;"></div>
        <div class="db-skeleton" style="width:45%;"></div>
        <div class="db-skeleton" style="width:70%;"></div>
      </div>
    </template>
    <template v-else-if="error">
      <div style="color:#E74C3C; font-size:13px;">{{ error }}</div>
    </template>
    <template v-else>
      <slot />
    </template>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
</script>

<style scoped>
.db-card__skeleton {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 20px 0;
  min-height: 240px;
  justify-content: center;
}
</style>
