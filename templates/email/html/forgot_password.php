<?php
/**
 * Forgot password email fragment.
 * Expects $name, $urlValue, $companyName (optional).
 */
$notifyEmail = \Cake\Core\Configure::read('AppEmail.notify_email')
    ?: \Cake\Core\Configure::read('AppEmail.from_email', '');
$companyName = $companyName ?? \Cake\Core\Configure::read('App.name', 'Orangescrum');
?>
    <div style="padding:20px 24px; background:#FB8C00; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('Reset your password'); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0;"><?php echo __('Dear'); ?> <?php echo h($name); ?>,</div>
        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php echo __('We received a request to reset the password for your {0} account. Click the button below to choose a new password. If you did not make this request you can safely ignore this email.', h($companyName)); ?>
        </div>

<?php echo $this->element('email/cta_button', [
            'url' => HTTP_ROOT . 'Users/forgotPassword' . $urlValue,
            'label' => __('Reset password'),
            'color' => '#FB8C00',
        ]); ?>

        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php echo __('This link expires in a short time for your security. If you have trouble, please write to'); ?>
            <a href="mailto:<?php echo h($notifyEmail); ?>" style="color:#FB8C00;"><?php echo h($notifyEmail); ?></a>.
        </div>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
