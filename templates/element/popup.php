<!--[if lte IE 9]>
<style>
    #chked_all{top:2px!important;}
</style>
<![endif]-->
<style>
    .abc {
        background: #FCFCFC;
        border: 1px solid #CCCCCC;
        box-shadow: 0 0 10px #AAAAAA;
        height: 200px;
        left: 50%;
        margin-left: -200px;
        position: fixed;
        top: 100px;
        width: 400px;
    }

    .quicktask_tr_backlog .form-group.has-error.is-focused .form-control {
        background-image: linear-gradient(#5093EC, #5093EC), linear-gradient(#d2d2d2, #d2d2d2);
        background-image: -webkit-linear-gradient(#5093EC, #5093EC), linear-gradient(#d2d2d2, #d2d2d2);
        border-color: #d2d2d2;
    }

    .icon_sec {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .icon_sec p {
        line-height: 30px;
        margin: 0px;
        font-size: 20px;
        font-weight: 600;
    }

    .icon_sec img {
        margin-right: 15px;
        max-width: 100%;
        display: inline-block
    }

    .modal-content.modal_popup_sec {
        width: 600px;
        max-width: 700px;
        margin: 0 auto
    }
    .modal-dialog .team-ui-field .select2-container--default .select2-selection--multiple .select2-selection__rendered li{
    list-style: none;
    display: inline-flex;
    float: none;
    width: auto;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    position: relative;
    align-items: center;
}
.busines-un .select2-container--default .select2-selection--single .select2-selection__clear {
    margin-right: 30px;
}
</style>
<script type="text/javascript">
    var rsrch = "";
</script>

<!-- START Modal -->
<div class="container cmn-popup ">
    <div class="modal fade" id="myModal" role="dialog" style="z-index:2001">
        <!-- Release Info Modal starts -->
        <div class="cmn_popup relese_description_pop" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content" id="inner_rels_detl">
                </div>
            </div>
        </div>
        <!-- Release Info Modal ends-->
        <!-- Sync Notifier Modal -->
        <div class="cmn_popup sync_prog_pop" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content" id="inner_rels_detl">
                    <div class="modal-header">
                        <h4 id="header_rlinfo"><?php echo __('Initial Sync in progress'); ?>.</h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="dtbl" target="javascript:void(0)">
                            <div class="dtbl_cel">
                                <img class="provider-icon os-provider-icon" alt="os logo"
                                    src="<?php echo HTTP_ROOT; ?>img/logo/logo-sm.svg" width="64" height="64">
                            </div>
                            <div class="dtbl_cel">
                                <h5><?php echo __('Syncing'); ?>..</h5>
                            </div>
                            <div class="dtbl_cel">
                                <img class="provider-icon os-provider-icon" alt="git logo"
                                    src="<?php echo HTTP_ROOT; ?>img/github/git-64.png">
                            </div>
                        </div>
                        <p class="note_hnt">
                            <span><i class="material-icons">error_outline</i></span>
                            <?php echo __("Please don't close or refresh this page"); ?>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- New extra coupon popup starts -->
        <div class="new_extracoupon cmn_popup" style="display: none;margin-top:13%">
        </div>
        <!-- New refer a friend popup starts -->
        <div class="new_referafriend cmn_popup createnew-project-pop" style="display: none;">
        </div>
        <!-- New project popup starts -->
        <div class="new_project cmn_popup createnew-project-pop" style="display: none;">
            <?php echo $this->element('popup/new_project'); ?>
        </div>

        <!-- Remove User popup starts -->
        <div class="remove_active_user cmn_popup createnew-project-pop cmn_auto_scroll_modal" style="display: none;">
            <?php echo $this->element('popup/remove_active_user'); ?>
        </div>


        <!--  All Wiki Popup Starts Here  -->

        <!-- Attachment Wiki popup starts -->

        <div class="attachment_pop_wiki cmn_popup" style="display: none;">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header popup-header">
                        <button type="button" class="close close-icon" data-dismiss="modal"
                            onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('All Attachments'); ?></h4>
                        <input type="hidden" id="hid_wiki_id" name="hid_wiki_name" value="" />
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_div_wiki">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <center>
                            <div id="cat_err_msg" style="color:#FF0000;display:none;"></div>
                        </center>

                        <div style="display:none;" id="inner_attachments_wiki">
                            <div class="logtime-content-wiki">
                                <div>
                                    <table border="0" cellpadding="2" cellspacing="2" width="100%"
                                        class="ClsTableDesignwiki">
                                        <tr>
                                            <td style="width:40%;vertical-align:top;padding:0px 5px 0 0;">
                                                <div id="displayAllAttachwiki"
                                                    style="height:440px;overflow-y:auto;overflow-x:hidden;"></div>
                                            </td>
                                            <td style="width:60%;vertical-align:top;text-align:center;padding:5px;"
                                                class="displayMainAttachwiki"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:left;padding:5px;">
                                                <div class="button_loader_div_wiki" style="display:none;">
                                                    <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif"
                                                            alt="Loading..." title="Loading..." /></center>
                                                </div>
                                                <div class="displayButtonswiki">
                                                    <button class="btn btn_blue DeleteAttachmentsClswiki"
                                                        type="button"><?php echo __('Delete'); ?></button>
                                                    <button
                                                        class="btn btn_cmn_efect cmn_bg btn-info cmn_size  DownloadAttachmentsClswiki"
                                                        type="button"><?php echo __('Download'); ?></button>
                                                </div>
                                            </td>
                                            <td style="width: 60%;vertical-align: middle;text-align: center;padding: 5px;font-size: 13px;color: #7899c8;font-weight: bold;"
                                                class="displayDisplayFileNamewiki"></td>
                                        </tr>
                                    </table>

                                    <table cellpadding="2" cellspacing="2" width="100%"
                                        class="ClsTableDesignNoResultwiki" style="display:none;">
                                        <tr class="text-center">
                                            <td colspan="2" rowspan="2" class="pad15 displayNoDataErrwiki found_null">
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer popup-footer"></div>
                </div>
            </div>
        </div>

        <!-- Attachment Wiki popup ends -->

        <!-- Wiki activity popup starts -->

        <div class="wiki_activity cmn_popup createnew-activity-pop" style="display: none;">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header popup-header">
                        <button type="button" class="close close-icon" data-dismiss="modal"
                            onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('All Activities'); ?></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv_wikiActivity">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <center>
                            <div id="cat_err_msg" style="color:#FF0000;display:none;"></div>
                        </center>

                        <div id="inner_wiki_activity_details">
                            <div class="activity-content-wiki">
                                <div style="font-size:13px;">
                                    <table border="1px" cellpadding="2" cellspacing="2" width="100%"
                                        class="ActivityFirstRow">
                                        <tr>
                                            <th style="width:60%;vertical-align:top;text-align:center">
                                                <?php echo __('Activity Details'); ?></th>
                                            <th style="width:40%;vertical-align:top;text-align:center">
                                                <?php echo __('Created On'); ?></th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer popup-footer"></div>
                </div>
            </div>
        </div>

        <!-- Wiki activity popup ends -->


        <!-- Wiki details popup starts -->
        <div class="wiki_details_approve cmn_popup" style="display: none;">
        </div>
        <!-- Wiki details popup ends -->


        <!-- Wiki comment popup starts -->
        <div class="wiki_comments cmn_popup createnew-category-pop" style="display: none;">
        </div>
        <!-- Wiki comment popup ends -->

        <!-- New wiki Category popup starts -->
        <div class="new_wiki_category cmn_popup createnew-category-pop" style="display: none;">
        </div>
        <!-- New wiki Category popup ends -->

        <!-- New wiki Sub-category popup starts -->
        <div class="new_wiki_subcategory cmn_popup createnew-subcategory-pop" style="display: none;">
        </div>
        <!-- New wiki Sub-category popup ends -->

        <!-- New wiki approver popup starts -->
        <div class="new_approver_wiki cmn_popup createnew-approver-pop" style="display: none;">
        </div>
        <!-- New approver popup ends -->

        <!-- Due date change Reason popup start here  -->
        <div class="new_duedt_change_rsn cmn_popup" style="display:none;">
            <?php echo $this->element('popup/duedt_change_reason_popup'); ?>
        </div>
        <!-- Due date change Reason popup end here  -->
        <!--  all Wiki popup Ends here   -->
        <div class="cmn_popup" style="display: none;" id="kanbanViewMain">
        </div>
        <!-- New project popup END -->
        <!--work load report start-->
        <div class="cmn_popup" style="display: none;" id="workLoadMain"></div>
        <!--work load report end-->
        <!-- Recurring Task popup starts -->
        <div class="recurring_tsk_popup cmn_popup " style="display: none;">
            <?php echo $this->element('popup/recurring_task'); ?>
        </div>
        <!-- Recurring Task popup END -->

        <!-- Log Time popup starts -->
        <div class="rounded-fild new_log cmn_popup logtime-pop cmn_tbl_widspace width_hover_tbl "
            style="display: none;">
            <?php echo $this->element('popup/log_task'); ?>
        </div>
        <!-- Log Time popup ends -->
        <!-- Add or Edit Milestone popup starts -->
        <div class="rounded-fild mlstn cmn_popup create-taskgroup-pop" style="display: none;">
            <?php echo $this->element('popup/popup_milestone'); ?>
        </div>

        <!-- Add or Edit Milestone popup starts -->
        <div class="rounded-fild description cmn_popup create-taskgroup-pop" style="display: none;">
            <?php echo $this->element('popup/popup_description'); ?>
        </div>

        <!-- Add or Edit Milestone popup ends -->

        <!-- Add or Edit Sprint popup starts -->
        <div class="rounded-fild sprint_add_edit cmn_popup create-taskgroup-pop" style="display: none;">
        </div>
        <!-- Add or Edit Sprint popup ends -->
        <!-- Start Sprint popup starts -->
        <div class="rounded-fild sprint_start_pop cmn_popup create-taskgroup-pop" style="display: none;">
        </div>
        <!-- Start Sprint popup ends -->
        <!-- Complete Sprint popup starts -->
        <div class="rounded-fild sprint_complete_pop cmn_popup create-taskgroup-pop" style="display: none;">
        </div>
        <!-- Complete Sprint popup ends -->

        <!-- Add users to projects starts -->
        <div class="rounded-fild  add_users_to_project rounded-fild cmn_popup assign-user-pop osinvite-user-pop"
            style="display: none;">
            <?php echo $this->element('popup/popup_add_user_to_project'); ?>
        </div>
        <!-- Add users to projects popup ends -->
        <!-- Remove users from Project popup starts -->
        <div class=" rounded-fild rmv_prj_usr cmn_popup remove-project-pop remove-user-pop" style="display: none;">
            <?php echo $this->element('popup/popup_remove_user_from_project'); ?>
        </div>
        <!-- Remove users from Project popup ends -->
        <!-- Edit Project popup starts -->
        <div class="edt_prj cmn_popup createnew-project-pop cmn_auto_scroll_modal" style="display: none;">
            <?php echo $this->element('popup/popup_edit_project'); ?>
        </div>
        <!-- Edit Project popup ends -->
        <!-- Setting Project popup starts -->
        <div class="rounded-fild setting_prj cmn_popup setting-project-pop" style="display: none;">
            <?php echo $this->element('popup/popup_setting_project'); ?>
        </div>
        <!-- Setting Project popup ends -->

        <!-- Add cases to Milestone popup ends -->
        <div class="rounded-fild mlstn_case cmn_popup remove-project-pop case_to_milestone cmn-pop-modal"
            style="display: none;">
            <?php echo $this->element('popup/popup_add_case_to_milestone'); ?>
        </div>
        <!-- Add cases to Milestone popup end -->

        <!-- project template create popup starts -->
        <div class="rounded-fild project_temp_popup cmn_popup proj-template-pop" style="display: none;">
        </div>
        <!-- project template create popup ends -->

        <!-- Mobile APP popup starts -->
        <div class="mobile-app-ppop cmn_popup proj-template-pop" style="display: none;">
        </div>
        <!-- Mobile APP popup ends -->

        <!-- Select tabs popup starts -->
        <div class="select_tab cmn_popup proj-template-pop" style="display: none;">
        </div>
        <!-- Select tabs popup ends -->
        <!-- RS Assign project popup starts -->

        <div class="rs_assign_project cmn_popup move_task_to_project" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_mvprj">Assign Project</h4>
                    </div>
                    <div id="rs_assign_project_content" style="display: block;">
                    </div>
                </div>
            </div>
        </div>
        <!-- RS Assign project end -->
        <!-- Help popup starts -->
        <div class="help_popup cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal"
                            onclick="trackclick('Close Button');closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Need help'); ?>?</h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div class="help-text">
                            <?php echo __("If you're stuck"); ?>, <a class="cmn_link_color"
                                href="<?php echo KNOWLEDGEBASE_URL; ?>" target="_blank"
                                onclick="trackclick('Help Desk');"><?php echo __('click here'); ?></a>
                            <?php echo __('for help'); ?>.
                            <br><?php echo __('Still not clear'); ?>, <a class="support-popup cmn_link_color"
                                href="javascript:void(0);"
                                onclick="trackclick('Send us a line')"><?php echo __('send us a line'); ?></a>
                            <?php echo __('someone from our help desk will be in touch'); ?>.
                        </div>
                        <div class="help-text" style="display: none;">
                            <?php echo __('If you get stuck or need help with anything we are here for you. Just'); ?>
                            <a class=" support-popup" href="javascript:void(0);"
                                onclick="trackclick('Send us a line')"><?php echo __('send us a line'); ?></a>
                            <?php echo __('we will get back to you as soon as possible or find your answer at our'); ?>
                            <a href="<?php echo KNOWLEDGEBASE_URL; ?>" target="_blank"
                                onclick="trackclick('Help Desk');"><?php echo __('help desk'); ?></a>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="fr popup-btn">
                            <span class="fl hover-pop-btn">
                                <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size"
                                    onclick="closePopup();trackclick('Ok ,got it!');"><?php echo __('Ok, got it'); ?>!</button>
                            </span>
                            <div class="cb"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Help popup ends -->
        <!-- Support popup starts -->
        <div class="support_popup cmn_popup support_popup" style="display: none;">
            <?php echo $this->element('popup/popup_support_feedback'); ?>
        </div>
        <!-- Support popup ends -->
        <!-- New user popup starts -->
        <div class="rounded-fild new_user cmn_popup new_user_quickadd" style="display: none;">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal"
                            onclick="closeUserPop();closePopup();"><i class="material-icons">&#xE14C;</i></button>
                        <h4 id="userpopup"><?php echo __('Add New User'); ?></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_user" style="display: none;">
                        <?php echo $this->element('popup/new_user'); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- New user popup ends -->
        <!-- Add project to a user popup starts -->
        <div class="add_usr_prj cmn_popup assign-project-pop add_project_to_user" style="display: none;">
            <?php echo $this->element('popup/popup_add_project_to_user'); ?>
        </div>

        <div class="ass_task_user cmn_popup assign-project-pop assign_tsk_to_usr" style="display: none;">
            <?php echo $this->element('popup/assign_tasks_user'); ?>
        </div>
        <!-- Add project to a user popup starts -->
        <div class="edit_usr_pop cmn_popup edit-user-pops rounded-fild" style="display: none;">
            <?php echo $this->element('popup/edit_user_popup'); ?>
        </div>
        <!-- Add project to a user popup ends -->
        <!-- Add Filter -->
        <div class="add_task_filter_pop cmn_popup add-task-filter-pops" style="display: none;">
            <?php echo $this->element('popup/save_filter'); ?>
        </div>
        <!-- Add project to a user popup ends -->

        <!-- Remove projects of a user popup starts -->
        <div class="rounded-fild rmv_usr_prj cmn_popup assign-project-pop add_project_to_user rem-popup"
            style="display: none;">
            <?php echo $this->element('popup/popup_remove_project_from_user'); ?>
        </div>
        <!-- Remove projects of a user popup ends -->

        <!-- Task template create popup starts -->
        <div class="rounded-fild task_temp_popup cmn_popup task_template_create" style="display: none;">
        </div>
        <!-- Task template create popup ends -->
        <!-- Edit Task Template popup starts -->
        <div class="rounded-fild edt_task_temp cmn_popup task_template_edit" style="display: none;">
        </div>
        <!-- Edit Task Template popup ends -->

        <!-- tasks of project template Edit popup starts -->
        <div class="rounded-fild task_project_edit cmn_popup tasks_of_project_template" style="display: none;">
        </div>
        <!-- tasks of project template Edit popup ends -->

        <!-- project template Edit popup starts -->
        <div class="rounded-fild project_temp_popup_edit cmn_popup project_template_edit" style="display: none;">
        </div>
        <!-- project template Edit popup ends -->

        <!-- Add tasks to Project popup starts -->
        <div class="rounded-fild add_to_project cmn_popup add_tasks_to_project" style="display: none;">
            <?php echo $this->element('popup/popup_add_tasks_to_project'); ?>
        </div>
        <!-- Add tasks to Project popup ends -->
        <!-- smtp popup start  -->
        <div class="smtp_email_popup cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_email_sts'); ?>
        </div>
        <!-- smtp popup end -->
        <!-- Task list export popup starts -->
        <div class="task_list_export cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_task_list_export'); ?>
        </div>
        <!-- Task list export popup ends -->

        <!-- Project list export popup starts -->
        <div class="project_list_export cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_project_list_export'); ?>
        </div>
        <!-- Project list export popup ends -->

        <!-- User list export popup starts -->
        <div class="user_list_export cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_user_list_export'); ?>
        </div>
        <!-- User list export popup ends -->

        <!-- Resource utilization export popup starts -->
        <div class="resource_utilization_list_export cmn_popup" style="display: none;">
        </div>
        <!-- Resource utilization export popup ends -->

        <!-- Timelog export popup starts -->
        <div class="timelog_list_export cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_timelog_export'); ?>
        </div>
        <div class="inactive_caseDetails cmn_popup" style="display: none;" id="inactiveCaseDetails">
            <?php //echo $this->element('popup/popup_timelog_export');
            ?>
        </div>
        <!-- Timelog export popup ends -->
        <!-- Support popup starts -->
        <div class="invoice_setting_popup cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Invoice Settings'); ?></h4>
                    </div>
                    <div id="invoice_wrapper">

                    </div>
                </div>
            </div>
        </div>
        <!-- Support popup ends -->

        <!-- Add New Link -->
        <div class="rounded-fild cmn_popup popup_add_new_link" style="display: none;">
            <?php echo $this->element('popup/popup_add_new_link'); ?>
        </div>
        <!-- End of new link -->
        <!-- Add New subtaskpop -->
        <div class="cmn_popup popup_add_new_subtask" style="display: none;">
            <?php echo $this->element('popup/popup_add_new_subtask'); ?>
        </div>
        <!-- End of new subtaskpop -->
        <!-- Add New reminder -->
        <div class="rounded-fild cmn_popup popup_add_new_reminder" style="display: none;">
            <?php echo $this->element('popup/popup_add_new_reminder'); ?>
        </div>
        <!-- End of new reminder -->

        <!-- Add New meeting -->
        <div class="cmn_popup popup_add_new_meeting" style="display: none;">
        </div>
        <div class="cmn_popup popup_add_existing_meeting" style="display: none;">
        </div>
        <div class="cmn_popup popup_connect_meeting" style="display: none;">
        </div>
        <!-- End of new meeting -->


        <!-- New customer popup starts -->
        <div class="rounded-fild new_customer cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="new_customer_title"><?php echo __('Add New Customer'); ?></h4>
                    </div>
                    <div class="">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="inner_customer_details"><?php echo $this->element('popup/new_customer'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New customer popup ends -->
        <!-- Assign multiple task to user starts -->
        <div class="rounded-fild cmn_popup assign_task_to_user" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_asntskuser"></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_asntskuser" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Assign multiple task to user ends -->
        <!-- Move project popup starts -->
        <div class="mv_project cmn_popup move_task_to_project rounded-fild" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_mvprj"><?php echo __('Move to Project'); ?></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_mvproj" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Move project popup ends -->
        <!-- Copy Checklist popup starts -->
        <div class="copy_check_list cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_copy_checklist"></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_copy_checklist" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Copy Checklist popup ends -->
        <!-- Copy project popup starts -->
        <div class="rounded-fild cp_project cmn_popup copy_task_to_project" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_cpprj"></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_cpproj" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Copy project popup ends -->

        <!-- Hierarchy move/delete popup starts -->
        <div class="hierarchy_move cmn_popup" style="display: none;">
            <?php echo $this->element('popup/hierarchy_move'); ?>
        </div>
        <!-- Hierarchy move/delete popup ends -->


        <!-- Move Task To Milestone popup Start -->
        <div class="movetaskTomlst cmn_popup move_task_to_milestone" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal"
                            onclick="closePopup('mvtask');"><i class="material-icons">&#xE14C;</i></button>
                        <h4>
                            <div class="fl">&nbsp;<?php echo __('Move task to task group/sprint'); ?>&nbsp;</div>
                            <div class="fl">&nbsp;<img
                                    src="<?php echo HTTP_IMAGES; ?>html5/icons/icon_breadcrumbs.png">&nbsp;</div>
                            <div id="mvtask_prj_ttl" class="fnt-nrml fl"></div>
                            <div class="cb"></div>
                        </h4>
                    </div>
                    <div class="">
                        <div class="loader_dv" id="mvtask_loader">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="mvtask_mlst"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Move Task To Milestone popup end -->

        <!-- project type popup starts -->
        <div class="new_projecttype cmn_popup manage_project_status" style="display: none;">
            <?php echo $this->element('popup/popup_new_project_type'); ?>
        </div>
        <div class="edit_projecttype cmn_popup manage_project_status" style="display: none;">
            <?php echo $this->element('popup/popup_edit_project_type'); ?>
        </div>
        <!-- project type popup ends -->

        <!-- project status popup starts -->
        <div class="new_projectstatus cmn_popup manage_project_status" style="display: none;">
            <?php echo $this->element('popup/popup_new_project_status'); ?>
        </div>
        <div class="edit_projectstatus cmn_popup manage_project_status" style="display: none;">
            <?php echo $this->element('popup/popup_edit_project_status'); ?>
        </div>
        <!-- project status popup ends -->

        <!-- Task type popup starts -->
        <div class="rounded-fild new_tasktype cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_new_task_type'); ?>
        </div>
        <div class="edit_tasktype cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_edit_task_type'); ?>
        </div>
        <!-- Task type popup ends -->
        <!-- Label popup starts -->
        <div class="rounded-fild new_label cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_new_label'); ?>
        </div>
        <div class="edit_label cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_edit_label'); ?>
        </div>
        <!-- Label popup ends -->
        <!-- Label popup starts -->
        <div class="rounded-fild new_tasklabel cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_new_tasklabel'); ?>
        </div>
        <!-- Label popup ends -->
        <!-- Due date change reason popup starts -->
        <div class="new_due_change_reason cmn_popup manage_task_type" style="display: none;">
            <?php echo $this->element('popup/popup_due_change_reason'); ?>
        </div>
        <!-- Due date change reason popup ends -->
        <div class="new_custom_field cmn_popup manage_task_type" style="display: none;">
        </div>
        <div class="new_project_custom_field cmn_popup manage_task_type" style="display: none;">
        </div>
        <!-- Export csv popup starts -->
        <div class="exportcsv cmn_popup export_csv_task" style="display: none;" id="exporttaskcsv_popup">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Export Tasks to CSV'); ?></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="exportcsv_content" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Export csv popup ends -->

        <!-- Profile Image popup starts -->
        <div class="prof_img cmn_popup edit_profile_image" style="display: none;">
            <?php echo $this->element('popup/popup_edit_profile_image'); ?>
        </div>
        <!-- Profile Image popup ends -->

        <!-- Create Company popup starts -->
        <div class="create_company cmn_popup create_new_company" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Create Your Own Company'); ?></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="crt_cmp"></div>
                </div>
            </div>
        </div>
        <!-- Create Company popup ends -->
        <!-- New video popup starts -->
        <div class="new_video cmn_popup view_howto_video" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Introduction'); ?></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="inner_video" style="display: none;height:480px"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New video popup ends -->
        <!-- Cancel subscription popup starts -->
        <div class="cancel_subscription_pop cmn_popup new_cancl_sub_pop" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Request Cancellation'); ?> </h4>
                    </div>
                    <div id="cancel_subscription_pop" style="padding: 24px;font-size:16px;">
                        <?php echo __("It's unfortunate to see you go. Neverthless we will honor your decision and help you with your request"); ?>.
                        <br /><br /><?php echo __('Please send us an email from your registered email id to'); ?> <span
                            style="color:#6BA8DE;">support&#64;orangescrum&#46;com</span>
                        <?php echo __('with a subject - CANCEL SUBSCRIPTION'); ?>
                        <?php echo __('and we will take care of it within 24hours'); ?>.
                    </div>
                </div>
            </div>
        </div>
        <!-- Cancel subscription popup ends -->
        <!-- Add unbilled time to invoice popup start -->
        <div id="add_invoice" class="cmn_popup add_unbilled_time_to_invoice" style="display: none;">
        </div>
        <!-- Add unbilled time to invoice popup ends -->

        <!-- save and send email invoice popup start -->
        <div id="EmailTemplate" class="cmn_popup save_and_send_email_invoice" style="display:none;">
        </div>
        <!-- save and send email invoice popup ends -->
        <!-- contact us inner popup start -->
        <div id="ContactUsInner" class="cmn_popup contact_us_inner" style="display:none;">
            <?php echo $this->element('popup/popup_contact_us_inner'); ?>
        </div>
        <!-- save and send email invoice popup ends -->

        <!-- Send transaction mail popup -->
        <div id="transMail" class="cmn_popup transmail_popup" style="display:none;">
        </div>
        <!-- Send transaction mail popup Ends -->

        <!-- Send transaction mail popup -->
        <div id="recurring_info" class="cmn_popup recur_popup" style="display:none;">
            <?php echo $this->element('popup/popup_recurring'); ?>
        </div>
        <!-- Send transaction mail popup Ends -->

        <!-- User Leave popup starts -->
        <div id="inner_leave" class="new_leave rounded-fild cmn_popup" style="display: none;">
        </div>

        <div id="options_div" class="options_div cmn_popup" style="display: none;">
            <?php echo $this->element('popup/popup_user_options'); ?>
        </div>
        <!-- User Leave popup ends -->
        <!-- contact us inner popup start -->
        <div id="createWorkFlow" class="cmn_popup rounded-fild create_workflow" style="display:none;">
            <?php echo $this->element('popup/popup_create_workflow'); ?>
        </div>
        <div id="createbookmark" class="cmn_popup create_bookmark rounded-fild" style="display:none;">
            <?php echo $this->element('popup/popup_create_bookmark'); ?>
        </div>
        <div id="editbookmark" class="cmn_popup create_bookmark edit_bookmark rounded-fild" style="display:none;">
            <?php echo $this->element('popup/Popup_edit_bookmark'); ?>
        </div>

        <!-- save and send email invoice popup ends -->
        <!-- contact us inner popup start -->
        <div id="createWorkFlowStatus" class="cmn_popup create_workflow_status rounded-fild" style="display:none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="label_title_sts"><?php echo __('Create New Status'); ?></h4>
                        <div id="task_status_content"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- save and send email invoice popup ends -->
        <!-- Resource Not Available popup starts -->
        <!-- New customer popup ends -->
        <!-- DEFECT MODUELE (fOR RETEST POPUP)-->
        <!-- REPLY  DEFECT START -->
        <div class="reply_def_project cmn_defect_modal cmn_popup" style="display: none;">
            <div class="modal-dialog wdth_med">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">close</i></button>
                        <h4><span id="header_reply_def_prj" class="fnt-nrml"></span></h4>
                    </div>
                    <div class="popup_form pad15">
                        <?php /*<div class="def_loader_dv"><center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></center></div> */ ?>
                        <div id="reply_def_inner_mvproj" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- REPLY DEFECT END -->
        <!-- Spanish Conversion -->
        <div class="spa_popup_content cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content"
                    style="position: fixed;left: 50%;width: 400px;transform: translate(-50%, 50%);transform: -webkit-translate(-50%, -50%);transform: -moz-translate(-50%, -50%);transform: -ms-translate(-50%, -50%);">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 style="color:#ff8600"><?php echo __('Congratulations!'); ?></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="row form-group">
                            <p style="font-size:14px; line-height: 20px;">
                                <?php echo __('Select your  preferred language'); ?></p>
                            <div class="col-md-9 col-sm-9" style="margin:0px; padding:0px;">
                                <div class="radio radio-primary">
                                    <select name="language" id="notify_lang_pop" class="form-control"
                                        onchange="saveDefaultLanguage();" placeholder="<?php echo __('Language'); ?>">
                                        <option <?php if ($Session->read('Config.language') == 'eng') { ?> selected <?php } ?> value="eng">English</option>
                                        <option <?php if ($Session->read('Config.language') == 'spa') { ?> selected <?php } ?> value="spa">Spanish</option>
                                        <option <?php if ($Session->read('Config.language') == 'por') { ?> selected <?php } ?> value="por">Portuguese</option>
                                        <option <?php if ($Session->read('Config.language') == 'deu') { ?> selected <?php } ?> value="deu">German</option>
                                        <option <?php if ($Session->read('Config.language') == 'fra') { ?> selected <?php } ?> value="fra">French</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-3 loaderLanguage" style="margin:0px; display:none">
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." />
                            </div>
                        </div>
                        <p style="font-size:13px; line-height: 20px; margin-bottom: 20px; font-style: italic;">
                            <strong><?php echo __('Note'); ?>:</Strong>
                            <?php echo __('You can also update the language from the'); ?> <a
                                href='<?php echo HTTP_ROOT; ?>users/profile'><?php echo __('My Profile'); ?></a>
                            <?php echo __('page later'); ?></p>
                        <div class="popup-btn" style="text-align: right;">
                            <span class="cancel-link"><button type="button"
                                    class="btn btn-default btn_hover_link cmn_size reset_btn nothanks_btn"
                                    onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                            <span class="hover-pop-btn"><a href="javascript:void(0);"
                                    class="btn btn_cmn_efect cmn_bg btn-info cmn_size loginactive1"
                                    onclick="closePopup();"><?php echo __('Okay'); ?></a></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- Spanish Conversion popup ends -->
        <!-- Exit timer popup -->
        <div class="exit_timer_popup cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 style="color:#ff8600"><?php echo __('Alert!'); ?></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <p style="font-size:14px; line-height: 20px;">
                            <?php echo __('Timer is running for this task. Do you want to save the current timelog ?'); ?>.
                        </p>

                        <div class="popup-btn" style="text-align: right;">
                            <span class="cancel-link"><button type="button"
                                    class="btn btn_cmn_efect cmn_bg btn-info cmn_size loginactive1"
                                    onclick="saveTimer();closePopup();"><?php echo __('Okay'); ?></button></span>
                            <span class="cancel-link"><button type="button"
                                    class="btn btn-default btn_hover_link cmn_size reset_btn nothanks_btn"
                                    onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                        </div>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
        <!-- Exit timer popup ends -->

        <!-- Checklist close popup -->
        <div class="checklist_close_popup cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 class="checklist_subheading" style="color:#ff8600"><?php echo __('Alert!'); ?></h4>
                        <p class="" style="font-size:14px; line-height: 20px;">
                            <?php echo __('Following checklists are open and will be closed along with task.'); ?></p>
                    </div>
                    <div id="all-checklists"></div>
                </div>
            </div>
        </div>
        <!-- Checklist close popup ends -->
        <!-- User Role Mangement notifications -->
        <div class="userrolemanagementnotification cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content"
                    style="position: fixed;left: 50%;width: 400px;transform: translate(-50%, 50%);transform: -webkit-translate(-50%, -50%);transform: -moz-translate(-50%, -50%);transform: -ms-translate(-50%, -50%);">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 style="color:#ff8600"><?php echo __('Congratulations!'); ?></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="row form-group">
                            <p style="font-size:14px; line-height: 20px;">
                                <?php echo __('We are pleased to announce the release of '); ?><strong><?php echo __('User Role Management') ?></strong>
                            </p>
                            <div class="col-md-9 col-sm-9" style="margin:0px; padding:0px;">
                            </div>
                        </div>
                        <p style="font-size:13px; line-height: 20px; margin-bottom: 20px; font-style: italic;">
                            <strong><?php echo __('Note'); ?>:</Strong>
                            <?php echo __('You can now manage the roles & privileges, click'); ?> <a
                                href='<?php echo HTTP_ROOT; ?>user-role-settings'
                                target="_blank"><?php echo __('here'); ?></a></p>

                        <div class="popup-btn" style="text-align: right;">
                            <span class="cancel-link"><button type="button"
                                    class="btn btn-default btn_hover_link cmn_size reset_btn nothanks_btn"
                                    onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                            <span class="hover-pop-btn"><a href="javascript:void(0);"
                                    class="btn btn_cmn_efect cmn_bg btn-info cmn_size loginactive1"
                                    onclick="closePopup();"><?php echo __('Okay'); ?></a></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- End -->
        <!-- User Role Mangement notifications -->
        <div class="gcProjectSetting cmn_popup" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="popup_close();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><?php echo __('Connect to Google Calendar'); ?></h4>
                    </div>
                    <div class="gcProjectSettingCnt"></div>
                </div>
            </div>
        </div>
        <!-- End -->
        <!-- Resource Work Hour popup starts -->
        <!-- Resource Work Hour popup ends -->
        <!-- One Drive Files popup starts -->
        <div class="azure_od_files cmn_popup" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content modal_popup_sec OneDrive-modal">
                    <div class="modal-header">
                        <div class="icon_sec d-flex">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>Azure/one-drive-icon.png" alt="OneDrive"
                                    title="OneDrive" width="27" height="18" />
                            </center>
                            <P>OneDrive</p>
                        </div>
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." />
                            </center>
                        </div>
                        <div id="inner_onedrive_files_sec" style="padding: 0px 15px"></div>
                    </div>

                </div>
            </div>
        </div>
        <!-- One Drive Files popup ends -->
        <!-- User list of a role popup -->
        <div class="user_role_list cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="user_role_name"><?php echo __('Users'); ?></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." />
                            </center>
                        </div>
                        <div id="inner_user_role_list" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- User list of a role popup ends-->

        <!-- New Module popup starts -->
        <div class="new_module cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="modulettl"><?php echo __('Create New Module'); ?></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." />
                            </center>
                        </div>
                        <div id="inner_module" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New Module popup ends -->

        <!-- New Action popup starts -->
        <div class="new_action cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="actionttl"><?php echo __('Create New Action'); ?></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." />
                            </center>
                        </div>
                        <div id="inner_action" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New Role popup ends -->
        <!-- Assign role to project users popup starts -->
        <div class="assgn_role_prj_usr cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="hdr-cnt"><?php echo __('Assign Role'); ?> <i class="material-icons"
                                    style="vertical-align: middle;">keyboard_arrow_right</i>
                                <span id="header_prj_usr_assgn_role" class="fnt-nrml prj_hd_title"></span></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="inner_prj_usr_assgn_role"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="assgn-role-btn" style="display: none; text-align: right;">
                            <span id="asgnroleloader" style="display: none;">
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..."
                                    title="loading..." />
                            </span>
                            <span id="popupload" class="ldr-ad-btn"><?php echo __('Loading users...'); ?> <img
                                    src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" title="Loading..."
                                    alt="Loading..." /></span>
                            <span id="confirmbtn" style="display:block;">
                                <span class="cancel-link"><button type="button"
                                        class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal"
                                        onclick="closePopup();"><?php echo __('Cancel'); ?>
                                        <div class="ripple-container"></div>
                                    </button></span>

                                <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" id="confirmusercls"
                                    value="Confirm" type="button" onclick="assignrole(this)"><i
                                        class="icon-big-tick"></i><?php echo __('Save'); ?></button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Assign role to project users popup ends -->

        <!-- Assign role to project users popup starts -->
        <div class="assgn_role_usr_prj cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="hdr-cnt"><?php echo __('Assign Role'); ?> <i class="material-icons"
                                    style="vertical-align: middle;">keyboard_arrow_right</i>
                                <span id="header_usr_prj_assgn_role" class="fnt-nrml prj_hd_title"></span></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="inner_usr_prj_assgn_role"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="assgn-role-btn" style="display: none;text-align: right;">
                            <span id="asgnroleusrloader" style="display: none;">
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..."
                                    title="loading..." />
                            </span>
                            <span id="popuploadusr" class="ldr-ad-btn"><?php echo __('Loading users...'); ?> <img
                                    src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" title="Loading..."
                                    alt="Loading..." /></span>
                            <span id="confirmbtnusr" style="display:block;">
                                <span class="cancel-link"><button type="button"
                                        class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal"
                                        onclick="closePopup();"><?php echo __('Cancel'); ?>
                                        <div class="ripple-container"></div>
                                    </button></span>

                                <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size" id="confirmuserclsusr"
                                    value="Confirm" type="button" onclick="assignroleuser(this)"><i
                                        class="icon-big-tick"></i><?php echo __('Save'); ?></button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Assign role to project users popup ends -->

        <!-- Manage Project role Actions popup starts -->
        <div class="manage_role_prj_usr cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" onclick="closePopupMR();"><i
                                class="material-icons" style="vertical-align: middle;">&#xE14C;</i></button>
                        <h4><span id="hdr-cnt"><?php echo __('Project Role'); ?> <i
                                    class="material-icons">keyboard_arrow_right</i>
                                <div id="header_prj_usr_manage_role" class="fnt-nrml fl prj_hd_title"></div>
                            </span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                    title="Loading..." /></center>
                        </div>
                        <div id="inner_prj_usr_manage_role" style="height:500px;overflow:auto"></div>

                        <div class="manage-role-btn" style="display: none;">
                            <span id="manageroleloader" style="display: none;">
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..."
                                    title="loading..." />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Manage Project role Actions popup ends -->
        <!-- Make parent task as subtask popup starts -->
        <div class="mk_tsk_sbtsk cmn_popup make_task_to_subtask rounded-fild" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4 id="header_mk_tsk_sbtsk"></h4>
                    </div>
                    <div class="loader_dv">
                        <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                title="Loading..." /></center>
                    </div>
                    <div id="inner_mk_tsk_sbtsk" style="display: none;"></div>
                </div>
            </div>
        </div>
        <!-- Make parent task as subtask  popup ends -->
        <!-- split task popup starts -->
        <div class="splttaskest cmn_popup split_estimation_task_popup" style="display:none;">
            <?php echo $this->element('popup/split_estimation_tast'); ?>
        </div>
        <!-- split task popup ends -->
        <!-- User list of a role popup -->
        <div class="user_team_list cmn_popup" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                                class="material-icons">&#xE14C;</i></button>
                        <h4><span id="user_team_name"></span></h4>
                    </div>
                    <div class="modal-body popup-container">
                        <div class="loader_dv">
                            <center>
                                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                                     title="Loading..." />
                            </center>
                        </div>
                        <div id="inner_user_team_list" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- User list of a role popup ends-->


        <!-- Add project to a user popup ends -->
         



        <?php if ($this->Format->isWikienabled()) { ?>
        <!-- Add Wiki Plugin popup -->
        <div class="cmn_popup add_wiki_to_project" style="display: none;">
        </div>
        <!-- Add Wiki Plugin popup ends -->
        <!-- Add Task Wiki Plugin popup -->
        <div class="cmn_popup add_wiki_to_task" style="display: none;">
        </div>
        <!-- Add Task Wiki Plugin popup ends -->
        <?php } ?>


        <!-- TestCaseManager popup starts -->
        <?php if ($this->Format->isTestCaseManagerOn()) { ?>
        <?php } ?>
        <!-- TestCaseManager popup ends -->

    </div>
</div>
<!-- END Modal -->

<div class="modal fade slide_right_modal task_details_modal" id="myModalDetail" role="dialog">
    <!-- Task Detail Popup -->
    <div class="task_details_popup pr" style="display: none;">
        <div class="modal-dialog width-80-per">
            <div class="modal-content">
                <div class="modal-body popup-container modal-taskdetal-pop-up">
                    <div class="close_modal"><button type="button" class="close" data-dismiss="modal"
                            onclick="closePopup('dtl_popup');"><i class="material-icons">&#xE14C;</i></button></div>
                    <div id="cnt_task_detail_kb">
                    </div>
                    <div class="loader_bg" id="caseLoaderPopupKB">
                        <div class="loadingdata"><img
                                src="<?php echo HTTP_ROOT; ?>images/rolling.gif?v=<?php echo RELEASE; ?>"
                                alt="loading..." title="loading..." /></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Support popup ends -->
</div>


<div id="myModalVideoHelp" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="help_video_cnt_frame"></div>
            </div>
        </div>

    </div>
</div>

<script type="text/template" id="case_details_popup_tmpl">
    <?php echo $this->element('popup/case_details_popup_new'); ?>
</script>

<div class="modal fade feeback_modal" id="feeback_modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="bnr_header">
                <?php echo __("Cool, we'd love to hear from you"); ?> !
                <p><?php echo __('Contact us 24/7 via email at'); ?> <a
                        href="mailto:support&#64;orangescrum&#46;com">support&#64;orangescrum&#46;com</a><br /><?php echo __('Or By filling the form below'); ?>.
                </p>
            </div>
            <form id="feedback-form" methdo="post">
                <h5><?php echo __('Overall Rating'); ?></h5>
                <div class="star" style="display:inline-block;">
                    <i class="material-icons str_5" onclick="setStarVal(5);">&#xE838;</i>
                    <i class="material-icons str_4" onclick="setStarVal(4);">&#xE838;</i>
                    <i class="material-icons str_3" onclick="setStarVal(3);">&#xE838;</i>
                    <i class="material-icons str_2" onclick="setStarVal(2);">&#xE838;</i>
                    <i class="material-icons str_1" onclick="setStarVal(1);">&#xE838;</i>
                </div>
                <input type="hidden" value="" id="feedback_star" />
                <textarea placeholder="<?php echo __('Enter Your Feedback Here'); ?>" id="feedback_textarea"
                    onkeyup="enableFeedbackBTN();"></textarea>
                <div>
                    <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link"
                            data-dismiss="modal"
                            onclick="closePopup();clearFeedbackData();"><?php echo __('Cancel'); ?></button></span>
                    <input type="button" id="feedback_submit" value="<?php echo __('Submit'); ?>" class="submit_btn"
                        onclick="saveFeedbackData();" />
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Help modal start-->
<!-- End -->
<div class="popup_overlay"></div>
<div class="popup_bg" id="popup_bg_main">

    <!-- Choose Task for project logtime popup starts  -->
    <div class="abc" style="display: none;">
        <div class="popup_title">
            <span><i class="icon-create-logtime"></i><?php echo __('Choose an existing task'); ?></span>
            <a href="javascript:jsVoid();" onclick="closetskPopup();">
                <div class="fr close_popup">X</div>
            </a>
        </div>
        <div class="popup_form">
            <div class="loader_dv">
                <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                        title="Loading..." /></center>
            </div>
            <div id="task_log" style="display:none;"><?php echo $this->element('popup/existing_task'); ?></div>
        </div>
    </div>
    <!--  Choose Task for project logtime popup ends -->




    <!-- Cancel Subscription popup starts -->
    <div class="cancel_sub_popup_content cmn_popup scrollTop" style="display: none;">
        <div class="popup_title">
            <span><i class="icon-create-proj"></i> <?php echo __('Cancel Subscription Information'); ?></span>
            <a href="javascript:jsVoid();" onclick="closePopup();">
                <div class="fr close_popup">X</div>
            </a>
        </div>
        <div class="popup_form">
            <div class="loader_dv">
                <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                        title="Loading..." /></center>
            </div>
            <div id="cancel_sub_popup_content" style="display: none;"></div>
        </div>
    </div>
    <!-- Cancel Subscription popup ends -->

    <!-- Add users from Project popup starts -->
    <div class="add_prj_usr cmn_popup" style="display: none;">
        <div class="popup_title pad-10">
            <div class="hdr-cnt fl">Add User </div>
            <div class="fl"><img src="<?php echo HTTP_IMAGES; ?>html5/icons/icon_breadcrumbs.png"></div>
            <div id="header_prj_usr_add" class="fnt-nrml fl prj_hd_title" style="margin-left: 10px;"></div>

            <a href="javascript:jsVoid();" onclick="closePopup();">
                <div class="fr close_popup">X</div>
            </a>
            <?php if (SES_TYPE != 3) { ?>
                <a id="invite_usr" class="dropdown-toggle upgrade_btn" onclick="newUser(1);" href="javascript:jsVoid();">
                    <button class="btn blue_btn blue_btn_lrg fr mrt-10" type="button"
                        style="margin-top: -3px;padding: 7px 10px 4px 32px;">
                        <i class="icon-invite-usr"></i>
                        <?php echo __('Add New User'); ?>
                    </button>
                </a>
            <?php } ?>
        </div>
        <div class="popup_form">
            <div id="pop_up_add_user_label" class="fl"
                style="overflow: auto; max-height: 90px; width: 576px;color:#999999;padding:3px;display:none;">

                <?php echo __('Please check the below user(s) list and Save to add them to this Project'); ?>.
            </div>
            <div class="loader_dv">
                <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                        title="Loading..." /></center>
            </div>
            <div id="usersrch" class="col-lg-4 mrt-14 fr" style="display:none;">
                <?php echo $this->Form->text('name', ['class' => 'form-control pro_srch', 'id' => 'name', 'maxlength' => '100', 'onkeyup' => "searchListWithInt('searchuser',600)", 'placeholder' => 'Enter User Name', 'escape' => false]); ?>
                <i class="icon-srch-img chng_icn"></i>
            </div>
            <span id="popupload1" class="usr-srh-new"><?php echo __('Loading users'); ?>... <img
                    src="<?php echo HTTP_IMAGES; ?>images/del.gif" title="Loading..." alt="Loading..." /></span>
            <div class="cb"></div>
            <div id="inner_prj_usr_add"></div>

            <div class="add-usr-btn" style="display: none;">
                <span id="userloader" style="display: none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..." title="loading..." />
                </span>
                <span id="popupload" class="ldr-ad-btn"><?php echo __('Loading users'); ?>... <img
                        src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" title="Loading..."
                        alt="Loading..." /></span>
                <span id="confirmbtn" style="display:block;">
                    <button class="btn btn_blue" id="confirmusercls" value="Confirm" type="button"
                        onclick="assignuser(this)"><i class="icon-big-tick"></i><?php echo __('Save'); ?></button>
                    <span class="or_cancel">or
                        <a onclick="closePopup();"><?php echo __('Cancel'); ?></a>
                    </span>
                </span>

                <span id="excptAddContinue" style="display:none;">
                    <button class="btn btn_blue" id="confirmusercls" value="Confirm" type="button"
                        onclick="assignuser(this)"><i class="icon-big-tick"></i><?php echo __('Add'); ?></button>
                    <span class="or_cancel">or
                        <a onclick="closePopup();"><?php echo __('Cancel'); ?></a>
                    </span>
                </span>
            </div>
        </div>
    </div>
    <!-- Add users from Project popup ends -->

    <!-- Remove cases From Milestone popup starts -->
    <div class="mlstn_remove_task cmn_popup" style="display: none;">
        <div class="popup_title pad-10">
            <div class="hdr-cnt">
                <div class="fl">&nbsp;<?php echo __('Remove Tasks'); ?>&nbsp;</div>
                <div class="fl">&nbsp;<img src="<?php echo HTTP_IMAGES; ?>html5/icons/icon_breadcrumbs.png">&nbsp;</div>
                <div id="header_prj_ttl_rt" class="fnt-nrml fl"></div>
                <div class="fl">&nbsp;<img src="<?php echo HTTP_IMAGES; ?>html5/icons/icon_breadcrumbs.png">&nbsp;</div>
                <div id="header_mlstn_ttl_rt" class="fnt-nrml ttc fl"></div>


                <div class="cb"></div>
            </div>
            <a href="javascript:jsVoid();" onclick="closePopup();">
                <div class="fr close_popup" style="margin-top: -20px;">X</div>
            </a>
        </div>
        <div class="popup_form">
            <div class="loader_dv" id="rmv_case_loader">
                <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                        title="Loading..." /></center>
            </div>

            <div id="tsksrch" class="col-lg-4 mrt-14 fr" style="display:none;">
                <?php echo $this->Form->text('name', ['class' => 'form-control pro_srch', 'id' => 'tsk_name_popup', 'maxlength' => '100', 'onkeyup' => 'searchMilestoneCase()', 'placeholder' => __('Title')]); ?>
                <i class="icon-srch-img chng_icn"></i>
            </div>

            <span id="tskpopupload1" class="mlstn-srh-ldr"><?php echo __('Loading tasks'); ?>... <img
                    src="<?php echo HTTP_IMAGES; ?>images/del.gif" title="Loading..." alt="Loading..." /></span>
            <div class="cb"></div>
            <div id="inner_mlstn_removetask"></div>

            <div class="add-mlstn-btn" style="display: none;">
                <center>
                    <span id="tskloader" style="display: none;">
                        <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="loading..."
                            title="loading..." />
                    </span>
                    <span id="confirmbtntsk" style="display:block;">
                        <button class="btn btn_blue" id="addtsk" value="Add" type="button"
                            onclick=" return removecaseFromMilestone(this)"><i
                                class="icon-big-tick"></i><?php echo __('Remove'); ?></button>
                        <span class="or_cancel">or<a onclick="closePopup();"><?php echo __('Cancel'); ?></a></span>
                    </span>
                </center>
            </div>
        </div>
    </div>
    <!-- Add cases to Milestone popup ends -->
</div>

<div class="crttask_overlay"></div>
<div class="create-task-container fl crt_tsk m-cmn-flow" style="display:none;">
    <div class="m-crt_task-width">
        <?php echo $this->element('popup/case_quick'); ?>
    </div>
</div>
