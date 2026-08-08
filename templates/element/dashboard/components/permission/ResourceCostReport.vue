<template>
  <WidgetCard :title="$t('Resource Cost Report')" :loading="loading" :error="error">
    <div v-if="data.list?.length" class="db-table-scroll db-table-scroll--compact">
      <table class="db-table">
        <thead>
          <tr><th>{{ $t('Project') }}</th><th>{{ $t('Budget') }}</th><th>{{ $t('Cost') }}</th></tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in data.list" :key="i">
            <td>{{ item.project_name || item.name || '-' }}</td>
            <td>{{ cur }}{{ fmt(item.budget) }}</td>
            <td>{{ cur }}{{ fmt(item.cost) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <EmptyState v-else :message="$t('No resource cost data')" />
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
const cur = computed(() => data.value.currencySymbol || '$')
const fmt = (v) => Number(v || 0).toLocaleString()

onMounted(async () => {
  try {
    const res = await api.post('my-dashboards/budget-summary', {})
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
