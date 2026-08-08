<?php
/**
 * Reusable bulletproof email CTA button.
 *
 *  $url    (string) — button href
 *  $label  (string) — button text (already escaped by caller)
 *  $color  (string) — background colour, e.g. '#0277BD'
 *  $margin (string) — optional wrapper margin, defaults to '24px 0 8px 0'
 *
 * Outlook Classic (2007-2019, Word rendering engine) ignores line-height and
 * inflates a padded <td>, producing an oversized button. mso-fit-shape-to-text
 * is unreliable there (text wraps), so we give Outlook a VML <v:roundrect> with
 * an explicit pixel width computed from the label length, and every other
 * client a normal padded inline-block <a>. Standard "bulletproof button".
 */
$margin = $margin ?? '24px 0 8px 0';

// Outlook needs an explicit VML width. We derive it from the label so it scales
// to any dynamic label length. The estimate is deliberately generous (~8px per
// char at 13px bold Arial + 52px side padding) so the text never wraps or clips
// — a slightly wide short label is fine. A 120px floor keeps tiny labels looking
// like buttons.
$labelText = trim(strip_tags(html_entity_decode((string)$label, ENT_QUOTES)));
$btnWidth  = max(120, (int) ceil(mb_strlen($labelText) * 8) + 52);
?>
<table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin:<?php echo h($margin); ?>;border-collapse:collapse;">
    <tr><td align="left">
        <!--[if mso]>
        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo h($url); ?>" style="height:38px;v-text-anchor:middle;width:<?php echo $btnWidth; ?>px;" arcsize="11%" stroke="f" fillcolor="<?php echo h($color); ?>">
            <w:anchorlock/>
            <center style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;mso-text-raise:1px;white-space:nowrap;"><?php echo $label; ?></center>
        </v:roundrect>
        <![endif]-->
        <!--[if !mso]><!-- -->
        <a href="<?php echo h($url); ?>" target="_blank" style="background-color:<?php echo h($color); ?>;border-radius:4px;color:#ffffff;display:inline-block;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:16px;mso-line-height-rule:exactly;font-weight:600;padding:11px 24px;text-decoration:none;white-space:nowrap;-webkit-text-size-adjust:none;"><?php echo $label; ?></a>
        <!--<![endif]-->
    </td></tr>
</table>
