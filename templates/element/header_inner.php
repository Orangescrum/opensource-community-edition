<?php echo $this->element('header_inner_style'); ?>

<input type="hidden" name="pageurl" id="pageurl" value="<?php echo $this->Url->build('/'); ?>" size="1"
    readonly="true" />
<input type="hidden" name="pagename" id="pagename" value="<?php echo PAGE_NAME; ?>" size="1" readonly="true" />
<input type="hidden" name="fmaxilesize" id="fmaxilesize" value="<?php echo MAX_FILE_SIZE; ?>" size="1"
    readonly="true" />
<input type="hidden" name="case_srch" id="case_srch" size="1" readonly="true" value="<?php $case_num ?? '' ?>" />

<script>
    $(document).ready(function () {
        $('.search_top_hide_show_spn').hide();
        $('.search_top_hide').show();
        $('.upgrd_btn_top_hdr,.upgrd_btn').hide();
        $('.right_pfl_menu').addClass('open_search');

        $("#case_search").off('keyup').on('keyup', function (e) {
            var unicode = e.charCode ? e.charCode : e.keyCode;
            if (unicode != 13 && unicode != 40 && unicode != 38) {
                var srch = $("#case_search").val();
                srch = srch.trim();
                if (srch == "") {
                    $('#srch_load1').hide();
                    $('#srch_remv').show();
                } else {
                    $('#srch_remv').hide();
                    $('#srch_load1').show();
                }
                if (globalTimeout != null)
                    clearTimeout(globalTimeout);
                $('#ajax_search').html("");
                focusedRow = null;
                globalCount = 0;
                globalTimeout = setTimeout(ajaxCaseSearch, 1000);
            }
            if (unicode == 40 || unicode == 38) {
                if ($('.ajx-srch-tbl tr').hasClass("alltrcls")) {
                    var rowCount = $('.ajx-srch-tbl tr').length;
                    if (focusedRow == null) {
                        focusedRow = $('tr:nth-child(2)', '.ajx-srch-tbl');
                        globalCount = 1;
                    } else if (unicode === 38) {
                        focusedRow.toggleClass('selctd-srch');
                        focusedRow = focusedRow.prev('tr');
                        globalCount--;
                    } else if (unicode === 40) {
                        focusedRow.toggleClass('selctd-srch');
                        focusedRow = focusedRow.next('tr');
                        globalCount++;
                    }
                    if ((parseInt(rowCount) == globalCount) || (globalCount == 0)) {
                        focusedRow = $('tr:nth-child(2)', '.ajx-srch-tbl');
                        focusedRow.toggleClass('selctd-srch');
                        globalCount = 1;
                    } else {
                        focusedRow.toggleClass('selctd-srch');
                    }
                }
            }
        });

        $('#srch_remv').off('click').on('click', function () {
            clearSearch('outer');
        });

        $('#srch_remv').show();
    });
</script>
<?php
$themes = [
    'default' => __('Default'),
    'deep-purple'  => __('Royal Amethyst'),
    'pink'         => __('Blush Petals'),
    'cyan'         => __('Crystal Lagoon'),
    'amber'        => __('Golden Ember'),
    'teal'         => __('Emerald Tide'),
    'light-blue'   => __('Sky Serenity'),
    'purple'       => __('Velvet Orchid')
];
?>
<?php

$projUniq1 = "";

$priving_arr_fun = ["subscription", "defectSeverity", 'defectCategory', 'defectIssueType', 'defectActivityType', 'defectPhase', 'defectRootCause', 'defectFixVersion', 'defectAffectVersion', 'defectOrigin', 'defectResolution'];
$getallproj ??= [];
if (count($getallproj) >= 1) {
    $projUniq1 = $getallproj[0]['Project']['uniq_id'];
}
if ($is_active_proj || (SES_TYPE == 3)) {
    if (!isset($projUniq)) {
        $projUniq = $projUniq1;
    }
    if (CONTROLLER == 'reports' && (PAGE_NAME == 'chart' || PAGE_NAME == 'glideChart' || PAGE_NAME == 'hoursReport')) {
        $projUniq = $proj_uniq;
    }
    if (SES_TYPE == 3 && $projUniq == 'all') {
        $projUniq = $getallproj[0]['Project']['uniq_id'];
    }
    setcookie('CPUID', strval($projUniq ?? ''), time() + (86400 * 30), '/', $_SERVER['HTTP_HOST'], false, false);
    ?>

    <input type="hidden" name="projFil" id="projFil" value="<?php echo $projUniq; ?>" size="24" readonly="true" />
    <input type="hidden" name="projMethType" id="projMethType" value="<?php echo $_SESSION['project_methodology']; ?>"
        size="24" readonly="true" />
    <input type="hidden" name="projIsChange" id="projIsChange" value="<?php echo $projUniq; ?>" size="24" readonly="true" />
    <input type="hidden" name="CS_project_id" id="CS_project_id" value="<?php echo $ctProjUniq ?? ''; ?>" size="24"
        readonly="true" />
    <input type="hidden" id="CS_assign_to" value="<?php echo SES_ID; ?>">
    <input type="hidden" id="own_session_id" value="<?php echo SES_ID; ?>">
<?php } ?>

<div class="navbar custom-navbar nav_inr_menu">
    <div class="container-fluid pad-left-non">
        <div class="navbar-header logo_cmpnay_name_toggle<?php echo ' cmn_white_bg'; ?>">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand nav-logo" href="<?php if ($_COOKIE['FIRST_LOGIN_1'] ?? false) {
                echo 'javascript:void(0);';
            } else {
                echo $this->Url->build('/' . ltrim((string) \Cake\Core\Configure::read('default_action'), '/'));
            } ?>">
                <img src="<?php echo HTTP_IMAGES; ?>logo/logo-sm.svg" height="32" width="32" />
            </a>
            <div class="logo_cmpany_name">
                <ul class="nav navbar-nav">
                    <li class="respo_menu"><i class="material-icons">&#xE5D2;</i></li>
                    <li class="nav-logo project-name-hd">
                        <a title="<?php echo defined('CMP_SITE') ? CMP_SITE : 'Orangescrum'; ?>" href="<?php if ($_COOKIE['FIRST_LOGIN_1'] ?? null) {
                                   echo 'javascript:void(0);';
                               } else {
                                   echo $this->Url->build('/mydashboard');
                               } ?>"
                            class="ellipsis-view company_name_view"><?php echo defined('CMP_SITE') ? CMP_SITE : 'Orangescrum'; ?></a>
                    </li>
                    <li class="web-menu">
                        <a onclick="javascript:toggleMenuBar();" class="anchor sidebar-toggle" data-toggle="offcanvas"
                            role="button">
                            <svg class="toggle-icon" style="margin-left: 50px;" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2.5" stroke="currentColor" stroke-width="1.8"/><line x1="9" y1="4" x2="9" y2="20" stroke="currentColor" stroke-width="1.8"/></svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php $men_col_class = ($theme_settings['navbar_color'] ?? '') == "gradient-45deg-white" ? " " . "cmn_white_bg" . " " . ($theme_settings['navbar_color'] ?? '') . " gradient-shadow" : ' ' . ($theme_settings['navbar_color'] ?? '') . ' gradient-shadow'; ?>
            <?php $col_class = $theme_settings['navbar_color'] ?? ''; ?>
            <div class="navbar-collapse collapse navbar-inverse-collapse pad-left-non">
                <div class="right_pfl_menu rgt_top_header cmn_white_bg">
                    <?php
                    // Hide the project dropdown for standalone plugin pages (DMS, Risk Management, Outlook)
                    $_pluginName = defined('PLUGIN_NAME') ? PLUGIN_NAME : null;
                    $_controllerName = defined('CONTROLLER') ? CONTROLLER : '';
                    $isPluginPage = in_array($_pluginName, ['Dms', 'RiskManagement', 'OutlookSync', 'OutlookIntegration'])
                                 || in_array($_controllerName, ['documents', 'risks', 'outlooksyncconnect', 'outlooksyncmanage']);
                    ?>
                    <?php if (PAGE_NAME !== "mydashboard" && PAGE_NAME !== "mydashboardv2" && !$isPluginPage) { ?>
                        <ul class="project_drop_nav_ul top_header_projct_list">
                            <?php
                            // taskviews is listed explicitly: the clause below reads
                            // `CONTROLLER !== 'UserSidebar' && PAGE_NAME != 'index'`, which drops
                            // the picker on every controller whose action is index, not just
                            // UserSidebar's. /task-views is one of those. Correcting that grouping
                            // would change other pages, so it is left alone here.
                            if ((!in_array(PAGE_NAME, $withoutprjdropdownpageArr) && (CONTROLLER != 'advanceddashboards') && (PLUGIN_NAME != "TestCaseManager") && !$isPluginPage && (CONTROLLER !== 'projectreports') && (CONTROLLER !== 'templates') && CONTROLLER != 'Roles' && (CONTROLLER !== 'users' && PAGE_NAME != 'manage') && (!in_array(PAGE_NAME, ['defectSeverity', 'defectCategory', 'defectIssueType', 'defectActivityType', 'defectPhase', 'defectRootCause', 'defectFixVersion', 'defectAffectVersion', 'defectOrigin', 'defectResolution'])) && (CONTROLLER !== 'projects' && PAGE_NAME != 'manage') && (CONTROLLER !== 'UserSidebar' && PAGE_NAME != 'index')) || (CONTROLLER == 'ganttchart' && PAGE_NAME == 'manage') || (CONTROLLER == 'taskCycleReports') || CONTROLLER == 'taskviews') {
                            ?>
                            <li <?php if (PAGE_NAME === 'mydashboard' && SES_TYPE < 3) { ?>style="display:none" <?php } ?>><i
                                    class="material-icons folder-icon">&#xE8F9;</i></li>
                            <li class="project-dropdown" <?php if (PAGE_NAME === 'mydashboard' && SES_TYPE < 3) { ?>style="display:none" <?php } ?>>
                                <div class="btn-group">
                                    <?php if ((count($getallproj) == 0) && (SES_TYPE == 1 || SES_TYPE == 2)) {
                                    } else { ?><!--Project:--> <?php } ?>
                                    <?php if ((count($getallproj) == 0) && (SES_TYPE == 1 || SES_TYPE == 2 || SES_TYPE == 3)) { ?>
                                        <?php if ($this->Format->isAllowed('Create Project', $roleAccess)) { ?>
                                            <button class="top_project_btn btn btn_cmn_efect cmn_bg btn-info cmn_size padd_cmn"
                                                type="button" onClick="newProject();">+ <?php echo __('Create Project'); ?></button>
                                        <?php } ?>
                                        <input type='hidden' id='boarding' value='1' />
                                    <?php } else { ?>
                                        <input type='hidden' id='boarding' value='0' />
                                        <?php if (count($getallproj) == '0') { ?>
                                            <i style="color:#FF0000"><?php echo __('None'); ?></i>
                                        <?php } else {
                                            if (count($getallproj) == 1) { ?>
                                                <button aria-expanded="false" aria-haspopup="true" data-toggle="dropdown"
                                                    class="top_project_btn btn btn_cmn_efect cmn_bg btn-info cmn_size dropdown-toggle project-drop-custom-pad prtl"
                                                    type="button" onclick="view_project_menu('<?php echo PAGE_NAME; ?>');">
                                                    <span id="pname_dashboard" class="ttc ellipsis-view top_header_ptjwth">
                                                        <?php if ($projUniq == 'all') { ?>
                                                            <a class="top_project_name " title="<?php echo __('All'); ?>"
                                                                href="javascript:void(0);"
                                                                onClick="updateAllProj(0,0,'dashboard', 'all', 'All');"><?php echo __('All'); ?></a>
                                                        <?php } else { ?>
                                                            <a class="top_project_name "
                                                                title="<?php echo h(ucfirst($getallproj[0]['Project']['name'])); ?>"
                                                                href="javascript:void(0);"
                                                                onClick="<?php if (PAGE_NAME == 'mydashboard') { ?> CaseDashboard('<?php echo $getallproj[0]['Project']['uniq_id']; ?>','<?php echo h(addslashes($getallproj[0]['Project']['name'])); ?>'); <?php } else { ?> updateAllProj('proj_<?php echo $getallproj[0]['Project']['uniq_id']; ?>','<?php echo $getallproj[0]['Project']['uniq_id']; ?>', '<?php echo PAGE_NAME; ?>', 0, '<?php echo $getallproj[0]['Project']['name']; ?>'); <?php } ?>"><?php echo h(ucfirst($getallproj[0]['Project']['name'])); ?></a>
                                                        <?php } ?>
                                                    </span>
                                                    <i class="nav-dot material-icons">expand_more</i>
                                                </button>
                                                <?php
                                                $swPrjVal = $getallproj[0]['Project']['name'];
                                                $soprjval = $getallproj[0]['Project']['name'];
                                            } else {
                                                $swPrjVal = $this->Format->shortLength($getallproj[0]['Project']['name'], 20);
                                                $soprjval = $getallproj[0]['Project']['name'];
                                                ?>
                                                <input type="hidden" id="pname_dashboard_hid"
                                                    value="<?php echo $this->Format->formatTitle($getallproj[0]['Project']['name']); ?>" />
                                                <input type="hidden" id="first_recent_hid"
                                                    value="<?php echo $getallproj['1']['Projects']['name'] ?? ''; ?>" />
                                                <input type="hidden" id="second_recent_hid"
                                                    value="<?php echo $getallproj['2']['Projects']['name'] ?? ''; ?>" />
                                                <button aria-expanded="false" aria-haspopup="true" data-toggle="dropdown"
                                                    class="top_project_btn btn btn_cmn_efect cmn_bg btn-info cmn_size dropdown-toggle project-drop-custom-pad prtl"
                                                    type="button" onclick="view_project_menu('<?php echo PAGE_NAME; ?>');">
                                                    <span id="pname_dashboard" class="ttc ellipsis-view top_header_ptjwth">
                                                        <?php if ($projUniq == 'all') { ?>
                                                            <a class="top_project_name" title="<?php echo __('All'); ?>"
                                                                href="javascript:void(0);"
                                                                onClick="updateAllProj(0,0,'dashboard', 'all', 'All');"><?php echo __('All'); ?></a>
                                                        <?php } else { ?>
                                                            <a class="top_project_name ellipsis-view"
                                                                title="<?php echo h(ucfirst($getallproj[0]['Project']['name'])); ?>"
                                                                href="javascript:void(0);"
                                                                onClick="<?php if (PAGE_NAME == 'mydashboard') { ?> CaseDashboard('<?php echo $getallproj[0]['Project']['uniq_id']; ?>','<?php echo h(addslashes($getallproj[0]['Project']['name'])); ?>'); <?php } else { ?> updateAllProj('proj_<?php echo $getallproj[0]['Project']['uniq_id']; ?>','<?php echo $getallproj[0]['Project']['uniq_id']; ?>', '<?php echo PAGE_NAME; ?>', 0, '<?php echo $getallproj[0]['Project']['name']; ?>'); <?php } ?>"><?php echo h(ucfirst($getallproj[0]['Project']['name'])); ?></a>
                                                        <?php } ?>
                                                    </span>
                                                    <i class="nav-dot material-icons">expand_more</i>
                                                </button>
                                            <?php } ?>
                                            <div class="dropdown-menu lft popup" id="projpopup">
                                                <center>
                                                    <div id="loader_prmenu" style="display:none;">
                                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="loading..."
                                                            title="<?php echo __('loading'); ?>..." />
                                                    </div>
                                                </center>
                                                <?php if (count($getallproj) >= 6) { ?>
                                                    <div id="find_prj_dv" class="pr" style="display: none;">
                                                        <i class="material-icons">&#xE8B6;</i>
                                                        <input type="text" placeholder="<?php echo __('Find Project'); ?>"
                                                            class="form-control pro_srch "
                                                            onkeyup="search_project_menu('<?php echo PAGE_NAME; ?>',this.value,event)"
                                                            id="search_project_menu_txt">
                                                        <div id="load_find_dashboard" style="display:none;" class="loading-pro">
                                                            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" />
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div id="ajaxViewProject" style='display:none;'></div>
                                                <div id="ajaxViewProjects" class="scroll-project"></div>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </li>
                        <?php } else { ?>
                            <?php if (count($getallproj) > '0') { ?>
                                <input type="hidden" id="pname_dashboard_hid"
                                    value="<?php echo $this->Format->formatTitle($getallproj[0]['Project']['name']); ?>" />
                            <?php } ?>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <ul class="nav navbar-nav profile-bar top_header_profile_sec">
                    <li class="">
                        <?php if (PAGE_NAME !== "work_load" && PAGE_NAME !== "updates" && PAGE_NAME != "defect") { ?>
                            <form class="navbar-form navbar-search top_search header-search-box" role="search">
                                <div id="tour_proj_srch" class="form-group" <?php if (in_array(PAGE_NAME, $priving_arr_fun)) { ?>style="display:none;" <?php } ?>>
                                    <div id="srch_remv" onclick="clearSearch('outer');">
                                        <i class="material-icons">clear</i>
                                    </div>
                                    <div id="srch_load1" class="lod-src-itm">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="loading"
                                            title="<?php echo __('loading'); ?>" />
                                    </div>
                                    <span class="nav-srch-icon search_top_hide_show_spn search_icon_header"
                                        style="display:none;">
                                        <i class="material-icons search_top_hide_show_icon">&#xE8B6;</i>
                                    </span>
                                    <?php if (PAGE_NAME != "dashboard") { ?>
                                        <input type="hidden" name="casePage" id="casePage" value="1" size="4" readonly="true" />
                                    <?php } ?>
                                    <input type="text" class="form-control col-md-8 nav-srch-box search_top search_top"
                                        name="case_search" autocomplete="off" id="case_search"
                                        onkeypress="onKeyPress(event,'case_search');"
                                        onkeydown="return goForSearch(event,'');"
                                        placeholder="<?php echo __('Search...'); ?>" />
                                </div>
                                <div id="ajax_search" class="ajx-srch-dv1"></div>
                            </form>
                        <?php } ?>
                    </li>
                    <?php if (PAGE_NAME != "updates" && PAGE_NAME != "help" && PAGE_NAME != "tour" && PAGE_NAME != "customer_support" && PAGE_NAME != 'onbording') { ?>
                        <li class="help_navli dropdown-hlp" title="<?php echo __('Help'); ?>" rel="tooltip_down">
                            <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" aria-expanded="true">
                                <i class="material-icons top_header_icon_sec dropdown_icon_black"
                                    >help_outline</i>
                            </a>
                            <ul class="dropdown-menu drop_menu_mc dropdown_menu_all_filters_ul help_drop_menu_mc">
                                <li class="drop_menu_mc top_hader_dropdown_listing">
                                    <a href="<?= h($helpdeskUrl) ?>" target="_blank">
                                        <i class="material-icons">support_agent</i><?php echo __('Help Desk'); ?>
                                    </a>
                                </li>
                                <li class="drop_menu_mc top_hader_dropdown_listing">
                                    <a href="<?= h($contactsalesURL) ?>" target="_blank">
                                        <i class="material-icons">contact_mail</i><?php echo __('Contact Support'); ?>
                                    </a>
                                </li>
                                <li class="drop_menu_mc top_hader_dropdown_listing">
                                    <a href="<?= h($requestdemoURL) ?>" target="_blank">
                                        <i class="material-icons">event_available</i><?php echo __('Request a Demo'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="theme_navli dropdown-theme" title="<?php echo __('Theme'); ?>" rel="tooltip_down">
                            <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" aria-expanded="true">
                                <i class="material-icons top_header_icon_sec hlp-icon"
                                    >palette</i>
                            </a>
                            <ul class="dropdown-menu drop_menu_mc dropdown_menu_all_filters_ul theme_drop_menu_mc">
                                <li class="drop_menu_mc top_hader_dropdown_listing">
                                    <strong style="padding: 5px 10px; display: block; border-bottom: 1px solid #eee; margin-bottom: 5px;"><?php echo __('Choose Theme'); ?></strong>
                                </li>
                                <?php foreach ($themes as $slug => $label) { ?>
                                    <li class="drop_menu_mc top_hader_dropdown_listing header-theme-option" data-value="<?=$slug?>">
                                        <a href="javascript:void(0);" style="display: flex; align-items: center;">
                                            <span class="theme-indicator indicator-<?=$slug?>" style="width: 10px; height: 10px; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                                            <span><?=h($label)?></span>
                                            <?php if (($theme_settings['sidebar_color'] ?? 'default') == $slug) { ?>
                                                <i class="material-icons" style="margin-left: auto; font-size: 18px; color: #177cc3;">check</i>
                                            <?php } ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php $appLauncherHtml = $this->element('app_launcher'); ?>
                    <?php if (trim($appLauncherHtml) !== '') { ?>
                        <li class="nav-app-launcher cmn_parent_navli">
                            <?php echo $appLauncherHtml; ?>
                        </li>
                    <?php } ?>
                    <li class="nav-profile-dropdown dropdown hover-menu cmn_parent_navli pfl_dtl_li">
                        <?php
                        $usrArr = $this->Format->getUserDtls(SES_ID);
                        if (!empty($usrArr)) {
                            $ses_name = $usrArr['name'];
                            $ses_photo = $usrArr['photo'];
                            $ses_email = $usrArr['email'];
                            $ses_last_name = $usrArr['last_name'];
                        }
                        ?>
                        <a href="javascript:void(0);" data-target="#" class="dropdown-toggle top_header_icon_sec <?php if ($men_col_class == ' cmn_white_bg gradient-45deg-white gradient-shadow') {
                            echo 'dropdown_icon_black';
                        } ?>" data-toggle="dropdown" id="tour_profile_setting">
                            <div class="prof_sett">
                                <span
                                    class="user_ipad"><?php echo $this->Format->shortLength(trim($ses_name), 8); ?></span>
                                <?php if (trim($ses_photo ?? '')) { ?>
                                    <img data-original="<?php echo $this->Url->build('/users/image_thumb/?type=photos&file=' . rawurlencode((string) $ses_photo) . '&sizex=28&sizey=28&quality=100'); ?>"
                                        class="lazy round_profile_img" height="28" width="28" />
                                <?php } else {
                                    $random_bgclr = $this->Format->getProfileBgColr(SES_ID);
                                    $usr_name_fst = mb_substr(trim($ses_name), 0, 1, "utf-8");
                                    ?>
                                    <span
                                        class="cmn_profile_holder <?php echo $random_bgclr; ?>"><?php echo $usr_name_fst; ?></span>
                                <?php } ?>
                                <span class="sett_icn">
                                    <i class="material-icons">&#xE8B8;</i>
                                </span>
                                <div class="cb"></div>
                            </div>
                        </a>
                        <div class="dropdown-menu top_maindropdown-menu cap-setting cmn_setting_menu fadeout_bkp"
                            id="tour_mainporf_setting_drop">
                            <div class="cmn_groupy_menu">
                                <ul id="group_menu">
                                    <li>
                                        <a href="javascript:void(0)" class="user_card">
                                            <span
                                                class="avatar_circle"><?= defined('USERSHORTNAME') ? USERSHORTNAME : '' ?></span>
                                            <span class="user_info">
                                                <strong
                                                    class="name_elipsis"><?= defined('USERNAME') ? USERNAME : '' ?></strong><br>
                                                <span
                                                    class="email-info name_elipsis"><?= defined('USEREMAIL') ? USEREMAIL : '' ?></span>
                                            </span>
                                        </a>
                                    </li>
                                    <li><a href="<?php echo $this->Url->build(['plugin' => null, 'controller' => 'Users', 'action' => 'profile']); ?>"
                                            class="grp_ttle">
                                            <span class="cmn_cstm_set"><i class="material-icons">perm_identity</i><i
                                                    class="material-icons abs_set_icon">&#xE8B8;</i></span>
                                            <?php echo __('My Profile'); ?></a></li>
                                    <?php if (!empty($isMultiTenant)): ?>
                                    <li><a href="<?php echo $this->Url->build(['plugin' => null, 'controller' => 'Users', 'action' => 'launchpad', '?' => ['show_all' => 1]]); ?>"
                                            class="grp_ttle">
                                            <span class="cmn_cstm_set"><i class="material-icons">swap_horiz</i></span>
                                            <?php echo __('Switch Company'); ?></a></li>
                                    <?php endif; ?>
                                    <li><a href="javascript:void(0)" class="grp_ttle togle_link"><span
                                                class="cmn_cstm_set"><i class="material-icons">info</i></span>
                                            <?php echo __('Orangescrum Info'); ?></a>
                                        <ul class="grp_sub_item">
                                            <li><a
                                                    href="<?php echo $this->Url->build('/getting_started'); ?>"><?php echo __('Getting Started'); ?></a>
                                            </li>
                                            <li><a
                                                    href="<?php echo $this->Url->build('/whats-new'); ?>"><?php echo __('Product Updates'); ?></a></li>
                                        </ul>
                                    </li>
                                    <li class="border_top"><a
                                            href="<?php echo $this->Url->build(['plugin' => null, 'controller' => 'Users', 'action' => 'logout']); ?>"
                                            class="sign_out grp_ttle" onclick=""><span class="cmn_cstm_set"><i
                                                    class="material-icons">power_settings_new</i></span>
                                            <?php echo __('Log Out'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="cb"></div>
        </div>
    </div>
</div>
<?php if (PAGE_NAME == 'onbording') { ?>
    <div class="crt_slide task_action_bar">
        <button type="button" class="btn gry_btn task_create_back" onclick="crt_popup_close()"><i
                class="icon-backto"></i><?php echo __('Go Back'); ?></button>
    </div>
<?php } ?>
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        $('#group_menu > li > a').click(function () {
            if (!$(this).hasClass('active')) {
                $('#group_menu li .grp_sub_item').slideUp();
                $(this).next().slideToggle();
                $('#group_menu li a').removeClass('active');
                $(this).addClass('active');
            } else {
                $('#group_menu li .grp_sub_item').slideUp();
                $('#group_menu li a').removeClass('active');
            }
        });
    });
    $('#tour_profile_setting').click(function () {
        $('.dropdown-hlp').removeClass('open');
        $('.dropdown-time').removeClass('open');
    });
    $('.nav-notification-bar').click(function () {
        $('.dropdown-hlp').removeClass('open');
        $('.dropdown-time').removeClass('open');
    });
    $('.dropdown_menu_all_filters_ul').click(function () {
        $('.dropdown-time').removeClass('open');
        $('.dropdown-hlp').removeClass('open');
    });
    $('#feeback_modal_open').click(function () {
        $('#feeback_modal').modal('show');
        $('.feeback_modal').addClass('open');
        $('.feeback_modal').css({
            'display': 'block'
        });
        return false;
    });
    $('.toggle-icon').click(function () {
        const isRotated = $(this).data('rotated');
        $(this).css({ transform: isRotated ? 'rotate(0deg)' : 'rotate(180deg)', transition: 'transform 0.3s ease-in-out' }).data('rotated', !isRotated);
    });

    $('.dropdown-theme > .dropdown-toggle').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $themeDropdown = $(this).closest('.dropdown-theme');
        var shouldOpen = !$themeDropdown.hasClass('open');

        $('.dropdown-theme').removeClass('open');
        if (shouldOpen) {
            $themeDropdown.addClass('open');
        }
    });

    $('.dropdown-theme .theme_drop_menu_mc').on('click', function (e) {
        e.stopPropagation();
    });

    if (!window.__osThemeDropdownCaptureBound) {
        window.__osThemeDropdownCaptureBound = true;
        document.addEventListener('click', function (event) {
            if (!$(event.target).closest('.dropdown-theme').length) {
                $('.dropdown-theme').removeClass('open');
            }
        }, true);
    }

    $('.header-theme-option').click(function(e) {
        e.preventDefault();
        var val = $(this).data('value');
        if (typeof theme_setting !== 'undefined') {
            $('#sidebar_color').val(val);
            theme_setting.menuColor(val);
            theme_setting.saveSetting();
        }
        $('.dropdown-theme').removeClass('open');
    });
</script>
<script type="text/template" id="notification_tmpl">
    <?php echo $this->element('top_notifications'); ?>
</script>
<script type="text/template" id="pr_release_notification_tmpl">
    <?php echo $this->element('top_notifications_productrelease'); ?>
</script>
<?php if (CONTROLLER == 'Pages' && PAGE_NAME == 'releaseActivity') { ?>
    <script type="text/template" id="productrelease_tmpl">
                        <?php echo $this->element('productrelease'); ?>
                </script>
<?php } ?>