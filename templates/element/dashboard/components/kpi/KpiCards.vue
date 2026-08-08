<template>
  <div class="db-kpi">
    <div v-for="card in cards" :key="card.key" class="db-kpi__item">
      <div class="db-card">
        <div class="db-kpi__name">{{ card.label }}</div>
        <div class="db-kpi__count">
          <strong :title="card.primaryTip">{{ card.primary }}</strong>
          <span class="db-kpi__sep">/</span>
          <span :title="card.secondaryTip">{{ card.secondary }}</span>
          <span v-if="card.suffix"> {{ card.suffix }}</span>
        </div>
        <div class="db-kpi__trend" :title="$t('Compared to the prior 30-day period')">
          <span :class="['db-kpi__trend-value', card.trendDir === 'up' ? 'db-kpi__trend--up' : card.trendDir === 'down' ? 'db-kpi__trend--down' : 'db-kpi__trend--flat']">
            {{ card.trendVal }}%
          </span>
          {{ card.trendDir === 'up' ? $t('Increase') : card.trendDir === 'down' ? $t('Decrease') : $t('No change') }} {{ $t('from Last Month') }}
        </div>
        <figure class="db-kpi__icon">
          <img :src="baseUrl + card.icon" :alt="card.label" width="55" height="55" />
        </figure>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../../composables/useApi'
import { t } from '../../composables/useI18n'

const { api } = useApi()
const config = window.DASHBOARD_CONFIG || {}
const baseUrl = config.baseUrl || '/'
const kpi = ref({})

const cards = computed(() => {
  const d = kpi.value
  return [
    {
      key: 'projects', label: t('Projects'),
      primary: d.projects?.completed ?? 0, secondary: d.projects?.total ?? 0, suffix: '',
      primaryTip: t('Completed projects'), secondaryTip: t('Total projects'),
      icon: 'img/tools/Dashboard-projects.png',
      trendDir: d.projects?.trend?.direction || 'flat', trendVal: d.projects?.trend?.percentage ?? 0,
    },
    {
      key: 'tasks', label: t('Tasks'),
      primary: d.tasks?.completed ?? 0, secondary: d.tasks?.total ?? 0, suffix: '',
      primaryTip: t('Closed tasks'), secondaryTip: t('Total active tasks'),
      icon: 'img/tools/Dashboard-tasks.png',
      trendDir: d.tasks?.trend?.direction || 'flat', trendVal: d.tasks?.trend?.percentage ?? 0,
    },
    {
      key: 'resources', label: t('Resources'),
      primary: d.resources?.active ?? 0, secondary: d.resources?.total ?? 0, suffix: '',
      primaryTip: t('Active users in last 30 days'), secondaryTip: t('Total team members'),
      icon: 'img/tools/Dashboard-resources.png',
      trendDir: d.resources?.trend?.direction || 'flat', trendVal: d.resources?.trend?.percentage ?? 0,
    },
    {
      key: 'time', label: t('Time Spent'),
      primary: d.timeSpent?.spent ?? 0, secondary: d.timeSpent?.estimated ?? 0, suffix: t('hours'),
      primaryTip: t('Total hours logged'), secondaryTip: t('Total estimated hours'),
      icon: 'img/tools/Dashboard-time.png',
      trendDir: d.timeSpent?.trend?.direction || 'flat', trendVal: d.timeSpent?.trend?.percentage ?? 0,
    },
  ]
})

onMounted(async () => {
  try {
    const { data } = await api.get('my-dashboards/kpi-counts')
    if (data?.success) kpi.value = data.data
  } catch (e) { /* cards show 0 */ }
})
</script>

<style scoped>
.db-kpi.db-kpi { display:flex; gap:0; margin:20px -10px 20px; }
.db-kpi__item { flex:1; padding:0 10px; }
.db-kpi__item .db-card { position:relative; min-height:130px; padding-right:80px; }
.db-kpi__name { font-size:13px; color:#757575; font-weight:500; margin:0; }
.db-kpi__count { font-size:18px; color:#71718E; margin:10px 0; }
.db-kpi__count strong { font-size:25px; color:#444; font-weight:600; cursor:help; }
.db-kpi__count span[title] { cursor:help; }
.db-kpi__sep { margin:0 2px; }
.db-kpi__trend { font-size:12px; color:#292940; }
.db-kpi__trend-value { font-size:14px; font-weight:500; }
.db-kpi__trend--up { color:#2AD36C; }
.db-kpi__trend--down { color:#E84C85; }
.db-kpi__trend--flat { color:#999; }
.db-kpi__icon { position:absolute; right:20px; top:0; bottom:0; margin:auto; width:55px; height:55px; border-radius:50%; }
</style>
