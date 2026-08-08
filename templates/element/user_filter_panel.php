<?php
/**
 * User manage — right-slide "Apply Filters" modal panel.
 * Follows the same multi-select checkbox pattern as
 * templates/element/task_filters.php.
 *
 * Required view variables (set by UsersController::manage()):
 *   $filterRolesList, $filterProjectsList,
 *   $filterRoleIds, $filterProjectIds
 *     (each one is an int[] of currently-selected IDs; empty = no filter)
 *
 * The legacy single-value vars $filterRoleId /
 * $filterProjectId are still set by the controller for
 * backward compatibility with any external caller, but this template
 * no longer reads them.
 */
$filterRoleIds    = isset($filterRoleIds)    && is_array($filterRoleIds)    ? array_map('intval', $filterRoleIds)    : [];
$filterProjectIds = isset($filterProjectIds) && is_array($filterProjectIds) ? array_map('intval', $filterProjectIds) : [];
?>
<style>
    /* Scoped fix for the user filter modal only.
       The shared `.filterModal .modal-body { height: calc(100% - 60px); }`
       rule in custom.css reserves space only for the modal-header (60px)
       and pushes the modal-footer (with the Apply Filters / Reset buttons)
       BELOW the viewport. Task filters get away with it because they have
       no footer (they apply on change). The user filter has an explicit
       Apply button — without these overrides the user literally cannot
       click it. */
    #usrFilterModal .modal-body { height: calc(100% - 60px - 70px) !important; padding-bottom: 8px !important; }
    #usrFilterModal .modal-footer {
        position: absolute; left: 0; right: 0; bottom: 0; height: 70px;
        background: #fff; border-top: 1px solid #ddd; padding: 15px;
        display: flex; align-items: center; justify-content: flex-end; gap: 8px;
        z-index: 2;
    }
    #usrFilterModal .modal-footer .btn { margin: 0; }
</style>
<div class="modal right fade filterModal" id="usrFilterModal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title"><?php echo __('Apply Filters'); ?></h3>
                <button type="button" class="close" onclick="$('#usrFilterModal').modal('hide');" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="active_filter_sec" id="usr_active_filter_sec" style="display:none;">
                    <div class="active_filter_sec_head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <span><?php echo __('Active Filters'); ?></span>
                        <a href="javascript:void(0);"
                           id="usr_reset_filter_icon"
                           onclick="resetUsrFilters(true); return false;"
                           title="<?php echo __('Reset Filters'); ?>"
                           style="display:none;color:#555;">
                            <i class="material-icons" style="font-size:20px;vertical-align:middle;">refresh</i>
                        </a>
                    </div>
                    <div class="active_filter_sec_cont" style="display:block;">
                        <div id="usr_active_filter_contain"></div>
                    </div>
                </div>

                <div class="filter_accordion">

                    <!-- Role filter (multi-select; no row = no filter) -->
                    <div class="filter_set">
                        <div class="filter_type_header <?php echo !empty($filterRoleIds) ? 'active' : ''; ?>">
                            <?php echo __('Role'); ?>
                            <span class="fa_arrow <?php echo !empty($filterRoleIds) ? 'fa-minus' : 'fa-plus'; ?>"></span>
                        </div>
                        <div class="filter_toggle_data" <?php echo !empty($filterRoleIds) ? 'style="display:block;"' : ''; ?>>
                            <?php if (!empty($filterRolesList)): ?>
                            <div class="usr_filter_search_wrap" style="position:relative;margin:6px 0 8px;">
                                <input type="text"
                                       class="usr_filter_search"
                                       data-target="role"
                                       placeholder="<?php echo __('Search role…'); ?>"
                                       style="width:100%;padding:6px 28px 6px 10px;border:1px solid #ddd;border-radius:4px;font-size:13px;box-sizing:border-box;">
                                <i class="material-icons usr_filter_search_icon" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:16px;color:#999;pointer-events:none;">search</i>
                            </div>
                            <div class="usr_filter_empty" data-target="role" style="display:none;color:#999;font-size:12px;padding:6px 4px;font-style:italic;">
                                <?php echo __('No matches'); ?>
                            </div>
                            <?php endif; ?>
                            <ul class="dropdown_status_filter_new usr_filter_list" data-target="role" style="list-style:none;padding-left:0;margin:0;max-height:240px;overflow-y:auto;">
                                <?php foreach ($filterRolesList as $rId => $rName): ?>
                                    <li class="li_check_radio usr_filter_opt" data-label="<?php echo h(strtolower($rName)); ?>" style="list-style:none;">
                                        <a href="javascript:void(0);">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="usr_filter_role[]"
                                                           class="usr_filter_role_chk"
                                                           value="<?php echo (int)$rId; ?>"
                                                           <?php echo in_array((int)$rId, $filterRoleIds, true) ? 'checked' : ''; ?>>
                                                    <?php echo h($rName); ?>
                                                </label>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Project filter -->
                    <?php if (!empty($filterProjectsList)): ?>
                    <div class="filter_set">
                        <div class="filter_type_header <?php echo !empty($filterProjectIds) ? 'active' : ''; ?>">
                            <?php echo __('Project'); ?>
                            <span class="fa_arrow <?php echo !empty($filterProjectIds) ? 'fa-minus' : 'fa-plus'; ?>"></span>
                        </div>
                        <div class="filter_toggle_data" <?php echo !empty($filterProjectIds) ? 'style="display:block;"' : ''; ?>>
                            <?php if (!empty($filterProjectsList)): ?>
                            <div class="usr_filter_search_wrap" style="position:relative;margin:6px 0 8px;">
                                <input type="text"
                                       class="usr_filter_search"
                                       data-target="project"
                                       placeholder="<?php echo __('Search project…'); ?>"
                                       style="width:100%;padding:6px 28px 6px 10px;border:1px solid #ddd;border-radius:4px;font-size:13px;box-sizing:border-box;">
                                <i class="material-icons usr_filter_search_icon" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:16px;color:#999;pointer-events:none;">search</i>
                            </div>
                            <div class="usr_filter_empty" data-target="project" style="display:none;color:#999;font-size:12px;padding:6px 4px;font-style:italic;">
                                <?php echo __('No matches'); ?>
                            </div>
                            <?php endif; ?>
                            <ul class="dropdown_status_filter_new usr_filter_list" data-target="project" style="list-style:none;padding-left:0;margin:0;max-height:240px;overflow-y:auto;">
                                <?php foreach ($filterProjectsList as $pId => $pName): ?>
                                    <li class="li_check_radio usr_filter_opt" data-label="<?php echo h(strtolower($pName)); ?>" style="list-style:none;">
                                        <a href="javascript:void(0);">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="usr_filter_project[]"
                                                           class="usr_filter_project_chk"
                                                           value="<?php echo (int)$pId; ?>"
                                                           <?php echo in_array((int)$pId, $filterProjectIds, true) ? 'checked' : ''; ?>>
                                                    <?php echo h($pName); ?>
                                                </label>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>


                </div><!-- /.filter_accordion -->
            </div><!-- /.modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="resetUsrFilters();">
                    <?php echo __('Reset'); ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="applyUsrFilters();">
                    <?php echo __('Apply Filters'); ?>
                </button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /#usrFilterModal -->
