<script>
    $(document).ready(function () {
        $('.app-container').show();
        $('#txt_UserId').focus();

        // Password toggle functionality
        $('#pass-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var passwordField = $('#txt_Password');
            var currentType = passwordField.attr('type');
            var icon = $(this).find('.glyphicon');
            
            if (currentType === 'password') {
                // Show password
                passwordField.attr('type', 'text');
                icon.removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                $(this).attr('aria-label', 'Hide password');
            } else {
                // Hide password
                passwordField.attr('type', 'password');
                icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
                $(this).attr('aria-label', 'Show password');
            }
        });

        // Prevent form submission when clicking the eye toggle
        $('#pass-toggle').on('mousedown', function(e) {
            e.preventDefault();
        });
    });
</script>
<?php $this->assign('title', 'Sign In'); ?>
<div class="app-container" style="display: none;">
    <!-- Left Panel: Brand Experience -->
    <div class="brand-side">
        <div>
            <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum" style="height: 40px; width: auto;" />
            </a>
            
            <h1 class="brand-h1">Orangescrum<br><span>Open Source Community Edition</span></h1>
            <p class="brand-p">
                Self-hosted project and task management you fully own — free software under the AGPL, with no user, project or storage limits.
            </p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Own Your Data</strong>
                        <span>Runs entirely on your own server — nothing phones home</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>No Limits, No Licence Keys</strong>
                        <span>Unlimited users and projects, free under the AGPL</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check-icon">
                        <span class="glyphicon glyphicon-ok"></span>
                    </div>
                    <div>
                        <strong>Tasks, Projects &amp; Workflows</strong>
                        <span>Plan work, track progress and ship with your team</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="form-side">
        <div class="form-container">
            <section id="step-account" class="step-section active">
                <div class="form-header">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to access your workspace</p>
                </div>
                <?php if (defined('EDITION_NAME')) { ?>
                    <div class="edition-tag" style="text-align:center; margin-bottom:15px; font-size:12px; color:#80868b; letter-spacing:.3px;">
                        <?= h(EDITION_NAME) ?><?php if (defined('EDITION_VERSION')) { ?> &middot; v<?= h(EDITION_VERSION) ?><?php } ?>
                    </div>
                <?php } ?>


                <div class="invalid-msg" style="text-align: center; margin-bottom: 15px;">
                    <?= $this->Flash->render() ?>
                </div>

                <?php echo $this->Form->create(null, ['id' => 'UserLogin', 'url' => ['controller' => 'Users', 'action' => 'login'], 'autocomplete' => 'off']); ?>
                
                <div class="form-group field_wrapper">
                    <label for="txt_UserId" class="control-label">Email Address</label>
                    <?= $this->Form->control('email', [
                        'type' => 'email',
                        'required' => true,
                        'class' => 'form-control',
                        'placeholder' => 'you@company.com',
                        'id' => 'txt_UserId',
                        'autocomplete' => 'off',
                        'label' => false
                    ]) ?>
                </div>

                <div class="form-group field_wrapper">
                    <label for="txt_Password" class="control-label">Password</label>
                    <div class="password-wrapper">
                        <?= $this->Form->control('password', [
                            'type' => 'password',
                            'required' => true,
                            'class' => 'form-control',
                            'placeholder' => 'Enter your password',
                            'id' => 'txt_Password',
                            'autocomplete' => 'off',
                            'label' => false
                        ]) ?>
                        <button type="button" class="eye-toggle" id="pass-toggle" aria-label="Show password">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </button>
                    </div>
                </div>

                <?= $this->Form->button(__('Sign In'), [
                    'type' => 'submit',
                    'name' => 'submit_Pass',
                    'id' => 'submit_Pass',
                    'class' => 'btn btn-submit'
                ]); ?>

                <div class="login-footer" style="margin-top:15px; text-align:center;">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'forgotPassword']) ?>">
                        Forgot password?
                    </a>
                </div>

                <?php echo $this->Form->end(); ?>
            </section>
        </div>
    </div>
</div>