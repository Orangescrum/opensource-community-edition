<?php
/**
 * Time Log — list of entries.
 *
 * The columns match the CSV and PDF exports, which read the same query, so a
 * reader can check on screen what they are about to download.
 */
$rows = $caseDetail ?? [];
$pageRows = count($rows);

$page = max(1, intval($page ?? 1));
$pageCount = intval($pageCount ?? 1);
$pageLimit = max(1, intval($pageLimit ?? 50));
$pageLimitOptions = $pageLimitOptions ?? [$pageLimit];
$firstRow = $caseCount ? (($page - 1) * $pageLimit) + 1 : 0;
$lastRow = min($page * $pageLimit, intval($caseCount));

$loggedHours = intval($total_billable_hours) + intval($total_non_billable_hours);

$canEdit = $this->Format->isAllowed('Edit Timelog Entry', $roleAccess);
$canDelete = $this->Format->isAllowed('Delete Timelog Entry', $roleAccess);
$showActions = $canEdit || $canDelete;

/*
 * The page is cross-project, but a Project Name column that repeats one value
 * on every row only steals width from Action. When the rows on this page carry
 * a single project the name is shown once, beside the heading, instead.
 */
$projectsOnPage = [];
if (($projFil ?? '') === 'all') {
    foreach ($rows as $row) {
        $name = trim((string)($row['LogTime']['prj_name'] ?? ''));
        if ($name !== '') {
            $projectsOnPage[$name] = true;
        }
    }
}
$projectsOnPage = array_keys($projectsOnPage);
$showProject = count($projectsOnPage) > 1;
$onlyProject = count($projectsOnPage) === 1 ? $projectsOnPage[0] : '';

$query = $this->getRequest()->getQueryParams();

$pageUrl = function (int $target) use ($query) {
    $query['page'] = $target;

    return $this->Url->build(['action' => 'index', '?' => $query]);
};

$limitUrl = function (int $target) use ($query) {
    unset($query['page']);
    $query['limit'] = $target;

    return $this->Url->build(['action' => 'index', '?' => $query]);
};

/*
 * A header link flips the direction when it is already the sorted column, and
 * otherwise starts on the direction that reads most usefully for that column:
 * newest first for dates and largest first for hours.
 */
$sortUrl = function (string $key) use ($query, $sortKey, $sortDirection) {
    $descFirst = in_array($key, ['date', 'hours'], true);
    if ($key === $sortKey) {
        $next = $sortDirection === 'ASC' ? 'desc' : 'asc';
    } else {
        $next = $descFirst ? 'desc' : 'asc';
    }
    unset($query['page']);
    $query['sort'] = $key;
    $query['direction'] = $next;

    return $this->Url->build(['action' => 'index', '?' => $query]);
};

$sortableColumns = $sortableColumns ?? [];

// Header cell: a sort link for the sortable columns, plain text otherwise.
$th = function (string $label, ?string $key = null, string $class = '') use ($sortUrl, $sortKey, $sortDirection, $sortableColumns) {
    $classes = trim('tlog-th ' . $class);
    if ($key === null || !in_array($key, $sortableColumns, true)) {
        printf('<th scope="col" class="%s">%s</th>', h($classes), h($label));

        return;
    }
    $isActive = $key === $sortKey;
    $arrow = $isActive ? ($sortDirection === 'ASC' ? 'arrow_upward' : 'arrow_downward') : 'sort';
    // Url::build() already escapes the query separator for HTML output; running
    // the result through h() as well turns &amp; into &amp;amp;, and the second
    // parameter arrives named "amp;direction".
    printf(
        '<th scope="col" class="%s%s" aria-sort="%s"><a href="%s">%s<i class="material-icons tlog-sort" aria-hidden="true">%s</i></a></th>',
        h($classes),
        $isActive ? ' is-sorted' : '',
        $isActive ? ($sortDirection === 'ASC' ? 'ascending' : 'descending') : 'none',
        $sortUrl($key),
        h($label),
        h($arrow)
    );
};

$hrs = fn($seconds) => $this->Format->format_time_hr_min(intval($seconds)) ?: '0 hrs';

// Icon-only state cell. The Material Icons ligature text is the accessible
// name, so a bare <i> is read out as "check box"; the icon is hidden from
// assistive technology and the state is given as text instead.
$stateCell = function (bool $on, string $yes, string $no) {
    printf(
        '<td class="text-center" title="%s"><i class="material-icons %s" aria-hidden="true">%s</i><span class="tlog-sr">%s</span></td>',
        h($on ? $yes : $no),
        $on ? 'tlog-yes' : 'tlog-no',
        $on ? 'check_box' : 'clear',
        h($on ? $yes : $no)
    );
};

$pagerNav = function () use ($page, $pageCount, $pageUrl) {
    if ($pageCount <= 1) {
        return;
    }
    ?>
    <nav class="tlog-pager__nav" aria-label="<?php echo __('Time log pages'); ?>">
        <?php if ($page > 1) { ?>
            <a href="<?php echo $pageUrl(1); ?>"><?php echo __('First'); ?></a>
            <a href="<?php echo $pageUrl($page - 1); ?>"><?php echo __('Previous'); ?></a>
        <?php } else { ?>
            <span class="is-disabled"><?php echo __('First'); ?></span>
            <span class="is-disabled"><?php echo __('Previous'); ?></span>
        <?php } ?>

        <span class="tlog-pager__page"><?php echo __('Page {0} of {1}', $page, $pageCount); ?></span>

        <?php if ($page < $pageCount) { ?>
            <a href="<?php echo $pageUrl($page + 1); ?>"><?php echo __('Next'); ?></a>
            <a href="<?php echo $pageUrl($pageCount); ?>"><?php echo __('Last'); ?></a>
        <?php } else { ?>
            <span class="is-disabled"><?php echo __('Next'); ?></span>
            <span class="is-disabled"><?php echo __('Last'); ?></span>
        <?php } ?>
    </nav>
    <?php
};
?>

<?php
/*
 * No rht_content_cmn / wrapper / wrapper-body / slide_rht_con here: the layout
 * already wraps the view in all four (templates/element/maincontent_inner.php).
 * Repeating them applied .rht_content_cmn's 240px sidebar padding a second
 * time, which indented the whole page and pushed the right-hand columns out of
 * sight.
 */
?>
<div class="tlog_list_page">

    <div class="tlog-head">
        <h2 class="tlog-head__title"><?php echo __('Time Logs'); ?></h2>
        <?php if ($onlyProject !== '') { ?>
            <span class="tlog-head__project" title="<?php echo h($onlyProject); ?>"><?php echo h($onlyProject); ?></span>
        <?php } ?>

        <ul class="tlog-summary">
            <li>
                <span class="tlog-summary__label"><?php echo __('Estimated'); ?>:</span>
                <strong><?php echo h($hrs($total_estimated_hours)); ?></strong>
            </li>
            <li>
                <span class="tlog-summary__label"><?php echo __('Logged'); ?>:</span>
                <strong><?php echo h($hrs($loggedHours)); ?></strong>
            </li>
            <li>
                <span class="tlog-summary__label"><?php echo __('Billable'); ?>:</span>
                <strong><?php echo h($hrs($total_billable_hours)); ?></strong>
            </li>
            <li>
                <span class="tlog-summary__label"><?php echo __('Non-Billable'); ?>:</span>
                <strong><?php echo h($hrs($total_non_billable_hours)); ?></strong>
            </li>
        </ul>

        <div class="tlog-head__actions">
            <a class="tlog-iconbtn" href="<?php echo $this->Url->build(['action' => 'downloadPdfTimelog', '?' => ['projuniqid' => 'all', 'date' => 'alldates']]); ?>"
               title="<?php echo __('Download as PDF'); ?>" aria-label="<?php echo __('Download as PDF'); ?>">
                <i class="material-icons" aria-hidden="true">picture_as_pdf</i>
            </a>
            <button type="button" class="tlog-iconbtn" id="tlogExportBtn"
                    title="<?php echo __('Export as CSV'); ?>" aria-label="<?php echo __('Export as CSV'); ?>">
                <i class="material-icons" aria-hidden="true">import_export</i>
            </button>
        </div>
    </div>

    <?php if (empty($rows)) { ?>
        <div class="tlog-empty">
            <p class="tlog-empty__title"><?php echo __('No Time Logs found'); ?></p>
            <p class="tlog-empty__hint"><?php echo __('Hours are logged against a task. Open a task and add the time there, and every entry across all of your projects is listed here.'); ?></p>
        </div>
    <?php } else { ?>
        <?php if ($canEdit) { ?>
            <div class="tlog-bulk" id="tlogBulk" hidden>
                <span class="tlog-bulk__count" role="status"></span>
                <button type="button" class="tlog-bulk__btn" data-billable="1"><?php echo __('Mark billable'); ?></button>
                <button type="button" class="tlog-bulk__btn" data-billable="0"><?php echo __('Mark non-billable'); ?></button>
            </div>
        <?php } ?>

        <?php
        /*
         * The range and the page controls are repeated here, above the table.
         * Fifty rows are taller than any window, so a pager only at the foot of
         * the table gave no sign on load that a second page existed.
         */
        ?>
        <div class="tlog-toolbar">
            <span class="tlog-toolbar__range">
                <?php echo __('Showing {0}–{1} of {2}', $firstRow, $lastRow, intval($caseCount)); ?>
            </span>

            <?php if (intval($caseCount) > min($pageLimitOptions)) { ?>
                <span class="tlog-pagesize">
                    <span class="tlog-pagesize__label"><?php echo __('Rows per page'); ?></span>
                    <?php foreach ($pageLimitOptions as $option) { ?>
                        <?php if ((int)$option === $pageLimit) { ?>
                            <span class="is-current" aria-current="true"><?php echo h($option); ?></span>
                        <?php } else { ?>
                            <a href="<?php echo $limitUrl((int)$option); ?>"><?php echo h($option); ?></a>
                        <?php } ?>
                    <?php } ?>
                </span>
            <?php } ?>

            <?php $pagerNav(); ?>
        </div>

        <div class="tlog-tablewrap">
            <table class="tlog-table">
                <thead>
                    <tr>
                        <?php if ($canEdit) { ?>
                            <th scope="col" class="tlog-th tlog-th--check">
                                <input type="checkbox" id="tlogCheckAll"
                                       aria-label="<?php echo __('Select the {0} entries on this page', $pageRows); ?>">
                            </th>
                        <?php } ?>
                        <?php $th(__('Date'), 'date'); ?>
                        <?php $th(__('Resource Name'), 'resource'); ?>
                        <?php $th(__('Task#'), 'task_no'); ?>
                        <?php $th(__('Task Title'), 'task_title'); ?>
                        <?php if ($showProject) { ?>
                            <?php $th(__('Project Name')); ?>
                        <?php } ?>
                        <?php $th(__('Logged Hours'), 'hours'); ?>
                        <?php $th(__('Note'), 'note'); ?>
                        <?php $th(__('Start'), 'start'); ?>
                        <?php $th(__('End'), 'end'); ?>
                        <?php $th(__('Break')); ?>
                        <?php $th(__('Billable'), null, 'text-center'); ?>
                        <?php $th(__('Timer'), null, 'text-center'); ?>
                        <?php if ($showActions) { ?>
                            <?php $th(__('Action'), null, 'text-center tlog-th--action'); ?>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $v) {
                        $logId = $v['LogTime']['log_id'] ?? '';
                        $note = trim(strip_tags((string)($v['LogTime']['description'] ?? '')));
                        $title = trim((string)($v['task_name'] ?? ''));
                        $break = intval($v['LogTime']['break_time'] ?? 0);
                        $hours = intval($v['LogTime']['total_hours'] ?? 0);
                        $prjName = trim((string)($v['LogTime']['prj_name'] ?? ''));
                        ?>
                        <tr>
                            <?php if ($canEdit) { ?>
                                <td class="tlog-td--check">
                                    <input type="checkbox" class="tlogRow" value="<?php echo h($logId); ?>"
                                           aria-label="<?php echo __('Select entry'); ?>">
                                </td>
                            <?php } ?>
                            <td class="tlog-nowrap"><?php echo !empty($v['start_datetime_v1']) ? h(date('M d, Y', strtotime($v['start_datetime_v1']))) : '---'; ?></td>
                            <td><?php echo h(trim(($v['user_name'] ?? '') . ' ' . ($v['user_last_name'] ?? ''))) ?: '---'; ?></td>
                            <td><?php echo !empty($v['task_no']) ? h($v['task_no']) : '---'; ?></td>
                            <td class="tlog-truncate" title="<?php echo h($title); ?>"><?php echo $title !== '' ? h($title) : '---'; ?></td>
                            <?php if ($showProject) { ?>
                                <td class="tlog-truncate" title="<?php echo h($prjName); ?>"><?php echo h($prjName) ?: '---'; ?></td>
                            <?php } ?>
                            <?php
                            /*
                             * h:mm in the cell, "4 hrs 30 mins" in its title.
                             * The long form was the widest thing in the table
                             * and set the width of every column after it.
                             */
                            ?>
                            <td class="tlog-nowrap tlog-hours" title="<?php echo $hours ? h($this->Format->format_time_hr_min($hours)) : ''; ?>"><?php echo $hours ? h($this->Format->format_time_hr_min($hours, 'hrmin')) : '---'; ?></td>
                            <td class="tlog-truncate tlog-truncate--note" title="<?php echo h($note); ?>"><?php echo $note !== '' ? h($note) : '---'; ?></td>
                            <td class="tlog-nowrap"><?php echo (!empty($v['LogTime']['start_time']) && $v['LogTime']['start_time'] != '00:00:00') ? h($v['LogTime']['start_time']) : '---'; ?></td>
                            <td class="tlog-nowrap"><?php echo (!empty($v['LogTime']['end_time']) && $v['LogTime']['end_time'] != '00:00:00') ? h($v['LogTime']['end_time']) : '---'; ?></td>
                            <td class="tlog-nowrap"><?php echo $break ? h($this->Format->format_time_hr_min($break)) : '---'; ?></td>
                            <?php $stateCell(!empty($v['LogTime']['is_billable']), __('Billable'), __('Non-billable')); ?>
                            <?php $stateCell(!empty($v['LogTime']['is_from_timer']), __('Recorded with the timer'), __('Entered by hand')); ?>
                            <?php if ($showActions) { ?>
                                <td class="text-center tlog-td--action action_td" data-logid="<?php echo h($logId); ?>">
                                    <?php if ($canEdit) { ?>
                                        <a class="anchor edit_time_log tlog-action" href="javascript:void(0);" role="button"
                                           data-task-id="<?php echo h($v['LogTime']['task_id'] ?? ''); ?>"
                                           data-prj-name="<?php echo h(rawurlencode($prjName)); ?>"
                                           title="<?php echo __('Edit'); ?>" aria-label="<?php echo __('Edit this entry'); ?>">
                                            <i class="material-icons" aria-hidden="true">mode_edit</i>
                                        </a>
                                    <?php } ?>
                                    <?php if ($canDelete) { ?>
                                        <a class="anchor tlog-delete tlog-action" href="javascript:void(0);" role="button"
                                           data-logid="<?php echo h($logId); ?>"
                                           title="<?php echo __('Delete'); ?>" aria-label="<?php echo __('Delete this entry'); ?>">
                                            <i class="material-icons tlog-action--danger" aria-hidden="true">delete_outline</i>
                                        </a>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="tlog-pager">
            <span class="tlog-pager__range">
                <?php echo __('Showing {0}–{1} of {2}', $firstRow, $lastRow, intval($caseCount)); ?>
            </span>
            <?php $pagerNav(); ?>
        </div>
    <?php } ?>

</div>

<script>
    (function () {
        var root = document.querySelector('.tlog_list_page');
        if (!root) {
            return;
        }

        // The bulk and delete endpoints are the same ones the task-detail time
        // log uses. Its handlers live in dashboard_v1.js, which this page does
        // not load, so the calls are made here.
        // Rendered from the request attribute. The csrfToken cookie is
        // httpOnly, so script cannot read it, and this page has no form whose
        // hidden _csrfToken field could be borrowed.
        var csrf = <?php echo json_encode((string)$this->getRequest()->getAttribute('csrfToken')); ?>;

        function post(url, data, done) {
            var body = new URLSearchParams();
            Object.keys(data).forEach(function (k) {
                var v = data[k];
                if (Array.isArray(v)) {
                    v.forEach(function (item) { body.append(k + '[]', item); });
                } else {
                    body.append(k, v);
                }
            });
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-Token': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            }).then(function (r) { return r.json().catch(function () { return {}; }); })
              .then(done)
              .catch(function () { done({}); });
        }

        var checkAll = root.querySelector('#tlogCheckAll');
        var bulk = root.querySelector('#tlogBulk');

        function rows() {
            return Array.prototype.slice.call(root.querySelectorAll('.tlogRow'));
        }

        function selected() {
            return rows().filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
        }

        function syncBulk() {
            if (!bulk) {
                return;
            }
            var ids = selected();
            bulk.hidden = ids.length === 0;
            var label = bulk.querySelector('.tlog-bulk__count');
            if (label) {
                // Selection and the billable update reach the rows on this page
                // and no others, so the count says so whenever more entries
                // match the filter than are shown.
                if (ids.length && ids.length === rows().length) {
                    label.textContent = <?php echo json_encode(
                        intval($caseCount) > $pageRows
                            ? __(
                                'All {0} entries on this page are selected. {1} more match the filter and are not.',
                                $pageRows,
                                intval($caseCount) - $pageRows
                            )
                            : __('All {0} entries are selected.', $pageRows)
                    ); ?>;
                } else {
                    label.textContent = ids.length === 1 ?
                        <?php echo json_encode(__('1 entry selected')); ?> :
                        ids.length + ' ' + <?php echo json_encode(__('entries selected')); ?>;
                }
            }
            if (checkAll) {
                checkAll.checked = ids.length > 0 && ids.length === rows().length;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                rows().forEach(function (c) { c.checked = checkAll.checked; });
                syncBulk();
            });
        }

        root.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('tlogRow')) {
                syncBulk();
            }
        });

        if (bulk) {
            bulk.addEventListener('click', function (e) {
                var btn = e.target.closest('.tlog-bulk__btn');
                if (!btn) {
                    return;
                }
                var ids = selected();
                if (!ids.length) {
                    return;
                }
                btn.disabled = true;
                post('<?php echo $this->Url->build(['action' => 'updateBillableType']); ?>',
                    { log_id: ids, billable_type: btn.getAttribute('data-billable') },
                    function () { window.location.reload(); });
            });
        }

        root.addEventListener('click', function (e) {
            var del = e.target.closest('.tlog-delete');
            if (!del) {
                return;
            }
            e.preventDefault();
            if (!window.confirm(<?php echo json_encode(__('Are you sure to delete the timelog?')); ?>)) {
                return;
            }
            post('<?php echo $this->Url->build(['action' => 'deleteTimelog']); ?>',
                { logid: del.getAttribute('data-logid'), projuniqid: 'all' },
                function () { window.location.reload(); });
        });

        // The row actions are anchors carrying role="button", so the space bar
        // has to activate them the way it does a real button.
        root.addEventListener('keydown', function (e) {
            if (e.key !== ' ' && e.key !== 'Spacebar') {
                return;
            }
            var action = e.target.closest ? e.target.closest('.tlog-action') : null;
            if (!action) {
                return;
            }
            e.preventDefault();
            action.click();
        });

        var exportBtn = root.querySelector('#tlogExportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                // escape => false: Url::build() escapes & for HTML by default,
                // and nothing decodes it inside a JS string, so the query
                // reached the server as "amp;checkedFields" and the export
                // died on an empty column name.
                window.location.href = <?php echo json_encode($this->Url->build([
                    'action' => 'exportCsvTimelog',
                    '?' => [
                        'projuniqid' => 'all',
                        'date' => 'alldates',
                        'checkedFields' => 'date,usr_name,prj_name,task_no,task_title,hours,note,start,end,break,billable',
                        'dt_format' => 'd/m/Y',
                    ],
                ], ['escape' => false])); ?>;
            });
        }
    }());
</script>

<style>
    .tlog_list_page {
        background: #fff;
        padding: 20px;
    }

    .tlog-sr {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
        border: 0;
    }

    .tlog-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e6e6e6;
    }

    .tlog-head__title {
        margin: 0;
        font-size: 20px;
        flex: 0 0 auto;
    }

    /* Shown in place of a Project Name column that would repeat one value. */
    .tlog-head__project {
        flex: 0 1 auto;
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 2px 9px;
        border: 1px solid #e0e0e0;
        border-radius: 11px;
        background: #f6f6f6;
        font-size: 12px;
        color: #555;
    }

    .tlog-head__actions {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    .tlog-iconbtn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid #dcdcdc;
        border-radius: 4px;
        background: #fff;
        color: #666;
        cursor: pointer;
    }

    .tlog-iconbtn:hover {
        border-color: #b9b9b9;
        color: #222;
    }

    .tlog-iconbtn .material-icons {
        font-size: 18px;
    }

    .tlog-iconbtn:focus-visible,
    .tlog-action:focus-visible,
    .tlog-bulk__btn:focus-visible,
    .tlog-table th a:focus-visible,
    .tlog-toolbar a:focus-visible,
    .tlog-pager a:focus-visible,
    .tlog_list_page input[type="checkbox"]:focus-visible {
        outline: 2px solid #2489b3;
        outline-offset: 2px;
    }

    /* Summary sits inline with the title, pushed away from the buttons. */
    .tlog-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin: 0 0 0 auto;
        padding: 0;
        list-style: none;
        font-size: 13px;
    }

    .tlog-summary li {
        display: flex;
        align-items: baseline;
        gap: 5px;
        line-height: 1.35;
    }

    .tlog-summary__label {
        color: #777;
    }

    .tlog-summary strong {
        color: #222;
    }

    .tlog-bulk {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        font-size: 13px;
        color: #555;
    }

    /* display:flex above would otherwise beat the hidden attribute. */
    .tlog-bulk[hidden] {
        display: none;
    }

    .tlog-bulk__btn {
        border: 1px solid #dcdcdc;
        background: #fff;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 12px;
        color: #2489b3;
        cursor: pointer;
    }

    .tlog-bulk__btn:hover {
        border-color: #2489b3;
    }

    .tlog-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px 18px;
        margin-top: 14px;
        font-size: 13px;
        color: #777;
    }

    .tlog-toolbar__range {
        color: #444;
    }

    .tlog-pagesize {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    .tlog-pagesize__label {
        color: #999;
    }

    .tlog-pagesize a {
        color: #2489b3;
        text-decoration: none;
    }

    .tlog-pagesize a:hover {
        text-decoration: underline;
    }

    .tlog-pagesize .is-current {
        color: #222;
        font-weight: 600;
    }

    /* The table is wide and fifty rows are taller than any window. The body
       scrolls inside this box, both ways, so the header row stays put and the
       pager below it is on screen when the page loads. */
    .tlog-tablewrap {
        overflow: auto;
        /* The reserve covers the head, both pager rows and the bulk bar, which
           appears on selection: without room for it the foot pager was pushed
           just below the fold. */
        max-height: calc(100vh - 372px);
        min-height: 220px;
        margin-top: 10px;
        border: 1px solid #ececec;
    }

    /* Not border-collapse: collapse. A collapsed border is owned by the table
       rather than the cell, and it is dropped from a sticky header or a sticky
       column as soon as the box is scrolled. */
    .tlog-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .tlog-table th,
    .tlog-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        text-align: left;
        vertical-align: middle;
        background: #fff;
    }

    .tlog-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #777;
        background: #fafafa;
        border-bottom: 1px solid #e0e0e0;
    }

    /* A two-word heading wraps onto a second line rather than setting the
       width of its whole column. "Resource Name" and "Logged Hours" were both
       wider than any value under them. */
    .tlog-table thead th a {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 2px;
        color: inherit;
        text-decoration: none;
    }

    .tlog-table th a:hover {
        color: #222;
    }

    .tlog-table th.is-sorted a {
        color: #222;
    }

    .tlog-sort {
        font-size: 14px;
        opacity: 0.45;
    }

    .tlog-table th.is-sorted .tlog-sort {
        opacity: 1;
    }

    .tlog-th--check,
    .tlog-td--check {
        width: 34px;
        padding-right: 0;
    }

    .tlog-nowrap {
        white-space: nowrap;
    }

    .tlog-hours {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    /* Free-text columns get a ceiling and an ellipsis; the full value is in
       the cell's title attribute. */
    .tlog-truncate {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tlog-truncate--note {
        max-width: 130px;
    }

    /* Action stays against the right edge of the box, so edit and delete are
       reachable at any width without scrolling the table sideways first. */
    .tlog-th--action,
    .tlog-td--action {
        position: sticky;
        right: 0;
        box-shadow: inset 1px 0 0 #e6e6e6;
    }

    .tlog-th--action {
        z-index: 3;
    }

    /* The sticky cell paints its own background, so a row highlight has to be
       set on the cells and not on the row. */
    .tlog-table tbody tr:hover td {
        background: #fafafa;
    }

    .tlog-table .text-center {
        text-align: center;
    }

    .tlog-yes {
        font-size: 18px;
        color: #2489b3;
    }

    .tlog-no {
        font-size: 18px;
        color: #b5b5b5;
    }

    .tlog-td--action {
        white-space: nowrap;
    }

    .tlog-action {
        display: inline-flex;
        color: #8a8a8a;
        margin: 0 2px;
    }

    .tlog-action:hover {
        color: #222;
    }

    .tlog-action .material-icons {
        font-size: 18px;
    }

    .tlog-action:hover .tlog-action--danger {
        color: #d0021b;
    }

    .tlog-empty {
        padding: 48px 20px;
        text-align: center;
        color: #777;
    }

    .tlog-empty__title {
        margin: 0 0 6px;
        font-size: 15px;
        color: #444;
    }

    .tlog-empty__hint {
        margin: 0;
        font-size: 13px;
    }

    .tlog-pager {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e6e6e6;
        font-size: 13px;
        color: #777;
    }

    .tlog-pager__nav {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tlog-pager__nav a {
        color: #2489b3;
        text-decoration: none;
    }

    .tlog-pager__nav a:hover {
        text-decoration: underline;
    }

    .tlog-pager__nav .is-disabled {
        color: #c4c4c4;
    }

    .tlog-pager__page {
        color: #222;
    }
</style>
