const config = window.DASHBOARD_CONFIG || {}
const tz = config.tz || {}

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

/**
 * Apply GMT offset to a UTC date string from the DB.
 */
function applyTzOffset(dateStr) {
  if (!dateStr) return null
  const d = new Date(dateStr.replace(' ', 'T') + 'Z') // treat as UTC
  const gmt = tz.gmt || '+00:00'
  const sign = gmt.charAt(0) === '-' ? -1 : 1
  const parts = gmt.replace(/[+-]/, '').split(':')
  const offsetMinutes = sign * ((parseInt(parts[0]) || 0) * 60 + (parseInt(parts[1]) || 0))
  return new Date(d.getTime() + offsetMinutes * 60000)
}

export function useFormatters() {
  /**
   * Format DB datetime to "Apr 10, 2026"
   */
  function formatDate(dateStr) {
    if (!dateStr || dateStr === 'N/A') return 'N/A'
    const d = applyTzOffset(dateStr)
    if (!d || isNaN(d.getTime())) return dateStr
    return `${months[d.getUTCMonth()]} ${d.getUTCDate()}, ${d.getUTCFullYear()}`
  }

  /**
   * Format DB datetime to "Apr 10, 2026 2:30 PM"
   */
  function formatDateTime(dateStr) {
    if (!dateStr) return ''
    const d = applyTzOffset(dateStr)
    if (!d || isNaN(d.getTime())) return dateStr
    let h = d.getUTCHours()
    const ampm = h >= 12 ? 'PM' : 'AM'
    h = h % 12 || 12
    const min = String(d.getUTCMinutes()).padStart(2, '0')
    return `${months[d.getUTCMonth()]} ${d.getUTCDate()}, ${d.getUTCFullYear()} ${h}:${min} ${ampm}`
  }

  /**
   * Format DB datetime to relative time "2h ago", "3d ago"
   */
  function timeAgo(dateStr) {
    if (!dateStr) return ''
    const d = applyTzOffset(dateStr)
    if (!d || isNaN(d.getTime())) return ''
    const now = new Date()
    const diff = Math.floor((now - d) / 60000)
    if (diff < 1) return 'just now'
    if (diff < 60) return diff + 'm ago'
    if (diff < 1440) return Math.floor(diff / 60) + 'h ago'
    if (diff < 10080) return Math.floor(diff / 1440) + 'd ago'
    return formatDate(dateStr)
  }

  function statusClass(status) {
    const s = (status || '').toLowerCase()
    if (s.includes('complete')) return 'complete'
    if (s.includes('track')) return 'ontrack'
    if (s.includes('delay')) return 'delay'
    if (s.includes('risk')) return 'risk'
    return 'ontrack'
  }

  function progressColor(pct) {
    if (pct >= 100) return '#2AD36C'
    if (pct >= 60) return '#6570FD'
    if (pct >= 30) return '#F99003'
    return '#E84C85'
  }

  function completedPct(graph) {
    const t = graph?.total || 0
    const c = graph?.completed || 0
    return t > 0 ? Math.round((c / t) * 100) : 0
  }

  return { formatDate, formatDateTime, timeAgo, statusClass, progressColor, completedPct }
}
