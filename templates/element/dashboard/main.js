import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import { t } from './composables/useI18n'

const app = createApp(App)

app.use(createPinia())

app.config.globalProperties.$t = t

app.mount('#dashboardApp')
