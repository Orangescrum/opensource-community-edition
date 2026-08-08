<div class="time-log-header timelog-table timelog-table-head detail_timelog_header" style="">
	<h4 class="fl subtask-block-h4">
	<a href="javascript:void(0);" title="<?php echo __('Subtasks'); ?>" onclick="addSubtaskLnk()" style="color: #727272;" id="tour_task_detail_subtask">
	<?php echo __('Subtasks'); ?>
	</a>
	</h4>
	<div class="fr tl-msg-btn subtask_holder_div" style="<% if(csLgndRep == 3 || is_active ==0){ %>display:none<% } %>">		
		<!--<a class="anchor other_page_subtask" rel="tooltip" title="<?php echo __('Create Subtask'); ?>" onclick="subTask(<%= '\'' + csAtId + '\'' %>, <%= '\'' + csNoRep + '\'' %>, <%= '\'' + csProjIdRep + '\'' %>);">
			<i style="font-weight: bold;" class="material-icons">&#xE145;</i>
		</a>-->
		<a class="anchor" rel="tooltip" title="<?php echo __('Reload Subtask');?>" onclick="loadSubtaskInDetail(<%= '\'' + csAtId + '\'' %>);">
			<i class="material-icons">&#xE5D5;</i>
		</a>
	<?php if($this->Format->isAllowed('Create Task',$roleAccess)){ ?>
		<a id="tour_task_detail_subtask_v2" class="anchor other_page_subtask" rel="tooltip" title="<?php echo __('Create Subtask'); ?>" onclick="addSubtaskPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csAtId + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'Title\'' %>);">
			<i style="font-weight: bold;" class="material-icons">&#xE145;</i>
		</a>
		<a style="display:none;" class="anchor detail_page_subtask" rel="tooltip" title="<?php echo __('Create Subtask'); ?>" onclick="addSubtaskPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csAtId + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'Title\'' %>);">
			<i style="font-weight: bold;" class="material-icons">&#xE145;</i>
		</a>
	<?php } ?>
	</div>
	<div class="cb"></div>
</div>
<% if(parseInt(subtasks.length) > 0){ var caseParenTUID = csUniqId; %>
<div class="task_listing sub_tasks_tbl">
	<div class="m-cmn-flow">
		<table class="table table-striped table-hover m-list-tbl subtask-task-detail">
			<thead>
				<tr>
					<th class="case_no"><?php echo __('Task#'); ?></th>
					<th class="case_no"></th>
					<th class="title count_ttl_td"><?php echo __('Title'); ?></th>
					<th class="status_td"><?php echo __('Status'); ?></th>
					<th class="assigned_user"><?php echo __('Assigned to'); ?></th>
					<th class="due"><?php echo __('Due Date'); ?></th>
					<th class="actions"></th>
				</tr>
			</thead>
			<tbody>
				<% 
				var count=0;
				for(var sKey in subtasks){
				var getdata = subtasks[sKey].Easycase;
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
				projectName = projName;
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
					<tr class="timelog-hover-block row_tr">
						<td class="case_no"><%= getdata.case_no%></td>	
						<td class="case_no"><span class="ttype_global tt_<%= getttformats(task_type_sub)%>" style="margin-top:-15px;"></span></td>
						<td class="title count_ttl_td">
							<div class="max_width_tltsk_title ellipsis-view" style="max-width: 381px;"> 
								<span id="titlehtml<%= count %>-sub" data-task="<%= getdata.uniq_id %>">
								<a href="javascript:void(0);" class="ttl_listing titlehtml" data-task="<%= getdata.uniq_id %>" data-task-id="<%= getdata.uniq_id %>">
									<%= formatText((getdata.title)) %>
								</a>
								</span>
							</div>
							<?php /*<a class="anchor subtask_attachment" <% if(getdata.format != 1 && getdata.format != 3) { %> style="display:none;" id="fileattch<%= count %>"<% } %>>
								<i class="glyphicon glyphicon-paperclip"></i>
							</a>
							<a class="anchor subtask_reply_count" id="repno<%= count %>" style="<% if(!getTotRep || getTotRep==0) { %>display:none<% } %>">
								<% if(getTotRep && getTotRep!=0) { %><%= getTotRep %><% } %>
									<i class="material-icons">&#xE0B9;</i>
							</a>*/ ?>
						</td>
						<td class="status_td">
							<span class="">
							<% if(getdata.isactive==0){ %>
								<div class="label new" style="background-color: olive"><?php echo __('Archived');?></div>
							<%}else if(groupby =='' || groupby !='status'){%>
							<% if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){ %>
									<%= easycase.getCustomStatus(getdata.CustomStatus, getdata.custom_status_id) %>
								<% }else{ %>
									<%= easycase.getStatus(getdata.type_id, getdata.legend) %>
								<% } %>
							<% } %>
							</span>
						</td>
						
						<td class="assigned_user"><%= getdata.Assigned %></td>
						<td class="due"><%= getdata.due_date!="" ? formatDate('MMM DD, YYYY',getdata.due_date) : "---" %></td>
						<td class="actions">
							<div class="check-drop-icon fl">
								<div class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
										<i class="material-icons">&#xE5D4;</i>
									</a>
									<ul class="dropdown-menu addn_menu_drop_pos" style="top: 0px;">
                  <% if(isAllowed('Change Status of Task',projectUniqid)){ %>
										<% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[projId] !='undefined' && customStatusByProject[projId] != null){
										 if(getdata.isactive == 1){
                     $.each(customStatusByProject[projId], function (key, data) {
											if(getdata.CustomStatus.id != data.id){
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

										<% 
										if( SES_ID == caseUserId) { caseFlag=3; }
										if(getdata.isactive == 1){ %>
											<!--<li onclick=" (<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" >
												<a href="javascript:void(0);"><i class="material-icons">&#xE254;</i>Edit</a>
											</li>
                                                <li onclick="copytask(<%= '\''+ caseUniqId+'\',\''+ caseAutoId+'\',\''+caseNo+'\',\''+projId+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="copy<%= caseAutoId %>" >
												  <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i>Copy</a>
											</li>-->
										<% } %>

										<% 
										if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed('Delete All Task',projectUniqid)) { caseFlag = "delete"; }
										if(getdata.isactive == 1){ %>
										<% if(isAllowed('Delete Task',projectUniqid) || isAllowed('Delete All Task',projectUniqid)){ %>
											<li onclick="deleteCase(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + projId + '\'' %>, <%= '\'t_' + caseUniqId + '\'' %>, 0, <%= '\'sub\'' %>);" id="delete<%= caseAutoId %>" style="<% if(caseFlag == "delete"){ %>display:block<% } else { %>display:none<% } %>">
												<a href="javascript:void(0);"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
											</li>
										<% } %>
										<% } %>

										</ul>
									</div>
								</div>
                            </td>
                        </tr>
                    <% } %>
                </tbody>
            </table>
        </div>
    </div>
<% }else{ %>
<div class="sub_tasks_tbl" >
<table class="table table-striped">
    <tr><td><?php echo __('No subtask available.');?></td></tr>
</table>
</div>
<% } %> 