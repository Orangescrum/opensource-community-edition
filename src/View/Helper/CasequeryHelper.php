<?php

declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\View\Helper;

use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query;
use Cake\View\Helper;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * Casequery helper
 */
class CasequeryHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);
    }

    public function getAllCaseMilestone($mstid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;

        $caseCount = $Easycase->query("SELECT COUNT(Easycase.id) as totcase FROM easycases as Easycase,easycase_milestones AS EasycaseMilestone WHERE EasycaseMilestone.easycase_id=Easycase.id AND Easycase.istype='1' AND Easycase.isactive='1' AND EasycaseMilestone.milestone_id='$mstid'");
        return $caseCount[0][0]['totcase'];
    }
    public function getAllCaseIdsFromM($mstid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;

        $allCases = $Easycase->query("SELECT Easycase.id as id FROM easycases as Easycase,easycase_milestones AS EasycaseMilestone WHERE EasycaseMilestone.easycase_id=Easycase.id AND Easycase.istype='1' AND Easycase.isactive='1' AND EasycaseMilestone.milestone_id='$mstid'");

        $caseIds = [];
        if (count($allCases) > 0) {
            foreach ($allCases as $allCase) {
                array_push($caseIds, $allCase['Easycase']['id']);
            }
        } else {

        }
        return $caseIds;
    }
    public function getMilestoneName($caseid)
    {
        $Milestone = ClassRegistry::init('Milestone');
        $Milestone->recursive = -1;

        $milestones = $Milestone->query("SELECT Milestone.title as title FROM milestones as Milestone,easycase_milestones AS EasycaseMilestone WHERE EasycaseMilestone.milestone_id=Milestone.id AND EasycaseMilestone.easycase_id='".$caseid."'");
        if (isset($milestones['0']['Milestone']['title']) && $milestones['0']['Milestone']['title']) {
            return $milestones['0']['Milestone']['title'];
        } else {
            return false;
        }
    }
    public function getAllClosedCaseMilestone($mstid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;

        $caseCount = $Easycase->query("SELECT COUNT(Easycase.id) as totcase FROM easycases as Easycase,easycase_milestones AS EasycaseMilestone WHERE EasycaseMilestone.easycase_id=Easycase.id AND Easycase.istype='1' AND Easycase.isactive='1' AND Easycase.legend='3' AND EasycaseMilestone.milestone_id='$mstid'");
        return $caseCount[0][0]['totcase'];
    }

    public function getAllCases($cid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;

        $easycases = $Easycase->find('all', ['conditions' => ['Easycase.id' => $cid]]);
        return $easycases[0];
    }
    public function getAllComments($repid)
    {
        App::import('Model', 'CaseComment');
        $CaseComment = new CaseComment();
        $CaseComment->recursive = -1;
        $comments = $CaseComment->find('all', ['conditions' => ['CaseComment.easycase_id' => $repid,'CaseComment.isactive' => 1],'order' => ['CaseComment.dt_created DESC']]);
        return $comments;
    }
    public function getComments($comntid)
    {
        App::import('Model', 'CaseComment');
        $CaseComment = new CaseComment();
        $CaseComment->recursive = -1;
        $cmnt = $CaseComment->find('first', ['conditions' => ['CaseComment.id' => $comntid,'CaseComment.isactive' => 1],'fields' => ['comments']]);
        return $cmnt['CaseComment']['comments'];
    }
    public function getCaseTitle($cid, $typ, $case_no, $project_id)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        if ($typ == 1) {
            $post = $Easycase->find('first', ['conditions' => ['Easycase.id' => $cid,'Easycase.isactive' => 1,'Easycase.title !=' => ''],'fields' => ['title']]);
            return $post['Easycase']['title'];
        } else {
            $post = $Easycase->find('first', ['conditions' => ['Easycase.id' => $cid,'Easycase.isactive' => 1],'fields' => ['message']]);
            if ($post['Easycase']['message']) {
                return $post['Easycase']['message'];
            } else {
                $getTitle = $Easycase->find('first', ['conditions' => ['Easycase.case_no' => $case_no,'Easycase.project_id' => $project_id,'Easycase.isactive' => 1,'Easycase.title !=' => ''],'fields' => ['title']]);
                return $getTitle['Easycase']['title'];

            }
        }
    }
    public function getTaskTitle($cid, $typ, $case_no, $project_id)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        if ($typ == 2) {
            $getTitle = $Easycase->find('first', ['conditions' => ['Easycase.case_no' => $case_no,'Easycase.project_id' => $project_id,'Easycase.isactive' => 1,'Easycase.thread_count !=' => 0],'fields' => ['title']]);
            return $getTitle['Easycase']['title'];
        } else {
            $post = $Easycase->find('first', ['conditions' => ['Easycase.id' => $cid,'Easycase.isactive' => 1],'fields' => ['title']]);
            if ($post['Easycase']['title']) {
                return $post['Easycase']['title'];
            }
        }
    }
    public function getCaseUniqId($cno, $pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $uniqid = $Easycase->find('first', ['conditions' => ['Easycase.case_no' => $cno,'Easycase.project_id' => $pid,'Easycase.istype' => 1,'Easycase.isactive' => 1],'fields' => ['uniq_id']]);
        return $uniqid['Easycase']['uniq_id'];
    }
    public function getProjUniqId($pid)
    {
        $Project = ClassRegistry::init('Project');
        $Project->recursive = -1;
        $uniqid = $Project->find('first', ['conditions' => ['Project.id' => $pid,'Project.isactive' => 1,'Project.company_id' => SES_COMP],'fields' => ['uniq_id']]);
        return $uniqid['Project']['uniq_id'];
    }
    public function getUserEmail($id)
    {
        $CaseUserEmail = ClassRegistry::init('CaseUserEmail');
        $CaseUserEmail->recursive = -1;
        $userIds = $CaseUserEmail->find('all', ['conditions' => ['CaseUserEmail.easycase_id' => $id,'CaseUserEmail.ismail' => 1], 'fields' => ['CaseUserEmail.user_id']]);
        return $userIds;
    }
    public function casePostId($cno)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $caseid = $Easycase->find('first', ['conditions' => ['Easycase.case_no' => $cno,'Easycase.istype' => 1], 'fields' => ['Easycase.uniq_id']]);
        return $caseid;
    }
    public function getProjectShortName($pid)
    {
        $shortName = '';
        $Project = ClassRegistry::init('Project');
        $Project->recursive = -1;
        $pjArr = $Project->find('first', ['conditions' => ['Project.id' => $pid,'Project.isactive' => 1,'Project.company_id' => SES_COMP], 'fields' => ['Project.short_name','Project.uniq_id']]);
        return $pjArr;
    }
    public function getProjectName($pid)
    {
        if (empty($pid)) {
            return null;
        }
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        return $projectsTable->find()
            ->select(['Projects.name','Projects.uniq_id','Projects.short_name','Projects.project_methodology_id'])
            ->where(['Projects.id' => $pid, 'Projects.company_id' => SES_COMP ])
            ->disableHydration()
            ->first();
    }
    public function getProjectNameByUniqid($puid)
    {
        if ($puid != 'all') {
            $Project = ClassRegistry::init('Project');
            $Project->recursive = -1;
            $pjArr = $Project->find('first', ['conditions' => ['Project.uniq_id' => $puid,'Project.isactive' => 1,'Project.company_id' => SES_COMP], 'fields' => ['Project.name']]);
            return $pjArr['Project']['name'] ;
        } else {
            $pjArr['Project']['name'] = 'All';
            return $pjArr['Project']['name'];
        }
    }
    public function getCaseNotification($cid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $allcase = $Easycase->find('first', ['conditions' => ['Easycase.id' => $cid,'Easycase.isactive' => 1], 'fields' => ['Easycase.uniq_id','Easycase.case_no','Easycase.project_id','Easycase.user_id','Easycase.title']]);
        return $allcase;
    }
    public function caseViewData($pid, $type)
    {
        if ($type == 'new') {
            $CaseUserView = ClassRegistry::init('CaseUserView');
            $caseMsg = $CaseUserView->find('count', ['conditions' => ['CaseUserView.user_id' => SES_ID,'CaseUserView.project_id' => $pid, 'CaseUserView.istype' => 1, 'CaseUserView.isviewed' => 0],'fields' => 'DISTINCT CaseUserView.id']);

            return $caseMsg;
        }
    }
    public function caseBcMems($uid)
    {
        $User = ClassRegistry::init('User');
        $User->recursive = -1;
        $usrDtls = $User->find('first', ['conditions' => ['User.id' => $uid,'User.isactive' => 1], 'fields' => ['User.short_name']]);
        return $usrDtls['User']['short_name'];
    }
    public function caseProject($pid)
    {
        $Project = ClassRegistry::init('Project');
        $Project->recursive = -1;
        $pjDtls = $Project->find('first', ['conditions' => ['Project.id' => $pid,'Project.isactive' => 1,'Project.company_id' => SES_COMP], 'fields' => ['Project.short_name','Project.name','Project.uniq_id']]);
        return $pjDtls;
    }
    public function caseBcTypes($typ)
    {
        if (strlen($typ) == 2 && $typ == 01) {
            $typ = 10;
        }
        $Type = ClassRegistry::init('Type');
        $cstype = $Type->find('first', ['conditions' => ['Type.id' => $typ], 'fields' => ['Type.short_name']]);
        return $cstype['Type']['short_name'];
    }
    public function getUserDtls($uid)
    {
        $User = TableRegistry::getTableLocator()->get('Users');
        $usrDtls = $User->find()
            ->where(['id' => $uid, 'isactive' => 1])
            ->select(['name','istype','email','short_name','photo','last_name'])
            ->disableHydration()
            ->first();

        return $usrDtls ?? [];
    }
    public function getUserDtlsArr($uid, $usrDtlsArr = [])
    {
        if (isset($usrDtlsArr[$uid])) {
            return $usrDtlsArr[$uid];
        } else {
            // echo "";
        }
    }
    public function getCaseFiles($cid)
    {
        App::import('Model', 'CaseFile');
        $CaseFile = new CaseFile();
        $CaseFile->recursive = -1;
        $caseFiles = $CaseFile->find('all', ['conditions' => ['CaseFile.easycase_id' => $cid,'CaseFile.comment_id' => 0,'CaseFile.isactive' => 1], 'fields' => ['CaseFile.file','CaseFile.file_size'], 'order' => ['CaseFile.file ASC']]);
        return $caseFiles;
    }
    public function countCaseFiles($allcsId)
    {
        $caseFiles = 0;
        App::import('Model', 'CaseFile');
        $CaseFile = new CaseFile();
        $CaseFile->recursive = -1;
        $caseFiles = $CaseFile->find('count', ['conditions' => ['CaseFile.easycase_id' => $allcsId,'CaseFile.isactive' => 1], 'fields' => 'CaseFile.id']);
        return $caseFiles;
    }
    public function getCommentFiles($cmnt)
    {
        App::import('Model', 'CaseFile');
        $CaseFile = new CaseFile();
        $CaseFile->recursive = -1;
        $caseFiles = $CaseFile->find('all', ['conditions' => ['CaseFile.comment_id' => $cmnt,'CaseFile.isactive' => 1], 'fields' => ['CaseFile.file','CaseFile.file_size'], 'order' => ['CaseFile.file ASC']]);
        return $caseFiles;
    }
    public function checkCaseFile($caseid)
    {
        App::import('Model', 'CaseFile');
        $CaseFile = new CaseFile();
        $CaseFile->recursive = -1;
        $caseFiles = $CaseFile->find('count', ['conditions' => ['CaseFile.easycase_id' => $caseid,'CaseFile.comment_id !=' => 0,'CaseFile.isactive' => 1], 'fields' => 'DISTINCT CaseFile.id']);
        return $caseFiles;
    }
    public function getType($typid)
    {
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $cstype = $typesTable->find('all', ['conditions' => ['id' => $typid], 'fields' => ['name', 'short_name']])->disableHydration()->disableResultsCasting()->first();
        return $cstype ?? [];
    }
    public function getTypeArr($typid, $cstypeArr)
    {
        $return = [];
        foreach ($cstypeArr as $type) {
            if (isset($type['id']) && $type['id'] == $typid) {
                $return = $type;
            }
            if (isset($type['Type']['id']) && $type['Type']['id'] == $typid) {
                $return = $type;
            }
        }
        return $return;
    }
    public function getLastCase($cno, $pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $lastcase = $Easycase->find('first', ['conditions' => ['Easycase.case_no' => $cno,'Easycase.project_id' => $pid,'Easycase.isactive' => 1], 'fields' => ['Easycase.id','Easycase.user_id'], 'order' => ['Easycase.id DESC'], 'limit' => 1]);
        return $lastcase;
    }
    public function allCaseReply($cno, $pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $allCase = $Easycase->find('count', ['conditions' => ['Easycase.case_no' => $cno,'Easycase.project_id' => $pid,'Easycase.isactive' => 1],'fields' => 'DISTINCT Easycase.id']);
        return $allCase;
    }
    public function displayCaseNo($pid = null, $type = null, $id = 0, $filters = '', $all = null)
    {
        $arr = [];
        if ($filters == 'assigntome' && $all != 'all') {
            $arr = ['OR' => [
                'AND' => [
                    'Easycase.isactive'   => 1,
                    'Easycase.istype'     => 1,
                    'Easycase.project_id' => $pid,
                    'Easycase.assign_to' => SES_ID
                ],
                [
                    'Easycase.isactive'  => 1,
                    'Easycase.istype'    => 1,
                    'Easycase.project_id' => $pid,
                    'Easycase.assign_to' => '0',
                    'Easycase.user_id' => SES_ID]]];
        }
        if ($filters == 'latest' && $all != 'all') {
            $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
            $arr = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <=' => GMT_DATETIME]];
        }
        if ($filters == 'delegateto' && $all != 'all') {
            $arr = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
        }
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        if ($type == 'project' && $all == '0') {
            $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid]]);
        } elseif ($type == 'project' && $all == 'all') {
            $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->unbindModel(['belongsTo' => ['User']]);
            $allProjArr = $ProjectUser->find('all', $cond);
            $ids = [];
            foreach ($allProjArr as $csid) {
                array_push($ids, $csid['Project']['id']);
            }
            $total = 0;
            for ($i = 0;$i < count($ids);$i++) {
                $Easycase = ClassRegistry::init('Easycase');
                $Easycase->recursive = -1;
                $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i]]]);
                $total += $totcase;
            }
            $totcase = $total;
        } elseif ($type == 'type' && $all != 'all') {
            $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid,'Easycase.type_id' => $id, $arr]]);
        } elseif ($type == 'type' && $all == 'all') {
            $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->unbindModel(['belongsTo' => ['User']]);
            $allProjArr = $ProjectUser->find('all', $cond);
            $ids = [];
            foreach ($allProjArr as $csid) {
                array_push($ids, $csid['Project']['id']);
            }
            $total = 0;
            for ($i = 0;$i < count($ids);$i++) {
                $Easycase = ClassRegistry::init('Easycase');
                $Easycase->recursive = -1;
                $arrtyp = [];
                if ($filters == 'assigntome' && $all == 'all') {
                    $arrtyp = ['OR' => [
                        'AND' => [
                            'Easycase.isactive'   => 1,
                            'Easycase.istype'     => 1,
                            'Easycase.project_id' => $ids[$i],
                            'Easycase.assign_to' => SES_ID
                        ],
                        [
                            'Easycase.isactive'  => 1,
                            'Easycase.istype'    => 1,
                            'Easycase.project_id' => $ids[$i],
                            'Easycase.assign_to' => '0',
                            'Easycase.user_id' => SES_ID]]];
                }
                if ($filters == 'latest' && $all == 'all') {
                    /*App::import('Model','User');$User = new User();
                    $cond = array('conditions'=>array('User.id' => SES_ID), 'fields' => array('User.dt_last_logout','User.dt_last_login'));
                    $res = $User->find('first', $cond);
                    $logout_time=$res['User']['dt_last_logout'];
                    $login_time=$res['User']['dt_last_login'];*/
                    $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
                    $arrtyp = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <=' => GMT_DATETIME]];
                }
                if ($filters == 'delegateto' && $all == 'all') {
                    $arrtyp = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
                }

                $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i],'Easycase.type_id' => $id, $arrtyp]]);
                $total += $totcase;
            }
            $totcase = $total;


        } elseif ($type == 'priority' && $all == 'all') {
            if ($id == 'High' && $all == 'all') {
                $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
                $ProjectUser = ClassRegistry::init('ProjectUser');
                $ProjectUser->unbindModel(['belongsTo' => ['User']]);
                $allProjArr = $ProjectUser->find('all', $cond);
                $ids = [];
                foreach ($allProjArr as $csid) {
                    array_push($ids, $csid['Project']['id']);
                }
                $total = 0;
                for ($i = 0;$i < count($ids);$i++) {


                    $arr1 = [];
                    if ($filters == 'assigntome' && $all == 'all') {
                        $arr1 = ['OR' => [
                            'AND' => [
                                'Easycase.isactive'   => 1,
                                'Easycase.istype'     => 1,
                                'Easycase.project_id' => $ids[$i],
                                'Easycase.assign_to' => SES_ID
                            ],
                            [
                                'Easycase.isactive'  => 1,
                                'Easycase.istype'    => 1,
                                'Easycase.project_id' => $ids[$i],
                                'Easycase.assign_to' => '0',
                                'Easycase.user_id' => SES_ID]]];
                    }
                    if ($filters == 'latest') {
                        /*App::import('Model','User');$User = new User();
                        $cond = array('conditions'=>array('User.id' => SES_ID), 'fields' => array('User.dt_last_logout','User.dt_last_login'));
                        $res = $User->find('first', $cond);
                        $logout_time=$res['User']['dt_last_logout'];
                        $login_time=$res['User']['dt_last_login'];*/
                        $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
                        $arr1 = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <=' => GMT_DATETIME]];
                    }
                    if ($filters == 'delegateto' && $all == 'all') {
                        $arr1 = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
                    }

                    $Easycase = ClassRegistry::init('Easycase');
                    $Easycase->recursive = -1;
                    $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i],'Easycase.priority' => 0, $arr1]]);
                    $total += $totcase;
                }
                $totcase = $total;
            } elseif ($id == 'Medium' && $all == 'all') {
                $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
                $ProjectUser = ClassRegistry::init('ProjectUser');
                $ProjectUser->unbindModel(['belongsTo' => ['User']]);
                $allProjArr = $ProjectUser->find('all', $cond);
                $ids = [];
                foreach ($allProjArr as $csid) {
                    array_push($ids, $csid['Project']['id']);
                }
                $total = 0;
                for ($i = 0;$i < count($ids);$i++) {

                    $arr2 = [];
                    if ($filters == 'assigntome' && $all == 'all') {
                        $arr2 = ['OR' => [
                            'AND' => [
                                'Easycase.isactive'   => 1,
                                'Easycase.istype'     => 1,
                                'Easycase.project_id' => $ids[$i],
                                'Easycase.assign_to' => SES_ID
                            ],
                            [
                                'Easycase.isactive'  => 1,
                                'Easycase.istype'    => 1,
                                'Easycase.project_id' => $ids[$i],
                                'Easycase.assign_to' => '0',
                                'Easycase.user_id' => SES_ID]]];
                    }
                    if ($filters == 'latest') {
                        /*App::import('Model','User');$User = new User();
                        $cond = array('conditions'=>array('User.id' => SES_ID), 'fields' => array('User.dt_last_logout','User.dt_last_login'));
                        $res = $User->find('first', $cond);
                        $logout_time=$res['User']['dt_last_logout'];
                        $login_time=$res['User']['dt_last_login'];*/
                        $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
                        $arr2 = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <=' => GMT_DATETIME]];
                    }
                    if ($filters == 'delegateto' && $all == 'all') {
                        $arr2 = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
                    }



                    $Easycase = ClassRegistry::init('Easycase');
                    $Easycase->recursive = -1;
                    $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i],'Easycase.priority' => 1, $arr2]]);
                    $total += $totcase;
                }
                $totcase = $total;
            } else {
                if ($all == 'all') {
                    $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
                    $ProjectUser = ClassRegistry::init('ProjectUser');
                    $ProjectUser->unbindModel(['belongsTo' => ['User']]);
                    $allProjArr = $ProjectUser->find('all', $cond);
                    $ids = [];
                    foreach ($allProjArr as $csid) {
                        array_push($ids, $csid['Project']['id']);
                    }
                    $total = 0;
                    for ($i = 0;$i < count($ids);$i++) {
                        $arr3 = [];
                        if ($filters == 'assigntome' && $all == 'all') {
                            $arr3 = ['OR' => [
                                'AND' => [
                                    'Easycase.isactive'   => 1,
                                    'Easycase.istype'     => 1,
                                    'Easycase.project_id' => $ids[$i],
                                    'Easycase.assign_to' => SES_ID
                                ],
                                [
                                    'Easycase.isactive'  => 1,
                                    'Easycase.istype'    => 1,
                                    'Easycase.project_id' => $ids[$i],
                                    'Easycase.assign_to' => '0',
                                    'Easycase.user_id' => SES_ID]]];
                        }
                        if ($filters == 'latest') {
                            /*App::import('Model','User');$User = new User();
                            $cond = array('conditions'=>array('User.id' => SES_ID), 'fields' => array('User.dt_last_logout','User.dt_last_login'));
                            $res = $User->find('first', $cond);
                            $logout_time=$res['User']['dt_last_logout'];
                            $login_time=$res['User']['dt_last_login'];*/
                            $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
                            $arr3 = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <=' => GMT_DATETIME]];
                        }
                        if ($filters == 'delegateto' && $all == 'all') {
                            $arr3 = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
                        }
                        $Easycase = ClassRegistry::init('Easycase');
                        $Easycase->recursive = -1;
                        $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i],'Easycase.priority >=' => 2, $arr3]]);
                        $total += $totcase;
                    }
                    $totcase = $total;

                }
            }
        } elseif ($type == 'priority' && $all != 'all') {
            if ($id == 'High' && $all != 'all') {
                $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid,'Easycase.priority' => 0, $arr]]);
            } elseif ($id == 'Medium' && $all != 'all') {
                $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid,'Easycase.priority' => 1, $arr]]);
            } else {
                if ($all != 'all') {
                    $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid,'Easycase.priority >=' => 2, $arr]]);
                }
            }
        } elseif ($type == 'member' && $all != 'all') {
            $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $pid,'Easycase.user_id' => $id, $arr]]);
        } elseif ($type == 'member' && $all == 'all') {
            $cond = ['conditions' => ['ProjectUser.user_id' => SES_ID,'Project.isactive' => 1], 'fields' => ['DISTINCT  Project.id'],'order' => ['ProjectUser.dt_visited DESC']];
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->unbindModel(['belongsTo' => ['User']]);
            $allProjArr = $ProjectUser->find('all', $cond);
            $ids = [];
            foreach ($allProjArr as $csid) {
                array_push($ids, $csid['Project']['id']);
            }
            $total = 0;
            for ($i = 0;$i < count($ids);$i++) {
                $arr3 = [];
                if ($filters == 'assigntome' && $all == 'all') {
                    $arr3 = ['OR' => [
                        'AND' => [
                            'Easycase.isactive'   => 1,
                            'Easycase.istype'     => 1,
                            'Easycase.project_id' => $ids[$i],
                            'Easycase.assign_to' => SES_ID
                        ],
                        [
                            'Easycase.isactive'  => 1,
                            'Easycase.istype'    => 1,
                            'Easycase.project_id' => $ids[$i],
                            'Easycase.assign_to' => '0',
                            'Easycase.user_id' => SES_ID]]];
                }
                if ($filters == 'latest') {
                    /*App::import('Model','User');$User = new User();
                    $cond = array('conditions'=>array('User.id' => SES_ID), 'fields' => array('User.dt_last_logout','User.dt_last_login'));
                    $res = $User->find('first', $cond);
                    $logout_time=$res['User']['dt_last_logout'];
                    $login_time=$res['User']['dt_last_login'];*/
                    $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME.'-2 day'));
                    $arr3 = ['AND' => ['Easycase.dt_created >' => $before,'Easycase.dt_created <' => GMT_DATETIME]];
                }
                if ($filters == 'delegateto' && $all == 'all') {
                    $arr3 = ['Easycase.user_id' => SES_ID, 'OR' => ['Easycase.assign_to !=' => 0], 'Easycase.assign_to !=' => SES_ID];
                }
                $Easycase = ClassRegistry::init('Easycase');
                $Easycase->recursive = -1;
                $totcase = $Easycase->find('count', ['conditions' => ['Easycase.isactive' => 1,'Easycase.istype' => 1,'Easycase.project_id' => $ids[$i],'Easycase.user_id' => $id, $arr3]]);
                $total += $totcase;
            }
            $totcase = $total;
        }
        return $totcase;
    }
    public function getAllCsId($pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $caseIds = $Easycase->find('all', ['conditions' => ['Easycase.project_id' => $pid],'fields' => 'id']);
        $ids = [];
        foreach ($caseIds as $csid) {
            array_push($ids, $csid['Easycase']['id']);
        }
        return $ids;
    }
    public function usedSpace($curProjId = null, $company_id = SES_COMP)
    {
        //$allTotsizeinMb = 0;
        //return $allTotsizeinMb;
        $CaseFiles = ClassRegistry::init('CaseFiles');
        $this->recursive = -1;
        $cond = ' 1 ';
        if ($company_id) {
            $cond .= ' AND company_id='.$company_id;
        }
        if ($curProjId) {
            $cond .= ' AND project_id='.$curProjId;
        }
        $sql = 'SELECT SUM(file_size) AS file_size  FROM case_files   WHERE '.$cond;
        $res1 = $CaseFiles->query($sql);
        $filesize = $res1['0']['0']['file_size'] / 1024;

        $CaseEditorFile = ClassRegistry::init('CaseEditorFile');
        $CaseEditorFile->recursive = -1;
        $sql_n = 'SELECT SUM(file_size) AS file_size FROM case_editor_files WHERE ' . $cond;
        $res_n = $CaseEditorFile->query($sql_n);
        $filesize_n = $res_n['0']['0']['file_size'] / 1024;
        $tot_size = $filesize_n + $filesize;

        //return number_format($filesize,2);
        return round($tot_size, 2);

        /*if(!$company_id) {
            $company_id = SES_COMP;
        }

        if($curProjId) {
            $cid = $this->getAllCsId($curProjId);
        }
        else {
            $Project = ClassRegistry::init('Project');
            $Project->recursive = -1;

            $curProjId = array();

            $allProjIds = $Project->find('all', array('conditions'=>array('Project.company_id' => $company_id),'fields' => array('Project.id')));
            foreach($allProjIds as $pjIds) {
                $curProjId[] = $pjIds['Project']['id'];
            }
            $cid = $this->getAllCsId($curProjId);
        }

        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $caseSize = $Easycase->find('all', array('conditions'=>array('Easycase.project_id' => $curProjId,'Easycase.isactive' => 1),'fields' => array('SUM(LENGTH(message)) as msg','SUM(LENGTH(title)) as titl')));

        App::import('Model','CaseFile'); $CaseFile = new CaseFile();
        $CaseFile->recursive = -1;
        $caseFileSize = $CaseFile->find('all', array('conditions'=>array('CaseFile.easycase_id' => $cid,'CaseFile.isactive' => 1), 'fields'=>array('SUM(file_size) AS filesize','SUM(LENGTH(file)) as filelength')));

        $totalsize = $caseSize['0']['0']['msg']+$caseSize['0']['0']['titl']+$caseFileSize['0']['0']['filelength'];
        $totalsizeInKB = $totalsize/1024;

        $filesizeInKb = $caseFileSize['0']['0']['filesize'];
        $allTotsizeinKb = $filesizeInKb+$totalsizeInKB;
        $allTotsizeinMb = round($allTotsizeinKb/1024,2);

        return $allTotsizeinMb;*/

    }
    public function fullSpace($used, $totalsize = 1024)
    {
        $full = $used * 100 / $totalsize;
        $used = round($full, 1);
        return $used;
    }
    public function fullSpacegrid($used, $totalsize = MAX_SPACE_USAGE)
    {
        $full = $used * 100 / $totalsize;
        $used = round($full, 2);
        return $used;
    }
    public function getalluser($pjid)
    {
        $ProjectUser = ClassRegistry::init('ProjectUser');
        $ProjectUser->recursive = -1;
        $userno = $ProjectUser->find('count', ['conditions' => ['ProjectUser.project_id' => $pjid],'fields' => 'DISTINCT ProjectUser.user_id']);
        return $userno;

    }
    public function getlatestactivitypid($pid, $chk = null)
    {
        $dateString = '';
        $Easycase = TableRegistry::getTableLocator()->get('Easycases');
        $latestactivity = $Easycase
            ->find()
            ->select(['Easycases.dt_created'])
            ->disableHydration()
            ->where(
                function (QueryExpression $exp, Query $q) use ($pid) {
                    return $exp->eq('Easycases.project_id', $pid);
                }
            )
            ->order(['Easycases.dt_created' => 'DESC'])
            ->first();
        if (!empty($latestactivity)) {
            $dateString = !empty($chk) ? $latestactivity['dt_created']->format('Y-m-d H:i:s') : $latestactivity['dt_created']->format('Y-m-d');
        }
        return $dateString;
    }

    public function getallproject($id, $company_id = null)
    {
        // [TODO optimize]
        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        if (empty($company_id)) {
            $company_id = SES_COMP;
        }
        $caseIds = $ProjectUser
            ->find()
            ->select(['project_id'])
            ->where(['user_id' => $id, 'company_id' => $company_id])
            ->disableHydration()->all()->toArray();

        $ids = Hash::extract($caseIds, '{n}.project_id');
        //return $ids;
        $userallprj = [];
        foreach ($ids as $k => $v) {
            $Project = TableRegistry::getTableLocator()->get('Projects');
            $caseIdss = $Project
                ->find()
                ->select(['name'])
                ->where(['id' => $v])->disableHydration()
                ->toArray();
            foreach ($caseIdss as $cssid) {
                array_push($userallprj, $cssid['name']);
            }
        }
        return $userallprj;
    }
    public function getlatestactivity($uid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $latestactivity = $Easycase->find('first', ['conditions' => ['Easycase.user_id =' => $uid],'fields' => 'dt_created','order' => ['Easycase.dt_created DESC']]);

        return $latestactivity;

    }
    public function getpjname($pid)
    {
        $projectsTable = TableRegistry::get('Projects');
        $uniqid = $projectsTable->find('all', [
            'conditions' => ['id' => $pid, 'isactive' => 1],
            'fields' => ['name', 'short_name']
        ])->disableHydration()->first();
        return $uniqid['name'] ?? '';
    }
    public function getusrname($uid)
    {
        $User = ClassRegistry::init('User');
        $User->recursive = -1;
        $usrname = $User->find('first', ['conditions' => ['User.id' => $uid,'User.isactive' => 1],'fields' => ['name','short_name']]);
        return $usrname;
    }
    public function getarccasecount($pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        if ($pid == 'all') {
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $getAllProj = $ProjectUser->find('all', ['conditions' => ['ProjectUser.user_id' => SES_ID,'ProjectUser.company_id' => SES_COMP],'fields' => 'ProjectUser.project_id']);

            $qry = '';
            $projIds = [];
            if (!empty($getAllProj)) {
                foreach ($getAllProj as $pj) {
                    $projIds[] = $pj['ProjectUser']['project_id'];
                }
                $getUsers = [];
                if (count($projIds)) {
                    $pjids = '('.implode(',', $projIds).')';
                    $qry = 'AND Easycase.project_id IN '.$pjids.'';
                }
            }
            $caseCount1 = $Easycase->query("SELECT COUNT( DISTINCT Easycase.id) as count FROM easycases as Easycase,archives as Archive WHERE Easycase.id=Archive.easycase_id AND Archive.type = '1' AND Archive.company_id ='".SES_COMP."' ".$qry." AND Easycase.project_id != '0';");
            return $caseCount1['0']['0']['count'];
        } else {
            $caseCount1 = $Easycase->query("SELECT COUNT( DISTINCT Easycase.id) as count FROM easycases as Easycase,archives as Archive WHERE Easycase.id=Archive.easycase_id AND Archive.type = '1' AND Archive.company_id ='".SES_COMP."' AND Easycase.project_id = '".$pid."'");
            return $caseCount1['0']['0']['count'];
        }
    }
    public function getarcfilecount($pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        if ($pid == 'all') {
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $getAllProj = $ProjectUser->find('all', ['conditions' => ['ProjectUser.user_id' => SES_ID,'ProjectUser.company_id' => SES_COMP],'fields' => 'ProjectUser.project_id']);

            $qry = '';
            $projIds = [];
            if (!empty($getAllProj)) {
                foreach ($getAllProj as $pj) {
                    $projIds[] = $pj['ProjectUser']['project_id'];
                }
                $getUsers = [];
                if (count($projIds)) {
                    $pjids = '('.implode(',', $projIds).')';
                    $qry = 'AND Easycase.project_id IN '.$pjids.'';
                }
            }
            $caseCount1 = $Easycase->query("SELECT COUNT(Easycase.id) as count FROM easycases as Easycase,case_files as CaseFile,archives as Archive WHERE Archive.case_file_id=CaseFile.id AND Easycase.id=CaseFile.easycase_id AND Easycase.isactive='1' AND CaseFile.isactive = '0' AND Archive.type='1' AND Archive.company_id ='".SES_COMP."' ".$qry." AND Easycase.project_id != '0';");
            return $caseCount1['0']['0']['count'];
        } else {
            $caseCount1 = $Easycase->query("SELECT COUNT(Easycase.id) as count FROM easycases as Easycase,case_files as CaseFile,archives as Archive WHERE Archive.case_file_id=CaseFile.id AND Easycase.id=CaseFile.easycase_id AND Easycase.isactive='1' AND CaseFile.isactive = '0' AND Archive.type='1' AND Archive.company_id ='".SES_COMP."' AND Easycase.project_id = '".$pid."';");
            return $caseCount1['0']['0']['count'];
        }
    }

    public function getactivitycount($pid)
    {
        if (!defined('SES_COMP')) {
            define('SES_COMP', 1);
        }
        $caseActivitiesTable = TableRegistry::getTableLocator()->get('CaseActivities');
        $caseActivitiesCond = ['CaseActivities.isactive' => 1];
        if ($pid !== 'all') {
            $caseActivitiesCond['CaseActivities.isactive'] = $pid;
        }
        $activitycount = $caseActivitiesTable->find()
            ->where($caseActivitiesCond)
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CaseActivities.project_id', 'Projects.id'),
                    'Projects.company_id' => SES_COMP
                ],
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id')
                ],
            ])
            ->count();
        return $activitycount;
    }

    public function getinviteqstr($cid, $uid)
    {

        $UserInvitation = TableRegistry::getTableLocator()->get('UserInvitations');
        $qstr = $UserInvitation
            ->find()
            ->select(['qstr'])
            ->where(['user_id' => $uid, 'company_id' => $cid])
            ->first();
        return  $qstr ? $qstr['qstr'] : '';
    }

    public function displaymilestoneNo($pjid)
    {
        $milestone = TableRegistry::getTableLocator()->get('Milestones');
        $mile = $milestone->find()->where(['project_id' => $pjid])->count();
        return $mile;
    }
}
