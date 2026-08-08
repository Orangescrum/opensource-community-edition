<?php 
echo $this->Html->script(['pswd-strength/pass-strength'], ['block' => 'script-block-first']);
echo $this->Html->css(['pswd-strength/pass-strength'], ['block' => 'css-block-first']); 
?>
<script>
    function validatepass() {
        var newpass = document.getElementById('password');
        var repass = document.getElementById('repass');
        var errMsg;
        var done = 1;
        
        $('.field_wrapper').removeClass('has-error');
        $('#err_pass').hide();
        
        if (newpass.value.trim() == "") {
            errMsg = "Password cannot be blank!";
            $('#password').parent().parent('.field_wrapper').addClass('has-error');
            done = 0;
        } else if (newpass.value.length < 8 || newpass.value.length > 30) {
            errMsg = "Password should be between 8-30 characters!";
            $('#password').parent().parent('.field_wrapper').addClass('has-error');
            done = 0;
        } else if (repass.value.trim() == "") {
            errMsg = "Re-Type Password cannot be blank!";
            $('#repass').parent().parent('.field_wrapper').addClass('has-error');
            done = 0;
        } else if (repass.value != newpass.value) {
            errMsg = "Passwords do not match!";
            $('#repass').parent().parent('.field_wrapper').addClass('has-error');
            done = 0;
        }
        
        if (done == 0) {
            $('#err_pass').text(errMsg).show();
            return false;
        } else {
            $('#err_pass').hide();
            $('#savpass').hide();
            $('#savload').show();
            $('#forgotpassForm').submit();
            return true;
        }
    }

    function noSpace(e) {
        var unicode = e.charCode ? e.charCode : e.keyCode;
        if (unicode != 8) {
            if (unicode == 32) {
                return false;
            }
        }
        return true;
    }

    $(document).ready(function () {
        $('.app-container').show();
        $('#txt_email').focus();
        $('#myPassword').strength_meter();

        // Password toggle functionality for new password
        $('#pass-toggle-new').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var passwordField = $('#password');
            var currentType = passwordField.attr('type');
            var icon = $(this).find('.glyphicon');
            
            if (currentType === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                $(this).attr('aria-label', 'Hide password');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
                $(this).attr('aria-label', 'Show password');
            }
        });

        // Password toggle functionality for re-type password
        $('#pass-toggle-retype').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var passwordField = $('#repass');
            var currentType = passwordField.attr('type');
            var icon = $(this).find('.glyphicon');
            
            if (currentType === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                $(this).attr('aria-label', 'Hide password');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
                $(this).attr('aria-label', 'Show password');
            }
        });

        // Prevent form submission when clicking the eye toggle
        $('#pass-toggle-new, #pass-toggle-retype').on('mousedown', function(e) {
            e.preventDefault();
        });

        // Form validation for forgot password - inline to avoid conflicts with common_outer.js
        $('#forgotPasswordForm').on('submit', function(e) {
            var email = $('#txt_email').val();
            var errMsg = '';
            
            // Clear previous errors
            $('#email-error').hide();
            $('#txt_email').parent('.field_wrapper').removeClass('has-error');
            
            if (email.trim() == "") {
                errMsg = "Please enter an email.";
                $('#email-error').text(errMsg).show();
                $('#txt_email').parent('.field_wrapper').addClass('has-error');
                return false;
            } else {
                var emailRegEx = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!email.match(emlRegExpRFC) || email.search(/\.\./) != -1) {
                    errMsg = "Invalid email address!";
                    $('#email-error').text(errMsg).show();
                    $('#txt_email').parent('.field_wrapper').addClass('has-error');
                    return false;
                }
            }
            
            // Validation passed
            $('#submit_button').hide();
            $('#submit_loader').show();
            return true;
        });
    });
</script>

<?php $this->assign('title', 'Forgot Password'); ?>

<?php if (isset($passemail) && !empty($passemail)) {
} else {
    $passemail = '10';
} ?>
<?php if (isset($chkemail) && !empty($chkemail)) {
    $chkemail = '10';
} else {
    $chkemail = '11';
} ?>

<div class="app-container" style="display: none;">
    <!-- Left Panel: Brand Experience -->
    <div class="brand-side">
        <div>
            <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum" style="height: 40px; width: auto;" />
            </a>
            
            <?php if ($chkemail == "11" && $passemail == "10"): ?>
                <h1 class="brand-h1">Reset your<br><span>Password</span></h1>
                <p class="brand-p">
                    Don't worry, it happens to everyone. Enter your email address and we'll send you a link to reset your password.
                </p>
            <?php elseif ($passemail == "12"): ?>
                <h1 class="brand-h1">Create new<br><span>Password</span></h1>
                <p class="brand-p">
                    Choose a strong password that you haven't used before. Make it memorable but secure.
                </p>
            <?php else: ?>
                <h1 class="brand-h1">Password<br><span>Updated!</span></h1>
                <p class="brand-p">
                    Your password has been successfully changed. You can now sign in with your new password.
                </p>
            <?php endif; ?>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Secure & Protected</strong>
                        <span>Your account security is our top priority</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Quick Recovery</strong>
                        <span>Get back to work in just a few clicks</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Form -->
    <div class="form-side">
        <div class="form-container">
            
            <?php if ($chkemail == "11" && $passemail == "10"): ?>
                <!-- Request Password Reset Form -->
                <section id="step-account" class="step-section active">
                    <div class="form-header">
                        <h2>Forgot Password?</h2>
                        <p>No problem! Enter your email and we'll send you reset instructions</p>
                    </div>

                    <div class="invalid-msg" style="text-align: center; margin-bottom: 15px;">
                        <?= $this->Flash->render() ?>
                    </div>

                    <?php echo $this->Form->create(null, ['url' => ['action' => 'forgotPassword'], 'id' => 'forgotPasswordForm']); ?>
                    
                    <div class="form-group field_wrapper">
                        <label for="txt_email" class="control-label">Email Address</label>
                        <?= $this->Form->control('email', [
                            'id' => 'txt_email',
                            'type' => 'email',
                            'required' => false,
                            'class' => 'form-control',
                            'placeholder' => 'you@company.com',
                            'autocomplete' => 'email',
                            'maxlength' => '100',
                            'value' => !empty($queryParams['email']) ? urldecode($queryParams['email']) : '',
                            'label' => false
                        ]) ?>
                        <div id="email-error" style="display:none;font-size:12px;color:#ef4444;margin-top:5px;font-weight:600;"></div>
                    </div>

                    <input type="hidden" name="hidtxt" value="<?php if (isset($queryParams['login'])) { echo $queryParams['login']; } ?>" readonly="true">
                    <input type="hidden" id="user_id" name="user_id" value="<?php if (isset($user_id)) { echo $user_id; } ?>" readonly="true">

                    <div id="submit_button">
                        <?= $this->Form->button(__('Send Reset Link'), [
                            'type' => 'submit',
                            'name' => 'submit_pwd',
                            'class' => 'btn btn-submit'
                        ]); ?>
                    </div>

                    <div id="submit_loader" style="display:none; text-align:center; margin-top: 15px;">
                        <img src="<?php echo HTTP_IMAGES; ?>images/feed.gif?v=<?php echo RELEASE; ?>" alt="Loading" />
                    </div>


                    <?php echo $this->Form->end(); ?>

                    <div class="login-footer">
                        <p>
                            Remember your password? 
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                                Sign in
                            </a>
                        </p>
                    </div>
                </section>

            <?php elseif ($passemail == "12"): ?>
                    <!-- Reset Password Form -->
                <section id="step-account" class="step-section active">
                    <div class="form-header">
                        <h2>Create New Password</h2>
                        <p>Enter a strong password that you haven't used before</p>
                    </div>

                    <div id="err_pass" class="error-msg" style="display:none;"></div>

                    <?php echo $this->Form->create(null, ['url' => ['action' => 'forgotPassword'], 'id' => 'forgotpassForm']); ?>
                    
                    <div class="form-group field_wrapper">
                        <label for="password" class="control-label">New Password</label>
                        <div id="myPassword"></div>
                    </div>

                    <div class="form-group field_wrapper">
                        <label for="repass" class="control-label">Re-type Password</label>
                        <div class="password-wrapper">
                            <?= $this->Form->control('repass', [
                                'type' => 'password',
                                'class' => 'form-control',
                                'id' => 'repass',
                                'maxlength' => '15',
                                'onKeyPress' => 'return noSpace(event)',
                                'autocomplete' => 'new-password',
                                'placeholder' => 'Re-enter your password',
                                'label' => false
                            ]) ?>
                            <button type="button" class="eye-toggle" id="pass-toggle-retype" aria-label="Show password">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="hidtxt" value="<?php if (isset($queryParams['login'])) { echo $queryParams['login']; } ?>" readonly="true">
                    <input type="hidden" id="user_id" name="user_id" value="<?php if (isset($user_id)) { echo $user_id; } ?>" readonly="true">
                    <input type="hidden" id="qstr_chk" name="qstr_chk" value="<?php if (isset($queryParams['qstr'])) { echo $queryParams['qstr']; } ?>" readonly="true">

                    <div id="savpass">
                        <button type="button" value="Submit" name="submit_pwd" class="btn btn-submit" onclick="validatepass();">
                            Reset Password
                        </button>
                    </div>

                    <div id="savload" style="display:none; text-align:center; margin-top: 15px;">
                        <img src="<?php echo HTTP_IMAGES; ?>images/feed.gif?v=<?php echo RELEASE; ?>" alt="Loading" />
                    </div>

                    <div class="login-footer">
                        <p>
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                                Back to Sign In
                            </a>
                        </p>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </section>

            <?php elseif ($chkemail == "10"): ?>
                <!-- Success Message -->
                <section id="step-account" class="step-section active">
                    <div style="text-align: center; padding: 40px 0;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <span class="glyphicon glyphicon-ok" style="font-size: 40px; color: white;"></span>
                        </div>
                        
                        <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 15px; color: #0f172a;">
                            Password Changed!
                        </h2>
                        
                        <p style="font-size: 16px; color: var(--slate-600); margin-bottom: 30px;">
                            Your password has been successfully updated. You can now sign in with your new credentials.
                        </p>

                        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>" 
                           class="btn btn-submit"
                           style="display: inline-block; text-decoration: none;">
                            Sign In Now
                        </a>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </div>
</div>