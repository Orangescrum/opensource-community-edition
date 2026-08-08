<?php $this->assign('title', 'SMTP Configuration'); ?>

<div class="form-header">
    <h2>SMTP Configuration</h2>
    <p class="install-step-label">Step 5 of 5</p>
</div>

<form id="setup" method="post">
    <input type="radio" name="Smtp[smpt_mail_type]" value="1" checked="checked" style="display:none;">

    <div class="form-group field_wrapper">
        <label>SMTP Type</label>
        <div style="display:flex; gap:16px; padding:8px 0;">
            <label style="font-size:14px; cursor:pointer;">
                <input type="radio" name="Smtp[auth_type]" value="auth" checked="checked" onclick="toggleAuthFields(this)"> Auth Required
            </label>
            <label style="font-size:14px; cursor:pointer;">
                <input type="radio" name="Smtp[auth_type]" value="noauth" onclick="toggleAuthFields(this)"> No Auth (Relay)
            </label>
        </div>
    </div>

    <div class="form-group field_wrapper">
        <label>Host</label>
        <input type="text" name="Smtp[host]" placeholder="ssl://smtp.gmail.com" autocomplete="off" class="form-control" />
        <input type="hidden" name="Smtp[is_smtp]" value="0" id="is_smtp" />
    </div>

    <div class="form-group field_wrapper">
        <label>Port</label>
        <input type="text" name="Smtp[port]" placeholder="25, 465 or 587" autocomplete="off" class="form-control" />
    </div>

    <div class="form-group field_wrapper auth_fields">
        <label>Username or Email</label>
        <input type="text" name="Smtp[email]" placeholder="youremail@gmail.com" autocomplete="off" class="form-control" />
    </div>

    <div class="form-group field_wrapper auth_fields">
        <label>Password</label>
        <input type="password" name="Smtp[password]" placeholder="******" autocomplete="off" class="form-control" />
    </div>

    <div class="form-group field_wrapper">
        <label>From Email</label>
        <input type="text" name="Smtp[from_email]" placeholder="youremail@gmail.com" autocomplete="off" class="form-control" />
    </div>

    <div class="form-group field_wrapper">
        <label>Notify Email</label>
        <input type="text" name="Smtp[notify_email]" placeholder="notify-email@gmail.com" autocomplete="off" class="form-control" />
    </div>

    <div class="form-group" style="padding:8px 0;">
        <label style="font-size:14px; cursor:pointer;">
            <input type="checkbox" name="Smtp[tls]" value="1" /> Use TLS
        </label>
    </div>

    <input type="submit" value="Save & Continue" class="btn btn-block btn-submit" />

    <div style="text-align:center; margin-top:12px;">
        <?= $this->Html->link('Skip for now', ['controller' => 'Install', 'action' => 'smtp', '?' => ['skip' => 'yes']], ['style' => 'color:var(--slate-500); font-size:14px;']) ?>
    </div>
</form>

<script>
function toggleAuthFields(el) {
    if (el.value === 'noauth') {
        $('.auth_fields').hide();
        $("input[name='Smtp[email]']").removeAttr('required').val("");
        $("input[name='Smtp[password]']").removeAttr('required').val("");
    } else {
        $('.auth_fields').show();
        $("input[name='Smtp[email]']").attr('required', true);
        $("input[name='Smtp[password]']").attr('required', true);
    }
}
</script>
