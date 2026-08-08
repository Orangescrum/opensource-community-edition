import { ref } from 'vue';
import axios from 'axios';

const settings = ref({
    sender_name: '',
    sender_signoff: '',
    brand_color: '',
    logo_url: '',
    include_header: false,
    include_footer: false,
});
const defaults = ref({
    sender_signoff: '',
    brand_color: '#1565C0',
});
const loaded = ref(false);
let loadPromise = null;

function resolveCommonUrl(apiBaseUrl) {
    return `${String(apiBaseUrl || '').replace(/\/email-templates$/, '')}/common-settings`;
}

export function useCommonSettings(apiBaseUrl) {
    const commonUrl = resolveCommonUrl(apiBaseUrl);

    async function load(force = false) {
        if (loaded.value && !force) return settings.value;
        if (!loadPromise) {
            loadPromise = axios.get(commonUrl).then(({ data }) => {
                Object.assign(settings.value, data.settings || {});
                Object.assign(defaults.value, data.defaults || {});
                loaded.value = true;
                loadPromise = null;
                return settings.value;
            }).catch((err) => {
                loadPromise = null;
                throw err;
            });
        }
        return loadPromise;
    }

    async function save(payload) {
        const { data } = await axios.post(commonUrl, payload);
        await load(true);
        return data;
    }

    async function reset() {
        await axios.post(`${commonUrl}/reset`);
        await load(true);
    }

    return { settings, defaults, loaded, load, save, reset };
}
