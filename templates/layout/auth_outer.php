<!doctype html>
<?php

use Cake\Core\Configure;
$locale = \Cake\I18n\I18n::getLocale();
$direction = 'ltr';
if (in_array($locale, ['ar', 'ara', 'he', 'fa', 'ur'])) {
    $direction = 'rtl';
}
?>
<html lang="<?php echo $locale; ?>" dir="<?php echo $direction; ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <link rel="shortcut icon" href="<?= $this->Url->webroot('favicon.ico') ?>" />
    <!-- Local Fonts (self-hosted, no CDN) -->
    <link rel="stylesheet" href="<?= $this->Url->webroot('css/fonts-local.css') ?>" />
    <!-- Modified for specifying cache validator. It is working fine -->
    <meta name="robots" content="noindex,nofollow" />

    <?php if (defined('PAGE_NAME') && PAGE_NAME == 'signup' && defined('DOMAIN') && DOMAIN == ($OrangescrumSignUp ?? '')) { ?>
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || []; w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                }); var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-W3H5PWN');</script>
        <!-- End Google Tag Manager -->
    <?php } ?>

    <?php
    echo $this->Html->meta('icon');
    echo $this->Html->css(['auth_page.css?v=']);
    // include if pages "help", "tour", "free_download", "community", "roadmap", "services"
    echo $this->Html->css('help');
    echo $this->fetch('css-block-first');
    ?>
    <style type="text/css">
        .feddback_btn {
            position: fixed;
            right: -6px;
            top: 40%;
        }

        .feddback_btn:hover {
            right: -2px;
        }

        #luckyext__watcher_div {
            top: 18% !important;
        }

        #luckyext__chat_pre_chat_form input {
            font-family: Muli-regular;
            padding: 8px;
            width: 90%;
        }

        #luckyext__submit_btn_area input[type="submit"] {
            background-image: -moz-linear-gradient(center top, #43c86f, #2fb45b) !important;
            border-radius: 5px !important;
            color: #fff !important;
            font-family: 'OPENSANS-REGULAR' !important;
            height: 29px !important;
            padding: 5px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.7) !important;
            text-transform: uppercase;
            width: 35% !important;
        }

        #luckyext__submit_btn_area {
            margin-top: 14px !important;
        }

        #luckyext__chat_box textarea {
            font-size: 14px !important;
        }

        #luckyext__chat_log {
            font-family: Muli-regular;
        }

        #luckyext__msg {
            padding: 3px 1px !important;
        }

        #luckyext_chat_agent_info {
            font-family: 'Lato Bold', Arial, sans-serif;
        }

        #hellobar_container {
            max-height: 38px !important;
        }
    </style>
    <script type="text/javascript">
        var emlRegExpRFC = RegExp(
            /^[a-zA-Z0-9.�*+/_-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
        );
        //For google login and signup end
    </script>
    <!-- Include jQuery and other JavaScript libraries -->
    <?= $this->Html->script('jquery/1.9.1/jquery.min.js'); ?>
    <?= $this->Html->script('index/common_outer.js', ['defer' => true]); ?>
    <?= $this->fetch('script-block-first'); ?>
    <?php echo $this->Html->css("typography.css"); ?>
</head>

<body class="head_back" id="headbody">

    <?php if (defined('PAGE_NAME') && PAGE_NAME == 'signup' && defined('DOMAIN') && DOMAIN == ($OrangescrumSignUp ?? '')) { ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W3H5PWN" height="0" width="0"
                style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    <?php } ?>

    <div id="cover" class="outer" style="filter:alpha(opacity=50);"></div>
    <?= $this->fetch('content') ?>
    
    <script type="text/javascript">
        $(function () {
            if (typeof $.material !== 'undefined' && $.material.init) {
                $.material.init();
            }
            var uri = window.location.href;
            var uri_blog = uri.substr(-12);
            if (uri_blog.toLowerCase() == 'googlesignup') {
                signinWithGoogle();
            }
        });
        function setSuptrackCookie(cname, cvalue, exdays) {
            localStorage.setItem(cname, cvalue);
        }
        function getSuptrackCookie(cname) {
            n = (typeof localStorage.getItem(cname) != 'undefined') ? localStorage.getItem(cname) : '';
            return n;
        }
    </script>

        <?php if (isset($googleOneTapEnabled) && $googleOneTapEnabled && PAGE_NAME == 'signup') { ?>
        
            <script src="https://accounts.google.com/gsi/client" async defer></script>
            <script>
                $(document).ready(function () {
                    // Initialize Google One Tap
                    window.initGoogleOneTap = function () {
                        if (typeof google === 'undefined' || !google.accounts || !google.accounts.id) {
                            return; // Library not loaded, fail silently
                        }

                        try {
                            var loginUrl = '<?= $this->Url->build(['controller' => 'Users', 'action' => 'googleOneTapLogin']) ?>';

                            // Initialize Google One Tap (production-ready configuration)
                            google.accounts.id.initialize({
                                client_id: '<?= $googleClientId ?>',
                                callback: function (response) {
                                    if (!response.credential) return;

                                    // Submit credential to backend (CSRF protection bypassed via JWT verification)
                                    $.ajax({
                                        url: loginUrl,
                                        type: 'POST',
                                        data: { credential: response.credential },
                                        dataType: 'json',
                                        success: function (data) {
                                            // Check if response data is valid
                                            if (!data || typeof data !== 'object') {
                                                alert('Invalid response from server. Please try again.');
                                                return;
                                            }
                                            
                                            if (data.success === true && data.redirect_url) {
                                                window.location.href = data.redirect_url;
                                            } else if (data.success === false || data.status === 'error') {
                                                alert(data.message || 'Authentication failed. Please try again.');
                                            } else {
                                                // Unexpected response format - don't auto-login
                                                alert('Unexpected response from server. Please try again.');
                                            }
                                        },
                                        error: function (xhr) {
                                            var message = 'Authentication failed. Please try again.';
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                message = xhr.responseJSON.message;
                                            }
                                            alert(message);
                                        }
                                    });
                                },
                                cancel_on_tap_outside: false,
                                auto_select: false,
                                itp_support: true
                                // Note: use_fedcm_for_prompt removed for better browser compatibility
                            });

                            // Try to display One Tap prompt - will silently fail if blocked
                            google.accounts.id.prompt();

                        } catch (error) {
                            // Silent fail - user can still use "Continue with Google" button
                        }
                    };

                    // Auto-execute Google One Tap if enabled
                    var googleOneTapEnabled = <?= $googleOneTapEnabled ?>;
                    // Get polling configuration from config
                    var maxAttempts = <?= $maxAttempts ?>;
                    var attempts = 0;
                    var pollInterval = <?= $pollInterval ?>;

                    // Wait for Google GSI library to load, then initialize
                    var waitForGoogleLib = setInterval(function () {
                        if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                            clearInterval(waitForGoogleLib);
                            window.initGoogleOneTap();
                        } else if (attempts++ >= maxAttempts) {
                            clearInterval(waitForGoogleLib);
                            // Silently fail - Google library or One Tap not available
                        }
                    }, pollInterval);
                });
            </script>
        <?php }
        ?>

</body>

</html>