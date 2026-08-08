<?php
/**
 * Invite user email content fragment.
 * Matches the library tone (webroot/templates.html → "Invite User"): a single
 * polite welcome to the {{ companyName }} workspace plus an Accept button.
 * Expects $invitedUserData (array) with keys: expName, companyName,
 * fromUserName, fromUserEmail, qstr, invite_token, existing_user.
 */
$notifyEmail = \Cake\Core\Configure::read('AppEmail.notify_email')
    ?: \Cake\Core\Configure::read('AppEmail.from_email', '');
$companyName = $invitedUserData['companyName'] ?? \Cake\Core\Configure::read('App.name', 'Orangescrum');
$inviteeName = ucfirst($invitedUserData['expName'] ?? '');
$inviterName = $invitedUserData['fromUserName'] ?? '';
$inviterEmail = $invitedUserData['fromUserEmail'] ?? '';

$params = ['qstr' => $invitedUserData['qstr'] ?? ''];
if (!empty($invitedUserData['invite_token'])) {
    $params['token'] = $invitedUserData['invite_token'];
}
$action = !empty($invitedUserData['existing_user']) ? 'invitation' : 'invitation';
$ctaUrl = \Cake\Routing\Router::url(['controller' => 'Users', 'action' => $action, '?' => $params], true);
?>
    <div style="padding:20px 24px; background:#FB8C00; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('You\'re invited to {0}', h($companyName)); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0;"><?php echo __('Dear'); ?> <?php echo h($inviteeName); ?>,</div>

        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php if ($inviterName !== ''): ?>
                <strong><?php if ($inviterEmail !== ''): ?><a href="mailto:<?php echo h($inviterEmail); ?>" style="color:#FB8C00;"><?php echo h($inviterName); ?></a><?php else: ?><?php echo h($inviterName); ?><?php endif; ?></strong>
                <?php echo __('has invited you to join the {0} workspace.', h($companyName)); ?>
            <?php else: ?>
                <?php echo __('You have been invited to join the {0} workspace.', h($companyName)); ?>
            <?php endif; ?>
            <?php echo __('Click the button below to set up your account and start collaborating.'); ?>
        </div>

<?php echo $this->element('email/cta_button', [
            'url' => $ctaUrl,
            'label' => __('Accept invitation'),
            'color' => '#FB8C00',
        ]); ?>

        <?php if ($notifyEmail !== ''): ?>
        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php echo __('If you have any questions, please write to'); ?>
            <a href="mailto:<?php echo h($notifyEmail); ?>" style="color:#FB8C00;"><?php echo h($notifyEmail); ?></a>.
        </div>
        <?php endif; ?>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
