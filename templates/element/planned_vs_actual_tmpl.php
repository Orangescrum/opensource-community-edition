<div class="plan_vs_actual_list">
    <div class="plans-section">
        <div class="fl left_sec_sub_bar">
            <div class="d-flex align-item-center">
                <h3><?php echo __("Planned Vs Actual"); ?></h3>
                <div class="next_prev ml-15">
                    <a href="javascript:void(0);" id="planned_actual_previous" onclick="changePlannedVsActualFilter(2);"rel="tooltip" title="Previous"><i class="material-icons">navigate_before</i></a>
                    <a href="javascript:void(0);" id="planned_actual_next" onclick="changePlannedVsActualFilter(1);" rel="tooltip" title="Next"><i class="material-icons">navigate_next</i></a>
                </div>
                <div id="Start_date_placeholder" class="ml-15" data-date="<%= startdate %>"><%= date_range %></div>
            </div>
        </div>
        <div class="fr planned_vs_actual_rgt_item">
            <div class="d-flex align-item-center justify-content-center">
    <span class="pfl-icon-dv show_hide_column_filter">
        <span id="showhide_drpdwns" class="dropdown">
        <a href="javascript:jsVoid();" title="<?php echo __('Show/Hide Columns');?>" class="dropdown-toggle" data-toggle="dropdown">
        <i class="material-icons">visibility_off</i> <?php echo __("Show/Hide");?><div class="ripple-container"></div></a>
        <ul class="dropdown-menu drop_menu_mc" id="plan_actual_taskcolumns">
            <li class="li_check_radio">
                <div class="checkbox">
                <label>
                    <input type="checkbox" class="selectedcolss" value="All" id="column_alls"  style="cursor:pointer" onchange="checkboxColumns(this);"> <?php echo __('Show/Hide All');?>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Assigned to',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Assigned to" id="columns_assigned" style="cursor:pointer" onchange="checkboxSingleColumns(this);"><span class="redesign"> <?php echo __('Assigned To');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Task Group',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Task Group" id="columns_group" style="cursor:pointer" onchange="checkboxSingleColumns(this);"><span class="redesign"> <?php echo __('Task Group');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Last Updated',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Last Updated" id="columns_updated" style="cursor:pointer" onchange="checkboxSingleColumns(this);"><span class="redesign"> <?php echo __('Last Updated');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Original Due Date',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Original Due Date" id="columns_orgduedate" style="cursor:pointer" onchange="checkboxSingleColumns(this);"> <span class="redesign"><?php echo __('Original Due Date');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Current Due Date',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Current Due Date" id="columns_crntduedate" style="cursor:pointer" onchange="checkboxSingleColumns(this);"><span class="redesign"> <?php echo __('Current Due Date');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio">
                <div class="checkbox">
                <label class="redesign">
                    <input type="checkbox" <% if(inArray('Difference from Original date',Dynamic_fields)){ %> checked="checked" <% } %> class="selectedcolss" value="Difference from Original date" id="columns_difforgdate" style="cursor:pointer" onchange="checkboxSingleColumns(this);"><span> <?php echo __('Difference from Original date');?></span>
                </label>
                </div>
            </li>
            <li class="li_check_radio checklist_save_btn">
                <div style="text-align:center;">
                <label class="redesign">
                    <input type="button" class="btn btn_cmn_efect cmn_bg btn-info show_btn save_btn" value="<?php echo __('Save');?>" onclick="getAllowedColumnsdata();">
                </label>
                </div>
            </li>
        </ul>
            <!-- Custom code Ends -->
        </span>
    </span>
                <span class="inner_search_span" onclick="slider_inner_project_search(<%= '\'open\'' %>);">
        <i class="inner_search_icon material-icons">&#xE8B6;</i>
        <input type="text" name="search_inner" id="plnd-vs-actl-inner-search" placeholder="<?php echo __('Search');?>" class="inner-search" value=""/>
        <img src="<?php echo HTTP_ROOT; ?>img/images/del.gif" alt="loading" title="<?php echo __('loading');?>" id="srch_inner_load1">
    </span>
                <div class="d-flex report-usser mr-15">
                    <a href="javascript:void(0);" class="report_pg report-singe-user <% if(usr_type != 'null' && usr_type == 'person' ){ %> active <% } %>" data-type="person"title="My tasks" rel="tooltip" onclick="changeReportUser(this);"><i id="report_user_person"class="material-icons defaultcolor">person</i></a>
                    <div class="midle_devider"></div>
                    <a href="javascript:void(0);" class="report_pg report-multiple-user <% if(usr_type == 'null' || usr_type == '' || usr_type == 'group' ){ %> active <% } %>" data-type="group"title="Everyone's tasks" rel="tooltip" onclick="changeReportUser(this);"><i id="report_user_group" class="material-icons defaultcolor">group</i></a>
                </div>
                <div id="planned_vs_actual_filter" class="dropdown">
                    <a href="javascript:void(0);" data-toggle="dropdown">
                        <div class="plned-vs-actualflt">
                            <span class="material-icons calender_month">calendar_month</span>
                            <span style="color: rgb(24 13 13 / 96%);"><% if(localStorage.getItem("plannedVsactual_filter") !== ""){ %><%= localStorage.getItem("plannedVsactual_filter") %><% } else { %><?php echo __("Week"); ?> <% } %></span>
                            <span><i class="material-icons">keyboard_arrow_down</i></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu drop_menu_mc" id="planned_vs_actual_filter_dropdown">
                        <li>
                            <a href="javascript:void(0);" onclick="fetchPlannedVsActualReportView(<%= '\'Week\'' %>,<%= '\'date\'' %>);"><?php echo __("Week"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="fetchPlannedVsActualReportView(<%= '\'Month\'' %>,<%= '\'date\'' %>);"><?php echo __("Month"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="fetchPlannedVsActualReportView(<%= '\'Quater\'' %>,<%= '\'date\'' %>);"><?php echo __("Quarter"); ?></a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="customDateFilter();"><?php echo __("Custom Date"); ?></a>
                        </li>
                        <li>
                            <div class="frto_sch">
                                <div class="form-group">
                                    <input type="text" class="smal_txt form-control " placeholder="<?php echo __('From Date');?>" readonly  id="custom_planned_strtdt" value=""/>
                                    <input type="text" class="smal_txt form-control " placeholder="<?php echo __('To Date');?>" readonly id="custom_planned_enddt" value=""/>
                                    <button class="btn btn-sm btn-raised  btn_cmn_efect cmn_bg btn-info cdate_btn aply_btn" type="button" onclick="setDateCustomRange();" id="btn_timelog_search"><?php echo __('Apply');?></button>
                                </div>
                            </div>
                        </li>
                    </ul>

                </div>


            </div>

            <div style="display:none" id="plnLoader" class="loader"><img src="<?php echo HTTP_ROOT; ?>images/rolling.gif"></div>
        </div>
        <div class="cb"></div>
    </div>
    <div id="proj_filtered_items" class="filter_tag_items"></div>
    <% if(Tasks.length > 0) { %>
    <table class="table table-striped custom-datatable table-hover">
        <thead>
        <tr>
            <th class="text-left" style="width:17%;"><?php echo __("Task Title"); ?></th>
            <th class="text-center"><?php echo __("Project"); ?></th>
            <% if (inArray('Assigned to', Dynamic_fields)) {%>
            <th class="text-center"><?php echo __("Assigned to"); ?></th>
            <% }else{ console.log('not');} %>
            <% if (inArray('Task Group', Dynamic_fields)) {%>
            <th class="text-center"><?php echo __("Task Group"); ?></th>
            <% } %>
            <% if (inArray('Last Updated', Dynamic_fields)) {%>
            <th class="text-center"><?php echo __("Last Updated"); ?></th>
            <% } %>
            <% if (inArray('Original Due Date', Dynamic_fields)) {%>
            <th class="text-center"><?php echo __("Original Due Date"); ?></th>
            <% } %>
            <% if (inArray('Current Due Date', Dynamic_fields)) {%>
            <th class="text-center"><?php echo __("Current Due Date"); ?></th>
            <% } %>
            <% if (inArray('Difference from Original date', Dynamic_fields)) {%>
            <th class="text-center" style="width:11%;">
                    <span><?php echo __("Difference from Original date"); ?><span>
                    <a href="javascript:void(0);" class="onboard_help_anchor"  rel="tooltip_differnce" title="<%= Difference_tooltip %>"><span class="help-icon"></span></a>
            </th>
            <% } %>
            <th class="text-center">
                <span><?php echo __("Status"); ?></span>
                <a href="javascript:void(0);" class="onboard_help_anchor"  rel="tooltip" title="<%= status_tooltip %>"><span class="help-icon"></span></a>
            </th>
        </tr>
        </thead>
        <tbody>
        <%
        for(var key in Tasks){
        var task_lists = Tasks[key];
        var task_name = task_lists.Easycase.title;
        var project_name = task_lists.Project.name;
        var username = task_lists.AssignUser.name;
        var project_short_name = task_lists.Project.short_name; %>
        <tr>
            <td class="text-left">#<%= task_lists.Easycase.case_no %>: <%= task_name %></td>
            <td class="text-center" title="<%= project_name %>"><%= project_short_name %></td>
            <% if (inArray('Assigned to', Dynamic_fields)) {%>
            <%if(username){
            if(task_lists.AssignUser.photo){ %>
            <td class="text-center">
                <img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= task_lists.AssignUser.photo %>&sizex=30&sizey=30&quality=100" class="lazy user_pfl" title="<%= username %>" width="30" height="30" />
            </td>
            <% }else{  var usr_name_fst = username.charAt(0); %>
            <td class="text-center ">
                                    <span class="cmn_profile_holder <%= task_lists.prflBg %>" title="<%= username %>">
                                        <%= usr_name_fst %>
                                    </span>
            </td>
            <% }
            }else{ %>
            <td class="text-center "><?php echo __("unassigned"); ?></td>
            <%  } }
            if (inArray('Task Group', Dynamic_fields)) {
            if(task_lists.Milestone.title !== null){ %>
            <td class="text-center "> <%= task_lists.Milestone.title %></td>
            <%  } else { %>
            <td class="text-center "><?php echo __("Default Task Group"); ?></td>
            <%  }} %>
            <% if (inArray('Last Updated', Dynamic_fields)) {%>
            <td class="text-center "><%= task_lists.last_upddtm %></td>
            <% } %>
            <% if (inArray('Original Due Date', Dynamic_fields)) {%>
            <td class="text-center "><%= task_lists.ori_duedate %></td>
            <% } %>
            <% if (inArray('Current Due Date', Dynamic_fields)) {%>
            <td class="text-center "><%= task_lists.current_duedate %></td>
            <% } %>
            <% if (inArray('Difference from Original date', Dynamic_fields)) {%>
            <td class="text-center "><%= task_lists.difference %></td>
            <% } %>

            <% if(task_lists.completed_date != ""){ %>
            <td class="text-center" style="text-align: -webkit-center;">
                <div style="width:210px;padding:10px;background-color:#d1ebd1">
                    <span class="planed_status_cmpl" style="color: #128912"><?php echo __("Completed"); ?></span>
                    <span class="planned_hour"><%= task_lists.completed_date %></span>
                </div>
            </td>
            <% } else { 
                if(task_lists.status_ovr_lft == "left") { %>
            <td class="text-center "  style="text-align: -webkit-center;">
                <div style="width:210px;padding:10px;background-color:#dfc03a75">
                    <span class="planed_status"style="color:#5a5a04d9;"><?php echo __("Upcoming"); ?></span>
                    <span class="planned_hour"><%= task_lists.status %></span>
                </div>
            </td>
            <% } else if(task_lists.status_ovr_lft == "over"){ %>
            <td class="text-center "  style="text-align: -webkit-center;">
                <div style="width:210px;padding:10px;background-color:#dd0f0f57">
                    <span class="planed_status"style="color:#8b2a2ad4;"><?php echo __("Late"); ?></span>
                    <span class="planned_hour"><%= task_lists.status %></span>
                </div>
            </td>
            <% } else { %>
                <td class="text-center "  style="text-align: -webkit-center;">
                    <span class="planned_hour"><%= task_lists.status %></span>
                </td>
            <% }
             } %>
        </tr>

        <% }
        %>
        </tbody>
    </table>
    <%
    if (caseCount) {
    $("#planned_vs_actual_paginate").html('');
    if(caseCount && caseCount!=0) {
    var pageVars = {pgShLbl:pgShLbl,csPage:csPage,page_limit:page_limit,caseCount:caseCount};
    $("#planned_vs_actual_paginate").html(tmpl("planned_vs_actual_paginate_tmpl", pageVars));
    }
    } %>
    <% } else {  $("#planned_vs_actual_paginate").html(''); %>
    <div class="no-data-box extra mtop15">
        <img src="<?php echo HTTP_ROOT;?>img/no-data/notask.png" width="150" height="150" />
        <div class="text-center mtop15">
            <p class="sub-head"><?php echo __('No tasks found');?></p>
        </div>
    </div>
    <% } %>
</div>
