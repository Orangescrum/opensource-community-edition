<?= $this->Form->create(null, [
    'url' => ['controller' => 'ProjectUsers', 'action' => 'assignProjectUserRole'],
    'name' => 'projectuserasgnrole',
    'id' => 'ProjectUserAssignRoleUsrFormuser'
]) ?>

<div class="row">
    <div class="col-md-12">
        <div class="ad_prj_usr_tbl user_role_modal">
            <input type="hidden" id="adusrprojnm" value="">
            <p class="user_on_proj"><?php echo __('Project(s) of this User'); ?></p>
            <table class="table users_no">
                <tr class="hdr_tr">
                    <th class="user_name"><?php echo __('Projects'); ?></th>
                    <th><?php echo __('Role'); ?></th>
                    <th><?php echo __('Action'); ?></th>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="role_user_scroll">
                            <table class="table asign_role">
                                <?php
                                $userCount = count($memsExstArr);
                                $count = 0;
                                $totids = [];
                                if ($userCount) {
                                    foreach ($memsExstArr as $memsAvlArr) {
                                        $project_id = $memsAvlArr['Projects']['id'];
                                        $totids[] = $project_id;
                                        $project_name = ucfirst($memsAvlArr['Projects']['name']);
                                        $count++;
                                        $class = ($count % 2 == 0) ? 'row_col' : 'row_col_alt';
                                        ?>
                                        <tr id="extlisting<?php echo $project_id; ?>" class="rw-cls1 <?php echo $class; ?>">
                                            <td <?php echo $class; ?>>
                                                <div class="fl" title="<?php echo $project_name; ?>">
                                                    <?php echo $this->Format->shortLength($project_name, 25); ?>
                                                </div>
                                                <div id="deleteImg_<?php echo $project_id; ?>" title="<?php echo __('Delete'); ?>"
                                                    class="dropdown_cross fr"
                                                    style="display:none; color:#D4696F; font-weight:bold; cursor:pointer"
                                                    onclick="deleteUsersInProject('<?php echo $project_id; ?>', '', '<?php echo urlencode($project_name); ?>');">
                                                    &times;</div>
                                                <div class="cb"></div>
                                            </td>
                                            <td <?php echo $class; ?>>
                                                <div class="">
                                                    <?= $this->Form->hidden('ProjectUser.id.', ['value' => $memsAvlArr['ProjectUsers']['id']]) ?>
                                                    <?= $this->Form->hidden('ProjectUser.user_id.', ['value' => $memsAvlArr['id']]) ?>
                                                    <?php if ($memsAvlArr['CompanyUsers']['role_id'] == 699): ?>
                                                        <?= $this->Form->hidden('ProjectUser.role_id.', ['value' => 699]) ?>
                                                        <span><?= __('Guest') ?></span>
                                                    <?php else: ?>
                                                        <?= $this->Form->control('ProjectUser.role_id.', [
                                                            'value' => $memsAvlArr['role_id'],
                                                            'class' => 'form-control select_role_add',
                                                            'type' => 'select',
                                                            'options' => $roles,
                                                            'onchange' => 'addActionId(this)',
                                                            'id' => 'ProjectUserRoleId' . $memsAvlArr['ProjectUsers']['id'],
                                                            'label' => false,
                                                            'div' => false
                                                        ]) ?>
                                                    <?php endif ?>
                                                </div>
                                                <div class="cb"></div>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0)" class="actionViewId"
                                                    data-roleId="<?php echo $memsAvlArr['role_id']; ?>" data-roleName=""
                                                    onclick="manage_project_role(this,'user')">
                                                    <i class="material-icons">visibility</i>
                                                    <?php echo __('View'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3">
                                            <center class="fnt_clr_rd"><?php echo __('No user(s) available.'); ?></center>
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
<?php $this->Form->end(); ?>
<input type="hidden" name="hid_cs" id="hid_cs" value="<?php echo $count; ?>" />
<input type="hidden" name="totid" id="totid" value="<?php echo implode('|', $totids); ?>" />
<input type="hidden" name="chkID" id="chkID" value="" />
<input type="hidden" name="slctcaseid" id="slctcaseid" value="" />
<input type="hidden" id="getusercount" value="<?php echo $userCount; ?>" readonly="true" />
<input type="hidden" name="user_id" id="puserId" value="<?php echo $usrid; ?>" />
<input type="hidden" name="user_name" id="puser_name" value="<?php echo $usrname; ?>" />
<input type="hidden" name="cntmng" id="cntmng" value="" />
<script>
    $(document).ready(function () {
        $(".select_role_add").select2().on('select2:select', function (evt) {
            $(this).closest('tr').find('.actionViewId').attr('data-roleId', $(this).val());
        });
    });
</script>