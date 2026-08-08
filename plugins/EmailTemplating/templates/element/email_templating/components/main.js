import { createApp } from 'vue';
import vuetify from './plugins/vuetify';
import App from './App.vue';
import axios from 'axios';

const config = window.EMAIL_TEMPLATING_CONFIG || {};
if (config.csrfToken) {
    axios.defaults.headers.common['X-CSRF-Token'] = config.csrfToken;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}

const mountEl = document.querySelector('#emailTemplatingApp');
if (mountEl && !mountEl.__vue_app__) {
    const app = createApp(App);
    app.use(vuetify);
    app.mount('#emailTemplatingApp');
}
