<?php
/**
 * Task Views SPA host.
 *
 * The bundle is built from frontend/task-views (npm run build) into
 * webroot/dist/task-views.
 *
 * There is NO PHP tab strip on these pages — the app renders one single bar
 * (navigation + contextual actions) in Vue. The nav items are built here only
 * because the links need HTTP_ROOT, the role checks and the translations, none
 * of which the bundle has access to.
 */
$dist = HTTP_ROOT . 'dist/task-views/';
$v = ASSET_RELEASE;
$current = $page ?? 'views';

/*
 * The legacy dashboard task list is deliberately NOT a tab here: it renders the
 * old PHP strip, so landing on it swapped the navigation out from under you.
 * The Views page is the list, so it owns the task tabs. It is named "Tasks"
 * rather than "List" because "List" now names one of its three view modes.
 */
$nav = [
    ['key' => 'myworks', 'label' => __('My Works'), 'url' => HTTP_ROOT . 'task-myworks', 'icon' => 'mdi-account-check-outline'],
    ['key' => 'views', 'label' => __('Tasks'), 'url' => HTTP_ROOT . 'task-views', 'icon' => 'mdi-format-list-bulleted'],
];

if ($this->Format->isAllowed('View Milestones', $roleAccess)) {
    $nav[] = ['key' => 'subtasks', 'label' => __('Subtask View'), 'url' => HTTP_ROOT . 'task-subtasks', 'icon' => 'mdi-file-tree-outline'];
}

if ($this->Format->isAllowed('View Kanban', $roleAccess)) {
    $nav[] = ['key' => 'kanban', 'label' => strip_tags($this->Format->displayKanbanOrBoard()), 'url' => HTTP_ROOT . 'task-kanban', 'icon' => 'mdi-view-column-outline'];
}

if ($this->Format->isAllowed('View Calendar', $roleAccess)) {
    $nav[] = ['key' => 'calendar', 'label' => __('Calendar'), 'url' => HTTP_ROOT . 'task-calendar', 'icon' => 'mdi-calendar-blank-outline'];
}

$nav[] = ['key' => 'overview', 'label' => __('Overview'), 'url' => HTTP_ROOT . 'task-overview', 'icon' => 'mdi-chart-box-outline'];

// The old Angular "#/tasks" list is retired in the Community Edition; the tabs
// above are the task UI. No "Classic view" link — it only bounced users to a
// dead route.
$legacyList = null;
?>

<div class="task-views-host">
    <div id="taskViewsApp"></div>
</div>

<script>
    window.TASK_VIEWS_CONFIG = {
        csrfToken: "<?php echo h($this->request->getAttribute('csrfToken') ?: $this->request->getCookie(env('CSRF_COOKIE_NAME', 'csrfToken'))); ?>",
        baseUrl: "<?php echo HTTP_ROOT; ?>",
        page: "<?php echo h($current); ?>",
        preferences: <?php echo json_encode((object)($preferences ?? []), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        nav: <?php echo json_encode($nav, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        legacyList: <?php echo json_encode($legacyList, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
    };
</script>

<link rel="stylesheet" href="<?php echo $dist; ?>task-views-vuetify.css?v=<?php echo $v; ?>">
<link rel="stylesheet" href="<?php echo $dist; ?>task-views-main.css?v=<?php echo $v; ?>">
<script type="module" src="<?php echo $dist; ?>task-views-vue.js?v=<?php echo $v; ?>"></script>
<script type="module" src="<?php echo $dist; ?>task-views-vuetify.js?v=<?php echo $v; ?>"></script>
<script type="module" src="<?php echo $dist; ?>task-views.js?v=<?php echo $v; ?>"></script>

<style>
    /*
     * Content already sits inside .rht_content_cmn > .wrapper > .wrapper-body >
     * .slide_rht_con, and .rht_content_cmn supplies the sidebar offset. This
     * wrapper stays plain so that offset is not applied twice.
     */
    .task-views-host {
        width: 100%;
        padding: 0;
        background: #fff;
    }

    .task-views-host #taskViewsApp {
        width: 100%;
    }

    /*
     * TOP SPACING. The navbar is fixed at 80px. .layout-fixer reserves 125px
     * and the wrapper adds 90-142px more — both sized for the dashboard's
     * offset task bar, which this page does not render.
     *
     * The clearance is trimmed on .layout-fixer itself rather than moved to a
     * padding on .rht_content_cmn. The Create Task popup
     * (.create-task-container) is `position:relative`, i.e. in normal flow
     * inside .rht_content_cmn — so it inherits whatever sits above it. Zeroing
     * the fixer and re-adding the space as padding was a net -45px and pulled
     * the popup up underneath the navbar.
     */
    body.page-taskviews .layout-fixer {
        height: 80px !important; /* == .custom-navbar.nav_inr_menu height */
    }

    body.page-taskviews .rht_content_cmn.task_lis_page .wrapper {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    body.page-taskviews .slide_rht_con {
        margin-top: 0;
    }

    /*
     * The Create Task popup carries margin:-100px (custom.css) to sit under the
     * dashboard's taller header. These pages do not render that header, so the
     * negative pull lifted the popup above the navbar and against the browser
     * chrome.
     *
     * It is also an ordinary block in the page flow while its backdrop
     * (.crttask_overlay) is fixed, so scrolling the page slid the form up under
     * the fixed navbar, where it could not be read or reached (public issue #9).
     * Anchor it to the viewport below the navbar and let it scroll inside
     * itself. 96px is where it already sat, so the popup looks unchanged until
     * you scroll.
     */
    body.page-taskviews .create-task-container {
        position: fixed;
        top: 96px;
        left: 50%;
        transform: translateX(-50%);
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        margin: 0 !important;
        z-index: 1000;
    }

    /*
     * The close button hangs 45px outside the card, which the scroll box above
     * would cut off. Bring it inside — and darken it, because outside it sat on
     * the dimmed backdrop and inside it sits on white.
     */
    body.page-taskviews .crt_tsk .crt_popup_close {
        right: 4px;
        top: 4px;
    }

    /*
     * Show/Hide sits in the same top-right corner, so moving the close button
     * inside put one on top of the other (public issue #23 - a regression from
     * the fix above). Reserve the close button's width plus a gap.
     */
    body.page-taskviews .crt_tsk #showhide_task_conf {
        margin-right: 48px;
    }

    body.page-taskviews .crt_tsk .crt_popup_close .close .material-icons {
        color: #666;
    }

    body.page-taskviews .crt_tsk .crt_popup_close .close:hover .material-icons {
        color: #d32f2f;
    }
</style>
