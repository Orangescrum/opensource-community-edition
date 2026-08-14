<?php
/**
 * Project view switcher.
 *
 * Every entry here used to open a `dashboard#...` route belonging to the
 * AngularJS app. That app was removed from this edition and its partials went
 * with it, so each of these led to a page that requested a file returning 404
 * and then sat on its loading placeholders for ever (public issue #20 for
 * Kanban, public issue #13 for Time Log — the rest were the same fault waiting
 * to be reported).
 *
 * They now point at the pages this edition actually ships.
 */
?>
<span class="dropdown cmn_hover_menu_open">
    <a class="dropdown-toggle active main_page_menu_togl top_main_page_menu_togl" data-toggle="dropdown"
        href="javascript:void(0);" data-target="#">
        <i id="top_main_togl_nav" class="material-icons">&#xE5C3;</i>
    </a>
    <ul class="dropdown-menu main_page_menu_togl_ul">
        <li>
            <a id="overview_btn" class="" href="<?php echo HTTP_ROOT . 'task-overview'; ?>">
                <span title="<?php echo __('Overview'); ?>"><i
                        class="material-icons">&#xE417;</i><?php echo __('Overview'); ?></span>
            </a>
        </li>
        <li>
            <a id="lview_btn" class="" href="<?php echo HTTP_ROOT . 'task-views'; ?>">
                <span title="<?php echo __('List View'); ?>"><i
                        class="material-icons">&#xE896;</i><?php echo __('List'); ?></span>
            </a>
        </li>
        <?php if ($_SESSION['project_methodology'] != 'scrum') { ?>
            <?php if ($this->Format->isAllowed('View Kanban', $roleAccess)) { ?>
                <li>
                    <a id="kbview_btn" class="" href="<?php echo HTTP_ROOT . 'task-kanban'; ?>"><span
                            id="kbview_btn" class="" title="<?php echo __('Kanban View'); ?>"><i
                                class="material-icons">&#xE8F0;</i><?php echo $this->Format->displayKanbanOrBoard(); ?></span></a>
                </li>
            <?php } ?>
        <?php } ?>
        <?php if ($this->Format->isAllowed('View File', $roleAccess)) { ?>
            <li><a id="files_btn" class="" href="<?php echo HTTP_ROOT . 'easycases/files_overview'; ?>">
                    <span title="<?php echo __('Files'); ?>"><i
                            class="material-icons">&#xE226;</i><?php echo __('Files'); ?></span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a id="actvt_btn" class="" href="<?php echo HTTP_ROOT . 'easycases/recent_activities'; ?>">
                <span title="<?php echo __('Activities'); ?>"><i
                        class="material-icons">&#xE922;</i><?php echo __('Activities'); ?></span>
            </a>
        </li>
        <?php if ($this->Format->isAllowed('View Calendar', $roleAccess)) { ?>
            <li><a id="calendar_btn" class="" href="<?php echo HTTP_ROOT . 'task-calendar'; ?>">
                    <span title="<?php echo __('Calendar'); ?>"><i
                            class="material-icons">&#xE916;</i><?php echo __('Calendar'); ?></span>
                </a></li>
        <?php } ?>
        <li><a id="timelog_btn" class="" href="<?php echo HTTP_ROOT . 'log-times'; ?>">
                <span title="<?php echo __('Time Log'); ?>"><i
                        class="material-icons">&#xE192;</i><?php echo __('Time Log'); ?></span>
            </a></li>
    </ul>
</span>
