<?php $page_array = ['glideChart', 'hoursReport', 'chart', 'weeklyusageReport', 'pendingTask', 'completedSprintReport', 'velocityReports']; 
            $showSidebarNew = false || (
                (CONTROLLER == 'projecttypes' && in_array(PAGE_NAME, [
                    'projectTypes',
                ]))
                || (CONTROLLER == 'projectstatuses' && in_array(PAGE_NAME, [
                    'projectStatus',
                ]))
                || ( CONTROLLER == 'taskimports' && in_array(PAGE_NAME, [
                    'uploadImport',
                    'mapImport',
                    'previewImport',
                    'confirmImport',
                ]))
                || (CONTROLLER == 'projects' && in_array(PAGE_NAME, [
                    'manageTaskStatusGroup',
                    'groupupdatealerts',
                    'importexport',
                    'confirmImport',
                    'importtimelog',
                    'importcomment',
                    'taskType',
                    'labels',
                    'csvDataimport',
                    'csvTldataimport',
                    'csvCommentimport',
                    'confirmTlimport',
                    'confirm_import',
                    'task_settings',
                    'settings',
                    'workflowListing',
                    'workFlowSettings',
                    'importJira',
                ]))
                || (CONTROLLER == 'users' && in_array(PAGE_NAME, [
                    'mycompany',
                    'profile',
                    'changepassword',
                    'emailNotifications',
                    'emailReports',
                    'defaultView'
                ]))
                || (CONTROLLER == 'taskactions' && PAGE_NAME == 'duedateChangeReason')
                || (PAGE_NAME == 'index' && in_array(CONTROLLER, [
                    "usersidebar",
                    "roles",
                    "superset",
                    'wiki',
                ]))
                || (CONTROLLER == 'about')
                || (PLUGIN_NAME == 'GitSync')
                || (PLUGIN_NAME == 'EmailTemplating')
                || (PLUGIN_NAME == 'DeveloperApi')
            );
            ?>
<style type="text/css">
    .new_back_icon {
        font-size: 14px;
        left: 10%;
        margin-top: 90px;
        position: fixed;
        top: 29%;
        z-index: 999;
        color: #A6A6A6;
    }

    .left-menu-panel .side-nav li .fixleft-submenu ul li:hover a {
        color: #F6911D
    }

    .new_back_icon:hover {
        color: #0091EA;
    }

    .left_panel_other_link {
        display: none;
    }

    .template-menu {
        display: none;
    }

    .mini-sidebar .new_back_icon {
        display: none;
    }

    .mini-sidebar .plan-info-li {
        display: none;
    }
</style>
<div class="main-container">
    <div class="left-menu-panel cmn_white_bg">
        <aside class="option_menu_panel">
            <?php echo $this->element('nav_bar'); ?>
        </aside>
    </div>
    <?php if (!(CONTROLLER == 'mydashboards' && PAGE_NAME == 'index')): ?>
    <div class="layout-fixer <?= $showSidebarNew ? 'layout-fixer-short' : ' layout-fixer-long'?>"></div>
    <?php endif; ?>
    <div class="rht_content_cmn task_lis_page">
        <?php echo $this->element('top_bar'); ?>
        <?php echo $this->element('popup'); ?>
        <div class="wrapper" style="<?= $showSidebarNew ? 'height: calc(100vh - 100px);' : '' ?> display: flex;">
            <?php
            if ($showSidebarNew) {
                echo $this->element('sidebar_new');
            }
            ?>
            <div class="loader_bg" id="beforeRenderPage">
                <div class="loadingdata">
                    <img src="<?php echo HTTP_ROOT; ?>images/rolling.gif?v=<?php echo RELEASE; ?>" style="width:60px;"
                        alt="<?php echo __('loading'); ?>..." title="<?php echo __('loading'); ?>..." />
                </div>
            </div>
            <div class="wrapper-body" style="display: none;">
                <div class="slide_rht_con <?= $showSidebarNew ? 'layout-no-scroll' : '' ?> ">
                    <?php echo $this->fetch('content'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('[rel="tooltipi"]').tipsy({
            gravity: 'w',
            fade: true,
            html: true
        });
    });
</script>
<script>
    $(document).ready(function () {
        expandLeftSubmenu();
    });

    function expandLeftSubmenu() {
        if ($('body').hasClass('mini-sidebar') && !$('body').hasClass('hover_left_menu')) {
            $(".left-palen-submenu").removeClass('glyphicon-menu-down').addClass('glyphicon-menu-right');
            $(".left-palen-submenu-items").hide();
        } else {
            $(".left-palen-submenu-items").hide();
            if (getHash() == 'timelog' || getHash() == 'calendar_timelog') {
                $(".menu-logs").find('.left-palen-submenu').removeClass('glyphicon-menu-right').addClass('glyphicon-menu-down');
                $(".menu-logs").find('.left-palen-submenu-items').show();

            }
            if (getHash() == 'files' || getHash() == 'caselist' || PAGE_NAME == "invoice" || PAGE_NAME == "groupupdatealerts" || CONTROLLER == 'templates') {
                $(".Miscl_list").find('.left-palen-submenu').removeClass('glyphicon-menu-right').addClass('glyphicon-menu-down');
                $(".Miscl_list").find('.left-palen-submenu-items').show();

            }
            if (getHash() == 'tasks' || getHash() == 'taskgroup' || getHash() == 'kanban' || getHash() == 'calendar' || getHash() == 'details' || getHash() == 'milestonelist') {
                $(".caseMenuLeft").find('.left-palen-submenu').removeClass('glyphicon-menu-right').addClass('glyphicon-menu-down');
                $(".caseMenuLeft").find('.left-palen-submenu-items').show();
            }
            <?php if ((CONTROLLER == 'ProjectReports' || CONTROLLER == 'project_reports' || in_array(PAGE_NAME, $page_array))) { ?>
                $(".projectReportMenuLeft").find('.left-palen-submenu').removeClass('glyphicon-menu-right').addClass('glyphicon-menu-down');
                $(".projectReportMenuLeft").find('.left-palen-submenu-items').show();
            <?php } ?>
            hasLeftScrollBar();
        }
    }
</script>
