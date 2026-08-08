<?php
/**
 * Forgot password (v4) email fragment.
 * Uses shared layout header/footer; expects $name, $resetUrl, $expiresIn, $companyName (optional).
 */
$companyName = $companyName ?? \Cake\Core\Configure::read('App.name', 'Orangescrum');
?>
    <div style="padding:20px 24px; background:#FB8C00; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('Reset your password'); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0;"><?php echo __('Dear'); ?> <?php echo h($name); ?>,</div>
        <div style="margin:0 0 16px 0; line-height:1.5;">
            <?php echo __('We received a password reset request for your {0} account. Click the button below to set a new password.', h($companyName)); ?>
        </div>

<?php echo $this->element('email/cta_button', [
            'url' => $resetUrl,
            'label' => __('Reset My Password'),
            'color' => '#FB8C00',
        ]); ?>

        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Link valid for'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo __('{0} minutes', h($expiresIn)); ?></td>
            </tr>
        </table>

        <div style="margin:0 0 12px 0; line-height:1.5; font-size:12px; color:#5A6474;">
            <?php echo __('If the button above doesn\'t work, you can copy and paste this link into your browser:'); ?><br/>
            <a href="<?php echo h($resetUrl); ?>" style="color:#FB8C00; word-break:break-all;"><?php echo h($resetUrl); ?></a>
        </div>

        <div style="margin:0 0 16px 0; line-height:1.5;">
            <strong><?php echo __('If you did not request this password reset, please ignore this email.'); ?></strong>
        </div>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
