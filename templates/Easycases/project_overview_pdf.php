<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <?php //echo $this->element('metadata'); ?>
    <title>Project Overview - <?php print $proj['Project']['name']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php echo $this->Html->meta('icon'); ?>
    <style>
        @font-face {
            font-family: 'Material Icons';
            font-style: normal;
            font-weight: 400;
            src: url(<?= $this->Url->webroot('fonts/flUhRq6tzZclQEJ-Vdg-IuiaDsNc.woff2') ?>) format('woff2');
        }

        .material-icons {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
    </style>
    <?php
    echo $this->Html->css('bootstrap.min.css');
    echo $this->Html->css('custom.css?v=' . RELEASE);
    echo $this->Html->css(array('project_overview.css?v=' . RELEASE));

    //Moved from Create New project ajax request page
    // echo $this->Html->css('wick_new.css?v=' . RELEASE);

    if (!defined('USE_LOCAL') || (defined('USE_LOCAL') && USE_LOCAL == 0)) {
        $js_arr = array('jquery/jquery-1.10.1.min.js', 'jquery-migrate-3.5.2.min.js');
        echo $this->Html->script($js_arr);
    } else {
        $js_arr = array('jquery.min.js');
        echo $this->Html->script($js_arr);
    }
    ?>
    <style>
        @media all and (-ms-high-contrast:none) {
            .rht_content_cmn {
                padding-left: 170px;
            }
        }

        body {
            font-family: 'Open Sans', sans-serif;
        }

        .os_projct_overview .scroll_body {
            height: auto !important;
            overflow: hidden;
        }

        table,
        tr,
        td,
        th,
        tbody,
        thead,
        tfoot {
            page-break-inside: avoid !important;
        }

        .os_projct_overview .wbox_data {
            height: auto !important;
            overflow: hidden;
        }

        .data_not_avail {
            font-size: 40px;
            top: 50%;
        }

        #time_worked_pie {
            width: 250px;
        }
    </style>
    <script type="text/javascript" src="<?php echo JS_PATH_HTTP; ?>chart.umd.min.js"></script>
    <script type="text/javascript" src="<?php echo JS_PATH_HTTP; ?>charts.js"></script>
    <script>
        Highcharts.setOptions({
            plotOptions: {
                series: {
                    animation: false
                }
            }
        });
    </script>
</head>

<body style="background:#fff;">
<div id="wrapper" <?php echo $styleClass ?? ''; ?>>
    <!-- ###########main Content here ############ -->
    <div class="os_projct_overview blank_view">

        <table style="width:100%;">
            <tr>
                <td style="width:49%; vertical-align: top;">
                    <h5>Project Name: <strong style="color:#666;"><?php print $proj['Project']['name']; ?></strong>
                        <?php if ($proj['Project']['priority'] == 2) { ?>
                            <span style="display: inline-block;margin-top: -5px;" class="prio_low prio_lmh prio_gen_prj prio-drop-icon proj_ov_priority"></span>
                            <span style="font-size:14px; color:#666;">Low</span>
                        <?php } else if ($proj['Project']['priority'] == 1) { ?>
                            <span style="display: inline-block;margin-top: -5px;" class="prio_medium prio_lmh prio_gen_prj prio-drop-icon proj_ov_priority"></span>
                            <span style="font-size:14px; color:#666;">Medium</span>
                        <?php } else { ?>
                            <span style="display: inline-block;margin-top: -5px;" class="prio_high prio_lmh prio_gen_prj prio-drop-icon proj_ov_priority"></span>
                            <span style="font-size:14px; color:#666;">High</span>
                        <?php } ?>

                    </h5>
                    <p>Overall Progress: <strong><?php echo round($project_progress) ?>%</strong></p>
                </td>
                <td style="width:49%; vertical-align: top; text-align: right;">
                    <?php $ps = json_decode($project_status['json_data'], true); ?>
                    <div class="bread_crumb">
                        <div class="overview_wrapper">
                            <ul>
                                <li class="a_task">
                                    <a><?php echo __('All Task'); ?> (<span id="ov_tsk_entry_cnt"><?php echo $ps['total']; ?></span>)</a>
                                </li>
                                <li class="t_entry">
                                    <a>Time Entry (<span id="ov_tim_entry_cnt">0</span>)</a>
                                </li>
                                <li class="activity_icon">
                                    <a>All Activities (<span id="ov_atvt_entry_cnt"><?php echo $total; ?></span>)</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div class="content_databox">
            <table style="width:100%">
                <tr>
                    <td>
                        <div class="project_lunch_date">
                            <div class="box_header">
                                <table style="width:100%">
                                    <tr>
                                        <td style="vertical-align: top; width:30%; text-align: left">
                                            <figure>
                                                <img src="<?php echo HTTP_ROOT_INVOICE; ?>img/flag-fill.png" alt="flag">
                                            </figure>
                                        </td>
                                        <td style="vertical-align: top; width:30%; text-align: left">
                                            <h4>
                                                <span><?php echo __('Project Start Date'); ?></span>
                                                <?php echo $started_date; ?>
                                            </h4>
                                        </td>
                                        <td style="vertical-align: top; width:30%; text-align: left">
                                            <h4>
                                                <span><?php echo __('Project Launch Date'); ?></span>
                                                <?php echo $ended_date; ?>
                                            </h4>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <section>
                <table style="width:100%">
                    <tr>
                        <td style="width: 300px;vertical-align:top;">
                            <div class="wbox_data">
                                <h5><?php echo __('Task Status'); ?></h5>
                                <div id="project_status">
                                    <div id="project_status_pie" style="min-width: 49%; height: auto; margin: auto auto;">
                                        <?php echo $this->element('projectoverview/project_status');  ?>
                                    </div>
                                </div>
                                <ul class="chat_status_result">
                                    <li class="total">
                                        <?php echo __('Total'); ?>
                                        <div>
                                            <div class="line_bar small denim"></div>
                                            <h6 class="status_total"><?php echo $ps['total']; ?></h6>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td style="width:49%;vertical-align:top; ">
                            <div class="wbox_data">
                                <h5><?php echo __('Time Log'); ?></h5>
                                <div id="time_worked" style="min-width: 49%; height: auto; margin: auto auto;">
                                    <?php if (is_array($data)) {
                                        echo $this->element('projectoverview/time_worked');
                                    } else {
                                        echo $data;
                                    } ?>
                                </div>
                                <ul class="chat_status_result chat_billable_result">
                                    <li>
                                        <?php echo __('Billable'); ?>
                                        <div class="v_line ov_t_cls">
                                            <div class="line_bar small darkblue"></div>
                                            <h6 class="ov_time_b ov_cmn_h6">0</h6>
                                        </div>
                                    </li>
                                    <li class="non_billable">
                                        <?php echo __('Non-Billable'); ?>
                                        <div class="v_line ov_t_cls">
                                            <div class="line_bar small darkorange"></div>
                                            <h6 class="ov_time_nb ov_cmn_h6">0</h6>
                                        </div>
                                    </li>
                                    <li class="total ov_t_cls">
                                        <?php echo __('Total'); ?>
                                        <div>
                                            <div class="line_bar small denim"></div>
                                            <h6 class="ov_time_total ov_cmn_h6">0</h6>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </table>
                <table style="width:100%">
                    <tr>
                        <td style="width:100%">
                            <div class="wbox_data" style="padding:0px;">
                                <div class="box_header">
                                    <h5><?php echo __('Team'); ?></h5>
                                </div>

                                <div id="project_users">
                                    <?php echo $this->element('projectoverview/project_users'); ?>
                                </div>

                            </div>
                        </td>
                    </tr>
                </table>
                <div class="clearfix"></div>
            </section>

            <table style="width:100%">
                <tr>
                    <td>
                        <div class="file">
                            <div class="box_header">
                                <h5><?php echo __('Files'); ?></h5>
                            </div>
                            <div class="wbox_data">
                                <div id="files_overview">
                                    <?php echo $this->element('projectoverview/case_files_overview'); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>


            <table style="width:100%">
                <tr>
                    <td style="width: 49%; vertical-align: top;">
                        <div class="task_type">
                            <div class="wbox_data">
                                <h5><?php echo __('Task type'); ?></h5>
                                <div id="task_status_pie" style="min-width: 340px; height: 230px; margin: 0 auto;">
                                    <?php echo $this->element('projectoverview/overview_task_type'); ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="total_task">
                                    <?php echo __('Total task'); ?> <span id="tot_tsx_typ_cnt">0</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 49%; vertical-align: top;">
                        <table style="width:100%">
                            <tr>
                                <td>
                                    <div class="col-md-12">
                                        <div class="col-md-12 pad-rht-0 pad-lft-0">
                                            <div class="project_description">
                                                <div class="wbox_data">
                                                    <h5><?php echo __('Project Description'); ?></h5>
                                                    <p>
                                                        <?php if (!empty($proj['Project']['description'])) {
                                                            echo $proj['Project']['description'];
                                                        } else {
                                                            echo '<span style="color:#888;margin-left: 15px;">N/A</span>';
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table style="width:100%">
                <tr>
                    <td>
                        <div class="overdue_task" style="height:auto; max-height: max-content; overflow: hidden;">
                            <div class="wbox_data">
                                <div class="box_header">
                                    <h5><?php echo __('Overdue Task'); ?></h5>
                                </div>
                                <div id="to_dos" style="height:auto; max-height: max-content; border: none; overflow: hidden;">
                                    <?php echo $this->element('projectoverview/to_dos_overview'); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="width:100%">
                <tr>
                    <td>
                        <div class="activites">
                            <div class="wbox_data">
                                <div class="box_header">
                                    <h5><?php echo __('All Activities'); ?></h5>
                                </div>
                                <div class="scroll_body">
                                    <div id="new_recent_activities">
                                        <?php echo $this->element('projectoverview/recent_activities'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>


            <table style="width:100%">
                <tr>
                    <td>
                        <div class="task_group os_projct_overview">
                            <div class="wbox_data file">
                                <div class="box_header">
                                    <h5><?php echo __('Task Group'); ?></h5>
                                </div>
                                <div id="project_groups">
                                    <?php echo $this->element('projectoverview/project_groups_pdf'); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <table style="width:100%">
                <tr>
                    <td>
                        <div class="overdue_task" style="height:auto; max-height: max-content; overflow: hidden;">
                            <div class="wbox_data">
                                <div class="box_header">
                                    <h5><?php echo __('Project Description'); ?></h5>
                                </div>
                                <div id="new_recent_activities">
                                    <div class="activity-row">
                                        <span style="font-size:14px"><?php echo $proj_desc; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <?php /*<table style="width:100%">
                <tr>
                    <td>
                        <div class="activites">
                            <div class="wbox_data">
                                <div class="box_header">
                                    <h5><?php echo __('Notes'); ?></h5>
                                </div>
                                <div class="scroll_body">
                                    <div id="new_recent_activities">
                                        <?php echo $this->element('projectoverview/notes'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <table style="width:100%">
                <tr>
                    <td>
                        <div class="activites">
                            <div class="wbox_data">
                                <div class="box_header">
                                    <h5><?php echo __('Project Details'); ?></h5>
                                </div>
                                <div class="scroll_body">
                                    <div id="new_recent_activities">
                                        <?php echo $this->element('projectoverview/project_details'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>*/?>
        </div>
    </div>
    <script type="text/javascript">
        var DASHBOARD_ORDER = "";
        var chk_inctv = "<?php echo $proj['Project']['isactive']; ?>";
        $(document).ready(function() {

            $('#list_pie_chart').show();
            /*** Project Status*/

            /** task status section **/
            $('#task_status_ldr_pie').hide();
            $('#loader-task_status_pie').hide();
            var dat = <?php echo $task_type; ?>;
            if (dat.total_cnt) {
                $('#tot_tsx_typ_cnt').text(dat.total_cnt);
            }
            if (dat.status == 'success' && parseInt(dat.total_cnt) > 0) {
                $('#task_status_pie').html('');
                chart = new Highcharts.Chart({
                    credits: {
                        enabled: false
                    },
                    chart: {
                        renderTo: 'task_status_pie',
                        type: 'pie',
                        animation: false
                    },
                    title: {
                        text: ''
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    plotOptions: {
                        pie: {
                            shadow: false
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.point.name + '</b>: ' + this.y;
                        }
                    },
                    series: [{
                        name: 'Browsers',
                        data: dat.data,
                        size: '120%',
                        innerSize: '70%',
                        showInLegend: true,
                        marker: {
                            symbol: "circle",
                            radius: 4
                        },
                        dataLabels: {
                            enabled: false
                        }
                    }],
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: 0,
                        y: 20,
                        borderWidth: 0,
                        labelFormatter: function() {
                            return this.name + ' - ' + this.y + '';
                        }
                    },
                });
            } else {
                $('#task_status_pie').html('<img src="/img/sample/dashboard/task_types_pie.jpg" style="width:98%;">');
            }
            $('.hide_back_overview').hide();
            if (chk_inctv == 2) {
                $('.hide_back_overview').show();
            }
            //$('.overview-bar').html('<?php echo $html; ?>');
            $('#add_user_pop_pname_overview').html('<?php echo html_entity_decode($prjnm); ?>');
            <?php
            if ($proj['Project']['status'] == 1) {
                $sts_cls = 'started-bnr';
                $sts_txt = 'started';
            } else if ($proj['Project']['status'] == 2) {
                $sts_cls = 'holdon-bnr';
                $sts_txt = 'hold_on';
            } else if ($proj['Project']['status'] == 3) {
                $sts_cls = 'stack-bnr';
                $sts_txt = 'Stack';
            }
            if ($proj['Project']['isactive'] == 2 || $proj['Project']['status'] == 4) {
                $sts_cls = 'completed-bnr';
                $sts_txt = 'Completed';
            }
            ?>
            <?php if (SES_TYPE == 1 || SES_TYPE == 2 || (SES_ID == $proj['Project']['user_id'])) { ?>
            $('#add_user_pop_pname_overview').next('span.inline-edit-usr').attr('data-prj-id', '<?php echo $prjunid; ?>');
            $('#add_user_pop_pname_overview').next('span.inline-edit-usr').attr('data-prj-name', '<?php echo ucwords(trim($prjnm)); ?>');
            <?php } else { ?>
            $('#add_user_pop_pname_overview').next('span.inline-edit-usr').remove();
            <?php } ?>
            $('.overview-sts').text('<?php echo $strtdtxt; ?>');
            $('.overview-bnr').addClass('<?php echo $sts_cls; ?>')
            var projid = '<?php echo $prjunid; ?>';
            var orderStr = new Array();
            for (var i in DASHBOARD_ORDER) {
                orderStr.push(DASHBOARD_ORDER[i].name.toLowerCase().replace(' ', '_'));
            }
            var sequency = orderStr;
            for (var i in sequency) {
                if ($("#" + sequency[i]).html() !== '') {
                    $("#" + sequency[i]).html('');
                }
            }
            sequency.reverse();
        });
    </script>
    <!-- ###############End of main Content Here ########## -->
</div>
</body>

</html>
