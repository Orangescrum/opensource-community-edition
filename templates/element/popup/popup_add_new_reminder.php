<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 class="add_reminder_temp_name ellipsis-view" style="max-width: 85%;"><?php echo __('Set Reminder');?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="form-group label-floating edit_reminder_dv">
                        <input type="hidden" id="reminder_task_id" value=""  />
                        <input type="hidden" id="reminder_prj_un_id" value=""  />
                        <input type="hidden" id="reminder_un_id" value=""  />
                        <label class="control-label dtlpopup" for="CS_dt_reminder"><?php echo __('Date & Time');?></label>
                        <input class="form-control" id="CS_dt_reminder" type="text"  readonly="readonly" />
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12 select2__wrapper">
                    <div class="form-group label-floating user">
                        <select class="popup-reminder-to-select form-control floating-label" placeholder="<?php echo __('Users');?>" multiple="multiple">
                        </select>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12 select2__wrapper">
                    <div class="form-group label-floating message_reminder_frmgrp">
                        <label class="control-label dtlpopup" for="message_reminder"><?php echo __('Message');?></label>
                        <textarea id="message_reminder" rows="2" style="resize:none" class="form-control" placeholder="<?php echo __('Enter Message');?>...">
							</textarea>
                    </div>
                </div>

                <div class="clearfix"></div>
            </div>
        </div>
        <div class="modal-footer ">
            <div class="fr popup-btn">
			<span id="addreminderder" class="addreminderder fr" style="display: none;">
				<img src="<?php echo HTTP_ROOT;?>img/images/case_loader2.gif" alt="loading..." title="loading...">
			</span>
                <div id="addreminder_btn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="savreminder" onclick="saveReminderPop();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Save');?></a></span>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
</div>
