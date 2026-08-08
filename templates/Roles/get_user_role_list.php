<style>
    .btn.role_user_form_back_btn {
        margin: 0px;
        padding: 3px 10px;
        border: 1px solid #ddd;
        font-size: 12px;
        vertical-align: middle;
    }

    .btn.role_user_form_add_btn {
        margin: 0px 0px 10px;
        padding: 3px 10px;
        border: 1px solid #ddd;
        font-size: 12px;
        vertical-align: middle;
    }

    .btn.role_user_form_add_btn span {
        font-size: 15px;
        margin-right: 5px;
    }
</style>
<div class="row" id="RoleUserForm" style="display:none;">
    <div class="col-md-12">
        <a href="javascript:void(0)" class="btn role_user_form_back_btn fr" onclick="displayRoleUserList();"><span class="os_sprite back-detail"></span><?php echo __('Back'); ?></a>
        <div class="cb"></div>
        <?php echo $this->Form->create($role, array('url' => '/roles/get_user_role_list', 'name' => 'roleedit' , 'name' => 'RoleeditForm')); ?>
        <?php echo $this->Form->input('role_id', array('type' => 'hidden', 'value' => $roleId)); ?>
        <div class="form-group cmn_ui_hider">
            <label class="control-label" for="role_user_auto"><?php echo __("Users"); ?></label>
            <select name="userIds" id="role_user_auto" class="form-control mod-wid-153 fl min_mgt"></select>
        </div>
        <div class="mtop20 mbtm15 text-right">
            <span id="roleloader" style="display:none;">
                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" />
            </span>
            <span id="rolebtn">
                <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size mright10" data-dismiss="modal" onclick="displayRoleUserList();"><?php echo __("Cancel"); ?><div class="ripple-container"></div></button></span>
                <button type="submit" value="Create" name="crtRole" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="return roleAdd('txt_role', 'txt_shortRole', 'roleloader', 'rolebtn');"><i class="icon-big-tick"></i><?php echo __("Add User"); ?></button>
            </span>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>

<div class="row" id="RoleUserList">
    <div class="col-md-12">
        <div class="ad_prj_usr_tbl user_role_modal">
            <?php if ($roleId != 1) { ?>
                <a href="javascript:void(0)" class="btn role_user_form_add_btn fr" onclick="displayRoleUserForm();"><span>+</span><?php echo __('Add Users'); ?></a>
            <?php } ?>
            <div class="cb"></div>
            <table class="table users_no">
                <tr class="hdr_tr">
                    <th class="slno"><?php echo __("Sl.No"); ?></th>
                    <th><?php echo __("User Name"); ?></th>
                    <th><?php echo __("User Email"); ?></th>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="role_user_scroll">
                            <table class="table">
                                <?php
                                $userCount = count($user_list);

                                if ($userCount) {
                                    $i = 1;
                                    foreach ($user_list as $ku => $uv) {
                                        $username = $uv['Users']['name'];
                                        if (empty($username)) {
                                            $uname = explode('@', $uv['Users']['email']);
                                            $username = $uname[0];
                                        }
                                        if ($i % 2 == 0) {
                                            $class = "row_col";
                                        } else {
                                            $class = "row_col_alt";
                                        }
                                ?>
                                        <tr>
                                            <td <?php echo $class; ?>>
                                                <div class="fl">
                                                    <?php echo $i; ?>
                                                </div>
                                                <div class="cb"></div>
                                            </td>
                                            <td <?php echo $class; ?>>
                                                <div class="fl" title="<?php echo $username; ?>">
                                                    <?php echo $this->Format->shortLength($username, 25); ?>
                                                </div>
                                                <div class="cb"></div>
                                            </td>
                                            <td <?php echo $class; ?>>
                                                <div class="fl" title="<?php echo $uv['Users']['email']; ?>">
                                                    <?php echo $uv['Users']['email']; ?>
                                                </div>
                                                <div class="cb"></div>
                                            </td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="2">
                                            <center class="fnt_clr_rd"><?php echo __("No user(s) available."); ?></center>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    function displayRoleUserForm() {
        $("#RoleUserList").slideUp('slow');
        $("#RoleUserForm").slideDown('slow', function() {
            initRoleUserSelect("role_user_auto");
        });
    }

    function displayRoleUserList() {
        $("#RoleUserForm").slideUp('slow');
        $("#RoleUserList").slideDown('slow');
    }
</script>