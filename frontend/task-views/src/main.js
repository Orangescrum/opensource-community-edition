import { createApp } from "vue";
import { createPinia } from "pinia";
import axios from "axios";
import vuetify from "@/plugins/vuetify";
import App from "@/App.vue";
import "@/styles/tokens.css";

const config = window.TASK_VIEWS_CONFIG || {};

if (config.csrfToken) {
    axios.defaults.headers.common["X-CSRF-Token"] = config.csrfToken;
    axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
}

const mountEl = document.querySelector("#taskViewsApp");

if (mountEl && !mountEl.__vue_app__) {
    createApp(App).use(createPinia()).use(vuetify).mount(mountEl);
}
