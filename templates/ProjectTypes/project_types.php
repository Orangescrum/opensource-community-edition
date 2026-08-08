<?php $quickEditOpen = 0;
if (! $this->request->is('ajax')) { ?>
<div id="pt_ajax_response">
    <?php }else{
        $quickEditOpen = $data['quickEditOpen'];
    } ?>
    <style type="text/css">
        .checkbox input[type="checkbox"]:disabled:checked + .checkbox-material .check::before, .checkbox input[type="checkbox"]:disabled:checked + .checkbox-material .check {
            border-color: #639fed !important;
            color: #639fed !important;
        }
        .project_status_cont.setting_label_page .project_label_grid .cstm_tt_wrapp .custom-checkbox.project_level_type {width: 91%;}
        .project_status_cont.setting_label_page .custom-checkbox.checkbox label .tsk-typ-nm {max-width:150px;}
        .setting_label_page h4 {text-align: left;}
    </style>
    <div class="user_profile_con task_type_disn tasktype-sett-page setting_wrapper setting_label_page project_status_cont">
        <div class="row">
            <div class="impexp_div">
                <div class="col-lg-9">
                </div>
                <div class="col-lg-3 text-right">
                    <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="addNewProjectType();">+ <?php echo __('New');?></button>
                </div>
            </div>
        </div>
        <?php if (isset($project_type_custom) && !empty($project_type_custom)) {?>
        <div class="row">
            <div class="col-lg-12 tsk-typ-div import-csv-file project_label_grid">
                <h4><?php echo __('Custom Project Types');?></h4>
                <div class="cstm_tt_wrapp">
                    <?php $cnt = 1;
                    foreach ($project_type_custom as $key => $value) {
                        if ($cnt%3 == 0) {
                            $cb = '<div class="cb"></div>';
                        } else {
                            $cb = "";
                        } ?>
                        <div class="col-lg-4">
                            <div class="light_bg_bkp">
                                <?php 
                                $checked = (intval($value['ProjectType']['is_exist'])) ? 'checked="checked"' : '';
                                $isDelete = (intval($value['ProjectType']['is_used'])) ? 0 : 1;
                                ?>
                                <div id="dv_ps_<?php echo $value['ProjectType']['id'];?>" class="checkbox custom-checkbox add-user-pro-chk project_level_type <?php echo  (!empty($value['ProjectType']['is_default'])&& !empty($checked)) ? "disabled" : ''; ?>">
                                    <label class="dv_tsktyp" data-id="<?php echo $value['ProjectType']['id'];?>" id="checkIdDisbaled<?php echo $value['ProjectType']['id']; ?>" <?php echo  (!empty($value['ProjectType']['is_default'])&& !empty($checked)) ? "style=cursor:not-allowed" : ''; ?>>
                                        <input type="checkbox" <?php echo  (!empty($value['ProjectType']['is_default'])&& !empty($checked)) ? "disabled" : ''; ?> class="all_tt" value="<?php echo $value['ProjectType']['id'];?>" name="data[ProjectType][<?php echo $value['ProjectType']['id'];?>]" <?php echo $checked;?> <?php echo $disabled ?? '';?>/>
                                        <span class="ellipsis-view tsk-typ-nm" rel="tooltip" title="<?php echo $value['ProjectType']['title'];?>"><?php echo $value['ProjectType']['title'];?></span> &nbsp;
                                        <?php if (intval($value['ProjectType']['proj_cnt'])) {?>
                                            (<span class="task-type-cnt" title="<?php echo __('Linked with').' '.$value['ProjectType']['proj_cnt'].' '.__('project(s)');?>"><?php echo $value['ProjectType']['proj_cnt'];?></span>)
                                        <?php }?>
                                        <?php if (intval($value['ProjectType']['company_id'])){ ?>
                                            <span id="edit_dvtsk_<?php echo $value['ProjectType']['id'];?>" style="display: none;">
															<span id="edit_lding_ps_<?php echo $value['ProjectType']['id'];?>" style="display: none;">
																<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading'); ?>..." title="<?php echo __('Loading'); ?>..." />
															</span>
															<span id="edit_tsk_<?php echo $value['ProjectType']['id']; ?>">
																<a href="javascript:void(0);" class="custom-t-type" onclick="editProjectType(this);" data-name="<?php echo $value['ProjectType']['title']; ?>" data-id="<?php echo $value['ProjectType']['id']; ?>" data-sortname="">
																	<i class="material-icons" title="<?php echo __('Edit'); ?>" id="edit_tsk_id<?php echo $value['ProjectType']['id']; ?>">&#xE254;</i>
																</a>
															</span>
														</span>
                                        <?php } ?>
                                        <?php if (intval($value['ProjectType']['company_id']) && $isDelete){ ?>
                                            <span id="del_dvtsk_<?php echo $value['ProjectType']['id'];?>" style="display: none;">
															<span id="lding_ps_<?php echo $value['ProjectType']['id'];?>" style="display: none;">
																<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading'); ?>..." title="<?php echo __('Loading'); ?>..." />
															</span>
															<span id="del_ps_<?php echo $value['ProjectType']['id']; ?>">
																<a href="javascript:void(0);" class="custom-t-type" onclick="deleteProjectType(this);" data-name="<?php echo $value['ProjectType']['title']; ?>" data-id="<?php echo $value['ProjectType']['id']; ?>">
																	<i class="material-icons" title="<?php echo __('Delete'); ?>" id="del_ps_id<?php echo $value['ProjectType']['id']; ?>">&#xE872;</i>
																</a>
															</span>
														</span>
                                        <?php } ?>
                                    </label>
                                </div>
                                <?php //} ?>
                                <div class="cb"></div>
                            </div>
                        </div>
                        <?php  echo $cb;$cnt++; } ?>

                </div>
            </div>
            <?php } ?>


            <?php if(empty($project_status) && empty($project_type_custom)){?>
                <div class="row impexp_div">
                    <div class="col-lg-7  padlft-non padrht-non">
                        <div class="tsk-typ-txt">
                            <?php echo __('Project Types are independent of Projects, please create your first Project Type.');?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>

        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                $.material.init();
                $('.dv_tsktyp').hover(function () {
                    var tid = $(this).attr('data-id');
                    if ($(this).find("#del_dvtsk_" + tid).length || $(this).find("#edit_dvtsk_" + tid).length) {
                        $(this).find("#del_dvtsk_" + tid).show();
                        $(this).find("#edit_dvtsk_" + tid).show();
                    }
                }, function () {
                    var tid = $(this).attr('data-id');
                    if ($(this).find("#del_dvtsk_" + tid).length || $(this).find("#edit_dvtsk_" + tid).length) {
                        $(this).find("#del_dvtsk_" + tid).hide();
                        $(this).find("#edit_dvtsk_" + tid).hide();
                    }
                });
                /* check/uncheck all default task type */
                $(".dflt_tt_wrapp").find(".all_tt").not("[disabled]").click(function () {
                    if ($(this).is(":checked")){
                        checkAllTT('default');
                    }else{
                        $("#all_default_task_type").prop("checked", false);
                    }
                });
                $(".cstm_tt_wrapp").find(".all_tt").not("[disabled]").click(function () {
                    if ($(this).is(":checked")){
                        checkAllTT('custom');
                    }else{
                        $("#all_custom_task_type").prop("checked", false);
                    }
                });
                checkAllTT('default');
                checkAllTT('custom');
                /* end */
                $(document).on('keyup','#task_type_nm',function(){
                    if($(this).closest('.verror').length){
                        //$('#tterr_msg').html('');
                        $("#task_type_nm").closest('.field_wrapper').removeClass('verror');
                    }
                });
                $(document).on('keyup','#task_type_nm_edit',function(){
                    if($(this).closest('.verror').length){
                        //$('#tterr_msg_edit').html('');
                        $("#task_type_nm_edit").closest('.field_wrapper').removeClass('verror');
                    }
                });
            });

            $(document).on('change', '.all_tt', function (e) {
                var id = $(this).val();
                var is_active = ($(this).is(':checked')) ? 1 : 0;
                $.post(HTTP_ROOT + "project-types/saveProjectType", { id: id, is_active: is_active }, function (res) {
                    if (res.status == 0) {
                        showTopErrSucc('error', '<?php echo __("Project type can not update now. Please try again later."); ?>.');
                    }
                }, 'json');
            })

            $(document).on('click', '[id^="checkIdDisbaled"]', function (e) {
                var typeId = $(this).attr('data-id');
                if ($(e.target).is('#edit_tsk_id' + typeId)) {
                    e.preventDefault();
                    //your logic for the button comes here
                } else if ($(e.target).is('#del_ps_id' + typeId)) {
                    e.preventDefault();
                } else {
                    var checkDisable = $(this).find(':checkbox.all_tt').attr('disabled');
                    if (checkDisable) {
                        $.post(HTTP_ROOT + "project-types/checkProjectType", {'typeId': typeId}, function (res) {
                            var msg = "<?php echo __("Sorry, You can't uncheck the project type because it has been used in the list of project(s)");?> - " + res;
                            showTopErrSucc('error', msg,1);
                        });
                    }
                }
            });
            function checkAllTT(typ){
                var cb_id = (typ=='default')?'all_default_task_type':'all_custom_task_type';
                var cb_class = (typ=='default')?'dflt_tt_wrapp':'cstm_tt_wrapp';
                var isAllChecked = 0;
                $("."+cb_class).find(".all_tt").each(function(){
                    if(!this.checked)
                        isAllChecked = 1;
                })
                if(isAllChecked == 0){
                    $("#"+cb_id).prop("checked", true);
                }else {
                    $("#"+cb_id).prop("checked", false);
                }
            }
        </script>
        <?php if (! $this->request->is('ajax')) { ?>
    </div>
<?php } ?>
