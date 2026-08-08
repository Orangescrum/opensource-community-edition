<!doctype html>
<?php
$locale = \Cake\I18n\I18n::getLocale();
$direction = 'ltr';
if (in_array($locale, ['ar', 'ara', 'he', 'fa', 'ur'])) {
    $direction = 'rtl';
}
?>
<html lang="<?php echo $locale; ?>" dir="<?php echo $direction; ?>">

<head>
    <meta charset="utf-8">
    <?php echo $this->element('metadata'); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php echo $this->Html->meta('icon'); ?>
    <?php echo $this->Html->css(['material-icon.css?v=' . ASSET_RELEASE]) ?>
    <link rel="stylesheet" href="<?php echo HTTP_ROOT; ?>css/print.css?v=<?php echo ASSET_RELEASE; ?>" type="text/css" media="print" />
    <?php
    // Load Toast UI Editor only for TestCaseManager plugin
    if (defined('PLUGIN_NAME') && PLUGIN_NAME === 'TestCaseManager') {
        echo $this->Html->css(HTTP_ROOT . 'css/toastui-editor.min.css?v=' . ASSET_RELEASE);
        echo $this->Html->script(HTTP_ROOT . 'js/toastui-editor-all.min.js?v=' . ASSET_RELEASE);
    }

    $theme = str_replace('-45deg', '', strtolower(defined('THEME') ? THEME : 'default'));
    
    echo $this->Html->css([
        'bootstrap.min.css?v=' . ASSET_RELEASE,
        'bootstrap-material-design.min.css?v=' . ASSET_RELEASE,
        'ripples.min.css?v=' . ASSET_RELEASE,
        'jquery.dropdown.css?v=' . ASSET_RELEASE,
        'custom.css?v=' . ASSET_RELEASE,
        'custom_task.css?v=' . ASSET_RELEASE,
        'custom_new.css?v=' . ASSET_RELEASE,
        'custom_theme.css?v=' . ASSET_RELEASE,
        'custom_theme-' . $theme . '.css?v=' . ASSET_RELEASE,
        'custom_shadcn_theme.css?v=' . ASSET_RELEASE,
        'style_switcher.css?v=' . ASSET_RELEASE,
        'jquery-ui.css?v=' . ASSET_RELEASE,
        'datepicker/bootstrap-datepicker.min.css?v=' . ASSET_RELEASE,
        'selectize.default.css?v=' . ASSET_RELEASE,
        'select2.min.css?v=' . ASSET_RELEASE,
        'bootstrap-datetimepicker.min.css?v=' . ASSET_RELEASE,
        'introjs/introjs.min.css?v=' . ASSET_RELEASE,
    ]);
    if (CONTROLLER == 'Ganttchart') {
        echo $this->Html->css(['jquery.ganttView.css?v=' . ASSET_RELEASE, 'reset.css?v=' . ASSET_RELEASE]);
    }

    if (CONTROLLER == 'easycases') {
        echo $this->Html->css(['project_overview.css?v=' . ASSET_RELEASE]);
    }

    if ((CONTROLLER == 'reports' && PAGE_NAME == 'taskCycleReport') || (CONTROLLER == 'yourworks' && PAGE_NAME == 'index') || (CONTROLLER == 'projects' && PAGE_NAME == 'manage') || (CONTROLLER == 'projecttemplates' && PAGE_NAME == 'index') || PLUGIN_NAME == 'SuperSet') {
        echo $this->Html->css('vue/vuetify.css?v=' . ASSET_RELEASE);
        echo $this->Html->css('materialdesignicons.min.css?v=' . ASSET_RELEASE);
        if (\Cake\Core\Configure::read('debug')) {
            echo $this->Html->script(['vue/vue.js?v=' . ASSET_RELEASE, 'vue/vuetify.js?v=' . ASSET_RELEASE, 'vue/vue-router.js?v=' . ASSET_RELEASE]);
        } else {
            echo $this->Html->script(['vue/vue.min.js?v=' . ASSET_RELEASE, 'vue/vuetify.min.js?v=' . ASSET_RELEASE, 'vue/vue-router.min.js?v=' . ASSET_RELEASE]);
        }
    }

    if ((CONTROLLER == 'projects' && PAGE_NAME == 'manage')) {
        echo $this->Html->css(['project-cardview.css?v=' . ASSET_RELEASE]);
    }

    if ((CONTROLLER == 'roles' && PAGE_NAME == 'index')) {
        echo $this->Html->css(['user-role.css?v=' . ASSET_RELEASE]);
    }

    if (PAGE_NAME == 'dashboard') {
        echo $this->Html->css('jquery.contextMenu.min.css?v=' . ASSET_RELEASE);
    }

    if (PAGE_NAME == 'mydashboard' || PAGE_NAME == 'dashboard' || PAGE_NAME == 'milestonelist' || PAGE_NAME == 'user_detail' || (CONTROLLER == 'projects' && PAGE_NAME == 'manage')) {
        echo $this->Html->css('jquery.jscrollpane.css?v=' . ASSET_RELEASE);
        echo $this->Html->css('angular_select.css?v=' . ASSET_RELEASE);
        echo $this->Html->css('xeditable.min.css?v=' . ASSET_RELEASE);
        echo $this->Html->css('select2.css?v=' . ASSET_RELEASE);
    }

    if (PAGE_NAME == 'profile' || PAGE_NAME == 'manage') {
        echo $this->Html->css('img_crop/imgareaselect-animated.css?v=' . ASSET_RELEASE);
    }
    echo $this->Html->css('fcbkcomplete.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('pace-theme-minimal.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('magnific-popup.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('jquery.timepicker.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('jquery.bxslider.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('wick_new.css?v=' . ASSET_RELEASE);
    if (PAGE_NAME == 'help' || PAGE_NAME == 'tour') {
        echo $this->Html->css('help.css?v=' . ASSET_RELEASE);
    }
    $js_arr = ['jquery.min.js?v=' . ASSET_RELEASE];
    echo $this->Html->script($js_arr);
    echo $this->Html->script('moment.js?v=' . ASSET_RELEASE, ['defer']);
    echo $this->Html->script('datepicker/bootstrap-datepicker.min.js?v=' . ASSET_RELEASE, ['defer']);
    echo $this->Html->script('angular.min.js?v=' . ASSET_RELEASE);
    echo $this->Html->script('angular-route.js?v=' . ASSET_RELEASE);
    echo $this->Html->script('angular-sanitize.js?v=' . ASSET_RELEASE, ['defer']);
    echo $this->Html->script('angular-animate.js?v=' . ASSET_RELEASE, ['defer']);
    echo $this->Html->script('jquery.bxslider.min.js?v=' . ASSET_RELEASE, ['defer']);
    echo $this->Html->script('axios.min.js?v=' . ASSET_RELEASE);
    // Prefer the CsrfProtectionMiddleware request attribute, which is always
    // populated for the current request. Reading the cookie alone was empty on
    // the very first page load of a new session (the cookie is only set on the
    // response), so a freshly registered user's SPA sent no token and every
    // AJAX call came back 403 until a manual reload.
    $token = $this->request->getAttribute('csrfToken')
        ?: $this->request->getCookie(env('CSRF_COOKIE_NAME', 'csrfToken'));
    ?>

    <script>
        const _csrfToken = '<?php echo $token; ?>';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-Token'] = _csrfToken;
        axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
        $(document).on('ajaxSend', function (event, xhr, settings) {
            if (xhr && xhr.setRequestHeader) {
                xhr.setRequestHeader('X-CSRF-Token', _csrfToken);
            }
        });
    </script>
    <script type="text/javascript">
        var emlRegExpRFC = RegExp(
            /^[a-zA-Z0-9.�*+/_-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
        );
    </script>
    <style>
        @media all and (-ms-high-contrast:none) {
            .rht_content_cmn {
                padding-left: 170px;
            }
        }
    </style>
    <?php if (PAGE_NAME == 'mydashboardv2') {
        echo $this->fetch('blockUI');
    } ?>

    <script type="text/javascript" src="<?= $this->Url->webroot('js/jq-validate/jquery.validate.js?v=' . ASSET_RELEASE) ?>"></script>

    <?php if (PLUGIN_NAME == 'TestCaseManager') {
    echo $this->Html->css('TestCaseManager.testcase-dashboard.css?v=' . ASSET_RELEASE);
    echo $this->Html->css('TestCaseManager.testcasemanager.css?v=' . ASSET_RELEASE);
    echo $this->Html->script('TestCaseManager.testcase-dashboard.js?v=' . ASSET_RELEASE);
    } ?>

    <?php if ($this->Format->isWikiEnabled()) { ?>
        <?= $this->Html->css('jstree/themes/default/style.min.css?v=' . ASSET_RELEASE); ?>
        <?= $this->Html->script('jstree/jstree.min.js?v=' . ASSET_RELEASE); ?>
    <?php echo $this->Html->script('Wiki.wiki.js?v=' . ASSET_RELEASE); ?>
    <?php } ?>
    
    <?php if (
        (CONTROLLER == 'tasktree' && PAGE_NAME == 'index') 
        || PLUGIN_NAME == 'GitSync'
    ) {
        echo $this->Html->css('vuetify3/vuetify.min.css?v=' . ASSET_RELEASE);
        echo $this->Html->css('materialdesignicons.min.css?v=' . ASSET_RELEASE);
        if (\Cake\Core\Configure::read('debug')) {
            echo $this->Html->script('vue3/vue.global.js?v=' . ASSET_RELEASE);
            echo $this->Html->script('vue3/vue-router.global.js?v=' . ASSET_RELEASE);
        } else {
            echo $this->Html->script('vue3/vue.global.prod.js?v=' . ASSET_RELEASE);
            echo $this->Html->script('vue3/vue-router.global.prod.js?v=' . ASSET_RELEASE);
        }
        echo $this->Html->script('vue3/vuetify.min.js?v=' . ASSET_RELEASE);
    } ?>
    
    <?php
    // Must be the last stylesheet — it overrides the Inter/Roboto/Trebuchet mix
    // hardcoded across the legacy sheets. Anything loaded after this wins.
    echo $this->Html->css('typography.css?v=' . ASSET_RELEASE);
    ?>
</head>
<?php
$leftMenuSize = $Session->read('leftMenuSize');
$body_class = '';
if ($leftMenuSize !== '') {
    $body_class .= $leftMenuSize;
    if ($leftMenuSize === 'mini-sidebar') {
        $body_class .= ' menu_squeeze';
    }
} else {
    $body_class = 'big-sidebar';
}

// Page hook so a template can target the surrounding chrome (the layout spacer,
// the content wrapper) without resorting to :has() on sibling elements.
$body_class .= ' page-' . strtolower(CONTROLLER);
?>

<body class="<?php echo $body_class ?> project-<?php echo $_SESSION['project_methodology'] ?? 'simple'; ?>">
    <?php echo $this->element('header_inner'); ?>
    <?php echo $this->element('maincontent_inner'); ?>
    <?php echo $this->element('footer_inner'); ?>
    <?php echo $this->element('pace'); ?>
    <?php echo $this->element('style_switcher'); ?>
    <?php echo $this->element('label_customizer_i18n_seed'); ?>
    <?php echo $this->element('js_i18n_seed'); ?>

</body>

</html>