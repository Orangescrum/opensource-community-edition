<style>
    .dashboard_design {
        font: bold 16px Arial;
        text-decoration: none;
        color: #333333;
        padding: 5px;
    }

    .dashboard_design a {
        color: #ff7a59
    }

    .dashboard_design a:hover {
        text-decoration: underline;
    }
</style>
<input type='hidden' id='hiddensavennew' value='0' />
<?php if (CONTROLLER == 'dashboard' && PAGE_NAME == 'index' && PLUGIN_NAME != 'TestCaseManager') { ?>
    <!-- Vue 3 dashboard handles its own top bar -->
<?php } elseif (CONTROLLER == 'easycases' && in_array(PAGE_NAME, ['mydashboard', 'mydashboardv2'])) { ?>
    <div class="task-list-bar dbrd-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-md-9">
                    <h2><?php echo __('Dashboard'); ?></h2>
                </div>
                <div class="col-md-3">
                    <?php if (isset($isAdvancedDashboard) && $isAdvancedDashboard) { ?>
                        <h2 class="text-right"><span class="dashboard_design"><a href="<?php echo $this->Url->build('/advanced-dashboards') ?>"> <?php echo __("Advanced Dashboard"); ?></a></span></h2>
                    <?php } ?>
                </div>
                <?php if (SES_TYPE < 3) { ?>
                <?php } else { ?>
                    <div class="col-lg-5 col-sm-5 m-top-bar text-right">
                        <div class="top_total_spent_hrs">
                            <div class="col-md-6"></div>
                            <div class="date col-md-6">
                                <strong><?php echo __('Hrs. Spent this week'); ?></strong>
                                <span
                                    id="spent_hr_stdt"><?php echo $this->Format->format_time_hr_min($totalhours ?? 0) != '' ? $this->Format->format_time_hr_min($totalhours ?? 0) : '00 hrs & 00 mins'; ?></span>
                            </div>
                            <div class="cb"></div>
                        </div>
                    </div>
                <?php } ?>
                <div class="cb"></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'advanceddashboards' &&  in_array (PAGE_NAME, ['dashboard', 'index'])) { ?>
<?php } elseif (CONTROLLER == 'wiki' && PAGE_NAME == 'index') { ?>
<?php } elseif (CONTROLLER == 'projects' && PAGE_NAME == 'manage') { ?>
    <?php
    if ($projtype == '') {
        $cookie_value = 'active-grid';
    } elseif ($projtype == 'inactive') {
        $cookie_value = 'inactive-grid';
    }
    if ($projtype == 'active-grid') {
        $gr_cookie_value = '';
        $cookie_value = 'active-grid';
    } elseif ($projtype == 'inactive-grid') {
        $gr_cookie_value = 'inactive';
        $cookie_value = 'inactive-grid';
    }
    if ($projtype == 'active-grid' || $projtype == 'inactive-grid') {
        $act_grid_view_opt = "active-grid";
        $inact_grid_view_opt = "inactive-grid";
        $extr_proj_url_params = "/active-grid";
    } else {
        $act_grid_view_opt = "";
        $inact_grid_view_opt = "inactive";
        $extr_proj_url_params = "";
    }
    ?>
    <div class="task-list-bar  project-grid-page">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <div
                        class="<?php if ($projtype !== 'active-grid') { ?> col-lg-8 col-sm-8  <?php } else { ?> col-lg-8 col-sm-8 <?php } ?>">
                        <ul id="tour_crt_proj_swtch" class="fl">
                            <?php if ($this->Format->isAllowed('Create Project', $roleAccess)) { ?>
                            <li>
                                <a class="cmn-btn" title="<?php echo __('Add Project'); ?>" href="javascript:void(0)" id="new_tour_onboarding_add_icon" onClick="newProject()">
                                    <i class="material-icons">&#xE145;</i> <?php echo __('Add Project'); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <li>
                                <a id="task_impExp" class="cmn-btn-outline" title="<?php echo __('Export'); ?>" onclick="openProjectListExportPopup('csv');">
                                    <span class="material-icons">&#xE2C4;</span> <?php echo __('Export'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="<?php if ($projtype !== 'active-grid') { ?> col-lg-2 col-sm-2  <?php } else { ?> col-lg-2 col-sm-2 <?php } ?>">
                        <div id="proj_filtered_items" class="filter_tag_items"></div>
                    </div>
                    <div class="<?php if ($projtype !== 'active-grid') { ?> col-lg-2 col-sm-2  <?php } else { ?> col-lg-2 col-sm-2 <?php } ?>">
                        <div id="tour_proj_view" class="fr pfl-icon-dv gidview-proj-menu">
                            <div class="btn-group-section">
                                <span id="task_filter" class="dropdown task_section case-filter-menu">
                                    <a class="dropdown-toggle dropdown_menu_all_filters_togl"
                                        href="javascript:void(0);showHidetaskFilter();" rel="tooltip"
                                        title="<?php echo __('Filter'); ?>"
                                        style="display:inline-flex;align-items:center;color:#727272;text-decoration:none;">
                                        <i class="glyphicon glyphicon-filter" style="font-size:14px;vertical-align:middle;"></i>
                                    </a>
                                    <div class="dropdown_menu_t dropdown_menu_all_filters_ul_bkp"
                                        id="dropdown_menu_all_filters_t">
                                        <?php echo $this->element('project_filter'); ?>
                                    </div>
                                </span>
                            </div>
                            <hr style="margin: 0px 5px;">
                            <div class="btn-group-section">
                                <span class="pro_card_view">
                                    <a href="javascript:void(0);" onclick="setDefaultProjectView('<?php echo $gr_cookie_value ?? ''; ?>');
                                        $('#projectLoader').show();"
                                        class="<?php if ($projtype == '' || $projtype == 'inactive') { ?>active<?php } ?>"
                                        rel="tooltip" title="<?php echo __('Card View'); ?>">
                                        <i class="material-icons">&#xE42A;</i>
                                    </a>
                                </span>
                                <span>
                                    <a href="javascript:void(0);" onclick="setDefaultProjectView('<?php echo $cookie_value; ?>');
                                      $('#projectLoader').show();" class="<?php if ($projtype == 'active-grid' || $projtype == 'inactive-grid') { ?>active<?php } ?>" rel="tooltip" title="<?php echo __('List View'); ?>">
                                        <i class="material-icons">&#xE5D2;</i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="cb"></div>
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'wiki' && PAGE_NAME == 'wikidetails') { ?>
    <div class="task-list-bar  project-grid-page">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-7">
                        <ul class="lft_tab_tasklist fl wikitab" id="mlsttab">
                            <li id="mlstab_act_wikis" class="invoice_lst_page_tabs active-list">
                                <a href="javascript:void(0)" class="all-list" onclick="switch_tab('allwikis')"><span
                                        class="wiki_icon prjwiki"></span> <?php echo __('Project Wikis'); ?></a>
                            </li>
                            <li id="mlstab_act_nonproj_wikis" class="invoice_lst_page_tabs">
                                <a href="javascript:void(0)" onclick="switch_tab('nonprojectwikis')"
                                    id="completed_tab"><span class="wiki_icon nonprjwiki"></span>
                                    <?php echo __('Non Project Wikis'); ?></a>
                            </li>
                            <?php if ($this->Format->isAllowed('View Wiki', $roleAccess)) {  //SES_TYPE == 1 || SES_TYPE == 2
                            ?>
                                <li id="mlstab_assign_wikis" class="invoice_lst_page_tabs">
                                    <a href="javascript:void(0)" onclick="switch_tab('assigntomewiki')"><span
                                            class="wiki_icon assgnwiki"></span> <?php echo __('Assigned to Me'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>


<?php } elseif (strtolower(CONTROLLER) == 'ldaps') { ?>
    <?php $sett_class = (strtolower($this->request->action) == 'index') ? 'invoice_lst_page_tabs active-list' : 'invoice_lst_page_tabs';
    $import_class = (strtolower($this->request->action) == 'ldapusers') ? 'invoice_lst_page_tabs active-list' : 'invoice_lst_page_tabs';
    ?>
    <div class="task-list-bar  project-grid-page">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-7">
                        <ul class="lft_tab_tasklist fl wikitab" id="mlsttab">
                            <li id="ldap_sett" class="<?php echo $sett_class; ?>">
                                <a href="<?php echo HTTP_ROOT; ?>ldap"
                                    class="all-list"><?php echo __('Ldap Settings'); ?></a>
                            </li>
                            <li id="ldap_import_export" class="<?php echo $import_class; ?>">
                                <a href="<?php echo HTTP_ROOT; ?>ldap/ldaps/ldapUsers"
                                    id="completed_tab"><?php echo __('Import/Export'); ?></a>
                            </li>
                        </ul>
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
<?php } elseif ((CONTROLLER == 'users' && (PAGE_NAME == 'manage'))) { ?>
    <?php echo $this->element('user_topbar'); ?>
<?php } elseif (CONTROLLER == "teams" && PAGE_NAME == "teamTaskReports") { ?>
    <div class="task-list-bar dbrd-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-9">
                    <h2><?php echo __('Team Task Reports'); ?></h2>
                </div>
                <div class="col-lg-3">
                </div>
            </div>
        </div>
    </div>
<?php } elseif ((in_array(CONTROLLER, ["reports"])) && in_array(PAGE_NAME, ["glideChart", "hoursReport", "chart", "weeklyusageReport", "resourceUtilization", "pendingTask", "workLoad", "profitableReport", "resourceUtilization"])) { ?>
    <div class="task-list-bar analytics-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-8 pr_0">
                        <ul class="lft_tab_tasklist fl">
                            <?php if (SES_TYPE < 3 || $this->Format->isAllowed('View Hour Spent Report', $roleAccess)) { ?>
                                <li
                                    class="all-list-glyph <?php if (CONTROLLER == "reports" && (PAGE_NAME == "hoursReport")) { ?>active-list<?php } ?>">
                                    <a href="<?php echo HTTP_ROOT . "hours-report/" ?>">
                                        <i class="material-icons hs-icon">&#xE192;</i>
                                        <?php echo __('Hours Spent'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (SES_TYPE < 3 || $this->Format->isAllowed('View Task Report', $roleAccess)) { ?>
                                <li <?php if (CONTROLLER == "reports" && (PAGE_NAME == "chart")) { ?>class="active-list" <?php } ?>>
                                    <a href="<?php echo HTTP_ROOT . "task-report/" ?>">
                                        <i class="material-icons">&#xE862;</i>
                                        <?php echo __('Task Reports'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="cb"></div>
                    </div>
                    <div class="col-lg-4 weeklytime_report analytic_form_fld<?php echo PAGE_NAME == 'weeklyusage_report' ? " text-right" : ''; ?>">
                        <?php if (PAGE_NAME == 'resourceUtilization') { ?>
                            <?php echo $this->element('resource_utilization_filters'); ?>
                        <?php } else if (PAGE_NAME == 'pendingTask') { ?>
                            <?php echo $this->element('pending_task_filters'); ?>
                        <?php } else if (PAGE_NAME == 'weeklyusageReport') { ?>
                        <?php } else if (PAGE_NAME == 'teamTaskReports') { ?>
                        <?php } else if (PAGE_NAME == 'profitableReport') { ?>
                        <?php } else { ?>
                                <div class="col-lg-3 col-sm-3 col-xs-3 padlft-non">
                                    <div class="form-group">
                                        <?php echo $this->Form->text('start_date', ['value' => $frm, 'class' => 'smal_txt datepicker form-control', 'id' => 'start_date', 'placeholder' => __('Start Date'), 'readonly' => 'true']); ?>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-3 col-xs-3 end-date">
                                    <div class="from_to"><?php echo __('to'); ?></div>
                                    <div class="form-group">
                                        <?php echo $this->Form->text('end_date', ['value' => $to, 'class' => 'smal_txt datepicker form-control', 'id' => 'end_date', 'placeholder' => __('End Date'), 'readonly' => "true"]); ?>
                                    </div>
                                </div>
                            <div class="col-lg-3 col-sm-3 col-xs-3">
                                <div id="apply_btn">
                                    <?php if (PAGE_NAME == 'glideChart') { ?>
                                        <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="submit_Profile"
                                                class="btn btn-sm btn_cmn_efect cmn_bg btn-info"
                                                onclick="return validatechart('bug');"><?php echo __('Apply'); ?></a></span>
                                    <?php } elseif (PAGE_NAME == 'chart') { ?>
                                        <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="submit_Profile"
                                                class="btn btn-sm btn_cmn_efect cmn_bg btn-info"
                                                onclick="return validatechart('task');"><?php echo __('Apply'); ?></a></span>
                                    <?php } elseif (PAGE_NAME == 'defectReport') { ?>
                                        <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="submit_Profile"
                                                class="btn btn-sm btn_cmn_efect cmn_bg btn-info"
                                                onclick="return validatechart('defect_report');"><?php echo __('Apply'); ?></a></span>
                                    <?php } elseif (PAGE_NAME == 'hoursReport') { ?>
                                        <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="submit_Profile"
                                                class="btn btn-sm btn_cmn_efect cmn_bg btn-info"
                                                onclick="return validatechart('hours');"><?php echo __('Apply'); ?></a></span>
                                    <?php } ?>
                                </div>
                                <div id="apply_loader" style="display:none;" class="fl">
                                    <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="<?php echo __('loading'); ?>..."
                                        title="<?php echo __('loading'); ?>..." />
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="cb"></div>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $("#start_date").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#start_date").datepicker('getFormattedDate');
                            $("#end_date").datepicker("setStartDate", dateText);
                        });
                        $("#end_date").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#end_date").datepicker('getFormattedDate');
                            $("#start_date").datepicker("setEndDate", dateText);
                        });
                        $("#utilizationstrtdt").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#utilizationstrtdt").datepicker('getFormattedDate');
                            $("#utilizationenddt").datepicker("setStartDate", dateText);
                        });
                        $("#utilizationenddt").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#utilizationenddt").datepicker('getFormattedDate');
                            $("#utilizationstrtdt").datepicker("setEndDate", dateText);
                        });
                        $("#pendingstrtdt").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#pendingstrtdt").datepicker('getFormattedDate');
                            $("#pendingenddt").datepicker("setStartDate", dateText);
                        });
                        $("#pendingenddt").datepicker({
                            format: 'M d, yyyy',
                            changeMonth: false,
                            changeYear: false,
                            startDate: 0,
                            hideIfNoPrevNext: true,
                            autoclose: true
                        }).on("changeDate", function() {
                            var dateText = $("#pendingenddt").datepicker('getFormattedDate');
                            $("#pendingstrtdt").datepicker("setEndDate", dateText);
                        });
                    });
                </script>
            </div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'templates' && (PAGE_NAME == 'tasks' || PAGE_NAME == 'projects')) { ?>
    <div class="task-list-bar templates-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="proj_stas_bar lft_tab_tasklist acct_set_tab">
                        <li <?php
                            if (CONTROLLER == 'templates' && PAGE_NAME == 'projects') {
                                echo 'class="active-list"';
                            }
                            ?>>
                            <a href="<?php echo HTTP_ROOT . 'templates/projects'; ?>" class="all-list" id="sett_my_profile">
                                <i class="material-icons">&#xE8F9;</i>
                                <?php echo __('Project'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'ganttchart' && PAGE_NAME == 'manage') { ?>
    <div class="task-list-bar templates-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-6 ganttchart-bar">
                        <h2><?php echo __('Gantt chart'); ?></h2>
                    </div>
                    <div class="col-lg-6 gantt-right-bar">
                        <div class="col-lg-6 padrht-non custom-task-fld gantt-month-fld labl-rt add_new_opt mtop10">
                            <select class="ganttmnth select form-control floating-label"
                                placeholder="<?php echo __('Select Month'); ?>" data-dynamic-opts=true
                                onchange="gantt_filter_mnth(this)">

                            </select>
                            <input type="hidden" id="gantt_mnth" value="<?php echo $_SESSION['ganttchart_month']; ?>" />
                        </div>
                        <div class="col-lg-6 padrht-non custom-task-fld gantt-year-fld labl-rt add_new_opt mtop10">
                            <select class="ganttyr select form-control floating-label"
                                placeholder="<?php echo __('Select Year'); ?>" data-dynamic-opts=true
                                onchange="gantt_filter_yr(this)">

                            </select>
                            <input type="hidden" id="gantt_yr" value="<?php echo $_SESSION['ganttchart_year']; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } elseif ((CONTROLLER == 'projecttypes' && in_array(PAGE_NAME, ['projectTypes',])) || (CONTROLLER == 'projectstatuses' && in_array(PAGE_NAME, ['projectStatus',])) || (CONTROLLER == 'projects' && in_array(PAGE_NAME, ['projectStatus'])) || (CONTROLLER == 'users' && PAGE_NAME == 'mycompany') || (CONTROLLER == 'taskactions' && PAGE_NAME == 'duedateChangeReason')) { ?>
<?php } elseif (CONTROLLER == 'teams' && PAGE_NAME == 'index') { ?>
<?php } elseif ((CONTROLLER == 'programs' && PAGE_NAME == 'listProgram') || (CONTROLLER == 'projecttemplates' && PAGE_NAME == 'index')) { ?>
    <?php echo $this->element('company_settings'); ?>
<?php } elseif (( in_array(CONTROLLER,['customfields', 'projects', 'costs']) && in_array(PAGE_NAME, ['groupupdatealerts', 'importexport', 'confirmImport', 'importtimelog', 'importcomment', 'taskType', 'labels', 'customField', 'csvDataimport', 'csvTldataimport', 'csvCommentimport', 'confirmTlimport', 'confirmImport', 'taskSettings', 'settings', 'projectCustomField', 'workflowListing', 'workFlowSettings', 'importJira'])) || (CONTROLLER == 'invoices' && in_array(PAGE_NAME, ['settings', 'importCustomers', 'csvDataimport', 'confirmImport', 'workflowListing'])) || (CONTROLLER == 'users' && in_array(PAGE_NAME, ['showCustomerInUserTab']))) { ?>
<?php } elseif (CONTROLLER == 'users' && in_array(PAGE_NAME, ['profile', 'changepassword', 'emailNotifications', 'emailReports', 'defaultView']) || (CONTROLLER == "usersidebar" && PAGE_NAME == "index")) { ?>
<?php } elseif (in_array (CONTROLLER, ['projectreports', 'reports'])) { ?>
    <div class="task-list-bar dbrd-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?php
                        if ((in_array(CONTROLLER, ['ProjectReports', 'project_reports', 'projectreports'])) && in_array(PAGE_NAME, ['dashboard', 'PlannedVsActualTaskView', 'utilization'])) {
                            echo __('All Reports');
                        } elseif (CONTROLLER == 'reports' && PAGE_NAME == 'taskCycleReport') {
                            echo __('Task Cycle Report');
                        }
                        ?></h2>
                </div>
            </div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'pages' && PAGE_NAME == 'releaseActivity') { ?>
    <div class="task-list-bar dbrd-bar">
        <div class="wrap_top_tlbar">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?php echo __('Product Releases'); ?></h2>
                </div>
            </div>
        </div>
    </div>
<?php } elseif (CONTROLLER == 'epics' && in_array(PAGE_NAME, ['importEpics', 'previewEpics', 'confirmEpics',])) { ?>
<?php } elseif (CONTROLLER == 'taskimports' && in_array(PAGE_NAME, ['uploadImport', 'mapImport', 'previewImport', 'confirmImport',])) { ?>
<?php } elseif (CONTROLLER == 'taskviews') { ?>
    <?php // No PHP strip — the Task Views app renders one single bar in Vue. ?>
<?php } elseif ((CONTROLLER == 'easycases' && PAGE_NAME=='dashboard') || in_array(CONTROLLER, ['yourworks', 'archives'])) { ?>
    <?php echo $this->element('easycases_topbar'); ?>
<?php } elseif (CONTROLLER == 'tasktree' && PAGE_NAME == 'index') { ?>
    <?php echo $this->element('tasktree/tasktree_topbar'); ?>
<?php } ?>
<script type="text/javascript">
    $(function() {
        checkProjFltSelect();
        $('#proj_prog_cnt').tipsy({
            gravity: 'w',
            fade: true
        });

    });
</script>