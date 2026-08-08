<?php
/**
 * Task / defect activity shell — Outlook-safe rebuild.
 */
$accent_color = $accent_color ?? '#0277BD';
$heading = $heading ?? '';
$greeting = $greeting ?? '';
$intro = $intro ?? '';
$comment_label = $comment_label ?? '';
$cta_label = $cta_label ?? '';
$cta_url = $cta_url ?? '';
$signoff = $signoff ?? '';
$footer_note = $footer_note ?? '';
$row_task_title = $case_title ?? '';
$row_task_no = $case_no ?? '';
$row_project = $projName ?? '';
$row_type = $cseTyp ?? '';
$row_priority = $priRity ?? '';
$row_defect_title = $defectTitle ?? '';
$row_defect_code = $defectCode ?? '';
$row_severity = $severity ?? '';
$row_priority2 = $priority ?? '';
$respond_body = $respond ?? ($defectDescription ?? ($activity ?? ''));
$task_link = $cta_url;
$status_badge_html = trim((string)($statusBadge ?? ''));
$attachments_html = trim((string)($attachments ?? ''));
$assignment_line_html = trim((string)($assignmentLine ?? ''));
$common_header_html = trim((string)($common_header_html ?? ''));
$common_footer_html = trim((string)($common_footer_html ?? ''));
$test_banner_html = trim((string)($test_banner_html ?? ''));
$light_accent = '#f5f7fa';
$accent_attr = htmlspecialchars($accent_color, ENT_QUOTES);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= strip_tags($heading) ?: 'Task update' ?></title>
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
</head>
<body>
<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#f4f6f9" style="background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#1A1A2E;">
    <tr><td align="center" style="padding:24px 12px;font-family:Arial,Helvetica,sans-serif;color:#1A1A2E;">
        <?php if ($test_banner_html !== ''): ?><?= $test_banner_html ?><?php endif; ?>
        <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="600" bgcolor="#ffffff" style="width:600px;max-width:600px;background-color:#ffffff;border:1px solid #e5e7eb;">
            <?php if ($common_header_html !== ''): ?><tr><td><?= $common_header_html ?></td></tr><?php endif; ?>
            <?php if ($heading !== ''): ?>
            <tr><td bgcolor="<?= $accent_attr ?>" style="background-color:<?= $accent_attr ?>;padding:20px 24px;color:#ffffff;font-size:18px;font-weight:600;line-height:1.3;font-family:Arial,Helvetica,sans-serif;">
                <?= $heading ?>
            </td></tr>
            <?php endif; ?>
            <tr><td style="padding:24px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#1A1A2E;">
                <?php if ($greeting !== ''): ?><div style="margin:0 0 16px 0;"><?= $greeting ?></div><?php endif; ?>
                <?php if ($intro !== ''): ?><div style="margin:0 0 16px 0;line-height:1.5;"><?= nl2br($intro, false) ?></div><?php endif; ?>

                <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin:16px 0;border-collapse:collapse;">
                    <?php
                    $rows = array_filter([
                        'Task'     => $row_task_title,
                        'Task #'   => $row_task_no,
                        'Defect'   => $row_defect_title,
                        'Defect #' => $row_defect_code,
                        'Project'  => $row_project,
                        'Type'     => $row_type,
                        'Priority' => $row_priority ?: $row_priority2,
                        'Severity' => $row_severity,
                    ], fn ($v) => $v !== '' && $v !== null);
                    $i = 0;
                    foreach ($rows as $label => $value):
                        $bg = $i % 2 === 0 ? $light_accent : '#ffffff';
                        $bg_attr = htmlspecialchars($bg, ENT_QUOTES);
                        $is_link = $label === 'Task' && $task_link !== '';
                    ?>
                    <tr>
                        <td bgcolor="<?= $bg_attr ?>" style="background-color:<?= $bg_attr ?>;padding:8px 12px;border-bottom:1px solid #eef1f5;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#5A6474;width:35%;"><?= htmlspecialchars((string)$label, ENT_QUOTES) ?></td>
                        <td bgcolor="<?= $bg_attr ?>" style="background-color:<?= $bg_attr ?>;padding:8px 12px;border-bottom:1px solid #eef1f5;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1A1A2E;">
                            <?php if ($is_link): ?>
                                <a href="<?= htmlspecialchars($task_link, ENT_QUOTES) ?>" style="color:<?= $accent_attr ?>;text-decoration:none;"><?= $value ?></a>
                            <?php else: ?>
                                <?= $value ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $i++; endforeach; ?>
                </table>

                <?php if ($status_badge_html !== ''): ?>
                <div style="margin:12px 0;font-size:13px;color:#1A1A2E;"><?= $status_badge_html ?></div>
                <?php endif; ?>

                <?php if (!empty($respond_body)): ?>
                <?php if ($comment_label !== ''): ?><div style="margin:16px 0 6px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#5A6474;font-weight:600;"><?= $comment_label ?></div><?php endif; ?>
                <?php
                $respond_source = (string)$respond_body;
                $respond_html = $respond_source !== strip_tags($respond_source)
                    ? $respond_source
                    : nl2br($respond_source, false);
                $respond_html = preg_replace_callback(
                    '/<(p|h[1-6]|ul|ol|li|blockquote|div)\b([^>]*)>/i',
                    static function ($m) {
                        if (preg_match('/\sstyle\s*=\s*"([^"]*)"/i', $m[2])) {
                            return '<' . $m[1] . preg_replace('/\sstyle\s*=\s*"([^"]*)"/i', ' style="$1;margin:0"', $m[2], 1) . '>';
                        }

                        return '<' . $m[1] . $m[2] . ' style="margin:0">';
                    },
                    $respond_html
                ) ?? $respond_html;
                ?>
                <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin:8px 0;">
                    <tr><td bgcolor="#fafbfc" style="background-color:#fafbfc;border-left:3px solid <?= $accent_attr ?>;padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.55;color:#1A1A2E;">
                        <?= $respond_html ?>
                    </td></tr>
                </table>
                <?php endif; ?>

                <?php if ($attachments_html !== ''): ?>
                <div style="margin:12px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#5A6474;"><strong style="color:#1A1A2E;">Attachments:</strong> <?= $attachments_html ?></div>
                <?php endif; ?>
                <?php if ($assignment_line_html !== ''): ?>
                <div style="margin:12px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1A1A2E;"><?= $assignment_line_html ?></div>
                <?php endif; ?>

                <?php if ($cta_label !== '' && $cta_url !== ''): ?>
                <?php
                $url = $cta_url;
                $label = $cta_label;
                $color = $accent_color;
                $margin = '24px 0 8px 0';
                include ROOT . DS . 'templates' . DS . 'element' . DS . 'email' . DS . 'cta_button.php';
                ?>
                <?php endif; ?>

                <?php if ($footer_note !== ''): ?><div style="margin:0 0 16px 0;line-height:1.5;"><?= $footer_note ?></div><?php endif; ?>
                <?php if ($signoff !== ''): ?>
                <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:32px;border-top:1px solid #eef1f5;">
                    <tr><td style="padding-top:16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1A1A2E;line-height:1.5;">
                        <?= $signoff ?>
                    </td></tr>
                </table>
                <?php endif; ?>
            </td></tr>
            <?php if ($common_footer_html !== ''): ?><tr><td><?= $common_footer_html ?></td></tr><?php endif; ?>
        </table>
    </td></tr>
</table>
</body>
</html>
