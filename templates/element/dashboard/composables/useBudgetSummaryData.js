import { ref, readonly, computed, onMounted } from 'vue'
import { useApi } from './useApi'

const data = ref({})
const loading = ref(true)
const error = ref(null)
const filters = ref({ proj_id: '' })
let initialized = false

export function useBudgetSummaryData() {
  const { api } = useApi()
  const config = window.DASHBOARD_CONFIG || {}

  async function fetchData() {
    loading.value = true
    error.value = null
    try {
      const res = await api.post('my-dashboards/budget-summary', filters.value)
      if (res.data?.success) data.value = res.data.data
    } catch (e) { error.value = e.message }
    finally { loading.value = false }
  }

  if (!initialized) { initialized = true; onMounted(fetchData) }

  const cur = computed(() => data.value.currencySymbol || '$')
  const fmt = (v) => Number(v || 0).toLocaleString()
  const pct = (val, bar) => {
    const max = Math.max(bar.budget || 0, bar.cost || 0, 1)
    return Math.round(((val || 0) / max) * 100)
  }

  return {
    data: readonly(data),
    loading: readonly(loading),
    error: readonly(error),
    filters,
    fetchData,
    cur,
    fmt,
    pct,
    projectList: config.filters?.project_list || {},
  }
}
