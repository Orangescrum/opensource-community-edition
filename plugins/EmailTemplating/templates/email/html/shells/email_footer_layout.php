<?php
/**
 * Shell for the `email_footer` layout template — Outlook-safe.
 */
$bg = $background_color ?? '#F9FAFB';
$blogUrl = trim((string)($blog_url ?? ''));
$helpUrl = trim((string)($help_url ?? ''));
$aboutUrl = trim((string)($about_url ?? ''));
$twitterUrl = trim((string)($twitter_url ?? ''));
$facebookUrl = trim((string)($facebook_url ?? ''));
$copyrightText = trim((string)($copyright_text ?? ''));
$addressText = trim((string)($address_text ?? ''));
$unsubscribeText = trim((string)($unsubscribe_text ?? ''));
$unsubscribeLinkLabel = trim((string)($unsubscribe_link_label ?? ''));
$unsubscribeLinkUrl = trim((string)($unsubscribe_link_url ?? ''));
$bg_attr = htmlspecialchars($bg, ENT_QUOTES);

$navLinks = array_filter([
    'Blog' => $blogUrl,
    'Help' => $helpUrl,
    'About Us' => $aboutUrl,
], fn ($u) => $u !== '');
$socialLinks = array_filter([
    'Twitter' => $twitterUrl,
    'Facebook' => $facebookUrl,
], fn ($u) => $u !== '');
?>
<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="<?= $bg_attr ?>" style="background-color:<?= $bg_attr ?>;">
    <tr>
        <td align="center" style="padding:16px 24px 14px 24px;font-family:Arial,Helvetica,sans-serif;">
            <?php if (!empty($navLinks)): ?>
            <div style="padding-bottom:10px;font-size:12px;line-height:1.4;">
                <?php $i = 0; foreach ($navLinks as $label => $url): ?>
                    <?php if ($i++ > 0): ?><span style="color:#D1D5DB;">|</span><?php endif; ?>
                    <a style="color:#1565C0;font-weight:500;text-decoration:none;padding:0 8px;" href="<?= htmlspecialchars($url, ENT_QUOTES) ?>"><?= htmlspecialchars($label, ENT_QUOTES) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($socialLinks)): ?>
            <div style="padding-bottom:12px;font-size:12px;line-height:1.4;">
                <?php foreach ($socialLinks as $label => $url): ?>
                    <a style="color:#1565C0;font-weight:500;text-decoration:none;padding:0 8px;" href="<?= htmlspecialchars($url, ENT_QUOTES) ?>"><?= htmlspecialchars($label, ENT_QUOTES) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($copyrightText !== '' || $addressText !== ''): ?>
            <div style="padding-bottom:8px;font-size:11px;line-height:1.5;color:#6B7280;">
                <?php if ($copyrightText !== ''): ?><?= htmlspecialchars($copyrightText, ENT_QUOTES) ?><?php endif; ?>
                <?php if ($copyrightText !== '' && $addressText !== ''): ?><br><?php endif; ?>
                <?php if ($addressText !== ''): ?><?= htmlspecialchars($addressText, ENT_QUOTES) ?><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($unsubscribeText !== '' || ($unsubscribeLinkLabel !== '' && $unsubscribeLinkUrl !== '')): ?>
            <div style="padding-top:6px;font-size:11px;line-height:1.5;color:#8A93A6;">
                <?php if ($unsubscribeText !== ''): ?><?= htmlspecialchars($unsubscribeText, ENT_QUOTES) ?><?php endif; ?>
                <?php if ($unsubscribeText !== '' && $unsubscribeLinkLabel !== '' && $unsubscribeLinkUrl !== ''): ?> <?php endif; ?>
                <?php if ($unsubscribeLinkLabel !== '' && $unsubscribeLinkUrl !== ''): ?>
                <a href="<?= htmlspecialchars($unsubscribeLinkUrl, ENT_QUOTES) ?>" style="color:#1565C0;text-decoration:none;font-weight:500;"><?= htmlspecialchars($unsubscribeLinkLabel, ENT_QUOTES) ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </td>
    </tr>
</table>
