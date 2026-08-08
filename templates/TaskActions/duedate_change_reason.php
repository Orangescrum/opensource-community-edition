<style>
    .new_custom_fld_btn .btn.cmn_size {
        padding: 6px 10px;
    }
</style>

<div class="task_listing setting_wrapper">
    <div class="row">
        <div class="col-lg-8 text-left">
            <h3 class="noborder">Reasons For Changing Due Date</h3>
        </div>
        <div class="col-lg-4 d-flex justify-end">
            <div class="search_new_cf">
                <div class="d-flex">
                    <div class="searchfld_group">
                        <div class="search_inp">
                            <input type="text" name="" placeholder="Search" class="search_control" value="">
                            <i class="material-icons magnify_icon">search</i>
                        </div>
                    </div>
                    <div class="new_custom_fld_btn ml-15">
                        <button class="btn btn_cmn_efect cmn_bg btn-info cmn_size cu_add" onclick="addNewReason();" style=" "><i class="material-icons add_plus">add</i> <?php echo __('Reasons'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="text-center" style="margin:auto;" id="due_date_change_rsn_dv">
                <div class="loading-custom-dv"><img src="<?php echo HTTP_ROOT; ?>images/rolling.gif?v=5019" alt="loading..." title="loading..."></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        ajaXLoadDuedateChangeReason();
    })

    function addNewReason() {
        openPopup();
        $('.new_duedt_change_rsn').show();
        $('#new_duedt_change_title').text(_('Add Reason'));
        $('#due_dt_change_rsn').focus();
        $('#duedt_cnge_rsn_id').val('');
        $('#due_dt_change_rsn').val('');
        $('#duedterr_msg').hide();
    }
</script>