<?php ?>
<style>
    .q_tour_btn:hover .mini-sidebar-label {
        color: #2e2e2e;
    }

    .q_tour_btn:hover .material-icons {
        color: #2e2e2e;
    }
    .new-user-change-label.control-label{ z-index: 99; line-height: 1.07142857; color: #80868b; font-weight: 400; margin: 16px 0 0 0; background: white; position: absolute; top: -22px; left: 14px; padding: 0 15px; font-size: 13px;
    }
    .assign-user-pop.new-asgn-user ul li::before { display:none !important; }
    .new-select-wrap .select2-container .select2-search--inline .select2-search__field{border:0 !important;width: 100% !important;}
    .modal-dialog .select2-container--default .select2-selection--multiple ul.select2-selection__rendered { display: inline-flex; align-items: center; }
</style>
<?php echo $this->Form->create(null, ['url' => '/users/new_user', 'id' => 'myform', 'name' => 'myform', 'onsubmit' => 'return memberCustomer(\'txt_email\',\'sel_custprj\',\'loader\',\'btn\')', 'escape' => false]); ?>
<div class="">
<div class="modal-body popup-container" style="padding-top:0px; ">
    <?= $this->Form->control('timezone_id', ['type' => 'hidden', 'value' => SES_TIMEZONE, 'id' => 'txt_loc']); ?>
    <?= $this->Form->control('istype', ['type' => 'hidden', 'value' => 3, 'id' => 'sel_Typ']); ?>
    <?= $this->Form->control('role', ['type' => 'hidden', 'value' => '', 'id' => 'role_hid']); ?>
    <div id="err_email_new" style="color:#FF0000;display:none;text-align:center; font-size: 14px;"></div>
    <div class="row">
        <div class="col-lg-12 import-g-contact">
            <div class="form-group label-floating mark_mandatory">
                <label class="control-label user_email_lable" for="txt_email"><span><?php echo __('Email ID'); ?></span></label>
                <?= $this->Form->control('email', ['templates'     => ['inputContainer' => '{{content}}'], 'type' => 'textarea', 'id' => 'txt_email', 'class' => 'form-control input-lg', 'placeholder' => '', 'rows' => '1', 'div' => false, 'label' => false,]) ?>
                <p class="help-block" style= "top:40px;"><?php echo __('Use comma to separate multiple email ids'); ?></p>
            </div>
            <?php if ($is_active_proj >= 1) { ?>
                <!-- <div class="cb height10"></div> -->
                <div id="tour_asnproj_user" class="project_to_be_assn proj-to-assign">
                    <?php
                    $company_id = SES_COMP;
                $sesId = SES_ID;
                $sql = " SELECT DISTINCT Projects.id, Projects.name FROM projects AS Projects WHERE Projects.isactive = 1 AND Projects.name != '' AND Projects.company_id = :company_id AND Projects.id IN ( SELECT DISTINCT ProjectUsers.project_id FROM project_users AS ProjectUsers WHERE ProjectUsers.user_id = :ses_id ) ORDER BY Projects.name ";
                $connection = \Cake\Datasource\ConnectionManager::get('default');
                $projArr = $connection->execute($sql, [
                    'company_id' => $company_id,
                    'ses_id' => $sesId
                ])->fetchAll('assoc');
                ?>
                    <div class="form-group label-floating" styel="padding-bottom:5px !important;">
                        <div class="cmn_help_select"></div>
                        <label class="control-label" style="top: -24px !important;z-index: 9;background: white;width: auto;left: 13px;padding: 0 4px;" for="assign_project_list"><?php echo __('Project to be assigned'); ?></label>
                        <select id="assign_project_list" class="form-control select2" rows="1" name="pid[]" multiple="multiple" placeholder="<?php echo __('Select Project'); ?>">>
                            <?php foreach ($projArr as $key => $value) {  ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
                            <?php } ?>
                        </select>
                        <div id="err_assing_project" style="display: none;color: #FF0000;"></div>
                        <div id="autopopup_projects"></div>
                        <p class="comma-seprate-txt"></p>
                    </div>
                </div>
            <?php } ?>
            <?php if (!empty($rolelist) && is_array($rolelist) && count($rolelist) > 0) { ?>
                <div class="field_wrapper nofloat_wrapper">
                    <?php echo $this->Form->input('role_id', ['type' => 'select', 'label' => false, 'options' => $rolelist, 'id' => 'select_role', 'class' => 'field_wrapper round_in', 'empty' => 'Select Role', 'placeholder' => __('')]); ?>
                    <div class="field_placeholder mark_mandatory"><span><?php echo __('Assign Role');?></span></div>
                </div>
            <?php } ?>

            <!--
                User Password section — admin can either send an invite email
                (legacy default) or set the password directly. Setting the
                password skips SMTP entirely, which is the only viable option
                for fresh self-hosted installs that haven't configured SMTP yet.
            -->
            <div class="user-pw-section" style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #eee;">
                <div style="font-weight: 600; margin-bottom: 4px;"><?php echo __('User Password'); ?></div>
                <div style="color: #666; font-size: 12px; margin-bottom: 10px;">
                    <?php echo __('You can choose to send this user an invitation email which allows them to set their own password, or you can set their password yourself below. The same password is applied to every email above when adding multiple users.'); ?>
                </div>

                <div class="send-invite-row">
                    <label for="send_invite_chk" class="send-invite-lbl">
                        <input type="checkbox"
                               id="send_invite_chk"
                               name="send_invite_chk"
                               value="1"
                               checked
                               class="send-invite-cb">
                        <span class="send-invite-text"><?php echo __('Send user invite email'); ?></span>
                    </label>
                </div>
                <?= $this->Html->css('add-user-modal.css?v=' . ASSET_RELEASE) ?>

                <!-- Hidden mirror of the checkbox state — what the controller actually reads. -->
                <input type="hidden" id="send_invite" name="send_invite" value="1">

                <div id="admin_pw_fields" style="display: none;">
                    <p style="color: #666; font-size: 12px; margin: 4px 0 10px 0;">
                        <?php echo __('Set a password used to log-in to the application. This must be at least 8 characters long.'); ?>
                    </p>
                    <div id="err_admin_pw" style="color:#FF0000;display:none;margin-bottom:8px;font-size:13px;"></div>
                    <div class="row admin-pw-row">
                        <div class="col-lg-6 admin-pw-col">
                            <label class="admin-pw-label" for="adminUserPassword"><?php echo __('Password'); ?></label>
                            <div class="admin-pw-wrap">
                                <input type="password"
                                       class="admin-pw-input"
                                       id="adminUserPassword"
                                       name="password"
                                       autocomplete="new-password"
                                       placeholder="<?php echo __('Enter password'); ?>">
                                <button type="button"
                                        class="admin-pw-toggle"
                                        data-pw-target="adminUserPassword"
                                        tabindex="-1"
                                        aria-label="<?php echo __('Show password'); ?>"
                                        onclick="return togglePwField('adminUserPassword', this);">
                                    <i class="material-icons">visibility</i>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-6 admin-pw-col">
                            <label class="admin-pw-label" for="adminUserConfirmPassword"><?php echo __('Confirm Password'); ?></label>
                            <div class="admin-pw-wrap">
                                <input type="password"
                                       class="admin-pw-input"
                                       id="adminUserConfirmPassword"
                                       name="confirm_password"
                                       autocomplete="new-password"
                                       placeholder="<?php echo __('Re-enter password'); ?>">
                                <button type="button"
                                        class="admin-pw-toggle"
                                        data-pw-target="adminUserConfirmPassword"
                                        tabindex="-1"
                                        aria-label="<?php echo __('Show password'); ?>"
                                        onclick="return togglePwField('adminUserConfirmPassword', this);">
                                    <i class="material-icons">visibility</i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Auto-generate strong password -->
                    <div style="margin-top: 4px;">
                        <button type="button"
                                class="gen-pw-btn"
                                onclick="return generateStrongPassword();"
                                tabindex="-1">
                            <i class="material-icons">autorenew</i>
                            <?php echo __('Generate strong password'); ?>
                        </button>
                        <div class="gen-pw-display" id="gen_pw_display">
                            <strong><?php echo __('Generated password (save this before submitting):'); ?></strong>
                            <span id="gen_pw_value"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <div class="fr popup-btn">

        <?php $totUsr = ''; ?>
        <span id="ldr" style="display:none;">
            <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." />
        </span>
        <span id="btn_addmem">
            <input type="hidden" id="uniq_id_new_user" value="<?php echo COMP_UID; ?>">
            <span class="cancel-link cancel_on_invite" style="display:none;">
                <button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closeInvitePopup();"><?php echo __('Cancel'); ?></button>
            </span>
            <span class="cancel-link cancel_on_direct">
                <button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closeUserPop();closePopup();"><?php echo __('Cancel'); ?></button>
            </span>
            <span class="hover-pop-btn"><button type="submit" class="btn cmn_size btn_cmn_efect cmn_bg btn-info addMember_popup"><?php echo __('Add'); ?></button></span>
        </span>
        <div class="cb"></div>
    </div>
    <div class="cb"></div>
</div>
<?php echo $this->Form->end(); ?>
<script>
    function closeUserPop() {
        $("#assign_project_list").val(null).trigger("change.select2");
    }
    $(document).ready(function() {
        $("#assign_project_list").val(null).trigger("change.select2");
        $(".proj-to-assign").on("focus", ".maininput", function() {
            $(this).parents(".form-group").addClass('is-focused');
        });
    });

    // -----------------------------------------------------------------
    // Show/hide password toggle (eye icon).
    // Defined globally + wired via inline `onclick` on each button so it
    // works even if jQuery delegation gets clobbered by other modal JS.
    // -----------------------------------------------------------------
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
            // Sync the "Generated password" plaintext pill to the reveal state.
            // Don't leave a plaintext copy on screen after the admin
            // deliberately masks the fields (e.g. for screen-sharing).
            syncGenPwDisplayVisibility();
            return false;
        };
    }

    // The generated-password pill should be visible ONLY when at least one
    // of the password fields is currently shown as plaintext (type=text).
    // If both are masked, hide the pill too.
    function syncGenPwDisplayVisibility() {
        var pwEl    = document.getElementById('adminUserPassword');
        var cpwEl   = document.getElementById('adminUserConfirmPassword');
        var display = document.getElementById('gen_pw_display');
        var val     = document.getElementById('gen_pw_value');
        if (!display || !val) return;
        // Only meaningful if we actually have a generated value to show
        if (!val.textContent) { display.style.display = 'none'; return; }
        var anyRevealed = (pwEl && pwEl.type === 'text') || (cpwEl && cpwEl.type === 'text');
        display.style.display = anyRevealed ? 'block' : 'none';
    }

    // -----------------------------------------------------------------
    // Auto-generate a strong random password.
    // Rules: min 12 chars, at least one uppercase, one lowercase,
    // one digit, and one special character.
    // Both Password and Confirm Password are filled automatically;
    // the generated value is displayed so the admin can note it down.
    // -----------------------------------------------------------------
    window.generateStrongPassword = function () {
        var upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var lower   = 'abcdefghijklmnopqrstuvwxyz';
        var digits  = '0123456789';
        var special = '!@#$%^&*()-_=+[]{}|;:,.<>?';
        var all     = upper + lower + digits + special;
        var length  = 14; // comfortable yet strong

        var pw = '';
        // Guarantee at least one char from each character class
        pw += upper  [Math.floor(Math.random() * upper.length)];
        pw += lower  [Math.floor(Math.random() * lower.length)];
        pw += digits [Math.floor(Math.random() * digits.length)];
        pw += special[Math.floor(Math.random() * special.length)];

        // Fill the rest randomly
        for (var i = 4; i < length; i++) {
            pw += all[Math.floor(Math.random() * all.length)];
        }

        // Fisher-Yates shuffle so the guaranteed chars aren't always at the front
        pw = pw.split('').sort(function () { return Math.random() - 0.5; }).join('');

        // Fill both password fields
        var pwEl  = document.getElementById('adminUserPassword');
        var cpwEl = document.getElementById('adminUserConfirmPassword');
        if (pwEl)  { pwEl.value  = pw; pwEl.type  = 'text'; }
        if (cpwEl) { cpwEl.value = pw; cpwEl.type = 'text'; }

        // Sync the toggle button icons to match the exposed type
        document.querySelectorAll('.admin-pw-toggle .material-icons').forEach(function (ico) {
            ico.textContent = 'visibility_off';
        });
        document.querySelectorAll('.admin-pw-toggle').forEach(function (btn) {
            btn.setAttribute('aria-label', 'Hide password');
        });

        // Show the generated password to the admin
        var display = document.getElementById('gen_pw_display');
        var val     = document.getElementById('gen_pw_value');
        if (display && val) {
            val.textContent = pw;
            display.style.display = 'block';
        }

        // Clear any previous validation errors
        $('#err_admin_pw').hide().text('');

        return false;
    };

    // -----------------------------------------------------------------
    // Send-invite checkbox <-> password fields toggle (admin-set path)
    // -----------------------------------------------------------------
    $(document).on('change', '#send_invite_chk', function () {
        var sendInvite = $(this).is(':checked') ? '1' : '0';
        $('#send_invite').val(sendInvite);
        if (sendInvite === '0') {
            $('#admin_pw_fields').show();
        } else {
            $('#admin_pw_fields').hide();
            $('#adminUserPassword, #adminUserConfirmPassword').val('');
            $('#err_admin_pw').hide().text('');
        }
    });

    // Wrap the existing memberCustomer onsubmit handler so we can run the
    // admin-password check *before* the legacy email/SMTP path takes over.
    // memberCustomer() lives in webroot/js/script_v1.js; it returns false to
    // block submission. We let it run first, then add our own check.
    (function () {
        var form = document.getElementById('myform');
        if (!form) return;
        // The form already has an inline `onsubmit="return memberCustomer(...)"`.
        // jQuery `submit` runs *before* native submission, so use that for the
        // additional password check — but only when manual-password mode is on.
        $(form).on('submit.adminPw', function (e) {
            if ($('#send_invite').val() !== '0') {
                return; // legacy invite path
            }
            var pw = $('#adminUserPassword').val() || '';
            var cpw = $('#adminUserConfirmPassword').val() || '';
            var $err = $('#err_admin_pw');
            $err.hide().text('');
            if (pw.length < 8) {
                $err.text('<?php echo __('Password must be at least 8 characters long.'); ?>').show();
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
            if (pw !== cpw) {
                $err.text('<?php echo __('Passwords do not match.'); ?>').show();
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });
    })();

    // Hide the generated-password display if the admin edits either field manually
    $(document).on('input', '#adminUserPassword, #adminUserConfirmPassword', function () {
        var display = document.getElementById('gen_pw_display');
        if (display) display.style.display = 'none';
    });

    // Hide the generated-password display when the admin-password section is hidden
    $(document).on('change', '#send_invite_chk', function () {
        if ($(this).is(':checked')) {
            var display = document.getElementById('gen_pw_display');
            if (display) display.style.display = 'none';
        }
    });

    // Handle role change to update agent/client label
    $(document).on('change', '#select_role', function() {
        var selectedText = $(this).find('option:selected').text().trim();
        var agentLabel = $('label[for="allow_agent"]');
        
        if (agentLabel.length > 0) {
            if (selectedText.toLowerCase() === 'client') {
                agentLabel.text('<?php echo __('Is Client to CRMLeaf'); ?>');
            } else if (selectedText.toLowerCase() === 'user') {
                agentLabel.text('<?php echo __('Is Agent'); ?>');
            } else {
                agentLabel.text('<?php echo __('Is Agent'); ?>');
            }
        }
    });
</script>