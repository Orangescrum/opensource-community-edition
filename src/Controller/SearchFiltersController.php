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

namespace App\Controller;

use App\Utility\CommonUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\FormatHelper;
use Cake\View\View;

/**
 * SearchFilters Controller
 *
 * @property \App\Model\Table\SearchFiltersTable $SearchFilters
 * @method \App\Model\Entity\SearchFilter[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SearchFiltersController extends AppController
{
    public function saveFilter()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        $data['SearchFilter'] = $postData['data']['SearchFilter'];
        $json_arr = [];
        if ($data['SearchFilter']['create'] == 0) {
            $data['SearchFilter']['id'] = '';
            $data['SearchFilter']['name'] = trim($postData['data']['SearchFilter']['name']);
            /* Check the duplicate value of filter name */
            $cnt = $this->SearchFilters->find()
                ->where([
                    'name' => trim($postData['data']['SearchFilter']['name']),
                    'user_id' => SES_ID,
                    'company_id' => SES_COMP
                ])
                ->all()
                ->count('id');
            if ($cnt > 0) {
                $msg['type'] = 'error';
                $msg['message'] = __('Filter name already exists.');
                return $this->jsonResponse(json_encode($msg));
            }
        }
        $json_arr['PRIORITY'] = ($data['SearchFilter']['PRIORITY']) ? $data['SearchFilter']['PRIORITY'] : '';
        $json_arr['CS_TYPES'] = ($data['SearchFilter']['CS_TYPES']) ? $data['SearchFilter']['CS_TYPES'] : '';
        $json_arr['TASKLABEL'] = ($data['SearchFilter']['TASKLABEL']) ? $data['SearchFilter']['TASKLABEL'] : '';
        $json_arr['MEMBERS'] = ($data['SearchFilter']['MEMBERS']) ? $data['SearchFilter']['MEMBERS'] : '';
        $json_arr['COMMENTS'] = ($data['SearchFilter']['COMMENTS']) ? $data['SearchFilter']['COMMENTS'] : '';
        $json_arr['ASSIGNTO'] = ($data['SearchFilter']['ASSIGNTO']) ? $data['SearchFilter']['ASSIGNTO'] : '';
        $json_arr['DATE'] = ($data['SearchFilter']['DATE']) ? $data['SearchFilter']['DATE'] : '';
        $json_arr['DUE_DATE'] = ($data['SearchFilter']['DUE_DATE']) ? $data['SearchFilter']['DUE_DATE'] : '';
        $json_arr['TASKGROUP'] = ($data['SearchFilter']['TASKGROUP']) ? $data['SearchFilter']['TASKGROUP'] : '';
        $json_arr['TASKGROUP_FIL'] = (!empty($_COOKIE['TASKGROUP_FIL'])) ? $_COOKIE['TASKGROUP_FIL'] : '';
        $json_arr['STATUS'] = ($data['SearchFilter']['STATUS']) ? $data['SearchFilter']['STATUS'] : '';
        $json_arr['CUSTOM_STATUS'] = ($data['SearchFilter']['CUSTOM_STATUS']) ? $data['SearchFilter']['CUSTOM_STATUS'] : '';
        $json_arr['CUSTOM_FIELD'] = ($data['SearchFilter']['CUSTOM_FIELD'] ?? '') ?: '';
        $data['SearchFilter']['json_array'] = json_encode($json_arr);
        $data['SearchFilter']['company_id'] = SES_COMP;

        $entity = $this->SearchFilters->patchEntity($this->SearchFilters->newEmptyEntity(), $data['SearchFilter']);
        $isSaved = $this->SearchFilters->save($entity);
        $msg = [];
        if ($isSaved) {
            $msg['type'] = 'success';
            if ($postData['data']['SearchFilter']['create'] == 0) {
                $msg['message'] = __('Filter saved successfully.');
            } else {
                $msg['message'] = __('Filter updated successfully.');
            }
            $msg['id'] = $isSaved->id;
        } else {
            $msg['type'] = 'error';
            $msg['message'] = __("Filter can't save. Please try again later.");
        }
        return $this->jsonResponse(json_encode($msg));
    }

    public function setFilter()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        if (isset($postData['id']) && !empty($postData['id'])) {
            $id = $postData['id'];
            if (stristr($postData['id'], 'custom-')) {
                $serch_id = explode('custom-', $postData['id']);
                $id = $serch_id[1];
            }
            if (is_numeric($id)) {
                $sfarray = $this->SearchFilters->find()
                    ->where(['id' => $id])
                    ->disableHydration()
                    ->first();
                return $this->jsonResponse($sfarray['json_array']);
            }
            echo '1';
            exit;
        }
    }

    public function getAllFilters()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        $labelsTable = $this->fetchTable('Labels');
        $page_limit = FILTER_PAGE_LIMIT;
        $filterPage = (isset($postData['casePage']) && !empty($postData['casePage'])) ? $postData['casePage'] : 1;
        $page = $filterPage;
        $limit1 = $page * $page_limit - $page_limit;
        $limit2 = $page_limit;
        $sortby = '';
        $orderby = [];
        if (isset($_COOKIE['TASKSORTBY'])) {
            $sortby = $_COOKIE['TASKSORTBY'];
            $sortorder = $_COOKIE['TASKSORTORDER'];
        } else {
            $sortorder = 'DESC';
        }
        if ($sortby == 'name') {
            $orderby = ['SearchFilter.name' => $sortorder] ;
        }

        $count = $this->SearchFilters->find()
            ->where([
                'user_id' => SES_ID,
                'company_id' => SES_COMP,
                'name !=' => 'default'
            ])
            ->all()
            ->count('id');


        $data = $this->SearchFilters->find()
            ->where([
                'user_id' => SES_ID,
                'company_id' => SES_COMP,
                'name !=' => 'default'
            ])
            ->order($orderby)
            ->limit($limit2)
            ->offset($limit1)
            ->disableHydration()
            ->toArray();
        $data = CommonUtility::insertModel('SearchFilter', $data);

        $data_new = [];
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());
        $typesTable = $this->fetchTable('Types');
        $typeArr = $typesTable->find()
            ->select($typesTable)
            ->where(['company_id IN' => [0, SES_COMP]])
            ->disableHydration()
            ->toArray();
        foreach ($data as $k => $v) {
            $data_new[$k]['SearchFilter'] = $v['SearchFilter'];
            $arr =  json_decode($v['SearchFilter']['json_array']);
            $arr_fil = json_decode($v['SearchFilter']['json_array'], true);
            $data_new[$k]['SearchFilter']['namewithcount'] = $this->SearchFilters->getCount($arr_fil, $postData['projUniq'], $postData['milestoneIds'], $postData['case_srch'], $postData['checktype']);
            $arr1 = [];
            $lbls = '';
            foreach ($arr as $k1 => $v1) {
                if ($k1 == 'TASKLABEL' && !empty($v1) && $v1 != 'all') {
                    $lbls = '';
                    if (strstr($v1, '-')) {
                        $expst_lbl = explode('-', $v1);
                    } else {
                        $expst_lbl = $v1;
                    }
                    $res_lbls = $labelsTable->getLabeByUid($expst_lbl, SES_COMP);
                    if ($res_lbls) {
                        foreach ($res_lbls as $stv_lbl) {
                            $lbls .= "<span class='filter_opn' rel='tooltip' title='Label'>" . $stv_lbl['lbl_title'] . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$stv_lbl['id']."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    }
                    $arr1['TASKLABEL'] = $lbls;
                } elseif ($k1 == 'CS_TYPES' && !empty($v1) && $v1 != 'all') {
                    $types = '';
                    if (strstr($v1, '-')) {
                        $expst3 = explode('-', $v1);
                        foreach ($expst3 as $st3) {
                            $csTypArr = $cq->getTypeArr($st3, $typeArr);
                            $types .= "<span class='filter_opn' rel='tooltip' title='Task Type'>" . $csTypArr['short_name'] . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st3."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                        $types = trim($types, ', ');
                    } else {
                        $csTypArr = $cq->getTypeArr($v1, $typeArr);
                        $types = "<span class='filter_opn' rel='tooltip' title='Task Type' >" . $csTypArr['short_name']. "<a href='javascript:void(0);'  onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['CS_TYPES'] = $types;
                } elseif ($k1 == 'MEMBERS' && $v1 != '' && $v1 != 'all') {
                    $mems = '';
                    if (strstr($v1, '-')) {
                        $expst4 = explode('-', $v1);
                        $cbymems = $this->Format->caseMemsList($expst4);
                        foreach ($cbymems as $key => $st4) {
                            $mems .= "<span class='filter_opn' rel='tooltip' title='Created By " . $this->Format->caseMemsName($key) . "' >" . $st4 . "<a href='javascript:void(0);'  onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st4."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } else {
                        $mems = "<span class='filter_opn' rel='tooltip' title='Created By " . $this->Format->caseMemsName($v1) . "' >" . $this->Format->caseMemsList($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['MEMBERS'] = $mems;
                } elseif ($k1 == 'COMMENTS' && $v1 != '' && $v1 != 'all') {
                    $coms = '';
                    if (strstr($v1, '-')) {
                        $expst14 = explode('-', $v1);
                        $cbymems = $this->Format->caseMemsList($expst14);
                        foreach ($cbymems as $key => $st14) {
                            $coms .= "<span class='filter_opn' rel='tooltip' title='Commented By " . $this->Format->caseMemsName($key) . "' >" . $st14 . "<a href='javascript:void(0);'  onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st14."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } else {
                        $coms = "<span class='filter_opn' rel='tooltip' title='Commented By " . $this->Format->caseMemsName($v1) . "' >" . $this->Format->caseMemsList($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['COMMENTS'] = $coms;
                } elseif ($k1 == 'TASKGROUP' && $v1 != '' && $v1 != 'all') {
                    $tsg = '';
                    if (strstr($v1, '-')) {
                        $expst19 = explode('-', $v1);
                        $tsgname = $this->Format->caseMilestonesName($expst19, 1);
                        if ($expst19[0] == 'default') {
                            $tsg .= "<span class='filter_opn' rel='tooltip' title='Task Group' >Default Task Group/Backlog<a href='javascript:void(0);'  onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1."\",\"default\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                        foreach ($tsgname as $key => $st19) {
                            $tsg .= "<span class='filter_opn' rel='tooltip' title='Task Group' >" . $st19 . "<a href='javascript:void(0);'  onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$key."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } else {
                        $tsg = "<span class='filter_opn' rel='tooltip' title='Task Group' >" . $this->Format->caseMilestonesName($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['TASKGROUP'] = $tsg;
                } elseif ($k1 == 'ASSIGNTO' && $v1 != '' && $v1 != 'all') {
                    $asns = '';
                    if (strstr($v1, '-')) {
                        $expst5 = explode('-', $v1);
                        $asmembers = $this->Format->caseMemsList($expst5);
                        foreach ($asmembers as $key => $st5) {
                            $asns .= "<span class='filter_opn' rel='tooltip' title='Assign To: " . $this->Format->caseMemsName($key) . "' >" . $st5 . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st5."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } elseif ($v1 == 'unassigned') {
                        $asns = "<span class='filter_opn' rel='tooltip' title='Assign To:No body' >Unassigned<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    } else {
                        $asns = "<span class='filter_opn' rel='tooltip' title='Assign To: " . $this->Format->caseMemsName($v1) . "' >" . $this->Format->caseMemsList($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['ASSIGNTO'] = $asns;
                } elseif ($k1 == 'STATUS' && $v1 != '' && $v1 != 'all') {
                    $sts = '';
                    if (strstr($v1, '-')) {
                        $expst6 = explode('-', $v1);
                        foreach ($expst6 as $key => $st6) {
                            $sts .= "<span class='filter_opn' rel='tooltip' title='Task Status' >" . $this->Format->displayStatus($st6) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st6."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } else {
                        $sts = "<span class='filter_opn' rel='tooltip' title='Task Status' >" . $this->Format->displayStatus($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['STATUS'] = $sts;
                } elseif ($k1 == 'CUSTOM_STATUS' && $v1 != '' && $v1 != 'all') {
                    $csts = '';
                    if (strstr($v1, '-')) {
                        $expst6 = explode('-', $v1);
                        foreach ($expst6 as $key => $st6) {
                            $csts .= "<span class='filter_opn' rel='tooltip' title='Task Status' >" . $this->Format->displayStatus($st6) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$st6."\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                        }
                    } else {
                        $csts = "<span class='filter_opn' rel='tooltip' title='Task Status' >" . $this->Format->displayStatus($v1) . "<a href='javascript:void(0);' onclick='deleteFilterItem(".$v['SearchFilter']['id'].',"'.$k1.'","'.$v1."\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                    }
                    $arr1['CUSTOM_STATUS'] = $csts;
                } else {
                    $arr1[$k1] = $v1;
                }
            }
            $data_new[$k]['SearchFilter']['json_array'] = json_encode($arr1);
        }

        $allFilters['details'] = $data_new;
        $allFilters['page_limit'] = $page_limit;
        $allFilters['csPage'] = $filterPage;
        $allFilters['caseCount'] = $count;
        $allFilters['orderBy'] = $sortby;
        $allFilters['orderByType'] = $sortorder;

        $pgShLbl = $frmt->pagingShowRecords($count, $page_limit, $filterPage);
        $allFilters['pgShLbl'] = $pgShLbl;
        return $this->jsonResponse(json_encode($allFilters));
    }

    public function editFilters()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        $msg = [];
        if (isset($postData['id']) && !empty($postData['id'])) {
            $cnt = $this->SearchFilters->find()
                ->where([
                    'name' => trim($postData['value']),
                    'user_id' => SES_ID,
                    'company_id' => SES_COMP,
                    'id !=' => $postData['id']
                ])
                ->all()->count('id');
            if ($cnt > 0) {
                $msg['type'] = 'error';
                $msg['message'] = 'Filter name already exists.';
            } else {
                $entity = $this->SearchFilters->get($postData['id']);
                $entity->name = trim($postData['value']);
                $this->SearchFilters->save($entity);
                $msg['message'] = __('Filter updated successfully').'.';
                $msg['type'] = 'success';
            }
        } else {
            $msg['message'] = __('Filter can not update. Please try again later');
            $msg['type'] = 'error';
        }
        return $this->jsonResponse(json_encode($msg));
    }

    public function deleteFilters()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        $msg = [];
        if (isset($postData['id']) && !empty($postData['id'])) {
            $entity = $this->SearchFilters->get($postData['id']);
            $result = $this->SearchFilters->delete($entity);
            $msg['message'] = __('Filter deleted successfully').'.';
            $msg['type'] = 'success';
        } else {
            $msg['message'] = __('Filter can not delete. Please try again later');
            $msg['type'] = 'error';
        }
        return $this->jsonResponse(json_encode($msg));
    }

    public function setDefaultValue()
    {
        $data = [
            'name' => 'default',
            'id' => '',
            'json_array' => json_encode([
                'PRIORITY' => '',
                'CS_TYPES' => '',
                'MEMBERS' => '',
                'COMMENTS' => '',
                'ASSIGNTO' => '',
                'DATE' => '',
                'DUE_DATE' => '',
                'TASKGROUP' => '',
                'TASKGROUP_FIL' => '',
                'STATUS' => ''
            ]),
            'company_id' => SES_COMP,
            'user_id' => SES_ID,
            'first_records' => 1
        ];

        $record = $this->SearchFilters->find()
            ->where([
                'user_id' => SES_ID,
                'company_id' => SES_COMP,
                'name' => 'default'
            ])
            ->disableHydration()
            ->first();

        if ($record) {
            $this->SearchFilters->updateAll(['first_records' => 1], [
                'SearchFilters.user_id' => SES_ID,
                'SearchFilters.company_id' => SES_COMP,
                'SearchFilters.name' => 'default'
            ]);
        } else {
            $entity = $this->SearchFilters->newEmptyEntity();
            $this->SearchFilters->patchEntity($entity, $data);
            $this->SearchFilters->save($entity);
        }
        exit;
    }

    public function deleteItems()
    {
        $n = $this->request->getData('name');
        if ($this->request->getData('id')) {
            $datas = $this->SearchFilters->find()
                ->where(['id' => $this->request->getData('id')])
                ->disableHydration()
                ->first();
            $json_array = json_decode($datas['json_array']);
            $val = $json_array->$n;
            $val_arr = explode('-', $val);
            $result_arr = array_diff($val_arr, [$this->request->getData('value')]);
            $result_string = implode('-', $result_arr);
            $json_array->$n = $result_string;

            $entity = $this->SearchFilters->get($datas->id);
            $entity->json_array = json_encode($json_array);
            $this->SearchFilters->save($entity);
        }
        exit;
    }

    public function kanbanFilters()
    {
        $sf = $this->SearchFilters->getFiltersWithCounts($this->request->getData());
        $data['sf'] = $sf;
        return $this->jsonResponse(json_encode($data));
    }
}
