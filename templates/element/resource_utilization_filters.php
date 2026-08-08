<div class="tag-btn utilization_filter_msg fr" data-column-id="filter_msg" style="display:table">
    <div class="ver_midl">
        <div id="filtered_items" class="tag-block" style="display: none;"></div>
    </div>
    <div id="utilization_filter_msg_close" class="filter_btn_section ver_midl">
        <span id="reset_btn" title="<?php echo __('Reset Filters'); ?>" style="display: none;">
            <i class="material-icons">&#xE8BA;</i>
        </span>
    </div>
</div>
<div class="fr pfl-icon-dv res_utli">
    <span class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" rel="tooltip"
            title="<?php echo __('Filter'); ?>">
            <i class="glyphicon glyphicon-filter"></i>
        </a>
        <ul class="dropdown-menu resource-utilization-filter drop_menu_mc dropdown_menu_all_filters_ul">
            <li class="drop_menu_mc">
                <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:void(0)"
                    onclick="allfiltervalue('utilization', event);">
                    <i class="material-icons">&#xE916;</i><?php echo __('Date'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm arch-due-dt" id="dropdown_menu_utilization">
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_today">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox" id="utilization_today"
                                    data-id="today" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'today') {
                                        echo "checked";
                                    } ?> onclick="utilization('today', 'check');" />
                                <?php echo __('Today'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_yesterday">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_yesterday" data-id="yesterday" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'yesterday') {
                                        echo "checked";
                                    } ?> onclick="utilization('yesterday', 'check');" />
                                <?php echo __('Yesterday'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_thisweek">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox" id="utilization_thisweek"
                                    data-id="thisweek" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'thisweek') {
                                        echo "checked";
                                    } ?> onclick="utilization('thisweek', 'check');" />
                                <?php echo __('This Week'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_thismonth">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_thismonth" data-id="thismonth" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'thismonth') {
                                        echo "checked";
                                    } ?> onclick="utilization('thismonth', 'check');" />
                                <?php echo __('This Month'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_thisquarter">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_thisquarter" data-id="thisquarter" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'thisquarter') {
                                        echo "checked";
                                    } ?>
                                    onclick="utilization('thisquarter', 'check');" />
                                <?php echo __('This Quarter'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_thisyear">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox" id="utilization_thisyear"
                                    data-id="thisyear" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'thisyear') {
                                        echo "checked";
                                    } ?> onclick="utilization('thisyear', 'check');" />
                                <?php echo __('This Year'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_lastweek">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox" id="utilization_lastweek"
                                    data-id="lastweek" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'lastweek') {
                                        echo "checked";
                                    } ?> onclick="utilization('lastweek', 'check');" />
                                <?php echo __('Last Week'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_lastmonth">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_lastmonth" data-id="lastmonth" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'lastmonth') {
                                        echo "checked";
                                    } ?> onclick="utilization('lastmonth', 'check');" />
                                <?php echo __('Last Month'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_lastquarter">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_lastquarter" data-id="lastquarter" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'lastquarter') {
                                        echo "checked";
                                    } ?>
                                    onclick="utilization('lastquarter', 'check');" />
                                <?php echo __('Last Quarter'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_lastyear">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox" id="utilization_lastyear"
                                    data-id="lastyear" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'lastyear') {
                                        echo "checked";
                                    } ?> onclick="utilization('lastyear', 'check');" />
                                <?php echo __('Last Year'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_last365days">
                                <input class="cst_date_cls cst_date_cls_resrc" type="checkbox"
                                    id="utilization_last365days" data-id="last365days" <?php if (($_COOKIE['utilization_date_filter'] ?? '') == 'last365days') {
                                        echo "checked";
                                    } ?>
                                    onclick="utilization('last365days', 'check');" />
                                <?php echo __('Last 365 days'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label for="utilization_custom_uti">
                                <input class="cst_date_cls" type="checkbox" data-id="custom" id="utilization_custom_uti"
                                    <?php if (strpos(($_COOKIE['utilization_date_filter'] ?? ''), ':')) {
                                        echo "checked";
                                    } ?> onclick="customdatetutilization();" />
                                <?php echo __('Custom Date'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="custome_timelog custom_date_li"
                        style="<?php if (strpos(($_COOKIE['utilization_date_filter'] ?? ''), ':')) { ?>display: block;<?php } else { ?>display: none;<?php } ?>">
                        <?php
                        $dt = isset($_COOKIE['utilization_date_filter']) ? explode(':', $_COOKIE['utilization_date_filter']) : [];
                        ?>
                        <div class="frto_sch">
                            <input type="text" class="smal_txt form-control "
                                placeholder="<?php echo __('From Date'); ?>" readonly id="utilizationstrtdt"
                                value="<?php echo count($dt) > 1 ? ($dt[0] ?? '') : ''; ?>" />
                            <input type="text" class="smal_txt form-control " placeholder="<?php echo __('To Date'); ?>"
                                readonly id="utilizationenddt"
                                value="<?php echo count($dt) > 1 ? ($dt[1] ?? '') : ''; ?>" />
                            <button class="btn btn-sm btn-raised  btn_cmn_efect cmn_bg btn-info cdate_btn aply_btn"
                                type="button" onclick="utilization('custom', 'Custom');"
                                id="btn_timelog_search"><?php echo __('Search'); ?></button>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('utilization_status', event);"><i
                        class="material-icons">&#xE88B;</i><?php echo __('Status'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_utilization_status">
                </ul>
            </li>
            <li class="drop_menu_mc">
                <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:void(0)"
                    onclick="allfiltervalue('utilization_project', event);"><i
                        class="material-icons">&#xE8F9;</i><?php echo __('Project'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_utilization_project">

                </ul>
            </li>
            <?php if ($this->Format->isAllowed('View All Resource', $roleAccess)) { ?>
                <li class="drop_menu_mc dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                        onclick="allfiltervalue('utilization_resource', event);"><i
                            class="material-icons">&#xE90F;</i><?php echo __('Resource'); ?></a>
                    <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                        id="dropdown_menu_utilization_resource"></ul>
                </li>
            <?php } ?>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('utilization_label', event);"><i
                        class="material-icons">label</i><?php echo __('Label'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing new-dropdown-scroll"
                    id="dropdown_menu_utilization_label"></ul>
            </li>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('utilization_billability', event);"><i
                        class="material-icons">local_atm</i><?php echo __('Billability'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_utilization_billability">
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="utilization-billability" id="utilization_billable"
                                    data-id="billable" onclick="utilization_billability('billable', 'check');" />
                                <?php echo __('Billable'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="utilization-billability" id="utilization_unbillable"
                                    data-id="unbillable" onclick="utilization_billability('unbillable', 'check');" />
                                <?php echo __('Unbillable'); ?>
                            </label>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('bunit', event);"><i
                        class="material-icons">apartment</i><?php echo __('Business Unit'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing" id="dropdown_menu_bunit">
                </ul>
            </li>
        </ul>
    </span>
</div>