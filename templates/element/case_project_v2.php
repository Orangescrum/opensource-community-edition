<%
    caseSrch = (typeof caseSrch != 'undefined') ? caseSrch : '' ;
    GrpBy = (typeof GrpBy != 'undefined')?GrpBy:'';
    var check_ids_array = typeof getCookie('PREOPENED_TASK_GROUP_IDS') != 'undefined' ?JSON.parse(getCookie('PREOPENED_TASK_GROUP_IDS')) :[];
    var rel_arr = new Array();
    var task_parent_ids = JSON.stringify(task_parent_ids);
    var hashtag = parseUrlHash(getHash());
    var page_hash = hashtag ? hashtag[0] : '';
%>
<% if(GrpBy != 'milestone') { %>
<div class="task_listing task_list_scroll_sticky">
    <?php /* Task List Action Bar */ ?>
    <?php if (PAGE_NAME == 'dashboard') { ?>
        <div id="widgethideshow" class="fl task-list-progress-bar fix-status-widget">
            <span id="task_count_of" style="float:left;display:block;top:5px;position:relative;"></span>
            <span class="pr fl inner_search_span" onclick="slider_inner_search(<%= '\'open\'' %>);" style="display:none;">
                <i class="material-icons clear_close_icon" title="<?php echo __('Clear search'); ?>" id="clear_close_icon" onclick="clearSearch(<%= '\'inner\'' %>);">close</i>
                <i class="inner_search_icon material-icons">&#xE8B6;</i>
                <input type="text" name="search_inner" id="inner-search" placeholder="<?php echo __('Search'); ?>" class="inner-search" value="<%=(caseSrch ? caseSrch : '' )%>" />
                <img src="<?php echo HTTP_ROOT; ?>img/images/del.gif" alt="loading" title="<?php echo __('loading'); ?>" class="search_load" id="srch_inner_load1">
                <div id="ajax_inner_search" class="ajx-srch-inner-dv1"></div>
            </span>
            <span style="cursor:pointer;">
                <a class="group_by_anchor os-new-views-link" href="<?php echo HTTP_ROOT; ?>task-views"
                   title="<?php echo __('Switch to the new task views'); ?>" rel="tooltip"
                   style="min-width:30px;display:inline-flex;align-items:center;gap:4px;padding:0 8px;text-decoration:none;">
                    <i class="material-icons" style="font-size:18px;">&#xE8EF;</i>
                    <span style="font-size:12px;"><?php echo __('New view'); ?></span>
                </a>
            </span>
            <div class="view_list_refresh" id="task_view_types">
                <span class="reload_icon">
                    <a class="" href="javascript:void(0);" onclick="reloadTasks();">
                        <span title="<?php echo __('Reload'); ?>" rel="tooltip"><i class="material-icons">&#xE5D5;</i></span>
                    </a>
                </span>
                <div class="cb"></div>
            </div>
            <% if(0 && page_hash =='epics') { %>
            <span style="cursor:pointer;">
                <div class="group_by_anchor" style="min-width:30px" onclick="getEpicTreeView();" title="<?php echo __("Epic Tree View")  ?>"><i class="material-icons">&#xE97A;</i></div>
            </span>
            <% } %>
            <% if(page_hash !=='epics' && page_hash !=='features') { %>
            <span id="ajaxCaseStatus" style="float:right;margin-top:7px; margin-right:-10px;"></span>
            <% } %>
            <span class="pfl-icon-dv show_hide_column_filter">
                <span id="showhide_drpdwn" class="dropdown">
                    <a href="javascript:jsVoid();" title="<?php echo __('Show/Hide Columns'); ?>" onclick="showColumnPreferences('<%= field_name_arr %>');" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="material-icons">visibility_off</i> <?php echo __("Show/Hide"); ?><div class="ripple-container"></div></a>
                    <ul class="dropdown-menu drop_menu_mc" id="dropdown_menu_taskcolumns">
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('All',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="All" id="column_all" style="cursor:pointer" onchange="checkboxColumn(this);"> <?php echo __('Show/Hide All'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Assigned to',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="Assigned to" id="column_assigned" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Assigned To'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Team',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="Team" id="column_team" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Team'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Priority',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="Priority" id="column_priority" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Priority'); ?>
                                </label>
                            </div>
                        </li>
                        <% if(page_hash !=='epics' && page_hash !=='features') { %>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Estimated Hours',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="Estimated Hours" id="column_estimatedhours" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Est. Hours'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Spent Hours',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="Spent Hours" id="column_spenthours" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Spent Hours'); ?>
                                </label>
                            </div>
                        </li>
                        <% } %>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Updated',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="Updated" id="column_updated" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Updated'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Status',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols " value="Status" id="column_status" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Status'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Due Date',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="Due Date" id="column_duedate" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Due Date'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Original Due Date',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="Original Due Date" id="column_duedate" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Original Due Date'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Progress',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="Progress" id="column_progress" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('% Completion'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio" style="border-top: 2px solid #ddd;">
                            <div class="checkbox">
                                <label>
                                <input type="checkbox" <% if(inArray('basicdetail',field_name_arr)){ %> checked="checked" <% } %>class="selectedcols show_hide_selectedcols " value="basicdetail" id="column_basicdetail" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Basic Detail');?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div style="text-align:center;">
                                <label>
                                    <input type="button" class="btn btn_cmn_efect cmn_bg btn-info show_btn" value="<?php echo __('Save'); ?>" onclick="getAllowedColumns();">
                                </label>
                            </div>
                        </li>
                    </ul>
                    <!-- Custom code Ends -->
                </span>
            </span>
            
            <% if(page_hash !=='epics' && page_hash !=='features') { %>
            <span class="filter_tag_items" id="task_groupby_items_list"></span>
            <span class="groupby_filter mtop5">
                <div class="dropdown dynamic_task_title">
                    <a href="javascript:void(0)" type="button" data-toggle="dropdown" onclick="openTaskGroupByDrpdwn();" class="group_by_anchor">
                        <i class="material-icons" style="vertical-align: middle;margin-right: 5px;">group_work</i>
                        Group By
                    </a>
                    <ul class="dropdown-menu" id="dropdown_task_groupby_filters">
                        <li>
                            <a href="javascript:jsVoid();" data-type="Date" data-toggle="dropdown" onclick="ajaxTaskGroupBy(this)" title="<?php echo __("Updated Date"); ?>"><input type="checkbox" class="selectedcolss D_ate drop-checkbox" value="Updated Date" id="column_priority" style="cursor:pointer"> <?php echo __("Updated Date"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:jsVoid();" data-type="Assign to" data-toggle="dropdown" onclick="ajaxTaskGroupBy(this)" title="<?php echo __("Assign To"); ?>"><input type="checkbox" class="selectedcolss A_ssign_To drop-checkbox" value="Assign To" id="column_priority" style="cursor:pointer"> <?php echo __("Assign To"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:jsVoid();" data-type="Status" data-toggle="dropdown" onclick="ajaxTaskGroupBy(this)" title="<?php echo __("Status"); ?>"> <input type="checkbox" class="selectedcolss S_tatus drop-checkbox" value="Status" id="column_priority" style="cursor:pointer"> <?php echo __("Status"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:jsVoid();" data-type="Priority" data-toggle="dropdown" onclick="ajaxTaskGroupBy(this)" title="<?php echo __("Priority"); ?>"> <input type="checkbox" class="selectedcolss P_riority drop-checkbox" value="Priority" id="column_priority" style="cursor:pointer"> <?php echo __("Priority"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:jsVoid();" data-type="None" data-toggle="dropdown" onclick="ajaxTaskGroupBy(this)" title="<?php echo __("None"); ?>"><input type="checkbox" class="selectedcolss N_one drop-checkbox" value="" id="column_none" style="cursor:pointer"> <?php echo __("None"); ?></a>
                        </li>
                    </ul>
                </div>
            </span>
            <?php if ($this->Format->isCriticalEnabled()) { ?>
            <span style="cursor:pointer;">
                <div class="group_by_anchor" style="min-width:30px" onclick="getCriticalPath();" title="<?php echo __("Critical Path")  ?>"><i class="material-icons">&#xE0D7;</i></div>
            </span>
            <?php } ?>
            <span style="cursor:pointer;">
                <div class="overdue_task_span" onclick="showOverDueTask();"><?php echo __("Overdue Tasks")  ?> : <%= over_due_task_count %></div>
            </span>
            <% } %>
            <div class="cb"></div>
        </div>
    <?php } /* END (PAGE_NAME == 'dashboard') */ ?>
    <?php /* Task List Action Bar */ ?>
    

    <div class="task-m-overflow cstm_responsive_tbl task_scrollable_list tasklist_view_table">
        <table class="table table-striped table-hover table_sticky_top_row">
            <?php /* Task List Table Header */ ?>
            <thead class="sticky_table_heading">
                <tr>
                    <th class="porl checkbox_th wth_1 left-pr">
                        <div class="pr left-pr">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="" class="chkAllTsk" id="chkAllTsk">
                                </label>
                            </div>
                            <div class="drop_th_ttl">
                                <span class="dropdown custom_th_drdown">
                                    <a class="dropdown-toggle mass_action_dpdwn" data-toggle="" href="javascript:void(0);">
                                        <i title="<?php echo __('Choose at least one task'); ?>" rel="tooltip" class="material-icons custom-dropdown">&#xE5C5;</i>
                                    </a>
                                    <ul class="dropdown-menu" id="dropdown_menu_chk">
                                        <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                                            <% if(projUniq == 'all'){%>
                                            <% }else{ %>
                                            <% if(typeof curProjId != "undefined" && typeof curProjId != "null" &&  typeof customStatusByProject !="undefined" && typeof customStatusByProject[curProjId] !='undefined' && customStatusByProject[curProjId] != null){
                                                $.each(customStatusByProject[curProjId], function (key, data) {
                                            %>
                                            <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                            <% if(data.status_master_id == 3){ %>
                                            <% if(isAllowed("Status change except Close",projectUniqid)){ %>
                                            <li onclick="multipleCustomAction(<%= '\'' + data.id + '\'' %>, <%= '\'' + escape(data.name) + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>);" id="sts_custm_<%= data.id %>">
                                                <a href="javascript:void(0);">
                                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                                    <%= data.name %></a>
                                            </li>
                                            <% } %>
                                            <% }else{ %>
                                            <li onclick="multipleCustomAction(<%= '\'' + data.id + '\'' %>, <%= '\'' + escape(data.name) + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>);" id="sts_custm_<%= data.id %>">
                                                <a href="javascript:void(0);">
                                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                                    <%= data.name %></a>
                                            </li>
                                            <% } %>
                                            <% } %>
                                            <%  });  }else{ %>
                                                <?php /* Remove statuses from multi epic and feature select. */ ?>
                                                <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                            <li>
                                                <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseNew\'' %>)"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseStart\'' %>)"><i class="material-icons">&#xE039;</i><?php echo __('Start'); ?></a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseResolve\'' %>)"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                                            </li>
                                            <% if(isAllowed('Status change except Close',projectUniqid)){ %>
                                            <li>
                                                <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseId\'' %>)"><i class="material-icons">&#xE5CD;</i><?php echo __('Close'); ?></a>
                                            </li>
                                            <% }  %>
                                            <% } %>
                                            <% } %>
                                            <% } %>
                                        <?php } ?>
                                        <?php if (SES_TYPE == 1 || SES_TYPE == 2 || $this->Format->isAllowed('Archive All Task', $roleAccess)) { ?>

                                            <?php if ($this->Format->isAllowed('Change Assigned to', $roleAccess)) { ?>
                                                <% if(page_hash ==='epics') { %>
                                                    <li>
                                                    <a href="javascript:void(0);" onclick="ajaxassignAllTaskToUser(<%= '\'movetop\'' %>);"><i class="material-icons"></i><?php echo __('Assign epic(s) to user'); ?></a>
                                                </li>
                                                    <% } else if(page_hash ==='features') { %>
                                                    <li>
                                                    <a href="javascript:void(0);" onclick="ajaxassignAllTaskToUser(<%= '\'movetop\'' %>);"><i class="material-icons"></i><?php echo __('Assign feature(s) to user'); ?></a>
                                                </li>
                                                    <% } else { %>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="ajaxassignAllTaskToUser(<%= '\'movetop\'' %>);"><i class="material-icons"></i><?php echo __('Assign task(s) to user'); ?></a>
                                                </li>
                                                <% } %>												<?php } ?>
                                            <?php if ($this->Format->isAllowed('Move to Project', $roleAccess)) { ?>
                                                <% if(page_hash !=='features') { %>
                                                <li id="mvTaskToProj">
                                                    <a href="javascript:void(0);" onclick="mvtoProject(<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'movetop\'' %>)"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to project'); ?></a>
                                                </li>
                                                <% }  %>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                                <% if(page_hash !=='features') { %>
                                                <li id="cpTaskToProj">
                                                    <a href="javascript:void(0);" onclick="cptoProject(<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'movetop\'' %>)"><i class="material-icons">&#xE14D;</i><?php echo __('Copy to Project'); ?></a>
                                                </li>
                                                <% }  %>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                                <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                                <% }  %>
                                            <?php } ?>

                                            <?php if (SES_TYPE == 1 || SES_TYPE == 2 || $this->Format->isAllowed('Delete All Task', $roleAccess)) { ?>
                                                <li id="delAllTsks">
                                                    <a href="javascript:void(0);" onclick="DeleteAllCaseTaskList( <%= '\'all\'' %> )"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </span>
                            </div>
                        </div>
                    </th>
                    <th class="wth_2">
                        <a href="javascript:void(0);" title="<?php echo __('Task'); ?>#" onclick="ajaxSorting(<%= '\'caseno\', ' + caseCount + ', this' %>);" class="sortcaseno">
                            #<span class="sorting_arw"><% if(typeof csNum != 'undefined' && csNum != "") { %>
                                <% if(csNum == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <th class="wth_3"></th>
                    <th class="wth_4">
                        <a class="sorttitle" href="javascript:void(0);" title="<?php echo __('Title'); ?>" onclick="ajaxSorting(<%= '\'title\', ' + caseCount + ', this' %>);">
                            <?php echo __('Title'); ?><span class="sorting_arw"><% if(typeof csTtl != 'undefined' && csTtl != "") { %>
                                <% if(csTtl == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <th class="wth_5"></th>
                    <% if(inArray('Assigned to',field_name_arr)){ %>
                    <th class="width_assign wth_6">
                        <a class="sortcaseAt" href="javascript:void(0);" title="<?php echo __('Assigned to'); ?>" onclick="ajaxSorting(<%= '\'caseAt\', ' + caseCount + ', this' %>);">
                            <?php echo __('Assigned to'); ?>
                            <span class="sorting_arw"><% if(typeof csAtSrt != 'undefined' && csAtSrt != "") { %>
                                <% if(csAtSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>
                    <% if(inArray('Team',field_name_arr)){ %>
                    <th class="width_assign wth_6">
                        <a class="sortcaseAt" href="javascript:void(0);" title="<?php echo __('Team'); ?>" onclick="ajaxSorting(<%= '\'team\', ' + caseCount + ', this' %>);">
                            <?php echo __('Team'); ?>
                            <span class="sorting_arw"><% if(typeof csTeamSrt != 'undefined' && csTeamSrt != "") { %>
                                <% if(csTeamSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>


                    <% if(inArray('Priority',field_name_arr)){ %>
                    <th class="width_priority text-center wth_7">
                        <a class="sortprioroty" href="javascript:void(0);" title="<?php echo __('Priority'); ?>" onclick="ajaxSorting(<%= '\'priority\', ' + caseCount + ', this' %>);">
                            <span class="priorotyelipsis"><?php echo __('Priority'); ?></span>
                            <span class="sorting_arw"><% if(typeof csPriSrt != 'undefined' && csPriSrt != "") { %>
                                <% if(csPriSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>
                    <% if(inArray('Estimated Hours',field_name_arr) && page_hash !=='epics' && page_hash !=='features'){ %>
                    <th class="width_estimatedhours text-center wth_71">
                        <a class="sortestimatedhours" href="javascript:void(0);" title="<?php echo __('Est. Hours'); ?>" onclick="ajaxSorting(<%= '\'estimatedhours\', ' + caseCount + ', this' %>);">
                            <span class="estimatedhourselipsis"><?php echo __('Est. Hours'); ?></span>
                            <span class="sorting_arw">
                                <% if(typeof csEstHrsSrt != 'undefined' && csEstHrsSrt != "") { %>
                                <% if(csEstHrsSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>
                    <!--  added spenthr start-->
                    <% if(inArray('Spent Hours',field_name_arr) && page_hash !=='epics' && page_hash !=='features'){ %>
                    <th class="width_estimatedhours text-center wth_71">
                        <a class="sortestimatedhours" href="javascript:void(0);" title="<?php echo __('Spent Hours'); ?>" onclick="ajaxSorting(<%= '\'spenthours\', ' + caseCount + ', this' %>);">
                            <span class="estimatedhourselipsis"><?php echo __('Spent Hours'); ?></span>
                            <span class="sorting_arw">
                                <% if(typeof csEstHrsSrt != 'undefined' && csEstHrsSrt != "") { %>
                                <% if(csEstHrsSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>

                    <!-- added spenthr end -->
                    <% if(inArray('Updated',field_name_arr)){ %>
                    <th class="width_update text-center wth_8">
                        <a class="sortupdated" href="javascript:void(0);" title="<?php echo __('Updated'); ?>" onclick="ajaxSorting(<%= '\'updated\', ' + caseCount + ', this' %>);">
                            <?php echo __('Updated'); ?><span class="sorting_arw"><% if(typeof csUpdatSrt != 'undefined' && csUpdatSrt != "") { %>
                                <% if(csUpdatSrt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>
                    <% if(inArray('Status',field_name_arr)){ %>
                    <th class="width_status text-center wth_9">
                        <?php echo __('Status'); ?>
                    </th>
                    <% } %>
                    <% if(inArray('Original Due Date',field_name_arr)){ %>
                    <th class="width_status text-center wth_10_cf">
                        <?php echo __('Original Due Date'); ?>
                    </th>
                    <% } %>
                    <% if(inArray('Due Date',field_name_arr)){ %>
                    <th class="tsk_due_dt wth_10">
                        <a class="sortduedate" href="javascript:void(0);" title="<?php echo __('Due Date'); ?>" onclick="ajaxSorting(<%= '\'duedate\', ' + caseCount + ', this' %>);">
                            <?php echo __('Due Date'); ?>
                            <span class="sorting_arw"><% if(typeof csDuDt != 'undefined' && csDuDt != "") { %>
                                <% if(csDuDt == 'asc'){ %>
                                <i class="material-icons tsk_asc">&#xE5CE;</i>
                                <% }else{ %>
                                <i class="material-icons tsk_desc">&#xE5CF;</i>
                                <% } %>
                                <% }else{ %>
                                <i class="material-icons">&#xE164;</i>
                                <% } %></span>
                        </a>
                    </th>
                    <% } %>
                    <% if(inArray('Progress',field_name_arr)){ %>
                    <th class="width_progress text-center wth_71">
                        <span class="progresselipsis"><?php echo __('% Completion'); ?></span>
                    </th>
                    <% } %>

                    <% if(inArray('Custom Field',field_name_arr)){ %>
                    <% if(allCustomFields) { %>
                    <% for(var customFieldNames in custom_field_head) {	var customFieldName = custom_field_head[customFieldNames]; %>
                    <th class="width_progress text-center wth_10_cf">
                        <span class=""><%= customFieldName  %></span>
                    </th>
                    <% } } } %>

                </tr>
            </thead>
            <?php /* END Task List Table Header */ ?>
            <?php /* Task List Table Body */ ?>
            <tbody>
                <?php /* Quick Task Section */ ?>
                <?php if ($this->Format->isAllowed('Create Task', $roleAccess) || SES_TYPE == 1) { ?>
                    <% if(typeof projUniq !== 'undefined' && projUniq && projUniq != 'all' && page_hash !=='epics' && page_hash !=='features') { %>
                    <tr class="qtask quicktsk_tr_lnk">
                        <td colspan="<%= totalColumnCount  %>">
                            <div class="new_qktask_mc">
                                <div class="new_grp_tsk" id="new_task_label" style="width: 130px;"><a href="javascript:void(0)" class="cmn-bxs-btn"><i class="material-icons quick_task_icon">&#xE145;</i><?php echo __('Quick Task'); ?></a></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="quicktsk_tr task_list_page">
                        <td colspan="<%= totalColumnCount  %>" class="quicktd_task">
                            <div class="col-md-3 form-group label-floating fl">
                                <div class="input-group">
                                    <label class="control-label" for="addon3a"><?php echo __('Task Title'); ?></label>
                                    <input class="form-control" type="text" id="inline_qktask">
                                </div>
                            </div>
                            <div class="col-md-2 form-group label-floating fl stop-floating-top qt_form-group <?php if (!$this->Format->isAllowed('Update Task Duedate', $roleAccess)) { ?> no-pointer <?php } ?>" style="width: 13%;">
                                <label class="control-label multilang_ellipsis" for="qt_due_dat" title="<?php echo __('Due Date'); ?>"><?php echo __('Due Date'); ?></label>
                                <?php $dues_date_qt_top = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), "date"); ?>
                                <?php echo $this->Form->text('qt_due_dat', ['value' => '', 'class' => 'form-control duedate-control', 'id' => 'qt_due_dat', 'readonly' => 'readonly', 'placeholder' => 'Ex. ' . date('M d, Y', strtotime($dues_date_qt_top))]); ?>
                                <div class="cmn_help_select"></div>
                            </div>
                            <div class="col-md-2 padrht-non cstm-drop-pad qt_dropdown task_type qt_tsk_type_dropdown <?php if (!$this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?> no-pointer <?php } ?>">
                                <select class="tsktyp-select form-control task_type floating-label" placeholder="<?php echo __('Task Type'); ?>" data-dynamic-opts=true id="qt_task_type">
                                    <%
                                    var select_task_type_qt = '';
                                    for(var k in GLOBALS_TYPE) {
                                        if(isDisplayEpicType(GLOBALS_TYPE[k].type.name)){
                                            if(GLOBALS_TYPE[k].type.project_id == 0 || GLOBALS_TYPE[k].type.project_id == PROJECTS_ID_MAP[$('#projFil').val()]){
                                            var v = GLOBALS_TYPE[k];
                                            var t = v.type.id;
                                            var t1 = v.type.short_name;
                                            var t2 = v.type.name;
                                            var txs_typ = t2;
                                            var check_sel = '';
                                            if(select_task_type_qt == ''){
                                            select_task_type_qt = v.type.name;
                                            }
                                            if(defaultTaskType != "" && defaultTaskType == v.type.id){
                                                check_sel = "selected";
                                                select_task_type_qt = v.type.name;
                                            }
                                    %>
                                    <option value="<%= v.type.id %>" <%= check_sel %>><%= v.type.name %></option>
                                    <% } } } %>
                                </select>
                                <div class="cmn_help_select"></div>
                            </div>

                            <div class="col-md-1 padrht-non custom-task-fld task-type-fld labl-rt cstm-drop-pad qt_dropdown <?php if (!$this->Format->isAllowed('Change Assigned to', $roleAccess)) { ?> no-pointer <?php } ?> qt_dropdown_assn" style="width:15%;">
                                <select class="form-control floating-label" placeholder="<?php echo __('Assign To'); ?>" data-dynamic-opts=true onchange="changeTypeId(this)" id="quick-assign">
                                    <% for(var qtk in QTAssigns){
                                        var check_sel = '';
                                        var user_nm_me = '<?php echo __('Me'); ?>';
                                        if(SES_TYPE >=3 && SES_ID == QTAssigns[qtk].id){
                                            check_sel = "selected";
                                            }else if(defaultAssign && QTAssigns[qtk].id == defaultAssign){
                                                check_sel = "selected";
                                            }
                                    %>
                                    <option value=" <%= QTAssigns[qtk].id %>" <%= check_sel %>><% if(SES_ID == QTAssigns[qtk].id){ %><%= user_nm_me %><% }else{ %><%= QTAssigns[qtk].name %><% } %></option>
                                    <% } %>
                                    <option value="0"><?php echo __('Unassigned'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-1 form-group label-floating fl stop-floating-top qt_form-group <?php if (!$this->Format->isAllowed('Est Hours', $roleAccess)) { ?> no-pointer <?php } ?>">
                                <label class="control-label" for="qt_estimated_hours"><?php echo __('Est. Hour'); ?></label>
                                <?php /*<span class="os_sprite est-hrs-icon" style="top:8px;"></span> */ ?>
                                <?php echo $this->Form->text('qt_estimated_hours', ['value' => '', 'placeholder' => 'hh:mm', 'class' => 'form-control check_minute_range duedate-control', 'id' => 'qt_estimated_hours', 'maxlength' => '5', 'onkeypress' => 'return numeric_decimal_colon(event)']); ?>
                            </div>
                            <div class="quicktask_save_exit_btn">
                                <div class="btn-group save_exit_btn">
                                    <input type="hidden" value="list" id="task_view_types_span" />
                                    <a id="quickcase_qt" href="javascript:void(0)" class="btn btn-primary btn-raised" onclick="AddQuickTask(<%= '\'sac\'' %>);"><?php echo __('Save'); ?></a>
                                    <span class="dropdown">
                                        <a href="javascript:void(0);" data-target="#" class="btn btn-primary btn-raised dropdown-toggle crtaskmoreoptn" data-toggle="dropdown"><span class="caret"></span></a>
                                        <ul class="dropdown-menu crtskmenus">
                                            <li><a href="javascript:void(0);" onclick="return AddQuickTask();"><?php echo __('Save & Continue'); ?></a></li>
                                            <li><a href="javascript:void(0);" onclick="return AddQuickTask(<%= '\'sact\'' %>);"><?php echo __('Save & Start Timer'); ?></a></li>
                                        </ul>
                                    </span>
                                </div>
                                <span class="input-group-btn ds_ib_btn">
                                    <a href="javascript:void(0);" onclick="blurqktask_qt();">
                                        <?php echo __('Cancel'); ?>
                                    </a>
                                </span>
                            </div>
                            <div class="cb"></div>
                        </td>
                    </tr>
                    <% } %>
                <?php } ?>
                <?php /* END Quick Task Section */ ?>
                <?php /* Task Rows Section */ ?>

                    <%
                    var count = 0;
                    var totids = "";
                    var openId = "";
                    var groupby = GrpBy;
                    var prvGrpvalue='';
                    var pgCaseCnt = caseAll?countJS(caseAll):0;
                    if(caseCount && caseCount != 0){
                    var count=0;
                    var caseNo = "";
                    var chkMstone = "";
                    var caseLegend = "";
                    var totids = "";
                    var show_history = "";
                    var projectName ='';var projectUniqid='';
                    var curGgroup = 0;
                    for(var caseKey in caseAll){
                        var getdata = caseAll[caseKey];
                        if(groupby=='milestone' && getdata.Easycase && getdata.EasycaseMilestone.mid == null){
                            getdata.EasycaseMilestone.mid = 'NA';
                        }
                        count++;
                        var caseAutoId = getdata.id;
                        var isFavourite = getdata.isFavourite;
                        var favMessage ="Set favourite";
                        if(isFavourite){
                            var favMessage ="Remove favourite";
                        }
                        var favouriteColor = getdata.favouriteColor;
                        var caseUniqId = getdata.uniq_id;
                        var caseNo = getdata.case_no;
                        var caseUserId = getdata.user_id;
                        var caseTypeId = getdata.type_id;
                        var projId = getdata.project_id;
                        var caseLegend = getdata.legend;
                        var casePriority = getdata.priority;
                        var caseFormat = getdata.format;
                        var caseTitle = getdata.title;
                        var caseEstHoursRAW = getdata.estimated_hours;
                        var caseEstHours = getdata.estimated_hours_convert;
                        var caseSpentHrs = getdata.tot_spent_hour;
                        var isactive = getdata.isactive;
                        var caseAssgnUid = getdata.assign_to;
                        var getTotRep = 0;
                        var caseParenId = getdata.parent_task_id;
                        var task_list_group_by = localStorage.getItem('AJAX_TASK_GROUPBY');
                        if(getdata.reply_cnt && getdata.reply_cnt!=0) {
                            getTotRep = getdata.reply_cnt;
                        }/*getdata.case_count */
                        var getTotRepCnt = (getdata.case_count)?getdata.case_count:0;
                        if(caseUrl == caseUniqId) {
                            openId = count;
                        }
                        if(caseLegend==2 || caseLegend==4){
                            var headerlegend = 2;
                        }else{
                            var headerlegend = caseLegend;
                        }
                        var chkDat = 0;
                        var showQuickActiononList = 0;
                    var showQuickActiononListEdit = 0;
                        /*if((caseLegend == 1 || caseLegend == 2 || caseLegend == 4) || (SES_TYPE == 1 || SES_TYPE == 2 || (caseUserId== SES_ID))) {
                            var showQuickActiononList = 1;
                        }*/
                        if(isactive == 1 && (caseLegend == 1 || caseLegend == 2 || caseLegend == 4) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
                            showQuickActiononList = 1;
                        }
                    if(isactive == 1 && (caseLegend == 1 || caseLegend == 2 || caseLegend == 4) && (caseUserId== SES_ID)){
                            showQuickActiononListEdit = 1;
                    }
                        var showQuickActiononCopy = 0;
                        if(isactive == 1 && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
                            showQuickActiononCopy = 1;
                        }

                        if(projUniq=='all' && (typeof getdata.pjname !='undefined')){
                            projectName = getdata.pjname;
                            projectUniqid = getdata.pjUniqid;
                        }else if(projUniq!='all'){
                            projectName = getdata.pjname;
                            projectUniqid = getdata.pjUniqid;
                        } %>
                        <% if(projUniq=='all' && groupby !='milestone') { %>
                        <tr class="list-dt-row">
                            <td colspan="<%= totalColumnCount %>" align="left" class="curr_day tkt_pjname">
                                <div class="<% if(count!=1) {%>y_day<% } %>"><span><%= getdata.pjname %></span></div>
                            </td>
                        </tr>
                        <% } %>
                        <% if(getdata.newActuldt && getdata.newActuldt!=0) { %>
                        <?php if (SES_COMP == 1) { ?>
                        <% if(getdata.newActuldt != "Today" && show_history == '' && caseMenuFilters == "assigntome"){ show_history="show"; %>
                        <tr class="list-dt-row my_qt_row_selct">
                            <td colspan="<%= totalColumnCount %>" align="left" class="curr_day qt_history_label">
                                <span><?php echo __('History'); ?></span>
                            </td>
                        </tr>
                        <% } %>
                        <tr class="list-dt-row <% if(getdata.newActuldt == "Today" && caseMenuFilters == "assigntome"){ %>my_qt_row_selct<% } %>">
                        <?php } ?>
                        <?php if (SES_COMP != 1 && SES_COMP != 28528) { ?>
                        <tr class="list-dt-row">
                        <?php } ?>
                        <% if(ajax_group_by == "" || ajax_group_by =="Date" ){ %>
                        <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                            <div class="dt_cmn_mc <% if(count!=1 && !getdata.pjname) {%>y_day<% } %>"> <span><%= getdata.newActuldt %> <?php if (SES_COMP == 1) { ?><?php echo __('Tasks'); ?><?php } ?></span>
                            </div>
                        </td>
                        <% } %>
                        </tr>
                        <% } %>
                    <tr class="list-dt-row">
                        <% if(ajax_group_by != "" && task_list_group_by == 'Assign to'){
                            if(curGgroup != getdata.asgnShortName){
                                curGgroup = getdata.asgnShortName; %>
                        <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                            <div class="dt_cmn_mc"> <span><%= getdata.asgnShortName %></span>
                            </div>
                        </td>
                        <% 	} } else if(task_list_group_by == 'Status') { 
                            if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){
                                    if(curGgroup != getdata.CustomStatus.name){
                                        curGgroup = getdata.CustomStatus.name; %>
                                            <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                                                <div class="dt_cmn_mc"> <span><%= getdata.CustomStatus.name %></span>
                                                </div>
                                            </td>
                                            <% }
                            }else{
                                if(curGgroup != getdata.legend){
                                    curGgroup = getdata.legend;  %>
                                            <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                                                <div class="dt_cmn_mc"> <span><%= easycase.getStatus(getdata.type_id, getdata.legend,'detail') %></span>
                                                </div>
                                            </td>
                                            <% }
                            }
                        } else if(task_list_group_by == 'Priority'){ 
                            if(curGgroup !== getdata.priority){
                                curGgroup = getdata.priority;
                                if(getdata.priority == '1'){ %>
                        <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                            <div class="dt_cmn_mc"> <span>Medium</span>
                            </div>
                        </td>
                        <% }else if(getdata.priority == '2'){ %>
                        <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                            <div class="dt_cmn_mc"> <span>Low</span>
                            </div>
                        </td>
                        <% } else { %>
                        <td colspan="<%= totalColumnCount  %>" align="left" class="curr_day">
                            <div class="dt_cmn_mc"> <span>High</span>
                            </div>
                        </td>
                        <% }  }  } %>
                    </tr>

                    <% if(typeof getdata.EasycaseMilestone != 'undefined'){ if(getdata.EasycaseMilestone.mid == null){ var mid = 'NA'; }else{ var mid = getdata.EasycaseMilestone.mid; } } %>
                    <% var bgcol = "#F2F2F2"; if(chkDat == 1) { bgcol = "#FFF"; } var borderBottom = ""; if(pgCaseCnt == count) { borderBottom = "border-bottom:1px solid #F2F2F2;"; } %>
                    
                    <tr class="row_tr tr_all trans_row 2 <% if(!inArray('basicdetail',field_name_arr)){ %>decrease_height<% } %> <% if(typeof mid != 'undefined'){ %>tgrp_tr_all<% } %><% if(getdata.priority == 0){ %>background_new<% } else { %><% } %>" id="curRow<%= caseAutoId %>" <% if(typeof mid != 'undefined'){ %>data-pid="<%= projId %>" data-mid="<%= mid %>" <% } %> data-is-parent="<% if(rel_arr.length && rel_arr.indexOf(caseAutoId) != -1){ %>1<% } %>">
                        <td <% if(groupby =='' || groupby !='priority'){%>class="check_list_task tsk_fst_td pr_<%= easycase.getPriority(casePriority) %>" <% } %>>
                            <div class="checkbox<% if (page_hash === 'tasks' && (getdata.type_id == 13 || getdata.type_id == 15)) { %> chk-not-allowed<% } %>">
                                <label>
                                    <% if (page_hash === 'tasks' && (getdata.type_id == 13 || getdata.type_id == 15)) { %>
                                        <input type="checkbox" style="cursor:not-allowed" id="actionChk<%= count %>" value="<%= caseAutoId + '|' + caseNo + '|' + caseUniqId %>" class="fl mglt chkOneTsk" data_epic_type="<%= getdata.type_id == getdata.original_epic_id %>" disabled="disabled">
                                    <% } else if(caseLegend != 3 && caseTypeId != 10) { %>
                                    <input type="checkbox" style="cursor:pointer" id="actionChk<%= count %>" value="<%= caseAutoId + '|' + caseNo + '|' + caseUniqId %>" class="fl mglt chkOneTsk" data_epic_type="<%= getdata.type_id == getdata.original_epic_id %>">
                                    <% } else if(caseTypeId != 10) { %>
                                    <input type="checkbox" id="actionChk<%= count %>" checked="checked" value="<%= caseAutoId + '|' + caseNo + '|closed' %>" disabled="disabled" class="fl mglt chkOneTsk" data_epic_type="<%= getdata.type_id == getdata.original_epic_id %>">
                                    <% } else { %>
                                    <input type="checkbox" id="actionChk<%= count %>" value="<%= caseAutoId + '|' + caseNo + '|update' %>" class="fl mglt chkOneTsk" data_epic_type="<%= getdata.type_id == getdata.original_epic_id %>">
                                    <% } %>
                                </label>
                            </div>
                            <input type="hidden" id="actionCls<%= count %>" value="<%= caseLegend %>" disabled="disabled" size="2" />
                        </td>
                        <td class="text-center count-plist-drop pr"><%= caseNo %>
                            <span class="watch showtime_<%= caseAutoId %>"></span>
                            <% if(page_hash === 'tasks') { %>
                            <% if(page_hash !=='epics' && page_hash !=='features') { %>
                            <div class="check-drop-icon" <% if(count == 1){ %>id="tour_task_title_listing_act" <% } %>>
                                <div class="dropdown cmn_tooltip_hover">
                                    <a class="dropdown-toggle tooltip_link" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                                        <i class="material-icons">&#xE5D4;</i><?php //&#xE5CF;
                                                                                ?>
                                    </a>
                                    <ul class="dropdown-menu hover_item">
                                        <% if( SES_ID == caseUserId) { caseFlag=3; }
                                if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid)){ %>
                                        <% if( (isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit ) || isAllowed('Edit All Task',projectUniqid)){ %>
                                        <% if(getdata.type_id == getdata.original_epic_id){ %>
                                        <li onclick="editepic(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit)){ %>display:block <% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                                        </li>
                                        <% }else if(getdata.type_id == getdata.original_feature_id){ %>
                                        <li onclick="editfeature(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit)){ %>display:block <% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                                        </li>
                                        <% }else{ %>
                                        <li onclick="editask(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit)){ %>display:block <% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <% } %>
                                        <% if(isAllowed("Change Status of Task",projectUniqid)){ %>
                                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){
                                        if(getdata.CustomStatus && getdata.CustomStatus.status_master_id != 3){ %>
                                                                                <li onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.status_master_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.name  + '\'' %>);" id="new<%= caseAutoId %>">
                                                                                    <a href="javascript:void(0);">
                                                                                        <span style="background-color:#<%= lastCustomStatus.LastCS.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                                                                        <%= lastCustomStatus.LastCS.name %></a>
                                                                                </li>
                                                                                <% }
                                    } else{ %>

                                        <% var caseFlag="";
                                if((caseLegend != 3) && caseTypeId != 10) { caseFlag=5; }
                                if(getdata.isactive == 1){ %>
                                        <% if(isAllowed("Status change except Close",projectUniqid)){ %>
                                        <li onclick="setCloseCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);" id="close<%= caseAutoId %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>

                                        <% } %>
                                        <% } %>
                                        <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                        <li data-prjid="<%= projId %>" data-caseid="<%= caseAutoId %>" data-caseno="<%= caseNo %>" id="cppy_checklist<%= caseAutoId %>" onclick="copyChecklist(<%= '\'' + count + '\'' %>,this);">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i><?php echo __('Copy Checklist'); ?></a>
                                        </li>
                                        <% } %>
                                        <% if(isAllowed("Create Task",projectUniqid)){ %>
                                        <%
                                if((getdata.is_sub_sub_task==null) || (getdata.is_sub_sub_task=='')){
                                if(caseLegend !=3 && caseTypeId != 10){ %>
                                        <% if(isEpicTask(getdata.type_id , getdata.actual_dt_created) && getdata.type_id != 13 && !(page_hash === 'tasks' && getdata.type_id == 15)){ %>
                                        <li onclick="addSubtaskPopup(<%= '\'' + projectUniqid + '\'' %>,<%= '\'' + getdata.id + '\'' %>,<%= '\'' + getdata.project_id + '\'' %>,<%= '\'' + getdata.uniq_id + '\'' %>,<%= '\'' + getdata.title + '\'' %>);">
                                            <a href="javascript:void(0);"><i class="material-icons"></i><?php echo __('Create Subtask'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } }%>
                                        <% } %>
                                        <% if(caseParenId){ %>
                                        <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <li onclick="convertToParentTask(<%= '\''+ caseAutoId+'\',\''+caseNo+'\'' %>);" id="convertToTask<%= caseAutoId %>" style=" <% if(showQuickActiononList){ %>display:block <% } else { %>display:none<% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE15A;</i><?php echo __('Convert To Parent'); ?></a>
                                            </li>
                                            <% } %>
                                        <?php } ?>
                                        <% } %>
                                        <% if(caseParenId == "" || caseParenId == null){ %>
                                        <%	if((getdata.sub_sub_task==null) || (getdata.sub_sub_task =="") || (getdata.sub_sub_task ==0) ){  %>
                                        <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <li onclick="convertToSubTask(<%= '\''+ caseAutoId+'\',\''+projId+'\',\''+caseNo+'\'' %>);" id="convertToSubTask<%= caseAutoId %>" style=" <% if(showQuickActiononList){ %>display:block <% } else { %>display:none<% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE15A;</i><?php echo __('Convert To Subtask'); ?></a>
                                            </li>
                                            <% } %>
                                        <?php } ?>

                                        <% } } %>
                                        
                                            <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                                            <% if(caseLegend ==3 ) { %>
                                            <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <li onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                                            </li>
                                            <% } %>
                                            <% } %>
                                            <% } else{ %>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <li onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                                            </li>
                                            <% } %>
                                            <% } %>
                                            <% } %>
                                            <% if(isAllowed("Start Timer",projectUniqid)){ %>
                                            <% if(caseLegend !=3 && caseTypeId != 10){ %>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <li onclick="startTimer(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>, <%= '\'' + caseUniqId + '\'' %>, <%= '\'' + projectUniqid + '\'' %>, <%= '\'' + escape(projectName) + '\'' %>); ">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE425;</i><?php echo __('Start Timer'); ?></a>
                                            </li>
                                            <% } %>
                                            <% } %>
                                            <% } %>

                                        <% if((caseFlag == 5 || caseFlag==2) && getdata.isactive == 1) { %>
                                        <!--<li class="divider"></li>-->
                                        <% } %>
                                        <% if(isAllowed("Reply on Task",projectUniqid)){ %>
                                        <% if(caseLegend == 3) { caseFlag= 7; } else { caseFlag= 8; }
                                if(getdata.isactive == 1){ %>
                                        <li id="act_reply<%= count %>" data-task="<%= caseUniqId %>" page-refer-val="Task List Page">
                                            <a href="javascript:void(0);" id="reopen<%= caseAutoId %>" style="<% if(caseFlag == 7){ %>display:block <% } else { %>display:none<% } %>">
                                                <div class="act_icon act_reply_task fl" title="Re-open"></div><i class="material-icons">&#xE898;</i> <?php echo __('Re-open'); ?>
                                            </a>
                                            <a href="javascript:void(0);" id="reply<%= caseAutoId %>" style="<% if(caseFlag == 8){ %>display:block <% } else { %>display:none<% } %>"><i class="material-icons">&#xE15E;</i><?php echo __('Reply'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <% if( SES_ID == caseUserId) { caseFlag=3; }
                                if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid)){ %>
                                        <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %>
                                        <li onclick="copytask(<%= '\''+ caseUniqId+'\',\''+ caseAutoId+'\',\''+caseNo+'\',\''+projId+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="copy<%= caseAutoId %>" style=" <% if(showQuickActiononCopy || isAllowed('Change Other Details of Task',projectUniqid)){ %>display:block <% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i><?php echo __('Copy'); ?></a>
                                        </li>
                                        <% } %>
                                        <% }
                                if((caseLegend == 1 || caseLegend == 2 || caseLegend == 4) && caseTypeId!= 10) { caseFlag=2; }
                                if((SES_TYPE == 1 || SES_TYPE == 2) || ((caseLegend == 1 || caseLegend == 2 || caseLegend == 4) &&  (SES_ID == caseUserId))){ %>
                                        <% if(isAllowed("Move to Project",projectUniqid)){ %>
                                        <% if(getdata.isactive == 1){ %>
                                        <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                        <li data-prjid="<%= projId %>" data-caseid="<%= caseAutoId %>" data-caseno="<%= caseNo %>" id="mv_prj<%= caseAutoId %>" style=" " onclick="mvtoProject(<%= '\'' + count + '\'' %>,this);">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to Project'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <% } %>
                                        <% if(getdata.isactive == 0){ %>
                                        <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %>
                                        <li data-prjid="<%= projId %>" data-caseid="<%= caseAutoId %>" data-caseno="<%= caseNo %>" id="mv_prj<%= caseAutoId %>" style=" ">
                                            <a onclick="restoreFromTask(<%= caseAutoId %>,<%= projId %>,<%= caseNo %>)" href="javascript:void(0);"><i class="material-icons">&#xE8B3;</i><?php echo __('Restore'); ?></a>
                                        </li>
                                        <li data-prjid="<%= projId %>" data-caseid="<%= caseAutoId %>" data-caseno="<%= caseNo %>" id="mv_prj<%= caseAutoId %>" style=" ">
                                            <a onclick="removeFromTask(<%= caseAutoId %>,<%= projId %>,<%= caseNo %>)" href="javascript:void(0);"><i class="material-icons">&#xE15C;</i><?php echo __('Remove'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <% }
                                        if(getdata.isactive == 1 &&  getdata.pjMethodologyid != 2){ %>
                                        <% if(isAllowed("Move to Milestone",projectUniqid)){ %>
                                        <li onclick="moveTask(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'\'' %>,<%= '\'' + projId + '\'' %>);" id="moveTask<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:block <% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE89F;</i><?php echo __('Move to Task Group/Sprint'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <% if(getdata.milestone_id){ %>
                                        <% if(isAllowed("Move to Milestone",projectUniqid)){ %>
                                        <li onclick="removeTask(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'\'' %>,<%= '\'' + projId + '\'' %>);" id="moveTask<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE15C;</i><?php echo __('Remove from Task Group'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                        <!--<li class="divider"></li>-->
                                        <% if(getdata.isactive == 1){
                                                        if(caseMenuFilters == "milestone" && (SES_TYPE == 1 || SES_TYPE == 2 || SES_ID == getdata.Em_user_id || isAllowed('Delete All Task',projectUniqid))) {
                                                        caseFlag = "remove";%>
                                        <% if(isAllowed("Delete Task",projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                                        <li onclick="removeThisCase(<%= '\'' + count + '\'' %>,<%= '\'' + getdata.Emid + '\'' %>, <%= '\'' + caseAutoId + '\'' %>, <%= '\'' + getdata.Em_milestone_id + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUserId + '\'' %>);" id="rmv<%= caseAutoId %>" style="<% if(caseFlag == "remove"){ %>display:block<% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE15C;</i><?php echo __('Remove Task'); ?></a>
                                        </li>
                                        <% } %>
                                        <%
                                            }
                                            }
                                            if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Archive All Task',projectUniqid)) { caseFlag = "archive"; }
                                            if(getdata.isactive == 1){ %>
                                                                    <% if(isAllowed("Archive Task",projectUniqid) || isAllowed("Archive All Task",projectUniqid)){ %>
                                                                    <li onclick="archiveCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>);" id="arch<%= caseAutoId %>" style="<% if(caseFlag == "archive"){ %>display:block<% } else { %>display:none<% } %>">
                                                                        <a href="javascript:void(0);"><i class="material-icons">&#xE149;</i><?php echo __('Archive'); ?></a>
                                                                    </li>
                                                                    <% } %>
                                                                    <% }
                                            if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Delete All Task',projectUniqid)) { caseFlag = "delete"; }
                                            if(getdata.isactive == 1){ %>
                                        <% if(isAllowed("Delete Task",projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                                        <li onclick="deleteCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>, <%= '\'' + getdata.is_recurring + '\'' %>);" id="arch<%= caseAutoId %>" style="<% if(caseFlag == "delete"){ %>display:block<% } else { %>display:none<% } %>">
                                            <a href="javascript:void(0);"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                                        </li>
                                        <% } %>
                                        <% } %>
                                    </ul>
                                </div>
                            </div>
                            <% } %>
                            <% } %>
                        </td>
                        <td class="favo-td">
                            <span class="ttype_global tt_<%= getdata.csTdTyp ? getttformats(getdata.csTdTyp[1]) : ''%>" rel="tooltip" original-title="<%= getdata.csTdTyp ? getdata.csTdTyp[1] : '' %>" style="margin-top:2px;">
                            </span>
                            <span id="caseProjectSpanFav<%=caseAutoId %>">
                                <a href="javascript:void(0);" class="caseFav" onclick="setCaseFavourite(<%=caseAutoId %>,<%=projId %>,<%= '\''+caseUniqId+'\'' %>,1,<%=isFavourite%>)" rel="tooltip" original-title="<%=favMessage%>" style="color:<%=favouriteColor%>;">
                                    <% if(isFavourite) { %>
                                    <i class="material-icons" style="font-size:18px;">star</i>
                                    <% }else{ %>
                                    <i class="material-icons" style="font-size:18px;">star_border</i>
                                    <% } %>
                                </a>
                            </span>
                        </td>
                        <td class="relative <% if(inArray('basicdetail',field_name_arr)){ %>list-cont-td<% } %>" <% if(count == 1){ %>id="tour_task_title_listing" <% } %>>
                            <div class="title-dependancy-all">
                                <a href="javascript:void(0);" class="ttl_listing" data-task-id="<%= caseUniqId %>">
                                    <?php if ($this->Format->isCriticalEnabled()) { ?>
                                    <% if(getdata.is_critical) { %>
                                    <i class="material-icons critical-path-icon" title="<?php echo __('Critical Path Task'); ?>">&#xE0D7;</i>
                                    <% } %>
                                    <?php } ?>
                                    <span id="titlehtml<%= count %>" data-task="<%= caseUniqId %>" class="case-title case_sub_task <% if(getdata.type_id!=10 && getdata.legend==3) { %>closed_tsk<% } %> case_title_<%= caseAutoId %>">
                                        <span class="max_width_tsk_title ellipsis-view <% if(caseLegend == 5){%>resolve_tsk<% } %> case_title wrapword task_title_ipad <% if(caseTitle.length>40){%>overme<% }%> " title="<%= formatText(ucfirst(caseTitle)) %>  ">
                                            <%= formatText(ucfirst(caseTitle)) %>
                                        </span>
                                    </span>
                                    <div class="task_dependancy_item">
                                        <div class="task_dependancy fr">
                                            <%  if(getdata.children){ %>
                                            <span class="fl case_act_icons task_parent_block" id="task_parent_block_<%= caseUniqId %>">
                                                <div rel="" title="<?php echo __('Parents'); ?>" onclick="showParents(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\'' + getdata.children + '\'' %>);" class=" task_title_icons_parents fl"></div>
                                                <div class="dropdown dropup fl1 open1 showParents">
                                                    <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                                        <li class="pop_arrow_new"></li>
                                                        <li class="task_parent_msg" style=""><?php echo __('These tasks are waiting on this task'); ?>.</li>
                                                        <li>
                                                            <ul class="task_parent_items" id="task_parent_<%= caseUniqId %>" style="">
                                                                <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </span>
                                            <% } %>
                                            <% if(getdata.depends){ %>
                                            <span class="fl case_act_icons task_dependent_block" id="task_dependent_block_<%= caseUniqId %>">
                                                <div rel="" title="<?php echo __('Dependents'); ?>" onclick="showDependents(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\'' + getdata.depends + '\'' %>);" class=" task_title_icons_depends fl"></div>
                                                <div class="dropdown dropup fl1 open1 showDependents">
                                                    <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                                        <li class="pop_arrow_new"></li>
                                                        <li class="task_dependent_msg" style=""><?php echo __("Task can't start. Waiting on these task to be completed"); ?>".</li>
                                                        <li>
                                                            <ul class="task_dependent_items" id="task_dependent_<%= caseUniqId %>" style="">
                                                                <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </span>
                                            <% } %>
                                        </div>
                                        <div class="task_dependancy parenttt fr">
                                            <% if(getdata.parent_task_id){ %>
                                            <span class="fl case_act_icons task_parent_block" id="task_parent_id_block_<%= caseUniqId %>">
                                                <div rel="" title="<?php echo __('Subtask'); ?>" onclick="showSubtaskParents(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\'' + getdata.parent_task_id + '\'' %>);" class="fl parent_sub_icons"><i class="material-icons">&#xE23E;</i></div>
                                                <div class="dropdown dropup fl1 open1 showParents">
                                                    <ul class="dropdown-menu  bottom_dropdown-caret inner_parent_ul">
                                                        <li class="pop_arrow_new"></li>
                                                        <li class="task_parent_msg" style=""><?php echo __('Below tasks are parent task of this Subtask'); ?>.</li>
                                                        <li>
                                                            <ul class="task_parent_tt_items" id="task_parent_tt_<%= caseUniqId %>" style="">
                                                                <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </span>
                                            <% } %>
                                        </div>
                                        <div class="task_dependancy fr">
                                            <% if(getdata.is_recurring == 1 || getdata.is_recurring == 2){ %>
                                            <div rel="tooltip" title="<?php echo __('Recurring Task'); ?>" onclick="showRecurringInfo(<%= caseAutoId %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></div>
                                            <% } %>
                                        </div>
                                        <div class="cb"></div>
                                    </div>
                                </a>
                            </div>
                            <% if(inArray('basicdetail',field_name_arr)){ %>
                                <div class="list-td-hover-cont">
                                <span class="created-txt"><% if(getTotRepCnt && getTotRepCnt!=0) { %><?php echo __('Updated');?><% } else { %><?php echo __('Created');?><% } %> <?php echo __('by');?> <%= getdata.usrShortName %> <% if(getdata.updtedCapDt.indexOf('Today')==-1 && getdata.updtedCapDt.indexOf('Y\'day')==-1) { %><?php echo __('on');?><% } %> <%= getdata.updtedCapDt %></span>
                                <span class="list-devlop-txt dropdown">
                            <a class="dropdown-toggle"  <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %>  data-toggle="dropdown" href="javascript:void(0);"<% } %>  data-target="#">
                                        <i class="material-icons tag_fl">&#xE54E;</i>
                                        <span id="showUpdStatus<%= caseAutoId %>" class="<% if(showQuickActiononList && getdata.isactive == 1){ %>clsptr<% } %>" title="<%= getdata.csTdTyp ? getdata.csTdTyp[1] : '' %>" >
                                            <span class="tsktype_colr" id="tsktype<%= caseAutoId %>"><%= getdata.csTdTyp ? getdata.csTdTyp[1] : ''%><span class="due_dt_icn"></span></span>
                                        </span>
                                    </a>
                                    <span id="typlod<%= caseAutoId %>" class="type_loader">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
                                    </span>
                                                <% if(showQuickActiononList && getdata.isactive == 1){ %>
                                                    <% if (page_hash === 'tasks' && getdata.type_id != 13 && getdata.type_id != 15) { %>
                                                    <% if(page_hash !=='epics' && page_hash !=='features') { %>	
                                        <ul class="dropdown-menu listgrp-bug-dropdn">
                                                            <li>
                                                            <input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="seachitems(this);" />
                                                        </li>
                                        <%
                                        for(var k in GLOBALS_TYPE) {
                                if(GLOBALS_TYPE[k].type.project_id == 0 || GLOBALS_TYPE[k].type.project_id == getdata.project_id){
                                        if(isDisplayEpicType(GLOBALS_TYPE[k].type.name)){
                                            var v = GLOBALS_TYPE[k];
                                            var t = v.type.id;
                                            var t1 = v.type.short_name;
                                            var t2 = v.type.name;
                                            var txs_typ = t2;
                                            $.each(DEFAULT_TASK_TYPES, function(i,n) {
                                                if(i == t1){
                                                    txs_typ = n;
                                                }
                                            });
                                        %>
                                        <li onclick="changeCaseType(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>); changestatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + t + '\'' %>, <%= '\'' + t1 + '\'' %>, <%= '\'' + t2 + '\'' %>, <%= '\'' + caseUniqId + '\'' %>)">
                                            <a href="javascript:void(0);">
                                                                <span class="ttype_global tt_<%= getttformats(t2)%>">
                                            <%= t2 %></span>
                                                                </a>
                                        </li>
                                        <% }
                                        }
                                } %>
                                        </ul>
                                                <% } %>
                                                <% } %>
                                                <% } %>
                                </span>
                                <% if (page_hash === 'tasks' && getdata.type_id != 13 && getdata.type_id != 15) { %>
                                <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                <span class="check-drop-icon dsp-block">
                                    <span class="dropdown">
                                <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %>data-toggle="dropdown" href="javascript:void(0);"<% } %>  data-target="#">
                                        <i class="material-icons">&#xE5C5;</i>
                                        </a>
                                        <% if(showQuickActiononList && getdata.isactive == 1){ %>
                                        <ul class="dropdown-menu listgrp-bug-dropdn">
                                                            <li>
                                                            <input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="seachitems(this);" />
                                                        </li>
                                        <%
                                        for(var k in GLOBALS_TYPE) {
                                if(GLOBALS_TYPE[k].type.project_id == 0 || GLOBALS_TYPE[k].type.project_id == getdata.project_id){
                                    if(isDisplayEpicType(GLOBALS_TYPE[k].type.name)){
                                            var v = GLOBALS_TYPE[k];
                                            var t = v.type.id;
                                            var t1 = v.type.short_name;
                                            var t2 = v.type.name;
                                            var txs_typ = t2;
                                            $.each(DEFAULT_TASK_TYPES, function(i,n) {
                                                if(i == t1){
                                                    txs_typ = n;
                                                }
                                            });
                                        %>
                                        <li onclick="changeCaseType(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>); changestatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + t + '\'' %>, <%= '\'' + t1 + '\'' %>, <%= '\'' + t2 + '\'' %>, <%= '\'' + caseUniqId + '\'' %>)">
                                            <a href="javascript:void(0);">
                                            <span class="ttype_global tt_<%= getttformats(t2)%>">
                                            <%= t2 %></span>
                                                                </a>
                                        </li>
                                        <% }
                                        }
                                } %>
                                        </ul>
                                        <% } %>
                                    </span>
                                </span>
                                <% } %>
                                <% } %>
                                <% if(getdata.epic) { %>
                                <div class="label epic-label"  rel="tooltip" title="<%= getdata.epic %>" ><%= getdata.epic %></div>
                                <% } %>
                                    
                                
                                    <% if (page_hash === 'tasks' && getdata.type_id != 13 && getdata.type_id != 15) { %>
                                    <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                <span class="small-list-devlop-icon">
                                    <% if(getdata.is_recurring == 1 || getdata.is_recurring == 2){ %>
                                    <a rel="tooltip" title="<?php echo __('Recurring Task');?>" href="javascript:void(0);" onclick="showRecurringInfo(<%= caseAutoId %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></a>
                                    <% } %>
                                    <% var caseFlag="";
                                        if((caseLegend == 1 || caseLegend == 2 || caseLegend == 4) && caseTypeId!= 10) { caseFlag=2; }
                                        if(getdata.isactive == 1){
                                        if(caseFlag == 2){ %>
                                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){ }else{ %>
                                        <% if(isAllowed('Change Status of Task',projectUniqid)){ %>
                                <a rel="tooltip" title="<?php echo __('Resolve');?>" href="javascript:void(0)" onclick="caseResolve(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);">
                                            <i class="material-icons">&#xE889;</i>
                                        </a>
                                <% } %>
                                        <% } } }
                                        if((caseLegend == 1 || caseLegend == 2 || caseLegend == 4 || caseLegend == 5) && caseTypeId != 10) { caseFlag=5; }
                                        if(getdata.isactive == 1){
                                        if(caseFlag == 5) {	%>
                                <% if(isAllowed('Change Status of Task',projectUniqid)){ %>
                                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){ %>
                                <% if(isAllowed("Status change except Close",projectUniqid)){ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Mark as Completed');?>" onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,0,<%= '\'3\'' %>,<%= '\'close\'' %>);">
                                            <i class="material-icons">&#xE876;</i>
                                        </a>
                                <% } %>
                                        <% }else{ %>
                                    <% if(isAllowed("Status change except Close",projectUniqid)){ %>
                                            <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Mark as Completed');?>" onclick="setCloseCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);">
                                                <i class="material-icons">&#xE876;</i>
                                            </a>
                                <% } %>
                                        <% } } } } %>
                                        
                                        <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                                        <% if(caseLegend ==3 ) { %>
                                                    <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                                                        <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                                <span rel="tooltip" title="<?php echo __('Time Entry');?>" onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);" class="case_act_icons task_title_icons_timelog fl"></span>
                                        <% } %>
                                        <% } %>
                                        <% } else{ %>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                            <span rel="tooltip" title="<?php echo __('Time Entry');?>" onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);" class="case_act_icons task_title_icons_timelog fl"></span>
                                        <% } %>
                                        <% } %>
                                        <% } %>

                                        <% if(caseLegend == 3) { caseFlag= 7; } else { caseFlag= 8; }
                                                        if (getdata.isactive == 1) {
                                                        %>
                                                        <% if(isAllowed('Change Status of Task',projectUniqid)){ %>
                                                        <a href="javascript:void(0);" id="act_reply_spn<%= count %>" style="<% if(caseFlag == 7){ %>display:inline-block <% } else { %>display:none<% } %>" data-task="<%= caseUniqId %>" page-refer-val="Task List Page" rel="tooltip" title="<?php echo __('Re-open');?>"><i class="material-icons">&#xE898;</i></a>
                                                        <% } %>
                                                        <% if(isAllowed('Reply on Task',projectUniqid)){ %>
                                                        <a href="javascript:void(0);" id="act_reply_spn<%= count %>" style="<% if(caseFlag == 8){ %>display:inline-block <% } else { %>display:none<% } %>" data-task="<%= caseUniqId %>" page-refer-val="Task List Page" rel="tooltip" title="<?php echo __('Reply');?>"><i class="material-icons">&#xE15E;</i></a>
                                                    <% } %>
                                        <% }
                                        if( SES_ID == caseUserId) { caseFlag=3; }
                                        if(getdata.isactive == 1){
                                        if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid)){ %>
                                        <% if((isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit) || isAllowed('Edit All Task',projectUniqid)){ %>
                                            <% if(getdata.type_id == getdata.original_epic_id){ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editepic(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);">
                                            <i class="material-icons">&#xE254;</i>
                                        </a>
                                    <% }else if(getdata.type_id == getdata.original_feature_id){ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editfeature(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);">
                                            <i class="material-icons">&#xE254;</i>
                                        </a>
                                    <% }else{ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editask(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);">
                                            <i class="material-icons">&#xE254;</i>
                                        </a>
                                    <% } %>
                                        <% } %>
                                        <% } } %>
                                        
                                        <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                                            <% if(caseLegend ==3 ) { %>
                                                <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                                                    <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Time Entry');?>" onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);">
                                                        <i class="material-icons">&#xE8B5;</i>
                                                        </a>
                                                <% } %>
                                                <% } %>
                                        <% } else { %>
                                            <% if((isEpicTask(getdata.type_id , getdata.original_epic_id, getdata.actual_dt_created) && !(page_hash === 'tasks' && getdata.type_id == 15))){ %>
                                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Time Entry');?>" onclick="createlog(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>);">
                                            <i class="material-icons">&#xE8B5;</i>
                                        </a>
                                        <% } %>
                                        <% } %>
                                        <% } %>
                                        <%
                                        if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Archive All Task',projectUniqid)) { caseFlag = "archive"; }
                                        if(getdata.isactive == 1){
                                        if(caseFlag == "archive"){ %>
                                        <% if(isAllowed('Archive Task',projectUniqid) || isAllowed("Archive All Task",projectUniqid)){ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Archive');?>" onclick="archiveCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>);">
                                            <i class="material-icons">&#xE149;</i>
                                        </a>
                                        <% } %>
                                        <% } }
                                        if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Delete All Task',projectUniqid)) { caseFlag = "delete"; }
                                        if(getdata.isactive == 1){  if(caseFlag == "delete"){ %>
                                        <% if(isAllowed('Delete Task',projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                                        <a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Delete');?>" onclick="deleteCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>, <%= '\'' + getdata.is_recurring + '\'' %>);">
                                            <i class="material-icons">&#xE872;</i>
                                        </a>
                                        <% } %>
                                        <% } } %>
                                </span>
                                <% }  %>
                                <% }  %>
                            </div>
                            <% }  %>
                        <!-- end -->

                        </td>
                        <td class="attach-file-comment" <% if(getTotRep && getTotRep!=0) { %>data-task="<%= caseUniqId %>" id="kanbancasecount<%= count %>" style="cursor:pointer;" <% } %>>
                            <a href="javascript:void(0);" <% if(getdata.format != 1 && getdata.format != 3) { %> style="display:none;" id="fileattch<%= count %>" <% } %>>
                                <i class="glyphicon glyphicon-paperclip"></i>
                            </a>
                            <% if(getTotRep && getTotRep!=0) { %><%= getTotRep %><% } %>
                            <a href="javascript:void(0)" id="repno<%= count %>" style="<% if(!getTotRep || getTotRep==0) { %>display:none<% } %>">
                                <i class="material-icons">&#xE0B9;</i>
                            </a>
                        </td>
                        <% if(isactive==0){ %>
                        <td></td>
                        <% } else { %>
                        <% if(inArray('Assigned to',field_name_arr)){ %>
                        <td class="assi_tlist">
                            <i class="material-icons">&#xE7FD;</i>
                            <% if((projUniq != 'all') && showQuickActiononList){ %>
                            <span id="showUpdAssign<%= caseAutoId %>" <% if(isAllowed('Change Assigned to',projectUniqid)){ %> data-toggle="dropdown" <% } %> title="<%= getdata.asgnName %>" class="clsptr" onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)"><%= getdata.asgnShortName %><span class="due_dt_icn"></span></span>
                            <% } else { %>
                            <span id="showUpdAssign<%= caseAutoId %>" style="cursor:text;text-decoration:none;color:#a7a7a7;"><%= getdata.asgnShortName %></span>
                            <% } %>
                            <% if((projUniq != 'all') && showQuickActiononList){ %>
                            <span id="asgnlod<%= caseAutoId %>" class="asgn_loader">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                            <% } %>
                            <span class="check-drop-icon dsp-block" <% if((projUniq != 'all') && showQuickActiononList){ %> onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)" <% } %>>
                                <span class="dropdown">
                                    <a class="dropdown-toggle" <% if(isAllowed('Change Assigned to',projectUniqid)){ %> data-toggle="<% if((projUniq != 'all') && showQuickActiononList){ %>dropdown<% } %>" href="javascript:void(0);" <% } %> data-target="#">
                                        <% if((projUniq != 'all') && showQuickActiononList){ %>
                                        <i class="material-icons">&#xE5C5;</i>
                                        <% } %>
                                    </a>
                                    <ul class="dropdown-menu asgn_dropdown-caret" id="showAsgnToMem<%= caseAutoId %>">
                                        <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId %>" /></li>
                                    </ul>
                                </span>
                            </span>
                        </td>
                        <% } %>
                        <% } %>



                        <% if(inArray('Priority',field_name_arr)){%>
                        <td class="text-center <% if(getdata.csTdTyp && getdata.csTdTyp[1] != 'Update'){ %>task_priority csm-pad-prior-td<% }else{ %>csm-pad12-prior-td<% } %>">
                            <% var csLgndRep = getdata.legend; %>
                            <% if(getdata.csTdTyp && getdata.csTdTyp[1] == 'Update'){ %>
                            <span class="priority_update priority_high prio_lmh prio-drop-icon" rel="tooltip" title="<?php echo __('Priority'); ?>:<?php echo __('high'); ?>"><?php echo __('High'); ?></span>
                            <% }else{ %>
                            <div style="" id="pridiv<%= caseAutoId %>" data-priority="<%= casePriority %>" class="pri_actions <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> <% if(showQuickActiononList){ %> dropdown<% } %> <% } %>">
                                <div class="dropdown cmn_h_det_arrow lmh-width">
                                    <div class="quick_action resize" <% if(showQuickActiononList){ %> class="quick_action" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" style="cursor:pointer" <% } %> <% } %>><span class="priority_<%= easycase.getPriority(casePriority) %> prio_lmh prio-drop-icon" rel="tooltip" title="<?php echo __('Priority'); ?>:<%= easycase.getPriority(casePriority) %>"><% if(easycase.getPriority(casePriority) == 'high'){ %>High<% }else if(easycase.getPriority(casePriority) == 'medium'){ %>Medium<% }else if(easycase.getPriority(casePriority) == 'urgent'){ %>Urgent<% }else{ %>Low<% }  %></span><i class="tsk-dtail-drop material-icons dropdown_icon_priority">&#xE5C5;</i></div>
                                    <% var csLgndRep = getdata.legend; %>
                                    <% if(showQuickActiononList){ %>
                                    <ul class="dropdown-menu quick_menu">
                                        <li class="low_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+caseAutoId+'\', \'2\', \''+caseUniqId+'\', \''+caseNo+'\'' %>)"><span class="priority-symbol"></span><?php echo __('Low'); ?></a></li>
                                        <li class="medium_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+caseAutoId+'\', \'1\', \''+caseUniqId+'\', \''+caseNo+'\'' %>)"><span class="priority-symbol"></span><?php echo __('Medium'); ?></a></li>
                                        <li class="high_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+caseAutoId+'\', \'0\', \''+caseUniqId+'\', \''+caseNo+'\'' %>)"><span class="priority-symbol"></span><?php echo __('High'); ?></a></li>
                                        <li class="urgent_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+caseAutoId+'\', \'3\', \''+caseUniqId+'\', \''+caseNo+'\'' %>)"><span class="priority-symbol"></span><?php echo __('Urgent'); ?></a></li>
                                    </ul>
                                    <% } %>
                                </div>
                            </div>
                            <span id="prilod<%= caseAutoId %>" style="display:none">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                            <% } %>
                        </td>
                        <% } %>

                        <% if(inArray('Estimated Hours',field_name_arr) && page_hash !=='epics' && page_hash !=='features'){ %>
                            <% if (page_hash === 'tasks' && (getdata.type_id == 13 || getdata.type_id == 15)) { %>
                            <td class="esthrs_dt_tlist" style="text-align:center">
                            <p class="estblist ttc <% if(!isAllowed('Est Hours',projectUniqid)){ %> no-pointer<% } %>" data-split="<%= getdata.is_splitted %>"  id="est_blist<%=caseAutoId%>" case-id-val="<%=caseAutoId%>">
                                <span class="border_dashed">
                                    <% if(caseEstHours) { %> <%= caseEstHours %> <% } else { %><?php echo __('--'); ?><% } %>
                                </span>
                            </p>
                        </td>
                        <% } else{ %>
                        <td class="esthrs_dt_tlist" style="text-align:center">
                            <p class="estblist ttc <% if(!isAllowed('Est Hours',projectUniqid)){ %> no-pointer<% } %>" data-split="<%= getdata.is_splitted %>" style="cursor:pointer;" id="est_blist<%=caseAutoId%>" case-id-val="<%=caseAutoId%>">
                                <span class="border_dashed">
                                    <% if(caseEstHours) { %> <%= caseEstHours %> <% } else { %><?php echo __('None'); ?><% } %>
                                </span>
                            </p>

                            <% var est_time = Math.floor(caseEstHoursRAW/3600)+':'+(Math.round(Math.floor(caseEstHoursRAW%3600)/60)<10?"0":"")+Math.round(Math.floor(caseEstHoursRAW%3600)/60); %>

                            <input type="text" data-est-id="<%=caseAutoId%>" data-est-no="<%=caseNo%>" data-est-uniq="<%=caseUniqId%>" data-est-time="<%=est_time%>" id="est_hrlist<%=caseAutoId%>" class="est_hrlist form-control check_minute_range" style="margin-bottom: 2px;display:none;" maxlength="5" rel="tooltip" title="<?php echo __('You can add time as 1.5(that mean 1 hour and 30 minutes) and press enter to save'); ?>" onkeypress="return numeric_decimal_colon(event)" value="<%= est_time %>" placeholder="hh:mm" data-default-val="<%=est_time%>" />

                            <span id="estlod<%=caseAutoId%>" style="display:none;margin-left:0px;">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                        </td>
                        <% } %>
                        <% } %>
                        <!--  add spenthr start-->
                        <% if(inArray('Spent Hours',field_name_arr) && page_hash !=='epics' && page_hash !=='features'){ %>
                            <% if (page_hash === 'tasks' && (getdata.type_id == 13 || getdata.type_id == 15)) { %>
                            <td class="border-right-td esthrs_dt_tlist text-center">
                            <p style="cursor:auto;">
                                <span>
                                    <% if(caseSpentHrs) { %> <%= caseSpentHrs %> <% } else { %><?php echo __('--'); ?><% } %>
                                </span>
                            </p>
                        </td>
                        <% } else{ %>
                        <td class="border-right-td esthrs_dt_tlist text-center">
                            <p style="cursor:auto;">
                                <span>
                                    <% if(caseSpentHrs) { %> <%= caseSpentHrs %> <% } else { %><?php echo __('None'); ?><% } %>
                                </span>
                            </p>
                        </td>
                        <% } %>
                        <% } %>

                        <% if(inArray('Updated',field_name_arr)){ %>
                        <td class="text-center" title="<% if(getTotRepCnt && getTotRepCnt!=0) { %><?php echo __('updated'); ?><% } else { %><?php echo __('created'); ?><% } %> <?php echo __('by'); ?> <%= getdata.usrShortName %> <% if( getdata.updtedCapDt && getdata.updtedCapDt.indexOf('Today')==-1 && getdata.updtedCapDt.indexOf('Y\'day')==-1) { %><?php echo __('on'); ?><% } %> <%= getdata.updtedCapDt %> <%= getdata.fbstyle %>."><%= getdata.fbstyle %></td>
                        <% } %>
                        <% if(inArray('Status',field_name_arr)){ %>
                        <td>
                            <div class="cs_select_dropdown">
                                <span id="csStsRep<%= count %>" class="cs_select_status">
                                    <% if(isactive==0){ %>
                                    <div class="label new" style="background-color: olive"><?php echo __('Archived'); ?></div>
                                    <%}else if(groupby =='' || groupby !='status'){
                                        if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){ %>
                                    <%= easycase.getCustomStatus(getdata.CustomStatus, getdata.custom_status_id) %>
                                    <% }else{ %>
                                    <%= easycase.getStatus(getdata.type_id, getdata.legend) %>
                                    <% } } %>
                                </span>
                                <% if(caseTypeId != 10){ %>
                                    <% if (page_hash === 'tasks' && getdata.type_id != 13 && getdata.type_id != 15) { %>
                                    <% if(page_hash !=='epics' && page_hash !=='features') { %>
                                    <span class="check-drop-icon dsp-block">
                                    <span class="dropdown cmn_h_det_arrow">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                                            <i class="material-icons">&#xE5C5;</i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <% if(isAllowed("Change Status of Task",projectUniqid)) {
                                                if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){
                                                    $.each(customStatusByProject[getdata.project_id], function (key, data) {
                                                        if(getdata.CustomStatus && getdata.CustomStatus.id != data.id){
                                                            if(data.status_master_id == 3){
                                                                if(isAllowed("Status change except Close",projectUniqid)){
                                            %>
                                            <li onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= caseAutoId %>">
                                                <a href="javascript:void(0);">
                                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                                    <%= data.name %></a>
                                            </li>
                                            <% } }else { %>
                                            <li onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= caseAutoId %>">
                                                <a href="javascript:void(0);">
                                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                                    <%= data.name %></a>
                                                <% } } }); } else { var caseFlag=""; if(caseLegend != 1 && caseTypeId != 10){ caseFlag=9; } if(getdata.isactive == 1){ %>
                                            <li onclick="setNewCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);" id="new<%= caseAutoId %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                                            </li>
                                            <% } if((caseLegend != 2 && caseLegend != 4) && caseTypeId!= 10) { caseFlag=1; } if(getdata.isactive == 1) { %>
                                            <li onclick="startCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);" id="start<%= caseAutoId %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE039;</i>
                                                    <% if(caseLegend == 1){ %>
                                                    <?php echo __('Start'); ?>
                                                    <% } else { %>
                                                    <?php echo __('In Progress'); ?>
                                                    <% } %>
                                                </a>
                                            </li>
                                            <% } if((caseLegend != 5) && caseTypeId!= 10) { caseFlag=2; } if(getdata.isactive == 1){ %>
                                            <li onclick="caseResolve(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);" id="resolve<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                                            </li>
                                            <% } if((caseLegend != 3) && caseTypeId != 10) { caseFlag=5; } if(getdata.isactive == 1){ if(isAllowed("Status change except Close",projectUniqid)){ %>
                                            <li onclick="setCloseCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>);" id="close<%= caseAutoId %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                                                <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                                            </li>
                                            <% }	 } 	}  } %>
                                        </ul>
                                    </span>
                                </span>
                                <% }  %>
                                    <%} %>
                                    <%} %>
                            </div>
                        </td>
                        <% } %>
                        <% if(inArray('Original Due Date',field_name_arr)){ %>
                        <td class="orig_due_dt_tlist">
                            <span class="initial_due">
                                <%= getdata.csDuDtFmtInitial %>
                            </span>
                        </td>
                        <% } %>
                        <% if(inArray('Due Date',field_name_arr)){ %>
                        <td class="due_dt_tlist" data-split="<%= getdata.is_splitted %>">
                            <div class="<% if(getdata.csDueDate == '' || getdata.legend == 5 || getdata.type_id == 10 || getdata.legend == 3){ %> toggle_due_dt <% } %><% if(!inArray('basicdetail',field_name_arr)){ %>set_align<% } %>">
                                <% if(getdata.isactive == 1){ %>
                                <span class="show_dt" id="showUpdDueDate<%= caseAutoId %>" title="<%= getdata.csDuDtFmtT %>">
                                    <%= getdata.csDuDtFmt %>
                                </span>
                                <span id="datelod<%= caseAutoId %>" class="asgn_loader">
                                    <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                                </span>
                                <% } %>
                                <span class="check-drop-icon dsp-block">
                                    <% if(showQuickActiononList){ %>
                                    <span class="dropdown">
                                        <a class="dropdown-toggle" <% if(isAllowed('Update Task Duedate',projectUniqid)){ %> data-toggle="<% if(showQuickActiononList){ %>dropdown<% } %>" href="javascript:void(0);" <% } %> data-target="#">
                                            <i class="material-icons">&#xE5C5;</i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="pop_arrow_new"></li>
                                            <li><a href="javascript:void(0);" onclick="changeCaseDuedate(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>);changeDueDate(<%= '\'' + caseAutoId + '\', \'00/00/0000\', \'No Due Date\', \'' + caseUniqId + '\'' %>)"><?php echo __('No Due Date'); ?></a></li>
                                            <li><a href="javascript:void(0);" onclick="changeCaseDuedate(<%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %>); changeDueDate(<%= '\'' + caseAutoId + '\', \'' + mdyCurCrtd + '\', \'Today\', \'' + caseUniqId + '\'' %>)"><?php echo __('Today'); ?></a></li>
                                            <li><a href="javascript:void(0);" onclick="changeCaseDuedate(<%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %>); changeDueDate(<%= '\'' + caseAutoId + '\', \'' + mdyTomorrow + '\', \'Tomorrow\', \'' + caseUniqId + '\'' %>)"><?php echo __('Tomorrow'); ?></a></li>
                                            <li><a href="javascript:void(0);" onclick="changeCaseDuedate(<%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %>); changeDueDate(<%= '\'' + caseAutoId + '\', \'' + mdyMonday + '\', \'Next Monday\', \'' + caseUniqId + '\'' %>)"><?php echo __('Next Monday'); ?></a></li>
                                            <li><a href="javascript:void(0);" onclick="changeCaseDuedate(<%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %>); changeDueDate(<%= '\'' + caseAutoId + '\', \'' + mdyFriday + '\', \'This Friday\', \'' + caseUniqId + '\'' %>)"><?php echo __('This Friday'); ?></a></li>
                                            <li>
                                                <a href="javascript:void(0);">
                                                    <div class="cstm-dt-option-dtpik prtl">
                                                        <div class="cstm-dt-option" data-csatid="<%= caseAutoId %>" style="position:absolute; left:0px; top:0px; z-index:99999999;">
                                                            <input data-csatid="<%= caseAutoId %>" value="" type="text" id="set_due_date_<%= caseAutoId %>" class="set_due_date hide_corsor" title="<?php echo __('Custom Date'); ?>" style="background:none; border:0px;" />
                                                        </div>
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                        <span style="position:relative;top:2px;"><?php echo __('Custom'); ?>&nbsp;<?php echo __('Date'); ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </span>
                                    <% } %>
                                </span>
                            </div>
                        </td>
                        <% } %>
                        <% if(inArray('Progress',field_name_arr)){ %>
                        <td class="progress_tlist text-center"><%= getdata.completed_task %>%</td>
                        <% } %>

                        <% if(inArray('Custom Field',field_name_arr)){ %>
                        <% for(var c_id in custom_field_ids){ var cstm_val = "no"; if(getdata.custom_fields[custom_field_ids[c_id]]){  if(getdata.custom_fields[custom_field_ids[c_id]]['placeholder'] == "variation" || getdata.custom_fields[custom_field_ids[c_id]]['placeholder'] == "timeBalance" || getdata.custom_fields[custom_field_ids[c_id]]['placeholder'] == "taskDuration"){ var cstm_val = getdata.custom_fields[custom_field_ids[c_id]]['CustomFieldValues']['value']; } } %>
                        <td class="customField_tlist text-center" <% if(cstm_val != 'no' && cstm_val !== 0){ if(getdata.custom_fields[custom_field_ids[c_id]] && cstm_val.toString().charAt(0) == '-'){ %>style="color:#EB7154;" <% }else{ %>style="color:#54EB7B;" <% } } %>>
                            <% if(getdata.custom_fields[custom_field_ids[c_id]]){ %>
                            <% if(getdata.legend != 3 && (getdata.custom_fields[custom_field_ids[c_id]]['placeholder'] == "variation" || getdata.custom_fields[custom_field_ids[c_id]]['placeholder'] == "taskCmplDate")){ %>
                            --
                            <% }else{ %>
                            <%= getdata.custom_fields[custom_field_ids[c_id]]['CustomFieldValues']['value'] %>
                            <% } %>
                            <% }else{ %>
                            --
                            <% } %>
                        </td>
                        <% } %>
                        <% } %>
                    </tr>
                    <% 	totids += caseAutoId + "|"; } } %>

                    <% if(!caseCount || caseCount==0) { var case_type = $("#caseMenuFilters").val(); %>
                    <tr class="empty_task_tr">
                        <td colspan="12" align="center" class="colr_red">
                            <% if(case_type == 'cases' || case_type == '') { 
                                if(filterenabled){ %>
                                <?php echo __('No Task Found'); ?>.
                                <% }else{  
                                    if(!QTAssigns){ %>
                                    <?php echo $this->element('no_data', array('nodata_name' => 'assigntomeproject', 'case_type' => '')); ?>
                                    <% }else{ 
                                        if(page_hash =='epics') { %>
                                        <?php echo $this->element('no_data', array('nodata_name' => 'epics', 'case_type' => '')); ?>
                                        <% } else if(page_hash =='features') { %>
                                        <?php echo $this->element('no_data', array('nodata_name' => 'features', 'case_type' => '')); ?>
                                        <% } else { %>
                                        <?php echo $this->element('no_data', array('nodata_name' => 'tasklist', 'case_type' => '')); ?>
                                        <% } %>
                                    <% } %>
                                <% } %>
                            <% } else if(case_type == 'assigntome'){ if(filterenabled){ %>
                            <?php echo __('No tasks for me'); ?>
                            <% }else{ if(!QTAssigns){ %>
                            <?php echo $this->element('no_data', ['nodata_name' => 'assigntomeproject', 'case_type' => '']); ?>
                            <% }else{ %>
                            <?php echo $this->element('no_data', ['nodata_name' => 'tasklist', 'case_type' => 'assigntome']); ?>
                            <% } %>
                            <% } %>
                            <% }else if(case_type == 'overdue'){ if(filterenabled){ %>
                            <?php echo __('No tasks as overdue'); ?>
                            <% }else{ %>
                            <?php echo $this->element('no_data', ['nodata_name' => 'tasklist', 'case_type' => 'overdue']); ?>
                            <% } %>
                            <% }else if(case_type == 'delegateto'){ if(filterenabled){ %>
                            <?php echo __('No tasks delegated'); ?>
                            <% }else{ %>
                            <?php echo $this->element('no_data', ['nodata_name' => 'tasklist', 'case_type' => 'delegateto']); ?>
                            <% } %>
                            <% }else if(case_type == 'highpriority'){ if(filterenabled){ %>
                            <?php echo __('No high priority tasks'); ?>
                            <% }else{ %>
                            <?php echo $this->element('no_data', ['nodata_name' => 'tasklist', 'case_type' => 'highpriority']); ?>
                            <% } %>
                            <% }else if(case_type == 'favourite'){ if(filterenabled){ %>
                            <?php echo __('No favourite tasks'); ?>
                            <% }else{ %>

                            <?php echo __('No favourite tasks'); ?>
                            <% } %>
                            <% } %>
                        </td>
                    </tr>
                    <% } %>

                <?php /* END Task Rows Section */ ?>
            </tbody>
            <?php /* Task List Table Body */ ?>
        </table>
    </div>
    <?php /* End Task List */ ?>


    <?php /* Pagination */ ?>
    <% $("#task_paginate").html('');
        if(caseCount && caseCount!=0) {
                var pageVars = {pgShLbl:pgShLbl,csPage:csPage,page_limit:page_limit,caseCount:caseCount};
                $("#task_paginate").html(tmpl("paginate_tmpl", pageVars));
        } %>
    <?php /* Pagination End */ ?>

    <?php /* Crete Task Button */ ?>
    <% var canCreateTask = isAllowed('Create Task', projectUniqid);
       if (canCreateTask) { %>
    <div class="crt_task_btn_btm <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT && $_SESSION['KEEP_HOVER_EFFECT'] && (($_SESSION['KEEP_HOVER_EFFECT'] & 8) == 8)) { ?>keep_hover_efct<?php } ?>">
        <span class="hide_tlp_cross" title="<?php echo __('Close'); ?>" onclick="resetHoverEffect(<%= '\'task\'' %>, this);">&times;</span>
        <% if (canCreateTask) { %>
        <div class="os_plus">
            <div class="ctask_ttip">
                <span class="label label-default">
                    <?php echo __('Create Task'); ?>
                </span>
            </div>
            <a href="javascript:void(0)" onclick="creatask();">
                <img src="<?php echo HTTP_ROOT; ?>img/images/creat-task.png" class="ctask_icn" />
                <img src="<?php echo HTTP_ROOT; ?>img/images/plusct.png" class="add_icn" />
            </a>
        </div>
        <% } %>
    </div>
    <% } %>
    <?php /* End Crete Task Button */ ?>
</div>
<% } else if(GrpBy == 'milestone') { %>
    <?php /* @deprecated - Not in use */ ?>
<% } %>
<input type="hidden" name="hid_cs" id="hid_cs" value="<%= count %>" />
<input type="hidden" name="totid" id="totid" value="<%= totids %>" />
<input type="hidden" name="chkID" id="chkID" value="" />
<input type="hidden" name="slctcaseid" id="slctcaseid" value="" />
<input type="hidden" id="getcasecount" value="<%= caseCount %>" readonly="true" />
<input type="hidden" id="openId" value="<%= openId %>" />
<input type="hidden" id="email_arr" value=<%= '\'' + ((typeof email_arr != 'undefined' && email_arr)?email_arr:'') + '\''  %> />
<input type="hidden" id="curr_sel_project_id" value="<% if(projUniq!='all'){%><%= projId %> <% } %>" />
