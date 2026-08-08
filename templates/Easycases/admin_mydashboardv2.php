<?php echo $this->Html->script(['jquery.blockUI'], ['block' => 'blockUI']); ?>
<style>
    .dashboard_new .mt_20 {
        margin-top: 20px
    }
.dashboard_new .cmn_tabular_data{overflow: auto;}
    .dashboard_new .mt_30 {
        margin-top: 30px
    }

    .dashboard_new .mt_60 {
        margin-top: 60px
    }

    .dashboard_new .cmn_row {
        margin-left: -15px;
        margin-right: -15px
    }

    .dashboard_new .plr_15 {
        padding: 0 15px
    }

    .dashboard_new .scroll_items {
        width: 100%;
        max-height: 300px;
        overflow-y: auto
    }

    .dashboard_new .scroll_items.workload_summary {
        min-height: 200px;
        max-height: 400px;
        overflow-x: hidden
    }

    .dashboard_new .item_card {
    width: 100%;
    height: 100%;
    min-height: 100%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .12), 0 1px 2px rgba(0, 0, 0, .24);
    border: 1px solid #F1F1F5;
    padding: 20px;
    text-align: left;
    position: relative;
    border-radius: 5px;
    }

    .dashboard_new .item_card:hover {
        box-shadow: 0px 0px 20px #71717c45
    }

    .dashboard_new .item_card .item_title {
        font-size: 14px;
        line-height: 30px;
        color: #292940;
        font-weight: 600;
        margin-right: auto;
    }

    .dashboard_new .item_card ul {
        margin: 0;
        padding: 0;
        list-style-type: none
    }

    .dashboard_new .counter_items .item_name {
        padding-right: 65px
    }

    .dashboard_new .counter_items figure {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        position: absolute;
        right: 20px;
        top: -60px;
        bottom: 0;
        margin: auto
    }

    .dashboard_new img {
        max-inline-size: 100%;
        block-size: auto;
    }

    .dashboard_new .counter_items h5 {
        font-size: 13px;
        line-height: 20px;
        color: #757575;
        font-weight: 500;
        margin: 0;
    }

    .dashboard_new .counter_items .count_no {
        font-size: 18px;
        line-height: 20px;
        color: #71718E;
        margin: 10px 0;
    }

    .dashboard_new .counter_items .count_no strong {
    font-size: 25px;
    line-height: 35px;
    color: #444;
    display: inline-block;
    vertical-align: baseline;
    font-weight: 600;
    }

    .dashboard_new .counter_items .growth_percentage {
        font-size: 12px;
        line-height: 18px;
        color: #292940;
    }

    .dashboard_new .growth_percentage span {
        display: inline-block;
        vertical-align: middle;
        position: relative;
        font-size: 14px;
        font-weight: 500;
    }

    /* .dashboard_new .counter_items .growth_percentage span::before {
        content: '';
        background: url(img/tools/dashboard-icons.png) no-repeat 0px 0px;
        width: 14px;
        height: 14px;
        margin-right: 3px;
        display: inline-block;
        vertical-align: middle
    } */

    .dashboard_new .counter_items .growth_percentage .increase::before {
        background-position: 0px -330px
    }

    .dashboard_new .counter_items .growth_percentage .decrease::before {
        background-position: 0px -355px
    }

    /* Hide .growth_percentage untill fixed*/
    .dashboard_new .counter_items .growth_percentage, .dashboard_new .counter_items .growth_percentage {
        visibility: hidden;
    }

    .dashboard_new .counter_items .increase {
        color: #2AD36C
    }

    .dashboard_new .counter_items .decrease {
        color: #E84C85
    }

    .dashboard_new .select_option .select_field_wrapper {
        width: 180px;
        position: relative;
        cursor: pointer;
        margin-left: 15px
    }

    .dashboard_new .select_option .select_field_wrapper:first-child {
        margin-left: 16px;
    }

    .dashboard_new .select_field_wrapper .select2::after {
        content: '';
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #666;
        position: absolute;
        right: 15px;
        top: 18px;
        z-index: 1;
    }

    .dashboard_new .select_option .select2-search__field {
        padding: 0 30px 0 10px;
    }

    .dashboard_new .progress_chat {
        width: 100%;
        height: 200px;
    }

    .dashboard_new .progress_chat .total_figure {
        width: 100px;
        height: 100px;
        font-size: 32px;
        line-height: 40px;
        color: #71718E;
        font-weight: 600;
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        padding: 10px;
        z-index: 1;
    }

    .dashboard_new .progress_chat .total_figure strong {
        display: block;
        margin-top: 3px;
        font-size: 16px;
        line-height: 26px;
    }

    .dashboard_new .project_status_no li {
        width: 18%;
        text-align: center;
        font-size: 11px;
        line-height: 18px;
        color: #71718E;
        padding: 0 10px;
        height: auto;
        background: transparent;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .dashboard_new .project_status_no li.total {
        width: 28%;
        font-size: 14px;
        line-height: 28px;
        font-weight: 600;
        color: #292940;
        padding: 0
    }

    .dashboard_new .project_status_no li.total strong {
        font-size: 20px;
        color: #292940
    }

    .dashboard_new .project_status_no li strong {
        display: block;
        font-size: 18px;
        margin-bottom: auto;
    }

    .dashboard_new .project_status_no li.complete strong {
        color: #2AD36C
    }

    .dashboard_new .project_status_no li.progress strong {
        color: #6570FD
    }

    .dashboard_new .project_status_no li.delay strong {
        color: #F99003
    }

    .dashboard_new .project_status_no li.risk strong {
        color: #E84C85
    }

    .dashboard_new .status {
        display: inline-block;
        position: relative
    }

    .dashboard_new .status::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
        margin-right: 5px;
    }

    .dashboard_new .status_complete::before {
        background: #2AD36C
    }

    .dashboard_new .status_progress::before {
        background: #1A73E8
    }

    .dashboard_new .status_ontrack::before {
        background: #6570FD
    }

    .dashboard_new .status_delay::before {
        background: #F99003
    }

    .dashboard_new .status_risk::before {
        background: #E84C85
    }

    .dashboard_new .profit_green {
        color: #2AD36C
    }

    .dashboard_new .profit_purple {
        color: #6570FD
    }

    .dashboard_new .profit_red {
        color: #E84C85
    }

    .dashboard_new .overall_progress_status ul li {
        display: block;
        width: 100%;
        padding: 0;
        text-align: left;
        margin-top: 15px;
        padding-left: 10px;
    }

    .dashboard_new .overall_progress_status ul li strong {
        display: inline-block;
        margin-right: 5px
    }

    .dashboard_new .overall_progress_status ul li.total {
        width: 100%;
        margin: 30px 0;
    }

    .dashboard_new .overall_progress_status ul li.total .total_milestone {
        min-width: 32px;
        min-height: 32px;
        border-radius: 4px;
        background: #ebedf7;
        display: inline-block;
        padding: 4px 12px;
        text-align: center;
        color: #71718E
    }


    .cmn_tabular_data table {
        width: 100%
    }

    .cmn_tabular_data tbody,
    .cmn_tabular_data thead {
        flex-direction: column;
        -webkit-flex-direction: column;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
    }

    .cmn_tabular_data tbody {
        height: 230px;
        overflow: auto;
    }

    .cmn_tabular_data thead {
        border-bottom: 1px solid #ddd;
    }

    .cmn_tabular_data tr {
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        align-items: center;
        -webkit-align-items: center;
    }

    .cmn_tabular_data tbody tr {
        border-top: 1px solid #f1f1f1
    }

    .cmn_tabular_data tbody tr:first-child {
        border-top: 1px solid transparent
    }

    .cmn_tabular_data tbody tr:hover {
        background: #f8f8f8
    }

    /*.cmn_tabular_data tbody tr:nth-child(2n+1) {background: #f8f8f8;}*/
    .cmn_tabular_data td,
    .cmn_tabular_data th {
        width: 125px;
        text-align: left;
        font-size: 14px;
        font-weight: 400;
        line-height: 18px;
        color: #727272;
        padding: 8px;
        flex: 1;
        -webkit-flex: 1;
        -ms-flex: 1;
        -webkit-box-flex: 1;
        -moz-box-flex: 1;
    }
    .cmn_tabular_data .th_title {
        position: relative;
        display: inline-block;
        padding-right: 20px;
        white-space: nowrap
    }

    .cmn_tabular_data .th_title .sort-icon {
        position: absolute;
        right: 0;
        top: 0
    }

    .cmn_tabular_data td {
        color: #2e2e2e;
    }

    .dashboard_new .filter_line .checkbox {
        margin: 0
    }

    .dashboard_new .filter_line .checkbox label {
        font-size: 16px;
        color: #292940
    }

    .dashboard_new .todo_tabs_panel {
        border: 1px solid #e8e8f3;
        padding: 10px;
        border-radius: 0px 4px 4px 4px;
    }

    .dashboard_new .todo_tabs_panel .tab_panel {
        display: none
    }

    .dashboard_new .item_card .todo_tabs {
        margin: 20px 0 0;
    }

    .dashboard_new .todo_tabs li {
        max-width: 50%;
        padding: 0;
        margin: 0;
        text-align: center;
    }

    .dashboard_new .todo_tabs a {
        text-decoration: none;
        display: block;
        color: #7878a5;
        font-size: 16px;
        line-height: 24px;
        font-weight: 500;
        padding: 6px 12px;
        border: 1px solid #e8e8f3;
        border-top-right-radius: 4px;
        border-top-left-radius: 4px;
        background-color: #E4E7EB;
        margin-bottom: -1px;
        margin-right: 30px;
    }

    .dashboard_new .todo_tabs a.active {
        border-bottom: 1px solid white;
        background-color: #fff;
        color: #292940;
    }

    .dashboard_new .todo_tabs a:hover {
        color: #292940;
    }


    .dashboard_new .task_todo_item {
        padding: 10px;
        border-top: 1px solid #eee
    }

    .dashboard_new .task_todo_item:first-child {
        border-top-color: transparent;
    }

    .dashboard_new .task_name_no {
        margin-bottom: 5px
    }

    .dashboard_new .task_todo_item p {
        font-size: 16px;
        line-height: 20px;
        font-weight: 400;
        color: #71718E;
        margin: 0;
        padding: 0
    }

    .dashboard_new .task_todo_item .task_name_no p {
        color: #292940;
        max-width: 50%;
        padding-right: 10px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .dashboard_new .task_todo_item .task_no {
        font-weight: 600;
    }

    .dashboard_new .task_todo_item span {
        display: inline-block;
        vertical-align: middle;
    }

    .dashboard_new .task_todo_item small {
        display: inline-block;
        vertical-align: middle;
        ;
        margin-right: 15px;
    }

    .dashboard_new .task_todo_item .due_tag {
        font-size: 13px;
        color: #E84C85;
        background: #f7e3ea;
        padding: 3px 6px;
        border-radius: 4px
    }


    .dashboard_new .activity_item {
        padding: 10px;
        border-top: 1px solid #eee;
    }

    .dashboard_new .activity_item:first-child {
        border-top-color: transparent;
    }

    .dashboard_new .activity_item .pfl_img {
        width: 60px;
        height: 60px;
        border: 1px solid #eee;
        border-radius: 50%;
        overflow: hidden
    }

    .dashboard_new .activity_item .pfl_img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover
    }

    .dashboard_new .activity_item .activity_desc {
        width: calc(100% - 60px);
        width: -webkit-calc(100% - 60px);
        width: -moz-calc(100% - 60px);
        padding-left: 10px
    }

    .dashboard_new .activity_item .activity_desc p {
        font-size: 16px;
        line-height: 20px;
        font-weight: 400;
        color: #292940;
        margin: 0;
        padding: 0
    }

    .dashboard_new .activity_item .activity_desc p+p {
        margin-top: 5px
    }

    .dashboard_new .activity_item .activity_desc small {
        display: inline-block;
        vertical-align: middle;
        color: #71718E
    }

    .dashboard_new .activity_item .activity_desc a {
        text-decoration: none;
        font-size: 85%;
        color: #6570FD;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px
    }

    .dashboard_new .activity_item .activity_desc strong {
        display: inline-block;
        vertical-align: middle;
        font-weight: 600;
        padding: 0 5px
    }

    .dashboard_new .activity_item .activity_desc .status::before {
        display: none
    }

    .dashboard_new .insight_item ul li .bg_tag {
        min-width: 30px;
        text-align: center;
        display: inline-block;
        background: #42b145;
        font-weight: 500;
        color: #fff;
        padding: 5px;
        border-radius: 4px;
        vertical-align: middle;
    }


    .dashboard_new .insight_item ul li.insight_task_li::before {
        background-position: 0px 0px
    }

    .dashboard_new .insight_item ul li.insight_proj_li::before {
        background-position: 0px -46px
    }

    .dashboard_new .insight_item ul li.insight_profit_li::before {
        background-position: 0px -95px
    }

    .dashboard_new .insight_item ul li.insight_time_li::before {
        background-position: 0px -144px
    }

    .dashboard_new .insight_item ul li.insight_bug_li::before {
        background-position: 2px -195px
    }

    .dashboard_new .insight_item ul li.insight_feature_li::before {
        background-position: 1px -241px
    }

    .select_field_wrapper .select2-container--default .select2-selection--single .select2-selection__clear,
    .select_field_wrapper .select2.select2-container--default .select2-selection .select2-selection__arrow {
        display: none
    }

    /*rounded circle*/
    .radial_count_chat {
        display: inline-block;
        position: relative;
    }

    .radial_count_chat .hover_data {
        font-size: 12px;
        color: #fff;
        width: 105px;
        height: auto;
        padding: 4px;
        border: 1px solid #3c3636;
        background: #3c3636;
        position: absolute;
        left: calc(100% + 0px);
        left: -webkit-calc(100% + 0px);
        left: -moz-calc(100% + 0px);
        top: 4px;
        z-index: 11;
        border-radius: 4px;
        display: none;
    }

    .radial_count_chat:hover .hover_data {
        display: block
    }

    .circle_percent {
        font-size: 40px;
        width: 1em;
        height: 1em;
        position: relative;
        background: #eee;
        border-radius: 50%;
        overflow: hidden;
        display: inline-block;
        margin: 0px;
    }

    .circle_inner {
        position: absolute;
        left: 0;
        top: 0;
        width: 1em;
        height: 1em;
        clip: rect(0 1em 1em .5em);
    }

    .round_per {
        position: absolute;
        left: 0;
        top: 0;
        width: 1em;
        height: 1em;
        background: #e4a6d2;
        clip: rect(0 1em 1em .5em);
        transform: rotate(180deg);
        transition: 1.05s;
    }

    .percent_more .circle_inner {
        clip: rect(0 .5em 1em 0em);
    }

    .percent_more:after {
        position: absolute;
        left: .5em;
        top: 0em;
        right: 0;
        bottom: 0;
        background: #2AD36C;
        content: '';
    }

    .percent_more.percent_more_complete:after {
        background: #2AD36C;
    }

    .percent_more.percent_more_ontrack:after {
        background: #6570FD;
    }

    .percent_more.percent_more_delay:after {
        background: #F99003;
    }

    .percent_more.percent_more_risk:after {
        background: #E84C85;
    }

    .circle_inbox {
        position: absolute;
        top: 5px;
        left: 5px;
        right: 5px;
        bottom: 5px;
        background: #fff;
        z-index: 3;
        border-radius: 50%;
    }

    .percent_text {
        position: absolute;
        font-size: 12px;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 3;
    }

    .total_percentage_mile,
    .total_percentage_graph {
        display: none;
    }

    .frontend_sorting .sort-th {
        cursor: pointer;
    }

    .frontend_sorting .sort-icon {
        vertical-align: middle;
        display: inline-block;
        line-height: 16px;
        position: relative;
        top: 3px;

    }

    .frontend_sorting .sort-icon .material-icons {
        position: relative;
        top: -2px;
        left: 2px
    }

    .frontend_sorting .sort-icon .material-icons.default-sort {
        font-size: 16px;
        color: #888;
        top: 0;
        left: 2px
    }

    .dot {
        height: 17px;
        width: 17px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-green {
        background-color: #2AD36C;
    }

    .dot-purple {
        background-color: #6570FD;
    }

    .togglebutton label .toggle {
        margin: 0 10px;
    }

    .togglebutton label .toggle,
    .togglebutton label input[type=checkbox][disabled]+.toggle {
        background-color: #8EC4F3;
    }

    .togglebutton label .toggle:after {
        background-color: #1F87E6
    }

    #tot_workload_info {
        padding: 16px 0 0;
        font-size: 17px;
    }

    .budget-total-cnt {
        font-size: 16px;
        line-height: 28px;
        font-weight: 600;
        position: relative;
        top: -20px;
        text-align: left;
    }

    /*.budget-total-cnt div{margin: 0 0 0 80px;}*/
    .budget-total-cnt div:first-child {
        margin-left: -5px;
    }

    .budget_help {
        top: -2px;
        position: relative;
    }

    .progress_chat .highcharts-container {
        width: 100% !important
    }

    .progress_chat .highcharts-root {
        width: 100% !important
    }
    .dashboard_new .item_card .togglebutton label{
        font-size: 14px;
        line-height: 1.42857143;
        font-weight: 400;
    }
    .dashboard_new table>tbody>tr>td{text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;}
</style>
<div class="dashboard_new">
    <div class="row counter_items">
        <div class="col-md-3">
            <div class="item_card  transition">
                <div class="item_name">
                    <h5><?php echo __('Projects'); ?></h5>
                    <div class="count_no"><strong id="proj-count-complete" rel="tooltip" title="<?php echo __('Total Completed Project'); ?>">0</strong> / <span id="proj-count-total" rel="tooltip" title="<?php echo __('Total Projects'); ?>">0</span></div>
                </div>
                <div class="growth_percentage" id="proj-inc-dec"><span class="increase">0%</span> <?php echo __('Increase from Last Month'); ?></div>
                <figure>
                    <img src="<?php echo HTTP_ROOT; ?>img/tools/Dashboard-projects.png" alt="<?php echo __('Projects'); ?>" width="65" height="65" />
                </figure>
            </div>
        </div>
        <div class="col-md-3">
            <div class="item_card transition">
                <div class="item_name">
                    <h5><?php echo __('Tasks'); ?></h5>
                    <div class="count_no"><strong id="tasks-count-complete" rel="tooltip" title="<?php echo __('Total Closed Task'); ?>">0</strong> / <span id="tasks-count-total" rel="tooltip" title="<?php echo __('Total Task'); ?>">0</span></div>
                </div>
                <div class="growth_percentage" id="tasks-inc-dec"><span class="decrease">0%</span> <?php echo __('Increase from Last Month'); ?></div>
                <figure>
                    <img src="<?php echo HTTP_ROOT; ?>img/tools/Dashboard-tasks.png" alt="<?php echo __('Tasks'); ?>" width="65" height="65" />
                </figure>
            </div>
        </div>
        <div class="col-md-3">
            <div class="item_card transition">
                <div class="item_name">
                    <h5><?php echo __('Resources'); ?></h5>
                    <div class="count_no"><strong id="rsrc-count-complete" rel="tooltip" title="<?php echo __('Active Resources'); ?>">0</strong> / <span id="rsrc-count-total" rel="tooltip" title="<?php echo __('Total Resources'); ?>">0</span></div>
                </div>
                <div class="growth_percentage" id="rsrc-inc-dec"><span class="increase">0%</span> <?php echo __('Increase from Last Month'); ?></div>
                <figure>
                    <img src="<?php echo HTTP_ROOT; ?>img/tools/Dashboard-resources.png" alt="<?php echo __('Resources'); ?>" width="65" height="65" />
                </figure>
            </div>
        </div>
        <div class="col-md-3">
            <div class="item_card transition">
                <div class="item_name">
                    <h5><?php echo __('Time Spent'); ?></h5>

                </div>
                <div class="count_no"><strong id="time-count-complete" rel="tooltip" title="<?php echo __('Total Logged Hours'); ?>">0</strong> / <span id="time-count-total" rel="tooltip" title="<?php echo __('Total Estimated Hours'); ?>">0</span> <?php echo __("hours"); ?></div>
                <div class="growth_percentage" id="timlog-inc-dec"><span class="decrease">0%</span> <?php echo __('Increase from Last Month'); ?></div>
                <figure>
                    <img src="<?php echo HTTP_ROOT; ?>img/tools/Dashboard-time.png" alt="<?php echo __('Time Spent'); ?>" width="65" height="65" />
                </figure>
            </div>
        </div>
    </div>
    

    <div class="cmn_row d-flex project_summary mt_30">
        <div class="width-65-per plr_15">
            <div class="item_card transition" id="proj-summary-block">
                <div class="d-flex align-item-center">
                    <div class="item_title"><?php echo __('Project Summary'); ?></div>
                    <div class="filter_line">
                        <div class="d-flex align-item-center select_option">
                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_rsrc_project_program" class="form-control">
                                    <?php foreach ($program_list as $program_k => $program_v) { ?>
                                        <option value="<?php echo $program_k; ?>"><?php echo $program_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_rsrc_project" class="form-control">
                                    <?php foreach ($project_list as $project_k => $project_v) { ?>
                                        <option value="<?php echo $project_k; ?>"><?php echo $project_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_project_manager" class="form-control"> <!--multiple="multiple" -->
                                    <?php foreach ($proj_mgr_list as $proj_mgr_k => $proj_mgr_v) { ?>
                                        <option value="<?php echo $proj_mgr_k; ?>"><?php echo $proj_mgr_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_project_status" class="form-control">
                                    <?php foreach ($project_status_list as $status_k => $status_v) { ?>
                                        <option value="<?php echo $status_k; ?>"><?php echo $status_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cmn_tabular_data mt_20">
                    <table>
                        <thead>
                            <tr class="frontend_sorting">
                                <th onclick="sortTableData('project_name', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Project Name'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('user_name', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Project Manager'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('due_date', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Due Date'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('status', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Status'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('percentage', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Progress'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('rem_days', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Remaining Days'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('rem_hours', this, 'project')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Remaining Hours'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <div id="proj_summary_container"></div>
                </div>
            </div>
        </div>
        <div class="width-35-per plr_15">
            <div class="item_card transition" id="overall-progress-block">
                <div class="d-flex align-item-center">
                    <div class="item_title"><?php echo __('Project Overall Progress'); ?></div>
                </div>

                <div class="loader_dv_db" id="project_status_ldr" style="display: none;margin-top:90px">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                </div>
                <div class="progress_chat mt_20">
                    <div id="project_status" class="text-center pr">
                        <div class="total_figure"><span class="total_percentage_graph">0</span><strong><?php echo __('Completed'); ?></strong></div>
                        <div id="project_status_pie">
                        </div>
                    </div>
                </div>


                <div class="project_status_no mt_20">
                    <ul class="d-flex width-100-per">
                        <li class="total">
                            <strong class="_graph_cnt total_prj">0</strong>
                            <?php echo __('Total Projects'); ?>
                        </li>
                        <li class="complete">
                            <strong class="_graph_cnt status_complete_graph">0</strong>
                            <?php echo __('Completed'); ?>
                        </li>
                        <li class="progress">
                            <strong class="_graph_cnt status_ontrack_graph">0</strong>
                            <?php echo __('On Track'); ?>
                        </li>
                        <li class="delay">
                            <strong class="_graph_cnt status_delay_graph">0</strong>
                            <?php echo __('Delayed'); ?>
                        </li>
                        <li class="risk">
                            <strong class="_graph_cnt status_risk_graph">0</strong>
                            <?php echo __('At Risk'); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

   

    <div class="cmn_row d-flex project_summary mt_30">
        <div class="width-65-per plr_15">
            <div class="item_card transition" id="milestone-summary-block">
                <div class="d-flex align-item-center">
                    <div class="item_title"><?php echo __('Milestone Summary'); ?></div>
                    <div class="filter_line">
                        <div class="d-flex align-item-center select_option">

                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_proj_milestone" class="form-control">
                                    <?php foreach ($project_list as $project_k => $project_v) { ?>
                                        <option value="<?php echo $project_k; ?>"><?php echo $project_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_milestone" class="form-control">
                                    <?php foreach ($milestone_list as $milestone_k => $milestone_v) { ?>
                                        <option value="<?php echo $milestone_k; ?>"><?php echo $milestone_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="select_field_wrapper size_35 select2-scroll-remover">
                                <select id="sel_milestone_status" class="form-control">
                                    <?php foreach ($project_status_list as $status_k => $status_v) { ?>
                                        <option value="<?php echo $status_k; ?>"><?php echo $status_v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="cmn_tabular_data mt_20">
                    <table>
                        <thead>
                            <tr class="frontend_sorting">
                                <th onclick="sortTableData('project_name', this, 'milestone')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Project Name'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('milestone_name', this, 'milestone')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Milestone'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('due_date', this, 'milestone')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Due Date'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('status', this, 'milestone')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Status'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                                <th onclick="sortTableData('percentage', this, 'milestone')" data-sort="1" class="sort-th">
                                    <div class="th_title"><?php echo __('Progress'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <div id="milestone_summary_container"></div>
                </div>
            </div>
        </div>
        <div class="width-35-per plr_15">
            <div class="item_card transition" id="milestone-progress-block">
                <div class="d-flex align-item-center">
                    <div class="item_title"><?php echo __('Milestone Overall Progress'); ?></div>
                </div>

                <div class="loader_dv_db mt_30" id="milestone_status_ldr" style="display: none">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="<?php echo __('Loading'); ?>..." />
                </div>

                <div class="d-flex width-100-per overall_progress_status mt_20">
                    <div class="width-40-per">

                        <div class="project_status_no">
                            <ul class="width-100-per">
                                <li class="total">
                                    <strong class="_graph_cnt_mile total_mile">0</strong>
                                    <?php echo __('Total Milestones'); ?>
                                </li>
                                <li class="complete">
                                    <strong class="_graph_cnt_mile status_complete_mile">0</strong>
                                    <?php echo __('Completed'); ?>
                                </li>
                                <li class="progress">
                                    <strong class="_graph_cnt_mile status_ontrack_mile">0</strong>
                                    <?php echo __('On Track'); ?>
                                </li>
                                <li class="delay">
                                    <strong class="_graph_cnt_mile status_delay_mile">0</strong>
                                    <?php echo __('Delayed'); ?>
                                </li>
                                <li class="risk">
                                    <strong class="_graph_cnt_mile status_risk_mile">0</strong>
                                    <?php echo __('At Risk'); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="width-60-per">
                        <div class="progress_chat">
                            <div id="milestone_status" class="text-center pr mt_60">
                                <div class="total_figure"><span class="total_percentage_mile">0</span><strong><?php echo __('Completed'); ?></strong></div>
                                <div id="milestone_status_pie"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="cmn_row d-flex project_summary mt_30">
		<div class="width-65-per plr_15">
			<div class="item_card transition" id="budget-summary-block">
				<div class="d-flex align-item-center">
					<div class="item_title"><?php echo __('Budget & Cost Report'); ?></div>
					<div class="filter_line">
						<div class="d-flex align-item-center select_option">
							<div class="togglebutton">
								<label class="m_0">
									<span class="toggleBtnLbl"><?php echo __("Fixed Budget");?></span>
									<input type="checkbox" name="showBudget" id="toggleShowbudget" style="cursor:pointer;">
									<input type="hidden" name="is_budgeted" id="is_budgeted" value="1">
									<span class="toggleBtnLbl"><?php echo __("Hourly Rate");?></span>
								</label>
							</div>
							<div class="select_field_wrapper size_35 select2-scroll-remover">
								<select id="sel_proj_budget" class="form-control">
									<?php foreach ($project_list as $project_k => $project_v) { ?>
										<option value="<?php echo $project_k; ?>"><?php echo $project_v; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="cmn_tabular_data mt_20">					
					<table>
						<thead>
							<tr class="frontend_sorting">
								<th onclick="sortTableData('project_name', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Project Name'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
								<th onclick="sortTableData('project_manager', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Manager'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
								<th onclick="sortTableData('budget', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Budget'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
								<th onclick="sortTableData('cost_to_client', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Cost to Client'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
								<th onclick="sortTableData('cost_to_company', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Cost to Company'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
								<th onclick="sortTableData('profit', this, 'budget')" data-sort="1" class="sort-th"><div class="th_title"><?php echo __('Profit'); ?> <span class="sort-icon"><i class="material-icons default-sort">sort</i></span></div></th>
							</tr>
						</thead>
					</table>
					<div id="budget_summary_container" class="text-center"></div>
				</div>				
			</div>
		</div>
		<div class="width-35-per plr_15">
			<div class="item_card transition" id="budget-progress-block">
				<div class="d-flex align-item-center">
					<div class="item_title">
						<?php echo __('Budget vs Cost');?>
						<a href="javascript:void(0);" class="onboard_help_anchor budget_help" 
							title="<?php 
                            echo sprintf(
                                '%s %s %s',
                                __('Budget and cost are shown as per the currency set in the My Company page.'),
                                __('You can change this under Company Settings.'),
                                __('Total values may vary according to the current exchange rate.')
                            );
                            ?>" rel="tooltip">
							<i class="material-icons">&#xE8FD;</i>
						</a>
					</div>
				</div>
				<div class="width-100-per">
					<div class="progress_chat mt_20">
						<div id="budget_status_pie" style="height:280px;"></div>
						<div class="d-flex width-100-per align-item-center justify-center budget-total-cnt">
							<div class="profit_green width-50-per" style="padding-left: 103px;">0</div>
							<div class="profit_purple width-50-per" style="padding-left: 23px;">0</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div style="height: 20px;"></div>
</div>

<script type="text/template" id="projectSummaryTmpl">
    <?php echo $this->element('project_summary_list'); ?> 
</script>
<script type="text/template" id="milestoneSummaryTmpl">
    <?php echo $this->element('milestone_summary_list'); ?> 
</script>
<script type="text/template" id="todoSummaryTmpl">
    <?php echo $this->element('todo_summary_list'); ?>
</script>
<script type="text/template" id="budgetSummaryTmpl">
    <?php echo $this->element('budget_summary_list'); ?> 
</script>

<script type="text/javascript" src="<?php echo JS_PATH; ?>my_dashboard.js?v=<?php echo RELEASE; ?>" defer>

</script>
<script type="text/javascript">
    var DASHBOARD_ORDER = <?php echo json_encode($GLOBALS['DASHBOARD_ORDER']); ?>;
    var MILESTONE_MAP = <?php echo !empty($milestone_list_map) ? json_encode($milestone_list_map) : '{}'; ?>;

    function prj_change_rsrc_reprt() {
        $('#resource_cost_report').html("");
        var url = HTTP_ROOT + 'easycases/resource_cost_report';
        var tmeflt = $('#sel_rsrc_time_typ').val();
        localStorage.removeItem('TIMELOGCOSTREP');
        localStorage.setItem('TIMELOGCOSTREP', JSON.stringify(tmeflt));
        var prjlrpt = $('#sel_rsrc_project').val();
        localStorage.removeItem('PROJECTLOGCOSTREP');
        localStorage.setItem('PROJECTLOGCOSTREP', JSON.stringify(prjlrpt));
        var project_cost_rep = localStorage.getItem('PROJECTLOGCOSTREP');
        if (project_cost_rep === 'null') {
            project_cost_rep = [];
        } else {
            project_cost_rep = JSON.parse(project_cost_rep);
        }
        $('#resource_cost_report_ldr').show();
        $.post(url, {
            "projid": project_cost_rep,
            "time_flt": tmeflt
        }, function(data) {
            $('#resource_cost_report_ldr').hide();
            $('#resource_cost_report').html(data);
        });
    }

    function fulscreen_div(type) {
        if (type == "cost_rprt") {
            var data = $('#rag_cost_report').html();
            if (data.indexOf("Oops") == -1) {
                $(".hide_buttn").toggle();
                $('.cst_rprt_tr').show();
                $('#cost_rpt_highchart').hide();
                $('#rag_cost_report').addClass('fullscreen');
            } else {
                showTopErrSucc('error', 'No data to display');
            }
        }
    }

    function fulscreen_div1(type) {
        if (type == "resource_cost_rprt") {
            var data = $('#resource_cost_report').html();
            if (data.indexOf("Oops") == -1) {
                $(".hide_buttn").toggle();
                $('.view_tr').toggle();
                $('.hide_td').show();
                $('.resource_tr').show();
                $('#resource_cost_report').addClass('fullscreen');
                $("#zoomData").addClass('mbtm30 ');
            } else {
                showTopErrSucc('error', 'No data to display');
            }
        }
    }

    function hide_flscrn_div(type) {
        if (type == 'cost_rprt') {
            $(".hide_buttn").toggle();
            $('.cst_rprt_tr').hide();
            $('#rag_cost_report').removeClass('fullscreen');
            $('#cost_rpt_highchart').show();
        } else if (type == 'resource_cost_rprt') {
            $(".hide_buttn").toggle();
            $('.resource_tr:gt(8)').css("display", "none");
            $('#resource_cost_report').removeClass('fullscreen');
        }
    }
    $(".select2-scroll-remover").click(function(e) {
        $('body').addClass('resource-cost-overflow');
        e.stopPropagation();
    });

    $(document).ready(function () {
        $("#sel_proj_milestone").change(function () {
            var projectId = $(this).val();
            var milestoneDropdown = $("#sel_milestone");
            milestoneDropdown.select2('destroy');
            milestoneDropdown.find('option').each(function () {
                var milestoneId = $(this).val();
                if (projectId && !(MILESTONE_MAP[milestoneId] == projectId)) {
                    $(this).prop("disabled", true);
                } else {
                    $(this).prop("disabled", false);
                }
            });

            milestoneDropdown.select2();
        });
    });
</script>