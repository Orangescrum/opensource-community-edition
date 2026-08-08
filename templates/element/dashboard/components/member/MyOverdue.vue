<template>
  <WidgetCard :title="$t('My Overdue Tasks')" :loading="loading" :error="error">
    <div v-if="tasks.length" class="db-scroll">
      <div v-for="task in tasks" :key="task.id" class="db-list-item">
        <div class="db-list-item__title">
          <span class="db-due-tag">{{ $t('Overdue') }}</span>
          {{ task.title }}
        </div>
        <div class="db-list-item__meta">{{ task.project }} &middot; {{ $t('Due:') }} {{ formatDate(task.dueDate) }}</div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No overdue tasks')" />
  </WidgetCard>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { useFormatters } from '../../composables/useFormatters'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { formatDate } = useFormatters()

const { api } = useApi()
const loading = ref(true)
const error = ref(null)
const tasks = ref([])

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/my-task-list')
    if (res.data?.success) tasks.value = res.data.data.overdue || []
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
