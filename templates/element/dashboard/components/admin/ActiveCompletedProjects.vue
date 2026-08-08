<template>
  <WidgetCard :title="$t('Active / Completed')" :loading="loading" :error="error">
    <div v-if="data.total" style="text-align:center;">
      <div style="position:relative; width:130px; height:130px; margin:10px auto;">
        <svg viewBox="0 0 36 36" style="width:130px; height:130px;">
          <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none" stroke="#eee" stroke-width="2.5" />
          <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none" stroke="#2AD36C" stroke-width="2.5" stroke-linecap="round"
            :stroke-dasharray="completedPct + ', 100'" />
        </svg>
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1;">
          <div style="font-size:24px; font-weight:600; color:#71718E;">{{ data.total }}</div>
          <div style="font-size:12px; color:#71718E;">{{ $t('Total') }}</div>
        </div>
      </div>
      <div style="display:flex; justify-content:center; gap:20px; font-size:13px; margin-top:8px;">
        <span style="color:#3498DB;">{{ data.active }} {{ $t('Active') }}</span>
        <span style="color:#2AD36C;">{{ data.completed }} {{ $t('Completed') }}</span>
      </div>
    </div>
    <EmptyState v-else :message="$t('No project data')" />
  </WidgetCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { api } = useApi()
const loading = ref(true)
const error = ref(null)
const data = ref({})
const completedPct = computed(() => data.value.total ? Math.round((data.value.completed / data.value.total) * 100) : 0)

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/project-status')
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
