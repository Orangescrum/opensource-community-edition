<?php
/**
 * Shared user-action dropdown <li> items.
 *
 * Passed variables:
 *   $menuUser  - the user data array (from either the grid or list view loop)
 *   $idSuffix  - '' for grid view, 'l' for list view (namespaces element IDs)
 *
 * View vars used directly (set by the controller):
 *   $role, $roleAccess, $istype, $userinmorecompany
 */
$_editId = 'edit-exist-usr' . ($idSuffix ? '-' . $idSuffix : '') . $menuUser['id'];
?>
<?php if ($menuUser['CompanyUsers']['user_type'] == 1 || ($menuUser['CompanyUsers']['user_type'] == 2 && SES_ID == $menuUser['id'])) { ?>
    <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)) { ?>
        <li><a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a></li>
    <?php } ?>
    <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
        <li><input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
            <a id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
        </li>
    <?php } ?>
<?php } else { ?>
    <?php if ($role == 'invited') { ?>
        <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
                <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                <span id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>></span>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>"><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Delete User', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-delete-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?del=<?php echo urlencode($menuUser['uniq_id']); ?>&role=<?php echo $this->request->getQuery('role'); ?>" Onclick="return confirm('Are you sure you want to delete \'<?php echo $menuUser['email']; ?>\' ?')"><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Add New User', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $menuUser['qstr']; ?>','<?php echo $menuUser['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a>
            </li>
        <?php } ?>
    <?php } else if ($role == 'recent') { ?>
        <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
                <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                <span id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>></span>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>"><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Add New User', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if (!$menuUser['dt_last_login']) { ?>
                    <a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $menuUser['qstr']; ?>','<?php echo $menuUser['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a>
                <?php } ?>
            </li>
        <?php } ?>
    <?php } else if ($role == 'disable') { ?>
        <?php if ($this->Format->isAllowed('Enable User', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-enable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?act=<?php echo urlencode($menuUser['uniq_id']); ?>&role=<?php echo $this->request->getQuery('role'); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to enable'); ?> \'<?php echo $menuUser['name']; ?>\' ?')"><i class="material-icons">&#xE87A;</i> <?php echo __('Enable'); ?></a>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                <a id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
            </li>
        <?php } ?>
    <?php } else if ($role == 'client') {
        if ($menuUser['CompanyUsers']['is_active'] == 0) { ?>
            <?php if ($this->Format->isAllowed('Enable User', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <a class="icon-enable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?act=<?php echo urlencode($menuUser['uniq_id']); ?>&role=<?php echo $this->request->getQuery('role'); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to enable'); ?> \'<?php echo $menuUser['name']; ?>\' ?')"><i class="material-icons">&#xE87A;</i> <?php echo __('Enable'); ?></a>
                </li>
            <?php } ?>
            <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                    <a id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
                </li>
            <?php } ?>
        <?php } else { ?>
            <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
                </li>
            <?php } ?>
            <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                    <a class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>"><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
                    <span id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>></span>
                </li>
            <?php } ?>
            <?php if ((defined('USER_TYPE') ? USER_TYPE : SES_TYPE) == 1) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <?php if ((defined('USER_TYPE') ? USER_TYPE : SES_TYPE) == 1) { ?>
                        <?php if ($menuUser['is_moderator']) { ?>
                            <a class="icon-moderator icon-remove-modrt" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-type="0" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8F1;</i> <?php echo __('Revoke Moderator'); ?></a>
                        <?php } else { ?>
                            <a class="icon-moderator icon-add-modrt" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-type="1" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8E9;</i> <?php echo __('Grant Moderator'); ?></a>
                        <?php } ?>
                    <?php } ?>
                </li>
            <?php } ?>
            <?php if (($this->Format->isAllowed('Delete User', $roleAccess) && !$menuUser['dt_last_login']) || $this->Format->isAllowed('Disable Users', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <?php if (!$menuUser['dt_last_login']) { ?>
                        <?php if ($this->Format->isAllowed('Delete User', $roleAccess)) { ?>
                            <a class="icon-delete-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?del=<?php echo urlencode($menuUser['uniq_id']); ?>&role=<?php echo $this->request->getQuery('role'); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to delete'); ?> \'<?php echo $menuUser['email']; ?>\' ?')"><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a>
                        <?php } ?>
                    <?php } else { ?>
                        <?php if ($this->Format->isAllowed('Disable Users', $roleAccess)) { ?>
                            <a class="icon-disable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?deact=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to disable'); ?> \'<?php echo $menuUser['name']; ?>\' ?')"><i class="material-icons">&#xE909;</i> <?php echo __('Disable'); ?></a>
                        <?php } ?>
                    <?php } ?>
                </li>
            <?php } ?>
            <?php if ($this->Format->isAllowed('Add New User', $roleAccess)) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <?php if (($istype == 1 || $istype == 2) && !$menuUser['dt_last_login']) { ?>
                        <a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $menuUser['qstr']; ?>','<?php echo $menuUser['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a>
                    <?php } ?>
                </li>
            <?php } ?>
            <?php if (SES_TYPE == 1 || SES_TYPE == 2) { ?>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <?php if ($menuUser['CompanyUsers']['is_client'] == '0') {  ?>
                        <a class="icon-client-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_client=<?php echo urlencode($menuUser['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to mark'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' <?php echo __('as client'); ?> ?')"><i class="material-icons">&#xE7FB;</i> <?php echo __('Mark Client'); ?></a>
                    <?php } else { ?>
                        <a class="icon-revclient-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_client=<?php echo urlencode($menuUser['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to revoke client access from'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Client'); ?></a>
                    <?php } ?>
                </li>
                <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                    <?php if ($menuUser['CompanyUsers']['user_type'] == 2) { ?>
                        <a class="icon-revadmin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_admin=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to revoke Admin privilege from'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Admin'); ?></a>
                    <?php } else { ?>
                        <a class="icon-admin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_admin=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to grant Admin privilege to'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE914;</i> <?php echo __('Grant Admin'); ?></a>
                    <?php } ?>
                </li>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <input id="rmv_allprj_<?php echo $idSuffix . $menuUser['id']; ?>" type="hidden" value="<?php echo $menuUser['all_projects'] ?? ''; ?>" />
                <a class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-total-project="<?php echo $menuUser['total_project'] ?? 0; ?>"><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
                <span id="rmv_prj_<?php echo $idSuffix . $menuUser['id']; ?>" <?php if ($menuUser['all_project'] == '') { ?> style="display:none;" <?php } ?>></span>
            </li>
        <?php } ?>
        <?php if (SES_TYPE == 1 || SES_TYPE == 2) {
            if ($menuUser['CompanyUsers']['user_type'] != 1 && $menuUser['CompanyUsers']['user_type'] != 2) {
        ?>
                <li>
                    <a href="javascript:void(0);" class="icon-assgn-role-user" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>"><i class="material-icons">&#xE147;</i> <?php echo __("Assign Role"); ?></a>
                </li>
        <?php }
        } ?>
        <?php if ((defined('USER_TYPE') ? USER_TYPE : SES_TYPE) == 1) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if ($menuUser['is_moderator']) { ?>
                    <a class="icon-moderator icon-remove-modrt" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-type="0" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8F1;</i> <?php echo __('Revoke Moderator'); ?></a>
                <?php } else { ?>
                    <a class="icon-moderator icon-add-modrt" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-type="1" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8E9;</i> Grant Moderator</a>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if (($this->Format->isAllowed('Delete User', $roleAccess) && !$menuUser['dt_last_login']) || $this->Format->isAllowed('Disable Users', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if (!$menuUser['dt_last_login']) { ?>
                    <?php if ($this->Format->isAllowed('Delete User', $roleAccess)) { ?>
                        <a class="icon-delete-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?del=<?php echo urlencode($menuUser['uniq_id']); ?>&role=<?php echo $this->request->getQuery('role'); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to delete'); ?> \'<?php echo $menuUser['email']; ?>\' ?')"><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a>
                    <?php } ?>
                <?php } else { ?>
                    <?php if ($this->Format->isAllowed('Disable Users', $roleAccess)) { ?>
                        <a class="icon-disable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?deact=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to disable'); ?> \'<?php echo $menuUser['name']; ?>\' ?')"><i class="material-icons">&#xE909;</i> <?php echo __('Disable'); ?></a>
                    <?php } ?>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if ($this->Format->isAllowed('Add New User', $roleAccess)) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if (($istype == 1 || $istype == 2) && !$menuUser['dt_last_login']) { ?>
                    <a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $menuUser['qstr']; ?>','<?php echo $menuUser['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if (SES_TYPE == 1 || SES_TYPE == 2) { ?>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if ($menuUser['CompanyUsers']['is_client'] == '0') {  ?>
                    <a class="icon-client-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_client=<?php echo urlencode($menuUser['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to mark'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' <?php echo __('as client'); ?> ?')"><i class="material-icons">&#xE7FB;</i> <?php echo __('Mark Client'); ?></a>
                <?php } else { ?>
                    <a class="icon-revclient-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_client=<?php echo urlencode($menuUser['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to revoke client access from'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Client'); ?></a>
                <?php } ?>
            </li>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
                <?php if ($menuUser['CompanyUsers']['user_type'] == 2) { ?>
                    <a class="icon-revadmin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_admin=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to revoke Admin privilege from'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Admin'); ?></a>
                <?php } else { ?>
                    <a class="icon-admin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_admin=<?php echo urlencode($menuUser['uniq_id']); ?>" Onclick="return confirm('<?php echo __('Are you sure you want to grant Admin privilege to'); ?> \'<?php echo ucfirst($menuUser['name']); ?>\' ?')"><i class="material-icons">&#xE914;</i> <?php echo __('Grant Admin'); ?></a>
                <?php } ?>
            </li>
            <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
            </li>
        <?php } ?>
    <?php } ?>
<?php } ?>
<?php if ((SES_TYPE == 1 || SES_TYPE == 2) && SES_ID != $menuUser['id'] && $menuUser['CompanyUsers']['user_type'] != 1 && $role != 'disable') { ?>
    <li data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-name="<?php echo $menuUser['email']; ?>">
        <a class="icon-reset-pw-usr" href="javascript:void(0);"
           onclick="openAdminResetPassword(<?php echo (int)$menuUser['id']; ?>, '<?php echo addslashes(trim($menuUser['name'] ?: $menuUser['email'])); ?>')">
            <i class="material-icons">&#xE8E3;</i> <?php echo __('Reset Password'); ?>
        </a>
    </li>
<?php } ?>
<?php if ((SES_TYPE == 1 || (SES_TYPE != 3 && $menuUser['CompanyUsers']['user_type'] != 1)) && $role != 'disable') { ?>
    <li><a class="edit-exist-usr" id="<?php echo $_editId; ?>" href="javascript:void(0);" data-usr-id="<?php echo $menuUser['id']; ?>" data-usr-uid="<?php echo $menuUser['uniq_id']; ?>" data-usr-name="<?php echo $menuUser['name']; ?>" data-comp-count="<?php echo ($userinmorecompany && in_array($menuUser['id'], $userinmorecompany) && SES_ID != $menuUser['id']) ? 1 : 0; ?>"><i class="material-icons">&#xE8A6;</i> <?php echo __('Edit Profile'); ?> <?php echo ($userinmorecompany && in_array($menuUser['id'], $userinmorecompany) && SES_ID != $menuUser['id']) ? '<i class="material-icons">&#xE897;</i>' : ''; ?></a> </li>
<?php } ?>
