
<style>
.rounded-fild .modal-content .form-group.label-floating label.control-label.popup-field-label.field-user{ line-height: 1.07142857; } 
.rounded-fild .modal-content .form-group.label-floating label.control-label.find-user.fins-new-label { line-height: 1.07142857; }
</style>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4>
                <span style="display:inline-block; overflow: hidden;"><?php echo __('Remove User(s) from'); ?>&nbsp;-&nbsp;</span>
                <span id="header_prj_usr_rmv" class="ellipsis-view max-width-75" style="display: inline-block;"></span>
            </h4>
        </div>
        <div class="modal-body popup-container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-6"></div>
                    <div class="col-lg-6 mtop20">
                        <div class="form-group label-floating mrg0">
                        <label class="control-label mark_mandatory popup-field-label field-user" for="rmname"><?php echo __('Search User');?></label>
                            <?php  echo $this->Form->text('name', ['class' => 'form-control', 'id' => 'rmname', 'maxlength' => '100', 'onkeyup' => "searchListWithInt('searchuserrem',600)", 'placeholder' => '', 'escape' => false]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <span id="popupload2" class="usr-srh" style="display: block; position: absolute; top:40px; left:25px; font-size: 12px;"><?php echo __('Loading'); ?>... <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" title="Loading..." alt="Loading..." /></span>
            <div class="cb"></div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="popup-inner-container" id="inner_prj_usr_rmv">
                        <?php echo __('Loading'); ?>...
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span id="rmvloader" class="ldr-ad-btn" style="display: none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..." title="loading..." />
                </span>
                <span id="rmv_btn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="rmvbtn" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="removeusers()"><?php echo __('Remove'); ?></a></span>
                </span>
                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script>
    function changeUserNotificatioSetting(obj, userid) {
        $(obj).is(':checked') ? setemail(obj, 'on', userid, 'off') : setemail(obj, 'off', userid, 'on');
    }
</script>