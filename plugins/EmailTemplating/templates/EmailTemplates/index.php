<?php
use Cake\Core\Configure;

/**
 * Email Templates settings — Vue mount point.
 *
 * @var \App\View\AppView $this
 */

$this->assign('title', __('Email Templates'));

$isDev = Configure::read('debug');

if ($isDev && Configure::read('EmailTemplating.viteDevServerDomain')) {
    $viteProtocol = Configure::read('EmailTemplating.viteDevServerProtocol') ?: 'https';
    $viteDomain = Configure::read('EmailTemplating.viteDevServerDomain');
    $vitePort = Configure::read('EmailTemplating.viteDevServerPort') ?: 5175;
    $viteBase = "{$viteProtocol}://{$viteDomain}:{$vitePort}";

    echo $this->Html->script("{$viteBase}/@vite/client", ['type' => 'module']);
    echo $this->Html->script("{$viteBase}/templates/element/email_templating/components/main.js", ['type' => 'module']);
} else {
    $jsDir = ROOT . DS . 'plugins' . DS . 'EmailTemplating' . DS . 'webroot' . DS . 'js';
    $files = is_dir($jsDir) ? scandir($jsDir) : [];

    echo $this->Html->css('EmailTemplating./js/email-templating-mdi');
    echo $this->Html->css('EmailTemplating./js/email-templating-vuetify');
    echo $this->Html->css('EmailTemplating./js/email-templating-app');

    foreach ($files as $file) {
        if (preg_match('/^email-templating-vue-vendor-.*\.js$/', $file)) {
            echo $this->Html->script('EmailTemplating./js/' . $file, ['type' => 'module']);
        }
    }
    foreach ($files as $file) {
        if (preg_match('/^email-templating-vuetify-.*\.js$/', $file)) {
            echo $this->Html->script('EmailTemplating./js/' . $file, ['type' => 'module']);
        }
    }
    foreach ($files as $file) {
        if (preg_match('/^email-templating-axios-.*\.js$/', $file)) {
            echo $this->Html->script('EmailTemplating./js/' . $file, ['type' => 'module']);
        }
    }
    echo $this->Html->script('EmailTemplating./js/email-templating-app.js', ['type' => 'module']);
}
?>

<div class="task_listing thwidth">
    <div id="emailTemplatingApp"></div>
</div>

<script>
    window.EMAIL_TEMPLATING_CONFIG = <?php echo json_encode([
        'apiListUrl' => $this->Url->build([
            'plugin' => 'EmailTemplating',
            'prefix' => 'Api',
            'controller' => 'EmailTemplates',
            'action' => 'index',
        ]),
        'apiBaseUrl' => '/email-templating/api/email-templates',
        'csrfToken' => $this->request->getAttribute('csrfToken'),
        'companyId' => defined('SES_COMP') ? SES_COMP : 0,
    ]); ?>;
</script>
