<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <link rel="shortcut icon" href="<?= $this->Url->webroot('favicon.ico') ?>" />
    <!-- Google Fonts -->
    <!-- Modified for specifying cache validator. It is working fine -->
    <meta name="robots" content="noindex,nofollow" />
    <?php
    echo $this->Html->meta('icon');
    echo $this->Html->css(['bootstrap.min.css', 'bootstrap-material-design.min.css', 'ripples.min.css', 'custom_outer.css?v=' ]);
    echo $this->Html->css('css_outer/style.css?v=' );
    echo $this->Html->css(['css_outer/animate.css', 'css_outer/fonts.css', 'css_outer/bootstrap.css']);

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
    <?= $this->Html->script('bootstrap.min.js', ['defer' => true]); ?>
    <?= $this->Html->script('material.min.js', ['defer' => true]); ?>
    <?= $this->Html->script('ripples.min.js', ['defer' => true]); ?>
    <!-- Modified for specifying cache validator -->
    <?php echo $this->Html->css(['material-icon']) ?>
    <?= $this->Html->script('outer_js/jquery.magnific-popup.min.js', ['defer' => true]); ?>
    <?= $this->Html->script('outer_js/jquery.nav.js', ['defer' => true]); ?>
    <?= $this->Html->script('outer_js/jquery.scrollTo-min.js', ['defer' => true]); ?>
    <?php // Last stylesheet — see the note in default_inner.php.
    echo $this->Html->css('typography.css?v=' . ASSET_RELEASE); ?>
    <?= $this->fetch('script-block-first');?>
</head>
<body class="head_back" id="headbody">
    <div id="cover" class="outer" style="filter:alpha(opacity=50);"></div>
    <?= $this->fetch('content') ?>
    <script type="text/javascript">
        $(function() {
            $.material.init();
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
</body>
</html>
