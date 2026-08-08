<template>
  <WidgetCard :title="$t('Budget & Cost Report')" :loading="loading" :error="error">
    <template #actions>
      <DbSelect v-model="filters.proj_id" :options="projectList" @update:modelValue="fetchData" />
    </template>
    <div v-if="data.list?.length" class="db-table-scroll db-table-scroll--compact">
      <table class="db-table">
        <thead>
          <tr>
            <th>{{ $t('Project Name') }}</th>
            <th>{{ $t('Manager') }}</th>
            <th>{{ $t('Budget') }}</th>
            <th>{{ $t('Cost to Client') }}</th>
            <th>{{ $t('Cost to Company') }}</th>
            <th>{{ $t('Profit') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in data.list" :key="i">
            <td>{{ item.project_name || item.name || '-' }}</td>
            <td>{{ item.manager || '-' }}</td>
            <td>{{ cur }}{{ fmt(item.budget) }}</td>
            <td>{{ cur }}{{ fmt(item.cost_to_client) }}</td>
            <td>{{ cur }}{{ fmt(item.cost) }}</td>
            <td :style="{ color: (item.profit || 0) >= 0 ? '#2AD36C' : '#E84C85' }">{{ cur }}{{ fmt(item.profit) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <EmptyState v-else :message="$t('No budget data available')" />
  </WidgetCard>
</template>

<script setup>
import { useBudgetSummaryData } from '../../composables/useBudgetSummaryData'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'
import DbSelect from '../shared/DbSelect.vue'

const { data, loading, error, filters, fetchData, cur, fmt, projectList } = useBudgetSummaryData()
</script>
