<div class="task-list-bar  user-grid-page">
    <div class="wrap_top_tlbar">
        <div class="row align-items-center">
            <div class="col-md-8">
                <ul class="lft_tab_tasklist li_pad10">
                    <li id="tour_actv_user" class="all-list-glyph <?php if (isset($role) && ($role == '' || $role == 'all')) { ?>active-list<?php } ?>">
                        <a href="javascript:void(0)" class="all-list" onclick="filterUserRole('all', '');">
                            <i class="material-icons">&#xE7FD;</i>
                            <?php echo __('Active'); ?> <span class="counter">(<?php echo $active_user_cnt; ?>)</span></span>
                        </a>
                    </li>
                    <li id="tour_invt_user" <?php if ($role == 'invited') { ?>class="active-list" <?php } ?>
                        onclick="filterUserRole('invited', '<?php echo $user_srch; ?>');">
                        <a href="javascript:void(0)">
                            <i class="material-icons">&#xE7FE;</i>
                            <?php echo __('Invited'); ?> <span class="counter">(<?php echo $invited_user_cnt; ?>)</span>
                        </a>
                    </li>
                    <li id="tour_disbl_user" <?php if ($role == 'disable') { ?>class="active-list" <?php } ?>
                        onclick="filterUserRole('disable', '<?php echo $user_srch; ?>');">
                        <a href="javascript:void)(0)">
                            <i class="material-icons">&#xE909;</i>
                            <?php echo __('Disabled'); ?> <span class="counter">(<?php echo $disabled_user_cnt; ?>)</span>
                        </a>
                    </li>
                    <li class="recent-icon <?php if ($role == 'recent') { ?>active-list<?php } ?>"
                        onclick="filterUserRole('recent', '<?php echo $user_srch; ?>');">
                        <a href="javascript:void)(0)">
                            <i class="material-icons">&#xE8B3;</i>
                            <?php echo __('Recent'); ?> <span class="counter">(<?php echo $recent_user_cnt; ?>)</span>
                        </a>
                    </li>
                    <li id="tour_clint_user" <?php if ($role == 'client') { ?>class="active-list" <?php } ?>
                        onclick="filterUserRole('client', '<?php echo $user_srch; ?>');">
                        <a href="javascript:void)(0)">
                            <i class="material-icons">&#xE7FB;</i>
                            <?php echo __('Client'); ?> <span class="counter">(<?php echo $client_user_cnt == 0 ? '0' : $client_user_cnt; ?>)</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; margin-bottom:15px; margin-top:10px; margin-right:10px;">
                    <!-- Filter button (from PR #20) — opens right-slide filter panel -->
                    <button onclick="$('#usrFilterModal').modal({show:true, backdrop:false, keyboard:false});"
                            class="cmn-btn-outline"
                            id="usr-filter-btn"
                            title="<?php echo __('Apply Filters'); ?>"
                            style="display:inline-flex !important; position:relative;">
                        <span class="material-icons">filter_list</span>
                        <?php echo __('Filter'); ?>
                        <?php
                        $activeFilterCount = (int)(!empty($filterRoleId))
                                          + (int)(!empty($filterProjectId));
                        ?>
                        <?php if ($activeFilterCount > 0): ?>
                            <span id="usr-filter-badge"
                                  style="display:inline-block;background:#ff7905;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;line-height:18px;text-align:center;margin-left:4px;">
                                <?php echo $activeFilterCount; ?>
                            </span>
                        <?php else: ?>
                            <span id="usr-filter-badge" style="display:none;background:#ff7905;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;line-height:18px;text-align:center;margin-left:4px;"></span>
                        <?php endif; ?>
                    </button>
                    <?php if (SES_TYPE == 1 || SES_TYPE == 2) { ?>
                        <button style="display:inline-flex !important" href="javascript:void(0);" onclick="openUserListExportPopup();" class="cmn-btn-outline" id="task_impExp" title="<?php echo __('Export'); ?>">
                            <span class="material-icons">&#xE2C4;</span> <?php echo __('Export'); ?>
                        </button>
                    <?php } ?>
                    <!-- Grid / List view toggle (from hot-fixes branch) -->
                    <div class="usr-view-toggle" style="display:inline-flex;border:1px solid #ddd;border-radius:4px;overflow:hidden;">
                        <button id="usr-view-btn-grid"
                                onclick="setUserViewMode('grid')"
                                class="usr-view-btn active"
                                title="<?php echo __('Grid View'); ?>"
                                style="padding:5px 10px;border:none;background:#fff;cursor:pointer;display:inline-flex;align-items:center;color:#555;">
                            <span class="material-icons" style="font-size:20px;">&#xE8F1;</span>
                        </button>
                        <button id="usr-view-btn-list"
                                onclick="setUserViewMode('list')"
                                class="usr-view-btn"
                                title="<?php echo __('List View'); ?>"
                                style="padding:5px 10px;border:none;background:#fff;cursor:pointer;display:inline-flex;align-items:center;color:#555;border-left:1px solid #ddd;">
                            <span class="material-icons" style="font-size:20px;">&#xE8EF;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>