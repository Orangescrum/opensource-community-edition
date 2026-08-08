<?php
/**
 * Reset Password Remote Template
 * Used when V4 user resets password from V2 common forgot password page
 */
?>
<?php $this->assign('title', 'Reset Password'); ?>

<div class="app-container">
    <!-- Left Panel: Brand Experience -->
    <div class="brand-side">
        <div>
            <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum" style="height: 40px; width: auto;" />
            </a>
            
            <h1 class="brand-h1">Create new<br><span>Password</span></h1>
            <p class="brand-p">
                Choose a strong password that you haven't used before. Make it memorable but secure.
            </p>

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
            <?= $this->element('auth_testimonial', ['page' => 'reset_password_remote']) ?>
        </div>
    </div>

    <!-- Right Panel: Form -->
    <div class="form-side">
        <div class="form-container">
            
            <?php if (isset($show_security_questions) && $show_security_questions && !empty($security_questions)): ?>
                <!-- Security Questions Verification Form -->
                <section id="step-security" class="step-section active">
                    <div class="form-header">
                        <h2>Verify Your Identity</h2>
                        <p>Please answer your security questions to verify your identity</p>
                    </div>

                    <div class="invalid-msg" style="text-align: center; margin-bottom: 15px;">
                        <?= $this->Flash->render() ?>
                    </div>

                    <?php echo $this->Form->create(null, [
                        'url' => ['action' => 'resetPasswordRemote'],
                        'id' => 'securityQuestionsForm'
                    ]); ?>
                    
                    <?php foreach ($security_questions as $index => $question): ?>
                        <div class="form-group field_wrapper">
                            <label for="security_answer_<?= h($question['id']) ?>" class="control-label">
                                <?= h($question['question_text']) ?>
                            </label>
                            <?php echo $this->Form->control('security_answer_' . $question['id'], [
                                'type' => 'text',
                                'label' => false,
                                'div' => false,
                                'class' => 'form-control',
                                'id' => 'security_answer_' . $question['id'],
                                'required' => true,
                                'autocomplete' => 'off',
                                'placeholder' => 'Enter your answer',
                                'templates' => ['inputContainer' => '{{content}}']
                            ]); ?>
                        </div>
                    <?php endforeach; ?>

                    <input type="hidden" name="token" value="<?php echo h($token); ?>">
                    <input type="hidden" name="verify_security_questions" value="1">
                    
                    <div>
                        <button type="submit" class="btn btn-submit">
                            Verify Answers
                        </button>
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

            <?php else: ?>
                <!-- Reset Password Form -->
                <section id="step-account" class="step-section active">
                    <div class="form-header">
                        <h2>Create New Password</h2>
                        <p>Enter a strong password that you haven't used before</p>
                    </div>

                    <div class="invalid-msg" style="text-align: center; margin-bottom: 15px;">
                        <?= $this->Flash->render() ?>
                    </div>

                    <!-- <div id="err_pass" class="error-msg" style="display:none;"></div> -->

                    <?php echo $this->Form->create(null, [
                        'url' => ['action' => 'resetPasswordRemote'],
                        'id' => 'resetPasswordForm'
                    ]); ?>
                
                <div class="form-group field_wrapper">
                    <label for="password" class="control-label">New Password</label>
                    <div class="password-wrapper">
                        <?php echo $this->Form->control('password', [
                            'type' => 'password',
                            'label' => false,
                            'div' => false,
                            'class' => 'form-control',
                            'id' => 'password',
                            'maxlength' => '50',
                            'autocomplete' => 'off',
                            'title' => 'New Password',
                            'placeholder' => 'Enter your new password',
                            'templates' => ['inputContainer' => '{{content}}']
                        ]); ?>
                        <button type="button" class="eye-toggle" id="pass-toggle-new" aria-label="Show password">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </button>
                    </div>
                </div>

                <div class="form-group field_wrapper">
                    <label for="confirm_password" class="control-label">Re-type Password</label>
                    <div class="password-wrapper">
                        <?php echo $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'label' => false,
                            'div' => false,
                            'class' => 'form-control',
                            'id' => 'confirm_password',
                            'maxlength' => '50',
                            'autocomplete' => 'off',
                            'title' => 'Re-type Password',
                            'placeholder' => 'Re-enter your password',
                            'templates' => ['inputContainer' => '{{content}}']
                        ]); ?>
                        <button type="button" class="eye-toggle" id="pass-toggle-retype" aria-label="Show password">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="token" value="<?php echo h($token); ?>">
                <div id="err_pass" class="error-msg" style="display:none;"></div>
                <div id="savpass">
                    <button type="submit"
                            value="Submit"
                            name="submit_pwd"
                            class="btn btn-submit">
                        Reset Password
                    </button>
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
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Focus first field
    $('#password').focus();
    
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
            $(this).addClass('hide-password');
            $(this).attr('aria-label', 'Hide password');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
            $(this).removeClass('hide-password');
            $(this).attr('aria-label', 'Show password');
        }
    });
    
    // Password toggle functionality for re-type password
    $('#pass-toggle-retype').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var passwordField = $('#confirm_password');
        var currentType = passwordField.attr('type');
        var icon = $(this).find('.glyphicon');
        
        if (currentType === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
            $(this).addClass('hide-password');
            $(this).attr('aria-label', 'Hide password');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
            $(this).removeClass('hide-password');
            $(this).attr('aria-label', 'Show password');
        }
    });
    
    // Prevent form submission when clicking the eye toggle
    $('#pass-toggle-new, #pass-toggle-retype').on('mousedown', function(e) {
        e.preventDefault();
    });
});

// Form validation
$('#resetPasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    var password = $('#password').val();
    var confirmPassword = $('#confirm_password').val();
    var errDiv = $('#err_pass');
    var isValid = true;
    var errorMsg = '';

    // Clear previous errors
    errDiv.hide().html('');

    // Check if password is empty
    if (!password || password.trim() === '') {
        errorMsg = 'Please enter a password.';
        isValid = false;
    }
    // Check minimum length
    else if (password.length < 6) {
        errorMsg = 'Password must be at least 6 characters long.';
        isValid = false;
    }
    // Check if passwords match
    else if (password !== confirmPassword) {
        errorMsg = 'Passwords do not match.';
        isValid = false;
    }

    if (!isValid) {
        errDiv.html(errorMsg).show();
        return false;
    }
    
    // Submit the form
    this.submit();
});

// Allow Enter key to submit
$('#password, #confirm_password').on('keypress', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        $('#resetPasswordForm').submit();
    }
});
</script>
