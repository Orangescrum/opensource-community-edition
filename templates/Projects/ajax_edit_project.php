<style>
    #txt_shortProjEdit {
        text-transform: uppercase;
    }

    .more_less_project_opts {
        display: block;
    }

    .project-customfield.create_custom_fld .field_wrapper {
        width: calc(32% - 25px);
        width: -webkit-calc(32% - 25px);
        width: -moz-calc(32% - 25px);
        margin-right: 25px;
        margin-bottom: 30px
    }

    .accordion-container {
        margin-bottom: 10px;
    }

    .accordion-header {
        cursor: pointer;
        background-color: #f5f5f5;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 0;
        transition: all 0.3s ease;
    }

    .accordion-header:hover {
        background-color: #f0f0f0;
    }

    .accordion-header .accordion-toggle-icon {
        display: inline-block;
        margin-right: 10px;
        transition: transform 0.3s ease;
        font-size: 20px;
        vertical-align: middle;
    }

    .accordion-header span {
        color: #333;
        font-size: 14px;
        font-weight: bold;
        vertical-align: middle;
    }
</style>
<?= $this->Form->create(NULL, ['url' => ['controller' => 'Projects', 'action' => 'update'], 'name' => 'projsettings', 'enctype' => 'multipart/form-data']) ?>
<center>
    <div id="edit_prj_err_msg" class="err_msg"></div>
</center>
<div class="rounded-fild">
    <div class="modal-body popup-container flex_scroll">
        <input type="hidden" name="data[Project][validateprj]" id="validateprj" readonly="true" value="0" />
        <input type="hidden" name="data[Project][pg]" id="pg" readonly="true" value="0" />
        <input type="hidden" value="<?php echo $uniqid; ?>" name="data[Project][uniq]" id="uniqid" />
        <input type="hidden" value="<?php echo $projArr['Project']['id'] ?>" name="data[Project][id]" />
        <input type="hidden" name="data[Project][click_referer_update]" id="upd_project_click_refer" readonly="true" value="" />

        <div class="row ">
            <div class="col-lg-12 padlft-non padrht-non pd-non">
                <div class="col-lg-4 col-sm-4">
                    <div class="form-group label-floating mark_mandatory">
                        <label class="control-label" for="txt_proj"><span><?php echo __('Specify your project name'); ?></span></label>
                        <?php echo $this->Form->text('name', array('value' => html_entity_decode($projArr['Project']['name'], ENT_QUOTES), 'class' => 'form-control input-lg', 'id' => 'txt_proj', 'placeholder' => "", 'maxlength' => '50')); ?>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4 col-xs-2">
                    <div class="form-group label-floating mark_mandatory">
                        <label class="control-label" for="txt_shortProjEdit"><span><?php echo __('Short name for your project'); ?></span></label>
                        <?php
                        $short_name = html_entity_decode($projArr['Project']['short_name']);
                        if (strtoupper($short_name) == 'WCOS') {
                            echo $this->Form->text('short_name', array('value' => stripslashes($short_name), 'class' => 'form-control input-lg', 'id' => 'txt_shortProjEdit', 'maxlength' => '5', 'readonly' => 'readonly'));
                        } else {
                            echo $this->Form->text('short_name', array('value' => stripslashes($short_name), 'class' => 'form-control input-lg', 'id' => 'txt_shortProjEdit', 'maxlength' => '5'));
                        }
                        ?>
                        <span id="ajxShort" style="display:none; position: absolute; top:30px; right:0px;">
                            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" width="16" height="16" />
                        </span>
                        <span id="ajxShortPage"></span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-2">
                    <div class="select2__wrapper mark_mandatory" id="priority_dropdown">
                        <select name="data[Project][priority]" class="form-control floating-label proj_prioty" placeholder="<?php echo __('Choose Priority'); ?>" data-dynamic-opts=true>
                            <option value='2' <?php if ($projArr['Project']['priority'] == 2) { ?>selected <?php } ?>><?php echo __('Low'); ?></option>
                            <option value='1' <?php if ($projArr['Project']['priority'] == 1) { ?>selected <?php } ?>><?php echo __('Medium'); ?></option>
                            <option value='0' <?php if ($projArr['Project']['priority'] == 0) { ?>selected <?php } ?>><?php echo __('High'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php /* <div class="form-group label-floating">
        <label class="control-label" for="txt_proj">Specify your project name</label>
        <?php echo $this->Form->text('name', array('value' => html_entity_decode($projArr['Project']['name'], ENT_QUOTES), 'class' => 'form-control input-lg', 'id' => 'txt_proj', 'placeholder' => "", 'maxlength' => '50')); ?>
    </div> */ ?>
        <div class="row">
            <div class="col-lg-12 padlft-non padrht-non pd-non">
                <div class="col-lg-4 col-sm-4 col-xs-4">
                    <div class="mtp-15 select2__wrapper" id="sel_Typ">
                        <select class="sel_Typ_dp form-control floating-label" name="data[Project][default_assign]" id="sel_Typ1" placeholder="<?php echo __('Default Assign To'); ?>:" data-dynamic-opts=true>
                            <option value="0" selected="selected"><?php echo __('Select User'); ?></option>
                            <?php foreach ($quickMem as $asgnMem) { ?>
                                <?php
                                $selected = "";
                                if ((isset($defaultAssign) && $defaultAssign) && ($asgnMem['User']['id'] == $defaultAssign)) {
                                    $selected = "selected='selected'";
                                } else if (!$defaultAssign && ($asgnMem['User']['id'] == SES_ID)) {
                                } ?>
                                <option value="<?php echo $asgnMem['User']['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo (($asgnMem['User']['id'] == SES_ID)) ? 'me' : $this->Format->formatText($asgnMem['User']['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4 col-xs-4">
                    <div class="mtp-15 select2__wrapper" id="sel_workflow">
                        <select class="workflow_dp form-control floating-label" name="data[Project][status_group_id]" id="sel_wflow" placeholder="<?php echo __('Select workflow'); ?>:" data-dynamic-opts=true <?php if ($tcnt >= 1) {
                                                                                                                                                                                                                    echo 'disabled="disabled"';
                                                                                                                                                                                                                } ?>>
                            <?php /* <option value="0" selected="selected"><?php echo __('Default Status Workflow');?></option> */ ?>
                            <?php foreach ($wf_list as $wf_key => $wf_val) { ?>
                                <?php
                                $selected = "";
                                if (!empty($status_group_id) && $status_group_id == $wf_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $wf_key; ?>" <?php echo $selected; ?>><?php echo $wf_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4 col-xs-4 hidden">
                    <div class="mtp-15 select2__wrapper" id="sel_defect_workflow">
                        <select class="workflow_dp form-control floating-label" name="data[Project][defect_status_group_id]" id="sel_defect_wflow" placeholder="<?php echo __('Select workflow for bug'); ?>:" data-dynamic-opts=true <?php if ($dcnt >= 1) {
                                                                                                                                                                                                                                            echo 'disabled="disabled"';
                                                                                                                                                                                                                                        } ?>>
                            <?php /* <option value="0" selected="selected"><?php echo __('Default Status Workflow');?></option> */ ?>
                            <?php foreach ($dfct_wf_list as $wf_key => $wf_val) { ?>
                                <?php
                                $selected = "";
                                if (!empty($defect_status_group_id) && $defect_status_group_id == $wf_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $wf_key; ?>" <?php echo $selected; ?>><?php echo $wf_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="cb"></div>
            </div>
        </div>
        <div class="row mtp-20">
            <div class="col-lg-12 padlft-non padrht-non pd-non">
                <div class="col-lg-4 col-sm-4 col-xs-4" style="padding:0px">
                    <div class="select2__wrapper" id="sel_TaskTyp">
                        <select class="tsk_Typ_dp form-control floating-label" name="data[Project][task_type]" id="sel_TaskTyp" placeholder="<?php echo __('Default Task Type'); ?>:" data-dynamic-opts=true>
                            <option value="0" selected="selected"><?php echo __('Select Task Type'); ?></option>
                            <?php foreach ($task_list as $task_key => $task_val) {
                                if ((strtolower($task_val) == 'epic' && $task_type == $task_key)  || strtolower($task_val) != 'epic') {
                            ?>
                                    <?php
                                    $selected = "";
                                    if (!empty($task_type) && $task_type == $task_key) {
                                        $selected = "selected='selected'";
                                    } ?>
                                    <option value="<?php echo $task_key; ?>" <?php echo $selected; ?>><?php echo $task_val; ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-8 col-sm-12" style="padding-left: 22px;padding-right:22px;padding-top:14px">
                    <div class="form-group prj-textarea-desc label-floating cmn-fg0 select2__wrapper ">
                        <label class="control-label" for="prj_desc"><?php echo __('Describe your project'); ?></label>
                        <textarea id="prj_desc" class="form-control input-lg expand hideoverflow" rows="1" wrap="virtual" name="data[Project][description]"><?php echo html_entity_decode(stripslashes($projArr['description'])); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group label-floating" style="padding-left: 10px;padding-right: 10px;padding-bottom: 30px;">
            <label class="control-label" style="top:-22px;left: 22px;"><?php echo __('Created by'); ?></label>
            <?php
            $locDT = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $projArr['Project']['dt_created'], "datetime");
            $gmdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATE, "date");
            $dateTime = $this->Datetime->dateFormatOutputdateTime_day($locDT, $gmdate, 'time');
            ?>
            <div class="form-control input-lg" style="background: #eee;padding: 8px 4px;height:40px;line-height:34px"><?php echo $this->Format->formatText($uname); ?>, <?php echo $dateTime; ?></div>
        </div>
        <div class="row">
            <div class="col-lg-12 more-opt pd-non">
                <div class="col-lg-4 col-sm-4 col-xs-4 padlft-non" id="EditProjEsthr">
                    <div class="form-group mrg0 time-est-hr">
                        <label class="control-label" for="txt_ProjEsthr"><?php echo __('Estimated Hours'); ?></label>
                        <?php /*<span class="os_sprite est-hrs-icon"></span> */ ?>
                        <?php echo $this->Form->text('data.Project.estimated_hours', array('value' => $projArr['Project']['estimated_hours'] ? stripslashes($projArr['Project']['estimated_hours']) : '', 'class' => 'form-control', 'id' => 'txt_ProjEsthr', 'placeholder' => __('hh'), 'maxlength' => '6', 'onkeypress' => 'return numericDecimalProj(event)')); ?>
                        <p class="help-block" style="margin-top: -5px;">(8 <?php echo __('hours'); ?> = 1 <?php echo __('Day'); ?>)</p>
                    </div>
                </div>
                <!-- <div class="col-lg-1 col-sm-1 col-xs-1"></div> -->
                <?php
                if (!empty($projArr['Project']['start_date'])) {
                    $projArr['Project']['start_date'] = date('M j, Y', strtotime($projArr['Project']['start_date']));
                }
                if (!empty($projArr['Project']['end_date'])) {
                    $projArr['Project']['end_date'] = date('M j, Y', strtotime($projArr['Project']['end_date']));
                }
                ?>
                <div class="col-lg-8 col-sm-8 col-xs-8 padlft-non padrht-non time_range_fld" id="ProjStartDate">
                    <div class="row input-daterange">
                        <div class="col-lg-6 col-sm-6 col-xs-6 padlft-non">
                            <div class="form-group mrg0 time-est-hr mark_mandatory">
                                <label class="control-label" for="edit_ProjStartDate"><?php echo __('Start Date'); ?></label>
                                <?php echo $this->Form->text('start_date', ['value' => $projArr['Project']['start_date'], 'class' => 'datepicker form-control', 'id' => 'edit_ProjStartDate', 'placeholder' => '', 'readonly' => 'true']); ?>
                            </div>
                        </div>
                        <div class="from_to"></div>
                        <div class="col-lg-6 col-sm-6 col-xs-6">
                            <div class="form-group mrg0 time-est-hr mark_mandatory">
                                <label class="control-label blank-label" for="edit_ProjEndDate"><?php echo __('End Date'); ?></label>
                                <?php echo $this->Form->text('end_date', ['value' => $projArr['Project']['end_date'], 'class' => 'datepicker form-control', 'id' => 'edit_ProjEndDate', 'placeholder' => '', 'readonly' => 'true']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cb"></div>
            </div>
        </div>

        <div class="row " style="margin-top:6px;">
            <div class="col-lg-12 padlft-non padrht-non pd-non">

                <div class="col-lg-4 col-sm-4">
                    <div class="select2__wrapper " id="">
                        <select id="updt_proj_mngr" name="data[ProjectMeta][project_manager]" class="form-control floating-label proj_manager" placeholder="<?php echo __('Project Manager'); ?>" data-dynamic-opts=true>
                            <?php foreach ($act_users as $usr_key => $usr_val) { ?>
                                <?php
                                $selected = "";
                                if (isset($All_Metas['ProjectMeta']) && !empty($usr_key) && $All_Metas['ProjectMeta']['project_manager'] == $usr_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $usr_key; ?>" <?php echo $selected; ?>><?php echo $usr_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php /* project client */ ?>
                <?php if ($this->Format->isAllowed('Customer Name', $roleAccess)) { ?>
                    <div class="col-lg-4 col-sm-4">
                        <div class="select2__wrapper" id="">
                            <select id="updt_proj_clnt" name="data[ProjectMeta][client]" class="form-control floating-label proj_client" placeholder="<?php echo __('Client'); ?>" data-dynamic-opts=true onchange="changeProjectCurrency(this, '<?php echo $uniqid; ?>');" id="proj_client<?php echo $uniqid; ?>" disabled="disabled">

                                <?php
                                $curncy_key = 68; //USD
                                $selected = "";
                                $client_key = 0;
                                $client_key_selected = 0;
                                if (isset($All_Metas['ProjectMeta']) && $All_Metas['ProjectMeta']['currency']) {
                                    $client_key_selected = $All_Metas['ProjectMeta']['currency'];
                                }
                                foreach ($all_customers as $cust_key => $cust_val) { ?>
                                    <?php
                                    if ($cust_key !== 0) {
                                        $t_client = explode('__', $cust_key);
                                        $client_key = ($t_client[0] == 0) ? '' : $t_client[0];
                                        $curncy_key = $t_client[1];
                                    }
                                    if (isset($All_Metas['ProjectMeta']) && !empty($client_key) && $All_Metas['ProjectMeta']['client'] == $client_key) {
                                        $selected = "selected='selected'";
                                        $client_key_selected = $curncy_key;
                                    } else {
                                        $selected = '';
                                    }
                                    if ($client_key_selected === 0) {
                                        $client_key_selected = 144;
                                    }
                                    ?>
                                    <option value="<?php echo $client_key; ?>" data-cust="<?php echo $curncy_key; ?>" <?php echo $selected; ?>><?php echo $cust_val; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php  } ?>
                <?php if ($this->Format->isAllowed('Customer Name', $roleAccess)) { ?>
                    <div class="row" id="add_instant_customer<?php echo $uniqid; ?>" style="display:none;">
                        <div class="col-lg-12 padlft-non padrht-non pd-non">
                            <div class="col-lg-4 col-sm-4 col-xs-4">
                                <div class="form-group label-floating task-form-group">
                                    <label class="control-label" for="cust_lname"><?php echo __("Client's name"); ?></label>
                                    <?php echo $this->Form->text('cust_fname', array('name' => "data[InvoiceCustomer][cust_fname]", 'value' => '', 'class' => 'form-control fl', 'id' => 'proj_cust_fname' . $uniqid, 'placeholder' => "", 'maxlength' => '100')); ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-xs-6">
                                <div class="form-group label-floating">
                                    <label class="control-label" for="cust_email"><?php echo __("Specify client's email id"); ?></label>
                                    <?php echo $this->Form->text('cust_email', array('name' => "data[InvoiceCustomer][cust_email]", 'value' => '', 'class' => 'form-control', 'id' => 'proj_cust_email' . $uniqid, 'placeholder' => "")); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php  } ?>
            </div>
        </div>
        <div class="w_c project-field-edt labl-rt">
            <div id="edt_custom_field_container" class="d-flex  project-customfield create_custom_fld mtop15"></div>
        </div>

        <div class="accordion-container edt-more-options">
            <div class="accordion-header">
                <i class="material-icons accordion-toggle-icon">expand_more</i>
                <span><?php echo __('Other'); ?></span>
            </div>
        <?php if ($this->Format->isAllowed('Budget', $roleAccess) || $this->Format->isAllowed('Default Rate', $roleAccess)) { ?>
            <div class="row more_less_project_opts-renew">
                <div class="col-lg-12 more-opt pd-non">
                    <?php if ($this->Format->isAllowed('Budget', $roleAccess)) { ?>
                        <div class="col-lg-4 col-sm-4 padlft-non" id="">
                            <div class="form-group">
                                <label class="control-label" for="txt_ProjStartDate"><?php echo __('Budget'); ?>
                                </label>
                                <?php
                                $budget_p = '';
                                if (isset($All_Metas['ProjectMeta'])) {
                                    $budget_p = $All_Metas['ProjectMeta']['budget'];
                                }
                                echo $this->Form->text('budget', array('name' => 'data[ProjectMeta][budget]', 'value' => '', 'class' => 'form-control', 'id' => 'budget', 'placeholder' => '6000', 'onkeypress' => 'return numeric_decimal_proj(event, 0)', 'maxlength' => '11', 'value' => $budget_p)); ?>
                            </div>
                        </div>
                    <?php  } ?>
                    <?php if ($this->Format->isAllowed('Default Rate', $roleAccess)) { ?>
                        <div class="col-lg-4 col-sm-4 padlft-non" id="">
                            <div class="form-group">
                                <label class="control-label" for="txt_ProjStartDate"><?php echo __('Default Rate'); ?>
                                </label>
                                <?php
                                $rate_p = '';
                                if (isset($All_Metas['ProjectMeta'])) {
                                    $rate_p = $All_Metas['ProjectMeta']['default_rate'];
                                }
                                echo $this->Form->text('default_rate', array('name' => 'data[ProjectMeta][default_rate]', 'value' => '', 'class' => 'form-control', 'id' => 'default_rate', 'placeholder' => __('0.0'), 'onkeypress' => 'return numeric_decimal_proj(event, 1)', 'maxlength' => '8', 'value' => $rate_p)); ?>
                            </div>
                        </div>
                    <?php  } ?>
                </div>
            </div>
        <?php  } ?>
        <?php if ($this->Format->isAllowed('Cost Appr', $roleAccess) || $this->Format->isAllowed('Minimum Tolerance', $roleAccess) || $this->Format->isAllowed('Maximum Tolerance', $roleAccess)) { ?>
            <div class="row more_less_project_opts-renew">
                <div class="col-lg-12 more-opt pd-non">
                    <?php if ($this->Format->isAllowed('Cost Appr', $roleAccess)) { ?>
                        <div class="col-lg-4 col-sm-4 padlft-non" id="">
                            <div class="form-group">
                                <label class="control-label" for="cost_appr"><?php echo __('Cost Appr'); ?></label>
                                <?php
                                $cost_appr_p = '';
                                if (isset($All_Metas['ProjectMeta'])) {
                                    $cost_appr_p = $All_Metas['ProjectMeta']['cost_appr'];
                                }
                                echo $this->Form->text('cost_appr', array('name' => 'data[ProjectMeta][cost_appr]', 'value' => '', 'class' => 'form-control', 'id' => 'cost_appr', 'placeholder' => 5000, 'onkeypress' => 'return numeric_decimal_proj(event, 0)', 'maxlength' => '11', 'value' => $cost_appr_p)); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($this->Format->isAllowed('Minimum Tolerance', $roleAccess) || $this->Format->isAllowed('Maximum Tolerance', $roleAccess)) { ?>
                        <div class="col-lg-8 col-sm-8 padrht-non time_range_fld" id="">
                            <div class="row">
                                <?php if ($this->Format->isAllowed('Minimum Tolerance', $roleAccess)) { ?>
                                    <div class="col-lg-6 col-sm-6 padlft-non">
                                        <div class="form-group">
                                            <label class="control-label" for="min_tol"><?php echo __('Min Tolerance%'); ?>
                                            </label>
                                            <?php
                                            $min_tol_p = '';
                                            if (isset($All_Metas['ProjectMeta'])) {
                                                $min_tol_p = $All_Metas['ProjectMeta']['min_tol'];
                                            }
                                            echo $this->Form->text('min_tol', array('name' => 'data[ProjectMeta][min_tol]', 'value' => '', 'class' => 'form-control', 'id' => 'min_tolerance', 'placeholder' => 0, 'onkeypress' => 'return numeric_decimal_proj(event, 0)', 'maxlength' => '3', 'value' => $min_tol_p)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($this->Format->isAllowed('Maximum Tolerance', $roleAccess)) { ?>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="control-label" for="max_tol"><?php echo __('Max Tolerance%'); ?>
                                            </label>
                                            <?php
                                            $max_tol_p = '';
                                            if (isset($All_Metas['ProjectMeta'])) {
                                                $max_tol_p = $All_Metas['ProjectMeta']['max_tol'];
                                            }
                                            echo $this->Form->text('max_tol', array('name' => 'data[ProjectMeta][max_tol]', 'value' => '', 'class' => 'form-control', 'id' => 'max_tolerance', 'placeholder' => 20, 'onkeypress' => 'return numeric_decimal_proj(event, 0)', 'maxlength' => '3', 'value' => $max_tol_p)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php  } ?>
        <div class="row more_less_project_opts-renew">
            <div class="col-lg-12 padlft-non padrht-non pd-non">
                <div class="col-lg-4 col-sm-4">
                    <div class="select2__wrapper" id="">
                        <select name="data[Project][status]" class="form-control floating-label proj_status" placeholder="<?php echo __('Project Status'); ?>" data-dynamic-opts=true>
                            <?php foreach ($All_status as $sts_key => $sts_val) { ?>
                                <?php
                                $selected = "";
                                if (!empty($sts_key) && $projArr['Project']['status'] == $sts_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $sts_key; ?>" <?php echo $selected; ?>><?php echo $sts_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4">
                    <div class="select2__wrapper " id="">
                        <select id="edit_projctType" name="data[ProjectMeta][proj_type]" class="form-control floating-label proj_type" placeholder="<?php echo __('Project Type'); ?>" data-dynamic-opts=true>
                            <?php foreach ($All_ptypes as $typ_key => $typ_val) { ?>
                                <?php
                                $selected = "";
                                if (isset($All_Metas['ProjectMeta']) && !empty($typ_key) && $All_Metas['ProjectMeta']['proj_type'] == $typ_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $typ_key; ?>" <?php echo $selected; ?>><?php echo $typ_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4">
                    <div class="select2__wrapper" id="">
                        <select name="data[ProjectMeta][industry]" class="form-control floating-label proj_industry" placeholder="<?php echo __('Industry'); ?>" data-dynamic-opts=true>
                            <?php foreach ($industries as $inds_key => $inds_val) { ?>
                                <?php
                                $selected = "";
                                if (isset($All_Metas['ProjectMeta']) && !empty($inds_key) && $All_Metas['ProjectMeta']['industry'] == $inds_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $inds_key; ?>" <?php echo $selected; ?>><?php echo $inds_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-4">
                    <div class="select2__wrapper" id="">
                        <select name="data[Project][parent_id]" class="form-control floating-label proj_prog_type" placeholder="<?php echo __('Program'); ?>" data-dynamic-opts=true>
                            <?php foreach ($editPgrm as $prm_key => $prm_val) { ?>
                                <?php
                                $selected = "";
                                if (!empty($prm_key) && $projArr['parent_id'] == $prm_key) {
                                    $selected = "selected='selected'";
                                } ?>
                                <option value="<?php echo $prm_key; ?>" <?php echo $selected; ?>><?php echo $prm_val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>



            </div>
        </div>
        </div>
    </div>
</div>
<div class="modal-footer popup_sticly_cta">
    <div class="fr popup-btn">
        <span id="settingldr" style="display:none;"><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" /></span>
        <span id="btn-edit-project" class="project_edit_button">
            <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
            <?php if ($page == 'manage_card') { ?>
                <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="btn_edit_project" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="return submitProject('txt_proj', 'txt_shortProjEdit', 'txt_ProjEsthr', 'edit_ProjStartDate', 'edit_ProjEndDate',true)" id="savebtn"><?php echo __('Update'); ?></a></span>
            <?php } else { ?>
                <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="btn_edit_project" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="return submitProject('txt_proj', 'txt_shortProjEdit', 'txt_ProjEsthr', 'edit_ProjStartDate', 'edit_ProjEndDate','active-grid')" id="savebtn"><?php echo __('Update'); ?></a></span>
            <?php } ?>
        </span>
    </div>
</div>
<?php $this->Form->end(); ?>
<script>
    $(function() {
        $("#edit_ProjStartDate").datepicker({
            format: 'M d, yyyy',
            changeMonth: false,
            changeYear: false,
            hideIfNoPrevNext: true,
            autoclose: true
        }).on('changeDate', function(e) {
            $("#edit_ProjEndDate").datepicker("setStartDate", $("#edit_ProjStartDate").datepicker('getFormattedDate'));
        });
        $("#edit_ProjEndDate").datepicker({
            format: 'M d, yyyy',
            changeMonth: false,
            changeYear: false,
            hideIfNoPrevNext: true,
            autoclose: true
        }).on('changeDate', function(e) {
            $("#edit_ProjStartDate").datepicker("setEndDate", $("#edit_ProjEndDate").datepicker('getFormattedDate'));
        });
        $('#txt_proj').blur(function(e) {
            var str = $(this).val();
        }).keyup(function(e) {
            var str = $(this).val();
            var str_temp = '';
            if (e.keyCode == 32 || e.keyCode == 8 || e.keyCode == 46) {}
        });
        $('#txt_proj,#txt_shortProjEdit')
            .change(function() {
                $(this).val().trim() != '' ? $("#btn_edit_project").removeClass('loginactive') : $("#btn_edit_project").addClass('loginactive');
                $('#edit_prj_err_msg').html('');
            });
        $('#edit_prj_err_msg').html('');
        $.material.init();
        $('.proj_prioty,.workflow_dp,.sel_Typ_dp').select2();
        $('.tsk_Typ_dp').select2({
            templateSelection: formatTaskType,
            templateResult: formatTaskType
        });

        $('#more_proj_options_edt').click(function() {
            $('#more_proj_options_edt').html(($('.more_less_project_opts').is(":visible") ? _("More options") : _("Hide options")));
            $('.more_less_project_opts').slideToggle('slow');
        });
        
        $('.edt-more-options').find('.more_less_project_opts-renew').hide();

        $('.edt-more-options').find('.accordion-header').on('click', function () {
            var content = $(this).nextAll('.more_less_project_opts-renew');
            var isOpen = content.is(':visible');
            
            content.slideToggle();

            var icon = $(this).find('.accordion-toggle-icon');
            var header = $(this);
            
            if (isOpen) {
                header.css('background-color', '#f5f5f5');
                icon.css('transform', 'rotate(0deg)');
            } else {
                header.css('background-color', '#e8e8e8');
                icon.css('transform', 'rotate(180deg)');
            }
        });
        $('.proj_manager').select2();
        $('.proj_client').select2();
        $('.proj_industry').select2();
        $('.proj_prog_type').select2();
        if (SES_TYPE == 3) {
            $(".proj_type").select2();
        } else {
            $(".proj_type").select2({
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    if (term.match(/[$-/:-?{-~!"^_`\[\]<>#]+/)) {
                        var msg = _("'Project Type' must not contain special characters!");
                        showTopErrSucc('error', msg);
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    }
                }
            }).off('select2:select').on('select2:select', function(evt) {
                if (evt.params.data.newTag == true) {
                    var name = evt.params.data.id;
                    $('#caseLoader').show();
                    $.post(HTTP_ROOT + 'projects/ajax_addProjectType', {
                        'name': evt.params.data.id
                    }, function(res) {
                        $('#caseLoader').hide();
                        if (res.status == 'error' && res.msg == 'name') {
                            showTopErrSucc('error', _('Project Type already esists!. Please enter another name.'));
                            $('.proj_type option[value="' + name + '"]').remove();
                            $('.proj_type').trigger('change');
                        } else if (res.status == 'success') {
                            if (res.msg == 'saved') {
                                showTopErrSucc('success', _('Project Type Successfully Added'));
                                $(".proj_type").append("<option value='" + res.id + "' selected>" + name + "</option>");
                                $('.proj_type option[value="' + res.id + '"]').prop('selected', true);
                                $('.proj_type').trigger('change');
                            } else {
                                showTopErrSucc('error', _('Project Type can not be added'));
                                $('.proj_type').trigger('change');
                            }
                        }
                    }, 'json');
                }
            });
        }
        if (SES_TYPE == 3) {
            $(".proj_status").select2();
        } else {
            $(".proj_status").select2({
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    if (term.match(/[$-/:-?{-~!"^_`\[\]<>#]+/)) {
                        var msg = _("'Project Status' must not contain special characters!");
                        showTopErrSucc('error', msg);
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    }
                }
            }).off('select2:select').on('select2:select', function(evt) {
                if (evt.params.data.newTag == true) {
                    var name = evt.params.data.id;
                    $('#caseLoader').show();
                    $.post(HTTP_ROOT + 'projects/ajax_addProjectStatus', {
                        'name': evt.params.data.id
                    }, function(res) {
                        $('#caseLoader').hide();
                        if (res.status == 'error' && res.msg == 'name') {
                            showTopErrSucc('error', _('Project Status already esists!. Please enter another name.'));
                            $('.proj_status option[value="' + name + '"]').remove();
                            $('.proj_status').trigger('change');
                        } else if (res.status == 'success') {
                            if (res.msg == 'saved') {
                                showTopErrSucc('success', _('Project Status Successfully Added'));
                                $(".proj_status").append("<option value='" + res.id + "' selected>" + name + "</option>");
                                $('.proj_status option[value="' + res.id + '"]').prop('selected', true);
                                $('.proj_status').trigger('change');
                            } else {
                                showTopErrSucc('error', _('Project Status can not be added'));
                                $('.proj_status').trigger('change');
                            }
                        }
                    }, 'json');
                }
            });
        }
    });

    /* Customer select: reveals the inline "add customer" form for the __new option. */
    function changeProjectCurrency(obj, id) {
        var in_val = $(obj).val();
        if (in_val == '0') {
            if ($('#add_instant_customer' + id).is(':visible')) {
                addCancelCustomer(id);
            }
            return;
        }
        if ($('.proj_client option:selected').attr('data-cust') === 'new') {
            addCancelCustomer(id);
        } else if ($('#add_instant_customer' + id).is(':visible')) {
            addCancelCustomer(id);
        }
    }

    function addCancelCustomer(typ) {
        $('#proj_cust_fname' + typ).val('');
        $('#proj_cust_email' + typ).val('');
        $('#add_instant_customer' + typ).slideToggle();
    }
</script>