<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 id="label_title"><?php echo __('Edit Label');?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="loader_dv"><center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></center></div>
            <div class="mtop15" id="inner_label_edit" style="display: none;">
                <center><div id="lterr_msg_edit" class="err_msg"></div></center>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Labels', 'action' => 'addNewLabel'], 'id' => 'customLabelForm_edit', 'autocomplete' => 'off']) ?>
                    <div class="field_wrapper">
                        <input type="text" value="" class="" name="data[Label][lbl_title]" id="label_nm_edit" placeholder="" maxlength="20" />
                        <div class="field_placeholder mark_mandatory"><span><?php echo __('Specify Label Name');?></span></div>
                        <input type="hidden" class="f" name="data[Label][id]" id="new-labelid_edit"/>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span id="lloader_edit" style="display:none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" style="vertical-align: inherit"/>
                </span>
                <span id="llbtn_edit">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="newlabel_btn_edit" onclick="validateLabel_edit();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Update');?></a></span>
                </span>

                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(function() {
        $("#newlabel_btn_edit").removeClass('loginactive');
        $('#lterr_msg_edit').html('');
        $(document).on('keyup','#label_nm_edit',function(){
            if($(this).closest('.verror').length){
                $('#lterr_msg_edit').html('');
                $("#label_nm_edit").closest('.field_wrapper').removeClass('verror');
            }
        });
        $(document).on('keypress','#label_nm_edit',function(e){
            if (e.which == 13) {
                validateLabel_edit();
            }
        });
    });
    function validateLabel_edit() {
        var msg = "";
        var nm = $.trim($("#label_nm_edit").val());
        var id = $.trim($("#new-labelid_edit").val());
        $("#lterr_msg_edit").html("");
        if (nm.length == 0) {
            msg = _("'Label Name' cannot be left blank!");
            $("#lterr_msg_edit").show().html(msg);
            $("#label_nm_edit").focus();
            $("#label_nm_edit").closest('.field_wrapper').addClass('verror');
            return false;
        }else{
            $.post(HTTP_ROOT + "labels/validateLabel", {
                'id': id,
                'name': nm,
                'project_id':$("#project_label_edit").val()
            }, function(data) {
                //$("#llbtn_edit").hide();
                $("#lloader_edit").show();
                $("#lterr_msg_edit").html('');
                $("#newlabel_btn_edit").addClass('loginactive');
                $("#llbtn_edit .cancel-link").find('button').prop('disabled',true);
                if (data.status == 'success') {
                    $('#customLabelForm_edit').submit();
                }else{
                    $("#lloader_edit").hide();
                    //$("#llbtn_edit").show();
                    $("#newlabel_btn_edit").removeClass('loginactive');
                    $("#llbtn_edit .cancel-link").find('button').prop('disabled',false);
                    $("#lterr_msg_edit").html('');
                    if (data.msg == 'name') {
                        $("#lterr_msg_edit").show().html(_('Name already exists. Please enter another name.'));
                    }  else {
                        $("#lterr_msg_edit").show().html(_('Oops! Missing input parameters.'));
                    }
                    $("#label_nm_edit").closest('.field_wrapper').addClass('verror');
                    return false;
                }
            }, 'json');
        }
    }

</script>
