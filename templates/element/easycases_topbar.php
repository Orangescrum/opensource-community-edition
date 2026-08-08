<?php $ftype ??= ''; ?>
<div id="task_list_bar"
    class="task-list-bar files-bar archive-bar activities-bar kanban-bar calendar-bar tg_calendar-bar task_section media-bar"
    style="display:none;">
    <div class="wrap_top_tlbar">
        <div class="row">
            <div class="col-lg-12">
                <div class="col-lg-7 col-sm-6 m-top-bar">
                    <ul class="proj_stas_bar archive_stas_bar">
                        <li id="task_li" class="all-list-glyph active-list">
                            <a onclick="task()" href="javascript:void(0);" class="all-list">
                                <i class="material-icons">&#xE862;</i>
                                <?php echo __('Tasks'); ?>(<span class="archive_active_task"></span>)
                            </a>
                        </li>
                        <li id="file_li">
                            <a href="javascript:void(0);" onclick="file()">
                                <i class="material-icons">&#xE226;</i> <?php echo __('Files'); ?> (<span
                                    class="archive_active_file"></span>)
                            </a>
                        </li>
                    </ul>
                    <div class="activity-bar">
                        <ul class="proj_stas_bar lft_tab_tasklist">
                            <li class="activitylist_breadcrumb actvty_li">
                                <a id="actvt_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#/activities'; ?>"
                                    onclick="return checkHashLoad('activities');">
                                    <i class="material-icons">&#xE922;</i><?php echo __('Activities'); ?>
                                </a>

                            </li>
                        </ul>
                    </div>
                    <div class="calendar-bar-text">
                        <h2><?php echo __('Calendar'); ?></h2>
                    </div>
                    <div class="files-bar files-nav-text">
                        <h2><?php echo __('Files'); ?></h2>
                    </div>
                    <div id="topactions">
                        <?php echo $this->element('task_tabs'); ?>
                    </div>
                    <ul class="proj_stas_bar kanban_stas_bar">
                        <li id="kanban_sts_bar">
                            <a href="javascript:void(0)" onclick="kanban_sattus();">
                                <i class="material-icons">&#xE862;</i> <?php echo __('Task Status'); ?>
                            </a>
                        </li>
                        <li id="mlstab_act_kanban_sta" class="all-list-glyph active-list">
                            <a href="dashboard#/milestonelist" class="all-list">
                                <i class="material-icons">&#xE065;</i>
                                <?php echo __('Task Group'); ?>(<span class="kanban_active_task"></span>)
                            </a>
                        </li>
                        <div style="display:none;">
                            <li id="filterSearch_id_kanban" class="filter-dropdown-kanban">
                                <div class="btn-group margin-left-2">
                                    <button aria-expanded="false" aria-haspopup="true" data-toggle="dropdown"
                                        class="top_project_btn btn btn_cmn_efect cmn_bg btn-info cmn_size dropdown-toggle project-drop-custom-pad prtl"
                                        type="button" onclick="viewFilters('kanban');">
                                        <span class="ellipsis-view max150"><a href="javascript:void(0);"
                                                class="top_project_name1" rel=""><?php echo __('Loading'); ?></a></span>
                                        <i class="nav-dot material-icons">&#xE5D3;</i>
                                    </button>
                                    <div id="filpopup_kanban" class="dropdown-menu lft popup" style="display: none;">
                                        <div class="scroll-project" id="ajaxViewFiltersKanban">
                                            <?php
                                            if (!empty($tablists)) {
                                                foreach ($tablists as $tabkey => $tabvalue) {
                                                    if (!($tabkey & ACT_TAB_ID)) {
                                                        $tab_spn_id = '';
                                                        if ($tabvalue["fkeyword"] == "cases") {
                                                            $tab_spn_id = "tskTabAllCnt";
                                                        } elseif ($tabvalue["fkeyword"] == "assigntome") {
                                                            $tab_spn_id = "tskTabMyCnt";
                                                        } elseif ($tabvalue["fkeyword"] == "delegateto") {
                                                            $tab_spn_id = "tskTabDegCnt";
                                                        } elseif ($tabvalue["fkeyword"] == "highpriority") {
                                                            $tab_spn_id = "tskTabHPriCnt";
                                                        } elseif ($tabvalue["fkeyword"] == "overdue") {
                                                            $tab_spn_id = "tskTabOverdueCnt";
                                                        } elseif ($tabvalue["fkeyword"] == "openedtasks") {
                                                            $tab_spn_id = "tskTabOpenedcnt";
                                                        } elseif ($tabvalue["fkeyword"] == "closedtasks") {
                                                            $tab_spn_id = "tskTabClosedCnt";
                                                        }
                                                        ?>
                                                        <a href="javascript:void(0);"
                                                            onclick="setSavedFilter(this, '<?php echo $tabvalue["fkeyword"]; ?>', 'dashboard', 'cases', '');"
                                                            id="kanban_otheropt<?php echo $tabkey; ?>" data-val="0"
                                                            data-tabkey="<?php echo $tabvalue["fkeyword"]; ?>" rel=""
                                                            class="gray-background"><?php echo $tabvalue["ftext"]; ?> <span
                                                                id="kanban_<?php echo $tab_spn_id; ?>" class="spncls"></span></a>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>

                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>
                        <script type="text/template" id="filterSearch_id_kanban_tmpl">
                                <?php echo $this->element('search_filter'); ?>
                            </script>

                    </ul>
                    <div class="overview-bar pr">
                        <div class="overview-bnr" style="display:none;"></div>
                        <div class="d_tbl project_overview_bar row" id="tour_overview_statistics">
                            <div class="d_tbl_cel col-sm-3 pad_zero">
                                <div id="pprog_holder">
                                    <div id="proj_loading_bar" style="height:150px; width:150px;"></div>
                                    <span id="proj_prog_cnt"
                                        title="<?php echo __('Total closed tasks compared to total tasks in a given project'); ?>"
                                        style="cursor:pointer;">0</span>
                                    <div class="dyn_overall_abstxt"><?php echo __('Overall Progress'); ?></div>
                                </div>
                            </div>
                            <div class="d_tbl_cel p_name col-sm-9">
                                <div class="proj_name_over_task">
                                    <div class="proj_name_overtask">
                                        <h2 id="ov_prj_name" title="<?php echo $proj['name'] ?? ''; ?>">
                                            <?php echo $proj['name'] ?? ''; ?>
                                        </h2>
                                    </div>
                                    <div class="edit_status_prior">
                                        <span id="project_stst_span" class=""
                                            title="<?php echo __('Project Status'); ?>" rel="tooltip"></span>
                                        <span class="prio_low prio_lmh prio_gen_prj prio-drop-icon proj_ov_priority"
                                            rel="tooltip" title="<?php echo __('Low Priority'); ?>"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="cb"></div>
                        </div>
                    </div>
                    <div class="tlog_top_cnt timlog_top_bar">
                        <ul id="select_view_timelog" class="proj_stas_bar">
                            <li title="<?php echo __('List View'); ?>"><a id="lview_btn_timelog"
                                    href="<?php echo HTTP_ROOT . 'dashboard#/timelog'; ?>"><i
                                        class="material-icons">&#xE8EF;</i>&nbsp;<?php echo __('List View'); ?></a></li>
                        </ul>
                        <div class="cb"></div>
                    </div>
                    <div class="filter_top_cnt filter_top_bar">
                        <h2><?php echo __('Manage Filters'); ?></h2>
                        <div class="cb"></div>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-5 m-top-bar">
                    <div class="os_projct_overview_date" style="display: none;">
                        <div class="d_tbl_cel date col-sm-5">
                            <span><?php echo __('Start Date'); ?></span>
                            <span
                                id="ov_prj_stdt"><?php echo isset($proj['Project']['start_date']) ? $proj['Project']['start_date'] : 'N/A'; ?></span>
                            <div class="v_line_separator"></div>
                        </div>
                        <div class="d_tbl_cel date col-sm-5">
                            <span><?php echo __('Due Date'); ?></span>
                            <span
                                id="ov_prj_eddt"><?php echo isset($proj['Project']['end_date']) ? $proj['Project']['end_date'] : 'N/A'; ?></span>
                        </div>
                    </div>

                    <div class="tag-btn fl timelog_filter_msg" style="display:none;padding-top:20px;">
                    </div>
                    <div class="fl milst_addition" style="display:none">
                        <span class="anchor print_w_usage actv-mls" title="<?php echo __('Active Task Groups'); ?>"
                            rel="tooltip" id="actv_btn_tsgrp">
                            <a href="javascript:void(0);" onclick="showMilestoneList('', 1)"><i
                                    class="material-icons">&#xE430;</i></a>
                        </span>
                        <span class="anchor print_w_usage" title="<?php echo __('Completed Task Groups'); ?>"
                            rel="tooltip" id="cmpl_btn_tsgrp">
                            <a id="completed_tab_bk" href="javascript:void(0);" onclick="showMilestoneList('', 0)"><i
                                    class="material-icons">&#xE876;</i></a>
                        </span>
                    </div>
                    <div class="tag-btn fl kanban_filter_det" style="display:none">
                        <div id="" class="ver_midl">
                            <div class="tag-block" id="kanban_filtered_items"></div>
                        </div>
                        <div class="filter_btn_section ver_midl" id="kanban_savereset_filter">
                            <span onClick="resetMilestoneSearch();" id="kanban_reset_btn"
                                title="<?php echo __('Reset'); ?>"><i class="material-icons">&#xE8BA;</i></span>
                        </div>
                    </div>
                    <!--- display search name in kanban task !-->
                    <div class="tag-btn fl kanban_tsk_filter_sec" style="display:none">
                        <div id="" class="ver_midl">
                            <div class="tag-block" id="kanban_tsk_filter_items"></div>
                        </div>
                        <div class="filter_btn_section ver_midl" id="kanban_srch_filter">

                        </div>
                    </div>
                    <!--- end of display search name in kanban task !-->
                    <div class="tag-btn fl archive_filter_det">
                        <div class="ver_midl">
                            <div id="archive_filtered_items" class="tag-block"></div>
                        </div>
                        <div class="filter_btn_section ver_midl" id="archive_savereset_filter">
                            <span onClick="resetAllFilters_archive('all');" id="archive_reset_btn"
                                title="<?php echo __('Reset Filters'); ?>"><i class="material-icons">&#xE8BA;</i></span>
                        </div>
                    </div>
                    <div class="tag-btn fl filter_det">
                        <div class="ver_midl">
                            <div class="tag-block" id="filtered_items"></div>
                        </div>
                        <div class="filter_btn_section ver_midl" id="savereset_filter">
                            <span onClick="resetAllFilters('all');" id="reset_btn"
                                title="<?php echo __('Reset Filters'); ?>" rel="tooltip"><i
                                    class="material-icons">&#xE8BA;</i></span>
                        </div>
                        <div style="display: table-cell;width: 30px;" class="filter_btn_section ver_midl">
                            <button class="btn btn_cmn_efect cmn_bg btn-info" id="saveFilter" onclick="saveFilter();"
                                style="display: inline-block;" title="<?php echo __('Update Filter'); ?>"
                                rel="tooltip"><i class="material-icons">?</i></button>
                        </div>
                    </div>
                    <div class="new_calendar_icon_on_top">
                        <span id="calendar_view_types" style="display:block;">
                        </span>
                    </div>
                    <div class="fr pfl-icon-dv">
                        <span id="task_filter" class="dropdown task_section case-filter-menu">
                            <a class="dropdown-toggle dropdown_menu_all_filters_togl"
                                href="javascript:void(0);showHidetaskFilter();" rel="tooltip"
                                title="<?php echo __('Filter'); ?>">
                                <i class="glyphicon glyphicon-filter"></i>
                            </a>
                            <div class="dropdown_menu_t dropdown_menu_all_filters_ul_bkp"
                                id="dropdown_menu_all_filters_t">
                                <?php echo $this->element('task_filters'); ?>
                            </div>

                        </span>

                        <?php
                        if (0 && CONTROLLER != 'archives' && PAGE_NAME != 'listall') {
                            // Hide PDF export until dompdf converted
                            ?>
                            <span id="overview_exp" class="dropdown timesheet_expPrnt">
                                <a id="overview_exp_lnk" href="javascript:void(0)" title="<?php echo __('Export as PDF'); ?>"
                                    onclick="overviewPDF();" rel="tooltip"><i class="material-icons">picture_as_pdf</i></a>
                                <img alt="<?php echo __('loading'); ?>..." title="<?php echo __('loading'); ?>..."
                                    id="ov_pdf_loader" src="<?php echo HTTP_IMAGES; ?>images/del.gif"
                                    style="display:none;padding-right: 10px;" />
                            </span>
                        <?php } ?>

                        <?php if ($this->Format->isAllowed('Download Task', $roleAccess)) { ?>
                            <span id="task_impExp" class="dropdown task_expPrnt case-filter-menu">
                                <a class="dropdown-toggle dropdown_menu_exp_print_togl pdf_export" data-toggle="dropdown"
                                    href="javascript:void(0);" data-target="#">
                                    <span class="export_file_icon"></span>
                                    <ul>
                                        <li onclick="openTaskListExportPopup();"><?php echo __('Export as CSV'); ?></li>
                                    </ul>
                                </a>
                            </span>
                        <?php } ?>
                        <!-- archive filter ssection-->
                        <span id="archive_filter" class="dropdown task_section archive-filter-menu hide">
                            <a class="dropdown-toggle dropdown_menu_all_filters_togl" data-toggle="dropdown"
                                href="javascript:void(0);" data-target="#"
                                onclick="openfilter_popup('0', 'dropdown_menu_archive_filters');" rel="tooltip"
                                title="<?php echo __('Filter'); ?>">
                                <i class="glyphicon glyphicon-filter"></i>
                            </a>

                            <ul id="dropdown_menu_archive_filters" class="dropdown-menu">
                                <li class="drop_menu_mc" id="casestatus_li" style="display:none;">
                                    <a href="javascript:jsVoid();" data-toggle="dropdown"
                                        onclick="allfiltervalue('casestatus', event, 'archive')"><i
                                            class="material-icons">&#xE90A;</i> <?php echo __('Status'); ?></a>
                                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm scrollable"
                                        id="dropdown_menu_casestatus"></ul>
                                </li>

                                <li class="drop_menu_mc">
                                    <a href="javascript:jsVoid();" data-toggle="dropdown"
                                        onclick="allfiltervalue('casedate', event, 'archive')"><i
                                            class="material-icons">&#xE149;</i> <?php echo __('Archived Date'); ?></a>
                                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm arch-dat"
                                        id="dropdown_menu_casedate" style="">
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="today"
                                                        id="archive_today"
                                                        onclick="checkboxarchivedate('today', 'check');" />
                                                    <?php echo __('Today'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="yesterday"
                                                        id="archive_yesterday"
                                                        onclick="checkboxarchivedate('yesterday', 'check');" />
                                                    <?php echo __('Yesterday'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="thisweek"
                                                        id="archive_thisweek"
                                                        onclick="checkboxarchivedate('thisweek', 'check');" />
                                                    <?php echo __('This Week'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="thismonth"
                                                        id="archive_thismonth"
                                                        onclick="checkboxarchivedate('thismonth', 'check');" />
                                                    <?php echo __('This Month'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="thisquarter"
                                                        id="archive_thisquarter"
                                                        onclick="checkboxarchivedate('thisquarter', 'check');" />
                                                    <?php echo __('This Quarter'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="thisyear"
                                                        id="archive_thisyear"
                                                        onclick="checkboxarchivedate('thisyear', 'check');" />
                                                    <?php echo __('This Year'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="lastweek"
                                                        id="archive_lastweek"
                                                        onclick="checkboxarchivedate('lastweek', 'check');" />
                                                    <?php echo __('Last Week'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="lastmonth"
                                                        id="archive_lastmonth"
                                                        onclick="checkboxarchivedate('lastmonth', 'check');" />
                                                    <?php echo __('Last Month'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="lastquarter"
                                                        id="archive_lastquarter"
                                                        onclick="checkboxarchivedate('lastquarter', 'check');" />
                                                    <?php echo __('Last Quarter'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="lastyear"
                                                        id="archive_lastyear"
                                                        onclick="checkboxarchivedate('lastyear', 'check');" />
                                                    <?php echo __('Last Year'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_date_cls" type="radio" data-id="last365days"
                                                        id="archive_last365days"
                                                        onclick="checkboxarchivedate('last365days', 'check');" />
                                                    <?php echo __('Last 365 days'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio dcus_dt">
                                            <a class="anchor" class="cstm-dt-option" onclick="customarchivedate();"
                                                style="padding:3px 25px">
                                                <span
                                                    style="position:relative;top:2px;cursor:pointer;"><?php echo __('Custom Date'); ?></span>
                                            </a>
                                        </li>
                                        <li class="custome_archive dft_dt" style="display:none;">
                                            <div class="form-group label-floating">
                                                <label class="control-label"
                                                    for="arcduestrtdt"><?php echo __('From Date'); ?></label>
                                                <input type="text" class="smal_txt form-control" placeholder="" readonly
                                                    id="arcduestrtdt" value="<?php echo $frm ?? ''; ?>" />
                                            </div>
                                        </li>
                                        <li class="custome_archive dft_dt" style="display:none;">
                                            <div class="form-group label-floating">
                                                <label class="control-label"
                                                    for="arcdueenddt"><?php echo __('To Date'); ?></label>
                                                <input type="text" class="smal_txt form-control" placeholder="" readonly
                                                    id="arcdueenddt" value="<?php echo $to ?? ''; ?>" />
                                            </div>
                                        </li>
                                        <li class="custome_archive" style="display:none;text-align:center;padding:5px">
                                            <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" type="button"
                                                onclick="arcivecustomdate();"
                                                id="btn_archive_search"><?php echo __('Search'); ?></button>
                                        </li>
                                    </ul>
                                </li>
                                <li class="drop_menu_mc" style="display:none;" id="caseduedate_li">
                                    <a href="javascript:jsVoid();" data-toggle="dropdown"
                                        onclick="allfiltervalue('archiveduedate', event, 'archive')"><i
                                            class="material-icons">&#xE8DF;</i> <?php echo __('Due Date'); ?></a>
                                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm arch-due-dt"
                                        id="dropdown_menu_archiveduedate">
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="today"
                                                        id="archivedue_today"
                                                        onclick="checkboxarchivedduedate('today', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Today'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="yesterday"
                                                        id="archivedue_yesterday"
                                                        onclick="checkboxarchivedduedate('yesterday', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Yesterday'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="thisweek"
                                                        id="archivedue_thisweek"
                                                        onclick="checkboxarchivedduedate('thisweek', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('This Week'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="thismonth"
                                                        id="archivedue_thismonth"
                                                        onclick="checkboxarchivedduedate('thismonth', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('This Month'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="thisquarter"
                                                        id="archivedue_thisquarter"
                                                        onclick="checkboxarchivedduedate('thisquarter', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('This Quarter'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="thisyear"
                                                        id="archivedue_thisyear"
                                                        onclick="checkboxarchivedduedate('thisyear', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('This Year'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="lastweek"
                                                        id="archivedue_lastweek"
                                                        onclick="checkboxarchivedduedate('lastweek', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Last Week'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="lastmonth"
                                                        id="archivedue_lastmonth"
                                                        onclick="checkboxarchivedduedate('lastmonth', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Last Month'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="lastquarter"
                                                        id="archivedue_lastquarter"
                                                        onclick="checkboxarchivedduedate('lastquarter', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Last Quarter'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="lastyear"
                                                        id="archivedue_lastyear"
                                                        onclick="checkboxarchivedduedate('lastyear', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Last Year'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio">
                                            <div class="radio radio-primary">
                                                <label>
                                                    <input class="cst_duedate_cls" type="radio" data-id="last365days"
                                                        id="archivedue_last365days"
                                                        onclick="checkboxarchivedduedate('last365days', 'check', '<?php echo $ftype; ?>');" />
                                                    <?php echo __('Last 365 days'); ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li class="li_check_radio dcus_dt">
                                            <a class="anchor" class="cstm-dt-option" onclick="customarchiveduedate();"
                                                style="padding:3px 25px">
                                                <span
                                                    style="position:relative;top:2px;cursor:pointer;"><?php echo __('Custom Date'); ?></span>
                                            </a>
                                        </li>
                                        <li class="custome_archive dft_dt" style="display:none;">
                                            <div class="form-group label-floating">
                                                <label class="control-label"
                                                    for="arcstrtdt"><?php echo __('From Date'); ?></label>
                                                <input type="text" class="smal_txt form-control" placeholder="" readonly
                                                    id="arcstrtdt" value="<?php echo $frm ?? ''; ?>" />
                                            </div>
                                        </li>
                                        <li class="custome_archive dft_dt" style="display:none;">
                                            <div class="form-group label-floating">
                                                <label class="control-label"
                                                    for="arcenddt"><?php echo __('To Date'); ?></label>
                                                <input type="text" class="smal_txt form-control" placeholder="" readonly
                                                    id="arcenddt" value="<?php echo $to ?? ''; ?>" />
                                            </div>
                                        </li>
                                        <li class="custome_archive drop-srch-li"
                                            style="display: none;text-align:center;padding:5px">
                                            <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" type="button"
                                                onclick="arcivecustomduedate();"
                                                id="btn_archive_search"><?php echo __('Search'); ?></button>
                                        </li>
                                    </ul>
                                </li>
                                <li class="drop_menu_mc">
                                    <a href="javascript:jsVoid();" data-toggle="dropdown"
                                        onclick="allfiltervalue('project', event, 'archive')"><i
                                            class="material-icons">&#xE8F9;</i> <?php echo __('Project'); ?></a>
                                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm scrollable"
                                        id="dropdown_menu_project">
                                    </ul>
                                </li>
                                <li class="drop_menu_mc">
                                    <a href="javascript:jsVoid();" data-toggle="dropdown"
                                        onclick="allfiltervalue('archivedby', event, 'archive')"><i
                                            class="material-icons">&#xE149;</i> <?php echo __('Archived By'); ?></a>
                                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm scrollable"
                                        id="dropdown_menu_archivedby">
                                    </ul>
                                </li>
                            </ul>
                        </span>
                        <!-- archive filter ssection end-->

                        <?php echo $this->element('top_menu_options_icon') ?>
                    </div>

                    <div class="cb"></div>
                </div>
                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>