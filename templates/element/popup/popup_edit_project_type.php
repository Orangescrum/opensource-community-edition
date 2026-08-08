<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 id="project_type_title_edit"><?php echo __('Update Project Type');?></h4>
        </div>
        <div class="modal-body popup-container">
            <!--   <div class="loader_dv"><center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></center></div> -->
            <div class="mtop15" id="inner_projecttype_edit" style="display: block;">
                <center><div id="tterr_msg_p_t_edit" class="err_msg" style="margin-bottom: 20px;"></div></center>
                <form name="task_type" id="customProjectTypeForm_edit" method="post" action="<?php echo HTTP_ROOT . "projects/addNewProjectType"; ?>" autocomplete="off">
                    <div class="field_wrapper">
                    <label class="control-label mark_mandatory duedate-change-label" for="select_role"><?php echo __('Specify project type name');?></label>
                        <input type="text" value="" class="" name="data[ProjectType][title]" id="project_type_nm_edit" placeholder="" maxlength="40" />
                        <div class="field_placeholder"></div>
                        <input type="hidden" class="" name="data[ProjectType][id]" id="new-p_type_id_edit"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
				<span id="ttloader_p_t_edit" style="display:none;">
					<img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" style="vertical-align: initial;"/>
				</span>
                <span id="ttbtn_p_t_edit">
					<span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
					<span class="fl hover-pop-btn"><a href="javascript:void(0)" id="new_p_t_btn_edit" onclick="validateProjectTypeEdit();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Update');?></a></span>
				</span>

                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        $('#project_type_nm_edit').val().trim()!=''?$("#new_p_t_btn_edit").removeClass('loginactive'):$("#new_p_t_btn_edit").addClass('loginactive');
        $('#project_type_nm_edit').on('change, keyup',function(){
            $('#project_type_nm_edit').val().trim()!=''?$("#new_p_t_btn_edit").removeClass('loginactive'):$("#new_p_t_btn_edit").addClass('loginactive');
            $('#tterr_msg_p_t_edit').html('');
        });
        $('#tterr_msg_p_t_edit').html('');
        $(document).on('keypress','#project_type_nm_edit',function(e){
            if (e.which == 13) {
                validateProjectTypeEdit();
                return false;
            }
        });
    });
</script>
