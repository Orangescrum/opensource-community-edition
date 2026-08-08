import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '../composables/useApi'

export const useDashboardStore = defineStore('dashboard', () => {
  const { api } = useApi()
  const config = window.DASHBOARD_CONFIG || {}
  const filters = ref(config.filters || {})
  const loading = ref({})
  const errors = ref({})

  async function fetchWidget(key, url, options = {}) {
    loading.value[key] = true
    errors.value[key] = null
    try {
      const method = options.method || 'get'
      const response = method === 'post'
        ? await api.post(url, options.data || {})
        : await api.get(url, { params: options.params })
      return response.data?.data ?? response.data
    } catch (err) {
      errors.value[key] = err.response?.data?.error || err.message
      return null
    } finally {
      loading.value[key] = false
    }
  }

  function isLoading(key) {
    return !!loading.value[key]
  }

  function getError(key) {
    return errors.value[key] || null
  }

  return { filters, loading, errors, fetchWidget, isLoading, getError }
})
