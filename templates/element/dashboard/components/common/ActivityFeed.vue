<template>
  <WidgetCard :title="$t('Activity Feed')" :loading="loading" :error="error">
    <div v-if="activities.length" class="db-scroll">
      <div v-for="act in activities" :key="act.id" class="db-list-item">
        <div class="db-list-item__title">
          <a v-if="act.uniqId" :href="taskHref(act.uniqId)" class="db-link">{{ act.title || $t('View task') }}</a>
          <template v-else>{{ act.title }}</template>
        </div>
        <div class="db-list-item__meta">
          <strong style="font-weight:600; padding-right:4px;">{{ act.user }}</strong>
          &middot; <a :href="projectHref(act.projectUniqId)" class="db-link--muted" @click.prevent="goToProject(act.projectUniqId)">{{ act.project }}</a>
          &middot; #{{ act.caseNo }}
          <span v-if="act.date"> &middot; {{ timeAgo(act.date) }}</span>
        </div>
      </div>
    </div>
    <EmptyState v-else :message="$t('No recent activity')" />
  </WidgetCard>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { useFormatters } from '../../composables/useFormatters'
import { useNavigation } from '../../composables/useNavigation'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { api } = useApi()
const { timeAgo } = useFormatters()
const { taskHref, goToProject, projectHref } = useNavigation()
const loading = ref(true)
const error = ref(null)
const activities = ref([])

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/activities')
    if (res.data?.success) activities.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
