<div class="user_profile_con setting_wrapper task_listing import-export-page cmn_tbl_widspace width_hover_tbl">
    <!--Tabs section starts -->
    <?php echo $this->element('import_page_tabs', array('mode' => 'importtimelog')); ?>
    <div class="imp-exp-upu">
        <ul id="breadcrumbs_imp">
            <li <?php if (PAGE_NAME == 'importtimelog') { ?>class="activ" <?php } ?>><?php echo __('Upload File'); ?></li>
            <li <?php if (PAGE_NAME == 'csvTldataimport') { ?>class="activ" <?php } ?>><?php echo __('Preview Data'); ?></li>
            <li <?php if (PAGE_NAME == 'confirmTlimport') { ?>class="activ" <?php } ?>><?php echo __('Upload Summary'); ?></li>
        </ul>
    </div>
    <?php if (PAGE_NAME != 'confirmTlimport') { ?>
        <div class="row exp_innerdiv" id="imploade_file" <?php echo isset($fileds) ? "style='display:none;'" : ''; ?>>
            <div class="col-lg-5 col-sm-5">
                <div class="import-csv-file">
                    <?= $this->Form->create(null, ['url' => ['controller' => 'Projects', 'action' => 'csvTldataimport'], 'type' => 'file', 'id' => 'data_tlimport_form']) ?>
                    <div class="form-group">
                        <?= $this->Form->file('tlimport_csv', ['id' => 'tlimport_csv', 'multiple' => false, 'onchange' => "check_tlcsvfile(); $('#tlcnt_btn').prop('disabled', false);"]) ?>
                        <div class="input-group">
                            <?= $this->Form->text('placeholder', ['id' => 'timelog_impot_placeholder', 'class' => 'form-control', 'placeholder' => __('Upload your CSV file'), 'readonly' => true]) ?>
                            <span class="input-group-btn input-group-sm">
                                <button type="button" class="btn btn-fab btn-fab-mini">
                                    <i class="material-icons import-attach-icon">attach_file</i>
                                </button>
                            </span>
                        </div>
                        <span class="upload_limit"><b>2 MB</b> or <b>1,000</b> <?= __('rows maximum size') ?></span>
                    </div>
                    <span id="err_span" style="color:#900;font-size:12px"></span>
                    <div class="import_btn_div text-right">
                        <img src="<?= HTTP_IMAGES ?>images/case_loader2.gif" alt="<?php echo __('Loading'); ?>..." title="<?php echo __('Loading'); ?>..."
                            id="tloader_img_csv" style="display: none;" />
                        <?= $this->Form->button(__('Continue'), ['id' => 'tlcnt_btn', 'class' => 'btn btn_cmn_efect cmn_bg btn-info cmn_size cmn_disabled_btn', 'disabled' => true]) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
            <div class="col-lg-7 col-sm-7">
                <div class="import-info-dif import_proj">
                    <div class="chk_content">
                        <h4 class="chk_head"><?php echo __('CSV File'); ?></h4>
                        <p style="font-size:11px;">
                            <?php echo __('Please strictly follow the below guideline while importing time log'); ?>. </p>
                        <div class="download_samplefile">
                            <a
                                href="<?php echo HTTP_ROOT; ?>projects/download_sample_tlcsvfile"><?php echo __('Download the sample file'); ?></a>
                            <?php echo __('to see what you can import'); ?>
                        </div>
                        <ul class="chk_desc">
                            <li><b><?php echo __('Project Name'); ?> </b> - <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Task'); ?># - </b> <?php echo __('Task Number'); ?> -
                                <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Assign to'); ?> - </b> <?php echo __('Email ID of Task Assigned To'); ?> -
                                <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Description'); ?> - </b> <?php echo __('Description of the task'); ?></li>
                            <li><b><?php echo __('Date'); ?> - </b> <?php echo __('Date of Time Log (mm/dd/yyyy)'); ?> -
                                <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Hours'); ?> - </b>(<span>Ex. 1:30 or 1.5</span> - 1 hour 30 mins -
                                <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Start Time/End Time'); ?> - </b>hh:mmam/pm <?php echo __('format only'); ?>
                                (<span> Ex. 10:45am/10:45pm</span>) - <span><?php echo __('mandatory'); ?></span></li>
                            <li><b><?php echo __('Break Time'); ?> - </b>hh:mm <?php echo __('format only'); ?> (<span>Ex.
                                    1:30</span> - 1 hour 30 mins -
                                <span><?php echo __('No am/pm is required here'); ?></span>)</li>
                            <li><b><?php echo __('Is Billable'); ?> - </b>(0,1)</li>
                        </ul>

                        <ul class="chk_desc">
                            <li><b><?php echo __('Note'); ?>:</b></li>
                            <ul>
                                <li><?php echo __('Please validate your data twice before importing to Orangescrum'); ?>.
                                </li>
                                <li><?php echo __('Can only import time logs for existing tasks'); ?>.</li>
                                <li><?php echo __('Can not create new tasks and import time log here. For this go to import task  section'); ?>.
                                </li>
                                <li><span><?php echo __('Can provide either Hours or set of (Start Time, End Time & Break Time)'); ?>.</span>
                                </li>
                                <li><span><?php echo __('Either Hours or set of (Start Time, End Time & Break Time) is compulsory'); ?>.</span>
                                </li>
                            </ul>
                        </ul>
                        <h4 class="chk_head"><?php echo __('Help'); ?></h4>
                        <ul class="chk_desc">
                            <li><?php echo __('Upload the valid CSV file with your Time Log'); ?></li>
                            <li><?php echo __('First system will prompt a preview of all the data in the file'); ?></li>
                            <li><?php echo __('In preview you can validate your data'); ?></li>
                            <li><?php echo __('Confirm the data in the preview and Import them to the system'); ?></li>
                            <li><?php echo __('All the Logs will be posted to the selected Project'); ?>.</li>
                            <li><?php echo __('Hours will be treated as the total hours spent on the task.'); ?></li>
                            <li><?php echo __('Start Time, End Time and Break Time will be treated as time log for the individual task. This is converted to Hours in the backend.'); ?>
                            </li>
                            <li><?php echo __('Is Billable is treated as whether the Time Log is billable one or not. The input will be either 0 or 1.'); ?><br />
                                <?php echo __('0 - Not Billable'); ?><br /><?php echo __('1 - Billable'); ?></li>
                            <li><span><?php echo __('Non mandatory fields left blank having no entry'); ?>'.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="cb"></div>
        </div>
        <div id="review_data" <?php if (!isset($fileds)) { ?>style="display: none;" <?php } ?>>
            <?php if (isset($fileds)) { ?>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Projects', 'action' => 'confirm_tlimport', $porj_uid ?? ''], 'method' => 'post']) ?>
                <?= $this->Form->hidden('csv_file_name', ['value' => $csv_file_name]) ?>
                <?= $this->Form->hidden('total_rows', ['value' => $total_rows]) ?>
                <?= $this->Form->hidden('new_file_name', ['value' => $new_file_name]) ?>
                <textarea name="task_arr" style="display:none;"><?= json_encode($task) ?></textarea>
                <div class="imp_data_outer mbtm30 weekly_btm_sumry imp_task">
                    <?php if (isset($task_err) && $task_err) { ?>
                        <div class="data-import-err">
                            <p class="text-success" <?php if (count($task) == 0) { ?>style="color:red" <?php } else { ?>style="color:green" <?php } ?>>
                                <b><?php echo count($task); ?></b> <?php echo __('Time Logs to Import'); ?>
                            </p>
                            <p class="text-muted">
                                <?php echo __('Please double-check the below points before importing your Time Logs'); ?></p>
                            <ul>
                                <li><?php echo __('Blank Project Name'); ?></li>
                                <li><?php echo __('Invalid Date'); ?> (<?php echo __('should be'); ?> <b>MM</b>/<b>DD</b>/<b>YYYY</b>)
                                </li>
                                <li><?php echo __('Unknown Assigned To Email ID (User must be associated with the project)'); ?></li>
                                <li><?php echo __('Invalid start time or end time or break time or spent hour'); ?></li>
                                <li><b><?php echo __('Note'); ?>:</b> <?php echo __('Data showing in'); ?> <span
                                        style="color:red"><?php echo __('red'); ?></span> <?php echo __('color is invalid data'); ?>.
                                    <br /> <?php echo __('Ex. Project name in'); ?> <span
                                        style="color:red"><?php echo __('red'); ?></span>
                                    <?php echo __('means, invalid project'); ?>.<br />
                                    <b><?php echo __('Invalid data is not going to be imported. Please validate your data and upload again'); ?>.</b>
                                </li>
                            </ul>
                            <?= $this->Form->button(__('Confirm & Import'), ['class' => 'fr btn btn-sm btn_cmn_efect cmn_bg btn-info cmn_size', 'style' => 'position:relative;']) ?>
                            <button type="button" class="fr btn btn-default btn_hover_link cmn_size" data-dismiss="modal"
                                onclick="deleteCsvFile('<?php echo $new_file_name; ?>');"
                                style="margin-right:10px;"><?php echo __('Cancel'); ?></button>
                            <div class="cb"></div>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                <?php } ?>
                <div class="cmn_tbl_widspace imprt_task_page_tbl">
                    <table class="table table-striped table-hover tsk_tbl arc_tbl" id="preview_data_tbl">
                        <thead>
                            <tr class="tab_tr">
                                <th><?php echo __('Sl'); ?>#</th>
                                <th><?php echo __('Project'); ?></th>
                                <th><?php echo __('Task'); ?>#</th>
                                <th><?php echo __('Description'); ?></th>
                                <th><?php echo __('Assigned to'); ?></th>
                                <th><?php echo __('Date'); ?></th>
                                <th><?php echo __('Hours'); ?></th>
                                <th><?php echo __('Start Time'); ?> (hh:mm)</th>
                                <th><?php echo __('End Time'); ?> (hh:mm)</th>
                                <th><?php echo __('Break Time'); ?> (hh:mm)</th>
                                <th><?php echo __('Is Billable'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($task) && $task) {
                                $error_arr = $task_err;
                                $i = 0;
                                foreach ($task as $k => $v) {
                                    $i++;
                                    ?>
                                    <tr class="tr_all">
                                        <td><?php echo $i; ?> </td>
                                        <td <?php
                                        if ($error_arr[$k]['project name']) {
                                            $err = 1;
                                            ?>class="error-imp-data" <?php } ?>
                                            valign="top"><?php echo htmlentities($v['project name']); ?></td>
                                        <td <?php
                                        if ($error_arr[$k]['task#']) {
                                            $err = 1;
                                            ?>class="error-imp-data" <?php } ?>
                                            valign="top"><?php echo htmlentities($v['task#']); ?></td>
                                        <td><?php echo $this->Format->formatTitle($v['description']); ?></td>
                                        <td <?php if ($error_arr[$k]['assigned to']) { ?>class="error-imp-data" <?php } ?> valign="top">
                                            <?php echo htmlentities(($v['assigned to'] && strtolower($v['assigned to']) != 'me') ? $v['assigned to'] : 'me'); ?>
                                        </td>
                                        <td valign="top"><?php echo $v['date']; ?></td>
                                        <td valign="top"><?php echo $v['hours']; ?></td>
                                        <td <?php if ($error_arr[$k]['start time']) { ?>class="error-imp-data" <?php } ?> valign="top">
                                            <?php echo $v['start time']; ?></td>
                                        <td <?php if ($error_arr[$k]['end time']) { ?>class="error-imp-data" <?php } ?> valign="top">
                                            <?php echo $v['end time']; ?></td>
                                        <td <?php if ($error_arr[$k]['break time']) { ?>class="error-imp-data" <?php } ?> valign="top">
                                            <?php echo $v['break time']; ?></td>
                                        <td <?php if ($error_arr[$k]['is billabe'] ?? '') { ?>class="error-imp-data" <?php } ?>
                                            valign="top"><?php echo $v['is billable']; ?></td>
                                    </tr>

                                <?php } ?>
                                <?php //}}    ?>
                            <?php } ?>


                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
<?php } else { ?>
    <div id="review_data">
        <h3><?php echo __('Upload Summary'); ?></h3>
        <table class="fyl_table" style="width:100%">
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td colspan="2"><span class="upld-sum-lebel"><?php echo __('Input CSV file'); ?>:&nbsp;</span><b><?php echo $csv_file_name; ?></b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span class="upld-sum-lebel"><?php echo __('Total data'); ?>:&nbsp;</span><b><?php echo ($total_rows - 1); ?></b> <?php echo __('rows'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span
                                    class="upld-sum-lebel"><?php echo __('Valid data'); ?>:&nbsp;</span><b><?php echo $total_valid_rows; ?></b>
                                <?php echo __('rows'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b><?php echo $total_valid_rows; ?></b><span class="upld-sum-lebel">
                                    <?php echo __('Time Log(s) Imported'); ?></span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
<?php } ?>
</div>
</div>
<script type="text/javascript">
    function check_tlcsvfile() {
        var url = '<?php echo $this->Url->build(['controller' => 'ProjectImports', 'action' => 'checktlfileExistance'], ['fullBase' => true]); ?>';
        if ($('#tlimport_csv').val()) {
            var file = $('#tlimport_csv').val();
            var ext = file.split('.').pop();
            if (ext == 'csv' || ext == 'CSV') {
                var data = new FormData();
                jQuery.each($('#tlimport_csv')[0].files, function (i, file) {
                    data.append('file-' + i, file);
                });
                $('#tloader_img_csv').css('display', 'inline-block');
                $.ajax({
                    url: url,
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        $('#tloader_img_csv').hide();
                        $('#err_span').html('');
                        if (data.error) {
                            if (confirm(data.msg)) {
                                $('#tlcnt_btn').prop('disabled', false);
                                $('#tlcnt_btn').removeClass('cmn_disabled_btn');
                            } else {
                                $('#tlimport_csv').val('');
                                $('#timelog_impot_placeholder').val('');
                            }
                        } else {
                            $('#tlcnt_btn').prop('disabled', false);
                            $('#tlcnt_btn').removeClass('cmn_disabled_btn');
                        }
                    }
                });
            } else {
                $('#err_span').html('<?php echo __('Please upload a valid csv file<br/>'); ?>');
                $('#tlimport_csv').val('');
            }
        } else {
            $('#tlcnt_btn').prop('disabled', true);
            $('#tlcnt_btn').addClass('cmn_disabled_btn');
        }
    }
    function deleteCsvFile(file) {
        let strURL = '<?php echo $this->Url->build(['controller' => 'ProjectImports', 'action' => 'deleteTlCsvFile'], ['fullBase' => true]); ?>';
        $.post(strURL, { "file": file }, function (res) {
            if (res) {
                window.location = HTTP_ROOT + 'import-timelog';
            }
        });
    }

</script>