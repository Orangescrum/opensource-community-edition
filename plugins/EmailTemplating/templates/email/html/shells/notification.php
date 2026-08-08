<?php
/**
 * Shared "notification" shell — Outlook-safe rebuild.
 *
 * Region values are pre-rendered (tokens substituted upstream); do not escape here.
 */
$accent_color = $accent_color ?? '#1565C0';
$heading = $heading ?? '';
$greeting = $greeting ?? '';
$body = $body ?? '';
$cta_label = $cta_label ?? '';
$cta_url = $cta_url ?? '';
$signoff = $signoff ?? '';
$footer_note = $footer_note ?? '';
$otp_code_value = trim((string)($otpCode ?? ''));
$metadata_rows_html = trim((string)($metadata_rows_html ?? ''));
$common_header_html = trim((string)($common_header_html ?? ''));
$common_footer_html = trim((string)($common_footer_html ?? ''));
$test_banner_html = trim((string)($test_banner_html ?? ''));
$accent_attr = htmlspecialchars($accent_color, ENT_QUOTES);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= strip_tags($heading) ?: 'Notification' ?></title>
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
                <?php if ($body !== ''): ?><div style="margin:0 0 16px 0;line-height:1.5;"><?= nl2br($body, false) ?></div><?php endif; ?>

                <?php if ($metadata_rows_html !== ''): ?>
                <?= $metadata_rows_html ?>
                <?php endif; ?>

                <?php if ($otp_code_value !== ''): ?>
                <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin:16px 0;">
                    <tr><td align="center" bgcolor="#fafbfc" style="background-color:#fafbfc;border:1px dashed #c5cad1;padding:18px 24px;font-family:Consolas,Menlo,monospace;font-size:28px;font-weight:600;letter-spacing:6px;color:<?= $accent_attr ?>;">
                        <?= $otp_code_value ?>
                    </td></tr>
                </table>
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
