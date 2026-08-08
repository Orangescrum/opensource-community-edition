// Dashboard i18n helper.
//
// The dashboard is a client-rendered Vue SPA, so it can't call CakePHP's __()
// at render time. Instead, MyDashboardsController ships a
//   { "<English msgid>": "<translated label>" }
// map in window.DASHBOARD_CONFIG.labels. Because that map is built server-side
// with __(), both layers are already applied:
//   Layer 1 — base gettext catalog (.po/.mo) for the active locale
//   Layer 2 — LabelCustomizer per-company overlay (Project -> Case, etc.)
//
// t() just looks the English source string up in that map, falling back to the
// source string when there is no entry (default English company, or a string
// not yet in the catalog).
const config = (typeof window !== 'undefined' && window.DASHBOARD_CONFIG) || {}
const labels = config.labels || {}

export function t(key) {
  const v = labels[key]
  return v == null || v === '' ? key : v
}
