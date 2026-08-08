<?php
/**
 * Shell for the `email_header` layout template — Outlook-safe.
 */
$bg = $background_color ?? '#FFFFFF';
$logoUrl = $logo_url ?? '';
$logoAlt = $logo_alt ?? '';
$homeUrl = $home_url ?? '';
if ($logoUrl === '') { return; }
$bg_attr = htmlspecialchars($bg, ENT_QUOTES);
$logo_attr = htmlspecialchars($logoUrl, ENT_QUOTES);
$alt_attr = htmlspecialchars($logoAlt, ENT_QUOTES);
$home_attr = htmlspecialchars($homeUrl, ENT_QUOTES);
?>
<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="<?= $bg_attr ?>" style="background-color:<?= $bg_attr ?>;">
    <tr>
        <td align="center" style="padding:18px 24px 14px 24px;">
            <?php if ($homeUrl !== ''): ?>
            <a href="<?= $home_attr ?>" style="text-decoration:none;border:0;">
                <img src="<?= $logo_attr ?>" alt="<?= $alt_attr ?>" width="44" height="44" border="0" style="display:block;width:44px;height:44px;border:0;outline:none;text-decoration:none;" />
            </a>
            <?php else: ?>
            <img src="<?= $logo_attr ?>" alt="<?= $alt_attr ?>" width="44" height="44" border="0" style="display:block;width:44px;height:44px;border:0;outline:none;text-decoration:none;" />
            <?php endif; ?>
        </td>
    </tr>
</table>
