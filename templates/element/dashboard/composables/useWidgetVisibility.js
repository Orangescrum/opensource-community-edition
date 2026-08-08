import { reactive, watch } from 'vue'
import { t } from './useI18n'

const config = window.DASHBOARD_CONFIG || {}
const STORAGE_KEY = `os_dashboard_widgets_${config.companyId || 0}_${config.userId || 0}`

const defaultVisibility = {
  kpiCards: true,
  projectSummary: true,
  projectSummaryProgress: true,
  milestoneSummary: true,
  milestoneSummaryProgress: true,
  budgetCostReport: true,
  budgetCostChart: true,
  topFiveProjects: true,
  activeCompleted: true,
  spentHour: true,
  myTasks: true,
  myOverdue: true,
  myProgress: true,
  costReport: true,
  resourceCostReport: true,
  resourceUtilization: true,
  timeLog: true,
  taskList: true,
  taskStatus: true,
  taskTypes: true,
  activityFeed: true,
  bookmarks: true,
  storageUsage: true,
  storageByProject: true,
}

function loadSaved() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
      return { ...defaultVisibility, ...JSON.parse(saved) }
    }
  } catch (e) {
    // ignore
  }
  return { ...defaultVisibility }
}

const visibility = reactive(loadSaved())

watch(visibility, (val) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...val }))
}, { deep: true })

export function useWidgetVisibility() {
  function toggle(key) {
    visibility[key] = !visibility[key]
  }

  function resetAll() {
    Object.keys(defaultVisibility).forEach(key => {
      visibility[key] = defaultVisibility[key]
    })
  }

  function selectAll(keys) {
    (keys || Object.keys(defaultVisibility)).forEach(key => {
      visibility[key] = true
    })
  }

  function selectNone(keys) {
    (keys || Object.keys(defaultVisibility)).forEach(key => {
      visibility[key] = false
    })
  }

  return { visibility, toggle, resetAll, selectAll, selectNone }
}

export const widgetLabels = {
  kpiCards: t('Dashboard Overview'),
  projectSummary: t('Project Summary'),
  projectSummaryProgress: t('Project Progress'),
  milestoneSummary: t('Milestone Summary'),
  milestoneSummaryProgress: t('Milestone Progress'),
  budgetCostReport: t('Budget & Cost Report'),
  budgetCostChart: t('Budget vs Cost Chart'),
  topFiveProjects: t('Top Five Projects'),
  activeCompleted: t('Active / Completed'),
  spentHour: t('Spent Hours'),
  myTasks: t('My Tasks'),
  myOverdue: t('My Overdue Tasks'),
  myProgress: t('My Progress'),
  costReport: t('Cost Report'),
  resourceCostReport: t('Resource Cost Report'),
  resourceUtilization: t('Resource Utilization'),
  timeLog: t('Time Log'),
  taskList: t('Task List'),
  taskStatus: t('Task Status'),
  taskTypes: t('Task Types'),
  activityFeed: t('Activity Feed'),
  bookmarks: t('Bookmarks'),
  storageUsage: t('Storage Usage'),
  storageByProject: t('Storage by Project'),
}

/**
 * Maps each widget visKey to the usePermissions() property that gates it.
 * null = always visible (no permission required).
 */
export const widgetPermissionKey = {
  kpiCards: 'canSeeKpiCards',
  projectSummary: 'canSeeProjectSummary',
  projectSummaryProgress: 'canSeeProjectSummary',
  milestoneSummary: 'canSeeMilestoneSummary',
  milestoneSummaryProgress: 'canSeeMilestoneSummary',
  budgetCostReport: 'canSeeBudgetCost',
  budgetCostChart: 'canSeeBudgetCost',
  topFiveProjects: 'canSeeTopProjects',
  activeCompleted: 'canSeeActiveCompleted',
  spentHour: 'canSeeSpentHour',
  myTasks: 'canSeeMyTasks',
  myOverdue: 'canSeeMyOverdue',
  myProgress: 'canSeeMyProgress',
  costReport: 'canSeeCostReport',
  resourceCostReport: 'canSeeResourceCost',
  resourceUtilization: 'canSeeResourceUtil',
  timeLog: 'canSeeTimelog',
  taskList: 'canSeeTaskList',
  taskStatus: 'canSeeTaskStatus',
  taskTypes: 'canSeeTaskTypes',
  activityFeed: 'canSeeActivity',
  bookmarks: 'canSeeBookmarks',
  storageUsage: 'canSeeStorage',
  storageByProject: 'canSeeStorage',
}
