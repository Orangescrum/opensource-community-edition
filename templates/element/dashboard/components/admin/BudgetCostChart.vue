<template>
  <WidgetCard :title="$t('Budget vs Cost')" :loading="loading">
    <div v-if="data.chart?.length" style="text-align:center; padding:20px 0;">
      <div v-for="(bar, i) in data.chart" :key="i" style="display:flex; align-items:center; margin:6px 0; font-size:13px;">
        <span style="width:80px; text-align:right; padding-right:8px; color:#555;">{{ bar.name || bar.project_name || '-' }}</span>
        <div style="flex:1; display:flex; gap:2px; height:20px;">
          <div :style="{ width: pct(bar.budget, bar) + '%', background:'#6570FD', borderRadius:'3px 0 0 3px', height:'100%' }" :title="$t('Budget') + ': ' + cur + fmt(bar.budget)"></div>
          <div :style="{ width: pct(bar.cost, bar) + '%', background:'#2AD36C', borderRadius:'0 3px 3px 0', height:'100%' }" :title="$t('Cost') + ': ' + cur + fmt(bar.cost)"></div>
        </div>
      </div>
      <div style="display:flex; justify-content:center; gap:20px; margin-top:12px; font-size:13px;">
        <span><span style="display:inline-block; width:10px; height:10px; background:#6570FD; border-radius:2px; margin-right:4px;"></span>{{ $t('Budget') }}</span>
        <span><span style="display:inline-block; width:10px; height:10px; background:#2AD36C; border-radius:2px; margin-right:4px;"></span>{{ $t('Cost') }}</span>
      </div>
    </div>
    <EmptyState v-else :message="$t('No chart data')" />
  </WidgetCard>
</template>

<script setup>
import { useBudgetSummaryData } from '../../composables/useBudgetSummaryData'
import WidgetCard from '../shared/WidgetCard.vue'
import EmptyState from '../shared/EmptyState.vue'

const { data, loading, cur, fmt, pct } = useBudgetSummaryData()
</script>
