<?php
/**
 * Postcase reply email template fragment.
 * Expects variables provided by the caller:
 *  $domain, $case_uniq_id, $case_title, $projName, $case_no, $cseTyp,
 *  $priRity, $msg, $postingName, $emailbody, $respond, $allfiles, $assignTo,
 *  $by_name, $name_email, $caseIstype, $csType
 */
$caseIstype = (int)($caseIstype ?? \App\Model\Table\EasycasesTable::TYPE_POST);
$csType = (string)($csType ?? '');
$isTaskPost = $caseIstype === \App\Model\Table\EasycasesTable::TYPE_POST;
$isComment  = $caseIstype === \App\Model\Table\EasycasesTable::TYPE_COMMENT;

$titleMap = [
    'New'              => __('New task'),
    'Replied'          => __('New comment on a task'),
    'Close'            => __('Task closed'),
    'Resolved'         => __('Task resolved'),
    'WIP'              => __('Task in progress'),
    'Started'          => __('Task started'),
    'Change Assignto'  => __('Task assigned'),
];
if (isset($titleMap[$csType])) {
    $emailHeading = $titleMap[$csType];
} else {
    $emailHeading = $isComment ? __('New comment on a task') : __('Task update');
}

$headerColorMap = [
    'New'              => '#0277BD',
    'Replied'          => '#0277BD',
    'Close'            => '#546E7A',
    'Resolved'         => '#2E7D32',
    'WIP'              => '#F57C00',
    'Started'          => '#1565C0',
    'Change Assignto'  => '#6A1B9A',
];
$headerColor = $headerColorMap[$csType] ?? '#0277BD';
$showReplyHint = $isComment || $csType === 'Replied' || $csType === 'New';
?>
    <div style="padding:20px 24px; background:<?php echo $headerColor; ?>; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;">
            <?php echo h($emailHeading); ?>
        </h1>
    </div>
    <div style="padding:24px;">
        <div style="margin:0 0 16px 0; line-height:1.5;">
            <strong><?php echo h($postingName . ' ' . $emailbody); ?></strong>
        </div>

        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Task'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;">
                    <a href="<?php echo h($domain . 'users/login/?dashboard#details/' . $case_uniq_id); ?>" target="_blank" style="color:<?php echo $headerColor; ?>; text-decoration:none;">
                        <?php echo h(stripslashes($case_title)); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Task'); ?> #</td>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($case_no); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Project'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($projName); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Type'); ?></td>
                <td style="padding:8px 12px; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo h($cseTyp); ?></td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:12px; color:#5A6474; width:35%;"><?php echo __('Priority'); ?></td>
                <td style="padding:8px 12px; background:#f5f7fa; border-bottom:1px solid #eef1f5; font-size:13px;"><?php echo $priRity; ?></td>
            </tr>
        </table>

        <?php if (!empty(trim((string)($msg ?? '')))): ?>
            <div style="margin:12px 0; font-size:13px; color:#1A1A2E;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div style="margin:16px 0; padding:14px 16px; background:#fafbfc; border-left:3px solid #0277BD; font-size:13px; line-height:1.55; color:#1A1A2E;">
            <?php echo htmlspecialchars(stripslashes($respond ?? '')); ?>
        </div>

        <?php if (!empty(trim((string)($allfiles ?? '')))): ?>
            <div style="margin:12px 0; font-size:12px; color:#5A6474;"><?php echo $allfiles; ?></div>
        <?php endif; ?>

        <?php if (!empty($assignTo)): ?>
            <div style="margin:12px 0; font-size:13px; color:#1A1A2E;"><?php echo $assignTo; ?></div>
        <?php endif; ?>

        <?php if ($showReplyHint): ?>
            <div style="margin:20px 0; padding:12px 16px; background:#E3F2FD; border:1px solid #BBDEFB; border-radius:6px; font-size:12px; line-height:1.55; color:#0D47A1;">
                <strong><?php echo __('Just REPLY to this Email the same will be added under the Task'); ?>.</strong>
                <div style="margin-top:4px; font-size:11px; color:#1565C0;">
                    <strong><?php echo __('NOTE'); ?>:</strong> <?php echo __('Do not remove this original message'); ?>.
                </div>
            </div>
        <?php endif; ?>

<?php echo $this->element('email/cta_button', [
            'url' => $domain . 'users/login/dashboard#details/' . $case_uniq_id,
            'label' => __('View task'),
            'color' => '#0277BD',
            'margin' => '20px 0 8px 0',
        ]); ?>

        <div style="margin:16px 0 0 0; font-size:12px; color:#5A6474; line-height:1.5;">
            <?php echo __('This email notification is sent by'); ?> <?php echo h($by_name); ?> <?php echo __('to'); ?> <?php echo h($name_email); ?>.
        </div>

        <div style="margin-top:24px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>

        <div style="margin-top:16px; text-align:center; font-size:11px; color:#8f96a3; line-height:14px;">
            <?php echo sprintf('%s %s', __('Don\'t want these emails?'), __('To unsubscribe, please contact your account administrator to turn off Email notification for you in the project level')); ?>.
        </div>
    </div>
