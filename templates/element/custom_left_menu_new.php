<?php

use App\Controller\Component\FormatComponent;
use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Hash;

$url = '';

$allLeftMenu = Cache::read('userMenu' . SES_COMP . '_' . SES_ID);
if (!is_array($allLeftMenu) || empty($allLeftMenu['allUsermenus']) || $allLeftMenu['allUsermenus'] == '') {
    $formatComponent = new FormatComponent(new ComponentRegistry());
    $formatComponent->setLeftMenu();
    $allLeftMenu = Cache::read('userMenu' . SES_COMP . '_' . SES_ID);
}

// Safety check: Ensure $allLeftMenu is an array
if (!is_array($allLeftMenu)) {
    $allLeftMenu = ['allUsermenus' => [], 'menus' => []];
}

if (!empty($cstm_order) && is_array($allLeftMenu['allUsermenus']) && count($allLeftMenu['allUsermenus'])) {
    /*
    Sort the user menus based on a custom order.
    1. It checks if there's a custom order ($cstm_order) and if there are any user menus.
    2. The custom order string is split into an array.
    3. The original user menus are stored in $sorted_arr.
    4. Two new arrays are created: $sort (to index menus by their ID) and $sorted (for the final sorted result).
    5. The code loops through the original menus and indexes them by ID in $sort.
    6. It then loops through the custom order array:
       - If a menu exists in $sort for the current ID, it's added to $sorted.
       - If not, it checks if it's a special menu type and adds a placeholder.
    7. Any remaining menus not in the custom order are appended to $sorted.
    8. Finally, the sorted menus replace the original menus in $allLeftMenu['allUsermenus'].
    */

    $cstm_order_arr = explode(',', $cstm_order);
    $allUsermenus = $allLeftMenu['allUsermenus'];
    $all_usermenus_order = Hash::extract($allUsermenus, '{n}.id');
    // Prefer user menus order if available
    if (!empty($all_usermenus_order)) {
        $cstm_order_arr = array_unique(array_merge($all_usermenus_order, $cstm_order_arr));
    }
    $more_id = 38;
    if (isset($allLeftMenu['menus']) && is_array($allLeftMenu['menus'])) {
        foreach ($allLeftMenu['menus'] as $menu) {
            if (isset($menu['name']) && $menu['name'] === 'More') {
                $more_id = $menu['id'];
                break;
            }
        }
    }
    // Keep $more_id at the end of the list
    $more_id_key = array_search($more_id, $cstm_order_arr);
    if ($more_id_key !== false) {
        unset($cstm_order_arr[$more_id_key]);
    }
    $cstm_order_arr[] = $more_id;
    $sorted_arr = $allLeftMenu['allUsermenus'];
    $sort = [];
    $sorted = [];

    foreach ($sorted_arr as $k => $v) {
        $sort[$v['id']] = $v;
    }

    foreach ($cstm_order_arr as $k => $v) {
        if (isset($sort[$v]) && !empty($sort[$v])) {
            $sorted[$v] = $sort[$v];
        } else {
            $mm = isset($allLeftMenu['menus'][$v]) ? $allLeftMenu['menus'][$v] : '';
            if (isset($allLeftMenu['menus'][$v]['menu_type']) && $allLeftMenu['menus'][$v]['menu_type'] == 1) {
                $sorted[$v]['id'] = $v;
            }
        }
    }
    foreach ($sort as $k => $v) {
        if (!array_key_exists($k, $sorted)) {
            $sorted[$k] = $v;
        }
    }
    $allLeftMenu['allUsermenus'] = $sorted;
}
$pmethodology = !empty($pmethodology) ? $pmethodology : $_SESSION['project_methodology'];

$result = Hash::extract($allLeftMenu, 'allUsermenus.{n}.children.{n}.id');

if (is_array($allLeftMenu) && isset($allLeftMenu['allUsermenus']) && is_array($allLeftMenu['allUsermenus']) && count($allLeftMenu['allUsermenus'])) {
    $masterMenu = $allLeftMenu['menus'] ?? [];
    foreach ($allLeftMenu['allUsermenus'] as $k => $v) {
        if (!empty($v)) {
            $menu_id = $v['id'];
            if (!$menu_id || empty($masterMenu[$menu_id])) {
                continue;
            }
            $meta = json_decode($masterMenu[$menu_id]['meta'] ?? '{}', true) ?: [];
            $menuStatus = $this->Format->checkCustomMenuStatus($masterMenu[$menu_id], $theme_settings, $page_array, $roleAccess, $pmethodology, 1, $url);
            $new_dashboard_active_cls = '';
            if (PAGE_NAME == 'classicdashboard') {
                $new_dashboard_active_cls = $menuStatus['active_class'] ?? '';
                $menuStatus['active_class'] = '';
            }
            ?>
            <?php
            $setUrl = ($menuStatus['dynamic_url'] ?? false) ? $menuStatus['dynamic_url'] : HTTP_ROOT . ltrim($meta['url'] ?? '', '/');
            $setAClick = ($menuStatus['dynamic_a_click'] ?? false) ? $menuStatus['dynamic_a_click'] : ($meta['a_click'] ?? '');
            $setMenuName = ($menuStatus['dynamic_menu_name'] ?? false) ? $menuStatus['dynamic_menu_name'] : ($masterMenu[$menu_id]['name'] ?? '');
            $vHasChildren = ($v['children'] ?? false) && count($v['children']) && !isset($menuStatus['not_show_inner_menu']);
            if ($vHasChildren) {
                $allChildrenAllowed = false;
                foreach ($v['children'] as $k1 => $v1) {
                    if (!empty($v1)) {
                        $menuId = $v1['id'];
                        if (empty($masterMenu[$menuId])) {
                            continue;
                        }
                        $menuConfig = $masterMenu[$menuId];
                        $c_menuStatus = $this->Format->checkCustomMenuStatus($menuConfig, $theme_settings, $page_array, $roleAccess, $pmethodology, 0, $url);
                        if (isset($c_menuStatus['isAllow']) && $c_menuStatus['isAllow']) {
                            $allChildrenAllowed = true;
                            break;
                        }
                    }
                }
                $vHasChildren = $allChildrenAllowed;
            }
            if (
                isset($masterMenu[$menu_id]['name'])
                && strtolower($masterMenu[$menu_id]['name']) === 'more'
                && !$vHasChildren
            ) {
                $menuStatus['isAllow'] = false;
            }
            ?>
            <?php if ($menuStatus && isset($menuStatus['isAllow']) && $menuStatus['isAllow']) { ?>
                <li class="sidebar_parent_li <?php echo $meta['li_class'] ?? ''; ?>  <?php echo $menuStatus['active_class'] ?? ''; ?>"
                    id="<?php echo $meta['li_id'] ?? ''; ?>" <?php if ($meta['li_click'] ?? false) { ?> onclick="<?php echo $meta['li_click']; ?>"
                    <?php } ?> onmouseover="isInViewport(this);">

                    <a href="<?= $setUrl ?>"
                        onclick="<?= $setAClick ?><?= (isset($masterMenu[$menu_id]['name']) && $masterMenu[$menu_id]['name'] === "Projects") ? ' resetProjectFilterItem();' : '' ?>">
                        <?php echo $masterMenu[$menu_id]['menu_icon'] ?? ''; ?>
                        <span class="mini-sidebar-label"><?php echo __($setMenuName); ?></span>
                        <?php if ($vHasChildren) { ?>
                            <i class="material-icons sidebar_arrow_icons" onClick="event.stopPropagation();">&#xE315;</i>
                        <?php } ?>
                    </a>
                    <?php if ($vHasChildren) { ?>

                        <ul class="hover_sub_menu<?= (isset($masterMenu[$menu_id]['name']) && $masterMenu[$menu_id]['name'] == 'Reports') ? ' incr_height' : '' ?>">

                            <?php
                            if (isset($masterMenu[$menu_id]['name']) && $masterMenu[$menu_id]['name'] == 'Reports') { ?>
                                <li class="sticky_only">
                                    <a href="<?php echo HTTP_ROOT; ?>project_reports/dashboard">
                                        <span style="display: inline-block; vertical-align: middle;" class="multilang_ellipsis">
                                            <?php echo __('All Reports'); ?>
                                        </span>
                                    </a>
                                </li>
                            <?php } ?>

                            <?php
                            foreach ($v['children'] as $k1 => $v1) {
                                if (!empty($v1) && isset($v1['id'])) {
                                    $c_menu_id = $v1['id'];
                                    if (empty($masterMenu[$c_menu_id])) continue;
                                    $c_meta = json_decode($masterMenu[$c_menu_id]['meta'] ?? '{}', true) ?: [];
                                    $c_menuStatus = $this->Format->checkCustomMenuStatus($masterMenu[$c_menu_id], $theme_settings, $page_array, $roleAccess, $pmethodology, 0, $url);

                                    ?>
                                    <?php
                                    $setUrl = ($c_menuStatus['dynamic_url'] ?? false) ? $c_menuStatus['dynamic_url'] : HTTP_ROOT . ltrim($c_meta['url'], '/');
                                    $setAClick = ($c_menuStatus['dynamic_a_click'] ?? false) ? $c_menuStatus['dynamic_a_click'] : $c_meta['a_click'];
                                    $setMenuName = ($c_menuStatus['dynamic_menu_name'] ?? false) ? $c_menuStatus['dynamic_menu_name'] : $masterMenu[$c_menu_id]['name'];
                                    ?>
                                    <?php if ($c_menuStatus['isAllow'] ?? false) { ?>
                                        <li id="<?php echo $c_meta['li_id'] ?? ''; ?>"
                                            class="<?php echo $c_meta['li_class'] ?? ''; ?> <?php echo $c_menuStatus['active_class'] ?? ''; ?>" <?php if ($c_meta['li_click'] ?? false) { ?> onclick="<?php echo $c_meta['li_click']; ?>" <?php } ?>>
                                            <a href="<?php echo $setUrl; ?>" <?php if (true) { ?>
                                                    onclick="<?php echo $setAClick; ?> displayMenuProjects('special_mydashoard', '6', '');" <?php } ?>
                                                title="<?php echo $c_meta['a_tooltip'] ?? ''; ?>">
                                                <?php  echo $masterMenu[$c_menu_id]['menu_icon'] ?? ''  ?>
                                                <span style="display: inline-block; vertical-align: middle;"
                                                    class="multilang_ellipsis"><?php echo __($setMenuName); ?></span>
                                                <?php echo $c_meta['cnt_span'] ?? ''; ?>
                                            </a>
                                        </li>
                                    <?php }
                                } ?>
                            <?php } ?>

                            <?php if (isset($masterMenu[$menu_id]['name']) && $masterMenu[$menu_id]['name'] == 'Tasks') { ?>
                                <li class="filter-dropdown" data-step="2"
                                    data-intro="<?php echo __('This is the filter drop-down, by choosing this you can move to any filter type'); ?>.">
                                    <div class="btn-group margin-left-2" id="filterSearch_id">
                                        <button aria-expanded="false" aria-haspopup="true" data-toggle="dropdown"
                                            class="top_project_btn btn btn_cmn_efect cmn_bg btn-info cmn_size dropdown-toggle project-drop-custom-pad prtl<?php echo ' ' . ($theme_settings['sidebar_color'] ?? '') . ' gradient-shadow'; ?>"
                                            type="button" onclick="viewFilters_new();">
                                            <span class="">
                                                <a href="javascript:void(0);" class="top_project_name1" rel="">
                                                    <?php echo __('Loading'); ?>
                                                </a>
                                                <span class="csm_flt_more"><i class="material-icons">&#xE5D4;</i></span>
                                            </span>
                                        </button>
                                    </div>
                                </li>
                                <script type="text/template" id="filterSearch_id_tmpl">
                                    <?php echo $this->element('search_filter'); ?>
                                </script>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </li>
                <?php if (($meta['url'] ?? '') == 'mydashboard' && in_array(SES_TYPE, [1, 2])) { ?>
                    <li class="sidebar_parent_li <?php echo $meta['li_class'] ?? ''; ?>  <?php echo $new_dashboard_active_cls; ?>"
                        id="<?php echo $meta['li_id'] ?? ''; ?>" <?php if ($meta['li_click'] ?? false) { ?> onclick="<?php echo $meta['li_click']; ?>"
                        <?php } ?> onmouseover="isInViewport(this);">
                        <?php
                        $setUrl = ($menuStatus['dynamic_url'] ?? false) ? $menuStatus['dynamic_url'] : HTTP_ROOT . ltrim($meta['url'] ?? '', '/');
                        $setUrl .= '/v2';
                        $setAClick = ($menuStatus['dynamic_a_click'] ?? false) ? $menuStatus['dynamic_a_click'] : ($meta['a_click'] ?? '');
                        $setMenuName = ($menuStatus['dynamic_menu_name'] ?? false) ? $menuStatus['dynamic_menu_name'] : ($masterMenu[$menu_id]['name'] ?? '');
                        ?>
                    </li>
                <?php } ?>

            <?php }
        } ?>
    <?php }
} ?>

<script>

    var isInViewport = function (elem) {
        obj = $(elem).find('.hover_sub_menu');
        if (typeof obj != 'undefined' && typeof obj.offset() != 'undefined') {
            var top_of_element = obj.offset().top;
            var bottom_of_element = obj.offset().top + obj.outerHeight();
            var bottom_of_screen = $(window).scrollTop() + $(window).innerHeight();
            var top_of_screen = $(window).scrollTop();

            if ((bottom_of_screen <= bottom_of_element)) {
                $(elem).addClass('miscellaneous_li');
            } else {

            }
        }
    };

    $(document).ready(function () {
        $('.sidebar_arrow_icons').on('click', function (e) {
            e.preventDefault();
            const $icon = $(this);
            const $parentLi = $icon.closest('.sidebar_parent_li');
            const $dropdown = $parentLi.find('.hover_sub_menu');

            $dropdown.toggleClass('open');
            $icon.toggleClass('rotated', $dropdown.hasClass('open'));

            $('.hover_sub_menu').not($dropdown).removeClass('open');
            $('.sidebar_arrow_icons').not($icon).removeClass('rotated');
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.sidebar_parent_li').length) {
                $('.hover_sub_menu').removeClass('open');
                $('.sidebar_arrow_icons').removeClass('rotated');
            }
        });
    });
</script>

<?php if (CONTROLLER != 'UserSidebar') { ?>
    <?php
    $settingsActive = false;
    // Check if any submenu under "Project Settings" is active
    if (
        (CONTROLLER == 'projecttypes' && in_array(PAGE_NAME, [
            'projectTypes',
        ]))
        || (CONTROLLER == 'projectstatuses' && in_array(PAGE_NAME, [
            'projectStatus',
        ]))
        || (CONTROLLER == 'customfields' && in_array(PAGE_NAME, [
            'projectCustomField',
            'customField',
        ]))
        || ( CONTROLLER == 'taskimports' && in_array(PAGE_NAME, [
            'uploadImport',
            'mapImport',
            'previewImport',
            'confirmImport',
        ]))
        || (CONTROLLER == 'projects' && in_array(PAGE_NAME, [
            'manageTaskStatusGroup'
        ])) ||
        (CONTROLLER == 'users' && PAGE_NAME == 'mycompany') ||
        (CONTROLLER == 'taskactions' && PAGE_NAME == 'duedateChangeReason') ||
        ((CONTROLLER == 'projects' || CONTROLLER == 'costs') && in_array(PAGE_NAME, [
            'groupupdatealerts',
            'importexport',
            'confirmImport',
            'importtimelog',
            'importcomment',
            'taskType',
            'labels',
            'csvDataimport',
            'csvTldataimport',
            'csv_commentimport',
            'confirmTlimport',
            'confirm_import',
            'task_settings',
            'settings',
            'workflowListing',
            'workFlowSettings',
            'importJira'
        ])) ||
        (CONTROLLER == 'invoices' && in_array(PAGE_NAME, ['settings', 'importCustomers', 'csvDataimport', 'confirmImport', 'workflowListing'])) ||
        (CONTROLLER == 'users' && in_array(PAGE_NAME, ['showCustomerInUserTab', 'profile', 'changepassword', 'emailNotifications', 'emailReports', 'defaultView'])) ||
        (CONTROLLER == "usersidebar" && PAGE_NAME == "index")
    ) {
        $settingsActive = true;
    }
    ?>
    <li class="sidebar_parent_li menu_sett_li <?php echo $settingsActive ? 'active-lists' : ''; ?>"
        style="display: flex; align-items: center;">
        <a href="<?php echo $this->Url->build(['controller' => 'Users', 'plugin' => null, 'action' => 'profile']); ?>" class="menu-settings-link">
            <i class="left-menu-icon material-icons">settings</i>
            <span class="mini-sidebar-label"><?php echo __('Settings'); ?></span>
        </a>
        <i class="material-icons close-sidebar-icon dbl_arrow_icon" onclick="closeSidebar(event)"
            style="margin-left: auto;">double_arrow</i>
    </li>
<?php } ?>