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

namespace App\Model\Table;

use App\Controller\Component\FormatComponent;
use App\Controller\Component\StorageComponent;
use App\Utility\FileUtility;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use DOMDocument;
use Exception;

/**
 * CaseEditorFiles Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CaseEditorFile newEmptyEntity()
 * @method \App\Model\Entity\CaseEditorFile newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CaseEditorFile[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CaseEditorFile get($primaryKey, $options = [])
 * @method \App\Model\Entity\CaseEditorFile findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CaseEditorFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CaseEditorFile[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CaseEditorFile|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseEditorFile saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseEditorFile[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseEditorFile[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseEditorFile[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseEditorFile[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CaseEditorFilesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('case_editor_files');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('uniq_id')
            ->maxLength('uniq_id', 64)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 200)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->integer('file_size')
            ->notEmptyFile('file_size');

        $validator
            ->notEmptyString('is_deleted');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        // $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        // $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        // $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function removeFile($name = null, $comp_id = null)
    {
        $retArr['status'] = 'success';
        try {
            if ($name) {
                $existImages = $this->find('all', ['conditions' => ['name' => $name, 'company_id' => $comp_id, 'is_deleted' => 2]])->disableHydration()->disableResultsCasting()->first();
                if (!empty($existImages)) {
                    $existImages['CaseEditorFile']['is_deleted'] = 1;
                    $updated = $this->updateAll(['is_deleted' => 1], ['id' => $existImages['id']]);
                    if ($updated) {
                        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
                        $ec = $easycasesTable->find()
                            ->where([
                                'project_id' => $existImages['project_id'],
                                fn($exp) => $exp->like('message', "%$name%")
                            ])
                            ->disableHydration()
                            ->disableResultsCasting()
                            ->first();
                        if ($ec) {
                            $orgi_contentxt = '';
                            $dom = new DOMDocument();
                            $dom->loadHTML($ec['message']);
                            foreach ($dom->getElementsByTagName('img') as $k => $item) {
                                $srcs = $item->getAttribute('src');
                                if (stristr($srcs, $name)) {
                                    $item->parentNode->removeChild($item);
                                    $orgi_content = $dom->saveHTML();
                                    $orgi_contentArr = explode('<body>', $orgi_content);
                                    $orgi_contentxt = str_replace('</body></html>', '', $orgi_contentArr[1]);
                                }
                            }
                            $ec['message'] = $orgi_contentxt;
                            $t_mesg = trim(strip_tags(nl2br($ec['message'])));
                            if (empty($t_mesg) && $ec['istype'] == EasycasesTable::TYPE_POST) {
                                if (!$easycasesTable->deleteAll(['id' => $ec['id']])) {
                                    throw new Exception(__('Failed to update task record.'));
                                }
                            } else {
                                if (!$easycasesTable->updateAll(['message' => $ec['message']], ['id' => $ec['id']])) {
                                    throw new Exception(__('Failed to update task record.'));
                                }
                            }
                        }
                    } else {
                        throw new Exception(__('Failed to update record.'));
                    }
                } else {
                    throw new Exception(__('Failed to update record.'));
                }
            } else {
                throw new Exception(__('Failed to update record.'));
            }
        } catch (Exception $e) {
            $retArr['status'] = 'err';
            $retArr['msg'] = $e->getMessage();
        }

        return $retArr;
    }

    private function getImageData($src = null)
    {
        if (!empty($src)) {
            $src_explode = explode('&quality=', explode('file=', $src)[1] ?? '');
            if (!empty($src_explode[0])) {
                return $src_explode[0];
            }
            return $src;
        }

        return $src;
    }

    public function formatImageCommentHtml($comment, $euid)
    {
        $retArr['status'] = 'success';
        try {
            if (!empty($comment)) {
                $orgi_contentxt = $comment;
                $dom = new DOMDocument();
                @$dom->loadHTML($comment);
                foreach ($dom->getElementsByTagName('img') as $k => $item) {
                    $srcs = $item->getAttribute('src');
                    $imgName = $this->getImageData($srcs);
                    $e = $dom->createElement('a', '');
                    $a = $item->parentNode->appendChild($e);
                    $a->setAttribute('href', $srcs);
                    $a->setAttribute('title', __('Preview Image'));
                    $a->setAttribute('rel', 'prettyPhoto[]');
                    $a->setAttribute('target', '_blank');
                    $a->setAttribute('class', 'remove-editor-img icon-menu-preview');

                    //download
                    $e1 = $dom->createElement('a', '');
                    $a1 = $item->parentNode->appendChild($e1);
                    $a1->setAttribute('href', HTTP_ROOT . 'easycases/download/' . $imgName);
                    $a1->setAttribute('title', __('Download'));
                    $a1->setAttribute('target', '_blank');
                    $a1->setAttribute('class', 'remove-editor-img icon-menu-download');

                    //delete
                    $e2 = $dom->createElement('a', '');
                    $a2 = $item->parentNode->appendChild($e2);
                    $a2->setAttribute('href', 'javascript:void(0);');
                    $a2->setAttribute('title', __('Delete'));
                    $a2->setAttribute('data-name', $imgName);
                    $a2->setAttribute('class', 'remove-editor-img delete-editor-img icon-menu-delete');

                    $orgi_content = $dom->saveHTML();
                    $orgi_contentArr = explode('<body>', $orgi_content);
                    $orgi_contentxt = str_replace('</body></html>', '', $orgi_contentArr[1]);
                }
                $retArr['comment'] = $orgi_contentxt;
            }
        } catch (Exception $e) {
            $retArr['comment'] = $comment;
        }

        return $retArr;
    }


    public function getImageFromComment($comment = '', $pid = null, $cs_id = null, $edtd_msg = '')
    {
        $retStatus = ['is_paste_image' => 0, 'comment' => $comment, 'uid' => 0];
        if ((!empty($comment) && stristr($comment, '<img')) || (!empty($edtd_msg) && stristr($edtd_msg, '<img'))) {
            if (!empty($edtd_msg)) {
                $doc = new DOMDocument();
                $doc->loadHTML($edtd_msg);
                $tags = $doc->getElementsByTagName('img');
                $imgArr = [];
                $imgArrExt = [];
                $imgArrNew = [];
                //iterate over all image tags
                $noimg_t = 0;
                foreach ($tags as $tag) {
                    //get src attribute of an img tag
                    $imgSrcExt = $tag->getAttribute('src');
                    if (stristr($imgSrcExt, DOMAIN) || stristr($imgSrcExt, 'users/image_thumb/?type=editor')) {
                        array_push($imgArrExt, $imgSrcExt);
                    }
                }
            }
            $doc = new DOMDocument();
            // dd($comment);
            $doc->loadHTML($comment);
            $tags = $doc->getElementsByTagName('img');
            $imgArr = [];
            //iterate over all image tags
            $noimg_t = 0;
            foreach ($tags as $tag) {
                //get src attribute of an img tag
                $imgSrc = $tag->getAttribute('src');
                if (stristr($imgSrc, 'base64,')) {
                    $comment = str_replace($imgSrc, 'EDTR__OS_IMG' . $noimg_t, $comment);
                    $exploddt_t = explode('base64,', $imgSrc);
                    $typ_ext = FileUtility::getImageExtFromComment($exploddt_t[0]);
                    $imgArr_t = [
                        'name' => time() . '_' . $noimg_t . '_case_edtr.' . $typ_ext[0],
                        'type' => $typ_ext[1],
                        'size' => ((strlen($exploddt_t[1]) * (3 / 4) - 1) / 1024),
                        'content' => $exploddt_t[1]
                    ];
                    array_push($imgArr, $imgArr_t);
                    $noimg_t++;
                } else {
                    if (stristr($imgSrc, 'users/image_thumb/?type=editor')) {
                        if (stristr($imgSrc, '../')) {
                            $imgSrc = str_replace('../', HTTP_ROOT, $imgSrc);
                        } elseif (stristr($imgSrc, 'users/image_thumb')) {
                            $imgSrc = str_replace('../', HTTP_ROOT, $imgSrc);
                        }
                        $imgSrc = str_replace('users/image_thumb', HTTP_ROOT . 'users/image_thumb', $imgSrc);
                        array_push($imgArrNew, $imgSrc);
                    }
                }
            }

            if (!empty($imgArrExt) && $cs_id) {
                $existImages = $this->find('list', [
                    'conditions' => [
                        'easycase_id' => $cs_id,
                        'company_id' => SES_COMP,
                        'project_id' => $pid,
                        'is_deleted' => 2
                    ],
                    'fields' => ['id', 'name'],
                    'keyField' => 'id',
                    'valueField' => 'name'
                ])->disableHydration()->toArray();
                if (!empty($existImages)) {
                    if (empty($imgArrNew)) {
                        $this->updateAll(['is_deleted' => 1], ['easycase_id' => $cs_id, 'company_id' => SES_COMP, 'project_id' => $pid]);
                    } else {
                        $n_fl_arra = [];
                        foreach ($imgArrNew as $rk => $rv) {
                            $q_pars = explode('&file=', parse_url($rv, PHP_URL_QUERY));
                            $q_pars_f = explode('&quality', $q_pars[1]);
                            array_push($n_fl_arra, $q_pars_f[0]);
                        }
                        foreach ($existImages as $rko => $rvo) {
                            if (!in_array(trim($rvo), $n_fl_arra)) {
                                $this->updateAll(['is_deleted' => 1], ['easycase_id' => $cs_id, 'company_id' => SES_COMP, 'project_id' => $pid, 'name' => trim($rvo)]);
                            }
                        }
                    }
                }
            }

            if (!empty($imgArr)) {
                $retArr = $this->editorFileUpload($imgArr, $pid, $cs_id);
                if (empty($retArr) || (isset($retArr['notallowed']) && $retArr['notallowed'])) {
                    $retStatus['comment'] = $comment;
                    return $retStatus;
                }
                $img_arry_uids = [];
                if (isset($retArr['images']) && !empty($retArr['images'])) {
                    $retStatus['is_paste_image'] = 1;
                    $doc_r = new DOMDocument();
                    $doc_r->loadHTML($comment);
                    $tags_r = $doc_r->getElementsByTagName('img');
                    $imgk = 0;
                    foreach ($tags_r as $tag_r) {
                        //get src attribute of an img tag
                        $imgSrc = $tag_r->getAttribute('src');
                        if (stristr($imgSrc, 'EDTR__OS_IMG')) {
                            if ($retArr['images'][$imgk]['status']) {
                                $url_t = HTTP_ROOT . 'users/image_thumb/?type=editor&file=' . $retArr['images'][$imgk]['name'] . '&quality=100';
                                $comment = str_replace('EDTR__OS_IMG' . $imgk, $url_t, $comment);
                                $img_arry_uids[$imgk] = $retArr['images'][$imgk]['uniq_id'];
                                $imgk++;
                            }
                        }
                    }
                    $retStatus['comment'] = $comment;
                    $retStatus['uid'] = $img_arry_uids;
                } else {
                    $retStatus['comment'] = $comment;
                }
            } else {
                $retStatus['comment'] = $comment;
            }
        }
        return $retStatus;
    }

    public function editorFileUpload($filesArr = null, $projId = null, $caseid = null)
    {
        if (!$filesArr) {
            return [];
        }
        $formatComponent = new FormatComponent(new ComponentRegistry());

        $file_path = WWW_ROOT . 'temp/';
        $file_orig_path = $file_path . 'orig/';
        if (!is_dir(DIR_CASE_EDITOR_FILES)) {
            mkdir(DIR_CASE_EDITOR_FILES, 0777, true);
        }
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        if (!is_dir($file_orig_path)) {
            mkdir($file_orig_path, 0777, true);
        }

        $name = '';
        $type = '';
        $newFileName = '';
        $message = 'success';
        $retarr = [];
        $user_id = SES_ID;
        $comp_id = SES_COMP;
        //remove file from comment while editing (read data from os and compare here)
        $allowedSize = MAX_FILE_SIZE * 1024;
        if ($filesArr) {
            foreach ($filesArr as $k => $v) {
                $name = $v['name'];
                $sizeinMb = $v['size'] / 1024;
                $sizeinkb = $v['size'];
                if ($sizeinMb <= $allowedSize) {
                    if ($name) {
                        $oldname = $name;
                        $ext1 = substr(strrchr($oldname, '.'), 1);
                        $message = $formatComponent->validateFileExt($ext1);
                        if ($message == 'success') {
                            $tot = strlen($oldname);
                            $extcnt = strlen($ext1);
                            $end = $tot - $extcnt - 1;
                            $onlyfile = substr($oldname, 0, $end);

                            $newFileName = $oldname;
                            //converting tif to png
                            !is_dir(WWW_ROOT . 'temp/') ? mkdir(WWW_ROOT . 'temp/', 0777, true) : '';
                            $file = $file_path . $newFileName;
                            $file_orig = $file_orig_path . $newFileName;
                            $fil_mob = fopen($file, 'w');
                            fwrite($fil_mob, base64_decode($v['content']));
                            fclose($fil_mob);
                            copy($file, $file_orig);
                            try {
                                // s3 bucket  start
                                $type = $v['type'];
                                $autogenId = FileUtility::generateUniqNumber();
                                $csEdtrFiles = [
                                    'user_id' => $user_id,
                                    'project_id' => $projId,
                                    'company_id' => $comp_id,
                                    'easycase_id' => $caseid,
                                    'name' => $newFileName,
                                    'file_size' => intval($sizeinkb),
                                    'uniq_id' => $autogenId,
                                ];
                                $csEdtrFilesEnt = $this->newEntity($csEdtrFiles);
                                if ($this->save($csEdtrFilesEnt)) {
                                    if (!empty(Configure::read('Storage'))) {
                                        $storageComponent = new StorageComponent(new ComponentRegistry());
                                        $folder_orig_Name = DIR_CASE_FILES_EDITOR_S3_FOLDER . trim($newFileName);
                                        $returnvalue = $storageComponent->uploadFile($file, $folder_orig_Name);
                                    } else {
                                        $casefile_name = DIR_CASE_EDITOR_FILES . $newFileName;
                                        $returnvalue = copy($file, $casefile_name);
                                        unlink($file_orig);
                                    }
                                    $retarr['images'][$k] = [
                                        'name' => $newFileName,
                                        'uniq_id' => $autogenId,
                                    ];
                                    if (!$returnvalue) {
                                        $retarr['images'][$k] += [
                                            'status' => 0,
                                            'msg' => __('You have error in uploading file to S3 bucket from the editor')
                                        ];
                                    } else {
                                        $retarr['images'][$k] += [
                                            'status' => 1,
                                            'msg' => ''
                                        ];
                                    }
                                } else {
                                    $retarr['error'][$k] = ['msg' => __('Not able to save in database')];
                                }
                            } catch (Exception $e) {
                            }
                        } else {
                            $retarr['error'][$k] = ['msg' => __('You have error in uploading file due to invalid file extension.')];
                        }
                    } else {
                        $retarr['error'][$k] = ['msg' => __('You have error in uploading file due to  invalid file name')];
                    }
                } else {
                    $retarr['error'][$k] = ['msg' => __('You have error in uploading file due to allowed upload size is exceeded.')];
                }
            }
        }
        return $retarr;
    }

}
