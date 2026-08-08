<template>
    <div class="emt-help">
        <div class="emt-header">
            <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$emit('back')" class="back-btn">
                {{ returnTo?.label || 'Back to list' }}
            </v-btn>
            <h2 class="emt-title">Help &amp; reference</h2>
            <p class="emt-subtitle">
                How customization, dynamic keywords, sending, and security work in the Email Templates plugin.
            </p>
        </div>

        <div class="emt-help-body">
            <nav class="emt-help-nav" aria-label="Help sections">
                <a href="#overview">Overview</a>
                <a href="#resolution">Resolution order</a>
                <a href="#tokens">Dynamic keywords &amp; substitution</a>
                <a href="#token-reference">Dynamic keywords reference</a>
                <a href="#preview">Preview vs Send test</a>
                <a href="#reset">Reset paths</a>
                <a href="#security">Security model</a>
                <a href="#coverage">Coverage &amp; carve-outs</a>
            </nav>

            <article class="emt-help-content">
                <section id="overview">
                    <h3>Overview</h3>
                    <p>
                        Customize the subject and body of every notification email your company
                        sends — no source-file edits required. Each template is split into
                        <strong>regions</strong> (Heading, Greeting, Body, Button label, Button URL,
                        Sign-off, Footer note). Edit any region in its own field and the live
                        preview on the right re-renders as you type.
                    </p>
                    <p>
                        Templates you haven't touched keep using the shipped defaults. There's no
                        publish step — saving a template makes the next send use the new copy.
                    </p>
                </section>

                <section id="resolution">
                    <h3>Resolution order at send time</h3>
                    <p>Each region resolves from the first source below that has a value:</p>
                    <ol class="emt-stack">
                        <li>
                            <span class="emt-step">1</span>
                            <div>
                                <strong>Per-template override</strong> — what you typed into the
                                template's editor. Always wins.
                            </div>
                        </li>
                        <li>
                            <span class="emt-step">2</span>
                            <div>
                                <strong>Common Settings</strong> — the company-wide sender name,
                                sign-off, brand color, and logo. Applied to every template that
                                hasn't been individually customized for that region.
                            </div>
                        </li>
                        <li>
                            <span class="emt-step">3</span>
                            <div>
                                <strong>Shipped default</strong> — the factory template value.
                                Used when neither of the above is set.
                            </div>
                        </li>
                    </ol>
                    <p class="emt-hint">
                        Example — set a Sign-off in Common Settings and leave Forgot Password's
                        Sign-off empty. Forgot Password uses the Common Settings sign-off. Edit
                        Forgot Password's Sign-off later and that template-specific value wins.
                    </p>
                </section>

                <section id="tokens">
                    <h3>Dynamic keywords &amp; substitution</h3>
                    <p>
                        Dynamic keywords are placeholders like <code v-pre>{{ userName }}</code> or
                        <code v-pre>{{ resetUrl }}</code> that the send-time pipeline replaces with
                        the real value. Each template exposes its own set — the editor's right-hand
                        <em>Available dynamic keywords</em> panel lists them, and the
                        <a href="#token-reference">Dynamic keywords reference</a> below gathers every available
                        keyword for every template.
                    </p>
                    <ul>
                        <li>
                            <strong>Unknown keywords</strong> render as literal text. Typing
                            <code v-pre>{{ unknown }}</code> into Forgot Password's Body shows up as
                            <code v-pre>{{ unknown }}</code> in the email.
                        </li>
                        <li>
                            <strong>Malformed keywords</strong> (e.g. <code v-pre>{{ broken</code>)
                            are left as-is. The engine never partially renders.
                        </li>
                        <li>
                            <strong>Special characters</strong> in keyword values (e.g.
                            <code>Acme &amp; Co</code>) are HTML-escaped once on output, so they
                            render correctly without double-encoding.
                        </li>
                    </ul>
                </section>

                <section id="token-reference">
                    <h3>Dynamic keywords reference</h3>
                    <p>
                        Looking for the full list of dynamic keywords available in each template — names,
                        descriptions, sample values, and which ones are raw HTML?
                    </p>
                    <p>
                        <v-btn
                            color="primary"
                            variant="tonal"
                            size="small"
                            prepend-icon="mdi-format-list-bulleted-square"
                            @click="$emit('tokens')"
                        >Open dynamic keywords reference</v-btn>
                    </p>
                </section>

                <section id="preview">
                    <h3>Preview vs Send test</h3>
                    <div class="emt-twoup">
                        <div>
                            <h4>Live preview</h4>
                            <p>
                                The panel beside the editor renders the template with sample data
                                and updates as you type (debounced ~350 ms). It's a sandboxed
                                iframe — clicks and scripts inside it can't escape.
                            </p>
                        </div>
                        <div>
                            <h4>Send test</h4>
                            <p>
                                Sends a real email so you can see the result in your inbox.
                                Recipients are restricted to your own address or the company's
                                configured sender address; this isn't an open mail-sending endpoint.
                            </p>
                        </div>
                    </div>
                </section>

                <section id="reset">
                    <h3>Reset paths</h3>
                    <p>Three reset scopes, smallest to largest:</p>
                    <ul>
                        <li>
                            <strong>Per-region reset arrow</strong> — the curved-arrow icon on any
                            dirty field reverts that one field to its shipped default. Click Save
                            to persist.
                        </li>
                        <li>
                            <strong>Reset all to defaults</strong> — in the template editor's
                            overflow menu. Deletes the entire override row; every region falls
                            back to its default. Asks for confirmation.
                        </li>
                        <li>
                            <strong>Reset to shipped defaults</strong> — in the Common Settings
                            overflow menu. Deletes the company's common-settings row; every
                            template that hasn't been individually customized returns to shipped
                            defaults.
                        </li>
                    </ul>
                </section>

                <section id="security">
                    <h3>Security model</h3>
                    <ul>
                        <li>
                            <strong>Admins only.</strong> Owner (SES_TYPE = 1) and Admin
                            (SES_TYPE = 2) can manage templates. Regular users are turned away
                            at both the admin page and the underlying APIs.
                        </li>
                        <li>
                            <strong>HTML sanitization.</strong> Admin-edited HTML in any region is
                            sanitized server-side. <code>&lt;script&gt;</code>,
                            <code>&lt;iframe&gt;</code>, and <code>&lt;style&gt;</code> blocks are
                            removed; event handlers like <code>onclick</code> and
                            <code>onerror</code> are stripped; <code>javascript:</code> URLs are
                            dropped; and <code>target="_blank"</code> anchors automatically gain
                            <code>rel="noopener noreferrer"</code>.
                        </li>
                        <li>
                            <strong>Sandboxed preview.</strong> The live preview iframe runs with
                            <code>sandbox=""</code> (no allow-list). Anything inside it that tries
                            to navigate, run scripts, submit forms, or open windows is blocked by
                            the browser.
                        </li>
                        <li>
                            <strong>Send-test recipient restriction.</strong> Only your own
                            address or the company's configured sender address is accepted as a
                            recipient. Anything else is rejected.
                        </li>
                        <li>
                            <strong>Per-company isolation.</strong> Every override and common
                            settings row is keyed by <code>company_id</code>, so one company's
                            customizations never leak into another's email.
                        </li>
                    </ul>
                </section>

                <section id="coverage">
                    <h3>Coverage &amp; carve-outs</h3>
                    <p>The new pipeline is wired into these live outbound flows:</p>
                    <ul class="emt-coverage">
                        <li>Forgot Password</li>
                        <li>Invite User</li>
                        <li>Registration Welcome</li>
                        <li>Task notifications — Created, Assigned, Mentioned, Comment, Status change</li>
                        <li>Invoice email</li>
                        <li>Project Added · Project Note added / updated</li>
                        <li>Workflow Action</li>
                        <li>Timesheet Approval status · Approver request</li>
                        <li>Epic approval status · Approver change</li>
                    </ul>
                    <p>Known carve-outs — by design, not regressions:</p>
                    <ul class="emt-coverage">
                        <li>
                            <strong>Plugin-2FA, Plugin-Leave, Plugin-TestCases.</strong> Rows
                            accept customizations and persist them, but their plugin flows haven't
                            been switched over to the new pipeline yet — outbound mail still uses
                            the legacy template.
                        </li>
                        <li>
                            <strong>Legacy reply hint and audit footer.</strong> The "Reply to
                            comment" hint and the "sent by X to Y" footer from the old task emails
                            are intentionally absent. Add them back via the Footer note region if
                            you want them.
                        </li>
                        <li>
                            <strong>Task Created / Task Assigned due date.</strong> Not shown in
                            the default intro line — the triggering flow doesn't supply one yet.
                        </li>
                    </ul>
                </section>
            </article>
        </div>
    </div>
</template>

<script setup>
defineProps({
    returnTo: {
        type: Object,
        default: () => ({ view: "list", key: null, label: "Back to list" }),
    },
});
defineEmits(["back", "tokens"]);
</script>

<style scoped>
.emt-help {
    padding: 0 4px 32px;
}

.emt-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.back-btn {
    margin-left: -8px;
    margin-bottom: 4px;
    min-height: 28px !important;
    padding: 0 6px !important;
    font-size: 12px !important;
}

.emt-title {
    font-size: 22px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 6px !important;
    letter-spacing: -0.01em;
}

.emt-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
    max-width: 640px;
}

.emt-help-body {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 32px;
    align-items: start;
}

.emt-help-nav {
    position: sticky;
    top: 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 13px;
    border-left: 2px solid #e5e7eb;
    padding-left: 12px;
}

.emt-help-nav a {
    color: #4b5563;
    text-decoration: none;
    padding: 4px 0;
    transition: color 120ms ease;
}

.emt-help-nav a:hover {
    color: #1565C0;
}

.emt-help-nav a:focus-visible {
    outline: 2px solid #1565C0;
    outline-offset: 2px;
    border-radius: 2px;
}

.emt-help-content {
    max-width: 720px;
    min-width: 0;
}

.emt-help-content section {
    margin-bottom: 32px;
    scroll-margin-top: 12px;
}

.emt-help-content h3 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 12px !important;
    padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
}

.emt-help-content h4 {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 6px !important;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.emt-help-content p {
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
    margin: 0 0 10px;
}

.emt-help-content ul,
.emt-help-content ol {
    font-size: 14px;
    color: #374151;
    line-height: 1.7;
    padding-left: 20px;
    margin: 8px 0 12px;
}

.emt-help-content li {
    margin-bottom: 4px;
}

.emt-help-content code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 12px;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 3px;
    color: #1565C0;
}

.emt-help-content strong {
    color: #111827;
}

.emt-hint {
    font-size: 13px !important;
    color: #4b5563 !important;
    background: #f9fafb;
    border-left: 3px solid #1565C0;
    padding: 10px 14px;
    border-radius: 0 4px 4px 0;
    line-height: 1.5;
}

.emt-stack {
    list-style: none;
    padding: 0 !important;
    margin: 0 0 12px !important;
}

.emt-stack li {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 4px;
    margin-bottom: 6px !important;
}

.emt-step {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #1565C0;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.emt-twoup {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 10px;
}

.emt-twoup>div {
    padding: 14px;
    background: #f9fafb;
    border-radius: 4px;
}

.emt-coverage {
    font-size: 13px !important;
}

@media (max-width: 720px) {
    .emt-help-body {
        grid-template-columns: 1fr;
    }

    .emt-help-nav {
        position: static;
        border-left: 0;
        border-top: 2px solid #e5e7eb;
        padding-left: 0;
        padding-top: 12px;
    }

    .emt-twoup {
        grid-template-columns: 1fr;
    }
}
</style>
