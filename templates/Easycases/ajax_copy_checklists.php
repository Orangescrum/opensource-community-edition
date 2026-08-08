<div class="modal-body popup-container">
    <div class="data-scroll user_pdt">
        <h4><span><?php echo __("Select Task To Copy Cheklists Of The Chosen Task"); ?></span></h4>
        <div class="mtop15">
            <div class="custom-task-fld assign-to-fld labl-rt add_new_opt select_placeholder customize-plshdr">
                <select name="select_task_to_checklist[]" id="select_task_to_checklist" class="select form-control movetoproj" multiple="multiple" data-dynamic-opts=true onchange="rmverrmsg();">
                    <option value="" disabled><?php echo __('Select Task'); ?></option>
                    <?php foreach ($all_task as $k => $v) {
                        if ($v['id'] == $old_case_id) {
                            unset($all_task[$k]);
                    ?>
                        <?php } else { ?>
                            <option value="<?php echo $v['id']; ?>"><?php echo "#" . $v['case_no'] . ': ' . ucwords($this->Format->shortLength($v['title'], 75)); ?></option>
                    <?php }
                    }  ?>
                </select>
            </div>
        </div>
        <input type="hidden" id="old_case_id" value="<?php echo $old_case_id ?>" />
        <input type="hidden" id="project_id_chk" value="<?php echo $project_id ?>" />
    </div>
</div>
<div class="modal-footer ">
    <div class="fr popup-btn">
        <span id="mvprjloader" class="mvprjlder fr" style="display: none;"><img src="<?= $this->Url->build('/img/images/case_loader2.gif') ?>" alt="loading..." title="<?php echo __('loading'); ?>..." /> </span>
        <div id="mvprj_btn">
            <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
            <span class="fl hover-pop-btn"><a href="javascript:void(0)" data-project-id="<?php echo $project_id ?>" data-old-caseid="<?php echo $old_case_id ?>" id="cchecklist" onclick="cpyCheckList(this);" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Copy'); ?></a></span>
            <div class="cb"></div>
        </div>
    </div>
</div>