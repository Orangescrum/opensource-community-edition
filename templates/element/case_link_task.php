<h4 class="detail_link_section">
<?php if($this->Format->isAllowed('Link Task',$roleAccess)){ ?>
<a href="javascript:void(0);" title="Add New Linking" onclick="addLinkPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>)" style="color: #727272;" id="tour_task_detail_linking">
<?php echo __('Linking Tasks');?>
</a>
<span><a id="tour_task_detail_linking_v2" href="javascript:void(0);" title="Add New Linking" onclick="addLinkPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);"><i class="material-icons">&#xE145;</i></a></span>
<?php }else{ ?>
	<?php echo __('Linking Tasks');?>
<?php } ?>
</h4>
<% if(link_tasks.length > 0){%>
<table class="table table-striped" style="margin-top:-10px">
<thead style="opacity:0">
<th style="width:4%;border:none;"></th>
<th style="width:4%;border:none;"></th>
<th style="width:40%;border:none;"></th>
<th style="width:15%;border:none;"></th>
<th style="width:18%;border:none;"></th>
<th style="border:none;"></th>
<th style="width:4%;border:none;"></th>
</thead>
	<tbody>
	<% 
	var relates_id = '';
	var count=0;
	for(var sKey in link_tasks){
	var getdata = link_tasks[sKey].Easycase;
	var easycaseLinking = link_tasks[sKey].EasycaseLinking;
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
	var projUniq = getdata.pjUniqid;
	var projectName = getdata.projectName;
	var sho_assign_nm = 'me';
	if(caseAssgnUid != SES_ID){
		sho_assign_nm = getdata.asgnName; 
	}
	var getTotRep = 0;
	if(getdata.thread_count && getdata.thread_count!=0) {
			getTotRep = getdata.thread_count;
	}
	count++;
	var task_type_sub = '';
	for(var k in GLOBALS_TYPE) {
		if(isDisplayEpicType(GLOBALS_TYPE[k].Type.name)){
		var v = GLOBALS_TYPE[k];
		if(v.Type.id == getdata.type_id){
			task_type_sub = v.Type.name;
		} 
	}
	}
	var showQuickActiononList = 0;
    if(isactive == 1 && (caseLegend == 1 || caseLegend == 2 || caseLegend == 4) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
        showQuickActiononList = 1;
    }
	if(relates_id != easycaseLinking.title ){ 
	relates_id = easycaseLinking.title;
	%>
		<tr><td colspan="7"><strong><%= relates_id %></strong></td></tr>
		
	<%	
	}	
	%>
		<tr class="timelog-hover-block row_tr" id="linkRow<%= caseAutoId %>">
				
				<td style="padding-right:0px;padding-left:0px;text-align: center;">
					<%= getdata.case_no%>
				</td>
		
				<td style="padding-right:0px;padding-left:0px;"><span class="ttype_global tt_<%= getttformats(task_type_sub)%>" style="margin-top:-16px;"></span></td>
				<td style="padding-left:0px;">
					<div class="max_width_tltsk_title ellipsis-view"> 
						<a href="javascript:void(0);" class="ttl_listing titlehtml" data-task="<%= getdata.uniq_id %>"  data-task-id="<%= caseUniqId %>" data-task-from="linkSection">
							<span id="titlehtml<%= count %>" data-task="<%= caseUniqId %>" class="case-title fs-hide <% if(getdata.type_id!=10 && getdata.legend==3) { %>closed_tsk<% } %> case_title_<%= caseAutoId %>"> 
							<span class="max_width_tsk_title ellipsis-view <% if(caseLegend == 5){%>resolve_tsk<% } %> case_title wrapword task_title_ipad <% if(caseTitle.length>40){%>overme<% }%> " title="<%= formatText(ucfirst(caseTitle)) %>  ">
								<%= formatText(ucfirst(caseTitle)) %>
							</span>
						</span>                        
							</a>
						</div>                              
				</td>
				<td style="width:80px;">
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
				<% if(isactive==0){ %>
				<td></td>
				<% } else { %>
							
				<td class="assi_tlist">
					<i class="material-icons" style="top:4px;">&#xE7FD;</i>			
					<% if((projUniq != 'all') && showQuickActiononList){ %>
	<span id="showUpdAssign<%= caseAutoId %>" <% if(isAllowed('Change Assigned to',projectUniqid)){ %>  data-toggle="dropdown" <% } %> title="<%= getdata.asgnName %>" class="clsptr" onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)"><%= getdata.asgnShortName %><span class="due_dt_icn"></span></span>
					<% } else { %>
						<span id="showUpdAssign<%= caseAutoId %>" style="cursor:text;text-decoration:none;color:#a7a7a7;"><%= getdata.asgnShortName %></span>
					<% } %>
					<% if((projUniq != 'all') && showQuickActiononList){ %>
					<span id="asgnlod<%= caseAutoId %>" class="asgn_loader" style="display:none;">
						<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
					</span>
					<% } %>			
					<span class="dsp-block" <% if((projUniq != 'all') && showQuickActiononList){ %> onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)" <% } %>>
						<span class="dropdown" style="display:inline-block;">
			<a class="dropdown-toggle" <% if(isAllowed('Change Assigned to',projectUniqid)){ %> data-toggle="<% if((projUniq != 'all') && showQuickActiononList){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
							  <i class="material-icons">&#xE5C5;</i>
							</a>
							<ul class="dropdown-menu asgn_dropdown-caret" id="showAsgnToMem<%= caseAutoId %>">
							  <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId %>" /></li>
							</ul>
						</span>
					</span>
					</td>
					<td style="text-align: right;">
<?php if($this->Format->isAllowed('Remove Link Task',$roleAccess)){ ?>
					<a href="javascript:void(0);" class="showinhover" onclick="removeLinkTask(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);" title="Remove Link"><span class="cmn_tskd_sp delete_icon del_link_tsk"></span></a>
					<?php } ?>
          </td>					
				<td style="padding-right:0px;padding-left:0px;">
        	<div class="fl">
        	<div class="dropdown"> 
        	<a class="dropdown-toggle showinhover" data-toggle="dropdown" <% if(isAllowed('Change Status of Task',projectUniqid)){ %> href="javascript:void(0);" <% } %> data-target="#">
        		  <i class="material-icons">&#xE5D4;</i>
        		</a>
        	<% if(isAllowed('Change Status of Task',projectUniqid)){ %>
        	<ul class="dropdown-menu addn_menu_drop_pos" style="top:0px;">
        	  <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[projId] !='undefined' && customStatusByProject[projId] != null){
        	  if(isAllowed('Change Status of Task',projectUniqid)){
        		if(isactive == 1){
        		 $.each(customStatusByProject[projId], function (key, data) {
        			if(getdata.CustomStatus.id != data.id){
        		  %>
        		  <li onclick="setCustomStatus(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseNo + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'link\'' %>);" id="new<%= caseAutoId %>">
        			<a href="javascript:void(0);">
        			<span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %></a>
        			</li>
        		 <%   } 
        			}); 
        			}
        		}
        	  } else{ %>
        	  <% var caseFlag="";
        		if(caseLegend != 1 && caseTypeId != 10){ caseFlag=9; }
        			if(isAllowed('Change Status of Task',projectUniqid)){
        		if(isactive == 1){ %>
        		<li onclick="linkActiononTask(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + caseUniqId + '\'' %>, <%= '\'' + caseNo + '\'' %>,<%= '\'new\'' %>,<%= '\'link_task\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);" id="new<%= caseAutoId %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
        			<a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New');?></a>
        		</li>
        		<% }
        		if((caseLegend != 2 && caseLegend != 4) && caseTypeId!= 10) { caseFlag=1; }
        							if(isactive == 1) { %>
        		<li onclick="linkActiononTask(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\'' + caseNo + '\'' %>,<%= '\'start\'' %>,<%= '\'link_task\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %> );" id="start<%= caseAutoId %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
        			<a href="javascript:void(0);"><i class="material-icons">&#xE039;</i><% if(caseLegend == 1){ %><?php echo __('Start');?><% }else{ %><?php echo __('In Progress');?><% } %></a>
        		</li>
        		<% }
        		if((caseLegend != 5) && caseTypeId!= 10) { caseFlag=2; }
        		if(isactive == 1){ %>
        		<li onclick="linkActiononTask(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %> , <%= '\'' + caseNo + '\'' %>,<%= '\'resolve\'' %>,<%= '\'link_task\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);" id="resolve<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
        			<a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve');?></a>
        		</li>
        		<% }
        		if((caseLegend != 3) && caseTypeId != 10) { caseFlag=5; }
        		if(isactive == 1){ %>
        		<% if(isAllowed('Status change except Close',projectUniqid)){ %>
        		<li onclick="linkActiononTask(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>, <%= '\'' + caseNo + '\'' %>,<%= '\'close\'' %>,<%= '\'link_task\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);" id="close<%= caseAutoId %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
        			<a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close');?></a>
        		</li>
        		<% } %>
        		<% } %>
        		<% } %>		
        		<% } %>                                    
        			</ul>
        		<% } %>
        		</div>
        	</div>
        	<div class="cb"></div>
        </td>					
				<% } %>                          
			</tr>
		<% } %>
	</tbody>
</table>
<% }else{ %>
<table class="table table-striped">
    <tr><td><?php echo __('No linking available.');?></td></tr>
</table>
<% } %>