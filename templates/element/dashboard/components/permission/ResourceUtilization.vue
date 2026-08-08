<template>
  <WidgetCard :title="$t('Resource Utilization')" :loading="loading" :error="error">
    <div v-if="data.categories?.length" class="db-scroll">
      <div v-for="(user, i) in data.categories" :key="i" style="display:flex; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5;">
        <span style="width:100px; font-size:13px; color:#444; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ user }}</span>
        <div class="db-progress" style="flex:1;">
          <div class="db-progress__bar">
            <div class="db-progress__fill" :style="{ width: utilPct(i) + '%', background: '#3498DB' }"></div>
          </div>
          <div class="db-progress__text">{{ utilPct(i) }}%</div>
        </div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No resource data')" />
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

const utilPct = (i) => {
  const total = data.value.total_hours?.[i] || 0
  const booked = data.value.data?.[3]?.[i] || 0
  return total > 0 ? Math.round((booked / total) * 100) : 0
}

onMounted(async () => {
  try {
    const res = await api.post('my-dashboards/workload-summary', {})
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
