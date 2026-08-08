/**
 * Dashboard permissions — pass-through from server.
 *
 * All permission logic lives in DashboardController::index().
 * The Vue layer just reads the pre-computed flags.
 * This ensures a visible widget will never get a 403 from its API endpoint.
 */
export function usePermissions() {
  const config = window.DASHBOARD_CONFIG || {}
  const p = config.permissions || {}

  return {
    isAdmin: !!p.isAdmin,

    // Widget visibility — computed server-side (RBAC + subscription)
    canSeeKpiCards:         p.canSeeKpiCards !== false,
    canSeeProjectSummary:   !!p.canSeeProjectSummary,
    canSeeMilestoneSummary: !!p.canSeeMilestoneSummary,
    canSeeWorkload:         !!p.canSeeWorkload,
    canSeeBudgetCost:       !!p.canSeeBudgetCost,
    canSeeTopProjects:      !!p.canSeeTopProjects,
    canSeeActiveCompleted:  !!p.canSeeActiveCompleted,
    canSeeClients:          !!p.canSeeClients,
    canSeeSpentHour:        !!p.canSeeSpentHour,
    canSeeMyTasks:          !!p.canSeeMyTasks,
    canSeeMyOverdue:        !!p.canSeeMyOverdue,
    canSeeMyProgress:       !!p.canSeeMyProgress,
    canSeeCostReport:       !!p.canSeeCostReport,
    canSeeResourceCost:     !!p.canSeeResourceCost,
    canSeeResourceUtil:     !!p.canSeeResourceUtil,
    canSeeTimelog:          p.canSeeTimelog !== false,
    canSeeTaskList:         p.canSeeTaskList !== false,
    canSeeTaskStatus:       p.canSeeTaskStatus !== false,
    canSeeTaskTypes:        p.canSeeTaskTypes !== false,
    canSeeActivity:         p.canSeeActivity !== false,
    canSeeBookmarks:        !!p.canSeeBookmarks,
    canSeeStorage:          !!p.canSeeStorage,

    // Data scoping
    viewAllTasks:   !!p.viewAllTasks,
    viewAllTimelog: !!p.viewAllTimelog,
    viewAllResource:!!p.viewAllResource,
  }
}
