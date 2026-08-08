<?php if ($this->request->is('ajax')):
    echo $this->element('user_manage_grid');
    return;
endif; ?>
<style>
    .disp_assn_proj_popup {
        cursor: pointer;
    }

    /* ── Grid / List view toggle ──────────────────────────────────────────── */
    #userViewContainer .usr-list-view { display: none; }

    #userViewContainer.view-list .usr_mcnt { display: none; }
    #userViewContainer.view-list .cb { display: none; }
    #userViewContainer.view-list .usr-list-view { display: block; }

    /* Active state for the toggle buttons */
    .usr-view-btn.active {
        background: var(--primary) !important;
        color: #fff !important;
    }
    .usr-view-btn.active .material-icons { color: #fff !important; }

    /* ── List-view table styles ─────────────────────────────────────────── */
    .usr-list-view {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        max-height: calc(100vh - 260px);
    }
    .usr-list-tbl {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        font-size: 13px;
    }
    .usr-list-tbl thead th {
        background: #f7f7f7;
        color: #555;
        font-weight: 600;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 2px solid #e8e8e8;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .usr-list-tbl tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background .15s;
    }
    .usr-list-tbl tbody tr:hover { background: #fafafa; }
    .usr-list-tbl tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        color: #444;
    }
    .usr-list-tbl .usr-list-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        margin-right: 8px;
        flex-shrink: 0;
        overflow: hidden;
        vertical-align: middle;
        background: #b6b6b6
    }
    .usr-list-tbl .usr-list-avatar img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
    }
    .usr-list-tbl .usr-list-name {
        display: inline-block;
        vertical-align: middle;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .usr-list-tbl .usr-cat-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    .usr-list-tbl .usr-cat-badge.own_clr  { background: #e8f5e9; color: #2e7d32; }
    .usr-list-tbl .usr-cat-badge.adm_clr  { background: #e3f2fd; color: #1565c0; }
    .usr-list-tbl .usr-cat-badge.usr_clr  { background: #f3e5f5; color: #6a1b9a; }
    .usr-list-tbl .usr-cat-badge.cli_clr  { background: #fff3e0; color: #e65100; }
    .usr-list-tbl .usr-list-email {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }
    .usr-list-tbl .usr-list-actions { width: 32px; padding: 0 4px; text-align: center; }
    .usr-list-tbl .usr-list-actions .dropdown-toggle { color: #888; }
    .usr-list-tbl .usr-list-actions .dropdown-menu { min-width: 170px; }
    .usr-list-tbl .no-wrap { white-space: nowrap; }
    .usr-list-tbl .muted { color: #aaa; font-style: italic; }

    .os_pagination.user-new-pagination {padding: 25px 0 20px 5px;}

    /* ── List-view sortable column headers ───────────────────────────── */
    .usr-list-tbl th.usr-list-sortable { cursor: pointer; padding-right: 6px; }
    .usr-list-tbl th.usr-list-sortable:hover { background: #efefef; }
    .usr-list-tbl th.usr-list-sorted { background: #eef3f7; }
    /* Each sortable header wraps a tiny POST form. Strip the form's
       default block layout so the header behaves like a normal cell. */
    .usr-list-tbl .usr-sort-form {
        display: inline;
        margin: 0;
        padding: 0;
    }
    /* The submit <button> inside is styled to look like a plain inline
       link — no chrome, inherits th color/weight. */
    .usr-list-tbl .usr-sort-link {
        background: none;
        border: 0;
        padding: 0;
        margin: 0;
        font: inherit;
        color: inherit;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .usr-list-tbl .usr-sort-link:hover { text-decoration: none; color: var(--primary, #2e2e2e); }
    .usr-list-tbl .usr-sort-link:focus { outline: 0; }
    .usr-list-tbl .usr-sort-arrow { font-size: 11px; line-height: 1; }
    .usr-list-tbl .usr-sort-arrow.inactive { color: #bbb; }
    .usr-list-tbl .usr-sort-arrow.active   { color: var(--primary, #2e2e2e); }

    /* ── KPI summary header (user_kpi_summary element) ─────────────────────── */
    .ukpi-grid { display: flex; flex-wrap: wrap; gap: 14px; margin: 4px 0 18px; }
    .ukpi-card {
        flex: 1 1 180px; min-width: 170px; position: relative;
        background: #fff; border: 1px solid #edf0f5; border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 16px 66px 16px 18px; min-height: 96px;
        display: flex; align-items: center;
    }
    .ukpi-label { font-size: 13px; color: #757575; font-weight: 500; margin-bottom: 8px; }
    .ukpi-value { font-size: 25px; line-height: 1; color: #444; font-weight: 600; }
    .ukpi-icon {
        position: absolute; right: 16px; top: 0; bottom: 0; margin: auto;
        width: 44px; height: 44px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .ukpi-icon .material-icons { font-size: 22px; }
    .ukpi-blue  { background: #e3f2fd; } .ukpi-blue  .material-icons { color: #1565c0; }
    .ukpi-green { background: #e8f5e9; } .ukpi-green .material-icons { color: #2e7d32; }
    .ukpi-amber { background: #fff3e0; } .ukpi-amber .material-icons { color: #e65100; }
    .ukpi-red   { background: #ffebee; } .ukpi-red   .material-icons { color: #c62828; }
    .ukpi-teal  { background: #e0f2f1; } .ukpi-teal  .material-icons { color: #00897b; }
    @media (max-width: 767px) { .ukpi-card { flex-basis: calc(50% - 14px); } }
</style>
<input type="hidden" id="role" value="<?php echo h($role); ?>">
<input type="hidden" id="type" value="<?php echo h($type); ?>">
<input type="hidden" id="user_srch" value="<?php echo h($user_srch); ?>">
<input type="hidden" id="hid_filter_role_id"    value="<?php echo (int)($filterRoleId ?? 0); ?>">
<input type="hidden" id="hid_filter_project_id" value="<?php echo (int)($filterProjectId ?? 0); ?>">
<div class="proj_grids m-cmn-flow">    
    <?php
    $queryRole = $this->request->getQuery('role');
    $queryUser = $this->request->getQuery('user', '');
    $srch_res = '';
    if (trim($queryUser) && isset($userArr['0']) && !empty($userArr['0'])) {
        $srch_res = $userArr['0']['name'] ? ucfirst($userArr['0']['name']) . " " . ucfirst($userArr['0']['last_name']) : $userArr['0']['email'];
    }

    if (isset($user_srch) && trim($user_srch)) {
        $srch_res = $user_srch;
    }
    ?>
    <?php if (trim($srch_res)) { ?>
        <div class="cmn_search_result cmn_bdr_shadow">
            <div class="global-srch-res fl"><?php echo __('Search Results for'); ?>: <span><?php echo h($srch_res); ?></span></div>
            <div class="fl global-srch-rst">
            <a href="<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'manage']); ?>">
                <i class="material-icons">&#xE8BA;</i>
            </a>
            </div>
            <div class="cb"></div>
        </div>
    <?php } ?>
    <!-- Card-grid view: extracted in PR #20 to its own element so the
         filter panel can replace the wrapper contents over AJAX without
         re-templating the whole page. The wrapping #userViewContainer
         div is kept from the hot-fixes branch so the grid/list view
         toggle (user_list_view element below + the topbar buttons) can
         find and hide/show this region. -->
    <div class="user_div_bk usrs_page m-list-tbl" id="userViewContainer">
        <div id="usrGridWrap">
            <?php echo $this->element('user_manage_content'); ?>
        </div>

    </div>
</div>
<div id="projectLoader">
    <div class="loadingdata"><?php echo __('Invitation resend'); ?>...</div>
</div>
<div class="crt_task_btn_btm <?php if (defined('COMP_LAYOUT') && COMP_LAYOUT && $_SESSION['KEEP_HOVER_EFFECT'] && ((\Cake\Cache\Cache::read('KEEP_HOVER_EFFECT_' . SES_ID) & 2) == 2)) { ?>keep_hover_efct<?php } ?>">
    <span class="hide_tlp_cross" title="<?php echo __('Close'); ?>" onclick="resetHoverEffect('user',this);">&times;</span>
    <?php if ($this->Format->isAllowed('Add New User', $roleAccess)) { ?>
        <div class="os_plus" id="tour_invt_user_btn">
            <div class="ctask_ttip">
                <span class="label label-default">
                    <?php if ($role == 'client') { ?>
                        <?php echo __('Add New Client'); ?>
                    <?php } else { ?>
                        <?php echo __('Add New User'); ?>
                    <?php } ?>
                </span>
            </div>
            <a href="javascript:void(0)" onClick="newUser()">
                <i class="material-icons cmn-icon-prop ctask_icn">&#xE7FB;</i>
                <img src="<?php echo HTTP_ROOT; ?>img/images/plusct.png" class="add_icn" />
            </a>
        </div>
    <?php } ?>
</div>
<?php echo $this->element('user_filter_panel'); ?>
<script>
    $(document).ready(function() {
        setTimeout(hideCmnMesg, 2000);
        $('.disp_assn_proj_popup').off().on('click', function() {

            var usr_id = $(this).attr('data-usr-id');
			var usr_name = $(this).attr('data-usr-name');
			var is_invited_user = $("#is_invited_user").val();
			add_project(usr_id, usr_name, is_invited_user);
            // if ($('.icon-assign-usr').length) {
            //     $('.icon-assign-usr').trigger('click');
            // }
        });

        // Apply saved view mode on page load
        var savedMode = localStorage.getItem('os_user_view_mode') || 'grid';
        applyUserViewMode(savedMode, false);
    });

    /**
     * Set and persist the user list view mode ('grid' or 'list').
     * Called from the toggle buttons in user_topbar.php.
     */
    function setUserViewMode(mode) {
        if (mode !== 'grid' && mode !== 'list') { return; }
        localStorage.setItem('os_user_view_mode', mode);
        applyUserViewMode(mode, true);
    }

    function applyUserViewMode(mode, animate) {
        var $container = $('#userViewContainer');
        var $btnGrid   = $('#usr-view-btn-grid');
        var $btnList   = $('#usr-view-btn-list');

        if (mode === 'list') {
            $container.addClass('view-list');
            $btnList.addClass('active');
            $btnGrid.removeClass('active');
        } else {
            $container.removeClass('view-list');
            $btnGrid.addClass('active');
            $btnList.removeClass('active');
        }
    }

    /**
     * Open user list export popup
     */
    function openUserListExportPopup(exportType) {
        openPopup();
        $(".loader_dv").show();
        $(".user_list_export").show();
        $("#hid_user_export_type_id").val(exportType);
        $('.user_exp_chkbx').prop('checked', true);
    }

    /**
     * Export users with selected columns
     */
    function userlistexport() {
        var checkedArr = [];
        $('.user_exp_chkbx').each(function () {
            if ($(this).is(':checked')) {
                checkedArr.push($(this).val());
            }
        });
        if (!checkedArr.length) {
            showTopErrSucc('Error', _('Please select atleast one field.'));
            return false;
        }
        var exportTypeVal = $("#hid_user_export_type_id").val();
        closePopup();
        
        // Get current filter parameters
        var role = $('#role').val() || '';
        var type = $('#type').val() || '';
        var user_srch = $('#user_srch').val() || '';
        
        // Create a form and submit via POST. No target='_blank' — with
        // Content-Disposition: attachment the browser downloads in place
        // without navigating away from the current page.
        var form = $('<form>', {
            'method': 'POST',
            'action': HTTP_ROOT + 'users/exportUsers'
        });
        
        // Add CSRF token
        form.append($('<input>', {'type': 'hidden', 'name': '_csrfToken', 'value': _csrfToken}));
        
        // Add form fields
        form.append($('<input>', {'type': 'hidden', 'name': 'checkedFields', 'value': checkedArr.join(',')}));
        form.append($('<input>', {'type': 'hidden', 'name': 'exportType', 'value': exportTypeVal}));
        form.append($('<input>', {'type': 'hidden', 'name': 'role', 'value': role}));
        form.append($('<input>', {'type': 'hidden', 'name': 'type', 'value': type}));
        form.append($('<input>', {'type': 'hidden', 'name': 'user_srch', 'value': user_srch}));
        
        form.append($('<input>', {'type': 'hidden', 'name': 'filter_role_ids',    'value': $('#hid_filter_role_id').val() || ''}));
        form.append($('<input>', {'type': 'hidden', 'name': 'filter_project_ids', 'value': $('#hid_filter_project_id').val() || ''}));
        
        // Append to body, submit, and remove
        $('body').append(form);
        form.submit();
        form.remove();
        
        return false;
    }

    /**
     * Export all users to CSV (legacy - kept for compatibility)
     */
    function exportUsersToCSV() {
        window.location.href = HTTP_ROOT + 'users/exportUsers';
    }

    // -------------------------------------------------------------------
    // Show/hide password toggle — global helper used via inline onclick
    // on each toggle button so it's resilient to JS load order. The
    // Add New User modal also relies on this same function name.
    // -------------------------------------------------------------------
    if (typeof window.togglePwField !== 'function') {
        window.togglePwField = function (inputId, btn) {
            var el = document.getElementById(inputId);
            if (!el) return false;
            var icon = btn ? btn.querySelector('.material-icons') : null;
            if (el.type === 'password') {
                el.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
                if (btn) btn.setAttribute('aria-label', 'Hide password');
            } else {
                el.type = 'password';
                if (icon) icon.textContent = 'visibility';
                if (btn) btn.setAttribute('aria-label', 'Show password');
            }
            return false;
        };
    }

    // -------------------------------------------------------------------
    // Admin "Reset Password" modal — invoked from the per-user dropdown.
    // Posts to /users/admin-reset-password (no SMTP). Validates min 8 +
    // confirm-match client side; server re-validates.
    // -------------------------------------------------------------------
    function openAdminResetPassword(userId, userName) {
        $('#admin_reset_pw_user_id').val(userId);
        $('#admin_reset_pw_user_label').text(userName || '');
        $('#admin_reset_pw_password').val('');
        $('#admin_reset_pw_confirm').val('');
        $('#admin_reset_pw_err').hide().text('');
        $('#admin_reset_pw_success').hide().text('');
        $('#adminResetPasswordModal').modal('show');
    }

    function submitAdminResetPassword() {
        var $err = $('#admin_reset_pw_err');
        var $ok = $('#admin_reset_pw_success');
        $err.hide().text('');
        $ok.hide().text('');

        var userId = $('#admin_reset_pw_user_id').val();
        var pw = $('#admin_reset_pw_password').val() || '';
        var cpw = $('#admin_reset_pw_confirm').val() || '';

        if (pw.length < 8) {
            $err.text("<?php echo __('Password must be at least 8 characters long.'); ?>").show();
            return false;
        }
        if (pw !== cpw) {
            $err.text("<?php echo __('Passwords do not match.'); ?>").show();
            return false;
        }

        $('#admin_reset_pw_submit_btn').prop('disabled', true);

        $.ajax({
            url: HTTP_ROOT + 'users/admin-reset-password',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                password: pw,
                confirm_password: cpw
            }
        }).done(function (resp) {
            if (resp && resp.status === 'success') {
                $ok.text(resp.message || "<?php echo __('Password has been updated.'); ?>").show();
                setTimeout(function () { $('#adminResetPasswordModal').modal('hide'); }, 900);
            } else {
                $err.text((resp && resp.message) || "<?php echo __('Failed to reset password.'); ?>").show();
            }
        }).fail(function (xhr) {
            var msg = "<?php echo __('Failed to reset password.'); ?>";
            try {
                var j = JSON.parse(xhr.responseText);
                if (j && j.message) msg = j.message;
            } catch (e) {}
            $err.text(msg).show();
        }).always(function () {
            $('#admin_reset_pw_submit_btn').prop('disabled', false);
        });
        return false;
    }

    // -----------------------------------------------------------------------
    // User manage — filter panel functions (right-slide modal)
    // -----------------------------------------------------------------------

    /** Toggle an accordion section inside the filter modal */
    function usrFilterToggle(header) {
        var $header = $(header);
        var $data   = $header.next('.filter_toggle_data');
        var $arrow  = $header.find('.fa_arrow');
        if ($data.is(':visible')) {
            $data.slideUp(150);
            $header.removeClass('active');
            $arrow.removeClass('fa-minus').addClass('fa-plus');
        } else {
            $data.slideDown(150);
            $header.addClass('active');
            $arrow.removeClass('fa-plus').addClass('fa-minus');
        }
    }

    /** Apply selected filters via AJAX — replaces #usrGridWrap content.
     *
     *  Each filter category supports multi-select (checkboxes). An empty
     *  selection for a category means "no filter / show all". Categories
     *  combine via AND; selections within a category combine via OR
     *  (handled by `IN` on the backend).
     *
     *  @param {boolean} keepOpen  Pass true to leave the filter panel open. */
    function applyUsrFilters(keepOpen) {
        function collect(name) {
            return $('input[name="' + name + '"]:checked').map(function () {
                return parseInt(this.value, 10);
            }).get().filter(function (v) { return !isNaN(v) && v > 0; });
        }
        var roleIds    = collect('usr_filter_role[]');
        var projectIds = collect('usr_filter_project[]');

        var postData = {
            _csrfToken: _csrfToken,
            role: $('#role').val() || '',
            type: $('#type').val() || '',
            user_srch: $('#user_srch').val() || '',
            // New array-form params. jQuery serializes these as
            // filter_role_ids%5B%5D=1&filter_role_ids%5B%5D=2, which PHP
            // reads as an array via getData/getQuery.
            filter_role_ids:    roleIds,
            filter_project_ids: projectIds
        };

        if (!keepOpen) { $('#usrFilterModal').modal('hide'); }
        $('#usrGridWrap').css('opacity', 0.4);

        $.ajax({
            url: window.location.pathname,
            type: 'POST',
            data: postData,
            traditional: false, // jQuery default — encodes arrays as key[]=v1&key[]=v2
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#usrGridWrap').html(html).css('opacity', 1);

                // Sync hidden inputs used by export. Comma-join so a single
                // hidden field round-trips multi-select cleanly to the
                // export action (the export controller already accepts
                // comma-separated lists; if a caller still needs the
                // single-value legacy field, the first id is used).
                $('#hid_filter_role_id').val(roleIds.join(','));
                $('#hid_filter_project_id').val(projectIds.join(','));

                updateUsrFilterBadge();
                updateUsrActiveFilters();

                // Rebind project popup
                $('#usrGridWrap .disp_assn_proj_popup').off().on('click', function () {
                    var usr_id  = $(this).attr('data-usr-id');
                    var usr_nm  = $(this).attr('data-usr-name');
                    var invited = $('#is_invited_user').val();
                    add_project(usr_id, usr_nm, invited);
                });

                // Lazy-load images in refreshed grid
                if (typeof $.fn.lazyload === 'function') {
                    $('#usrGridWrap img.lazy').lazyload();
                }
            },
            error: function () {
                $('#usrGridWrap').css('opacity', 1);
            }
        });
    }

    /** Reset all filters and reload.
     *  Multi-select: simply uncheck every checkbox in the panel —
     *  empty selection means "no filter". */
    function resetUsrFilters(keepOpen) {
        $('input[name="usr_filter_role[]"], input[name="usr_filter_project[]"]').prop('checked', false);
        // Clear any search-within-filter text and restore all options.
        $('#usrFilterModal .usr_filter_search').val('');
        $('#usrFilterModal .usr_filter_opt').show();
        $('#usrFilterModal .usr_filter_empty').hide();
        applyUsrFilters(!!keepOpen);
    }

    /** Build and render active filter chips in the user filter panel.
     *  With multi-select, one category can contribute multiple chips
     *  ("Role: TeamLead", "Role: Developer", etc.). */
    function updateUsrActiveFilters() {
        var active = [];

        function pushChips(name, labelPrefix) {
            $('input[name="' + name + '"]:checked').each(function () {
                var txt = $(this).closest('label').text().trim();
                if (txt) active.push(labelPrefix + ': ' + txt);
            });
        }
        pushChips('usr_filter_role[]',    '<?php echo addslashes(__('Role')); ?>');
        pushChips('usr_filter_project[]', '<?php echo addslashes(__('Project')); ?>');

        var $sec = $('#usr_active_filter_sec');
        var $cont = $('#usr_active_filter_contain');
        var $reset = $('#usr_reset_filter_icon');

        if (!$sec.length || !$cont.length) {
            return;
        }

        if (!active.length) {
            $cont.empty();
            $sec.hide();
            $reset.hide();
            return;
        }

        var html = '';
        for (var i = 0; i < active.length; i++) {
            html += '<span class="filter_opn">' + $('<div/>').text(active[i]).html() + '</span>';
        }
        $cont.html(html);
        $sec.show();
        $reset.show();
    }

    /** Show/hide the filter badge on the topbar button.
     *  Counts how many CATEGORIES have at least one checked option —
     *  not how many checkboxes total. Matches the way Task filters
     *  render their badge ("3 categories active" rather than "8
     *  individual selections"). */
    function updateUsrFilterBadge() {
        function any(name) {
            return $('input[name="' + name + '"]:checked').length > 0 ? 1 : 0;
        }
        var n = any('usr_filter_role[]')
              + any('usr_filter_project[]');
        var $badge = $('#usr-filter-badge');
        if (n > 0) { $badge.text(n).show(); } else { $badge.hide(); }
    }

    /** Live-filter the visible checkbox options inside a single filter
     *  section by the search query the user typed. Matches are substring
     *  + case-insensitive against the option's visible label (stored in
     *  the <li>'s data-label attribute as already-lowercased text). */
     function filterUsrFilterOptions(target, query) {
        var q = (query || '').toString().toLowerCase().trim();
        var $list = $('#usrFilterModal .usr_filter_list[data-target="' + target + '"]');
        var $empty = $('#usrFilterModal .usr_filter_empty[data-target="' + target + '"]');
        var shown = 0;
        $list.find('.usr_filter_opt').each(function () {
            var label = $(this).attr('data-label') || '';
            var match = !q || label.indexOf(q) !== -1;
            $(this).toggle(match);
            if (match) shown++;
        });
        if ($empty.length) {
            $empty.toggle(q.length > 0 && shown === 0);
        }
    }

    // Initialise badge on page load
    $(document).ready(function () {
        updateUsrFilterBadge();
        updateUsrActiveFilters();

        // Live-filter checkbox options as the user types in the
        // per-section search box. Pure client-side — no AJAX.
        $(document).on('input', '#usrFilterModal .usr_filter_search', function () {
            filterUsrFilterOptions($(this).attr('data-target'), $(this).val());
        });

        // Clearing the search via Esc resets visibility for that section.
        $(document).on('keydown', '#usrFilterModal .usr_filter_search', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                $(this).val('');
                filterUsrFilterOptions($(this).attr('data-target'), '');
            }
        });

        // List-view action dropdowns live inside .usr-list-view, which uses
        // overflow:auto to scroll a wide/tall table — that overflow clips the
        // absolutely-positioned menu (only the first item peeked out). Pin the
        // menu with position:fixed off the toggle's viewport rect so it escapes
        // the scroll container, flipping above the toggle and right-aligning
        // when there isn't room below / to the right.
        // NOTE: Bootstrap 3 fires shown/hidden.bs.dropdown on the .dropdown
        // container, NOT on the [data-toggle] element.
        $(document).on('shown.bs.dropdown', '.usr-list-actions .dropdown', function () {
            var $toggle = $(this).find('[data-toggle="dropdown"]');
            var $menu = $(this).find('.dropdown-menu');
            if (!$toggle.length || !$menu.length) { return; }
            var rect = $toggle[0].getBoundingClientRect();
            var menuW = $menu.outerWidth();
            var menuH = $menu.outerHeight();
            var openUp = rect.top > (window.innerHeight - rect.bottom) && rect.top > menuH;
            // Left-align under the toggle (matches the menu's .left0 default),
            // pulling left only if it would spill past the right viewport edge.
            var left = rect.left;
            if (left + menuW > window.innerWidth - 4) { left = window.innerWidth - menuW - 4; }
            if (left < 4) { left = 4; }
            var top = openUp ? (rect.top - menuH) : rect.bottom;
            $menu.css({
                position: 'fixed',
                top: Math.round(top) + 'px',
                left: Math.round(left) + 'px',
                right: 'auto',
                'z-index': 2000
            });
        });
        $(document).on('hidden.bs.dropdown', '.usr-list-actions .dropdown', function () {
            $(this).find('.dropdown-menu').css({
                position: '', top: '', left: '', right: '', 'z-index': ''
            });
        });
    });

</script>

<!-- Admin Reset Password modal (Bootstrap 3 style — matches the rest of Manage Users) -->
<div class="modal fade" id="adminResetPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Reset Password'); ?></h4>
            </div>
            <div class="modal-body">
                <p style="color:#666;font-size:13px;margin-bottom:10px;">
                    <?php echo __('Set a new password for'); ?>
                    <strong id="admin_reset_pw_user_label"></strong>.
                    <?php echo __('The user can log in with this password immediately.'); ?>
                </p>
                <div id="admin_reset_pw_err" style="display:none;color:#FF0000;margin-bottom:10px;font-size:13px;"></div>
                <div id="admin_reset_pw_success" style="display:none;color:#2e7d32;margin-bottom:10px;font-size:13px;"></div>

                <input type="hidden" id="admin_reset_pw_user_id" value="">

                <div style="margin-bottom:12px;">
                    <label class="admin-pw-label" for="admin_reset_pw_password"><?php echo __('Password'); ?></label>
                    <div class="admin-pw-wrap" style="position:relative;width:100%;">
                        <input type="password"
                               class="admin-pw-input"
                               id="admin_reset_pw_password"
                               autocomplete="new-password"
                               placeholder="<?php echo __('Enter new password'); ?>">
                        <button type="button"
                                class="admin-pw-toggle"
                                data-pw-target="admin_reset_pw_password"
                                tabindex="-1"
                                aria-label="<?php echo __('Show password'); ?>"
                                onclick="return togglePwField('admin_reset_pw_password', this);">
                            <i class="material-icons">visibility</i>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label class="admin-pw-label" for="admin_reset_pw_confirm"><?php echo __('Confirm Password'); ?></label>
                    <div class="admin-pw-wrap" style="position:relative;width:100%;">
                        <input type="password"
                               class="admin-pw-input"
                               id="admin_reset_pw_confirm"
                               autocomplete="new-password"
                               placeholder="<?php echo __('Re-enter new password'); ?>">
                        <button type="button"
                                class="admin-pw-toggle"
                                data-pw-target="admin_reset_pw_confirm"
                                tabindex="-1"
                                aria-label="<?php echo __('Show password'); ?>"
                                onclick="return togglePwField('admin_reset_pw_confirm', this);">
                            <i class="material-icons">visibility</i>
                        </button>
                    </div>
                </div>
                <p style="color:#888;font-size:12px;margin-top:6px;">
                    <?php echo __('Must be at least 8 characters long.'); ?>
                </p>
                <?= $this->Html->css('admin-reset-password.css?v=' . ASSET_RELEASE) ?>
            </div>
            <div class="modal-footer" style="text-align:right;padding:14px 20px;border-top:1px solid #eee;">
                <button type="button"
                        class="btn btn-default"
                        data-dismiss="modal"
                        style="margin-right:8px;"><?php echo __('Cancel'); ?></button>
                <button type="button"
                        id="admin_reset_pw_submit_btn"
                        onclick="return submitAdminResetPassword();"
                        style="background-color:#ff7905 !important;color:#fff !important;border:none !important;padding:8px 22px !important;border-radius:4px !important;font-size:14px !important;font-weight:500 !important;cursor:pointer !important;display:inline-block !important;"><?php echo __('Save'); ?></button>
            </div>
        </div>
    </div>
</div>