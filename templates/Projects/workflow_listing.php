<div class="slide_rht_con new-cmn-table">
    <div class="task_listing setting_wrapper add_custom_fld_page">
        <div class="row">
            <div class="col-lg-3 text-left">
                <h3 class="noborder" id="cfpage-title"><?php echo __('Workflow Automation'); ?></h3>
            </div>
            <div class="col-lg-9 d-flex">
                <div class="search_new_cf">
                    <div class="d-flex">
                        <div class="new_custom_fld_btn ml-15">
                            <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size cu_add" onclick="addNewWorkflow()" style=""><i class="material-icons add_plus">add</i> <?php echo __('New'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="custom_field_container_dv">
                    <div class="row mtop20">
                        <div class="col-md-12">
                            <div class="cmn_custom_dtable">
                                <input type="hidden" value="60" id="cu_user_limit">
                                <input type="hidden" value="13" id="cu_list_count">
                                <table class="table custom_fld_list_table m-btm0" id="workflow_automation_table">
                                    <thead>
                                    <tr>
                                        <th class="th_2"><?php echo __('Name'); ?></th>
                                        <th class="th_2"><?php echo __('Project'); ?></th>
                                        <th class="th_7 text-center"><?php echo __('Action'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($workflows){
                                        foreach($workflows as $k=>$v){ ?>
                                            <tr>
                                                <td>
                                                    <?php echo $v['name'];?>
                                                </td>
                                                <td>
                                                    <?php echo $v['project']['name'] ?? '';?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0);" class=" custom-t-type" onclick="editworkflow(this,'<?php echo $v['id']; ?>');">
                                                        <i class="edit-link material-icons" title="<?php echo __('Edit'); ?>" id="edit_tsk_id">&#xE254;</i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="custom-t-type" onclick="deleteWorkflow(this,'<?php echo $v['id']; ?>');">
                                                        <i class="delete-link material-icons" title="<?php echo __('Delete'); ?>">&#xE872;</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php }
                                    }else{ ?>
                                        <tr id="custom_status_tr_255" data-seqid="255">
                                            <td colspan="3" style="text-align:center; color:red;">
                                                <?php echo __('No workflow found!'); ?>
                                            </td>
                                        </tr>
                                    <?php }  ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        var ses_message = getCookie('workflow_message');
        if(typeof ses_message != 'undefined' && ses_message){
            showTopErrSucc('success',(ses_message == 'updated') ? _("Workflow updated successfully.") : _("Workflow added successfully."));
            delete_cookie('workflow_message');
        }
    });

    function addNewWorkflow(){
        window.location = "<?php echo HTTP_ROOT; ?>projects/workFlowSettings";
    }
    function deleteWorkflow(obj,id){
        if(confirm(_("Are you sure you want to delete the workflow?"))){
            $.post("<?php echo HTTP_ROOT ;?>projects/deleteWorkflowAutomation",{id:id},function(res){
                if(res.status == 'success'){
                    $(obj).closest('tr').remove();
                    showTopErrSucc('success',res.message);
                    if(!$("#workflow_automation_table tbody tr").length){
                        $("#workflow_automation_table tbody").html(`<tr><td colspan="3" style="text-align:center; color:red;">${_("No workflow found!")}</td></tr>`);
                    }
                }else{
                    showTopErrSucc('error',res.message);
                }
            },'json');
        }
    }
    function editworkflow(obj,id){
        window.location = "<?php echo HTTP_ROOT; ?>projects/workFlowSettings/"+id;
    }
</script>
