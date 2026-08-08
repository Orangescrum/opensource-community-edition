<?php
/**
 * Combined user-manage content partial — grid view + list view + pagination.
 *
 * This element is rendered:
 *   (a) Inline by manage.php for the initial full-page load.
 *   (b) As the sole response body for AJAX filter/sort requests
 *       (UsersController renders this with disableAutoLayout).
 *
 * All required view variables are set by UsersController::manage().
 */
?>
<?php echo $this->element('user_manage_grid'); ?>

<?php echo $this->element('user_list_view'); ?>

<?php
// Pagination
if (!empty($caseCount)):
    $page_url = \Cake\Routing\Router::url(['controller' => 'Users', 'action' => 'manage', '?' => [
        'role'              => $this->request->getQuery('role'),
        'type'              => $this->request->getQuery('type'),
        'user_srch'         => $this->request->getQuery('user_srch'),
        'filter_role_id'    => ($filterRoleId ?? 0) ?: null,
        'filter_project_id' => ($filterProjectId ?? 0) ?: null,
        'page'              => '',
    ]]);
    echo $this->element('task_paginate', [
        'mode'       => 'php',
        'pgShLbl'    => $this->Format->pagingShowRecords($caseCount, $page_limit, $casePage),
        'csPage'     => $casePage,
        'page_limit' => $page_limit,
        'caseCount'  => $caseCount,
        'page_url'   => $page_url,
    ]);
endif;
?>
