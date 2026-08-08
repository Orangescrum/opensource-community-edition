
<?php if($this->Format->isAllowed('Link Task',$roleAccess)){ ?>
    <div class="sec_title d-flex tog" data-cmnt_id ="tsklink_sec">
        <div class="heading_title">
            <span class="sec_icon link_icon"></span>
            <h3><?php echo __('Tasks Links'); ?></h3>
        </div>
        <div class="icon_collapse " ></div>
    </div>

<?php }else{ ?>
    <?php echo __('Linking Tasks');?>
<?php } ?>

<div class="toggle_details">

    <div class="detail_list_table ">

        <div class="d-flex mt-20">
            <% if(is_inactive_case == 0 && is_active == 1){ %>
            <?php if($this->Format->isAllowed('Link Task',$roleAccess)){ ?>
                <div class="ml-auto sec_action_item">
                    <div class="cursor link-icon" onclick="addLinkPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>)" id="tour_task_detail_linking">
                        <i class="material-icons">add</i> <?php echo __('Add New Linking'); ?>
                    </div>
                </div>
            <?php } ?>
            <% } %>
        </div>

        <% if(link_tasks.length > 0){%>
        <table class="table width-100-per mt-20">

            <tbody>
            <%
            var relates_id = '';
            var count=0;
            for(var sKey in link_tasks){
            var getdata = link_tasks[sKey];
            var easycaseLinking = new Object();
            easycaseLinking.easycase_relate_id = getdata.easycase_relate_id;
            easycaseLinking.title = getdata.easycase_relate_title;
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
            if(isactive == 1 && (caseLegend == 1 || caseLegend == 2 || caseLegend == 3 || caseLegend == 4) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
            showQuickActiononList = 1;
            }
            if(relates_id != easycaseLinking.title ){
            relates_id = easycaseLinking.title;
            %>
            <tr class="separetor_bgtr"><td colspan="6"><strong><%= relates_id %></strong></td></tr>

            <%
            }
            %>
            <tr class="row_tr" id="linkRow<%= caseAutoId %>">
                <td class="width-5-per">
                    <div><%= getdata.case_no%></div>
                </td>
                <td class="width-5-per">
                    <div class="type_icon">
                        <span class="ttype_global tt_<%= getttformats(task_type_sub)%> " width="20" height="20"></span>
                    </div>
                </td>
                <td class="width-40-per">

                    <div class="">
                        <a href="javascript:void(0);" class="titlehtml" data-task="<%= getdata.uniq_id %>"  data-task-id="<%= caseUniqId %>" data-task-from="linkSection">
						<span id="titlehtml<%= count %>" data-task="<%= caseUniqId %>" class="case-title fs-hide <% if(getdata.type_id!=10 && getdata.legend==3) { %>closed_tsk<% } %> case_title_<%= caseAutoId %>">
						<span class="break-all link-text <% if(caseLegend == 5){%>resolve_tsk<% } %>  <% if(caseTitle.length>40){%>overme<% }%> " title="<%= formatText(ucfirst(caseTitle)) %>  ">
							<%= formatText(ucfirst(caseTitle)) %>
						</span>
					</span>
                        </a>
                    </div>

                </td>

                <td class="width-20-per">
                    <div class="cs_select_dropdown" style="width: 90px;">
					<span class="cs_select_status">
				<% if(getdata.isactive==0){ %>
						<div class="task_status new"></div>
					<%}else if(groupby =='' || groupby !='status'){%>
						<% if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){ %>
							<div class="new"style="width: 20px;"><%= easycase.getCustomStatus(getdata.CustomStatus, getdata.custom_status_id) %></div>
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
						<% if(is_inactive_case == 0 && is_active == 1){
							if(typeof customStatusByProject !== "undefined" && typeof customStatusByProject[projId] !== 'undefined' && customStatusByProject[projId] !== null){
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
						<% } %>
						</ul>
						</span>
						</span>
                    </div>
                </td>

                <% if(isactive==0){ %>
                <td></td>
                <% } else { %>
                <td class="width-20-per">
                    <div class="assign_tasklink_dropdown dropdown">

					<span class="dropdown d-inline-block" >
						<span class="d-inline-block dropdown" <% if((projUniq != 'all') && showQuickActiononList){ %> <% if(is_inactive_case == 0 && is_active ==1) {%> onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)"<% } %> <% } %>>
							<p class="quick_action dropdown-toggle" <% if(isAllowed('Change Assigned to',projectUniqid)){ %> data-toggle="<% if((projUniq != 'all') && showQuickActiononList){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
							<% if((projUniq != 'all') && showQuickActiononList){ %>
								<span class="drop_action_data ttc" id="showUpdAssign<%= caseAutoId %>" <% if(isAllowed('Change Assigned to',projectUniqid)){ %>  data-toggle="dropdown" <% } %>  class="clsptr" <% if(is_inactive_case == 0 && is_active ==1) {%> onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\' \'' %>,<%= '\' \'' %>,<%= '\'' + getdata.client_status + '\'' %>)" <% } %>><%= getdata.asgnShortName %><span class="due_dt_icn"></span></span>
                        <% } else { %>
                        <span id="showUpdAssign<%= caseAutoId %>"><%= getdata.asgnShortName %></span>
                        <% } %>
                        <% if(is_inactive_case == 0 && is_active == 1){%>
                        <i class="tsk-dtail-drop material-icons"></i>
                        <% } %>
                        </p>
                        <ul class="dropdown-menu" id="showAsgnToMem<%= caseAutoId %>">
                            <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId %>" /></li>
                        </ul>
                        </span>
                        </span>
                    </div>
                </td>

                <td class="width-10-per action_td">
                    <% if(is_inactive_case == 0 && is_active == 1){%>
                    <div class="d-flex">
                        <?php if($this->Format->isAllowed('Remove Link Task',$roleAccess)){ ?>
                            <% if(is_inactive_case == 0 && is_active ==1) {%>
                            <div class="cursor link-icon" onclick="removeLinkTask(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + link_parent + '\'' %>,<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csProjIdRep + '\'' %>);" title="<?php echo __('Remove Link'); ?>">
                                <i class="material-icons delete_icon">delete_outline</i>
                            </div>
                            <% } %>
                        <?php } ?>
                    </div>
                    <% } %>
                </td>

                <% } %>
            </tr>
            <% } %>
            </tbody>
        </table>
    </div>
    <% }else{ %>
    <div class="nodetail_found">
        <figure>
            <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
                 height="120">
        </figure>
        <div class="colr_red mtop15"><?php echo __('No Linked Task found'); ?></div>
    </div>
    <% } %>

</div>
