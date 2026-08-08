<script>
    $(document).ready(function () {
        $('.app-container').show();
        var visitortime = new Date();
        var visitortimezone = -visitortime.getTimezoneOffset() / 60;
        $('#timezone_id').val(visitortimezone);

        // Password toggle functionality
        $('#pass-toggle').on('click', function (e) {
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

        $('#pass-toggle').on('mousedown', function (e) {
            e.preventDefault();
        });

        // Auto-suggest company name from email
        var companySuggested = false;
        $('#email').on('blur change', function () {
            var email = $.trim($(this).val());
            var companyField = $('#company');
            if (!email || companyField.val().trim()) return;

            var parts = email.split('@');
            if (parts.length < 2) return;

            var domain = parts[1];
            var local = parts[0];
            var freeProviders = ['gmail', 'yahoo', 'hotmail', 'live', 'outlook', 'rediff', 'zoho', 'icloud', 'mail', 'aol', 'protonmail'];
            var candidate = domain.split('.')[0];

            if (freeProviders.indexOf(candidate.toLowerCase()) !== -1) {
                candidate = local;
            }

            candidate = candidate.replace(/[^a-zA-Z0-9]/g, '');
            if (candidate) {
                companyField.val(candidate.charAt(0).toUpperCase() + candidate.slice(1));
                companySuggested = true;
            }

            // Also suggest name if empty
            var nameField = $('#name');
            if (!nameField.val().trim()) {
                var namePart = local.replace(/[^a-zA-Z]/g, ' ').replace(/\s+/g, ' ').trim();
                if (namePart) {
                    nameField.val(namePart.split(' ').map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' '));
                }
            }
        });
    });
</script>
<?php $this->assign('title', 'Sign Up'); ?>
<div class="app-container" style="display: none;">
    <!-- Left Panel: Brand Experience -->
    <div class="brand-side">
        <div>
            <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum"
                    style="height: 40px; width: auto;" />
            </a>

            <h1 class="brand-h1">Simplify work.<br><span>Deliver</span> results.</h1>
            <p class="brand-p">
                All-in-one project management for teams that want to stay organized, productive, and ahead of the
                deadline.
            </p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="check-icon"><span class="glyphicon glyphicon-ok"></span></div>
                    <div>
                        <strong style="display: block; font-size: 15px;">Plan & Track</strong>
                        <span style="color: var(--slate-500); font-size: 13px; font-weight: 500;">Interactive Gantt
                            charts and Kanban boards.</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check-icon"><span class="glyphicon glyphicon-ok"></span></div>
                    <div>
                        <strong style="display: block; font-size: 15px;">Manage Resources</strong>
                        <span style="color: var(--slate-500); font-size: 13px; font-weight: 500;">Optimize team
                            utilization and workload.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Sign Up Form -->
    <div class="form-side">
        <div class="form-container">
            <section id="step-account" class="step-section active">
                <div class="form-header">
                    <h2>Get Started Now</h2>
                    <p>Set up your self-hosted project management in 60 seconds.</p>
                </div>

                <div class="invalid-msg" style="text-align: center; margin-bottom: 15px;">
                    <?= $this->Flash->render() ?>
                </div>

                <div id="signupsuccess" style="text-align:center;width:100%;display:none;">
                    <img src="<?php echo HTTP_ROOT; ?>img/inbox.png" title="Inbox(1)" alt="Inbox(1)" />
                    <h3 style="text-align:center;color:#FF7E00;font-size:24px;padding-top:10px;">
                        Account Created Successfully!</h3>
                    <div style="text-align:center;color:#333333;padding-top:10px;font-size:16px;">
                        Redirecting you to your tasks...
                    </div>
                </div>

                <?php echo $this->Form->create(null, array('id' => 'signupForm', 'onsubmit' => 'return validateForm()')); ?>

                <div class="form-group field_wrapper">
                    <label>Full Name</label>
                    <?php echo $this->Form->text('name', [
                        'class' => 'form-control',
                        'placeholder' => 'Your full name',
                        'id' => 'name',
                        'label' => false,
                        'required' => false,
                        'maxlength' => 100
                    ]); ?>
                    <p id="name-error" class="error-msg"></p>
                </div>

                <div class="form-group field_wrapper">
                    <label>Company Name <span style="color:#ef4444;">*</span></label>
                    <?php echo $this->Form->text('company', [
                        'class' => 'form-control',
                        'placeholder' => 'Your company or team name',
                        'id' => 'company',
                        'label' => false,
                        'required' => false,
                        'maxlength' => 100
                    ]); ?>
                    <p id="company-error" class="error-msg"></p>
                </div>

                <div class="form-group field_wrapper">
                    <label>Business Email <span style="color:#ef4444;">*</span></label>
                    <?php echo $this->Form->text('email', [
                        'class' => 'form-control',
                        'placeholder' => 'name@companydomain.com',
                        'id' => 'email',
                        'autocomplete' => 'off',
                        'label' => false,
                        'required' => false
                    ]); ?>
                    <p id="email-error" class="error-msg"></p>
                </div>

                <div class="form-group field_wrapper">
                    <label>Password <span style="color:#ef4444;">*</span></label>
                    <div class="password-wrapper">
                        <?php echo $this->Form->password('password', [
                            'class' => 'form-control',
                            'placeholder' => 'Minimum 8 characters required',
                            'id' => 'password',
                            'autocomplete' => 'off',
                            'label' => false,
                            'required' => false,
                            'maxlength' => 30
                        ]); ?>
                        <button class="eye-toggle" id="pass-toggle" type="button" aria-label="Show password"
                            title="Toggle password visibility">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </button>
                    </div>
                    <div id="password_err" class="error-msg"></div>
                </div>

                <div id="email_exist" style="display:none;font-size:12px;color:#ef4444;margin-top:5px;font-weight:600;">
                </div>
                <input type="hidden" name="data[User][timezone_id]" id="timezone_id" value="">

                <?= $this->Form->button(__('Create Free Account'), [
                    "value" => "Save",
                    "name" => "submit_button",
                    "id" => "submit_button",
                    "type" => "button",
                    "class" => "btn btn-block btn-submit",
                    "onclick" => "return validateForm()"
                ]); ?>

                <?php echo $this->Form->end(); ?>

                <img src="<?php echo HTTP_ROOT . "img/images/case_loader2.gif"; ?>" id="submit_loader"
                    style="display:none;margin:10px auto;width:32px;height:32px;" />

                <div id="donot_refresh" style="display:none;text-align:center;margin-top:20px;padding:15px;color:#666;">
                    <div style="margin-bottom:10px;">
                        Just a moment... we're setting up your account.<br />
                        Please don't refresh or close this page.
                    </div>
                </div>

            </section>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#name').focus();
    });

    function validateForm() {
        var name = $.trim($("#name").val());
        var company = $.trim($("#company").val());
        var email = $.trim($("#email").val());
        var password = $.trim($("#password").val());
        var emailRegEx = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var error_flag = true;

        $(".field_wrapper").removeClass('has-error');
        $("#email_exist, #password_err, #email-error, #name-error, #company-error").hide();

        if (!company) {
            $('#company').parent('.field_wrapper').addClass('has-error');
            $("#company-error").show().html('Please enter your company name.');
            if (error_flag) $("#company").focus();
            error_flag = false;
        }

        if (!email) {
            $('#email').parent('.field_wrapper').addClass('has-error');
            $("#email-error").show().html('Please enter your email address.');
            if (error_flag) $("#email").focus();
            error_flag = false;
        } else if (!email.match(emailRegEx)) {
            $('#email').parent('.field_wrapper').addClass('has-error');
            $("#email-error").show().html('Please enter a valid email address.');
            if (error_flag) $("#email").focus();
            error_flag = false;
        }

        if (!password) {
            $('#password').parent().parent('.field_wrapper').addClass('has-error');
            $("#password_err").show().html('Please enter a password.');
            if (error_flag) $("#password").focus();
            error_flag = false;
        } else if (password.length < 8) {
            $('#password').parent().parent('.field_wrapper').addClass('has-error');
            $("#password_err").show().html('Password must be at least 8 characters long.');
            if (error_flag) $("#password").focus();
            error_flag = false;
        } else if (password.length > 30) {
            $('#password').parent().parent('.field_wrapper').addClass('has-error');
            $("#password_err").show().html('Password cannot exceed 30 characters.');
            if (error_flag) $("#password").focus();
            error_flag = false;
        }

        if (!error_flag) return false;

        submitSignupRequest(name, company, email, password);
        return false;
    }

    function submitSignupRequest(name, company, email, password) {
        $("#submit_button").hide();
        $("#submit_loader").show();
        $('#donot_refresh').show();

        var formData = {
            name: name,
            company: company,
            seo_url: company.toLowerCase().replace(/[^a-z0-9]/g, ''),
            email: email,
            password: password,
            timezone_id: $("#timezone_id").val()
        };

        $.ajax({
            url: "<?= $this->Url->build(['controller' => 'Users', 'action' => 'registerUser']) ?>",
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.loggedin === 'loggedin') {
                    $('.form-header').hide();
                    $('.invalid-msg').hide();
                    $('#signupForm').hide();
                    $('#signupsuccess').show();
                    $('#donot_refresh').hide();
                    setTimeout(function () {
                        window.location.href = "<?= $this->Url->build(['controller' => 'TaskViews', 'action' => 'index']) ?>";
                    }, 1500);
                } else {
                    $('#donot_refresh').hide();
                    $("#submit_button").show();
                    $("#submit_loader").hide();
                    $("#email_exist").show().html(response.msg || 'Registration failed. Please try again.');
                }
            },
            error: function () {
                $('#donot_refresh').hide();
                $("#submit_button").show();
                $("#submit_loader").hide();
                alert('Something went wrong. Please try again.');
            }
        });
    }
</script>
