<style type="text/css">
    .inactiveDetails:hover,
    .inactiveDetails:active,
    .inactiveDetails:link,
    .inactiveDetails:visited {
        text-decoration: none;
        color: black;
    }
</style>
<?php if ($extra == 'overview') { ?>
<?php } else { ?>
    <ul role="tablist" class="nav nav-tabs" id="myTabs">
        <li class="active tab_li anchor" role="presentation">
            <a aria-expanded="true" aria-controls="home" data-toggle="tab" role="tab" id="overdue-tab" onclick="toggleOUTasks('overdue_tasks');"><i class="material-icons">&#xE001;</i><?php echo __('Overdue'); ?></a>
        </li>
        <li class="tab_li anchor" role="presentation">
            <a aria-controls="profile" data-toggle="tab" id="upcoming-tab" role="tab" onclick="toggleOUTasks('upcomming_tasks');"><i class="material-icons">&#xE89C;</i><?php echo __('Upcoming'); ?></a>
        </li>
    </ul>
<?php } ?>
<?php if (isset($gettodos) && !empty($gettodos)) { ?>
    <?php
    $cnt = 0;
    $od_label = $td_label = 0;
    $gmdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATE, "date");
    foreach ($gettodos as $key => $value) {
        $cnt++;
        $due_date = '';

        $actual_dt_created = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $value['actual_dt_created'], "date");

        if ($value['due_date'] != "NULL" && $value['due_date'] != "0000-00-00 00:00:00" && $value['due_date'] != "" && $value['due_date'] != "1970-01-01 00:00:00") {
            $locDT = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $value['due_date'], "date");

            $is_overdue = 0;
            if (strtotime($gmdate) > strtotime($locDT)) {
                $is_overdue = 1;
                $due_date = $this->Datetime->facebook_datestyle($value['due_date']);
            } else {
                $due_date = $this->Datetime->dateFormatOutputdateTime_day($locDT, $gmdate, "date");
            }
        }
        $actual_dt_created = $this->Datetime->dateFormatOutputdateTime_day($actual_dt_created, $gmdate, "date");
    ?>
        <?php if ($value[0]['todos_type'] == 'od' && !$od_label) {
            $od_label = 1;
        ?>
            <table id="overdue_tasks" class="table table-striped table-hover">
            <?php } ?>
            <?php if ($value[0]['todos_type'] == 'td' && !$td_label) {
                $td_label = 1;
            ?>
                <table id="upcomming_tasks" class="table table-striped table-hover" style="display:none;">
                <?php } ?>
                <tr>
                    <td class="td-1st">
                        <p>
                            <?php if ($extra != 'overview') { ?>
                                <a href="<?php echo HTTP_ROOT; ?>dashboard/?project=<?php echo $value['Projects']['uniq_id']; ?>" title="<?php echo ucfirst($value['Projects']['name']); ?>" style="color:#5191BD"><?php echo $this->Format->shortLength(strtoupper($value['Projects']['short_name']), 4); ?></a> -
                            <?php } ?>
                            <?php if ($value['Projects']['isactive'] == 1) { ?>
                                <a href="javascript:void(0);" data-href="<?php echo HTTP_ROOT; ?>dashboard#details/<?php echo $value['uniq_id']; ?>" title="<?php echo $this->Format->formatTitle($this->Format->convert_ascii($value['title'])); ?>" onclick="return switchtaskwithProject(this);" data-pid="<?php echo $value['uniq_id']; ?>">#<?php echo $value['case_no']; ?>: <?php echo $this->Format->formatTitle($this->Format->convert_ascii($value['title'])); ?></a>
                            <?php } elseif ($value['Projects']['isactive'] == 2) { ?>
                                <a class='inactiveDetails' href="javascript:void(0);" data-href="<?php echo HTTP_ROOT; ?>dashboard#details/<?php echo $value['uniq_id']; ?>" onclick="overdue_show_task('<?php echo $value['Projects']['id']; ?>,<?php echo $value['Projects']['uniq_id']; ?>,<?php echo $value['Projects']['isactive']; ?>,<?php echo $value['uniq_id']; ?>')" title="<?php echo $this->Format->formatTitle($this->Format->convert_ascii($value['title'])); ?>" data-pid="<?php echo $value['uniq_id']; ?>">#<?php echo $value['case_no']; ?>: <?php echo $this->Format->formatTitle($this->Format->convert_ascii($value['title'])); ?></a>
                            <?php } ?>
                    </td>
                    <?php
                    $priArr = array('high', 'medium', 'low');
                    ?>
                    <td class="td-2nd"><span class="prio_<?php echo $priArr[$value['priority']]; ?> prio_lmh prio_gen" rel="tooltip" title="<?php echo __('Priority'); ?>:<?php echo $priArr[$value['legend']]; ?>"></span></td>
                    <td class="td-3rd">
                        <div class="progress m-btm0" rel="tooltip" title="<?php echo $value['completed_task']; ?>% <?php echo __('Completed'); ?>">
                            <div class="progress-bar progress-bar-info" style="width: <?php echo $value['completed_task']; ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php if ($gettodos[$key + 1][0]['todos_type'] == 'td' && $gettodos[$key][0]['todos_type'] == 'od') { ?>
                </table>
            <?php } else if ($key == (count($gettodos) - 1)) { ?>
            </table>
        <?php } ?>
    <?php } ?>
    <?php if ($od_label == 0 && $td_label == 0) { ?>
    <?php } else if ($od_label == 0) { ?>
        <div id="overdue_tasks" class="no-task-txt">
            <p><?php echo __('No overdue task'); ?></p>
        </div>
    <?php } else if ($td_label == 0) { ?>
        <div id="upcomming_tasks" class="no-task-txt" style="display:none;">
            <p><?php echo __('No upcoming task'); ?></p>
        </div>
    <?php } ?>
    <div id="to_dos_more" data-value="<?php echo $total; ?>" style="display: none;"></div>
<?php } else { ?>
    <?php if ($extra == 'overview') { ?>
        <?php if ($this->Format->isAllowed('Create Task', $roleAccess)) { ?>
            <div class="mytask" onclick="creatask();" style="float:left;margin:40px 0 0 10px;"></div>
        <?php } ?>
        <div class="mytask_txt" style="float:right;margin:50px 40px 0 0;color:#EAC5C5"><?php echo __("Great Job. You don't have any overdue task"); ?></div>
        <div id="to_dos_more" data-value="0" style="display: none;"></div>
    <?php } else { ?>
        <?php if ($this->Format->isAllowed('Create Task', $roleAccess)) { ?>
            <div class="mytask" onclick="creatask();"></div>
            <div class="mytask_txt"><a onclick="creatask();" href="javascript:void(0);"><?php echo __('Create'); ?></a> <?php if (SES_TYPE < 3) { ?><?php echo __('or'); ?> <a href="'.HTTP_ROOT.'import-export"><?php echo __('Import'); ?></a><?php } ?> <?php echo __('Task'); ?></div>
        <?php } ?>
        <div id="to_dos_more" data-value="0" style="display: none;"></div>
    <?php } ?>
<?php } ?>
<input type="hidden" value="<?php echo $Od_total; ?>" id="only_overdue_count" />
<script type="text/javascript">
    function overdue_show_task(value) {
        var new_val = value.split(',');
        openPopup();
        $(".loader_dv").show();
        $(".inactive_caseDetails").show();
        var Id = new_val[0];
        var proId = new_val[1];
        var inact = new_val[2];
        var caseUniqId = new_val[3];
        if (inact == 2) {
            $.post(HTTP_ROOT + "easycases/inactive_case_details", {
                'id': Id,
                'proId': proId,
                'caseUniqId': caseUniqId
            }, function(data) {
                if (data) {
                    $("#inactiveCaseDetails").html(tmpl("inactive_case_details_tmpl", data));
                    $(".loader_dv").hide();
                }
            }, 'json');
        }
    }
</script>