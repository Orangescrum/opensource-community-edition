<?php
/**
 * Workflow action email fragment.
 * Uses shared layout header/footer. Expects `$message_text` (plain text).
 */
?>
    <div style="padding:20px 24px; background:#455A64; color:#ffffff;">
        <h1 style="margin:0; font-size:18px; font-weight:600;"><?php echo __('Workflow update'); ?></h1>
    </div>
    <div style="padding:24px;">
        <div style="padding:14px 16px; background:#fafbfc; border-left:3px solid #455A64; border-radius:4px; font-size:13px; line-height:1.6;">
            <?php echo nl2br(h($message_text)); ?>
        </div>

        <div style="margin-top:32px; padding-top:16px; border-top:1px solid #eef1f5; font-size:13px; color:#1A1A2E;">
            <?php echo __('Thanks & Regards'); ?>,<br/>
            <strong><?php echo __('The Orangescrum Team'); ?></strong>
        </div>
    </div>
