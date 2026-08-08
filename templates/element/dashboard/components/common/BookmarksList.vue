<template>
  <WidgetCard :title="$t('Bookmarks')" :loading="loading" :error="error">
    <div v-if="bookmarks.length" class="db-scroll">
      <div v-for="bm in bookmarks" :key="bm.id" class="db-list-item">
        <a :href="bm.url || '#'" class="db-list-item__title" style="color:#292940; text-decoration:none; display:block;">
          {{ bm.title || bm.name || $t('Untitled') }}
        </a>
      </div>
    </div>
    <EmptyState v-else :message="$t('No bookmarks saved')" />
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
const bookmarks = ref([])

onMounted(async () => {
  try {
    const res = await api.get('my-dashboards/bookmarks')
    if (res.data?.success) bookmarks.value = res.data.data
  } catch (e) { error.value = e.message }
  finally { loading.value = false }
})
</script>
