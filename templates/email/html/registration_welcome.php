<?php
/**
 * Registration welcome email fragment.
 * Expects $userName, $companyName, $loginUrl
 */
$notifyEmail = \Cake\Core\Configure::read('AppEmail.notify_email')
    ?: \Cake\Core\Configure::read('AppEmail.from_email', '');
$companyName = $companyName ?? 'Orangescrum';
?>
    <div style="padding:20px 24px; background:#2E7D32; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('Welcome to'); ?> <?php echo h($companyName); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0;"><?php echo __('Dear'); ?> <?php echo h($userName); ?>,</div>
        <div style="margin:0 0 16px 0; line-height:1.5;"><?php echo __('Your account on'); ?> <strong><?php echo h($companyName); ?></strong> <?php echo __('has been activated. You can now sign in and start using the platform.'); ?></div>

        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Account'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($userName); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Company'); ?></td>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($companyName); ?></td>
            </tr>
        </table>

        <?php if (!empty($loginUrl)): ?>
<?php echo $this->element('email/cta_button', [
                'url' => $loginUrl,
                'label' => __('Login to Your Account'),
                'color' => '#2E7D32',
            ]); ?>
        <?php endif; ?>

        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php echo __('If you have any questions or need assistance, please don\'t hesitate to contact our support team at'); ?>
            <a href="mailto:<?php echo h($notifyEmail); ?>" style="color:#2E7D32;"><?php echo h($notifyEmail); ?></a>.
        </div>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
