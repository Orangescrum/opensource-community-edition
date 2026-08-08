<style type="text/css">
    .import_project_div #pname_dashboard{max-width:226px;}
</style>
<div class="user_profile_con setting_wrapper task_listing import-export-page cmn_tbl_widspace width_hover_tbl">
    <!--Tabs section starts -->
    <?php echo $this->element('import_page_tabs', array('mode' => 'importjira')); ?>
    <div class="imp-exp-upu">
        <ul id="breadcrumbs_imp">
            <li <?php if (PAGE_NAME == 'importexport') { ?>class="activ"<?php } ?>><?php echo __('Upload File');?></li>
            <li <?php if (PAGE_NAME == 'csvDataimport') { ?>class="activ"<?php } ?> ><?php echo __('Preview Data');?></li>
            <li <?php if (PAGE_NAME == 'confirmImport') { ?>class="activ"<?php } ?>><?php echo __('Upload Summary');?></li>
        </ul>
    </div>
    <?php $swPrjVal = '';?>
    <?php if (PAGE_NAME != 'confirmImport') { ?>
    <div class="row exp_innerdiv" id="imploade_file"  <?php
    if (isset($fileds)) {
        echo "style='display:none;'";
    }
    ?>>
        <div class="col-lg-5 col-sm-5">
            <div class="import-csv-file">
                <!-- <div class="import_project_div mtop20 mbtm15 pr">
                    <span class="browse-file-name"><?php echo __('Project');?>:</span>
                    <?php if ((count($getallproj) == 0) && (SES_TYPE == 1 || SES_TYPE == 2)) { ?>
                        <button onclick="newProject('menupj', 'loaderpj');"><?php echo __('Create Project');?></button>
                    <?php } else { ?>
                        <?php if (count($getallproj) == 0) { ?>
                            --<?php echo __('None');?>--
                        <?php } else {
                            $swPrjVal = $import_pjname ?? ($projectname ?? '');
                            ?>
                            <a href="javascript:void(0);" onclick="view_project_menu('import');" data-toggle="dropdown" class="option-toggle" id="prj_ahref">
                                <span id="pname_dashboard"><?php echo $this->Format->shortLength(ucfirst($swPrjVal), 30); ?></span>
                                <i class="caret"></i>
                            </a>
                            <div class="dropdown-menu lft popup scroll-project" id="projpopup">
                                <center>
                                    <div id="loader_prmenu" style="display:none;">
                                        <img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="loading..." title="loading..."/>
                                    </div>
                                </center>
                                <?php if (count($getallproj) >= 6) { ?>
                                    <div id="find_prj_dv" style="display: none;">
                                        <input type="text" placeholder="<?php echo __('Find a Project');?>" class="form-control pro_srch" onkeyup="search_project_menu('import', this.value, event)" id="search_project_menu_txt">
                                        <i class="icon-srch-img"></i>
                                        <div id="load_find_dashboard" style="display:none;" class="loading-pro">
                                            <img src="<?php echo HTTP_IMAGES; ?>images/del.gif"/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <input type="hidden" id="caseMenuFilters" value="" />
                                <div id="ajaxViewProject" style='display:none;'></div>
                                <div id="ajaxViewProjects"></div>
                            </div>
                            <?php // }  ?>
                        <?php } ?>
                    <?php } ?>
                </div> -->
                <?= $this->Form->create(null, ['url' => ['controller' => 'Projects', 'action' => 'csvDataimport', $proj_uid ?? ($porj_uid ?? '')], 'enctype' => 'multipart/form-data', 'id' => 'data_import_form']) ?>
                            <div class="form-group">
                                <label for="api_token"><?php echo __('API Token'); ?></label>
                                <input type="text" class="form-control" id="api_token" name="api_token" required placeholder="<?php echo __('e.g. NzM2MjM4MDotYWRtaW4tMTIzNDU2Nzg5'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="jira_email"><?php echo __('JIRA Login Email Address'); ?></label>
                                <input type="email" class="form-control" id="jira_email" name="jira_email" required placeholder="<?php echo __('e.g. user@example.com'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="jira_password"><?php echo __('Password'); ?></label>
                                <input type="password" class="form-control" id="jira_password" name="jira_password" required placeholder="<?php echo __('Your JIRA password'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="jira_url"><?php echo __('JIRA Account URL'); ?>:</label>
                                <input type="url" class="form-control" id="jira_url" name="jira_url" required placeholder="<?php echo __('e.g. https://your-domain.atlassian.net'); ?>">
                            </div>
                    <input type="hidden" value="<?php echo $proj_id ?? ($porj_id ?? ''); ?>" name="proj_id" id="proj_id"/>
                    <input type="hidden" value="<?php echo $proj_uid ?? ($porj_uid ?? ''); ?>" name="proj_uid" id="proj_uid"/>
                     
                    <span id="err_span" style="color:#900;font-size:12px"></span>
                    <div class="import_btn_div text-right">
                        <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="<?php echo __('Loading'); ?>..." title="<?php echo __('Loading'); ?>..."  id="loader_img_csv" style="display: none;"/>
                        <button type="submit" id="cnt_btn" class="btn btn_cmn_efect cmn_bg btn-info cmn_size cmn_disabled_btn" disabled="true">
                            <i class="icon-big-tick"></i>
                            <span><?php echo __('Continue');?></span>
                        </button>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <div class="col-lg-7 col-sm-7">
            <div class="import-info-dif import_proj">
                <div class="chk_content">
                    <h4 class="chk_head"><?php echo __('JIRA Import Details');?></h4>
                    <ul class="chk_desc">
                        <li><b><?php echo __('API Token');?> - </b> <?php echo __('Your JIRA API token');?></li>
                        <li><b><?php echo __('JIRA Login Email');?> - </b> <?php echo __('Email address associated with your JIRA account');?></li>
                        <li><b><?php echo __('Password');?> - </b> <?php echo __('Your JIRA account password');?></li>
                        <li><b><?php echo __('JIRA Account URL');?> - </b> <?php echo __('URL of your JIRA instance (e.g., https://your-domain.atlassian.net)');?></li>
                    </ul>

                    <h4 class="chk_head"><?php echo __('Help');?></h4>
                    <ul class="chk_desc">
                        <li><?php echo __('Enter your JIRA API token, login email, password, and account URL');?></li>
                        <li><?php echo __('Ensure you have the necessary permissions in JIRA to access the projects and issues you want to import');?></li>
                        <li><?php echo __('The system will connect to your JIRA account and retrieve available projects and issues');?></li>
                        <li><?php echo __('You will be able to select which JIRA projects and issues to import');?></li>
                        <li><?php echo __('Review the data before confirming the import');?></li>
                        <li><?php echo __('The imported JIRA issues will be converted to tasks in the selected project');?></li>
                        <li><span><?php echo __('Make sure to keep your JIRA credentials secure and do not share them with others');?></span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="cb"></div>
    </div>
    <div id="review_data" <?php if (!isset($fileds)) { ?>style="display: none;"<?php } ?>>
        <?php if (isset($fileds)) { ?>
            <?= $this->Form->create(null, ['url' => ['controller' => 'Projects', 'action' => 'confirmImport', $porj_uid ?? '']]); ?>
            <input type="hidden" value="<?php echo $porj_id ?? ($porj_id ?? ''); ?>" name="project_id" />
            <input type="hidden" value="<?php echo $csv_file_name ?? ($csv_file_name ?? ''); ?>" name="csv_file_name" />
            <input type="hidden" value="<?php echo $total_rows ?? 0; ?>" name="total_rows" />
            <input type="hidden" value="<?php $mserialize = serialize($milestone_arr ?? []);
            echo htmlentities($mserialize);
            ?>" name="milestone_arr"/>
            <input type="hidden" value="<?php echo $new_file_name ?? ($new_file_name ?? ''); ?>" name="new_file_name"/>
            <textarea name="task_arr" style="display:none;"><?php echo json_encode($task ?? []); ?></textarea>
            <div class="imp_data_outer mbtm30 weekly_btm_sumry imp_task">
                <?php if (isset($task_err) && $task_err) { ?>
                <div class="data-import-err">
                    <h2 class="cmn_h2"><span><?php echo __('Project');?>:&nbsp;</span><?php echo $projectname; ?></h2>
                    <p class="text-success" <?php if (count($task) == 0) { ?>style="color:red"<?php } else { ?>style="color:green"<?php } ?>>
                        <b><?php echo count($task); ?></b> <?php echo __('Tasks to Import');?>
                    </p>
                    <p class="text-muted"><?php echo __('Please double-check(specifically text showing in red color) the below points before importing your Tasks');?></p>
                    <ul>
                        <li><?php echo __('Blank Title');?></li>
                        <li><?php echo __('Invalid Due Date');?> (<?php echo __('should be');?> <b>MM</b>/<b>DD</b>/<b>YYYY</b>)</li>
                        <li><?php echo __('Invalid or Misspelled Status');?></li>
                        <li><?php echo __('Invalid or Misspelled Type');?></li>
                        <li><?php echo __('Unknown Assigned To Email ID (User must be associated with the project)');?></li>
                        <li><?php echo __('Unknown Create By Email ID (User must be associated with the project)');?></li>
                        <li><?php echo __('Invalid Estimated Hour');?></li>
                        <li><?php echo __('Invalid start time or end time or break time or spent hour');?></li>
                        <li><?php echo __('If');?> "<?php echo __('Project Name');?>" <?php echo __('are showing in red color then project does not exits in your application, it will be created as new project');?></li>
                        <li>"<?php echo __('Assigned To');?>" <?php echo __('must be a valid email address of an existing/invited user in Orangescrum');?>.</li>
                    </ul>
                    <?php if($is_ttl_length){ ?>
                        <div style="position: absolute;font-size: 15px;color: #ff0000;">
                            <?php echo __('We have found').' '.$is_ttl_length.' '.__('tasks having character length more than 240.'); ?> <br />
                            <?php echo __('Limit the task title to 240 at max, otherwise it will be truncated after upload.');?>
                        </div>
                    <?php } ?>
                    <button type="submit" class="fr btn btn-sm btn_cmn_efect cmn_bg btn-info cmn_size" style="position:relative;">
                        <i class="icon-big-tick"></i>
                        <?php echo __('Confirm & Import');?>
                    </button>
                    <button type="button" class="fr btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="deleteCsvFile('<?php echo $new_file_name; ?>');"  style="margin-right:10px;"><?php echo __('Cancel');?></button>
                    <div class="cb"></div>
                </div>
            </div>
            <?php }
            ?>
            <div class="cmn_tbl_widspace imprt_task_page_tbl">
                <table class="table table-striped table-hover tsk_tbl arc_tbl" id="preview_data_tbl">
                    <thead>
                    <tr class="tab_tr">
                        <th><?php echo __('Sl');?>#</th>
                        <?php
                        $_t_field_arr = array('estimated hour', 'start time', 'end time', 'break time');
                        foreach ($fileds as $hk => $hv) {
                            ?>
                            <th><?php
                                echo $hv;
                                if (in_array(strtolower($hv), $_t_field_arr)) {
                                    echo ' (hh:mm)';
                                    if (strtolower($hv) == 'start time' || strtolower($hv) == 'end time') {
                                        echo '(am/pm)';
                                    }
                                }
                                ?></th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (isset($task) && $task) {
                        $error_arr = $task_err;
                        $i = 0;
                        foreach ($task AS $k => $v) {
                            $i++;
                            ?>
                            <tr class="tr_all">
                                <td><?php echo $i; ?> </td>
                                <?php foreach ($fileds as $hk => $hv) { ?>
                                    <?php if (isset($v[strtolower($hv)])) { ?>
                                        <td <?php if ($error_arr[$k][strtolower($hv)]) {
                                            $err = 1;
                                            ?>class="error-imp-data"<?php } ?> valign="top">
                                            <?php
                                            if (in_array(strtolower($hv), array('taskgroup', 'title', 'description'))) {
                                                echo $this->Format->formatTitle($v[strtolower($hv)]);
                                            } else {
                                                echo htmlentities($v[strtolower($hv)]);
                                            }
                                            ?>
                                        </td>
                                    <?php }
                                }
                                ?>
                            </tr>
                        <?php } ?>
                        <?php //}}   ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php if ($task) { ?>
                <div class="mtop20 text-right">
                    <button type="button" class="btn btn-default btn_hover_link cmn_size mright5" data-dismiss="modal" onclick="deleteCsvFile('<?php echo $new_file_name; ?>');"><?php echo __('Cancel');?></button>
                    <button type="submit" class="btn btn-sm btn_cmn_efect cmn_bg btn-info cmn_size" style="position:relative">
                        <i class="icon-big-tick"></i>
                        <?php echo __('Import');?>
                    </button>
                </div>
            <?php } ?>
            <?php } ?>
    </div>
    <?= $this->Form->end(); ?>
</div>
<?php } else { ?>
    <div id="review_data">
        <h3><?php echo __('Upload Summary');?></h3>
        <table class="fyl_table" style="width:100%">
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td colspan="2"><span class="upld-sum-lebel"><?php echo __('Input CSV file');?>:&nbsp;</span><b><?php echo $csv_file_name; ?></b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span class="upld-sum-lebel"><?php echo __('Total data');?>:&nbsp;</span><b><?php echo!empty($newtotal_task) ? ($newtotal_task ) : 0; ?></b> <?php echo __('rows');?></td>
                        </tr>
                        <tr>
                            <td colspan="2" ><span class="upld-sum-lebel"><?php echo __('Valid data');?>:&nbsp;</span><b><?php echo!empty($total_valid_rows) ? $total_valid_rows : 0; ?></b> <?php echo __('rows');?></td>
                        </tr>
                        <tr>
                            <td colspan="2" ><b><?php echo $total_task ?? ($total_rows ?? ''); ?></b><span class="upld-sum-lebel"> <?php echo __('Task(s) Imported into project');?>:&nbsp;</span><b><?php echo!empty($proj_name) ? $proj_name : 'NA'; ?></b></td>
                        </tr>
                        <?php if (isset($non_create_projects) && !empty($non_create_projects)) { ?>
                            <tr>
                                <td colspan="2" ><span class="upld-sum-lebel"><?php echo __("Project(s) can't Imported");?> :&nbsp;</span><b><?php echo!empty($non_create_projects) ? $non_create_projects : 'NA'; ?></b> <i style='color:#ff0000;margin-left: 20px;'> <?php echo __('Project Limit Exceeded');?>!. <?php echo __('Please');?> <a href="<?php echo HTTP_ROOT . 'users/pricing' ?>" target="_blank"><?php echo __('Upgrade');?></a> .</i></td>
                            </tr>
                        <?php } ?>
                        <?php if ($non_existing_typ_with) { ?>
                            <tr>
                                <td colspan="4">
                                    <b><?php echo __('Note'); ?>: </b>
                                    <span class="upld-sum-lebel" style="color:red;">
                                        <?php echo __('We found some non existence task type(s)/blank  spaces');?>: <span style="color:#639fed"><?php echo implode(', ', $non_existing_typ); ?></span> <br />
                                       <?php echo ' ' . __('We have replaced those task type(s) with');?>:  <span style="color:#639fed"><?php echo $non_existing_typ_with; ?></span>
                                        <br />
                                        <?php echo __('If you want to change the task type(s) then please follow the below steps');?>:
                                        <ul>
                                            <li><?php echo __('Go to the');?> <a href="<?php echo HTTP_ROOT . 'task-type'; ?>"><?php echo __('Task Type');?></a> <?php echo __('section and add');?> "+ <?php echo __('New Task Type');?>".</li>
                                            <li><?php echo __('Edit each task and update the task type');?>.</li>
                                        </ul>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
<?php } ?>
</div>
</div>