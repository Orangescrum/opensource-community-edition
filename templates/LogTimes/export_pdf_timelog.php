<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?php echo __('Time Log Reports'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0px;
            font-size: 12px;
        }
        
        .task_listing {
            width: 100%;
        }
        
        h1 {
            width: 100%;
            text-align: center;
            font-size: 18px;
            margin: 10px 0;
        }
        
        .tlog_top_cnt {
            width: 100%;
        }
        
        .m-cmn-flow {
            overflow: auto;
            width: 100%;
        }
        
        .summary-section {
            width: 100%;
            padding: 10px 0;
            font-size: 14px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        
        .summary-left {
            width: 74%;
            float: left;
        }
        
        .summary-right {
            width: 25%;
            float: right;
            text-align: right;
        }
        
        .clear-both {
            clear: both;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        th {
            font-weight: bold;
            font-size: 12px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-center {
            text-align: center;
        }
        
        .material-icons {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        
        .tick_mark:before {
            content: "";
            color: green;
            font-weight: bold;
        }
        
        .cross_mark:before {
            content: "";
            color: red;
            font-weight: bold;
        }
        
        @page {
            margin: 20px;
            size: A4 landscape;
        }
    </style>
</head>

<body>
    <div class="task_listing timelog_lview hidetablelog timelog-detail-tbl">
        <h1><?php echo __('Time Log Reports'); ?></h1>
        
        <div class="tlog_top_cnt">
            <div class="m-cmn-flow">
                <div class="summary-section">
                    <div class="summary-left">
                        <span><?php echo __('Total Records'); ?>: <strong><?php echo $caseCount; ?></strong></span>
                        <strong> | </strong>
                        <span><?php echo __('Billable Hrs'); ?>: <strong><?php echo $this->Format->gethh_mm(intval($total_billable_hours)); ?> hrs</strong></span>
                        <strong> | </strong>
                        <span><?php echo __('Non-Billable Hrs'); ?>: <strong><?php echo $this->Format->gethh_mm(intval($total_non_billable_hours)); ?> hrs</strong></span>
                        <strong> | </strong>
                        <span><?php echo __('Total Hrs'); ?>: <strong><?php echo $this->Format->gethh_mm(intval($total_billable_hours + $total_non_billable_hours)); ?> hrs</strong></span>
                    </div>
                    <div class="summary-right">
                        <span><?php echo __('Export Date'); ?>: <strong>
                            <?php 
                            $_curdt = $this->Tmzone->GetDateTime($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, GMT_DATETIME, "datetime");
                            $_fmt = ($SES_TIME_FORMAT == 12) ? 'M d,Y g:i a' : 'M d,Y H:i';
                            echo date($_fmt, strtotime($_curdt));
                            ?>
                        </strong></span>
                    </div>
                    <div class="clear-both"></div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <?php if (in_array('date', $checkedFields)) { ?>
                                <th><?php echo __('Date'); ?></th>
                            <?php } ?>
                            <?php if (in_array('usr_name', $checkedFields)) { ?>
                                <th><?php echo __('Resource Name'); ?></th>
                            <?php } ?>
                            <?php if ($projFil == 'all') { ?>
                                <th><?php echo __('Project Name'); ?></th>
                            <?php } ?>
                            <?php if (in_array('task_no', $checkedFields)) { ?>
                                <th><?php echo __('Task'); ?></th>
                            <?php } ?>
                            <?php if (in_array('task_title', $checkedFields)) { ?>
                                <th><?php echo __('Task Title'); ?></th>
                            <?php } ?>
                            <?php if (in_array('hours', $checkedFields)) { ?>
                                <th><?php echo __('Logged Hours'); ?></th>
                            <?php } ?>
                            <?php if (in_array('description', $checkedFields)) { ?>
                                <th><?php echo __('Note'); ?></th>
                            <?php } ?>
                            <?php if (in_array('start', $checkedFields)) { ?>
                                <th><?php echo __('Start'); ?></th>
                            <?php } ?>
                            <?php if (in_array('end', $checkedFields)) { ?>
                                <th><?php echo __('End'); ?></th>
                            <?php } ?>
                            <?php if (in_array('break', $checkedFields)) { ?>
                                <th><?php echo __('Break'); ?></th>
                            <?php } ?>
                            <?php if (in_array('billable', $checkedFields)) { ?>
                                <th class="text-center"><?php echo __('Billable'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($caseDetail as $k => $v) { ?>
                            <tr>
                                <?php if (in_array('date', $checkedFields)) { ?>
                                    <td><?php echo date('M d,Y', strtotime($v['start_datetime_v1'])); ?></td>
                                <?php } ?>
                                <?php if (in_array('usr_name', $checkedFields)) { ?>
                                    <td><?php echo ($v['user_name'] ?? '') . ' ' . ($v['user_last_name'] ?? ''); ?></td>
                                <?php } ?>
                                <?php if ($projFil == 'all') { ?>
                                    <td><?php echo $v['LogTime']['prj_name'] ?? '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('task_no', $checkedFields)) { ?>
                                    <td><?php echo ($v['task_no']) ? $v['task_no'] : '---' ?></td>
                                <?php } ?>
                                <?php if (in_array('task_title', $checkedFields)) { ?>
                                    <td><?php echo ($v['task_name']) ? $this->Format->smart_wordwrap($v['task_name'], 30) : '---' ?></td>
                                <?php } ?>
                                <?php if (in_array('hours', $checkedFields)) { ?>
                                    <td><?php echo ($v['LogTime']['total_hours']) ? $this->Format->gethh_mm(intval($v['LogTime']['total_hours'])) . ' hrs' : '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('description', $checkedFields)) { ?>
                                    <td><?php echo ($v['LogTime']['description']) ? $this->Format->smart_wordwrap($v['LogTime']['description'], 30) : '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('start', $checkedFields)) { ?>
                                    <td><?php echo ($v['LogTime']['start_time'] && $v['LogTime']['start_time'] != '00:00:00') ? $v['LogTime']['start_time'] : '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('end', $checkedFields)) { ?>
                                    <td><?php echo ($v['LogTime']['end_time'] && $v['LogTime']['end_time'] != '00:00:00') ? $v['LogTime']['end_time'] : '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('break', $checkedFields)) { ?>
                                    <td><?php echo ($v['LogTime']['break_time']) ? $this->Format->gethh_mm(intval($v['LogTime']['break_time'])) . ' hrs' : '---'; ?></td>
                                <?php } ?>
                                <?php if (in_array('billable', $checkedFields)) { ?>
                                    <td class="text-center">
                                        <?php if ($v['LogTime']['is_billable']) { ?>
                                            <span class="material-icons tick_mark">Yes</span>
                                        <?php } else { ?>
                                            <span class="material-icons cross_mark">No</span>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>