import axios from 'axios'

const config = window.DASHBOARD_CONFIG || {}

const api = axios.create({
  baseURL: config.baseUrl || '/',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-Token': config.csrfToken || '',
  },
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    return Promise.reject(error)
  }
)

export function useApi() {
  return { api }
}
