<template>
    <div class="emt-edit">
        <v-progress-linear v-if="loading" indeterminate color="primary" />

        <div v-else>
            <div class="emt-header">
                <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$emit('back')" class="back-btn">
                    Back to list
                </v-btn>
                <div class="emt-header-row">
                    <div class="emt-header-text">
                        <h2 class="emt-title">Email configuration</h2>
                        <p class="emt-subtitle">
                            <code>email-config</code> — Transport, sender address, and credentials. Changes take effect
                            on the next request.
                        </p>
                    </div>
                    <div class="emt-actions">
                        <v-btn variant="text" prepend-icon="mdi-history" @click="openHistory">History</v-btn>
                        <v-btn variant="text" :disabled="!isDirty" @click="resetForm">Discard</v-btn>
                        <v-btn variant="text" @click="$emit('back')">Cancel</v-btn>
                        <v-btn color="primary" variant="flat" :loading="saving" :disabled="envLocked" @click="askPassword('save')">Save</v-btn>
                    </div>
                </div>
            </div>

            <v-alert v-if="envLocked" type="warning" class="mb-4" density="comfortable">
                <div class="text-subtitle-2 mb-1">Email configuration is managed by environment variables</div>
                <div class="text-body-2">
                    The following env vars are set in this deployment and override anything saved here:
                    <code>{{ envKeys.join(', ') }}</code>. Edit them in your environment to change these settings.
                    Send test still uses the effective runtime config.
                </div>
            </v-alert>

            <v-alert v-if="error" type="error" closable class="mb-4" @click:close="error = null">{{ error }}</v-alert>
            <v-alert v-if="savedAt" type="success" closable class="mb-4" @click:close="savedAt = null">
                Saved at {{ savedAt }}
            </v-alert>

            <v-row class="emt-body-row">
                <v-col cols="12" md="8" class="emt-form-col">
                    <fieldset :disabled="envLocked" class="emt-form-fieldset">
                    <div class="region-field mb-4">
                        <v-card class="pa-3">
                            <div class="text-subtitle-2 mb-2">Transport</div>
                            <v-radio-group v-model="form.transport" inline density="compact" hide-details>
                                <v-radio label="SMTP" value="smtp"></v-radio>
                                <v-radio label="SendGrid" value="sendgrid"></v-radio>
                            </v-radio-group>
                        </v-card>
                    </div>

                    <template v-if="form.transport === 'smtp'">
                        <div class="region-field mb-4">
                            <v-text-field v-model="form.host" label="SMTP host" placeholder="smtp.example.com"
                                variant="outlined" density="compact" hide-details maxlength="255" />
                        </div>

                        <div class="region-field mb-4">
                            <div class="d-flex align-center" style="gap:16px;">
                                <v-text-field v-model="form.port" label="Port" placeholder="25, 465 or 587"
                                    variant="outlined" density="compact" hide-details maxlength="10"
                                    style="max-width:200px;" />
                                <v-switch v-model="form.tls" label="Use TLS" color="primary" density="compact"
                                    hide-details class="emt-switch" />
                            </div>
                        </div>

                        <div class="region-field mb-4">
                            <v-card class="pa-3">
                                <div class="text-subtitle-2 mb-2">SMTP type</div>
                                <v-radio-group v-model="authType" inline density="compact" hide-details>
                                    <v-radio label="Auth Required" value="auth"></v-radio>
                                    <v-radio label="No Auth (Relay)" value="noauth"></v-radio>
                                </v-radio-group>
                            </v-card>
                        </div>

                        <div v-if="authType === 'auth'" class="region-field mb-4">
                            <v-text-field v-model="form.email" label="SMTP username" placeholder="username or email"
                                variant="outlined" density="compact" hide-details maxlength="255" autocomplete="off" />
                        </div>

                        <div v-if="authType === 'auth'" class="region-field mb-4">
                            <v-text-field v-model="form.password" label="SMTP password" type="password"
                                :placeholder="hasSmtpPassword ? '(unchanged — leave blank to keep current password)' : ''"
                                variant="outlined" density="compact" hide-details autocomplete="new-password" />
                        </div>
                    </template>

                    <template v-else>
                        <div class="region-field mb-4">
                            <v-text-field v-model="form.api_key" label="SendGrid API key" type="password"
                                :placeholder="hasSendgridKey ? '(unchanged — leave blank to keep current key)' : 'SG.xxxxx'"
                                variant="outlined" density="compact" autocomplete="new-password" maxlength="500"
                                hint="Create an API key in SendGrid → Settings → API Keys with Mail Send permission."
                                persistent-hint />
                        </div>
                    </template>

                    <div class="region-field mb-4">
                        <div class="section-heading">Sender addresses</div>
                    </div>

                    <div class="region-field mb-4">
                        <v-text-field v-model="form.from_email" label="From email" type="email"
                            placeholder="noreply@yourcompany.com" variant="outlined" density="compact" maxlength="255"
                            hint="Appears in the From header of outbound mail." persistent-hint />
                    </div>

                    <div class="region-field mb-4">
                        <v-text-field v-model="form.notify_email" label="Notify email" type="email"
                            placeholder="ops@yourcompany.com" variant="outlined" density="compact" maxlength="255"
                            hint="Used for internal admin notifications." persistent-hint />
                    </div>
                    </fieldset>

                    <v-card class="pa-3 mt-4">
                        <div class="text-subtitle-2 mb-2">Send test email</div>
                        <div class="text-caption text-medium-emphasis mb-3">
                            Sends a one-line message using the currently saved transport.
                        </div>
                        <div class="d-flex align-center" style="gap:12px;">
                            <v-text-field v-model="testRecipient" label="Recipient" placeholder="you@example.com"
                                density="compact" variant="outlined" hide-details class="flex-grow-1" />
                            <v-btn color="primary" :loading="testInFlight" :disabled="!testRecipient"
                                @click="onTestSend">Send test</v-btn>
                        </div>
                        <div v-if="testResult" class="text-caption mt-2"
                            :style="{ color: testResult.success ? '#1b5e20' : '#b00020' }">
                            {{ testResult.message }}
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </div>

        <v-dialog v-model="historyOpen" max-width="640">
            <v-card>
                <v-card-title class="d-flex align-center">
                    <span>Email configuration history</span>
                    <v-spacer />
                    <v-btn icon="mdi-close" variant="text" @click="historyOpen = false"></v-btn>
                </v-card-title>
                <v-card-text>
                    <div v-if="!history.length" class="text-medium-emphasis py-4">No history yet.</div>
                    <v-list v-else lines="two">
                        <v-list-item v-for="(entry, idx) in history" :key="idx">
                            <template #title>
                                <span style="font-weight:500;">{{ formatTimestamp(entry.timestamp) }}</span>
                                <span class="ml-2 text-caption text-medium-emphasis">({{ entry.action }})</span>
                            </template>
                            <template #subtitle>
                                <div class="text-caption">{{ entry.user_email || 'unknown user' }}</div>
                                <div class="text-caption">{{ historyDescription(entry.snapshot) }}</div>
                            </template>
                            <template #append>
                                <v-btn size="small" variant="text" :loading="revertingIndex === idx"
                                    @click="askPassword({ kind: 'revert', index: idx })">Revert</v-btn>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snack" :timeout="2200">{{ snackText }}</v-snackbar>

        <v-dialog v-model="pwPromptOpen" max-width="420" :persistent="pwSubmitting">
            <v-card>
                <v-card-title>Confirm with your password</v-card-title>
                <v-card-text>
                    <p class="text-caption text-medium-emphasis mb-3">Re-enter your account password to confirm this change.</p>
                    <v-text-field v-model="pwInput" type="password" label="Password" autofocus
                        :error-messages="pwError ? [pwError] : []" @keyup.enter="confirmPasswordAndSubmit"
                        density="compact" hide-details="auto" autocomplete="current-password" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" :disabled="pwSubmitting" @click="pwPromptOpen = false">Cancel</v-btn>
                    <v-btn color="primary" :loading="pwSubmitting" @click="confirmPasswordAndSubmit">Confirm</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import axios from "axios";

defineEmits(["back"]);

const config = window.EMAIL_TEMPLATING_CONFIG || {};
const apiBase = String(config.apiBaseUrl || '').replace(/\/email-templates$/, '');
const emailUrl = `${apiBase}/email-config`;

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const savedAt = ref(null);
const hasSmtpPassword = ref(false);
const hasSendgridKey = ref(false);
const authType = ref('auth');
const envLocked = ref(false);
const envKeys = ref([]);

const form = reactive({
    transport: "smtp",
    host: "",
    port: "",
    email: "",
    password: "",
    tls: false,
    api_key: "",
    from_email: "",
    notify_email: "",
});
const initial = reactive({ ...form });

const snack = ref(false);
const snackText = ref("");

const isDirty = computed(() => {
    for (const k of Object.keys(form)) {
        if (form[k] !== initial[k]) return true;
    }
    return false;
});

function syncFormFromPayload(payload) {
    const transport = payload.transport || 'smtp';
    const smtp = payload.smtp || {};
    const sg = payload.sendgrid || {};

    hasSmtpPassword.value = !!smtp.has_password;
    hasSendgridKey.value = !!sg.has_api_key;

    form.transport = transport;
    form.host = smtp.host || "";
    form.port = smtp.port || "";
    form.email = smtp.email || "";
    form.password = "";
    form.tls = !!smtp.tls;
    form.api_key = "";
    form.from_email = payload.from_email || "";
    form.notify_email = payload.notify_email || "";
    authType.value = (smtp.email || smtp.has_password) ? 'auth' : 'noauth';

    Object.assign(initial, { ...form });
}

async function load() {
    loading.value = true;
    try {
        const { data } = await axios.get(emailUrl);
        syncFormFromPayload(data.email || {});
        envLocked.value = !!data.env_locked;
        envKeys.value = Array.isArray(data.env_keys) ? data.env_keys : [];
    } catch (e) {
        error.value = e.response?.data?.error || e.message || "Failed to load email configuration";
    } finally {
        loading.value = false;
    }
}

async function doSave(currentPassword) {
    saving.value = true;
    error.value = null;
    try {
        const payload = {
            transport: form.transport,
            from_email: form.from_email,
            notify_email: form.notify_email,
            current_password: currentPassword,
        };
        if (form.transport === 'smtp') {
            Object.assign(payload, {
                host: form.host,
                port: form.port,
                email: form.email,
                password: form.password,
                tls: form.tls,
            });
        } else {
            payload.api_key = form.api_key;
        }
        const { data } = await axios.post(emailUrl, payload);
        if (data && data.success === false) {
            const err = new Error(data.error || "Save failed");
            err.response = { data };
            throw err;
        }
        await load();
        savedAt.value = new Date().toLocaleTimeString();
        snackText.value = "Email configuration saved";
        snack.value = true;
    } finally {
        saving.value = false;
    }
}

function resetForm() {
    Object.assign(form, initial);
    snackText.value = "Changes discarded";
    snack.value = true;
}

watch(authType, (newVal) => {
    if (newVal === 'noauth') {
        form.email = '';
        form.password = '';
    }
});

const testRecipient = ref('');
const testInFlight = ref(false);
const testResult = ref(null);

async function onTestSend() {
    testInFlight.value = true;
    testResult.value = null;
    try {
        const { data } = await axios.post(`${emailUrl}/test`, { to: testRecipient.value });
        testResult.value = {
            success: !!data.success,
            message: data.success ? `Sent to ${testRecipient.value}` : (data.error || 'Failed'),
        };
    } catch (e) {
        testResult.value = { success: false, message: e.response?.data?.error || e.message || 'Request failed' };
    } finally {
        testInFlight.value = false;
    }
}

const historyOpen = ref(false);
const history = ref([]);
const revertingIndex = ref(null);

async function loadHistory() {
    try {
        const { data } = await axios.get(`${emailUrl}/history`);
        history.value = data.history || [];
    } catch (e) {
        history.value = [];
    }
}

async function openHistory() {
    historyOpen.value = true;
    await loadHistory();
}

function historyDescription(snapshot) {
    if (!snapshot) return '';
    const t = snapshot.transport || 'smtp';
    if (t === 'sendgrid') {
        return `SendGrid → ${snapshot.from_email || '(no from)'}`;
    }
    return `SMTP ${snapshot.host || ''}:${snapshot.port || ''} → ${snapshot.from_email || '(no from)'}`;
}

async function doRevert(idx, currentPassword) {
    revertingIndex.value = idx;
    try {
        const { data } = await axios.post(`${emailUrl}/revert`, {
            index: idx,
            current_password: currentPassword,
        });
        if (data && data.success === false) {
            const err = new Error(data.error || 'Revert failed');
            err.response = { data };
            throw err;
        }
        await load();
        await loadHistory();
        snackText.value = 'Reverted to selected configuration';
        snack.value = true;
    } finally {
        revertingIndex.value = null;
    }
}

const pwPromptOpen = ref(false);
const pwPromptAction = ref(null);
const pwInput = ref('');
const pwError = ref('');
const pwSubmitting = ref(false);

function askPassword(action) {
    pwPromptAction.value = action;
    pwInput.value = '';
    pwError.value = '';
    pwPromptOpen.value = true;
}

async function confirmPasswordAndSubmit() {
    if (!pwInput.value) {
        pwError.value = 'Enter your password.';
        return;
    }
    pwSubmitting.value = true;
    pwError.value = '';
    try {
        if (pwPromptAction.value === 'save') {
            await doSave(pwInput.value);
        } else if (pwPromptAction.value && pwPromptAction.value.kind === 'revert') {
            await doRevert(pwPromptAction.value.index, pwInput.value);
        }
        pwPromptOpen.value = false;
    } catch (e) {
        const payload = e?.response?.data;
        if (payload?.password_invalid) {
            pwError.value = payload.error || 'Incorrect password.';
        } else {
            pwError.value = payload?.error || e.message || 'Action failed.';
        }
    } finally {
        pwSubmitting.value = false;
    }
}

function formatTimestamp(iso) {
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

onMounted(load);
</script>

<style scoped>
.emt-edit {
    padding: 0 4px;
    scrollbar-gutter: stable;
}

.emt-header {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #ffffff;
    margin: 0 -4px 16px -4px;
    padding: 12px 12px 14px 12px;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    min-height: 78px;
    box-sizing: border-box;
}

.emt-header-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.emt-header-text {
    flex: 1;
    min-width: 0;
}

.emt-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px !important;
    letter-spacing: -0.01em;
}

.emt-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.emt-subtitle code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 11px;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 3px;
}

.back-btn {
    margin-left: -8px;
    margin-bottom: 0;
    min-height: 28px !important;
    padding: 0 6px !important;
    font-size: 12px !important;
}

.emt-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.emt-body-row {
    margin: 0 !important;
}

.emt-form-col {
    padding-right: 16px !important;
    min-width: 0;
}

.region-field {
    position: relative;
}

.section-heading {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    border-top: 1px solid #e5e7eb;
    padding-top: 16px;
    margin-top: 4px;
}

.emt-switch :deep(.v-input__control) {
    min-height: 28px;
}

.emt-switch :deep(.v-selection-control) {
    min-height: 28px;
}

.emt-switch :deep(.v-label) {
    font-size: 13px;
    opacity: 1;
    padding-left: 4px;
}

.emt-switch {
    margin: 0;
    padding: 0;
}

.emt-form-fieldset {
    border: 0;
    padding: 0;
    margin: 0;
    min-width: 0;
}
.emt-form-fieldset:disabled {
    opacity: 0.6;
}
</style>
