<?php if (!$is_ajax) { ?>
    <div id="ps_ajax_response">
    <?php } ?>
    <style type="text/css">
        .checkbox input[type="checkbox"]:disabled:checked+.checkbox-material .check::before,
        .checkbox input[type="checkbox"]:disabled:checked+.checkbox-material .check {
            border-color: #639fed !important;
            color: #639fed !important;
        }

        .project_status_cont.setting_label_page .custom-checkbox.checkbox label .tsk-typ-nm {
            max-width: 150px;
        }

        .setting_label_page h4 {
            text-align: left;
            margin-left: -5px;
        }
    </style>
    <div
        class="user_profile_con task_type_disn tasktype-sett-page setting_wrapper setting_label_page project_status_cont">
        <?php if (isset($project_status) && !empty($project_status)) { ?>
            <div class="row">
                <div class="impexp_div">
                    <div class="col-lg-9">
                        <div class="tsk-typ-txt">
                            <?php echo __('4 default Project Status listed and the #of Projects associated with them.'); ?>.<br /><?php echo __('Click on the'); ?>
                            "<b>+ <?php echo __('New'); ?></b>"
                            <?php echo __('and add your new Project Status'); ?>.
                        </div>
                    </div>
                    <div class="col-lg-3 text-right">
                        <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="addNewProjectStatus();">+
                            <?php echo __('New'); ?></button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 tsk-typ-div import-csv-file mtop20">
                    <form name="project_status" id="project_status" method="post" action="javascript:void(0);">
                        <?php
                        $cnt = 1;
                        $custom = 0;
                        $default = 0;
                        $t_key = 0;
                        foreach ($project_status as $key => $value) {
                            $t_key = $key + 1;
                            $cb = $cnt % 4 == 0 ? '<div class="cb"></div>' : '';
                            $checked = intval($value['is_exist']) ? 'checked="checked"' : '';
                            $isDelete = intval($value['is_used']) ? 0 : 1;
                            ?>
                            <?php if ($value['company_id'] == 0 && !$default) {
                                $default = 1; ?>
                                <div class="setting_title mbtm15">
                                    <h3><?php echo __('Default Project Status'); ?></h3>
                                </div>
                                <div class="dflt_tt_wrapp">
                                <?php } else if ($value['company_id'] != 0 && !$custom) {
                                $custom = 1; ?>
                                        <div class="cb"></div>
                                        <div class="setting_title">
                                            <h3><?php echo __('Custom Project Status'); ?></h3>
                                        </div>
                                        <h4><?php echo __('Company Project Status'); ?></h4>
                                        <div class="cstm_tt_wrapp">
                                    <?php } ?>
                                    <div id="dv_ps_<?php echo $value['id']; ?>"
                                        class="checkbox custom-checkbox add-user-pro-chk <?php echo (!empty($value['is_default']) && !empty($checked)) ? "disabled" : ''; ?>">
                                        <label class="dv_tsktyp" data-id="<?php echo $value['id']; ?>"
                                            id="checkIdDisbaled<?php echo $value['id']; ?>" <?php echo (!empty($value['is_default']) && !empty($checked)) ? "style=cursor:not-allowed" : ''; ?>>
                                            <input type="checkbox" <?php echo (!empty($value['is_default']) && !empty($checked)) ? "disabled" : ''; ?> class="all_tt"
                                                value="<?php echo $value['id']; ?>"
                                                name="data[ProjectStatus][<?php echo $value['id']; ?>]" <?php echo $checked; ?>         <?php echo $disabled ?? false; ?> />
                                            <span class="ellipsis-view tsk-typ-nm" rel="tooltip"
                                                title="<?php echo $value['name']; ?>"><?php echo $value['name']; ?></span>
                                            &nbsp;
                                            <?php if (intval($value['proj_cnt'])) { ?>
                                                (<span class="task-type-cnt"
                                                    title="<?php echo __('Linked with') . ' ' . $value['proj_cnt'] . ' ' . __('project(s)'); ?>"><?php echo $value['proj_cnt']; ?></span>)
                                            <?php } ?>
                                            <?php if (intval($value['company_id'])) { ?>
                                                <span id="edit_dvtsk_<?php echo $value['id']; ?>"
                                                    style="display: none;">
                                                    <span id="edit_lding_ps_<?php echo $value['id']; ?>"
                                                        style="display: none;">
                                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..."
                                                            title="Loading..." />
                                                    </span>
                                                    <span id="edit_tsk_<?php echo $value['id']; ?>">
                                                        <a href="javascript:void(0);" class="custom-t-type"
                                                            onclick="editProjectStatus(this);"
                                                            data-name="<?php echo $value['name']; ?>"
                                                            data-id="<?php echo $value['id']; ?>" data-sortname="">
                                                            <i class="material-icons" title="Edit"
                                                                id="edit_tsk_id<?php echo $value['id']; ?>">&#xE254;</i>
                                                        </a>
                                                    </span>
                                                </span>
                                            <?php } ?>
                                            <?php if (intval($value['company_id']) && $isDelete) { ?>
                                                <span id="del_dvtsk_<?php echo $value['id']; ?>"
                                                    style="display: none;">
                                                    <span id="lding_ps_<?php echo $value['id']; ?>"
                                                        style="display: none;">
                                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..."
                                                            title="Loading..." />
                                                    </span>
                                                    <span id="del_ps_<?php echo $value['id']; ?>">
                                                        <a href="javascript:void(0);" class="custom-t-type"
                                                            onclick="deleteProjectStatus(this);"
                                                            data-name="<?php echo $value['name']; ?>"
                                                            data-id="<?php echo $value['id']; ?>">
                                                            <i class="material-icons" title="Delete"
                                                                id="del_ps_id<?php echo $value['id']; ?>">&#xE872;</i>
                                                        </a>
                                                    </span>
                                                </span>
                                            <?php } ?>
                                        </label>
                                    </div>
                                    <?php if ((intval($project_status[$t_key]['company_id']??0) != 0) && ($custom == 0)) {
                                        $cnt = 0; ?>
                                        <div class="cb"></div>
                                    </div>
                                <?php } else if ($key == (count($project_status) - 1)) { ?>
                                <?php } ?>
                                <?php
                                $cnt++;
                        }
                        ?>
                            <div class="cb"></div>
                        </div>
                        <div class="cb"></div>
                    </form>
                </div>
                <div class="cb"></div>
            </div>
        <?php } ?>

        <?php if (isset($project_status_custom) && !empty($project_status_custom)) { ?>
            <div class="row">
                <div class="col-lg-12 tsk-typ-div import-csv-file project_label_grid">
                    <h4><?php echo __('Custom Project Status'); ?></h4>
                    <div class="cstm_tt_wrapp">
                        <?php $cnt = 1;
                        foreach ($project_status_custom as $key => $value) {
                            $cb = $cnt % 3 == 0 ? '<div class="cb"></div>' : '';
                            ?>
                            <div class="col-lg-4">
                                <div class="light_bg_bkp">
                                    <?php
                                            $checked = intval($value['is_exist']) ? 'checked="checked"' : '';
                                            $isDelete = intval($value['is_used']) ? 0 : 1;
                                            ?>
                                    <div id="dv_ps_<?php echo $value['id']; ?>"
                                        class="checkbox custom-checkbox add-user-pro-chk project_level_type <?php echo (!empty($value['is_default']) && !empty($checked)) ? "disabled" : ''; ?>">
                                        <label class="dv_tsktyp" data-id="<?php echo $value['id']; ?>"
                                            id="checkIdDisbaled<?php echo $value['id']; ?>" <?php echo (!empty($value['is_default']) && !empty($checked)) ? "style=cursor:not-allowed" : ''; ?>>
                                            <input type="checkbox" <?php echo (!empty($value['is_default']) && !empty($checked)) ? "disabled" : ''; ?> class="all_tt"
                                                value="<?php echo $value['id']; ?>"
                                                name="data[ProjectStatus][<?php echo $value['id']; ?>]" <?php echo $checked; ?>         <?php echo $disabled ?? false; ?> />
                                            <span class="ellipsis-view tsk-typ-nm" rel="tooltip"
                                                title="<?php echo $value['name']; ?>"><?php echo $value['name']; ?></span>
                                            &nbsp;
                                            <?php if (intval($value['proj_cnt'])) { ?>
                                                (<span class="task-type-cnt"
                                                    title="<?php echo __('Linked with') . ' ' . $value['proj_cnt'] . ' ' . __('project(s)'); ?>"><?php echo $value['proj_cnt']; ?></span>)
                                            <?php } ?>
                                            <?php if (intval($value['company_id'])) { ?>
                                                <span id="edit_dvtsk_<?php echo $value['id']; ?>"
                                                    style="display: none;">
                                                    <span id="edit_lding_ps_<?php echo $value['id']; ?>"
                                                        style="display: none;">
                                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..."
                                                            title="Loading..." />
                                                    </span>
                                                    <span id="edit_tsk_<?php echo $value['id']; ?>">
                                                        <a href="javascript:void(0);" class="custom-t-type"
                                                            onclick="editProjectStatus(this);"
                                                            data-name="<?php echo $value['name']; ?>"
                                                            data-id="<?php echo $value['id']; ?>" data-sortname="">
                                                            <i class="material-icons" title="Edit"
                                                                id="edit_tsk_id<?php echo $value['id']; ?>">&#xE254;</i>
                                                        </a>
                                                    </span>
                                                </span>
                                            <?php } ?>
                                            <?php if (intval($value['company_id']) && $isDelete) { ?>
                                                <span id="del_dvtsk_<?php echo $value['id']; ?>"
                                                    style="display: none;">
                                                    <span id="lding_ps_<?php echo $value['id']; ?>"
                                                        style="display: none;">
                                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..."
                                                            title="Loading..." />
                                                    </span>
                                                    <span id="del_ps_<?php echo $value['id']; ?>">
                                                        <a href="javascript:void(0);" class="custom-t-type"
                                                            onclick="deleteProjectStatus(this);"
                                                            data-name="<?php echo $value['name']; ?>"
                                                            data-id="<?php echo $value['id']; ?>">
                                                            <i class="material-icons" title="Delete"
                                                                id="del_ps_id<?php echo $value['id']; ?>">&#xE872;</i>
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
                            <?php echo $cb;
                            $cnt++;
                        } ?>

                    </div>
                </div>
            <?php } ?>


            <?php if (empty($project_status) && empty($project_status_custom)) { ?>
                <div class="row impexp_div">
                    <div class="col-lg-7  padlft-non padrht-non">
                        <div class="tsk-typ-txt">
                            <?php echo __('Project Statuses are independent of Projects, please create your first Project Status.'); ?>
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
                $("#all_default_task_type").change(function () {
                    if (this.checked) {
                        $(".dflt_tt_wrapp").find(".all_tt").not("[disabled]").each(function () {
                            this.checked = true;
                        })
                    } else {
                        $(".dflt_tt_wrapp").find(".all_tt").not("[disabled]").each(function () {
                            this.checked = false;
                        })
                    }
                });
                $("#all_custom_task_type").change(function () {
                    if (this.checked) {
                        $(".cstm_tt_wrapp").find(".all_tt").not("[disabled]").each(function () {
                            this.checked = true;
                        })
                    } else {
                        $(".cstm_tt_wrapp").find(".all_tt").not("[disabled]").each(function () {
                            this.checked = false;
                        })
                    }
                });

                $(".dflt_tt_wrapp").find(".all_tt").not("[disabled]").click(function () {
                    if ($(this).is(":checked")) {
                        checkAllTT('default');
                    } else {
                        $("#all_default_task_type").prop("checked", false);
                    }
                });
                $(".cstm_tt_wrapp").find(".all_tt").not("[disabled]").click(function () {
                    if ($(this).is(":checked")) {
                        checkAllTT('custom');
                    } else {
                        $("#all_custom_task_type").prop("checked", false);
                    }
                });
                checkAllTT('default');
                checkAllTT('custom');
                /* end */
                $(document).on('keyup', '#task_type_nm', function () {
                    if ($(this).closest('.verror').length) {
                        //$('#tterr_msg').html(''); 
                        $("#task_type_nm").closest('.field_wrapper').removeClass('verror');
                    }
                });
                $(document).on('keyup', '#task_type_nm_edit', function () {
                    if ($(this).closest('.verror').length) {
                        //$('#tterr_msg_edit').html(''); 
                        $("#task_type_nm_edit").closest('.field_wrapper').removeClass('verror');
                    }
                });
            });
            $(document).on('change', '.all_tt', function (e) {
                var id = $(this).val();
                var is_active = ($(this).is(':checked')) ? 1 : 0;
                $.post(HTTP_ROOT + "project-statuses/saveProjectStatus", { id: id, is_active: is_active }, function (res) {
                    if (res.status == 0) {
                        showTopErrSucc('error', '<?php echo __("Project status can not update now. Please try again later."); ?>.');
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
                    if (checkDisable == 'disabled') {
                        $.post(HTTP_ROOT + "project-statuses/checkProjectStatus", { 'typeId': typeId }, function (res) {
                            var msg = "<?php echo __("Sorry, You can't uncheck the default project statuses"); ?>";
                            showTopErrSucc('error', msg, 1);
                        });
                    }
                }
            });
            function checkAllTT(typ) {
                var cb_id = (typ == 'default') ? 'all_default_task_type' : 'all_custom_task_type';
                var cb_class = (typ == 'default') ? 'dflt_tt_wrapp' : 'cstm_tt_wrapp';
                var isAllChecked = 0;
                $("." + cb_class).find(".all_tt").each(function () {
                    if (!this.checked)
                        isAllChecked = 1;
                })
                if (isAllChecked == 0) {
                    $("#" + cb_id).prop("checked", true);
                } else {
                    $("#" + cb_id).prop("checked", false);
                }
            }
        </script>
        <?php if (!$this->request->is('ajax')) { ?>
        </div>
    <?php } ?>