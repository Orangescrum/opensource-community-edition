<?= $this->Form->create(null, [
    'url' => ['controller' => 'ProjectUsers', 'action' => 'assignProjectUserRole'],
    'name' => 'projectuserasgnrole',
    'id' => 'ProjectUserAssignRoleForm'
]) ?>

<div class="row">
    <div class="col-md-12">
        <div class="ad_prj_usr_tbl user_role_modal">
            <input type="hidden" id="adusrprojnm" value="<?php echo $this->Format->formatText($pjname); ?>">
            <p class="user_on_proj"><?php echo __('User(s) in this Project'); ?></p>
            <table class="table users_no">
                <tr class="hdr_tr">
                    <th class="user_name"><?php echo __('Users'); ?></th>
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
                                $class = '';
                                $totCase = 0;
                                $totids = '';
                                if ($userCount) {
                                    $typ = '';
                                    foreach ($memsExstArr as $memsAvlArr) {
                                        $user_id = $memsAvlArr['id'];
                                        $user_name = ucfirst($memsAvlArr['name']);
                                        $user_shortName = $memsAvlArr['short_name'];
                                        $user_email = $memsAvlArr['email'];
                                        $user_istype = $memsAvlArr['istype'];
                                        $count++;
                                        $class = ($count % 2 == 0) ? 'row_col' : 'row_col_alt';
                                        ?>
                                        <tr id="extlisting<?php echo $user_id; ?>" class="rw-cls1 <?php echo $class; ?>">
                                            <td <?php echo $class; ?>>
                                                <div class="fl" title="<?php echo $user_email; ?>">
                                                    <?php echo $this->Format->shortLength($user_name, 25); ?>
                                                </div>
                                                <div id="deleteImg_<?php echo $user_id; ?>" title="<?php echo __('Delete'); ?>"
                                                    class="dropdown_cross fr"
                                                    style="display:none;color:#D4696F;font-weight:bold;cursor:pointer"
                                                    onclick="deleteUsersInProject('<?php echo $user_id; ?>', '<?php echo $projid; ?>', '<?php echo urlencode($user_name); ?>');">
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
                                                    onclick="manage_project_role(this)"><i class="material-icons">visibility</i>
                                                    <?php echo __('View'); ?><a>
                                            </td>
                                        </tr>
                                        <?php
                                        $totids .= $user_id . '|';
                                        $typ = $user_istype;
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
<input type="hidden" name="totid" id="totid" value="<?php echo $totids; ?>" />
<input type="hidden" name="chkID" id="chkID" value="" />
<input type="hidden" name="slctcaseid" id="slctcaseid" value="" />
<input type="hidden" id="getusercount" value="<?php echo $userCount; ?>" readonly="true" />
<input type="hidden" name="project_id" id="projectId" value="<?php echo $projid; ?>" />
<input type="hidden" name="project_name" id="project_name" value="<?php echo $pjname; ?>" />
<input type="hidden" name="cntmng" id="cntmng" value="<?php echo $cntmng; ?>" />
<script>
    $(document).ready(function () {
        $(".select_role_add").select2().on('select2:select', function (evt) {
            //addActionId(this)
            $(this).closest('tr').find('.actionViewId').attr('data-roleId', $(this).val());
        });

    });
</script>