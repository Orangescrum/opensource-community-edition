<style>
    .control-label{
        padding-bottom : 1px;
    }
</style>

<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 class="add_task_temp_name ellipsis-view" style="max-width: 85%;"><?php echo __('Linking Tasks');?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="row" style="margin-top:15px;margin-bottom:15px;">
                <div class="col-lg-12 col-sm-12 col-xs-12 select2__wrapper">
                    <div class="form-group label-floating">
                        <input type="hidden" id="popup_plnk_id" value=""  />
                        <input type="hidden" id="popup_prj_id" value=""  />
                        <input type="hidden" id="popup_prj_un_id" value=""  />
                        <select class="popup-relates-select form-control floating-label" placeholder="<?php echo __('Relate to');?>">
                            <?php foreach($GLOBALS['RELATES'] as $k=>$v){ ?>
                                <option value="<?php echo $v['id']?>"><?php echo $v['title']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12 select2__wrapper">
                    <div class="form-group label-floating">
                        <select class="popup-link-to-select form-control floating-label" placeholder="<?php echo __('Linking Task');?>" multiple="multiple">
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="modal-footer ">
            <div class="fr popup-btn">
                <span id="addlinkder" class="addlnkder fr" style="display: none;"><img src="<?php echo HTTP_ROOT;?>img/images/case_loader2.gif" alt="loading..." title="loading..."> </span>
                <div id="addlnk_btn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="savlnk" onclick="saveLnk();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Save');?></a></span>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
</div>
