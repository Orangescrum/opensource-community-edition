<div class="sec_title d-flex tog" data-cmnt_id="subtask_sec">
    <div class="heading_title">
        <span class="sec_icon <%= type === 'story' ? 'story_icon' : (type === 'task' ? 'subtask_icon' : ({ [original_epic_id]: 'feature_icon', [original_feature_id]: 'story_icon' }[csTypRep] || 'subtask_icon')) %>"></span>
        <h3>
            <%= type === 'story' ? '<?php echo __('Stories'); ?>' : (type === 'task' ? '<?php echo __('Tasks'); ?>' : ({ [original_epic_id]: '<?php echo __('Features'); ?>', [original_feature_id]: '<?php echo __('Stories'); ?>' }[csTypRep] || '<?php echo __('Subtasks'); ?>')) %>
        </h3>
    </div>
    <div class="icon_collapse"></div>
</div>

<div class="toggle_details">
    <div class="d-flex sec_action_item">
        <div class="ml-auto d-flex">
            <% if(is_inactive_case == 0 && is_active == 1) { %>
            <div class="cursor link-icon">
                <a class="anchor" rel="tooltip" title="<%= type === 'story' ? '<?php echo __('Reload Stories'); ?>' : (type === 'task' ? '<?php echo __('Reload Tasks'); ?>' : ({ [original_epic_id]: '<?php echo __('Reload Features'); ?>', [original_feature_id]: '<?php echo __('Reload Stories'); ?>' }[csTypRep] || '<?php echo __('Reload Subtask'); ?>')) %>" onclick="loadSubtaskInDetail(<%= '\'' + csAtId + '\'' %>);">
                    <span class="reload_icon"><i class="material-icons">autorenew</i></span>
                </a>
            </div>
            <?php if($this->Format->isAllowed('Create Task',$roleAccess)){ ?>
                <div class="cursor link-icon ml-10" rel="tooltip" title="<%= type === 'story' ? '<?php echo __('Create Story'); ?>' : (type === 'task' ? '<?php echo __('Create Task'); ?>' : '<?php echo __('Create Subtask'); ?>') %>" 	
                    <%
                    var ptask_type = type === 'story' ? '<?php echo __('Story'); ?>' : (type === 'task' ? '<?php echo __('Task'); ?>' : ({ [original_epic_id]: '<?php echo __('Feature'); ?>', [original_feature_id]: '<?php echo __('Story'); ?>' }[csTypRep] || '<?php echo __('Subtask'); ?>'));
                    var ptask_type_key = type === 'story' ? 'story' : (type === 'task' ? 'task' : ({ [original_epic_id]: 'feature', [original_feature_id]: 'story' }[csTypRep] || 'subtask'));
                    if(is_inactive_case == 0 && is_active == 1) {%>  onclick="addSubtaskPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csAtId + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'Title\'' %>,<%= '\'' + ptask_type + '\'' %>,<%= '\'' + ptask_type_key + '\'' %>);" <% } %> >
                <i class="material-icons">&#xE145;</i> 
                <%= type === 'story' ? '<?php echo __('Add New Story'); ?>' : (type === 'task' ? '<?php echo __('Add New Task'); ?>' : ({ [original_epic_id]: '<?php echo __('Add New Feature'); ?>', [original_feature_id]: '<?php echo __('Add New Story'); ?>' }[csTypRep] || '<?php echo __('Add New Subtask'); ?>')) %>
                </div>
            <?php } ?>            
            <% } %>
        </div>
    </div>
<% if(subtasks.length > 0){ var caseParenTUID = csUniqId; %>
<div class="detail_list_table mtop20">
    <table class="width-100-per layout_fixed">
        <thead>
        <tr>
            <th class="width-10-per case_no"><?php echo __('Task#'); ?></th>
            <th class="width-30-per title count_ttl_td"><?php echo __('Title'); ?></th>
            <th class="width-20-per status_td"><?php echo __('Status'); ?></th>
            <th class="width-20-per assigned_user"><?php echo __('Assigned to'); ?></th>
            <th class="width-15-per due"><?php echo __('Due Date'); ?></th>
            <th class="width-5-per actions"></th>

        </tr>
        </thead>
        <tbody>
        <%
        var count=0;
        for(var sKey in subtasks){
        var getdata = subtasks[sKey].Easycases ? subtasks[sKey].Easycases : (subtasks[sKey].Easycase ? subtasks[sKey].Easycase : subtasks[sKey]);
        var getdatas = subtasks[sKey].Easycases ? subtasks[sKey].Easycases : (subtasks[sKey].Easycase ? subtasks[sKey].Easycase : subtasks[sKey]);
        var username = subtasks[sKey].User.Name ? subtasks[sKey].User.Name : (subtasks[sKey].User.name ? subtasks[sKey].User.name : (subtasks[sKey].User.Fullname ? subtasks[sKey].User.Fullname : ''));
        var caseAutoId = getdata.id;
        var caseUniqId = getdata.uniq_id;
        var caseNo = getdata.case_no;
        var caseUserId = getdata.user_id;
        var caseTypeId = getdata.type_id;
        var projId = getdata.project_id;
        var caseLegend = getdata.legend;
        var casePriority = getdata.priority;
        var caseFormat = getdata.format;
        var caseTitle = getdata.title;
        var isactive = getdata.isactive;
        var caseAssgnUid = getdata.assign_to;
        var projectUniqid=getdata.proj_uniq_to;
        var projectName ='';
        var getTotRep = 0;
        if(getdata.thread_count && getdata.thread_count!=0) {
        getTotRep = getdata.thread_count;
        }
        count++;
        %>
        <%
        var task_type_sub = '';
        for(var k in GLOBALS_TYPE) {
        if(isDisplayEpicType(GLOBALS_TYPE[k].Type.name)){
        var v = GLOBALS_TYPE[k];
        if(v.Type.id == getdata.type_id){
        task_type_sub = v.Type.name;
        }
        }
        } %>
        <tr>
            <td class="width-10-per">
                <div><%= getdata.case_no%></div>
            </td>
            <td class="width-30-per">
                <div><span id="titlehtml<%= count %>-sub" data-task="<%= getdata.uniq_id %>">
						<a href="javascript:void(0);" class="link-text" data-task="<%= getdata.uniq_id %>" data-task-id="<%= getdata.uniq_id %>">
							<%= formatText((getdata.title)) %>
						</a>
						</span> </div>
            </td>



            <td class="width-20-per">
                <div class="cs_select_dropdown" style="width: 90px;">
					<span class="cs_select_status">
						<% if(groupby =='' || groupby !='status'){%>
								<% if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){ %>
									<div class="new" style="width: 20px;"><%= easycase.getCustomStatus(getdata.CustomStatus, getdata.custom_status_id) %></div>
								<% }else{ %>
								<% if(getdata.legend == 3){ var cl = 'closed'}else if(getdata.legend == 2){ var cl = 'wip' }else{ var cl = 'new' } %>
									<div class="<%= cl%>"style="width: 20px;"><%= easycase.getStatus(getdata.type_id, getdata.legend) %></div>
								<% } %>
							<% } %>
					</span>
                    <span class="check-drop-icon dsp-block" style="visibility: visible;">
						<span class="dropdown">
						<a class="dropdown-toggle"  data-toggle="dropdown" href="javascript:void(0);" data-target="#">
							<i class="material-icons">&#xE5C5;</i>
						</a>
						<ul class="dropdown-menu">
						<% if(isAllowed('Change Status of Task',projectUniqid)){ %>
							<% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[projId] !='undefined' && customStatusByProject[projId] != null){
											if(getdata.isactive == 1){
						$.each(customStatusByProject[projId], function (key, data) {
												if(getdata.CustomStatus == null || getdata.CustomStatus.id != data.id){
											%>
											<li onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>,0,<%= '\'sub\'' %>);" id="new<%= caseAutoId %>">
												<a href="javascript:void(0);"><span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %></a>
												</li>
											<%   }
												});
						}
										} else{ %>
										<% var caseFlag="";
											if(caseLegend != 1 && caseTypeId != 10){ caseFlag=9; }
											if(getdata.isactive == 1){ %>
												<li onclick="setNewCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,0,<%= '\'sub\'' %>);" id="new<%= caseAutoId %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
													<a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                            </li>
                            <% } %>
											<% if((caseLegend != 2 && caseLegend != 4) && caseTypeId!= 10) { caseFlag=1; }
											if(getdata.isactive == 1) { %>
												<li onclick="startCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,0, <%= '\'sub\'' %>);" id="start<%= caseAutoId %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
													<a href="javascript:void(0);"><i class="material-icons">&#xE039;</i><% if(caseLegend == 1){ %><?php echo __('Start'); ?><% }else{ %><?php echo __('In Progress'); ?><% } %></a>
                            </li>
                            <% } %>
											<% if((caseLegend != 5) && caseTypeId!= 10) { caseFlag=2; }
											if(getdata.isactive == 1){ %>
											<li onclick="caseResolve(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,0, <%= '\'sub\'' %>);" id="resolve<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
												<a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
											</li>
											<% }
											if((caseLegend != 3) && caseTypeId != 10) { caseFlag=5; }
											if(getdata.isactive == 1){ %>
											<% if(isAllowed('Status change except Close',projectUniqid)){ %>
												<input type="hidden" value="<%= caseParenTUID%>" id="closecase_id<%= caseAutoId%>" />
												<li onclick="setCloseCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,0,<%= '\'sub\'' %>);" id="close<%= caseAutoId %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
													<a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
												</li>
											<% } %>
											<% } %>
											<% } %>
						<% } %>
						</ul>
						</span>
					</span>
                </div>
            </td>

            <td class="width-20-per">
                <div rel="tooltip" title="<%= (username) ? username : '<?php echo __('unassigned'); ?>' %>"style="width:fit-content;">
                    <%= (username) ? username.trim().split(/\s+/)[0] : '<?php echo __('unassigned'); ?>' %>
                </div>
            </td>
            <td class="width-15-per"><%= getdata.due_date!="" ? formatDate('MMM DD, YYYY',getdata.due_date) : "---" %></td>
            <td class="width-5-per">
                <% if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Delete All Task',projectUniqid)) { caseFlag = "delete"; }
                if(getdata.isactive == 1){ %>
                <% if(isAllowed('Delete Task',projectUniqid) || isAllowed('Delete All Task',projectUniqid)){ %>
                <div class="cursor link-icon" onclick="deleteCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>, 0, <%= '\'sub\'' %>);" title="<?php echo __('Delete Task'); ?>">
                    <i class="material-icons delete_icon">delete_outline</i>
                </div>
                <% } %>
                <% } %>
            </td>
        </tr>
        <% } %>
        </tbody>
    </table>
</div>
<% } else { %>
<div class="nodetail_found">
    <figure>
        <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
             height="120">
    </figure>
    <div class="colr_red mtop15">
        <%= type === 'story' ? '<?php echo __("No Stories found"); ?>' : (type === 'task' ? '<?php echo __("No Tasks found"); ?>' : ({ [original_epic_id]: '<?php echo __("No Features found"); ?>', [original_feature_id]: '<?php echo __("No Stories found"); ?>' }[csTypRep] || '<?php echo __("No Sub-Tasks found"); ?>')) %>
    </div>
</div>
<% } %>
</div>
