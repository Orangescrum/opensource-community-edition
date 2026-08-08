<template>
  <WidgetCard :title="$t('Task Status')" :loading="loading" :error="error">
    <div v-if="items.length">
      <div v-for="item in items" :key="item.status" style="display:flex; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5;">
        <span :style="{ width:'10px', height:'10px', borderRadius:'50%', background: item.color, marginRight:'10px', flexShrink:0 }"></span>
        <span style="flex:1; font-size:14px; color:#444;">{{ item.status }}</span>
        <span style="font-size:14px; font-weight:600; color:#333;">{{ item.count }}</span>
      </div>
    </div>
    <EmptyState v-else :message="$t('No task data')" />
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
const items = ref([])

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/task-status')
    if (res.data?.success) items.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
