<template>
  <WidgetCard :title="$t('Task List')" :loading="loading" :error="error">
    <div class="db-tabs">
      <button :class="['db-tabs__btn', tab === 'overdue' && 'db-tabs__btn--active']" @click="tab = 'overdue'">
        {{ $t('Overdue') }} ({{ data.overdueCount || 0 }})
      </button>
      <button :class="['db-tabs__btn', tab === 'upcoming' && 'db-tabs__btn--active']" @click="tab = 'upcoming'">
        {{ $t('Upcoming') }} ({{ data.upcomingCount || 0 }})
      </button>
    </div>
    <div v-if="currentTasks.length" class="db-scroll">
      <div v-for="task in currentTasks" :key="task.id" class="db-list-item">
        <div class="db-list-item__title">
          <span v-if="tab === 'overdue'" class="db-due-tag">{{ $t('Overdue') }}</span>
          <a :href="taskHref(task.uniqId)" class="db-link">{{ task.title }}</a>
        </div>
        <div class="db-list-item__meta">
          <a :href="projectHref(task.projectUniqId)" class="db-link--muted" @click.prevent="goToProject(task.projectUniqId)">{{ task.project }}</a>
          &middot; #{{ task.caseNo }}
          <span v-if="task.dueDate"> &middot; {{ formatDate(task.dueDate) }}</span>
        </div>
      </div>
    </div>
    <EmptyState v-else :message="tab === 'overdue' ? $t('No overdue tasks') : $t('No upcoming tasks')" />
  </WidgetCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { useFormatters } from '../../composables/useFormatters'
import { useNavigation } from '../../composables/useNavigation'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { formatDate } = useFormatters()
const { taskHref, goToProject, projectHref } = useNavigation()

const { api } = useApi()
const loading = ref(true)
const error = ref(null)
const data = ref({})
const tab = ref('overdue')
const currentTasks = computed(() => tab.value === 'overdue' ? (data.value.overdue || []) : (data.value.upcoming || []))

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/task-list')
    if (res.data?.success) data.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
