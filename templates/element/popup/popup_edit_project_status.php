<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i
                    class="material-icons">&#xE14C;</i></button>
            <h4 id="project_status_title_edit"><?php echo __('Update Project Status'); ?></h4>
        </div>
        <div class="modal-body popup-container">
            <!--   <div class="loader_dv"><center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></center></div> -->
            <div class="mtop15" id="inner_projectstatus_edit" style="display: block;">
                <center>
                    <div id="tterr_msg_p_s_edit" class="err_msg" style="margin-bottom: 20px;"></div>
                </center>
                <form name="task_type" id="customProjectStatusForm_edit" method="post"
                    action="<?php echo HTTP_ROOT . "project-statuses/addNewProjectStatus"; ?>" autocomplete="off">
                    <div class="field_wrapper">
                        <input type="text" value="" class="" name="data[ProjectStatus][name]"
                            id="project_status_nm_edit" placeholder="" maxlength="40" />
                        <div class="field_placeholder mark_mandatory">
                            <span><?php echo __('Specify project status name'); ?></span></div>
                        <input type="hidden" class="" name="data[ProjectStatus][id]" id="new-p_status_id_edit" />
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span id="ttloader_p_s_edit" style="display:none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader"
                        style="vertical-align: initial;" />
                </span>
                <span id="ttbtn_p_s_edit">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size"
                            data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="new_p_s_btn_edit"
                            onclick="validateProjectStatusEdit();"
                            class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Update'); ?></a></span>
                </span>

                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('#project_status_nm_edit').val().trim() != '' ? $("#new_p_s_btn_edit").removeClass('loginactive') : $("#new_p_s_btn_edit").addClass('loginactive');
        $('#project_status_nm_edit').on('change, keyup', function () {
            $('#project_status_nm_edit').val().trim() != '' ? $("#new_p_s_btn_edit").removeClass('loginactive') : $("#new_p_s_btn_edit").addClass('loginactive');
            $('#tterr_msg_p_s_edit').html('');
        });
        $('#tterr_msg_p_s_edit').html('');
        $(document).on('keypress', '#project_status_nm_edit', function (e) {
            if (e.which == 13) {
                validateProjectStatusEdit();
                return false;
            }
        });
    });
</script>