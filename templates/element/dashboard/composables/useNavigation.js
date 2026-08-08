const config = window.DASHBOARD_CONFIG || {}
const baseUrl = config.baseUrl || '/'
const csrfToken = config.csrfToken || ''

/**
 * Navigation helpers for the Vue dashboard.
 *
 * Task links: simple hash navigation to /dashboard#/details/{uniqId}
 * Project links: AJAX to /projects/updateDateVisited (sets server context),
 *   then redirect to /dashboard/#overview (matches legacy projectBodyClick flow)
 */
export function useNavigation() {

  /**
   * Build task detail href for <a> tags.
   */
  function taskHref(taskUniqId) {
    return `${baseUrl}dashboard#/details/${taskUniqId}`
  }

  /**
   * Build project href fallback for <a> tags (used with @click.prevent).
   */
  function projectHref(projectUniqId, page = 'overview') {
    return `${baseUrl}dashboard/#${page}`
  }

  /**
   * Navigate to project page. Replicates legacy projectBodyClick:
   * 1. POST /projects/updateDateVisited to set server project context
   * 2. Redirect based on response (methodology determines tasks/backlog/kanban)
   *
   * @param {string} projectUniqId
   * @param {string} page  'overview' | 'tasks' | 'taskgroups'
   */
  async function goToProject(projectUniqId, page = 'overview') {
    try {
      // Use FormData to match legacy jQuery $.post behavior
      const formData = new FormData()
      formData.append('uniq_id', projectUniqId)

      const response = await fetch(`${baseUrl}projects/updateDateVisited`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken,
        },
        body: formData,
      })
      const data = await response.json()

      if (data.status !== 'success') {
        alert('Oops! You are not a member of the project. Please add yourself as a member of this project.')
        return
      }

      // Forced page navigation
      if (page === 'tasks') {
        redirectToTasks(data)
        return
      }
      if (page === 'taskgroups') {
        window.location.href = `${baseUrl}dashboard/#taskgroups`
        return
      }

      // Overview: if no tasks, go to task list instead
      if (data.tsk_cnt !== undefined && !parseInt(data.tsk_cnt)) {
        redirectToTasks(data)
      } else {
        window.location.href = `${baseUrl}dashboard/#overview`
      }
    } catch (e) {
      // navigation error — silently handled
    }
  }

  /**
   * Redirect to task list based on project methodology.
   */
  function redirectToTasks(data) {
    const math = String(data.proj_math || '1')
    if (math === '2') {
      window.location.href = `${baseUrl}dashboard/#backlog`
    } else if (math === '1') {
      window.location.href = `${baseUrl}dashboard/#tasks`
    } else {
      window.location.href = `${baseUrl}dashboard/#kanban`
    }
  }

  return { taskHref, projectHref, goToProject }
}
