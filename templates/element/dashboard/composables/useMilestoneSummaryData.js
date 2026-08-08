import { ref, readonly, onMounted } from 'vue'
import { useApi } from './useApi'

const data = ref({})
const loading = ref(true)
const error = ref(null)
const filters = ref({ proj_id: '', milestone_id: '', status: '' })
let initialized = false

export function useMilestoneSummaryData() {
  const { api } = useApi()
  const config = window.DASHBOARD_CONFIG || {}

  async function fetchData() {
    loading.value = true
    error.value = null
    try {
      const res = await api.post('my-dashboards/milestone-summary', filters.value)
      if (res.data?.success) data.value = res.data.data
    } catch (e) { error.value = e.message }
    finally { loading.value = false }
  }

  if (!initialized) { initialized = true; onMounted(fetchData) }

  return {
    data: readonly(data),
    loading: readonly(loading),
    error: readonly(error),
    filters,
    fetchData,
    projectList: config.filters?.project_list || {},
    milestoneList: config.filters?.milestone_list || {},
    statusList: config.filters?.project_status_list || {},
  }
}
