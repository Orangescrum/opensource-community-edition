<?php
/**
 * Users > Manage — KPI summary header (markup only).
 *
 * Rendered at the top of user_manage_grid so it re-renders with the current
 * filters on every full load AND every AJAX filter/search/pagination reload.
 * Counts come from $userKpis (UserService::getManageKpis). Styles live once in
 * Users/manage.php (this partial is re-inserted via .html() on AJAX, so it must
 * not carry its own <style>/<script>).
 *
 * Visual style mirrors the dashboard KPI cards (KpiCards.vue / .db-kpi).
 */
$k = $userKpis ?? ['total' => 0, 'active' => 0, 'pending' => 0, 'disabled' => 0, 'online' => 0];

$cards = [
    ['key' => 'total',    'label' => __('Total Users'),     'icon' => 'group',        'tone' => 'blue'],
    ['key' => 'active',   'label' => __('Active Users'),    'icon' => 'check_circle', 'tone' => 'green'],
    ['key' => 'pending',  'label' => __('Pending Invites'), 'icon' => 'mail',         'tone' => 'amber'],
    ['key' => 'disabled', 'label' => __('Disabled Users'),  'icon' => 'block',        'tone' => 'red'],
    ['key' => 'online',   'label' => __('Logged-In Users'), 'icon' => 'sensors',      'tone' => 'teal', 'tip' => __('Users who logged in within the last 24 hours.')],
];
?>
<div class="ukpi-grid" id="userKpiSummary">
    <?php foreach ($cards as $c) : ?>
        <div class="ukpi-card"<?php echo !empty($c['tip']) ? ' title="' . h($c['tip']) . '"' : ''; ?>>
            <div class="ukpi-body">
                <div class="ukpi-label"><?php echo h($c['label']); ?></div>
                <div class="ukpi-value" data-ukpi="<?php echo h($c['key']); ?>">
                    <?php echo number_format((int)($k[$c['key']] ?? 0)); ?>
                </div>
            </div>
            <span class="ukpi-icon ukpi-<?php echo h($c['tone']); ?>">
                <i class="material-icons"><?php echo h($c['icon']); ?></i>
            </span>
        </div>
    <?php endforeach; ?>
</div>
