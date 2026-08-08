<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 id='new_duedt_change_title'> <?php echo __("Add Reason"); ?></h4>
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
        </div>
        <div class="modal-body  popup-container">
            <div class="mtop15">
                <form name="task_type" id="duedateChangersnFrm" method="post" action="javascript:void(0);" autocomplete="off">
                    <?= $this->Form->control('_csrfToken', ['type' => 'hidden', 'value' => $this->request->getAttribute('csrfToken')]); ?>
                    <div class="col-md-2">
                        <input type="hidden" value="" name="duedt_cnge_rsn_id" id="duedt_cnge_rsn_id" />
                    </div>
                  
                    <div class="field_wrapper">
                    <label class="control-label mark_mandatory duedate-change-label" for="select_role"><?php echo __('Specify your reason');?></label>
                        <input type="text" value=""  name="due_dt_change_rsn" id="due_dt_change_rsn" autocomplete="off"/>
                        <div class="field_placeholder"></div>
                    </div>

                    <div id="duedterr_msg" class="font14 colr_red" style="display:none;"></div>
                    <div class="modal-footer mtop30 mbtm20 p_0">
                        <div class="fr popup-btn">
                            <span class="checkbox custom-checkbox create_another_reason" id="add_another_reason">
                                <label>
                                    <input type="checkbox" id="add_dt_reason" name="add_df_reason" value="1"><?php echo __("Create another"); ?>
                                </label>
                            </span>
                            <span id="duedtbtn">
                                <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                                <span class="hover-pop-btn"><button type="button" value="Add" id="newdfct_btn_duedt_reason" name="crttasktype" onclick="ajaXAddNewDuedtChangeRsn();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Add'); ?></button></span>

                            </span>
                        </div>
                        <span id="dfctloaderseverity" style="display:none;">
                            <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" />
                        </span>
                        <div class="cb"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>