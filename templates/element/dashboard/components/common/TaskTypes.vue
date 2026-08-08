<template>
  <WidgetCard :title="$t('Task Types')" :loading="loading" :error="error">
    <div v-if="items.length">
      <div v-for="item in items" :key="item.typeId" style="display:flex; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5;">
        <span style="flex:1; font-size:14px; color:#444;">{{ item.name }}</span>
        <span style="font-size:14px; font-weight:600; color:#333;">{{ item.count }}</span>
      </div>
    </div>
    <EmptyState v-else :message="$t('No task type data')" />
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
    const res = await api.get('my-dashboards/task-types')
    if (res.data?.success) items.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
