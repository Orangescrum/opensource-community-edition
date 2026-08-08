<div class="modal-body popup-container">
    <div class="scrl-ovr">
        <table cellpadding="0" cellspacing="0" class="table table-striped table-hover">
            <tr class="hdr_tr">
                <th></th>
                <th style="width:60%;"><?php echo __('Task Group/Sprint'); ?></th>
                <th><?php echo __('Start Date'); ?></th>
                <th><?php echo __('End Date'); ?></th>
            </tr>
            <?php
            $caseCount = count($milestones);
            $classes = ["row_col", "row_col_alt"];
            if ($caseCount) {
                foreach ($milestones as  $count => $milestone) {
                    $mlstAutoId = $milestone['id'];
                    $class = $classes[$count % 2 == 0];
            ?>
                    <tr id="mvtask_listings<?php echo $count; ?>" class="rw-cls <?php echo $class; ?>">
                        <td align="left">
                            <div class="radio radio-primary mrg0">
                                <label>
                                    <input type="radio" class="radio_cur ad-mlstn" id="actradio<?php echo $count; ?>" value="<?php echo $mlstAutoId; ?>" data-sprint-status="<?php echo $milestone['is_started']; ?>" name="milestone_radio" <?php if ($mlstid == $mlstAutoId && $mlstAutoId != 0 || $caseCount == 1) { ?>checked='true' <?php } ?> />
                                </label>
                            </div>
                            <input type="hidden" id="mvtask_actionClss<?php echo $count; ?>" value="0" />
                        </td>
                        <td>
                            <label for="actradio<?php echo $count; ?>" class="ad_cs mv_tsk_mlstn" title="<?php echo $milestone['milestoneTitle']; ?>">
                                <?php echo $milestone['milestoneTitle']; ?></label>
                        </td>
                        <td> <?php echo $milestone['start_date']; ?></td>
                        <td> <?php echo $milestone['end_date']; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr valign="middle">
                    <td colspan="7" align="center">
                        <center class="fnt_clr_rd"><?php echo __('No Task Group(s) available'); ?>.</center>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <input type="hidden" id="mvtask_project_id" value="<?php echo $project_id; ?>" />
        <input type="hidden" id="mvtask_proj_name" value="<?php echo $mvtask_proj_name; ?>" />
        <input type="hidden" id="ext_mlst_id" value="<?php echo $mlstid ?? ''; ?>" />
        <input type="hidden" id="mvtask_id" value="<?php echo $mlst_id ?? ''; ?>" />
        <input type="hidden" id="mvtask_task_no" value="<?php echo $task_no; ?>" />
        <input type="hidden" id="mvtask_cnt" value="<?php echo $caseCount; ?>" />
    </div>
</div>

<div class="modal-footer add-mlstn-btn" style="display: none;">
    <div class="fr popup-btn">
        <span id="tskloader" style="display: none;">
            <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..." title="loading..." />
        </span>
        <div id="mvtask_confirmbtn" style="display:block;">
            <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup('mvtask');"><?php echo __('Cancel'); ?></button></span>
            <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="mvtask_movebtn" onclick="switchTaskToMilestone(this);" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Move Task'); ?></a></span>
            <div class="cb"></div>
        </div>
    </div>
    <div class="cb"></div>
</div>