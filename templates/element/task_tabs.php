<?php
/**
 * The task navigation tabs — single source of truth.
 *
 * Included by BOTH easycases_topbar.php (dashboard/list) and
 * task_views_topbar.php (the TaskViews Vue pages). Defining the tabs twice is
 * how the two surfaces drifted into showing different navigation.
 *
 * My Works, Subtask View, Kanban, Views, Calendar and Overview are Vue pages
 * (TaskViewsController). "List" stays the legacy dashboard task list, which
 * switches in place via JS hash routing when you are already on the dashboard.
 *
 * $tvPage = the active TaskViews page (index maps to 'views'); '' elsewhere.
 */
$onDashboard = (strtolower(CONTROLLER) === 'easycases');
$tvPage = (strtolower(CONTROLLER) === 'taskviews')
    ? (PAGE_NAME === 'index' ? 'views' : strtolower(PAGE_NAME))
    : '';
?>
<ul class="proj_stas_bar lft_tab_tasklist">
    <li class="Your-work-breadcrumb <?php echo $tvPage === 'myworks' ? 'active' : ''; ?>">
        <a href="<?php echo HTTP_ROOT; ?>task-myworks">
            <i class="material-icons">&#xF075;</i> <?php echo __('My Works'); ?>
        </a>
    </li>
    <?php // "List" is the TaskViews app, not the legacy dashboard list — the
          // legacy page renders a different navigation strip, so routing here
          // kept swapping the nav out mid-session. ?>
    <li class="tasklist_breadcrumb <?php echo $tvPage === 'views' ? 'active' : ''; ?>">
        <a href="<?php echo HTTP_ROOT; ?>task-views">
            <i class="material-icons">&#xE8EF;</i> <?php echo __('List'); ?>
        </a>
    </li>
    <?php if ($this->Format->isAllowed('View Milestones', $roleAccess)) { ?>
        <li id="top_subtask_li" class="taskgroup_breadcrumb <?php echo $tvPage === 'subtasks' ? 'active' : ''; ?>">
            <a href="<?php echo HTTP_ROOT; ?>task-subtasks">
                <i class="material-icons">&#xE065;</i> <?php echo __('Subtask View'); ?>
            </a>
        </li>
    <?php } ?>
    <?php if ($this->Format->isAllowed('View Kanban', $roleAccess)) { ?>
        <li class="kanban_breadcrumb <?php echo $tvPage === 'kanban' ? 'active' : ''; ?>" id="tour_kanban_view">
            <a href="<?php echo HTTP_ROOT; ?>task-kanban">
                <i class="material-icons">&#xE8F0;</i> <?php echo $this->Format->displayKanbanOrBoard(); ?>
            </a>
        </li>
    <?php } ?>
    <?php if ($this->Format->isAllowed('View Calendar', $roleAccess)) { ?>
        <li class="calendar_breadcrumb <?php echo $tvPage === 'calendar' ? 'active' : ''; ?>" id="tour_calendar_view">
            <a href="<?php echo HTTP_ROOT; ?>task-calendar">
                <i class="material-icons">&#xE916;</i> <?php echo __('Calendar'); ?>
            </a>
        </li>
    <?php } ?>
    <li class="overview_breadcrumb <?php echo $tvPage === 'overview' ? 'active' : ''; ?>">
        <a href="<?php echo HTTP_ROOT; ?>task-overview">
            <i class="material-icons">&#xE417;</i> <?php echo __('Overview'); ?>
        </a>
    </li>
</ul>
