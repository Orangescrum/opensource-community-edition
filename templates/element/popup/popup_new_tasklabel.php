<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 class="add_task_temp_name ellipsis-view" style="max-width: 85%;"><?php echo __('Add Label');?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="row" style="margin-top:15px;margin-bottom:15px;">
                <div class="col-lg-12 col-sm-12 col-xs-12 select2__wrapper">
                    <div class="form-group label-floating">
                        <input type="hidden" class="form-control" name="data[Label][labeltype]" id="new-labeltype"/>
                        <input type="hidden" class="form-control" name="data[Label][proj_id]" id="new-taskprojid"/>
                        <input type="hidden" class="form-control" name="data[Label][proj_uid]" id="new-taskprojuid"/>
                        <input type="hidden" class="form-control" name="data[Label][task_id]" id="new-tasklabelid" />
                        <input type="hidden" class="form-control" name="data[Label][task_uniqid]" id="new-taskuniqid" />
                        <select class="popup-label-to-select form-control floating-label" placeholder="<?php echo __('Label');?>" multiple="multiple">
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="modal-footer ">
            <div class="fr popup-btn">
                <span id="addlabelder" class="addlnkder fr" style="display: none;"><img src="<?php echo HTTP_ROOT;?>img/images/case_loader2.gif" alt="loading..." title="loading..."> </span>
                <div id="addlabel_btn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="savlnk" onclick="saveTaskLabel();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Save');?></a></span>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
</div>
