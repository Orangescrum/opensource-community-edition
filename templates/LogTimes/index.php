<?php
/**
 * Time Log — list of entries.
 *
 * The columns match the CSV and PDF exports, which read the same query, so a
 * reader can check on screen what they are about to download.
 */
$totalHours = intval($total_billable_hours) + intval($total_non_billable_hours);
$showProject = ($projFil ?? '') === 'all';
?>

<div class="rht_content_cmn tlog_list_page">
    <div class="wrapper">
        <div class="wrapper-body">
            <div class="slide_rht_con">

                <div class="tlog-head">
                    <h2><?php echo __('Time Log'); ?></h2>
                    <ul class="tlog-summary">
                        <li>
                            <span class="tlog-summary__label"><?php echo __('Entries'); ?></span>
                            <strong><?php echo intval($caseCount); ?></strong>
                        </li>
                        <li>
                            <span class="tlog-summary__label"><?php echo __('Billable'); ?></span>
                            <strong><?php echo $this->Format->gethh_mm(intval($total_billable_hours)); ?> <?php echo __('hrs'); ?></strong>
                        </li>
                        <li>
                            <span class="tlog-summary__label"><?php echo __('Non-billable'); ?></span>
                            <strong><?php echo $this->Format->gethh_mm(intval($total_non_billable_hours)); ?> <?php echo __('hrs'); ?></strong>
                        </li>
                        <li class="is-total">
                            <span class="tlog-summary__label"><?php echo __('Total'); ?></span>
                            <strong><?php echo $this->Format->gethh_mm($totalHours); ?> <?php echo __('hrs'); ?></strong>
                        </li>
                    </ul>
                </div>

                <?php if (empty($caseDetail)) { ?>
                    <p class="tlog-empty"><?php echo __('No Time Logs found'); ?></p>
                <?php } else { ?>
                    <div class="tlog-tablewrap">
                        <table class="tlog-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('Date'); ?></th>
                                    <th><?php echo __('Resource Name'); ?></th>
                                    <?php if ($showProject) { ?>
                                        <th><?php echo __('Project Name'); ?></th>
                                    <?php } ?>
                                    <th><?php echo __('Task'); ?></th>
                                    <th><?php echo __('Task Title'); ?></th>
                                    <th><?php echo __('Logged Hours'); ?></th>
                                    <th><?php echo __('Note'); ?></th>
                                    <th><?php echo __('Start'); ?></th>
                                    <th><?php echo __('End'); ?></th>
                                    <th><?php echo __('Break'); ?></th>
                                    <th class="text-center"><?php echo __('Billable'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($caseDetail as $v) { ?>
                                    <tr>
                                        <td><?php echo !empty($v['start_datetime_v1']) ? h(date('M d,Y', strtotime($v['start_datetime_v1']))) : '---'; ?></td>
                                        <td><?php echo h(trim(($v['user_name'] ?? '') . ' ' . ($v['user_last_name'] ?? ''))) ?: '---'; ?></td>
                                        <?php if ($showProject) { ?>
                                            <td><?php echo h($v['LogTime']['prj_name'] ?? '') ?: '---'; ?></td>
                                        <?php } ?>
                                        <td><?php echo !empty($v['task_no']) ? h($v['task_no']) : '---'; ?></td>
                                        <td><?php echo !empty($v['task_name']) ? h($v['task_name']) : '---'; ?></td>
                                        <td><?php echo !empty($v['LogTime']['total_hours']) ? $this->Format->gethh_mm(intval($v['LogTime']['total_hours'])) . ' ' . __('hrs') : '---'; ?></td>
                                        <td><?php echo !empty($v['LogTime']['description']) ? h($v['LogTime']['description']) : '---'; ?></td>
                                        <td><?php echo (!empty($v['LogTime']['start_time']) && $v['LogTime']['start_time'] != '00:00:00') ? h($v['LogTime']['start_time']) : '---'; ?></td>
                                        <td><?php echo (!empty($v['LogTime']['end_time']) && $v['LogTime']['end_time'] != '00:00:00') ? h($v['LogTime']['end_time']) : '---'; ?></td>
                                        <td><?php echo !empty($v['LogTime']['break_time']) ? $this->Format->gethh_mm(intval($v['LogTime']['break_time'])) . ' ' . __('hrs') : '---'; ?></td>
                                        <td class="text-center">
                                            <?php echo !empty($v['LogTime']['is_billable']) ? __('Yes') : __('No'); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<style>
    .tlog_list_page .slide_rht_con {
        background: #fff;
        padding: 20px;
    }

    .tlog-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 24px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e6e6e6;
    }

    .tlog-head h2 {
        margin: 0;
        font-size: 20px;
    }

    .tlog-summary {
        display: flex;
        gap: 22px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tlog-summary li {
        display: flex;
        flex-direction: column;
        line-height: 1.35;
    }

    .tlog-summary__label {
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #777;
    }

    .tlog-summary .is-total strong {
        color: #222;
    }

    /* The table is wide. It scrolls inside its own box so the page itself
       never scrolls sideways. */
    .tlog-tablewrap {
        overflow-x: auto;
        margin-top: 16px;
    }

    .tlog-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .tlog-table th,
    .tlog-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
    }

    .tlog-table th {
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #777;
        background: #fafafa;
        border-bottom: 1px solid #e6e6e6;
    }

    /* Only the two free-text columns wrap; the rest stay on one line so the
       numbers line up down the page. */
    .tlog-table td:nth-child(5),
    .tlog-table td:nth-child(7) {
        white-space: normal;
        min-width: 200px;
    }

    .tlog-table tbody tr:hover {
        background: #fafafa;
    }

    .tlog-table .text-center {
        text-align: center;
    }

    .tlog-empty {
        padding: 48px 0;
        text-align: center;
        color: #777;
    }
</style>
