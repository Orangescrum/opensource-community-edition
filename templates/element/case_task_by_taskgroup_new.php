<% var sb_show = {
    assign:   inArray('Assigned to', field_name_arr)   || inArray('All', field_name_arr),
    estHours: inArray('Estimated Hours', field_name_arr) || inArray('All', field_name_arr),
    status:   inArray('Status', field_name_arr)        || inArray('All', field_name_arr),
    dueDate:  inArray('Due Date', field_name_arr)      || inArray('All', field_name_arr),
    progress: inArray('Progress', field_name_arr)      || inArray('All', field_name_arr)
}; %>
<% if(casePage == 1){ %>
<style>
    <% if(!sb_show.assign){ %>   .sb-tg-assign   { display: none; } <% } %>
    <% if(!sb_show.estHours){ %> .sb-tg-esthours { display: none; } <% } %>
    <% if(!sb_show.status){ %>   .sb-tg-status   { display: none; } <% } %>
    <% if(!sb_show.dueDate){ %>  .sb-tg-duedate  { display: none; } <% } %>
    <% if(!sb_show.progress){ %> .sb-tg-progress { display: none; } <% } %>
    #groupby_drpdwn_subtask > a.group_by_anchor {
        background: var(--primary) !important;
        color: var(--white) !important;
        border-color: var(--primary) !important;
    }
</style>
<style>
    .task_listing table.table td.attach-file-comment:hover,
    .attach-file-comment:hover a .material-icons {
        color: #2d6dc4
    }
</style>

<div class="slide_switch_container pr d-flex">
    <?php echo $this->element('milestone_list_view'); ?>
    <div class="task_listing subtask_listing task-grouping-page task_subtask_group_listing switch_listing">
        <div id="widgethideshow" class="fl task-list-progress-bar fix-status-widget pr">
            <span id="task_count_of" style="float:left;display:block;"></span>
            <span class="pr fl inner_search_span" onclick="slider_inner_search(<%= '\'open\'' %>);">
                <i class="material-icons clear_close_icon" title="<?php echo __('Clear search'); ?>" id="clear_close_icon" onclick="clearSearch(<%= '\'inner\'' %>);">close</i>
                <i class="inner_search_icon material-icons subtask_view">&#xE8B6;</i>
                <input type="text" name="search_inner" id="inner-search" placeholder="<?php echo __('Search'); ?>" class="inner-search subtask_view_search" value="<%=caseSrch%>" />
                <img src="<?php echo HTTP_ROOT; ?>img/images/del.gif" alt="loading" title="<?php echo __('loading'); ?>" id="srch_inner_load1">
                <div id="ajax_inner_search" class="ajx-srch-inner-dv1"></div>
            </span>
            <div class="view_list_refresh" id="task_view_types">
                <span class="reload_icon">
                    <a class="" href="javascript:void(0);" onclick="reloadTasks();">
                        <span title="<?php echo __('Reload'); ?>" rel="tooltip"><i class="material-icons">&#xE5D5;</i></span>
                    </a>
                </span>
                <?php if (!defined('USE_SCRUM_PLUGIN_BOARD') || USE_SCRUM_PLUGIN_BOARD !== '1') { ?>
                    <span class="action_dropmenu n_tsk_grp_bkp backlog_setting fr">
                        <a class="main_page_menu_togl dropdown-toggle active" data-toggle="dropdown" href="javascript:void(0);" data-target="#"><i class="material-icons" style="padding-top: 1px; padding-bottom: 1px;">&#xE5D4;</i></a>
                        <ul class="dropdown-menu sett_dropdown-caret aede-drop-text bklog_rt">
                            <li class="makeHover">
                                <a href="javascript:void(0);" onclick="switchToScrumPluginView(<%= '\'board\'' %>);" data-prj-name="<?php echo __('Try new Task Groups Page'); ?>">
                                    <i class="material-icons">&#xE89C;</i><?php echo __('Try new Task Groups Page (beta)'); ?>
                                </a>
                            </li>
                        </ul>
                    </span>
                <?php } ?>
                <div class="cb"></div>
            </div>
            <span id="ajaxCaseStatus" style="float:right;margin-top:7px; margin-right:-10px;"></span>

            <span class="pfl-icon-dv show_hide_column_filter">
                <span id="showhide_drpdwn_subtask" class="dropdown">
                    <a href="javascript:jsVoid();" title="<?php echo __('Show/Hide Columns'); ?>" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="material-icons">visibility_off</i> <?php echo __("Show/Hide"); ?><div class="ripple-container"></div>
                    </a>
                    <ul class="dropdown-menu drop_menu_mc" onclick="event.stopPropagation();">
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('All',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="All" id="column_all" style="cursor:pointer" onchange="checkboxColumn(this);"> <?php echo __('Show/Hide All'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Assigned to',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="Assigned to" id="column_assigned" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Assigned To'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Estimated Hours',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="Estimated Hours" id="column_estimatedhours" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Est. Hours'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Status',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="Status" id="column_status" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Status'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Due Date',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="Due Date" id="column_duedate" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('Due Date'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" <% if(inArray('Progress',field_name_arr)){ %> checked="checked" <% } %> class="selectedcols show_hide_selectedcols" value="Progress" id="column_progress" style="cursor:pointer" onchange="checkboxSingleColumn(this);"> <?php echo __('% Completion'); ?>
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
                </span>
                <span id="groupby_drpdwn_subtask" class="dropdown">
                    <a href="javascript:jsVoid();" title="<?php echo __('Group By (applies to parent tasks only)'); ?>" class="dropdown-toggle group_by_anchor" data-toggle="dropdown" style="padding: 4px;">
                        <i class="material-icons">group_work</i> <?php echo __("Group By"); ?><div class="ripple-container"></div>
                    </a>
                    <ul class="dropdown-menu drop_menu_mc" onclick="event.stopPropagation();">
                        <% var __cgb = (typeof casegroupby !== 'undefined' && casegroupby) ? casegroupby : 'None'; %>
                        <li class="li_check_radio">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="subtaskGroupBy" value="None"      <% if(__cgb=='None'){      %> checked="checked" <% } %> onchange="setSubtaskGroupBy(this.value);"> <?php echo __('None'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="subtaskGroupBy" value="Date"      <% if(__cgb=='Date'){      %> checked="checked" <% } %> onchange="setSubtaskGroupBy(this.value);"> <?php echo __('Updated Date'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="subtaskGroupBy" value="Assign to" <% if(__cgb=='Assign to'){ %> checked="checked" <% } %> onchange="setSubtaskGroupBy(this.value);"> <?php echo __('Assign To'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="subtaskGroupBy" value="Status"    <% if(__cgb=='Status'){    %> checked="checked" <% } %> onchange="setSubtaskGroupBy(this.value);"> <?php echo __('Status'); ?>
                                </label>
                            </div>
                        </li>
                        <li class="li_check_radio">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="subtaskGroupBy" value="Priority"  <% if(__cgb=='Priority'){  %> checked="checked" <% } %> onchange="setSubtaskGroupBy(this.value);"> <?php echo __('Priority'); ?>
                                </label>
                            </div>
                        </li>
                    </ul>
                </span>
                <% $.each(field_name_arr, function(idx, name){
                    if(name !== 'All' && name !== 'Assigned to' && name !== 'Estimated Hours' && name !== 'Status' && name !== 'Due Date' && name !== 'Progress'){ %>
                    <input type="checkbox" class="show_hide_selectedcols" value="<%= name %>" checked="checked" style="display:none;">
                <% } }); %>
            </span>

            <span style="cursor:pointer;">
                <div class="showOverDueTask overdue_task_span" onclick="showOverDueTask();"><?php echo __("Overdue Tasks")  ?> : <%= over_due_task_count %></div>
            </span>
            <div class="cb"></div>
        </div>

        <div class="milestone_filter_active">
            <div class="mil_title"><?php echo __('Task Group'); ?>: <span id="milestone_filter_active_name"></span></div>
            <div class="reset_mil"><i class="material-icons" rel="tooltip" title="Reset" onclick="clearSubtakMilFiletr();">close</i></div>
        </div>

        <div class="task-m-overflow cstm_responsive_tbl min-height-400">
            <table class="table table-striped table-hover subtsk_list_tbl" style="border-collapse: separate;">
                <thead>
                    <tr>
                        <th class="porl checkbox_th wth_1">
                            <div class="pr">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="" class="chkAllTsk" id="chkAllTsk">
                                    </label>
                                </div>
                                <div class="drop_th_ttl">
                                    <span class="dropdown custom_th_drdown">
                                        <a class="dropdown-toggle mass_action_dpdwn" data-toggle="dropdown" href="javascript:void(0);">
                                            <i title="Choose at least one task" rel="tooltip" class="material-icons custom-dropdown">&#xE5C5;</i>
                                        </a>
                                        <ul class="dropdown-menu" id="dropdown_menu_chk">
                                            <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                                                <% if(typeof curProjId != "undefined" && typeof curProjId != "null" &&  typeof customStatusByProject !="undefined" && typeof customStatusByProject[curProjId] !='undefined' && customStatusByProject[curProjId] != null){ $.each(customStatusByProject[curProjId], function (key, data) { %>
                                                <% if(data.status_master_id == 3){ %>
                                                <% if(isAllowed("Status change except Close",projUniq)){ %>
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
                                                        <%= data.name %>
                                                    </a>
                                                </li>
                                                <% } %>
                                                <% }); %>
                                                <% }else{ %>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseNew\'' %>)"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseStart\'' %>)"><i class="material-icons">&#xE039;</i><?php echo __('Start'); ?></a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseResolve\'' %>)"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                                                </li>
                                                <?php if ($this->Format->isAllowed('Status change except Close', $roleAccess)) { ?>
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="multipleCaseAction(<%= '\'caseId\'' %>)"><i class="material-icons">&#xE5CD;</i><?php echo __('Close'); ?></a>
                                                    </li>
                                                <?php } ?>
                                                <% } %>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Move to Project', $roleAccess)) { ?>
                                                <li id="mvTaskToProj">
                                                    <a href="javascript:void(0);" onclick="mvtoProject( <%= '\' \'' %> , <%= '\' \'' %> , <%= '\'movetop\'' %> )"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to project'); ?></a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                                <li id="cpTaskToProj">
                                                    <a href="javascript:void(0);" onclick="cptoProject( <%= '\' \'' %> , <%= '\' \'' %> , <%= '\'movetop\'' %> )"><i class="material-icons">&#xE14D;</i><?php echo __('Copy to Project'); ?></a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Move to Milestone', $roleAccess)) { ?>
                                                <li id="mvTskToTgrp">
                                                    <a href="javascript:void(0);" onclick="moveTaskToTaskGroup(<%= '\'all\'' %>);"><i class="material-icons">&#xE89F;</i><?php echo __('Move to Task Group'); ?></a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                                            <?php }  ?>
                                            <?php if (SES_TYPE == 1 || SES_TYPE == 2 || $this->Format->isAllowed('Archive All Task', $roleAccess)) { ?>
                                                <?php //if($this->Format->isAllowed('Archive Task',$roleAccess) || $this->Format->isAllowed('Archive All Task',$roleAccess)){ 
                                                ?>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="archiveCase( <%= '\'all\'' %> )"><i class="material-icons">&#xE149;</i><?php echo __('Archive'); ?></a>
                                                </li>
                                                <?php //} 
                                                ?>
                                            <?php } ?>
                                            <?php if (SES_TYPE == 1 || SES_TYPE == 2 || $this->Format->isAllowed('Delete All Task', $roleAccess)) { ?>
                                                <li id="delAllTsks">
                                                    <a href="javascript:void(0);" onclick="DeleteAllCase( <%= '\'all\'' %> )"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </span>
                                </div>
                            </div>
                        </th>
                        <th class="wth_2"></th>
                        <th class="wth_2">
                            <a href="javascript:void(0);" title="<?php echo __('Task'); ?>#" class="sortcaseno" onclick="treeSortBy(<%= '\'caseno\'' %>, this);">
                                #
                                <span class="sorting_arw">
                                    <% if(typeof csNum != 'undefined' && csNum){ %>
                                        <% if(csNum == 'asc'){ %><i class="material-icons tsk_asc">&#xE5CE;</i><% } else { %><i class="material-icons tsk_desc">&#xE5CF;</i><% } %>
                                    <% } else { %><i class="material-icons">&#xE164;</i><% } %>
                                </span>
                            </a>
                        </th>
                        <th class="wth_4">
                            <span id="shortingByTitle" class="dropdown" style="display: inline;">
                                <a class="sorttitle" href="javascript:void(0);" title="<?php echo __('Title'); ?>" onclick="treeSortBy(<%= '\'title\'' %>, this);">
                                    <?php echo __('Title'); ?>
                                    <span class="sorting_arw">
                                        <% if(typeof csTtl != 'undefined' && csTtl){ %>
                                            <% if(csTtl == 'asc'){ %><i class="material-icons tsk_asc">&#xE5CE;</i><% } else { %><i class="material-icons tsk_desc">&#xE5CF;</i><% } %>
                                        <% } else { %><i class="material-icons">&#xE164;</i><% } %>
                                    </span>
                                </a>
                            </span>
                        </th>
                        <th class="wth_3"></th>
                        <th class="width_assign wth_6 sb-tg-assign">
                            <a class="sortcaseAt" href="javascript:void(0);" title="<?php echo __('Assigned to'); ?>" onclick="treeSortBy(<%= '\'caseAt\'' %>, this);">
                                <?php echo __('Assigned to'); ?>
                                <span class="sorting_arw">
                                    <% if(typeof csAtSrt != 'undefined' && csAtSrt){ %>
                                        <% if(csAtSrt == 'asc'){ %><i class="material-icons tsk_asc">&#xE5CE;</i><% } else { %><i class="material-icons tsk_desc">&#xE5CF;</i><% } %>
                                    <% } else { %><i class="material-icons">&#xE164;</i><% } %>
                                </span>
                            </a>
                        </th>
                        <th class="tsk_est_hours wth_7 text-center sb-tg-esthours">
                            <a class="sortestimatedhours" href="javascript:void(0);" title="<?php echo __('Est. Hours'); ?>" >
                                <?php echo __('Est. Hours'); ?>
                            </a>
                        </th>
                        <th class="width_status text-center wth_9 sb-tg-status"><?php echo __('Status'); ?></th>
                        <th class="tsk_due_dt wth_10 sb-tg-duedate">
                            <a class="sortduedate" href="javascript:void(0);" title="<?php echo __('Due Date'); ?>" onclick="treeSortBy(<%= '\'duedate\'' %>, this);">
                                <?php echo __('Due Date'); ?>
                                <span class="sorting_arw">
                                    <% if(typeof csDuDt != 'undefined' && csDuDt){ %>
                                        <% if(csDuDt == 'asc'){ %><i class="material-icons tsk_asc">&#xE5CE;</i><% } else { %><i class="material-icons tsk_desc">&#xE5CF;</i><% } %>
                                    <% } else { %><i class="material-icons">&#xE164;</i><% } %>
                                </span>
                            </a>
                        </th>
                        <th class="width_progress text-center wth_9 sb-tg-progress">
                            <span class="progresselipsis"><?php echo __('% Completion'); ?></span>
                        </th>
                    </tr>
                </thead>
                <tbody id="subtaskListBody">
                <% } %>
                <!-- Task List Rows -->
                <?php echo $this->element('sub_task_rows'); ?>
                <!-- Rows End -->
                <% if(casePage == 1) { %>
                </tbody>
            </table>
            <div class="text-center">
                <a href="javascript:void(0);" id="subtask-load-more" class="btn btn-primary"><?php echo __("Load More Task"); ?></a>
            </div>
        </div>
        <% $("#task_paginate").html(''); %>
        <div class="crt_task_btn_btm <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT && $_SESSION['KEEP_HOVER_EFFECT'] && (($_SESSION['KEEP_HOVER_EFFECT'] & 8) == 8)) { ?>keep_hover_efct<?php } ?> num_2">
            <span class="hide_tlp_cross" title="<?php echo __('Close'); ?>" onclick="resetHoverEffect(<%= '\'task\'' %>, this);">&times;</span>
            <div class="pr">
                <?php if ($this->Format->isAllowed('Create Milestone', $roleAccess)) { ?>
                    <div class="os_plus ctg_btn">
                        <div class="ctask_ttip">
                            <span class="label label-default"><?php echo __('Create Task Group'); ?></span>
                        </div>
                        <a href="javascript:void(0)" onclick="addEditMilestone(<%= '\' \'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\' \'' %>);">
                            <i class="material-icons">&#xE065;</i>
                        </a>
                    </div>
                <?php } ?>
            </div>
            <?php if ($this->Format->isAllowed('Create Task', $roleAccess) || SES_TYPE == 1) { ?>
                <div class="os_plus">
                    <div class="ctask_ttip">
                        <span class="label label-default"><?php echo __('Create Task'); ?></span>
                    </div>
                    <a href="javascript:void(0)" onclick="creatask();">
                        <img src="<?php echo HTTP_ROOT; ?>img/images/creat-task.png" class="ctask_icn" />
                        <img src="<?php echo HTTP_ROOT; ?>img/images/plusct.png" class="add_icn" />
                    </a>
                </div>
            <?php } ?>
        </div>
        <div class="cb"></div>
        <input type="hidden" name="hid_cs" id="hid_cs" value="" />
        <input type="hidden" name="totid" id="totid" value="" />
        <input type="hidden" name="chkID" id="chkID" value="" />
        <input type="hidden" name="slctcaseid" id="slctcaseid" value="" />
        <input type="hidden" id="getcasecount" value="" readonly="true" />
        <input type="hidden" id="openId" value="" />
        <input type="hidden" id="email_arr" value="" />
        <input type="hidden" id="curr_sel_project_id" value="<%= curProjId %>">
        <input type="hidden" id="displayedTaskGroups" value="20">
        <input type="hidden" id="totalTaskGroups" value="<%= caseCount %>">
    </div>
</div>
<% } %>