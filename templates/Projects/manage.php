<div class="loader_bg" id="projectCardViewLoader">
    <div class="loadingdata">
        <img src="<?php echo HTTP_ROOT; ?>images/rolling.gif?v=5019" alt="loading..." title="loading...">
    </div>
</div>
<div id="project-card-view" v-cloak>
    <v-app class="transparent">
        <v-container fluid class="ma-0 pa-0 project-list-view-container" ref="container">
            <?php
            $active_url = HTTP_ROOT . 'projects/manage';
            $inactive_url = $active_url . '/inactive';
            if ($projtype == '') {
                $grid_url = $active_url . '/active-grid';
                $cookie_value = 'active-grid';
            } elseif ($projtype == 'inactive') {
                $grid_url = $active_url . '/inactive-grid';
                $cookie_value = 'inactive-grid';
            }
            $srch_res = '';
            if ($this->request->getQuery('project') != '' && trim($this->request->getQuery('project'))) {
                $srch_res = $this->request->getQuery('project');
            } else if ($this->request->getQuery('proj_srch') != '' && trim($this->request->getQuery('proj_srch'))) {
                $srch_res = $proj_srch = $this->request->getQuery('proj_srch');
                $active_url .= "?proj_srch=" . $proj_srch;
                $inactive_url .= "?proj_srch=" . $proj_srch;
                $grid_url .= "?proj_srch=" . $proj_srch;
            }
            $program_res = '';
            if ($this->request->getQuery('program') != '' && trim($this->request->getQuery('program'))) {
                $program_res = $this->request->getQuery('program');
            }
            
            // Check for first login to trigger tour
            $isFirstLogin = isset($firstLogin) && $firstLogin == '1';
            ?>
            
            <?php if (trim($srch_res)) { ?>
                <div class="cmn_search_result cmn_bdr_shadow" ref="cmn_search_result" v-show="cmn_search_result">
                    <div class="global-srch-res fl"><?php echo __('Search Results for'); ?>: <span ref="cmn_search_result_text"><?php echo h($srch_res); ?></span></div>
                    <div class="fl global-srch-rst">
                        <a href="<?php echo HTTP_ROOT . 'projects/manage'; ?>"><i class="material-icons">&#xE8BA;</i></a></a>
                    </div>
                    <div class="cb"></div>
                </div>
            <?php } ?>
            <?php if (trim($program_res)) { ?>
                <div class="cmn_search_result cmn_bdr_shadow" ref="cmn_search_result" v-show="cmn_search_result">
                    <div class="global-srch-res fl"><?php echo __('Program'); ?>: <span ref="cmn_search_result_text"><?php echo h($projectName['name']); ?></span></div>
                    <div class="fl global-srch-rst">
                        <a href="<?php echo HTTP_ROOT . 'projects/manage'; ?>"><i class="material-icons">&#xE8BA;</i></a></a>
                    </div>
                    <div class="cb"></div>
                </div>
            <?php } ?>
            <v-row id="scroll-row">
                <v-col v-for="item in data.prjAllArr" :key="item.id" cols="12" md="4">
                    <v-hover v-slot="{ hover }">
                        <v-card :elevation="hover ? 5 : 3" class="rounded-lg">
                            <v-toolbar dense class="elevation-0">

                                <template v-if="item['isactive'] == 1">
                                    <span :class="'prio_'+getPriority(item['priority'])" :title="getPriorityTitle(item['priority'])" class="prio_lmh prio_gen_prj prio-drop-icon" rel="tooltip"></span>
                                </template>
                                <template v-else-if="item['isactive'] == 2">
                                    <span :class="'prio_'+getPriority(item['priority'])" :title="getPriorityTitle(item['priority'])" class=" prio_lmh prio_gen_prj prio-drop-icon" rel="tooltip"></span>
                                </template>

                                <div :title="getStatus(item)" :class="getStatusClass(item)" rel="tooltip">
                                    <v-icon v-if="item['isactive'] == 2 || item['status'] == 4">check_circle</v-icon>
                                    <v-icon v-else-if="item['status']==1">grade</v-icon>
                                    <v-icon v-else-if="item['status']==2">hd</v-icon>
                                    <v-icon v-else-if="item['status']==3">layers</v-icon>
                                    <v-icon v-else>card_travel</v-icon>
                                    <v-chip label x-small color="#2e2e2e" text-color="white" class="pa-1" title="<?php echo __('Role'); ?>">
                                        {{ (item['role'])?item['role']:item['crole'] }}
                                    </v-chip>
                                </div>
                                <v-spacer></v-spacer>

                                <v-menu left offset-y>

                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" class="usr_act_det" small>
                                            <i class="material-icons" :class="hover ? '' :''">&#xE5D4;</i>
                                        </v-btn>
                                    </template>
                                    <?php if (SES_TYPE == 1 || SES_TYPE == 2 || SES_TYPE == 3) { ?>
                                        <v-list dense style="max-height: 270px; width:200px;" class="overflow-y-auto ma-0 pa-0">

                                            <template v-if="item['isactive'] == 2 || item['status'] == 4">
                                                <?php if ($this->Format->isAllowed('Notcomplete Project', $roleAccess)) { ?>
                                                    <v-list-item @click="enablePrjCardView(item);">
                                                        <v-icon class="mr-2" dense>not_interested</v-icon>
                                                        <v-list-item-title><?php echo __('Not Complete'); ?></v-list-item-title>
                                                    </v-list-item>
                                                <?php } ?>
                                                <?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
                                                    <v-list-item @click="deleteProjectCardView(item)">
                                                        <v-icon class="mr-2" dense>delete</v-icon>
                                                        <v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
                                                    </v-list-item>
                                                <?php } ?>
                                            </template>

                                            <template v-else>
                                                <template v-if="(SES_TYPE == 1 || SES_TYPE == 2) && data.proj_users_list[item['id']] && data.proj_users_list[item['id']].indexOf(+SES_ID) > -1 ">
                                                    <?php if ($this->Format->isAllowed('Add Users to Project', $roleAccess)) { ?>
                                                        <v-list-item @click="removeMeFromPrj(item); " :class="'assgnremoveme'+item['uniq_id']">
                                                            <v-icon class="mr-2" dense>remove_circle</v-icon>
                                                            <v-list-item-title>
                                                                <a :data-prj-uid="item['uniq_id']" :data-prj-id="item['id']" :data-prj-name="item['Prjname']" data-prj-usr="<?php echo SES_ID; ?>">
                                                                    <?php echo __('Remove me from here'); ?>
                                                                </a>
                                                            </v-list-item-title>
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
                                                    <v-list-item @click="addUserToProjectCardView(item)">
                                                        <v-icon class="mr-2" dense>add_circle</v-icon>
                                                        <v-list-item-title><?php echo __('Add User'); ?></v-list-item-title>
                                                    </v-list-item>
                                                <?php } ?>
                                                <template v-if="item['totusers']">
                                                    <?php if ($this->Format->isAllowed('Remove Users from Project', $roleAccess)) { ?>
                                                        <v-list-item @click="removeUsrFrmProjectCardView(item)">
                                                            <v-icon class="mr-2" dense>remove_circle</v-icon>
                                                            <v-list-item-title><?php echo __('Remove User'); ?></v-list-item-title>
                                                        </v-list-item>
                                                    <?php } ?>
                                                </template>
                                                <?php if (SES_TYPE == 1 || SES_TYPE == 2) { ?>
                                                    <v-list-item @click="assignRoleFrmCardView(item)">
                                                        <v-icon class="mr-2" dense>add_circle</v-icon>
                                                        <v-list-item-title><?php echo __("Assign Role"); ?></v-list-item-title>
                                                    </v-list-item>
                                                <?php } ?>
                                                <?php if ($this->Format->isAllowed('Edit Project', $roleAccess)) { ?>
                                                    <v-list-item @click="editProjectFrmCardView(item)">
                                                        <v-icon class="mr-2" dense>mode_edit</v-icon>
                                                        <v-list-item-title><?php echo __('Edit'); ?></v-list-item-title>
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
                                                        <!-- "Completed" finalises the project status. Same permission
                                                             gate as the per-status loop above; before this fix it sat
                                                             outside the if-block and was visible to every user who
                                                             could open the menu (including view-only roles). -->
                                                        <v-list-item @click="changePrjStatusCompleted(item)">
                                                            <v-icon class="mr-2" dense>check_circle</v-icon>
                                                            <v-list-item-title><?php echo __('Completed'); ?></v-list-item-title>
                                                        </v-list-item>
                                                    <?php } ?>

                                                </template>

                                                <template v-else>
                                                    <?php if ($this->Format->isAllowed('Delete Project', $roleAccess)) { ?>
                                                        <v-list-item @click="deleteProjectCardView(item)">
                                                            <v-icon class="mr-2" dense>delete</v-icon>
                                                            <v-list-item-title><?php echo __('Delete'); ?></v-list-item-title>
                                                        </v-list-item>
                                                    <?php } ?>
                                                </template>

                                            </template>

                                        </v-list>
                                    <?php } else { ?>
                                        <template v-if="SES_TYPE == 3 && item['user_id'] != SES_ID">
                                            <v-list dense style="max-height: 270px" class="overflow-y-auto" @click="notAuthAlert"></v-list>
                                        </template>

                                        <template v-else>

                                            <v-list dense style="max-height: 270px" class="overflow-y-auto">

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

                                            </v-list>
                                        </template>

                                    <?php } ?>
                                </v-menu>

                            </v-toolbar>

                            <v-card-text class="usr_top_cnt">
                                <div class="title_short_name">
                                    <div class="top_projn">
                                        <h3 class="text-truncate" v-if="item['isactive'] == 1">
                                            <a :title="item.name" @click="projectBodyClick(item)" rel="tooltip"> {{ item.name }} </a>
                                        </h3>
                                        <h3 class="text-truncate" v-if="item['isactive'] == 2">
                                            <a :title="item.name" @click="inactiveProjectBodyClick(item)" rel="tooltip"> {{ item.name }} </a>
                                        </h3>
                                        <span>
                                            <span class="shortname_txt txt_upper_prj" id="tour_proj_shortnm">({{ item.short_name }})</span>
                                            <span>{{ data.methodologies[item.project_methodology_id] }} <?php echo __('Project'); ?></span>
                                        </span>
                                        <!-- <span class="cnt_usr ellipsis-view proj_description_wdth" data-actin="1"></span> -->
                                    </div>
                                </div>
                                <div class="proj_created_by" rel="tooltip" :title="'<?php echo __('Created by') ?> '+ data.p_u_name[item.user_id] +' <?php echo __('on'); ?> '+ item.dateTime" :original-title="'<?php echo __('Created by') ?> '+ data.p_u_name[item.user_id] +' <?php echo __('on'); ?> '+ item.dateTime">
                                    <?php echo __('Created by') ?> {{ data.p_u_name[item.user_id] }} <?php echo __('on'); ?> {{ item.dateTime }}
                                </div>
                                <v-progress-linear color="black" height="5" :value="data.project_progress_data[item['id']]" rel="tooltip" title="<?php echo __("Overall Progress"); ?>">
                                    <template v-slot:default="{ value }">
                                        <strong class="v-progress-text text-no-wrap" :style="{ left: value+'%' }">{{ Math.ceil(value) }}%</strong>
                                    </template>
                                </v-progress-linear>
                                <div class="mt-5"></div>
                                <div class="last_actvty">
                                    <span class="cnt_ttl_usr"><?php echo __('Status Workflow'); ?>:</span>
                                    <span class="cnt_usr" v-if="data.csts_arr_grp != ''"> {{ (item['status_group_id'] != 0 ) ? data.csts_arr_grp[item['status_group_id']]['name'] : '<?php echo __('Default Status Workflow') ?>' }}</span>
                                    <span class="cnt_usr" v-else><?php echo __('Default Status Workflow') ?></span>
                                </div>
                                <div class="last_actvty">
                                    <span class="cnt_ttl_usr"><?php echo __('Last Activity'); ?>:</span>
                                    <span class="cnt_usr" v-if="item['getactivity'] == null"><?php echo __('No activity'); ?></span>
                                    <span class="cnt_usr" v-else> {{ item['localActivityDTArr'] }} </span>
                                </div>
                                <div class="">
                                    <span class="cnt_ttl_usr"><?php echo __('User(s)'); ?>: <span class="cnt_usr">{{ item.totusers }}</span></span>
                                </div>
                                <div class="m-2" style="height: 40px;">
                                    <v-slide-group>
                                        <template v-if="data.proj_users_list[item['id']]">
                                            <v-slide-item v-for="user in data.proj_users_list[item['id']]" :key="user" v-slot="{ active, toggle }">
                                                <v-avatar size="32" v-if="data.proj_users_dtllist[user] && data.proj_users_dtllist[user]['photo']">
                                                    <img :title="data.proj_users_dtllist[user]['name']" rel="tooltip" :src="'<?php echo HTTP_ROOT; ?>'+'users/image_thumb/?type=photos&file=' + data.proj_users_dtllist[user]['photo'] +'&sizex=32&sizey=32&quality=100'" class="lazy" alt="No Image" />
                                                </v-avatar>
                                                <v-avatar class="cmn_profile_holder" size="32" :class="random_bgclr(data.proj_users_dtllist[user]['id'])" v-else>
                                                    <span :title="data.proj_users_dtllist[user] ? data.proj_users_dtllist[user]['name'] : ''" rel="tooltip">{{ data.proj_users_dtllist[user] ? data.proj_users_dtllist[user]['name'].slice(0,1) : '' }}</span>
                                                </v-avatar>
                                            </v-slide-item>
                                        </template>
                                    </v-slide-group>
                                </div>
                                <div class="all_hrs mb-2">
                                    <span class="cnt_ttl_usr"><?php echo __('Hours'); ?> (<?php echo __('Estimated'); ?>/<?php echo __('Spent'); ?>):</span>
                                    <span class="cnt_usr"> {{ item['estimated_hours'] ? item['estimated_hours'] + ' hrs':'0 hrs' }} / {{ formatHour(item.totalhours) }}</span>
                                </div>
                            </v-card-text>

                            <!-- images -->
                            <div class="crdvw_foot_bg">
                                <div class="crdvw_foot">
                                    <div class="tsk_storage">
                                        <ul>
                                            <template v-if="item['isactive'] == 2 || item['status'] == 4">
                                                <li>
                                                    <span class="crdvw task_icon " title="<?php echo __('Task(s)'); ?>" rel="tooltip"><?php echo __('Task(s)'); ?></span>
                                                    <p class=""><span class="" style="font-size: 12px;font-weight: bold;">{{ item['totalcase'] ? item['totalcase'] : 0  }}</span></p>
                                                </li>
                                            </template>
                                            <template v-else>
                                                <li class="project_tasks" style="color:#2e2e2e;cursor:pointer;" @click="projectBodyClick(item,'tasks');">
                                                    <span class="crdvw task_icon project_tasks" title="<?php echo __('Task(s)'); ?>">
                                                        <?php echo __('Task(s)'); ?>
                                                    </span>
                                                    <p class="project_tasks"><span class="project_tasks" style="font-size: 12px;font-weight: bold;">
                                                            {{ item['totalcase'] ? item['totalcase'] : 0  }}
                                                        </span></p>
                                                </li>
                                            </template>
                                            <li class="vline_li" style="cursor:default;">
                                                <span class="crdvw stroage_icon text-truncate" title="<?php echo __('Storage'); ?>"></span><span class="text-truncate" style="font-weight:normal;"><?php echo __('Storage'); ?></span>
                                                <p>{{ item['totalcase'] && item['storage_used'] ? (item['storage_used'] / 1024).toFixed(2) : '0.00' }} mb </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="se_date" style="cursor:default;">
                                        <ul>
                                            <li>
                                                <span class="crdvw date_icon"></span><?php echo __("Start Date"); ?>
                                                <p v-if="item['start_date']">
                                                    {{ item['project_tz_startdate'] }}
                                                </p>
                                                <p v-else>
                                                    <?php echo __("N/A"); ?>
                                                </p>
                                            </li>
                                            <li>
                                                <span class="crdvw date_icon"></span><?php echo __("End Date"); ?>
                                                <p v-if="item['end_date']">
                                                    {{ item['project_tz_enddate'] }}
                                                </p>
                                                <p v-else>
                                                    <?php echo __("N/A"); ?>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </v-card>
                    </v-hover>
                </v-col>
            </v-row>
            <v-row v-if="noProjects" class="mt-2">
                <v-col cols="12">
                    <div class="cmn_bdr_shadow no-data-box extra nodata-foud-program-box">
                        <div>
                            <!-- <p class="head">No Projects Found</p> -->
                            <p class="sub-head">No Projects Found</p>
                        </div> <img style="max-height: 100px;" src="<?php echo HTTP_ROOT;?>img/no-data/no-project.png" /> <br><br> <?php if ($this->Format->isAllowed('Create Project', $roleAccess)) { ?><span class="m-left"><a class="btn btn_cmn_efect cmn_bg btn-info cmn_size" href="javascript:void(0)" onClick="newProject('', '', undefined, false, undefined, undefined, '<?php echo $program_id ?? ''; ?>')"><?php echo __('Create New Project'); ?><div class="ripple-container"></div></a></span><?php } ?>
                    </div>
                </v-col>
            </v-row>
            <template v-if="data.prjAllArr && data.prjAllArr.length">
                <div class="text-center my-5">
                    <div class="show_total_case">{{ data.pgShLbl }}</div>
                    <v-pagination class="v-pagination-inline" v-model="projectCardViewOptions.page" :length="page_length" :total-visible="projectCardViewOptions.total_visible"></v-pagination>
                </div>
            </template>
        </v-container>
    </v-app>
</div>

<script>
    let changeProjectStatusUrl = '<?php echo $this->Url->build(["controller" => "Projects","action" => "changeProjectStatus"]);?>';
    let cardViewUrl = '<?php echo $this->Url->build(["controller" => "Projects","action" => "ajaxCardView"]);?>';
    $(document).ready(function() {
        var vue_obj = new Vue({
            el: '#project-card-view',
            vuetify: new Vuetify({
                icons: {
                    iconfont: 'md'
                }
            }),
            data() {
                return {
                    project: {},
                    projectData: {
                        Project: {},
                        ProjectMeta: {},
                        InvoiceCustomer: {},
                    },
                    projectDetails: {
                        project: {}
                    },
                    editProjectLoader: false,
                    editProjectDialog: false,
                    SES_TYPE: '<?php echo SES_TYPE; ?>',
                    SES_ID: '<?php echo SES_ID; ?>',
                    projectCardViewOptions: {
                        projtype: '<?php echo $projtype; ?>',
                        filtype: '<?php echo $filtype; ?>',
                        p_type: '<?php echo $p_type; ?>',
                        client: '<?php echo $client; ?>',
                        manager: <?php echo json_encode((string)($manager_id ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                        program: <?php echo json_encode((string)($program_id ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                        project: <?php echo json_encode((string)($project_uid ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                        url_status: <?php echo json_encode((string)($url_status ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                        proj_srch: <?php echo json_encode((string)($prjsrch ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                        page: 1,
                        page_limit: 18,
                        total_visible: 7,
                    },
                    page_length: 1,
                    projects: [],
                    data: {},
                    total: 0,
                    searchInput: '',
                    search: '',
                    searching: false,
                    searchBoxClosed: true,
                    showNoProjects: true,
                    empty: true,
                    scrollOptions: {
                        offset: "10%",
                    },
                    bgclr: ['clr1', 'clr2', 'clr3', 'clr4', 'clr5', 'clr6', 'clr7', 'clr8', 'clr9', 'clr10'],
                    cmn_search_result: false,
                }
            },
            created() {
                this.getProjects();
            },
            mounted() {
                // $("#projectCardViewLoader").hide();
            },
            computed: {
                params(nv) {
                    return {
                        ...this.projectCardViewOptions
                    };
                },
                noProjects() {
                    if (this.data.prjAllArr) {
                        return !this.data.prjAllArr.length;
                    }
                    return false;
                },
            },
            watch: {
                params: {
                    handler(nv, ov) {
                        this.getProjects(this.params);
                    },
                    deep: true
                },
            },
            methods: {
                formatHour(secs) {
                    var hrs = Math.floor(secs / 3600) > 0 ? Math.floor(secs / 3600) + ' hr' + (Math.floor(secs / 3600) > 1 ? 's' : '') + ' ' : '';
                    var mins = Math.round((secs % 3600) / 60) > 0 ? Math.round((secs % 3600) / 60) + ' min' + (Math.round((secs % 3600) / 60) > 1 ? 's' : '') + '' : '';
                    return hrs != '' || mins != '' ? hrs + mins : '0 hrs';
                },
                getStatus(project) {
                    let status = project.status;
                    let isactive = project.isactive;
                    if (isactive == 2 || status == 4) {
                        return '<?php echo __('Completed'); ?>';
                    }
                    if (status == 1) {
                        return '<?php echo __('Started'); ?>';
                    }
                    if (status == 2) {
                        return '<?php echo __('On Hold'); ?>';
                    }
                    if (status == 3) {
                        return '<?php echo __('Stack'); ?>';
                    }
                    return this.data.ProjectStatus[status];
                },
                getStatusClass(project) {
                    let status = project.status;
                    let isactive = project.isactive;
                    if (isactive == 2 || status == 4) {
                        return 'completed';
                    }
                    if (status == 1) {
                        return 'started'
                    }
                    if (status == 2) {
                        return 'on-hold'
                    }
                    if (status == 3) {
                        return 'stack';
                    }
                    return 'stack';
                },
                getPriority(proj_priority) {
                    var priorities = ['high', 'medium', 'low'];
                    return priorities[proj_priority] || '';
                },
                getPriorityTitle(proj_priority) {
                    var proj_priority = this.getPriority(proj_priority);
                    return proj_priority && proj_priority.length ? proj_priority.replace(proj_priority[0], proj_priority[0].toUpperCase()) + ' Priority' : '';
                },
                random_bgclr(id) {
                    return this.bgclr[id % 10];
                },
                searchTable(value) {
                    this.searching = true;
                    this.search = value;
                },
                addOrRemoveUser(item, typ) {
                    let proj_id = item['id'];
                    let proj_uid = item['uniq_id'];
                    let user_ids = '<?php echo SES_ID; ?>';
                    let pname = item['Prjname'];
                    $.ajax({
                        url: HTTP_ROOT + 'projects/assignRemovMeToProject',
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

                    let item_class = 'assgnremoveme' + item['uniq_id'];
                    var vue_obj = this;
                    // this.addOrRemoveUser(item, 'rm');
                    let loc = HTTP_ROOT + "projects/assignRemovMeToProject/";
                    let proj_id = item['id'];
                    let proj_uid = item['uniq_id'];
                    let user_ids = '<?php echo SES_ID; ?>';
                    let pname = item['Prjname'];
                    let user_arr = [user_ids];
                    let param_data = {
                        project_id: proj_id,
                        user_arr: user_arr,
                        field: 'id'
                    }
                    $.ajax({
                            url: `${HTTP_ROOT}projects/ajaxcheckUserTasks`,
                            type: 'POST',
                            dataType: 'json',
                            data: param_data,
                        })
                        .done(function(res) {
                            if (res.status) {
                                $.post(loc, {
                                    'user_ids': user_ids,
                                    'project_id': proj_id,
                                    'typ': 'rm'
                                }, function(res) {
                                    if (res.status == 'nf') {
                                        showTopErrSucc('error', '<?php echo __("Failed to assign user to the project."); ?>');
                                    } else {
                                        if (trim(res.message) != '') {
                                            showTopErrSucc('success', res.message);
                                            vue_obj.getProjects();
                                            // $('.assgnremoveme' + proj_uid).html('<a href="javascript:void(0);" class="icon-add-usr" data-prj-uid ="' + proj_uid + '" data-prj-id="' + proj_id + '" data-prj-name="' + pname + '" data-prj-usr="<?php echo SES_ID; ?>" onclick="assignMeToPrj(this);"><i class="material-icons">&#xE147;</i> <?php echo __("Add me here"); ?></a>');
                                        }
                                    }
                                }, 'json');
                            } else {
                                //Show Popup
                                openPopup();
                                $(".ass_task_user").show();
                                $('#inner_usr_case_add').hide();
                                $('#pop_up_assign_case_user_label').hide();
                                $('.add-prj-btn').hide();
                                $('#inner_usr_case_add').html('');
                                $(".popup_bg").css({
                                    "width": '850px'
                                });
                                $(".popup_form").css({
                                    "margin-top": "6px"
                                });
                                $('#inner_usr_case_add').hide();
                                $.ajax({
                                        url: `${HTTP_ROOT}projects/ajaxGetProjUsers`,
                                        type: 'POST',
                                        dataType: 'html',
                                        data: {
                                            param_data: param_data,
                                            user_data: res,
                                            page: PAGE_NAME,
                                            el_class: item_class
                                        },
                                    })
                                    .done(function(res_data) {
                                        $(".loader_dv").hide();
                                        $('#inner_usr_case_add').show();
                                        $('#inner_usr_case_add').html(res_data);
                                        $('#pop_up_assign_case_user_label').html('');
                                        $('#pop_up_assign_case_user_label').html($('#hid_ext_use_lbl').html());
                                        $('#pop_up_assign_case_user_label').css('display', 'block');
                                        $('.add-prj-btn').show();
                                        $.material.init();
                                    });
                            }
                        });
                },
                addUserToProjectCardView(item) {
                    let prj_id = item['uniq_id'];
                    let prj_name = item['Prjname'];
                    addUsersToProject(prj_id, prj_name);
                },
                editProjectFrmCardView(item) {
                    let proj_id = item['id'];
                    let prj_id = item['uniq_id'];
                    let user_ids = '<?php echo SES_ID; ?>';
                    let prj_name = item['Prjname'];
                    openPopup();
                    $(".loader_dv").show();
                    $("#inner_prj_edit").hide();
                    $(".edt_prj").show();
                    $("#header_prj").html(prj_name);
                    $.post(HTTP_ROOT + "projects/ajaxEditProject", {
                        "pid": prj_id,
                        "page": 'manage_card'
                    }, function(data) {
                        if (data) {
                            $(".loader_dv").hide();
                            $('#inner_prj_edit').show();
                            $('#inner_prj_edit').html(data);
                            $.material.init();
                            $('.proj_prioty').select2();
                            $('.proj_methodology').select2();
                            $('.sel_Typ_dp').select2();
                            $('.tsk_Typ_dp').select2({templateSelection: formatTaskType,templateResult: formatTaskType});
                            $('.status_typ_dp').select2();
                            $('.workflow_dp').select2();
                            if (typeof $.fn.autoGrow === 'function') {
                                $('textarea').autoGrow().keyup();
                            }
                        }
                    });
                },
                removeUsrFrmProjectCardView(item) {
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
                assignRoleFrmCardView(item) {
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
                            url: changeProjectStatusUrl,
                            type: "POST",
                            dataType: 'json',
                            data: {
                                prj_id,
                                prj_name,
                                status_name,
                                status_id
                            },
                            headers: {
                                'X-CSRF-Token': _csrfToken
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
                deleteProjectCardView(item) {
                    let projuid = item['uniq_id'];
                    let prj_nm = item['Prjname'];
                    if (confirm(_("Are you sure to delete project") + " '" + prj_nm + "'")) {
                        if (confirm(_("All the Tasks, Files associated with") + " '" + prj_nm + "' " + _("will be deleted permanently."))) {
                            $.ajax({
                                url: HTTP_ROOT + '/projects/ajaxDeleteProject',
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    projuid,
                                    prj_nm,
                                },
                                context: this,
                                success: function(data, textStatus, jqXHR) {
                                    showTopErrSucc(data.status, data.message);
                                    this.getProjects()
                                }
                            });
                        }
                    }
                },
                disablePrjCardView(item) {},
                enablePrjCardView(item) {
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
                projectBodyClick(item) {
                    let uniq_id = item['uniq_id'];
                    let tasks = '';
                    if (typeof arguments[1] != 'undefined' && arguments[1] == 'tasks') {
                        tasks = 'tasks';
                    }
                    if (projectBodyClick != 'undefined' && typeof projectBodyClick === "function") {
                        if (tasks !== '') {
                            projectBodyClick(uniq_id, tasks);
                        } else {
                            projectBodyClick(uniq_id);
                        }
                    } else {
                        remember_filters('ALL_PROJECT', '');
                        resetAllFilters('all', 1);
                        $('#projFil').val(uniq_id);
                        $.ajax({
                            url: HTTP_ROOT + '/projects/updateDateVisited',
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
                    }
                },
                inactiveProjectBodyClick(item) {
                    let uniq_id = item['uniq_id'];
                    $.post(`${HTTP_ROOT}projects/updateDateVisited`, {
                        'uniq_id': uniq_id
                    }, function(res) {
                        if (res.status == 'success') {
                            window.location.href = HTTP_ROOT + "dashboard/#overview?prouid=" + uniq_id;
                        } else {
                            showTopErrSucc('error', '<?php echo __('Oops! You are not a member of the project. Please add yourself as a member of this project.'); ?>');
                            return false;
                        }
                    }, 'json');
                },

                getProjects(params) {
                    this.loading = true;
                    this.cmn_search_result = false;
                    $("#projectCardViewLoader").show();
                    let options = params ? params : this.params;
                    let customHeaders = {
                        'X-CSRF-Token': _csrfToken
                    };
                    axios.post(cardViewUrl, options, {
                        headers: customHeaders
                    })
                        .then((response) => {
                            let data = response.data;
                            this.data = data;
                            this.total = +data.caseCount;
                            this.page_length = Math.ceil(+data.caseCount / this.projectCardViewOptions.page_limit);
                            if (this.projectCardViewOptions.srch) {
                                this.searching = false;
                            }
                            this.loading = false;
                            this.setProjectCount(data);
                            this.$vuetify.goTo('#scroll-row', this.scrollOptions);
                            $("#projectCardViewLoader").hide();
                            this.setSearchBanner(data);
                        })
                        .catch((error) => {})
                        .finally(() => {
                            $("#projectCardViewLoader").hide();
                            $('[rel="tooltip"]').tipsy({
                                gravity: 's',
                                fade: true
                            });
                        });
                },
                notAuthAlert() {
                    showTopErrSucc('error', "<?php echo __('Oops! You are not authorized to do this operation. Please contact your Admin/Owner'); ?>.");
                },
                setProjectCount(data) {
                    $('#inactive_proj_cnt').text('(' + (data.inactive_project_cnt ? data.inactive_project_cnt : 0) + ')');
                    $('#active_proj_cnt').text('(' + (data.active_project_cnt ? data.active_project_cnt : 0) + ')');
                    $('#started_proj_cnt').text('(' + (data.started_project_cnt ? data.started_project_cnt : 0) + ')');
                    $('#hold_proj_cnt').text('(' + (data.hold_project_cnt ? data.hold_project_cnt : 0) + ')');
                    $('#stack_proj_cnt').text('(' + (data.stack_project_cnt ? data.stack_project_cnt : 0) + ')');
                },
                setSearchBanner(data) {
                    this.cmn_search_result = true;
                    if (this.projectCardViewOptions.project) {
                        let project_name = data.prjAllArr[0]['Prjname'];
                        if (this.$refs['cmn_search_result_text']) {
                            this.$refs['cmn_search_result_text'].innerText = project_name;
                        }
                    }
                },
            },
        });
        $(document).on('projects:reload', function() {
            vue_obj.getProjects();
        });
    });
</script>

<script>
    $(document).ready(function() {
        setTimeout(hideCmnMesg, 2000);
        $('.cmn_bdr_shadow').click(function(e) {
            if (!$(e.target).closest('.usr_act_det').length) {
                var prjuid = $(e.target).closest('.cmn_bdr_shadow').attr('data-prjuid');
                if (typeof prjuid != 'undefined') {
                    var actIn = $("#actIn_" + prjuid).attr('data-actIn');
                    if (actIn == 1) {
                        if ($(e.target).hasClass('project_tasks')) {
                            projectBodyClick(prjuid, 'tasks');
                        } else {
                            //projectBodyClick(prjuid);
                        }
                    } else if (actIn == 2) {
                        //inactiveProjectBodyClick(prjuid);
                    }
                }
            }
        });
    });

    var table;
    $(document).on('click', '.remove-user-pop button', function() {
        $(document).trigger("projects:reload");
    });
    $(document).on('click', '.ass_task_user.cmn_popup.assign-project-pop.assign_tsk_to_usr .close.close-icon', function(event) {
        $(document).trigger("projects:reload");
    });
</script>

</div>
<div class="cb"></div>
<input type="hidden" id="getcasecount" value="<?php echo $caseCount ?? 0; ?>" readonly="true" />
<input type="hidden" id="totalcount" name="totalcount" value="<?php echo $count ?? 0; ?>" />
