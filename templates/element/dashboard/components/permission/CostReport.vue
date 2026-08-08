<template>
  <WidgetCard :title="$t('Project Cost Report')" :loading="loading" :error="error">
    <template #actions>
      <DbSelect v-model="filters.proj_id" :options="projectList" @update:modelValue="fetchData" />
    </template>
    <div v-if="data.list?.length" class="db-table-scroll db-table-scroll--compact">
      <table class="db-table">
        <thead>
          <tr>
            <th>{{ $t('Project') }}</th><th>{{ $t('Budget') }}</th><th>{{ $t('Cost to Client') }}</th><th>{{ $t('Cost to Company') }}</th><th>{{ $t('Profit') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in data.list" :key="i">
            <td>{{ item.project_name || item.name || '-' }}</td>
            <td>{{ cur }}{{ fmt(item.budget) }}</td>
            <td>{{ cur }}{{ fmt(item.cost_to_client) }}</td>
            <td>{{ cur }}{{ fmt(item.cost) }}</td>
            <td :style="{ color: (item.profit || 0) >= 0 ? '#2AD36C' : '#E84C85' }">{{ cur }}{{ fmt(item.profit) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <EmptyState v-else :message="$t('No cost data available')" />
  </WidgetCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'
import DbSelect from '../shared/DbSelect.vue'

const { api } = useApi()
const config = window.DASHBOARD_CONFIG || {}
const projectList = config.filters?.project_list || {}
const loading = ref(true)
const error = ref(null)
const data = ref({})
const filters = ref({ proj_id: '' })
const cur = computed(() => data.value.currencySymbol || '$')
const fmt = (v) => Number(v || 0).toLocaleString()

async function fetchData() {
  loading.value = true
  try {
    const res = await api.post('my-dashboards/budget-summary', filters.value)
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
