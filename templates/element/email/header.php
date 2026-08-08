<?php
/**
 * Email header — logo on a clean white bar matching the new design palette.
 * Expects optional `logoUrl`, `baseUrl`, `homeUrl`.
 */
$baseUrl = $baseUrl
    ?? \Cake\Core\Configure::read('App.fullBaseUrl')
    ?? (defined('HTTP_ROOT') ? rtrim(HTTP_ROOT, '/') : \Cake\Routing\Router::fullBaseUrl());
$baseUrl = rtrim((string)$baseUrl, '/');
$logoUrl = $logoUrl ?? $baseUrl . '/img/logo/logo-sm.svg';
$homeUrl = $homeUrl ?? $baseUrl . '/';
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color:#FFFFFF;">
    <tr>
        <td style="padding:18px 24px 14px 24px; text-align:center;">
            <a href="<?php echo h($homeUrl); ?>" style="text-decoration:none; display:inline-block; line-height:0;">
                <img src="<?php echo h($logoUrl); ?>" alt="Orangescrum" title="Orangescrum" width="44" height="44" style="display:inline-block; width:44px; height:44px; border:0;">
            </a>
        </td>
    </tr>
</table>
