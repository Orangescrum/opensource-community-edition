<style>
    .workflow-header{padding:0; margin:20px 0; border-bottom:1px solid #ddd}
    .workflow-header li{list-style:none; font-size:15px; display:inline-block; padding:10px 15px;margin-right: 10px; border: 1px solid #ddd; border-bottom:0; border-radius:10px 10px 0 0; cursor: pointer;}
    .submit-workflow{width:100%; text-align:right; padding-top:20px;}
    .workflow-header li.active{background:#ddd;}
    .action_to_wrapper,.action_cc_wrapper{margin-bottom:10px;}
</style>
<div class="task_lis_page">
    <div class="task_listing">
        <div class="proj_grids glide_div invoice_label_type_setting">
            <div class="cb"></div>
            <div class="dataTables_wrapper setting_accordian">
                <div class="d-flex">
                    <h3 class="cmn_head_title"><?php echo __("Workflow automation settings"); ?></h3>
                    <div class="ml-auto">
                    </div>
                </div>
                <div class="row">
                    <?php
                    echo $this->Form->create(null, array(
                        'url' => array('controller' => 'Projects', 'action' => 'saveWorkflow'),
                        'id' => 'workflow-form',
                        'onSubmit' => "return checkValidation()"
                    ));
                    ?>
                    <input type="hidden" name="wid" id="wid" value="<?php echo isset($workflowdetail['wid']) && !empty($workflowdetail['wid']) ? $workflowdetail['wid'] : ""; ?>" />
                    <div class="col-sm-12">
                        <ul class="workflow-header">
                            <li class="active workflow-choose-type workflow-menu" onclick="workflowNext('type')"><?php echo __('Choose Type'); ?></li>
                            <li class="workflow-choose-conditions workflow-menu" onclick="workflowNext('conditions')"><?php echo __('Conditions'); ?></li>
                            <li class="workflow-choose-action workflow-menu" onclick="workflowNext('action')"><?php echo __('Actions'); ?></li>
                        </ul>
                        <div class="workflow-box">
                            <div class="workflow-step-1 w-steps" style="width:50%">
                                <div class="field_wrapper nofloat_wrapper">
                                    <input type="text" maxlength='240' name="workflow_name" id="workflow_name" value="<?php echo isset($workflowdetail['workflow_name']) && !empty($workflowdetail['workflow_name']) ? $workflowdetail['workflow_name'] : ""; ?>" />
                                    <div class="field_placeholder mark_mandatory"><span><?php echo __('Workflow Name');?></span></div>
                                </div>
                                <div class="select_field_wrapper" style="margin-bottom:20px;">
                                    <select class="workflow_type workflow_project" name="workflow_project" placeholder="<?php echo __('Projects');?>">
                                        <option value=""><?php echo __('Select Project'); ?></option>
                                        <?php foreach($projects as $k => $v){ ?>
                                            <option value="<?php echo $k;?>"
                                                <?php if($k == @$workflowdetail['workflow_project']){ echo 'selected="selected"'; } ?>
                                            ><?php echo $v;?></option>
                                        <?php  } ?>
                                    </select>
                                </div>
                                <div class="submit-workflow">
                                    <a href="javascript:void(0)" id="next_work_1" class="btn cmn_size btn_cmn_efect cmn_bg btn-info" onclick="workflowNext('conditions')"><?php echo __('Next'); ?><div class="ripple-container"></div></a>
                                </div>
                            </div>
                            <!-- Seep 2 -->
                            <div class="workflow-step-2 w-steps" style="display:none;">
                                <div class="row w-row">
                                    <div class="col-sm-4">
                                        <div class="select_field_wrapper">
                                            <select class="workflow_type workflow_cnd" name="workflow_cnd" placeholder="<?php echo __('Conditions');?>" onchange="getWorkflowConditionValue()">
                                                <option value=""><?php echo __('Select Condition'); ?></option>
                                                <?php foreach($conditions as $k => $v){ ?>
                                                    <option value="<?php echo $k;?>"
                                                        <?php if($k == @$workflowdetail['workflow_cnd']){ echo 'selected="selected"'; } ?>
                                                    ><?php echo $v;?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="select_field_wrapper">
                                            <select class="workflow_type workflow_opt" name="workflow_opt">
                                                <option value="0" <?php if(0 == @$workflowdetail['operation']){ echo 'selected="selected"'; } ?>><?php echo __('Is equal to'); ?></option>
                                                <option value="1" <?php if(1 == @$workflowdetail['operation']){ echo 'selected="selected"'; } ?>><?php echo __('Is not equal to'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="select_field_wrapper workflow_cond-val-wrapper">
                                            <select class="workflow_type workflow_cond-val" name="workflow_cond_val">
                                                <option value=""><?php echo __('Select Value'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="submit-workflow">
                                    <a href="javascript:void(0)" class="btn cmn_size btn_cmn_efect cmn_bg btn-info" onclick="workflowNext('action')"><?php echo __('Next'); ?><div class="ripple-container"></div></a>
                                </div>
                            </div>
                            <!-- Seep 3 -->
                            <div class="workflow-step-3 w-steps" style="display:none;">
                                <div class="row a-row">
                                    <div class="col-sm-4">
                                        <div class="select_field_wrapper">
                                            <select class="workflow_type workflow_action" name="workflow_action" placeholder="<?php echo __('Conditions');?>" onchange="setActionValue();">
                                                <option value=""><?php echo __('Select Action'); ?></option>
                                                <?php foreach($actions as $k => $v){ ?>
                                                    <option value="<?php echo $k;?>"
                                                        <?php if($k == @$workflowdetail['workflow_action']){ echo 'selected="selected"'; } ?>
                                                    ><?php echo $v;?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="select_field_wrapper action_value_wrapper action_user_wrapper" style="display:none; width:50%;">
                                            <select class="workflow_type workflow_action_user" name="workflow_action_user" placeholder="<?php echo __('Users');?>">
                                                <option value=""><?php echo __('Select User'); ?></option>
                                                <?php foreach($actions as $k => $v){ ?>
                                                    <option value="<?php echo $k;?>"><?php echo $v;?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                        <div class="select_field_wrapper action_value_wrapper action_to_wrapper" style="display:none;">
                                            <select class="workflow_type workflow_action_to" name="workflow_action_to" placeholder="<?php echo __('To');?>">
                                                <option value=""><?php echo __('Select to email'); ?></option>
                                            </select>
                                        </div>
                                        <div class="select_field_wrapper action_value_wrapper action_cc_wrapper" style="display:none;">
                                            <select class="workflow_type workflow_action_cc" name="workflow_action_cc" placeholder="<?php echo __('Cc');?>">
                                                <option value=""><?php echo __('Select cc email'); ?></option>
                                            </select>
                                        </div>
                                        <div class="field_wrapper action_value_wrapper action_textbox_wrapper" style="display:none;">
                                            <textarea name="action_box" id="action_box" rows="10" style="min-height:200px;" placeholder="<?php echo __('message'); ?>"></textarea>
                                        </div>
                                        <div class="field_wrapper nofloat_wrapper action_value_wrapper action_text_wrapper" style="display:none;">
                                            <input type="text" maxlength='240' name="workflow_action_name" id="workflow_action_name" />
                                            <div class="field_placeholder mark_mandatory"><span id="action_n"><?php echo __('Workflow Name');?></span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="submit-workflow">
                                    <div class="submit">
                                        <input id="createWork" class="btn cmn_size btn_cmn_efect cmn_bg btn-info" type="submit" value="<?php echo __('Save'); ?>">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
					<?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $(".workflow_type").select2();
        <?php if(isset($workflowdetail['workflow_cnd']) && !empty($workflowdetail['workflow_cnd'])){ ?>
        getWorkflowConditionValue();
        setActionValue();
        <?php } ?>

        var ses_message = '<?php echo '';?>';
        if(ses_message){
            showTopErrSucc('error',ses_message);
        }
    });
    function workflowNext(type){
        if(type=='type'){
            $(".w-steps").hide();
            $(".workflow-step-1").show();
            $(".workflow-menu").removeClass('active');
            $(".workflow-choose-type").addClass('active');
        }
        if(type=='conditions'){
            if($("#workflow_name").val().trim()){
                if($(".workflow_project ").val().trim()){
                    $("#next_work_1").addClass('disabled');
                    $.post("<?php echo HTTP_ROOT ;?>projects/checkWorkflowName",{name:$("#workflow_name").val().trim(), pid:$(".workflow_project").val(),wid:$("#wid").val()},function(res){
                        if(res.status =='success'){
                            $(".w-steps").hide();
                            $(".workflow-step-2").show();
                            $(".workflow-menu").removeClass('active');
                            $(".workflow-choose-conditions").addClass('active');
                        }else{
                            showTopErrSucc('error',res.message);
                        }
                        $("#next_work_1").removeClass('disabled');
                    },'json');
                }else{
                    showTopErrSucc('error',_('Please select project.'));
                }
            }else{
                showTopErrSucc('error',_('Please Enter the workflow name'));
            }

        }
        if(type=='action'){
            var sts = false;
            $(".w-row").each(function(){
                if(
                    $(this).find('.workflow_cnd').val() && $(this).find('.workflow_cnd').val().trim() &&
                    $(this).find('.workflow_opt').val() && $(this).find('.workflow_opt').val().trim() &&
                    $(this).find('.workflow_cond-val').val() && $(this).find('.workflow_cond-val').val().trim()
                ){
                    sts = true;
                }else{
                    sts = false;
                    return false;
                }
            })
            if(!$("#workflow_name").val().trim() && $(".workflow_project ").val().trim()){
                showTopErrSucc('error',_('Please Enter the workflow name and select project'));
                return false;
            }
            if(!sts){
                showTopErrSucc('error',_('Please Enter the workflow conditions'));
            }else{
                $(".w-steps").hide();
                $(".workflow-step-3").show();
                $(".workflow-menu").removeClass('active');
                $(".workflow-choose-action").addClass('active');
            }

        }
    }
    function checkValidation(){
        var sts = false;
        $(".a-row").each(function(){
            if($(this).find('.workflow_action').val().trim() &&
                (
                    $(this).find('.workflow_action_user').val().trim() ||
                    ( $(this).find('.workflow_action_to').val().trim()  &&  $(this).find('#action_box').val().trim() ) ||
                    $(this).find('#workflow_action_name').val().trim()
                )
            ){
                sts = true;
            }else{
                sts = false;
                return false;
            }
        })
        if(!sts){
            showTopErrSucc('error',_('Please Enter the workflow actions'));
        }
        return sts;
    }
    function getWorkflowConditionValue(){
        var conditionValue =  $(".workflow_cnd").val();
        $('.workflow_cond-val').html('').val('');
        if(conditionValue){
            getConditionOptions(conditionValue);
        }
    }
    function getConditionOptions(val){
        $.post("<?php echo HTTP_ROOT ;?>projects/getConditionOptions",{value:val, pid:$(".workflow_project").val()},function(res){
            if(res.status == 'success'){
                if(res.result){
                    str = "<option value=''>"+_('Select Value')+"</option>";
                    $.each(res.result, function (key, data) {
                        str += "<option value='"+key+"'>"+data+"</option>";
                    });
                    $('.workflow_cond-val').html(str);
                    <?php if(isset($workflowdetail['value']) && !empty($workflowdetail['value'])){ ?>
                    $('.workflow_cond-val').val('<?php echo $workflowdetail['value'];?>');
                    <?php } ?>
                }
            }
        },'json');
    }
    function setActionValue(){
        var actionValue =  $(".workflow_action").val();
        $('.action_value_wrapper').hide();
        if(actionValue && parseInt(actionValue) == 2){
            getActionOptions(actionValue,'assign');
        }else if(actionValue && (parseInt(actionValue) == 3 || parseInt(actionValue) == 6 )){
            $('.action_text_wrapper').show();
            if(parseInt(actionValue) == 3){
                $("#action_n").html(_("Task Title"));
            }else{
                $("#action_n").html(_("Tag Name"));
            }
            $('#workflow_action_name').val('<?php echo @$workflowdetail['workflow_action_name'];?>');
        }else if(actionValue){
            getActionOptions(2,'email');
            $('.action_to_wrapper').show();
            $('.action_cc_wrapper').show();
            $('.action_textbox_wrapper').show();
            $('#action_box').val(`<?php echo @$workflowdetail['action_box'];?>`);
        }

    }
    function getActionOptions(val,fields){
        $.post("<?php echo HTTP_ROOT ;?>projects/getActionOptions",{value:val, pid:$(".workflow_project").val()},function(res){
            if(res.status == 'success'){
                if(res.result){
                    str = "";
                    $.each(res.result, function (key, data) {
                        str += "<option value='"+key+"'>"+data+"</option>";
                    });
                    if(fields == 'assign'){
                        $('.workflow_action_user').html("<option value=''>"+_('Select User')+"</option>"+str);
                        $('.action_user_wrapper').show();
                        <?php if(isset($workflowdetail['workflow_action_user']) && !empty($workflowdetail['workflow_action_user'])){ ?>
                        $('.workflow_action_user').val('<?php echo $workflowdetail['workflow_action_user'];?>');
                        <?php } ?>

                    }else if(fields == 'email'){
                        $('.workflow_action_to').html("<option value=''>"+_('Select to email')+"</option>"+str);
                        $('.workflow_action_cc').html("<option value=''>"+_('Select cc email')+"</option>"+str);

                        <?php if(isset($workflowdetail['workflow_action_to']) && !empty($workflowdetail['workflow_action_to'])){ ?>
                        $('.workflow_action_to').val('<?php echo $workflowdetail['workflow_action_to'];?>');
                        $('.workflow_action_cc').val('<?php echo $workflowdetail['workflow_action_cc'];?>');
                        <?php } ?>
                    }
                }
            }
        },'json');
    }

</script>
