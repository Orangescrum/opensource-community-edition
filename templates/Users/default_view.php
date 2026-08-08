<style>
    .default-view-page .scrum-beta-options { display: flex; flex-direction: column; gap: 12px; }
    .default-view-page .scrum-beta-option {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 14px;
        font-weight: 400;
        color: #4a4a4a;
        cursor: pointer;
    }
    .default-view-page .scrum-beta-option input[type="checkbox"] {
        float: none;
        margin: 0;
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        cursor: pointer;
    }
    .default-view-page .scrum-beta-option span { line-height: 1.4; }
</style>
<div class="setting_wrapper task_listing cmn_tbl_widspace width_hover_tbl default-view-page">
    <div class="row">
        <div class="col-lg-6">
            <?php echo $this->Form->create(null, array('url' => '/users/saveDefaultView', 'onsubmit' => 'return validateDefaultViewForm()', 'name' => 'defaultviewform', 'id' => 'DefaultViewDefaultViewForm')); ?>
            <input name="default_view_id" type="hidden" value="<?php echo $id; ?>" />
            <div class="mtop20">
                <div class="select_field_wrapper up_select_control">
                    <?= $this->Form->control('taskviews', [ 'id' => 'DefaultViewTaskviews', 'type' => 'select', 'class' => 'select form-control floating-label', 'label' => false, 'placeholder' => __("Tasks"), 'options' => $taskViews, 'default' => $taskView, 'value' => $taskView, 'empty' => false, ]); ?>                    
                </div>
            </div>

            <div class="mtop20">
                <div class="select_field_wrapper up_select_control">
                    <?= $this->Form->control('timelogview', [ 'id' => 'DefaultViewTimelogview', 'type' => 'select', 'class' => 'select form-control floating-label', 'label' => false, 'placeholder' => __("Time Log"), 'options' => $timelogViews, 'default' => $timelogView, 'value' => $timelogView, 'empty' => false, ]); ?>                    
                </div>
            </div>

            <div class="mtop20">
                <div class="select_field_wrapper up_select_control">
                    <?= $this->Form->control('projectview', [ 'id' => 'DefaultViewProjectview', 'type' => 'select', 'class' => 'select form-control floating-label', 'label' => false, 'placeholder' => __("Projects"), 'options' => $projectViews, 'default' => $projectView, 'value' => $projectView, 'empty' => false, ]); ?>
                </div>
            </div>

            <div class="mtop20 task_type_filter_block">
                <label class="control-label"><?php echo __('Show task types on Task page'); ?></label>
                <div class="checkbox_group mtop10">
                    <label class="checkbox-inline">
                        <input type="checkbox" disabled checked /> <?php echo __('Task'); ?>
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="show_story" id="show_story" value="1" <?php echo !empty($taskTypes['story']) ? 'checked' : ''; ?> /> <?php echo __('Story'); ?>
                    </label>
                </div>
            </div>

            <div class="mtop20 task_type_filter_block">
                <label class="control-label"><?php echo __('Task Detail Custom Fields View'); ?></label>
                <div class="checkbox_group mtop10">
                    <label class="radio-inline">
                        <input type="radio" name="task_detail_view" value="tab" <?php echo (($taskDetailView ?? 'tab') !== 'side') ? 'checked' : ''; ?> /> <?php echo __('Tab view'); ?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="task_detail_view" value="side" <?php echo (($taskDetailView ?? 'tab') === 'side') ? 'checked' : ''; ?> /> <?php echo __('Side view'); ?>
                    </label>
                </div>
            </div>

            <div class="cb"></div>
            <div class="row">
                <div class=" col-lg-12">
                    <div class="btn_row fr">
                        <div id="defaultView-btns ">
                            <div class="fl"><a class="btn btn-default btn_hover_link cmn_size"
                                    onclick="cancelProfile('<?php echo $referer ?? ''; ?>');"><?php echo __('Cancel'); ?></a>
                            </div>
                            <div class="fl btn-margin">
                                <span id="defaultView-loader" style="display:none">
                                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." />
                                </span>
                                <button type="submit" name="submit_defaultView" id="submit_defaultView"
                                    class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Save'); ?></button>
                            </div>
                            <div class="cb"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $.material.init();
        $("#DefaultViewTaskviews,#DefaultViewTimelogview,#DefaultViewProjectview").select2({
            minimumResultsForSearch: -1
        });

        /*select2 js customize event*/
        $('.up_select_control').click(function() {
            if ($(this).find('span').hasClass("select2-container--open")) {
                $(this).addClass('open_label');
            } else {
                $('.up_select_control').removeClass('open_label');
            }
        });
        /*end*/

        $('#DefaultViewTaskviews').select2();
        $('#DefaultViewTimelogview').select2();
        $('#DefaultViewProjectview').select2();
    });

    function validateDefaultViewForm() {
        $('#submit_defaultView').hide();
        $('#defaultView-loader').show();

        var taskview = $('#DefaultViewTaskviews').val();
        var timelogview = $('#DefaultViewTimelogview').val();
        var projectview = $('#DefaultViewProjectview').val();

        if (!taskview) {
            showTopErrSucc('error', '<?php echo __('Please Select a Task view.'); ?>');
            $('#submit_defaultView').show();
            $('#defaultView-loader').hide();
            return false;
        }

        if (!timelogview) {
            showTopErrSucc('error', '<?php echo __('Please Select a Time Log view.'); ?>');
            $('#submit_defaultView').show();
            $('#defaultView-loader').hide();
            return false;
        }

        if (!projectview) {
            showTopErrSucc('error', '<?php echo __('Please Select a Project view.'); ?>');
            $('#submit_defaultView').show();
            $('#defaultView-loader').hide();
            return false;
        }

        return true;
    }
</script>