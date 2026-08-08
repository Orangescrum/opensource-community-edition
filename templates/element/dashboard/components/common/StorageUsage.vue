<template>
  <WidgetCard :title="$t('Storage')" :loading="loading" :error="error">
    <div v-if="data" style="padding:10px 0;">
      <div style="text-align:center; padding:20px 0;">
        <div style="font-size:28px; font-weight:600; color:var(--primary, #6366f1);">{{ formatMb(data.used_mb) }}</div>
        <div style="font-size:13px; color:#71718E; margin-top:4px;">{{ $t('used across all attachments') }}</div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No storage data')" />
  </WidgetCard>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { api } = useApi()
const loading = ref(true)
const error = ref(null)
const data = ref(null)

const formatMb = (mb) => {
  if (mb == null) return '—'
  if (mb >= 1024) return (mb / 1024).toFixed(2) + ' GB'
  if (mb >= 1) return mb.toFixed(2) + ' MB'
  return (mb * 1024).toFixed(0) + ' KB'
}

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/storage-usage')
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
