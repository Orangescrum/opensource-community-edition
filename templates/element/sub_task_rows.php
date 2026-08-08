<?php $dues_date_qt_top = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), "date"); ?>
<% var rel_arr = new Array(); var d_mid = mid; if(d_mid == "NA"){ d_mid = 0;} %>
<% if(casePage == 1) { %>
    <?php echo $this->element('quick_task_sub_view'); ?>
<% } %>
<% 
    var hashtag = parseUrlHash(getHash());
	var page_hash = hashtag ? hashtag[0] : '';
    var subTaskPname = ""; 
    var count = 0;
    var count1= 0;
    var count2 = 0;
    var projectUniqid = projUniq ;
    if(resCaseProj.length >0){ %>
                                <% var sindex = 0;
            var __cgb = (typeof casegroupby !== 'undefined' && casegroupby) ? casegroupby : 'None';
            var __prevGroup = '__init__';
            for (var key in resCaseProj) {
                if(sindex == page_limit){
                    break;
                }
                Easycase= resCaseProj[key];
                Easycase= resCaseProj[key]['Easycase'];
                CustomStatus = Easycase['CustomStatus'];
                caseAutoId=Easycase['id'];
                var isFavourite = Easycase['isFavourite'];
                var favMessage ="Set favourite";
                if(isFavourite){
                    var favMessage ="Remove favourite";
                }
                var favouriteColor = Easycase['favouriteColor'];
                projId=Easycase['project_id'];
                
                caseLegend = Easycase['custom_status_id'] != 0 ? Easycase['custom_status_id'] : Easycase['legend'];
                caseTypeId=Easycase['type_id'];
                caseNo = Easycase['case_no'];
                caseUniqId =Easycase['uniq_id'];
                caseUserId = Easycase['user_id'];
                casePriority = Easycase['priority'];
                caseFormat = Easycase['format'];
                caseTitle = Easycase['title'];
                caseEstHoursRAW = Easycase['estimated_hours'];
                
                isactive = Easycase['isactive'];
                caseAssgnUid = Easycase['assign_to'];
                is_recurring=Easycase['is_recurring'];
                var showQuickActiononList = 0;
                var showQuickActiononListEdit = 0;
                if(isactive == 1 && (caseLegend != max_custom_status) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
                    showQuickActiononList = 1;
                }
                var showQuickActiononCopy = 0;
                if(isactive == 1 && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
                    showQuickActiononCopy = 1;
                }
                if(isactive == 1 && (caseLegend != max_custom_status) && (caseUserId== SES_ID)){
                    showQuickActiononListEdit = 1;
                }
            
                csTdTyp=resCaseProj[key]['Type']['name']; 
                csDueDate=Easycase['csDueDate'];
                csDuDtFmt=Easycase['csDuDtFmt'];
                csDuDtFmtT=Easycase['csDuDtFmtT'];
                count++;
                        sindex++;
                            if (resCaseProj.hasOwnProperty(key)) {
                            var getdata = resCaseProj[key];
                %>
<% if (__cgb !== 'None') {
       var __gk = (typeof getdata.group_key !== 'undefined') ? String(getdata.group_key) : '';
       if (__gk !== __prevGroup) {
           __prevGroup = __gk;
%>
<tr class="list-dt-row"><td colspan="20" class="curr_day"><div class="dt_cmn_mc"><span><%= subtaskGroupLabel(__cgb, getdata) %></span></div></td></tr>
<% } } %>
<tr class="row_tr tr_all trans_row parent_tr " id="curRow_subtask_<%= getdata.Easycase.id %>" data-mid="<%= d_mid %>">
    <td class="check_list_task tsk_fst_td pr_low">
        <div class="checkbox">
            <label>
                <% if (page_hash === 'taskgroups' && (getdata.Easycase.type_id == 13 || getdata.Easycase.type_id == 15)) { %>
                <input type="checkbox" id="actionChk<%= getdata.Easycase.id %>" value="<%= getdata.Easycase.id + '|' + getdata.Easycase.case_no + '|' + getdata.Easycase.uniq_id %>" class="fl mglt chkOneTsk" disabled="disabled">
				<% } else if(getdata.Easycase.legend != 3 && getdata.Easycase.type_id != 10) { %>
                <input type="checkbox" style="cursor:pointer" id="actionChk<%= getdata.Easycase.id %>" value="<%= getdata.Easycase.id + '|' + getdata.Easycase.case_no + '|' + getdata.Easycase.uniq_id %>" class="fl mglt chkOneTsk">
                <% } else if(getdata.Easycase.type_id != 10) { %>
                <input type="checkbox" id="actionChk<%= getdata.Easycase.id %>" checked="checked" value="<%= getdata.Easycase.id + '|' + getdata.Easycase.case_no + '|closed' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } else { %>
                <input type="checkbox" id="actionChk<%= getdata.Easycase.id %>" checked="checked" value="<%= getdata.Easycase.id + '|' + getdata.Easycase.case_no + '|update' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } %>
            </label>
        </div>
        <input type="hidden" id="actionCls<%= getdata.Easycase.id %>" value="1" disabled="disabled" size="2">
        <% if (page_hash === 'taskgroups' && getdata.Easycase.type_id != 13 && getdata.Easycase.type_id != 15) { %>
        <div class="check-drop-icon hover-effect">
            <div class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                    <i class="material-icons">&#xE5D4;</i>
                </a>
                <ul class="dropdown-menu tsg_chng_action_menu hover-block">
                    <% if( SES_ID == caseUserId) { caseFlag=3; }
if(isactive == 1){ %>
                    <% if(showQuickActiononList || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <li onclick="editepic(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% }else{ %>
                    <li onclick="editask(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){
if(getdata.CustomStatus.status_master_id != 3){ %>
                        <li onclick="setCustomStatus(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.status_master_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.name  + '\'' %>);" id="new<%= getdata.Easycase.id %>">
                            <a href="javascript:void(0);">
                                <span style="background-color:#<%= lastCustomStatus.LastCS.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                <%= lastCustomStatus.LastCS.name %></a>
                        </li>
                        <%   } 
} else{ %>
                        <% var caseFlag="";
    if((getdata.Easycase.legend != 3) && getdata.Easycase.type_id != 10) { caseFlag=5; }
    if(getdata.Easycase.isactive == 1){ %>
                        <% if(isAllowed("Status change except Close",getdata.Project.uniq_id)){ %>
                        <li onclick="setCloseCase(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>);" id="close<%= getdata.Easycase.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <?php } ?>
                    <% if(isAllowed("Create Task",projectUniqid)){ %>
                    <% 
        if(caseLegend != max_custom_status && caseTypeId != 10){ %>
                    <% if(isEpicTask(getdata.Easycase.type_id, getdata.actual_dt_created)){ %>
                    <li onclick="addSubtaskPopup(<%= '\'' + projectUniqid + '\'' %>,<%= '\'' + getdata.Easycase.id + '\'' %>,<%= '\'' + getdata.project_id + '\'' %>,<%= '\'' + getdata.Easycase.uniq_id + '\'' %>,<%= '\'' + getdata.title + '\'' %>);">
                        <a href="javascript:void(0);"><i class="material-icons"></i><?php echo __('Create Subtask'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <%	if(getdata.sub_sub_task == 0){  %>
                    <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                    <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                        <li onclick="convertToSubTask(<%= '\''+ caseAutoId+'\',\''+projId+'\',\''+caseNo+'\'' %>);" id="convertToSubTask<%= caseAutoId %>" style=" <% if(showQuickActiononList){ %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE15A;</i><?php echo __('Convert To Subtask'); ?></a>
                        </li>
                    <?php } ?>

                    <% }  %>
                    <% }  %>
                        <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                        <% if(caseLegend == max_custom_status){ %>
                        <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } else{ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                        <% if(caseLegend !=3 && caseTypeId != 10){ %>
                        <% if(isAllowed("Start Timer",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="startTimer(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle,3)) + '\'' %>, <%= '\'' + caseUniqId + '\'' %>,<%= '\'' + projUniq + '\'' %>,<%= '\'' + escape(htmlspecialchars(projectName,3)) + '\'' %>); ">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE425;</i><?php echo __('Start Timer'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <% if(caseLegend == max_custom_status) { caseFlag= 7; } else { caseFlag= 8; }
if(isactive == 1){ %>
                    <% if(isAllowed("Reply on Task",projectUniqid)){ %>
                    <li id="subact_replys<%= count %>" data-task="<%= caseUniqId %>" page-refer-val="Task Group List Pages">
                        <a href="javascript:void(0);" id="reopen<%= caseAutoId %>" style="<% if(caseFlag == 7){ %>display:block <% } else { %>display:none<% } %>">
                            <div class="act_icon act_reply_task fl" title="<?php echo __('Re-open'); ?>"></div><i class="material-icons">&#xE898;</i> <?php echo __('Re-open'); ?>
                        </a>

                        <a href="javascript:void(0);" id="reply<%= caseAutoId %>" style="<% if(caseFlag == 8){ %>display:block <% } else { %>display:none<% } %>">
                            <i class="material-icons">&#xE15E;</i><?php echo __('Reply'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% if( SES_ID == caseUserId) { caseFlag=3; }
if(isactive == 1){ %>
                    <% if(showQuickActiononList || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <!-- <li onclick="editepic(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li> -->
                    <% }else{ %>
                    <!-- <li onclick="editask(<%= '\''+ caseUniqId+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId %>" style=" <% if(showQuickActiononList || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li> -->
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %>
                    <li onclick="copytask(<%= '\''+ caseUniqId+'\',\''+ caseAutoId+'\',\''+caseNo+'\',\''+projId+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="copy<%= caseAutoId %>" style=" <% if(showQuickActiononCopy){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i><?php echo __('Copy'); ?></a>
                    </li>
                    <% } %>

                    <% } %>
                    <% if((caseLegend != max_custom_status) && caseTypeId!= 10) { caseFlag=2; }
if((SES_TYPE == 1 || SES_TYPE == 2) || (((caseLegend == 1 || caseLegend == 2 || caseLegend == 4) || (caseLegend != max_custom_status)) &&  (SES_ID == caseUserId))){ %>
                    <% if(isactive == 1){ %>
                    <% if(isAllowed("Move to Project",projectUniqid)){ %>
                    <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                    <li data-prjid="<%= projId %>" data-caseid="<%= caseAutoId %>" data-caseno="<%= caseNo %>" id="mv_prj<%= caseAutoId %>" style=" " onclick="mvtoProject( <%= '\'' + count + '\'' %> , this);">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to Project'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isactive == 1){ %>
                    <% if(isAllowed("Move to Milestone",projectUniqid)){ %>
                    <li onclick="moveTask( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + caseNo + '\'' %> , <%= '\'\'' %> , <%= '\'' + projId + '\'' %> );" id="moveTask<%= caseAutoId %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:block <% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE89F;</i><?php echo __('Move to Task Group'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed("Archive All Task",projectUniqid)) { caseFlag = "archive"; }
if(isactive == 1){ %>
                    <% if(isAllowed("Archive Task",projectUniqid) || isAllowed("Archive All Task",projectUniqid)){ %>
                    <li onclick="archiveCase( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + caseNo + '\'' %> , <%= '\'' + projId + '\'' %> , <%= '\'t_' + caseUniqId + '\'' %> );" id="arch<%= caseAutoId %>" style="<% if(caseFlag == "archive"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE149;</i><?php echo __('Archive'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <%	if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId) || isAllowed("Delete All Task",projectUniqid)) { caseFlag = "delete"; }
if(isactive == 1){ %>
                    <% if(isAllowed("Delete Task",projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                    <li onclick="deleteCase( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + caseNo + '\'' %> , <%= '\'' + projId + '\'' %> , <%= '\'t_' + caseUniqId + '\'' %> , <%= '\'' + is_recurring + '\'' %>);" id="arch<%= caseAutoId %>" style="<% if(caseFlag == "delete"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                </ul>
            </div>
        </div>
        <% } %>
    </td>
    <td class="favo-td">
        <span id="caseProjectSpanFav<%=caseAutoId %>">
            <a href="javascript:void(0);" class="caseFav" onclick="setCaseFavourite(<%=caseAutoId %>,<%=projId %>,<%= '\''+caseUniqId+'\'' %>,1,<%=isFavourite%>)" rel="tooltip" original-title="<%=favMessage%>" style="margin-top:0px;color:<%=favouriteColor%>;">
                <% if(isFavourite) { %>
                <i class="material-icons" style="font-size:18px;">star</i>
                <% }else{ %>
                <i class="material-icons" style="font-size:18px;">star_border</i>
                <% } %>
            </a>
        </span>
    </td>
    <td class="text-left count-plist-drop pr">
        <%= getdata.Easycase.case_no %> <span class="watch showtime_<%= getdata.Easycase.id %>"></span>
    </td>

    <td class="relative list-cont-td label_task_tle" id="tour_task_title_listing">
        <?php /*
<span class="ttype_global tt_<%= getttformats(getdata.Type.name)%>"></span> 
*/ ?>
        <%
var priorClass = 'prio_low';
if(getdata.priority == 1){
priorClass = 'prio_medium';
}else if(getdata.priority == 0){
priorClass = 'prio_high';
}
%>
        <div style="" id="pridiv<%= caseAutoId %>" class="pri_actions <% if(showQuickActiononList){ %> dropdown<% } %>">
            <div class="dropdown cmn_h_det_arrow">
                <div <% if(showQuickActiononList){ %> class="quick_action" <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %> data-toggle="dropdown" <% } %> <% } %> style="cursor:pointer"><span class=" priority <%= priorClass %> prio_lmh prio_gen prio-drop-icon" rel="tooltip" title="<?php echo __('Priority'); ?>"></span><% if(showQuickActiononList){ %> <i class="tsk-dtail-drop material-icons">&#xE5C5;</i> <% } %></div>
                <% var csLgndRep = caseLegend; %>
                <% if(showQuickActiononList){ %>
                <ul class="dropdown-menu quick_menu">
                    <li class="low_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId + '\', \'2\', \'' + caseUniqId + '\', \'' + caseNo + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Low'); ?></a></li>
                    <li class="medium_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId + '\', \'1\', \'' + caseUniqId + '\', \'' + caseNo + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Medium'); ?></a></li>
                    <li class="high_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId + '\', \'0\', \'' + caseUniqId + '\', \'' + caseNo + '\'' %> )"><span class="priority-symbol"></span><?php echo __('High'); ?></a></li>
                    <li class="urgent_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId + '\', \'3\', \'' + caseUniqId + '\', \'' + caseNo + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Urgent'); ?></a></li>
                </ul>
                <% } %>
            </div>
        </div>
        <span id="prilod<%= caseAutoId %>" style="display:none">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>
        <div class="ttl_listing" data-task-id="<%= getdata.Easycase.uniq_id %>">
            <a href="javascript:void(0);" class="titlehtml" data-task="<%= getdata.Easycase.uniq_id %>">
                <span class="case-title_<%= getdata.Easycase.id %> case_sub_task <% if(getdata.Easycase.type_id!=10 && (getdata.Easycase.legend == max_custom_status || getdata.custom_status_id == max_custom_status)) { %>closed_tsk<% } %>">
                    <span class="max_width_tsk_title ellipsis-view <% if(getdata.Easycase.legend == 5){%>resolve_tsk<% } %> case_title wrapword task_title_ipad <% if(getdata.title && getdata.title.length>100){%>overme<% }%> " title="<%= formatText(ucfirst(getdata.title)) %>  ">
                        <%= formatText(ucfirst(getdata.title)) %>
                        
                    </span>
                </span>
            </a>

            <div class="list-td-hover-cont">
                <?php /*<span class="created-txt"><% if(getdata.case_count!=0) { %><?php echo __('Updated');?><% } else { %><?php echo __('Created');?><% } %> <?php echo __('by');?> <%= getdata.User.name %> <?php echo __('on');?> <%= moment(getdata.dt_created).format("LLLL") %></span>*/ ?>
                <span class="created-txt"><% if(getdata.case_count!=0) { %><?php echo __('Updated'); ?><% } else { %><?php echo __('Created'); ?><% } %> <?php echo __('on'); ?> <%= moment(getdata.dt_created).format("lll") %></span>
                <span class="list-devlop-txt dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons tag_fl">&#xE54E;</i>
                        <span id="showUpdStatus<%= caseAutoId %>" class="<% if(showQuickActiononList && isactive == 1){ %>clsptr<% } %>" title="<%= csTdTyp %>">
                            <span class="tsktype_colr" id="tsktype<%= caseAutoId %>"><%= csTdTyp%><span class="due_dt_icn"></span>
                            </span>
                        </span>
                    </a>
                    <% if (page_hash === 'taskgroups' && getdata.Easycase.type_id != 13 && getdata.Easycase.type_id != 15) { %>
                    <span class="check-drop-icon dsp-block">
                        <span class="dropdown">
                            <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                                <i class="material-icons">&#xE5C5;</i>
                            </a>
                            <span id="typlod<%= caseAutoId %>" class="type_loader">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                            <% if(showQuickActiononList && isactive == 1){ %>
                            <ul class="dropdown-menu listgrp-bug-dropdn">
                                <li>
                                    <input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitems(this);" />
                                </li>
                                <%
for(var k in GLOBALS_TYPE) {
    if(isDisplayEpicType(GLOBALS_TYPE[k].Type.name)){
    if(GLOBALS_TYPE[k].Type.project_id == 0 || GLOBALS_TYPE[k].Type.project_id == getdata.project_id){
var v = GLOBALS_TYPE[k];
var t = v.Type.id;
var t1 = v.Type.short_name;
var t2 = v.Type.name;
var txs_typ = t2;
$.each(DEFAULT_TASK_TYPES, function(i,n) {
if(i == t1){
txs_typ = n;
}
});
%>
                                <li onclick="changeCaseType( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + caseNo + '\'' %> ); changestatus( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + t + '\'' %> , <%= '\'' + t1 + '\'' %> , <%= '\'' + t2 + '\'' %> , <%= '\'' + caseUniqId + '\'' %> )">
                                    <a href="javascript:void(0);">
                                        <span class="ttype_global tt_<%= getttformats(t2)%>"><%= t2 %></span>
                                    </a>
                                </li>
                                <% } } } %>
                            </ul>
                            <% } %>
                        </span>
                    </span>
                    <% } %>
                    <% if(getdata.epic) { %>
                    <span class="label epic-label" rel="tooltip" title="<%= getdata.epic %>"><%= getdata.epic %></span>
                    <% } %>
                </span>
            </div>
            <div class="task_dependancy_item">
                <div class="task_dependancy fr">
                    <% if(getdata.Easycase.children && getdata.Easycase.children != ""){ %>
                    <span class="fl case_act_icons task_parent_block" id="task_parent_block_<%= caseUniqId %>">
                        <div rel="" title="<?php echo __('Parents'); ?>" onclick="showParents(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + caseUniqId + '\'' %>,<%= '\'' + getdata.Easycase.children + '\'' %>);" class=" task_title_icons_parents fl"></div>
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
                    <% if(getdata.depends && getdata.depends != ""){ %>
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

                <div class="subcls_rcrng fr">
                    <% if(getdata.is_recurring == 1 || getdata.is_recurring == 2){ %>
                    <a rel="tooltip" title="<?php echo __('Recurring Task'); ?>" href="javascript:void(0);" onclick="showRecurringInfo(<%= caseAutoId %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></a>
                    <% } %>
                </div>
                <div class="cb"></div>

            </div>
        </div>
    </td>
    <td class="attach-file-comment text-center">
        <a href="javascript:void(0);" style="display:none;" id="fileattch1"> <i class="glyphicon glyphicon-paperclip"></i> </a>
    </td>
    <td class="assi_tlist sb-tg-assign">
        <div class="user-task-pf">
            <% if(!getdata.AssignTo.photo){ getdata.AssignTo.photo = 'user.png'; } %>
            <% var usr_name_fst = (getdata.AssignTo.name != null)?getdata.AssignTo.name:"<?php echo __("Unassigned"); ?>"; %>
            <i class="material-icons">&#xE7FD;</i>
            <% if((projUniq != 'all') && showQuickActiononList){ %>
            <span id="showUpdAssign<%= caseAutoId %>" <% if(isAllowed("Change Assigned to",projectUniqid)){ %> data-toggle="dropdown" <% } %>title="<%= usr_name_fst %>" class="clsptr" onclick="displayAssignToMem( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + projUniq + '\'' %> , <%= '\'' + caseAssgnUid + '\'' %> , <%= '\'' + caseUniqId + '\'' %> )"><%= usr_name_fst %><span class="due_dt_icn"></span></span>
            <% } else { %>
            <span id="showUpdAssign<%= caseAutoId %>" style="cursor:text;text-decoration:none;color:#a7a7a7;"><%= usr_name_fst %></span>
            <% } %>
            <% if((projUniq != "all") && showQuickActiononList){ %>
            <span id="asgnlod<%= caseAutoId %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block" <% if((projUniq != 'all') && showQuickActiononList){ %> onclick="displayAssignToMem(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid + '\'' %>,<%= '\'' + caseUniqId + '\'' %>)" <% } %>>
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Change Assigned to',projectUniqid)){ %> data-toggle="<% if((projUniq != 'all') && showQuickActiononList){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu asgn_dropdown-caret" id="showAsgnToMem<%= caseAutoId %>">
                        <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId %>" /></li>
                    </ul>
                </span>
            </span>
        </div>
    </td>
    <td class="esthrs_dt_tlist text-center sb-tg-esthours">
        <p class="<?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> estblists <?php } ?> estblist_subtask" <?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> style="cursor:pointer;" <?php } ?> id="est_blist_sub<%= getdata.Easycase.id %>" data-split="<%= getdata.is_splitted %>" case-id-val="<%= getdata.Easycase.id %>">
            <span class="border_dashed_subtask">
                <%= format_time_hr_min(getdata.estimated_hours) %>
            </span>
        </p>
        <% var est_time = Math.floor(caseEstHoursRAW/3600)+':'+(Math.round(Math.floor(caseEstHoursRAW%3600)/60)<10?"0":"")+Math.round(Math.floor(caseEstHoursRAW%3600)/60); %>

        <input type="text" data-est-id="<%=caseAutoId%>" data-est-no="<%=caseNo%>" data-est-uniq="<%=caseUniqId%>" data-est-time="<%=est_time%>" id="est_hr_sub_list<%=caseAutoId%>" class="est_hr_sub_list form-control check_minute_range" style="margin-bottom: 2px;display:none;" maxlength="5" rel="tooltip" title="<?php echo __('You can enter time as 1.5(that mean 1 hour and 30 minutes)'); ?>" onkeypress="return numeric_decimal_colon(event)" value="<%= est_time %>" placeholder="hh:mm" data-default-val="<%=est_time%>" />

        <span id="estlod<%=caseAutoId%>" style="display:none;margin-left:0px;">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>
    </td>
    <td class="text-center sb-tg-status">
        <div class="cs_select_dropdown">
            <span id="csStsRep_sub<%= getdata.Easycase.id %>" class="cs_select_status">
                <% if(getdata.Easycase.isactive==0){ %>
                    <div class="label new" style="background-color: olive"><?php echo __('Archived'); ?></div>
                <% }else {
                    if(getdata.custom_status_id != 0 && getdata.CustomStatus != null ){ %>
                        <%= easycase.getCustomStatus(getdata.CustomStatus, getdata.custom_status_id) %>
                <% }else{ %>
                    <%= easycase.getStatus(getdata.Easycase.type_id, getdata.Easycase.legend) %>
                <% } } %>
            </span>
            <% if (page_hash === 'taskgroups' && getdata.Easycase.type_id != 13 && getdata.Easycase.type_id != 15) { %>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                            <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata.project_id] !='undefined' && customStatusByProject[getdata.project_id] != null){
$.each(customStatusByProject[getdata.project_id], function (key, data) {
if(getdata.CustomStatus.id != data.id){
%>
                            <% if(data.status_master_id == 3){ %>
                            <% if(isAllowed("Status change except Close",getdata.Project.uniq_id)){ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata.Easycase.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <% }else{ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata.Easycase.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <%   } 
}); 
} else{ %>
                            <% var caseFlag="";
    if(getdata.Easycase.legend != 1 && getdata.Easycase.type_id != 10){ caseFlag=9; }
    if(getdata.Easycase.isactive == 1){ %>
                            <li onclick="setNewCase(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>);" id="new<%= getdata.Easycase.id %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                            </li>
                            <% }
    if((getdata.Easycase.legend != 2 && getdata.Easycase.legend != 4) && getdata.Easycase.type_id!= 10) { caseFlag=1; }
                        if(getdata.Easycase.isactive == 1) { %>
                            <li onclick="startCase(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>);" id="start<%= getdata.Easycase.id %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE039;</i><% if(getdata.Easycase.legend == 1){ %><?php echo __('Start'); ?><% }else{ %><?php echo __('In Progress'); ?><% } %></a>
                            </li>
                            <% }
                        if((getdata.Easycase.legend != 5) && getdata.Easycase.type_id!= 10) { caseFlag=2; }
                        if(getdata.Easycase.isactive == 1){ %>
                            <li onclick="caseResolve(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>);" id="resolve<%= getdata.Easycase.id %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                            </li>
                            <% }
    if((getdata.Easycase.legend != 3) && getdata.Easycase.type_id != 10) { caseFlag=5; }
    if(getdata.Easycase.isactive == 1){ %>
                            <% if(isAllowed("Status change except Close",getdata.Project.uniq_id)){ %>
                            <li onclick="setCloseCase(<%= '\'' + getdata.Easycase.id + '\'' %>, <%= '\'' + getdata.Easycase.case_no + '\'' %>, <%= '\'' + getdata.Easycase.uniq_id + '\'' %>);" id="close<%= getdata.Easycase.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                            </li>
                            <% } %>
                            <% } %>
                            <% } %>
                        <?php } ?>
                    </ul>
                </span>
            </span>
            <% } %>
        </div>
    </td>
    <td class="due_dt_tlist text-center sb-tg-duedate" data-split="<%= getdata.is_splitted %>">
        <div class="<% if(csDueDate == '' || caseLegend == 5 || caseTypeId == 10 || caseLegend == 3){ %> toggle_due_dt <% } %>">
            <% if(isactive == 1){ %>
            <% if(showQuickActiononList && caseTypeId != 10){ %>
            <?php /*
<span class="glyphicon glyphicon-calendar" <% if(showQuickActiononList){ %>title="<?php echo __('Edit Due Date');?>"<% } %>></span>
*/ ?>
            <% } %>
            <span class="show_dt" id="showUpdDueDate<%= caseAutoId %>" title="<%= csDuDtFmtT %>">
                <%= csDuDtFmt %>
            </span>
            <span id="datelod<%= caseAutoId %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Update Task Duedate',projectUniqid)){ %> data-toggle="<% if(showQuickActiononList){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="pop_arrow_new"></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId + '\'' %> , <%= '\'' + caseNo + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId + '\', \'00/00/0000\', \'No Due Date\', \'' + caseUniqId + '\'' %> )"><?php echo __('No Due Date'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId + '\', \'' + mdyCurCrtd + '\', \'Today\', \'' + caseUniqId + '\'' %> )"><?php echo __('Today'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId + '\', \'' + mdyTomorrow + '\', \'Tomorrow\', \'' + caseUniqId + '\'' %> )"><?php echo __('Tomorrow'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId + '\', \'' + mdyMonday + '\', \'Next Monday\', \'' + caseUniqId + '\'' %> )"><?php echo __('Next Monday'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId + '\', \'' + caseNo + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId + '\', \'' + mdyFriday + '\', \'This Friday\', \'' + caseUniqId + '\'' %> )"><?php echo __('This Friday'); ?></a></li>
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
            </span>
        </div>
        <div class="overdueby_spns overdueby_spn_<%= caseAutoId %>"><% if(showQuickActiononList){ %><%= getdata.csDuDtFmtBy %><% } %></div>
    </td>
    <td class="due_dt_tlist text-center sb-tg-progress">
        <%= (getdata.completed_task)?getdata.completed_task :"0" %> %
    </td>
</tr>
<% 
if(getdata.children.length) {
var childe = getdata.children;
for (var key1 in childe) {
Easycase1= childe[key1];
Easycase1= childe[key1]['Easycase'];
CustomStatus1 = childe[key1]['CustomStatus'];
caseAutoId1=Easycase1['id'];
var isFavourite1 = Easycase1['isFavourite'];
var favMessage1 ="Set favourite";
if(isFavourite1){
var favMessage1 ="Remove favourite";
}
var favouriteColor1 = Easycase1['favouriteColor'];


projId1=Easycase1['project_id'];

caseLegend1 = Easycase1['custom_status_id'] != 0 ? Easycase1['custom_status_id'] : Easycase1['legend'];
caseTypeId1=Easycase1['type_id'];
caseNo1 = Easycase1['case_no'];
caseUniqId1 =Easycase1['uniq_id'];
caseUserId1= Easycase1['user_id'];
casePriority1 = Easycase1['priority'];
caseFormat1 = Easycase1['format'];
caseTitle1 = Easycase1['title'];
caseEstHoursRAW1 = Easycase1['estimated_hours'];

isactive1 = Easycase1['isactive'];
caseAssgnUid1 = Easycase1['assign_to'];
is_recurring1=Easycase1['is_recurring'];
csTdTyp1=childe[key1]['Type']['name'];
csDueDate1=Easycase1['csDueDate'];
csDuDtFmt1=Easycase1['csDuDtFmt'];
csDuDtFmtT1=Easycase1['csDuDtFmtT'];
count1++;
var showQuickActiononList1 = 0;
var showQuickActiononListEdit1 = 0;
if(isactive1 == 1 && (caseLegend1 != max_custom_status) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
showQuickActiononList1 = 1;
}
var showQuickActiononCopy1 = 0;
if(isactive1 == 1 && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
showQuickActiononCopy1 = 1;
}
if(isactive1 == 1 && (caseLegend1 != max_custom_status) && (caseUserId1 == SES_ID)){
showQuickActiononListEdit1 = 1;
}
if (childe.hasOwnProperty(key1)) { 
var getdata1 = childe[key1]; %>
<tr class="row_tr tr_all trans_row child_task_tr" id="curRow_subtask_<%= getdata1.id %>" data-mid="<%= d_mid %>">
    <td class="check_list_task tsk_fst_td pr_low text-left">
        <div class="checkbox">
            <label>
                <% if(getdata1.legend != 3 && getdata1.type_id != 10) { %>
                <input type="checkbox" style="cursor:pointer" id="actionChk<%= getdata1.id %>" value="<%= getdata1.id + '|' + getdata1.case_no + '|' + getdata1.uniq_id %>" class="fl mglt chkOneTsk">
                <% } else if(getdata1.type_id != 10) { %>
                <input type="checkbox" id="actionChk<%= getdata1.id %>" checked="checked" value="<%= getdata1.id + '|' + getdata1.case_no + '|closed' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } else { %>
                <input type="checkbox" id="actionChk<%= getdata1.id %>" checked="checked" value="<%= getdata1.id + '|' + getdata1.case_no + '|update' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } %>
            </label>
        </div>
        <input type="hidden" id="actionCls<%= getdata1.id %>" value="1" disabled="disabled" size="2">
        <div class="check-drop-icon">
            <div class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                    <i class="material-icons">&#xE5D4;</i>
                </a>
                <ul class="dropdown-menu tsg_chng_action_menu">
                    <% if( SES_ID == caseUserId1) { caseFlag2=3; }
if(isactive1 == 1){ %>
                    <% if(showQuickActiononList1 || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit2) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <li onclick="editepic(<%= '\''+ caseUniqId1+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId1 %>" style=" <% if(showQuickActiononList1 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit1) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% }else{ %>
                    <li onclick="editask(<%= '\''+ caseUniqId1+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId1 %>" style=" <% if(showQuickActiononList1 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit1) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata1.project_id] !='undefined' && customStatusByProject[getdata1.project_id] != null){
if(getdata1.CustomStatus.status_master_id != 3){ %>
                        <li onclick="setCustomStatus(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.status_master_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.name  + '\'' %>);" id="new<%= getdata1.id %>">
                            <a href="javascript:void(0);">
                                <span style="background-color:#<%= lastCustomStatus.LastCS.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                <%= lastCustomStatus.LastCS.name %></a>
                        </li>
                        <%   } 
} else{ %>
                        <% var caseFlag="";
    if((getdata1.legend != 3) && getdata1.type_id != 10) { caseFlag=5; }
    if(getdata1.isactive == 1){ %>
                        <% if(isAllowed("Status change except Close",getdata1.Project.uniq_id)){ %>
                        <li onclick="setCloseCase(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>);" id="close<%= getdata1.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <?php } ?>
                    <% if(isAllowed("Create Task",projectUniqid)){ %>
                    <% 
        if(caseLegend1 != max_custom_status && caseTypeId1 != 10){ %>
                    <% if(isEpicTask(getdata.Easycase.type_id , getdata.actual_dt_created)){ %>
                    <li onclick="addSubtaskPopup(<%= '\'' + projectUniqid + '\'' %>,<%= '\'' + getdata1.id + '\'' %>,<%= '\'' + getdata1.project_id + '\'' %>,<%= '\'' + getdata1.uniq_id + '\'' %>,<%= '\'' + getdata1.title + '\'' %>);">
                        <a href="javascript:void(0);"><i class="material-icons"></i><?php echo __('Create Subtask'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>

                    <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="convertToParentTask(<%= '\''+ caseAutoId1+'\',\''+caseNo1+'\'' %>);" id="convertToTask<%= caseAutoId1 %>" style=" <% if(showQuickActiononList1){ %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE15A;</i><?php echo __('Convert To Parent'); ?></a>
                        </li>
                        <% } %>
                    <?php } ?>
                        <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                        <% if(caseLegend1 == max_custom_status){ %>
                        <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle1, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } else { %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle1, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                        <% if(caseLegend1 != max_custom_status && caseTypeId1 != 10){ %>
                        <% if(isAllowed("Start Timer",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="startTimer(<%= '\'' + caseAutoId1 + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle1,3)) + '\'' %>, <%= '\'' + caseUniqId1 + '\'' %>,<%= '\'' + projUniq + '\'' %>,<%= '\'' + escape(htmlspecialchars(projectName,3)) + '\'' %>);">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE425;</i><?php echo __('Start Timer'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <% if(caseLegend1 == max_custom_status) { caseFlag1= 7; } else { caseFlag1= 8; }
if(isactive == 1){ %>
                    <% if(isAllowed("Reply on Task",projectUniqid)){ %>
                    <li id="subact_replys<%= count1 %>" data-task="<%= caseUniqId1 %>" page-refer-val="Task Group List Pages">
                        <a href="javascript:void(0);" id="reopen<%= caseAutoId1 %>" style="<% if(caseFlag1 == 7){ %>display:block <% } else { %>display:none<% } %>">
                            <div class="act_icon act_reply_task fl" title="<?php echo __('Re-open'); ?>"></div><i class="material-icons">&#xE898;</i> <?php echo __('Re-open'); ?>
                        </a>

                        <a href="javascript:void(0);" id="reply<%= caseAutoId1 %>" style="<% if(caseFlag1 == 8){ %>display:block <% } else { %>display:none<% } %>">
                            <i class="material-icons">&#xE15E;</i><?php echo __('Reply'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% if( SES_ID == caseUserId1) { caseFlag2=3; }
if(isactive1 == 1){ %>
                    <% if(showQuickActiononList1 || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit2) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <li onclick="editepic(<%= '\''+ caseUniqId1+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId1 %>" style=" <% if(showQuickActiononList1 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit1) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% }else{ %>
                    <li onclick="editask(<%= '\''+ caseUniqId1+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId1 %>" style=" <% if(showQuickActiononList1 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit1) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %>
                    <li onclick="copytask(<%= '\''+ caseUniqId1+'\',\''+ caseAutoId1+'\',\''+caseNo1+'\',\''+projId1+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="copy<%= caseAutoId1 %>" style=" <% if(showQuickActiononCopy1){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i><?php echo __('Copy'); ?></a>
                    </li>
                    <% } %>

                    <% } %>
                    <% if((caseLegend1 != max_custom_status) && caseTypeId1!= 10) { caseFlag2=2; }
if((SES_TYPE == 1 || SES_TYPE == 2) || (((caseLegend1 == 1 || caseLegend1 == 2 || caseLegend1 == 4) || (caseLegend1 != max_custom_status)) &&  (SES_ID == caseUserId1))){ %>
                    <% if(isactive1 == 1){ %>
                    <% if(isAllowed("Move to Project",projectUniqid)){ %>
                    <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                    <li data-prjid="<%= projId1 %>" data-caseid="<%= caseAutoId1 %>" data-caseno="<%= caseNo1 %>" id="mv_prj<%= caseAutoId1 %>" style=" " onclick="mvtoProject( <%= '\'' + count1 + '\'' %> , this);">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to Project'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isactive1 == 1){ %>
                    <% if(isAllowed("Move to Milestone",projectUniqid)){ %>
                    <li onclick="moveTask( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + caseNo1 + '\'' %> , <%= '\'\'' %> , <%= '\'' + projId1 + '\'' %> );" id="moveTask<%= caseAutoId1 %>" style=" <% if(caseFlag1 == 2){ %> display:block <% } else { %> display:block <% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE89F;</i><?php echo __('Move to Task Group'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId1) || isAllowed("Archive All Task",projectUniqid)) { caseFlag1 = "archive"; }
if(isactive1 == 1){ %>
                    <% if(isAllowed("Archive Task",projectUniqid) || isAllowed("Archive All Task",projectUniqid)){ %>
                    <li onclick="archiveCase( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + caseNo1 + '\'' %> , <%= '\'' + projId1 + '\'' %> , <%= '\'t_' + caseUniqId1 + '\'' %> );" id="arch<%= caseAutoId1 %>" style="<% if(caseFlag1 == "archive"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE149;</i><?php echo __('Archive'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <%	if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId1) || isAllowed("Delete All Task",projectUniqid)) { caseFlag1 = "delete"; }
if(isactive == 1){ %>
                    <% if(isAllowed("Delete Task",projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                    <li onclick="deleteCase( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + caseNo1 + '\'' %> , <%= '\'' + projId1 + '\'' %> , <%= '\'t_' + caseUniqId1 + '\'' %> , <%= '\'' + is_recurring1 + '\'' %>);" id="arch<%= caseAutoId1 %>" style="<% if(caseFlag1 == "delete"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                </ul>
            </div>
        </div>
    </td>
    <td class="favo-td">
        <span id="caseProjectSpanFav<%=caseAutoId1 %>">
            <a href="javascript:void(0);" class="caseFav" onclick="setCaseFavourite(<%=caseAutoId1 %>,<%=projId1 %>,<%= '\''+caseUniqId1+'\'' %>,1,<%=isFavourite1%>)" rel="tooltip" original-title="<%=favMessage1%>" style="margin-top:0px;color:<%=favouriteColor1%>;">
                <% if(isFavourite1) { %>
                <i class="material-icons" style="font-size:18px;">star</i>
                <% }else{ %>
                <i class="material-icons" style="font-size:18px;">star_border</i>
                <% } %>
            </a>
        </span>
    </td>
    <td class="text-left count-plist-drop pr">
        <%= getdata1.case_no %> <span class="watch showtime_<%= getdata1.id %>"></span>

    </td>
    <td class="text-left relative list-cont-td label_task_tle" id="tour_task_title_listing">
        <?php /*			   
<span class="ttype_global tt_<%= getttformats(getdata1.Type.name)%>"></span>  
*/ ?>
        <%
var priorClass = 'prio_low';
if(getdata1.priority == 1){
priorClass = 'prio_medium';
}else if(getdata1.priority == 0){
priorClass = 'prio_high';
}
%>
        <div style="" id="pridiv<%= caseAutoId1 %>" class="pri_actions <% if(showQuickActiononList1){ %> dropdown<% } %>">
            <div class="dropdown cmn_h_det_arrow">
                <div <% if(showQuickActiononList1){ %> class="quick_action" <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %> data-toggle="dropdown" <% } %> <% } %> style="cursor:pointer"><span class=" priority <%= priorClass %> prio_lmh prio_gen prio-drop-icon" rel="tooltip" title="<?php echo __('Priority'); ?>"></span><% if(showQuickActiononList1){ %> <i class="tsk-dtail-drop material-icons">&#xE5C5;</i> <% } %> </div>
                <% var csLgndRep1 = caseLegend1; %>
                <% if(showQuickActiononList1){ %>
                <ul class="dropdown-menu quick_menu">
                    <li class="low_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId1 + '\', \'2\', \'' + caseUniqId1 + '\', \'' + caseNo1 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Low'); ?></a></li>
                    <li class="medium_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId1 + '\', \'1\', \'' + caseUniqId1 + '\', \'' + caseNo1 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Medium'); ?></a></li>
                    <li class="high_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId1 + '\', \'0\', \'' + caseUniqId1 + '\', \'' + caseNo1 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('High'); ?></a></li>
                    <li class="urgent_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId1 + '\', \'3\', \'' + caseUniqId1 + '\', \'' + caseNo1 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Urgent'); ?></a></li>
                </ul>
                <% } %>
            </div>
        </div>
        <span id="prilod<%= caseAutoId1 %>" style="display:none">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>
        <div class="ttl_listing" data-task-id="<%= getdata1.uniq_id %>">
            <a href="javascript:void(0);" class="titlehtml" data-task="<%= getdata1.uniq_id %>">
                <span class="case-title_<%= getdata1.id %> case_sub_task <% if(getdata1.type_id!=10 && (getdata1.legend == max_custom_status || getdata1.custom_status_id == max_custom_status)) { %>closed_tsk<% } %>">
                    <span class="max_width_tsk_title ellipsis-view <% if(getdata1.legend == 5){%>resolve_tsk<% } %> case_title wrapword task_title_ipad <% if(getdata1.title && getdata1.title.length>100){%>overme<% }%> " title="<%= formatText(ucfirst(getdata1.title)) %>  ">
                        <%= formatText(ucfirst(getdata1.title)) %>
                    </span>
                </span>
            </a>

            <div class="list-td-hover-cont">
                <?php /* <span class="created-txt"><% if(getdata1.case_count!=0) { %><?php echo __('Updated');?><% } else { %><?php echo __('Created');?><% } %> <?php echo __('by');?> <%= getdata1.User.name %> <?php echo __('on');?> <%= moment(getdata1.dt_created).format("LLLL") %></span> */ ?>
                <span class="created-txt"><% if(getdata1.case_count!=0) { %><?php echo __('Updated'); ?><% } else { %><?php echo __('Created'); ?><% } %> <?php echo __('on'); ?> <%= moment(getdata1.dt_created).format("lll") %></span>
                <span class="list-devlop-txt dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons tag_fl">&#xE54E;</i>
                        <span id="showUpdStatus<%= caseAutoId1 %>" class="<% if(showQuickActiononList1 && isactive1 == 1){ %>clsptr<% } %>" title="<%= csTdTyp1 %>">
                            <span class="tsktype_colr" id="tsktype<%= caseAutoId1 %>"><%= csTdTyp1 %><span class="due_dt_icn"></span>
                            </span>
                        </span>
                    </a>
                    <span class="check-drop-icon dsp-block">
                        <span class="dropdown">
                            <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                                <i class="material-icons">&#xE5C5;</i>
                            </a>
                            <span id="typlod<%= caseAutoId1 %>" class="type_loader">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                            <% if(showQuickActiononList1 && isactive1 == 1){ %>
                            <ul class="dropdown-menu listgrp-bug-dropdn">
                                <li>
                                    <input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitems(this);" />
                                </li>
                                <%
for(var k in GLOBALS_TYPE) {
    if(GLOBALS_TYPE[k].Type.project_id == 0 || GLOBALS_TYPE[k].Type.project_id == getdata1.project_id){
var v = GLOBALS_TYPE[k];
var t = v.Type.id;
var t1 = v.Type.short_name;
var t2 = v.Type.name;
var txs_typ = t2;
$.each(DEFAULT_TASK_TYPES, function(i,n) {
if(i == t1){
txs_typ = n;
}
});
%>
                                <li onclick="changeCaseType( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + caseNo1 + '\'' %> ); changestatus( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + t + '\'' %> , <%= '\'' + t1 + '\'' %> , <%= '\'' + t2 + '\'' %> , <%= '\'' + caseUniqId1 + '\'' %> )">
                                    <a href="javascript:void(0);">
                                        <span class="ttype_global tt_<%= getttformats(t2)%>"><%= t2 %></span>
                                    </a>
                                </li>
                                <% } } %>
                            </ul>
                            <% } %>
                        </span>
                    </span>
            </div>
            <div class="task_dependancy_item">
                <div class="task_dependancy fr">
                    <% if(getdata1.Easycase.children && getdata1.Easycase.children != ""){ %>
                    <span class="fl case_act_icons task_parent_block" id="task_parent_block_<%= caseUniqId1 %>">
                        <div rel="" title="<?php echo __('Parents'); ?>" onclick="showParents(<%= '\'' + caseAutoId1 + '\'' %>,<%= '\'' + caseUniqId1 + '\'' %>,<%= '\'' + getdata1.Easycase.children + '\'' %>);" class=" task_title_icons_parents fl"></div>
                        <div class="dropdown dropup fl1 open1 showParents">
                            <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                <li class="pop_arrow_new"></li>
                                <li class="task_parent_msg" style=""><?php echo __('These tasks are waiting on this task'); ?>.</li>
                                <li>
                                    <ul class="task_parent_items" id="task_parent_<%= caseUniqId1 %>" style="">
                                        <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </span>
                    <% } %>
                    <% if(getdata1.depends && getdata1.depends != ""){ %>
                    <span class="fl case_act_icons task_dependent_block" id="task_dependent_block_<%= caseUniqId1 %>">
                        <div rel="" title="<?php echo __('Dependents'); ?>" onclick="showDependents(<%= '\'' + caseAutoId1 + '\'' %>,<%= '\'' + caseUniqId1 + '\'' %>,<%= '\'' + getdata1.depends + '\'' %>);" class=" task_title_icons_depends fl"></div>
                        <div class="dropdown dropup fl1 open1 showDependents">
                            <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                <li class="pop_arrow_new"></li>
                                <li class="task_dependent_msg" style=""><?php echo __("Task can't start. Waiting on these task to be completed"); ?>".</li>
                                <li>
                                    <ul class="task_dependent_items" id="task_dependent_<%= caseUniqId1 %>" style="">
                                        <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </span>
                    <% } %>
                </div>

                <div class="subcls_rcrng fr">
                    <% if(getdata1.is_recurring == 1 || getdata1.is_recurring == 2){ %>
                    <a rel="tooltip" title="<?php echo __('Recurring Task'); ?>" href="javascript:void(0);" onclick="showRecurringInfo(<%= caseAutoId1 %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></a>
                    <% } %>
                </div>
                <div class="cb"></div>

            </div>
            <?php /*
<% if(getdata1.is_recurring == 1 || getdata1.is_recurring == 2){ %>
<a rel="tooltip" title="<?php echo __('Recurring Task');?>" href="javascript:void(0);" onclick="showRecurringInfo(<%= caseAutoId1 %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></a>
<% } %>
*/ ?>
        </div>
    </td>
    <td class="attach-file-comment text-center">
        <a href="javascript:void(0);" style="display:none;" id="fileattch1"> <i class="glyphicon glyphicon-paperclip"></i> </a> <a href="javascript:void(0)" id="repno1" style="display:none"> <i class="material-icons"></i> </a>
    </td>
    <td class="assi_tlist sb-tg-assign">
        <div class="user-task-pf">
            <% if(!getdata1.AssignTo.photo){ getdata1.AssignTo.photo = 'user.png'; } %>
            <% var usr_name_fst = (getdata1.AssignTo.name != null)?getdata1.AssignTo.name:"<?php echo __("Unassigned"); ?>"; %>
            <i class="material-icons">&#xE7FD;</i>
            <% if((projUniq != 'all') && showQuickActiononList1){ %>
            <span id="showUpdAssign<%= caseAutoId1 %>" <% if(isAllowed("Change Assigned to",projectUniqid)){ %> data-toggle="dropdown" <% } %>title="<%= usr_name_fst %>" class="clsptr" onclick="displayAssignToMem( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + projUniq + '\'' %> , <%= '\'' + caseAssgnUid1 + '\'' %> , <%= '\'' + caseUniqId1 + '\'' %> )"><%= usr_name_fst %><span class="due_dt_icn"></span></span>
            <% } else { %>
            <span id="showUpdAssign<%= caseAutoId1 %>" style="cursor:text;text-decoration:none;color:#a7a7a7;"><%= usr_name_fst %></span>
            <% } %>
            <% if((projUniq != "all") && showQuickActiononList1){ %>
            <span id="asgnlod<%= caseAutoId1 %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block" <% if((projUniq != "all") && showQuickActiononList1){ %> onclick="displayAssignToMem(<%= '\'' + caseAutoId1 + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid1 + '\'' %>,<%= '\'' + caseUniqId1 + '\'' %>)" <% } %>>
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed("Change Assigned to",projectUniqid)){ %> data-toggle="<% if((projUniq != "all") && showQuickActiononList1){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu asgn_dropdown-caret" id="showAsgnToMem<%= caseAutoId1 %>">
                        <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId1 %>" /></li>
                    </ul>
                </span>
            </span>
        </div>
    </td>
    <td class="esthrs_dt_tlist text-center sb-tg-esthours">
        <p class="<?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> estblists <?php } ?> estblist_subtask" <?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> style="cursor:pointer;" <?php } ?> id="est_blist_sub<%= getdata1.id %>" data-split="<%= getdata1.is_splitted %>" case-id-val="<%= getdata1.id %>">
            <span class="border_dashed_subtask">
                <%= format_time_hr_min(getdata1.estimated_hours) %>
            </span>
        </p>
        <% var est_time = Math.floor(caseEstHoursRAW1/3600)+':'+(Math.round(Math.floor(caseEstHoursRAW%3600)/60)<10?"0":"")+Math.round(Math.floor(caseEstHoursRAW%3600)/60); %>

        <input type="text" data-est-id="<%=caseAutoId1%>" data-est-no="<%=caseNo1%>" data-est-uniq="<%=caseUniqId1%>" data-est-time="<%=est_time%>" id="est_hr_sub_list<%=caseAutoId1%>" class="est_hr_sub_list form-control check_minute_range" style="margin-bottom: 2px;display:none;" maxlength="5" rel="tooltip" title="<?php echo __('You can enter time as 1.5(that mean 1 hour and 30 minutes)'); ?>" onkeypress="return numeric_decimal_colon(event)" value="<%= est_time %>" placeholder="hh:mm" data-default-val="<%=est_time%>" />

        <span id="estsublod<%=caseAutoId1%>" style="display:none;margin-left:0px;">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>
    </td>
    <td class="text-center sb-tg-status">
        <div class="cs_select_dropdown">
            <span id="csStsRep_sub<%= getdata1.id %>" class="cs_select_status">
                <% if(getdata1.isactive==0){ %>
                <div class="label new" style="background-color: olive"><?php echo __('Archived'); ?></div>
                <%}else {
if(getdata1.custom_status_id != 0 && getdata1.CustomStatus != null ){ %>
                <%= easycase.getCustomStatus(getdata1.CustomStatus, getdata1.custom_status_id) %>
                <% }else{ %>
                <%= easycase.getStatus(getdata1.type_id, getdata1.legend) %>
                <% }
} %>
            </span>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                            <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata1.project_id] !='undefined' && customStatusByProject[getdata1.project_id] != null){
$.each(customStatusByProject[getdata1.project_id], function (key, data) {
if(getdata1.CustomStatus.id != data.id){
%>
                            <% if(data.status_master_id == 3){ %>
                            <% if(isAllowed("Status change except Close",getdata1.Project.uniq_id)){ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata1.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <% }else{ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata1.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <%   } 
}); 
} else{ %>
                            <% var caseFlag="";
    if(getdata1.legend != 1 && getdata1.type_id != 10){ caseFlag=9; }
    if(getdata1.isactive == 1){ %>
                            <li onclick="setNewCase(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>);" id="new<%= getdata1.id %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                            </li>
                            <% }
    if((getdata1.legend != 2 && getdata1.legend != 4) && getdata1.type_id!= 10) { caseFlag=1; }
                        if(getdata1.isactive == 1) { %>
                            <li onclick="startCase(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>);" id="start<%= getdata1.id %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE039;</i><% if(getdata1.legend == 1){ %><?php echo __('Start'); ?><% }else{ %><?php echo __('In Progress'); ?><% } %></a>
                            </li>
                            <% }
                        if((getdata1.legend != 5) && getdata1.type_id!= 10) { caseFlag=2; }
                        if(getdata1.isactive == 1){ %>
                            <li onclick="caseResolve(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>);" id="resolve<%= getdata.Easycase.id %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                            </li>
                            <% }
    if((getdata1.legend != 3) && getdata1.type_id != 10) { caseFlag=5; }
    if(getdata1.isactive == 1){ %>
                            <% if(isAllowed("Status change except Close",getdata1.Project.uniq_id)){ %>
                            <li onclick="setCloseCase(<%= '\'' + getdata1.id + '\'' %>, <%= '\'' + getdata1.case_no + '\'' %>, <%= '\'' + getdata1.uniq_id + '\'' %>);" id="close<%= getdata1.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                            </li>
                            <% } %>
                            <% } %>
                            <% } %>
                        <?php } ?>
                    </ul>
                </span>
            </span>
        </div>
    </td>
    <td class="due_dt_tlist text-center sb-tg-duedate" data-split="<%= getdata1.is_splitted %>">
        <div class="<% if(csDueDate1 == '' || caseLegend1 == 5 || caseTypeId1 == 10 || caseLegend1 == 3){ %> toggle_due_dt <% } %>">
            <% if(isactive == 1){ %>
            <% if(showQuickActiononList1 && caseTypeId1 != 10){ %>
            <?php /*
<span class="glyphicon glyphicon-calendar" <% if(showQuickActiononList1){ %>title="<?php echo __('Edit Due Date');?>"<% } %>></span>
*/ ?>
            <% } %>
            <span class="show_dt" id="showUpdDueDate<%= caseAutoId1 %>" title="<%= csDuDtFmtT1 %>">
                <%= csDuDtFmt1 %>
            </span>
            <span id="datelod<%= caseAutoId1 %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Update Task Duedate',projectUniqid)){ %> data-toggle="<% if(showQuickActiononList1){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="pop_arrow_new"></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId1 + '\'' %> , <%= '\'' + caseNo1 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId1 + '\', \'00/00/0000\', \'No Due Date\', \'' + caseUniqId1 + '\'' %> )"><?php echo __('No Due Date'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId1 + '\', \'' + caseNo1 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId1 + '\', \'' + mdyCurCrtd + '\', \'Today\', \'' + caseUniqId1 + '\'' %> )"><?php echo __('Today'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId1+ '\', \'' + caseNo1 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId1 + '\', \'' + mdyTomorrow + '\', \'Tomorrow\', \'' + caseUniqId1 + '\'' %> )"><?php echo __('Tomorrow'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId1 + '\', \'' + caseNo1 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId1 + '\', \'' + mdyMonday + '\', \'Next Monday\', \'' + caseUniqId1 + '\'' %> )"><?php echo __('Next Monday'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId1 + '\', \'' + caseNo1 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId1 + '\', \'' + mdyFriday + '\', \'This Friday\', \'' + caseUniqId1 + '\'' %> )"><?php echo __('This Friday'); ?></a></li>
                        <li>
                            <a href="javascript:void(0);">
                                <div class="cstm-dt-option-dtpik prtl">
                                    <div class="cstm-dt-option" data-csatid="<%= caseAutoId1 %>" style="position:absolute; left:0px; top:0px; z-index:99999999;">
                                        <input data-csatid="<%= caseAutoId1 %>" value="" type="text" id="set_due_date_<%= caseAutoId1 %>" class="set_due_date hide_corsor" title="<?php echo __('Custom Date'); ?>" style="background:none; border:0px;" />
                                    </div>
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    <span style="position:relative;top:2px;"><?php echo __('Custom'); ?>&nbsp;<?php echo __('Date'); ?></span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </span>
            </span>
        </div>
        <div class="overdueby_spns overdueby_spn_<%= caseAutoId1 %>"><% if(showQuickActiononList1){ %><%= getdata1.csDuDtFmtBy %><% } %></div>
    </td>
    <td class="due_dt_tlist text-center sb-tg-progress">
        <%= (getdata1.completed_task)?getdata1.completed_task :"0" %> %
    </td>
</tr>
<% 
if(getdata1.children.length) {
var childe1 = getdata1.children;
for (var key2 in childe1) {
Easycase2= childe1[key2];
Easycase2= childe1[key2]['Easycase'];
CustomStatus2 = childe1[key2]['CustomStatus'];
caseAutoId2=Easycase2['id'];
var isFavourite2 = Easycase2['isFavourite'];
var favMessage2 ="Set favourite";
if(isFavourite2){
var favMessage2 ="Remove favourite";
}
var favouriteColor2 = Easycase2['favouriteColor'];


projId2=Easycase2['project_id'];

caseLegend2 = Easycase2['custom_status_id'] != 0 ? Easycase2['custom_status_id'] : Easycase2['legend'];
caseTypeId2=Easycase2['type_id'];
caseNo2 = Easycase2['case_no'];
caseUniqId2 =Easycase2['uniq_id'];
caseUserId2 = Easycase2['user_id'];
casePriority2 = Easycase2['priority'];
caseFormat2 = Easycase2['format'];
caseTitle2 = Easycase2['title'];
caseEstHoursRAW2 = Easycase2['estimated_hours'];

isactive2 = Easycase2['isactive'];
caseAssgnUid2 = Easycase2['assign_to'];
is_recurring2=Easycase2['is_recurring'];
csTdTyp2=childe1[key2]['Type']['name'];
csDueDate2=Easycase1['csDueDate'];
csDuDtFmt2=Easycase1['csDuDtFmt'];
csDuDtFmtT2=Easycase1['csDuDtFmtT'];
count2++;
var showQuickActiononList2 = 0;
var showQuickActiononListEdit2 = 0;
if(isactive2 == 1 && (caseLegend2 != max_custom_status) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
showQuickActiononList2 = 1;
}
var showQuickActiononCopy2 = 0;
if(isactive2 == 1 && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (caseUserId== SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))){
showQuickActiononCopy2 = 1;
}
if(isactive2 == 1 && (caseLegend2 != max_custom_status) && (caseUserId2 == SES_ID)){
showQuickActiononListEdit2 = 1;
}
if (childe1.hasOwnProperty(key2)) { 
var getdata2 = childe1[key2]; %>
<tr class="row_tr tr_all trans_row sub_child_task_tr " id="curRow_subtask_<%= getdata2.id %>" data-mid="<%= d_mid %>">
    <td class="check_list_task tsk_fst_td pr_low text-left">
        <div class="checkbox">
            <label>
                <% if(getdata2.legend != 3 && getdata2.type_id != 10) { %>
                <input type="checkbox" style="cursor:pointer" id="actionChk<%= getdata2.id %>" value="<%= getdata2.id + '|' + getdata2.case_no + '|' + getdata2.uniq_id %>" class="fl mglt chkOneTsk">
                <% } else if(getdata2.type_id != 10) { %>
                <input type="checkbox" id="actionChk<%= getdata2.id %>" checked="checked" value="<%= getdata2.id + '|' + getdata2.case_no + '|closed' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } else { %>
                <input type="checkbox" id="actionChk<%= getdata2.id %>" checked="checked" value="<%= getdata2.id + '|' + getdata2.case_no + '|update' %>" disabled="disabled" class="fl mglt chkOneTsk">
                <% } %>

            </label>
        </div>
        <input type="hidden" id="actionCls<%= getdata2.id %>" value="1" disabled="disabled" size="2">
        <div class="check-drop-icon">
            <div class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                    <i class="material-icons">&#xE5D4;</i>
                </a>
                <ul class="dropdown-menu tsg_chng_action_menu">
                    <% if( SES_ID == caseUserId2) { caseFlag2=3; }
if(isactive2 == 1){ %>
                    <% if(showQuickActiononList2 || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit2) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <li onclick="editepic(<%= '\''+ caseUniqId2+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId2 %>" style=" <% if(showQuickActiononList2 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit2) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% }else{ %>
                    <li onclick="editask(<%= '\''+ caseUniqId2+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId2 %>" style=" <% if(showQuickActiononList2 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit2) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                        <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata2.project_id] !='undefined' && customStatusByProject[getdata2.project_id] != null){
if(getdata2.CustomStatus.status_master_id != 3){ %>
                        <li onclick="setCustomStatus(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.status_master_id + '\'' %>,<%= '\'' + lastCustomStatus.LastCS.name  + '\'' %>);" id="new<%= getdata2.id %>">
                            <a href="javascript:void(0);">
                                <span style="background-color:#<%= lastCustomStatus.LastCS.color %>;height: 11px;width: 11px;display: inline-block;"></span>
                                <%= lastCustomStatus.LastCS.name %></a>
                        </li>
                        <%   } 
} else{ %>
                        <% var caseFlag="";
    if((getdata2.legend != 3) && getdata2.type_id != 10) { caseFlag=5; }
    if(getdata2.isactive == 1){ %>
                        <% if(isAllowed("Status change except Close",getdata2.Project.uniq_id)){ %>
                        <li onclick="setCloseCase(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>);" id="close<%= getdata2.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <?php } ?>
                    <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="convertToParentTask(<%= '\''+ caseAutoId2+'\',\''+caseNo2+'\'' %>);" id="convertToTask<%= caseAutoId2 %>" style=" <% if(showQuickActiononList2){ %>display:block <% } else { %>display:none<% } %>">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE15A;</i><?php echo __('Convert To Parent'); ?></a>
                        </li>
                        <% } %>
                    <?php } ?>
                        <% if(isAllowed("Manual Time Entry",projectUniqid)){ %>
                        <% if(caseLegend2 == max_custom_status){ %>
                        <% if(isAllowed("Time Entry On Closed Task",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle2, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } else{ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="createlog( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + escape(htmlspecialchars(caseTitle2, 3)) + '\'' %> );" class="anchor">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE192;</i><?php echo __('Time Entry'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                        <% if(caseLegend2 !=3 && caseTypeId2 != 10){ %>
                        <% if(isAllowed("Start Timer",projectUniqid)){ %>
                        <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                        <li onclick="startTimer(<%= '\'' + caseAutoId2 + '\'' %>,<%= '\'' + escape(htmlspecialchars(caseTitle2,3)) + '\'' %>, <%= '\'' + caseUniqId2 + '\'' %>,<%= '\'' + projUniq + '\'' %>,<%= '\'' + escape(htmlspecialchars(projectName,3)) + '\'' %>);">
                            <a href="javascript:void(0);"><i class="material-icons">&#xE425;</i><?php echo __('Start Timer'); ?></a>
                        </li>
                        <% } %>
                        <% } %>
                        <% } %>
                    <% if(caseLegend2 == max_custom_status) { caseFlag2= 7; } else { caseFlag2= 8; }
if(isactive2 == 1){ %>
                    <% if(isAllowed("Reply on Task",projectUniqid)){ %>
                    <li id="subact_replys<%= count2 %>" data-task="<%= caseUniqId2 %>" page-refer-val="Task Group List Pages">
                        <a href="javascript:void(0);" id="reopen<%= caseAutoId2 %>" style="<% if(caseFlag2 == 7){ %>display:block <% } else { %>display:none<% } %>">
                            <div class="act_icon act_reply_task fl" title="<?php echo __('Re-open'); ?>"></div><i class="material-icons">&#xE898;</i> <?php echo __('Re-open'); ?>
                        </a>

                        <a href="javascript:void(0);" id="reply<%= caseAutoId2 %>" style="<% if(caseFlag2 == 8){ %>display:block <% } else { %>display:none<% } %>">
                            <i class="material-icons">&#xE15E;</i><?php echo __('Reply'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% if( SES_ID == caseUserId2) { caseFlag2=3; }
if(isactive2 == 1){ %>
                    <% if(showQuickActiononList2 || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if((isAllowed("Edit Task",projectUniqid) && showQuickActiononListEdit2) || isAllowed("Edit All Task",projectUniqid)){ %>
                    <% if(getdata.Easycase.type_id  == getdata.Easycase.original_epic_id){ %>
                    <li onclick="editepic(<%= '\''+ caseUniqId2+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId2 %>" style=" <% if(showQuickActiononList2 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit2) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% }else{ %>
                    <li onclick="editask(<%= '\''+ caseUniqId2+'\',\''+projectUniqid+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="edit<%= caseAutoId2 %>" style=" <% if(showQuickActiononList2 || isAllowed('Edit All Task',projectUniqid) || (isAllowed('Edit Task',projectUniqid) && showQuickActiononListEdit2) ){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %>
                    <li onclick="copytask(<%= '\''+ caseUniqId2+'\',\''+ caseAutoId2+'\',\''+caseNo2+'\',\''+projId2+'\',\''+htmlspecialchars(projectName)+'\'' %>);" id="copy<%= caseAutoId2 %>" style=" <% if(showQuickActiononCopy2){ %>display:block <% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE14D;</i><?php echo __('Copy'); ?></a>
                    </li>
                    <% } %>

                    <% } %>
                    <% if((caseLegend2 != max_custom_status) && caseTypeId2!= 10) { caseFlag2=2; }
if((SES_TYPE == 1 || SES_TYPE == 2) || (((caseLegend2 == 1 || caseLegend2 == 2 || caseLegend2 == 4) || (caseLegend2 != max_custom_status)) &&  (SES_ID == caseUserId2))){ %>
                    <% if(isactive2 == 1){ %>
                    <% if(isAllowed("Move to Project",projectUniqid)){ %>
                    <% if(isEpicTask(getdata.Easycase.type_id , getdata.Easycase.original_epic_id, getdata.actual_dt_created)){ %>
                    <li data-prjid="<%= projId2 %>" data-caseid="<%= caseAutoId2 %>" data-caseno="<%= caseNo2 %>" id="mv_prj<%= caseAutoId2 %>" style=" " onclick="mvtoProject( <%= '\'' + count2 + '\'' %> , this);">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE8D4;</i><?php echo __('Move to Project'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% } %>
                    <% if(isactive2 == 1){ %>
                    <% if(isAllowed("Move to Milestone",projectUniqid)){ %>
                    <li onclick="moveTask( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + caseNo2 + '\'' %> , <%= '\'\'' %> , <%= '\'' + projId2 + '\'' %> );" id="moveTask<%= caseAutoId2 %>" style=" <% if(caseFlag2 == 2){ %> display:block <% } else { %> display:block <% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE89F;</i><?php echo __('Move to Task Group'); ?></a>
                    </li>
                    <% } %>
                    <% } %>

                    <% if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId2) || isAllowed("Archive All Task",projectUniqid)) { caseFlag2 = "archive"; }
if(isactive2 == 1){ %>
                    <% if(isAllowed("Archive Task",projectUniqid) || isAllowed("Archive All Task",projectUniqid)){ %>
                    <li onclick="archiveCase( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + caseNo2 + '\'' %> , <%= '\'' + projId2 + '\'' %> , <%= '\'t_' + caseUniqId2 + '\'' %> );" id="arch<%= caseAutoId2 %>" style="<% if(caseFlag2 == "archive"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE149;</i><?php echo __('Archive'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                    <%	if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == caseUserId2) || isAllowed("Delete All Task",projectUniqid)) { caseFlag2 = "delete"; }
if(isactive == 1){ %>
                    <% if(isAllowed("Delete Task",projectUniqid) || isAllowed("Delete All Task",projectUniqid)){ %>
                    <li onclick="deleteCase( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + caseNo2 + '\'' %> , <%= '\'' + projId2 + '\'' %> , <%= '\'t_' + caseUniqId2 + '\'' %> , <%= '\'' + is_recurring2 + '\'' %>);" id="arch<%= caseAutoId2 %>" style="<% if(caseFlag2 == "delete"){ %>display:block<% } else { %>display:none<% } %>">
                        <a href="javascript:void(0);"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                    </li>
                    <% } %>
                    <% } %>
                </ul>
            </div>
        </div>
    </td>
    <td class="favo-td">
        <span id="caseProjectSpanFav<%=caseAutoId2 %>">
            <a href="javascript:void(0);" class="caseFav" onclick="setCaseFavourite(<%=caseAutoId2 %>,<%=projId2 %>,<%= '\''+caseUniqId2+'\'' %>,1,<%=isFavourite2%>)" rel="tooltip" original-title="<%=favMessage2%>" style="margin-top:0px;color:<%=favouriteColor2%>;">
                <% if(isFavourite2) { %>
                <i class="material-icons" style="font-size:18px;">star</i>
                <% }else{ %>
                <i class="material-icons" style="font-size:18px;">star_border</i>
                <% } %>
            </a>
        </span>
    </td>
    <td class="text-left count-plist-drop pr">
        <%= getdata2.case_no %> <span class="watch showtime_<%= getdata2.id %>"></span>

    </td>
    <td class="relative list-cont-td label_task_tle text-left" id="tour_task_title_listing">
        <?php /*
<span class="ttype_global tt_<%= getttformats(getdata2.Type.name)%>"></span> 
*/ ?>
        <%
var priorClass = 'prio_low';
if(getdata2.priority == 1){
priorClass = 'prio_medium';
}else if(getdata2.priority == 0){
priorClass = 'prio_high';
}
%>
        <div style="" id="pridiv<%= caseAutoId2 %>" class="pri_actions <% if(showQuickActiononList2){ %> dropdown<% } %>">
            <div class="dropdown cmn_h_det_arrow">
                <div <% if(showQuickActiononList2){ %> class="quick_action" <% if(isAllowed("Change Other Details of Task",projectUniqid)){ %> data-toggle="dropdown" <% } %> <% } %> style="cursor:pointer"><span class=" priority <%= priorClass %> prio_lmh prio_gen prio-drop-icon" rel="tooltip" title="<?php echo __('Priority'); ?>"></span><% if(showQuickActiononList2){ %> <i class="tsk-dtail-drop material-icons">&#xE5C5;</i> <% } %></div>
                <% var csLgndRep2 = caseLegend2; %>
                <% if(showQuickActiononList2){ %>
                <ul class="dropdown-menu quick_menu">
                    <li class="low_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId2 + '\', \'2\', \'' + caseUniqId2 + '\', \'' + caseNo2 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Low'); ?></a></li>
                    <li class="medium_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId2 + '\', \'1\', \'' + caseUniqId2 + '\', \'' + caseNo2 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Medium'); ?></a></li>
                    <li class="high_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId2 + '\', \'0\', \'' + caseUniqId2 + '\', \'' + caseNo2 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('High'); ?></a></li>
                    <li class="urgent_priority"><a href="javascript:void(0);" onclick="detChangepriority( <%= '\'' + caseAutoId2 + '\', \'3\', \'' + caseUniqId2 + '\', \'' + caseNo2 + '\'' %> )"><span class="priority-symbol"></span><?php echo __('Urgent'); ?></a></li>
                </ul>
                <% } %>
            </div>
        </div>
        <span id="prilod<%= caseAutoId2 %>" style="display:none">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>
        <div class="ttl_listing" data-task-id="<%= getdata2.uniq_id %>">
            <a href="javascript:void(0);" class="titlehtml" data-task="<%= getdata2.uniq_id %>">
                <span class="case-title_<%= getdata2.id %> case_sub_task <% if(getdata2.type_id!=10 && (getdata2.legend == max_custom_status || getdata2.custom_status_id == max_custom_status)) { %>closed_tsk<% } %>">
                    <span class="max_width_tsk_title ellipsis-view <% if(getdata2.legend == 5){%>resolve_tsk<% } %> case_title wrapword task_title_ipad <% if(getdata2.title && getdata2.title.length>100){%>overme<% }%> " title="<%= formatText(ucfirst(getdata2.title)) %>  ">
                        <%= formatText(ucfirst(getdata2.title)) %>
                    </span>
                </span>
            </a>

            <div class="list-td-hover-cont">
                <?php /*<span class="created-txt"><% if(getdata2.case_count!=0) { %><?php echo __('Updated');?><% } else { %><?php echo __('Created');?><% } %> <?php echo __('by');?> <%= getdata2.User.name %> <?php echo __('on');?> <%= moment(getdata2.dt_created).format("LLLL") %></span> */ ?>
                <span class="created-txt"><% if(getdata2.case_count!=0) { %><?php echo __('Updated'); ?><% } else { %><?php echo __('Created'); ?><% } %> <?php echo __('on'); ?> <%= moment(getdata2.dt_created).format("lll") %></span>
                <span class="list-devlop-txt dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons tag_fl">&#xE54E;</i>
                        <span id="showUpdStatus<%= caseAutoId2 %>" class="<% if(showQuickActiononList2 && isactive2 == 1){ %>clsptr<% } %>" title="<%= csTdTyp2 %>">
                            <span class="tsktype_colr" id="tsktype<%= caseAutoId2 %>"><%= csTdTyp2 %><span class="due_dt_icn"></span>
                            </span>
                        </span>
                    </a>
                    <span class="check-drop-icon dsp-block">
                        <span class="dropdown">
                            <a class="dropdown-toggle" <% if(isAllowed('Change Other Details of Task',projectUniqid)){ %> data-toggle="dropdown" <% } %> href="javascript:void(0);" data-target="#">
                                <i class="material-icons">&#xE5C5;</i>
                            </a>
                            <span id="typlod<%= caseAutoId2 %>" class="type_loader">
                                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                            </span>
                            <% if(showQuickActiononList2 && isactive2 == 1){ %>
                            <ul class="dropdown-menu listgrp-bug-dropdn">
                                <li>
                                    <input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitems(this);" />
                                </li>
                                <% for(var k in GLOBALS_TYPE) { if(isDisplayEpicType(GLOBALS_TYPE[k].Type.name)){ if(GLOBALS_TYPE[k].Type.project_id == 0 || GLOBALS_TYPE[k].Type.project_id == getdata2.project_id){ var v = GLOBALS_TYPE[k]; var t = v.Type.id; var t1 = v.Type.short_name; var t2 = v.Type.name; var txs_typ = t2; $.each(DEFAULT_TASK_TYPES, function(i,n) { if(i == t1){ txs_typ = n; } }); %>
                                <li onclick="changeCaseType( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + caseNo2 + '\'' %> ); changestatus( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + t + '\'' %> , <%= '\'' + t1 + '\'' %> , <%= '\'' + t2 + '\'' %> , <%= '\'' + caseUniqId2 + '\'' %> )">
                                    <a href="javascript:void(0);">
                                        <span class="ttype_global tt_<%= getttformats(t2)%>"><%= t2 %></span>
                                    </a>
                                </li>
                                <% } } } %>
                            </ul>
                            <% } %>
                        </span>
                    </span>

                </span>
            </div>
            <div class="task_dependancy_item">
                <div class="task_dependancy fr">
                    <% if(getdata2.Easycase.children && getdata2.Easycase.children != ""){ %>
                    <span class="fl case_act_icons task_parent_block" id="task_parent_block_<%= caseUniqId2 %>">
                        <div rel="" title="<?php echo __('Parents'); ?>" onclick="showParents(<%= '\'' + caseAutoId2 + '\'' %>,<%= '\'' + caseUniqId2 + '\'' %>,<%= '\'' + getdata2.Easycase.children + '\'' %>);" class=" task_title_icons_parents fl"></div>
                        <div class="dropdown dropup fl1 open1 showParents">
                            <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                <li class="pop_arrow_new"></li>
                                <li class="task_parent_msg" style=""><?php echo __('These tasks are waiting on this task'); ?>.</li>
                                <li>
                                    <ul class="task_parent_items" id="task_parent_<%= caseUniqId2 %>" style="">
                                        <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </span>
                    <% } %>
                    <% if(getdata2.depends && getdata2.depends != ""){ %>
                    <span class="fl case_act_icons task_dependent_block" id="task_dependent_block_<%= caseUniqId2 %>">
                        <div rel="" title="<?php echo __('Dependents'); ?>" onclick="showDependents(<%= '\'' + caseAutoId2 + '\'' %>,<%= '\'' + caseUniqId2 + '\'' %>,<%= '\'' + getdata2.depends + '\'' %>);" class=" task_title_icons_depends fl"></div>
                        <div class="dropdown dropup fl1 open1 showDependents">
                            <ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
                                <li class="pop_arrow_new"></li>
                                <li class="task_dependent_msg" style=""><?php echo __("Task can't start. Waiting on these task to be completed"); ?>".</li>
                                <li>
                                    <ul class="task_dependent_items" id="task_dependent_<%= caseUniqId2 %>" style="">
                                        <li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT; ?>img/images/loader1.gif"></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </span>
                    <% } %>
                </div>

                <div class="subcls_rcrng fr">
                    <% if(getdata2.is_recurring == 1 || getdata2.is_recurring == 2){ %>
                    <a rel="tooltip" title="<?php echo __('Recurring Task'); ?>" href="javascript:void(0);" onclick="showRecurringInfo(<%= caseAutoId2 %>);" class="recurring-icon"><i class="material-icons">&#xE040;</i></a>
                    <% } %>
                </div>
                <div class="cb"></div>

            </div>
        </div>
    </td>
    <td class="attach-file-comment text-center">
        <a href="javascript:void(0);" style="display:none;" id="fileattch1"> <i class="glyphicon glyphicon-paperclip"></i> </a> <a href="javascript:void(0)" id="repno1" style="display:none"> <i class="material-icons"></i> </a>
    </td>
    <td class="assi_tlist sb-tg-assign">
        <div class="user-task-pf">
            <% if(!getdata2.AssignTo.photo){ getdata2.AssignTo.photo = 'user.png'; } %>
            <% var usr_name_fst = (getdata2.AssignTo.name != null)?getdata2.AssignTo.name:"<?php echo __("Unassigned"); ?>"; %>
            <i class="material-icons">&#xE7FD;</i>
            <% if((projUniq != 'all') && showQuickActiononList2){ %>
            <span id="showUpdAssign<%= caseAutoId2 %>" <% if(isAllowed("Change Assigned to",projectUniqid)){ %> data-toggle="dropdown" <% } %>title="<%= usr_name_fst %>" class="clsptr" onclick="displayAssignToMem( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + projUniq + '\'' %> , <%= '\'' + caseAssgnUid2 + '\'' %> , <%= '\'' + caseUniqId2 + '\'' %> )"><%= usr_name_fst %><span class="due_dt_icn"></span></span>
            <% } else { %>
            <span id="showUpdAssign<%= caseAutoId %>" style="cursor:text;text-decoration:none;color:#a7a7a7;"><%= usr_name_fst %></span>
            <% } %>
            <% if((projUniq != "all") && showQuickActiononList2){ %>
            <span id="asgnlod<%= caseAutoId2 %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block" <% if((projUniq != "all") && showQuickActiononList2){ %> onclick="displayAssignToMem(<%= '\'' + caseAutoId2 + '\'' %>, <%= '\'' + projUniq + '\'' %>,<%= '\'' + caseAssgnUid2 + '\'' %>,<%= '\'' + caseUniqId2 + '\'' %>)" <% } %>>
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed("Change Assigned to",projectUniqid)){ %> data-toggle="<% if((projUniq != "all") && showQuickActiononList2){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu asgn_dropdown-caret" id="showAsgnToMem<%= caseAutoId2 %>">
                        <li class="text-centre"><img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="assgnload<%= caseAutoId2 %>" /></li>
                    </ul>
                </span>
            </span>
        </div>
    </td>
    <td class="esthrs_dt_tlist text-center sb-tg-esthours">
        <p class="<?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> estblists <?php } ?> estblist_subtask" <?php if ($this->Format->isAllowed('Est Hours', $roleAccess)) { ?> style="cursor:pointer;" <?php } ?> id="est_blist_sub<%= getdata2.id %>" data-split="<%= getdata2.is_splitted %>" case-id-val="<%= getdata2.id %>">
            <span class="border_dashed_subtask">
                <%= format_time_hr_min(getdata2.estimated_hours) %>
            </span>
        </p>
        <% var est_time = Math.floor(caseEstHoursRAW2/3600)+':'+(Math.round(Math.floor(caseEstHoursRAW%3600)/60)<10?"0":"")+Math.round(Math.floor(caseEstHoursRAW%3600)/60); %>

        <input type="text" data-est-id="<%=caseAutoId2%>" data-est-no="<%=caseNo2%>" data-est-uniq="<%=caseUniqId2%>" data-est-time="<%=est_time%>" id="est_hr_sub_list<%=caseAutoId2%>" class="est_hr_sub_list form-control check_minute_range" style="margin-bottom: 2px;display:none;" maxlength="5" rel="tooltip" title="<?php echo __('You can enter time as 1.5(that mean 1 hour and 30 minutes)'); ?>" onkeypress="return numeric_decimal_colon(event)" value="<%= est_time %>" placeholder="hh:mm" data-default-val="<%=est_time%>" />

        <span id="estsublod<%=caseAutoId2%>" style="display:none;margin-left:0px;">
            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
        </span>

    </td>
    <td class="text-center sb-tg-status">
        <div class="cs_select_dropdown">
            <span id="csStsRep_sub<%= getdata2.id %>" class="cs_select_status">
                <% if(getdata2.isactive==0){ %>
                <div class="label new" style="background-color: olive"><?php echo __('Archived'); ?></div>
                <%}else { if(getdata2.custom_status_id != 0 && getdata2.CustomStatus != null ){ %>
                <%= easycase.getCustomStatus(getdata2.CustomStatus, getdata2.custom_status_id) %>
                <% }else{ %>
                <%= easycase.getStatus(getdata2.type_id, getdata2.legend) %>
                <% } } %>
            </span>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                            <% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[getdata2.project_id] !='undefined' && customStatusByProject[getdata2.project_id] != null){ $.each(customStatusByProject[getdata2.project_id], function (key, data) { if(getdata2.CustomStatus.id != data.id){ %>
                            <% if(data.status_master_id == 3){ %>
                            <% if(isAllowed("Status change except Close",getdata2.Project.uniq_id)){ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata2.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <% }else{ %>
                            <li onclick="setCustomStatus(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>,<%= '\'' + data.id + '\'' %>,<%= '\'' + data.status_master_id + '\'' %>,<%= '\'' +data.name  + '\'' %>);" id="new<%= getdata2.id %>">
                                <a href="javascript:void(0);">
                                    <span style="background-color:#<%= data.color %>;height: 11px;width: 11px;display: inline-block;"></span> <%= data.name %>
                                </a>
                            </li>
                            <% } %>
                            <%   } }); } else{ %>
                            <% var caseFlag=""; if(getdata2.legend != 1 && getdata2.type_id != 10){ caseFlag=9; } if(getdata2.isactive == 1){ %>
                            <li onclick="setNewCase(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>);" id="new<%= getdata2.id %>" style=" <% if(caseFlag == "9"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE166;</i><?php echo __('New'); ?></a>
                            </li>
                            <% } if((getdata2.legend != 2 && getdata2.legend != 4) && getdata2.type_id!= 10) { caseFlag=1; } if(getdata2.isactive == 1) { %>
                            <li onclick="startCase(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>);" id="start<%= getdata2.id %>" style=" <% if(caseFlag == "1"){ %>display:block<% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE039;</i><% if(getdata2.legend == 1){ %><?php echo __('Start'); ?><% }else{ %><?php echo __('In Progress'); ?><% } %></a>
                            </li>
                            <% } if((getdata2.legend != 5) && getdata2.type_id!= 10) { caseFlag=2; } if(getdata2.isactive == 1){ %>
                            <li onclick="caseResolve(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>);" id="resolve<%= getdata.Easycase.id %>" style=" <% if(caseFlag == 2){ %> display:block <% } else { %> display:none <% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE889;</i><?php echo __('Resolve'); ?></a>
                            </li>
                            <% } if((getdata2.legend != 3) && getdata2.type_id != 10) { caseFlag=5; } if(getdata2.isactive == 1){ %>
                            <% if(isAllowed("Status change except Close",getdata2.Project.uniq_id)){ %>
                            <li onclick="setCloseCase(<%= '\'' + getdata2.id + '\'' %>, <%= '\'' + getdata2.case_no + '\'' %>, <%= '\'' + getdata2.uniq_id + '\'' %>);" id="close<%= getdata2.id %>" style=" <% if(caseFlag == 5) { %>display:block <% } else { %>display:none<% } %>">
                                <a href="javascript:void(0);"><i class="material-icons">&#xE876;</i><?php echo __('Close'); ?></a>
                            </li>
                            <% } %>
                            <% } %>
                            <% } %>
                        <?php } ?>
                    </ul>
                </span>
            </span>
        </div>
    </td>
    <td class="due_dt_tlist text-center sb-tg-duedate" data-split="<%= getdata2.is_splitted %>">
        <div class="<% if(csDueDate2 == '' || caseLegend2 == 5 || caseTypeId2 == 10 || caseLegend2 == 3){ %> toggle_due_dt <% } %>">
            <% if(isactive == 1){ %>
            <span class="show_dt" id="showUpdDueDate<%= caseAutoId2 %>" title="<%= csDuDtFmtT2 %>">
                <%= csDuDtFmt2 %>
            </span>
            <span id="datelod<%= caseAutoId2 %>" class="asgn_loader">
                <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
            </span>
            <% } %>
            <span class="check-drop-icon dsp-block">
                <span class="dropdown">
                    <a class="dropdown-toggle" <% if(isAllowed('Update Task Duedate',projectUniqid)){ %> data-toggle="<% if(showQuickActiononList2){ %>dropdown<% } %>" <% } %> href="javascript:void(0);" data-target="#">
                        <i class="material-icons">&#xE5C5;</i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="pop_arrow_new"></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId2 + '\'' %> , <%= '\'' + caseNo2 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId2 + '\', \'00/00/0000\', \'No Due Date\', \'' + caseUniqId2 + '\'' %> )"><?php echo __('No Due Date'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId2 + '\', \'' + caseNo2 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId2 + '\', \'' + mdyCurCrtd + '\', \'Today\', \'' + caseUniqId2 + '\'' %> )"><?php echo __('Today'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId2+ '\', \'' + caseNo2 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId2 + '\', \'' + mdyTomorrow + '\', \'Tomorrow\', \'' + caseUniqId2 + '\'' %> )"><?php echo __('Tomorrow'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId2 + '\', \'' + caseNo2 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId2 + '\', \'' + mdyMonday + '\', \'Next Monday\', \'' + caseUniqId2 + '\'' %> )"><?php echo __('Next Monday'); ?></a></li>
                        <li><a href="javascript:void(0);" onclick="changeCaseDuedate( <%= '\'' + caseAutoId2 + '\', \'' + caseNo2 + '\'' %> ); changeDueDate( <%= '\'' + caseAutoId2 + '\', \'' + mdyFriday + '\', \'This Friday\', \'' + caseUniqId2 + '\'' %> )"><?php echo __('This Friday'); ?></a></li>
                        <li>
                            <a href="javascript:void(0);">
                                <div class="cstm-dt-option-dtpik prtl">
                                    <div class="cstm-dt-option" data-csatid="<%= caseAutoId2 %>" style="position:absolute; left:0px; top:0px; z-index:99999999;">
                                        <input data-csatid="<%= caseAutoId2 %>" value="" type="text" id="set_due_date_<%= caseAutoId2 %>" class="set_due_date hide_corsor" title="<?php echo __('Custom Date'); ?>" style="background:none; border:0px;" />
                                    </div>
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    <span style="position:relative;top:2px;"><?php echo __('Custom'); ?>&nbsp;<?php echo __('Date'); ?></span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </span>
            </span>
        </div>
        <div class="overdueby_spns overdueby_spn_<%= caseAutoId2 %>"><% if(showQuickActiononList2){ %><%= getdata2.csDuDtFmtBy %><% } %></div>
    </td>
    <td class="due_dt_tist text-center sb-tg-progress">
        <%= (getdata2.completed_task)?getdata2.completed_task :"0" %> %
    </td>
</tr>
<% } } }%>

<% }
}
} %>
<tr class="separetor_tr">
    <td colspan="9"></td>
</tr>
<% } } %>

<% if(resCaseProj.length == 0){ %>
<tr>
    <td colspan="9" style="color: #ff0000;"> <?php echo __("No task found."); ?></td>
</tr>
<% } %>
<%  }else{%>
<tr class="noRecord">
    <td colspan="9" class="textRed"><?php echo __('No tasks found'); ?>.</td>
</tr>
<% } %>