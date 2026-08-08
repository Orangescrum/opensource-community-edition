<?php
/**
 * Project added email fragment. Uses shared layout header/footer.
 * Expects $to_name, $from_name, $projName, $company_name, $multiple, $projUniqId
 */
$button = empty($multiple) ? __('Open project') : __('Go to Projects');
?>
    <div style="padding:20px 24px; background:#0277BD; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('Added to a project'); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0;"><?php echo __('Dear'); ?> <?php echo h($to_name); ?>,</div>
        <div style="margin:0 0 16px 0; line-height:1.5;">
            <strong><?php echo h(ucfirst($from_name)); ?></strong>
            <?php echo __('added you to project'); ?>
            <strong><?php echo h($projName); ?></strong>
            <?php echo __('on'); ?>
            <?php echo h(ucfirst($company_name)); ?>.
        </div>

        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Project'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($projName); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Added by'); ?></td>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h(ucfirst($from_name)); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Company'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h(ucfirst($company_name)); ?></td>
            </tr>
        </table>

<?php echo $this->element('email/cta_button', [
            'url' => HTTP_ROOT . 'users/login/?project=' . $projUniqId,
            'label' => h($button),
            'color' => '#0277BD',
        ]); ?>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
