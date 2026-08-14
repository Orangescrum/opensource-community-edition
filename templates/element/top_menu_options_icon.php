<span class="dropdown cmn_hover_menu_open">
    <a class="dropdown-toggle active main_page_menu_togl top_main_page_menu_togl" data-toggle="dropdown"
        href="javascript:void(0);" data-target="#">
        <i id="top_main_togl_nav" class="material-icons">&#xE5C3;</i>
    </a>
    <ul class="dropdown-menu main_page_menu_togl_ul">
        <li>
            <a id="overview_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#overview'; ?>"
                onclick="return checkHashLoad('overview');">
                <span title="<?php echo __('Overview'); ?>"><i
                        class="material-icons">&#xE417;</i><?php echo __('Overview'); ?></span>
            </a>
        </li>
        <li>
            <a id="lview_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#tasks'; ?>"
                onclick="return checkHashLoad('tasks');">
                <span title="<?php echo __('List View'); ?>"><i
                        class="material-icons">&#xE896;</i><?php echo __('List'); ?></span>
            </a>
        </li>
        <?php if ($_SESSION['project_methodology'] != 'scrum') { ?>
            <?php
            $kanbanurl = '';
            $kanbanurl = DEFAULT_KANBANVIEW == 'kanban' ? 'kanban' : 'milestonelist';
            ?>
            <?php if ($this->Format->isAllowed('View Kanban', $roleAccess)) { ?>
                <li>
                    <a id="kbview_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#' . $kanbanurl; ?>"
                        onclick="return checkHashLoad('<?php echo $kanbanurl; ?>');"><span
                            id="kbview_btn" class="" title="<?php echo __('Kanban View'); ?>"><i
                                class="material-icons">&#xE8F0;</i><?php echo $this->Format->displayKanbanOrBoard();//__('Kanban'); ?></span></a>
                </li>
            <?php } ?>
        <?php } ?>
        <?php if ($this->Format->isAllowed('View File', $roleAccess)) { ?>
            <li><a id="files_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#files'; ?>"
                    onclick="return checkHashLoad('files');">
                    <span title="<?php echo __('Files'); ?>"><i
                            class="material-icons">&#xE226;</i><?php echo __('Files'); ?></span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a id="actvt_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#activities'; ?>"
                onclick="return checkHashLoad('activities');">
                <span title="<?php echo __('Activities'); ?>"><i
                        class="material-icons">&#xE922;</i><?php echo __('Activities'); ?></span>
            </a>
        </li>
        <li class="hidden">
            <a id="actvt_btns" class="" href="<?php echo HTTP_ROOT . 'dashboard#mentioned_list'; ?>"
                onclick="return checkHashLoad('mentioned_list');">
                <span title="<?php echo __('Mentions'); ?>"><i
                        class="material-icons">alternate_email</i><?php echo __('Mentions'); ?></span>
            </a>
        </li>
        <?php if ($this->Format->isAllowed('View Calendar', $roleAccess)) { ?>
            <li><a id="calendar_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#calendar'; ?>"
                    onclick="return calendarView('calendar');">
                    <span title="<?php echo __('Calendar'); ?>"><i
                            class="material-icons">&#xE916;</i><?php echo __('Calendar'); ?></span>
                </a></li>
        <?php } ?>
        <?php
        $timelogurl = '';
        // Time Log is a page of its own now; the AngularJS views it used to
        // switch between were removed from this edition (public issue #13).
        $timelogurl = 'timelog';
        ?>
        <li><a id="timelog_btn" class="" href="<?php echo HTTP_ROOT . 'dashboard#' . $timelogurl; ?>"
                onclick="return checkHashLoad('timelog');">
                <span title="<?php echo __('Time Log'); ?>"><i
                        class="material-icons">&#xE192;</i><?php echo __('Time Log'); ?></span>
            </a></li>
        <?php if (SES_TYPE < 3) { ?>
                    <?php } ?>
    </ul>
</span>