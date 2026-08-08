<style type="text/css">
    .recurring-wrapper {
        padding: 0 15px 0 15px
    }

    .recurrence-pattern,
    .recurrence-range {
        height: 180px;
        border: 1px solid #ccc;
        border-radius: 15px;
        margin-bottom: 10px;
        padding: 10px
    }

    .recurring-wrapper .recurrence-pattern-lbl,
    .recurring-wrapper .recurrence-range-lbl {
        font-size: 15px;
        font-weight: bold;
        display: block;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee
    }

    .recurring-wrapper .recurrence-pattern-body,
    .recurring-wrapper .recurrence-range-body {
        height: 100%;
        width: 100%
    }

    .recurring-wrapper .recurrence-pattern-body .recurrence-pattern-body-left-panel {
        width: 20%;
        height: 85%;
        padding: 5px;
        border-right: 1px solid #eee
    }

    .recurring-wrapper .recurrence-pattern-body .recurrence-pattern-body-main-panel,
    .recurring-wrapper .recurrence-range-body .recurrence-range-body-main-panel {
        width: 80%;
        padding: 10px;
    }

    .recurring-wrapper .recurrence-pattern-body .recurrence-pattern-body-left-panel span,
    .recurring-wrapper .recurrence-pattern-body .recurrence-pattern-body-main-panel span,
    .recurring-wrapper .recurrence-range-body .recurrence-range-body-main-panel span,
    .recurring-wrapper .recurrence-range-body .recurrence-range-body-left-panel span {
        display: block;
        margin-bottom: 10px
    }

    .recurring-wrapper .recurrence-range-body .recurrence-range-body-left-panel {
        width: 40%;
        padding: 5px;
    }

    .recurring-wrapper .recurrence-range-body .recurrence-range-body-main-panel {
        width: 60%;
        padding: 10px;
    }

    .recurring-wrapper span {
        font-size: 14px;
    }

    .recurrence-range .datepicker {
        z-index: 9999;
    }
</style>
<?php
$today = GMT_DATETIME;
$currentDay = date('N');
$currentMonth = date('m');
$currentDayofMonth = date('j');
$currentWeekOfMonth = $this->Format->weekOfMonth(strtotime(date('Y-m-d')));
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="cancel_repeat();"><i class="material-icons">&#xE14C;</i></button>
            <h4><?php echo __('Recurring'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="recurring-wrapper repeat-task-form mtop10">
                <form action="javascript:void(0);" id="recurrence_details_form">
                    <input type="hidden" name="editEasycaseId" id="editEasycaseId" value="" />
                    <input type="hidden" name="editRecurId" id="editRecurId" value="" />
                    <input type="hidden" name="editRecurProjId" id="editRecurProjId" value="" />
                    <div class="recurrence-pattern">
                        <span class="recurrence-pattern-lbl"><?php echo __('Recurrence Pattern'); ?></span>
                        <div class="recurrence-pattern-body">
                            <div class="recurrence-pattern-body-left-panel fl">
                                <span><input type="radio" name="recur_pattern" id="daily_pattern" value="daily" onclick="showRecurrencePatternDetails(this)" /> <?php echo __('Daily'); ?></span>
                                <span><input type="radio" name="recur_pattern" id="weekly_pattern" value="weekly" onclick="showRecurrencePatternDetails(this)" /> <?php echo __('Weekly'); ?></span>
                                <span><input type="radio" name="recur_pattern" id="monthly_pattern" value="monthly" onclick="showRecurrencePatternDetails(this)" /> <?php echo __('Monthly'); ?></span>
                                <span><input type="radio" name="recur_pattern" id="yearly_pattern" value="yearly" onclick="showRecurrencePatternDetails(this)" /> <?php echo __('Yearly'); ?></span>
                            </div>
                            <div class="recurrence-pattern-body-main-panel fl">
                                <div id="daily_details" class="recur-pattern-details" style="display:none">
                                    <span><input type="radio" name="daily_check" id="daily_interval" value="interval" /> <?php echo __('Every'); ?> <input type="text" style="width:100px" value="1" name="daily_interval" class="dailyInterval" /> <?php echo __('day(s)'); ?></span>
                                    <span><input type="radio" name="daily_check" id="weekday_interval" value="weekday" /> <?php echo __('Every weekday'); ?></span>
                                </div>
                                <div id="weekly_details" class="recur-pattern-details" style="display:none">
                                    <span><?php echo __('Recur every'); ?> <input type="text" style="width:100px" value="1" class="weeklyInterval" name="weekly_interval" /> <?php echo __('week(s) on'); ?>:</span>
                                    <span>
                                        <input type="checkbox" name="weekly_days" value="MO" <?php echo $currentDay == 1 ? "checked='true'" : ""; ?>> <?php echo __('Monday'); ?>
                                        <input type="checkbox" name="weekly_days" value="TU" <?php echo $currentDay == 2 ? "checked='true'" : ""; ?>> <?php echo __('Tuesday'); ?>
                                        <input type="checkbox" name="weekly_days" value="WE" <?php echo $currentDay == 3 ? "checked='true'" : ""; ?>> <?php echo __('Wednesday'); ?> <input type="checkbox" name="weekly_days" value="TH" <?php echo $currentDay == 4 ? "checked='true'" : ""; ?>> <?php echo __('Thursday'); ?>
                                        <br /><input type="checkbox" name="weekly_days" value="FR" <?php echo $currentDay == 5 ? "checked='true'" : ""; ?>> <?php echo __('Friday'); ?>
                                        <input type="checkbox" name="weekly_days" value="SA" <?php echo $currentDay == 6 ? "checked='true'" : ""; ?>> <?php echo __('Saturday'); ?>
                                        <input type="checkbox" name="weekly_days" value="SU" <?php echo $currentDay == 7 ? "checked='true'" : ""; ?>> <?php echo __('Sunday'); ?>
                                    </span>
                                </div>
                                <div id="monthly_details" class="recur-pattern-details" style="display:none">
                                    <span><input type="radio" name="monthly_check" id="monthly_interval" value="interval" /> <?php echo __('Day'); ?> <input type="text" style="width:100px" value="<?php echo $currentDayofMonth; ?>" name="monthly_date" class="monthlyDate" /> <?php echo __('of every'); ?> <input type="text" style="width:100px" value="1" name="monthly_interval" class="monthlyInterval" /> <?php echo __('month(s)'); ?></span>
                                    <span>
                                        <input type="radio" name="monthly_check" id="monthly_complicated" value="complecated" /> <?php echo __('The'); ?>
                                        <select id="monthly_mask" style="width:70px" name="monthly_mask">
                                            <option value="1" <?php echo $currentWeekOfMonth == 1 ? "selected='selected'" : ""; ?>><?php echo __('First'); ?></option>
                                            <option value="2" <?php echo $currentWeekOfMonth == 2 ? "selected='selected'" : ""; ?>><?php echo __('Second'); ?></option>
                                            <option value="3" <?php echo $currentWeekOfMonth == 3 ? "selected='selected'" : ""; ?>><?php echo __('Third'); ?></option>
                                            <option value="4" <?php echo $currentWeekOfMonth == 4 ? "selected='selected'" : ""; ?>><?php echo __('Forth'); ?></option>
                                            <option value="5" <?php echo $currentWeekOfMonth == 5 ? "selected='selected'" : ""; ?>><?php echo __('Last'); ?></option>
                                        </select>
                                        <select id="weekday_mask" style="width:100px" name="monthly_day">
                                            <option value="MO" <?php echo $currentDay == 1 ? "selected='selected'" : ""; ?>><?php echo __('Monday'); ?></option>
                                            <option value="TU" <?php echo $currentDay == 2 ? "selected='selected'" : ""; ?>><?php echo __('Tuesday'); ?></option>
                                            <option value="WE" <?php echo $currentDay == 3 ? "selected='selected'" : ""; ?>><?php echo __('Wednesday'); ?></option>
                                            <option value="TH" <?php echo $currentDay == 4 ? "selected='selected'" : ""; ?>><?php echo __('Thursday'); ?></option>
                                            <option value="FR" <?php echo $currentDay == 5 ? "selected='selected'" : ""; ?>><?php echo __('Friday'); ?></option>
                                            <option value="SA" <?php echo $currentDay == 6 ? "selected='selected'" : ""; ?>><?php echo __('Saturday'); ?></option>
                                            <option value="SU" <?php echo $currentDay == 7 ? "selected='selected'" : ""; ?>><?php echo __('Sunday'); ?></option>
                                        </select>
                                        <?php echo __('of every'); ?> <input type="text" style="width:40px" value="1" name="monthly_interval_complete" class="monthlyIntervalComplete" /> <?php echo __('month(s)'); ?>
                                    </span>
                                </div>
                                <div id="yearly_details" class="recur-pattern-details" style="display:none">
                                    <span>Recur every <input type="text" id="yearly_interval" style="width:100px" name="yearly_interval" value="1" /> <?php echo __('year(s)'); ?></span>
                                    <span><input type="radio" name="yearly_check" id="yearly_on" value="on_date" /> <?php echo __('on'); ?>:
                                        <select id="months" style="width:100px" name="yearly_month">
                                            <option value="1" <?php echo $currentMonth == 1 ? "selected='selected'" : ""; ?>><?php echo __('January'); ?></option>
                                            <option value="2" <?php echo $currentMonth == 2 ? "selected='selected'" : ""; ?>><?php echo __('February'); ?></option>
                                            <option value="3" <?php echo $currentMonth == 3 ? "selected='selected'" : ""; ?>><?php echo __('March'); ?></option>
                                            <option value="4" <?php echo $currentMonth == 4 ? "selected='selected'" : ""; ?>><?php echo __('April'); ?></option>
                                            <option value="5" <?php echo $currentMonth == 5 ? "selected='selected'" : ""; ?>><?php echo __('May'); ?></option>
                                            <option value="6" <?php echo $currentMonth == 6 ? "selected='selected'" : ""; ?>><?php echo __('June'); ?></option>
                                            <option value="7" <?php echo $currentMonth == 7 ? "selected='selected'" : ""; ?>><?php echo __('July'); ?></option>
                                            <option value="8" <?php echo $currentMonth == 8 ? "selected='selected'" : ""; ?>><?php echo __('August'); ?></option>
                                            <option value="9" <?php echo $currentMonth == 9 ? "selected='selected'" : ""; ?>><?php echo __('September'); ?></option>
                                            <option value="10" <?php echo $currentMonth == 10 ? "selected='selected'" : ""; ?>><?php echo __('October'); ?></option>
                                            <option value="11" <?php echo $currentMonth == 11 ? "selected='selected'" : ""; ?>><?php echo __('November'); ?></option>
                                            <option value="12" <?php echo $currentMonth == 12 ? "selected='selected'" : ""; ?>><?php echo __('December'); ?></option>
                                        </select> <input type="text" style="width:100px" name="yearly_date" class="yearlyDate" value="<?php echo $currentDayofMonth; ?>" />
                                    </span>
                                    <span>
                                        <input type="radio" name="yearly_check" id="yearly_complicated" value="complecated" /> <?php echo __('on the'); ?>:
                                        <select id="yearly_mask" style="width:70px" name="yearly_mask">
                                            <option value="1" <?php echo $currentWeekOfMonth == 1 ? "selected='selected'" : ""; ?>><?php echo __('First'); ?></option>
                                            <option value="2" <?php echo $currentWeekOfMonth == 2 ? "selected='selected'" : ""; ?>><?php echo __('Second'); ?></option>
                                            <option value="3" <?php echo $currentWeekOfMonth == 3 ? "selected='selected'" : ""; ?>><?php echo __('Third'); ?></option>
                                            <option value="4" <?php echo $currentWeekOfMonth == 4 ? "selected='selected'" : ""; ?>><?php echo __('Forth'); ?></option>
                                            <option value="5" <?php echo $currentWeekOfMonth == 5 ? "selected='selected'" : ""; ?>><?php echo __('Last'); ?></option>
                                        </select>
                                        <select id="weekday_yearly" style="width:100px" name="yearly_day">
                                            <option value="MO" <?php echo $currentDay == 1 ? "selected='selected'" : ""; ?>><?php echo __('Monday'); ?></option>
                                            <option value="TU" <?php echo $currentDay == 2 ? "selected='selected'" : ""; ?>><?php echo __('Tuesday'); ?></option>
                                            <option value="WE" <?php echo $currentDay == 3 ? "selected='selected'" : ""; ?>><?php echo __('Wednesday'); ?></option>
                                            <option value="TH" <?php echo $currentDay == 4 ? "selected='selected'" : ""; ?>><?php echo __('Thursday'); ?></option>
                                            <option value="FR" <?php echo $currentDay == 5 ? "selected='selected'" : ""; ?>><?php echo __('Friday'); ?></option>
                                            <option value="SA" <?php echo $currentDay == 6 ? "selected='selected'" : ""; ?>><?php echo __('Saturday'); ?></option>
                                            <option value="SU" <?php echo $currentDay == 7 ? "selected='selected'" : ""; ?>><?php echo __('Sunday'); ?></option>
                                        </select> of
                                        <select id="monthsComplete" style="width:100px" name="yearly_month_complete">
                                            <option value="1" <?php echo $currentMonth == 1 ? "selected='selected'" : ""; ?>><?php echo __('January'); ?></option>
                                            <option value="2" <?php echo $currentMonth == 2 ? "selected='selected'" : ""; ?>><?php echo __('February'); ?></option>
                                            <option value="3" <?php echo $currentMonth == 3 ? "selected='selected'" : ""; ?>><?php echo __('March'); ?></option>
                                            <option value="4" <?php echo $currentMonth == 4 ? "selected='selected'" : ""; ?>><?php echo __('April'); ?></option>
                                            <option value="5" <?php echo $currentMonth == 5 ? "selected='selected'" : ""; ?>><?php echo __('May'); ?></option>
                                            <option value="6" <?php echo $currentMonth == 6 ? "selected='selected'" : ""; ?>><?php echo __('June'); ?></option>
                                            <option value="7" <?php echo $currentMonth == 7 ? "selected='selected'" : ""; ?>><?php echo __('July'); ?></option>
                                            <option value="8" <?php echo $currentMonth == 8 ? "selected='selected'" : ""; ?>><?php echo __('August'); ?></option>
                                            <option value="9" <?php echo $currentMonth == 9 ? "selected='selected'" : ""; ?>><?php echo __('September'); ?></option>
                                            <option value="10" <?php echo $currentMonth == 10 ? "selected='selected'" : ""; ?>><?php echo __('October'); ?></option>
                                            <option value="11" <?php echo $currentMonth == 11 ? "selected='selected'" : ""; ?>><?php echo __('November'); ?></option>
                                            <option value="12" <?php echo $currentMonth == 12 ? "selected='selected'" : ""; ?>><?php echo __('December'); ?></option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div class="cb"></div>
                        </div>
                    </div>
                    <div class="recurrence-range">
                        <span class="recurrence-range-lbl"><?php echo __('Range of Recurrence'); ?></span>
                        <div class="recurrence-range-body">
                            <div class="recurrence-range-body-left-panel fl">
                                <div class="form-group label-floating title-fld top-tsk-ttl repeat-strtdt">
                                    <label class="control-label" for="start_datePicker"><?php echo __('Starts'); ?></label>
                                    <input type="text" id="recurrence_start_date_formatted" class="form-control ttfont est" disabled readonly="true" name="recur_start_date1" onkeypress="return numeric_decimal_colon(event)" />
                                    <input type="hidden" id="recurrence_start_date" name="recur_start_date" />
                                </div>
                            </div>
                            <div class="recurrence-range-body-main-panel fl">
                                <span><input type="radio" name="recurrence_end_type" id="no_end_date" value="no" /> <?php echo __('No end date'); ?></span>
                                <span><input type="radio" name="recurrence_end_type" id="end_after" value="occurrances" /> <?php echo __('End after'); ?>: <input type="text" style="width:100px" id="end_ocurrences" name="occurrances" value="10" /> <?php echo __('occurrences'); ?></span>
                                <span><input type="radio" name="recurrence_end_type" id="end_by" value="date" /> <?php echo __('End by'); ?>: <input type="text" id="recurrence_end_date_formatted" value="" style="margin-left:15px" /> <input type="hidden" id="recurrence_end_date" name="recur_end_date" /></span>
                            </div>
                            <div class="cb"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <div class="fr popup-btn">
                <span id="loader" style="display:none;"><img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loader" /></span>
                <span id="btn-recurring-task">
                    <span class="fl cancel-link cancel_recurring_task"><button type="submit" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="cancel_repeat();"><?php echo __('Cancel'); ?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" data-dismiss="modal" onclick="validate_recurring();"><?php echo __('Save'); ?></a></span>
                </span>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function showRecurrencePatternDetails(obj) {
        if (typeof obj != 'undefined') {
            if ($(obj).is(":checked")) {
                $('.recur-pattern-details').hide();
                $(obj).prop('checked', true);
                var id = $(obj).val();
                $('#' + id + '_details').show();
                $('#' + id + '_details').find('input[type="radio"]:first').prop('checked', true);
                switch (id) {
                    case 'daily':
                        $('#recurrence_start_date_formatted').val(moment().format('MMM DD, YYYY'));
                        $('#recurrence_start_date').val(moment().format('YYYY-MM-DD'));
                        $('#recurrence_end_date_formatted').val(moment().add(10, 'days').format('MMM DD, YYYY'));
                        $('#recurrence_end_date').val(moment().add(10, 'days').format('YYYY-MM-DD'));
                        break;
                    case 'weekly':
                        var daysToAdd = <?php echo $currentDay; ?>;
                        $('#recurrence_start_date_formatted').val(moment().startOf('week').add(daysToAdd, 'days').format('MMM DD, YYYY'));
                        $('#recurrence_start_date').val(moment().startOf('week').add(daysToAdd, 'days').format('YYYY-MM-DD'));
                        $('#recurrence_end_date_formatted').val(moment().startOf('week').add(daysToAdd, 'days').add(70, 'days').format('MMM DD, YYYY'));
                        $('#recurrence_end_date').val(moment().startOf('week').add(daysToAdd, 'days').add(70, 'days').format('YYYY-MM-DD'));
                        break;
                    case 'monthly':
                        $('#recurrence_start_date_formatted').val(moment().format('MMM DD, YYYY'));
                        $('#recurrence_start_date').val(moment().format('YYYY-MM-DD'));
                        $('#recurrence_end_date_formatted').val(moment().add(10, 'months').format('MMM DD, YYYY'));
                        $('#recurrence_end_date').val(moment().add(10, 'months').format('YYYY-MM-DD'));
                        break;
                    case 'yearly':
                        $('#recurrence_start_date_formatted').val(moment().format('MMM DD, YYYY'));
                        $('#recurrence_start_date').val(moment().format('YYYY-MM-DD'));
                        $('#recurrence_end_date_formatted').val(moment().add(10, 'years').format('MMM DD, YYYY'));
                        $('#recurrence_end_date').val(moment().add(10, 'years').format('YYYY-MM-DD'));
                        break;
                }
            }
        } else {
            $('.recur-pattern-details').hide();
            $('#daily_pattern').prop('checked', true);
            $('#daily_details').show();
            $('#daily_details').find('input[type="radio"]:first').prop('checked', true);
            $('.recurrence-range-body-main-panel').find('input[type="radio"]:first').prop('checked', true);
            $('#recurrence_start_date_formatted').val(moment(new Date(), 'YYYY-MM-DD').format('MMM DD, YYYY'));
            $('#recurrence_start_date').val(moment(new Date(), 'YYYY-MM-DD').format('YYYY-MM-DD'));
            $('#recurrence_end_date_formatted').val(moment(new Date(), 'YYYY-MM-DD').add(10, 'days').format('MMM DD, YYYY'));
            $('#recurrence_end_date').val(moment(new Date(), 'YYYY-MM-DD').add(10, 'days').format('YYYY-MM-DD'));
        }
    }
    $(function() {
        showRecurrencePatternDetails();
        $('#recurrence_details_form :input').on('change', function() {
            var recurrenceDetailsArr = $('#recurrence_details_form').serializeArray();
            var data = serializeDatatoArray(recurrenceDetailsArr);
            var url = HTTP_ROOT + 'projects/testRRule';
            $.post(url, {
                'recurrenceDrtails': data
            }, function(res) {
                res.formatted_end_date != '' ? $('#recurrence_end_date_formatted').val(res.formatted_end_date) : '';
                res.end_date != '' ? $('#recurrence_end_date').val(res.end_date) : '';
            }, 'json');
        });

        $('#btn_add_recurrence').off('click').on('click', function() {
            closePopup();
        });
    });

    function closeRecurrencePopup() {
        $('#recurrence_details_form')[0].reset();
        $('#recurring_invoice').prop('checked', false);
        $('#is_recurring').prop('checked', false);
        closePopup();
    }
</script>