<style type="text/css">
    .checkbox input[type="checkbox"]:disabled:checked + .checkbox-material .check::before, .checkbox input[type="checkbox"]:disabled:checked + .checkbox-material .check {
        border-color: #639fed !important;
        color: #639fed !important;
    }
    .tsk-typ-txt {border: none;text-align: left;background: none;color: #ff0000;font-size: 13px;}
</style>
<div class="setting_wrapper user_profile_con tasktype-sett-page task_type_disn setting_label_page">
    <!--Tabs section starts -->
    <div class="row">
        <div class="impexp_div">
            <div class="col-lg-12 text-right">
                <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="addNewLabel();">+ <?php echo __('New');?></button>
            </div>
        </div>
    </div>
    <?php if (isset($labels) && !empty($labels)) {?>
        <div class="row">
            <div class="col-lg-12 tsk-typ-div import-csv-file company_label_grid">
                <form name="task_labels" id="task_labels" method="post" action="javascript:void(0);">
                    <?php
                    $cnt = 1;
                    $custom = 0;
                    $default = 0;
                    $t_key = 0;
                    foreach ($labels as $key => $value) {
                        $t_key = $key+1;
                        if ($cnt%4 == 0) {
                            $cb = '<div class="cb"></div>';
                        } else {
                            $cb = "";
                        }

                        $checked = '';
                        if (isset($value['Label']['is_active']) && !empty($value['Label']['is_active'])) {
                            if ($value['Label']['is_active']==1) {
                                $checked = 'checked="checked"';
                            } else {
                                $checked = '';
                            }
                        }
                        if (intval($value['0']['total_label'])) {
                            $disabled = 'disabled="true"';
                            $isDelete = 0;
                        } else {
                            $isDelete = 1;
                            $disabled = '';
                        }
                        ?>


                        <?php
                        if($value['Label']['company_id'] != 0 && !$custom){
                            $custom = 1;
                            ?>
                            <div class="mbtm15"><h3><?php echo __('Company Level Labels');?></h3></div>
                        <?php }else if($value['Label']['company_id'] == 0 && !$default){ $default = 1;?>
                            <div class="mtb"><h3><?php echo __('Default Level Labels');?></h3></div>
                        <?php }?>
                        <div id="dv_tsk_<?php echo $value['Label']['id'];?>" class="checkbox custom-checkbox add-user-pro-chk">
                            <label class="dv_tsktyp" data-id="<?php echo $value['Label']['id'];?>" id="checkIdDisbaled<?php echo $value['Label']['id']; ?>" <?php echo  (!empty($value['Label']['is_default'])&& !empty($checked)) ? "style=cursor:not-allowed" : ''; ?>>
                                <input type="checkbox" class="all_tt" value="<?php echo $value['Label']['id'];?>" name="data[Label][<?php echo $value['Label']['id'];?>]" <?php echo $checked;?> />
                                <span class="ellipsis-view tsk-typ-nm" rel="tooltip" title="<?php echo $value['Label']['lbl_title'];?>"><?php echo $value['Label']['lbl_title'];?></span>
                                <?php if (intval($value['0']['total_label'])) {?>
                                    <span class="task-type-cnt" title="<?php echo $value['0']['total_label']." ".__('Task(s)');?>">(<?php echo $value['0']['total_label'];?>)</span>
                                <?php }?>
                                <?php if (intval($value['Label']['company_id'])){ ?>
                                    <span id="edit_dvtsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                    <span id="edit_lding_tsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading');?>..." title="<?php echo __('Loading');?>..." />
                                    </span>
                                                <span id="edit_tsk_<?php echo $value['Label']['id']; ?>">
                                                    <a href="javascript:void(0);" class="custom-t-type" onclick="editLabel(this);" data-name="<?php echo $value['Label']['lbl_title']; ?>" data-id="<?php echo $value['Label']['id']; ?>" data-sortname="<?php echo $value['Label']['lbl_title']; ?>">
                                                        <i class="material-icons" title="<?php echo __('Edit');?>" id="edit_tsk_id<?php echo $value['Label']['id']; ?>">&#xE254;</i>
                                        </a>
                                    </span>
                                </span>
                                <?php } ?>
                                <?php if (intval($value['Label']['company_id']) && $isDelete){ ?>
                                    <span id="del_dvtsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                    <span id="lding_tsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading');?>..." title="<?php echo __('Loading');?>..." />
                                    </span>
                                                <span id="del_tsk_<?php echo $value['Label']['id']; ?>">
                                                    <a href="javascript:void(0);" class="custom-t-type" onclick="deleteLabel(this);" data-name="<?php echo $value['Label']['lbl_title']; ?>" data-id="<?php echo $value['Label']['id']; ?>">
                                                        <i class="material-icons" title="<?php echo __('Delete');?>" id="del_tsk_id<?php echo $value['Label']['id']; ?>">&#xE872;</i>
                                        </a>
                                    </span>
                                </span>
                                <?php } ?>
                            </label>
                        </div>
                        <?php if((intval($labels[$t_key]['Label']['company_id']) == 0) && ($default == 0)){ $cnt = 0; ?>
                        <?php } else if($key == (count($labels)-1)){ ?>
                        <?php } ?>
                        <?php
                        $cnt++;
                    }
                    ?>
            </div>
            <div class="cb"></div>
            <!-- <div class="import_btn_div text-right">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="<?php echo __('Loading');?>..." title="<?php echo __('Loading');?>..."  id="loader_img_tt" style="display: none;position: absolute;"/>
                    <button type="button" id="tt_save_btn" name="tt_save_btn" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="return saveLabel();">
                        <span><?php echo __('Save');?></span>
                    </button>
                </div> -->
            <div class="cb"></div>
            </form>
        </div><div class="cb"></div>
    <?php } ?>

    <?php if (isset($labels_custom) && !empty($labels_custom)) {?>
        <div class="tsk-typ-div import-csv-file mtop30 project_label_grid">
            <div class="mbtm15"><h3><?php echo __('Project Labels');?></h3></div>
            <div class="row">
                <?php $cnt =1;
                foreach ($labels_custom as $key => $val) {
                    if ($cnt % 2 == 0) {
                        $cb = '<div class="cb"></div>';
                    } else {
                        $cb = "";
                    } ?>
                    <div class="col-md-6">
                        <div class="light_bg">
                            <h5><?php echo $val[0]['Project']['name']; ?></h5>
                            <?php foreach($val as $k=>$value){
                                $checked = '';
                                if (isset($value['Label']['is_active']) && !empty($value['Label']['is_active'])) {
                                    if ($value['Label']['is_active']==1) {
                                        $checked = 'checked="checked"';
                                    } else {
                                        $checked = '';
                                    }
                                }
                                if (intval($value['0']['total_label'])) {
                                    $disabled = 'disabled="true"';
                                    $isDelete = 0;
                                } else {
                                    $isDelete = 1;
                                    $disabled = '';
                                }
                                ?>

                                <div id="dv_tsk_<?php echo $value['Label']['id'];?>" class="checkbox custom-checkbox add-user-pro-chk project_level_type">
                                    <label class="dv_tsktyp" data-id="<?php echo $value['Label']['id'];?>" id="checkIdDisbaled<?php echo $value['Label']['id']; ?>" <?php echo  (!empty($value['Label']['is_default'])&& !empty($checked)) ? "style=cursor:not-allowed" : ''; ?>>
                                        <input type="checkbox" class="all_tt" value="<?php echo $value['Label']['id'];?>" name="data[Label][<?php echo $value['Label']['id'];?>]" <?php echo $checked;?> />
                                        <span class="ellipsis-view tsk-typ-nm" rel="tooltip" title="<?php echo htmlentities($value['Label']['lbl_title']);?>"><?php echo $value['Label']['lbl_title'];?></span>
                                        <?php if (intval($value['0']['total_label'])) {?>
                                            <span class="task-type-cnt" title="<?php echo $value['0']['total_label']." ".__('Task(s)');?>">(<?php echo $value['0']['total_label'];?>)</span>
                                        <?php }?>
                                        <?php if (intval($value['Label']['company_id'])){ ?>
                                            <span id="edit_dvtsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                    <span id="edit_lding_tsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading');?>..." title="<?php echo __('Loading');?>..." />
                                    </span>
                                                <span id="edit_tsk_<?php echo $value['Label']['id']; ?>">
                                                    <a href="javascript:void(0);" class="custom-t-type" onclick="editLabel(this);" data-name="<?php echo htmlentities($value['Label']['lbl_title']); ?>" data-id="<?php echo $value['Label']['id']; ?>" data-sortname="<?php echo htmlentities($value['Label']['lbl_title']); ?>">
                                                        <i class="material-icons" title="<?php echo __('Edit');?>" id="edit_tsk_id<?php echo $value['Label']['id']; ?>">&#xE254;</i>
                                        </a>
                                    </span>
                                </span>
                                        <?php } ?>
                                        <?php if (intval($value['Label']['company_id']) && $isDelete){ ?>
                                            <span id="del_dvtsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                    <span id="lding_tsk_<?php echo $value['Label']['id'];?>" style="display: none;">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('Loading');?>..." title="<?php echo __('Loading');?>..." />
                                    </span>
                                                <span id="del_tsk_<?php echo $value['Label']['id']; ?>">
                                                    <a href="javascript:void(0);" class="custom-t-type" onclick="deleteLabel(this);" data-name="<?php echo htmlentities($value['Label']['lbl_title']); ?>" data-id="<?php echo $value['Label']['id']; ?>">
                                                        <i class="material-icons" title="<?php echo __('Delete');?>" id="del_tsk_id<?php echo $value['Label']['id']; ?>">&#xE872;</i>
                                        </a>
                                    </span>
                                </span>
                                        <?php } ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="cb"></div>
                        </div>
                    </div>
                    <?php echo $cb; $cnt++;} ?>
            </div>
        </div>

    <?php } ?>

    <?php if (empty($labels) && empty($labels_custom)) {?>
        <div class="row impexp_div">
            <div class="col-lg-7 padlft-non padrht-non">
                <div class="tsk-typ-txt">
                    <?php //echo __('Labels are independent of Projects, but please create a Project to get started.');?>
                    <?php echo __('No Label found. Create you first label.');?>
                </div>
            </div>
            <!-- <div class="col-lg-5 text-right padlft-non padrht-non">
						 <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="addNewLabel();">+ <?php echo __('New Label');?></button>
						</div> -->
            <?php /* if($this->Format->isAllowed('Create Project',$roleAccess)){ ?>
            <div class="col-lg-5 text-right padlft-non padrht-non">
                <button class="btn btn-sm btn_cmn_efect cmn_bg btn-info" onclick="newProject();"><?php echo __('Create Project');?></button>
            </div>
        <?php }  */ ?>
        </div>
        <?php
    }
    ?>
</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
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
    });
    $(document).on('click', '[id^="checkIdDisbaled"]', function (e) {
        var typeId = $(this).attr('data-id');
        if ($(e.target).is('#edit_tsk_id' + typeId)) {
            e.preventDefault();
            //your logic for the button comes here
        } else if ($(e.target).is('#del_tsk_id' + typeId)) {
            e.preventDefault();
        } else {
            var checkDisable = $(this).find(':checkbox.all_tt').attr('disabled');
            if (checkDisable == 'disabled') {
                $.post(HTTP_ROOT + "projects/checkLabel", {'typeId': typeId}, function (res) {
                    var msg = "<?php echo __("Sorry, You can't uncheck the Label because it has been used as Label in the list of task(s)");?> - " + res;
                    showTopErrSucc('error', msg,1);
                });
            }
        }
    });
    $(document).on('change','.all_tt', function(e){
        var id = $(this).val();
        var is_active = ($(this).is(':checked'))?1:0;
        $.post(HTTP_ROOT + "labels/saveLabel",{id:id,is_active:is_active},function(res){
            if(res.status==1){
                // showTopErrSucc('success', '<?php echo __("Label can not update now. Please try again later.");?>.');
            }else{
                showTopErrSucc('error', '<?php echo __("Label can not update now. Please try again later.");?>.');
            }
        },'json');
    })

    function saveLabel() {
        var isTaskIds = 0;
        if ($('#tt_save_btn').hasClass('loginactive')) {
            return false;
        }
        $(".all_tt").each(function() {
            if ($(this).is(":checked")) {
                isTaskIds = 1;
            }
        });
        if (parseInt(isTaskIds)) {
            $('.all_tt').attr('disabled', false);
            $("#tt_save_btn").hide();
            $("#loader_img_tt").show();
            $('#task_labels').attr("action", HTTP_ROOT + "labels/saveLabel");
            document.task_labels.submit();
            return true;
        } else {
            showTopErrSucc('error', '<?php echo __("Check at least one label");?>.');
            return false;
        }
    }

    function editLabel(obj) {
        var nm = $(obj).attr("data-name");
        var id = $(obj).attr("data-id");
        var srt_name = $(obj).attr("data-sortname");
        openPopup();
        $(".edit_label").show();
        $(".loader_dv").hide();
        $('#inner_label_edit').show();
        $("#lterr_msg_edit").html('');
        $.material.init();
        $("#label_nm_edit").val(nm).keyup();
        $("#new-labelid_edit").val(id);
        setTimeout(function() {$("#label_nm_edit").focus();},'1000');

    }
    function addNewLabel(type) {
        $('#newlabel_btn').text(_('Add'));
        $('#label_title').text(_('New Label'));
        openPopup();
        $(".new_label").show();
        $(".loader_dv").hide();
        $('#inner_label').show();
        $("#label_nm").val('');
        //$.material.init();
        $("#lterr_msg").html('');
        $("#project_label").val('');
        $("#project_label").select2().on('change', function(evt) {
            if($(this).find("option:selected").length >1 && $(this).find("option:selected").first().val() ==0){
                $(this).val(0).change();
            }
            if($(this).closest('.verror').length){
                $('#lterr_msg').html('');
                $(this).closest('.select_field_wrapper').removeClass('verror');
            }
        });
        setTimeout(function() {$("#project_label").focus();},'1000');
        $("#newlabel_btn").removeClass('loginactive');

    }

    function deleteLabel(obj) {
        var nm = $(obj).attr("data-name");
        var id = $(obj).attr("data-id");
        if (confirm(_("Are you sure you want to delete") + " '" + nm + "' " + _("label ?"))) {
            $("#del_tsk_" + id).hide();
            $("#lding_tsk_" + id).show();
            $.post(HTTP_ROOT + "labels/deleteLabel", {
                "id": id
            }, function(res) {
                if (parseInt(res)) {
                    var parentDiv  =  $("#dv_tsk_" + id).closest('.col-md-6');
                    $("#dv_tsk_" + id).fadeOut(300, function() {
                        showTopErrSucc('success', _("Label") + " '" + nm + "' " + _("has deleted successfully."));
                        if(!parentDiv.find('div[id^="dv_tsk_"]').not("#dv_tsk_" + id).length){
                            $("#tt_save_btn").hide();
                            window.location.reload();
                        }else{
                            $(this).remove();
                        }
                    });
                } else {
                    $("#lding_tsk_" + id).hide();
                    $("#del_tsk_" + id).show();
                    showTopErrSucc('error', _('Label can not be deleted'));
                }
            });
        }
    }

</script>
