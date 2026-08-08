<?php if (isset($allProjArr) && count($allProjArr)) { ?>
    <?php $gp = isset($_COOKIE['TASKGROUPBY']) && $_COOKIE['TASKGROUPBY'] == 'milestone' ? 1 : 0;
    $pageArr = ['reports', 'import', 'chart', 'hoursReport', 'glideChart', 'defectSeverity'];
    $pageFilterArr = ['files'];
    ?>

    <?php if (!in_array($page, $pageArr) && !in_array($pageFilter ?? '', $pageFilterArr ?? [])) { ?>
        <?php if (isset($popupid) && $popupid != 'projpopup_log') { ?>
            <a class="show_all_opt_in_listonly"
                style="<?php if ((DEFAULT_TASKVIEW == 'tasks' && $gp != 1) || ($page == 'special_mydashoard')) { ?>display:block;<?php } else { ?>display:none;<?php } ?>"
                href="javascript:jsVoid();"
                onClick="<?php if ($page == 'activity') { ?>CaseActivity('all', 'All'); <?php } elseif ($page == 'mydashboard') { ?>CaseDashboard('all', 'All'); <?php } elseif ($page == 'milestone') { ?>caseMilestone('all', 'All', 1); <?php } else { ?>updateAllProj('0', '0', '<?php echo $page; ?>', 'all', 'All') <?php } ?>;"><?php echo __('All'); ?>
                <?php if (isset($allPjCount)) { ?>(<?php echo $allPjCount; ?>) <?php } ?></a>
        <?php } ?>
    <?php } ?>

    <?php if ($page == 'import' && $limit != "all") { ?>
        <a href="javascript:jsVoid();" class="proj_lnks ttc"
            onClick="updateAllProj('proj_all', 'all', '<?php echo $page; ?>', '0', '<?php echo rawurlencode('All'); ?>')"><?php echo $this->Format->shortLength('All', 30); ?>
            <?php if (isset($allPjCount)) { ?>(<?php echo $allPjCount; ?>)<?php } ?></a>
    <?php } ?>

    <?php if ($page == 'dynamicimport' && $limit != "all") { ?>
        <a href="javascript:jsVoid();" class="proj_lnks ttc"
            onClick="updateAllProj('proj_all', 'all', '<?php echo $page; ?>', '0', '<?php echo rawurlencode('All'); ?>')"><?php echo $this->Format->shortLength('All', 30); ?>
            <?php if (isset($allPjCount)) { ?>(<?php echo $allPjCount; ?>)<?php } ?></a>
    <?php } ?>

    <?php if ($page == 'mydashboard' && $limit != "all") { ?>
        <a href="javascript:jsVoid();" class="proj_lnks ttc"
            onClick="CaseDashboard('all', 'All');"><?php echo $this->Format->shortLength('All', 30); ?>
            <?php if (isset($allPjCount)) { ?>(<?php echo $allPjCount; ?>)<?php } ?></a>
    <?php } ?>
    <?php
    $i = 0;
    $colrs = "";
    foreach ($allProjArr as $key => $proj) {
        $i++;
        $colrs = "";
        ?>
        <?php
        if ($popupid != 'projpopup_log' && $popupid != 'projpopup_subtask') {
            if (in_array($page, ['chart', 'hoursReport', 'glideChart'])) {
                $pageMenus = [
                    'chart' => 'ReportMenu',
                    'hoursReport' => 'hoursreport',
                    'glideChart' => 'ReportGlideMenu',
                ];
                ?>
                <a href="javascript:jsVoid();" class="proj_lnks ttc"
                    onclick="<?php echo $pageMenus[$page]; ?>('<?php echo $proj['p']['uniq_id']; ?>');">
                    <?php echo $this->Format->shortLength(ucfirst($proj['p']['name']), 30); ?>
                </a>
            <?php } else if ($page == 'timer') { ?>
                    <option value="<?php echo $proj['p']['uniq_id']; ?>"><?php echo $this->Format->shortLength($proj['p']['name'], 25); ?>
                    </option>
            <?php } else { ?>
                <?php if ($key == 0) {
                    echo "<strong class='recent_used'>Recently used</strong>";
                } ?>
                    <a href="javascript:jsVoid();" <?php if ($page == 'mydashboard') { ?>data-proj-name="<?php echo $proj['p']['name']; ?>"
                            data-proj-id="<?php echo $proj['p']['uniq_id']; ?>" <?php } ?>
                        class="proj_lnks ttc <?php if ($page == 'mydashboard') { ?>proj_link_for_invite<?php } ?>"
                        onClick="<?php if ($page == 'activity') { ?>CaseActivity('<?php echo $proj['p']['id']; ?>', '<?php echo rawurlencode($proj['p']['name']); ?>'); <?php } elseif ($page == 'mydashboard') { ?>CaseDashboard('<?php echo $proj['p']['uniq_id']; ?>', '<?php echo rawurlencode($proj['p']['name']); ?>'); <?php } elseif ($page == 'milestone') { ?>caseMilestone('<?php echo $proj['p']['id']; ?>', '<?php echo rawurlencode($proj['p']['name']); ?>', 1); <?php } else { ?>updateAllProj('proj_<?php echo $proj['p']['uniq_id']; ?>', '<?php echo $proj['p']['uniq_id']; ?>', '<?php echo $page; ?>', '0', '<?php echo rawurlencode($proj['p']['name']); ?>','',<?php echo $proj['p']['project_methodology_id']; ?>) <?php } ?>;"><?php echo $this->Format->shortLength(ucfirst($proj['p']['name']), 30); ?>
                    <?php if (isset($proj[0]['count'])) { ?>(<?php echo $proj['0']['count']; ?>)<?php } ?></a>
                <?php if ($key == 2) {
                    echo "<strong class='recent_used'>All Projects</strong>";
                } ?>
            <?php }
        } else {
            if ($popupid == 'projpopup_subtask') { ?>
                <a href="javascript:jsVoid();" <?php if ($page == 'mydashboard') { ?>data-proj-name="<?php echo $proj['p']['name']; ?>"
                        data-proj-id="<?php echo $proj['p']['uniq_id']; ?>" <?php } ?>
                    class="proj_lnks ttc <?php if ($page == 'mydashboard') { ?>proj_link_for_invite<?php } ?>"
                    onClick="updateAllProj('proj_<?php echo $proj['p']['uniq_id']; ?>', '<?php echo $proj['p']['uniq_id']; ?>', '<?php echo $page; ?>', '0', '<?php echo rawurlencode($proj['p']['name']); ?>','',<?php echo $proj['p']['project_methodology_id']; ?>)"><?php echo $this->Format->shortLength(ucfirst($proj['p']['name']), 30); ?>
                    <?php if (isset($proj[0]['count'])) { ?>(<?php echo $proj['0']['count']; ?>)<?php } ?></a>
            <?php } else { ?>
                <a href="javascript:jsVoid();" <?php if ($page == 'mydashboard') { ?>data-proj-name="<?php echo $proj['p']['name']; ?>"
                        data-proj-id="<?php echo $proj['p']['uniq_id']; ?>" <?php } ?>
                    class="proj_lnks ttc <?php if ($page == 'mydashboard') { ?>proj_link_for_invite<?php } ?>"
                    onClick="setProjectid('<?php echo $proj['p']['id']; ?>', '<?php echo rawurlencode($proj['p']['name']); ?>', '<?php echo $proj['p']['uniq_id']; ?>')"><?php echo $this->Format->shortLength(ucfirst($proj['p']['name']), 30); ?>
                    <?php if (isset($proj[0]['count'])) { ?>(<?php echo $proj['0']['count']; ?>)<?php } ?></a>
                <?php
            }
        }
    } ?>
    <?php if ($page != 'timer') { ?>
        <?php if ($limit != "all" && $countAll > 1) { ?>
            <?php if (isset($popupid) && $popupid == 'projpopup_log') { ?>
                <div id="showMenu_case_txt_log">
                    <a href="javascript:jsVoid();" class="proj_lnks more"
                        onClick="displayMenuProjects('<?php echo $page; ?>', 'all');"><?php echo __('more'); ?>...</a>
                </div>
                <span id="loaderMenu_case_log" style="display:none;">
                    <a href="javascript:jsVoid();"
                        style="text-decoration:none;color:#000000;padding:4px;cursor:wait">Loading...&nbsp;&nbsp;<img
                            src="<?php echo HTTP_IMAGES; ?>images/del.gif" width="16" height="16" alt="loading..."
                            title="loading..." /></a>
                </span>
            <?php } else { ?>
                <span id="loaderMenu_case" style="display:none;">
                    <a href="javascript:jsVoid();"
                        style="text-decoration:none;color:#000000;padding:4px;cursor:wait">Loading...&nbsp;&nbsp;<img
                            src="<?php echo HTTP_IMAGES; ?>images/del.gif" width="16" height="16" alt="loading..."
                            title="loading..." /></a>
                </span>
                <div id="showMenu_case_txt">
                    <a href="javascript:jsVoid();" class="proj_lnks more"
                        onClick="displayMenuProjects('<?php echo $page; ?>', 'all');"><?php echo __('more'); ?>...</a>
                </div>
            <?php } ?>
        <?php } ?>

        <!-- Add project option start -->
        <?php if ((SES_TYPE == 1 || SES_TYPE == 2) && isset($popupid) && $popupid != 'projpopup_log' && $popupid != 'projpopup_subtask') { ?>
            <?php if ($this->Format->isAllowed('Create Project', $roleAccess)) { ?>
                <div id="showMenu_case_txt">
                    <a class="show_all_opt_in_listonly"
                        style="<?php if ((DEFAULT_TASKVIEW == 'tasks' && $gp != 1) || ($page == 'special_mydashoard')) { ?>display:block;<?php } else { ?>display:none;<?php } ?>"
                        href="javascript:jsVoid();"
                        onClick="<?php if ($page == 'activity') { ?>CaseActivity('all', 'All'); <?php } elseif ($page == 'mydashboard') { ?>CaseDashboard('all', 'All'); <?php } elseif ($page == 'milestone') { ?>caseMilestone('all', 'All', 1); <?php } else { ?>updateAllProj('0', '0', '<?php echo $page; ?>', 'all', 'All') <?php } ?>;"><?php echo __('All Projects'); ?>
                        <?php if (isset($allPjCount)) { ?>(<?php echo $allPjCount; ?>) <?php } ?></a>
                </div>
                <div id="newprj_but">
                    <a id="newproject" class="proj_lnks col333" href="javascript:jsVoid();"
                        onclick="newProject('newproject', 'loaderprj');">+
                        <?php echo __('Create Project'); ?></a>
                </div>
                <a href="javascript:jsVoid()" id="loaderprj" style="text-decoration:none;cursor:wait;display:none;">
                    Loading...<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" width="16" height="16" alt="loading..."
                        title="loading..." />
                </a>
            <?php } ?>
        <?php } ?>
        <!-- Add project option end -->

    <?php } ?>
<?php } else { ?>
    <?php if ($page == 'timer') { ?>
        <option value=""><?php echo __('No projects available'); ?></option>
    <?php } ?>
<?php } ?>