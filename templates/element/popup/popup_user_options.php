<div class="modal-dialog userleave-modal">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4><?php echo __("Choose Options"); ?></h4>

        </div>

        <div class="radio radio-primary">
            <label>
                <input class="" type="radio" data-id = "thismonth" id="archive_thismonth" onclick="creatask();"/> <?php echo __('Create Task');?>
            </label>
        </div>

        <div class="radio radio-primary">
            <label>
                <input class="" type="radio" data-id = "thismonth" id="archive_thismonth" onclick="applyForVacation(10, 'check');"/> <?php echo __('Submit Leave');?>
                <input name="leave_user" type="hidden"  id="leave_user_id" />
                <input name="leave_date" type="hidden"  id="leave_date" />

            </label>
        </div>
    </div>
</div>
