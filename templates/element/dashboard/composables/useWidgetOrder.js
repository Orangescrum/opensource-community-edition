import { ref, watch } from 'vue'
import { getDefaultOrder } from '../config/widgetRegistry'

const config = window.DASHBOARD_CONFIG || {}
const STORAGE_KEY = `os_dashboard_order_${config.companyId || 0}_${config.userId || 0}`

const defaultOrder = getDefaultOrder()

function loadSaved() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
      const parsed = JSON.parse(saved)
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Merge: keep saved order, append any new IDs not yet in saved
        const missing = defaultOrder.filter(id => !parsed.includes(id))
        return [...parsed, ...missing]
      }
    }
  } catch (e) {
    // ignore
  }
  return [...defaultOrder]
}

const order = ref(loadSaved())

watch(order, (val) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(val))
}, { deep: true })

export function useWidgetOrder() {
  function updateOrder(newOrder) {
    order.value = newOrder
  }

  function resetOrder() {
    order.value = [...defaultOrder]
  }

  return { order, updateOrder, resetOrder }
}
