<?php

namespace App\Controller;

use App\Model\Table\UserPreferencesTable;

/**
 * Hosts the Task Views single-page app.
 *
 * One Vue bundle serves every task tab (Views, Kanban, Calendar, Overview,
 * Subtasks, My Works); each action just tells the app which page to render via
 * the `page` view var. Kept out of EasycasesController so the SPA host does not
 * add surface to an already very large controller.
 */
class TaskViewsController extends AppController
{
    /**
     * Preferences handed to the bundle at render time, and the only keys
     * `savePreference()` will accept. Sending them with the page rather than
     * over a second request means the first paint already has the user's
     * layout — fetching it would show the default columns and then rearrange.
     */
    private const PREFERENCE_KEYS = ['taskViews.hiddenColumns'];

    public function index()
    {
        $this->renderApp('views');
    }

    public function kanban()
    {
        $this->renderApp('kanban');
    }

    public function calendar()
    {
        $this->renderApp('calendar');
    }

    public function overview()
    {
        $this->renderApp('overview');
    }

    public function subtasks()
    {
        $this->renderApp('subtasks');
    }

    public function myworks()
    {
        $this->renderApp('myworks');
    }

    /**
     * Store one preference for the logged-in user.
     *
     * The scope comes from the session, never from the request, so a caller
     * cannot write another user's row.
     */
    public function savePreference()
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        $key = (string)$this->request->getData('key');
        if (!in_array($key, self::PREFERENCE_KEYS, true)) {
            return $this->response
                ->withStatus(400)
                ->withType('application/json')
                ->withStringBody((string)json_encode(['saved' => false]));
        }

        $raw = (string)$this->request->getData('value');
        $value = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || strlen($raw) > UserPreferencesTable::MAX_VALUE_BYTES) {
            return $this->response
                ->withStatus(400)
                ->withType('application/json')
                ->withStringBody((string)json_encode(['saved' => false]));
        }

        $saved = $this->fetchTable('UserPreferences')
            ->write((int)SES_COMP, (int)SES_ID, $key, $value);

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode(['saved' => $saved]));
    }

    private function renderApp($page)
    {
        $this->viewBuilder()->setLayout('default_inner');
        $this->viewBuilder()->setTemplate('index');
        $this->set('page', $page);
        $this->set('pageTitle', __('Tasks'));
        $this->set('preferences', $this->fetchTable('UserPreferences')
            ->readMany((int)SES_COMP, (int)SES_ID, self::PREFERENCE_KEYS));
    }
}
