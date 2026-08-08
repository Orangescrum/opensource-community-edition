<?php $this->assign('title', 'System Requirements'); ?>

<div class="form-header">
    <h2>System Requirements</h2>
    <p class="install-step-label">Step 1 of 5</p>
</div>

<?= $this->Flash->render() ?>

<?= $this->Form->create(null, ['id' => 'setup']) ?>

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
    <small style="color:green;">Please make sure the App URL is correct. If not, update <code>App.fullBaseUrl</code> in <code>config/app.php</code>.</small>
</div>

<ul style="list-style:none; padding:0; margin:20px 0;">
    <li style="padding:10px 0; border-bottom:1px solid #e2e8f0; font-size:14px; color:#64748b;">
        PHP Version >= <?= $phpSupportInfo['minimum'] ?>
    </li>
    <?php foreach ($requirements['requirements'] as $extension => $enabled): ?>
        <li style="padding:10px 0; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:14px;"><?= htmlspecialchars($extension) ?></span>
            <span>
                <?php if ($enabled): ?>
                    <span style="color:#22c55e; font-size:18px;">&#10003;</span>
                <?php else: ?>
                    <span style="color:#ef4444; font-size:18px;">&#10007;</span>
                <?php endif; ?>
            </span>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (!isset($requirements['errors']) && $phpSupportInfo['supported']): ?>
    <?= $this->Form->submit('Next', ['class' => 'btn btn-block btn-submit']) ?>
<?php endif; ?>

<?= $this->Form->end() ?>

<script>$(document).ready(function () { localStorage.clear(); });</script>
