
<div class="task_listing role-access-settings-page u-vmenu user_role_mgnt">
    <h2><?php echo __("Role & Permissions"); ?></h2>

    <ul class="1st_menu cd-accordion-menu animated parent_acdn_menu">
        <?php if (!empty($rolesWithoutGroups)) : ?>
            <li>
                <?php $roleCount = count($rolesWithoutGroups); ?>
                <a href="javascript:void(0);"><?php echo __("Default Roles"); ?>&nbsp;&nbsp;(<?php echo($roleCount ? ($roleCount > 1 ? $roleCount . ' ' . __('Roles') : $roleCount . ' ' . __('Role')) : __('No Role')); ?>)</a>
                <ul class="sub-menu child_acdn_menu" id="info_list2">
                    <?php foreach ($rolesWithoutGroups as $k => $role): ?>
                        <?php $no_ofusers = !empty($role['company_users']) ? count($role['company_users']) : 0; ?>
                        <li class="has_par has-children">
                            <a href="javascript:void(0);" <?php if (strtolower($role['role']) == 'user') {
                                echo 'id="tour_role_group"';
                            } ?>>
                                <?php echo ucfirst($role['role']); ?>&nbsp;&nbsp;(<?php echo ($no_ofusers != 0) ? ($no_ofusers > 1 ? $no_ofusers . ' ' . __('Users') : '1 ' . __('User')) : __('No User'); ?>)
                            </a>
                            <div class="edit-close">
                                <?php
                                $title = '';
                        if (!empty($role['company_users'])) {
                            $title = implode(', ', array_map('ucfirst', $role['company_users']));
                        }
                        ?>
                                <span class="user"<?php echo $title ? " title='" . $title . "'" : ""; ?> onclick="userRole_list(<?php echo $role['id']; ?>, '<?php echo $role['role']; ?>')"><i class="material-icons">person_outline</i></span>
                            </div>
                            <ul class="sub-menu child_acdn_menu">
                                <?php foreach ($modules as $moduleKey => $module) { ?>
                                    <?php if ($module['name'] == 'Wiki' && !$this->Format->isWikiOn()) {
                                        continue;
                                    } ?>
                                    <?php if ($module['name'] == 'Documents' && !\Cake\Core\Plugin::isLoaded('Dms')) {
                                        continue;
                                    } ?>
                                    <?php if ($module['name'] == 'Risk Management' && !\Cake\Core\Plugin::isLoaded('RiskManagement')) {
                                        continue;
                                    } ?>
                                    <?php if ($module['name'] == 'Attendance & Leave' && !\Cake\Core\Plugin::isLoaded('AttendanceLeave')) {
                                        continue;
                                    } ?>
                                    <?php if ($module['name'] == 'Scaled Agile' && !(defined('SCALED_AGILE_ENABLED') && SCALED_AGILE_ENABLED === true && \Cake\Core\Plugin::isLoaded('ScaledAgile'))) {
                                        continue;
                                    } ?>
                                    <?php if ($module['name'] == 'Versions & Releases' && !\Cake\Core\Plugin::isLoaded('VersionRelease')) {
                                        continue;
                                    } ?>
                                    <?php if (array_key_exists($module['id'], $role['role_modules']) && $role['role_modules'][$module['id']] == 1) { ?>
                                    <?php  $frm_actions = ($role['id'] == 699) ? "saveGuestRoleActions" : "saveRoleActions"; ?>
                                    <?php echo $this->Form->create(null, ['method' => 'POST', 'url' => ['controller' => 'roles', 'action' => $frm_actions], 'name' => 'roleForm' . $role['id']]); ?>
                                        <li class="has_par has-children">
                                            <a href="javascript:void(0);" <?php if (strtolower($module['name']) == 'task' && $role['id'] == 3) { ?>id="tour_role_action" <?php } ?>><?php echo ucfirst(__(str_replace('Milestone', 'Taskgroup/Sprint', $module['name']))); ?>&nbsp;&nbsp;(<?php echo (count($module['actions'])) ? ((count($module['actions']) > 1) ? count($module['actions']) . ' Actions' : '1 Action') : 'No Action'; ?>)</a>
                                            <ul class="sub-menu child_acdn_menu sub_child_acdn_menu" <?php if (strtolower($module['name']) == 'task' && $role['id'] == 3) { ?>id="tour_role_action_ul" <?php } ?>>

                                                <?php
                                                    $moduleActions = $module['actions'];
                                                    $moduleActionGroups = [];
                                                    foreach ($moduleActions as $action) {
                                                        if (isset($action['display_group'])) {
                                                            switch ($action['display_group']) {
                                                                case 0:
                                                                    $moduleActionGroups['View'][] = $action;
                                                                    break;
                                                                case 1:
                                                                    $moduleActionGroups['Manage'][] = $action;
                                                                    break;
                                                                case 2:
                                                                    $moduleActionGroups['Delete'][] = $action;
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                    $icons = [ 'View' => 'visibility', 'Manage' => 'edit', 'Delete' => 'delete' ]
                                                ?>
                                                    <li class="user_group_role_li" <?php if (strtolower($module['name']) == 'task' && $role['id'] == 3) { ?>id="tour_role_box" <?php } ?>>
                                                        <?php foreach ($moduleActionGroups as $moduleActionKey => $moduleAction) { ?>
                                                        <ul class="group_list_ul">
                                                            <li class="group_ttle">
                                                                <h5><i class="material-icons"><?php echo $icons[$moduleActionKey] ;?></i> <?php echo __($moduleActionKey); ?></h5>
                                                            </li>
                                                            <li <?php if ($role['id'] != 699) { ?>class="disable_roll" <?php } ?>>
                                                                <ul>
                                                                    <?php foreach ($moduleAction as $actionKey => $action) { ?>
                                                                        <?php if (!($module['name'] == 'Invoice' && ($action['display_name'] == 'Archive' || $action['display_name'] == 'Restore'))) { ?>
                                                                            <?php
                                                                                $isSaViewLocked = (
                                                                                    $module['name'] === 'Scaled Agile'
                                                                                    && $action['uniq_id'] === 'view_scaled_agile'
                                                                                    && in_array((int) $role['id'], [1, 2], true)
                                                                                    && defined('SCALED_AGILE_ENABLED') && SCALED_AGILE_ENABLED === true
                                                                                );
                                                                            ?>
                                                                            <li class="has_par has-children">
                                                                                <div class="checkbox cmn_oth_checkbox <?php echo(($role['id'] != 699 || $moduleActionKey != 'View' || ($role['id'] != 699 && $displayGroup != 'Manage')) ? ' no-pointer' : ''); ?><?php echo $isSaViewLocked ? ' no-pointer' : ''; ?>">
                                                                                    <label class="switch_label">
                                                                                        <input class="onoffswitch_checkbox1" name="<?php echo $action['id']; ?>" data-action-id="<?php echo $action['id']; ?>" type="checkbox" id="cb_<?php echo $action['id']; ?>_<?php echo $module['id']; ?>_<?php echo $role['id']; ?>" <?php if (($role['role_actions'][$action['id']] ?? 0) != 0 || $isSaViewLocked) { ?>checked="checked"<?php } ?> disabled="disabled" value="1">
                                                                                        <span class="action"><?= ucfirst($action['display_name'] ? __($action['display_name']) : $action['action']); ?>
                                                                                        </span>
                                                                                    </label>
                                                                                </div>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                        <?php } ?>
                                                    </li>
                                            </ul>
                                        </li>
                                    <?php } ?>
                                        
                                <?php } ?>
                                <div class="cb"></div>
                                <?php echo $this->Form->end(); ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>

            </li>

        <?php else: ?>
            <li class="has-children">
                <a href="javascript:void(0);"><?php echo __("No Role Groups and no roles found"); ?>.</a>
            </li>
        <?php endif; ?>
    </ul>



</div>
<script type="text/javascript">
    (function($) {
        $.fn.vmenuModule = function(option) {
            var obj, item;
            var options = $.extend({
                Speed: 220,
                autostart: true,
                autohide: 1
            }, option);
            obj = $(this);

            item = obj.find("ul").parent("li").children("a");
            item.attr("data-option", "off");

            item.unbind('click').on("click", function() {
                var a = $(this);
                if (options.autohide) {
                    a.parent().parent().find("a[data-option='on']").parent("li").children("ul").slideUp(options.Speed / 1.2,
                        function() {
                            $(this).parent("li").children("a").attr("data-option", "off");
                        })
                }
                if (a.attr("data-option") == "off") {
                    a.parent("li").children("ul").slideDown(options.Speed,
                        function() {
                            a.attr("data-option", "on");
                        });
                }
                if (a.attr("data-option") == "on") {
                    a.attr("data-option", "off");
                    a.parent("li").children("ul").slideUp(options.Speed)
                }
            });
            if (options.autostart) {
                obj.find("a").each(function() {

                    $(this).parent("li").parent("ul").slideDown(options.Speed,
                        function() {
                            $(this).parent("li").children("a").attr("data-option", "on");
                        })
                })
            }

        }

        $("checkbox[class^='module_action_']").each(function() {

        });

    })(window.jQuery);
    $(document).ready(function() {
        $('.onoffswitch_checkbox1').change(function() {
            var id = parseInt($(this).attr('data-action-id'));
            if (id == 108) {
                if ($(this).is(":checked")) {
                    $('.onoffswitch_checkbox1').filter();
                    $('input[data-action-id=8]').prop('checked', !0);
                }
            }
        });
        $(".u-vmenu").vmenuModule({
            Speed: 200,
            autostart: true,
            autohide: true
        });
        $(window).on('beforeunload', function() {
            var message = roleSettingsChanged == 1 ? "<?php echo __("You have modified actions, reloading the page will reset all changes"); ?>." : void 0;
            e.returnValue = message;
            return message;
        });

        $(".action_lists_ul").each(function() {
            var parentUl = $(this);
            parentUl.find('.all_cb_chkbx').prop("checked", true);
            parentUl.find('.switch_label').each(function(index, el) {
                if (!$(this).find('input[type="checkbox"]').prop("checked")) {
                    parentUl.find('.all_cb_chkbx').prop("checked", false);
                    return false;
                }
            });

        });

        $(".onoffswitch_checkbox1").click(function() {
            parentUl = $(this).closest('.child_acdn_menu');
            if (parentUl.find('.onoffswitch_checkbox1').length == parentUl.find('.onoffswitch_checkbox1:checked').length) {
                parentUl.find('.all_cb_chkbx').prop("checked", true);
            } else {
                parentUl.find('.all_cb_chkbx').prop("checked", false);
            }
        })

    });

    function checkAllRoles(obj) {
        var status = $(obj).prop("checked");
        $(obj).closest('ul').find('.onoffswitch_checkbox1').prop('checked', status);

    }
</script>
