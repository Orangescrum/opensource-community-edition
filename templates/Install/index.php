<?php $this->assign('title', 'Database Configuration'); ?>

<div class="ld_pop_mcnt" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:40px; border-radius:12px; text-align:center; font-family:'Inter',sans-serif;">
        Please do not refresh the page while installation is being processed.
        <div style="margin-top:15px;">
            <img src="<?= $this->Url->webroot('img/images/case_loader2.gif') ?>" style="width:32px;" />
        </div>
    </div>
</div>

<?php if ($step == 1) { ?>

<div class="form-header">
    <h2>Database Configuration</h2>
    <p class="install-step-label">Step 3 of 5</p>
</div>

<?= $this->Flash->render() ?>

<?php if ($ask_upgrade && !$is_upgrade && !$is_reinstall) { ?>
    <div class="alert alert-info" style="background:#dbeafe; color:#1e40af; padding:14px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;">
        <p style="margin:0 0 4px; font-weight:600;">This database already contains data.</p>
        <p style="margin:0 0 12px;">Choose how you'd like to proceed:</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn btn-submit" style="flex:1; min-width:170px; text-align:center; padding:10px 14px;"
               href="<?= $this->Url->build(['plugin' => null, 'controller' => 'Install', 'action' => 'database', '?' => ['upgrade' => 1]]) ?>">
                Keep data &amp; upgrade
            </a>
            <a class="btn" style="flex:1; min-width:170px; text-align:center; padding:10px 14px; border:1.5px solid #ef4444; color:#b91c1c; background:#fff; border-radius:8px; text-decoration:none;"
               href="<?= $this->Url->build(['plugin' => null, 'controller' => 'Install', 'action' => 'database', '?' => ['reinstall' => 1]]) ?>">
                Erase &amp; clean reinstall
            </a>
        </div>
        <small style="display:block; margin-top:10px; color:#1e3a8a; line-height:1.5;">
            <strong>Keep data &amp; upgrade</strong> preserves your existing data and applies any schema updates.<br>
            <strong>Clean reinstall</strong> permanently deletes all tables and data, then installs fresh.
        </small>
    </div>
<?php } ?>

<?php if ($is_reinstall) { ?>
    <div class="alert alert-danger" style="background:#fee2e2; color:#991b1b; padding:14px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;">
        <p style="margin:0; font-weight:600;">Clean reinstall selected</p>
        <p style="margin:6px 0 0;">Clicking <strong>Erase &amp; Install</strong> will permanently delete every table and all data in this database, then install a fresh copy.</p>
    </div>
<?php } ?>

<?php
$dbFormUrl = ['plugin' => null, 'controller' => 'Install', 'action' => 'database'];
if ($is_reinstall) {
    $dbFormUrl['?'] = ['reinstall' => 1];
} elseif ($is_upgrade) {
    $dbFormUrl['?'] = ['upgrade' => $is_upgrade];
}
?>
<?= $this->Form->create(null, ['id' => 'setup', 'url' => $dbFormUrl]) ?>

<div class="form-group field_wrapper">
    <label>App URL</label>
    <?= $this->Form->control('App.fullBaseUrl', [
        'type' => 'text',
        'label' => false,
        'value' => $app_config['fullBaseUrl'] ?? '',
        'readonly' => true,
        'disabled' => true,
        'class' => 'form-control'
    ]) ?>
</div>

<div class="form-group field_wrapper">
    <label>Database Name <span style="color:#ef4444;">*</span></label>
    <?= $this->Form->control('Database.database', [
        'type' => 'text',
        'placeholder' => 'Enter database name',
        'autocomplete' => 'off',
        'label' => false,
        'value' => $database_config['database'] ?? '',
        'class' => 'form-control'
    ]) ?>
</div>

<div class="form-group field_wrapper">
    <label>Host <span style="color:#ef4444;">*</span></label>
    <?= $this->Form->control('Database.host', [
        'type' => 'text',
        'placeholder' => 'localhost or IP address',
        'autocomplete' => 'off',
        'label' => false,
        'value' => $database_config['host'] ?? '',
        'class' => 'form-control'
    ]) ?>
</div>

<div class="form-group field_wrapper">
    <label>Port</label>
    <?= $this->Form->control('Database.port', [
        'type' => 'text',
        'placeholder' => '5432',
        'value' => $database_config['port'] ?? '5432',
        'autocomplete' => 'off',
        'label' => false,
        'class' => 'form-control'
    ]) ?>
</div>

<div class="form-group field_wrapper">
    <label>Username <span style="color:#ef4444;">*</span></label>
    <?= $this->Form->control('Database.user', [
        'type' => 'text',
        'placeholder' => 'Enter database username',
        'autocomplete' => 'off',
        'label' => false,
        'value' => $database_config['user'] ?? '',
        'class' => 'form-control'
    ]) ?>
</div>

<div class="form-group field_wrapper">
    <label>Password</label>
    <?= $this->Form->control('Database.pass', [
        'type' => 'password',
        'placeholder' => 'Enter database password',
        'autocomplete' => 'off',
        'label' => false,
        'value' => $database_config['pass'] ?? '',
        'class' => 'form-control'
    ]) ?>
</div>

<?php
$dbSubmitLabel = $is_reinstall ? 'Erase & Install' : ($is_upgrade ? 'Use Existing Database & Continue' : 'Next');
?>
<?= $this->Form->submit($dbSubmitLabel, ['class' => 'btn btn-block btn-submit']) ?>
<?= $this->Form->end() ?>

<script type="text/javascript">
$(document).ready(function () {
    $("#setup").validate({
        rules: {
            'Database[database]': { required: true },
            'Database[host]': { required: true },
            'Database[user]': { required: true }
        },
        messages: {
            'Database[database]': { required: "Please enter your database name" },
            'Database[host]': { required: "Please enter your database host" },
            'Database[user]': { required: "Please enter your database username" }
        },
        errorElement: "small",
        errorPlacement: function (error, element) { error.insertAfter(element); },
        submitHandler: function (form) {
            $(".ld_pop_mcnt").css("display","flex");
            form.submit();
        }
    });
});
</script>

<?php } ?>

<?php if ($step == 2) { ?>

<div class="form-header">
    <h2>Ready to Install</h2>
    <p class="install-step-label">Step 4 of 5</p>
</div>

<?= $this->Flash->render() ?>

<div style="text-align:center; padding:20px 0;">
    <p style="font-size:16px; color:#334155; margin-bottom:24px;">
        Great! You're almost there. Are you ready to complete the installation?
    </p>

    <?= $this->Form->create(null, ['url' => ['controller' => 'Install', 'action' => 'confirm', '?' => ['upgrade' => $is_upgrade]], 'id' => 'confirmForm']) ?>
    <button type="button" id="btnFinishInstall" class="btn btn-block btn-submit">Yes, Let's Finish Installation</button>
    <?= $this->Form->end() ?>

    <div style="margin-top:16px;">
        <?= $this->Html->link('Not Yet, I Need to Review', ['controller' => 'Install', 'action' => 'index', '?' => ['back' => 'install']], ['style' => 'color:var(--slate-500); font-size:14px;']) ?>
    </div>
</div>

<!-- Custom confirm dialog -->
<div id="confirmOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9998; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px; max-width:420px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3); font-family:'Inter',sans-serif;">
        <div style="width:56px; height:56px; background:#fff7ed; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <h3 style="margin:0 0 8px; font-size:20px; font-weight:700; color:#1e293b;">Complete Installation?</h3>
        <p style="margin:0 0 24px; font-size:14px; color:#64748b; line-height:1.5;">This will create all database tables and seed initial data. This action cannot be undone.</p>
        <div style="display:flex; gap:12px;">
            <button id="confirmCancel" style="flex:1; padding:10px; border:1.5px solid #e2e8f0; background:#fff; border-radius:8px; font-size:14px; font-weight:600; color:#64748b; cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.2s;">Cancel</button>
            <button id="confirmOk" style="flex:1; padding:10px; border:none; background:#f97316; border-radius:8px; font-size:14px; font-weight:600; color:#fff; cursor:pointer; font-family:'Inter',sans-serif; transition:all 0.2s;">Yes, Install</button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $('#btnFinishInstall').on('click', function () {
        $('#confirmOverlay').css('display', 'flex');
    });
    $('#confirmCancel').on('click', function () {
        $('#confirmOverlay').hide();
    });
    $('#confirmOk').on('click', function () {
        $('#confirmOverlay').hide();
        $(".ld_pop_mcnt").css("display","flex");
        $('#confirmForm')[0].submit();
    });
});
</script>

<?php } ?>
