<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 id="label_title"><?php echo __('New Label');?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="loader_dv"><center><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." /></center></div>
            <div class="mtop15" id="inner_label" style="display: none;">
                <center><div id="lterr_msg" class="err_msg"></div></center>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Labels', 'action' => 'addNewLabel'], 'id' => 'customLabelForm', 'autocomplete' => 'off']) ?>
                    <div class="select_field_wrapper mbtm15 mark_mandatory">
                        <select name="data[Label][project_id][]" multiple="multiple" class="label_project form-control floating-label" id="project_label"  placeholder="<?php echo __('Projects');?>">
                            <?php if(is_array($GLOBALS['getallprojForAdmin']) && count($GLOBALS['getallprojForAdmin']) > 1){ ?>
                                <option value="0"><?php echo __("All Project");?></option>
                            <?php } ?>
                            <?php if($GLOBALS['getallprojForAdmin']) foreach($GLOBALS['getallprojForAdmin'] as $k=>$v){ ?>
                                <option value="<?php echo $v['Project']['id'];?>"><?php echo $v['Project']['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="field_wrapper">
                    <label class="control-label mark_mandatory task-type-label" for="select_role"><?php echo __('Specify Label Name');?></label>
                        <input type="text" value="" class="" name="data[Label][lbl_title]" id="label_nm" placeholder="" maxlength="20" />
												
                        <input type="hidden" class="" name="data[Label][id]" id="new-labelid"/>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span id="lloader" style="display:none;">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" style="vertical-align: inherit"/>
                </span>
                <span id="llbtn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="newlabel_btn" onclick="validateLabel();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Add');?></a></span>
                </span>

                <div class="cb"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        $("#newlabel_btn").removeClass('loginactive');
        $('#lterr_msg').html('');
        $(document).on('keyup','#label_nm',function(){
            if($(this).closest('.verror').length){
                $('#lterr_msg').html('');
                $("#label_nm").closest('.field_wrapper').removeClass('verror');
            }
        });
        $(document).on('keypress','#label_nm',function(e){
            if (e.which == 13) {
                validateLabel();
            }
        });
    });
    function validateLabel() {
        var msg = "";
        var nm = $.trim($("#label_nm").val());
        var id = $.trim($("#new-labelid").val());
        $("#lterr_msg").html("");
        if($.trim($("#project_label").val()) ==''){
            msg = _("'Project' cannot be left blank!");
            $("#lterr_msg").show().html(msg);
            $("#project_label").focus();
            $("#project_label").closest('.select_field_wrapper').addClass('verror');
            return false;
        }
        if (nm.length == 0) {
            msg = _("'Label Name' cannot be left blank!");
            $("#lterr_msg").show().html(msg);
            $("#label_nm").focus();
            $("#label_nm").closest('.field_wrapper').addClass('verror');
            return false;
        }else{
            $.post(HTTP_ROOT + "labels/validateLabel", {
                'id': id,
                'name': nm,
                'project_id':$("#project_label").val()
            }, function(data) {
                //$("#llbtn").hide();
                $("#lloader").show();
                $("#lterr_msg").html('');
                $("#newlabel_btn").addClass('loginactive');
                $("#llbtn .cancel-link").find('button').prop('disabled',true);
                if (data.status == 'success') {
                    $('#customLabelForm').submit();
                }else{
                    $("#lloader").hide();
                    // $("#llbtn").show();
                    $("#newlabel_btn").removeClass('loginactive');
                    $("#llbtn .cancel-link").find('button').prop('disabled',false);
                    $("#lterr_msg").html('');
                    if (data.msg == 'name') {
                        $("#lterr_msg").show().html(_('Name already exists. Please enter another name.'));
                    }  else {
                        $("#lterr_msg").show().html(_('Oops! Missing input parameters.'));
                    }
                    $("#label_nm").closest('.field_wrapper').addClass('verror');
                    return false;
                }
            }, 'json');
        }
    }

</script>
