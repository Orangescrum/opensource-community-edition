<template>
  <WidgetCard :title="$t('Time Log')" :loading="loading" :error="error">
    <template #actions>
      <DbSelect v-model="period" :options="periodOptions" :searchable="false" @update:modelValue="fetchData" />
    </template>
    <div v-if="chartData.labels?.length">
      <div style="display:flex; align-items:flex-end; gap:4px; height:140px; padding-top:10px;">
        <div v-for="(val, i) in (chartData.values || [])" :key="i"
          style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;">
          <div :style="{ height: barHeight(val) + 'px', background:'var(--primary, #6366f1)', borderRadius:'3px 3px 0 0', width:'100%', minHeight: val > 0 ? '3px' : '1px', opacity: val > 0 ? 1 : 0.3 }"></div>
          <span style="font-size:10px; color:#888; margin-top:4px; white-space:nowrap;">{{ fmtDate(chartData.labels[i]) }}</span>
        </div>
      </div>
      <div style="text-align:center; font-size:13px; color:#71718E; margin-top:10px; font-weight:500;">
        {{ $t('Total:') }} {{ (chartData.values || []).reduce((a, b) => a + b, 0).toFixed(1) }} {{ $t('hours') }}
      </div>
    </div>
    <EmptyState v-else :message="$t('No time log data for this period')" />
  </WidgetCard>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { t } from '../../composables/useI18n'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'
import DbSelect from '../shared/DbSelect.vue'

const periodOptions = {
  thisweekfull: t('This Week'),
  lastweek: t('Last Week'),
  thismonthfull: t('This Month'),
  lastmonth: t('Last Month'),
}

const { api } = useApi()
const loading = ref(true)
const error = ref(null)
const chartData = ref({})
const period = ref('thisweekfull')

const maxVal = () => Math.max(...(chartData.value.values || [1]), 1)
const barHeight = (val) => Math.max(Math.round((val / maxVal()) * 110), val > 0 ? 4 : 1)
const fmtDate = (d) => { const dt = new Date(d); return ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][dt.getDay()] || (dt.getMonth()+1) + '/' + dt.getDate() }

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('my-dashboards/time-log', { params: { period: period.value } })
    if (res.data?.success) chartData.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
