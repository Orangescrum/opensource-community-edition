import { markRaw } from 'vue'
import KpiCards from '../components/kpi/KpiCards.vue'
import ProjectSummaryTable from '../components/admin/ProjectSummaryTable.vue'
import ProjectSummaryProgress from '../components/admin/ProjectSummaryProgress.vue'
import MilestoneSummaryTable from '../components/admin/MilestoneSummaryTable.vue'
import MilestoneSummaryProgress from '../components/admin/MilestoneSummaryProgress.vue'
import BudgetCostTable from '../components/admin/BudgetCostTable.vue'
import BudgetCostChart from '../components/admin/BudgetCostChart.vue'
import TopFiveProjects from '../components/admin/TopFiveProjects.vue'
import ActiveCompletedProjects from '../components/admin/ActiveCompletedProjects.vue'
import SpentHour from '../components/member/SpentHour.vue'
import MyTasks from '../components/member/MyTasks.vue'
import MyOverdue from '../components/member/MyOverdue.vue'
import MyProgress from '../components/member/MyProgress.vue'
import CostReport from '../components/permission/CostReport.vue'
import ResourceCostReport from '../components/permission/ResourceCostReport.vue'
import ResourceUtilization from '../components/permission/ResourceUtilization.vue'
import TimeLogChart from '../components/common/TimeLogChart.vue'
import TaskList from '../components/common/TaskList.vue'
import TaskStatus from '../components/common/TaskStatus.vue'
import TaskTypes from '../components/common/TaskTypes.vue'
import ActivityFeed from '../components/common/ActivityFeed.vue'
import BookmarksList from '../components/common/BookmarksList.vue'
import StorageUsage from '../components/common/StorageUsage.vue'
import StorageByProject from '../components/common/StorageByProject.vue'

/**
 * Widget types — each maps to a CSS Grid column span (12-column grid).
 *
 *   kpi   → 12 cols  (special compact row)
 *   table → 8 cols   (two-thirds)
 *   chart → 4 cols   (one-third)
 *   stat  → 6 cols   (half)
 *   full  → 12 cols  (full width)
 */
export const GRID_SPANS = {
  kpi: 12,
  table: 8,
  chart: 4,
  stat: 6,
  full: 12,
}

/**
 * Flat widget registry — each widget is an independent draggable card.
 * No row grouping. The CSS Grid handles layout via column spans.
 *
 * id:        unique key (matches visibility + order + localStorage)
 * component: Vue component (markRaw)
 * type:      layout type → grid span
 * canShow:   function(permissions) => boolean — RBAC gate
 */
export function buildWidgets(can) {
  return [
    { id: 'kpiCards', component: markRaw(KpiCards), type: 'kpi', canShow: () => true },

    // Project
    { id: 'projectSummary', component: markRaw(ProjectSummaryTable), type: 'table', canShow: () => can.canSeeProjectSummary },
    { id: 'projectSummaryProgress', component: markRaw(ProjectSummaryProgress), type: 'chart', canShow: () => can.canSeeProjectSummary },

    // Milestone
    { id: 'milestoneSummary', component: markRaw(MilestoneSummaryTable), type: 'table', canShow: () => can.canSeeMilestoneSummary },
    { id: 'milestoneSummaryProgress', component: markRaw(MilestoneSummaryProgress), type: 'chart', canShow: () => can.canSeeMilestoneSummary },

    // Workload

    // Budget
    { id: 'budgetCostReport', component: markRaw(BudgetCostTable), type: 'table', canShow: () => can.canSeeBudgetCost },
    { id: 'budgetCostChart', component: markRaw(BudgetCostChart), type: 'chart', canShow: () => can.canSeeBudgetCost },

    // Admin charts
    { id: 'topFiveProjects', component: markRaw(TopFiveProjects), type: 'stat', canShow: () => can.canSeeTopProjects },
    { id: 'activeCompleted', component: markRaw(ActiveCompletedProjects), type: 'stat', canShow: () => can.canSeeActiveCompleted },

    // Member
    { id: 'spentHour', component: markRaw(SpentHour), type: 'stat', canShow: () => !can.isAdmin },
    { id: 'myProgress', component: markRaw(MyProgress), type: 'stat', canShow: () => !can.isAdmin },
    { id: 'myTasks', component: markRaw(MyTasks), type: 'stat', canShow: () => !can.isAdmin },
    { id: 'myOverdue', component: markRaw(MyOverdue), type: 'stat', canShow: () => !can.isAdmin },

    // Permission-gated
    { id: 'costReport', component: markRaw(CostReport), type: 'full', canShow: () => can.canSeeCostReport },
    { id: 'resourceCostReport', component: markRaw(ResourceCostReport), type: 'full', canShow: () => can.canSeeResourceCost },

    // Common
    { id: 'taskList', component: markRaw(TaskList), type: 'table', canShow: () => true },
    { id: 'timeLog', component: markRaw(TimeLogChart), type: 'chart', canShow: () => true },
    { id: 'resourceUtilization', component: markRaw(ResourceUtilization), type: 'chart', canShow: () => can.canSeeResourceUtil },
    { id: 'taskStatus', component: markRaw(TaskStatus), type: 'chart', canShow: () => true },
    { id: 'taskTypes', component: markRaw(TaskTypes), type: 'chart', canShow: () => true },
    { id: 'activityFeed', component: markRaw(ActivityFeed), type: 'chart', canShow: () => true },
    { id: 'bookmarks', component: markRaw(BookmarksList), type: can.isAdmin ? 'full' : 'chart', canShow: () => can.canSeeBookmarks },

    // Storage usage (owner + admin only). 4 cols (1/3) + 8 cols (2/3) = 12.
    { id: 'storageUsage', component: markRaw(StorageUsage), type: 'chart', canShow: () => can.canSeeStorage },
    { id: 'storageByProject', component: markRaw(StorageByProject), type: 'table', canShow: () => can.canSeeStorage },
  ]
}

/**
 * Per-role default layouts — packs cards into clean grid rows.
 * Each row comment shows the column math (must sum to 12).
 */
const ADMIN_ORDER = [
  'kpiCards',                                              // 12
  'projectSummary', 'projectSummaryProgress',              // 8+4
  'milestoneSummary', 'milestoneSummaryProgress',          // 8+4
  'topFiveProjects', 'activeCompleted',                    // 6+6
  'taskList', 'timeLog',                                   // 8+4
  'taskStatus', 'taskTypes', 'activityFeed',               // 4+4+4
  'storageUsage', 'storageByProject',                      // 4+8 (bottom)
]

const MEMBER_ORDER = [
  'kpiCards',                                              // 12
  'spentHour', 'myProgress',                               // 6+6
  'myTasks', 'myOverdue',                                  // 6+6
  'taskStatus', 'taskTypes', 'activityFeed',               // 4+4+4
  'taskList', 'timeLog',                                   // 8+4
]

/**
 * Get the default order for the current user's role.
 * Falls back to admin order (superset of all widgets).
 */
export function getDefaultOrder() {
  const config = window.DASHBOARD_CONFIG || {}
  const p = config.permissions || {}

  const base = p.isAdmin ? ADMIN_ORDER : MEMBER_ORDER

  // Merge: use role-specific order, append any widgets not listed (future-proofing)
  const allIds = ADMIN_ORDER // superset
  const missing = allIds.filter(id => !base.includes(id))
  return [...base, ...missing]
}

/** Flat superset for backwards compat */
export const DEFAULT_ORDER = ADMIN_ORDER
