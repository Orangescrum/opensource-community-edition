<?php
/**
 * User manage grid partial — included by manage.php (full page) and
 * returned as-is for AJAX filter/pagination requests.
 *
 * Required view variables (set by UsersController::manage()):
 *   $userArr, $role, $istype, $userinmorecompany, $caseCount,
 *   $page_limit, $casePage, $filterRoleId,
 *   $filterProjectId, $filterTeamId, $roleAccess
 */
$queryRole = $this->request->getQuery('role');
$queryUser = $this->request->getQuery('user', '');
$count = 1;
$is_invited_user = ($role === 'invited') ? 1 : 0;
?>
<?php echo $this->element('user_kpi_summary'); ?>
<div id="userViewContainer" class="user_div_bk usrs_page m-list-tbl">
    <?php foreach ($userArr as $user): ?>
        <?php
        if ($user['Roles']['role'] === 'Owner') {
            $colors = 'own_clr'; $usr_typ_name = __('Owner');
        } elseif ($user['Roles']['role'] === 'Admin') {
            $colors = 'adm_clr'; $usr_typ_name = __('Admin');
        } elseif ($user['Roles']['role'] === 'User' && $role != 3) {
            $colors = 'usr_clr'; $usr_typ_name = __('User');
        } elseif ($user['Roles']['role'] === 'Guest') {
            $colors = 'cli_clr'; $usr_typ_name = __('Guest');
        } else {
            $colors = 'usr_clr';
            $usr_typ_name = $user['Roles']['role'] ?: __('User');
        }
        if ($role === 'invited') {
            $colors = 'usr_clr'; $usr_typ_name = __('User');
            if ($user['CompanyUsers']['is_client'] == 1) { $colors = 'cli_clr'; $usr_typ_name = __('Client'); }
        }
        if ($role === 'recent') {
            $colors = 'usr_clr';
            $usr_typ_name = ($user['Roles']['role']) ? $user['Roles']['role'] : __('User');
            if ($user['CompanyUsers']['is_client'] == 1) { $colors = 'cli_clr'; $usr_typ_name = __('Client'); }
        }
        if ($user['CompanyUsers']['is_client'] == 1) { $colors = 'cli_clr'; $usr_typ_name = __('Client'); }
        if ($user['CompanyUsers']['is_client'] == 1 && $user['CompanyUsers']['user_type'] == 2) {
            $colors = 'cli_clr'; $usr_typ_name = __('Admin/Client');
        }
        $fullName = trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        ?>
        <div class="usr_mcnt fl cmn_bdr_shadow" id="usr_mcnt<?php echo $user['id']; ?>">
            <div class="usr_top_cnt">
                <div class="usr_cat <?php echo $colors; ?>" rel="tooltip" title="<?php echo $usr_typ_name; ?>"><?php echo $usr_typ_name; ?></div>
                <div class="usr_act_det">
                    <span class="dropdown">
                        <a class="dropdown-toggle active" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                            <i class="material-icons">&#xE5D4;</i>
                        </a>
                        <ul class="dropdown-menu right0 new-dropdown">
                            <?php if ($user['CompanyUsers']['user_type'] == 1 || ($user['CompanyUsers']['user_type'] == 2 && SES_ID == $user['id'])): ?>
                                <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)): ?>
                                    <li><a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a></li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)): ?>
                                    <li>
                                        <input id="rmv_allprj_<?php echo $user['id']; ?>" type="hidden" value="<?php echo $user['all_projects'] ?? ''; ?>" />
                                        <a id="rmv_prj_<?php echo $user['id']; ?>" class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>" data-total-project="<?php echo $user['total_project'] ?? 0; ?>" <?php if (empty($user['all_project'])): ?> style="display:none;"<?php endif; ?>><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php elseif ($role === 'invited'): ?>
                                <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)): ?>
                                    <li><a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['email']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
                                        <input id="rmv_allprj_<?php echo $user['id']; ?>" type="hidden" value="<?php echo $user['all_projects'] ?? ''; ?>" />
                                    </li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Delete User', $roleAccess)): ?>
                                    <li><a class="icon-delete-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?del=<?php echo urlencode($user['uniq_id']); ?>&role=<?php echo urlencode($queryRole); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete'); ?> \'<?php echo $user['email']; ?>\' ?')"><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a></li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Add New User', $roleAccess)): ?>
                                    <li><a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $user['qstr'] ?? ''; ?>','<?php echo $user['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a></li>
                                <?php endif; ?>
                            <?php elseif ($role === 'recent'): ?>
                                <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)): ?>
                                    <li><a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['email']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a>
                                        <input id="rmv_allprj_<?php echo $user['id']; ?>" type="hidden" value="<?php echo $user['all_projects'] ?? ''; ?>" />
                                    </li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Add New User', $roleAccess) && !$user['dt_last_login']): ?>
                                    <li><a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $user['qstr'] ?? ''; ?>','<?php echo $user['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a></li>
                                <?php endif; ?>
                            <?php elseif ($role === 'disable'): ?>
                                <?php if ($this->Format->isAllowed('Enable User', $roleAccess)): ?>
                                    <li><a class="icon-enable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?act=<?php echo urlencode($user['uniq_id']); ?>&role=<?php echo urlencode($queryRole); ?>" onclick="return confirm('<?php echo __('Are you sure you want to enable'); ?> \'<?php echo $user['name']; ?>\' ?')"><i class="material-icons">&#xE87A;</i> <?php echo __('Enable'); ?></a></li>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)): ?>
                                    <li><a class="icon-assign-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>"><i class="material-icons">&#xE85D;</i> <?php echo __('Assign Project'); ?></a></li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Remove Project', $roleAccess)): ?>
                                    <li>
                                        <input id="rmv_allprj_<?php echo $user['id']; ?>" type="hidden" value="<?php echo $user['all_projects'] ?? ''; ?>" />
                                        <a class="icon-remprj-usr" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>" data-total-project="<?php echo $user['total_project'] ?? 0; ?>"><i class="material-icons">&#xE15C;</i> <?php echo __('Remove Project'); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ((SES_TYPE == 1 || SES_TYPE == 2) && $user['CompanyUsers']['user_type'] != 1 && $user['CompanyUsers']['user_type'] != 2): ?>
                                    <li><a href="javascript:void(0);" class="icon-assgn-role-user" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>"><i class="material-icons">&#xE147;</i> <?php echo __('Assign Role'); ?></a></li>
                                <?php endif; ?>
                                <?php if (((defined('USER_TYPE') ? USER_TYPE : SES_TYPE) == 1)): ?>
                                    <li>
                                        <?php if ($user['is_moderator']): ?>
                                            <a class="icon-moderator icon-remove-modrt" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>" data-type="0" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8F1;</i> <?php echo __('Revoke Moderator'); ?></a>
                                        <?php else: ?>
                                            <a class="icon-moderator icon-add-modrt" href="javascript:void(0);" data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>" data-type="1" onclick="grantOrRemoveModerator(this);"><i class="material-icons">&#xE8E9;</i> <?php echo __('Grant Moderator'); ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                                <?php if (($this->Format->isAllowed('Delete User', $roleAccess) && !$user['dt_last_login']) || $this->Format->isAllowed('Disable Users', $roleAccess)): ?>
                                    <li>
                                        <?php if (!$user['dt_last_login'] && $this->Format->isAllowed('Delete User', $roleAccess)): ?>
                                            <a class="icon-delete-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?del=<?php echo urlencode($user['uniq_id']); ?>&role=<?php echo urlencode($queryRole); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete'); ?> \'<?php echo $user['email']; ?>\' ?')"><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a>
                                        <?php elseif ($this->Format->isAllowed('Disable Users', $roleAccess)): ?>
                                            <a class="icon-disable-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?deact=<?php echo urlencode($user['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to disable'); ?> \'<?php echo $user['name']; ?>\' ?')"><i class="material-icons">&#xE909;</i> <?php echo __('Disable'); ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ($this->Format->isAllowed('Add New User', $roleAccess) && ($istype == 1 || $istype == 2) && !$user['dt_last_login']): ?>
                                    <li><a class="icon-resend-usr" href="javascript:void(0);" onclick="return resend_invitation('<?php echo $user['qstr'] ?? ''; ?>','<?php echo $user['email']; ?>');"><i class="material-icons">&#xE040;</i> <?php echo __('Resend'); ?></a></li>
                                <?php endif; ?>
                                <?php if (SES_TYPE == 1 || SES_TYPE == 2): ?>
                                    <li>
                                        <?php if ($user['CompanyUsers']['is_client'] == '0'): ?>
                                            <a class="icon-client-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_client=<?php echo urlencode($user['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to mark'); ?> \'<?php echo ucfirst($user['name']); ?>\' <?php echo __('as client'); ?> ?')"><i class="material-icons">&#xE7FB;</i> <?php echo __('Mark Client'); ?></a>
                                        <?php else: ?>
                                            <a class="icon-revclient-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_client=<?php echo urlencode($user['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to revoke client access from'); ?> \'<?php echo ucfirst($user['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Client'); ?></a>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <?php if ($user['CompanyUsers']['user_type'] == 2): ?>
                                            <a class="icon-revadmin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?revoke_admin=<?php echo urlencode($user['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to revoke Admin privilege from'); ?> \'<?php echo ucfirst($user['name']); ?>\' ?')"><i class="material-icons">&#xE7FF;</i> <?php echo __('Revoke Admin'); ?></a>
                                        <?php else: ?>
                                            <a class="icon-admin-usr" href="<?php echo HTTP_ROOT; ?>users/manage/?grant_admin=<?php echo urlencode($user['uniq_id']); ?>" onclick="return confirm('<?php echo __('Are you sure you want to grant Admin privilege to'); ?> \'<?php echo ucfirst($user['name']); ?>\' ?')"><i class="material-icons">&#xE914;</i> <?php echo __('Grant Admin'); ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ((SES_TYPE == 1 || SES_TYPE == 2) && SES_ID != $user['id'] && $user['CompanyUsers']['user_type'] != 1 && $role !== 'disable'): ?>
                                <li>
                                    <a class="icon-reset-pw-usr" href="javascript:void(0);" onclick="openAdminResetPassword(<?php echo (int)$user['id']; ?>, '<?php echo addslashes(trim($user['name'] ?: $user['email'])); ?>')">
                                        <i class="material-icons">&#xE8E3;</i> <?php echo __('Reset Password'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ((SES_TYPE == 1 || (SES_TYPE != 3 && $user['CompanyUsers']['user_type'] != 1)) && $role !== 'disable'): ?>
                                <li>
                                    <a class="edit-exist-usr" id="edit-exist-usr<?php echo $user['id']; ?>" href="javascript:void(0);"
                                       data-usr-id="<?php echo $user['id']; ?>"
                                       data-usr-uid="<?php echo $user['uniq_id']; ?>"
                                       data-usr-name="<?php echo $user['name']; ?>"
                                       data-comp-count="<?php echo ($userinmorecompany && in_array($user['id'], $userinmorecompany) && SES_ID != $user['id']) ? 1 : 0; ?>">
                                        <i class="material-icons">&#xE8A6;</i> <?php echo __('Edit Profile'); ?>
                                        <?php if ($userinmorecompany && in_array($user['id'], $userinmorecompany) && SES_ID != $user['id']): ?><i class="material-icons">&#xE897;</i><?php endif; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </span>
                </div>
                <?php $random_bgclr = $this->Format->getProfileBgColr($user['id']); ?>
                <div id="pimg_<?php echo $user['id']; ?>" class="user_img holder <?php echo $random_bgclr; ?>">
                    <?php if (trim($user['photo'] ?? '')): ?>
                        <img class="lazy" data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo $user['photo']; ?>&sizex=94&sizey=94&quality=100" width="94" height="94" />
                    <?php elseif (isset($user['name']) && trim($user['name'])): ?>
                        <span class="name_txt"><?php echo mb_substr(trim($user['name']), 0, 1, "utf-8"); ?></span>
                    <?php elseif (isset($user['short_name']) && trim($user['short_name'])): ?>
                        <?php echo mb_substr(trim($user['short_name']), 0, 1, "utf-8"); ?>
                    <?php else: ?>
                        <img src="<?php echo HTTP_ROOT; ?>img/images/user.png" />
                    <?php endif; ?>
                </div>
                <h3 class="invite_user_cls ellipsis-view" id="pn_<?php echo $user['id']; ?>"
                    data-usr-id="<?php echo $user['id']; ?>"
                    data-usr-name="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"
                    title="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"
                    rel="tooltip"><?php echo $fullName !== '' ? htmlspecialchars(ucfirst($user['name'] ?? '') . ' ' . ($user['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '&nbsp;'; ?></h3>
                <h4 id="psn_<?php echo $user['id']; ?>"><?php echo $user['short_name']; ?></h4>
            </div>
            <div class="usr_cnts">
                <ul>
                    <li>
                        <span class="cnt_ttl_usr"><?php echo __('Last Activity'); ?></span>
                        <span class="cnt_usr" id="pla_<?php echo $user['id']; ?>">
                            <?php
                            if ($user['CompanyUsers']['is_active'] == 0 && $queryRole === 'invited') {
                                $activity = __('Invited');
                            } elseif ($queryRole === 'recent') {
                                if ($user['CompanyUsers']['is_active'] == 2) {
                                    $activity = __('Invited');
                                } elseif (($istype == 1 || $istype == 2) && !$user['dt_last_login']) {
                                    $activity = __('No activity yet');
                                } else {
                                    $activity = $user['latest_activity'] ?? __('No activity yet');
                                }
                            } else {
                                if (($istype == 1 || $istype == 2) && !$user['dt_last_login']) {
                                    $activity = ($user['CompanyUsers']['is_active'] == 2) ? __('Invited') : __('No activity yet');
                                } else {
                                    $activity = $user['latest_activity'] ?? __('No activity yet');
                                }
                            }
                            echo $activity;
                            ?>
                        </span>
                    </li>
                    <li>
                        <span class="cnt_ttl_usr"><?php echo __('Created'); ?></span>
                        <span class="cnt_usr" id="pcr_<?php echo $user['id']; ?>">
                            <?php
                            if ($role === 'invited') {
                                $crdt = $user['UserInvitation']['created'] ?? '';
                            } elseif ($role === 'recent') {
                                $crdt = $user['dt_created'] ?? '';
                            } else {
                                $crdt = $user['CompanyUsers']['created'] ?? '';
                            }
                            if ($crdt && $crdt !== '0000-00-00 00:00:00') {
                                echo $user['created_on'] ?? '';
                            }
                            ?>
                        </span>
                    </li>
                    <li>
                        <span class="usr_email cnt_ttl_usr"><?php echo __('Email'); ?></span>
                        <span class="cnt_usr" id="pemail_<?php echo $user['id']; ?>" title="<?php echo $user['email']; ?>">
                            <?php echo $this->Format->shortLength($user['email'], 25); ?>
                        </span>
                    </li>
                    <li data-usr-id="<?php echo $user['id']; ?>" data-usr-name="<?php echo $user['name']; ?>"
                        <?php if ($this->Format->isAllowed('Assign Project', $roleAccess)): ?> class="disp_assn_proj_popup"<?php endif; ?>>
                        <span class="cnt_ttl_usr"><?php echo __('Projects'); ?></span>
                        <span id="remain_prj_<?php echo $user['id']; ?>" class="cnt_usr nm_prj nm_prj_mx_width ellipsis-view" title="<?php echo $user['all_project_lst'] ?? ''; ?>">
                            <?php echo (isset($user['all_project']) && trim($user['all_project'])) ? $user['all_project'] : 'N/A'; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <?php $count++; ?>
    <?php endforeach; ?>

    <div class="cb"></div>
    <input type="hidden" id="is_invited_user" value="<?php echo $is_invited_user; ?>" />

    <?php if (empty($userArr)): ?>
        <div class="row">
            <div class="col-lg-12 text-centre">
                <div class="no_usr fl cmn_bdr_shadow">
                    <h2 class="fnt_clr_rd">
                        <?php echo ($role === 'client') ? __('No clients found') : __('No users found') . '.'; ?>
                    </h2>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="cbt"></div>
<input type="hidden" id="getcasecount" value="<?php echo $caseCount ?? 0; ?>" readonly="true" />
<?php // Pagination intentionally NOT rendered inside this element.
      // manage.php includes both the grid AND user_list_view; whichever
      // view is active the pagination should sit at the bottom of the
      // visible data. Rendering pagination here put it between the two
      // view blocks, which surfaced above the list table when list view
      // was selected (the grid was CSS-hidden). The page now renders the
      // pagination once, after both view includes, in manage.php. ?>
<input type="hidden" id="totalcount" name="totalcount" value="<?php echo $count; ?>" />
