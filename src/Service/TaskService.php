<?php

namespace App\Service;

use App\Model\Table\EasycasesTable;
use App\Model\Table\TypesTable;
use Cake\ORM\Locator\LocatorAwareTrait;

class TaskService
{
    use LocatorAwareTrait;

    public function getParentTask(string $caseUid): ?array
    {
        return $this->fetchTable('Easycases')
            ->find()
            ->where([
                'uniq_id'  => $caseUid,
                'istype'   => EasycasesTable::TYPE_POST,
                'isactive' => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->first();
    }

    /**
     * Returns all stories directly linked to an epic, scoped to company + project.
     *
     * @param int $epicId    Parent epic case ID
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array<int, array>
     */
    public function getStoriesByEpicId(int $epicId, int $projectId, int $companyId): array
    {
        return $this->fetchTable('Easycases')
            ->getStoriesByEpicId($epicId, $projectId, $companyId);
    }

    /**
     * Returns all non-hierarchy tasks (excludes Epic/Feature/Story) directly
     * linked to an epic via epic_id, scoped to company + project.
     *
     * @param int $epicId    Parent epic case ID
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array<int, array>
     */
    public function getTasksByEpicId(int $epicId, int $projectId, int $companyId): array
    {
        return $this->fetchTable('Easycases')
            ->getTasksByEpicId($epicId, $projectId, $companyId);
    }

    /**
     * Returns counts of features, stories, and tasks under an epic.
     *
     * @param int $epicId    Parent epic case ID
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array{totalFeatures: int, totalStories: int, totalTasks: int, completedStories: int}
     */
    public function getEpicChildCounts(int $epicId, int $projectId, int $companyId): array
    {
        $easycases = $this->fetchTable('Easycases');
        $base = [
            'Easycases.epic_id'          => $epicId,
            'Easycases.istype'           => EasycasesTable::TYPE_POST,
            'Easycases.isactive'         => EasycasesTable::IS_ACTIVE,
            'Easycases.project_id'       => $projectId,
            'Easycases.client_status !=' => 1,
        ];
        $storyBase = array_merge($base, ['Easycases.type_id' => TypesTable::STORY]);

        return [
            'totalFeatures'    => $easycases->find()
                ->where(array_merge($base, ['Easycases.type_id' => TypesTable::FEATURE]))
                ->count(),
            'totalStories'     => $easycases->find()->where($storyBase)->count(),
            'completedStories' => $easycases->find()
                ->where(array_merge($storyBase, ['Easycases.legend IN' => [EasycasesTable::LEGEND_CLOSED, EasycasesTable::LEGEND_RESOLVED]]))
                ->count(),
            'totalTasks'       => $easycases->find()
                ->where(array_merge($base, ['Easycases.type_id NOT IN' => [TypesTable::EPIC, TypesTable::FEATURE, TypesTable::STORY]]))
                ->count(),
        ];
    }

    /**
     * Count stories under a Feature (via feature_id), including completed.
     *
     * @param int $featureId Parent feature case ID
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array{totalStories: int, completedStories: int}
     */
    public function getFeatureChildCounts(int $featureId, int $projectId, int $companyId): array
    {
        $easycases = $this->fetchTable('Easycases');
        $base = [
            'Easycases.feature_id'       => $featureId,
            'Easycases.type_id'          => TypesTable::STORY,
            'Easycases.istype'           => EasycasesTable::TYPE_POST,
            'Easycases.isactive'         => EasycasesTable::IS_ACTIVE,
            'Easycases.project_id'       => $projectId,
            'Easycases.client_status !=' => 1,
        ];

        return [
            'totalStories'     => $easycases->find()->where($base)->count(),
            'completedStories' => $easycases->find()
                ->where(array_merge($base, ['Easycases.legend IN' => [EasycasesTable::LEGEND_CLOSED, EasycasesTable::LEGEND_RESOLVED]]))
                ->count(),
        ];
    }

    /**
     * Count tasks under a Story (via parent_task_id), including completed.
     *
     * @param int $storyId   Parent story case ID
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array{totalTasks: int, completedTasks: int}
     */
    public function getStoryChildCounts(int $storyId, int $projectId, int $companyId): array
    {
        $easycases = $this->fetchTable('Easycases');
        $base = [
            'Easycases.parent_task_id'   => $storyId,
            'Easycases.type_id NOT IN'   => [TypesTable::EPIC, TypesTable::FEATURE, TypesTable::STORY],
            'Easycases.istype'           => EasycasesTable::TYPE_POST,
            'Easycases.isactive'         => EasycasesTable::IS_ACTIVE,
            'Easycases.project_id'       => $projectId,
            'Easycases.client_status !=' => 1,
        ];

        return [
            'totalTasks'     => $easycases->find()->where($base)->count(),
            'completedTasks' => $easycases->find()
                ->where(array_merge($base, ['Easycases.legend IN' => [EasycasesTable::LEGEND_CLOSED, EasycasesTable::LEGEND_RESOLVED]]))
                ->count(),
        ];
    }

    /**
     * Fetch subtasks for a parent case, apply type filter, and paginate.
     *
     * @param int    $caseId      Parent case ID
     * @param int    $projectId   Project ID
     * @param int    $caseTypeId  Parent task's type_id
     * @param int    $companyId   Tenant company ID
     * @param string $subtaskType 'story' | 'task' | '' (feature/default)
     * @param int    $page        1-based page number
     * @return array{subtasks: array, total_count: int, page_limit: int}
     */
    public function getPagedSubtasks(int $caseId, int $projectId, int $caseTypeId, int $companyId, string $subtaskType, int $page): array
    {
        $easycases = $this->fetchTable('Easycases');

        $epicFetchers = [
            'story' => fn() => $this->getStoriesByEpicId($caseId, $projectId, $companyId),
            'task'  => fn() => $this->getTasksByEpicId($caseId, $projectId, $companyId),
        ];

        $fetchers = [
            TypesTable::EPIC    => $epicFetchers[$subtaskType] ?? fn() => $easycases->getFeaturesByEpicId($caseId, $projectId, $companyId),
            TypesTable::FEATURE => fn() => $easycases->getStoriesByFeatureId($caseId, $projectId, $companyId),
        ];

        $subtasks = ($fetchers[$caseTypeId] ?? fn() => $easycases->getSubTasksByTaskId($caseId, $projectId, $companyId))();

        if ($subtaskType === 'story') {
            $subtasks = array_values(array_filter($subtasks, fn($s) => ($s['Easycase']['type_id'] ?? null) == TypesTable::STORY));
        } elseif ($subtaskType === 'task') {
            $excludeTypeIds = [TypesTable::EPIC, TypesTable::FEATURE, TypesTable::STORY];
            $subtasks = array_values(array_filter($subtasks, fn($s) => !in_array($s['Easycase']['type_id'] ?? null, $excludeTypeIds)));
        }

        usort($subtasks, fn($a, $b) => ($b['Easycase']['id'] ?? 0) <=> ($a['Easycase']['id'] ?? 0));

        $total_count = count($subtasks);
        $pageLimit = (int)\Cake\Core\Configure::read('Pagination.subtask_page_limit', 10);
        $subtasks = array_slice($subtasks, ($page - 1) * $pageLimit, $pageLimit);

        return [
            'subtasks'    => $subtasks,
            'total_count' => $total_count,
            'page_limit'  => $pageLimit,
        ];
    }

    /**
     * Returns hierarchy children and potential new-parent targets for an Epic, Feature, or Story.
     *
     * @param int $taskId    The task being evaluated for deletion
     * @param int $projectId Project ID
     * @param int $companyId Company (tenant) ID — use SES_COMP at the call site
     * @return array{has_children: bool, task_type?: string, child_count?: int, child_type?: string, targets?: list<array{id: int, title: string, case_no: int}>}
     */
    public function getChildrenTask(int $taskId, int $projectId, int $companyId): array
    {
        $easycases = $this->fetchTable('Easycases');

        $task = $easycases->find()
            ->select(['id', 'type_id'])
            ->where([
                'id'         => $taskId,
                'project_id' => $projectId,
                'istype'     => EasycasesTable::TYPE_POST,
                'isactive'   => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->first();
        if (!$task) {
            return ['has_children' => false];
        }

        $typeId = (int)$task['type_id'];

        $labels = [
            TypesTable::EPIC    => ['task_type' => 'epic',    'child_type' => 'feature'],
            TypesTable::FEATURE => ['task_type' => 'feature', 'child_type' => 'story'],
            TypesTable::STORY   => ['task_type' => 'story',   'child_type' => 'subtask'],
        ];
        $label = $labels[$typeId] ?? ['task_type' => 'task', 'child_type' => 'subtask'];

        $fkFilters = [
            TypesTable::EPIC    => ['epic_id'        => $taskId],
            TypesTable::FEATURE => ['feature_id'     => $taskId],
            TypesTable::STORY   => ['parent_task_id' => $taskId],
        ];

        $fkFilter = $fkFilters[$typeId] ?? ['parent_task_id' => $taskId];

        $childCount = $easycases->find()
            ->where(array_merge($fkFilter, [
                'project_id' => $projectId,
                'istype'     => EasycasesTable::TYPE_POST,
                'isactive'   => EasycasesTable::IS_ACTIVE,
            ]))
            ->count();

        if ($childCount === 0) {
            return ['has_children' => false];
        }

        return [
            'has_children' => true,
            'task_type'    => $label['task_type'],
            'child_count'  => $childCount,
            'child_type'   => $label['child_type'],
            'targets'      => $this->getMoveTargets($typeId, $projectId, $taskId, !isset($fkFilters[$typeId])),
        ];
    }

    /**
     * Returns tasks of the same hierarchy type in the project that can receive
     * children when the given task is deleted.
     *
     * @param int $typeId    TypesTable constant (EPIC / FEATURE / STORY)
     * @param int $projectId Project scope
     * @param int $excludeId The task being deleted — omitted from results
     * @return list<array{id: int, case_no: int, title: string}>
     */
    public function getMoveTargets(int $typeId, int $projectId, int $excludeId, bool $topLevelOnly = false): array
    {
        $query = $this->fetchTable('Easycases')
            ->find()
            ->select(['id', 'case_no', 'title'])
            ->where([
                'type_id'    => $typeId,
                'project_id' => $projectId,
                'istype'     => EasycasesTable::TYPE_POST,
                'isactive'   => EasycasesTable::IS_ACTIVE,
                'legend !='  => EasycasesTable::LEGEND_CLOSED,
                'id !='      => $excludeId,
            ])
            ->orderAsc('case_no')
            ->disableHydration()
            ->disableResultsCasting();

        if ($topLevelOnly) {
            $query->where(fn($exp) => $exp->or(['parent_task_id IS' => null, 'parent_task_id' => 0]));
        }

        $rows = $query->all()->toArray();

        return array_map(fn($r) => [
            'id'      => (int)$r['id'],
            'case_no' => (int)$r['case_no'],
            'title'   => (string)$r['title'],
        ], $rows);
    }

    /**
     * Moves all direct children of $taskId to $newParentId via a single tenant-scoped updateAll per type.
     *
     * @param int    $taskId      The parent task being deleted
     * @param int    $newParentId The new parent to assign children to
     * @param string $taskType    'epic' | 'feature' | 'story' | 'task'
     * @param int    $projectId   Project ID
     * @param int    $companyId   Company (tenant) ID — use SES_COMP at the call site
     */
    public function moveChildren(int $taskId, int $newParentId, string $taskType, int $projectId, int $companyId): void
    {
        $easycases = $this->fetchTable('Easycases');
        $scope = ['project_id' => $projectId];

        $lookup = [
            'epic'    => ['set' => ['epic_id' => $newParentId],        'where' => ['epic_id' => $taskId]],
            'feature' => ['set' => ['feature_id' => $newParentId],     'where' => ['feature_id' => $taskId]],
            'story'   => ['set' => ['parent_task_id' => $newParentId], 'where' => ['parent_task_id' => $taskId, 'istype' => EasycasesTable::TYPE_POST]],
            'task'    => ['set' => ['parent_task_id' => $newParentId], 'where' => ['parent_task_id' => $taskId, 'istype' => EasycasesTable::TYPE_POST]],
        ];

        if (!isset($lookup[$taskType])) {
            return;
        }
        $op = $lookup[$taskType];

        $easycases->updateAll($op['set'], array_merge($op['where'], $scope));
    }

    /**
     * Moves all direct children to $newParentId, then deletes the task.
     * Returns null if the new parent or the case cannot be found.
     * Returns a flat result array the controller uses to send email and build the JSON response.
     *
     * @param string $cno         case_no of the task to delete
     * @param int    $newParentId ID of the new parent task (must exist and be active in the project)
     * @param string $taskType    'epic' | 'feature' | 'story' | 'task'
     * @param int    $projectId   Project ID
     * @param int    $companyId   Company (tenant) ID — pass SES_COMP at call site
     * @return array{
     *     id: int, type_id: int, type_label: string,
     *     parent_task_id: int, feature_id: int, epic_id: int,
     *     case_no: string, title: string, priority: string, uniq_id: string,
     *     assign_to: mixed, email_users: array, notify_users: mixed,
     *     project_uniq_id: string, project_short_name: string
     * }|null
     */
    public function deleteWithReparentedChildren(
        string $cno,
        int $newParentId,
        string $taskType,
        int $projectId,
        int $companyId
    ): ?array {
        $easycases    = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');

        $newParent = $easycases->find()
            ->select(['id'])
            ->where([
                'id'         => $newParentId,
                'project_id' => $projectId,
                'istype'     => EasycasesTable::TYPE_POST,
                'isactive'   => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->first();

        if (!$newParent) {
            return null;
        }

        $caseList = $easycases->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select([
                'Easycase.id',
                'Easycase.title',
                'Easycase.parent_task_id',
                'Easycase.epic_id',
                'Easycase.feature_id',
                'Easycase.type_id',
                'Easycase.assign_to',
                'Easycase.case_no',
                'Easycase.priority',
                'Easycase.uniq_id',
            ])
            ->where([
                'Easycase.case_no'    => $cno,
                'Easycase.istype'     => EasycasesTable::TYPE_POST,
                'Easycase.project_id' => $projectId,
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (!$caseList) {
            return null;
        }

        $id       = (int)$caseList['Easycase']['id'];
        $assignTo = $caseList['Easycase']['assign_to'] ?? null;

        $caseUserEmailsTable = $this->fetchTable('CaseUserEmails');
        $projectUsersTable   = $this->fetchTable('ProjectUsers');
        $emailUsers          = $caseUserEmailsTable->getEmailUsers($id);
        if (!empty($assignTo) && !in_array($assignTo, $emailUsers)) {
            $emailUsers[] = $assignTo;
        }
        $notifyUsers = !empty($emailUsers)
            ? $projectUsersTable->getAllExistingNotifyUser($projectId, $emailUsers)
            : null;

        $this->moveChildren($id, $newParentId, $taskType, $projectId, $companyId);

        $easycases->deleteTasksRecursively([$id], $projectId);

        $typesTable = $this->fetchTable('Types');
        $epicId     = $typesTable->getEpicId();
        $featureId  = $typesTable->getFeatureId();
        $typeId     = (int)($caseList['Easycase']['type_id'] ?? 0);
        $typeLabel  = [$epicId => 'Epic', $featureId => 'Feature'][$typeId] ?? 'Task';

        $prjuniq = $projectsTable->find()
            ->select(['uniq_id', 'short_name'])
            ->where(['id' => $projectId])
            ->disableHydration()
            ->first();

        return [
            'id'                 => $id,
            'type_id'            => $typeId,
            'type_label'         => $typeLabel,
            'parent_task_id'     => (int)($caseList['Easycase']['parent_task_id'] ?? 0),
            'feature_id'         => (int)($caseList['Easycase']['feature_id'] ?? 0),
            'epic_id'            => (int)($caseList['Easycase']['epic_id'] ?? 0),
            'case_no'            => (string)($caseList['Easycase']['case_no'] ?? $cno),
            'title'              => (string)($caseList['Easycase']['title'] ?? ''),
            'priority'           => (string)($caseList['Easycase']['priority'] ?? ''),
            'uniq_id'            => (string)($caseList['Easycase']['uniq_id'] ?? ''),
            'assign_to'          => $assignTo,
            'email_users'        => $emailUsers,
            'notify_users'       => $notifyUsers,
            'project_uniq_id'    => (string)($prjuniq['uniq_id'] ?? ''),
            'project_short_name' => (string)($prjuniq['short_name'] ?? ''),
        ];
    }
}
