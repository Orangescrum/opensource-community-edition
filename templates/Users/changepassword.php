<?php
$isOAuthUser = $isOAuthUser ?? false;
$hasPassword = $hasPassword ?? true;
$hasOAuth = $hasOAuth ?? false;
$forcePasswordChange = $forcePasswordChange ?? false;
?>

<div class="setting_wrapper task_listing cmn_tbl_widspace width_hover_tbl changepassword-page">
    <div class="row">
        <div class="col-lg-8">
            <!-- Password Policy Expiration Alert -->
            <?php if ($forcePasswordChange): ?>
                <div class="alert alert-warning" style="margin-bottom: 20px;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong><?php echo __('Password Expired'); ?></strong>
                    <p style="margin-top: 8px; margin-bottom: 0;">
                        <?php echo __('Your password has expired according to your company\'s password policy. Please change your password to continue.'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Info Alert Section -->
            <?php if ($isOAuthUser): ?>
                <div class="info-alert">
                    <i class="fa fa-info-circle"></i>
                    <span><?php echo __('You signed up using Google OAuth. You can set a password to enable traditional login alongside Google login.'); ?></span>
                </div>
            <?php elseif ($hasOAuth && $hasPassword): ?>
                <div class="info-alert">
                    <i class="fa fa-info-circle"></i>
                    <span><?php echo __('You have both Google OAuth and password login enabled. Changing your password will not affect your Google login.'); ?></span>
                </div>
            <?php endif; ?>

            <!-- Remove Password Section (only if user has OAuth and password) -->
            <?php if ($hasOAuth && $hasPassword): ?>
                <div class="section-card">
                    <p class="section-title">
                        <i class="fa fa-lock" style="margin-right: 8px;"></i>
                        <?php echo __('Remove Password Login'); ?>
                    </p>
                    <p class="section-description">
                        <?php echo __('Since you have Google OAuth enabled, you can remove your password and rely solely on Google login.'); ?>
                    </p>
                    <?php echo $this->Form->create(null, ['url' => '/users/changepassword', 'id' => 'RemovePasswordForm', 'onsubmit' => 'return confirm("' . __('Are you sure you want to remove your password? You will only be able to login using Google OAuth.') . '");']); ?>
                    <input type="hidden" name="remove_password" value="1" />
                    <button type="submit" class="btn btn-sm btn-remove-password">
                        <i class="fa fa-trash" style="margin-right: 6px;"></i>
                        <?php echo __('Remove Password'); ?>
                    </button>
                    <?php echo $this->Form->end(); ?>
                </div>
            <?php endif; ?>
            
            <!-- Password Change Form Section -->
            <div class="section-card">
                <p class="section-title">
                    <i class="fa fa-key" style="margin-right: 8px;"></i>
                    <?php echo $isOAuthUser ? __('Set Password') : __('Change Password'); ?>
                </p>
                
                <?php echo $this->Form->create(null, array('url' => '/users/changepassword', 'id' => 'UserChangepasswordForm', 'onsubmit' => "return checkPasswordMatch('pas_new','pas_retype','old_pass'," . ($isOAuthUser ? '1' : '0') . ")", 'autocomplete' => 'off')); ?>
                <input type="hidden" name="data[User][changepass]" id="changepass" readonly="true" value="0" />
                
                <!-- Current Password Field (for existing password users only) -->
                <?php if (!$isOAuthUser): ?>
                <div class="form-group">
                    <div class="field_wrapper nofloat_wrapper">
                        <?php echo $this->Form->password('data.User.old_pass', ['value' => '', 'id' => 'old_pass', 'maxlength' => '30', 'onKeyPress' => 'return noSpace(event)', 'autocomplete' => 'off', 'placeholder' => __('Enter your current password')]); ?>
                        <div class="field_placeholder mark_mandatory" for="old_pass">
                            <span><?php echo __('Current Password'); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- New Password Field -->
                <div class="form-group">
                    <div class="field_wrapper nofloat_wrapper">
                        <?php echo $this->Form->password('data.User.pas_new', ['value' => '', 'id' => 'pas_new', 'maxlength' => '30', 'onKeyPress' => 'return noSpace(event)', 'placeholder' => __('Enter a strong password')]); ?>
                        <div class="field_placeholder mark_mandatory" for="pas_new">
                            <span><?php echo __('New Password'); ?></span>
                        </div>
                    </div>
                    <div class="password-hint">
                        <i class="fa fa-circle-o-notch"></i>
                        <span id="hints"><?php echo __('Between 8-30 characters'); ?></span>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <div class="field_wrapper nofloat_wrapper">
                        <?php echo $this->Form->password('data.User.pas_retype', ['value' => '', 'id' => 'pas_retype', 'maxlength' => '30', 'onKeyPress' => 'return noSpace(event)', 'autocomplete' => 'off', 'placeholder' => __('Confirm your password')]); ?>
                        <div class="field_placeholder mark_mandatory" for="pas_retype">
                            <span><?php echo __('Confirm Password'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <button type="button" class="btn btn-default btn-sm" onclick="cancelProfile('<?php echo $referer ?? ''; ?>');">
                        <i class="fa fa-times" style="margin-right: 6px;"></i>
                        <?php echo __('Cancel'); ?>
                    </button>
                    <button type="button" name="submit_Pass" id="submit_Pass" class="btn btn-sm btn-primary update-btn" onclick="$('#changepass').val('1');checkCsrfToken('UserChangepasswordForm');">
                        <span id="submit_text"><?php echo $isOAuthUser ? __('Set Password') : __('Change Password'); ?></span>
                        <span id="subprof2" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        // Update hints after delay if needed
        setTimeout(function () {
            if ($('#hints').html() === '<?php echo __('Between 8-30 characters'); ?>') {
                // Already set correctly
            }
        }, 2000);
    });
</script>