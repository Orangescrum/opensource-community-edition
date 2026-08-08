<?php
/**
 * @var \App\View\AppView $this
 *
 * Wraps file-template emails (i.e. the fallback path when a template has no
 * manifest shell, or the override pipeline failed and we fell back to file
 * rendering) with the per-company common header / footer configured under
 * Common Settings. Shells already do this themselves; this layout brings the
 * legacy file-template path to parity so admins see consistent behavior.
 */
$companyId = defined('SES_COMP') ? (int)SES_COMP : null;
$commonHeader = '';
$commonFooter = '';
if (class_exists(\EmailTemplating\Service\ShellRenderer::class)) {
    // ShellRenderer composes "[custom_html] + [canned element]" for header
    // and "[canned element] + [custom_html]" for footer, honoring the
    // include_header / include_footer toggles.
    $commonHeader = \EmailTemplating\Service\ShellRenderer::commonHeaderHtml($companyId);
    $commonFooter = \EmailTemplating\Service\ShellRenderer::commonFooterHtml($companyId);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $this->fetch('title'); ?></title>
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
</head>
<body style="width:100%; margin:0; padding:0; -webkit-text-size-adjust:none; -ms-text-size-adjust:none; background-color:#F4F6F9; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color:#1A1A2E;">
<table cellpadding="0" cellspacing="0" border="0" id="backgroundTable" style="height:auto !important; margin:0; padding:0; width:100% !important; background-color:#F4F6F9; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:14px; line-height:1.5; color:#1A1A2E;">
    <tr>
        <td style="padding:24px 12px;">
            <div id="tablewrap" style="width:100% !important; max-width:560px !important; text-align:center; margin:0 auto;">
                <table id="contenttable" width="560" align="center" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF; margin:0 auto; text-align:left; border:1px solid #e5e7eb; border-radius:8px; width:100% !important; max-width:560px !important; overflow:hidden;">
                    <?php if (trim($commonHeader) !== ''): ?>
                    <tr><td><?php echo $commonHeader; ?></td></tr>
                    <?php endif; ?>
                    <tr>
                        <td width="100%" style="text-align:left;">
                            <?php echo $this->fetch('content'); ?>
                        </td>
                    </tr>
                    <?php if (trim($commonFooter) !== ''): ?>
                    <tr><td><?php echo $commonFooter; ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
