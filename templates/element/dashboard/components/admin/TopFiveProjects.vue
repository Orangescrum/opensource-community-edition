<template>
  <WidgetCard :title="$t('Top Five Projects')" :loading="loading" :error="error">
    <div v-if="projects.length">
      <div v-for="proj in projects" :key="proj.id" style="display:flex; align-items:center; padding:10px 0; border-bottom:1px solid #f5f5f5;">
        <a :href="projectHref(proj.uniqId)" class="db-link" style="flex:0 0 35%; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; padding-right:10px;" @click.prevent="goToProject(proj.uniqId)">{{ proj.name }}</a>
        <div class="db-progress" style="flex:1;">
          <div class="db-progress__bar" style="height:10px;">
            <div class="db-progress__fill" :style="{ width: barWidth(proj.taskCount) + '%', background: 'var(--primary, #6366f1)' }"></div>
          </div>
          <div class="db-progress__text">{{ proj.taskCount }}</div>
        </div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No projects found')" />
  </WidgetCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { useNavigation } from '../../composables/useNavigation'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { api } = useApi()
const { goToProject, projectHref } = useNavigation()
const loading = ref(true)
const error = ref(null)
const projects = ref([])
const maxTasks = computed(() => Math.max(...projects.value.map(p => p.taskCount), 1))
const barWidth = (count) => (count / maxTasks.value) * 100

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/top-projects')
    if (res.data?.success) projects.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
