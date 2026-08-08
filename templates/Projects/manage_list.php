<?php
if ($projtype == 'active-grid') {
	$gr_cookie_value = '';
	$cookie_value = 'active-grid';
} elseif ($projtype == 'inactive-grid') {
	$gr_cookie_value = 'inactive';
	$cookie_value = 'inactive-grid';
}
?>
<div id="project-list-view">
	<v-app>
		<v-container fluid class="ma-0 pa-0 project-list-view-container">
			<div class=" task_lis_page">
				<div class="task_listing">
					<div class="mt-1">
						<v-data-table
							:headers="projectTableHeaders"
							:items="projects"
							:options.sync="projectTableOptions"
							:server-items-length="totalProjects"
							:loading="loading"
							:footer-props="{ showFirstLastPage: true, showCurrentPage:true,itemsPerPageOptions: [20,40,50]}"
							:header-props="{ sortIcon:'sort', }"
							loader-height="0"
							:height="'70vh'"
							class="sticky-header-table"
							fixed-header
							must-sort
							:loading="false"
							loading-text=" ">
							<!-- no-data -->
							<template v-slot:no-data>
							<v-col cols="12">
								<div class="no-data-box extra nodata-foud-program-box">
									<div>
										<!-- <p class="head">No Projects Found</p> -->
										<p class="sub-head"><?php echo __('No Projects Found'); ?></p>
									</div> <img style="max-height: 100px;" src="<?php echo HTTP_ROOT;?>img/no-data/no-project.png" /> <br><br> <span class="m-left"><a class="btn btn_cmn_efect cmn_bg btn-info cmn_size" href="javascript:void(0)" onClick="newProject()"><?php echo __('Create New Project'); ?><div class="ripple-container"></div></a></span>
								</div>
							</v-col>
										</template>
										<template v-slot:no-results>
										<v-col cols="12">
								<div class="no-data-box extra nodata-foud-program-box">
									<div>
										<!-- <p class="head">No Projects Found</p> -->
										<p class="sub-head"><?php echo __('No Projects Found'); ?></p>
									</div> <img style="max-height: 100px;" src="<?php echo HTTP_ROOT;?>img/no-data/no-project.png" /> <br><br> <span class="m-left"><a class="btn btn_cmn_efect cmn_bg btn-info cmn_size" href="javascript:void(0)" onClick="newProject()"><?php echo __('Create New Project'); ?><div class="ripple-container"></div></a></span>
								</div>
							</v-col>
							</template>
							<!-- headers -->
							<template v-for="h in projectTableHeaders" v-slot:[`header.${h.value}`]="{ header }">
								<span class="font-weight-medium custom-header">{{ headerLabel(header.text) }}</span>
							</template>
							<!-- action menu -->
							<!-- end action menu -->
							<!-- custom fields -->
							<!-- project name with link -->
							<template v-slot:item.actions="{ item }" v-if="isRowHover">
								<div class="actionButtons">
									<v-menu left offset-y>
										<template v-slot:activator="{ on, attrs }">
											<v-btn icon v-bind="attrs" v-on="on">
												<i class="material-icons">&#xE5D4;</i>
											</v-btn>
										</template>
										<?php if (SES_TYPE == 1 || SES_TYPE == 2 || SES_TYPE == 3) { ?>
											<v-list dense style="max-height: 270px; width:200px;" class="overflow-y-auto ma-0 pa-0">
												<template v-if="data.projtype == 'active-grid'">

													<template v-if="item['isactive'] == 2">
														<?php if ($this->Format->isAllowed('Notcomplete Project', $roleAccess)) { ?>
															<v-list-item @click="enablePrjListView(item)">
																<v-icon class="mr-2" dense>not_interested</v-icon>
																<v-list-item-title><?php echo __('Not Complete'); ?></v-list-item-title>
															</v-list-item>
														<?php } ?>
														<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
															<v-list-item @click="deleteProjectListView(item)">
																<v-icon class="mr-2" dense>delete</v-icon>
																<v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
															</v-list-item>
														<?php } ?>
													</template>

													<template v-else>

														<?php if ($this->Format->isAllowed('Edit Project', $roleAccess)) { ?>
															<v-list-item @click="editProjectFrmListView(item)">
																<v-icon class="mr-2" dense>mode_edit</v-icon>
																<v-list-item-title><?php echo __('Edit'); ?></v-list-item-title>
															</v-list-item>
														<?php } ?>
														<template v-if="(SES_TYPE == 1 || SES_TYPE == 2) && data.proj_users_list[item['id']]">
															<?php if ($this->Format->isAllowed('Add Users to Project', $roleAccess)) { ?>
																<v-list-item @click="removeMeFromPrj(item)">
																	<v-icon class="mr-2" dense>remove_circle</v-icon>
																	<v-list-item-title><?php echo __('Remove me from here'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>
														<template v-else-if="(SES_TYPE == 1 || SES_TYPE == 2)">
															<?php if ($this->Format->isAllowed('Add Users to Project', $roleAccess)) { ?>
																<v-list-item @click="assignMeToPrj(item)">
																	<v-icon class="mr-2" dense>add_circle</v-icon>
																	<v-list-item-title><?php echo __('Add me here'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>
                                                        
														<?php if ($this->Format->isAllowed('Add Users to Project', $roleAccess)) { ?>
															<v-list-item @click="addUserToProjectListView(item)">
																<v-icon class="mr-2" dense>add_circle</v-icon>
																<v-list-item-title><?php echo __('Add User'); ?></v-list-item-title>
															</v-list-item>
														<?php } ?>

														<template v-if="item['totusers']">
															<?php if ($this->Format->isAllowed('Remove Users from Project', $roleAccess)) { ?>
																<v-list-item @click="removeUsrFrmProjectListView(item)">
																	<v-icon class="mr-2" dense>remove_circle</v-icon>
																	<v-list-item-title><?php echo __('Remove User'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>

														<?php if (SES_TYPE == 1 || SES_TYPE == 2) { ?>
															<v-list-item @click="assignRoleFrmListView(item)">
																<v-icon class="mr-2" dense>add_circle</v-icon>
																<v-list-item-title><?php echo __("Assign Role"); ?></v-list-item-title>
															</v-list-item>
														<?php } ?>

														<template v-if="item['totalcase'] != 0">
															<template v-for="(value,key) in data.ProjectStatus">
																<?php if ($this->Format->isAllowed('Complete Project', $roleAccess, 0, SES_COMP)) { ?>
																	<template v-if="value !='Completed'">
																		<v-list-item @click="changePrjStatus(item, value, key)">
																			<v-icon class="mr-2" dense>check_circle</v-icon>
																			<v-list-item-title>{{ value }}</v-list-item-title>
																		</v-list-item>
																	</template>
																<?php } ?>
															</template>
															<?php if ($this->Format->isAllowed('Complete Project', $roleAccess, 0, SES_COMP)) { ?>
																<!-- See note in manage.php: this "Completed" item was leaking past the permission gate. -->
																<v-list-item @click="changePrjStatusCompleted(item)">
																	<v-icon class="mr-2" dense>check_circle</v-icon>
																	<v-list-item-title><?php echo __('Completed'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>
														<template v-else>
															<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
																<v-list-item @click="deleteProjectListView(item)">
																	<v-icon class="mr-2" dense>delete</v-icon>
																	<v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>
													</template>
												</template>
												<template v-if="data.projtype == 'inactive-grid'">
													<?php if ($this->Format->isAllowed('Notcomplete Project', $roleAccess)) { ?>
														<v-list-item @click="enablePrjListView(item)">
															<v-icon class="mr-2" dense>not_interested</v-icon>
															<v-list-item-title><?php echo __('Not Complete'); ?></v-list-item-title>
														</v-list-item>
													<?php } ?>
													<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
														<v-list-item @click="deleteProjectListView(item)">
															<v-icon class="mr-2" dense>delete</v-icon>
															<v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
														</v-list-item>
													<?php } ?>
												</template>
											</v-list>
										<?php } else { ?>
											<template v-if="SES_TYPE == 3 && item['user_id'] != SES_ID">
												<v-list dense style="max-height: 270px" class="overflow-y-auto" @click="notAuthAlert"></v-list>
											</template>
											<template v-else>
												<v-list dense style="max-height: 270px" class="overflow-y-auto">

													<template v-if="projtype == 'active-grid'">

														<template v-if="item['isactive'] == 2 || item['status'] == 4">
															<?php if ($this->Format->isAllowed('Notcomplete Project', $roleAccess)) { ?>
																<v-list-item>
																	<v-icon class="mr-2" dense>not_interested</v-icon>
																	<v-list-item-title><?php echo __('Not Complete'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
																<v-list-item>
																	<v-icon class="mr-2" dense>delete</v-icon>
																	<v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
														</template>

														<template v-else>
															<?php if ($this->Format->isAllowed('Edit Project', $roleAccess)) { ?>
																<v-list-item>
																	<v-icon class="mr-2" dense>mode_edit</v-icon>
																	<v-list-item-title><?php echo __('Edit'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<?php if ($this->Format->isAllowed('Add Users to Project', $roleAccess)) { ?>
																<v-list-item>
																	<v-icon class="mr-2" dense>add_circle</v-icon>
																	<v-list-item-title><?php echo __('Add User'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<?php if ($this->Format->isAllowed('Remove Users from Project', $roleAccess)) { ?>
																<v-list-item v-if="item['totusers']">
																	<v-icon class="mr-2" dense>remove_circle</v-icon>
																	<v-list-item-title><?php echo __('Remove User'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<?php if ($this->Format->isAllowed('Remove Users from Project', $roleAccess)) { ?>
																<v-list-item style="display:none;">
																	<v-icon class="mr-2" dense>remove_circle</v-icon>
																	<v-list-item-title><?php echo __('Remove User'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<?php if ($this->Format->isAllowed('Edit Project', $roleAccess)) { ?>
																<v-list-item>
																	<v-icon class="mr-2" dense>mode_edit</v-icon>
																	<v-list-item-title><?php echo __('Edit'); ?></v-list-item-title>
																</v-list-item>
															<?php } ?>
															<template v-if="item['totalcase']">
																<?php if ($this->Format->isAllowed('Complete Project', $roleAccess, 0, SES_COMP)) { ?>
																	<v-list-item>
																		<v-icon class="mr-2" dense>check_circle</v-icon>
																		<v-list-item-title><?php echo __('Complete'); ?></v-list-item-title>
																	</v-list-item>
																<?php } ?>
															</template>
															<template v-else>
																<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
																	<v-list-item>
																		<v-icon class="mr-2" dense>delete</v-icon>
																		<v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
																	</v-list-item>
																<?php } ?>
															</template>
														</template>

													</template>

													<template v-else>
														<?php if ($this->Format->isAllowed('Notcomplete Project', $roleAccess)) { ?>
															<li>
																<a><i class="material-icons">&#xE033;</i> <?php echo __('Not Complete'); ?></a>
															</li>
														<?php } ?>
														<?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
															<li><a><i class="material-icons">&#xE872;</i> <?php echo __('Delete'); ?></a></li>
														<?php } ?>
													</template>
												</v-list>
											</template>
										<?php } ?>
									</v-menu>
								</div>
							</template>
							<template v-slot:item.short_name="{ item }">
								<td><span>{{ item['short_name'] }}</span></td>
							</template>
							<template v-slot:item.prio="{ item }">
								<td>
									<span
										:style="{
											backgroundColor: getPriorityColor(item.prio).bg,
											color: getPriorityColor(item.prio).text,
											padding: '4px 8px',
											borderRadius: '5px',
											fontSize: '12px',
											fontWeight: 'bold',
											display: 'inline-block',
											minWidth: '60px',
											textAlign: 'center',
										}">
										{{ formatPriority(item.prio) }}
									</span>
								</td>
							</template>


							<template v-slot:item.project_name="{ item }">
								<template v-if="item['isactive'] == 1">
									<a class="ttl_listing" :title="item.tooltip" @click="projectBodyClick(item)">
										<span v-html="item.Prjname"></span>
									</a><br />
								</template>
								<template v-if="item['isactive'] == 2">
									<a class="ttl_listing" :title="item.tooltip" @click="inactiveProjectBodyClick(item)">
										<span v-html="item.prj_name_shrt"></span>
									</a><br />
								</template>
							</template>
							<!-- other columns -->

							<template v-slot:item.timeline="{ item }">
								<td>
									<span>
										{{ item['project_tz_startdate'] || '-' }} - {{ item['project_tz_enddate'] || '-' }}
									</span>
								</td>
							</template>
							<template v-slot:item.status="{ item }">
								<td>
									<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
										<span
											:style="{
												backgroundColor: getStatusColor(item.status),
												width: '8px',
												height: '8px',
												borderRadius: '50%',
												display: 'inline-block'
												}">
										</span>
										<span style="font-size: 13px; color: #555;">
											{{ getStatusLabel(item.status) }}
										</span>
									</div>
									<div style="display: flex; align-items: center; gap: 6px;margin-right:10px">
										<v-progress-linear
											:value="data.project_progress_data[item['id']] || 0"
											height="8"
											:color="getPriorityColor(item.prio).progress"
											background-color="#e0e0e0"
											rounded
											style="flex: 1; width: 100px;">
										</v-progress-linear>
										<span style="font-size: 12px;">
											{{ Math.ceil(data.project_progress_data[item['id']] || 0) }}%
										</span>
									</div>
								</td>
							</template>

							<template v-slot:item.users="{ item }">
								<td>
									<div class="d-flex align-center" style="gap: -10px; overflow: visible;">
										<template v-if="data.proj_users_list[item['id']]">
											<template v-for="(user, index) in data.proj_users_list[item['id']].slice(0, 4)">
												<v-avatar size="32" class="mr-n2" v-if="data.proj_users_dtllist[user] && data.proj_users_dtllist[user]['photo']">
													<img :title="data.proj_users_dtllist[user]['name']"
														:src="'<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=' + data.proj_users_dtllist[user]['photo'] + '&sizex=32&sizey=32&quality=100'" />
												</v-avatar>
												<v-avatar size="32" class="mr-n2 cmn_profile_holder"
													:class="random_bgclr(data.proj_users_dtllist[user]['id'])" v-else>
													<span :title="data.proj_users_dtllist[user]['name']">
														{{ data.proj_users_dtllist[user]['name'].charAt(0).toUpperCase() }}
													</span>
												</v-avatar>
											</template>
											<template v-if="data.proj_users_list[item['id']].length > 4">
												<v-avatar size="32" class="mr-n2" color="#f0f0f0" style="color:#555; font-size: 12px;">
													+{{ data.proj_users_list[item['id']].length - 4 }}
												</v-avatar>
											</template>
										</template>
									</div>
								</td>
							</template>

						</v-data-table>
					</div>
				</div>
			</div>
		</v-container>
	</v-app>
</div>
<script>
	let listViewUrl = '<?php echo $this->Url->build(["controller" => "Projects", "action" => "ajaxGridView"]); ?>';
	$(document).ready(function() {
		new Vue({
			el: '#project-list-view',
			vuetify: new Vuetify({
				icons: {
					iconfont: 'md'
				}
			}),
			data() {
				return {
					page: 0,
					pageCount: 0,
					fields: [],
					totalProjects: 0,
					projects: [],
					loading: true,
					projectTableOptions: {},
					projectTableHeaders: [],
					headerRendered: false,
					p_u_name: [],
					SES_TYPE: '<?php echo SES_TYPE; ?>',
					SES_ID: '<?php echo SES_ID; ?>',
					data: {},
					projtype: '<?php echo $cookie_value; ?>',
					filtype: '<?php echo $filtype; ?>',
					searchInput: '',
					search: '',
					searching: false,
					searchBoxClosed: true,
					custom_field_ids: [],
					headerLabels: <?php echo json_encode([
						'Code' => __('Code'),
						'Project Name' => __('Project Name'),
						'Priority' => __('Priority'),
						'Members' => __('Members'),
						'Timeline' => __('Timeline'),
						'Status' => __('Status'),
					], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
				}
			},
			computed: {
				params(nv) {
					if (this.search) {
						this.projectTableOptions.page = 1;
					}
					return {
						...this.projectTableOptions,
						srch: this.search,
					};
				},
				isRowHover() {
					return true;
				}
			},
			watch: {
				// projectTableOptions: {
				// 	handler() {
				// 		this.getProjects();
				// 	},
				// 	deep: true,
				// },
				params: {
					handler(nv, ov) {
						this.getProjects(this.params);
					},
					deep: true
				},
			},
			methods: {
				searchTable(value) {
					this.searching = true;
					this.search = value;
				},
				casePaging(page) {
					var getpage = getPage();
					var filetype = localStorage.getItem("PROJECTLISTVIEW_FILTYPE");
					var srch = localStorage.getItem("PROJECTLISTVIEW_SCRH");
					var sort_by = localStorage.getItem("PROJECTLISTVIEW_SORTBY");
					var order = localStorage.getItem("PROJECTLISTVIEW_ORDER");
					var tcls = localStorage.getItem("PROJECTLISTVIEW_TCLS");
					if (getpage == "projects") {
						ajaxGridViewLoad('<?php echo $cookie_value; ?>', srch, page, filetype, order, sort_by, tcls);
						remember_filters('PROJECTLISTVIEW_PAGE', page);
					}
				},
				formatPriority(prio) {
					if (!prio) return '';
					let val = prio.toString().toLowerCase();
					return val.charAt(0).toUpperCase() + val.slice(1);
				},

				slider_inner_project_search(v) {
					if (v == 'open') {
						if ($('#inner-project-search').width() == 0 || $('#inner-project-search').width() < 10) {
							$('#inner-project-search').addClass('open');
							$("#inner-project-search").animate({
								width: '200px'
							}, 400, function() {
								$('#inner-project-search').focus();
							});
						}
					}
					$("#inner-project-search").off().on('blur', function() {
						search_val = $('#inner-project-search').val().trim();
						if (search_val == '') {
							$(this).animate({
								width: '0px'
							}, 400, function() {
								$('#inner-project-search').removeClass('open');
							});
						}
					});
				},
				addOrRemoveUser(item, typ) {
					let proj_id = item['id'];
					let proj_uid = item['uniq_id'];
					let user_ids = '<?php echo SES_ID; ?>';
					let pname = item['Prjname'];
					$.ajax({
						url: '/projects/assignRemovMeToProject',
						type: "POST",
						dataType: 'json',
						data: {
							user_ids: user_ids,
							project_id: proj_id,
							typ: typ,
						},
						context: this,
						success: function(data, textStatus, jqXHR) {
							if (data.status == 'nf') {
								showTopErrSucc('error', '<?php echo __("Failed to assign user to the project."); ?>');
								if (typ == "rm") {} else if (typ == "as") {}
							} else {
								if (trim(data.message) != '') {
									showTopErrSucc('success', data.message);
								}
							}
							this.getProjects();
						}
					});
				},
				assignMeToPrj(item) {
					this.addOrRemoveUser(item, 'as');
				},
				removeMeFromPrj(item) {
					this.addOrRemoveUser(item, 'rm');
				},
				addUserToProjectListView(item) {
					let prj_id = item['uniq_id'];
					let prj_name = item['Prjname'];
					addUsersToProject(prj_id, prj_name);
				},
				editProjectFrmListView(item) {
					let proj_id = item['id'];
					let prj_id = item['uniq_id'];
					let user_ids = '<?php echo SES_ID; ?>';
					let prj_name = item['Prjname'];
					openPopup();
					$(".loader_dv").show();
					$("#inner_prj_edit").hide();
					$(".edt_prj").show();
					$("#header_prj").html(prj_name);
					$.post(HTTP_ROOT + "projects/ajax_edit_project", {
						"pid": prj_id,
						"page": 'active-grid'
					}, function(data) {
						if (data) {
							$(".loader_dv").hide();
							$('#inner_prj_edit').show();
							$('#inner_prj_edit').html(data);
							$.material.init();
							$('.proj_prioty').select2();
							$('.proj_methodology').select2();
							$('.sel_Typ_dp').select2();
							$('.tsk_Typ_dp').select2({
								templateSelection: formatTaskType,
								templateResult: formatTaskType
							});
							$('.status_typ_dp').select2();
							$('.workflow_dp').select2();
							if (typeof $.fn.autoGrow === 'function') {
								$('textarea').autoGrow().keyup();
							}
						}
					});
				},
				removeUsrFrmProjectListView(item) {
					let prj_id = item['id'];
					let prj_name = item['Prjname'];
					openPopup();
					$("#popupload2").show();
					$(".rmv_prj_usr").show();
					$("#header_prj_usr_rmv").html(prj_name);
					$('#inner_prj_usr_rmv').hide();
					$('.rmv-btn').hide();
					$('#rmname').val('');
					$('#remusersrch').hide();
					$.post(HTTP_ROOT + "projects/user_listing", {
						"project_id": prj_id
					}, function(data) {
						if (data) {
							$(".loader_dv").hide();
							$('#inner_prj_usr_rmv').show();
							$('#inner_prj_usr_rmv').html(data);
							if (parseInt($("#is_users").val())) {
								$('.rmv-btn').show();
								$('#remusersrch').show();
								enableAddUsrBtns('.rem-usr-prj');
							}
							$("#popupload2").hide();
							$.material.init();
							$('[rel="tooltip"]').tipsy({
								gravity: 's',
								fade: true
							});
						}
					});
				},
				assignRoleFrmListView(item) {
					let prj_id = item['id'];
					let prj_name = item['Prjname'];
					assign_role(prj_id, prj_name);
				},
				changePrjStatus(item, status, key) {
					if (!item) {
						return false;
					}
					let prj_id = item['id'];
					let prj_name = item['Prjname'];
					let status_name = status;
					let status_id = key;
					let conf = confirm(_("Are you sure you want to mark") + " '" + prj_name + "' as " + status_name);
					if (conf == true) {
						$.ajax({
							url: HTTP_ROOT + '/projects/changeProjectStatus',
							type: "POST",
							dataType: 'json',
							data: {
								prj_id,
								prj_name,
								status_name,
								status_id
							},
							context: this,
							success: function(data, textStatus, jqXHR) {
								showTopErrSucc(data.status, data.message);
								this.getProjects()
							}
						});
					}
				},
				changePrjStatusCompleted(item) {
                    let prj_id = item['id'];
                    let prj_name = item['Prjname'];

                    if (confirm(_("Are you sure you want to mark") + " '" + prj_name + "' " + _("as completed ?"))) {
                        $.ajax({
                            url: HTTP_ROOT + '/projects/ajaxDeactivateProject',
                            type: "POST",
                            dataType: 'json',
                            data: {
                                prj_id,
                                prj_name,
                            },
                            context: this,
                            success: function(data, textStatus, jqXHR) {
                                showTopErrSucc(data.status, data.message);
                                this.getProjects()
                            }
                        });
                    }
                },
				random_bgclr(user_id) {
					const bgclr = ['clr1', 'clr2', 'clr3', 'clr4', 'clr5', 'clr6', 'clr7', 'clr8', 'clr9', 'clr10'];
					const index = user_id % bgclr.length;
					return bgclr[index];
				},

				deleteProjectListView(item) {
					let prj_unq_id = item['uniq_id'];
					let prj_nm = item['Prjname'];
					if (confirm(_("Are you sure to delete project") + " '" + prj_nm + "'")) {
						if (confirm(_("All the Tasks, Files associated with") + " '" + prj_nm + "' " + _("will be deleted permanently."))) {

                            $.ajax({
                                url: HTTP_ROOT + '/projects/ajaxDeleteProject',
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    projuid: prj_unq_id,
                                    prj_nm,
                                },
                                context: this,
                                success: function(data, textStatus, jqXHR) {
                                    showTopErrSucc(data.status, data.message);
                                    this.getProjects()
                                }
                            });
						} else {
							return false;
						}
					} else {
						return false;
					}
				},
				disablePrjListView(item) {},
				enablePrjListView(item) {
					 let prj_id = item['id'];
                    let prj_name = item['Prjname'];
                    if (confirm(_("Are you sure you want to mark") + " '" + prj_name + "' " + _("as not complete ?"))) {
                        $.ajax({
                            url: HTTP_ROOT + '/projects/ajaxActivateProject',
                            type: "POST",
                            dataType: 'json',
                            data: {
                                prj_id,
                                prj_name,
                            },
                            context: this,
                            success: function(data, textStatus, jqXHR) {
                                showTopErrSucc(data.status, data.message);
                                this.getProjects()
                            }
                        });
                    }
				},
				getPriorityColor(prio) {
					const val = prio ? prio.toLowerCase() : '';
					switch (val) {
						case 'low':
							return {
								bg: '#fff28abd', text: '#2e2e2ecf', progress: '#f7efb0'
							};
						case 'medium':
							return {
								bg: '#a8ffcfb5', text: '#2e2e2ecf', progress: '#91e5bd'
							};
						case 'high':
							return {
								bg: '#ffc2c2', text: '#2e2e2ecf', progress: '#e57373'
							};
						default:
							return {
								bg: '#e0e0e0', text: '#2e2e2ecf', progress: '#bdbdbd'
							};
					}
				},

				projectBodyClick(item) {
					let uniq_id = item['uniq_id'];
					remember_filters('ALL_PROJECT', '');
					resetAllFilters('all', 1);
					$('#projFil').val(uniq_id);
					$.ajax({
						url: `${HTTP_ROOT}projects/updateDateVisited`,
						type: "POST",
						dataType: 'json',
						data: {
							uniq_id
						},
						context: this,
						success: function(res) {
							if (res.status == 'success') {
								if (typeof res.tsk_cnt != 'undefined' && !parseInt(res.tsk_cnt)) {
									if (res.proj_math == '2') {
										window.location.href = HTTP_ROOT + "dashboard/#backlog";
									} else if (res.proj_math == '1') {
										window.location.href = HTTP_ROOT + "dashboard/#tasks";
									} else {
										window.location.href = HTTP_ROOT + "dashboard/#kanban";
									}
								} else {
									window.location.href = HTTP_ROOT + "dashboard/#overview";
								}
							} else {
								showTopErrSucc('error', _('Oops! You are not a member of the project. Please add yourself as a member of this project.'));
							}
						}
					});
				},
				inactiveProjectBodyClick(uniq_id) {
					inactiveProjectBodyClick(uniq_id);
				},
				inArray: function(needle, haystack) {
					if (typeof haystack != 'undefined' && haystack != null) {
						var length = haystack.length;
						if (length != 0) {
							for (var i = 0; i < length; i++) {
								if (haystack[i] == needle)
									return true;
							}
						} else {
							return true;
						}
					} else {
						return true;
					}
					return false;
				},
				getStatusLabel(status) {
					switch (status) {
						case 1:
							return _('Started');
						case 2:
							return _('On Hold');
						case 3:
							return _('Stack');
						case 4:
							return _('Completed');
						default:
							return _('Unknown');
					}
				},
				getStatusColor(status) {
					switch (status) {
						case 1:
							return '#4caf50';
						case 2:
							return '#bb6b1b';
						case 3:
							return '#4db6ac';
						case 4:
							return '#9e9e9e';
						default:
							return '#ccc';
					}
				},

				headerLabel(text) {
					return (this.headerLabels && this.headerLabels[text]) ? this.headerLabels[text] : text;
				},
				setTableHeaders(fields, custom_field_head) {
					const defaultHeaders = [{
						text: 'Code',
						value: 'short_name',
					}, {
						text: 'Project Name',
						value: 'project_name',
					}, {
						text: 'Priority',
						value: 'prio',
					}, {
						text: 'Members',
						value: 'users',
						sortable: false
					}, {
						text: 'Timeline',
						value: 'timeline'
					}, {
						text: 'Status',
						value: 'status',
						sortable: false,
					}];
					const fixed = ['Code', 'Project Name', 'Priority', 'Members', 'Timeline', 'Status'];
					this.projectTableHeaders.splice(0);
					this.projectTableHeaders.push({
						text: ' ',
						value: 'actions',
						sortable: false,
						width: '60px'
					});
					defaultHeaders.forEach(header => {
						header.cellClass = 'cell-fix';
						if (this.inArray(header.text, fixed)) {
							if (header.text == 'Project Name') {
								header.width = "40%";
							} else {
								header.width = "";
							}
							this.projectTableHeaders.push(header)
						} else if (this.inArray(header.text, fields)) {
							if (header.text == 'Custom Field') {
								for (let key in custom_field_head) {
									if (Object.hasOwnProperty.call(custom_field_head, key)) {
										let custom_field = custom_field_head[key];
										this.projectTableHeaders.push({
											text: custom_field.split(' ').map(w => w[0].toUpperCase() + w.substring(1).toLowerCase()).join(' '),
											value: 'Project.custom_fields.' + key + '.CustomFieldValue.value',
											sortable: false,
											width: "",
											cellClass: 'cell-fix'
										});
									}
								}
							} else {
								header.width = "";
								this.projectTableHeaders.push(header)
							}
						}
					});
					this.headerRendered = true;
				},
				getProjects(params) {
					let options = {  
                         p_type: '<?php echo $p_type; ?>',
                         client: '<?php echo $client; ?>',
                         manager: '<?php echo $manager_id??''; ?>',
                         program: '<?php echo $program_id ??''; ?>',
                         project: '<?php echo $project_uid ??''; ?>',
                         url_status: '<?php echo $url_status; ?>',
                         proj_srch: '<?php echo $prjsrch; ?>', 
						'projtype': this.projtype,
						'srch': '',
						'page': this.projectTableOptions.page,
						'filtype': this.filtype,
						'order': this.projectTableOptions.sortDesc[0] == true ? 'desc' : (this.projectTableOptions.sortDesc[0] == false ? 'asc' : ''),
						'sortby': this.projectTableOptions.sortBy[0],
						'page_limit': this.projectTableOptions.itemsPerPage,
					};

					if (params) {
						options.srch = params.srch;
					}

					this.loading = true
					let vue_obj = this;
					let customHeaders = {
						'X-CSRF-Token': _csrfToken
					};
					axios.post(listViewUrl, options, {
							headers: customHeaders
						})
						.then((response) => {
							let data = response.data;
							this.data = data;
							if (!this.headerRendered) {
								this.setTableHeaders(data.fields, data.allCustomFields);
							}
							this.setTableRows(data.prjAllArr);
							this.setProjectCount(data);
							this.projtype = data.projtype;
							this.totalProjects = +data.caseCount
							this.custom_field_ids = data.custom_field_ids;
							this.loading = false;
							if (params) {
								this.searching = false;
							}
						})
						.catch((error) => {});
				},
				setTableRows(items) {
					this.projects = items;
				},
				notAuthAlert() {
					showTopErrSucc('error', "<?php echo __('Oops! You are not authorized to do this operation. Please contact your Admin/Owner'); ?>.");
				},
				setProjectCount(data) {
					$('#inactive_proj_cnt').text('(' + (+data.inactive_project_cnt) + ')');
					$('#active_proj_cnt').text('(' + (+data.active_project_cnt) + ')');
					$('#started_proj_cnt').text('(' + (+data.started_project_cnt) + ')');
					$('#hold_proj_cnt').text('(' + (+data.hold_project_cnt) + ')');
					$('#stack_proj_cnt').text('(' + (+data.stack_project_cnt) + ')');
				}
			},
			created() {
				remember_filters('PROJECTLISTVIEW_SORTBY', '');
				remember_filters('PROJECTLISTVIEW_ORDER', '');
				remember_filters('PROJECTLISTVIEW_TCLS', '');
				remember_filters('PROJECTLISTVIEW_SCRH', '');
			},
			mounted() {},
		});

	});
</script>

<script>
	var table;
	$(document).on('click', '.disbl_prj', function() {
		var ref = window.location.href;
		ref = ref.split('projects/');
		var prj_id = $(this).attr('data-prj-id');
		var prj_name = $(this).attr('data-prj-name');
		var conf = confirm(_("Are you sure you want to mark") + " '" + prj_name + "' " + _("as completed ?"));
		if (conf == true) {
			var pg = $(".button_page").html();
			var loc = HTTP_ROOT + 'projects/gridview/?id=' + prj_id + '&action=deactivate';
			if (parseInt(pg) > 1) {
				loc = loc + "&pg=" + pg;
			}
			window.location = loc + "&req_uri=" + ref[1];
		} else {
			return false;
		}
	});

	function casePaging(page) {
		var getpage = getPage();
		var filetype = localStorage.getItem("PROJECTLISTVIEW_FILTYPE");
		var srch = localStorage.getItem("PROJECTLISTVIEW_SCRH");
		var sort_by = localStorage.getItem("PROJECTLISTVIEW_SORTBY");
		var order = localStorage.getItem("PROJECTLISTVIEW_ORDER");
		var tcls = localStorage.getItem("PROJECTLISTVIEW_TCLS");
		if (getpage == "projects") {
			ajaxGridViewLoad('<?php echo $cookie_value; ?>', srch, page, filetype, order, sort_by, tcls);
			remember_filters('PROJECTLISTVIEW_PAGE', page);
		}
	}

	function checkboxCol(ev) {
		var status = $(ev).is(":checked");
		$(".clmn_chkbx").prop("checked", status);
		$("#showhide_drpdwn").addClass("open");
	}

	function checkboxProjectColumn(ev) {
		$("#showhide_drpdwn").addClass("open");
		var status = $(ev).is(":checked");
		var col_id = $(ev).attr('id');
		// var column = table.column( $(ev).attr('data-column') );
		if ($('.clmn_chkbx:checked').length == $('.clmn_chkbx').length) {
			$('#column_all').prop('checked', true);
		} else {
			$('#column_all').prop('checked', false);
		}

	}

	function showColumnPreferences(pref) {
		//$(".selectedcols").trigger('change');
	}

	function saveAllowedColumns() {
		var cvals = [];
		$('.selectedcls:checkbox:checked').each(function() {
			cvals.push(this.value);
		});
		var selectedCols = cvals.join(",");
		$.post(HTTP_ROOT + "requests/saveProjectColumns", {
			"cols": selectedCols
		}, function(data) {
			if (data) {
				window.location.reload();
			}
		});
	}

	function inactiveProjectBodyClick(uid) {
		$('.project-dropdown').hide();
		$('.project-dropdown').prev('li').hide();
		$.post(HTTP_ROOT + "projects/updateDateVisited", {
			'uniq_id': uid
		}, function(res) {
			if (res.status == 'success') {
				window.location.href = HTTP_ROOT + "dashboard/#overview?prouid=" + uid;
			} else {
				showTopErrSucc('error', '<?php echo __('Oops! You are not a member of the project. Please add yourself as a member of this project.'); ?>');
				return false;
			}
		}, 'json');

	}
</script>