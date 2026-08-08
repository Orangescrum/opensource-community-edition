<!--
    Hierarchy-aware delete modal for Easycases (Epics → Features → Stories).
    Opens from dashboard_v1.js::deleteCase() when the task being deleted
    has hierarchy children. dashboard_v1.js populates the dynamic bits:
      • #hierarchyParentLabel     — "Feature #1 — My Cool Feature"
      • #hierarchyChildSummary    — "2 stories under this feature"
      • #hierarchyTargetSelect    — list of other features/epics to move into
      • data attributes on .hierarchy_move (taskId / taskCno / taskPid / ...)

    Two real actions are exposed:
      • Move children to another parent, then delete this task
        (only when other valid targets exist).
      • Delete this task.
        Per data-model fix 78df4265, children survive — their parent
        ref (feature_id / epic_id) is nulled and they live on as
        standalone items. The button copy says this explicitly so
        users aren't surprised.

    Each option is a clickable card with the consequence stated inline,
    so the result is obvious BEFORE the Continue button is pressed.
-->
<style>
    .hierarchy_move .modal-content {
        border-radius: 8px;
        border: 0;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.18);
    }
    .hierarchy_move .modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .hierarchy_move .modal-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .hierarchy_move .modal-header .close-icon {
        margin-left: auto;
        background: none;
        border: 0;
        color: #94a3b8;
        cursor: pointer;
    }
    .hierarchy_move .hier-warn-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #fef3c7;
        color: #b45309;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .hierarchy_move .modal-body {
        padding: 18px 22px 8px;
    }
    .hierarchy_move .hier-parent-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .hierarchy_move .hier-parent-card .hier-icon {
        color: #6366f1;
        flex-shrink: 0;
    }
    .hierarchy_move .hier-parent-card .hier-text {
        flex: 1;
        min-width: 0;
    }
    .hierarchy_move .hier-parent-card .hier-parent-label {
        font-weight: 600;
        font-size: 13px;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .hierarchy_move .hier-parent-card .hier-child-summary {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }
    .hierarchy_move .hier-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .hierarchy_move .hier-option {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 14px;
        cursor: pointer;
        transition: border-color 0.12s, background 0.12s, box-shadow 0.12s;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .hierarchy_move .hier-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .hierarchy_move .hier-option.selected {
        border-color: #0B7285;
        background: #f0fdfa;
        box-shadow: inset 0 0 0 1px #0B7285;
    }
    .hierarchy_move .hier-option .hier-radio {
        margin-top: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        flex-shrink: 0;
        position: relative;
    }
    .hierarchy_move .hier-option.selected .hier-radio {
        border-color: #0B7285;
    }
    .hierarchy_move .hier-option.selected .hier-radio::after {
        content: '';
        position: absolute;
        top: 2px; left: 2px;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #0B7285;
    }
    .hierarchy_move .hier-option-body {
        flex: 1;
        min-width: 0;
    }
    .hierarchy_move .hier-option-title {
        font-weight: 600;
        font-size: 13px;
        color: #0f172a;
        margin-bottom: 3px;
    }
    .hierarchy_move .hier-option-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.45;
    }
    .hierarchy_move #hierarchyMoveSection {
        margin-top: 10px;
    }
    .hierarchy_move #hierarchyTargetSelect {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 13px;
        background: #fff;
    }
    .hierarchy_move .modal-footer {
        padding: 12px 22px 18px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
    }
    /* Both footer buttons share these dimensions so the legacy Cancel
       (.btn .cmn_size, sized by Bootstrap-derived rules elsewhere) and
       the custom Continue (.btn-primary-action) line up to the same
       height. Without this, Cancel rendered noticeably shorter. */
    .hierarchy_move .modal-footer .btn,
    .hierarchy_move .modal-footer .btn-primary-action {
        box-sizing: border-box;
        height: 36px;
        line-height: 1;
        padding: 0 16px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }
    .hierarchy_move .modal-footer .btn-primary-action {
        background: #0B7285;
        color: #fff;
        border: 0;
        cursor: pointer;
    }
    .hierarchy_move .modal-footer .btn-primary-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div class="modal-dialog" style="max-width: 520px;">
    <div class="modal-content">
        <div class="modal-header">
            <span class="hier-warn-icon"><i class="material-icons" style="font-size:20px;">warning_amber</i></span>
            <h4><?php echo __('Delete task with children'); ?></h4>
            <button type="button" class="close-icon" onclick="closeHierarchyPopup();" title="<?php echo __('Close'); ?>"><i class="material-icons">&#xE14C;</i></button>
        </div>
        <div class="modal-body">

            <!-- Parent + child summary -->
            <div class="hier-parent-card">
                <i class="material-icons hier-icon">account_tree</i>
                <div class="hier-text">
                    <div class="hier-parent-label" id="hierarchyParentLabel"></div>
                    <div class="hier-child-summary" id="hierarchyChildSummary"></div>
                </div>
            </div>

            <p style="font-size:13px;color:#475569;margin-bottom:12px;"><?php echo __('Decide what should happen to the children before this task is deleted.'); ?></p>

            <div class="hier-options">

                <!-- Option 1: Move children to another parent.
                     Hidden when no valid targets exist (set inline by
                     dashboard_v1.js via #hierOptionMove style.display). -->
                <label class="hier-option" id="hierOptionMove" style="display:none;">
                    <input type="radio" name="hierAction" value="move" class="hier-radio-input" style="display:none;" />
                    <span class="hier-radio"></span>
                    <div class="hier-option-body">
                        <div class="hier-option-title"><?php echo __('Move children to another parent first'); ?></div>
                        <div class="hier-option-desc"><?php echo __('Re-parents the children to a task you pick, then deletes this one. Children keep all their data.'); ?></div>
                        <div id="hierarchyMoveSection" style="display:none;">
                            <select id="hierarchyTargetSelect" name="new_parent_id">
                                <option value=""><?php echo __('Select a new parent…'); ?></option>
                            </select>
                        </div>
                    </div>
                </label>

                <!-- Option 2: Delete this task; children survive as standalone.
                     Always available. Maps to easycases/delete_case — the
                     deleteTasksRecursively path nulls children's feature_id /
                     epic_id refs (per fix 78df4265) so they live on without
                     a parent. Copy says this explicitly. -->
                <label class="hier-option selected" id="hierOptionDelete">
                    <input type="radio" name="hierAction" value="delete" class="hier-radio-input" checked style="display:none;" />
                    <span class="hier-radio"></span>
                    <div class="hier-option-body">
                        <div class="hier-option-title" id="hierOptionDeleteTitle"><?php echo __('Delete this task — children become standalone'); ?></div>
                        <div class="hier-option-desc" id="hierOptionDeleteDesc">
                            <?php echo __('Deletes only this task. The children survive as standalone items without a parent.'); ?>
                        </div>
                    </div>
                </label>

            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn_hover_link cmn_size" onclick="closeHierarchyPopup();"><?php echo __('Cancel'); ?></button>
            <button type="button" class="btn-primary-action" id="hierarchyApplyBtn"><?php echo __('Delete this task'); ?></button>
        </div>
    </div>
</div>

<script>
    // Card-selection wiring. Clicking anywhere on a card selects it and
    // updates the Continue button label to reflect the chosen action.
    // The actual AJAX calls still live in dashboard_v1.js
    // (#hierarchyDeleteAllBtn for delete, #hierarchyMoveConfirmBtn for
    // move) — we just trigger them from the unified Continue button.
    (function () {
        function modalRoot() { return document.querySelector('.hierarchy_move'); }

        function selectOption(value) {
            const root = modalRoot();
            if (!root) return;
            root.querySelectorAll('.hier-option').forEach(el => el.classList.remove('selected'));
            const target = value === 'move'
                ? root.querySelector('#hierOptionMove')
                : root.querySelector('#hierOptionDelete');
            if (target) target.classList.add('selected');
            root.setAttribute('data-current-action', value);
            updateContinueButton();
        }

        function updateContinueButton() {
            const root = modalRoot();
            if (!root) return;
            const action = root.getAttribute('data-current-action') || 'delete';
            const btn = root.querySelector('#hierarchyApplyBtn');
            if (!btn) return;
            if (action === 'move') {
                const target = root.querySelector('#hierarchyTargetSelect');
                btn.disabled = !target || !target.value;
                btn.textContent = '<?php echo __('Move & Delete'); ?>';
            } else {
                btn.disabled = false;
                btn.textContent = '<?php echo __('Delete this task'); ?>';
            }
        }

        // Card click: pick that option.
        document.addEventListener('click', function (ev) {
            const card = ev.target.closest('.hierarchy_move .hier-option');
            if (!card) return;
            // Clicks inside the new-parent <select> shouldn't re-trigger
            // a card pick; let the native dropdown handle them.
            if (ev.target.closest('#hierarchyMoveSection')) return;
            if (card.id === 'hierOptionMove') selectOption('move');
            else if (card.id === 'hierOptionDelete') selectOption('delete');
        });

        // Target-select change re-evaluates whether Continue is enabled
        // in Move mode.
        document.addEventListener('change', function (ev) {
            if (ev.target && ev.target.id === 'hierarchyTargetSelect') {
                updateContinueButton();
            }
        });

        // Continue → delegate to the existing per-scenario handlers in
        // dashboard_v1.js. We keep the old hidden buttons around as the
        // single source of truth for the AJAX flow.
        document.addEventListener('click', function (ev) {
            if (!ev.target || ev.target.id !== 'hierarchyApplyBtn') return;
            const root = modalRoot();
            if (!root) return;
            const action = root.getAttribute('data-current-action') || 'delete';
            if (action === 'move') {
                jQuery('#hierarchyMoveConfirmBtn').trigger('click');
            } else {
                jQuery('#hierarchyDeleteAllBtn').trigger('click');
            }
        });
    })();
</script>

<!-- Legacy hidden buttons — kept so dashboard_v1.js's existing AJAX
     handlers (registered against #hierarchyDeleteAllBtn and
     #hierarchyMoveConfirmBtn) still fire. The new Continue button
     above forwards clicks to whichever one matches the selected
     option. Keeping them out of the visible footer avoids breaking
     external callers that may also be hooked to these IDs. -->
<button type="button" id="hierarchyDeleteAllBtn" style="display:none;"></button>
<button type="button" id="hierarchyMoveConfirmBtn" style="display:none;"></button>
