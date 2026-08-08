<?php
declare(strict_types=1);

namespace EmailTemplating\Controller;

use App\Controller\AppController as BaseController;
use Cake\Event\EventInterface;
use Cake\Http\Response;

class AppController extends BaseController
{
    /**
     * Authorize the request — managing email templates is reserved for owner
     * (SES_TYPE=1) and admin (SES_TYPE=2). Regular users (SES_TYPE>=3) and
     * unauthenticated requests are rejected.
     *
     * UI requests get a redirect to the dashboard with a flash; API requests
     * (prefix=Api) get a 403 JSON response so the Vue client surfaces an error.
     */
    public function beforeFilter(EventInterface $event)
    {
        $parentResult = parent::beforeFilter($event);
        if ($parentResult instanceof Response) {
            return $parentResult;
        }

        // parent::beforeFilter may have allowed an unauthenticated visitor through
        // for installer / outer pages — but every action in this plugin requires auth.
        if ($this->request->getAttribute('identity') === null) {
            return $this->forbidden('Authentication required');
        }

        $type = \defined('SES_TYPE') ? (int)SES_TYPE : 3;
        // Owner = 1, Admin = 2. Anything else cannot manage templates or settings.
        if ($type > 2) {
            return $this->forbidden('Insufficient privileges to manage email templates');
        }

        return null;
    }

    private function forbidden(string $message): Response
    {
        $isApi = $this->request->getParam('prefix') === 'Api';
        if ($isApi) {
            return $this->response
                ->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode(['error' => $message]));
        }

        $this->request->getSession()->write('ERROR', $message);

        return $this->redirect([
            'plugin' => null,
            'controller' => 'Easycases',
            'action' => 'dashboard',
        ]);
    }
}
