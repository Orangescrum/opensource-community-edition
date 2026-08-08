<?php

namespace App\Controller;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

/**
 * Dashboard data endpoints for the Vue dashboard app.
 *
 * index() renders the shell and passes pre-computed permission flags to the
 * client, so a widget that renders is guaranteed to have access to its data
 * endpoint. Every JSON action returns {success: bool, data: mixed}.
 *
 * A task's company is its project's company: easycases.company_id is not
 * reliably populated, so every task-scoped query filters through
 * projects.company_id (via companyProjectIds()) rather than easycases.company_id.
 */
class MyDashboardsController extends AppController
{
    private const LEGEND_CLOSED = 3;
    private const TYPE_TASK = 1;
    private const PURPOSE_PROJECT = 'project';

    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->addUnauthenticatedActions([]);
    }

    /**
     * Dashboard shell. Permission flags are resolved here — never client-side.
     */
    public function index()
    {
        $isAdmin = defined('SES_TYPE') && SES_TYPE < 3;
        $roleAccess = $GLOBALS['roleAccess'] ?? [];

        $this->set('dashboardPermissions', [
            'isAdmin' => $isAdmin,
            'canSeeKpiCards' => true,
            'canSeeProjectSummary' => $isAdmin,
            'canSeeMilestoneSummary' => $isAdmin,
            'canSeeTopProjects' => $isAdmin,
            'canSeeActiveCompleted' => $isAdmin,
            'canSeeClients' => false,
            'canSeeBudgetCost' => false,
            'canSeeCostReport' => false,
            'canSeeResourceCost' => false,
            'canSeeResourceUtil' => false,
            'canSeeSpentHour' => !$isAdmin,
            'canSeeMyTasks' => !$isAdmin,
            'canSeeMyOverdue' => !$isAdmin,
            'canSeeMyProgress' => !$isAdmin,
            'canSeeTimelog' => true,
            'canSeeTaskList' => true,
            'canSeeTaskStatus' => true,
            'canSeeTaskTypes' => true,
            'canSeeActivity' => true,
            'canSeeBookmarks' => false,
            'canSeeStorage' => $isAdmin,
            'viewAllTasks' => $isAdmin,
            'viewAllTimelog' => $isAdmin,
            'viewAllResource' => $isAdmin,
        ]);
    }

    private function ok($data)
    {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'data' => $data]));
    }

    /**
     * Ids of every project owned by the current company. Task, file and log
     * queries are scoped by membership of this set, since their own company_id
     * column is not dependable.
     */
    private function companyProjectIds(): array
    {
        return $this->fetchTable('Projects')->find()
            ->select(['Projects.id'])
            ->where(['Projects.company_id' => SES_COMP])
            ->all()->extract('id')->toList();
    }

    /**
     * COUNT(CASE WHEN <cond> THEN <idField> END) — counts only the rows
     * matching $cond within the surrounding aggregate query.
     */
    private function countCase($q, array $cond, string $idField)
    {
        return $q->func()->count(
            $q->newExpr()->case()->when($cond)->then($q->identifier($idField))
        );
    }

    /**
     * SUM(CASE WHEN <cond> THEN <valueField> ELSE 0 END).
     */
    private function sumCase($q, array $cond, string $valueField)
    {
        return $q->func()->sum(
            $q->newExpr()->case()->when($cond)->then($q->identifier($valueField))->else(0)
        );
    }

    /**
     * Derived project/milestone health used by the summary widgets. There is no
     * stored health column in this edition, so it is computed from completion
     * and the due date: overdue = Delayed, due within a week = At Risk.
     */
    private function healthStatus(bool $completed, $dueDate, int $progress, FrozenDate $today): string
    {
        if ($completed || $progress >= 100) {
            return 'Completed';
        }
        if ($dueDate) {
            $due = new FrozenDate($dueDate);
            if ($due->lt($today)) {
                return 'Delayed';
            }
            if ($due->lte($today->addDays(7))) {
                return 'At Risk';
            }
        }
        return 'On Track';
    }

    private function graphKey(string $status): string
    {
        return [
            'Completed' => 'completed',
            'On Track' => 'onTrack',
            'Delayed' => 'delayed',
            'At Risk' => 'atRisk',
        ][$status] ?? 'onTrack';
    }

    /**
     * Percentage change between two periods, plus a direction for the arrow.
     */
    private function trend($current, $previous): array
    {
        $current = (float) $current;
        $previous = (float) $previous;
        if ($previous <= 0) {
            return ['direction' => $current > 0 ? 'up' : 'flat', 'percentage' => $current > 0 ? 100 : 0];
        }
        $pct = round((($current - $previous) / $previous) * 100);
        return [
            'direction' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat'),
            'percentage' => abs($pct),
        ];
    }

    public function kpiCounts()
    {
        $comp = SES_COMP;
        $pids = $this->companyProjectIds();
        $now = FrozenTime::now();
        $d30 = $now->subDays(30)->format('Y-m-d H:i:s');
        $d60 = $now->subDays(60)->format('Y-m-d H:i:s');
        $d30date = $now->subDays(30)->format('Y-m-d');
        $d60date = $now->subDays(60)->format('Y-m-d');

        $pq = $this->fetchTable('Projects')->find();
        $proj = $pq->select([
            'total' => $pq->func()->count('*'),
            'completed' => $this->countCase($pq, ['Projects.isactive' => 0], 'Projects.id'),
            'cur' => $this->countCase($pq, ['Projects.dt_created >=' => $d30], 'Projects.id'),
            'prev' => $this->countCase($pq, ['Projects.dt_created >=' => $d60, 'Projects.dt_created <' => $d30], 'Projects.id'),
        ])
            ->where(['Projects.company_id' => $comp, 'Projects.purpose_type' => self::PURPOSE_PROJECT])
            ->enableHydration(false)->first() ?: [];

        $tq = $this->fetchTable('Easycases')->find();
        $task = $tq->select([
            'total' => $tq->func()->count('*'),
            'completed' => $this->countCase($tq, ['Easycases.legend' => self::LEGEND_CLOSED], 'Easycases.id'),
            'cur' => $this->countCase($tq, ['Easycases.dt_created >=' => $d30], 'Easycases.id'),
            'prev' => $this->countCase($tq, ['Easycases.dt_created >=' => $d60, 'Easycases.dt_created <' => $d30], 'Easycases.id'),
        ])
            ->where([
                'Easycases.project_id IN' => $pids,
                'Easycases.isactive' => 1,
                'Easycases.istype' => self::TYPE_TASK,
            ])
            ->enableHydration(false)->first() ?: [];

        $rq = $this->fetchTable('CompanyUsers')->find();
        $res = $rq->select([
            'total' => $rq->func()->count('*'),
            'active' => $this->countCase($rq, ['Users.dt_last_login >=' => $d30], 'CompanyUsers.id'),
        ])
            ->innerJoinWith('Users')
            ->where(['CompanyUsers.company_id' => $comp, 'CompanyUsers.is_active' => 1])
            ->enableHydration(false)->first() ?: [];

        $lq = $this->fetchTable('LogTimes')->find();
        $time = $lq->select([
            'spent' => $lq->func()->sum($lq->identifier('LogTimes.total_hours')),
            'cur' => $this->sumCase($lq, ['LogTimes.task_date >=' => $d30date], 'LogTimes.total_hours'),
            'prev' => $this->sumCase($lq, ['LogTimes.task_date >=' => $d60date, 'LogTimes.task_date <' => $d30date], 'LogTimes.total_hours'),
        ])
            ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.company_id' => $comp]))
            ->enableHydration(false)->first() ?: [];

        return $this->ok([
            'projects' => [
                'total' => (int) ($proj['total'] ?? 0),
                'completed' => (int) ($proj['completed'] ?? 0),
                'trend' => $this->trend($proj['cur'] ?? 0, $proj['prev'] ?? 0),
            ],
            'tasks' => [
                'total' => (int) ($task['total'] ?? 0),
                'completed' => (int) ($task['completed'] ?? 0),
                'trend' => $this->trend($task['cur'] ?? 0, $task['prev'] ?? 0),
            ],
            'resources' => [
                'total' => (int) ($res['total'] ?? 0),
                'active' => (int) ($res['active'] ?? 0),
                'trend' => ['direction' => 'flat', 'percentage' => 0],
            ],
            'timeSpent' => [
                'spent' => round((float) ($time['spent'] ?? 0), 2),
                'estimated' => 0,
                'trend' => $this->trend($time['cur'] ?? 0, $time['prev'] ?? 0),
            ],
        ]);
    }

    public function taskStatus()
    {
        $q = $this->fetchTable('Easycases')->find();
        $rows = $q->select([
            'statusName' => 'StatusMasters.name',
            'legend' => 'StatusMasters.legend',
            'count' => $q->func()->count('*'),
        ])
            ->join(['StatusMasters' => [
                'table' => 'status_masters',
                'type' => 'INNER',
                'conditions' => $q->newExpr()->equalFields('StatusMasters.legend', 'Easycases.legend'),
            ]])
            ->where([
                'Easycases.project_id IN' => $this->companyProjectIds(),
                'Easycases.isactive' => 1,
                'Easycases.istype' => self::TYPE_TASK,
            ])
            ->group(['StatusMasters.name', 'StatusMasters.legend'])
            ->order(['StatusMasters.legend' => 'ASC'])
            ->enableHydration(false)->toArray();

        $palette = ['#F5A623', '#4A90D9', '#7ED321', '#D0021B', '#9013FE', '#50E3C2'];
        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = [
                'status' => $r['statusName'],
                'count' => (int) $r['count'],
                'color' => $palette[$i % count($palette)],
            ];
        }

        return $this->ok($out);
    }

    public function taskTypes()
    {
        $q = $this->fetchTable('Easycases')->find();
        $rows = $q->select([
            'typeId' => 'Types.id',
            'name' => 'Types.name',
            'count' => $q->func()->count('*'),
        ])
            ->innerJoinWith('Types')
            ->where([
                'Easycases.project_id IN' => $this->companyProjectIds(),
                'Easycases.isactive' => 1,
                'Easycases.istype' => self::TYPE_TASK,
            ])
            ->group(['Types.id', 'Types.name'])
            ->enableHydration(false)->toArray();

        $out = array_map(fn($r) => [
            'typeId' => (int) $r['typeId'],
            'name' => $r['name'],
            'count' => (int) $r['count'],
        ], $rows);
        usort($out, fn($a, $b) => $b['count'] <=> $a['count']);

        return $this->ok($out);
    }

    /**
     * Overdue and upcoming tasks. Non-admins only see their own.
     */
    public function taskList()
    {
        return $this->ok($this->buildTaskList(SES_TYPE >= 3 ? SES_ID : null));
    }

    public function myTaskList()
    {
        return $this->ok($this->buildTaskList(SES_ID));
    }

    private function buildTaskList(?int $userId): array
    {
        $today = FrozenDate::now();

        $make = function ($dueWhere) use ($userId) {
            $q = $this->fetchTable('Easycases')->find()
                ->select([
                    'id' => 'Easycases.id',
                    'uniqId' => 'Easycases.uniq_id',
                    'title' => 'Easycases.title',
                    'caseNo' => 'Easycases.case_no',
                    'dueDate' => 'Easycases.due_date',
                    'project' => 'Projects.name',
                    'projectUniqId' => 'Projects.uniq_id',
                ])
                ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.company_id' => SES_COMP]))
                ->where([
                    'Easycases.isactive' => 1,
                    'Easycases.istype' => self::TYPE_TASK,
                    'Easycases.legend <>' => self::LEGEND_CLOSED,
                    'Easycases.due_date IS NOT' => null,
                ])
                ->where($dueWhere)
                ->order(['Easycases.due_date' => 'ASC'])
                ->limit(50)
                ->enableHydration(false);
            if ($userId !== null) {
                $q->where(['Easycases.assign_to' => $userId]);
            }
            return $q->toArray();
        };

        $overdue = $make(['Easycases.due_date <' => $today]);
        $upcoming = $make(['Easycases.due_date >=' => $today]);

        return [
            'overdue' => $overdue,
            'upcoming' => $upcoming,
            'overdueCount' => count($overdue),
            'upcomingCount' => count($upcoming),
        ];
    }

    public function activities()
    {
        $rows = $this->fetchTable('Easycases')->find()
            ->select([
                'id' => 'Easycases.id',
                'uniqId' => 'Easycases.uniq_id',
                'title' => 'Easycases.title',
                'caseNo' => 'Easycases.case_no',
                'date' => 'Easycases.dt_created',
                'project' => 'Projects.name',
                'projectUniqId' => 'Projects.uniq_id',
                'user' => 'Users.name',
            ])
            ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.company_id' => SES_COMP]))
            ->leftJoinWith('Users')
            ->where(['Easycases.isactive' => 1])
            ->order(['Easycases.dt_created' => 'DESC'])
            ->limit(15)
            ->enableHydration(false)->toArray();

        foreach ($rows as &$r) {
            $r['user'] = $r['user'] ?? '';
        }
        unset($r);

        return $this->ok($rows);
    }

    public function timeLog()
    {
        $since = FrozenDate::now()->subDays(29);

        $rows = $this->fetchTable('LogTimes')->find()
            ->select(['taskDate' => 'LogTimes.task_date', 'hours' => 'LogTimes.total_hours'])
            ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.company_id' => SES_COMP]))
            ->where(['LogTimes.task_date >=' => $since])
            ->enableHydration(false)->toArray();

        $byDay = [];
        foreach ($rows as $r) {
            $day = substr((string) $r['taskDate'], 0, 10);
            $byDay[$day] = ($byDay[$day] ?? 0) + (float) $r['hours'];
        }
        ksort($byDay);

        return $this->ok([
            'labels' => array_keys($byDay),
            'values' => array_map(fn($v) => round((float) $v, 2), array_values($byDay)),
            'total_hours' => round(array_sum($byDay), 2),
        ]);
    }

    public function myHours()
    {
        $today = FrozenDate::now()->format('Y-m-d');
        $weekStart = FrozenDate::now()->subDays(6)->format('Y-m-d');

        $q = $this->fetchTable('LogTimes')->find();
        $r = $q->select([
            'week' => $this->sumCase($q, ['LogTimes.task_date >=' => $weekStart], 'LogTimes.total_hours'),
            'today' => $this->sumCase($q, ['LogTimes.task_date' => $today], 'LogTimes.total_hours'),
            'total' => $q->func()->sum($q->identifier('LogTimes.total_hours')),
        ])
            ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.company_id' => SES_COMP]))
            ->where(['LogTimes.user_id' => SES_ID])
            ->enableHydration(false)->first() ?: [];

        return $this->ok([
            'today' => round((float) ($r['today'] ?? 0), 2),
            'week' => round((float) ($r['week'] ?? 0), 2),
            'total' => round((float) ($r['total'] ?? 0), 2),
            'value' => round((float) ($r['week'] ?? 0), 2),
        ]);
    }

    public function myProgress()
    {
        $today = FrozenDate::now()->format('Y-m-d');

        $q = $this->fetchTable('Easycases')->find();
        $r = $q->select([
            'assigned' => $q->func()->count('*'),
            'completed' => $this->countCase($q, ['Easycases.legend' => self::LEGEND_CLOSED], 'Easycases.id'),
            'overdue' => $this->countCase($q, ['Easycases.legend <>' => self::LEGEND_CLOSED, 'Easycases.due_date <' => $today], 'Easycases.id'),
        ])
            ->where([
                'Easycases.project_id IN' => $this->companyProjectIds(),
                'Easycases.isactive' => 1,
                'Easycases.istype' => self::TYPE_TASK,
                'Easycases.assign_to' => SES_ID,
            ])
            ->enableHydration(false)->first() ?: [];

        $assigned = (int) ($r['assigned'] ?? 0);
        $completed = (int) ($r['completed'] ?? 0);

        return $this->ok([
            'assigned' => $assigned,
            'completed' => $completed,
            'overdue' => (int) ($r['overdue'] ?? 0),
            'progress' => $assigned > 0 ? (int) round(($completed / $assigned) * 100) : 0,
        ]);
    }

    public function projectStatus()
    {
        $q = $this->fetchTable('Projects')->find();
        $r = $q->select([
            'active' => $this->countCase($q, ['Projects.isactive' => 1], 'Projects.id'),
            'completed' => $this->countCase($q, ['Projects.isactive' => 0], 'Projects.id'),
        ])
            ->where(['Projects.company_id' => SES_COMP, 'Projects.purpose_type' => self::PURPOSE_PROJECT])
            ->enableHydration(false)->first() ?: [];

        $active = (int) ($r['active'] ?? 0);
        $completed = (int) ($r['completed'] ?? 0);

        return $this->ok([
            'active' => $active,
            'completed' => $completed,
            'labels' => ['Active', 'Completed'],
            'values' => [$active, $completed],
        ]);
    }

    public function projectSummary()
    {
        $filters = (array) $this->request->getData();
        $today = FrozenDate::now();

        $q = $this->fetchTable('Projects')->find();
        $q->select([
            'uniqId' => 'Projects.uniq_id',
            'name' => 'Projects.name',
            'owner' => 'Users.name',
            'dueDate' => 'Projects.end_date',
            'isactive' => 'Projects.isactive',
            'totalTasks' => $q->func()->count($q->identifier('Easycases.id')),
            'completedTasks' => $this->countCase($q, ['Easycases.legend' => self::LEGEND_CLOSED], 'Easycases.id'),
        ])
            ->leftJoinWith('Users')
            ->leftJoinWith('Easycases', fn($e) => $e->where(['Easycases.isactive' => 1, 'Easycases.istype' => self::TYPE_TASK]))
            ->where(['Projects.company_id' => SES_COMP, 'Projects.purpose_type' => self::PURPOSE_PROJECT])
            ->group(['Projects.id', 'Projects.uniq_id', 'Projects.name', 'Users.name', 'Projects.end_date', 'Projects.isactive'])
            ->order(['Projects.name' => 'ASC'])
            ->enableHydration(false);

        if (!empty($filters['proj_id'])) {
            $q->where(['Projects.id' => $filters['proj_id']]);
        }
        if (!empty($filters['manager_id'])) {
            $q->where(['Projects.user_id' => $filters['manager_id']]);
        }

        $graph = ['total' => 0, 'completed' => 0, 'onTrack' => 0, 'delayed' => 0, 'atRisk' => 0];
        $list = [];
        foreach ($q->toArray() as $r) {
            $total = (int) $r['totalTasks'];
            $done = (int) $r['completedTasks'];
            $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $status = $this->healthStatus((int) $r['isactive'] === 0, $r['dueDate'], $progress, $today);

            if (!empty($filters['status']) && strcasecmp($filters['status'], $status) !== 0) {
                continue;
            }

            $graph['total']++;
            $graph[$this->graphKey($status)]++;
            $list[] = [
                'uniqId' => $r['uniqId'],
                'name' => $r['name'],
                'owner' => $r['owner'] ?: '-',
                'dueDate' => $r['dueDate'],
                'status' => $status,
                'progress' => $progress,
            ];
        }

        return $this->ok(['list' => $list, 'graph' => $graph]);
    }

    public function topProjects()
    {
        $q = $this->fetchTable('Projects')->find();
        $rows = $q->select([
            'name' => 'Projects.name',
            'taskCount' => $q->func()->count($q->identifier('Easycases.id')),
        ])
            ->leftJoinWith('Easycases', fn($e) => $e->where(['Easycases.isactive' => 1, 'Easycases.istype' => self::TYPE_TASK]))
            ->where(['Projects.company_id' => SES_COMP, 'Projects.isactive' => 1, 'Projects.purpose_type' => self::PURPOSE_PROJECT])
            ->group(['Projects.id', 'Projects.name'])
            ->enableHydration(false)->toArray();

        $out = array_map(fn($r) => [
            'name' => $r['name'],
            'taskCount' => (int) $r['taskCount'],
        ], $rows);
        usort($out, fn($a, $b) => $b['taskCount'] <=> $a['taskCount']);

        return $this->ok(array_slice($out, 0, 5));
    }

    public function milestoneSummary()
    {
        $filters = (array) $this->request->getData();
        $today = FrozenDate::now();

        $q = $this->fetchTable('Milestones')->find();
        $q->select([
            'uniqId' => 'Milestones.uniq_id',
            'name' => 'Milestones.title',
            'projectName' => 'Projects.name',
            'projectUniqId' => 'Projects.uniq_id',
            'dueDate' => 'Milestones.end_date',
            'completedDate' => 'Milestones.completed_date',
            'isactive' => 'Milestones.isactive',
            'totalTasks' => $q->func()->count($q->identifier('MTasks.id')),
            'completedTasks' => $this->countCase($q, ['MTasks.legend' => self::LEGEND_CLOSED], 'MTasks.id'),
        ])
            ->innerJoinWith('Projects', fn($p) => $p->where(['Projects.isactive' => 1]))
            ->join([
                'EM' => [
                    'table' => 'easycase_milestones',
                    'type' => 'LEFT',
                    'conditions' => $q->newExpr()->equalFields('EM.milestone_id', 'Milestones.id'),
                ],
                'MTasks' => [
                    'table' => 'easycases',
                    'type' => 'LEFT',
                    'conditions' => $q->newExpr()
                        ->equalFields('MTasks.id', 'EM.easycase_id')
                        ->add(['MTasks.isactive' => 1, 'MTasks.istype' => self::TYPE_TASK]),
                ],
            ])
            ->where(['Milestones.company_id' => SES_COMP])
            ->group([
                'Milestones.id', 'Milestones.uniq_id', 'Milestones.title',
                'Projects.name', 'Projects.uniq_id',
                'Milestones.end_date', 'Milestones.completed_date', 'Milestones.isactive',
            ])
            ->order(['Milestones.id' => 'DESC'])
            ->enableHydration(false);

        if (!empty($filters['proj_id'])) {
            $q->where(['Projects.id' => $filters['proj_id']]);
        }
        if (!empty($filters['milestone_id'])) {
            $q->where(['Milestones.id' => $filters['milestone_id']]);
        }

        $graph = ['total' => 0, 'completed' => 0, 'onTrack' => 0, 'delayed' => 0, 'atRisk' => 0];
        $list = [];
        foreach ($q->toArray() as $r) {
            $total = (int) $r['totalTasks'];
            $done = (int) $r['completedTasks'];
            $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $completed = !empty($r['completedDate']) || (int) $r['isactive'] === 0;
            $status = $this->healthStatus($completed, $r['dueDate'], $progress, $today);

            if (!empty($filters['status']) && strcasecmp($filters['status'], $status) !== 0) {
                continue;
            }

            $graph['total']++;
            $graph[$this->graphKey($status)]++;
            $list[] = [
                'uniqId' => $r['uniqId'],
                'name' => $r['name'],
                'projectName' => $r['projectName'],
                'projectUniqId' => $r['projectUniqId'],
                'dueDate' => $r['dueDate'],
                'status' => $status,
                'progress' => $progress,
            ];
        }

        return $this->ok(['list' => $list, 'graph' => $graph]);
    }

    public function storageUsage()
    {
        $q = $this->fetchTable('CaseFiles')->find();
        $r = $q->select(['used' => $q->func()->sum($q->identifier('CaseFiles.file_size'))])
            ->where(['CaseFiles.project_id IN' => $this->companyProjectIds(), 'CaseFiles.isactive' => 1])
            ->enableHydration(false)->first() ?: [];

        return $this->ok([
            'used_mb' => round(((float) ($r['used'] ?? 0)) / 1024, 2),
        ]);
    }

    public function storageByProject()
    {
        $q = $this->fetchTable('Projects')->find();
        $rows = $q->select([
            'projectId' => 'Projects.id',
            'projectName' => 'Projects.name',
            'sizeKb' => $q->func()->sum($q->identifier('CaseFiles.file_size')),
        ])
            ->leftJoinWith('CaseFiles', fn($c) => $c->where(['CaseFiles.isactive' => 1]))
            ->where(['Projects.company_id' => SES_COMP, 'Projects.isactive' => 1])
            ->group(['Projects.id', 'Projects.name'])
            ->enableHydration(false)->toArray();

        $out = [];
        foreach ($rows as $r) {
            $kb = (float) ($r['sizeKb'] ?? 0);
            if ($kb <= 0) {
                continue;
            }
            $out[] = [
                'project_id' => (int) $r['projectId'],
                'project_name' => $r['projectName'],
                'used_mb' => round($kb / 1024, 2),
            ];
        }
        usort($out, fn($a, $b) => $b['used_mb'] <=> $a['used_mb']);

        return $this->ok(['projects' => array_slice($out, 0, 10)]);
    }

    /** Bookmarks are not part of this edition. */
    public function bookmarks()
    {
        return $this->ok([]);
    }

    /** Client tracking is not part of this edition. */
    public function clients()
    {
        return $this->ok(['total' => 0, 'new' => 0]);
    }

    /** Budget tracking is not part of this edition. */
    public function budgetSummary()
    {
        return $this->ok([]);
    }
}
