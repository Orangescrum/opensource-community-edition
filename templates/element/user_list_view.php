<!-- ── List View Table ──────────────────────────────────────────── -->
<?php
// Sortable column helper. Renders a <th> containing a tiny form that
// POSTs sort+direction (plus existing filter/role/search state) back
// to /users/manage. Each header is its own form so the user can sort
// without JS and without polluting the URL.
//
// Click behaviour:
//   - first click on an unsorted column → asc
//   - click on currently-asc column     → desc
//   - click on currently-desc column    → asc
// Active column gets ▲/▼; inactive sortable columns show a faint ⇅.
$curSort      = $currentSort          ?? '';
$curDirection = $currentSortDirection ?? 'ASC';
// CSRF token — Cake's CSRF middleware rejects POSTs without it. Read
// from the request attribute (server-side, never exposed to JS).
$csrfTokenForSort = \Cake\Routing\Router::getRequest()->getAttribute('csrfToken');
// State to persist across the sort POST so we don't lose the user's
// current tab / search / filters when the form submits and the page
// re-renders.
$req           = \Cake\Routing\Router::getRequest();
$persistRole   = (string)$req->getQuery('role', '');
$persistType   = (string)$req->getQuery('type', '');
$persistSrch   = (string)$req->getQuery('user_srch', '');
$persistRoleId = (int)($filterRoleId ?? 0);
$persistPrjId  = (int)($filterProjectId ?? 0);

$sortHeader = function (string $label, string $sortKey)
    use ($curSort, $curDirection, $csrfTokenForSort,
         $persistRole, $persistType, $persistSrch,
         $persistRoleId, $persistPrjId) {
    $isActive  = ($curSort === $sortKey);
    $nextDir   = ($isActive && $curDirection === 'ASC') ? 'desc' : 'asc';
    $arrow     = !$isActive ? '<span class="usr-sort-arrow inactive">⇅</span>'
               : ($curDirection === 'ASC' ? '<span class="usr-sort-arrow active">▲</span>'
                                          : '<span class="usr-sort-arrow active">▼</span>');
    $action = \Cake\Routing\Router::url(['controller' => 'Users', 'action' => 'manage']);
    ob_start(); ?>
        <th class="usr-list-sortable<?= $isActive ? ' usr-list-sorted' : '' ?>">
            <form method="post" action="<?= h($action) ?>" class="usr-sort-form">
                <input type="hidden" name="_csrfToken" value="<?= h($csrfTokenForSort) ?>">
                <input type="hidden" name="sort" value="<?= h($sortKey) ?>">
                <input type="hidden" name="direction" value="<?= h($nextDir) ?>">
                <?php // Preserve user's current tab + search + filters so the
                      // post doesn't wipe them. Page intentionally omitted —
                      // a fresh sort resets to page 1 to avoid an
                      // out-of-range slice. ?>
                <?php if ($persistRole !== ''): ?>
                    <input type="hidden" name="role" value="<?= h($persistRole) ?>">
                <?php endif; ?>
                <?php if ($persistType !== ''): ?>
                    <input type="hidden" name="type" value="<?= h($persistType) ?>">
                <?php endif; ?>
                <?php if ($persistSrch !== ''): ?>
                    <input type="hidden" name="user_srch" value="<?= h($persistSrch) ?>">
                <?php endif; ?>
                <?php if ($persistRoleId  > 0): ?><input type="hidden" name="filter_role_id"    value="<?= $persistRoleId  ?>"><?php endif; ?>
                <?php if ($persistPrjId   > 0): ?><input type="hidden" name="filter_project_id" value="<?= $persistPrjId   ?>"><?php endif; ?>
                <button type="submit" class="usr-sort-link">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> <?= $arrow ?>
                </button>
            </form>
        </th>
    <?php return ob_get_clean();
};
?>
<div class="usr-list-view">
        <table class="usr-list-tbl">
            <thead>
                <tr>
                    <th></th>
                    <?= $sortHeader(__('User'),          'name') ?>
                    <?= $sortHeader(__('Role'),          'role') ?>
                    <?= $sortHeader(__('Email'),         'email') ?>
                    <?= $sortHeader(__('Last Activity'), 'last_activity') ?>
                    <?= $sortHeader(__('Created'),       'created') ?>
                    <th><?php echo __('Projects'); ?></th>
                </tr>
            </thead>
            <tbody>
    <?php if (!empty($userArr)): ?>
                <?php foreach ($userArr as $listUser):
                    if ($listUser['Roles']['role'] == 'Owner') {
                        $lColors = 'own_clr';
                        $lTypeName = __('Owner');
                    } elseif ($listUser['Roles']['role'] == 'Admin') {
                        $lColors = 'adm_clr';
                        $lTypeName = __('Admin');
                    } elseif ($listUser['Roles']['role'] == 'Guest') {
                        $lColors = 'cli_clr';
                        $lTypeName = __('Guest');
                    } else {
                        $lColors = 'usr_clr';
                        $lTypeName = $listUser['Roles']['role'] ?: __('User');
                    }
                    if ($listUser['CompanyUsers']['is_client'] == 1) {
                        $lColors = 'cli_clr';
                        $lTypeName = __('Client');
                    }
                    if ($listUser['CompanyUsers']['is_client'] == 1 && $listUser['CompanyUsers']['user_type'] == 2) {
                        $lColors = 'cli_clr';
                        $lTypeName = __('Admin/Client');
                    }
                    $lBgClr = $this->Format->getProfileBgColr($listUser['id']);
                ?>
                    <tr id="usr_lrow<?php echo $listUser['id']; ?>">
                        <!-- Actions -->
                        <td class="usr-list-actions">
                            <span class="dropdown">
                                <a class="dropdown-toggle active" data-toggle="dropdown" href="javascript:void(0);" data-target="#">
                                    <i class="material-icons">&#xE5D4;</i>
                                </a>
                                <ul class="dropdown-menu left0 new-dropdown">
                                    <?php echo $this->element('user_actions_menu_items', ['menuUser' => $listUser, 'idSuffix' => 'l']); ?>
                                </ul>
                            </span>
                        </td>
                        <!-- User avatar + name -->
                        <td style="white-space:nowrap;">
                            <div class="usr-list-avatar <?php echo $lBgClr; ?>" style="display:inline-flex;">
                                <?php if (trim($listUser['photo'] ?? '')): ?>
                                    <img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo $listUser['photo']; ?>&sizex=34&sizey=34&quality=100" width="34" height="34" />
                                <?php elseif (trim($listUser['name'] ?? '')): ?>
                                    <?php echo mb_strtoupper(mb_substr(trim($listUser['name']), 0, 1, 'utf-8'), 'utf-8'); ?>
                                <?php else: ?>
                                    <img src="<?php echo HTTP_ROOT; ?>img/images/user.png" />
                                <?php endif; ?>
                            </div>
                            <?php $listFullName = trim(($listUser['name'] ?? '') . ' ' . ($listUser['last_name'] ?? '')); ?>
                            <span class="usr-list-name" title="<?php echo htmlspecialchars($listFullName, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo $listFullName !== '' ? htmlspecialchars(ucfirst($listUser['name'] ?? '') . ' ' . ($listUser['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '&nbsp;'; ?>
                            </span>
                        </td>
                        <!-- Role badge -->
                        <td><span class="usr-cat-badge <?php echo $lColors; ?>"><?php echo $lTypeName; ?></span></td>
                        <!-- Email -->
                        <td><span class="usr-list-email" title="<?php echo h($listUser['email']); ?>"><?php echo h($listUser['email']); ?></span></td>
                        <!-- Last Activity -->
                        <td class="no-wrap">
                            <?php echo isset($listUser['latest_activity']) ? h($listUser['latest_activity']) : '<span class="muted">' . __('No activity yet') . '</span>'; ?>
                        </td>
                        <!-- Created -->
                        <td class="no-wrap">
                            <?php echo $listUser['created_on'] ?? ''; ?>
                        </td>
                        <!-- Projects -->
                        <td>
                            <span class="nm_prj ellipsis-view"
                                  title="<?php echo h($listUser['all_project_lst'] ?? ''); ?>"
                                  style="display:block;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?php echo trim($listUser['all_project'] ?? '') ? $listUser['all_project'] : '<span class="muted">N/A</span>'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
    <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding:40px 0;">
                        <h2 class="fnt_clr_rd" style="margin:0;">
                            <?php echo ($role == 'client') ? __('No clients found') : __('No users found') . '.'; ?>
                        </h2>
                    </td>
                </tr>
    <?php endif; ?>
            </tbody>
        </table>
</div>
<!-- ── /List View Table ─────────────────────────────────────────── -->
