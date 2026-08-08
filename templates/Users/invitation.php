
<script>
    $(document).ready(function () {
        $('.app-container').show();
        
        setTimeout(function () {
            $('#password').focus();
        }, 500);

        var visitortime = new Date();
        var visitortimezone = -visitortime.getTimezoneOffset() / 60;
        $('#timezone_id').val(visitortimezone);

        // Password toggle functionality
        $('.eye-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var passwordField = $(this).siblings('input');
            var currentType = passwordField.attr('type');
            
            if (currentType === 'password') {
                passwordField.attr('type', 'text');
                $(this).addClass('hide-password');
                $(this).attr('aria-label', 'Hide password');
            } else {
                passwordField.attr('type', 'password');
                $(this).removeClass('hide-password');
                $(this).attr('aria-label', 'Show password');
            }
        });

        $('.eye-toggle').on('mousedown', function(e) {
            e.preventDefault();
        });
    });
</script>
<?php $this->assign('title', 'Set Password'); ?>
<div class="app-container" style="display: none;">
    <!-- Left Panel: Brand Experience -->
    <div class="brand-side">
        <div>
            <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum" style="height: 40px; width: auto;" />
            </a>
            
            <h1 class="brand-h1">Welcome to<br><span>Orangescrum</span></h1>
            <p class="brand-p">
                Join your team and start managing projects efficiently. Stay organized, productive, and in control.
            </p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Real-time Collaboration</strong>
                        <span>Work seamlessly with your team in real-time</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Track Progress Effortlessly</strong>
                        <span>Monitor tasks, milestones, and project health</span>
                    </div>
                </div>
            </div>
            <?= $this->element('auth_testimonial', ['page' => 'login']) ?>
        </div>
    </div>

    <!-- Right Panel: Form Side -->
    <div class="form-side">
        <div class="form-container">
            <section id="step-account" class="step-section active">
                <div class="form-header">
                    <h2>Set Your Password</h2>
                    <p>
                        You've been invited to join <span class="company_highlight"><?= h($company_name ?? '') ?></span>
                    </p>
                </div>

                <div class="email_display">
                    <strong>Email:</strong> <?= h($email ?? '') ?>
                </div>

                <div class="invalid-msg" style="text-align: center; margin-bottom: 20px;">
                    <?= $this->Flash->render() ?>
                </div>

                <?php echo $this->Form->create(null, [
                    'id' => 'invitationForm',
                    'onsubmit' => 'return validateForm()'
                ]); ?>

                <div class="form-group field_wrapper">
                    <label for="password" class="control-label">New Password</label>
                    <div class="password-wrapper">
                        <?php echo $this->Form->password('password', [
                            'class' => 'form-control',
                            'placeholder' => 'Password (6-15 characters)',
                            'id' => 'password',
                            'title' => 'Password (6-15 characters)',
                            'maxlength' => 15,
                            'autocomplete' => 'new-password',
                            'label' => false,
                            'required' => false,
                            'onkeypress' => 'return noSpace(event)'
                        ]); ?>
                        <button type="button" class="eye-toggle" aria-label="Show password"></button>
                    </div>
                    <div id="password_err"></div>
                </div>

                <div class="form-group field_wrapper">
                    <label for="pas_retype" class="control-label">Confirm Password</label>
                    <div class="password-wrapper">
                        <?php echo $this->Form->password('pas_retype', [
                            'class' => 'form-control',
                            'placeholder' => 'Confirm Password',
                            'id' => 'pas_retype',
                            'title' => 'Confirm Password',
                            'maxlength' => 15,
                            'autocomplete' => 'new-password',
                            'label' => false,
                            'required' => false,
                            'onkeypress' => 'return noSpace(event)'
                        ]); ?>
                        <button type="button" class="eye-toggle" aria-label="Show password"></button>
                    </div>
                    <div id="confirm_password_err"></div>
                </div>

                <input type="hidden" name="timezone_id" id="timezone_id" value="">

                <?= $this->Form->button(__('Complete Setup'), [
                    "value" => "Submit",
                    "name" => "submit_button",
                    "id" => "submit_button",
                    "type" => "submit",
                    "class" => "btn btn-submit"
                ]); ?>
                <img src="<?= $this->Url->webroot('img/images/case_loader2.gif') ?>" id="submit_loader" style="display:none; margin: 0 auto;" />

                <div class="login-footer">
                    <p>
                        Already have an account? 
                        <a href="<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                            Login
                        </a>
                    </p>
                </div>
                
                <?php if (!empty($googleOAuthEnabled)): ?>
                <!-- Google OAuth option for invited users -->
                <div class="divider">
                    <hr />
                    <span>or</span>
                </div>
                
                <a href="<?= $this->Url->build(['controller' => 'OAuth', 'action' => 'googleAuth']) ?>" class="btn btn-block btn-google">
                    <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></svg>
                    Use Google Account
                </a>
                <?php endif; ?>
                
                <?php echo $this->Form->end(); ?>
            </section>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validateForm() {
        var password = $.trim($("#password").val());
        var confirmPassword = $.trim($("#pas_retype").val());
        var error_flag = true;

        // Clear previous errors
        $(".field_wrapper").removeClass('has-error');
        $("#password_err, #confirm_password_err").hide();

        // Validate password
        if (!password || password.length < 6 || password.length > 15) {
            $('#password').closest('.field_wrapper').addClass('has-error');
            if (password.length > 0) {
                $("#password_err").show().html('Password should be between 6-15 characters!');
            }
            $("#password").focus();
            error_flag = false;
        }

        // Validate confirm password
        if (!confirmPassword || confirmPassword !== password) {
            $('#pas_retype').closest('.field_wrapper').addClass('has-error');
            if (confirmPassword.length > 0 && confirmPassword !== password) {
                $("#confirm_password_err").show().html('Passwords do not match!');
            }
            if (error_flag) $("#pas_retype").focus();
            error_flag = false;
        }

        if (!error_flag) return false;

        // Show loader
        $("#submit_button").hide();
        $("#submit_loader").show();

        return true;
    }

    // Prevent spaces in password
    function noSpace(e) {
        var unicode = e.charCode ? e.charCode : e.keyCode;
        if (unicode != 8) {
            if (unicode == 32) {
                return false;
            }
        }
        return true;
    }
</script>
