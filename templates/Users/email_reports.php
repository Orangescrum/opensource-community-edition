<style>
    input#weekday_1 {margin-left: 100px;}
    div.manangement-notify{padding-bottom:45px;}
</style>
<div class="setting_wrapper task_listing send-email-notify-page eu-report-page">
    <div class="pririty-fld labl-rt add_new_opt padrht-non create_priority">
        <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'emailReports'], 'onsubmit' => 'return validateemailrpt();']) ?>

        <input type="hidden" name="data[UserNotification][id]" value="<?php echo !empty($getAllNot) ? $getAllNot->id : ''; ?>"/>
        <input type="hidden" name="data[UserNotification][type]" value="1"/>
        <input type="hidden" name="data[User][changepass]" id="changepass" readonly="true" value="0"/>
        <div class="row">
            <div class="col-lg-12 email_report_label">
                <h3><?php echo __('Send me Email Reports');?></h3>
                <div class="ser-label mtop30">
                    <?php if (SES_TYPE < 3) { ?>
                        <div class="row form-group">
                            <label class="col-md-3 col-sm-3 control-label padlft-non"><p><?php echo __('Weekly Usage');?></p></label>
                            <div class="col-md-9 col-sm-9">
                                <div class="radio radio-primary">
                                    <label><input type="radio" name="data[UserNotification][weekly_usage_alert]" id="wkugalyes" value="1" <?php echo (!empty($getAllNot) && $getAllNot->weekly_usage_alert == 1) ? 'checked="checked"' : ""; ?> /><?php echo __('Yes');?></label>
                                    <label><input type="radio" name="data[UserNotification][weekly_usage_alert]" id="wkugalno" value="0" <?php echo (!empty($getAllNot) && $getAllNot->weekly_usage_alert == 0) ? 'checked="checked"' : ""; ?> /><?php echo __('No');?></label>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row form-group">
                        <label class="col-md-3 col-sm-3 control-label padlft-non"><p><?php echo __('Task Status');?></p></label>
                        <div class="col-md-9 col-sm-9">
                            <div class="radio radio-primary">
                                <label><input type="radio" name="data[UserNotification][value]" id="valdaily" value="1" <?php echo (!empty($getAllNot) && $getAllNot->value == 1) ? 'checked="checked"' : ""; ?> /><?php echo __('Daily');?></label>
                                <label><input type="radio" name="data[UserNotification][value]" id="valweekly" value="2" <?php echo (!empty($getAllNot) && $getAllNot->value == 2) ? 'checked="checked"' : ""; ?> /><?php echo __('Weekly');?></label>
                                <label><input type="radio" name="data[UserNotification][value]" id="valmonthly" value="3" <?php echo (!empty($getAllNot) && $getAllNot->value == 3) ? 'checked="checked"' : ""; ?> /><?php echo __('Monthly');?></label>
                                <label><input type="radio" name="data[UserNotification][value]" id="valnone" value="0" <?php echo (!empty($getAllNot) && $getAllNot->value == 0) ? 'checked="checked"' : ""; ?> /><?php echo __('None');?></label>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 col-sm-3 control-label padlft-non"><p><?php echo __('Task Due (daily)');?></p></label>
                        <div class="col-md-9 col-sm-9">
                            <div class="radio radio-primary">
                                <label><input type="radio" name="data[UserNotification][due_val]" id="dueyes" value="1" <?php echo (!empty($getAllNot) && $getAllNot->due_val == 1) ? 'checked="checked"' : ""; ?> /><?php echo __('Yes');?></label>
                                <label><input type="radio" name="data[UserNotification][due_val]" id="dueno" value="0" <?php echo (!empty($getAllNot) && $getAllNot->due_val == 0) ? 'checked="checked"' : ""; ?> /><?php echo __('No');?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div >
            <h3 class="hide"><?php echo __('Daily Update Report');?></h3>
            <div class="row mtop30">
                <div class="col-lg-12">
                    <div class="col-lg-6 col-sm-6 padlft-non hide">
                        <div class="row form-group">
                            <label class="col-md-6 col-sm-6 control-label padlft-non"><p><?php echo __('Send me Email');?></p></label>
                            <div class="col-md-6 col-sm-6 ser-label">
                                <div class="radio radio-primary semail-rdo">
                                    <label><input type="radio" name="data[DailyupdateNotification][dly_update]"  id="dlyupdateyes" value="1" <?php echo (!empty($getAllDailyupdateNot) && $getAllDailyupdateNot->dly_update == 1) ? 'checked="checked"' : ""; ?> onClick="showbox('show')" /><?php echo __('Yes');?></label>
                                    <label><input type="radio" name="data[DailyupdateNotification][dly_update]"  id="dlyupdateno" value="0" <?php echo (!empty($getAllDailyupdateNot) && $getAllDailyupdateNot->dly_update == 0) ? 'checked="checked"' : ""; ?> onClick="showbox('hide')"/><?php echo __('No');?></label>
                                </div>
                            </div>
                        </div>
                        <?php
                        $hr_min = [];
                        if (!empty($getAllDailyupdateNot) && $getAllDailyupdateNot->dly_update == 1) {
                            $style = '';
                            $hr_min = explode(':', $getAllDailyupdateNot->notification_time);
                        } else
                            $style = 'style="display:none"';
                        ?>
                        <div class="row for-option dlyupdt mtop20" <?php echo $style; ?>>
                            <div class="col-lg-6 col-sm-6 custom-task-fld time-font">
                                <div class="form-group custom-drop-lebel label-floating">
                                    <select id="not_hr" name="data[DailyupdateNotification][not_hr]" class="select form-control floating-label" placeholder="Hour <span style='color:red'>*</span>" data-dynamic-opts="true">
                                        <option selected="" value="0"><?php echo __('Hour');?></option>
                                        <?php
                                        for ($i = 1; $i <= 24; $i++) {
                                            if ($i <= 9) {
                                            }
                                            $select_string = (isset($hr_min[0]) && $i == $hr_min[0]) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo $i; ?>" <?php echo $select_string; ?>><?php echo $i <= 9 ?'0'.$i:$i; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 custom-task-fld time-font">
                                <div class="form-group custom-drop-lebel label-floating">
                                    <select id="not_mn"  name="data[DailyupdateNotification][not_mn]" class="select form-control floating-label" placeholder="Min <span style='color:red'>*</span>" data-dynamic-opts="true">
                                        <option selected="" value="-1"><?php echo __('Min');?></option>
                                        <?php
                                        for ($i = 0; $i <= 45; $i = $i + 15) {
                                            if ($i < 10)
                                                //$i = '0' . $i;

                                                ?>
                                                <?php  $select_string = (isset($hr_min[0]) && intval($hr_min[1]) != 0 &&  $i == intval($hr_min[1])) ? 'selected' : ''; ?>
                                                <option value="<?php echo $i; ?>" <?php echo $select_string; ?>><?php echo $i < 10 ? '0' . $i : $i; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mtop30" id="rdy_project_div" <?php echo $style; ?>>
                            <div class="col-lg-9  custom-task-fld time-font">
                                <div class="form-group custom-drop-lebel label-floating dlyupdt prj-dlyupdt pr">
                                    <select name="data[DailyupdateNotification][proj_name][]" multiple="multiple" id="rpt_selprjproj_ch_r1" class="form-control floating-label" placeholder="Projects To Notify<span style='color:red'>*</span>">
                                        <?php
                                        foreach ($projArray as $pjtnm => $prjtnm) {
                                            ?>
                                            <option value="<?php echo $prjtnm['id']; ?>"  <?php if(in_array($pjtnm,$selectedproj_ids)) { ?> selected <?php } ?> >
                                                <?php echo $prjtnm['name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php /*<div class="row mtop30">
                            <div class="col-lg-12 custom-task-fld time-font">
                                <div class="form-group custom-drop-lebel label-floating dlyupdt prj-dlyupdt pr" <?php echo $style; ?>>
                                    <select name="data[DailyupdateNotification][proj_name]" id="rpt_selprj" class="form-control floating-label" placeholder="Projects" data-dynamic-opts="true">
                                        <?php
                                        if (!empty($getAllDailyupdateNot) && trim($getAllDailyupdateNot->proj_name) != '') {
                                            $pjarr = explode(",", $getAllDailyupdateNot->proj_name);
                                            if (isset($pjarr[0])) {
                                                foreach ($pjarr as $pjtnm) {
                                                    ?>
                                                    <option value="<?php echo $pjtnm; ?>" class="selected">
                                                        <?php
                                                        $prjtnm = $this->Casequery->getProjectName($pjtnm);
                                                        echo $prjtnm['name'];
                                                        ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <option value="<?php echo $pjarr; ?>" class="selected">
                                                    <?php
                                                    $prjtnm = $this->Casequery->getProjectName($pjarr);
                                                    echo $prjtnm['name'];
                                                    ?>
                                                </option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <span id="ajax_loader" style="display:none;position:absolute; right: -25px;top: 59px;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." />
                </span>
                                </div>
                            </div>
                        </div> */?>
                    </div>
                    <?php $prj_hr_min = !empty($getproject_data) ? explode(':', $getproject_data->notification_time) : [];?>
                    <div class="cb"></div>
                    <div class="mtop20 hide">
                        <h3><?php echo __('Management Reports');?></h3>
                        <div class="row form-group" style="top:30px;">
                            <label class="col-md-6 col-sm-6 control-label padlft-non"><p><?php echo __('Send Project Estimation Vs Actual');?></p></label>
                            <div class="col-md-6 col-sm-6 ser-label">
                                <div class="radio radio-primary semail-rdo">
                                    <label><input type="radio" name="data[ProjectNotification][sent_mail]"  id="prjmanageyes" value="1" <?php echo (!empty($getproject_data) && $getproject_data->sent_mail == 1) ? 'checked="checked"' : ""; ?>  onClick="showboxproject('show');" /><?php echo __('Yes');?></label>
                                    <label><input type="radio" name="data[ProjectNotification][sent_mail]"  id="prjmanageno" value="0" <?php echo (!empty($getproject_data) && $getproject_data->sent_mail == 0) ? 'checked="checked"' : ""; ?>  onClick="showboxproject('hide');"/><?php echo __('No');?></label>
                                </div>
                            </div>
                        </div>
                        <div id="projestactdiv" style="display:none">
                            <div class="row form-group" style="top: 30px; display:none">
                                <label class="col-md-6 col-sm-6 control-label padlft-non"><p><?php echo __('Notify Frequency');?></p></label>
                                <div class="col-md-6 col-sm-6 ser-label">
                                    <div class="radio radio-primary semail-rdo">
                                        <label><input type="radio" name="data[ProjectNotification][frequncy]"  id="prjmanagedaily" value="1"/><?php echo __('Daily');?></label>
                                        <label><input type="radio" name="data[ProjectNotification][frequncy]"  id="prjmanageweekly" value="2"/><?php echo __('Weekly');?></label>
                                        <label><input type="radio" name="data[ProjectNotification][frequncy]"  id="prjmanagemonthly" value="3"/><?php echo __('mothly');?></label>
                                    </div>
                                </div>
                            </div>

                            <div id="coosen_day" style="top: 30px;" class="form-group custom-drop-lebel label-floating mtop20 weekend-checkbox">
                                <label style="margin-bottom:30px;"><?php echo __('Choose a day for sending notification');?><span style='color:red'>*</span></label>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="1" id="weekday_1" <?php if(in_array(1,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Monday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="2" id="weekday_2" <?php if(in_array(2,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Tuesday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="3" id="weekday_3" <?php if(in_array(3,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Wednesday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="4" id="weekday_4" <?php if(in_array(4,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Thursday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="5" id="weekday_5" <?php if(in_array(5,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Friday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="6" id="weekday_6" <?php if(in_array(6,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Saturday');?></span>
                                <input type="checkbox" class="projweekendcb" name="data[ProjectNotification][day][]" value="0" id="weekday_0" <?php if(in_array(0,$selected_date)){ echo "checked='checked'";} ?> /> <span><?php echo __('Sunday');?></span>
                            </div>
                            <div class="row for-option dlyupdt mtop30">
                                <div class="col-lg-3 col-sm-3 custom-task-fld time-font">
                                    <div class="form-group custom-drop-lebel label-floating">
                                        <select id="pnot_hr" name="data[ProjectNotification][not_hr]" class="select form-control floating-label" placeholder="Hour <span style='color:red'>*</span>" data-dynamic-opts="true">
                                            <option selected="" value="0"><?php echo __('Hour');?></option>
                                            <?php
                                            for ($i = 1; $i <= 24; $i++) {
                                                if ($i <= 9) {
                                                    //   $i = '0' . $i;
                                                }
                                                $selected = (isset($prj_hr_min[0]) && $i == $prj_hr_min[0]) ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo $i; ?>" <?php echo $selected;?>><?php echo $i <= 9 ?'0'.$i:$i; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-3 custom-task-fld time-font">
                                    <div class="form-group custom-drop-lebel label-floating">
                                        <select id="pnot_mn"  name="data[ProjectNotification][not_mn]" class="select form-control floating-label" placeholder="Min <span style='color:red'>*</span>" data-dynamic-opts="true">
                                            <option selected="" value="-1"><?php echo __('Min');?></option>
                                            <?php
                                            for ($i = 0; $i <= 45; $i = $i + 15) {
                                                if ($i < 10)

                                                    ?>
                                                    <?php $selected = (isset($prj_hr_min[1]) && intval($prj_hr_min[1]) != 0 && $i == intval($prj_hr_min[1])) ? 'selected' : ''; ;?>
                                                    <option value="<?php echo $i; ?>"<?php echo $selected; ?>><?php echo $i < 10 ? '0' . $i : $i; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3  custom-task-fld time-font">
                                        <div class="form-group custom-drop-lebel label-floating dlyupdt prj-dlyupdt pr">
                                            <select name="data[ProjectNotification][proj_name][]" multiple="multiple" id="rpt_selprjproj" class="form-control floating-label" placeholder="Projects To Notify<span style='color:red'>*</span>">
                                                <?php
                                                foreach ($allProjects as $pjtnm => $prjtnm) {
                                                    ?>
                                                    <option value="<?php echo $pjtnm; ?>"  <?php if(in_array($pjtnm,$selectedproj_ids)) { ?> selected <?php } ?> >
                                                        <?php echo $prjtnm; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div style="top: 30px;"class="form-group manangement-notify custom-drop-lebel label-floating mtop20 weekend-checkbox">
                                <label><?php echo __('Notify To');?></label>
                                <input type="checkbox" class=""style="margin-left: 10px;" name="data[ProjectNotification][role_name][]" value="1"<?php if(in_array(1,$selecterole_ids)){ echo "checked='checked'";} ?> id=" owner_proj_est"/> <span><?php echo __('Owner');?></span>

                                <input type="checkbox" class="" onclick="checkAdmin();" name="data[ProjectNotification][role_name][]" value="2" <?php if(in_array(2,$selecterole_ids)){ echo "checked='checked'";} ?> id="admin_proj_est"/> <span><?php echo __('Admin');?></span>

                            </div>
                            <div class="row" id="admin_user_id_selection" style="display:none">
                                <div class="col-lg-3  custom-task-fld time-font">
                                    <div class="form-group custom-drop-lebel label-floating dlyupdt prj-dlyupdt pr">
                                        <select name="data[ProjectNotification][admin_user][]" multiple="multiple" id="rpt_seladmin_user" class="form-control floating-label" placeholder="Select Admin<span style='color:red'>*</span>">
                                            <?php
                                            foreach ($admin_users as $key => $value) {
                                                ?>
                                                <option value="<?php echo $key; ?>"  <?php if(in_array($key,$selectedadmin_ids)) { ?> selected <?php } ?> >
                                                    <?php echo $value; ?>
                                                </option>
                                            <?php }  ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mtop20">
                        <div class="btn_row fr">
                            <div id="subprof1">
                                <div class="fl"><a class="btn btn-default btn_hover_link cmn_size" onclick="cancelProfile('<?php echo $referer; ?>');"><?php echo __('Cancel');?></a></div>
                                <div class="fl btn-margin"><button type="submit" value="Save" name="submit_Pass"onclick="validateEmailReport();"  id="submit_Pass" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Update');?></button></div>
                                <div class="cb"></div>
                            </div>
                            <span id="subprof2" style="display:none">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." />
                </span>
                        </div>
                        <div class="cb"></div>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#rpt_selprjproj_ch_r1').select2();
        $('#rpt_selprjproj').select2();
        $('#rpt_seladmin_user').select2();
        $.material.init();
        $(".select").dropdown({"optionClass": "withripple"});

        if($('#admin_proj_est').is(':checked'))
        {
            $('#admin_user_id_selection').show();
        }
        getAutocompleteTag("rpt_selprj", "users/getProjects", "480px", "Type to select projects");
        if($('#prjmanageyes').is(':checked'))
        {
            showboxproject('show');
        }

        if($('#dlyupdateyes').is(':checked'))
        {
            showbox('show');
        }
        if($('#dlyupdateyes').is(':checked'))
        {
            showbox('show');
            $("#rdy_project_div").show();
        }
        $("input:radio[name='data[DailyupdateNotification][dly_update]']").on('change',function(){
            if($("#dlyupdateyes").is(":checked")){
                $("#rdy_project_div").show();
            }else{
                $("#rdy_project_div").hide();
            }
        })
    });
    function validateEmailReport(){
        if($('#teammanageyes').prop('checked') == true){
            if(($('#owner_team_est').prop('checked') == false) && ($('#admin_team_est').prop('checked') == false)){
                showTopErrSucc('error', _('Please choose a user to send team utilization report.'));
                return false;
            }else{

            }
        }else{

        }

    }
    function checkAdmin(){
        if($('#admin_proj_est').is(':checked'))
        {
            $('#admin_user_id_selection').show();
        }else{
            $('#admin_user_id_selection').hide();
        }
    }
    function showboxproject(act){
        if (act == 'show') {
            $('#projestactdiv').slideDown("fast");
        } else {
            $('#projestactdiv').slideUp("fast");
        }
    }
    function showbox(act) {
        if (act == 'show') {
            $('#dlyupdt,.dlyupdt').slideDown("fast");
        } else {
            $('#dlyupdt,.dlyupdt').slideUp("fast");
        }
    }
</script>
