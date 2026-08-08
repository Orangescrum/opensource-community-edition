<?php
// Task-detail custom-field layout ('tab' or 'side'); provided by AppController::beforeRender().
$taskDetailView = $taskDetailView ?? 'tab';
?>
<style>
	ul.dropdown-menu li .caseDetailsSpan a:hover {background: #eee;color: #2e2e2e !important;}
	.ttype_global {position: relative;display: inline;width: 100%;padding-left: 20px;}
	.attach_detils{ display: flex;flex-wrap: wrap;align-items: flex-start;    margin: 0 -10px;}
	span.downlodfile_detail {padding: 0 10px;display: block;width: 33.33%;border: none;background: transparent;}
	.downlodfile_detail a .ellipsis-view {margin-left: 5px;max-width: 100px;}
	.downlodfile_detail a {text-decoration:none;color: #000;display: flex;padding: 5px;border: 1px solid #e6e6e6;border-radius: 4px;background-color: #e6e6e6;margin: 5px;}
	.downlodfile_detail a small {display: block;margin: 3px 0 0 15px;color: #939fa9;}
	.task_detail .task_title_heading .label{float:right; white-space: nowrap;max-width: 200px; overflow: hidden;text-overflow: ellipsis;letter-spacing:0; }
   .details_item_tab li.item_li .link_item .story_icon { background-position: -76px -277px;}
	.details_item_tab li.item_li:hover .link_item .story_icon, .details_item_tab li.item_li.active .link_item .story_icon { background-position: -95px -277px; }
	.task_details_popup .left_detail .story_icon::before { background-position: -118px -418px; }
	.task_details_popup #customSec .add_plus_label a.add_plus_item:hover { text-decoration: none; }
</style>

<%
	var showQuickAct = showQuickActDD = 0; var UserClients_dtl = ''; var clientids = '';
	var user_can_change = 0;
	var showQuickActiononListEdit = 0;
	if (((csLgndRep == 1 || csLgndRep == 2 || csLgndRep == 4) || (SES_TYPE == 1 || SES_TYPE == 2 || (csUsrDtls == SES_ID))) && is_active == 1) {
	var showQuickAct = 1;
	}
	if (showQuickAct && taskTyp.id != 10) {
	var showQuickActDD = 1;
	}
	if (is_active == 1 && (csLgndRep == 1 || csLgndRep == 2 || csLgndRep == 4) && ((SES_TYPE == 1 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (SES_TYPE == 2 && (EDIT_TASK == 1 || EDIT_TASK == 2)) || (csUsrDtls == SES_ID) || (SES_TYPE == 3 && EDIT_TASK == 1))) {
	user_can_change = 1;
	}
	if (is_active == 1 && (csLgndRep == 1 || csLgndRep == 2 || csLgndRep == 4) && (csUsrDtls == SES_ID)) {
	showQuickActiononListEdit = 1;
	}
	var users_colrs = { "clr1": "#AB47BC;", "clr2": "#455A64;", "clr3": "#5C6BC0;", "clr4": "#512DA8;", "clr5": "#004D40;", "clr6": "#EB4A3C;", "clr7": "#ace1ad;", "clr8": "#ffe999;", "clr9": "#ffa080;", "clr10": "#b5b8ea;", };
	var taskCreatedDate = frmtCrtdDt;
	var taskcrtdBy = crtdBy;

	if (isFavourite) {
	var favMessage = "<?php echo __('Remove favourite');?>";
	} else {
	var favMessage = "<?php echo __('Set favourite');?>";
	}
	var params = parseUrlHash(urlHash);
	var descr = csMsgRep;
   	var r = localStorage.getItem("last_url").split("/");
	var url_count = (r.length)-1;
%>
<input type="hidden" value="<%= Case_mislestone_id %>" id="Case_mislestone_id_<%= csUniqId %>"/>
<div id="t_<%= csUniqId %>" class="yoxview task_detail">
   	<div class="p_0 task-details-wrapper taskdetail_page task_detail_container">
		<?php ### Task Details Header ### ?>
		<section class="header_navigate d-flex width-100-per">
			<div class="width-100-per">
				<div class="task-detail-head task_details_title fw_tskdtail_head <%= protyCls %>">
					<div id="caseDetailsSpanNextPrev<%=csid %>" class="displayOnlyForBackLog" style="display:none;">
						<div class="padlft-non padrht-non task_action_bar_div task_detail_head">
						<div class="d-flex align-item-center back-frwd-btn task_action_bar">
							<div class="displayParentBackButton">
								<a href="javascript:void(0)" class="back-btn task_detail_back1 pop_backbtn">
									<div class="backToParentBorder">
									<span class="os_sprite back-detail" title="<?php echo __('Back to Parent');?>" rel="tooltip"></span>
									</div>
								</a>
							</div>
							<div class="back-frwd">
								<span class="glyphicon glyphicon-menu-left prev" title="<?php echo __('Previous');?>" rel="tooltip_previous" <% if(r[url_count] != "tasks" && r[url_count] != "backlog") { %> style="visibility:hidden;" <% } %>></span>
								<span class="glyphicon glyphicon-menu-right next" title="<?php echo __('Next');?>" rel="tooltip_nxt" <% if(r[url_count] != "tasks" && r[url_count] != "backlog") { %> style="visibility:hidden;" <% } %>></span>
							</div>
							<input type="hidden" name="hiddden_case_uid" id="hidden_case_uid" value="" />
							<input type="hidden" name="hiddden_parent_case_uid" id="hidden_parent_case_uid" value="" />
							<div class="cb"></div>
						</div>
						</div>
					</div>
					<div class="d-flex align-item-center mtop10">
						<div class="width-70-per create_by_taskdtl">
							<div class="d-flex align-item-center">
								<div>
									<% if(related_tasks) { var i = 0;  %>
										<div>
											<p>
											<% for(var pkey in related_tasks.task){
												var getParents = related_tasks.task[pkey];
											%>
											<a href="javascript:void(0)" class="link-text" onclick="easycase.ajaxCaseDetails(<%= '\''+ related_tasks.data[pkey].uniq_id +'\'' %>,<%= '\'case\''%>,0,<%= '\'popup\''%>, <%= '\'action\''%>);"  ><%= getParents %> </a> <% if(i == 0 && related_tasks.parent_counts > 1) {%>/<% } %>
											<% i++ ; } %>
											</p>
										</div>
									<% } %>
									<p>
										<% if(cntdta && (cntdta>1)) { %>
										<?php echo __('Last updated');?>
										<% } else { %>
										<?php echo __('Created');?><% } %>
										<?php echo __('by');?>
										<span class="create_person"><%= shortLength(lstUpdBy,8) %></span>
										<% if(lupdtm.indexOf('Today')==-1 && lupdtm.indexOf('Y\'day')==-1) { %><?php echo __('on');?><% } %>
										<none title="<%= lupdtTtl %>"><%= lupdtm %>.</none>
										<% if(srtdt){ %><span class="start-date m_0" title="<%= srtdtT %>" rel="tooltip">(<?php echo __('Start');?>: <%= srtdt %>)</span><% } %>
										<% if(csDuDtFmt){ %><span id="update_due-date">(<?php echo __('Due');?>: <%= duedate %>)</span><% } %>
									</p>
								</div>
								<div>
								<% if(client_status == 1){ %>
									<div class="client_no_task">
									<p><?php echo __('Clients can not see this task');?></p>
									</div>
									<% } %>
								</div>
								<% if(children && children != "") { %>
								<div class="task_parent_block" id="task_parent_block_<%= csUniqId %>">
									<div rel="" title="<?php echo __('Parents');?>" onclick="showParents(<%= '\'' + csid + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'' + children + '\'' %>);" class=" task_title_icons_parents fl"></div>
									<div class="dropdown dropup fl1 open1 showParents">
										<ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
											<li class="pop_arrow_new"></li>
											<li class="task_parent_msg" style=""><?php echo __('These tasks are waiting on this task');?>.</li>
											<li>
											<ul class="task_parent_items" id="task_parent_<%= csUniqId %>">
												<li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT;?>img/images/loader1.gif   "></li>
											</ul>
											</li>
										</ul>
									</div>
								</div>
								<% } %>
								<% if(depends && depends != ""){ %>
								<div class="task_dependent_block" id="task_dependent_block_<%= csUniqId %>">
									<div rel="" title="<?php echo __('Dependents');?>" onclick="showDependents(<%= '\'' + csid + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'' + depends + '\'' %>);" class=" task_title_icons_depends fl"></div>
									<div class="dropdown dropup fl1 open1 showDependents">
										<ul class="dropdown-menu  bottom_dropdown-caret" style="left: -11px; padding:5px; cursor:default; min-width:250px; max-width:500px;">
											<li class="pop_arrow_new"></li>
											<li class="task_dependent_msg" style=""><?php echo __("Task can't start. Waiting on these task to be completed");?>.</li>
											<li>
											<ul class="task_dependent_items" id="task_dependent_<%= csUniqId %>">
												<li style="text-align:center;" class="loader"><img src="<?php echo HTTP_ROOT;?>img/images/loader1.gif"></li>
											</ul>
											</li>
										</ul>
									</div>
								</div>
								<% } %>
							</div>
						</div>
						<div class="width-30-per ml-auto text-right task_action_status">
						<% if(is_inactive_case == 0 ) { %>
							<div class="icon-menu-bar">
								<a href="javascript:void(0)" rel="tooltip"id="detail_popup_reload" title="<?php echo __('Reload');?>" onclick="easycase.ajaxCaseDetails(<%= '\''+ csUniqId+'\'' %>,<%= '\'case\''%>,0,<%= '\'popup\''%>,<%= '\'reload\''%>);">
									<span class="cmn_tskd_sp reload_icon"></span>
								</a>
								<?php if($this->Format->isAllowed('Manual Time Entry',$roleAccess)){ ?>
									<% if(is_inactive_case == 0 && is_active == 1) {%>
										<% if(logtimes.csLgndRep ==3 ) { %>
											<?php if($this->Format->isAllowed('Time Entry On Closed Task',$roleAccess)){ ?>
												<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
												<span class="cursor link-icon">
													<a class="<%=logtimes.page%> d-inline-block link-icon"id="tog_tm_time_entry rel="tooltip" title="<?php echo __('Manual Time Entry');?>" onclick="createlog(<%= logtimes.task_id %>,'<%= escape(htmlspecialchars(logtimes.task_title))%>')">
													<i class="material-icons icon-colr">access_time</i> <!-- Time Entry --> </a>
												</span>
												<% } %>
											<?php } ?>
										<% } else{ %>
											<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
											<span class="cursor link-icon">
												<a class="<%=logtimes.page%> d-inline-block link-icon" rel="tooltip" title="<?php echo __('Manual Time Entry');?>" onclick="createlog(<%= logtimes.task_id %>,'<%= escape(htmlspecialchars(logtimes.task_title))%>')">
												<i class="material-icons icon-colr">access_time</i> <!-- Time Entry --> </a>
											</span>
										<% } %>
										<% } %>
										<% } %>
									<?php } ?>
								<% if(is_active == 1) {%>
									<span id="DetailsSpanFav<%=csid %>">
											<a href="javascript:void(0);" id="t_fav" class="caseFav" onclick="setCaseFavourite(<%=csid %>,<%=csProjIdRep %>,<%= '\''+csUniqId+'\'' %>,4,<%=isFavourite%>)" rel="tooltip" original-title="<%=favMessage%>" style="color:<%=favouriteColor%>;" >
											<% if(isFavourite) { %>
											<span id="fav_span" class="cmn_tskd_sp starfill_icon"></span>
											<% }else{ %>
											<span id="fav_span" class="cmn_tskd_sp starline_icon"></span>
											<% } %>
											<!-- <?php echo __('Favorite');?> -->
											</a>
									</span>
									<% } %>
									<% if(is_active == 1) { %>
										<?php if($this->Format->isAllowed('Change Status of Task',$roleAccess) && $this->Format->isAllowed('Status change except Close',$roleAccess) ){ ?>
										<% if(csLgndRep != 3) {%>
											<% if(typeof customStatusByProject !="undefined" && typeof customStatusByProject[projId] !='undefined' && customStatusByProject[projId] != null){ %>
												<% if(isAllowed('Change Status of Task',projUniqId)){ %>
												<% if(isAllowed("Status change except Close",projUniqId)){ %>
												<a href="javascript:void(0)" rel="tooltip" title="<?php echo __('Mark as Completed');?>" onclick="setCustomStatus(<%= '\'' + csAtId + '\'' %>, <%= '\'' + csNoRep + '\'' %>, <%= '\'' + csUniqId + '\'' %>,0,<%= '\'3\'' %>,<%= '\'close\'' %>);">
												<span class="cmn_tskd_sp closecase_icon"></span>
												</a>
												<% } %>
												<% } %>
											<% } else { %>
												<% if(isAllowed('Change Status of Task',projUniqId)){ %>
													<% if(isAllowed("Status change except Close",projUniqId)){ %>
														<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Close');?>" onclick="setCloseCase(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\'popup\''%>);">
															<!--<i class="material-icons">&#xE876;</i><?php //&#xE14C; ?>-->
															<span class="cmn_tskd_sp closecase_icon"></span>
															<!-- <?php echo __('Close');?> -->
														</a>

													<% } %>
												<% } %>
											<% } %>
										<% } %>
										<?php } ?>
									<% } %>

								<% if(is_active && (user_can_change || isAllowed('Edit All Task'))){ %>
								<% if( (isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task")){ %>

									<% if(csTypRep == original_epic_id){ %>
										<a class="edit_my_task" id="edit_act<%= csUniqId %>" href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editepic(<%= '\''+ csUniqId+'\',\''+projUniqId+'\',\''+escape(htmlspecialchars(projName))+'\'' %>);closePopupCaseDetails();">
											<span class="cmn_tskd_sp edit_icon"></span>
										</a>
									<% } else if(csTypRep == original_feature_id){ %>
										<a class="edit_my_task" id="edit_act<%= csUniqId %>" href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editfeature(<%= '\''+ csUniqId+'\',\''+projUniqId+'\',\''+escape(htmlspecialchars(projName))+'\'' %>);closePopupCaseDetails();">
											<span class="cmn_tskd_sp edit_icon"></span>
										</a>
									<% }else{ %>
										<a class="edit_my_task" id="edit_act<%= csUniqId %>" href="javascript:void(0)" rel="tooltip" title="<?php echo __('Edit');?>" onclick="editask(<%= '\''+ csUniqId+'\',\''+projUniqId+'\',\''+escape(htmlspecialchars(projName))+'\'' %>);closePopupCaseDetails();">
											<span class="cmn_tskd_sp edit_icon"></span>
										</a>
									<% } %>

								<% } %>
								<% } %>
								<% if(is_active && (SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == csUsrDtls) || isAllowed('Archive All Task'))) { %>
								<?php if($this->Format->isAllowed('Archive Task',$roleAccess) || $this->Format->isAllowed('Archive All Task',$roleAccess)){ ?>
								<a href="javascript:void(0)" id="arcv" data-uniq_id="<%= csUniqId %>" rel="tooltip" title="<?php echo __('Archive');?>" onclick="archiveCase(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csProjIdRep+'\'' %>, <%= '\'t_'+csUniqId+'\'' %>,<%= '\'popdtl\'' %>);">
									<!--<i class="material-icons">&#xE861;</i>-->
									<span class="cmn_tskd_sp archive_icon"></span>
								</a>
								<?php } ?>
								<% } %>
								<% if(!is_active){ %>
								<?php if($this->Format->isAllowed('Change Status of Task',$roleAccess)){ ?>
								<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Restore');?>" onclick="restoreTaskDetail(<%= '\''+ csUniqId+'\',\''+csNoRep+'\'' %>,<%= '\'popdtl\'' %>);">
									<!--<i class="material-icons">&#xE042;</i>-->
									<span class="cmn_tskd_sp restore_icon"></span>
								</a>
								<?php } ?>
								<% } %>
								<% if(is_active){ %>

								<% if(SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == csUsrDtls) || isAllowed('Delete All Task')) { %>
									<?php if($this->Format->isAllowed('Delete Task',$roleAccess) || $this->Format->isAllowed('Delete All Task',$roleAccess)){ ?>
									<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Delete');?>" onclick="deleteCase(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csProjIdRep+'\'' %>, <%= '\'t_'+csUniqId+'\'' %>, <%= '\'' + isRecurring + '\'' %>,<%= '\'dtl\'' %>,<%= '\'popdtl\'' %>);">
										<!--<i class="material-icons">&#xE872;</i>-->
										<span class="delete_icon material-icons">delete_outline</span>
									</a>
									<?php } ?>
								<% } %>
								<% } %>
								<% if(is_active){ %>
									<% if(csTypRep != original_epic_id && csTypRep != original_feature_id) { %>
									<div class="more_action dropdown">
										<span class="cmn_tskd_sp more_icon m_0" id="more-action" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"></span>
										<ul class="dropdown-menu" aria-labelledby="more-action">
											<li>
												<% if(!parseInt(custom_status_id)){ %>
												<% if(is_active && ((is_active && csLgndRep == 1 || csLgndRep == 2 || csLgndRep == 4))) { %>
												<?php if($this->Format->isAllowed('Change Status of Task',$roleAccess)){ ?>
												<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Resolve');?>" onclick="caseResolve(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\'popup\''%>);">
													<!--<i class="material-icons">&#xE153;</i>-->
													<span class="cmn_tskd_sp resolve_icon"></span>
													<?php echo __('Resolve');?>
												</a>
												<?php } ?>
												<% } %>
												<% } %>
												<% if(is_active && ((csLgndRep == 1 || csLgndRep == 2 || csLgndRep == 4 || csLgndRep == 5))) { %>
												<?php if($this->Format->isAllowed('Change Status of Task',$roleAccess)){ ?>
												<?php if($this->Format->isAllowed('Status change except Close',$roleAccess)){ ?>
												<% if(!parseInt(custom_status_id)){ %>
												<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Close');?>" onclick="setCloseCase(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\'popup\''%>);">
													<!--<i class="material-icons">&#xE876;</i><?php //&#xE14C; ?>-->
													<span class="cmn_tskd_sp closecase_icon"></span>
													<?php echo __('Close');?>
												</a>
												<% } %>
												<?php } ?>
												<?php } ?>

												<% } %>
												<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
												<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Copy Task Link');?>" id="cpy_lnk" data-cpylnk="<?php echo HTTP_ROOT;?>dashboard#/details/<%= csUniqId %>" >
													<i class="material-icons">content_copy</i>
													<!-- <span class="cmn_tskd_sp case_icon"></span> -->
													<?php echo __('Copy Task Link');?>
												</a>
												<% } %>

												<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
													<a href="javascript:void(0);" rel="tooltip"  title="<?php echo __('Checklist');?>" onclick="openTab(1);">
														<span class="cmn_tskd_sp checklist_icon"></span>
														<?php echo __('Checklist');?>
													</a>
												<% } %>

												<a href="javascript:void(0);" rel="tooltip"  title="<?php echo __('Subtask');?>" onclick="openTab(2);">
													<span class="cmn_tskd_sp subtask_icon"></span>
													<?php echo __('Subtask');?>
												</a>

												<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
												<a href="javascript:void(0);" rel="tooltip"  title="<?php echo __('Task Links');?>" onclick="openTab(3);">
													<span class="cmn_tskd_sp tasklink_icon"></span>
													<?php echo __('Task Links');?>
												</a>
												<% } %>

												<!-- <a href="javascript:void(0);" rel="tooltip"  title="<?php echo __('Reminders');?>" onclick="openTab(4);">
													<span class="cmn_tskd_sp reminder_icon"></span>
													<?php echo __('Reminders');?>
												</a> -->

												<div class="caseDetailsSpan" id="caseDetailsSpanFav<%=csid %>">
												<a href="javascript:void(0);" class="caseFav" onclick="setCaseFavourite(<%=csid %>,<%=csProjIdRep %>,<%= '\''+csUniqId+'\'' %>,4,<%=isFavourite%>)" rel="tooltip" original-title="<%=favMessage%>" style="color:#888888;" >
												<% if(isFavourite) { %>
												<span id="fav_icon" class="cmn_tskd_sp starfill_icon"></span>
												<% }else{ %>
												<span id="fav_icon" class="cmn_tskd_sp starline_icon"></span>
												<% } %>
												<?php echo __('Favorite');?>
												</a>
												</div>

											</li>
										</ul>
									</div>
									<% } %>
								<% } %>
								</div>
							<% } %>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php ### Task Details Popup Start here ### ?>
		<section class="scroll_details d-flex">
			<aside class="left_detail">
				<!-- Quick task action start here -->
				<div class="row mtop10">
					<div class="col-md-12">
						<h5 class="task_title_heading" id="tour_task_detail_sec">
							<% var easycaseTitle = showSubtaskTitle(caseTitle,csAtId,related_tasks,9,2,'detail'); %>
							<div id="case_ttl_edit_main_<%= csUniqId %>" class="wrapword fs-hide" onmouseover="displayEdit(<%= '\''+csUniqId+'\'' %>,1);" onmouseout="displayEdit(<%= '\'' +csUniqId+ '\'' %>,0);">
							<% if(is_inactive_case == 0 && is_active == 1) { %><div <% if(user_can_change == 1 ||isAllowed("Edit All Task")){ %><% if( (isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task")){ %>class="task_title_hover ellipsis-view" style="float:left;cursor:pointer;" id="case_ttl_edit_spn_<%= csUniqId %>" title="<?php echo __('Edit Title');?>" rel="tooltip" onclick="showEditTitle(<%= '\'' +csUniqId+ '\'' %>);" <% } %><% }else{ %>style="float:left;"<% } %>>#<%= csNoRep %>: <%= formatText(ucfirst(caseTitle)) %></div><% } else {%> <div><%= formatText(ucfirst(caseTitle)) %> </div><% }%><% if(epic_name){%><span class="label epic-label" title="<%= epic_name %>"><%= epic_name %></span><% } %>
							<div class="cb"></div>
							</div>
							<% if(is_inactive_case == 0 && is_active == 1) { %>
							<% if( (isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task") || 1){ %>
							<div class="case_ttl_edit_dv width-100-per m_0 p_0 custom-task-fld title-fld top-tsk-ttl" style="display:none;" id="case_ttl_edit_dv<%= csUniqId %>">
							<div class="d-flex align-item-center">
							<div class="width-70-per">
							<input class="width-100-per m_0 custom_input_control form-control" maxlength="240"  placeholder="<?php echo __('Enter task title');?>..." type="text" data-caseno="<%= csNoRep %>" id="case_ttl_edit_<%= csUniqId %>" onkeyup="saveEditTitle(<%= '\'' +csUniqId+ '\'' %>,event);"/>
							<textarea class="custom_input_control" style="display:none;" id="temp_title_holder_<%= csUniqId %>"><%= formatText(ucfirst(caseDataTitle)) %></textarea>
							</div>
							<div class="width-30-per text-right ml-auto">
								<span class="save_exit_btn mright10"><button id="btn_blue_save_<%= csUniqId %>" class="btn cmn_size btn_cmn_efect cmn_bg btn-info" type="button" onclick="saveEditTitle(<%= '\'' +csUniqId+ '\'' %>,0);"><?php echo __('Save');?></button></span>
								<span class="save_exit_btn"><button id=" btn_blue_cancel_<%= csUniqId %>" class="btn btn_cancel" type="button" onclick="cancelEditTitle(<%= '\'' +csUniqId+ '\'' %>);"><?php echo __('Cancel');?></button></span>
								<img id="title_edit_loader_<%= csUniqId %>" src="<?php echo HTTP_IMAGES;?>images/del.gif" style="display:none;"/>
							</div>
							</div>
							</div>
							<% } }%>
							<div class="cb"></div>
						</h5>
						<div class="quick_option_detail hover_option_detail mtop15 mbtm15">
							<div class="task_detail_option_head task-detail-head-extr <% if(taskTyp.name == 'Story'){%>tsk-detail-story<%}%>">
								<div class="d-flex">
									<div class="d-flex-column width-25-per">
										<div class="detail_fld_label">
										<?php echo __('Project');?>
										</div>
										<div class="detail_fld_data">
										<p class="ttc"><%= shortLength(projName,22) %></p>
										</div>
									</div>
									<div class="d-flex width-75-per">
										<!-- epic check 1 -->
										<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)) { %>
										<div class="d-flex-column width-20-per pl-15">
											<div class="detail_fld_label pl-8">
											<% if(project_mothodology == 2){ %>
											<?php echo __('Sprint');?>
											<%}else{%>
											<?php echo __('Task Group');?>
											<%}%>
											</div>
											<span id="tgrplod<%= csAtId %>" style="display:none">
											<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
											</span>
											<div class="detail_fld_data"  id="tgrpdiv<%= csAtId %>">
											<% if(is_active && user_can_change) { %>
											<div class="dropdown cmn_h_det_arrow">
												<div class="opt1" id="opt80">
													<% var more_opt = 'more_opt80';
													if(mistn != ""){
														var drp_action_title = mistn;
													}else if((csLgndRep == 3 || csLgndRep ==5) && project_mothodology == 2){
														var drp_action_title = "None"
													}else if(project_mothodology == 2){
														if(csLgndRep == 3 || csLgndRep ==5){
															var drp_action_title = 'None';
														}else{
															var drp_action_title = 'Backlog';
														}
													}else{
														var drp_action_title = 'Default Task Group';
													}
													%>
													<p class="quick_action status_tdet">
													<a  rel="tooltip" id="tsk_grp_opt80" title="<%= drp_action_title %>" class="drop_action_data" <?php if($this->Format->isAllowed('Move to Milestone',$roleAccess)){ ?> <% if(is_inactive_case == 0 && is_active == 1) { %> href="javascript:void(0);"  onclick="open_more_opt('<%= more_opt %>',<%= '\''+csAtId+'\'' %>)"<% } %> <?php } ?>>
														<% if(mistn != '') { %>
															<% if((csLgndRep == 3 || csLgndRep ==5) && project_mothodology == 2){ %>
																<?php echo __("None");?>
															<% }else{ %>
																<%= shortLength(ucfirst(formatText(mistn)),15) %>
															<% } %>
														<% } else { %>
															<% if(project_mothodology == 2){ %>
																<% if(csLgndRep == 3 || csLgndRep ==5){ %>
																<?php echo __('None');?>
																<% }else{ %>
																<?php echo __('Backlog');?>
																<% } %>
															<% } else { %>
																<?php echo __('Default Task Group');?>
															<% } %>
														<% } %>
														<i class="tsk-dtail-drop material-icons">&#xE5C5;</i>
														</a>
													</p>
													<% if(spnt_cnt) { %>
													<div class="dropdown d-inline-block">
														<button class="btn btn-primary dropdown-toggle history_btn" type="button" <?php if($this->Format->isAllowed('Move to Milestone',$roleAccess)){ ?> data-toggle="dropdown" <?php } ?> >+ <%= spnt_cnt %></button>
														<ul class="dropdown-menu">
														<li class="history_heading"><%= spnt_cnt %> <?php echo __('Completed Sprint');?>.</li>
														<%=  history_str %>
														</ul>
													</div>
													<% } %>
												</div>
												<?php if($this->Format->isAllowed('Move to Milestone',$roleAccess)){ ?>
												<div class="more_opt new_opt_more" id="more_opt80<%= csAtId %>">
													<ul class="dropdown-menu">
														<li class="searchLi">
														<input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitemsTg(this);" />
														</li>
														<% for(var mkey in all_milestones){
														var getMilestones = all_milestones[mkey];
														var getMilestones1 = {} ;
														getMilestones1.Milestone = getMilestones;
														getMilestones = getMilestones1;
														milestoneName = getMilestones.Milestone.title;
														mls_id = getMilestones.Milestone.id;
														mistnId != ''? mistnId:0; %>
														<li>
															<a href="javascript:void(0);" onclick="detChangeMilestone(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csUniqId+'\'' %>, <%= '\''+escape(milestoneName)+'\'' %>, <%= '\''+mls_id+'\'' %>, <%= '\''+mistnId+'\'' %>);"><%= ucfirst(milestoneName) %></a>
														</li>
														<% } %>
														<li><a href="javascript:void(0);" onclick="detChangeMilestone(<%= '\''+csAtId+'\'' %>, <%= '\''+csNoRep+'\'' %>, <%= '\''+csUniqId+'\'' %>, <%= '\'Default Task Group\'' %>, <%= 0 %>, <%= '\''+mistnId+'\'' %>);">
														<% if(project_mothodology == 2){ %>
														<?php echo __('Backlog');?>
														<%}else{%>
														<?php echo __('Default Task Group');?>
														<% } %>
														</a>
														</li>
													</ul>
												</div>
												<?php } ?>
											</div>
											<% } else { %>
											<p class="ttc d-inline-block">
												<% if(mistn != '') { %>
												<% if((csLgndRep == 3 || csLgndRep ==5) && project_mothodology == 2){ %>
												<?php echo __("None");?>
												<% }else{ %>
												<%= shortLength(ucfirst(formatText(mistn)),15) %>
												<% } %>
												<% } else { %>
												<% if(project_mothodology == 2){ %>
												<% if(csLgndRep == 3 || csLgndRep ==5){ %>
												<?php echo __('None');?>
												<% }else{ %>
												<?php echo __('Backlog');?>
												<% } %>
												<%}else{%>
												<?php echo __('Default Task Group');?>
												<% } %>
												<% } %>
											</p>
											<% if(spnt_cnt) { %>
											<div class="dropdown d-inline-block">
												<button class="btn btn-primary dropdown-toggle history_btn" type="button" data-toggle="dropdown">+ <%= spnt_cnt %></button>
												<ul class="dropdown-menu">
													<li class="history_heading"><%= spnt_cnt %> <?php echo __('Completed Sprint');?>.</li>
													<%=  history_str %>
												</ul>
											</div>
											<% } %>
											<% } %>
											</div>
										</div>
										<% } %>
										<!-- epic check 1 end -->
										<div class="d-flex-column width-20-per pl-15">
											<div class="detail_fld_label pl-8">
											<span class="multilang_ellipsis"><?php echo __('ASSIGN TO');?></span>
											</div>
											<div class="detail_fld_data">
												<div class="d-flex align-item-center">
													<div class="">
														<% var asgnNm = (csUsrDtlsLog == asgnUid) ? '<?php echo __("me");?>' : shortLength(asgnTo,10); %>
														<?php if($this->Format->isAllowed('Change Assigned to',$roleAccess)){ ?>
														<div class="cmn_h_det_arrow tsk-dtails-assignto <% if(showQuickAct == 1){ %> dropdown<% } %>">
															<% if(is_inactive_case == 0 && is_active == 1) {%>
																<div class="detasgnlod" id="detasgnlod<%= csAtId %>" style="display:none">
																<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
																</div>
															<% } %>
															<p  <% if(showQuickAct==1){ %> class="assgn quick_action" data-toggle="dropdown"<% } %> <% if(is_inactive_case == 0 && is_active == 1) { %> onclick="displayAssignToMem(<%= '\''+csAtId+'\'' %>, <%= '\''+projUniqId+'\'' %>,<%= '\''+asgnUid+'\'' %>,<%= '\''+csUniqId+'\'' %>,<%= '\'details\'' %>,<%= '\''+csNoRep+'\'' %>,<%= '\''+client_status+'\'' %> )" <% } %> >
																<span id="case_dtls_new<%= csAtId %>" class="drop_action_data ttc"><%= asgnNm %></span>
																<i class="tsk-dtail-drop material-icons">&#xE5C5;</i>
															</p>
															<span class="edit edit-assign" style="display:none;"><?php echo __('Edit');?> </span>
															<% if(showQuickAct==1){ %>
																<% if(is_inactive_case == 0 && is_active == 1) {%>
																<ul class="dropdown-menu quick_menu" id="detShowAsgnToMem<%= csAtId %>">
																	<li class="text-centre">
																		<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" id="detAssgnload<%= csAtId %>" />
																	</li>
																</ul>
																<% } %>
															<% } %>
														</div>
														<?php }else { ?>
															<span rel="tooltip" title="<%= asgnNm %>" id="case_dtls_new<%= csAtId %>" class="drop_action_data ttc"><%= asgnNm %></span>
														<?php }?>

													</div>
												</div>
											</div>
										</div>

										<div class="d-flex-column width-20-per pl-15 ellipsis-view-display">
											<div class="detail_fld_label pl-8">
											<span class="multilang_ellipsis"><?php echo __('DUE DATE');?></span>
											</div>
											<div class="detail_fld_data">
												<div class="caleder-due-date <?php if(!$this->Format->isAllowed('Update Task Duedate',$roleAccess)){ ?> no-pointer <?php } ?>">
													<div class="calender-txt cmn_h_det_arrow anchor">
														<div id="detddlod<%= csAtId %>" style="display:none">
														<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
														</div>
														<div id="case_dtls_due<%= csAtId %>" class="duedate-txts <% if(user_can_change == 1){ %>dropdown<% } %>"onclick="openDueDateDrpDwn();">
															<% if(csDuDtFmt) { %>
															<div title="<%= csDuDtFmtT %>" rel="tooltip" class="quick_action <% if(user_can_change == 1){ %>dropdown<% } %>">
															<%= csDuDtFmt %>
															<?php if($this->Format->isAllowed('Update Task Duedate',$roleAccess)){ ?>
																<% if((is_inactive_case == 0 && is_active == 1) && localStorage.getItem('is_splitted') == 0) {%>
															<ul class="dropdown-menu quick_menu">
																<li class="pop_arrow_new" style="margin-left:1%;"></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \'00/00/0000\', \'No Due Date\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('No Due Date');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyCurCrtd+'\', \'Today\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Today');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyTomorrow+'\', \'Tomorrow\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Tomorrow');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyMonday+'\', \'Next Monday\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Next Monday');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyFriday+'\', \'This Friday\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('This Friday');?></a></li>
																<li>
																	<a href="javascript:void(0);">
																		<div class="cstm-dt-option-dtpik prtl">
																		<div class="cstm-dt-option" data-csatid="<%= csAtId %>" style="position:absolute; left:0px; top:0px; z-index:99999999;">
																			<input data-csatid="<%= csAtId %>" value="" type="text" id="det_set_due_date_<%= csAtId %>" class="set_due_date set_due_date_custm hide_corsor" title="<?php echo __('Custom Date');?>" style="background:none; border:0px;"/>
																		</div>
																		<span class="cd-caleder glyphicon glyphicon-calendar"></span>
																		<span class="set_due_date_custm_spn" style="position:relative;top:2px;cursor:text;"><?php echo __('Custom');?>&nbsp;<?php echo __('Date');?></span>
																		</div>
																	</a>
																</li>
															</ul>
															<% } %>
															<?php } ?>
															</div>
															<% } else { %>
															<div class="quick_action no_due_dt dropdown ">
															<div class="due-txt no_due cursor" data-toggle="dropdown"  ><span class="multilang_ellipsis"><?php echo __('Date Not Set');?></span>
															<i class="tsk-dtail-drop material-icons"></i>
															</div>
															<?php if($this->Format->isAllowed('Update Task Duedate',$roleAccess)){ ?>
																<% if(is_inactive_case == 0 && is_active == 1) {%>
															<ul class="dropdown-menu quick_menu">
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyCurCrtd+'\', \'Today\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Today');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyTomorrow+'\', \'Tomorrow\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Tomorrow');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyMonday+'\', \'Next Monday\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('Next Monday');?></a></li>
																<li><a href="javascript:void(0);" onclick="detChangeDueDate(<%= '\''+csAtId+'\', \''+mdyFriday+'\', \'This Friday\', \''+csUniqId+'\', \''+csNoRep+'\'' %>)"><?php echo __('This Friday');?></a></li>
																<li>
																	<a href="javascript:void(0);">
																		<div class="cstm-dt-option-dtpik prtl">
																		<div class="cstm-dt-option" data-csatid="<%= csAtId %>" style="position:absolute; left:0px; top:0px; z-index:99999999;">
																			<input data-csatid="<%= csAtId %>" value="" type="text" id="det_set_due_date_<%= csAtId %>" class="set_due_date set_due_date_custm hide_corsor" title="<?php echo __('Custom Date');?>" style="background:none; border:0px;"/>
																		</div>
																		<span class="cd-caleder glyphicon glyphicon-calendar"></span>
																		<span class="set_due_date_custm_spn" style="position:relative;top:2px;cursor:text;"><?php echo __('Custom');?>&nbsp;<?php echo __('Date');?></span>
																		</div>
																	</a>
																</li>
															</ul>
															<% } %>
															<?php } ?>
															</div>
															<% } %>
														</div>
													</div>
													<!--<i class="material-icons">&#xE916;</i>-->
												</div>
												</div>
										</div>

										<!-- epic check 2 -->
										<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
										<div class="d-flex-column width-20-per pl-15">
											<div class="detail_fld_label">
												<?php echo __('EST.HOURS');?>
												<?php
													$allowed_split = null; // OSS: resource allocation removed
												?>
											</div>
											<div class="detail_fld_data">
											<div class="est_hrs_group cursor" id="estdiv<%= csAtId %>">
												<% if(taskTyp.id !== "10" && user_can_change == 1){ %>
												<p class="<?php if($this->Format->isAllowed('Est Hours',$roleAccess)){ ?><% if(is_inactive_case == 0 && is_active == 1) {%> estb <% } %><?php } ?> ttc" style="">
													<span class="border_dashed">
													<% if(estimated_hours != 0.0) { %> <%= format_time_hr_min(estimated_hours) %> <% } else { %><?php echo __('None');?><% } %>
													</span>
												</p>
												<% var est_time = Math.floor(estimated_hours/3600)+':'+(Math.round(Math.floor(estimated_hours%3600)/60)<10?"0":"")+Math.round(Math.floor(estimated_hours%3600)/60); %>
												<input type="text" data-est-id="<%=csAtId%>" data-est-no="<%=csNoRep%>" data-est-uniq="<%=csUniqId%>" data-est-time="<%=est_time%>" id="est_hr<%=csAtId%>" class="est_hr form-control check_minute_range" style="display:none;" maxlength="5" rel="tooltip" title="<?php echo __('You can add time as 1.5(that mean 1 hour and 30 minutes) and press enter to save');?>" onkeypress="return numeric_decimal_colon(event)" value="<%= est_time %>" placeholder="hh:mm" data-default-val="<%=est_time%>"/>
												<% }else { %>
												<p class="ttc">
													<% if(estimated_hours != 0.0) { %><%= format_time_hr_min(estimated_hours) %><% } else { %><?php echo __('None');?><% } %>
												</p>
												<% } %>
											</div>
											<span id="estlod<%=csAtId%>" style="display:none;">
											<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
											</span>
											</div>
										</div>
										<% } %>
									</div>
								</div>
							</div>
						</div>

						<div class="d-flex status_breadcrumbs hover_option_detail mbtm20">
							<div class="all_breadcrumbs d-flex width-25-per">
								<div class="status_detail width-100-per">
								<div class="status_top dtl_page_sts<%= csAtId %>">
									<% var typetsk_id = taskTyp.id; %>
									<?php echo $this->element('case_details_sts_new', array('popup' => 1)); ?>
								</div>
								</div>
							</div>
							<div class="d-flex width-75-per">
								<input type="hidden" id="asn_hiddden" value= "<%= asgnUid %>" />
								<div class="tsk-dtail-priorty d-flex-column width-25-per pl-15">
									<div class="detail_fld_label pl-8">
									<?php echo __('PRIORITY');?>
									</div>
									<div class="detail_fld_data">
										<% if(taskTyp.id == "10"){ %>
										<div id="pridiv<%= csAtId %>" class="pri_actions high_priority">
											<input type="hidden" id="hid_prittl" value="High" />
											<p><span class="priority-symbol"></span><?php echo __('High');?></p>
										</div>
										<% } else{ %>
										<div style="" id="pridiv<%= csAtId %>" data-priority ="<%= csPriRep %>" class="pri_actions <?php if($this->Format->isAllowed('Change Other Details of Task',$roleAccess)){ ?> <%= protyCls %><% if(showQuickAct==1){ %> dropdown<% } %> <?php } ?>">
											<input type="hidden" id="hid_prittl" value="<%= protyTtl %>" />
											<span class="dropdown cmn_h_det_arrow">
												<p  <?php if($this->Format->isAllowed('Change Other Details of Task',$roleAccess)){ ?><% if(showQuickAct==1){ %> class="quick_action " data-toggle="dropdown" <% } %> <?php } ?>>
													<span class="priority-symbol"></span><%= protyTtl %><i class="tsk-dtail-drop material-icons">&#xE5C5;</i>
												</p>
												<% if(csLgndRep !=3 && csLgndRep !=5){ %>
												<div class="cb"></div>
												<% } %>
												<?php if($this->Format->isAllowed('Change Other Details of Task',$roleAccess)){ ?>
												<% if(showQuickAct==1){ %>
													<% if(is_inactive_case == 0 && is_active == 1) {%>
												<ul class="dropdown-menu quick_menu">
													<li class="low_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+csAtId+'\', \'2\', \''+csUniqId+'\', \''+csNoRep+'\'' %>,<%= '\'popup\''%>)"><span class="priority-symbol"></span><?php echo __('Low');?></a></li>
													<li class="medium_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+csAtId+'\', \'1\', \''+csUniqId+'\', \''+csNoRep+'\'' %>,<%= '\'popup\''%>)"><span class="priority-symbol"></span><?php echo __('Medium');?></a></li>
													<li class="high_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+csAtId+'\', \'0\', \''+csUniqId+'\', \''+csNoRep+'\'' %>,<%= '\'popup\''%>)"><span class="priority-symbol"></span><?php echo __('High');?></a></li>
													<li class="urgent_priority"><a href="javascript:void(0);" onclick="detChangepriority(<%= '\''+csAtId+'\', \'3\', \''+csUniqId+'\', \''+csNoRep+'\'' %>,<%= '\'popup\''%>)"><span class="priority-symbol"></span><?php echo __('Urgent');?></a></li>
												</ul>
												<% } %>
											</span>
											<% } %>
											<?php } ?>
										</div>
										<span id="prilod<%= csAtId %>" style="display:none">
										<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
										</span>
										<% } %>
									</div>
								</div>

								<div class="d-flex-column width-25-per pl-15">
									<div class="type-devlop">
										<div class="detail_fld_label pl-8">
											<?php echo __('TYPE');?>
										</div>
										<div class="detail_fld_data">
											<div id="typdiv<%= csAtId %>" class=" typ_actions <% if(showQuickAct==1){ %> dropdown<% } %>" data-typ-id = "<%= taskTyp.id %>">
												<span class="dropdown cmn_h_det_arrow">
													<p  <?php if($this->Format->isAllowed('Change Other Details of Task',$roleAccess)){ ?><% if(showQuickAct== 1){ %> class="quick_action type_show" data-toggle="dropdown" <% } %> <?php } ?>>
														<span class="ttype_global tt_<%= getttformats(taskTyp.name)%>">
														<%= shortLength(taskTyp.name,22) %>
														</span>
														<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
														<i class="tsk-dtail-drop material-icons">&#xE5C5;</i>
														<% } %>
													</p>
													<?php if($this->Format->isAllowed('Change Other Details of Task',$roleAccess)){ ?>
													<% if(showQuickAct==1){ %>
														<% if(is_inactive_case == 0 && is_active == 1) {%>
													<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
													<ul class="dropdown-menu quick_menu">
														<input class="search_inp" type="text" placeholder="<?php echo __('Search');?>" onkeyup="searchTaskTypeDetail(this);"  />
														<li class="pop_arrow_new"></li>
														<% for(var k in GLOBALS_TYPE) {
														if(GLOBALS_TYPE[k].Type.project_id == 0 || GLOBALS_TYPE[k].Type.project_id == csProjIdRep){
														var v = GLOBALS_TYPE[k]; var t = v.Type.id; var t1 = v.Type.short_name; var t2 = v.Type.name; %>
														<%
														var txs_typ = t2;
														$.each(DEFAULT_TASK_TYPES, function(i,n) {
																		if(i == t1){
																				txs_typ = n;
																		}
																});
														%>
														<% if(t2 != taskTyp.name || 1){%>
															<% if(t2 != 'Epic' && t2 != 'Feature') { %>
														<li>
														<a href="javascript:void(0);" onclick="changetype(<%= '\''+csAtId+'\'' %>, <%= '\''+t+'\'' %>, <%= '\''+t1+'\'' %>, <%= '\''+t2+'\'' %>, <%= '\''+csUniqId+'\'' %>, <%= '\''+csNoRep+'\'' %>)">
														<span class="ttype_global tt_<%= getttformats(t2)%>"><%= t2 %></span>
														</a>
														</li>
														<% } %>
														<% } } } %>
													</ul>
													<% } %>
													<% } %>
												</span>
												<% } %>
												<?php } ?>
											</div>

											<span id="dettyplod<%= csAtId %>" style="display:none">
											<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="<?php echo __('Loading');?>..."/>
											</span>
											<div class="cb"></div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>

					<div class="mt-30">
						<div class="">
							<ul class="details_item_tab">
								<li class="item_li upper active" id="tab-overView" data-tab="tab-overView" data-active="<%= is_inactive_case %>"  data-case_uid="<%= csUniqId %>"  onclick="show_hide_tab(this)"><a href="javascript:void(0)" class="link_item "><span class="overview_icon"></span><?php echo __('Overview'); ?></a></li>
								<% var chk_sub_parent = easycaseTitle.split('<i class="material-icons case_symb'); %>
								<% if(chk_sub_parent.length < 3){ %>
								<li class="item_li upper" id="tab-subTask" data-active="<%= is_inactive_case %>"  data-tab="tab-subTask" data-case_uid="<%= csUniqId %>" onclick="show_hide_tab(this)">
									<a href="javascript:void(0)" class="link_item">
										<% if(csTypRep == original_epic_id) { %>
											<span class="feature_icon"></span>
											<?php echo __('Features'); ?>
										<% } else if(csTypRep == original_feature_id) { %>
											<span class="story_icon"></span>
											Stories
										<% } else { %>
											<span class="subtask_icon"></span>
											Subtasks
										<% } %>
									</a>
								</li>								<% } %>
								<% if(csTypRep == original_epic_id){ %>
									<li class="item_li upper" id="tab-storyTask" data-active="<%= is_inactive_case %>" data-tab="tab-storyTask" data-case_uid="<%= csUniqId %>" onclick="show_hide_tab(this)">
										<a href="javascript:void(0)" class="link_item">
											<span class="story_icon"></span>
											Stories
										</a>
									</li>

									<li class="item_li upper" id="tab-task" data-active="<%= is_inactive_case %>" data-tab="tab-task" data-case_uid="<%= csUniqId %>" onclick="show_hide_tab(this)">
										<a href="javascript:void(0)" class="link_item">
											<span class="subtask_icon"></span>
											Tasks
										</a>
									</li>
								<% } %>
								<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
								<li class="item_li upper" id="tab-timelog" data-tab="tab-timeLog" data-active="<%= is_inactive_case %>"  data-case_uid="<%= csUniqId %>" onclick="show_hide_tab(this)"><a href="javascript:void(0)" class="link_item"><span class="timelog_icon"></span>Time Logs</a></li>
								<% } %>
								<li class="item_li upper" id="tab-files" data-active="<%= is_inactive_case %>"  data-tab="tab-files" data-case_uid="<%= csUniqId %>" onclick="fetchFilesTskDtl(this);show_hide_tab(this);"><a href="javascript:void(0)" class="link_item"><span class="files_icon"></span>Files</a></li>
							<?php if ($taskDetailView === 'tab'): ?>
							<?php endif; ?>
								<?php
								// DEPRECATED: Bugs Tab removed as of DF_239
								// The bug tracking functionality has been deprecated and replaced by the test case management system.
								// Previously located here was: <li class="item_li upper" id="tab-bugs"> with onclick="fetchAllBugsTsk(this)"
								// Reason: Multiple 503 database errors when users interacted with bug features.
								// Reference: fix-changelog/2026-01-08-DF_239-remove-deprecated-bugs-tab.md
								?>
								<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
								<% } %>


							</ul>
						</div>
					</div>
					<!-- Quick task action end here -->
					<!-- Overview section start here -->
					<div id="overview_items" class="details_item_content" style="display:block">
					<div class="toggle_task_details fs-hide <% if(cntdta) { %>hide_detail<% } else{ %>show_detail<% } %>">

						<!-- Descrition section start here -->
							<div class="cmn_sec_card selected" id="desc_sec">
								<div class="sec_title tog" data-cmnt_id ="desc_sec">
									<div class="heading_title">
										<span class="sec_icon desc_icon"></span>
									<h3 id="tour_taskdetail_description"><?php echo __('Description');?></h3>
									</div>
									<div class="icon_collapse" ></div>
								</div>
								<div class="toggle_details">
									<div class="description_details">

										<div class="plane_p_txt">
											<% if(dispSec) { %>
												<div id="cnt_0" class="details_task_desc wrapword" style="overflow:hidden;">
                                                    <% if(user_can_change == 1 || isAllowed("Edit All Task")) { %>
                                                        <% if((isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task")) { %>
                                                            <div class="task_desc_hover" title="<?php echo __('Edit Description');?>" rel="tooltip" onclick="showEditDescription(<%= '\'' +csUniqId+ '\'' %>);" >
                                                                <i class="material-icons icon-edit">&#xE3C9;</i>
                                                            </div>
                                                        <% } %>
                                                    <% } %>
                                                    <p><%= csMsgRep %></p>
													<% var fc = 0;  
													if(csFiles) { %>
														<span class="attac_count_task_det attachment_wrap">
															<i class="material-icons">&#xE226;</i>
															<% if(filesArr){ %> <span class="attach_cnt"> <% if((filesArr.length)==1){ %> <?php echo __('1 Attachment');?> <%}else {%><%= filesArr.length%> <?php echo __('Attachments');?> <% } %></span> <% } else { %><?php echo __('No Attachments');?> <% } %>
														</span>
														<div class="attach_detils" style="margin-bottom:20px">
														<% for(var fileKey in filesArr) {
															var getFiles = filesArr[fileKey];
															caseFileName = getFiles.CaseFile.file;
															caseFileUName = getFiles.CaseFile.upload_name;
															caseFileId = getFiles.CaseFile.id;
															downloadurl = getFiles.CaseFile.downloadurl;
															var d_name = getFiles.CaseFile.display_name;
															if(!d_name){ d_name = caseFileName;}
															if(caseFileUName == null){ caseFileUName = caseFileName;}  %>

															<span class ="downlodfile_detail">
																<% if(getFiles.CaseFile.cloud_provider){ %>
																<a href="<%= getFiles.CaseFile.downloadurl %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>" target="_blank"><i style="color: #049aff"class="material-icons">&#xE2C4;</i><div class="ellipsis-view"><%= d_name %></div> <small>(<%= getFiles.CaseFile.file_size %>)</small></a>
																<% } else { %>
																<a href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i style="color: #049aff"class="material-icons">&#xE2C4;</i><div class="ellipsis-view"><%= d_name %></div> <small>(<%= getFiles.CaseFile.file_size %>)</small></a>
																<% } %>
															</span>

															<% } %>
														</div>
														<% var images = ""; var caseFileName = "";
																for(var fileKey in filesArr) {
																var getFiles = filesArr[fileKey];
																caseFileName = getFiles.CaseFile.file;
																caseFileUName = getFiles.CaseFile.upload_name;
																						caseFileId = getFiles.CaseFile.id;
																downloadurl = getFiles.CaseFile.downloadurl;
																oneDriveMetaAvailable = getFiles.CaseFile.hasOwnProperty('OneDriveMeta') ? true : false;
																var d_name = getFiles.CaseFile.display_name;
																if(!d_name){ d_name = caseFileName;}
																						if(caseFileUName == null){ caseFileUName = caseFileName;}
																if(getFiles.CaseFile.is_exist) {
																fc++;
																file_icon_name = easycase.imageTypeIcon(getFiles.CaseFile.format_file); %>
																<?php if($this->Format->isAllowed('View File',$roleAccess)){ ?>
																	<div class="fr atachment_det atachment_<%=caseFileId%>" <% if(easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'png'){ %> <% }else{%> style="display:none;" <%} %>>
																	<div class="aat_file">
																		<?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>
																		<div class="file_show_dload">
																			<a href="<%= getFiles.CaseFile.fileurl %>" target="_blank" alt="<%= caseFileName %>" title="<?php echo __('Preview Image');?>" <% if(easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'png'){ %>rel="prettyPhoto[]"<% } %>><i class="material-icons">&#xE8FF;</i></a>
																			<% if(getFiles.CaseFile.cloud_provider){ %>
																			<a href="<%= getFiles.CaseFile.downloadurl %>" target="_blank" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a>
																			<% } else { %>
																			<a href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a>
																			<% } %>
																		</div>
																	<?php } ?>
																		<?php if($this->Format->isAllowed('Delete File',$roleAccess)){ ?>
																		<div class="attach-close">
																			<% if(user_can_change == 1){ %>
																				<a href="javascript:void(0);" class="hover-close close-icon" onclick="removefiledirect(<%= '\''+caseFileId+'\'' %>,<%='\''+csAtId+'\'' %>,<%='\''+csUniqId+'\'' %>,<%= '\''+csNoRep+'\'' %>)"><i class="material-icons">&#xE872;</i></a>
																			<% } %>
																		</div>
																	<?php } ?>
																		<% if(file_icon_name == 'jpg' || file_icon_name == 'png' || file_icon_name == 'bmp'){ %>
																			<% if (typeof getFiles.CaseFile.fileurl_thumb != 'undefined' && getFiles.CaseFile.fileurl_thumb != ''){%>
																					<img data-original="<%= getFiles.CaseFile.fileurl_thumb %>" class="lazy asignto" style="max-width:180px;" title="<%= d_name %>" alt="Loading image.." />
																			<% }else{ %>
																					<img data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=90&sizey=60&quality=100" class="lazy asignto" width="180" height="120" title="<%= d_name %>" alt="Loading image.." />
																			<% } %>
																		<% } else { %>
																			<div style="display:none;" class="tsk_fl <%= easycase.imageTypeIcon(getFiles.CaseFile.format_file) %>_file"></div>
																		<% } %>
																		<div class="file_cnt ellipsis-view" title="<%= d_name %>" rel="tooltip"><%= d_name %></div>
																		<div class="file_cnt_info">
																			<span class="file-date fl"><%= frmtCrtdDt %></span>
																			<span class="file-size fr"><%= getFiles.CaseFile.file_size %></span>
																			<div class="cb"></div>
																		</div>
																	</div>
																</div>
																	<div class="fr atachment_det parent_other_holder atachment_<%=caseFileId%>" <% if(easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'png'){ %> style="display:none;" <% } %>>
																	<div class="aat_file">
																		<?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>
																		<div class="file_show_dload">
																			<% if(easycase.imageTypeIcon(getFiles.CaseFile.format_file) == 'pdf'){ %>
																			<a href="javascript:void(0);" onclick="viewPdfFile(<%= getFiles.CaseFile.id %>);" alt="<%= caseFileName %>" title="<?php echo __('Preview Image');?>"><i class="material-icons">&#xE8FF;</i></a>
																			<% } %>
																			<% if(downloadurl) { %>
																				<% if(oneDriveMetaAvailable) { %>
																					<a href="<%= getFiles.CaseFile.OneDriveMeta.embedLink %>" alt="<%= caseFileName %>" title="<?php echo __('Preview');?>" target="_blank"><i class="material-icons">&#xE8FF;</i></a>
																				<% } else {%>
																			<a href="<%= downloadurl %>" alt="<%= caseFileName %>" title="<?php echo __('Preview');?>" target="_blank"><i class="material-icons">&#xE8FF;</i></a>
																				<% } %>
																			<% } %>
																			<% if(getFiles.CaseFile.cloud_provider){ %><a href="<%= getFiles.CaseFile.downloadurl %>" target="_blank" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a><% } else { %><a href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a><% } %>
																			
																		</div>
																	<?php } ?>
																		<?php if($this->Format->isAllowed('Delete File',$roleAccess)){ ?>
																		<div class="attach-close">
																			<% if(user_can_change == 1){ %>
																				<a href="javascript:void(0);" class="hover-close close-icon" onclick="removefiledirect(<%= '\''+caseFileId+'\'' %>,<%='\''+csAtId+'\'' %>,<%='\''+csUniqId+'\'' %>,<%= '\''+csNoRep+'\'' %>)"><i class="material-icons">&#xE872;</i></a>
																			<% } %>
																		</div>
																	<?php } ?>
																		<% if(downloadurl) { %>
																			<img src="<?php echo HTTP_IMAGES; ?>images/task_dtl_imgs/<%= easycase.imageTypeIcon(getFiles.CaseFile.format_file) %>_64.png" alt="Loading image.." />
																		<% }else{ %>
																			<% if(getFiles.CaseFile.is_ImgFileExt){ %>
																				<img data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=180&sizey=120&quality=100" class="lazy asignto" width="180" height="120" title="<%= d_name %>" alt="Loading image.." />
																			<%  } else{ %>
																			<img src="<?php echo HTTP_IMAGES; ?>images/task_dtl_imgs/<%= easycase.imageTypeIcon(getFiles.CaseFile.format_file) %>_64.png" alt="Loading image.." />
																			<% } %>
																		<% } %>

																		<div class="file_cnt ellipsis-view" title="<%= d_name %>" rel="tooltip"><%= d_name %></div>
																		<div class="file_cnt_info">
																			<span class="file-date fl"><%= frmtCrtdDt %></span>
																			<span class="file-size fr"><%= getFiles.CaseFile.file_size %></span>
																			<div class="cb"></div>
																		</div>
																	</div>
																</div>
															<?php } ?>
																<% if(fc%4==0) { %><div class="cb"></div><% } %>
														<% } %>
													<% } %>
												<% } %>
											</div>
											<% } else { %>
                                                <div id="cnt_0" class="details_task_desc wrapword" style="overflow:hidden; height: 30px;">
                                                    <% if(user_can_change == 1 || isAllowed("Edit All Task")) { %>
                                                        <% if((isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task")) { %>
                                                            <div class="task_desc_hover" title="<?php echo __('Edit Description');?>" rel="tooltip" onclick="showEditDescription(<%= '\'' +csUniqId+ '\'' %>);" >
                                                                <i class="material-icons icon-edit">&#xE3C9;</i>
                                                            </div>
                                                        <% } %>
                                                    <% } %>
                                                    <p></p>
                                                </div>
											<% } %>
										</div>

                                        <!-- Description Edit Area -->
                                        <% if(is_active == 1 && ((isAllowed("Edit Task") && showQuickActiononListEdit) || isAllowed("Edit All Task"))) { %>
                                        <div class="case_desc_edit_dv width-100-per m_0 p_0 custom-task-fld" style="display:none;" id="case_desc_edit_dv<%= csUniqId %>">
                                            <div class="d-flex-column">
                                                <div class="width-100-per">
                                                    <div id="case_desc_edit_<%= csUniqId %>" style="width:100%;"></div>
                                                    <div style="display:none;" id="temp_description_holder_<%= csUniqId %>"><%= csMsgRep %></div>
                                                </div>
                                                <div class="width-100-per text-right mt-2">
                                                    <span class="save_exit_btn mright10">
                                                        <button id="btn_blue_save_desc_<%= csUniqId %>" class="btn cmn_size btn_cmn_efect cmn_bg btn-info" type="button" onclick="saveEditDescription(<%= '\'' +csUniqId+ '\'' %>,0);">
                                                            <?php echo __('Save');?>
                                                        </button>
                                                    </span>
                                                    <span class="save_exit_btn">
                                                        <button id="btn_blue_cancel_desc_<%= csUniqId %>" class="btn btn_cancel" type="button" onclick="cancelEditDescription(<%= '\'' +csUniqId+ '\'' %>);">
                                                            <?php echo __('Cancel');?>
                                                        </button>
                                                    </span>
                                                    <img id="desc_edit_loader_<%= csUniqId %>" src="<?php echo HTTP_IMAGES;?>images/del.gif" style="display:none;"/>
                                                </div>
                                            </div>
                                        </div>
                                        <% } %>

								</div>
							</div>
						</div>
				<!-- Descrition section end here -->

				<!-- Comment section starts here -->
				<div class="comment_tab" id="comment_tab_id">
				<?php echo $this->element('case_comment_new'); ?>
				</div>
				<!-- Comment section Ends here -->
				</div>
			</div>
			<!-- Overview section end here -->

			<!-- Subtask section start here -->
				<div id="subtask_items" class="details_item_content mt-20 mb-30">
					<div class="cmn_sec_card selected" id="subtask_sec">

						<% var chk_sub_parent = easycaseTitle.split('<i class="material-icons case_symb'); %>
					<% if(chk_sub_parent.length < 3){ %>
					<div class="">
						<div  id="case_subtask_task<%= csUniqId %>"></div>
					</div>
					<% } %>
					</div>
				</div>
			<!-- Subtask section end here -->

            </div>

			<!-- Timelog section start here -->
				<div id="timelog_items" class="details_item_content mt-20 mb-20">
					<div class="cmn_sec_card selected" id="tmelg_sec">
						<div class="" id="reply_time_log<%= csUniqId %>">
						<div id="reply_time_log<%= csAtId %>">
							<?php echo $this->element('case_timelog_new'); ?>
						</div>
					</div>
					</div>
				</div>
				<!-- Timelog section end here -->

				<!-- Files section start here -->
				<div id="file_items" class="details_item_content mt-20 mb-20">
					<div class="cmn_sec_card selected" id="files_sec">
						<div id="tskDtlFiles">
						</div>
					</div>
				</div>
				<!-- Files section end here -->
				
				<?php if ($taskDetailView === 'tab'): ?>
				<?php endif; ?>

				<?php
				// DEPRECATED: Bugs Tab Content Section removed as of DF_239
				// Previously contained: <div id="case_bug_task_dtlpop"> which displayed bug/defect information
				// The entire bugs_items section has been removed as bug tracking is deprecated.
				// The functionality has been replaced by the test case management system.
				// Reason for removal: Multiple 503 database errors when accessing bug tracking features.
				// Related JavaScript: fetchAllBugsTsk() in script_v1.js (no longer called after UI removal)
				// Reference: fix-changelog/2026-01-08-DF_239-remove-deprecated-bugs-tab.md
				?>



			<!-- Other secondary tabs start here -->
			<div class="row mt-20">
				<div class="col-md-12" style="margin-top:8px;">
					<ul class="details_item_tab">
						<li class="item_li lower active" id="tab-activity" data-active="<%= is_inactive_case %>" data-tab="tab-activity" data-case_uid="<%= csUniqId %>" onclick="fetchActivityTsk(this);show_hide_lower_tab(this)"><a href="javascript:void(0)" class="link_item"><span class="activity_icon"></span>Activity Log</a></li>
						<% if( isFeatureTask(csTypRep ,original_feature_id)  &&  isEpicTask(csTypRep ,original_epic_id, actual_dt_created)){ %>
						<li class="item_li lower" id="tab-checkList" data-active="<%= is_inactive_case %>" data-tab="tab-checkList" data-case_uid="<%= csUniqId %>" onclick="fetchChckLists(this);show_hide_lower_tab(this)"><a href="javascript:void(0)" class="link_item"><span class="checklist_icon"></span>Checklist<div class="counter_badge" id="checkList_count"><%= allCheckedChecklist %> / <%= allCheckList %></div></a></li>
						<li class="item_li lower" id="tab-taskLink" data-active="<%= is_inactive_case %>" data-tab="tab-taskLink" data-id="<%= csid %>" data-case_uid="<%= csUniqId %>" onclick="fetchAllTAskLinks(this);show_hide_lower_tab(this)"><a href="javascript:void(0)" class="link_item"><span class="tasklink_icon"></span><?php echo __('Task Links'); ?></a></li>
						<% } %>
					</ul>
				</div>
			</div>
			<!-- Other secondary tabs end here -->



			<!-- Activity Log section start here -->
				<div id="activitylog_items" class="details_item_content mt-20 mb-20" style="display:block">
					<div class="cmn_sec_card selected" id="actyvty_sec">
						<div class="sec_title d-flex tog" data-cmnt_id ="actyvty_sec">
							<div class="heading_title">
								<span class="sec_icon activities_icon"></span>
								<h3 id="tour_taskdetail_activity"><?php echo __('Activities');?></h3>
							</div>
							<div class="icon_collapse" ></div>
						</div>
					<div class="toggle_details mt-20">
						<div class="activity_log" id="case_activity_task_dtlpop">
						<%
						var users_colrs = {"clr1":"#AB47BC;","clr2":"#455A64;","clr3":"#5C6BC0;","clr4":"#512DA8;","clr5":"#004D40;","clr6":"#EB4A3C;","clr7":"#ace1ad;","clr8":"#ffe999;","clr9":"#ffa080;","clr10":"#b5b8ea;",};
						if(typeof threadDetails != 'undefined') {
							var getdata = threadDetails.curCaseDtls;
							getdata.Easycase.userArr = getdata.userArr;
							var userArr = getdata.Easycase.userArr;
							var by_name = getdata[0].user_name;
							var by_photo = getdata.User.photo;
							var photo_exist = getdata.User.photo_exist;
							var photo_existBg = getdata.User.photo_existBg;
							var pf_bg = userArr.User.prflBg;

							var filesArr = getdata.Easycase.rply_files;
							if(getdata.Easycase.message == '' && filesArr.length == 0){
								%>

							<div class="mt-20 actv_count">
								<div class="d-flex align-item-center">
									<div class="username">
										<div class="user-task-pf">
										<% if(photo_exist && photo_exist!=0) { %>
									<img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=30&sizey=30&quality=100" class="" title="<%= by_name %>" width="30" height="30" />
									<% } else { %>
									<% var usr_name_fst = by_name.charAt(0); %>
									<span class="cmn_profile_holder <%= pf_bg %>" title="<%= by_name %>">
									<%= usr_name_fst %>
									</span>
									<% } %>
											<!-- <img src="<?php echo HTTP_ROOT;?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=30&sizey=30&quality=100"
											width="30" height="30"> -->
										</div>
										<%= formatText(by_name) %>
									</div>&nbsp;
									<div>
										<strong>
											<%= getdata.Easycase.replyCap %>
										</strong>

										<span>
											<%= getdata.Easycase.rply_dt %>
										</span>
									</div>
								</div>
							</div>
							<div class="mt-15">
								<button id="show_more_bun" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" style="display:none;" onclick="showMoreActivityTsk('<%= csUniqId %>');"> <?php echo __('Show more'); ?></button>
								<button id="show_less_bun" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" data-uid="<%= csUniqId %>" style="display:none;"onclick="showLessActivity(this);">Show less</button>
							</div>
							<%
							}
						}else if(typeof sqlcaseactivity != 'undefined' && sqlcaseactivity.length > 0) {
						%>
								<% for(var repKey in sqlcaseactivity){
							var getdata = sqlcaseactivity[repKey];
							getdata.Easycase = getdata;
							var userArr = getdata.Easycase.userArr;
							getdata.Easycase.userArr.User = getdata.Easycase.userArr;
							var by_name = userArr.User.name;
							var by_photo = userArr.User.photo;
							var photo_exist = userArr.User.photo_exist;
							var photo_existBg = userArr.User.photo_existBg;
							var filesArr = getdata.Easycase.rply_files;
							var pf_bg = userArr.User.prflBg;
							if((getdata.Easycase.message == null || getdata.Easycase.message == '') && filesArr.length == 0){
							%>


				<div class="mt-15 actv_count">
					<div class="d-flex align-item-center">
						<div class="username">
							<div class="user-task-pf">
							<% if(photo_exist && photo_exist!=0) { %>
									<img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=30&sizey=30&quality=100" class="" title="<%= by_name %>" width="30" height="30" />
									<% } else { %>
									<% var usr_name_fst = by_name.charAt(0); %>
									<span class="cmn_profile_holder <%= pf_bg %>" title="<%= by_name %>">
									<%= usr_name_fst %>
									</span>
									<% } %>
								<!-- <img src="<?php echo HTTP_ROOT;?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=30&sizey=30&quality=100"
								width="30" height="30"> -->
							</div>
							<%= formatText(by_name) %>
						</div>&nbsp;
						<div>
							<strong>
								<%= getdata.Easycase.replyCap %>
							</strong>
							<span>
								<%= getdata.Easycase.rply_dt %>
							</span>
						</div>
					</div>
				</div>
				<div class="mt-15">
					<button id="show_more_bun" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" style="display:none;" onclick="showMoreActivityTsk('<%= csUniqId %>');"> <?php echo __('Show more'); ?></button>
					<button id="show_less_bun" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" data-uid="<%= csUniqId %>" style="display:none;"onclick="showLessActivity(this);">Show less</button>
				</div>
				<% } } %>

					<% if(activitycountall > 10){%>
						<div class="mt-15">
							<button id="show_more_bun" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="showMoreActivityTsk('<%= csUniqId %>');"> <?php echo __('Show more'); ?></button>
						</div>
					<% } %>
				<% }else{ %>

						<div id="noactivity" class="nodetail_found">
							<figure>
								<img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
								height="120">
							</figure>
							<div class="colr_red mtop15"><?php echo __('No activity found');?></div>
						</div>
					<div class="cb"></div>
						<% } %>
						</div>

					</div>
				</div>

				</div>
				<!-- Activity Log section end here -->

				<!-- Checklist section start here -->
				<div id="checklist_items" class="details_item_content mt-20 mb-20">
					<div class="cmn_sec_card selected" id="chklst_sec">
						<div class="" id="tour_detl_checklist<%= csUniqId %>">
						<div id="case_checklist_task_dtl<%= csUniqId %>">
						</div>
					</div>

					</div>
				</div>
				<!-- Checklist section end here -->


				<!-- Task link section start here -->
				<div id="tasklink_items" class="details_item_content mtop20">
					<div class="cmn_sec_card selected" id="tsklink_sec">
					<div id="case_link_task<%= csid %>">
					</div>
					</div>

				</div>
				<!-- Task link section end here -->


				<!-- Reminder section start here -->
				<div id="reminder_items" class="details_item_content mtop20">
					<div class="cmn_sec_card selected" id="rmnd_sec">

					<?php //if($this->Format->isTaskReminderOn()){ ?>
						<div id="case_reminder_task_dtlpop<%= csUniqId %>">
						</div>
					<?php // } ?>

				</div>
				</div>
				<!-- Reminder section end here -->
			</aside>

			<aside class="right_detail">
				<div class="task-detail-rht">

				<!-- Timeline section start here -->
				<div class="cmn_sec_head selected" id="timelineSec">
					<div class="sec_ttl tog" id="tour_detail_timeline" data-cmnt_id="timelineSec">
						<span class="label_icon date_icon"></span>
						<h5><?php echo __('Timeline');?></h5>
						<div class="icon_collapse"></div>
					</div>
					<div id="itemcard1" class="toggle_card_item" style="cursor: default;">

					<div class="d-flex">
						<div class="width-50-per pr-7">
								<div class="detail_fld_label">
									<?php echo __('Est. Hours');?>
								</div>
								<%
								/* changeEstHour() already writes back to #est_time, so editing here
								   keeps the header's copy of the value in step for free. */
								var estHrVal = estimated_hours ? (Math.floor(estimated_hours/3600)+':'+(Math.round(Math.floor(estimated_hours%3600)/60)<10?'0':'')+Math.round(Math.floor(estimated_hours%3600)/60)) : '';
								%>
								<?php $canEditEst = $this->Format->isAllowed('Est Hours', $roleAccess); ?>
								<% if(<?php echo $canEditEst ? 'true' : 'false'; ?> && user_can_change == 1 && is_active == 1 && is_inactive_case == 0){ %>
									<span id="est_time" class="os-editable-date" title="<?php echo __('Set estimated hours');?>"
										onclick="osEditEstHours(<%= csAtId %>)"><% if(estimated_hours != 0.0) { %><%= format_time_hr_min(estimated_hours) %><% } else { %><?php echo __('None');?><% } %></span>
									<input type="text" class="os-inline-input" id="os_esthr_input_<%= csAtId %>" value="<%= estHrVal %>"
										placeholder="hh:mm" maxlength="5" style="display:none;"
										data-uniq="<%= csUniqId %>" data-cno="<%= csNoRep %>"
										title="<?php echo __('You can add time as 1.5 (1 hour 30 minutes) and press enter to save');?>"
										onkeypress="return numeric_decimal_colon(event)"
										onkeydown="osEstHoursKey(event, <%= csAtId %>)"
										onblur="osCancelEstHours(<%= csAtId %>)" />
								<% }else{ %>
									<span id="est_time"><% if(estimated_hours != 0.0) { %><%= format_time_hr_min(estimated_hours) %><% } else { %><?php echo __('None');?><% } %></span>
								<% } %>
							</div>
							<div class="width-50-per pl-7">
							<div class="detail_fld_label">
									<?php echo __('Spent Hours');?>
							</div>
										<div class="detail_fld_data">
										<div class="os-spent-row">
										<p class="ttc totalSPH"><% if(hours != 0.0) { %><%= format_time_hr_min(hours) %><% } else { %><?php echo __('None');?><% } %></p>
										<?php if($this->Format->isAllowed('Manual Time Entry', $roleAccess)){ ?>
											<%
											/* Spent hours is the sum of the task's time logs, so this adds an
											   entry rather than overwriting the total. */
											var canAddSpent = is_inactive_case == 0 && is_active == 1
												&& (csLgndRep != 3 || <?php echo $this->Format->isAllowed('Time Entry On Closed Task', $roleAccess) ? 'true' : 'false'; ?>);
											%>
											<% if(canAddSpent){ %>
											<button type="button" class="os-add-spent-btn" title="<?php echo __('Add time entry');?>"
												onclick="createlog(<%= csAtId %>,'<%= escape(htmlspecialchars(caseTitle)) %>')">+</button>
											<% } %>
										<?php } ?>
										</div>
										</div>
							</div>

						</div>
						<div class="hr_separetor_line"></div>

						<div class="d-flex">
						<div class="width-50-per pr-7">
								<div class="detail_fld_label">
									<?php echo __('Start Date');?>
								</div>
								<div class="detail_fld_data">
								<div class="activity-info">
									<p>
									<%
									/* srtdt_database is Y-m-d but falls back to the epoch when no start
									   date is set, so only trust it when srtdt itself is present. */
									var srtdtVal = srtdt ? srtdt_database : '';
									%>
									<?php $canEditStartDate = $this->Format->isAllowed('Update Task Duedate', $roleAccess); ?>
									<% if(<?php echo $canEditStartDate ? 'true' : 'false'; ?> && user_can_change == 1 && is_active == 1 && is_inactive_case == 0){ %>
										<span class="start-date os-editable-date" id="os_startdate_<%= csAtId %>"
											title="<% if(srtdt){ %><%= srtdtT %><% }else{ %><?php echo __('Set start date');?><% } %>"
											onclick="osEditStartDate(<%= csAtId %>, '<%= srtdtVal %>')"><% if(srtdt){ %><%= srtdt %><% }else{ %><?php echo __('Date Not Set');?><% } %></span>
										<input type="date" class="os-date-input" id="os_startdate_input_<%= csAtId %>" value="<%= srtdtVal %>"
											style="display:none;" onchange="osSaveStartDate(<%= csAtId %>, this.value)"
											onblur="osCancelStartDate(<%= csAtId %>)" />
									<% }else{ %>
										<span class="start-date" title="<%= srtdtT %>"><% if(srtdt){ %><%= srtdt %><% }else{ %><?php echo __('Date Not Set');?><% } %></span>
									<% } %>
									</p>
								</div>
								</div>
							</div>
							<div class="width-50-per pl-7">
							<div class="detail_fld_label">
									<?php echo __('Due Date');?>
							</div>
							<div class="detail_fld_data">
							<%
							/* detChangeDueDate() runs the change-reason flow and already writes
							   back to #duedate_id, so the header and this stay in step. */
							/* duedate_database is Y-m-d but falls back to the epoch when no due
							   date is set, so only trust it when the formatted value is present. */
							var dueVal = csDuDtFmt1 ? duedate_database : '';
							%>
							<?php $canEditDue = $this->Format->isAllowed('Update Task Duedate', $roleAccess); ?>
							<% if(<?php echo $canEditDue ? 'true' : 'false'; ?> && user_can_change == 1 && is_active == 1 && is_inactive_case == 0){ %>
								<p>
								<span class="start-date os-editable-date" id="duedate_id" title="<?php echo __('Set due date');?>"
									onclick="osEditDueDate(<%= csAtId %>)"><% if(csDuDtFmt1){ %><%= csDuDtFmt1 %><% }else{ %><?php echo __('Date Not Set');?><% } %></span>
								<input type="date" class="os-date-input" id="os_duedate_input_<%= csAtId %>" value="<%= dueVal %>"
									style="display:none;" data-uniq="<%= csUniqId %>" data-cno="<%= csNoRep %>"
									onchange="osSaveDueDate(<%= csAtId %>, this.value)"
									onblur="osCancelDueDate(<%= csAtId %>)" />
								</p>
							<% }else{ %>
								<p><span class="start-date" id="duedate_id"><% if(csDuDtFmt1){ %><%= csDuDtFmt1 %><% }else{ %><?php echo __('Date Not Set');?><% } %></span></p>
							<% } %>
							<!-- <span id="duedate_id"><%= csDuDtFmt1 %></span> -->
								</div>
							</div>

						</div>
						<div class="hr_separetor_line"></div>


						<div class="d-flex">
							<div class="width-50-per pr-7">
								<div class="detail_fld_label">
									<?php echo __('Last Updated');?>
							</div>
							<div class="detail_fld_data">
								<div class="activity-info">
									<p><div id="lst_uptd"><%= lupdtm %></div> <?php echo __('by');?> <span <% if(lstUpdBy != 'me'){ %> class="ttc" <% } %> style=""><%= shortLength(lstUpdBy,3,0) %></span></p>
								</div>
								</div>
							</div>
							<div class="width-50-per pl-7">
							<div class="detail_fld_label">
									<?php echo __('Last Commented');?>
							</div>
							<div class="detail_fld_data">
								<div class="activity-info">
									<p><%= frmtCrtdDt %> <?php echo __('by');?> <%= shortLength(lstUpdBy,3,0) %></p>
								</div>
							</div>
							</div>
						</div>
						<div class="hr_separetor_line"></div>


						<div class="d-flex">
							<div class="width-50-per pr-7">
								<div class="detail_fld_label">
									<?php echo __('Created Date');?>
							</div>
							<div class="detail_fld_data">
								<div class="activity-info">
									<p><%= taskCreatedDate %></p>
								</div>
								</div>
							</div>
							<% if(lstRes) { %>
								<div class="width-50-per pl-7">
									<div class="detail_fld_label">
										<?php echo __('Resolved Date');?>
								</div>
								<div class="detail_fld_data">
									<div class="activity-info">
										<p><%= lstRes %></p>
									</div>
								</div>
								</div>
							<% } %>
								<% if(lstRes && lstRes) { %>
									<div class="hr_separetor_line"></div>
									<% } %>
							<div class="width-50-per pl-7">
								<div class="detail_fld_label">
									<?php echo __('Closed');?>
							</div>
							<div class="detail_fld_data">
								<div class="activity-info">
								<% if(lstCls) { %>
									<p><%= lstCls %></p>
									<% }else{ %>
										<p><?php echo __('--'); ?> <p>
										<% } %>
								</div>
								</div>
							</div>
						</div>


						<div class="hr_separetor_line"></div>
							<div class="d-flex">

						<% if(timeBalancRemainingValue) { %>

							<div class="width-50-per pl-7">
									<div class="detail_fld_label">
											<?php echo __('Time Balance Remaining:');?>
									</div>
									<div class="detail_fld_data">
										<div class="activity-info">
										<p class="time_balance_value <% if(timeBalancRemainingValue < '0'){ %>overdue_redd<% } %>">
												<% if(caseStatus == 2) { %>0<% } else { %><%= timeBalancRemainingValue %><% } %>
													</p>
										</div>
									</div>
								</div>

								<% } %>

						</div>
							
					</div>
				</div>
				<!-- Timeline section end here -->


				<!-- People section start here -->
				<div class="cmn_sec_head mtop10" id="peopleSec">
						<div class="sec_ttl tog" id="tour_detail_people" data-cmnt_id="peopleSec">
							<span class="label_icon people_icon"></span>
							<h5>People</h5>
							<div class="icon_collapse" ></div>
						</div>
						<div id="itemcard2" class="toggle_card_item"style="cursor: default;">
					<div id="asgnUsrdiv<%= csAtId %>" class="assign_to user-task-info">
						<input type="hidden" id="hid_asgn_uid" value="<%= asgnUid %>" />
						<div class="d-flex">
							<div class="width-100-per">
								<div class="detail_fld_label">
									<?php echo __('Assign To');?>
							</div>

								<div class="detail_fld_data">
								<div class="username d-flex width-100-per pt-0">
								<div class="user-task-pf">
									<% if(asgnPic && asgnPic!=0) { %>
									<img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= asgnPic %>&sizex=30&sizey=30&quality=100" class="" title="<%= asgnTo %>" width="30" height="30" />
									<% } else { %>
									<% var usr_name_fst = asgnTo.charAt(0); %>
									<span class="cmn_profile_holder <%= asgnPicBg %>" title="<%= asgnTo %>">
									<%= usr_name_fst %>
									</span>
									<% } %>
									</div>
									<div class="">
										<input type="hidden" id="asgn_to" value="<%= asgnUid %>">
										<div class="cmn_h_det_arrow tsk-dtails-assignto">
											<span id="asgnto_id" class="ttc"><%= asgnNm %></span>
										</div>
									</div>
								</div>
							</div>
							</div>
						</div>
					</div>
					<div class="hr_separetor_line"></div>
					<div class="involve-people">
						<div class="d-flex">
							<div class="width-100-per">
								<div class="detail_fld_label">
									<?php echo __('People Involved');?>
							</div>
							<div class="detail_fld_data">
							<div class="activity-info">
								<% for(i in taskUsrs) { %>
								<span class="user-task-pf">
								<% var upic = 'user.png'; %>
								<%
								taskUsrs[i].User = taskUsrs[i];
								var nm_t = formatText(taskUsrs[i].User.name); var usr_name_fst = nm_t.charAt(0);
								%>
								<% if(taskUsrs[i].User.photo && taskUsrs[i].User.photo!=0) {
									upic = taskUsrs[i].User.photo; %>
								<img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= upic %>&sizex=30&sizey=30&quality=100" class="" title="<%= ucwords(formatText(taskUsrs[i].User.name+' '+taskUsrs[i].User.last_name)) %>" width="30" height="30" rel="tooltip" />
								<% }else{ %>
								<span class="cmn_profile_holder <%= taskUsrs[i].User.prflBg %>" title="<%= ucwords(formatText(taskUsrs[i].User.name+' '+taskUsrs[i].User.last_name)) %>">
								<%= usr_name_fst %>
								</span>
								<% } %>
								</span>
								<% } %>
								<div  class="cb"></div>
							</div>
						</div>
						</div>
						</div>
						<div class="hr_separetor_line"></div>
						<div class="d-flex">
							<div class="width-100-per">
								<div class="detail_fld_label">
									<?php echo __('Created By');?>
							</div>
							<div class="detail_fld_data">
							<div class="activity-info">
								<span class="user-task-pf">
									<% if(pstFileExst) { %>
									<img data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= pstPic %>&sizex=30&sizey=30&quality=100" class="lazy rep_bdr" title="<%= pstNm %>" width="30" height="30" />
									<% } else { %>
									<% var usr_name_fst = pstNm.charAt(0); %>
									<span class="cmn_profile_holder <%= pstPicBg %>">
									<%= usr_name_fst %>
									</span>
									<% } %>
									<div class="cb"></div>
								</span>
								<span><%= shortLength(crtdBy,25) %></span>

								<div  class="cb"></div>
							</div>
						</div>
						</div>
						</div>
					</div>
				</div>
				</div>
				<!-- People section end here -->

				<?php if ($taskDetailView === 'side'): ?>
				<?php endif; ?>



				<!-- Linked section start here -->
				<!-- Linked section end here -->
				<!-- Tag section start here -->
				<div class="cmn_sec_head mtop10" id="lableSec">
						<div class="sec_ttl tog" data-cmnt_id="lableSec" id="tour_lbl">
							<span class="label_icon tags_icon"></span>
							<h5><?php echo __('Labels');?></h5>
							<div class="icon_collapse" ></div>
						</div>
						<div class="toggle_card_item"style="cursor: default;">
							<div class="d-flex align-item-center">
								<div class="width-100-per">
									<div class="detail_fld_data">
										<div class="add_plus_label">
										<?php if($this->Format->isAllowed('Add Label',$roleAccess)){ ?>
											<% if(is_inactive_case == 0 && is_active == 1) {%>
											<span class="cursor add_plus_item" onclick="addLabel(<%= '\''+csAtId+'\'' %>, <%= '\''+csProjIdRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\''+projUniqId+'\'' %>,2);" rel="tooltip" original-title="<?php echo __('Add label');?>">
											<i class="material-icons">&#xE145;</i>

											<?php echo __('Add New Label');?>
											</span>
											<% } %>
											<?php } ?>

										</div>
									</div>
								</div>
							</div>
							<div class="d-flex mtop20">
								<div class="width-100-per fld_label_width">
									<!-- <div class="detail_fld_label field_icon">
										<?php echo __('Label');?>
								</div> -->
								<div class="detail_fld_data">
									<div id="tour_detl_labels<%= csUniqId %>" class="label_in_task">
										<?php echo $this->element('case_label_task');?>
									</div>
									</div>
								</div>
							</div>
						</div>
				</div>
				<!-- Tag section end here -->

				<?php if($this->Format->isWikiEnabled()){ ?>
				<!-- Wiki section start here -->
               <div class="cmn_sec_head mtop10" id="wikiSec">
					<div class="sec_ttl tog" data-cmnt_id="wikiSec" id="tour_wiki">
						<span class="label_icon wiki_icon"></span>
						<h5><?php echo __('Wiki');?></h5>
						<?php if( (SES_TYPE == 1 || SES_TYPE == 2) || $this->Format->isAllowed('Add Wiki',$roleAccess)){ ?>
						<span class="wiki-info-icon" rel="tooltip" original-title="" data-proj-uniq-id="<%= projUniqId %>" data-task-uniq-id="<%= csUniqId %>" style="cursor:pointer;margin-left:6px;line-height:1;" onclick="event.stopPropagation();">
							<i class="material-icons" style="font-size:16px;color:#888;vertical-align:middle;">info</i>
						</span>
						<?php } ?>
						<div class="icon_collapse" ></div>
					</div>
					<div class="toggle_card_item"style="cursor: default;">
						<div class="d-flex align-item-center">
							<div class="width-100-per">
								<div class="detail_fld_data">
									<div class="add_plus_wiki">
									  <?php if( (SES_TYPE == 1 || SES_TYPE == 2) || $this->Format->isAllowed('Add Wiki',$roleAccess)){ ?>
										<% if(is_inactive_case == 0 && is_active == 1) { %>
										<span class="cursor add_plus_item add-task-wiki" onclick="showTaskWikiPopUp(<%= '\''+csAtId+'\'' %>, <%= '\''+csProjIdRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\''+projUniqId+'\'' %>);" rel="tooltip" original-title="<?php echo __('Add wiki');?>">
											<i class="material-icons">&#xE145;</i>
											<?php echo __('Add Wiki');?>
										</span>
										<% } %>
										<?php } ?>
									  <?php if( (SES_TYPE == 1 || SES_TYPE == 2) || $this->Format->isAllowed('Remove Wiki',$roleAccess)){ ?>
										<% if(is_inactive_case == 0 && is_active == 1) { %>
										<span class="cursor add_plus_item remove-wiki remove-task-wiki" onclick="unlinkkWikiFromTask(<%= '\''+csAtId+'\'' %>, <%= '\''+csProjIdRep+'\'' %>, <%= '\''+csUniqId+'\'' %>,<%= '\''+projUniqId+'\'' %>);" rel="tooltip" original-title="<?php echo __('Remove wiki');?>">
											<i class="material-icons">&#xe15b;</i>
											<?php echo __('Remove Wiki');?>
										</span>
										<% } %>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<div class="d-flex mtop20">
							<div class="width-100-per fld_wiki_width">
							   <div class="detail_fld_data">
									<div id="tour_detl_wikis<%= csUniqId %>" class="wiki_in_task">
										<div style="height: 200px; overflow-x: hidden; overflow-y: scroll;" >
											<div id="case_wiki_tree"></div>
										</div>
								  	</div>
								</div>
							</div>
						</div>
					</div>
               </div>
			   <!-- Wiki section end here -->
			   <?php } ?>
			</aside>
		</section>
   <div class="clearfix"></div>
   <input type="hidden" value="<%= csUniqId %>" id="case_uiq_detail_popup">
   <input type="hidden" value="<%= projUniqId %>" id="proj_uinq_detail_popup">
</div>
</div>
<div class="cb"></div>
</div>
<!-- risk-task-integration.js loaded from popup.php (outside template tag, path: /risk-management/js/) -->
