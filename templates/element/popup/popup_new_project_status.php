<style type="text/css">
    .modal-footer .custom-checkbox {
        float: none;
        width: auto
    }
</style>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                    class="material-icons">&#xE14C;</i></button>
            <h4 id="project_status_title"><?php echo __('New Project Status'); ?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="loader_dv">
                <center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..."
                        title="Loading..." /></center>
            </div>
            <div class="mtop15" id="inner_projectstatus" style="display: none;">
                <center>
                    <div id="tterr_msg_p_s" class="err_msg" style="margin-bottom: 20px;"></div>
                </center>
                <form name="task_type" id="customProjectStatusForm" method="post"
                    action="<?php echo HTTP_ROOT . "project-statuses/addNewProjectStatus"; ?>" autocomplete="off">
                    <div class="field_wrapper">
                    <label class="control-label mark_mandatory duedate-change-label" for="select_role"><?php echo __('Specify project status name'); ?></label>
                        <input type="text" value="" class="" name="data[ProjectStatus][name]" id="project_status_nm"
                            placeholder="" maxlength="40" />
                        <div class="field_placeholder">
                          </div>
                        <input type="hidden" class="" name="data[ProjectStatus][id]" id="projectstatusid" />
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span class="checkbox custom-checkbox">
                    <label style="line-height:25px;">
                        <input type="checkbox" class="add_new_type" value="1" name="another_type_p_s"
                            id="another_type_p_s" /> <?php echo __("Create another"); ?>
                    </label>
                </span>
                <span id="ttbtn_p_s">
                    <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size"
                            data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                    <span class="hover-pop-btn"><a href="javascript:void(0)" id="new_p_s_btn"
                            onclick="validateProjectStatus();"
                            class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Add'); ?></a></span>
                </span>
                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('#project_status_nm').on('change, keyup', function () {
            $('#tterr_msg_p_s').html('');
        });
        $('#tterr_msg_p_s').html('');
        $(document).on('keypress', '#project_status_nm', function (e) {
            if (e.which == 13) {
                validateProjectStatus();
                return false;
            }
        });
    });
</script>