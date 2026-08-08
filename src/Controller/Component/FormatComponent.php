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

namespace App\Controller\Component;

use App\Model\Table\EasycasesTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\TypesTable;
use App\Model\Table\WorkHoursTable;
use App\Utility\CommonUtility;
use App\View\Helper\FormatHelper;
use Cake\Cache\Cache;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\SocketException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\View\View;
use DateInterval;
use DatePeriod;
use DateTime;
use EmailTemplating\Mailer\TemplatedMailer;
use Exception;
use RRule\RRule;

/**
 * @property \App\Controller\Component\TmzoneComponent $Tmzone
 * @property \App\Controller\Component\PostcaseComponent $Postcase
 * @property \App\Controller\Component\StorageComponent $Storage
 * Format component
 */
class FormatComponent extends Component
{
    use LocatorAwareTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];
    protected $components = ['Tmzone', 'Postcase', 'Storage'];

    protected $formatHelper = null;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->formatHelper = new FormatHelper(new View());
    }

    public function getUniqueDomainForOs($email)
    {
        return true;
    }

    public function SaveLoginSessionCURL($sessionUserID, $datetime)
    {
        return true;
    }

    public function SaveLoguotSessionCURL($sessionUserID, $datetime)
    {
        return true;
    }
    /**
     * convertCurrency
     * Convert one curreny to another as per the exchange rates
     *
     * @param  mixed $price amount to be converted
     * @param  mixed $from_cur currency code like USD
     * @param  mixed $to_cur currency code like INR
     * @return float
     */
    public function convertCurrency($price, $from_cur, $to_cur)
    {
        if (empty($price)) {
            return $price;
        }
        //base exchange rate is in USD
        $rates = [];
        $_from_cxh_rate = $rates[$from_cur] ?? 1;
        //if not found then it will consider it as USD
        $_to_cxh_rate = $rates[$to_cur] ?? 1;
        if ($from_cur == 'USD') {
            $convt_price = round($price * $_to_cxh_rate, 2);
        } else {
            $dolr_price = round((1 / $_from_cxh_rate) * $price, 2);
            $convt_price = round($dolr_price * $_to_cxh_rate, 2);
        }

        return $convt_price;
    }
    public function getHelpCatsCURL($id = 0, $type = 'cat')
    {
        return [];
    }
    public function formatTGMeta($val, $type)
    {
        if ($val instanceof FrozenTime || $val instanceof FrozenDate) {
            $val = $val->format('Y-m-d H:i:s');
        }
        if ($type == 'est') {
            if (!empty($val)) {
                if ($val > 1) {
                    //return $val.' '.__('hrs');
                    return $val;
                } else {
                    //return $val.' '.__('hr');
                    return $val;
                }
            }
            return __('--');
        } else {
            $invd_dts = ['0000-00-00', null, '1970-01-01'];
            if (in_array($val, $invd_dts)) {
                return __('--');
            } else {
                return date('M d, Y', strtotime($val));
            }
        }
    }
    public function getUserTags($uids)
    {
        $User = $this->fetchTable('Users');
        $uids = json_decode($uids);
        if (stristr($uids, ',')) {
            $uids = explode(',', $uids);
        }
        $usrDtls = $User->find()
            ->select(['name', 'id', 'photo', 'last_name'])
            ->where(['id IN' => $uids])
            ->disableHydration()
            ->toArray();

        //we will consider photo later
        if ($usrDtls) {
            $retStr = '';

            foreach ($usrDtls as $k => $v) {
                // pr($uid); exit;
                $retStr .= '<span class="dtl_label_tag padrt10">' . $v['name'] . ' ' . $v['last_name'] . '</span>';
            }

            return $retStr;
        } else {
            return __('NA');
        }
    }
    public function getUserTag($uids)
    {
        $User = $this->fetchTable('Users');
        $uids = json_decode($uids);
        if (stristr($uids, ',')) {
            $uids = explode(',', $uids);
        }
        $usrDtls = $User->find()
            ->select(['name', 'id', 'photo', 'last_name'])
            ->where(['id IN' => $uids])
            ->disableHydration()
            ->toArray();
        //we will consider photo later
        if ($usrDtls) {
            $retStr = '';
            foreach ($usrDtls as $k => $v) {
                $uid = $v['id'];
                $retStr .= '<li  class="filter_tag remove_user_tsk_rem_td">
                <div class="ellipsis">' . $v['name'] . ' ' . $v['last_name'] . '</div>
                <span id="' . $uid . '" class="cursor remove_user_tsk_rem"onclick="removeUserFromReminder(this);">&times;</span>
            </li>';
            }
            return $retStr;
        } else {
            return __('NA');
        }
    }

    public function generateUniqNumber()
    {
        return md5(Text::uuid());
    }

    public function genRandomStringCustom($length = 7)
    {
        $characters = '0123456789@$abcdefghijklmnopqrstuvwxyz';
        $string = '';
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
    }
    /**
     * checkCsrf
     *@ added by- Swetalina
     * @param  mixed $csrf_data
     * @return int
     */
    public function checkCsrf($csrf_data = null)
    {
        if ($csrf_data == ($_SESSION['CSRFTOKEN'] ?? null)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function genRandomString($length = 7)
    {
        $characters = '0123456789@$abcdefghijklmnopqrstuvwxyz';
        $characterCount = strlen($characters);
        $string = '';

        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[random_int(0, $characterCount - 1)];
        }

        return $string;
    }


    public function longstringwrap($string = '')
    {
        return $string;
        //return preg_replace_callback( '/\w{10,}/ ', create_function( '$matches', 'return chunk_split( $matches[0], 5, "&#8203;" );' ), $string );
    }

    public function getUserShortName($uid)
    {
        $Users = $this->fetchTable('Users');
        $query = $Users->find()
            ->select(['name', 'short_name', 'photo'])
            ->where(['id' => $uid])
            ->disableHydration();

        return $query->first();
    }

    public function getUserFullName($uid)
    {
        $Users = $this->fetchTable('Users');
        $query = $Users->find()
            ->select(['name', 'last_name', 'short_name', 'photo'])
            ->where(['id' => $uid])
            ->disableHydration();

        return $query->first();
    }

    public function getUserNameForEmail($uid)
    {
        $Users = $this->fetchTable('Users');
        $query = $Users->find()
            ->select(['name', 'email', 'id'])
            ->where(['id' => $uid, 'isactive' => 1])
            ->disableHydration();

        return $query->first();
    }


    public function getAllNotifyUser($project_id, $type = null)
    {
        $usersTable = $this->fetchTable('Users');
        $userNotiCond = ($type == 'new') ? ['UserNotification.new_case' => '1'] : ['UserNotification.reply_case' => '1'];
        $usrDtlsQuery = $usersTable->selectQuery()
            ->from(['User' => 'users'], true)
            ->select(['User.id', 'User.name', 'User.email'])
            ->join([
                'table' => 'user_notifications',
                'alias' => 'UserNotification',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('UserNotification.user_id', 'User.id')
            ])
            ->join([
                'alias' => 'ProjectUser',
                'table' => 'project_users',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id'), 'ProjectUser.default_email' => 1]
            ])
            ->join('CompanyUser', [
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('CompanyUser.user_id', 'User.id'), 'CompanyUser.is_active' => '1']
            ])
            ->where(['User.isactive' => '1'])
            ->where($userNotiCond)
            ->where(['ProjectUser.project_id' => $project_id])
            ->where(['CompanyUser.company_id' => SES_COMP]);

        $usrDtls = $usrDtlsQuery->toArray();

        return $usrDtls;
    }

    public function changeGanttData($json_arr)
    {
        //echo "<pre>";print_r($json_arr);
        $colors = [0 => '#73BCDE', 1 => '#8BC2B9', 2 => '#F8B363', 3 => '#EA7373', 4 => '#9ECC61'];
        foreach ($json_arr as $key => $value) {
            $json_arr[$key]['series'] = [];
            $json_arr[$key]['series'][0]['name'] = htmlspecialchars($value['title']);
            $json_arr[$key]['series'][0]['id'] = $value['id'];

            if ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);print $v['id'];echo "    1";exit;
                $json_arr[$key]['series'][0]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['series'][0]['end'] = $value['due_date'];
                $json_arr[$key]['series'][0]['color'] = $colors[$key];
            } elseif ((empty($value['gantt_start_date']) || is_null($value['gantt_start_date']) || $value['gantt_start_date'] == '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);echo "   2";exit;
                $json_arr[$key]['series'][0]['start'] = $value['due_date'];
                $json_arr[$key]['series'][0]['end'] = $value['due_date'];
                $json_arr[$key]['series'][0]['color'] = $colors[$key];
            } elseif ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] == '' || is_null($value['due_date']) || $value['due_date'] == '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);echo "   3";exit;
                $json_arr[$key]['series'][0]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['series'][0]['end'] = date('Y-m-d', $this->dateConvertion($value['gantt_start_date']));
                $json_arr[$key]['series'][0]['color'] = $colors[$key];
            } else {
                //print_r($v['gantt_start_date']);echo "   4";exit;
                $start = explode(' ', $value['actual_dt_created']);
                $json_arr[$key]['series'][0]['start'] = $start[0];
                $json_arr[$key]['series'][0]['end'] = date('Y-m-d', $this->dateConvertion($value['actual_dt_created']));
                $json_arr[$key]['series'][0]['color'] = $colors[$key];
            }
            if ($value['legend'] == '1') {
                $json_arr[$key]['series'][0]['color'] = '#F08E83';
            } elseif ($value['legend'] == '2' || $value['legend'] == '6') {
                $json_arr[$key]['series'][0]['color'] = '#6BA8DE';
            } elseif ($value['legend'] == '5') {
                $json_arr[$key]['series'][0]['color'] = '#72CA8D';
            } elseif ($value['legend'] == '3') {
                $json_arr[$key]['series'][0]['color'] = '#FAB858';
            } else {
                $json_arr[$key]['series'][0]['color'] = '#3dbb89';
            }
            unset($json_arr[$key]['title']);
            unset($json_arr[$key]['id']);
            unset($json_arr[$key]['legend']);
            unset($json_arr[$key]['gantt_start_date']);
            unset($json_arr[$key]['due_date']);
            unset($json_arr[$key]['actual_dt_created']);
        } //exit;
        #echo "<pre>";print_r($json_arr);exit;
        return $json_arr;
    }

    public function getTypes()
    {
        $Type = ClassRegistry::init('Type');
        $quickTyp = $Type->find('all', ['order' => ['Type.seq_order']]);

        return $quickTyp;
    }

    public function uploadPhoto($tmp_name, $name, $size, $path, $count, $type)
    {
        if ($name) {
            $inkb = $size / 1024;
            $oldname = strtolower($name);
            $ext = substr(strrchr($oldname, '.'), 1);
            if (($ext != 'gif') && ($ext != 'jpg') && ($ext != 'jpeg') && ($ext != 'png')) {
                return 'ext';
            }
            /* elseif($inkb > 1024) {
              return "size";
              } */ else {
                list($width, $height) = getimagesize($tmp_name);

                if ($width > 800) {
                    try {
                        $src = match ($ext) {
                            'png' => imagecreatefrompng($tmp_name),
                            'gif' => imagecreatefromgif($tmp_name),
                            'bmp' => imagecreatefromwbmp($tmp_name),
                            default => imagecreatefromjpeg($tmp_name),
                        };

                        $newwidth = 800;
                        $newheight = ($height / $width) * $newwidth;
                        $tmp = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        $newname = md5(time() . $count) . '.' . $ext;
                        $targetpath = "$path$newname";

                        imagejpeg($tmp, $targetpath, 100);
                        imagedestroy($src);
                        imagedestroy($tmp);
                        // s3 bucket  start
                        $s3 = new S3(awsAccessKey, awsSecretKey);
                        //$s3->putBucket(BUCKET_NAME, S3::ACL_PUBLIC_READ_WRITE);
                        $s3->putBucket(BUCKET_NAME, S3::ACL_PRIVATE);
                        $folder_orig_Name = ($type == 'profile_img') ? 'files/photos/' . trim($newname) : 'files/company/' . trim($newname);
                        //$s3->putObjectFile($tmp_name,BUCKET_NAME ,$folder_orig_Name ,S3::ACL_PUBLIC_READ_WRITE);
                        $s3->putObjectFile($targetpath, BUCKET_NAME, $folder_orig_Name, S3::ACL_PRIVATE);
                        //s3 bucket end
                        unlink($targetpath);
                    } catch (Exception $e) {
                        return false;
                    }
                } else {
                    $newname = md5(time() . $count) . '.' . $ext;
                    $targetpath = "$path$newname";
                    move_uploaded_file($tmp_name, $targetpath);
                    // s3 bucket  start
                    $s3 = new S3(awsAccessKey, awsSecretKey);
                    $s3->putBucket(BUCKET_NAME, S3::ACL_PRIVATE);
                    if ($type == 'profile_img') {
                        $folder_orig_Name = 'files/photos/' . trim($newname);
                    } else {
                        $folder_orig_Name = 'files/company/' . trim($newname);
                    }
                    //$folder_orig_Name = 'files/photos/'.trim($newname);
                    //$s3->putObjectFile($tmp_name,BUCKET_NAME ,$folder_orig_Name ,S3::ACL_PUBLIC_READ_WRITE);
                    $s3->putObjectFile($targetpath, BUCKET_NAME, $folder_orig_Name, S3::ACL_PRIVATE);
                    //s3 bucket end
                    unlink($targetpath);
                }

                if ($width < 200 || $height < 200) {
                    $im_P = 'convert ' . $targetpath . '  -background white -gravity center -extent 200x200 ' . $targetpath;
                    exec($im_P);
                }

                return $newname;
            }
        } else {
            return false;
        }
    }

    public function uploadProfilePhoto($name, $path)
    {
        if ($name) {
            $oldname = strtolower($name);
            $ext = substr(strrchr($oldname, '.'), 1);
            if (($ext != 'gif') && ($ext != 'jpg') && ($ext != 'jpeg') && ($ext != 'png') && ($ext != 'bmp')) {
                return 'ext';
            } else {
                $newname = $name;
                $is_storage = !empty(Configure::read('Storage'));
                if ($is_storage) {
                    $this->Storage->copyFile(DIR_USER_PHOTOS_THUMB . trim($newname), DIR_USER_PHOTOS_S3_FOLDER . $newname);
                }
                return $newname;
            }
        } else {
            return false;
        }
    }

    public function showuploadImage($tmp_name, $name, $size, $path, $count)
    {
        if ($name) {
            $image = strtolower($name);
            $extname = substr(strrchr($image, '.'), 1);
            $extname = strtolower($extname);
            if (($extname != 'gif') && ($extname != 'jpg') && ($extname != 'jpeg') && ($extname != 'png') && ($extname != 'bmp')) {
                return false;
            } else {
                // Suppressed warnings and check return value
                $imageInfo = @getimagesize($tmp_name);
                if ($imageInfo === false) {
                    Log::error('getimagesize failed for file: ' . $tmp_name);
                    return false;
                }
                list($width, $height) = $imageInfo;
                if (($width < 100 && $height < 100) || ($width < 100) || ($height < 100)) {
                    return 'small size image';
                } else {
                    if ($width > 200) {
                        try {
                            // Check if exif extension is available, fallback to mime type detection
                            if (function_exists('exif_imagetype')) {
                                $type = @exif_imagetype($tmp_name);
                            } else {
                                // Fallback: detect from extension
                                $type = match ($extname) {
                                    'gif' => 1,
                                    'jpg', 'jpeg' => 2,
                                    'png' => 3,
                                    'bmp' => 6,
                                    default => 2,
                                };
                            }
                            
                            if ($type === false) {
                                Log::error('exif_imagetype failed for file: ' . $tmp_name);
                                return false;
                            }
                            
                            switch ($type) {
                                case 1:
                                    $src = @imagecreatefromgif($tmp_name);
                                    break;
                                case 2:
                                    $src = @imagecreatefromjpeg($tmp_name);
                                    break;
                                case 3:
                                    $src = @imagecreatefrompng($tmp_name);
                                    break;
                                case 6:
                                    $src = @imagecreatefromwbmp($tmp_name);
                                    break;
                                default:
                                    $src = @imagecreatefromjpeg($tmp_name);
                                    break;
                            }
                            
                            if ($src === false) {
                                Log::error('Image creation from source failed for file: ' . $tmp_name);
                                return false;
                            }

                            $newwidth = 200;
                            $newheight = intval(($height / $width) * $newwidth);

                            $tmp = imagecreatetruecolor($newwidth, $newheight);

                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                            $time = time() . $count;
                            $filepath = md5($time) . '.' . $extname;
                            $targetpath = "$path$filepath";
                            $dir = dirname($targetpath);
                            if (!is_dir($dir)) {
                                @mkdir($dir, 0755, true);
                            }
                            if (!is_writable($dir)) {
                                return false;
                            }
                            $success = imagejpeg($tmp, $targetpath, 100);
                            imagedestroy($src);
                            imagedestroy($tmp);
                        } catch (Exception $e) {
                            return false;
                        }
                    } else {
                        $time = time() . $count;
                        $filepath = md5($time) . '.' . $extname;
                        $targetpath = "$path$filepath";
                        if (!is_dir($path)) {
                            @mkdir($path, 0755, true);
                        }
                        // Try native move first; if it fails (e.g. FrankenPHP temp streams), fall back to copy
                        $moved = false;
                        if (is_uploaded_file($tmp_name)) {
                            $moved = @move_uploaded_file($tmp_name, $targetpath);
                        }
                        if (!$moved) {
                            // fallback: try copy if the tmp file is readable
                            if (is_readable($tmp_name)) {
                                $copied = @copy($tmp_name, $targetpath);
                                if ($copied) {
                                    $moved = true;
                                }
                            }
                            // If still not moved, try stream copy (handles php://temp or other wrappers)
                            if (!$moved) {
                                $in = @fopen($tmp_name, 'r');
                                $out = @fopen($targetpath, 'w');
                                if ($in && $out) {
                                    if (@stream_copy_to_stream($in, $out) !== false) {
                                        $moved = true;
                                    }
                                }
                                if ($in) {
                                    @fclose($in);
                                }
                                if ($out) {
                                    @fclose($out);
                                }
                            }
                        }
                    }
                    if (file_exists($targetpath)) {
                        return $filepath;
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    public function caseKeywordSearch($caseSrch, $type)
    {
        $searchcase = '';
        $escape = ' ';
        if (trim(urldecode($caseSrch))) {
            // PostgreSQL-safe escaping for the single-quoted LIKE literals below;
            // addslashes() does not neutralise quotes when standard_conforming_strings is on.
            $srchstr1 = str_replace("'", "''", trim(urldecode($caseSrch)));
            if (substr($srchstr1, 0, 1) == '#') {
                $srchstr1 = substr($srchstr1, 1, strlen($srchstr1));
            }
            if (strpos($srchstr1, '\\') !== false) {
                $escape = " ESCAPE '~'";
            }
            if ($type == 'case_no_title') {
                $searchcase .= "AND ( concat(CONVERT(Easycase.title using utf8mb4),CONVERT(Easycase.case_no using utf8mb4)) LIKE '%$srchstr1%' $escape )";
            } elseif (preg_match('/[0-9]/', $srchstr1)) {
                $searchcase = "AND ( concat(CONVERT(Easycase.title using utf8mb4),CONVERT(Easycase.case_no using utf8mb4)) LIKE '%$srchstr1%' $escape )";
            } else {
                if (preg_match('[^A-Za-z -()@$&,]', $srchstr1) && !strstr($srchstr1, ' ') && !strstr($srchstr1, '-') && !strstr($srchstr1, ',') && !strstr($srchstr1, '/') && !strstr($srchstr1, '_') && !strstr($srchstr1, '_') && !strstr($srchstr1, ':') && !strstr($srchstr1, '.') && !strstr($srchstr1, '&')) {
                    $caseno = preg_replace('[^0-9]', '', $srchstr1);
                    $searchcase = "AND (Easycase.case_no LIKE '$caseno%' $escape OR Easycase.title LIKE '%$srchstr1%' $escape)";
                } else {
                    if (strstr($srchstr1, ' ') && $type == 'full') {
                        $searchcase = "AND ( concat(CONVERT(Easycase.title using utf8mb4),CONVERT(Easycase.message using utf8mb4)) LIKE '%$srchstr1%' $escape )";
                    } elseif ($type == 'half') {
                        $searchcase = "AND ( concat(CONVERT(Easycase.title using utf8mb4),CONVERT(Easycase.message using utf8mb4)) LIKE '%$srchstr1%' $escape )";
                    } elseif ($type == 'title') {
                        $searchcase = "AND Easycase.title LIKE '%$srchstr1%' $escape";
                    } else {
                        $searchcase .= "AND ( concat(CONVERT(Easycase.title using utf8mb4),CONVERT(Easycase.message using utf8mb4)) LIKE '%$srchstr1%' $escape )";
                    }
                }
            }
        }
        return $searchcase;
    }

    public function caseKeywordSearchArr($caseSrch, $type = null, $model = 'Easycase')
    {
        $searchcase = [];

        if (trim(urldecode($caseSrch))) {
            $srchstr1 = addslashes(trim(urldecode($caseSrch)));

            if (substr($srchstr1, 0, 1) == '#') {
                $srchstr1 = substr($srchstr1, 1, strlen($srchstr1));
            }

            if ($type == 'case_no_title') {
                $searchcase = [
                    'OR' => [
                        $model . '.title LIKE' => "%$srchstr1%",
                        $model . '.case_no LIKE' => "%$srchstr1%"
                    ]
                ];
            } elseif (preg_match('/[0-9]/', $srchstr1)) {
                $searchcase = [
                    'OR' => [
                        $model . '.title LIKE' => "%$srchstr1%",
                        $model . '.case_no LIKE' => "%$srchstr1%"
                    ]
                ];
            } else {
                if (preg_match('[^A-Za-z -()@$&,]', $srchstr1) && !strstr($srchstr1, ' ') && !strstr($srchstr1, '-') && !strstr($srchstr1, ',') && !strstr($srchstr1, '/') && !strstr($srchstr1, '_') && !strstr($srchstr1, '_') && !strstr($srchstr1, ':') && !strstr($srchstr1, '.') && !strstr($srchstr1, '&')) {
                    $caseno = preg_replace('[^0-9]', '', $srchstr1);
                    $searchcase = [
                        'OR' => [
                            $model . '.case_no LIKE' => "$caseno%",
                            $model . '.title LIKE' => "%$srchstr1%"
                        ]
                    ];
                } else {
                    if (strstr($srchstr1, ' ') && $type == 'full') {
                        $searchcase = [
                            'OR' => [
                                $model . '.title LIKE' => "%$srchstr1%",
                                $model . '.message LIKE' => "%$srchstr1%"
                            ]
                        ];
                    } elseif ($type == 'half') {
                        $searchcase = [
                            'OR' => [
                                $model . '.title LIKE' => "%$srchstr1%",
                                $model . '.message LIKE' => "%$srchstr1%"
                            ]
                        ];
                    } elseif ($type == 'title') {
                        $searchcase = [
                            $model . '.title LIKE' => "%$srchstr1%"
                        ];
                    } else {
                        $searchcase = [
                            'OR' => [
                                $model . '.title LIKE' => "%$srchstr1%",
                                $model . '.message LIKE' => "%$srchstr1%"
                            ]
                        ];
                    }
                }
            }
        }

        return $searchcase;
    }

    public function defectKeywordSearch($caseSrch, $type)
    {
        $searchcase = '';
        $escape = ' ';

        if (trim(urldecode($caseSrch))) {
            $srchstr1 = addslashes(trim(urldecode($caseSrch)));

            if (substr($srchstr1, 0, 1) == '#') {
                $srchstr1 = substr($srchstr1, 1, strlen($srchstr1));
            }

            if (strpos($srchstr1, '\\') !== false) {
                $escape = " ESCAPE '~'";
            }

            if (!preg_match('/[^0-9]/', $srchstr1)) {
                $searchcase = [
                    'OR' => [
                        'Easycases.title LIKE' => "%$srchstr1%",
                        'Easycases.case_no LIKE' => "$srchstr1%",
                        'Defects.title LIKE' => "%$srchstr1%",
                        'Defects.issue_no LIKE' => "$srchstr1%"
                    ]
                ];
            } else {
                if (preg_match('/[^A-Za-z -()@$&,]/', $srchstr1) && !strstr($srchstr1, ' ') && !strstr($srchstr1, '-') && !strstr($srchstr1, ',') && !strstr($srchstr1, '/') && !strstr($srchstr1, '_') && !strstr($srchstr1, '_') && !strstr($srchstr1, ':') && !strstr($srchstr1, '.') && !strstr($srchstr1, '&')) {
                    $projshortname = preg_replace('/[^A-Za-z]/', '', $srchstr1);
                    $caseno = preg_replace('/[^0-9]/', '', $srchstr1);

                    $searchcase = [
                        'OR' => [
                            'Easycases.title LIKE' => "%$srchstr1%",
                            'Easycases.case_no LIKE' => "$caseno%",
                            'Defects.title LIKE' => "%$srchstr1%",
                            'Defects.issue_no LIKE' => "$caseno%"
                        ]
                    ];
                } else {
                    $caseno = preg_replace('/[^0-9]/', '', $srchstr1);
                    $innerCondition = [
                        'Easycases.title LIKE' => "%$srchstr1%",
                        'Defects.title LIKE' => "%$srchstr1%"
                    ];
                    if (!empty(trim($caseno))) {
                        $innerCondition['Defects.issue_no LIKE'] = "$caseno%";
                    }
                    $searchcase = [
                        'OR' => $innerCondition
                    ];
                }
            }
        }

        return $searchcase;
    }

    public function statusFilter($caseStatus, $type = null, $no_brackt = 0, $model = 'Easycase')
    {
        $qry = '';
        if (!empty($caseStatus)) {
            $caseStatus = "$caseStatus-";
            $stsArr = explode('-', $caseStatus);
            $onlyDeflt = 0;
            $CstmStsArrLst = [];
            $customStatusTable = $this->fetchTable('CustomStatuses');
            $conditions = ['CustomStatuses.company_id' => SES_COMP];
            $query = $customStatusTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->enableHydration(false)
                ->where($conditions)
                ->order(['CustomStatuses.seq' => 'ASC']);
            $CstmStsArrLst = $query->toArray();

            foreach ($stsArr as $chksts) {
                if (trim($chksts)) {
                    if ($type && $type == 'work_load') {
                        if ($chksts == 2) {
                            $qry .= "$model.legend=2 OR $model.legend=4 OR ";
                        } else {
                            $qry .= "$model.legend=" . (int)$chksts . ' OR ';
                        }
                    } elseif ($chksts == 'attch' || $chksts == 'upd') {
                        if ($chksts == 'attch') {
                            $qry .= "$model.format=1 OR ";
                        }
                        if ($chksts == 'upd') {
                            $qry .= "$model.type_id=10 OR ";
                        }
                    } elseif ($chksts == 2) {
                        $onlyDeflt = 1;
                        $qry .= "$model.legend=2 OR $model.legend=4 OR ";
                    } else {
                        if (stristr($chksts, 'c')) {
                            $chksts_temp = substr($chksts, 1);
                            $chksts_temp = strval($chksts_temp);
                            if (trim($chksts_temp)) {
                                if (!empty($CstmStsArrLst)) {
                                    foreach ($CstmStsArrLst as $c_key => $c_val) {
                                        if (trim(strval($c_key)) == trim($chksts_temp)) {
                                            $qry .= "$model.custom_status_id =" . $c_key . ' OR ';
                                        }
                                    }
                                } else {
                                    $qry .= "$model.custom_status_id =" . (int)$chksts_temp . ' OR ';
                                }
                            }
                        } else {
                            $qry .= "$model.legend=" . (int)$chksts . ' OR ';
                            $onlyDeflt = 1;
                        }
                    }
                }
            }
            $qry = substr($qry, 0, -3);
            if ($onlyDeflt) {
                if ($no_brackt) {
                    $qry = '((' . trim($qry) . ") AND $model.custom_status_id=0)";
                } else {
                    $qry = ' AND ((' . trim($qry) . ") AND $model.custom_status_id=0)";
                }
            } else {
                if ($qry) {
                    if ($no_brackt) {
                        $qry = '(' . trim($qry) . ')';
                    } else {
                        $qry = ' AND (' . trim($qry) . ')';
                    }
                }
            }
        }
        return $qry;
    }

    public function customStatusFilter($caseCustomStatus, $type = null, $chk = 0, $no_brackt = 0, $model = null)
    {
        #print $caseCustomStatus.'---'.$type.'---'.$chk;exit;
        $CstmStsArrLst = [];
        if (strtolower(trim($type)) == 'all') {
            $customStatusTable = $this->fetchTable('CustomStatuses');
            $conditions = ['CustomStatuses.company_id' => SES_COMP];
            $query = $customStatusTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->enableHydration(false)
                ->where($conditions)
                ->order(['CustomStatuses.seq' => 'ASC']);
            $CstmStsArrLst = $query->toArray();
        }

        $qry = '';
        if (!empty($caseCustomStatus)) {
            $caseCustomStatus = $caseCustomStatus . '-';
            $stsArr = explode('-', $caseCustomStatus);

            foreach ($stsArr as $chksts) {
                if (trim($chksts)) {
                    if (!empty($CstmStsArrLst)) {
                        $sname = $CstmStsArrLst[$chksts];
                        foreach ($CstmStsArrLst as $c_key => $c_val) {
                            if (strtolower($sname) == strtolower($c_val)) {
                                // Check if model is passed, then prefix the column with model name (e.g., Easycases.custom_status_id)
                                $column = $model ? $model . '.custom_status_id' : 'custom_status_id';
                                $qry .= $column . ' =' . $c_key . ' OR ';
                            }
                        }
                    } else {
                        // Check if model is passed, then prefix the column with model name (e.g., Easycases.custom_status_id)
                        $column = $model ? $model . '.custom_status_id' : 'custom_status_id';
                        $qry .= $column . ' =' . (int)$chksts . ' OR ';
                    }
                }
            }
            $qry = substr($qry, 0, -3);

            if (strtolower(trim($type)) == 'all' && (trim($chk) && $chk != 'all')) {
                if ($qry) {
                    if ($no_brackt) {
                        $qry = '(' . trim($qry) . ')';
                    } else {
                        $qry = ' OR (' . trim($qry) . ')';
                    }
                }
            } else {
                if ($qry) {
                    if ($no_brackt) {
                        $qry = '(' . trim($qry) . ')';
                    } else {
                        $qry = ' AND (' . trim($qry) . ')';
                    }
                }
            }
        }
        return $qry;
    }

    public function typeFilter($caseTypes, $model = 'Easycase')
    {
        $qry = '';
        $qryTyp = '';
        if ($caseTypes != 'all') {
            if (strstr($caseTypes, '-')) {
                $typArr = explode('-', $caseTypes);
                foreach ($typArr as $typChk) {
                    $qryTyp .= "$model.type_id=" . (int)$typChk . " OR ";
                }
                $qryTyp = substr($qryTyp, 0, -3);
                $qry .= " AND ($qryTyp)";
            } else {
                $qry .= " AND $model.type_id=" . (int)$caseTypes;
            }
        }
        return $qry;
    }
    public function labelFilter($caseLabel, $curProjId, $comp_id, $ses_type, $ses_id)
    {
        $qry = '';
        $qryTyp = '';
        if (!empty($caseLabel) && $caseLabel != 'all') {
            $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
            $labelsTable = $this->fetchTable('Labels');

            $caseLabel = trim($caseLabel, '-');
            if (strstr($caseLabel, '-')) {
                $lblArr = explode('-', $caseLabel);
            } else {
                $lblArr = [$caseLabel];
            }
            $lblArr_new = $lblArr;
            foreach ($lblArr as $k => $v) {
                $Label_dtls = $labelsTable
                    ->find()
                    ->disableHydration()
                    ->select($labelsTable)
                    ->where([
                        'id' => $v
                    ])
                    ->first();
                $get_lbl_list = $labelsTable->find()
                    ->select($labelsTable)
                    ->disableHydration()
                    ->where([
                        'Labels.lbl_title' => $Label_dtls['lbl_title']
                    ])
                    ->toArray();
                foreach ($get_lbl_list as $k1 => $v1) {
                    if ($v1['id'] != $v) {
                        array_push($lblArr_new, $v1['id']);
                    }
                }
            }
            if (!$curProjId || $curProjId == 'all') {
                $projectUserTable = $this->fetchTable('ProjectUsers');
                $al_actvs = $projectUserTable->getAllActiveProject($ses_id, $comp_id, $ses_type);
                if ($al_actvs) {
                    $al_actvs = Hash::extract($al_actvs, '{n}.project_id');
                    $lblQuery = $easycaseLabelsTable->find()
                        ->select(['id', 'easycase_id'])
                        ->where([
                            'company_id' => $comp_id,
                            'project_id IN' => $al_actvs,
                            'label_id IN' => $lblArr_new
                        ])
                        ->disableHydration()
                        ->orderDesc('id');
                    $lbl_qry = $lblQuery->toArray();
                } else {
                    $lbl_qry = '';
                }
            } else {
                $lblQuery = $easycaseLabelsTable->find()
                    ->select(['id', 'easycase_id'])
                    ->where([
                        'company_id' => $comp_id,
                        'project_id' => $curProjId,
                        'label_id IN' => $lblArr_new
                    ])
                    ->disableHydration()
                    ->orderDesc('id');

                $lbl_qry = $lblQuery->toArray();
            }
            if (!empty($lbl_qry)) {
                $eids_lbl = Hash::extract($lbl_qry, '{n}.easycase_id');
                $qry = ' AND Easycase.id IN(' . implode(',', $eids_lbl) . ')';
            } else {
                $qry = ' AND Easycase.id =0';
            }
        }
        return $qry;
    }
    public function projectFilter($prjid, $type = null)
    {
        $qry = '';
        if ($prjid != 'all') {
            if (strstr($prjid, '-')) {
                $typArr = explode('-', $prjid);
                if (!empty($typArr)) {
                    $typ = implode(',', $typArr);
                    $qry .= 'AND Easycase.project_id IN (' . $typ . ')';
                }
            } else {
                $qry .= ' AND Easycase.project_id=' . $prjid;
            }
        }
        return $qry;
    }
    public function arcDateFiltxt($duedate)
    {
        if (!empty($duedate)) {
            $txt = match ($duedate) {
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'thisweek' => 'This Week',
                'thismonth' => 'This Month',
                'thisquarter' => 'This Quarter',
                'thisyear' => 'This Year',
                'lastyear' => 'Last Year',
                'lastweek' => 'Last Week',
                'lastmonth' => 'Last Month',
                'lastquarter' => 'Last Quarter',
                'last365days' => 'Last 365 Days',
                default => '',
            };
        }
        return $txt;
    }

    public function formatprjnm($prjid)
    {
        $projectTable = $this->fetchTable('Projects');
        $projectShortName = $projectTable
            ->find()
            ->select(['short_name'])
            ->where([
                'id' => $prjid,
                'company_id' => SES_COMP,
            ])->disableHydration()
            ->first();
        return $projectShortName['short_name'] ?? null;
    }

    public function arcUserFilter($usrid, $type = null)
    {
        $qry = '';
        $qryTyp = '';
        if (!empty($usrid) && $usrid != 'all') {
            if (strstr($usrid, '-')) {
                $typArr = explode('-', $usrid);
                foreach ($typArr as $typChk) {
                    if ($type == 'utilization') {
                        $qryTyp .= 'LogTime.user_id=' . $typChk . ' OR ';
                    } elseif ($type == 'invoice') {
                        $qryTyp .= 'LogTime.user_id=' . $typChk . ' OR ';
                    } elseif ($type == 'work_load') {
                        $qryTyp .= 'Easycase.assign_to=' . $typChk . ' OR ';
                    } elseif ($type == 'pending') {
                        $qryTyp .= ' Easycase.assign_to=' . $typChk . ' OR ';
                    } else {
                        $qryTyp .= 'Archive.user_id=' . $typChk . ' OR ';
                    }
                }
                $qryTyp = substr($qryTyp, 0, -3);
                if ($type != 'invoice') {
                    $qry .= ' AND (' . $qryTyp . ')';
                } else {
                    $qry .= ' (' . $qryTyp . ')';
                }
            } else {
                if ($type == 'utilization') {
                    $qry .= ' AND LogTime.user_id=' . $usrid;
                } elseif ($type == 'invoice') {
                    $qry .= 'LogTime.user_id=' . $usrid;
                } elseif ($type == 'work_load') {
                    $qry .= 'Easycase.assign_to=' . $usrid;
                } elseif ($type == 'pending') {
                    $qry .= ' AND Easycase.assign_to=' . $usrid;
                } else {
                    $qry .= ' AND Archive.user_id=' . $usrid;
                }
            }
        }
        return $qry;
    }
    public function arcLabelFilter($labelid, $type = null)
    {
        $qry = '';
        $qryTyp = '';
        if (!empty($labelid) && $labelid != 'all') {
            if (strstr($labelid, '-')) {
                $typArr = explode('-', $labelid);
                foreach ($typArr as $typChk) {
                    if ($type == 'utilization') {
                        $qryTyp .= ' EasycaseLabel.label_id=' . $typChk . ' OR ';
                    }
                }
                $qryTyp = substr($qryTyp, 0, -3);
                $qry .= ' AND (' . $qryTyp . ')';
            } else {
                $qry .= ' AND EasycaseLabel.label_id=' . $labelid;
            }
        }
        return $qry;
    }
    public function arcBillabilityFilter($billabilityid, $type = null)
    {
        $qry = '';
        $qryTyp = '';
        if (!empty($billabilityid) && $billabilityid != 'all') {
            if (strstr($billabilityid, '-')) {
                $typArr = explode('-', $billabilityid);
                foreach ($typArr as $typChk) {
                    if ($type == 'utilization') {
                        $typChk1 = ($typChk == 'billable') ? 1 : 0;
                        $qryTyp .= ' LogTime.is_billable=' . $typChk1 . ' OR ';
                    }
                }
                $qryTyp = substr($qryTyp, 0, -3);
                $qry .= ' AND (' . $qryTyp . ')';
            } else {
                $billabilityid = ($billabilityid == 'billable') ? 1 : 0;
                $qry .= ' AND LogTime.is_billable=' . $billabilityid;
            }
        }
        return $qry;
    }

    public function priorityFilter($priorityFil, $caseTypes)
    {
        $qry = '';

        if (!empty($priorityFil) && $priorityFil != 'all') {
            $qryPri = ' AND (';

            if (strstr($priorityFil, '-')) {
                $priArr = explode('-', $priorityFil);
                $priorityConditions = [];

                foreach ($priArr as $priChk) {
                    if ($priChk) {
                        switch ($priChk) {
                            case 'High':
                                $priorityConditions[] = "Easycase.priority = '" . EasycasesTable::PRIORITY_HIGH . "'";
                                break;
                            case 'Medium':
                                $priorityConditions[] = "Easycase.priority = '" . EasycasesTable::PRIORITY_MEDIUM . "'";
                                break;
                            default:
                                $priorityConditions[] = "Easycase.priority >= '" . EasycasesTable::PRIORITY_LOW . "'";
                        }
                    }
                }

                $qryPri .= implode(' OR ', $priorityConditions);
            } else {
                switch ($priorityFil) {
                    case 'High':
                        $qryPri .= "Easycase.priority = '" . EasycasesTable::PRIORITY_HIGH . "'";
                        break;
                    case 'Medium':
                        $qryPri .= "Easycase.priority = '" . EasycasesTable::PRIORITY_MEDIUM . "'";
                        break;
                    default:
                        $qryPri .= "Easycase.priority >= '" . EasycasesTable::PRIORITY_LOW . "'";
                }
            }

            $qryPri .= ')';

            if ($caseTypes != 10) {
                $qryPri .= ' AND Easycase.type_id != 10';
            }

            $qry .= $qryPri;
        }

        return $qry;
    }


    public function memberFilter($caseUserId)
    {
        $qry = '';
        $qryMem = '';
        if (!empty($caseUserId) && $caseUserId != 'all') {
            if (strstr($caseUserId, '-')) {
                $memArr = explode('-', $caseUserId);
                foreach ($memArr as $memChk) {
                    $qryMem .= 'Easycase.user_id=' . (int)$memChk . ' OR ';
                }
                $qryMem = substr($qryMem, 0, -3);
                $qry .= ' AND (' . $qryMem . ')';
            } else {
                $qry .= ' AND Easycase.user_id=' . (int)$caseUserId;
            }
        }
        return $qry;
    }

    public function commentFilter($caseUserId, $curProjId = null, $case_date = null)
    {
        $qry = $qry1 = '';
        $arr = [];
        $prj_ids = [];
        $upd_ids = [];
        if (!empty($caseUserId) && $caseUserId != 'all') {
            if (strstr($caseUserId, '-')) {
                $memArr = explode('-', $caseUserId);
                foreach ($memArr as $memChk) {
                    $arr[] = (int)$memChk;
                }
            } else {
                $arr[] = (int)$caseUserId;
            }
            /* date condition for comments */
            if ($case_date && trim($case_date) != '') {
                $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
                $now = new FrozenTime('now', $toTz);
                $ymdHisFormat = 'Y-m-d H:i:s';

                if (trim($case_date) == 'one') {
                    $threshold = (clone $now)->subHours(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == '24') {
                    $filterenabled = 1;
                    $threshold = (clone $now)->subDays(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'week') {
                    $filterenabled = 1;
                    $threshold = (clone $now)->subWeeks(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'month') {
                    $filterenabled = 1;
                    $threshold = (clone $now)->subMonths(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'year') {
                    $filterenabled = 1;
                    $threshold = (clone $now)->subYears(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (strstr(trim($case_date), '_')) {
                    $filterenabled = 1;
                    $ar_dt = explode('_', trim($case_date));
                    $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['0'])), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['1'])), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $qry1 .= " AND Easycase.dt_created >= '$from_d' AND Easycase.dt_created <= '$to_d'";
                }
            }

            if (count($arr) > 0) {
                $sql = "select DISTINCT Easycase.case_no from easycases as Easycase where Easycase.istype='2' and Easycase.isactive='1' AND Easycase.user_id IN(" . implode(',', $arr) . ") AND Easycase.project_id='" . (int)$curProjId . "' $qry1 AND Easycase.project_id!=0";
                $connection = ConnectionManager::get('default');
                $q = $connection->execute($sql)->fetchAll('assoc');

                $n_qry = '';

                if (count($q)) {
                    $s = [];
                    foreach ($q as $k => $v) {
                        $s[] = $v['case_no'];
                        if (isset($v['project_id'])) {
                            $prj_ids[] = $v['project_id'];
                            if ($n_qry == '') {
                                $n_qry = '(Easycase.case_no = ' . $v['case_no'] . ' AND Easycase.project_id=' . $v['project_id'] . ')';
                            } else {
                                $n_qry .= ' OR (Easycase.case_no = ' . $v['case_no'] . ' AND Easycase.project_id=' . $v['project_id'] . ')';
                            }
                            if (!empty($v['updated_by'])) {
                                $upd_ids[] = $v['updated_by'];
                            }
                        }
                    }
                    $qry = ' AND Easycase.case_no IN(' . implode(',', $s) . ')';
                } else {
                    $qry = ' AND Easycase.case_no IN(0)';
                }
            }
        }
        return $qry;
    }

    public function caseTaskGroupFilter($caseTgId, $curProjId = null, $case_date = null)
    {
        $qry = $this->taskgroupFilter($caseTgId);
        if ($qry == '' || $curProjId == 'all') {
            return $qry;
        }
        $final_qry = '';
        $sql = 'SELECT
								Easycase.id,
								EasycaseMilestone.id,
								EasycaseMilestone.milestone_id
							FROM easycases Easycase
							LEFT JOIN easycase_milestones EasycaseMilestone ON Easycase.id=EasycaseMilestone.easycase_id
							WHERE Easycase.project_id=' . $curProjId . $qry;
        $connection = ConnectionManager::get('default');
        $res = $connection->execute($sql)->fetchAll('assoc');

        if (count($res)) {
            $task_ids = array_filter(Hash::extract($res, '{n}.id'), function ($id) {
                return !is_null($id);
            });
            if (!empty($task_ids)) {
                $final_qry = ' AND Easycase.id IN(' . implode(',', $task_ids) . ') ';
            } else {
                $final_qry = ' AND Easycase.id IN(0) '; // No valid IDs found
            }
        }

        return $final_qry;
    }
    public function taskgroupFilter($caseTaskgroup)
    {
        $qry = '';
        $qrygroup = '';
        if (!empty($caseTaskgroup) && $caseTaskgroup != 'all') {
            if (strstr($caseTaskgroup, '-')) {
                $groupArr = explode('-', $caseTaskgroup);
                foreach ($groupArr as $groupChk) {
                    if ($groupChk == 'default') {
                        $qrygroup .= 'EasycaseMilestone.milestone_id IS NULL ' . ' OR ';
                    } else {
                        $qrygroup .= 'EasycaseMilestone.milestone_id=' . $groupChk . ' OR ';
                    }
                }
                $qrygroup = substr($qrygroup, 0, -3);
                $qry .= ' AND (' . $qrygroup . ')';
            } else {
                if (strtolower($caseTaskgroup) == 'default') {
                    $qry .= ' AND EasycaseMilestone.milestone_id IS NULL ';
                } else {
                    $qry .= ' AND EasycaseMilestone.milestone_id=' . $caseTaskgroup;
                }
            }
        }
        return $qry;
    }
    public function assigntoFilter($caseAssignTo)
    {
        $qry = '';
        $qryAsn = '';
        if (!empty($caseAssignTo) && $caseAssignTo != 'all') {
            if (strstr($caseAssignTo, '-')) {
                $asnArr = explode('-', $caseAssignTo);
                foreach ($asnArr as $asnChk) {
                    $qryAsn .= 'Easycase.assign_to=' . (int)$asnChk . ' OR ';
                }
                $qryAsn = substr($qryAsn, 0, -3);
                $qry .= ' AND (' . $qryAsn . ')';
            } else {
                if (strtolower($caseAssignTo) == 'unassigned') {
                    $caseAssignTo = 0;
                }
                $qry .= ' AND Easycase.assign_to=' . (int)$caseAssignTo;
            }
        }
        return $qry;
    }

    public function filterMilestone($milestoneUid = '')
    {
        if ($milestoneUid) {
            $mlst_cls = ClassRegistry::init('Milestone');
            $mlist = $mlst_cls->find('first', ['conditions' => ['Milestone.uniq_id' => $milestoneUid], 'fields' => 'Milestone.id,Milestone.title']);
            return ' AND EasycaseMilestone.milestone_id=' . $mlist['Milestone']['id'];
        } else {
            return '';
        }
    }

    public function find_file($dirname, $fname, &$file_path)
    {
        if (file_exists($dirname . $fname)) {
            return $dirname . $fname;
        } else {
            return false;
        }
    }

    public function emailBodyFilter($value)
    {
        $pattern = ["/\n/", "/\r/", '/content-type:/i', '/to:/i', '/from:/i', '/cc:/i'];
        $value = preg_replace($pattern, '', $value);
        return $value;
    }

    public function validateEmail($email)
    {
        $at = strrpos($email, '@');
        if ($at && ($at < 1 || ($at + 1) == strlen($email))) {
            return false;
        }
        if (preg_match("/(\.{2,})/", $email)) {
            return false;
        }
        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);
        $locLen = strlen($local);
        $domLen = strlen($domain);
        if ($locLen < 1 || $locLen > 64 || $domLen < 4 || $domLen > 255) {
            return false;
        }
        if (preg_match("/(^\.|\.$)/", $local) || preg_match("/(^\.|\.$)/", $domain)) {
            return false;
        }
        if (!preg_match('/^"(.+)"$/', $local)) {
            if (!preg_match('/^[-a-zA-Z0-9!#$%*\/?|^{}~&\'+=_\.]*$/', $local)) {
                return false;
            }
        }
        if (!preg_match("/^[-a-zA-Z0-9\.]*$/", $domain) || !strpos($domain, '.')) {
            return false;
        }
        return true;
    }

    public function generatePassword($length)
    {
        $vowels = 'aeuy';
        $consonants = '3@Z6!29G7#$QW4';
        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    /**
     * Downloads a file from the server or Amazon S3 storage.
     *
     * @param string $filename The name of the file to download.
     * @param string|null $chk Additional parameter, not used in the provided code.
     * @param int $is_editor Flag indicating whether the file is from the editor folder.
     * @return \Cake\Http\Response The response object with the file content.
     */
    public function downloadFile($filename, $chk = null, $is_editor = 0)
    {
        $response = new Response();
        if (!isset($filename) || empty($filename)) {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 14px verdana;color:#FF0000;' align='center'>Please specify a file name for download.</td></tr></table>";
            return $response->withStringBody($var);
        }

        if (!empty(Configure::read('Storage'))) {
            try {
                $info = $this->Storage->headObject(($is_editor ? DIR_CASE_FILES_EDITOR_S3_FOLDER : DIR_CASE_FILES_S3_FOLDER) . $filename);
            } catch (Exception $th) {
                $info = null;
            }
        } elseif (file_exists(DIR_CASE_EDITOR_FILES . $filename)) {
            $info2 = 1;
        } elseif (file_exists(DIR_CASE_FILES . $filename)) {
            $info = 1;
        } elseif (file_exists(HTTP_DEFECT_ROOT_FILES . $filename)) {
            $info1 = 1;
        }

        if (isset($info) && $info) {
            $file_path = (!empty(Configure::read('Storage'))) ? $this->Storage->generateTemporaryURL(($is_editor ? DIR_CASE_FILES_EDITOR_S3_FOLDER : DIR_CASE_FILES_S3_FOLDER) . $filename) : DIR_CASE_FILES . $filename;
        } elseif (isset($info1) && $info1) {
            $file_path = (!empty(Configure::read('Storage'))) ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $filename) : HTTP_DEFECT_ROOT_FILES . $filename;
        } elseif (isset($info2) && $info2) {
            $file_path = (!empty(Configure::read('Storage'))) ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_EDITOR_S3_FOLDER . $filename) : DIR_CASE_EDITOR_FILES . $filename;
        } else {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 12px verdana;color:#FF0000;' align='center'>Oops! File not found.<br/> File may be deleted or make sure you specified correct file name.</td></tr></table>";
            return $response->withStringBody($var);
        }

        $filename = !empty($chk) ? str_ireplace(' ', '', (string) $chk) : $filename;
        return $this->getDownloadFile($file_path, $filename);
    }

    /**
     * Downloads a file from the server or Amazon S3 storage.
     *
     * @param string $filename The name of the file to download.
     * @param string|null $chk Additional parameter, not used in the provided code.
     * @param int $is_editor Flag indicating whether the file is from the editor folder.
     * @return \Cake\Http\Response The response object with the file content.
     */
    public function downloadTMpFile($filename)
    {
        set_time_limit(0);

        $response = new Response();
        if (!isset($filename) || empty($filename)) {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 14px verdana;color:#FF0000;' align='center'>Please specify a file name for download.</td></tr></table>";
            return $response->withStringBody($var);
        }

        if (!empty(Configure::read('Storage'))) {
            try {
                $info = $this->Storage->headObject(DIR_CASE_FILES_S3_FOLDER_TEMP . $filename);
            } catch (Exception $th) {
                $info = null;
            }
        } elseif (file_exists(DIR_CASE_FILES . 'temp/' . $filename)) {
            $info = 1;
        }
        if ($info) {
            $file_path = (!empty(Configure::read('Storage'))) ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER_TEMP . $filename) : DIR_CASE_FILES . 'temp/' . $filename;
        } else {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 12px verdana;color:#FF0000;' align='center'>Oops! File not found.<br/> File may be deleted or make sure you specified correct file name.</td></tr></table>";
            return $response->withStringBody($var);
        }

        return $this->getDownloadFile($file_path, $filename);
    }

    /**
     * Generates a response to facilitate file download.
     *
     * @param string $file_path The full path to the file on the server.
     * @param string $filename The name of the file to be presented to the user for download.
     * @param \Cake\Http\Response|null $response An optional response object to modify. If null, a new response object will be created.
     * @return \Cake\Http\Response The response object configured for file download.
     *
     * @throws \RuntimeException If the file cannot be read or does not exist.
     *
     * This method determines the MIME type of the file based on its extension
     * and sets appropriate headers for the response to prompt the user to download the file.
     * If the file extension is not recognized, a default MIME type of "application/force-download" is used.
     */
    public function getDownloadFile($file_path, $filename, $response = null)
    {
        $response = $response ?? new Response();

        /* Figure out the MIME type | Check in array */
        $known_mime_types = [
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'exe' => 'application/octet-stream',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpeg' => 'image/jpg',
            'jpg' => 'image/jpg',
            'php' => 'text/plain'
        ];
        $file_extension = strtolower(substr(strrchr($filename, '.'), 1));
        $mime_type = array_key_exists($file_extension, $known_mime_types) ? $known_mime_types[$file_extension] : 'application/force-download';

        $response = $response
            ->withType($mime_type)
            ->withHeader('Content-Disposition', 'attachment;filename=' . $filename)
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
        $response->getBody()->rewind();
        $response->getBody()->write(file_get_contents($file_path));

        return $response;
    }

    public function getReplacedStrng($str = '')
    {
        $str ??= '';
        if (trim($str) != '') {
            return str_ireplace("''", "'", str_ireplace('"', "'", html_entity_decode(trim($str), ENT_QUOTES, 'UTF-8')));
        }
        return trim($str);
    }
    public function stripHtml($str = '')
    {
        $str ??= '';
        if (trim($str) != '') {
            return htmlspecialchars_decode(strip_tags($str));
        }
        return trim($str);
    }
    public function downloadFile1($filename)
    {
        set_time_limit(0);
        if (!isset($filename) || empty($filename)) {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 14px verdana;color:#FF0000;' align='center'>Please specify a file name for download.</td></tr></table>";
            die($var);
        }

        if (strpos($filename, "\0") !== false) {
            die('');
        }
        $fname = basename($filename);

        if (file_exists(DIR_CASE_FILES . $fname)) {
            $file_path = DIR_CASE_FILES . $fname;
        } else {
            $var = "<table align='center' width='100%'><tr><td style='font:bold 12px verdana;color:#FF0000;' align='center'>Oops! File not found.<br/> File may be deleted or make sure you specified correct file name.</td></tr></table>";
            die($var);
        }
        $fsize = filesize($file_path);

        $fext = strtolower(substr(strrchr($fname, '.'), 1));

        if (!isset($_GET['fc']) || empty($_GET['fc'])) {
            $asfname = $fname;
        } else {
            $asfname = str_replace(['"', "'", '\\', '/'], '', $_GET['fc']);
            if ($asfname === '') {
                $asfname = 'NoName';
            }
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-Type: ');
        header("Content-Disposition: attachment; filename=\"$asfname\"");
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $fsize);

        $file = @fopen($file_path, 'rb');
        if ($file) {
            while (!feof($file)) {
                print(fread($file, 1024 * 8));
                flush();
                if (connection_status() != 0) {
                    @fclose($file);
                    die();
                }
            }
            @fclose($file);
        }
    }

    public function chnageUploadedFileName($filename)
    {
        // Allowlist filename characters. Anything outside [A-Za-z0-9._-] — every
        // shell metacharacter (backtick, newline, <, >, *, \, quotes, $, ;, |,
        // spaces) plus non-ASCII bytes — is replaced with '_'. This name is
        // interpolated into the ImageMagick exec() calls in the upload handlers,
        // so restricting it here is what makes those calls safe. Collapsing '..'
        // also removes path-traversal via the filename.
        $output = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$filename);
        $output = str_replace('..', '_', $output);

        return $output === '' ? '_' : $output;
    }

    public function validateFileExt($ext, $typChk = 0)
    {
        // Blocked upload extensions. Every server-executable / scriptable type
        // is listed, including all PHP handler variants (php, phtml, phar, pht,
        // php3-8, phps) that stock mod_php maps to the interpreter, plus svg/html
        // (which execute JS in the app origin when served from webroot/files).
        // The webroot/files/.htaccess execution guard is the definitive backstop.
        $extList = [
            'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phtml', 'phtm', 'pht', 'phar', 'phps', 'phps5', 'inc',
            'htaccess', 'htpasswd', 'cgi', 'pl', 'py', 'pyc', 'rb', 'sh', 'bash', 'ksh', 'zsh',
            'asp', 'aspx', 'ashx', 'asmx', 'jsp', 'jspx', 'jhtml', 'cfm', 'cfml',
            'htm', 'html', 'xhtml', 'shtml', 'shtm', 'svg', 'svgz', 'xml', 'xht',
            'bat', 'com', 'cpl', 'dll', 'exe', 'msi', 'msp', 'pif', 'shs', 'sys', 'reg', 'bin', 'torrent', 'yps', 'mpg', 'dat', 'xvid', 'scr', 'chm', 'cmd', 'crt', 'hlp', 'hta', 'inf', 'ins', 'isp', 'jse', 'js', 'jar', 'war', 'lnk', 'mdb', 'ms', 'pcd', 'sct', 'vb', 'vbe', 'ws', 'wsf', 'wsh', 'vbs',
        ];

        $onlyIngExt = ['jpeg', 'jpg', 'png'];

        $ext = strtolower($ext);
        if ($typChk) {
            if (in_array($ext, $onlyIngExt)) {
                return 'success';
            } else {
                return '.' . $ext;
            }
        } elseif (!in_array($ext, $extList)) {
            return 'success';
        } else {
            return '.' . $ext;
        }
    }

    public function todo_typ($type, $title)
    {
        $disp_type = '<img src="' . HTTP_IMAGES . 'images/types/' . $type . '.png" title="' . $title . '" alt="' . $type . '" rel="tooltip"/>';
        return $disp_type;
    }

    public function formatText($value, $type = null)
    {
        $value = str_replace('�', '"', $value);
        $value = str_replace('�', '"', $value);
        if (!$type) {
            $value = preg_replace('/[^(\x20-\x7F)\x0A]*/', '', $value);
        }
        $value = stripslashes($value);
        $value = html_entity_decode($value, ENT_QUOTES);
        $trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
        $value = strtr($value, $trans);
        $value = stripslashes(trim($value));
        return $value;
    }

    public function chgdate($val)
    {
        $dt = explode('/', $val);
        $dateformat = $dt['2'] . '-' . $dt['0'] . '-' . $dt['1'];
        return $dateformat;
    }

    public function dateFormatReverse($output_date)
    {
        if ($output_date != '') {
            if (strstr($output_date, ' ')) {
                $exp = explode(' ', $output_date);
                $od = $exp[0];
                $date_ex2 = explode('-', $od);
                $dateformated_input = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0] . ' ' . $exp[1];
            } else {
                $exp = explode('-', $output_date);
                $dateformated_input = $exp[1] . '/' . $exp[2] . '/' . $exp[0];
            }
            return $dateformated_input;
        }
    }

    public function makeSeoUrl($url, $type = 0)
    {
        if ($url) {
            $url = trim(strtolower($url));
            $url = str_replace(' ', '', $url);
            $value = preg_replace('/[^A-Za-z0-9\-]/', '', $url);
            $url = trim($value);
        }
        if ($type && strlen($url) > 20) {
            $url = substr($url, 0, 20);
        }
        return $url;
    }

    public function makeShortName($first, $last)
    {
        $firstWords = explode(' ', $first);
        $let1 = substr($firstWords[0], 0, 1);
        $let2 = isset($firstWords[1]) ? substr($firstWords[1], 0, 1) : '';
        $let3 = substr($last, 0, 1);

        return strtoupper($let1 . $let2 . $let3);
    }


    public function makeShortName_old($first, $last)
    {
        if (stristr($first, ' ')) {
            $firstexp = explode(' ', $first);
            $let1 = substr($firstexp[0], 0, 1);
            $let2 = substr($firstexp[1], 0, 1);
        } else {
            $let1 = substr($first, 0, 2);
        }
        $let3 = substr($last, 0, 1);

        return strtoupper($let1 . $let2 . $let3);
    }

    public function displayStatus($st)
    {
        if ($st == 1) {
            $status = 'New';
        } elseif ($st == 2) {
            $status = 'In Progress';
        } elseif ($st == 3) {
            $status = 'Closed';
        } elseif ($st == 4) {
            $status = 'Started';
        } elseif ($st == 5) {
            $status = 'Resolved';
        } elseif ($st == 'hctta') {
            $status = 'Files';
        } elseif ($st == 'dpu') {
            $status = 'Updates';
        } elseif (stristr($st, 'c')) {
            $status = $this->displayCustomStatus(substr($st, 1));
        } elseif ($st >= 20) {
            $status = $this->displayCustomStatus($st);
        } else {
            $status = 'All';
        }
        return $status;
    }

    public function displayCustomStatus($customStatusId)
    {
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $customStatus = $customStatusesTable->find()
            ->where(['id' => $customStatusId])
            ->select(['name'])
            ->disableHydration()
            ->first();
        return $customStatus['name'] ?? 'All';
    }
    public function caseBcMems($uid)
    {
        $User = ClassRegistry::init('User');
        $User->recursive = -1;
        $usrDtls = $User->find('first', ['conditions' => ['User.id' => $uid, 'User.isactive' => 1], 'fields' => ['User.short_name']]);
        return $usrDtls['User']['short_name'];
    }

    public function caseMemsList($uid)
    {
        $User = $this->fetchTable('Users');
        $usrDtls = $User->find('list', [
            'keyField' => 'id',
            'valueField' => 'short_name'
        ])
            ->where(['id' . (is_array($uid) ? ' IN' : '') => $uid, 'isactive' => 1])->toArray();

        // When called with an array of IDs (e.g. from foreach callers), always return an array.
        // When called with a scalar ID (e.g. direct HTML embed), return the name string.
        if (is_array($uid)) {
            return $usrDtls;
        }

        return array_values($usrDtls)[0] ?? '';
    }
    public function caseGroupsList($uid)
    {
        if ($uid == 'default') {
            return ['default' => __('Default Task Group')];
        } else {
            $Milestone = $this->fetchTable('Milestones');
            $mileDtls = $Milestone->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->where(['id' . (is_array($uid) ? ' IN' : '') => $uid])->toArray();
            return $mileDtls;
        }
    }

    public function caseLabelList($uid)
    {
        $labelTable = $this->fetchTable('Labels');
        $labelDtls = $labelTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'lbl_title',
            'conditions' => ['Labels.id' => $uid, 'Labels.is_active' => 1],
        ])->disableHydration()->toArray();

        if (count($labelDtls) == 1) {
            $labellist = array_values($labelDtls);
            return $labellist[0];
        } else {
            return $labelDtls;
        }
    }

    public function caseBillabilityList($uid)
    {
        return ($uid == 'billable') ? 'Billable' : 'Unbillable';
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

    public function caseBcLabels($lbl)
    {
        $Label = ClassRegistry::init('Label');
        $csLabel = $Label->find('first', ['conditions' => ['Label.id' => $lbl], 'fields' => ['Label.lbl_title']]);
        return $csLabel['Label']['lbl_title'];
    }
    public function fullSpace($used, $totalsize = 1024)
    {
        $full = $used > 0 ? $used * 100 / $totalsize : 0;
        $used = round($full, 1);
        return $used;
    }

    public function usedSpace($curProjId = null, $company_id = SES_COMP, $typeChk = 0)
    {
        $CaseFiles = ClassRegistry::init('CaseFiles');
        $this->recursive = -1;
        $cond = ' 1 ';
        $cond_n = ' 1 ';
        if ($company_id) {
            $cond .= ' AND CaseFile.company_id=' . $company_id;
            $cond_n .= ' AND CaseEditorFile.company_id=' . $company_id;
        }
        if ($curProjId) {
            $cond .= ' AND CaseFile.project_id=' . $curProjId;
            $cond_n .= ' AND CaseEditorFile.project_id=' . $curProjId;
        }
        $sql = 'SELECT SUM(file_size) AS file_size  FROM case_files AS CaseFile  WHERE ' . $cond;
        $res1 = $CaseFiles->query($sql);
        $filesize = $res1['0']['0']['file_size'] / 1024;

        $CaseEditorFile = ClassRegistry::init('CaseEditorFile');
        $CaseEditorFile->recursive = -1;
        $sql_n = 'SELECT SUM(file_size) AS file_size FROM case_editor_files as CaseEditorFile WHERE ' . $cond_n;
        $res_n = $CaseEditorFile->query($sql_n);
        $filesize_n = $res_n['0']['0']['file_size'] / 1024;

        $tot_size = $filesize_n + $filesize;
        if (empty($tot_size)) {
            return '0.00';
        }
        /*if (empty($res1)) {
            return '0.00';
        }	*/
        if ($typeChk) {
            return round($tot_size, 2);
        } else {
            return number_format($tot_size, 2);
        }
    }

    public function shortLength($value, $len, $wrap = true)
    {
        $value_format = $this->formatText($value, 1);
        $value_raw = html_entity_decode($value_format, ENT_QUOTES);
        if (strlen($value_raw) > $len) {
            //$value_strip = substr($value_raw, 0, $len);
            $value_strip = mb_substr($value_raw, 0, $len, 'utf-8');
            $value_strip = $this->formatText($value_strip, 1);
            if ($wrap) {
                $lengthvalue = "<span title='" . $value_format . "' >" . $value_strip . '...</span>';
            } else {
                $lengthvalue = $value_strip . '...';
            }
        } else {
            $lengthvalue = $value_format;
        }
        return $lengthvalue;
    }

    public function getAllCsId($pid)
    {
        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;
        $caseIds = $Easycase->find('all', ['conditions' => ['Easycase.project_id' => $pid], 'fields' => 'id']);
        $ids = [];
        foreach ($caseIds as $csid) {
            array_push($ids, $csid['Easycase']['id']);
        }
        return $ids;
    }


    public function getProjectName($pid)
    {
        $projectsTable = $this->fetchTable('Projects');
        $project = $projectsTable->find()
            ->select(['name'])
            ->where(['id' => $pid, 'isactive' => 1, 'company_id' => SES_COMP])
            ->disableHydration()
            ->first();

        return $project['name'] ?? '';
    }

    public function getProjectShortName($pid)
    {
        $shortName = '';
        $Project = ClassRegistry::init('Project');
        $Project->recursive = -1;
        $pjArr = $Project->find('first', ['conditions' => ['Project.id' => $pid, 'Project.isactive' => 1, 'Project.company_id' => SES_COMP], 'fields' => ['Project.short_name']]);
        return $pjArr['Project']['short_name'];
        //return $pjArr;
    }

    public function getRequireUserName($UserId = null, $is_email = null)
    {
        $User = $this->fetchTable('Users');
        $query = $User->find()
            ->select(['name', 'last_name', 'email'])
            ->where(['id' => $UserId])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if ($query) {
            $fullname = $query['name'] . ' ' . $query['last_name'];
            if (isset($is_email)) {
                $fullname = $query['email'];
            }

            return $fullname;
        }

        return null;
    }


    public function getRequireTypeName($typeId = null)
    {
        if ($typeId === null) {
            return null; // Or handle the null case as per your needs
        }

        $typeTable = $this->fetchTable('Types');

        $type = $typeTable->find()
            ->select(['name'])
            ->where(['id' => $typeId])
            ->disableHydration()
            ->first();

        // Check if the result is not null
        if ($type) {
            return $type['name'];
        }

        return null;
    }

    public function getRequireMilestoneName($MId = null)
    {
        $Mlstn = ClassRegistry::init('Milestone');
        $Mlstn->recursive = -1;
        $mlstnDtls = $Mlstn->query("SELECT title FROM milestones WHERE id='" . $MId . "'");
        $mlstnname = $mlstnDtls[0]['milestones']['title'];
        return $mlstnname;
    }

    public function dateFormatOutputdateTime_day_EXPORT($date_time, $curdate = null, $type = null)
    {
        if ($date_time != '') {
            $date_time = date('Y-m-d H:i:s', strtotime($date_time));
            $output = explode(' ', $date_time);
            $date_ex2 = explode('-', $output[0]);

            $dateformated = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                $displayWeek = 0;
                $timeformat = date('g:i a', strtotime($date_time));

                $week1 = date('l', mktime(0, 0, 0, $date_ex2[1], $date_ex2[2], $date_ex2[0]));
                $week_sub1 = substr($week1, '0', '3');

                $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));

                if ($dateformated == $this->dateFormatReverse($curdate)) {
                    $dateTime_Format = 'Today';
                } elseif ($dateformated == $this->dateFormatReverse($yesterday)) {
                    $dateTime_Format = "Y'day";
                } else {
                    $CurYr = date('Y', strtotime($curdate));
                    $DateYr = date('Y', strtotime($dateformated));
                    if ($CurYr == $DateYr) {
                        $dateformated = date('m/d', strtotime($dateformated));
                        $displayWeek = 1;
                    } else {
                        $dateformated = date('M d Y', strtotime($dateformated));
                    }
                    $dateTime_Format = $dateformated;
                }

                if ($type == 'date') {
                    return $dateTime_Format;
                } elseif ($type == 'time') {
                    return $dateTime_Format . ' ' . $timeformat;
                } elseif ($type == 'week') {
                    if ($dateTime_Format == 'Today' || $dateTime_Format == "Y'day" || !$displayWeek) {
                        return $dateTime_Format;
                    } else {
                        return $dateTime_Format . ', ' . date('D', strtotime($dateformated));
                    }
                } else {
                    if ($dateTime_Format == 'Today' || $dateTime_Format == "Y'day") {
                        return $dateTime_Format . ' ' . $timeformat;
                    } else {
                        return $dateTime_Format . ', ' . date('D', strtotime($dateformated)) . ' ' . $timeformat;
                    }
                }
            }
        }
    }

    public function mdyFormat($date_time, $type = null)
    {
        if ($date_time != '') {
            $date_time = date('Y-m-d H:i:s', strtotime($date_time));
            $output = explode(' ', $date_time);
            $date_ex2 = explode('-', $output[0]);

            $dateformated = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                $timeformat = date('g:i a', strtotime($date_time));
                $dateformated = date('m/d/Y', strtotime($dateformated));
                $dateTime_Format = $dateformated;

                if ($type == 'time') {
                    return $dateTime_Format . ' ' . $timeformat;
                } else {
                    return $dateTime_Format;
                }
            }
        }
    }

    public function checkEmailExists($betaEmail)
    {
        $BetaUser = ClassRegistry::init('BetaUser');
        $BetaUser->recursive = -1;

        $findUserEmail = $BetaUser->find('first', ['conditions' => ['BetaUser.email' => $betaEmail], 'fields' => ['BetaUser.id', 'BetaUser.is_approve']]);

        $id = $findUserEmail['BetaUser']['id'];
        $is_approve = $findUserEmail['BetaUser']['is_approve'];

        if ($id) {
            $User = ClassRegistry::init('User');
            $User->recursive = -1;
            $findUser = $User->find('count', ['conditions' => ['User.email' => $betaEmail], 'fields' => ['User.id']]);

            if ($findUser) {
                return 1; //Present in both user table and betauser table  //User Already Exists
            } else {
                if ($is_approve == 1) {
                    return 2; //Present in beta table but not in user table and is_approve in 1  //Your beta user has been approved
                } else {
                    return 3; //Present in beta table but not in user table and is_approve in 0  //Your beta user has been disapproved
                }
            }
        } else {
            $User = ClassRegistry::init('User');
            $User->recursive = -1;
            $findUser = $User->find('count', ['conditions' => ['User.email' => $betaEmail], 'fields' => ['User.id']]);

            if ($findUser) {
                return 4; //Present in user table and not present in betauser table  //User Already Exists
            } else {
                return 5; //Not present in both user and beta user table
            }
        }
    }

    public function isValidDateTime($dateTime, $format = '')
    {
        if (!is_string($dateTime)) {
            return false;
        }

        // Accept dates like dd/mm/yyyy or mm/dd/yyyy depending on $format
        if (preg_match("/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/", trim($dateTime), $matches)) {
            $fmt = strtolower((string)$format);

            if ($fmt === 'm/d/y') {
                // format is month/day/year
                $month = (int) $matches[1];
                $day = (int) $matches[2];
            } else {
                // default: day/month/year
                $day = (int) $matches[1];
                $month = (int) $matches[2];
            }

            $year = (int) $matches[3];

            if (checkdate($month, $day, $year)) {
                return true;
            }
        }
        return false;
    }

    public function isValidStatus($sts_nm, $prj_name, $prjs, $stslists)
    {
        $ProjectsTable = $this->fetchTable('Projects');
        $findProj = $ProjectsTable->find()
            ->where([
                'Projects.name LIKE' => '%' . trim(strtolower($v ?? '')) . '%',
                'Projects.company_id' => SES_COMP
            ])
            ->select(['Projects.id', 'Projects.status_group_id'])
            ->disableHydration()
            ->first();
        if ($prj_name == '') {
            if ($stslists) {
                $retSts = 0;
                foreach ($stslists as $stk => $stv) {
                    if (trim(strtolower($sts_nm)) == trim(strtolower($stv))) {
                        $retSts = $stk;
                    }
                }
                return $retSts;
            }
        } else {
            $retSts = 0;
            foreach ($prjs as $stk => $stv) {
                if (trim(strtolower($prj_name)) == trim(strtolower($stv['name']))) {
                    foreach ($stv['status_group']['custom_statuses'] as $stk1 => $stv1) {
                        if (trim(strtolower($sts_nm)) == trim(strtolower($stv1['name']))) {
                            $retSts = $stv1['id'];
                        }
                    }
                }
            }
            return $retSts;
        }
        return false;
    }

    public function getValidprojectStstus($proj_sts, $sts_nm, $proj_id)
    {
        $legend = 1;
        $sts_grp = 0;
        if (isset($proj_sts[$proj_id])) {
            //Custom status
            if (trim($sts_nm) == '') {
                $legend = $proj_sts[$proj_id]['custom_statuses'][0]['status_master_id'];
                $sts_grp = $proj_sts[$proj_id]['custom_statuses'][0]['id'];
            } else {
                foreach ($proj_sts[$proj_id]['custom_statuses'] as $csk => $csvl) {
                    if (strtolower(trim($sts_nm)) == strtolower(trim($csvl['name']))) {
                        $legend = $csvl['status_master_id'];
                        $sts_grp = $csvl['id'];
                    }
                }
                if ($sts_grp == 0) {
                    $legend = $proj_sts[$proj_id]['custom_statuses'][0]['status_master_id'];
                    $sts_grp = $proj_sts[$proj_id]['custom_statuses'][0]['id'];
                }
            }
        } else {
            //Default sttaus
            $sts_grp = 0;
            if (trim($sts_nm) == '') {
                $legend = 1;
            } else {
                if (((strtolower(trim($sts_nm)) == 'wip') || (strtolower(trim($sts_nm)) == 'in progress'))) {
                    $legend = 2;
                } elseif (((strtolower(trim($sts_nm)) == 'close') || (strtoupper(trim($sts_nm)) == 'CLOSED'))) {
                    $legend = 3;
                } elseif ((strtolower(trim($sts_nm)) == 'resolve' || strtolower(trim($sts_nm)) == 'resolved')) {
                    $legend = 5;
                } else {
                    $legend = 1;
                }
            }
        }
        return [$legend, $sts_grp];
    }

    public function isValidDateHours($hour, $chk = null, $chk_1 = null)
    {
        if ($chk_1) {
            $exp = '/^[0-9]{0,5}:[0-9]{2}$/';
        } elseif ($chk) {
            $exp = '/^[0-9]{0,2}:[0-9]{2}[ap]m$/';
        } else {
            $exp = '/^[0-9]{0,2}:[0-9]{2}$/';
        }
        if (preg_match($exp, $hour)) {
            return true;
        }
        return false;
    }

    public function isValidTlDateHours($hour, $chk)
    {
        $exp = '/^[0-9]{0,2}:[0-9]{2}\s?[apAP][mM]$/';
        if (preg_match($exp, $hour)) {
            return true;
        }
        return false;
    }

    public function convert_ascii($string)
    {
        // Replace Single Curly Quotes
        $search[] = chr(226) . chr(128) . chr(152);
        $replace[] = "'";
        $search[] = chr(226) . chr(128) . chr(153);
        $replace[] = "'";

        // Replace Smart Double Curly Quotes
        $search[] = chr(226) . chr(128) . chr(156);
        $replace[] = '\"';
        $search[] = chr(226) . chr(128) . chr(157);
        $replace[] = '\"';

        // Replace En Dash
        $search[] = chr(226) . chr(128) . chr(147);
        $replace[] = '--';

        // Replace Em Dash
        $search[] = chr(226) . chr(128) . chr(148);
        $replace[] = '---';

        // Replace Bullet
        $search[] = chr(226) . chr(128) . chr(162);
        $replace[] = '*';

        // Replace Middle Dot
        $search[] = chr(194) . chr(183);
        $replace[] = '*';

        // Replace Ellipsis with three consecutive dots
        $search[] = chr(226) . chr(128) . chr(166);
        $replace[] = '...';

        $search[] = chr(150);
        $replace[] = '-';

        // Apply Replacements
        $string = str_replace($search, $replace, $string);

        // Remove any non-ASCII Characters
        //$string = preg_replace("/[^\x01-\x7F]/","", $string);
        return $string;
    }

    public function getSqlFields($arr, $prj_unq_id)
    {
        $qry = '';
        if (isset($arr)) {
            //Filter by date
            $case_date = $arr['date'];
            if (trim($case_date) == '1') {
                $one_date = date('Y-m-d H:i:s', time() - 3600);
                $qry .= " AND Easycase.dt_created >='" . $one_date . "'";
            } elseif (trim($case_date) == '24') {
                $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 day'));
                $qry .= " AND Easycase.dt_created >='" . $day_date . "'";
            } elseif (trim($case_date) == 'week') {
                $week_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 week'));
                $qry .= " AND Easycase.dt_created >='" . $week_date . "'";
            } elseif (trim($case_date) == 'month') {
                $month_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 month'));
                $qry .= " AND Easycase.dt_created >='" . $month_date . "'";
            } elseif (trim($case_date) == 'year') {
                $year_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 year'));
                $qry .= " AND Easycase.dt_created >='" . $year_date . "'";
            } elseif (strstr(trim($case_date), ':')) {
                //echo $case_date;exit;
                $ar_dt = explode(':', trim($case_date));
                $frm_dt = $ar_dt['0'];
                $to_dt = $ar_dt['1'];
                $qry .= " AND DATE(Easycase.dt_created) >= '" . date('Y-m-d H:i:s', strtotime($frm_dt)) . "' AND DATE(Easycase.dt_created) <= '" . date('Y-m-d H:i:s', strtotime($to_dt)) . "'";
            }

            //	if($arr['date'] =='1'){
            //	    $qry .=" AND Easycase.dt_created >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            //	}elseif($arr['date'] =='24'){
            //	    $qry .=" AND Easycase.dt_created >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            //	}elseif($arr['date'] =='week'){
            //	    $qry .=" AND Easycase.dt_created >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            //	}elseif($arr['date'] =='month'){
            //	    $qry .=" AND Easycase.dt_created >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            //	}elseif($arr['date'] =='year'){
            //	    $qry .=" AND Easycase.dt_created >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
            //	}elseif($arr['date'] =='cst_rng'){
            //	    $fm_date = explode("/", $arr['from']);
            //	    $from_date = $fm_date['2']."-".$fm_date['0']."-".$fm_date['1'];
            //
            //	    $t_date = explode("/", $arr['to']);
            //	    $to_date = $t_date['2']."-".$t_date['0']."-".$t_date['1'];
            //
            //	    $qry .=" AND Easycase.dt_created >= ".$from_date." AND Easycase.dt_created <=".$to_date;
            //	}
            //Filter by status
            if (intval($arr['status'])) {
                if ($arr['status'] > 6) {
                    $CustomStatus = ClassRegistry::init('CustomStatus');
                    $sts_cond = ['CustomStatus.company_id' => SES_COMP];
                    $CstmStsArrLst = $CustomStatus->find('list', ['conditions' => $sts_cond, 'fields' => ['CustomStatus.id', 'CustomStatus.name'], 'order' => ['CustomStatus.seq' => 'ASC']]);
                    if (!empty($CstmStsArrLst)) {
                        $id_sep = '';
                        foreach ($CstmStsArrLst as $c_key => $c_val) {
                            if (trim($c_val) == trim($CstmStsArrLst[$arr['status']])) {
                                if ($id_sep == '') {
                                    $id_sep = $c_key;
                                } else {
                                    $id_sep .= ',' . $c_key;
                                }
                            }
                        }
                        if ($id_sep != '') {
                            $qry .= ' AND Easycase.custom_status_id IN(' . $id_sep . ')';
                        }
                    } else {
                        $qry .= " AND Easycase.custom_status_id='" . (int)$arr['status'] . "'";
                    }
                } elseif ($arr['status'] == 2) {
                    $qry .= " AND (Easycase.legend='" . (int)$arr['status'] . "' OR Easycase.legend='4')";
                } else {
                    $qry .= " AND Easycase.legend='" . (int)$arr['status'] . "'";
                }
            } elseif ($arr['status'] == 'attach') {
                $qry .= " AND Easycase.format='1'";
            } elseif ($arr['status'] == 'update') {
                $qry .= " AND Easycase.type_id='10'";
            }
            if ($arr['types'] == 'all' && $arr['status'] != 'update') {
                $qry .= " AND Easycase.type_id !='10'";
            }
            //Filter by types
            if (intval($arr['types'])) {
                $qry .= " AND Easycase.type_id='" . (int)$arr['types'] . "'";
            }
            //Filter by priority
            if ($arr['priority'] != 'all') {
                $qry .= " AND Easycase.priority='" . (int)$arr['priority'] . "'";
            }

            //if (isset($prj_unq_id) && $prj_unq_id != 'all') {
            if (isset($prj_unq_id)) { //to fix the export with individual fields issue
                //Filter by members
                if (intval($arr['members'])) {
                    $qry .= " AND Easycase.user_id='" . (int)$arr['members'] . "'";
                }
                //Filter by assign to
                if (intval($arr['assign_to'])) {
                    $qry .= " AND Easycase.assign_to='" . (int)$arr['assign_to'] . "'";
                }
                //Filter by milestone
                if (intval($arr['milestone'])) {
                    $qry .= " AND EasycaseMilestone.milestone_id='" . (int)$arr['milestone'] . "'";
                }
            }
            return $qry;
        }
    }

    /**
     * @method public iptolocation(string $ip) Detect the location from IP
     * @author GDR<support@ornagescrum.com>
     * @return bool  Location fromt the ip
     */
    public function validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }

    public function getRealIpAddr()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if ($this->validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }


    /**
     * @method: Public hoursspent($project_id) Total hours spent in a project
     * @author GDR <support@orangescrum.com>
     * @return int hours spent
     */
    public function hoursspent($project_id, $isClientValue = null)
    {
        $easycasecls = ClassRegistry::init('Easycase');
        $easycasecls->recursive = -1;
        if ($project_id) {
            //$result = $easycasecls->query("SELECT ROUND(SUM(easycases.hours), 1) as hours from easycases WHERE project_id=".$project_id." AND istype='2' and isactive='1'");
            if (SES_TYPE == 3 || $isClientValue == 1) {
                $userHoursSpent = 'LogTime.user_id =' . SES_ID;
            } elseif (SES_TYPE < 3) {
                $userHoursSpent = '1';
            }
            $sql = 'SELECT SUM(LogTime.total_hours) AS hours '
                . 'FROM log_times as LogTime '
                . 'LEFT JOIN easycases AS Easycase ON LogTime.task_id=Easycase.id AND LogTime.project_id=Easycase.project_id '
                . 'WHERE ' . $userHoursSpent . ' AND LogTime.project_id =' . $project_id . ' AND Easycase.isactive=1';
            $result = $easycasecls->query($sql);
            #pr($result);exit;
            return $this->format_time_hr_min($result['0']['0']['hours']);
        } else {
            $projcls = ClassRegistry::init('Project');
            $projcls->recursive = -1;
            $project_list = $projcls->find('list', ['conditions' => ['isactive' => 1, 'company_id' => SES_COMP], 'fields' => ['id']]);
            if (empty($project_list)) {
                return '00 hrs 00 mins';
            }
            if (SES_TYPE == 3 || $isClientValue == 1) {
                $sql = 'SELECT SUM(LogTime.total_hours) AS hours '
                    . 'FROM log_times as LogTime '
                    . 'LEFT JOIN easycases AS Easycase ON LogTime.task_id=Easycase.id AND LogTime.project_id=Easycase.project_id '
                    . 'WHERE LogTime.user_id =' . SES_ID . ' AND LogTime.project_id IN (' . implode(',', $project_list) . ') AND Easycase.isactive=1';
            } elseif (SES_TYPE < 3) {
                $sql = 'SELECT SUM(LogTime.total_hours) AS hours '
                    . 'FROM log_times as LogTime '
                    . 'LEFT JOIN easycases AS Easycase ON LogTime.task_id=Easycase.id AND LogTime.project_id=Easycase.project_id '
                    . 'WHERE LogTime.project_id IN (' . implode(',', $project_list) . ') AND Easycase.isactive=1';
            }
            $result = $easycasecls->query($sql);
            return $this->format_time_hr_min($result['0']['0']['hours']);
        }
    }

    /**
     * @method: PUBLIC generate_invoiceid()
     */
    public function generate_invoiceid()
    {
        $trnsclas = ClassRegistry::init('Transaction');
        $trnsclas->recursive = -1;
        $trans = $trnsclas->find('first', ['conditions' => ('invoice_id IS NOT NULL'), 'order' => 'id DESC', 'fields' => ['invoice_id']]);

        if ($trans) {
            $prv_invoice_id = (int) $trans['Transaction']['invoice_id'];
            if ($prv_invoice_id == 1) {
                $prv_invoice_id = 153702;
            }
            $prv_invoice_id = (int) $trans['Transaction']['invoice_id'] + 1;
        } else {
            $prv_invoice_id = 153700;
        }
        $current_invoice_id = str_pad($prv_invoice_id, 6, 0, STR_PAD_LEFT);
        return $current_invoice_id;
    }

    public function getRemoteIP()
    {
        $ipaddress = '';
        if ($_SERVER['HTTP_CLIENT_IP']) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_X_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ($_SERVER['HTTP_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif ($_SERVER['REMOTE_ADDR']) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     *
     * @param type $source
     * @param type $destination
     * @param string $flag
     * @return boolean
     */
    public function zipFile($source, $destination, $flag = '')
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));
        if ($flag) {
            $flag = basename($source) . '/';
            //$zip->addEmptyDir(basename($source) . '/');
        }

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            $arr = [];
            foreach ($files as $file) {
                $arr[] = $file->getFileName();
                if ($file->getFileName() == '.' || $file->getFileName() == '..') {
                    continue;
                }
                $file = str_replace('\\', '/', realpath($file));
                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $flag . $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $flag . $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString($flag . basename($source), file_get_contents($source));
        }
        return $zip->close();
    }

    /* gantt start GKM */

    public function changeGanttDataV2($json_arr)
    {
        #echo "<pre>";print_r($json_arr); exit;
        $user_ids = [];
        $colors = [0 => '#73BCDE', 1 => '#8BC2B9', 2 => '#F8B363', 3 => '#EA7373', 4 => '#9ECC61'];
        foreach ($json_arr as $key => $value) {
            $assign_to = intval($value['assign_to']);
            if ($assign_to > 0 && !in_array($assign_to, $user_ids)) {
                $user_ids[] = $assign_to;
            }

            $json_arr[$key]['id'] = $value['id'];
            $json_arr[$key]['name'] = trim($value['title']);
            $json_arr[$key]['description'] = trim($value['message']);

            #STATUS_ACTIVE, STATUS_DONE, STATUS_FAILED, STATUS_SUSPENDED, STATUS_UNDEFINED.
            #$json_arr[$key]['status'] = 'STATUS_ACTIVE';
            $json_arr[$key]['status'] = $assign_to > 0 ? 'color' . array_search($assign_to, $user_ids) . '' : 'color15';
            $json_arr[$key]['canWrite'] = true;


            $json_arr[$key]['startIsMilestone'] = false;
            $json_arr[$key]['endIsMilestone'] = false;
            $json_arr[$key]['collapsed'] = false;
            $json_arr[$key]['assigs'] = [];
            $json_arr[$key]['hasChild'] = 0;
            $json_arr[$key]['level'] = 1;
            $json_arr[$key]['depends'] = $value['depends'];
            $json_arr[$key]['progress'] = $value['progress'];
            $json_arr[$key]['assigned_to'] = $value['assigned_to'];
            $json_arr[$key]['priority'] = $value['priority'];
            $json_arr[$key]['type_id'] = $value['type_id'];
            $json_arr[$key]['case_no'] = $value['case_no'];

            if ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);print $v['id'];echo "    1";exit;
                $json_arr[$key]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['end'] = $value['due_date'];
                $json_arr[$key]['color'] = $colors[$key];
            } elseif ((empty($value['gantt_start_date']) || is_null($value['gantt_start_date']) || $value['gantt_start_date'] == '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);echo "   2";exit;
                $json_arr[$key]['start'] = $value['due_date'];
                $json_arr[$key]['end'] = $value['due_date'];
                $json_arr[$key]['color'] = $colors[$key];
            } elseif ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] == '' || is_null($value['due_date']) || $value['due_date'] == '0000-00-00 00:00:00')) {
                //print_r($v['due_date']);echo "   3";exit;
                $json_arr[$key]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['end'] = date('Y-m-d', $this->dateConvertion($value['gantt_start_date']));
                $json_arr[$key]['color'] = $colors[$key];
            } else {
                //print_r($v['gantt_start_date']);echo "   4";exit;
                $start = explode(' ', $value['actual_dt_created']);
                $json_arr[$key]['start'] = $start[0];
                $json_arr[$key]['end'] = date('Y-m-d', $this->dateConvertion($value['actual_dt_created']));
                $json_arr[$key]['color'] = $colors[$key];
            }

            /* convert to user timezone */
            $json_arr[$key]['start'] = $this->convert_date_timezone($json_arr[$key]['start']);
            $json_arr[$key]['end'] = $this->convert_date_timezone($json_arr[$key]['end']);

            $json_arr[$key]['duration'] = $this->days_diff($json_arr[$key]['start'], $json_arr[$key]['end']);
            $json_arr[$key]['o_start'] = ($json_arr[$key]['start']);
            $json_arr[$key]['o_end'] = ($json_arr[$key]['end']);

            /* convert to millisecond */
            $json_arr[$key]['start'] = strtotime($json_arr[$key]['start']) * 1000;
            $json_arr[$key]['end'] = strtotime($json_arr[$key]['end']) * 1000;

            if ($value['legend'] == '1') {
                $json_arr[$key]['color'] = '#f19a91';
            } elseif ($value['legend'] == '2' || $value['legend'] == '6') {
                $json_arr[$key]['color'] = '#8dc2f8';
            } elseif ($value['legend'] == '5') {
                $json_arr[$key]['color'] = '#f3c788';
            } elseif ($value['legend'] == '3') {
                $json_arr[$key]['color'] = '#8ad6a3';
            } else {
                $json_arr[$key]['color'] = '#3dbb89';
            }
            unset($json_arr[$key]['title']);
            #unset($json_arr[$key]['id']);
            #unset($json_arr[$key]['legend']);
            unset($json_arr[$key]['gantt_start_date']);
            unset($json_arr[$key]['due_date']);
            unset($json_arr[$key]['actual_dt_created']);
        } //exit;
        #echo "<pre>";print_r($json_arr);exit;
        return $json_arr;
    }

    public function get_formated_date($value = '')
    {
        $zero_dates = ['0000-00-00', '0000-00-00 00:00:00'];
        $value = array_merge([
            'start_date' => null,
            'end_date' => null,
            'created' => null
        ], (array) $value);

        if (
            (!empty($value['start_date']) && $value['start_date'] !== null && !in_array($value['start_date'], $zero_dates)) &&
            (!empty($value['end_date']) && $value['end_date'] !== null && !in_array($value['end_date'], $zero_dates))
        ) {
            $start = $value['start_date'];
            $end = $value['end_date'];
        } elseif (
            (empty($value['start_date']) || $value['start_date'] === null || in_array($value['start_date'], $zero_dates)) &&
            (!empty($value['end_date']) && $value['end_date'] !== null && !in_array($value['end_date'], $zero_dates))
        ) {
            $start = $value['created'] ?? date('Y-m-d');
            $end = $value['end_date'];
        } elseif (
            (!empty($value['start_date']) && $value['start_date'] !== null && !in_array($value['start_date'], $zero_dates)) &&
            (empty($value['end_date']) || $value['end_date'] === null || in_array($value['end_date'], $zero_dates))
        ) {
            $start = $value['start_date'];
            $end = date('Y-m-d', $this->dateConvertion($value['start_date']));
        } else {
            $start = $value['created'] ? date('Y-m-d', strtotime($value['created'])) : date('Y-m-d');
            $end = $value['created'] ? date('Y-m-d', $this->dateConvertion($value['created'])) : date('Y-m-d');
        }

        /* overwrite if in-proper date  */
        $start_year = date('Y', strtotime($start));
        $end_year = date('Y', strtotime($end));
        if ($start_year == 1970 && $end_year == 1970) {
            $start = date('Y-m-d');
            $end = date('Y-m-d');
        }
        if ($start_year == 1970) {
            $start = $end_year == 1970 ? date('Y-m-d') : $end;
        }
        if ($end_year == 1970) {
            $end = date('Y-m-d');
        }
        /* convert to user timezone */
        $start = $this->convert_date_timezone($start);
        $end = $this->convert_date_timezone($end);

        $json_arr = [];
        $json_arr['duration'] = $this->days_diff($start, $end);
        $json_arr['o_start'] = $start;
        $json_arr['o_end'] = $end;
        $json_arr['start_date'] = $start;
        $json_arr['end_date'] = $end;

        $json_arr['color'] = $color ?? '';
        // convert to millisec
        $json_arr['start'] = strtotime($start) * 1000;
        $json_arr['end'] = strtotime($end) * 1000;
        return $json_arr;
    }

    public function days_diff($from = '', $to = '')
    {
        $from_date = strtotime($from); // or your date as well
        $to_date = strtotime($to);
        $datediff = $to_date - $from_date;
        //echo $from. " >>>>>> ".$to.' >>>>?? '.ceil($datediff / (60 * 60 * 24)).'<br>';
        #return round($datediff / (60 * 60 * 24)) > 1 ? round($datediff / (60 * 60 * 24)) : 1;
        return ceil($datediff / (60 * 60 * 24)) > 1 ? ceil($datediff / (60 * 60 * 24)) : 1;
    }

    /**
     * getWeekEnds
     *
     * @param  mixed $start_date
     * @param  mixed $end_date
     * @return array
     */
    public function getWeekEnds($start_date, $end_date, $comp_data)
    {
        $compWeekend = explode(',', $comp_data['week_ends'] ?? '');
        if (empty($compWeekend)) {
            return [];
        }
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date)
        );

        $weekends = [];
        foreach ($period as $key => $value) {
            $week_no = $value->format('N');
            $week_no = ($week_no > 6) ? 0 : $week_no;
            if (in_array($week_no, $compWeekend)) {
                $weekends[$value->format('Y-m-d')] = $value->format('D');
            }
        }

        return $weekends;
    }

    /**
     * calculateHolidays
     *
     * @param  mixed $all_weekends
     * @param  mixed $holidayLists
     * @param  mixed $tz timezone component instance
     * @return array
     */
    public function calculateHolidays($all_weekends, $holidayLists, $tz)
    {
        if (empty($all_weekends) && empty($holidayLists)) {
            return [];
        } elseif (!empty($all_weekends) && !empty($holidayLists)) {
            foreach ($holidayLists as $k => $v) {
                $actual_date = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v, 'datetime');
                $actual_date = date('Y-m-d', strtotime($actual_date));
                if (!array_key_exists($actual_date, $all_weekends)) {
                    $all_weekends[$actual_date] = date('D', strtotime($actual_date));
                }
            }

            return $all_weekends;
        } else {
            return (empty($all_weekends)) ? $holidayLists : $all_weekends;
        }
    }

    /**
     * formatWorkHours
     * fetch last 3 work hours
     *
     * @param  mixed $work_hours
     * @author PRB
     * @return array
     */
    public function formatWorkHours($work_hours)
    {
        if (!empty($work_hours)) {
            //fetch last 3 work hours
            $newArr = [];
            foreach ($work_hours as $k => $v) {
                $dt = date('Y-m-d', strtotime($k));
                if (!empty($newArr) && count($newArr) == 3) {
                    break;
                }
                if (!isset($newArr[$dt])) {
                    $newArr[$dt] = $v;
                } else {
                    if ($newArr[$dt] < $v) {
                        $newArr[$dt] = $v;
                    }
                }
            }

            return $newArr;
        }

        return $work_hours;
    }

    public function getWorkHour($work_hours, $date)
    {
        $hour = 0;
        $t_keys = array_keys($work_hours);
        $t_dates = array_values($work_hours);

        if ($date >= $t_keys[0]) {
            $hour = $t_dates[0];
        } elseif ($date <= $t_keys[2]) {
            $hour = $t_dates[2];
        } else {
            $hour = $t_dates[1];
        }

        return $hour;
    }

    public function totalWorkHours($utcstartdate, $utcenddate, $work_hours, $comp_data)
    {
        $tot_days = $this->days_diff($utcstartdate, $utcenddate);
        $tot_hours = 0;
        if (empty($work_hours) || count($work_hours) == 1) {
            $tot_hours = $tot_days * $comp_data['work_hour'];
        } else {
            for ($i = 0; $i < $tot_days; $i++) {
                $date = date('Y-m-d', strtotime($utcstartdate . ' +' . $i . ' day'));
                $tot_hours += $this->getWorkHour($work_hours, $date);
            }
        }

        return $tot_hours;
    }

    public function totalLeaveHours($utcstartdate, $utcenddate, $work_hours)
    {
        $tot_hours = 0;
        if (date('Y-m-d', strtotime($utcstartdate)) == date('Y-m-d', strtotime($utcenddate))) {
            $date = date('Y-m-d', strtotime($utcstartdate));
            $tot_hours += $this->getWorkHour($work_hours, $date);
        } else {
            $tot_days = $this->days_diff($utcstartdate, $utcenddate) + 1;
            for ($i = 0; $i <= $tot_days; $i++) {
                $date = date('Y-m-d', strtotime($utcstartdate . ' +' . $i . ' day'));
                $tot_hours += $this->getWorkHour($work_hours, $date);
            }
        }

        return $tot_hours;
    }

    /**
     * totalHolidayWorkHours
     *
     * @param  mixed $all_holidays include weekends
     * @param  mixed $work_hours
     * @return void
     */
    public function totalHolidayWorkHours($all_holidays, $work_hours)
    {
        $tot_hours = 0;
        if (!empty($all_holidays)) {
            foreach ($all_holidays as $k => $v) {
                $tot_hours += $this->getWorkHour($work_hours, $k);
            }
        }

        return $tot_hours;
    }
    public function convert_date_timezone($date = '')
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        return $date;
    }

    public function formatTitle($title)
    {
        if (isset($title) && !empty($title)) {
            $title = stripcslashes(htmlspecialchars(html_entity_decode($title, ENT_QUOTES, 'UTF-8')));
        }
        return $title;
    }

    public function dateConvertion($date)
    {
        //print_r($date);exit;
        $seconds = strtotime($date);
        return ($seconds + 86400);
    }

    /* gantt end */

    public function format_date($date = '', $format = 'date')
    {
        if ($format == 'date') {
            return date('Y-m-d', strtotime($date));
        } else {
            return date('Y-m-d H:i:s', strtotime($date));
        }
    }

    /* Author: GKM
     * to format sec to hr min
     */

    public function format_time_hr_min($totalsecs = '', $typ = null)
    {
        $hours = floor($totalsecs / 3600);
        $mins = round(($totalsecs % 3600) / 60);

        $hours_str = $hours > 0 ? $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' : '';
        $mins_str = $mins > 0 ? $mins . ' min' . ($mins > 1 ? 's' : '') : '';

        if ($typ) {
            return "<div class='billable-graph un-bill-time'><h2>$hours_str<span></span></h2><h2>$mins_str<span></span></h2></div>";
        } else {
            return $hours_str . $mins_str;
        }
    }

    public function format_time_hr_min_point($totalsecs = '', $typ = null)
    {
        $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) : 0;
        //return round(($totalsecs / 3600),1);
        $mins = round(($totalsecs % 3600) / 60) > 0 ? (($totalsecs % 3600) / 3600) : 0;
        if ($mins >= 0.5) {
            $mins = round($mins, 1);
        }
        if ($hours && $mins) {
            //return floatval($hours . "." . $mins);
            return $hours + $mins;
        } elseif ($hours) {
            return $hours;
        } elseif ($mins) {
            //return floatval('0.'.$mins);
            return $mins;
        } else {
            return 0;
        }
    }

    public function format_second_hrmin($totalsecs = '')
    {
        $hours = $mins = '00';
        if (!empty($totalsecs)) {
            $totalsecs = (int) $totalsecs; // Ensure $totalsecs is an integer
            $hours = floor($totalsecs / 3600) > 0 ? strval(floor($totalsecs / 3600)) : '00';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? strval(round(($totalsecs % 3600) / 60)) : '00';
        }
        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }


    public function format_second_hrmin_nopad($totalsecs = '')
    {
        $hours = $mins = '0';
        if (!empty($totalsecs)) {
            $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) : '0';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? round((($totalsecs % 3600) / 60), 2) : '0';
        }
        if (stristr(strval($mins), '.')) {
            $mins = substr(strval($mins), 0, strpos(strval($mins), '.'));
        }
        return $hours . ':' . $mins;
    }

    /* By GKM
     * used to generate invoice number
     */

    public function invoice_number($invoice)
    {
        $invoice_code = 'IN';
        if ($invoice < 10) {
            $invoice_code .= '000';
        } elseif ($invoice < 100) {
            $invoice_code .= '00';
        } elseif ($invoice < 1000) {
            $invoice_code .= '0';
        } else {
            $invoice_code .= '';
        }
        return $invoice_code .= $invoice;
    }

    /* By GKM
     * used to format price value
     */

    public function format_price($price)
    {
        return number_format($price, 2, '.', '');
    }

    /* author: GKM
     * it is used to format 24 hr to 12 hr with am / pm format
     */

    public function format_24hr_to_12hr($time)
    {
        $out_time_arr = explode(':', $time);
        if (SES_TIME_FORMAT == 12) {
            $out_mode = intval($out_time_arr[0]) < 12 ? 'am' : 'pm';
            $out_hr = intval($out_time_arr[0]) > 12 ? intval($out_time_arr[0]) - 12 : intval($out_time_arr[0]);
            $out_min = intval($out_time_arr[1]);
        } else {
            $out_mode = '';
            $out_hr = $out_time_arr[0];
            $out_min = intval($out_time_arr[1]);
        }
        return ($out_hr > 0 ? $out_hr : 12) . ':' . ($out_min < 10 ? '0' : '') . $out_min . '' . $out_mode;
    }
    public function api_format_24hr_to_12hr($time)
    {
        $out_time_arr = explode(':', $time);
        $out_mode = intval($out_time_arr[0]) < 12 ? ' AM' : ' PM';
        $out_hr = intval($out_time_arr[0]) > 12 ? intval($out_time_arr[0]) - 12 : intval($out_time_arr[0]);
        $out_min = intval($out_time_arr[1]);
        return ($out_hr > 0 ? $out_hr : 12) . ':' . ($out_min < 10 ? '0' : '') . $out_min . '' . $out_mode;
    }

    /* by GKM
     * for removing special characters
     */

    public function seo_url($string = '', $flag = '-')
    {
        if (trim($string) != '') {
            return trim(preg_replace('/[^a-z0-9]+/i', $flag, $string), $flag);
        } else {
            return '';
        }
    }

    public static function is_image($mime)
    {
        $image_type_to_mime_type = [
            'gif' => 'image/gif', // IMAGETYPE_GIF
            'jpg' => 'image/jpeg', // IMAGETYPE_JPEG
            'jpeg' => 'image/jpeg', // IMAGETYPE_JPEG
            'png' => 'image/png', // IMAGETYPE_PNG
            'bmp' => 'image/bmp', // IMAGETYPE_BMP
        ];

        return (in_array($mime, $image_type_to_mime_type) ? true : false);
    }
    public function dateRangeFilter($filter, $start_date, $end_date)
    {
        if ($filter == 'Week' || $filter == '') {
            $date_of_startdate = date('d', strtotime($start_date));
            $date_of_enddate = date('d M', strtotime($end_date));
            $date_range = $date_of_startdate . '-' . $date_of_enddate;
        } elseif ($filter == 'Month' || $filter == 'customdate') {
            $date_of_startdate = date('d M Y', strtotime($start_date));
            $date_of_enddate = date('d M Y', strtotime($end_date));
            $date_range = $date_of_startdate . '-' . $date_of_enddate;
        } elseif ($filter == 'Quater') {
            $year1 = date('Y', strtotime($start_date));
            $year2 = date('Y', strtotime($end_date));
            $month1 = date('m', strtotime($start_date));
            $month2 = date('m', strtotime($end_date));
            // pr($start_date); pr($end_date); exit;
            if ($month1 > 9 && $month2 <= 01) {
                $date_range1 = 'Q4';
            } elseif ($month1 > 3 && $month2 <= 7) {
                $date_range1 = 'Q2';
            } elseif ($month1 > 6 && $month2 <= 10) {
                $date_range1 = 'Q3';
            } elseif ($month2 <= 4) {
                $date_range1 = 'Q1';
            }
            $date_range = $date_range1 . ' ' . $year1;
        }
        return $date_range;
    }
    public function date_filter($filter = '', $curDateTime = '')
    {
        if (empty($curDateTime)) {
            $curDateTime = date('Y-m-d H:i:s');
        }
        $data = [];
        $month = date('m', strtotime($curDateTime . ($filter == 'lastquarter' ? ' -3 months' : '')));
        if (PAGE_NAME == 'FetchPlannedVSActualData') {
            $month = date('m', strtotime($curDateTime));
        }
        if ($month < 4) {
            $start = 'first day of january';
            $end = 'last day of march';
        } elseif ($month > 3 && $month < 7) {
            $start = 'first day of april';
            $end = 'last day of june';
        } elseif ($month > 6 && $month < 10) {
            $start = 'first day of july';
            $end = 'last day of september';
        } elseif ($month > 9) {
            $start = 'first day of october';
            $end = 'last day of december';
        }
        switch ($filter) {
            case 'today':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'yesterday':
                $data['strddt'] = date('Y-m-d', strtotime("$curDateTime -1 day"));
                $data['enddt'] = date('Y-m-d', strtotime("$curDateTime -1 day"));
                break;
            case 'thisweek':
                if (date('D', strtotime($curDateTime)) === 'Mon') {
                    $data['strddt'] = date('Y-m-d', strtotime($curDateTime));
                    $data['enddt'] = date('Y-m-d', strtotime($curDateTime . ' +6 days'));
                } else {
                    $data['strddt'] = date('Y-m-d', strtotime('last monday', strtotime($curDateTime)));
                    $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                }
                break;
            case 'thisweekfull':
                if (date('D', strtotime($curDateTime)) === 'Mon') {
                    $data['strddt'] = date('Y-m-d', strtotime($curDateTime));
                    $data['enddt'] = date('Y-m-d', strtotime($curDateTime . ' +6 days'));
                } else {
                    $data['strddt'] = date('Y-m-d', strtotime('last monday', strtotime($curDateTime)));
                    $data['enddt'] = date('Y-m-d', strtotime($data['strddt'] . ' +6 days'));
                }
                break;
            case 'thismonth':
                $data['strddt'] = date('Y-m-d', strtotime('first day of this month', strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'thismonthfull':
                $data['strddt'] = date('Y-m-d', strtotime('first day of this month', strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime('last day of this month', strtotime($curDateTime)));
                break;
            case 'thisquarter':
                $data['strddt'] = date('Y-m-d', strtotime($start, strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'thisyear':
                $data['strddt'] = date('Y-m-d', strtotime('first day of January', strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'lastweek':
                if (date('D', strtotime($curDateTime)) === 'Mon') {
                    $data['strddt'] = date('Y-m-d', strtotime('last monday', strtotime($curDateTime)));
                    $data['enddt'] = date('Y-m-d', strtotime($curDateTime . ' -1 days'));
                } else {
                    $data['strddt'] = date('Y-m-d', strtotime('last monday', strtotime($curDateTime . ' -7 days')));
                    $data['enddt'] = date('Y-m-d', strtotime('next sunday', strtotime($curDateTime . ' -7 days')));
                }
                break;
            case 'nextweek':
                $data['strddt'] = date('Y-m-d', strtotime('next monday', strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime('next sunday', strtotime($data['strddt'])));
                break;
            case 'lastmonth':
                $data['strddt'] = date('Y-m-d', strtotime('first day of this month', strtotime($curDateTime . ' -1 month')));
                $data['enddt'] = date('Y-m-d', strtotime('last day of this month', strtotime($curDateTime . ' -1 month')));
                break;
            case 'nextmonth':
                $data['strddt'] = date('Y-m-d', strtotime('first day of next month', strtotime($curDateTime . ' 0 month')));
                $data['enddt'] = date('Y-m-d', strtotime('last day of next month', strtotime($curDateTime . ' 0 month')));
                break;
            case 'lastquarter':
                $data['strddt'] = date('Y-m-d', strtotime($start, strtotime($curDateTime)));
                $data['enddt'] = date('Y-m-d', strtotime($end, strtotime($curDateTime)));
                if ($month > 9) {
                    $data['strddt'] = date('Y-m-d', strtotime($data['strddt'] . '-1 year'));
                    $data['enddt'] = date('Y-m-d', strtotime($data['enddt'] . '-1 year'));
                }
                break;
            case 'lastyear':
                $data['strddt'] = date('Y-m-d', strtotime('first day of January', strtotime($curDateTime . ' -1 year')));
                $data['enddt'] = date('Y-m-d', strtotime('last day of December', strtotime($curDateTime . ' -1 year')));
                break;
            case 'last365days':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime . ' -364 days'));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'last30days':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime . ' -30 days'));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'last15days':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime . ' -15 days'));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime));
                break;
            case 'last30p30days':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime . ' -60 days'));
                $data['enddt'] = date('Y-m-d', strtotime($data['strddt'] . ' +30 days'));
                break;
            case 'next30days':
                $data['strddt'] = date('Y-m-d', strtotime($curDateTime));
                $data['enddt'] = date('Y-m-d', strtotime($curDateTime . ' +30 days'));
                break;
            case 'alldates':
                break;
            case 'custom':
                break;
            default:
                break;
        }
        return $data;
    }

    public function getQuarter($curDateTime)
    {
        $curDateTime = $curDateTime != '' ? $curDateTime : date('Y-m-d H:i:s');
        $data = [];
        $month = date('m', strtotime($curDateTime));
        if ($month < 4) {
            $start = 'first day of january';
            $end = 'last day of march';
        } elseif ($month > 3 && $month < 7) {
            $start = 'first day of april';
            $end = 'last day of june';
        } elseif ($month > 6 && $month < 10) {
            $start = 'first day of july';
            $end = 'last day of september';
        } elseif ($month > 9) {
            $start = 'first day of october';
            $end = 'last day of december';
        }

        $data['strddt'] = date('Y-m-d', strtotime($start, strtotime($curDateTime)));
        $data['enddt'] = date('Y-m-d', strtotime($end, strtotime($curDateTime)));

        return $data;
    }
    public function caseMemsName($uid)
    {
        $User = $this->fetchTable('Users');

        $usrDtls = $User->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])
            ->where(['id' . (is_array($uid) ? ' IN' : '') => $uid, 'isactive' => 1])->toArray();
        if (count($usrDtls) == 1) {
            $memlist = array_values($usrDtls);
            return $memlist[0];
        } else {
            return $usrDtls;
        }
    }
    public function caseMilestonesName($mid, $send_arr = 0)
    {

        $Milestone = $this->fetchTable('Milestones');
        $mlDtls = $Milestone->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['id' => $mid, 'isactive' => 1])
            ->disableHydration()
            ->toArray();
        if (count($mlDtls) == 1) {
            $memlist = array_values($mlDtls);
            return ($send_arr) ? $memlist : $memlist[0];
        } else {
            return $mlDtls;
        }
    }

    public function generateRandomString($str, $length = 2)
    {
        $str = str_replace(' ', '', $str);
        $characters = str_split($str);
        $charactersLength = count($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getCurrencyCode($invId)
    {
        $currenciesTable = $this->fetchTable('Currencies');

        return $currenciesTable->getCurrencyCode($invId);
    }

    public function getDateFromYearAndMonth($year = '', $month = '', $curDateTime = '')
    {
        $curDateTime = $curDateTime != '' ? $curDateTime : date('Y-m-d H:i:s');
        $data = [];
        $start = 1;
        $end = 31;

        if ($month == '') {
            $month = date('m', strtotime($curDateTime));
        }
        if ($year == '') {
            $year = date('Y', strtotime($curDateTime));
        }
        $month30Arr = [4, 6, 9, 11];
        if ((int) $month != 2 && in_array((int) $month, $month30Arr)) {
            $end = 30;
        } else {
            if ((int) $month == 2 && ($year % 4 == 0 || $year % 100 == 0 || $year % 400 == 0)) {
                $end = 29;
            } elseif ((int) $month == 2) {
                $end = 28;
            }
        }

        $data['strddt'] = date('Y-m-d', strtotime($year . '-' . $month . '-' . $start));
        $data['enddt'] = date('Y-m-d', strtotime($year . '-' . $month . '-' . $end));

        return $data;
    }

    public function seconds2human($ss, $humanReadable = false)
    {
        $h = floor($ss / 3600);
        $m = floor($ss / 60 % 60);
        $minStr = $humanReadable ? 'minutes' : 'mins';
        if ($h && $m) {
            $hrs = $humanReadable ? (($h == 1) ? 'hour' : 'hours') : (($h == 1) ? 'hr' : 'hrs');
            return "$h $hrs $m $minStr";
        } elseif ($h) {
            $hrs = $humanReadable ? (($h == 1) ? 'hour' : 'hours') : (($h == 1) ? 'hr' : 'hrs');
            return "$h $hrs";
        } elseif ($m) {
            return "$m $minStr";
        } else {
            return '';
        }
    }

    public function seconds2fraction($ss, $noLetters = false, $is_rounded = 0)
    {
        // Ensure $ss is numeric and convert to float to avoid implicit conversion warnings
        $ss = (float)$ss;

        $h = intval($ss / 3600);
        if ($h) {
            $totalMinutes = $ss / 60;
            $m = intval($totalMinutes - ($h * 60));
            if ($is_rounded) {
                $mi = $m > 30 ? 1 : 0;
                $str = $h + $mi;
            } else {
                $mi = $m ? ':' . round($m, 2) : '';
                $str = !$noLetters ? "{$h}{$mi} h" : "{$h}{$mi}";
            }
            return $str;
        } else {
            $totalMinutes = $ss / 60;
            $m = intval($totalMinutes % 60);
            if ($is_rounded) {
                $str = $m > 30 ? 1 : 0;
            } else {
                $str = !$noLetters ? round($m, 2) . ' m' : round($m, 2);
            }
            return $str;
        }
    }
    /* To get the Recurrence Rule according to the input given by the user
     */
    public function getRRule($recurrenceDetail, $type = 'test')
    {
        $frequency = $interval = $byday = $bymonthday = $byweekno = $bymonth = '';
        $startDate = $recurrenceDetail['recur_start_date'];
        $endDate = $recurrenceDetail['recurrence_end_type'] == 'date' ? $recurrenceDetail['recur_end_date'] : '';
        $occurrences = $recurrenceDetail['recurrence_end_type'] == 'occurrances' ? $recurrenceDetail['occurrances'] : '10';
        if ($recurrenceDetail['recurrence_end_type'] == 'date') {
            $occurrences = '';
        }
        switch ($recurrenceDetail['recur_pattern']) {
            case 'daily':
                $frequency = 'DAILY';
                $interval = $recurrenceDetail['daily_interval'];
                if ($recurrenceDetail['daily_check'] == 'interval') {
                    $byday = '';
                } else {
                    $byday = 'MO,TU,WE,TH,FR';
                }
                break;
            case 'weekly':
                $frequency = 'WEEKLY';
                $interval = $recurrenceDetail['weekly_interval'];
                $byday = $recurrenceDetail['weekly_days'];
                break;
            case 'monthly':
                $frequency = 'MONTHLY';
                $interval = $recurrenceDetail['monthly_interval'];
                $bymonthday = $recurrenceDetail['monthly_date'];
                if ($recurrenceDetail['monthly_check'] == 'complecated') {
                    $byweekno = $recurrenceDetail['monthly_mask'];
                    $byday = $recurrenceDetail['monthly_day'];
                    $interval = $recurrenceDetail['monthly_interval_complete'];
                }
                break;
            case 'yearly':
                $frequency = 'YEARLY';
                $interval = $recurrenceDetail['yearly_interval'];
                $bymonthday = $recurrenceDetail['yearly_date'];
                $bymonth = $recurrenceDetail['yearly_month'];
                if ($recurrenceDetail['yearly_check'] == 'complecated') {
                    $byday = $recurrenceDetail['yearly_day'];
                    $byweekno = $recurrenceDetail['yearly_mask'];
                    $bymonth = $recurrenceDetail['yearly_month_complete'];
                }
                break;
        }
        $interval = intval($interval);
        $rrule = new RRule([
            'FREQ' => $frequency,
            'INTERVAL' => (is_numeric($interval) && $interval != 0) ? round($interval) : 1,
            'BYMONTHDAY' => $bymonthday,
            'BYDAY' => $byday,
            'BYWEEKNO' => $byweekno,
            'BYMONTH' => $bymonth,
            'DTSTART' => ($startDate != '') ? $startDate : date('Y-m-d'),
            'COUNT' => $occurrences,
            'UNTIL' => $endDate
        ]);
        return $rrule;
    }

    public function getRRuleByDate($recurringDetails, $date)
    {
        $recurr_dayy = $recurringDetails['byweekno'] == 5 ? -1 : $recurringDetails['byweekno'];
        $recurringDetails['bymonthday'] = $recurringDetails['byday'] ? '' : $recurringDetails['bymonthday'];
        if ($recurringDetails['frequency'] == 'WEEKLY') {
            $ocrce = ($recurringDetails['occurrences']) ? $recurringDetails['occurrences'] : '';
        } elseif ($recurringDetails['frequency'] == 'DAILY') {
            $ocrce = ($recurringDetails['occurrences']) ? ($recurringDetails['occurrences'] + 1) : '';
        } elseif ($recurringDetails['frequency'] == 'MONTHLY') {
            $ocrce = ($recurringDetails['occurrences']) ? ($recurringDetails['occurrences'] + 1) : '';
        } elseif ($recurringDetails['frequency'] == 'YEARLY') {
            $ocrce = ($recurringDetails['occurrences']) ? ($recurringDetails['occurrences'] + 1) : '';
        }
        $rrule = new RRule([
            'FREQ' => $recurringDetails['frequency'],
            'INTERVAL' => $recurringDetails['rec_interval'],
            'BYMONTHDAY' => $recurringDetails['bymonthday'],
            'BYDAY' => $recurr_dayy . $recurringDetails['byday'],
            'BYMONTH' => $recurringDetails['bymonth'],
            'DTSTART' => $recurringDetails['start_date'],
            'COUNT' => $ocrce,
            'UNTIL' => ($recurringDetails['occurrences']) ? '' : (empty($recurringDetails['end_date']) ? $date : $recurringDetails['end_date'])
        ]);
        $occurrenceDates = $rrule->getOccurrences();
        return $occurrenceDates;
    }

    /*
     * Author: Satyajeet
     * To check current date is in recurring date array or not
     */
    public function checkDateInRecurring($recurringDetails, $date)
    {
        $occurrenceDates = $this->getRRuleByDate($recurringDetails, $date);
        foreach ($occurrenceDates as $k => $orrcurrences) {
            $orrcurrence = $orrcurrences->format('Y-m-d');
            if (strtotime($date) == strtotime($orrcurrence)) {
                return $k;
            }
        }
        return 0;
    }

    public function getRecurring($recurringDetails, $date)
    {
        $occurrenceDates = $this->getRRuleByDate($recurringDetails, $date);
        $occurrenceDates1 = [];
        foreach ($occurrenceDates as $k => $v) {
            $currentDate = strtotime($date);
            $compareDate = strtotime($v->format('Y-m-d'));
            if ($currentDate < $compareDate) {
                $occurrenceDates1[] = $v->format('l, d F Y');
            }
        }
        return $occurrenceDates1;
    }
    public function getApiStatus($type, $legend)
    {
        if ($type == 10) {
            return 'Update';
        } elseif ($legend == 1) {
            return 'New';
        } elseif ($legend == 2 || $legend == 4) {
            return 'In Progress';
        } elseif ($legend == 3) {
            return 'Closed';
        } elseif ($legend == 5) {
            return 'Resolved';
        }
    }
    public function getLeaveDates($start_date, $end_date, $id)
    {
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
        $x = floor($days);
        if ($x < 7) {
            $interval = 1;
        } elseif ($x > 80) {
            $interval = ceil($x / 10);
        } else {
            $interval = 7;
        }
        $dt_arr = [];
        for ($i = 0; $i <= $x; $i++) {
            $m = ' +' . $i . 'day';
            $dt = date('Y-m-d', strtotime(date('Y-m-d', strtotime($start_date)) . $m));
            $dt_arr = array_merge($dt_arr, [$dt => $id]);
        }
        return $dt_arr;
    }

    /*
     * Delete all booked and overloaded hours from database
     */

    /*
     * To check task is active or not
     */
    public function taskIsActive($caseId)
    {
        $easycaseTable = $this->fetchTable('Easycases');
        $taskDetails = $easycaseTable->find()
            ->select(['isactive'])
            ->where(['id' => $caseId])
            ->disableHydration()
            ->first();

        return $taskDetails ? $taskDetails['isactive'] : null;
    }

    /*
     * Check the resource availability On
     */

    /* gantt start */

    public function changeGanttDataV3($json_arr, $status_arr = '', $date_format = 'Y-m-d')
    {
        $user_ids = [];

        $colors = [0 => '#73BCDE', 1 => '#8BC2B9', 2 => '#F8B363', 3 => '#EA7373', 4 => '#9ECC61'];
        foreach ($json_arr as $key => $value) {
            $assign_to = intval($value['assign_to']);
            if ($assign_to > 0 && !in_array($assign_to, $user_ids)) {
                $user_ids[] = $assign_to;
            }
            if ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                $json_arr[$key]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['end'] = $value['due_date'];
            } elseif ((empty($value['gantt_start_date']) || is_null($value['gantt_start_date']) || $value['gantt_start_date'] == '0000-00-00 00:00:00') && ($value['due_date'] != '' && !is_null($value['due_date']) && $value['due_date'] != '0000-00-00 00:00:00')) {
                $json_arr[$key]['start'] = $value['due_date'];
                $json_arr[$key]['end'] = $value['due_date'];
            } elseif ((!empty($value['gantt_start_date']) && !is_null($value['gantt_start_date']) && $value['gantt_start_date'] != '0000-00-00 00:00:00') && ($value['due_date'] == '' || is_null($value['due_date']) || $value['due_date'] == '0000-00-00 00:00:00')) {
                $json_arr[$key]['start'] = $value['gantt_start_date'];
                $json_arr[$key]['end'] = date($date_format, $this->dateConvertion($value['gantt_start_date']));
            } else {
                $start = explode(' ', $value['actual_dt_created']);
                $json_arr[$key]['start'] = $start[0];
                $json_arr[$key]['end'] = date($date_format, $this->dateConvertion($value['actual_dt_created']));
            }

            /* convert to user timezone */
            $json_arr[$key]['start'] = $this->convert_date_timezone($json_arr[$key]['start']);
            $json_arr[$key]['end'] = $this->convert_date_timezone($json_arr[$key]['end']);

            $json_arr[$key]['duration'] = $this->days_diff($json_arr[$key]['start'], $json_arr[$key]['end']);
            $json_arr[$key]['start_date'] = ($json_arr[$key]['start']);
            $json_arr[$key]['end_date'] = ($json_arr[$key]['end']);

            /* convert to millisecond */
            $json_arr[$key]['start_date'] = $json_arr[$key]['start'];
            $json_arr[$key]['start'] = strtotime($json_arr[$key]['start']) * 1000;
            $json_arr[$key]['end'] = strtotime($json_arr[$key]['end']) * 1000;

            switch ($value['legend']) {
                case '1':
                    $json_arr[$key]['color'] = '#f19a91';
                    break;
                case '2':
                case '6':
                    $json_arr[$key]['color'] = '#8dc2f8';
                    break;
                case '5':
                    $json_arr[$key]['color'] = '#f3c788';
                    break;
                case '3':
                    $json_arr[$key]['color'] = '#8ad6a3';
                    break;
                default:
                    $json_arr[$key]['color'] = isset($status_arr[$value['legend']]) && !empty($status_arr[$value['legend']])
                        ? $status_arr[$value['legend']]['color']
                        : '#3dbb89';
            }
        }
        return $json_arr;
    }

    public function recursive_map($haystack)
    {
        if (count($haystack) > 0) {
            foreach ($haystack as $key => $value) {
                foreach ($value as $ckey => $cval) {
                    if (!is_array($cval) && array_key_exists($cval, $haystack)) {
                        $haystack[$key][$cval] = $haystack[$cval];
                        unset($haystack[$cval]);
                        return $this->recursive_map($haystack);
                    } elseif (is_array($cval) && count($cval) > 0) {
                        foreach ($cval as $k1 => $v1) {
                            if (array_key_exists($k1, $haystack)) {
                                $haystack[$key][$ckey][$k1] = $haystack[$k1];
                                unset($haystack[$k1]);
                            }
                        }
                    } else {
                        continue;
                    }
                }
            }
        }
        return $haystack;
    }

    public function gethrev($msg = '')
    {
        $msg = trim($msg);
        if ($msg == '') {
            return '';
        }
        if (stristr($msg, '&lt;') || stristr($msg, '&gt;')) {
            $msg = htmlspecialchars_decode($msg);
        }
        return addslashes($msg);
    }
    public function geth($msg = '')
    {
        $msg = trim($msg);
        if ($msg == '') {
            return '';
        }
        $msg = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $msg);
        $msg = preg_replace('/(<br \/>)+$/', '', $msg);
        return addslashes($msg);
    }
    /* gantt end */
    public function subscribeToDrip($params = [])
    {
        return true;
    }
    public function subscribeToFomo($params = [])
    {
        return true;
    }
    public function getworkhr($whl, $dt)
    {
        $defaultWorkhour = WorkHoursTable::DEFAULT_WORKHOUR;
        if (!empty($whl)) {
            foreach ($whl as $k => $v) {
                $logdt = date('Y-m-d', strtotime($k));
                if (strtotime($dt) >= strtotime($logdt)) {
                    return $v;
                }
            }
        }
        return $defaultWorkhour;
    }
    public function showSubtaskTitle($title, $id, $related, $is_angular = 0)
    {
        if (!empty($related['parent'][$id])) {
            $parent_id = $related['parent'][$id];
            $aro_icon = ' <i class="material-icons">&#xE314;</i> ';
            if ($is_angular) { // for admin my dashboard
                $aro_icon = ' < ';
            }
            if (!empty($related['parent'][$parent_id])) {
                $super_parent_id = $related['parent'][$parent_id];
                $title .= $aro_icon . trim($related['task'][$parent_id]);
                $title .= $aro_icon . trim($related['task'][$super_parent_id]);
            } else {
                $title .= $aro_icon . trim($related['task'][$parent_id]);
            }
        }
        return $title;
    }

    public function parentDropDown($data, $parentId = 0, $level = 0, $options = ['Select Parent'])
    {
        $level++;
        foreach ($data as $val) {
            if ($val['parent_task_id'] == $parentId) {
                $options[$val['id']] = str_repeat('-- ', $level - 1) . $val['title'];
                $newParent = $val['id'];
                $options = $this->categoryDropDown($data, $newParent, $level, $options);
            }
        }
        return $options;
    }
    public function task_dependency($EasycaseId = '')
    {
        /* dependency check start */
        $Easycase = ClassRegistry::init('Easycase');
        $allowed = 'Yes';
        $params = [
            'conditions' => ['Easycase.id' => $EasycaseId],
            'fields' => ['Easycase.id', 'Easycase.depends']
        ];
        $depends = $Easycase->find('first', $params);
        if (is_array($depends) && count($depends) > 0 && trim($depends['Easycase']['depends']) != '') {
            $parent_params = [
                'conditions' => ['Easycase.id IN (' . $depends['Easycase']['depends'] . ')'],
                'fields' => ['Easycase.id', 'Easycase.title', 'Easycase.legend', 'Easycase.status', 'Easycase.isactive', 'Easycase.due_date']
            ];
            $result = $Easycase->find('all', $parent_params);
            if (is_array($result) && count($result) > 0) {
                foreach ($result as $key => $parent) {
                    if (($parent['Easycase']['status'] == 2 && $parent['Easycase']['legend'] == 3) || ($parent['Easycase']['legend'] == 3)) {
                        // NO ACTION
                    } elseif ($parent['Easycase']['isactive'] == 0) {
                        // NO ACTION
                    } else {
                        $allowed = 'No';
                    }
                }
                $this->parent_task = $result;
            }
        }
        /* dependency check end */
        return $allowed;
    }
    public function convert_hhmm_hours($time)
    {
        $timeArr = explode(':', $time);
        $decTime = (float) ($timeArr[0] ?? 0) + ((float) ($timeArr[1] ?? 0) / 60);
        return $decTime;
    }
    public function getBacklogFilter($filters, $pid, $is_backlog = 0)
    {
        $ret_qry = '';
        $ret_qry_assn = $ret_qry_type = $ret_qry_status = $ret_qry_custom = $ret_qry_epic = [];
        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $GMT_DATEs = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $now = new FrozenTime('now', $toTz);
        $ymdHisFormat = 'Y-m-d H:i:s';
        if ($filters != '' && !empty($filters)) {
            foreach ($filters as $k => $v) {
                if ($v == 'me') {
                    $v = SES_ID;
                }
                if ($v == 'me') {
                    $ret_qry = ' AND Easycase.assign_to=' . SES_ID;
                } elseif ($v == 'last') {
                    $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 day'));
                    $ret_qry .= " AND (Easycase.dt_created >='" . $day_date . "')";
                } elseif (strpos($v, 'types_') !== false) {
                    $t_t = str_replace('types_', '', $v);
                    if (!empty($t_t)) {
                        array_push($ret_qry_type, $t_t);
                    }
                } elseif (strpos($v, 'epic_') !== false) {
                    $t_t = str_replace('epic_', '', $v);
                    if (!empty($t_t)) {
                        array_push($ret_qry_epic, $t_t);
                    }
                } elseif (strpos($v, 'custom_status_') !== false) {
                    $cus_s = str_replace('custom_status_', '', $v);
                    if (!empty($cus_s)) {
                        array_push($ret_qry_custom, $cus_s);
                    }
                } elseif (strpos($v, 'status_') !== false) {
                    if (str_replace('status_', '', $v) == 'done') {
                        array_push($ret_qry_status, 3);
                        array_push($ret_qry_status, 5);
                    } elseif (str_replace('status_', '', $v) == 'inprogress') {
                        array_push($ret_qry_status, 2);
                        array_push($ret_qry_status, 4);
                    } elseif (str_replace('status_', '', $v) == 'resolve') {
                        array_push($ret_qry_status, 5);
                    } else {
                        array_push($ret_qry_status, 1);
                    }
                } elseif (strpos($v, 'quk_assnto_') !== false) {
                    $ast = str_replace('quk_assnto_', '', $v);
                    array_push($ret_qry_assn, $ast);
                } elseif ($v == 'one') {
                    $threshold = (clone $now)->subHours(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif ($v == '24') {
                    $threshold = (clone $now)->subDays(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif ($v == 'week') {
                    $threshold = (clone $now)->subWeeks(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif ($v == 'month') {
                    $threshold = (clone $now)->subMonths(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif ($v == 'year') {
                    $threshold = (clone $now)->subYears(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif ($v == 'sprint_due_overdue') {
                    $midnight = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.due_date IS NOT NULL AND Easycase.due_date < '$midnight' AND (Easycase.legend !=3)";
                } elseif ($v == 'sprint_due_24') {
                    $from_d = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (clone $now)->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.due_date >= '$from_d' AND Easycase.due_date <= '$to_d'";
                } elseif ($v == 'sprint_start_one') {
                    $threshold = (clone $now)->subHours(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.gantt_start_date >= '$threshold'";
                } elseif ($v == 'sprint_start_24') {
                    $threshold = (clone $now)->subDays(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.gantt_start_date >= '$threshold'";
                } elseif ($v == 'sprint_start_week') {
                    $threshold = (clone $now)->subWeeks(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.gantt_start_date >= '$threshold'";
                } elseif ($v == 'sprint_start_month') {
                    $threshold = (clone $now)->subMonths(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.gantt_start_date >= '$threshold'";
                } elseif ($v == 'sprint_start_year') {
                    $threshold = (clone $now)->subYears(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry .= " AND Easycase.gantt_start_date >= '$threshold'";
                } elseif (!empty($v) || $is_backlog) {
                    if ($is_backlog) {
                        array_push($ret_qry_assn, $v);
                    } else {
                        $word = 'sprint_due_';
                        $words = 'sprint_start_';
                        if (strpos($v, $word) !== false) {
                            $sprint_d = explode('sprint_due_', $v);
                            $ar_dt = explode('_', trim($sprint_d[1]));
                            if ($ar_dt[0] == 'custom') {
                                $ret_qry .= '';
                            } else {
                                $frm_dt = $ar_dt['0'];
                                $to_dt = $ar_dt['1'];
                                $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($frm_dt)), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($to_dt)), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $ret_qry .= " AND Easycase.due_date >= '$from_d' AND Easycase.due_date <= '$to_d'";
                            }
                        } elseif (strpos($v, $words) !== false) {
                            $sprint_d = explode('sprint_start_', $v);
                            $ar_dt = explode('_', trim($sprint_d[1]));
                            if ($ar_dt[0] == 'custom') {
                                $ret_qry .= '';
                            } else {
                                $frm_dt = $ar_dt['0'];
                                $to_dt = $ar_dt['1'];
                                $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($frm_dt)), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($to_dt)), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $ret_qry .= " AND Easycase.gantt_start_date >= '$from_d' AND Easycase.gantt_start_date <= '$to_d'";
                            }
                        } else {
                            $ar_dt = explode('_', trim($v));
                            if (($ar_dt[2] ?? '') == 'custom') {
                                $ret_qry .= '';
                            } else {
                                $frm_dt = $ar_dt['0'];
                                $to_dt = $ar_dt['1'];
                                $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($frm_dt)), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($to_dt)), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                                $ret_qry .= " AND Easycase.dt_created >= '$from_d' AND Easycase.dt_created <= '$to_d'";
                            }
                        }
                    }
                }
            }
            if (!empty($ret_qry_assn)) {
                $PrjMod = $this->fetchTable('Projects');
                $Pusers = $PrjMod->find()
                    ->select(['User.id', 'User.uniq_id', 'User.name'])
                    ->join([
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('User.id', 'User.id')
                    ])
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUser',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUser.user_id', 'User.id')
                    ])
                    ->join([
                        'table' => 'project_users',
                        'alias' => 'ProjectUser',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id')
                    ])
                    ->where([
                        'ProjectUser.project_id' => $pid,
                        'CompanyUser.company_id' => SES_COMP,
                        'CompanyUser.is_active' => '1',
                    ])
                    ->order(['User.name' => 'ASC'])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->toArray();
                $proje_req_users = [];
                if ($Pusers) {
                    $Uids = Hash::extract($Pusers, '{n}.User.id');
                    foreach ($ret_qry_assn as $jk => $jv) {
                        if ($jv != 0) {
                            if (in_array($jv, $Uids)) {
                                array_push($proje_req_users, $jv);
                            }
                        } else {
                            if ($jv == 0) {
                                array_push($proje_req_users, $jv);
                            }
                        }
                    }
                }
                if (!empty($proje_req_users)) {
                    $ret_qry .= ' AND Easycase.assign_to IN (' . implode(',', $proje_req_users) . ')';
                }
            }
            if (!empty($ret_qry_type)) {
                $ret_qry .= ' AND Easycase.type_id IN (' . implode(',', $ret_qry_type) . ')';
            }
            if (!empty($ret_qry_epic)) {
                $ret_qry .= ' AND Easycase.epic_id IN (' . implode(',', $ret_qry_epic) . ')';
            }
            if (!empty($ret_qry_status)) {
                $ret_qry .= ' AND Easycase.legend IN (' . implode(',', $ret_qry_status) . ')';
            }
            if (!empty($ret_qry_custom)) {
                $ret_qry .= ' AND Easycase.custom_status_id IN (' . implode(',', $ret_qry_custom) . ')';
            }
        }
        return $ret_qry;
    }
    public function getPMethodology($pmid)
    {
        if ($pmid) {
            $projectMethodologyTable = $this->fetchTable('ProjectMethodologies');
            $pm = $projectMethodologyTable->find()
                ->where(['id' => $pmid])
                ->select(['title'])
                ->first();

            if ($pm) {
                return strtolower($pm->title);
            }
        }
        return null;
    }


    /*
     * Check the resource availability On
     */
    public function isWikiOn($sts = null)
    {
        return $this->formatHelper->isWikiOn($sts);
    }


    public function getlatestactivitypid($pid, $chk = null)
    {
        $dateString = '';
        $Easycase = $this->fetchTable('Easycases');
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
    public function cmnHnyCheck($data, $key)
    {
        $allHnys = Configure::read('VALID_H_POT');
        $req_chk = $allHnys[$key];
        if ($key == 'inv_eml') { // done
            if ($data['name'] !== $req_chk[0]) {
                return false;
            }
        } elseif ($key == 'order_self') { // done
            if ($data['sp'] !== $req_chk[0] || $data['se'] !== $req_chk[1]) {
                return false;
            }
        } elseif ($key == 'register_outer') { //not done
            if ($data['lastname'] !== $req_chk[0] || $data['middlename'] !== $req_chk[1]) {
                return false;
            }
        } elseif ($key == 'register') { //not done
            if ($data['lastname'] !== $req_chk[0] || $data['middlename'] !== $req_chk[1]) {
                return false;
            }
        } elseif ($key == 'refer') { // done
            if ((int) $data['name'] !== $req_chk[0] || $data['whattsapp'] !== $req_chk[1]) {
                return false;
            }
        } elseif ($key == 'tutorial') { // done
            if ($data['lastname'] !== $req_chk[0] || $data['middlename'] !== $req_chk[1]) {
                return false;
            }
        }
        return true;
    }
    public function checkmultilabel($subtask1, $is_project = null)
    {
        $subtasknotallow = [];
        $subtasknotallow1 = [];
        $parent_ids = [];
        if ($is_project == 'all') {
            foreach ($subtask1 as $k => $v) {
                $temp = $v;
                $taskid = explode('@@@', $k);
                $tid = $taskid[1];
                $pname = $taskid[0];
                $subtasknotallow1[$tid] = 1;
                $count = 0;
                do {
                    if ($count > 0 && $temp == $v) {
                        $subtasknotallow1[$tid] = 4; // This is the case for circular child and parent;
                        $parent_ids[$tid] = $v;
                        break;
                    }
                    if (isset($subtask1[$pname . '@@@' . $temp])) {
                        $subtasknotallow1[$tid] = $subtasknotallow1[$tid] + 1;
                        if ($subtasknotallow1[$tid] >= 4) {
                            break;
                        }
                        $temp = $subtask1[$pname . '@@@' . $temp];
                    } else {
                        $temp = 0;
                    }
                    $count++;
                    if ($count == 6) {
                        exit;
                    }
                } while ($temp);
            }
        } else {
            foreach ($subtask1 as $k => $v) {
                $temp = $v;
                $subtasknotallow1[$k] = 1;
                $count = 0;
                do {
                    if ($count > 0 && $temp == $v) {
                        $subtasknotallow1[$k] = 4; // This is the case for circular child and parent;
                        $parent_ids[$k] = $v;
                        break;
                    }
                    if (isset($subtask1[$temp])) {
                        $subtasknotallow1[$k] = $subtasknotallow1[$k] + 1;
                        if ($subtasknotallow1[$k] >= 4) {
                            break;
                        }
                        $temp = $subtask1[$temp];
                    } else {
                        $temp = 0;
                    }
                    $count++;
                } while ($temp);
            }
        }
        if (!empty($subtasknotallow1)) {
            foreach ($subtasknotallow1 as $k => $v) {
                if ($v > 2) {
                    //$subtasknotallow[$k] = $v;
                    $subtasknotallow[$k] = $parent_ids[$k];
                }
            }
        }
        return $subtasknotallow;
    }
    public function getCachedRoleInfo()
    {
        $arr = [];
        $curProjId = '';
        $Project = TableRegistry::getTableLocator()->get('Projects');
        $RoleAction = TableRegistry::getTableLocator()->get('RoleActions');

        $moduleNames = TableRegistry::getTableLocator()->get('Modules')
            ->find('list', ['keyField' => 'id', 'valueField' => 'name'])
            ->disableHydration()->toArray();

        $roleActionRows = $RoleAction->find()->select(['Actions.action', 'Actions.module_id', 'RoleActions.is_allowed'])->where(['RoleActions.role_id' => SES_ROLE, 'OR' => ['RoleActions.company_id IN' => [SES_COMP, 0]]])->contain(['Actions'])->disableHydration()->all()->toArray();

        $roleAccess = Hash::combine($roleActionRows, '{n}.action.action', '{n}.is_allowed');
        foreach ($roleActionRows as $rar) {
            $mid = $rar['action']['module_id'] ?? null;
            $aname = $rar['action']['action'] ?? null;
            if ($mid && $aname && isset($moduleNames[$mid])) {
                $roleAccess[$moduleNames[$mid] . '::' . $aname] = $rar['is_allowed'];
            }
        }

        // [TODO use model association]
        $module_query = "SELECT Module.name FROM modules AS Module LEFT JOIN role_modules AS RoleModule ON Module.id = RoleModule.module_id WHERE RoleModule.role_id = '" . SES_ROLE . "'";
        $connection = $RoleAction->getConnection();
        $results = $connection->execute($module_query)->fetchAll('assoc');
        $module_list = Hash::extract($results, '{n}.name');
        if (SES_ROLE == 699) {
            $GuestRoleAction = TableRegistry::getTableLocator()->get('GuestRoleActions');
            $guest_role_action = $GuestRoleAction->find()->where(['GuestRoleActions.company_id' => SES_COMP, 'GuestRoleActions.role_id' => SES_ROLE])->first();
            if (!empty($guest_role_action)) {
                $Action = TableRegistry::getTableLocator()->get('Actions');
                $action_lists = $Action->find('list', ['keyField' => 'id', 'valueField' => 'action'])->toArray();
                $new_role_access = [];
                $roleAccess_arr = json_decode($guest_role_action['action_details'], true);
                foreach ($roleAccess_arr as $kr => $vr) {
                    if (array_key_exists($kr, $action_lists)) {
                        $new_role_access[$action_lists[$kr]] = $vr;
                    }
                }
                $roleAccess = $new_role_access;
            }
            $guestroleAccess = $roleAccess;
        }
        $arr['roleAccess'][0] = $roleAccess;
        $arr['module_list'] = $module_list;

        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        $Project = TableRegistry::getTableLocator()->get('Projects');
        $arrProjectIds = $ProjectUser->find()
            ->select(['project_id' => 'pu.project_id', 'project_uniq_id' => 'p.uniq_id'])
            ->from(['pu' => $ProjectUser->getTable()])
            ->join([
                'p' => [
                    'table' => $Project->getTable(),
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('p.id', 'pu.project_id'),
                    ],
                ],
            ])
            ->where([
                'pu.user_id' => SES_ID,
                'p.isactive' => 1,
                'p.company_id' => SES_COMP,
            ])->disableHydration()
            ->toArray();
        $projectIdList = Hash::combine($arrProjectIds, '{n}.project_id', '{n}.project_uniq_id');
        $arrProjectIds = Hash::extract($arrProjectIds, '{n}.project_id');

        $Role = TableRegistry::getTableLocator()->get('Roles');

        $allRoleActions = [];
        if (!empty($arrProjectIds)) {
            $allRoleActions = $ProjectUser->find()
                ->contain([
                    'Roles' => [
                        'RoleActions' => ['Actions']
                    ]
                ])
                ->select([
                    'ProjectUsers.role_id',
                    'Roles.id',
                    'ProjectUsers.project_id',
                ])
                ->where(['ProjectUsers.project_id IN' => $arrProjectIds, 'ProjectUsers.user_id' => SES_ID])
                ->disableHydration()
                ->toArray();
        }

        if (!empty($allRoleActions)) {
            foreach ($allRoleActions as $k => $v) {
                $innrArr = [];
                if (!empty($v['role']['role_actions'])) {
                    foreach ($v['role']['role_actions'] as $k1 => $v1) {
                        if (!empty($v1['action'])) {
                            $innrArr[$v1['action']['action']] = $v1['is_allowed'];
                            $mid = $v1['action']['module_id'] ?? null;
                            if ($mid && isset($moduleNames[$mid])) {
                                $innrArr[$moduleNames[$mid] . '::' . $v1['action']['action']] = $v1['is_allowed'];
                            }
                        }
                    }
                }
                $arr['roleAccess'][$projectIdList[$v['project_id']]] = $innrArr;
                if (SES_ROLE == 699) {
                    $arr['roleAccess'][$projectIdList[$v['project_id']]] = $guestroleAccess;
                }
            }
        }

        $userRoleAccess = [];
        foreach ($roleAccess as $ki => $vi) {
            $ki = str_replace(' ', '_', $ki);
            $userRoleAccess[$ki] = $vi;
        }
        $arr['userRoleAccess'] = $userRoleAccess;
        Cache::write('userRole' . SES_COMP . '_' . SES_ID, $arr);
    }

    /* Check user role */
    public function isAllowed($action, $project_id = 0, $company = 0)
    {
        // Check if SES_TYPE constant is defined before using it
        $sesType = defined('SES_TYPE') ? SES_TYPE : 0;
        
        if (($sesType == 2 || $sesType == 1) && $action != 'Change Due Date Reason') {
            return true;
        }
        
        // Check if SES_COMP and SES_ID constants are defined
        $sesComp = defined('SES_COMP') ? SES_COMP : 0;
        $sesId = defined('SES_ID') ? SES_ID : 0;
        
        $userRoleInfo = Cache::read('userRole' . $sesComp . '_' . $sesId);
        
        // Check if cache data exists and has roleAccess key
        if (empty($userRoleInfo) || !isset($userRoleInfo['roleAccess'])) {
            return false;
        }
        
        $userRoleAccess = $userRoleInfo['roleAccess'];

        return $this->formatHelper->isAllowed($action, $userRoleAccess, $project_id, $company);

    }
    /* Google Calendar integration starts Here */
    public function GetAccessToken($client_id, $redirect_uri, $client_secret, $code)
    {
        $url = 'https://accounts.google.com/o/oauth2/token';

        $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code=' . $code . '&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            throw new Exception('Error : Failed to receieve access token');
        }

        return $data;
    }
    public function createNewGoogleCalendar($name, $token = null)
    {
        return null;
    }

    public function createGoogleCalendarEvent($eid, $task = [], $type = null, $token = null)
    {
        return null;
    }
    public function createChannel($calID, $token = null)
    {
        return null;
    }
    public function updateGoogleEvents($cal_id)
    {
        return null;
    }
    public function secondsToFormat($ss)
    {
        $h = floor($ss / 3600);
        $m = floor($ss / 60 % 60);
        if ($h && $m) {
            return "$h:$m";
        } elseif ($h) {
            return "$h:0";
        } elseif ($m) {
            return "0:$m";
        } else {
            return '0';
        }
    }
    /**
     * Checks if a project has a custom task status by retrieving its status group ID.
     *
     * @param mixed $pid The project identifier
     * @param string $column The column name to match against the project ID
     * @return int The status group ID if found, 0 otherwise
     */
    public function hasCustomTaskStatus($pid, $column)
    {
        $result = $this->fetchTable('Projects')
            ->find()
            ->select(['status_group_id'])
            ->where([$column => $pid])
            ->disableHydration()
            ->first();

        return $result['status_group_id'] ?? 0;
    }

    /*
    function to check whether the project has custom defect status group
    */
    public function hasCustomDefectStatus($pid, $column)
    {
        $projectsTable = $this->fetchTable('Projects');

        $result = $projectsTable->find()
            ->select(['defect_status_group_id'])
            ->where([$column => $pid])
            ->first();

        return $result ? $result->defect_status_group_id : 0;
    }

    public function getStatusGroups($comp_id)
    {
        $sql = 'SELECT StatusGroup.id, StatusGroup.name FROM status_groups AS StatusGroup WHERE StatusGroup.company_id IN(' . $comp_id . ', 0) AND StatusGroup.parent_id = 0 ORDER BY StatusGroup.is_default DESC, CASE WHEN StatusGroup.is_default = 0 THEN StatusGroup.name ELSE CAST(StatusGroup.id AS VARCHAR) END ASC';
        $connection = ConnectionManager::get('default');
        $wf_list = $connection->execute($sql)->fetchAll('assoc');
        return (!empty($wf_list)) ? $wf_list : [];
    }

    public function getCustomTaskStatus($sts_grp_id = null, $type = 'all', $col_id = null, $ord = 'ASC')
    {
        $customStatusTable = $this->fetchTable('CustomStatuses');
        if ($sts_grp_id == 0) {
            /*
             * A project with no workflow of its own falls back to the seeded
             * "Default Status Workflow". This used to read a config key that is
             * defined nowhere in the app, so it returned null and every caller
             * got an empty status list.
             */
            $sts_grp_id = (int)Configure::read('OS_DEFAULT_STS_GROUP_ID');
            if (!$sts_grp_id) {
                $defaultGroup = $this->fetchTable('StatusGroups')->find()
                    ->select(['id'])
                    ->where(['name' => 'Default Status Workflow'])
                    ->order(['id' => 'ASC'])
                    ->disableHydration()
                    ->first();
                $sts_grp_id = (int)($defaultGroup['id'] ?? 1);
            }
        }
        if ($col_id && $sts_grp_id) {
            if ($sts_grp_id == -1) {
                $cond = ['CustomStatus.company_id' => SES_COMP, 'CustomStatus.id' => $col_id];
            } else {
                $cond = ['CustomStatus.status_group_id' => $sts_grp_id, 'CustomStatus.id' => $col_id];
            }
            $type = 'first';
        } elseif ($sts_grp_id) {
            if ($sts_grp_id == -1) {
                $projectsTable = $this->fetchTable('Projects');
                $allstsgrps = $projectsTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'status_group_id'
                ])->where([
                            'company_id' => SES_COMP,
                            'isactive' => 1
                        ])->toArray();
                if ($allstsgrps) {
                    $arrIDs = array_unique(array_values($allstsgrps));
                    $cond = ['CustomStatus.company_id' => SES_COMP, 'CustomStatus.status_group_id IN' => $arrIDs];
                } else {
                    $cond = ['CustomStatus.company_id' => SES_COMP];
                }
            } else {
                $cond = ['CustomStatus.status_group_id' => $sts_grp_id];
            }
        } elseif ($col_id) {
            $cond = ['CustomStatus.id' => $col_id];
            $type = 'first';
        }
        $CustomStatusArr = $customStatusTable->find('all', ['conditions' => $cond, 'order' => ['CustomStatus.seq' => $ord]])
            ->join([
                'table' => 'custom_statuses',
                'alias' => 'CustomStatus',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('CustomStatuses.id', 'CustomStatus.id')],
            ])->disableHydration();

        $CustomStatusArr = $type == 'first' ? $CustomStatusArr->first() : $CustomStatusArr->toArray();

        return $CustomStatusArr;
    }
    /*pending status filter*/
    public function getCustomPendingTaskStatus($sts_grp_id = null, $type = 'all', $col_id = null, $ord = 'ASC')
    {
        $CustomStatus = ClassRegistry::init('CustomStatus');
        if ($col_id && $sts_grp_id) {
            if ($sts_grp_id == -1) {
                $cond = ['CustomStatus.company_id' => SES_COMP, 'CustomStatus.id' => $col_id, 'CustomStatus.status_master_id' => [1, 2, 3]];
            } else {
                $cond = ['CustomStatus.status_group_id' => $sts_grp_id, 'CustomStatus.id' => $col_id, 'CustomStatus.status_master_id' => [1, 2, 3]];
            }
            $type = 'first';
        } elseif ($sts_grp_id) {
            if ($sts_grp_id == -1) {
                $cond = ['CustomStatus.company_id' => SES_COMP, 'CustomStatus.status_master_id' => [1, 2, 3]];
            } else {
                $cond = ['CustomStatus.status_group_id' => $sts_grp_id, 'CustomStatus.status_master_id' => [1, 2, 3]];
            }
        } elseif ($col_id) {
            $cond = ['CustomStatus.id' => $col_id, 'CustomStatus.status_master_id' => [1, 2, 3]];
            $type = 'first';
        }
        $CustomStatusArr = $CustomStatus->find($type, ['conditions' => $cond, 'order' => ['CustomStatus.seq' => $ord]]);
        return $CustomStatusArr;
    }
    //get the custom task tis detail by id
    public function getStatusMasterId($sts_id, $proj_id, $chk_legnd = 0)
    {
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $projectsTable = $this->fetchTable('Projects');
        if ($sts_id) {
            if ($chk_legnd) {
                $dtlProj = $projectsTable->getProjectFields(['Projects.id' => $proj_id], ['Projects.status_group_id']);
                $cond = [
                    'CustomStatuses.company_id' => SES_COMP,
                    'CustomStatuses.status_group_id' => $dtlProj['status_group_id']
                ];

                if ($sts_id < 7) {
                    $cond['CustomStatuses.status_master_id'] = $sts_id;
                } else {
                    $cond['CustomStatuses.id'] = $sts_id;
                }

            } else {
                $cond = ['CustomStatuses.company_id' => SES_COMP, 'CustomStatuses.id' => $sts_id];
            }
            $query = $customStatusesTable->find()
                ->where($cond)
                ->order(['CustomStatuses.seq' => 'ASC']);

            $result = $query->disableHydration()->first();
            return $result;
        }
        return false;
    }

    public function getStatusByProject($pid)
    {
        $projectsTable = $this->fetchTable('Projects');
        $query = $projectsTable->find()
            ->where([
                'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                'Projects.company_id' => SES_COMP
            ]);

        if ($pid !== 'all') {
            $query->andWhere(fn($exp) => $exp->eq('Projects.id', $pid));
        } else {
            $query->andWhere(fn($exp) => $exp->notEq('Projects.status_group_id', 0));
        }
        $query->contain([
            'StatusGroups.CustomStatuses' => [
                'sort' => ['CustomStatuses.seq' => 'ASC']
            ]
        ]);
        return $query->disableHydration()->toArray() ?? [];
    }



    public function getStatusByProject1($pid)
    {
        $Project = ClassRegistry::init('Project');
        $StatusGroup = ClassRegistry::init('StatusGroup');
        $StatusGroup->bindModel(['hasMany' => ['CustomStatus' => ['fields' => ['CustomStatus.id', 'CustomStatus.name', 'CustomStatus.color', 'CustomStatus.status_master_id'], 'order' => ['CustomStatus.seq' => 'ASC']]]]);
        $Project->bindModel(['belongsTo' => ['StatusGroup' => ['contains' => ['CustomStatus'], 'fields' => ['StatusGroup.id']]]]);
        if ($pid != 'all') {
            $allCSByProj = $Project->find('all', ['conditions' => ['Project.isactive' => 1, 'Project.company_id' => SES_COMP, 'Project.id' => $pid], 'recursive' => 2]);
        } else {
            $allCSByProj = $Project->find('all', ['conditions' => ['Project.isactive' => 1, 'Project.company_id' => SES_COMP, 'Project.status_group_id !=' => 0], 'recursive' => 2]);
        }

        return $allCSByProj;
    }
    public function getCustomStatusProj($customStatus, $proj_id, $sts_id)
    {
        if ($customStatus && $proj_id && isset($customStatus[$proj_id])) {
            foreach ($customStatus[$proj_id]['CustomStatus'] as $ks => $vs) {
                if ($sts_id == $vs['id']) {
                    return $vs;
                }
            }
        }
        return [];
    }


    public function getCustomStatusProj1($customStatus, $proj_id, $sts_id)
    {
        if ($customStatus && $proj_id && isset($customStatus[$proj_id])) {
            foreach ($customStatus[$proj_id]['custom_statuses'] as $ks => $vs) {
                if ($sts_id == $vs['id']) {
                    return $vs;
                }
            }
        }
        return [];
    }
    public function preprCustomKanban($customStatus, $key)
    {
        //Hash::combine($get_od_todos, '{n}.Easycase.eid', '{n}.Easycase.parent_task_id')
        if ($customStatus) {
            $retStst_arr = [];
            foreach ($customStatus as $k => $v) {
                $retStst_arr[$key . $v['CustomStatus']['id']] = $v['CustomStatus'];
            }
            return $retStst_arr;
        }
        return [];
    }
    public function isGoogleSyncOn($company_id, $user_id, $type = '1')
    {
        return $this->formatHelper->isGoogleSyncOn($company_id, $user_id, $type);
    }
    public function isGitsyncOn($company_id, $chk = 0)
    {
        return $this->formatHelper->isGitsyncOn($company_id, $chk);
    }
    public function check_in_date_range($start_date, $end_date, $date_from_user)
    {
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }
    public function strip_tags_deep($arr)
    {
        $res_arr = is_array($arr) ? array_map([$this, 'strip_tags_deep'], $arr) : strip_tags($arr);
        return $res_arr;
    }
    public function getTaskPermalink($projShortName, $taskNo)
    {
        return strtoupper($projShortName) . '#' . $taskNo;
    }


    /*For AMP Page end*/
    public function deleteCustomStatusGroup($id)
    {
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $customStatusesTable->deleteCustomStatusGroup($id);
        return true;
    }

    public function setLeftMenu()
    {
        if (SES_COMP) {
            $cacheKey = 'userMenu' . SES_COMP . '_' . SES_ID;
            $arr = Cache::read($cacheKey);
            if (null == $arr) {
                $arr = [];
                $menusTable = $this->fetchTable('Menus');
                $userMenusTable = $this->fetchTable('UserMenus');
                $userMenu = $userMenusTable->find()
                    ->where([
                        'user_id' => SES_ID,
                        'company_id' => SES_COMP
                    ])->disableHydration()->first();
                if (empty($userMenu)) {
                    $this->insertLeftMenu(SES_COMP, SES_ID);
                    $userMenu = $userMenusTable->find()
                        ->where([
                            'user_id' => SES_ID,
                            'company_id' => SES_COMP
                        ])->disableHydration()->first();
                }
                $arr['allUsermenus'] = json_decode($userMenu['menu'], true);
                $menus = $menusTable->find()->disableHydration()->toArray();
                foreach ($menus as $k => $v) {
                    $arr['menus'][$v['id']] = $v;
                }
                Cache::write($cacheKey, $arr);
            }
            return $arr;
        }
    }
    public function insertLeftMenu($company_id, $user_id)
    {
        $Menu = $this->fetchTable('Menus');
        $UserMenu = $this->fetchTable('UserMenus');
        $isExists = $UserMenu->find()->where(['UserMenus.company_id' => $company_id, 'UserMenus.user_id' => $user_id])->count();
        if (!$isExists) {
            $UserSidebar = $this->fetchTable('UserSidebarMenus');
            $clms = $UserSidebar->readmenudataDetlfromCache($user_id, $company_id);

            if (!empty($clms)) {
                $allM = $Menu->find('list', ['keyField' => 'id', 'valueField' => 'name', 'conditions' => ['parent_id' => 0], 'order' => ['menu_order' => 'ASC']])->all()->toArray();

                foreach ($allM as $k => $v) {
                    $allM[$k] = strtolower($v);
                }
                $ids = [0];
                foreach ($clms['checked_left_menu'] as $k => $v) {
                    $menuKey = strtolower((string)$v);
                    if (in_array($menuKey, $allM, true)) {
                        $ids[] = array_search($menuKey, $allM, true);
                    }
                }
                $getAllMenus = $Menu->find('threaded')
                    ->select(['id', 'parent_id'])
                    ->where([
                        'OR' => [
                            'Menu.id IN' => $ids,
                            'Menu.parent_id IN' => $ids
                        ]
                    ])
                    ->order(['menu_order' => 'ASC'])->all();
            } else {
                $getAllMenus = $Menu->find('threaded')
                    ->select(['id', 'parent_id'])
                    ->where(['default_menu' => 1])
                    ->order(['menu_order' => 'ASC'])->all();
            }
            $ga = [];
            foreach ($getAllMenus as $k => $v) {
                $ga[$k]['id'] = $v['id'];
                if (!empty($v['children'])) {
                    foreach ($v['children'] as $k1 => $v1) {
                        $ga[$k]['children'][$k1]['id'] = $v1['id'];
                    }
                }
            }
            $userMenuEntity = $UserMenu->newEmptyEntity();
            $userMenuEntity->user_id = $user_id;
            $userMenuEntity->company_id = $company_id;
            $userMenuEntity->menu = json_encode($ga);
            $UserMenu->save($userMenuEntity);
        }
    }
    public function getParentTaskUnid($case_no, $proj_id, $unid)
    {

        $Easycase = $this->fetchTable('Easycases');
        $ECDT = $Easycase->find()
            ->where([
                'case_no' => $case_no,
                'project_id' => $proj_id,
                'istype' => 1
            ])
            ->first();
        if ($ECDT) {
            $unid = $ECDT->uniq_id;
        }
        return $unid;
    }

    public function getStorageCount($user_count)
    {
        if ($user_count <= 14) {
            return 5120;
        } elseif ($user_count <= 24) {
            return 10240;
        } elseif ($user_count <= 54) {
            return 20480;
        } elseif ($user_count <= 100) {
            return 51200;
        } elseif ($user_count <= 200) {
            return 102400;
        } elseif ($user_count > 200) {
            return 153600;
        }
    }
    /*
    Author:C pattnaik
    function to check  given string contains multibyte character or not
    returns true if there is a multibyte character else false
    */
    public function contains_any_multibyte($string)
    {
        return !mb_check_encoding($string, 'ASCII') && mb_check_encoding($string, 'UTF-8');
    }

    /*
    Author:c pattnaik
    function to check usser restriction to access Defect module logic
    */
    public function isAllowedDefectModule()
    {
        return $this->formatHelper->isAllowedDefectModule();
    }
    /*
       Author:C pattnaik
      * returns the status name or color of default status
      */
    public function getDefaultStatus($sts, $type)
    {
        $name_arry = [1 => 'New', 2 => 'In-Progress', 3 => 'Closed', 5 => 'Resolved'];
        $color_arry = [1 => '#f19a91', 2 => '#8dc2f8', 3 => '#f3c788', 5 => '#8ad6a3'];
        if ($type == 'name') {
            return $name_arry[$sts];
        } else {
            return $color_arry[$sts];
        }
    }

    public function getNewlinesInsingle($inpt = null)
    {
        if ($inpt) {
            $inpt = trim(preg_replace('/\s+/', ' ', $inpt));
        }
        return $inpt;
    }
    /**
     * isAllowZapier
     * @author bijaya
     * @param  mixed $company_id
     * @return int
     * this function check whether user is allowed to access zpaier or not
     */
    public function isAllowZapier($company_id)
    {
        return 0;
    }

    public function isZoomOn()
    {
        return 0;
    }
    public function getDatesFromRange($start_date, $due_date, $format = 'Y-m-d')
    {
        $array = [];

        // Variable that store the date interval
        // of period 1 day
        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($due_date);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start_date), $interval, $realEnd);

        // Use loop to store date into array
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        // Return the array elements
        return $array;
    }
    public function getEpicId()
    {
        $Type = $this->fetchTable('Types');
        $epic = $Type->find()
            #->select(['id'])
            ->where(['name' => 'Epic', 'company_id' => 0, 'project_id' => 0])
            ->disableHydration()
            ->first();
        return !empty($epic) ? $epic['id'] : 0;
    }
    public function applyWorkflowAutomation($pid, $eid, $value, $otype)
    {
        if ($otype && !in_array($otype, ['type', 'status'])) {
            return;
        }
        $workflowsTable = $this->fetchTable('Workflows');
        $workflowDetailsTable = $this->fetchTable('WorkflowDetails');

        $conditionId = $this->getWorkflowConditionId($otype);
        $workflows = $workflowsTable->find()
            ->select($workflowDetailsTable)
            ->select($workflowsTable)
            ->where(['Workflows.project_id IN' => [$pid, 0], 'WorkflowDetails.workflow_condition_id' => $conditionId])
            ->join([
                'table' => 'workflow_details',
                'alias' => 'WorkflowDetails',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Workflows.id', 'WorkflowDetails.workflow_id')],
            ])
            ->disableHydration()
            ->toArray();

        foreach ($workflows as $workflow_key => $workflow_value) {
            $conditionarray = $action_array = [];
            if (!empty($workflow_value['WorkflowDetails'])) {
                if (!empty($workflow_value['WorkflowDetails']['condition_details'])) {
                    $conditionarray = json_decode($workflow_value['WorkflowDetails']['condition_details'], true);
                }
                if (!empty($workflow_value['WorkflowDetails']['action_details'])) {
                    $action_array = json_decode($workflow_value['WorkflowDetails']['action_details'], true);
                }
            }
            $checkcondition = ($conditionarray[0]['operation']) ? $conditionarray[0]['value'] != $value : $conditionarray[0]['value'] == $value;
            if ($checkcondition) {
                $this->sendWorkFlowAction($action_array, $eid);
            }
        }
    }
    public function getWorkflowConditionId($otype)
    {
        $id = 0;
        switch ($otype) {
            case 'type':
                $id = 1;
                break;
            case 'status':
                $id = 2;
                break;
        }
        return $id;
    }
    public function sendWorkFlowAction($action, $eid)
    {
        switch ($action[0]['action']) {
            case 1:
                $rawMessage = (string)($action[0]['value']['action_box'] ?? '');
                $subject = 'Orangescrum Workflow automation message';
                $usersTable = $this->fetchTable('Users');
                $touserDetailsQuery = $usersTable->find();
                $touserdetails = $touserDetailsQuery
                    ->select(['email', 'name'])
                    ->where(['id' => $action[0]['value']['workflow_action_to']])
                    ->disableHydration()
                    ->first();
                if ($action[0]['value']['workflow_action_cc']) {
                    $ccuserDetailsQuery = $usersTable->find();
                    $ccuserdetails = $ccuserDetailsQuery
                        ->select(['email', 'name'])
                        ->where(['id' => $action[0]['value']['workflow_action_cc']])
                        ->disableHydration()
                        ->first();
                } else {
                    $ccuserdetails = [];
                }

                $actorName = '';
                if (defined('SES_ID')) {
                    $actor = $usersTable->find()
                        ->select(['name'])
                        ->where(['id' => SES_ID])
                        ->disableHydration()
                        ->first();
                    $actorName = (string)($actor['name'] ?? '');
                }
                $workflowName = (string)($action[0]['value']['workflow_action_name'] ?? '');
                $docTitle = '';
                $caseUniqId = '';
                $caseNo = '';
                $projName = '';
                $cseTyp = '';
                $priRity = '';
                if (!empty($eid)) {
                    $easycase = $this->fetchTable('Easycases')->find()
                        ->select([
                            'title' => 'Easycases.title',
                            'uniq_id' => 'Easycases.uniq_id',
                            'case_no' => 'Easycases.case_no',
                            'priority' => 'Easycases.priority',
                            'proj_name' => 'Projects.name',
                            'proj_short' => 'Projects.short_name',
                            'type_name' => 'Types.name',
                        ])
                        ->join([
                            ['table' => 'projects', 'alias' => 'Projects', 'type' => 'LEFT', 'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id')],
                            ['table' => 'types', 'alias' => 'Types', 'type' => 'LEFT', 'conditions' => fn($exp) => $exp->equalFields('Types.id', 'Easycases.type_id')],
                        ])
                        ->where(['Easycases.id' => $eid])
                        ->disableHydration()
                        ->disableResultsCasting()
                        ->first();
                    $docTitle = (string)($easycase['title'] ?? '');
                    $caseUniqId = (string)($easycase['uniq_id'] ?? '');
                    $projName = (string)($easycase['proj_name'] ?? '');
                    $cseTyp = (string)($easycase['type_name'] ?? '');
                    $caseNo = ($easycase['proj_short'] ?? '') !== ''
                        ? $easycase['proj_short'] . '-' . ($easycase['case_no'] ?? '')
                        : (string)($easycase['case_no'] ?? '');
                    // Mirror the priority badge markup used by the task-activity emails.
                    $priColor = '#AD9227';
                    $priText = __('LOW');
                    if ((string)($easycase['priority'] ?? '') === '0') {
                        $priColor = '#AE432E';
                        $priText = __('HIGH');
                    } elseif ((string)($easycase['priority'] ?? '') === '1') {
                        $priColor = '#28AF51';
                        $priText = __('MEDIUM');
                    }
                    $priRity = "<font color='#737373'><b>" . __('Priority') . ":</b></font> <font style='color:{$priColor};'>" . $priText . '</font>';
                }

                $mailer = new Mailer(Configure::read('AppEmail.transport'));
                $mailer->setFrom(Configure::read('AppEmail.from_email'));
                $mailer->setTo($touserdetails['email']);
                if (!empty($ccuserdetails['email'])) {
                    $mailer->setCc($ccuserdetails['email']);
                }
                $mailer->setSubject($subject);
                $mailer->setViewVars(['message_text' => $rawMessage, 'home_url' => HTTP_ROOT]);
                $mailer->setEmailFormat('html');
                $mailer->viewBuilder()->setTemplate('workflow_action');
                try {
                    $wfCtaUrl = rtrim(HTTP_ROOT, '/') . '/dashboard/#details/' . (string)($caseUniqId !== '' ? $caseUniqId : $eid);
                    $wfCompanyId = defined('SES_COMP') ? (int)SES_COMP : null;
                    $wfMessageHtml = nl2br(htmlspecialchars($rawMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                    TemplatedMailer::deliver($mailer, 'workflow_action', $wfCompanyId, [
                        'recipientName' => (string)($touserdetails['name'] ?? ''),
                        'userName' => (string)($touserdetails['name'] ?? ''),
                        'actorName' => $actorName,
                        'docTitle' => $docTitle,
                        'workflowName' => $workflowName,
                        'message_text' => $wfMessageHtml,
                        'respond' => $wfMessageHtml,
                        'case_title' => $docTitle,
                        'case_no' => $caseNo,
                        'case_uniq_id' => $caseUniqId,
                        'projName' => $projName,
                        'cseTyp' => $cseTyp,
                        'priRity' => $priRity,
                        'ctaUrl' => $wfCtaUrl,
                        'companyName' => \EmailTemplating\Service\GlobalSettings::companyName($wfCompanyId),
                    ], $subject);
                } catch (Exception $e) {
                }
                break;
            case 2:
                $easycasesTable = $this->fetchTable('Easycases');
                $query = $easycasesTable->updateQuery();
                $query->set([
                    'assign_to' => $action[0]['value']['workflow_action_user'],
                    'dt_created' => new FrozenTime(GMT_DATETIME),
                    'case_count' => $query->newExpr('case_count + 1'),
                    'updated_by' => SES_ID,
                ])
                    ->where(['id' => $eid, 'isactive' => 1])
                    ->execute();
                break;
        }
        return true;
    }
    public function SaveEventTrackUsingCURL($sessionEventName, $sessionReferName, $sessionUserID)
    {
        return true;
    }

    public function insertModel(string $oldModel, array $data): array
    {
        $formattedArray = [];
        foreach ($data as $key => $val) {
            $formattedArray[$key][$oldModel] = $val;
        }
        return $formattedArray;
    }

    public function ldapLogin($uname, $psd)
    {
        $ldapinformation = [];
        foreach ($ldapinformation as $key => $ldapinfo) {
            $ldap_dn = $ldapinfo['ldapdn'];
            $ldap_password = $ldapinfo['ldapassword'];
            $ldap_con = ldap_connect($ldapinfo['ldapserverip']);
            ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
            if (ldap_bind($ldap_con, $ldap_dn, $ldap_password)) {
                if ($ldapinfo['ldapattname']) {
                    #$filter = "(uid=" . $uname . ")";
                    $filter = '(' . $ldapinfo['ldapattname'] . '=' . $uname . ')';
                    $result = ldap_search($ldap_con, $ldapinfo['ldaptree'], $filter) or exit('Unable to search');
                    $entries = ldap_get_entries($ldap_con, $result);
                    $entries['ldapattname'] = $ldapinfo['ldapattname'];
                } else {
                    $filter = '(uid=' . $uname . ')';
                    $result = ldap_search($ldap_con, $ldapinfo['ldaptree'], $filter) or exit('Unable to search');
                    $entries = ldap_get_entries($ldap_con, $result);
                    $entries['ldapattname'] = 'uid';
                    if (empty($entries[0]['dn'])) {
                        $filter = '(userprincipalname=' . $uname . ')';
                        $result = ldap_search($ldap_con, $ldapinfo['ldaptree'], $filter) or exit('Unable to search');
                        $entries = ldap_get_entries($ldap_con, $result);
                        $entries['ldapattname'] = 'userprincipalname';
                        if (empty($entries[0]['dn'])) {
                            $filter = '(mail=' . $uname . ')';
                            $result = ldap_search($ldap_con, $ldapinfo['ldaptree'], $filter) or exit('Unable to search');
                            $entries = ldap_get_entries($ldap_con, $result);
                            $entries['ldapattname'] = 'mail';
                        }
                    }
                }
                if ($entries['count'] != 0 && ldap_bind($ldap_con, $entries[0]['dn'], $psd)) {
                    $entries[0]['ldapattname'] = $entries['ldapattname'];
                    return $entries[0];
                    exit;
                } else {
                    ldap_close($ldap_con);
                    continue;
                }
            } else {
                ldap_close($ldap_con);
                continue;
            }
        }
        return false;
    }

    public function isWikiEnabled()
    {
        return $this->formatHelper->isWikiEnabled();
    }
    public function dateFormatOutputdateTime_day($date_time, $curdate = null, $type = null, $is_month_last = 0, $viewtype = '')
    {
        return $this->formatHelper->dateFormatOutputdateTime_day($date_time, $curdate, $type, $is_month_last, $viewtype);
    }

    public function getActiveSprintFilter($filters, $pid, $is_backlog = 0)
    {
        $ret_qry = [];
        $ret_qry_assn = [];
        $ret_qry_type = [];
        $ret_qry_status = [];
        $ret_qry_custom = [];
        $ret_qry_epic = [];

        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $ymdHisFormat = 'Y-m-d H:i:s';
        $now = new FrozenTime('now', $toTz);
        $GMT_DATE = GMT_DATETIME;

        if (!empty($filters)) {
            foreach ($filters as $k => $v) {
                if ($v == 'me') {
                    array_push($ret_qry_assn, SES_ID);
                } elseif ($v == 'last') {
                    $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' -1 day'));
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $day_date,
                    ];
                } elseif (strpos($v, 'types_') !== false) {
                    $t_t = str_replace('types_', '', $v);
                    if (!empty($t_t)) {
                        array_push($ret_qry_type, $t_t);
                    }
                } elseif (strpos($v, 'epic_') !== false) {
                    $t_t = str_replace('epic_', '', $v);
                    if (!empty($t_t)) {
                        array_push($ret_qry_epic, $t_t);
                    }
                } elseif (strpos($v, 'custom_status_') !== false) {
                    $cus_s = str_replace('custom_status_', '', $v);
                    if (!empty($cus_s)) {
                        array_push($ret_qry_custom, $cus_s);
                    }
                } elseif (strpos($v, 'status_') !== false) {
                    if (str_replace('status_', '', $v) == 'done') {
                        array_push($ret_qry_status, 3);
                        array_push($ret_qry_status, 5);
                    } elseif (str_replace('status_', '', $v) == 'inprogress') {
                        array_push($ret_qry_status, 2);
                        array_push($ret_qry_status, 4);
                    } elseif (str_replace('status_', '', $v) == 'resolve') {
                        array_push($ret_qry_status, 5);
                    } else {
                        array_push($ret_qry_status, 1);
                    }
                } elseif (strpos($v, 'quk_assnto_') !== false) {
                    $ast = str_replace('quk_assnto_', '', $v);
                    array_push($ret_qry_assn, $ast);
                } elseif ($v == 'one') {
                    $one_date = date('Y-m-d H:i:s', strtotime($GMT_DATE) - 3600);
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $one_date,
                    ];
                } elseif ($v == '24') {
                    $day_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 day'));
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $day_date,
                    ];
                } elseif ($v == 'week') {
                    $week_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 week'));
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $week_date,
                    ];
                } elseif ($v == 'month') {
                    $month_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 month'));
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $month_date,
                    ];
                } elseif ($v == 'year') {
                    $year_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 year'));
                    $ret_qry[] = [
                        'Easycase.dt_created >=' => $year_date,
                    ];
                } elseif ($v == 'sprint_due_overdue') {
                    $today_start = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry[] = [
                        'Easycase.due_date <' => $today_start,
                        'Easycase.due_date IS NOT' => null,
                        'Easycase.legend !=' => 3,
                    ];
                } elseif ($v == 'sprint_due_24') {
                    $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' +1 day'));
                    $from_d = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (clone $now)->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $ret_qry[] = [
                        'Easycases.due_date >=' => $from_d,
                        'Easycases.due_date <=' => $to_d,
                    ];
                } elseif ($v == 'sprint_start_one') {
                    $one_date = date('Y-m-d H:i:s', strtotime($GMT_DATE) - 3600);
                    $ret_qry[] = [
                        'Easycase.gantt_start_date >=' => $one_date,
                    ];
                } elseif ($v == 'sprint_start_24') {
                    $day_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 day'));
                    $ret_qry[] = [
                        'Easycase.gantt_start_date >=' => $day_date,
                    ];
                } elseif ($v == 'sprint_start_week') {
                    $week_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 week'));
                    $ret_qry[] = [
                        'Easycase.gantt_start_date >=' => $week_date,
                    ];
                } elseif ($v == 'sprint_start_month') {
                    $month_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 month'));
                    $ret_qry[] = [
                        'Easycase.gantt_start_date >=' => $month_date,
                    ];
                } elseif ($v == 'sprint_start_year') {
                    $year_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 year'));
                    $ret_qry[] = [
                        'Easycase.gantt_start_date >=' => $year_date,
                    ];
                } elseif (!empty($v) || $is_backlog) {
                    if ($is_backlog) {
                        array_push($ret_qry_assn, $v);
                    } else {
                        $word = 'sprint_due_';
                        $words = 'sprint_start_';
                        if (strpos($v, $word) !== false) {
                            $sprint_d = explode('sprint_due_', $v);
                            $ar_dt = explode('_', trim($sprint_d[1]));
                            if ($ar_dt[0] == 'custom') {
                            } else {
                                [$from_d, $to_d] = array_map(
                                    fn($date) => (new FrozenTime(date($ymdHisFormat, strtotime($date)), $toTz)),
                                    $ar_dt
                                );
                                $from_d = $from_d->startOfDay();
                                $to_d = $to_d->endOfDay();
                                $from_d = $from_d->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = $to_d->setTimezone('UTC')->format($ymdHisFormat);
                                if ($from_d && $to_d) {
                                    $conditions_dt[] = ['Easycases.due_date >=' => $from_d];
                                    if ($from_d !== $to_d) {
                                        $conditions_dt[] = ['Easycases.due_date <=' => $to_d];
                                    }
                                }
                                $ret_qry[] = $conditions_dt;
                            }
                        } elseif (strpos($v, $words) !== false) {
                            $sprint_d = explode('sprint_start_', $v);
                            $ar_dt = explode('_', trim($sprint_d[1]));
                            if ($ar_dt[0] == 'custom') {
                            } else {
                                [$from_d, $to_d] = array_map(
                                    fn($date) => (new FrozenTime(date($ymdHisFormat, strtotime($date)), $toTz)),
                                    $ar_dt
                                );
                                $from_d = $from_d->startOfDay();
                                $to_d = $to_d->endOfDay();
                                $from_d = $from_d->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = $to_d->setTimezone('UTC')->format($ymdHisFormat);
                                if ($from_d && $to_d) {
                                    $conditions_gsd[] = ['Easycases.gantt_start_date >=' => $from_d];
                                    if ($from_d !== $to_d) {
                                        $conditions_gsd[] = ['Easycases.gantt_start_date <=' => $to_d];
                                    }
                                }
                                $ret_qry[] = $conditions_gsd;
                            }
                        } else {
                            $ar_dt = explode('_', trim($v));
                            if (($ar_dt[2] ?? '') == 'custom') {
                            } else {
                                [$from_d, $to_d] = array_map(
                                    fn($date) => (new FrozenTime(date($ymdHisFormat, strtotime($date)), $toTz)),
                                    $ar_dt
                                );
                                $from_d = $from_d->startOfDay();
                                $to_d = $to_d->endOfDay();
                                $from_d = $from_d->setTimezone('UTC')->format($ymdHisFormat);
                                $to_d = $to_d->setTimezone('UTC')->format($ymdHisFormat);
                                if ($from_d && $to_d) {
                                    $conditions_dct[] = ['Easycases.dt_created >=' => $from_d];
                                    if ($from_d !== $to_d) {
                                        $conditions_dct[] = ['Easycases.dt_created <=' => $to_d];
                                    }
                                }
                                $ret_qry[] = $conditions_dct;
                            }
                        }
                    }
                }
            }
            if (!empty($ret_qry_assn)) {
                $projectsTable = $this->fetchTable('Projects');
                $projectUsers = $projectsTable->find()
                    ->select(['User.id', 'User.uniq_id', 'User.name'])
                    ->join([
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('User.id', 'User.id')
                    ])
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUser',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUser.user_id', 'User.id')
                    ])
                    ->join([
                        'table' => 'project_users',
                        'alias' => 'ProjectUser',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id')
                    ])
                    ->where([
                        'ProjectUser.project_id' => $pid,
                        'CompanyUser.company_id' => SES_COMP,
                        'CompanyUser.is_active' => '1',
                    ])
                    ->order(['User.name' => 'ASC'])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->toArray();
                $proje_req_users = [];
                if ($projectUsers) {
                    $Uids = Hash::extract($projectUsers, '{n}.User.id');
                    foreach ($ret_qry_assn as $jk => $jv) {
                        if ($jv != 0) {
                            if (in_array($jv, $Uids)) {
                                array_push($proje_req_users, $jv);
                            }
                        } else {
                            if ($jv == 0) {
                                array_push($proje_req_users, $jv);
                            }
                        }
                    }
                }
                if (!empty($proje_req_users)) {
                    $ret_qry[] = ['Easycase.assign_to IN' => $proje_req_users];
                }
            }
            if (!empty($ret_qry_type)) {
                $ret_qry[] = ['Easycase.type_id IN' => $ret_qry_type];
            }
            if (!empty($ret_qry_epic)) {
                $ret_qry[] = ['Easycase.epic_id IN' => $ret_qry_epic];
            }
            if (!empty($ret_qry_status)) {
                $ret_qry[] = ['Easycase.legend IN' => $ret_qry_status];
            }
            if (!empty($ret_qry_custom)) {
                $ret_qry[] = ['Easycase.custom_status_id IN' => $ret_qry_custom];
            }
        }
        return $ret_qry;
    }

    public function getClientCondition($model = 'Easycase')
    {
        $request = $this->getController()->getRequest();
        $clientCondition = [];
        $isClient = intval($request->getSession()->read('AuthView.User.is_client'));
        $sesUserId = intval($request->getSession()->read('AuthView.User.id'));
        if ($isClient) {
            $clientCondition[] = [
                'OR' => [
                    [
                        "{$model}.client_status" => $isClient,
                        "{$model}.user_id" => $sesUserId
                    ],
                    ["{$model}.client_status !=" => $isClient]
                ]
            ];
        }
        return $clientCondition;
    }

    public function statusFilterArr($caseStatus, $model = 'Easycase')
    {
        $qry = [];
        $caseStatus = trim(strval($caseStatus));
        if (empty($caseStatus)) {
            return [];
        }
        $caseStatus = "$caseStatus-";
        $stsArr = array_map('trim', array_filter(explode('-', $caseStatus)));
        $onlyDeflt = 0;
        $customStatusTable = $this->fetchTable('CustomStatuses');
        $customStatusList = $customStatusTable->getCustomStatusList();
        $statusConditions = [];
        foreach ($stsArr as $chksts) {
            if (trim($chksts)) {
                if ($chksts == 'attch') {
                    $statusConditions[] = ["$model.format" => EasycasesTable::FORMAT_FILES_DETAILS];
                } elseif ($chksts == 'upd') {
                    $statusConditions[] = ["$model.type_id" => TypesTable::UPDATE];
                } elseif ($chksts == 2) {
                    $onlyDeflt = 1;
                    $statusConditions[] = [fn($exp) => $exp->in("$model.legend", [EasycasesTable::LEGEND_OPENED, EasycasesTable::LEGEND_STARTED])];
                } else {
                    if (stristr($chksts, 'c')) {
                        $chksts_temp = substr($chksts, 1);
                        $chksts_temp = strval($chksts_temp);
                        if (trim($chksts_temp)) {
                            if (!empty($customStatusList)) {
                                foreach ($customStatusList as $c_key => $c_val) {
                                    if (trim(strval($c_key)) == trim($chksts_temp)) {
                                        $statusConditions[] = ["$model.custom_status_id" => $c_key];
                                    }
                                }
                            } else {
                                $statusConditions[] = ["$model.custom_status_id" => $chksts_temp];
                            }
                        }
                    } else {
                        $statusConditions[] = ["$model.legend" => $chksts];
                        $onlyDeflt = 1;
                    }
                }
            }
        }
        $qry[] = [
            'OR' => $statusConditions,
        ];
        if ($onlyDeflt) {
            $qry[] = ["$model.custom_status_id" => 0];
        }

        return $qry;
    }

    public function customStatusFilterArr($caseCustomStatus, $all_project = '', $caseStatus = '', $model = 'Easycase')
    {
        $customStatusTable = $this->fetchTable('CustomStatuses');
        $customStatusList = (strtolower(trim($all_project)) == 'all') ? $customStatusTable->getCustomStatusList() : [];

        $qry = [];
        $caseCustomStatus = trim(strval($caseCustomStatus));
        if (!empty($caseCustomStatus)) {
            $stsArr = explode('-', $caseCustomStatus);
            foreach ($stsArr as $chksts) {
                if (trim($chksts)) {
                    if (!empty($customStatusList)) {
                        $sname = $customStatusList[$chksts];
                        foreach ($customStatusList as $c_key => $c_val) {
                            if (strtolower($sname) == strtolower($c_val)) {
                                $column = "$model.custom_status_id";
                                $qry[] = [$column => $c_key];
                            }
                        }
                    } else {
                        $column = "$model.custom_status_id";
                        $qry[] = [$column => $chksts];
                    }
                }
            }

            if (strtolower(trim($all_project)) == 'all' && (trim($caseStatus) && $caseStatus != 'all')) {
                // if all projects and status filter then use OR
                // $qry = " OR (" . trim($qry) . ")";
            } else {
                // Use and for single project
                // $qry = " AND (" . trim($qry) . ")";
            }
        }
        return $qry;
    }

    public function typeFilterArr($caseTypes, $model = 'Easycase')
    {
        $qry = [];
        if ($caseTypes != 'all') {
            if (strstr($caseTypes, '-')) {
                $typArr = explode('-', $caseTypes);
                $qry[] = [fn($exp) => $exp->in("$model.type_id", $typArr)];
            } else {
                $qry[] = [fn($exp) => $exp->eq("$model.type_id", $caseTypes)];
            }
        }
        return $qry;
    }
    public function labelFilterArr($caseLabel, $curProjId, $comp_id, $ses_type, $ses_id)
    {
        $qry = [];

        if (!empty($caseLabel) && $caseLabel !== 'all') {
            $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
            $projectUserTable = $this->fetchTable('ProjectUsers');

            $lblArr = array_map('intval', array_filter(explode('-', trim($caseLabel, '-'))));
            if (!$lblArr) {
                return $qry;
            }

            if (!$curProjId || $curProjId === 'all') {
                $al_actvs = $projectUserTable->getAllActiveProject($ses_id, $comp_id, $ses_type);
                $projectIds = $al_actvs ? Hash::extract($al_actvs, '{n}.project_id') : [];
            } else {
                $projectIds = [$curProjId];
            }

            if ($projectIds) {
                $eids_lbl = $easycaseLabelsTable
                    ->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'easycase_id'
                    ])
                    ->where([
                        'company_id' => $comp_id,
                        'project_id IN' => $projectIds,
                        'label_id IN' => $lblArr
                    ])
                    ->orderDesc('id')
                    ->disableHydration()
                    ->toArray();

                $eids_lbl = array_unique(array_values($eids_lbl));
                if ($eids_lbl) {
                    $qry[] = fn($exp) => $exp->in('Easycase.id', $eids_lbl);
                }
            }
        }

        return $qry;
    }



    public function priorityFilterArr($priorityFil, $caseTypes, $model = 'Easycase')
    {
        $qry = [];
        $priorityMap = [
            'High' => ["$model.priority" => (string)EasycasesTable::PRIORITY_HIGH],
            'Medium' => ["$model.priority" => (string)EasycasesTable::PRIORITY_MEDIUM],
            'Low' => ["$model.priority >=" => (string)EasycasesTable::PRIORITY_LOW],
        ];
        if (!empty($priorityFil) && $priorityFil != 'all') {
            if (strstr($priorityFil, '-')) {
                $priArr = array_map('trim', array_filter(explode('-', $priorityFil)));
                foreach ($priArr as $priChk) {
                    $priorityConditions[] = $priorityMap[$priChk];
                }
                $qry = ['OR' => $priorityConditions];
            } else {
                $qry[] = $priorityMap[$priorityFil];
            }

            if ($caseTypes != TypesTable::UPDATE) {
                $qry[] = ["$model.type_id !=" => TypesTable::UPDATE];
            }
        }

        return $qry;
    }
    public function memberFilterArr($caseUserId, $model = 'Easycase')
    {
        $qry = [];
        if (!empty($caseUserId) && $caseUserId != 'all') {
            if (strstr($caseUserId, '-')) {
                $memArr = array_filter(explode('-', $caseUserId));
                $qry[] = [fn($exp) => $exp->in("$model.user_id", $memArr)];
            } else {
                $qry[] = [fn($exp) => $exp->eq("$model.user_id", $caseUserId)];
            }
        }
        return $qry;
    }

    public function commentFilterArr($caseUserId, $curProjId = null, $case_date = null)
    {
        $qry = [];
        if (!empty($caseUserId) && $caseUserId != 'all') {
            $memArr = array_filter(explode('-', strval($caseUserId)));
            if (count($memArr) > 0) {
                $easycasesTable = $this->fetchTable('Easycases');
                $caseNos = $easycasesTable->find()
                    ->select(['case_no'])
                    ->distinct(['case_no'])
                    ->where([
                        'istype' => EasycasesTable::TYPE_COMMENT,
                        'isactive' => EasycasesTable::IS_ACTIVE,
                        'user_id IN' => $memArr,
                        'project_id' => $curProjId,
                        'project_id !=' => 0,
                    ])
                    ->disableHydration()
                    ->toArray();
                if ($caseNos) {
                    $caseNos = array_unique(Hash::extract($caseNos, '{n}.case_no'));
                    $qry = ['Easycase.case_no IN' => $caseNos];
                } else {
                    $qry = ['Easycase.case_no IN' => [0]];
                }
            }
        }
        return $qry;
    }
    public function taskgroupFilterArr($caseTaskgroup)
    {
        $qry = [];
        if (!empty($caseTaskgroup) && $caseTaskgroup !== 'all') {
            $groupArr = array_filter(explode('-', $caseTaskgroup));
            $qrygroup = [];

            if (($key = array_search('default', $groupArr, true)) !== false) {
                $qrygroup[] = fn($exp) => $exp->isNull('EasycaseMilestone.milestone_id');
                unset($groupArr[$key]);
            }

            if ($groupArr) {
                $qrygroup[] = fn($exp) => $exp->in('EasycaseMilestone.milestone_id', $groupArr);
            }

            if ($qrygroup) {
                $qry[] = ['OR' => $qrygroup];
            }
        }
        return $qry;
    }

    public function assigntoFilterArr($caseAssignTo, $model = 'Easycase')
    {
        $qry = [];
        if (!empty($caseAssignTo) && $caseAssignTo != 'all') {
            if (strstr($caseAssignTo, '-')) {
                $asnArr = array_map('intval', array_map('trim', array_filter(explode('-', $caseAssignTo))));
                $qry[] = [fn($exp) => $exp->in("$model.assign_to", $asnArr)];
            } else {
                if (strtolower($caseAssignTo) == 'unassigned') {
                    $qry[] = [fn($exp) => $exp->eq("$model.assign_to", 0)];
                } else {
                    $qry[] = [fn($exp) => $exp->eq("$model.assign_to", $caseAssignTo)];
                }
            }
        }
        return $qry;
    }

    public function caseKeywordSearchArrExp($caseSrch, $type = null, $model = 'Easycase')
    {
        $searchcase = [];

        $srchstr1 = trim(urldecode($caseSrch));
        if ($srchstr1 === '') {
            return $searchcase;
        }

        $srchstr1 = addslashes($srchstr1);

        if ($srchstr1[0] === '#') {
            $srchstr1 = substr($srchstr1, 1);
        }

        // Case number + title mode
        if ($type === 'case_no_title' || preg_match('/[0-9]/', $srchstr1)) {
            $searchcase[] = fn($exp) => $exp->or([
                $exp->like("$model.title", "%$srchstr1%"),
                $exp->like("$model.case_no", "%$srchstr1%"),
            ]);
        }
        // If it's likely a case number (special char + digits)
        elseif (preg_match('[^A-Za-z -()@$&,]', $srchstr1) && !strpbrk($srchstr1, ' -,/_:.&')) {
            $caseno = preg_replace('[^0-9]', '', $srchstr1);
            $searchcase[] = fn($exp) => $exp->or([
                $exp->like("$model.case_no", "$caseno%"),
                $exp->like("$model.title", "%$srchstr1%"),
            ]);
        }
        // Type-specific searches
        elseif ($type === 'title') {
            $searchcase[] = fn($exp) => $exp->like("$model.title", "%$srchstr1%");
        } else {
            // "full", "half", or default → title + message
            $searchcase[] = fn($exp) => $exp->or([
                $exp->like("$model.title", "%$srchstr1%"),
                $exp->like("$model.message", "%$srchstr1%"),
            ]);
        }

        return $searchcase;
    }

    public function projectFilterArr($prjid, $model = 'Easycase')
    {
        $qry = [];
        if ($prjid != 'all') {
            $prjArr = array_map('intval', array_filter(explode('-', strval($prjid))));
            $qry[] = [fn($exp) => $exp->in("$model.project_id", $prjArr)];
        }
        return $qry;
    }

    public function arcUserFilterArr($usrid, $type = null)
    {
        $qry = [];
        if (!empty($usrid) && $usrid !== 'all') {
            $userArr = array_map('intval', array_filter(explode('-', (string) $usrid)));

            $column = match ($type) {
                'utilization', 'invoice' => 'LogTime.user_id',
                'work_load', 'pending' => 'Easycase.assign_to',
                default => 'Archive.user_id',
            };

            if (count($userArr) === 1) {
                $qry[] = [fn($exp) => $exp->eq($column, $userArr[0])];
            } elseif (!empty($userArr)) {
                $qry[] = [fn($exp) => $exp->in($column, $userArr)];
            }
        }
        return $qry;
    }

    public function arcLabelFilterArr($labelid, $type = null, $model = 'EasycaseLabel')
    {
        $qry = [];
        if (!empty($labelid) && $labelid != 'all') {
            if (strstr($labelid, '-')) {
                $typArr = explode('-', $labelid);
                $qry = ['EasycaseLabel.label_id IN' => $typArr];
            } else {
                $qry = ['EasycaseLabel.label_id' => $labelid];
            }
        }
        return $qry;
    }

    public function arcBillabilityFilterArr($billabilityid, $type = null)
    {
        $qry = [];
        if (!empty($billabilityid) && $billabilityid != 'all') {
            if (strstr($billabilityid, '-')) {
                $typArr = explode('-', $billabilityid);
                $qry['OR'] = [];
                foreach ($typArr as $typChk) {
                    if ($type == 'utilization') {
                        $typChk1 = ($typChk == 'billable') ? 1 : 0;
                        $qry['OR'][] = ['LogTime.is_billable' => $typChk1];
                    }
                }
            } else {
                $billabilityid = ($billabilityid == 'billable') ? 1 : 0;
                $qry[] = ['LogTime.is_billable' => $billabilityid];
            }
        }
        return $qry;
    }

    public function isGuestEnabled(): bool
    {
        return $this->formatHelper->isGuestEnabled();
    }

    public function isCriticalEnabled(): bool
    {
        return $this->formatHelper->isCriticalEnabled();
    }

}
