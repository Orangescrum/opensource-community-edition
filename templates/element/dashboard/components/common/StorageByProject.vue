<template>
  <WidgetCard :title="$t('Storage by Project')" :loading="loading" :error="error">
    <div v-if="projects.length" class="db-table-scroll">
      <table class="db-table">
        <thead>
          <tr>
            <th>{{ $t('Project') }}</th>
            <th style="text-align:right; width:120px;">{{ $t('Used') }}</th>
            <th style="width:30%;">{{ $t('Share') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in projects" :key="p.project_id">
            <td>
              <a :href="'/projects/dashboard/' + p.project_id" class="db-link">{{ p.project_name }}</a>
            </td>
            <td style="text-align:right;">{{ formatMb(p.used_mb) }}</td>
            <td>
              <div class="db-progress">
                <div class="db-progress__bar">
                  <div class="db-progress__fill" :style="{ width: pctOfMax(p.used_mb) + '%', background: '#6570FD' }"></div>
                </div>
                <div class="db-progress__text">{{ pctOfTotal(p.used_mb) }}%</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <EmptyState v-else :message="$t('No project storage usage yet')" />
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
const projects = ref([])

const totalMb = computed(() => (projects.value || []).reduce((s, p) => s + (p.used_mb || 0), 0))
const maxMb = computed(() => (projects.value || []).reduce((m, p) => Math.max(m, p.used_mb || 0), 0) || 1)

const formatMb = (mb) => {
  if (mb == null) return '—'
  if (mb >= 1024) return (mb / 1024).toFixed(2) + ' GB'
  if (mb >= 1) return mb.toFixed(2) + ' MB'
  return (mb * 1024).toFixed(0) + ' KB'
}

const pctOfMax = (mb) => Math.min(100, Math.round((mb / maxMb.value) * 100))
const pctOfTotal = (mb) => totalMb.value > 0 ? Math.round((mb / totalMb.value) * 100) : 0

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/storage-by-project')
    if (res.data?.success) projects.value = res.data.data?.projects || []
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
