<div id="menu-settings-sidebar" class="sub-menu-sidebar" style="display: none;">
    <div class="search-container">
        <input type="text" class="search-field" placeholder="<?php echo __('Search Settings'); ?>" id="menu-settings-sidebar-search"
            onkeyup="searchMenuSettings(this.value)">
    </div>

    <?php if (SES_TYPE < 3) { ?>
        <div class="multi-menu menu-item-container">
            <div class="menu-item" onclick="toggleSubmenu('project-settings-submenu', 'project-settings-arrow')">
                <i class="material-icons">settings</i> <?php echo __('Project Settings'); ?>
                <i id="project-settings-arrow" class="material-icons">&#xE315;</i>
            </div>
            <div id="project-settings-submenu" class="<?= (in_array(PAGE_NAME, [
                'importexport',
                'importtimelog',
                'importcomment',
                'csvDataimport',
                'confirmImport',
                'csvTldataimport',
                'csvCommentimport',
                'confirmTlimport',
                'taskType',
                'labels',
            ])
                || (CONTROLLER == 'taskimports' && in_array(PAGE_NAME, [
                    'uploadImport',
                    'mapImport',
                    'previewImport',
                    'confirmImport',
                ])))
                ? 'show'
                : ''; ?>">
                <li
                    class="<?= (in_array(PAGE_NAME, ['importexport', 'importtimelog', 'importcomment', 'csvDataimport', 'confirmImport', 'csvTldataimport', 'csvCommentimport', 'confirmTlimport', 'importCustomers', 'csvDataimport', 'confirmImport'])) ? 'active-lists' : '' ?>">
                    <a id="sett_imp_exp_prof"
                        href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'importexport', 'plugin' => null]) ?>"
                        class="all-list menu-item">
                        <i class="material-icons">import_export</i> <?php echo __('Import & Export'); ?>
                    </a>
                </li>
                <li <?php if (PAGE_NAME == 'taskType') { ?>class="active-lists" <?php } ?>>
                    <a id="sett_task_type"
                        href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'taskType', 'plugin' => null]) ?>"
                        class="all-list menu-item">
                        <i class="material-icons">task</i> <?php echo __('Task Type'); ?>
                    </a>
                </li>
                <li
                    class="task-sett pr <?php if (CONTROLLER == 'projects' && PAGE_NAME == 'labels') { ?>active-lists<?php } ?>">
                    <a id="sett_invoice" href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'labels', 'plugin' => null]) ?>"
                        class="all-list menu-item">
                        <i class="material-icons">label</i> <?php echo __('Label'); ?>
                    </a>
                </li>
            </div>
        </div>
    <?php } ?>

    <?php if (SES_TYPE < 3) { ?>
        <div class="multi-menu menu-item-container">
            <div class="menu-item menu-toggles"
                onclick="toggleSubmenu('company-settings-submenu', 'company-settings-arrow')">
                <i class="material-icons">business</i>
                <?php echo __('Company Settings'); ?>
                <i id="company-settings-arrow" class="material-icons">&#xE315;</i>
            </div>
            <div id="company-settings-submenu" class="<?php if (
                in_array(PAGE_NAME, ['mycompany', 'organization', 'index', 'taskType', 'labels']) ||
                (CONTROLLER == 'projectstatuses' && PAGE_NAME == 'projectStatus') || (CONTROLLER == 'projecttypes' && PAGE_NAME == 'projectTypes') || (CONTROLLER == 'emailtemplates' && PLUGIN_NAME == 'EmailTemplating')
            ) ?>">
                <?php if (SES_TYPE < 3) { ?>
                    <li <?php if (PAGE_NAME == 'mycompany') { ?>class="active-lists" <?php } ?>>
                        <a id="sett_mail_noti_prof" href="<?php echo HTTP_ROOT . 'my-company'; ?>" class="all-list menu-item">
                            <i class="material-icons">business</i> <?php echo __('My Company'); ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (SES_TYPE < 3) { ?>
                    <li
                        class="task-sett pr <?php if (CONTROLLER == 'projectstatuses' && PAGE_NAME == 'projectStatus') { ?>active-lists<?php } ?>">
                        <a id="sett_invoice" href="<?php echo HTTP_ROOT . 'project-status'; ?>" class="all-list menu-item">
                            <i class="material-icons">flag</i> <?php echo __('Project Status'); ?>
                        </a>
                    </li>

                    <li
                        class="task-sett pr <?php if (CONTROLLER == 'projecttypes' && PAGE_NAME == 'projectTypes') { ?>active-lists<?php } ?>">
                        <a id="sett_invoice" href="<?php echo HTTP_ROOT . 'project-type'; ?>" class="all-list menu-item">
                            <i class="material-icons">category</i> <?php echo __('Project Type'); ?>
                        </a>
                    </li>

                <?php } ?>
                <li <?php if (PAGE_NAME == 'manageTaskStatusGroup' || PAGE_NAME == 'manageStatus') { ?>class="active-lists"
                    <?php } ?>>
                    <a id="sett_mail_noti_prof" href="<?php echo HTTP_ROOT . 'workflow-setting'; ?>"
                        class="all-list menu-item" style="max-width:200px;">
                        <i class="material-icons">account_tree</i> <?php echo __('Status Workflow'); ?>
                    </a>
                </li>
                <li
                    class="task-sett pr <?php if (CONTROLLER == 'roles' && PAGE_NAME == 'index') { ?>active-lists<?php } ?>">
                    <a class="all-list menu-item" href="<?php echo HTTP_ROOT . 'user-role-settings/'; ?>"
                        style="max-width: 180px;">
                        <i class="material-icons">manage_accounts</i>
                        <?php echo __('Roles & Permissions'); ?>
                    </a>
                </li>
                <?php if (SES_TYPE < 3) { ?>
                    <li
                        class="task-sett pr <?php if (CONTROLLER == 'taskactions' && PAGE_NAME == 'duedateChangeReason') { ?>active-lists<?php } ?>">
                        <a class="all-list menu-item" href="<?php echo HTTP_ROOT . 'duedate-change-reason'; ?>">
                            <i class="material-icons">event_note</i> <?php echo __('Due Date Change Reason'); ?>
                        </a>
                    </li>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <div class="multi-menu menu-item-container">
        <div class="menu-item profile-setting-submenu"
            onclick="toggleSubmenu('profile-settings-submenu', 'profile-settings-arrow')">
            <i class="material-icons">person</i> <?php echo __('Profile Settings'); ?>
            <i id="profile-settings-arrow" class="material-icons">&#xE315;</i>
        </div>
        <div id="profile-settings-submenu" class="<?php if (PAGE_NAME == 'changepassword' || PAGE_NAME == 'emailNotifications' || PAGE_NAME == 'defaultView' || (CONTROLLER == 'usersidebar' && PAGE_NAME == 'index') || (PLUGIN_NAME == 'OutlookIntegration' && CONTROLLER == 'OutlookIntegration')) {
            echo 'show';
        } ?>">
            <li <?php if (PAGE_NAME == 'profile') { ?>class="active-lists" <?php } ?>>
                <a id="sett_my_profile" href="<?php echo HTTP_ROOT . 'users/profile'; ?>" class="all-list menu-item">
                    <i class="material-icons">&#xE7FD;</i> <?php echo __('My Profile'); ?>
                </a>
            </li>
            <li <?php if (PAGE_NAME == 'changepassword') { ?>class="active-lists" <?php } ?>>
                <a id="sett_cpw_prof" href="<?php echo HTTP_ROOT . 'users/changepassword'; ?>"
                    class="all-list menu-item">
                    <i class="material-icons">&#xE897;</i> <?php echo __('Change Password'); ?>
                </a>
            </li>
            <li <?php if (PAGE_NAME == 'emailNotifications') { ?>class="active-lists" <?php } ?>>
                <a id="sett_mail_noti_prof" href="<?php echo HTTP_ROOT . 'users/emailNotifications'; ?>"
                    class="all-list menu-item">
                    <i class="material-icons">&#xE003;</i> <?php echo __('Notifications'); ?>
                </a>
            </li>
            <li <?php if (PAGE_NAME == 'defaultView') { ?>class="active-lists" <?php } ?>>
                <a id="sett_dflt_view_prof" href="<?php echo HTTP_ROOT . 'users/defaultView'; ?>"
                    class="all-list menu-item">
                    <i class="material-icons">&#xE417;</i> <?php echo __('My Default View'); ?>
                </a>
            </li>
            <li <?php if (CONTROLLER == 'usersidebar' && PAGE_NAME == 'index') { ?>class="active-lists" <?php } ?>>
                <a id="sett_dflt_view_prof" href="<?php echo HTTP_ROOT . 'sidebar-settings'; ?>"
                    class="all-list menu-item">
                    <i class="material-icons">subject</i> <?php echo __('Left Menus'); ?>
                </a>
            </li>
            <?php if (\Cake\Core\Configure::read('OutlookIntegration.enabled') && \Cake\Core\Plugin::isLoaded('OutlookIntegration')) { ?>
            <li <?php if (PLUGIN_NAME == 'OutlookIntegration' && CONTROLLER == 'OutlookIntegration') { ?>class="active-lists" <?php } ?>>
                <a href="<?= $this->Url->build(['plugin' => 'OutlookIntegration', 'controller' => 'OutlookIntegration', 'action' => 'index']) ?>"
                    class="all-list menu-item">
                    <i class="material-icons">email</i> <?php echo __('Outlook'); ?>
                </a>
            </li>
            <?php } ?>
        </div>
    </div>

    <div class="multi-menu menu-item-container">
        <a href="<?php echo HTTP_ROOT . 'about'; ?>"
            class="all-list menu-item <?= (CONTROLLER == 'about') ? 'active' : '' ?>">
            <i class="material-icons">info_outline</i> <?php echo __('About'); ?>
        </a>
    </div>

    <?php if (\Cake\Core\Plugin::isLoaded('DeveloperApi') && $this->Format->hasActiveApiKeys(SES_COMP) && (SES_TYPE == 1 || SES_TYPE == 2)) { ?>
        <div class="multi-menu menu-item-container">
            <a href="<?= $this->Url->build(['plugin' => 'DeveloperApi', 'controller' => 'CompanySettings', 'action' => 'index']) ?>"
                class="all-list menu-item <?= (strtolower(CONTROLLER) == 'companysettings' && PLUGIN_NAME == 'DeveloperApi') ? 'active' : '' ?>">
                <i class="material-icons">code</i> <?php echo __('Developer API'); ?>
            </a>
        </div>
    <?php } ?>

</div>