<?php echo $this->Form->create(null, array('id' => 'addmilestone')); ?>
<?php echo $this->Form->hidden('user_id', array('id' => 'user_id', 'value' => SES_ID)); ?>
<?php echo $this->Form->hidden('id', array('id' => 'id', 'value' => $milearr['id'] ?? '')); ?>
<?php echo $this->Form->hidden('default_id', array('value' => $mileuniqid ?? '' )); ?>
<div class="modal-body popup-container">
    <center>
        <div id="milestone_err_msg" class="err_msg"></div>
    </center>
    <div class="task-group-popup round_in_dv">
    <div class="form-group label-floating">
        <label class="control-label" for="title"><?php echo __('Name your group'); ?></label>
        <?php echo $this->Form->text('title', array('class' => 'form-control input-lg', 'id' => 'title', 'maxlength' => '100', 'placeholder' => '', 'value' => htmlspecialchars_decode($milearr['title'] ?? ''), 'data-title' => htmlspecialchars_decode($milearr['title'] ?? ''))); ?>

    </div>
    </div>
    <div id="newmileproj" class="pop-dropdown">
        <?php if (!($milearr['id']??'')) { ?>
            <label class="control-label milestn_lebel" style="z-index:9;"><?php echo __('Project'); ?></label>
            <select id="project_id" name="project_id" class="select_mile form-control floating-label remove-dp" placeholder="">
                <?php foreach ($projArr as $prjarr) { ?>
                    <option <?php echo ($projUid == $prjarr['uniq_id']) ? "selected=selected" : ""; ?> value="<?php echo $prjarr['id']; ?>" data-pname="<?php echo $prjarr['name']; ?>" data-puniq="<?php echo $prjarr['uniq_id']; ?>"><?php echo $this->Format->formatText($prjarr['name']); ?></option>
                <?php } ?>
            </select>

        <?php } else { ?>
            <input type="hidden" name="project_id" id="project_id" value="<?php echo $milearr['project_id']; ?>" data-pname="<?php echo $projArr[0]['name']; ?>" data-puniq="<?php echo $projArr[0]['uniq_id']; ?>">
            <div class="form-group label-floating">
                <label class="control-label"><?php echo __('Project'); ?></label>
                <div class="form-control input-lg edit-fld"><?php echo $this->Format->formatText($projArr[0]['name']); ?></div>
            </div>
        <?php } ?>
    </div>
    <div class="field_wrapper nofloat_wrapper form-group-lg is-empty label-floating">
        <?php echo $this->Form->textarea('description', array('id' => 'description', 'class' => 'field_wrapper round_in expand hideoverflow', 'value' => $milearr['description'] ?? '', 'data-desc' => $milearr['description'] ?? '', 'rows' => '1', 'placeholder' => '')); ?>
        <div class="field_placeholder" style="top:8px;left:-6px"><span><?php echo __('Describe your group');?></span></div>

    </div>
    <div class="row">
        <div class="col-lg-12 padlft-non padrht-non">
            <div class="col-lg-4 col-sm-4">
                <div class="field_wrapper nofloat_wrapper form-group-lg mrg0">
                    <label class="control-label" for="txt_MlsEsthr"></label>
                    <?php $estimated_hours = !empty($milearr['estimated_hours']) ? $milearr['estimated_hours'] : ""; ?>
                    <?php echo $this->Form->text('estimated_hours', array('data-eshr' => $estimated_hours, 'value' => $estimated_hours, 'class' => 'field_wrapper round_in', 'id' => 'txt_MlsEsthr', 'placeholder' => 'hh', 'maxlength' => '6', 'onkeypress' => 'return numericDecimalProj(event)')); ?>
                    <div class="field_placeholder selt_to_lbl"><span><?php echo __('Estimated Hours');?></span></div>
                </div>
            </div>
            <!-- <div class="col-lg-1 col-sm-1"></div> -->
            <?php
            if (!empty($milearr)) {

                if (strtotime($milearr['start_date']) > 0 && $milearr['start_date'] != '1970-01-01') {
                    $milearr['start_date'] = date('M j, Y', strtotime($milearr['start_date']));
                } else if ($milearr['start_date'] == '0000-00-00' || $milearr['start_date'] == '1970-01-01') {
                    $milearr['start_date'] = '';
                } else {
                    $milearr['start_date'] = '';
                }

                if (strtotime($milearr['end_date']) > 0 && $milearr['end_date'] != '1970-01-01') {
                    $milearr['end_date'] = date('M j, Y', strtotime($milearr['end_date']));
                } else if ($milearr['end_date'] == '0000-00-00' || $milearr['end_date'] == '1970-01-01') {
                    $milearr['end_date'] = '';
                } else {
                    $milearr['end_date'] = '';
                }
            }
            ?>
            <div class="col-lg-8 col-sm-8">
                <div class="col-lg-6 col-sm-6 padlft-non">
                    <div class="field_wrapper nofloat_wrapper form-group-lg mrg0">
                        <label class="control-label" for="start_date"></label>
                        <?php echo $this->Form->text('start_date', ['class' => 'datepicker field_wrapper round_in', 'id' => 'start_date_mil', 'placeholder' => __('Start Date'), 'readonly' => 'readonly', 'value' => $milearr['start_date'] ?? '', 'data-sdate' => $milearr['start_date'] ?? '']); ?>
                        <div class="field_placeholder selt_to_lbl"><span><?php echo __('Start Date');?></span></div>
                    </div>
                </div>
                <!-- <div class="from_to">to</div> -->
                <div class="col-lg-6 col-sm-6 padrht-non">
                    <div class="field_wrapper nofloat_wrapper form-group-lg mrg0">
                        <label class="control-label blank-label" for="end_date"></label>
                        <?php echo $this->Form->text('end_date', ['class' => 'datepicker field_wrapper round_in', 'id' => 'end_date_mil', 'placeholder' => __('End Date'), 'readonly' => 'readonly', 'value' => $milearr['end_date'] ?? '', 'data-edate' => $milearr['end_date'] ?? '']); ?>
                        <div class="field_placeholder selt_to_lbl"><span><?php echo __('End Date');?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="fr popup-btn">
        <span id="ldr" style="display:none;"><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></span>
        <span id="btn_mlstn">
            <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
            <span class="hover-pop-btn"><a href="javascript:void(0)" id="btn-add-new-milestone" onclick="return validatemilestone();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size loginactive"><?php echo (!empty($edit)) ? __("Update") : __("Save"); ?></a></span>
        </span>
    </div>
    <div class="cb"></div>
</div>



<input type="hidden" value="<?php echo $mlstfrom?? ''; ?>" id="milestone_crted_from" />
<?php echo $this->Form->end(); ?>
<script>
    $(function() {
        $("#end_date_mil").datepicker({
            format: 'M d, yyyy',
            todayHighlight: true,
            changeMonth: false,
            changeYear: false,
            startDate: 0,
            hideIfNoPrevNext: true,
            autoclose: true
        }).on("changeDate", function() {
            var dateText = $("#end_date_mil").datepicker('getFormattedDate');
            $("#start_date_mil").datepicker("setEndDate", dateText);
        });
        $("#start_date_mil").datepicker({
            format: 'M d, yyyy',
            todayHighlight: true,
            changeMonth: false,
            changeYear: false,
            startDate: 0,
            hideIfNoPrevNext: true,
            autoclose: true
        }).on("changeDate", function() {
            var dateText = $("#start_date_mil").datepicker('getFormattedDate');
            $("#end_date_mil").datepicker("setStartDate", dateText);
        });
        $('#title').val().trim() != '' ? $("#btn-add-new-milestone").removeClass('loginactive') : $("#btn-add-new-milestone").addClass('loginactive');
        $('#title').keyup(function() {
            $('#title').val().trim() != '' ? $("#btn-add-new-milestone").removeClass('loginactive') : $("#btn-add-new-milestone").addClass('loginactive');
            $('#milestone_err_msg').html('');
        });
        $('#milestone_err_msg').html('');
    });

    function validatemilestone() {
        $('#milestone_err_msg').html('');
        $("#btn-add-new-milestone").removeClass('loginactive')
        var title = $('#title').val().trim();
        var start_date = $('#start_date_mil').val().trim();
        var end_date = $('#end_date_mil').val().trim();
        var project_id = $('#project_id').val();
        var desc = $('#description').val().trim();
        var est_hr = $('#txt_MlsEsthr').val().trim();
        var any_changes = 1;
        if ($('#id').val()) {
            var data_title = $('#title').attr('data-title').trim();
            var data_start_date = $('#start_date_mil').attr('data-sdate').trim();
            var data_end_date = $('#end_date_mil').attr('data-edate').trim();
            var data_desc = $('#description').attr('data-desc');
            var data_est_hr = $('#txt_MlsEsthr').attr('data-eshr').trim();
            if ((title == data_title) && (data_start_date == start_date) && (data_end_date == end_date) && (data_desc == desc) && (data_est_hr == est_hr)) {
                any_changes = 0;
            }
        }
        if ($('#id').val()) {
            var proj_uniq = $('#project_id').attr('data-puniq');
            var proj_name = $('#project_id').attr('data-pname');
        } else {
            var proj_uniq = $('#project_id option[value="' + project_id + '"]').attr('data-puniq');
            var proj_name = $('#project_id option[value="' + project_id + '"]').attr('data-pname');
        }

        var errMsg;
        var done = 1;

        if (project_id.trim() == "") {
            errMsg = "<?php echo __('Project cannot be left blank'); ?>!";
            $('#project_id').focus();
            done = 0;

        } else if (title == "" || title.toLowerCase() == 'default task group') {
            errMsg = "<?php echo __('Task group name cannot be left blank'); ?>!";
            if (title.toLowerCase() == 'default task group') {
                errMsg = "<?php echo __('Task group name'); ?> '" + title + "' <?php echo __('already exists'); ?>!";
            }
            $('#title').focus();
            done = 0;
        } else if ((start_date.trim() != "" && end_date.trim() == "") || (start_date.trim() == "" && end_date.trim() != "")) {
            if (start_date.trim() == "") {
                errMsg = "<?php echo __('Start Date cannot be left blank'); ?>!";
                $('#start_date_mil').focus();
            } else {
                errMsg = "<?php echo __('End Date cannot be left blank'); ?>!";
                $('#end_date_mil').focus();
            }
            done = 0;
        } else if ((start_date.trim() != "" && end_date.trim() != "") && (Date.parse(start_date) > Date.parse(end_date))) {
            errMsg = "<?php echo __('Start Date cannot exceed End Date'); ?>!";
            $('#end_date_mil').focus();
            done = 0;
        }
        if (done == 0) {
            $("#btn-add-new-milestone").addClass('loginactive');
        }
        if ($("#btn-add-new-milestone").hasClass('loginactive')) {
            done = 0;
        }
        if (done == 0) {
            showTopErrSucc('error', errMsg);
            return false;
        } else {
            var mdata = $('#addmilestone').serialize();
            var url = HTTP_ROOT + 'milestones/ajax_new_milestone';
            $('#inner_mlstn #btn_mlstn').hide();
            $('#inner_mlstn #ldr').show();
            $.ajax({
                type: "POST",
                url: url,
                data: mdata,
                dataType: "json",
                success: function(res) {
                    if (res.error) {
                        showTopErrSucc('error', res.msg);
                        $('#inner_mlstn #btn_mlstn').show();
                        $('#inner_mlstn #ldr').hide();
                        return false;
                    } else if (res.success) {
                        if (any_changes == 1) {
                            showTopErrSucc('success', res.msg);
                        }
                    }

                    if (typeof CONTROLLER !== 'undefined' && CONTROLLER === 'taskviews') {
                        $('#inner_mlstn #btn_mlstn').show();
                        $('#inner_mlstn #ldr').hide();
                        if (typeof window.taskViewsRefresh === 'function') {
                            window.taskViewsRefresh();
                        }
                        closePopup();
                        return false;
                    }

                    var match = window.location.href.replace('#/', '#').match(/#(.*)$/);
                    match = match ? match[1] : '';
                    if (match == 'overview') {
                        $('#inner_mlstn #btn_mlstn').show();
                        $('#inner_mlstn #ldr').hide();
                        location.reload();
                        return false;
                    }
                    if (match == 'taskgroups') {
                        $('#inner_mlstn #btn_mlstn').show();
                        $('#inner_mlstn #ldr').hide();
                        showTaskGroupsList();
                        closePopup();
                        return false;
                    }

                    $('#mlstPage').val(1);
                    if ($('#milestone_crted_from').val() == 'createTask') {
                        milstoneonTask($('#title').val(), res.milestone_id);
                    } else if ($('#caseMenuFilters').val() == 'milestone') {
                        if ($('#id').val()) {
                            updateAllProj('proj_' + proj_uniq, proj_uniq, 'dashboard', '0', proj_name);
                        } else {
                            $('#mlsttabvalue').val(1);
                            updateAllProj('proj_' + proj_uniq, proj_uniq, 'dashboard', '0', proj_name);
                        }
                    } else if ($('#caseMenuFilters').val() == 'milestonelist') {
                        updateAllProj('proj_' + proj_uniq, proj_uniq, 'dashboard', '0', proj_name);
                    } else if ($('#caseMenuFilters').val() == 'kanban') {
                        <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT) {  ?>
                            easycase.refreshTaskList();
                        <?php } else { ?>
                            easycase.showKanbanTaskList();
                        <?php } ?>
                    } else {
                        var dflt = '';
                        if($('#MilestoneDefaultId').length > 0){
                            dflt = $('#MilestoneDefaultId').val().trim();
                        }
                        if (($('#inline_milestone').length || (dflt == 'default')) && $('#caseMenuFilters').val().trim() != 'timelog' && $('#caseMenuFilters').val().trim() != 'activities' && $('#caseMenuFilters').val().trim() != 'files') {
                            if (res.success) {
                                if (dflt == 'default' || dflt == '0') {
                                    if (dflt == 'default') {
                                        $('#miview_default').text(title.trim());
                                    }
                                    easycase.refreshTaskList();
                                } else {
                                    var id = $('#id').val();
                                    $('#miview_' + id).text(title.trim());
                                    $('#tg_spn_est_id' + id).text($('#txt_MlsEsthr').val());
                                    $('#tg_spn_st_id' + id).text($('#start_date_mil').val());
                                    $('#tg_spn_ed_id' + id).text($('#end_date_mil').val());
                                }
                            }
                        } else {
                            if (PAGE_NAME != 'dashboard' && PAGE_NAME != 'invoice') {
                                <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT) { ?>
                                    window.location.href = HTTP_ROOT + 'dashboard#tasks';
                                <?php } else { ?>
                                    window.location.href = HTTP_ROOT + 'dashboard#milestonelist';
                                <?php  } ?>

                            } else {
                                if (dflt == '0') {
                                    groupby('milestone');
                                } else {
                                    <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT) { ?>
                                        window.location.href = HTTP_ROOT + 'dashboard#tasks';
                                    <?php  } else { ?>
                                        window.location.href = HTTP_ROOT + 'dashboard#milestonelist';
                                    <?php } ?>

                                }
                            }
                        }
                    }
                    $('#inner_mlstn #btn_mlstn').show();
                    $('#inner_mlstn #ldr').hide();
                    closePopup();
                }
            });
        }
    }
</script>
