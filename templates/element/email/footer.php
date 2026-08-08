<?php
/**
 * Email footer — modern light footer matching the new design palette.
 * Expects optional `homeUrl`, `baseUrl`, `twitterUrl`, `facebookUrl`.
 */
$baseUrl = $baseUrl
    ?? \Cake\Core\Configure::read('App.fullBaseUrl')
    ?? (defined('HTTP_ROOT') ? rtrim(HTTP_ROOT, '/') : \Cake\Routing\Router::fullBaseUrl());
$baseUrl = rtrim((string)$baseUrl, '/');
$homeUrl ??= $baseUrl . '/';
$twitterUrl ??= $baseUrl . '/img/tw.png';
$facebookUrl ??= $baseUrl . '/img/fb.png';
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color:#F9FAFB;">
    <tr>
        <td style="padding:16px 24px 14px 24px; text-align:center; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;">
            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td align="center" style="padding-bottom:10px;">
                        <a style="color:#1565C0; font-size:12px; font-weight:500; text-decoration:none; padding:0 8px;" href="https://blog.orangescrum.com/">Blog</a>
                        <span style="color:#D1D5DB;">|</span>
                        <a style="color:#1565C0; font-size:12px; font-weight:500; text-decoration:none; padding:0 8px;" href="https://www.orangescrum.com/help">Help</a>
                        <span style="color:#D1D5DB;">|</span>
                        <a style="color:#1565C0; font-size:12px; font-weight:500; text-decoration:none; padding:0 8px;" href="https://www.orangescrum.com/about-us">About Us</a>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-bottom:12px;">
                        <a style="text-decoration:none; display:inline-block; margin:0 4px;" href="https://twitter.com/theorangescrum">
                            <img src="<?php echo h($twitterUrl); ?>" alt="Twitter" width="24" height="24" style="width:24px;height:24px;display:inline-block;border:0;vertical-align:middle;">
                        </a>
                        <a style="text-decoration:none; display:inline-block; margin:0 4px;" href="https://www.facebook.com/pages/Orangescrum/170831796411793">
                            <img src="<?php echo h($facebookUrl); ?>" alt="Facebook" width="24" height="24" style="width:24px;height:24px;display:inline-block;border:0;vertical-align:middle;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-bottom:8px; font-size:11px; line-height:1.5; color:#6B7280;">
                        &copy; <?php echo date('Y'); ?> Orangescrum. All rights reserved.<br>
                        2059 Camden Ave. #118, San Jose, CA 95124, USA
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top:6px; font-size:11px; line-height:1.5; color:#8A93A6;">
                        <?php echo __('You received this message because you are an Orangescrum customer.'); ?>
                        <br>
                        <?php echo __('If you would prefer not to receive these emails, you can'); ?>
                        <a href="<?php echo h($homeUrl); ?>" style="color:#1565C0; text-decoration:none; font-weight:500;"><?php echo __('unsubscribe'); ?></a>
                        <?php echo __('at any time.'); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
