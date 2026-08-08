<?php use Cake\Core\Configure; ?>
<style>
    .create-task-form-main {
        box-sizing: border-box;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-flex: 0;
        -ms-flex: 0 1 auto;
        flex: 0 1 auto;
        -webkit-box-orient: horizontal;
        -webkit-box-direction: normal;
        -ms-flex-direction: row;
        flex-direction: row;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        margin-left: -7.5px;
        margin-right: -7.5px;
    }

    a.reset_split_task_values {
        color: #757575;
        margin-right: 20px;
        margin-left: -30px;
    }

    .split_task_reset_button i.material-icons {
        font-size: 21px;
        margin-top: 14px;
    }

    .create-task-form-main>.w_a {
        width: 33.33%;
        -ms-flex-preferred-size: 33.33%;
        flex-basis: 33.33%;
        box-sizing: border-box;
        padding: 0 7.5px;
        position: relative;
    }

    .create-task-form-main>.w_b {
        width: 66.66%;
        -ms-flex-preferred-size: 66.66%;
        flex-basis: 66.66%;
        box-sizing: border-box;
        padding: 0 7.5px;
        position: relative;
    }

    .create-task-form-main>.w_c {
        width: 100.00%;
        -ms-flex-preferred-size: 100.00%;
        flex-basis: 100.00%;
        box-sizing: border-box;
        padding: 0 7.5px;
        position: relative;
    }

    #showhide_task_conf,
    #showhide_project_conf {
        display: inline-block;
        position: absolute;
        right: 20px;
        top: 12px;
        z-index: 999999;
    }



    #showhide_task_conf a:hover,
    #showhide_project_conf a:hover {
        box-shadow: 0px 5px 10px rgba(218, 219, 220, 0.19)
    }

    #showhide_task_conf .material-icons,
    #showhide_project_conf .material-icons {
        font-size: 20px;
        margin-right: 3px;
    }

    #dropdown_menu_task_configuration,
    #dropdown_menu_project_configuration {
        width: 400px;
        left: initial;
        right: 0;
        top: 45px;
        padding: 5px 0px 5px 5px;
        margin: 0;
    }

    #dropdown_menu_task_configuration li,
    #dropdown_menu_project_configuration li {
        display: inline-block;
        vertical-align: middle;
        width: calc(50% - 5px);
        border-bottom: none;
        padding: 5px 5px 5px 15px
    }

    #dropdown_menu_task_configuration li.li_check_radio:first-child,
    #dropdown_menu_task_configuration li.li_check_radio:nth-child(2),
    #dropdown_menu_project_configuration li.li_check_radio:first-child,
    #dropdown_menu_project_configuration li.li_check_radio:nth-child(2) {
        padding-left: 5px
    }

    .close_config {
        position: absolute;
        right: 10px;
        top: 10px;
        font-size: 18px;
        line-height: 20px;
        color: #ff0000;
        cursor: pointer;
        display: inline-block;
        z-index: 1;
    }

    #dropdown_menu_task_configuration li:hover,
    #dropdown_menu_project_configuration li:hover {
        color: #1A73E8
    }

    #showhide_task_conf .save_configure_btn {
        display: block;
        width: 100%;
        text-align: right;
        padding: 15px 15px 15px
    }

    #showhide_task_conf .save_configure_btn .btn_cmn_efect {
        margin: 0
    }

    .create-task-form .create-task-form-main .labl-rt.custom-task-fld {
        margin-top: 25px;
    }

    .create-task-form-main .mtop15 {
        margin-top: 25px
    }

    .create-task-form-main .row {
        margin: 0 -7.5px
    }

    .create-task-form-main .row .col-md-6,
    .create-task-form-main .row .col-md-4,
    .create-task-form-main .row .col-md-3,
    .create-task-form-main .row .col-md-2 {
        padding: 0 7.5px
    }

    .cmn_create_task_form .field_wrapper {
        margin-bottom: 0
    }

    #tour_crt_recur .custom-checkbox {
        float: none
    }

    .create-task-container .cmn_create_task_form .task-editor-form {
        margin: 10px 0 0;
    }

    .create-task-container .cmn_help_select+.onboard_help_anchor {
        top: -14px;
        right: 10px;
    }

    .left-134 {
        left: -134px;
        padding-top: 3px;
    }

    .crtskmenus.left-134 li {
        padding: 6px;
    }

    .left-5 {
        margin-left: -5px;
    }

    .save_exit_btn.sticly_cta_btn:hover {
        box-shadow: none !important;
    }

    .help-video-pop.inpopup {
        position: absolute;
        right: 160px;
        top: 25px;
        z-index: 99;
        pointer-events: auto;
    }
</style>

<script type="text/javascript" src="<?php echo HTTP_ROOT . 'js/jquery-ui-1.10.3.js'; ?>" defer></script>
<script type="text/javascript">
    $(function () {
        $(".field_wrapper .field_placeholder").on("click", function () {
            $(this).closest(".field_wrapper").find("input").focus();
        });
        $(".field_wrapper input").on("keyup", function () {
            var value = $.trim($(this).val());
            if (value) {
                $(this).closest(".field_wrapper").addClass("hasValue");
            } else {
                $(this).closest(".field_wrapper").removeClass("hasValue");
            }
        });
        $('.crt_popup_close .close').tipsy({
            gravity: 'w',
            fade: true
        });
    });
</script>

<div class="cmn_create_task_form pr create-task">
    <?php if (
        !$this->Format->isAllowed('Change Assigned to', $roleAccess) ||
        !$this->Format->isAllowed('Update Task Duedate', $roleAccess) ||
        !$this->Format->isAllowed('Change Other Details of Task', $roleAccess) ||
        !$this->Format->isAllowed('Move to Milestone', $roleAccess) ||
        !$this->Format->isAllowed('Est Hours', $roleAccess) ||
        !$this->Format->isAllowed('Manual Time Entry', $roleAccess) ||
        !$this->Format->isAllowed('Link Task', $roleAccess) ||
        !$this->Format->isAllowed('Add Label', $roleAccess)
    ) { ?>
        <div class="ur-not-msg"><span><i
                    class="material-icons">error</i><?php echo __("Your admin or owner has not allowed you to update certain task fields. Contact your admin or owner for enhanced permissions."); ?></span>
        </div>
    <?php } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="crt_popup_close">
                <button type="button" class="close close-icon back-btn" title="<?php echo __('Close'); ?>"
                    onclick="crt_popup_close('CT');"><i class="material-icons">&#xE14C;</i></button>
            </div>
            <div class="fl">
                <h4 id="taskheading"><?php echo __('Create Task'); ?></h4>
            </div>
            <span id="showhide_task_conf" class="dropdown">
                <a href="javascript:jsVoid();" title="<?php echo __('Task Configuration'); ?>" onclick=""
                    class="dropdown-toggle" data-toggle="dropdown"><i class="material-icons">visibility_off</i>
                    <?php echo __("Show/Hide"); ?></a>
                <ul class="dropdown-menu drop_menu_mc" id="dropdown_menu_task_configuration">
                    <span class="close_config" onclick="$('#showhide_task_conf').removeClass('open');">&times;</span>
                    <li class="li_check_radio border-bottom">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="selectedcols_all" value="all" id="column_all_fields"
                                    style="cursor:pointer"> <?php echo __("All"); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio border-bottom padbtmset"></li>
                    <?php $taskFileds = Configure::read('TASK_FIELDS');
                    $selectedColumns = explode(',', $defaultfields['form_fields']);
                    foreach ($taskFileds as $k => $v) {
                        if ($v['is_default'] == 0) { ?>
                            <li class="li_check_radio">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="selectedcols configfields" value="<?php echo $v['id']; ?>"
                                            id="column_all_<?php echo $v['id']; ?>" style="cursor:pointer" <?php if (in_array($v['id'], $selectedColumns) || empty($selectedColumns)) { ?>
                                                checked="checked" <?php } ?> onchange="showHideTaskFields(<?php echo $v['id']; ?>)">
                                        <?php echo $v['label']; ?>
                                    </label>
                                </div>
                            </li>
                        <?php }
                    } ?>
                </ul>
            </span>
            <div class="cb"></div>
        </div>
        <div class="cb"></div>
    </div>
    <div class="flex_scroll">
        <div class="row">
            <div class="col-md-12">
                <div class="create-task-form">
                    <form>
                        <div class="create-task-form-main proj_task_ttle_row">
                            <div class="w_a task-field-1 mtop15 task-field-all">
                                <div class="add_task_project select_field_wrapper mark_mandatory">
                                    <select class="prj-select form-control floating-label"
                                        placeholder="<?php echo __('Project'); ?>">
                                        <?php if (is_array($getallproj) && count($getallproj) != 0) {
                                            foreach ($getallproj as $getPrj) { ?>
                                                <option value="<?php echo $getPrj['Project']['uniq_id'] ?? ''; ?>"
                                                    data-methodlogy="<?php echo $getPrj['Project']['project_methodology_id'] ?? ''; ?>"
                                                    <?php echo (isset($ctProjUniq) && $ctProjUniq == ($getPrj['Project']['uniq_id'] ?? '')) ? 'selected' : ''; ?>>
                                                    <?php echo $this->Format->shortLength($getPrj['Project']['name'] ?? '', 27); ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="w_b task-field-2 mtop15">
                                <div class="custom-task-fld title-fld top-tsk-ttl ct-title">
                                    <div class="field_wrapper nofloat_wrapper">
                                        <input id="CS_title" type="text" maxlength='240'
                                            onblur='blur_txt();checkAllProj();' onfocus='focus_txt()'
                                            onkeydown='return onEnterPostCase(event)' onkeyup='checktitle_value();' />
                                        <div class="field_placeholder mark_mandatory">
                                            <span><?php echo __('Task Title'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="data[Easycase][istype]" id="CS_istype_quick" value="1"
                                readonly="true" />
                            <input type="hidden" readonly="readonly" value="<?php echo $projUniq1 ?? ''; ?>"
                                id="curr_active_project_quick" />
                            <div class="col-lg-5 padlft-non custom-task-fld proj-fld-fld labl-rt" style="display:none;">
                                <span class="os_sprite crt-proj-icon"></span>
                            </div>
                            <div id="tour_crt_asign"
                                class="w_a task-field-3 mtop15 task-field-all custom-task-fld assign-to-fld labl-rt add_new_opt <?php if (!$this->Format->isAllowed('Change Assigned to', $roleAccess)) { ?>no-pointer<?php } ?> ">
                                <div class="select_field_wrapper">
                                    <select id="crtskasgnusr_task"
                                        class="crtskasgnusr form-control floating-label remove-dp"
                                        placeholder="<?php echo __('Assign To Individual '); ?>"
                                        onchange="showHideTimelogBlock(this);notifi_cq_users(this);">
                                    </select>
                                </div>
                            </div>
                            <div id="tour_crt_type"
                                class="w_a task-field-4 mtop15 task-field-all custom-task-fld task-type-fld labl-rt task_type cstm-drop-pad <?php if (!$this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>no-pointer<?php } ?>">
                                <div class="select_field_wrapper mark_mandatory">
                                    <select class="tsktyp-select form-control floating-label"
                                        placeholder="<?php echo __('Task Type'); ?>" data-dynamic-opts=true
                                        onchange="changeTypeId(this)" id="inline-add-tsk">
                                        <?php foreach ($GLOBALS['TYPE'] as $k => $v) {
                                            if ($v['Type']['project_id'] == 0 || $v['Type']['project_id'] == ($getallproj[0]['Project']['id'] ?? '')) {
                                                $type = $v['Type'];
                                                if (trim($type['short_name']) && file_exists(WWW_ROOT . "img/images/types/" . $type['short_name'] . ".png")) {
                                                    $im1 = $this->Format->todo_typ_src($type['short_name'], $type['name']); ?>
                                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                                <?php } else {
                                                    $cl_cs = 'taxt_typ_width';
                                                    if (mb_detect_encoding($type['name'], mb_detect_order(), true) == 'UTF-8') {
                                                        $cl_cs = 'taxt_typ_width_utf';
                                                    } ?>
                                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                                <?php }
                                            }
                                        } ?>
                                    </select>
                                </div>
                                <div class="cmn_help_select"></div>
                            </div>
                            <div id="tour_crt_prio"
                                class="w_a task-field-5 mtop15 task-field-all pririty-fld labl-rt add_new_opt create_priority mtop20 <?php if (!$this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>no-pointer<?php } ?>">
                                <div class="priority_field_wrapper">
                                    <label class="control-label mark_mandatory"><?php echo __('Priority'); ?></label>
                                    <div class="form-group pri-div ct-prior-lmh">
                                        <span class="radio radio-primary custom-rdo priority-low-clr">
                                            <label>
                                                <input name="priority" id="priority_low1" value="2" type="radio"
                                                    onclick="priority_change(this)" />
                                                <?php echo __('Low'); ?>
                                            </label>
                                        </span>
                                        <span class="radio radio-primary custom-rdo priority-medium-clr">
                                            <label>
                                                <input name="priority" id="priority_mid1" value="1" type="radio"
                                                    onclick="priority_change(this)" />
                                                <?php echo __('Medium'); ?>
                                            </label>
                                        </span>
                                        <span class="radio radio-primary custom-rdo priority-high-clr">
                                            <label>
                                                <input name="priority" id="priority_high1" value="0" type="radio"
                                                    onclick="priority_change(this)" />
                                                <?php echo __('High'); ?>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="tour_crt_tskgrp"
                                class="w_a task-field-8 task-field-all mtop15 <?php if (!$this->Format->isAllowed('Move to Milestone', $roleAccess)) { ?>no-pointer<?php } ?>">
                                <div class="select_field_wrapper">
                                    <select class="crtskgrp form-control floating-label"
                                        placeholder="<?php echo __('Task Group'); ?>" data-dynamic-opts=true
                                        onchange="changeMilsestoneId(this)" id="crtskgrp_id">
                                    </select>
                                    <div class="cmn_help_select"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Target Here -->


        <div class="multiple-file-upload up_file_list">
            <style>.up-file-heading:has(+ #up_files:empty){display:none;}</style>
            <div class="up-file-heading" style="font-weight:600;font-size:12px;color:#555;margin:6px 0 4px;"><?php echo __('Uploaded files'); ?></div>
            <table id="up_files" style="font-weight:normal;width: 100%;"></table>
            <form id="cloud_storage_form_0" name="cloud_storage_form_0" action="javascript:void(0)" method="POST">
                <div style="float: left;margin-top: 7px;" id="cloud_storage_files_0"></div>
            </form>
            <div style="clear: both;margin-bottom: 3px;"></div>
        </div>
        <div class="create-task-editor">
            <div class="create-task-form-main">
                <div class="w_c task-field-6 task-field-all">
                    <?php if (\Cake\Core\Plugin::isLoaded('Dms') && CONTROLLER == 'easycases' && PAGE_NAME == 'dashboard') { ?>
                        <?= $this->element('Dms.dms_picker_list', ['scope' => 'quick', 'render' => 'list']) ?>
                    <?php } ?>
                    <div id="tour_crt_desc" style="height:auto;padding:0" class="col-md-8">
                        <textarea name="data[Easycase][message]" id="CS_message" onfocus="openEditor()" rows="2"
                            style="resize:none" class="form-control"
                            placeholder="<?php echo __('Enter Description'); ?>..."><?php if (isset($taskdetails['message']) && $taskdetails['message']) {
                                   echo $taskdetails['message'];
                               } ?></textarea>
                    </div>
                    <?php if ($this->Format->isAllowed('Upload File to Task', $roleAccess)) { ?>
                        <div id="tour_crt_attach" class="col-md-4" style="padding:0">
                            <?php echo $this->Form->create(null, ['url' => ['controller' => 'easycases', 'action' => 'fileupload', '?' => time()], 'type' => 'file', 'id' => 'file_upload']); ?>
                            <div class="drag_and_drop" id="holder_crt_task"
                                style="min-height:100px;margin:0px;box-shadow: none;">
                                <header class="crt_header">
                                    <?php echo __('Attachments'); ?>
                                    <div class="fr">
                                        <?php if (\Cake\Core\Configure::read('CloudStorage.enabled', false)) { ?>
                                            <div class="dropbox-gdrive" style="display:inline-block;vertical-align:middle;">
                                                <a href="javascript:void(0)" onclick="openCloudStoragePicker(0);"
                                                    title="<?php echo __('Link from Cloud Storage'); ?>"><span
                                                        class="os_sprite g-drive"></span></a>
                                                <span id="gloader" style="display: none;">
                                                    <img src="<?php echo HTTP_IMAGES; ?>images/del.gif"
                                                        style="position: absolute;bottom: 95px;margin-left: 125px;" />
                                                </span>
                                            </div>
                                        <?php } ?>
                                        <!-- Task 1362 — small DMS picker icon in the header, beside G-Drive -->
                                        <?php if (\Cake\Core\Plugin::isLoaded('Dms') && CONTROLLER == 'easycases' && PAGE_NAME == 'dashboard') { ?>
                                            <?= $this->element('Dms.dms_picker_button', ['scope' => 'quick']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="cb"></div>
                                </header>
                                <div class="drop-file crttask_attachment" style="min-height: 108px;">
                                    <span><?php echo __('Drop files here or'); ?></span>
                                    <div class="customfile-button">
                                        <input class="customfile-input fileload fl" id="task_file"
                                            name="data[Easycase][case_files]" type="file" multiple=""
                                            style="visibility:visible;" />
                                        <label class="att_fl" for="tsk_attach<%= csAtId %>"
                                            style=""><?php echo __('click upload'); ?></label>
                                    </div>
                                </div>
                                <!-- DMS hidden doc-ids field (posts with the task save). The
                                     visible chip list is rendered above #tour_crt_desc. -->
                                <?php if (\Cake\Core\Plugin::isLoaded('Dms') && CONTROLLER == 'easycases' && PAGE_NAME == 'dashboard') { ?>
                                    <?= $this->element('Dms.dms_picker_list', ['scope' => 'quick', 'render' => 'hidden']) ?>
                                <?php } ?>
                            </div>
                            <?php echo $this->Form->end(); ?>
                        </div>
                        <?php if (\Cake\Core\Plugin::isLoaded('Dms') && CONTROLLER == 'easycases' && PAGE_NAME == 'dashboard') { ?>
                            <?= $this->element('Dms.dms_picker_bootstrap') ?>
                        <?php } ?>
                        <div class="clearfix"></div>
                    <?php } ?>
                </div>

            </div>

            <div class="task-editor-form">
                <form>
                    <div class="create-task-form-main">
                        <div id="tour_crt_srtend"
                            class="w_a task-field-7 mtop15 task-field-all <?php if (!$this->Format->isAllowed('Update Task Duedate', $roleAccess)) { ?>no-pointer<?php } ?>">
                            <?php $dues_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), "date"); ?>
                            <div class="form-group hidden">
                                <label class="control-label" for="due_date"><?php echo __('Due Date'); ?></label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field_wrapper nofloat_wrapper">
                                        <input type="text" id="start_date" name="start_date" class=""
                                            placeholder="<?php echo date('M d, Y', strtotime($dues_date)); ?>" value=""
                                            readonly="readonly" onchange="setStartDueDt();" />
                                        <div class="field_placeholder"><span><?php echo __('Start Date'); ?></span>
                                        </div>
                                        <div class="inp_icon"></div>
                                    </div>
                                </div>
                                <div class="from_to hidden">to</div>
                                <div class="col-md-6">
                                    <div class="field_wrapper nofloat_wrapper">
                                        <input class="task_edit_crt_due_dt" id="due_date" type="text"
                                            placeholder="<?php echo date('M d, Y', strtotime($dues_date)); ?>"
                                            readonly="readonly" onchange="setStartDueDt();">
                                        <div class="field_placeholder"><span><?php echo __('Due Date'); ?></span></div>
                                        <div class="inp_icon"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tour_crt_estmtd"
                            class="w_a task-field-9 mtop15 task-field-all <?php if (!$this->Format->isAllowed('Est Hours', $roleAccess)) { ?>no-pointer<?php } ?>">
                            <div class="field_wrapper nofloat_wrapper" rel="tooltip"
                                title="<?php echo __('You can enter time as 1.5  (that  mean 1 hour and 30 minutes)'); ?>.">
                                <input type="text"
                                    onkeypress="return numeric_decimal_colon(event),mins_validation(this)"
                                    id="estimated_hours" name="data[Easycase][estimated_hours]" maxlength="5"
                                    class="ttfont est check_minute_range" value="<?php if (isset($taskdetails['estimated_hours']) && $taskdetails['estimated_hours']) {
                                        echo $taskdetails['estimated_hours'];
                                    } ?>" placeholder="hh:mm" onchange="mins_validation(this);setStartDueDt();">
                                <div class="field_placeholder"><span><?php echo __('Estimated Hours'); ?></span></div>
                            </div>
                        </div>

                        <div id="tour_crt_recur" class="d-flex w_a task-field-10 mtop15 task-field-all">
                            <div class="checkbox custom-checkbox">
                                <label>
                                    <input type="checkbox" id="is_recurring"
                                        onclick="showRecurringTask('r');"><?php echo __('Recurring'); ?>
                                    <a class="displayEditRecurring" style="display:none;"
                                        onclick="showRecurringTask('p');" href="javascript:void(0);"
                                        title="<?php echo __('Update recurring status'); ?>" rel="tooltip"><i
                                            class="material-icons"
                                            style="font-size: 17px;color: #8B8B8B;">&#xE254;</i></a>
                                </label>
                            </div>
                        </div>

                        <div class="task-field-11 task-field-all w_c mtop15">
                            <div
                                class='timelog_block <?php if (!$this->Format->isAllowed('Manual Time Entry', $roleAccess)) { ?>no-pointer<?php } ?>'>
                                <div class="timelog_toggle_block pr">
                                    <div class="row">
                                        <div id="tour_crt_timrng" class="col-md-4  time_range_fld">
                                            <div class="field_wrapper nofloat_wrapper">
                                                <?php $start_placeholder = (SES_TIME_FORMAT == 12) ? '08:00am' : '13:00'; ?>
                                                <input type="text" id="start_time" name="data[TimeLog][start_time]"
                                                    onchange="updatetime('start_time');"
                                                    class="from_range ttfont w105 tl_start_time"
                                                    placeholder="<?php echo $start_placeholder; ?>" value="<?php if (isset($taskdetails['start_time']) && $taskdetails['start_time']) {
                                                           echo $taskdetails['start_time'];
                                                       } ?>">
                                                <div class="field_placeholder">
                                                    <span><?php echo __('Time Log'); ?></span>
                                                </div>
                                                <div class="from_to">to</div>
                                                <?php $end_placeholder = (SES_TIME_FORMAT == 12) ? '08:30am' : '13:30'; ?>
                                                <input type="text" id="end_time" name="data[TimeLog][end_time]"
                                                    onchange="updatetime('end_time');"
                                                    class="to_range ttfont w105 tl_end_time"
                                                    placeholder="<?php echo $end_placeholder; ?>" value="<?php if (isset($taskdetails['end_time']) && $taskdetails['end_time']) {
                                                           echo $taskdetails['end_time'];
                                                       } ?>">
                                                <div class="cb"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="field_wrapper nofloat_wrapper">
                                                <input id="break_time" name="data[TimeLog][break_time]"
                                                    class=" ttfont w105 tl_break_time check_minute_range brk_hr_mskng "
                                                    value="" placeholder="hh:mm" maxlength="5">
                                                <div class="field_placeholder">
                                                    <span><?php echo __('Break Time'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="field_wrapper nofloat_wrapper">
                                                <input type="text" id="hours" name="data[Easycase][hours]" maxlength="6"
                                                    class="ttfont tl_hours" value="<?php if (isset($taskdetails['hours']) && $taskdetails['hours']) {
                                                        echo $taskdetails['hours'];
                                                    } ?>" placeholder="hh:mm" rel="tooltip"
                                                    title='<?php echo __('Select Start time and End time, it will calculate spent hours automatically'); ?>.'>
                                                <div class="field_placeholder">
                                                    <span><?php echo __('Spent Hours'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="tour_crt_isbil" class="col-md-2">
                                            <div class="checkbox custom-checkbox">
                                                <label>
                                                    <input type="checkbox" id="is_bilable"
                                                        name="data[LogTime][is_bilable]" class="is_bilable"
                                                        value="Yes"><?php echo __('Is Billable'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cb"></div>
                            </div>
                        </div>

                        <div
                            class="w_b task-field-12 mtop15 task-field-all <?php if (!$this->Format->isAllowed('Link Task', $roleAccess)) { ?>no-pointer<?php } ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div id="tour_crt_relate" class="select_field_wrapper">
                                        <select class="relates-select form-control floating-label"
                                            placeholder="<?php echo __('Relate to'); ?>">
                                            <?php foreach ($GLOBALS['RELATES'] as $k => $v) { ?>
                                                <option value="<?php echo $v['id']; ?>"><?php echo $v['title']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div id="tour_crt_linking" class="select_field_wrapper">
                                        <select class="link-to-select form-control floating-label"
                                            placeholder="<?php echo __('Linking Task'); ?>" multiple="multiple">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- LABELS START -->
                        <div
                            class="w_a task-field-13 mtop15 task-field-all <?php if (!$this->Format->isAllowed('Add Label', $roleAccess)) { ?>no-pointer<?php } ?>">
                            <input type="hidden" value='' id="CS_parent_task" />
                            <div class="custom-task-fld parent-task-fld labl-rt add_new_opt">
                                <div id="tour_crt_label" class="select_field_wrapper auto_label_choice">
                                    <select class="label-to-select form-control floating-label"
                                        placeholder="<?php echo __('Label'); ?>" multiple="multiple">
                                        <option value=""><?php echo __('Select Label'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- LABELS END -->

                        <!-- DEPENDENCIES START -->
                        <div class="w_c task-field-17 task-field-all labl-rt mtop15">
                        </div>

                        <!-- CHECKLIST START -->
                        <div class="w_c task-field-14 task-field-all custom-task-fld parent-task-fld labl-rt mtop15">
                            <?php echo $this->element('case_checklist_create'); ?>
                        </div>

                        <div class="w_c task-field-16 task-field-all custom-task-fld parent-task-fld labl-rt">
                            <div id="custom_field_container"></div>
                        </div>

                        <div id="tour_crt_notify" class="w_c task-field-15 task-field-all notify_email add_new_opt">
                            <div class="row">
                                <div class="col-md-12">
                                    <div>
                                        <label id="tour_crt_notify_v2" for="select111"
                                            class="control-label"><?php echo __('Notify via Email'); ?>
                                        </label>
                                    </div>
                                    <div class="checkbox all-check">
                                        <label>
                                            <input type="checkbox" name="chk_all" id="chked_all" value="all"
                                                onclick="checkedAllRes();" />
                                            <?php echo __('ALL'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class='ntfy-usrs'>
                                        <div id="viewmemdtls" class="checkbox"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="row mtop30">
                        <div class="col-md-7 notify_email blank_red-tag">
                            <div id="clientdiv" class="checkbox">
                                <label>
                                    <input type="checkbox" name="chk_all" id="make_client" value="0"
                                        onclick="chk_client();" />
                                    <?php echo __('Do not show the task to the client'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <input type="hidden" name="totfiles" id="totfiles" value="0" readonly="true">
                    <input type="hidden" id="is_default_task_type" value="<?php echo $GLOBALS['TYPE_DEFAULT']; ?>"
                        readonly="true">
                    <input type="hidden" id="CS_type_id" value="<?php if (isset($taskdetails) && $taskdetails['type_id']) {
                        echo $taskdetails['type_id'];
                    } else {
                        if (isset($GLOBALS['TYPE_DEFAULT']) && $GLOBALS['TYPE_DEFAULT'] == 1) { ?>2 <?php } else {
                            echo $GLOBALS['TYPE'][0]['Type']['id'];
                        }
                    } ?>">
                    <input type="hidden" id="CS_priority" value="<?php if (isset($taskdetails) && $taskdetails['priority']) {
                        echo $taskdetails['priority'];
                    } else {
                        echo "2";
                    } ?>">
                    <input type="hidden" id="CS_start_date" value="<?php if (isset($taskdetails) && $taskdetails['start_date']) {
                        echo date('m/d/Y', strtotime($taskdetails['start_date']));
                    } else { ?>No Start Date<?php } ?>">
                    <input type="hidden" id="CS_due_date" value="<?php if (isset($taskdetails) && $taskdetails['due_date']) {
                        echo date('m/d/Y', strtotime($taskdetails['due_date']));
                    } else { ?>No Due Date<?php } ?>">
                    <input type="hidden" id="CS_milestone" value="<?php if (isset($taskdetails) && $taskdetails['milestone_id']) {
                        echo $taskdetails['milestone_id'];
                    } ?>">
                    <input type='hidden' id="client" value='0' />
                    <input type="hidden" id="userIds" />
                    <input type="hidden" id="userNames" />
                    <input type="hidden" id="CSrepeat_due_date" value='' />
                    <input type="hidden" id="CSrepeat_start_date" value='' />
                    <input type="hidden" id="CSrepeat_type" value='' />
                    <input type="hidden" id="CSrepeat_occurrence" value='' />
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="popup_sticly_cta">
        <div class="row">
            <div class="media-se-btn col-md-12 text-right">
                <input type="hidden" value="" name="easycase_uid" id="easycase_uid" readonly="readonly" />
                <input type="hidden" value="" name="easycase_id" id="CSeasycaseid" readonly="readonly" />
                <input type="hidden" value="" name="cs_index" id="CSindex" readonly="readonly" />
                <input type="hidden" value="" name="editRemovedFile" id="editRemovedFile" readonly="readonly" />
                <span id="quickloading" class="fr" style="display:none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" title="<?php echo __('Loading'); ?>..."
                        alt="Loading..." />
                </span>

                <div class="dib_can_savebtn sticly_cta_btn" id="tour_crt_post">
                    <div class="sticly_cta_btn">
                        <div class="checkbox sticly_cta_btn mtop5 m-btm0" id="create_another_task_dv">
                            <label style="display:block;">
                                <input type="checkbox" id="create_another_task"> <?php echo __('Create another'); ?>
                            </label>
                        </div>
                        <div class="dib_can_savebtn sticly_cta_btn">
                            <button class="btn btn-default btn_hover_link cmn_size" onclick="crt_popup_close('CT');"
                                type="button"><?php echo __('Cancel'); ?></button>
                        </div>
                        <div class="save_exit_btn sticly_cta_btn">
                            <a id="quickcase_qt" href="javascript:void(0)"
                                class="btn cmn_size btn_cmn_efect cmn_bg btn-info"
                                onclick="return submitAddNewCase('Post',0,'','','',1,'');"><?php echo __('Save'); ?></a>
                        </div>
                    </div>
                </div>

                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>