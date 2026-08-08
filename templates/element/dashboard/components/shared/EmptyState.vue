<template>
  <div class="db-empty">{{ message }}</div>
</template>

<script setup>
import { t } from '../../composables/useI18n'

defineProps({
  message: { type: String, default: t('No data available') },
})
</script>
