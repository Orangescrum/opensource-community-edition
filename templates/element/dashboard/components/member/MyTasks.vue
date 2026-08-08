<template>
  <WidgetCard :title="$t('My Tasks')" :loading="loading" :error="error">
    <div v-if="tasks.length" class="db-scroll">
      <div v-for="task in tasks" :key="task.id" class="db-list-item">
        <div class="db-list-item__title">{{ task.title }}</div>
        <div class="db-list-item__meta">{{ task.project }} &middot; #{{ task.caseNo }}</div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No assigned tasks')" />
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
const tasks = ref([])

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/my-task-list')
    if (res.data?.success) tasks.value = res.data.data.upcoming || []
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
