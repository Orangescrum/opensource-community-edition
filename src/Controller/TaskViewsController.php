<?php

namespace App\Controller;

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

    private function renderApp($page)
    {
        $this->viewBuilder()->setLayout('default_inner');
        $this->viewBuilder()->setTemplate('index');
        $this->set('page', $page);
        $this->set('pageTitle', __('Tasks'));
    }
}
