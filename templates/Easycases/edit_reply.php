<table cellpadding="0" cellspacing="0" class="edit_rep_768 col-lg-12">
    <tr>
        <td>
            <textarea name="edit_reply_txtbox<?php echo $case_info['id']; ?>"
                id="edit_reply_txtbox<?php echo $case_info['id']; ?>" rows="3"
                class="reply_txt_ipad col-lg-12">
            </textarea>
        </td>
    </tr>
    <tr>
        <td align="right">
            <div id="edit_btn<?php echo $case_info['id']; ?>" class="fr mtop10">
                <button type="reset" id="comment_cancel_button" class="btn btn_grey cmn_size"
                    onclick="<?php if ($reply_flag) { ?>cancel_editor_reply<?php } else { ?>cancel_editor<?php } ?>(<?php echo $case_info['id']; ?>);"><i
                        class="icon-big-cross"></i><?php echo __('Cancel'); ?></button>
                <button type="button" value="Save" class="btn btn_blue btn-raised common-submit-btn cmn_size"
                    onclick="<?php if ($reply_flag) { ?>save_editedvalue_reply<?php } else { ?>save_editedvalue<?php } ?>(<?php echo $case_info['case_no']; ?>,<?php echo $case_info['id']; ?>,<?php echo $proj_id; ?>,'<?php echo $case_info['uniq_id']; ?>');"><i
                        class="icon-big-tick"></i><?php echo __('Update'); ?></button>
            </div>
            <div id="edit_loader<?php echo $case_info['id']; ?>" class="fr loading" style="display:none;margin:6px;"
                title="<?php echo __('Loading'); ?>"></div>
        </td>
    </tr>
</table>