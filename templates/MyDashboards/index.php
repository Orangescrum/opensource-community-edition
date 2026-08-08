<?php
/**
 * Dashboard SPA host.
 *
 * Bundle is built from templates/element/dashboard (npm run build) into
 * webroot/js/dashboard. Permission flags come from MyDashboardsController so a
 * widget that renders always has access to its data endpoint.
 */
$dist = HTTP_ROOT . 'js/dashboard/';
$v = ASSET_RELEASE;
?>

<div class="dashboard-host">
    <div id="dashboardApp"></div>
</div>

<script>
    window.DASHBOARD_CONFIG = {
        csrfToken: "<?php echo h($this->request->getAttribute('csrfToken') ?: $this->request->getCookie(env('CSRF_COOKIE_NAME', 'csrfToken'))); ?>",
        baseUrl: "<?php echo HTTP_ROOT; ?>",
        permissions: <?php echo json_encode($dashboardPermissions ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
    };
</script>

<link rel="stylesheet" href="<?php echo $dist; ?>dashboard-app.css?v=<?php echo $v; ?>">
<script type="module" src="<?php echo $dist; ?>dashboard-app.js?v=<?php echo $v; ?>"></script>

<style>
    /*
     * Content already sits inside .rht_content_cmn > .wrapper > .wrapper-body >
     * .slide_rht_con, and .rht_content_cmn supplies the sidebar offset. This
     * wrapper stays plain so that offset is not applied twice.
     */
    .dashboard-host {
        width: 100%;
        padding: 0;
        background: #fff;
    }

    .dashboard-host #dashboardApp {
        width: 100%;
    }

    body.page-mydashboards .slide_rht_con {
        margin-top: 0;
    }

    /*
     * The legacy shell scrolls inside .wrapper, whose height is sized for the
     * task list's offset filter bar. This page trims .layout-fixer to the
     * navbar height, so that calculation leaves the wrapper short of the
     * footer and the page background shows through as a grey band.
     * The wrapper is the scroll container, so it must start *below* the fixed
     * 50px navbar — otherwise scrolled content slides underneath it — and run
     * to the bottom of the viewport so the scrollbar is flush with the window
     * edge rather than stopping short.
     */
    body.page-mydashboards .rht_content_cmn .wrapper {
        margin-top: 50px !important; /* == .custom-navbar.nav_inr_menu height */
        height: calc(100vh - 50px) !important;
        max-height: none !important;
    }

    /*
     * The global footer hosts task-list counters (#csTotalHours,
     * #projectaccess) that this page never populates, so it is 36px of empty
     * chrome that also truncates the scroll track.
     */
    body.page-mydashboards .sticky_footer {
        display: none !important;
    }

    body.page-mydashboards .wrapper-body {
        min-height: 0;
    }
</style>
