import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

/**
 * Read CSS custom property from the app's theme.
 * Falls back to default if not set (e.g. in test environments).
 */
function getCssVar(name, fallback) {
  const val = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return val || fallback
}

export function createDashboardVuetify() {
  return createVuetify({
    components,
    directives,
    theme: {
      defaultTheme: 'osTheme',
      themes: {
        osTheme: {
          dark: false,
          colors: {
            primary: getCssVar('--primary', '#6366f1'),
            secondary: getCssVar('--gray-500', '#64748b'),
            accent: getCssVar('--primary', '#f59e0b'),
            error: getCssVar('--danger', '#ef4444'),
            warning: getCssVar('--warning', '#f59e0b'),
            info: getCssVar('--info', '#3b82f6'),
            success: getCssVar('--success', '#10b981'),
            background: 'transparent',
            surface: getCssVar('--white', '#ffffff'),
          },
        },
      },
    },
    defaults: {
      VSelect: {
        variant: 'outlined',
        density: 'compact',
        hideDetails: true,
      },
      VCard: { elevation: 0 },
      VBtn: { rounded: 'lg' },
    },
  })
}
