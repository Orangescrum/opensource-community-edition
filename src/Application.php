<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use App\Events\Auth\AuthenticationListener;
use App\Middleware\ServerTimingMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\Exception\MissingPluginException;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManager;
use Cake\Http\BaseApplication;
use Cake\Log\Log;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\SessionCsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();
        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }
        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            try {
                $this->addPlugin('DebugKit');
            } catch (MissingPluginException $e) {

            } catch (\Throwable $th) {

            }
        }

        // Load more plugins here
        $this->addPlugin('Authentication');

        // Queue subsystem — load Cake/Queue and sync the config from
        // `config/queue.php` (already in Configure via config/bootstrap.php)
        // into `QueueManager`'s static registry. Must run BEFORE any plugin
        // whose bootstrap pushes to the queue (Dms, AttendanceLeave, etc.).
        $this->loadQueuePlugin();

        // EmailTemplating is load-bearing infra (templated mail) and stays
        // unconditional. Task attachments are stored locally in core (case_files);
        // the cloud-provider integration was removed for the Community Edition.
        $this->addPlugin('EmailTemplating', ['routes' => true, 'bootstrap' => true]);
        $eventManager = EventManager::instance();
        // Attach Authentication.afterIdentify event listener
        $eventManager->on(new AuthenticationListener());
    }
    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $csrfFwdProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
        $csrfIsHttps  = $csrfFwdProto === 'https'
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $csrfForceSamesite = env('SESSION_COOKIE_SAMESITE', '');
        $csrfForceSecure   = env('SESSION_COOKIE_SECURE', '');

        $csrfSecure = $csrfForceSecure !== ''
            ? filter_var($csrfForceSecure, FILTER_VALIDATE_BOOLEAN)
            : $csrfIsHttps;

        $csrfCookieName = env('CSRF_COOKIE_NAME', 'csrfToken');
        $csrf = new \App\Middleware\SelfHealingCsrfMiddleware([
            'cookieName' => $csrfCookieName,
            'httponly' => true,
            'secure'   => $csrfSecure,
            'samesite' => $csrfForceSamesite !== ''
                ? $csrfForceSamesite
                : 'Lax',
        ]);
        $csrf->skipCheckCallback(function (ServerRequestInterface $request) {
            $params = $request->getAttribute('params');

            return
                $params['controller'] == 'Install' ||
                ($params['controller'] == 'Projects' && $params['action'] == 'addCustomerForm') ||
                ($params['controller'] == 'Users' && ($params['action'] == 'validateEmailurl' || $params['action'] == 'registerUser'));
        });

        // Normalize the `email` field on login-style POSTs to lowercase
        // *before* the AuthenticationMiddleware runs. This is what makes
        // `Abhi@os.com` and `abhi@os.com` log in interchangeably without
        // showing a "wrong credentials" message. Pairs with the
        // `_setEmail()` mutator on the User entity (lowercases on save)
        // and the one-time `NormalizeUserEmailsLowercase` migration
        // (lowercases existing rows).
        // Redirect any request whose Host header doesn't match the configured
        // SESSION_COOKIE_DOMAIN to that domain. Without this, hitting
        // http://localhost:8091 while cookies are scoped to oss.localhost
        // silently drops the session cookie and login appears to fail.
        $sessionCookieDomain = (string)env('SESSION_COOKIE_DOMAIN', '');
        $hostRedirect = function (ServerRequestInterface $request, $handler) use ($sessionCookieDomain) {
            if ($sessionCookieDomain !== '') {
                $uri = $request->getUri();
                $host = $uri->getHost();
                if ($host !== '' && strcasecmp($host, $sessionCookieDomain) !== 0) {
                    $target = $uri->withHost($sessionCookieDomain);
                    return (new \Cake\Http\Response())
                        ->withStatus(302)
                        ->withHeader('Location', (string)$target);
                }
            }
            return $handler->handle($request);
        };

        $loginEmailNormalizer = function (ServerRequestInterface $request, $handler) {
            if (strtoupper($request->getMethod()) === 'POST') {
                $path = $request->getUri()->getPath();
                if (preg_match('#/users/(login|ad-?login|forgot|forgot-?password)#i', $path)
                    || preg_match('#/users/?$#i', $path)
                ) {
                    $body = $request->getParsedBody();
                    if (is_array($body) && isset($body['email']) && is_string($body['email'])) {
                        $body['email'] = strtolower(trim($body['email']));
                        $request = $request->withParsedBody($body);
                    }
                }
            }
            return $handler->handle($request);
        };

        $middlewareQueue
            ->add(new \App\Middleware\TrustProxyMiddleware())
            ->add($hostRedirect)
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add($loginEmailNormalizer)
            ->add(new AuthenticationMiddleware($this))
            ->add($csrf)
            ->add(new \App\Middleware\SecurityHeadersMiddleware())
            ->add(new ServerTimingMiddleware());

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Cake/Repl');
        $this->addOptionalPlugin('Bake');
        $this->addPlugin('Migrations');
        // Load more plugins here
    }

    /**
     * Load the Cake/Queue plugin and sync the queue configuration from
     * `Configure` into `QueueManager`'s static registry.
     *
     * The Queue plugin exposes both the `bin/cake queue worker` command and
     * `Cake\Queue\QueueManager::push()`. Plugins that push during their own
     * bootstrap (Dms, AttendanceLeave, TestCaseManager) all
     * rely on this — without it, the worker never starts and every push
     * throws "Unsupported operand types: null + array".
     *
     * Failures here are logged and swallowed — the rest of the app should
     * still boot even if the Queue plugin is missing (composer install drift,
     * vendor directory corruption, etc.). Anything that tries to push will
     * surface its own error in that case.
     */
    private function loadQueuePlugin(): void
    {
        try {
            if (!class_exists('\\Cake\\Queue\\Plugin')) {
                Log::warning('Cake/Queue plugin class not found — queue subsystem disabled');
                return;
            }

            try {
                $this->addPlugin('Cake/Queue');
            } catch (MissingPluginException $e) {
                Log::warning('Cake/Queue addPlugin failed: {error}', [
                    'error' => $e->getMessage(),
                ]);
                return;
            }

            $queueConfig = Configure::read('Queue');
            if (!is_array($queueConfig) || empty($queueConfig)) {
                Log::warning('Queue config missing from Configure — config/queue.php not loaded?');
                return;
            }

            foreach ($queueConfig as $name => $config) {
                if (\Cake\Queue\QueueManager::getConfig($name) === null) {
                    \Cake\Queue\QueueManager::setConfig($name, $config);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Queue subsystem bootstrap failed: {error}', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $authenticationService = new AuthenticationService();
        $authenticationService->setConfig([
            'unauthenticatedRedirect' => Router::url(['prefix' => false, 'plugin' => null, 'controller' => 'Users', 'action' => 'login']),
            'queryParam' => 'redirect',
        ]);
        // Load identifiers, ensure we check email and password fields
        $fields = [
            IdentifierInterface::CREDENTIAL_USERNAME => 'email',
            IdentifierInterface::CREDENTIAL_PASSWORD => 'password',
        ];
        $authenticationService->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'finder' => 'auth',
            ],
        ]);
        // Load the authenticators, you want session first
        $authenticationService->loadAuthenticator('Authentication.Session');
        if (Configure::read('OutlookIntegration.enabled')) {
            $authenticationService->loadAuthenticator('OutlookIntegration.TaskpaneToken');
        }
        // Configure form data check to pick email and password
        $authenticationService->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
            'loginUrl' => Router::url(['prefix' => false, 'plugin' => null, 'controller' => 'Users', 'action' => 'login']),
        ]);

        return $authenticationService;
    }
}
