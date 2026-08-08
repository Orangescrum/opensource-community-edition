<template>
  <WidgetCard :title="$t('My Progress')" :loading="loading" :error="error">
    <div v-if="data.assigned !== undefined" style="text-align:center;">
      <div style="position:relative; width:110px; height:110px; margin:10px auto;">
        <svg viewBox="0 0 36 36" style="width:110px; height:110px;">
          <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none" stroke="#eee" stroke-width="2.5" />
          <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            fill="none" stroke="#2AD36C" stroke-width="2.5" stroke-linecap="round"
            :stroke-dasharray="data.progress + ', 100'" />
        </svg>
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1;">
          <div style="font-size:22px; font-weight:600; color:#444;">{{ data.progress }}%</div>
        </div>
      </div>
      <div class="db-stats" style="margin-top:15px;">
        <div class="db-stats__item"><strong>{{ data.assigned }}</strong>{{ $t('Assigned') }}</div>
        <div class="db-stats__item db-stats__item--complete"><strong>{{ data.completed }}</strong>{{ $t('Done') }}</div>
        <div class="db-stats__item db-stats__item--risk"><strong>{{ data.overdue }}</strong>{{ $t('Overdue') }}</div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No progress data')" />
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
    const res = await api.get('my-dashboards/my-progress')
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
