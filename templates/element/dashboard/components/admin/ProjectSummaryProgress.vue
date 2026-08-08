<template>
  <WidgetCard :title="$t('Project Overall Progress')" :loading="loading">
    <div style="text-align:center; margin:15px 0;">
      <svg viewBox="0 0 36 36" style="width:130px; height:130px;">
        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="2.5" />
        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#2AD36C" stroke-width="2.5" stroke-linecap="round" :stroke-dasharray="pct + ', 100'" />
      </svg>
      <div style="margin-top:-85px; position:relative; z-index:1;">
        <div style="font-size:28px; font-weight:600; color:#71718E;">{{ graph.completed || 0 }}</div>
        <div style="font-size:14px; color:#71718E; font-weight:600;">{{ $t('Completed') }}</div>
      </div>
      <div style="height:45px;"></div>
    </div>
    <div class="db-stats">
      <div class="db-stats__item db-stats__item--total"><strong>{{ graph.total || 0 }}</strong>{{ $t('Total Projects') }}</div>
      <div class="db-stats__item db-stats__item--complete"><strong>{{ graph.completed || 0 }}</strong>{{ $t('Completed') }}</div>
      <div class="db-stats__item db-stats__item--ontrack"><strong>{{ graph.onTrack || 0 }}</strong>{{ $t('On Track') }}</div>
      <div class="db-stats__item db-stats__item--delay"><strong>{{ graph.delayed || 0 }}</strong>{{ $t('Delayed') }}</div>
      <div class="db-stats__item db-stats__item--risk"><strong>{{ graph.atRisk || 0 }}</strong>{{ $t('At Risk') }}</div>
    </div>
  </WidgetCard>
</template>

<script setup>
import { computed } from 'vue'
import { useProjectSummaryData } from '../../composables/useProjectSummaryData'
import { useFormatters } from '../../composables/useFormatters'
import WidgetCard from '../shared/WidgetCard.vue'

const { data, loading } = useProjectSummaryData()
const { completedPct } = useFormatters()
const graph = computed(() => data.value.graph || {})
const pct = computed(() => completedPct(graph.value))
</script>
