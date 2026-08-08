<template>
  <WidgetCard :title="$t('Spent Hours')" :loading="loading" :error="error">
    <div v-if="data.thisWeek !== undefined" style="display:flex; gap:30px; padding:15px 0;">
      <div style="text-align:center; flex:1;">
        <div style="font-size:30px; font-weight:600; color:var(--primary, #6366f1);">{{ data.thisWeek }}</div>
        <div style="font-size:13px; color:#71718E; margin-top:4px;">{{ $t('hrs this week') }}</div>
      </div>
      <div style="width:1px; background:#eee;"></div>
      <div style="text-align:center; flex:1;">
        <div style="font-size:30px; font-weight:600; color:#444;">{{ data.total }}</div>
        <div style="font-size:13px; color:#71718E; margin-top:4px;">{{ $t('hrs total') }}</div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No time data')" />
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
const data = ref({})

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/my-hours')
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
