<template>
  <WidgetCard :title="$t('Project Summary')" :loading="loading" :error="error">
    <template #actions>
      <DbSelect v-model="filters.prog_id" :options="programList" @update:modelValue="fetchData" />
      <DbSelect v-model="filters.proj_id" :options="projectList" @update:modelValue="fetchData" />
      <DbSelect v-model="filters.manager_id" :options="mgrList" @update:modelValue="fetchData" />
      <DbSelect v-model="filters.status" :options="statusList" @update:modelValue="fetchData" />
    </template>
    <div v-if="items.length" class="db-table-scroll">
      <table class="db-table">
        <thead><tr><th>{{ $t('Project Name') }}</th><th>{{ $t('Project Manager') }}</th><th>{{ $t('Due Date') }}</th><th>{{ $t('Status') }}</th><th>{{ $t('Progress') }}</th></tr></thead>
        <tbody>
          <tr v-for="item in items" :key="item.uniqId || item.name">
            <td><a :href="projectHref(item.uniqId)" class="db-link" @click.prevent="goToProject(item.uniqId)">{{ item.name }}</a></td>
            <td>{{ item.owner ?? '-' }}</td>
            <td>{{ formatDate(item.dueDate) }}</td>
            <td><span class="db-status" :class="'db-status--' + statusClass(item.status)">{{ item.status }}</span></td>
            <td>
              <div class="db-progress">
                <div class="db-progress__bar"><div class="db-progress__fill" :style="{ width: item.progress + '%', background: progressColor(item.progress) }"></div></div>
                <div class="db-progress__text">{{ item.progress }}%</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <EmptyState v-else :message="$t('No summary available.')" />
  </WidgetCard>
</template>

<script setup>
import { computed } from 'vue'
import { useProjectSummaryData } from '../../composables/useProjectSummaryData'
import { useFormatters } from '../../composables/useFormatters'
import { useNavigation } from '../../composables/useNavigation'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'
import DbSelect from '../shared/DbSelect.vue'

const { data, loading, error, filters, fetchData, programList, projectList, mgrList, statusList } = useProjectSummaryData()
const { formatDate, statusClass, progressColor } = useFormatters()
const { goToProject, projectHref } = useNavigation()
const items = computed(() => data.value.list || [])
</script>
