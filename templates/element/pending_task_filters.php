<div class="fr pfl-icon-dv pen_task">
    <span class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" rel="tooltip"
            title="<?php echo __('Filter'); ?>">
            <i class="glyphicon glyphicon-filter"></i>
        </a>
        <ul class="dropdown-menu pending-task-filter drop_menu_mc dropdown_menu_all_filters_ul">
            <li class="drop_menu_mc">
                <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:void(0)"
                    onclick="allfiltervalue('pendingtask', event);"><i
                        class="material-icons">&#xE916;</i><?php echo __('Date'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm arch-due-dt" id="dropdown_menu_pendingtask">
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_today" data-id="today" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'today') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('today', 'check');" />
                                <?php echo __('Today'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_yesterday" data-id="yesterday" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'yesterday') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('yesterday', 'check');" />
                                <?php echo __('Yesterday'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_thisweek" data-id="thisweek" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'thisweek') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('thisweek', 'check');" />
                                <?php echo __('This Week'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_thismonth" data-id="thismonth" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'thismonth') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('thismonth', 'check');" />
                                <?php echo __('This Month'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_thisquarter"
                                    data-id="thisquarter" <?php
                                    if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'thisquarter') {
                                        echo "checked";
                                    }
                                    ?> onclick="pendingtask('thisquarter', 'check');" />
                                <?php echo __('This Quarter'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_thisyear" data-id="thisyear" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'thisyear') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('thisyear', 'check');" />
                                <?php echo __('This Year'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_lastweek" data-id="lastweek" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'lastweek') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('lastweek', 'check');" />
                                <?php echo __('Last Week'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_lastmonth" data-id="lastmonth" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'lastmonth') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('lastmonth', 'check');" />
                                <?php echo __('Last Month'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_lastquarter"
                                    data-id="lastquarter" <?php
                                    if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'lastquarter') {
                                        echo "checked";
                                    }
                                    ?> onclick="pendingtask('lastquarter', 'check');" />
                                <?php echo __('Last Quarter'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_lastyear" data-id="lastyear" <?php
                                if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'lastyear') {
                                    echo "checked";
                                }
                                ?> onclick="pendingtask('lastyear', 'check');" />
                                <?php echo __('Last Year'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" id="pending_last365days"
                                    data-id="last365days" <?php
                                    if (isset($_COOKIE['pending_date_filter']) && $_COOKIE['pending_date_filter'] == 'last365days') {
                                        echo "checked";
                                    }
                                    ?> onclick="pendingtask('last365days', 'check');" />
                                <?php echo __('Last 365 days'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="li_check_radio">
                        <div class="checkbox">
                            <label>
                                <input class="cst_date_cls" type="checkbox" data-id="custom" id="pending_custom" <?php
                                if (isset($_COOKIE['pending_date_filter']) && strpos($_COOKIE['pending_date_filter'], ':')) {
                                    echo "checked";
                                }
                                ?> onclick="customdatependingtask(this);" />
                                <?php echo __('Custom Date'); ?>
                            </label>
                        </div>
                    </li>
                    <li class="custome_timelog custom_date_li" style="display: none;">
                        <?php
                        if (isset($_COOKIE['pending_date_filter'])) {
                            $dt = explode(':', $_COOKIE['pending_date_filter']);
                        } else
                            $dt = '';
                        ?>
                        <div class="frto_sch">
                            <input type="text" class="smal_txt form-control "
                                placeholder="<?php echo __('From Date'); ?>" readonly id="pendingstrtdt"
                                value="<?php echo @$dt[0]; ?>" />
                            <input type="text" class="smal_txt form-control " placeholder="<?php echo __('To Date'); ?>"
                                readonly id="pendingenddt" value="<?php echo @$dt[1]; ?>" />
                            <button class="btn btn-sm btn-raised  btn_cmn_efect cmn_bg btn-info cdate_btn aply_btn"
                                type="button" onclick="pendingtask('custom', 'Custom');"
                                id="btn_timelog_search"><?php echo __('Search'); ?></button>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('pendingtask_status', event);"><i
                        class="material-icons">&#xE88B;</i><?php echo __('Status'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_pending_status">
                </ul>
            </li>
            <li class="drop_menu_mc">
                <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="javascript:void(0)"
                    onclick="allfiltervalue('pending_project', event);"><i
                        class="material-icons">&#xE8F9;</i><?php echo __('Project'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_pending_project">

                </ul>
            </li>
            <li class="drop_menu_mc dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" data-target="#" data-toggle="dropdown"
                    onclick="allfiltervalue('pending_resource', event);"><i
                        class="material-icons">&#xE90F;</i><?php echo __('Resource'); ?></a>
                <ul class="dropdown_status dropdown-menu drop_smenu ltsm scroll-listing"
                    id="dropdown_menu_pending_resource"></ul>
            </li>
        </ul>
    </span>
</div>
<div class="tag-btn pending_filter_msg fr" data-column-id="filter_msg" style="display:table">
    <div class="ver_midl">
        <div id="filtered_items" class="tag-block" style="display: none;"></div>
    </div>
    <div id="pending_filter_msg_close" class="filter_btn_section ver_midl">
        <span id="reset_btn" title="<?php echo __('Reset Filters'); ?>" style="display: none;">
            <i class="material-icons">&#xE8BA;</i>
        </span>
    </div>
</div>