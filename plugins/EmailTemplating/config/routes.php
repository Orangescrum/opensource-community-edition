<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin(
    'EmailTemplating',
    ['path' => '/email-templating'],
    function (RouteBuilder $builder) {
        $builder->setRouteClass(DashedRoute::class);

        $builder->connect(
            '/email-template-settings',
            ['controller' => 'EmailTemplates', 'action' => 'index'],
            ['_name' => 'emailTemplateSettings']
        );

        $builder->prefix('Api', ['path' => '/api'], function (RouteBuilder $builder) {
            $builder->get('/common-settings', ['controller' => 'CommonSettings', 'action' => 'view']);
            $builder->post('/common-settings', ['controller' => 'CommonSettings', 'action' => 'save']);
            $builder->post('/common-settings/reset', ['controller' => 'CommonSettings', 'action' => 'reset']);
            $builder->get('/email-config', ['controller' => 'EmailConfig', 'action' => 'view']);
            $builder->post('/email-config', ['controller' => 'EmailConfig', 'action' => 'save']);
            $builder->post('/email-config/test', ['controller' => 'EmailConfig', 'action' => 'test']);
            $builder->get('/email-config/history', ['controller' => 'EmailConfig', 'action' => 'history']);
            $builder->post('/email-config/revert', ['controller' => 'EmailConfig', 'action' => 'revert']);
            $builder->get('/email-templates', ['controller' => 'EmailTemplates', 'action' => 'index']);
            // Bulk import/export — must come before /{key} so "export" / "import"
            // aren't swallowed by the greedy key regex.
            $builder->get('/email-templates/export', ['controller' => 'EmailTemplates', 'action' => 'export']);
            $builder->post('/email-templates/import', ['controller' => 'EmailTemplates', 'action' => 'import']);
            // Sub-action routes MUST come before /{key} so the greedy key regex
            // doesn't swallow paths like "registration_welcome/preview".
            $builder->post(
                '/email-templates/{key}/preview',
                ['controller' => 'EmailTemplates', 'action' => 'preview']
            )->setPass(['key'])->setPatterns(['key' => '[\w\.\/-]+']);
            $builder->post(
                '/email-templates/{key}/test-send',
                ['controller' => 'EmailTemplates', 'action' => 'testSend']
            )->setPass(['key'])->setPatterns(['key' => '[\w\.\/-]+']);
            $builder->post(
                '/email-templates/{key}/reset',
                ['controller' => 'EmailTemplates', 'action' => 'reset']
            )->setPass(['key'])->setPatterns(['key' => '[\w\.\/-]+']);
            $builder->get(
                '/email-templates/{key}',
                ['controller' => 'EmailTemplates', 'action' => 'view']
            )->setPass(['key'])->setPatterns(['key' => '[\w\.\/-]+']);
            $builder->post(
                '/email-templates/{key}',
                ['controller' => 'EmailTemplates', 'action' => 'save']
            )->setPass(['key'])->setPatterns(['key' => '[\w\.\/-]+']);
        });

        $builder->fallbacks();
    }
);

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->scope('/', function (RouteBuilder $builder) {
    $builder->setRouteClass(DashedRoute::class);
    $builder->connect(
        '/email-template-settings',
        ['plugin' => 'EmailTemplating', 'controller' => 'EmailTemplates', 'action' => 'index']
    );
});
