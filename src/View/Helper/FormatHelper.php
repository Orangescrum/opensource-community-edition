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

use App\Model\Table\ProjectsTable;
use App\Utility\CommonUtility;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Format helper
 */
class FormatHelper extends Helper
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

    public function initialize(array $config): void
    {
    }


    public function get_IP_address()
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $IPaddress) {
                    $IPaddress = trim($IPaddress); //Just to be safe
                    if (filter_var($IPaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $IPaddress;
                    }
                }
            }
        }
    }
    public function is_url_exist($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $status = ($code == 200) ? true : false;
        curl_close($ch);

        return $status;
    }
    public function getRealIpAddr()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        if ($this->is_private_ip($ip)) {
            return false;
        }

        return $ip;
    }

    public function is_private_ip($ip)
    {
        if (empty($ip) or !ip2long($ip)) {
            return false;
        }
        $private_ips = [
            ['10.0.0.0', '10.255.255.255'],
            ['172.16.0.0', '172.31.255.255'],
            ['192.168.0.0', '192.168.255.255'],
        ];
        $ip = ip2long($ip);
        foreach ($private_ips as $ipr) {
            $min = ip2long($ipr[0]);
            $max = ip2long($ipr[1]);
            if (($ip >= $min) && ($ip <= $max)) {
                return true;
            }
        }

        return false;
    }

    public function getUserDtls($uid)
    {
        $User = TableRegistry::getTableLocator()->get('Users');
        $usrDtls = $User->find()
            ->select(['name', 'photo', 'email', 'last_name', 'dt_created', 'dt_last_login', 'btprofile_id', 'uniq_id'])
            ->where(['id' => $uid])
            ->disableHydration()
            ->first();

        return $usrDtls;
    }

    public function displayStorage($value, $flag = 0)
    {
        if (strtolower(strval($value)) != 'unlimited' && $value) {
            if ($value < 1024) {
                return $value . ' MB';
            } else {
                if (!$flag) {
                    return number_format(($value / 1024), 1, '.', '') . ' GB';
                } else {
                    return round(($value / 1024)) . ' GB';
                }
            }
        } else {
            return $value;
        }
    }

    public function longstringwrap($string = '')
    {
        return $string;
    }

    public function getStatus($type, $legend)
    {
        if ($type == 10) {
            return '<span class="label label-update fade-update update">' . __('Update') . '</span>';
        } elseif ($legend == 1) {
            return '<span class="label new label-danger fade-red">' . __('New') . '</span>';
        } elseif ($legend == 2 || $legend == 4) {
            return '<span class="label wip label-info fade-blue">' . __('In Progress') . '</span>';
        } elseif ($legend == 3) {
            return '<span class="label closed label-success fade-green">' . __('Closed') . '</span>';
        } elseif ($legend == 5) {
            return '<span class="label resolved label-warning fade-orange">' . __('Resolved') . '</span>';
        }
    }
    public function getCustomStatus($customStatus)
    {
        return '<span class="label label-custom label-info" style="background-color:#' . $customStatus['color'] . '">' . $customStatus['name'] . '</span>';
    }
    public function getCustomStatusProj($customStatus, $proj_id, $sts_id)
    {
        if ($customStatus && $proj_id && isset($customStatus[$proj_id])) {
            foreach ($customStatus[$proj_id]['CustomStatus'] as $ks => $vs) {
                if ($sts_id == $vs['id']) {
                    return '<span class="label label-custom label-info" style="background-color:#' . $vs['color'] . '">' . $vs['name'] . '</span>';
                }
            }
        }

        return false;
    }
    public function getttformats($v)
    {
        return implode('-', explode(' ', strtolower($v)));
    }
    public function getUserStatus($total, $remain)
    {
        if (strtolower($total) == 'unlimited') {
            return 0;
        }
        $current = $total - $remain;
        $per_9 = round(0.9 * $total);
        if ($current >= $per_9) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getStatusWl($typ)
    {
        if ($typ == 10) {
            return '<span class="label update label-update fade-update">' . __('Update') . '</span>';
        } elseif ($typ == 1) {
            return '<span class="label new label-danger fade-red">' . __('New') . '</span>';
        } elseif ($typ == 2 || $typ == 4) {
            return '<span class="label wip label-info fade-blue">' . __('In Progress') . '</span>';
        }
        if ($typ == 3) {
            return '<span class="label closed label-success fade-green">' . __('Closed') . '</span>';
        } elseif ($typ == 4) {
            return '<span class="label wip label-info fade-blue">' . __('In Progress') . '</span>';
        } elseif ($typ == 5) {
            return '<span class="label resolved label-warning fade-orange">' . __('Resolved') . '</span>';
        }
    }
    public function fixtags($text)
    {
        //$text = htmlspecialchars($text);
        $text = preg_replace('/=/', '=""', $text);
        $text = preg_replace('/&quot;/', '&quot;"', $text);
        $tags = "/&lt;(\/|)(\w*)(\ |)(\w*)([\\\=]*)(?|(\")\"&quot;\"|)(?|(.*)?&quot;(\")|)([\ ]?)(\/|)&gt;/i";
        $replacement = '<$1$2$3$4$5$6$7$8$9$10>';
        $text = preg_replace($tags, $replacement, $text);
        $text = preg_replace('/=""/', '=', $text);

        return $text;
    }

    public function emailText($value)
    {
        $value = stripslashes(trim($value));
        $value = str_replace('“', '"', $value);
        $value = str_replace('”', '"', $value);
        $value = str_replace('�', '"', $value);
        $value = str_replace('�', '"', $value);
        //$value = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $value);
        $value = $this->fixtags($value);
        //$value = html_entity_decode($value, ENT_QUOTES);
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        return stripslashes($value);
    }

    public function getBrowser()
    {
        $browser = $_SERVER['HTTP_USER_AGENT'];
        if (strstr($browser, 'Safari') && !strstr($browser, 'Chrome')) {
            $agent = 'S';
        } elseif (strstr($browser, 'Firefox')) {
            $agent = 'F';
        } elseif (strstr($browser, 'Chrome')) {
            $agent = 'C';
        } elseif (strstr($browser, 'MSIE')) {
            $agent = 'I';
        }

        return $agent;
    }

    public function pub_file_exists($folder, $fileName)
    { //echo $fileName;exit;
        try {
            $s3 = new S3(awsAccessKey, awsSecretKey);
            $info = $s3->getObjectInfo(BUCKET_NAME, $folder . $fileName);
            if ($info) {
                //File exists
                return true;
            } else {
                //File doesn't exists
                return false;
            }
        } catch (\Exception $e) {
            print $e->getMessage();
            exit;
        }
    }

    public function imageExists($dir, $image)
    {
        if ($image && file_exists($dir . $image)) {
            return true;
        } else {
            return false;
        }
    }

    public function pagingShowRecords($total_records, $page_limit, $page)
    {
        $start = ($page - 1) * $page_limit;
        $start1 = $start + 1;
        $end = min($page * $page_limit, $total_records);

        return "$start1 - $end of $total_records";
    }

    public function formatText($value)
    {
        if (!$value) {
            return $value;
        }

        $value = str_replace(['“', '”', '�', '�'], '"', strval($value));
        $value = stripslashes(trim($value));
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        return $value;
    }
    public function formatSearchText($value)
    {
        $value = str_replace('“', '"', $value);
        $value = str_replace('”', '"', $value);
        $value = str_replace('�', '"', $value);
        $value = str_replace('�', '"', $value);

        return h($value);
    }
    public function paragraph_trim($content)
    {
        $result = preg_replace('!(^(\s*<p>(\s|&nbsp;)*</p>\s*)*|(\s*<p>(\s|&nbsp;)*</p>)*\s*\Z)!em', '', $content);

        return $result === null ? $content : $result;
    }

    public function formatCms($value)
    {
        $value = stripslashes(trim($value));
        $value = str_replace('�', '"', $value);
        $value = str_replace('�', '"', $value);
        $value = str_replace('~', '&#126;', $value);

        if (!stristr($value, "target='_blank'") && !stristr($value, 'target="_blank"')) {
            $value = str_replace('a href=', "a style='text-decoration:underline;color:#371FEE' target='_blank' href=", $value);
        }
        if (stristr($value, 'http://http://')) {
            $value = str_replace('http://http://', 'http://', $value);
        }
        if (stristr($value, 'http://http//')) {
            $value = str_replace('http://http//', 'http://', $value);
        }
        if (stristr($value, 'https://https://')) {
            $value = str_replace('https://https://', 'https://', $value);
        }
        if (stristr($value, 'https://https//')) {
            $value = str_replace('https://https//', 'https://', $value);
        }
        if (stristr($value, 'http://https://')) {
            $value = str_replace('http://https://', 'https://', $value);
        }

        return stripslashes($value);
    }

    public function shortLength($value, $len, $typ = 0)
    {
        $value_format = $this->formatText($value);
        $value_raw = html_entity_decode($value_format ?? '', ENT_QUOTES);
        if (strlen($value_raw) > $len) {
            $value_strip = mb_substr($value_raw, 0, $len);
            $value_strip = $this->formatText($value_strip);
            if ($typ) {
                $lengthvalue = $value_strip;
            } else {
                $lengthvalue = $value_strip . '...';
            }
        } else {
            $lengthvalue = $value_format;
        }

        return $lengthvalue;
    }

    public function shortLengthCMS($value, $len)
    {
        $value = stripslashes($value);
        $value = str_replace('�', '"', $value);
        $value = str_replace('�', '"', $value);
        //$value = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $value);
        $value = str_replace('~', '&#126;', $value);
        $value = strip_tags($value);
        $value = trim($value);

        if (strlen($value) > $len) {
            $value_strip = substr($value, 0, $len);
            $lengthvalue = $value_strip . '...';
        } else {
            $lengthvalue = $value;
        }

        //$lengthvalue = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $lengthvalue);
        return $lengthvalue;
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
        } else {
            $status = 'All';
        }

        return $status;
    }

    public function getFileType($file_type)
    {
        $ext = strtolower(pathinfo($file_type, PATHINFO_EXTENSION));
        $fileTypes = [
            'pdf' => 'pdf_file',
            'csv' => 'csv_file',
            'doc' => 'doc_file',
            'docx' => 'doc_file',
            'rtf' => 'doc_file',
            'odt' => 'doc_file',
            'dotx' => 'doc_file',
            'docm' => 'doc_file',
            'xls' => 'xls_file',
            'xlsx' => 'xls_file',
            'ods' => 'xls_file',
            'xlsm' => 'xls_file',
            'xlsb' => 'xls_file',
            'xltx' => 'xls_file',
            'xltm' => 'xls_file',
            'png' => 'png_file',
            'tif' => 'tif_file',
            'bmp' => 'bmp_file',
            'gif' => 'png_file',
            'jpg' => 'jpg_file',
            'jpeg' => 'jpg_file',
            'zip' => 'zip_file',
            'rar' => 'zip_file',
            'gz' => 'zip_file',
        ];
        if (isset($fileTypes[$ext])) {
            $fileClass = $fileTypes[$ext];

            return '<div class="' . $fileClass . ' cmn_fl os_sprite fl"></div>';
        } else {
            return '<div class="new_custom_file"><span class="default_type">' . $ext . '</span></div>';
        }
    }

    public function imageType($filename, $width1, $height1, $link, $downloadUrl = null, $is_ext = null)
    {
        if ($width1 != 0) {
            $width = "width='" . $width1 . "'";
        } else {
            $width = '';
        }
        if ($height1 != 0) {
            $height = "height='" . $height1 . "'";
        } else {
            $height = '';
        }

        $oldname = strtolower($filename);
        $ext = substr(strrchr($oldname, '.'), 1);

        if ($link == 1) {
            if (isset($downloadUrl) && trim($downloadUrl)) { //By Sunil
                $links1 = "<a href='" . $downloadUrl . "' target='_blank' style='font:bold 11px verdana;text-transform:uppercase;color:#000000'>";
            } else {
                $links1 = "<a href='" . HTTP_ROOT . 'easycases/download/' . $filename . "' style='font:bold 11px verdana;text-transform:uppercase;color:#000000'>";
            }
            $links2 = '</a>';
        } else {
            $links1 = '';
            $links2 = '';
        }

        $style = "style='border:0px solid #C3C3C3'";

        if (isset($is_ext)) {
            return $ext;
        }

        if ($ext == 'zip') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/zip.png' alt='[zip]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'rar') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/rar.png' alt='[rar]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'xls' || $ext == 'xlsx') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/xls.png' alt='[xls]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'doc' || $ext == 'docx' || $ext == 'rtf') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/doc.png' alt='[doc]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'txt') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/txt.png' alt='[txt]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'jpg' || $ext == 'jpeg') {
            $image = "<img src='" . HTTP_IMAGES . "images/case/jpg.png' alt='[jpg]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />';
        } elseif ($ext == 'png') {
            $image = "<img src='" . HTTP_IMAGES . "images/case/png.png' alt='[png]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />';
        } elseif ($ext == 'gif') {
            $image = "<img src='" . HTTP_IMAGES . "images/case/gif.png' alt='[gif]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />';
        } elseif ($ext == 'bmp') {
            $image = "<img src='" . HTTP_IMAGES . "images/case/bmp.png' alt='[bmp]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />';
        } elseif ($ext == 'ppt') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/ppt.png' alt='[ppt]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } elseif ($ext == 'pdf') {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/pdf.png' alt='[pdf]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        } else {
            $image = $links1 . "<img src='" . HTTP_IMAGES . "images/case/other.png' alt='[other]' title='" . $filename . "' " . $width . ' ' . $height . " border='0' " . $style . ' />' . $links2;
        }

        return $image;
    }

    public function todo_typ($type, $title)
    {
        $disp_type = (file_exists('' . HTTP_IMAGES . 'images/types/' . $type . 'png')) ? '<img src="' . HTTP_IMAGES . 'images/types/' . $type . '.png" title="' . $title . '" alt="' . $type . '" />' : '';

        return $disp_type;
    }

    public function todo_typ_src($type, $title)
    {
        $disp_type = HTTP_IMAGES . 'images/types/' . $type . ".png'";

        return $disp_type;
    }

    ######## WordWrap #######

    public function html_wordwrap($str, $width, $break = "\n", $cut = false)
    {
        //same functionality as wordwrap, but ignore html tags
        $unused_char = $this->find_unused_char($str); //get a single character that is not used in the string
        $tags_arr = $this->get_tags_array($str);
        $q = '?';
        $str1 = ''; //the string to be wrapped (will not contain tags)
        $element_lengths = []; //an array containing the string lengths of each element
        foreach ($tags_arr as $tag_or_words) {
            if (preg_match("/<.*$q>/", $tag_or_words)) {
                continue;
            }
            $str1 .= $tag_or_words;
            $element_lengths[] = strlen($tag_or_words);
        }
        $str1 = wordwrap($str1, $width, $unused_char, $cut);
        foreach ($tags_arr as &$tag_or_words) {
            if (preg_match("/<.*$q>/", $tag_or_words)) {
                continue;
            }
            $tag_or_words = substr($str1, 0, $element_lengths[0]);
            $str1 = substr($str1, $element_lengths[0]);
            array_shift($element_lengths); //delete the first array element - we have used it now so we do not need it
        }
        $str2 = implode('', $tags_arr);
        $str3 = str_replace($unused_char, $break, $str2);

        return $str3;
    }

    public function get_tags_array($str)
    {
        //given a string, return a sequential array with html tags in their own elements
        $q = '?';

        return preg_split("/(<.*$q>)/", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    public function find_unused_char($str)
    {
        $possible_chars = ['|', '!', '@', '#', '$', '%', '^', '&', '*', '~'];
        foreach ($possible_chars as $char) {
            if (strpos($str, $char) === false) {
                return $char;
            }
        }
    }

    //Start function explode_ wrap
    public function explode_wrap($text, $chunk_length)
    {
        $string_chunks = explode(' ', $text);
        foreach ($string_chunks as $chunk => $value) {
            if (strlen($value) >= $chunk_length) {
                $new_string_chunks[$chunk] = chunk_split($value, $chunk_length, ' ');
            } else {
                $new_string_chunks[$chunk] = $value;
            }
        }

        return $new_text = implode(' ', $new_string_chunks);
    }

    public function strip_word_html($text, $allowed_tags = '<b><i><sup><sub><em><strong><u><br><ul><li><ol><strike>')
    {
        mb_regex_encoding('UTF-8');
        $search = ['/&lsquo;/u', '/&rsquo;/u', '/&ldquo;/u', '/&rdquo;/u', '/&mdash;/u'];
        $replace = ['\'', '\'', '"', '"', '-'];
        $text = preg_replace($search, $replace, $text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        if (mb_stripos($text, '/*') !== false) {
            $text = mb_eregi_replace('#/\*.*?\*/#s', '', $text, 'm');
        }
        $text = preg_replace(['/<([0-9]+)/'], ['< $1'], $text);
        $text = strip_tags($text, $allowed_tags);
        $text = preg_replace(['/^\s\s+/', '/\s\s+$/', '/\s\s+/u'], ['', '', ' '], $text);
        $search = ['#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu', '#<(em|i)[^>]*>(.*?)</(em|i)>#isu', '#<u[^>]*>(.*?)</u>#isu'];
        $replace = ['<b>$2</b>', '<i>$2</i>', '<u>$1</u>'];
        $text = preg_replace($search, $replace, $text);
        $num_matches = preg_match_all("/\<!--/u", $text, $matches);
        if ($num_matches) {
            $text = preg_replace('/\<!--(.)*--\>/isu', '', $text);
        }

        return $text;
    }

    public function closetags($html)
    {
        preg_match_all('#<([a-z]+)( .*)?(?!/)>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i = 0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</' . $openedtags[$i] . '>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }

        return $html;
    }

    public function getFileSize($size)
    {
        if ($size) {
            if ($size < 1024) {
                return $size . ' Kb';
            } else {
                $filesize = $size / 1024;

                return number_format($filesize, 2) . ' Mb';
            }
        }
    }

    public function displayImages($caseFileName)
    {
        $imgaes = '';
        $oldname = strtolower($caseFileName);
        $ext = substr(strrchr($oldname, '.'), 1);
        if ($ext == 'png' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'gif' || $ext == 'ttf' || $ext == 'bmp') {
            //$size = getimagesize(DIR_CASE_FILES.$caseFileName);
            //$size = getimagesize(DIR_CASE_FILES_S3.$caseFileName);
            $fileurl = $this->generateTemporaryURL(DIR_CASE_FILES_S3 . $caseFileName);
            $size = getimagesize($fileurl);
            if ($size[0] >= 225) {
                $imgaes = "<a href='" . HTTP_ROOT . 'easycases/download/' . $caseFileName . "'>
								<img src='" . HTTP_ROOT . 'easycases/image_thumb/?type=case&file=' . $caseFileName . "&sizex=225&sizey=200&quality=100' border='0' style='border:1px solid #D6D6D6;background:#FEFEE2' alt='" . $caseFileName . "' title='" . $caseFileName . "'/>
							</a>";
            } else {
                $imgaes = "<a href='" . HTTP_ROOT . 'easycases/download/' . $caseFileName . "'>
								<img src='" . HTTP_CASE_FILES . $caseFileName . "' border='0' style='border:1px solid #D6D6D6;background:#FEFEE2'  alt='" . $caseFileName . "' title='" . $caseFileName . "'/>
							</a>";
            }
        }

        return $imgaes;
    }

    public function validateImgFileExt($filename)
    {
        if (!$filename) {
            return false;
        }
        $dotPos = strrpos($filename, '.');
        if ($dotPos === false) {
            return false;
        }
        $ext = strtolower(substr($filename, $dotPos + 1));
        $extList = ['png', 'gif', 'jpg', 'jpeg', 'bmp'];

        return in_array($ext, $extList);
    }
    public function validatePdfFileExt($filename)
    {
        if (!$filename) {
            return false;
        }
        $dotPos = strrpos($filename, '.');
        if ($dotPos === false) {
            return false;
        }
        $ext = strtolower(substr($filename, $dotPos + 1));
        return $ext === 'pdf';
    }
    public function generateTemporaryURL($resource)
    {
        $bucketname = BUCKET_NAME;
        $awsAccessKey = awsAccessKey;
        $awsSecretKey = awsSecretKey;
        $expires = strtotime('+1 day'); //1.day.from_now.to_i;
        $s3_key = explode(BUCKET_NAME, $resource);
        $x = $s3_key[1];
        $s3_key[1] = substr($x, 1);
        $string = "GET\n\n\n{$expires}\n/{$bucketname}/{$s3_key[1]}";
        $signature = urlencode(base64_encode((hash_hmac('sha1', utf8_encode($string), $awsSecretKey, true))));

        return "{$resource}?AWSAccessKeyId={$awsAccessKey}&Signature={$signature}&Expires={$expires}";
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

    public function isiPad()
    {
        preg_match('/iPad/i', $_SERVER['HTTP_USER_AGENT'], $match);
        if (!empty($match)) {
            return true;
        }

        return false;
    }

    /**
     * @method: public formatprofileimage(string $photoname) Get the formatted image
     * @author GDR <support@orangescrum.com>
     * @return string Formatted Image
     */
    public function formatprofileimage($photoname = '')
    {
        if ($photoname) {
            return '<img src="' . HTTP_ROOT . 'users/image_thumb/?type=photos&file=' . $photoname . '&sizex=28&sizey=28&quality=100" class="round_profile_img" height="28" width="28" />';
        } else {
            return '<img src="' . HTTP_ROOT . 'users/image_thumb/?type=photos&file=user.png&sizex=28&sizey=28&quality=100" class="round_profile_img" height="28" width="28" />';
        }
    }

    public function getTaskdetails($prjid, $tskid)
    {
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $tskDtls = $easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))
            ->where(['Easycase.id' => $tskid, 'Easycase.project_id' => $prjid])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        return $tskDtls;
    }

    public function getTaskType($tsktypid)
    {
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $typDtls = $typesTable->find('all')
            ->where(['Type.id' => $tsktypid])
            ->select(CommonUtility::getSelectColumns('Types', null, 'Type'))
            ->join(CommonUtility::tableSelfJoin('types', 'Type', 'Types'))
            ->disableHydration()
            ->first();

        return $typDtls;
    }

    public function frmtdata($str, $strt = 0, $len = 20)
    {
        if (!empty($str) && strlen($str) > $len) {
            $newstr = substr($str, $strt, $len);

            return $newstr . '...';
        } else {
            return $str;
        }
    }

    public function chngdttime($lgdt, $lgtime)
    {
        $newdt = $lgdt . ' ' . $lgtime;

        return date('g:i A', strtotime($newdt));
    }

    /* Author: GKM
     * to format sec to hr min
     */

    public function format_time_hr_min($totalsecs = '', $mode = '', $is_formt = 0)
    {
        if ($mode == 'decimal') {
            $val = round($totalsecs / 3600, 2);
            if ($is_formt) {
                $val = number_format($val);
            }
            #$val = floor($totalsecs / 3600) . "." . round(($totalsecs % 3600) / 60);
        } elseif ($mode == 'hrmin') {
            $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) : '0';
            if ($is_formt) {
                $hours = number_format($hours);
            }
            $mins = round(($totalsecs % 3600) / 60) > 0 ? round(($totalsecs % 3600) / 60) : '00';
            $val = $hours . ':' . str_pad(strval($mins), 2, '0', STR_PAD_LEFT);
        } elseif ($mode == 'hh:min') {
            $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) : '0';
            if ($is_formt) {
                $hours = number_format($hours);
            }
            $mins = round(($totalsecs % 3600) / 60) > 0 ? round(($totalsecs % 3600) / 60) : '00';
            if ($hours <= 9) {
                $hours = '0' . $hours;
            }
            $val = $hours . ':' . str_pad(strval($mins), 2, '0', STR_PAD_LEFT);
        } else {
            if ($is_formt) {
                $hours = floor($totalsecs / 3600) > 0 ? number_format(floor($totalsecs / 3600)) . ' hr' . (floor($totalsecs / 3600) > 1 ? 's' : '') . ' ' : '';
            } else {
                $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) . ' hr' . (floor($totalsecs / 3600) > 1 ? 's' : '') . ' ' : '';
            }
            $mins = round(($totalsecs % 3600) / 60) > 0 ? '' . round(($totalsecs % 3600) / 60) . ' min' . (round(($totalsecs % 3600) / 60) > 1 ? 's' : '') : '';
            $val = $hours . '' . $mins;
        }

        return $val;
    }
    public function api_format_time_hr_min($totalsecs = '', $mode = '')
    {
        if ($mode == 'decimal') {
            $val = round($totalsecs / 3600, 2);
            #$val = floor($totalsecs / 3600) . "." . round(($totalsecs % 3600) / 60);
        } elseif ($mode == 'hrmin') {
            $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) : '0';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? round(($totalsecs % 3600) / 60) : '00';
            $val = $hours . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
        } else {
            $hours = floor($totalsecs / 3600) > 0 ? floor($totalsecs / 3600) . ':' . (floor($totalsecs / 3600) > 1 ? '' : '') . '' : '00:';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? '' . round(($totalsecs % 3600) / 60) . '' . (round(($totalsecs % 3600) / 60) > 1 ? '' : '') : '00';
            $val = $hours . '' . $mins;
        }

        return $val;
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
     * used to format time only
     */

    public function get_time($date = '', $format = 'h:i a')
    {
        if ($date == '') {
            $date = date('Y-m-d H:i:s');
        }
        $format = (SES_TIME_FORMAT == 12) ? 'g:i a' : 'H:i';

        return date($format, strtotime($date));
    }

    /* By GKM
     * used to format date only
     */

    public function get_date($date = '', $format = 'M d, Y')
    {
        if ($date == '') {
            $date = date('Y-m-d H:i:s');
        }

        return date($format, strtotime($date));
    }

    /* By GKM
     * used to format date and time
     */

    public function get_date_time($date = '', $format = 'M d, Y h:i a')
    {
        if ($date == '') {
            $date = date('Y-m-d H:i:s');
        }

        return date($format, strtotime($date));
    }

    /* By GKM
     * currency dropdown data
     */

    public function currency_opts($chk = 0)
    {
        $currenciesTable = TableRegistry::getTableLocator()->get('Currencies');
        $currencyData = $currenciesTable->find()
            ->select(['id', 'code', 'name'])
            ->where(['status' => 'Active'])
            ->order(['code' => 'ASC'])
            ->disableHydration()
            ->toArray();
        $final_arr = [];
        $length = 45;
        if (!$chk) {
            $final_arr[0] = 'Select Currency';
        }
        if (is_array($currencyData) && count($currencyData) > 0) {
            foreach ($currencyData as $val) {
                $name = trim($val['name']);
                $final_arr[$val['id']] = $val['code'] . ' : ' . (strlen($name) > $length ? substr($name, 0, $length) . '...' : $name);
            }
        }

        return $final_arr;
    }

    /* By GKM
     * used to format price value
     */

    public function format_price($number, $decimals = 2, $dec_point = '.', $thousands_sep = '')
    {
        // Handle null or empty values
        if ($number === null || $number === '') {
            $number = 0;
        }

        // Convert to float to ensure numeric value
        $number = floatval($number);

        return number_format($number, $decimals, $dec_point, $thousands_sep);
        #return number_format($number, 2, '.', '');
    }

    /* By GKM
     * used to display_activity_log
     */

    public function display_activity_log($data = [])
    {
        if ($data['user_id'] == SES_ID) {
            $return_text = 'You have ';
        } else {
            $return_text = $data['name'] . ' ' . $data['last_name'] . ' has ';
        }

        if ($data['activity'] == 'create') {
            $return_text .= ' created ';
        } elseif ($data['activity'] == 'download') {
            $return_text .= ' downloaded ';
        } elseif ($data['activity'] == 'email') {
            $return_text .= ' sent ';
        } elseif ($data['activity'] == 'modify') {
            $return_text .= ' modified ';
        } elseif ($data['activity'] == 'view') {
            $return_text .= ' viewed ';
        } elseif ($data['activity'] == 'paid') {
            $return_text .= ' received payment on ';
        } elseif ($data['activity'] == 'unpaid') {
            $return_text .= ' not received payment on ';
        }

        return $return_text .= ' this invoice.';
    }

    public function formatTitle($title)
    {
        if (isset($title) && !empty($title)) {
            $title = htmlspecialchars(html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
        }

        return $title;
    }

    /* By STJ
     * used to Convert seconds into hours.mins format
     */

    public function formatHour($secds)
    {
        $number = $secds / 3600;

        return number_format((float) $number, 2, '.', '');
    }

    public function getProfileBgColr($uid = null)
    {
        if ($uid) {
            $t_clr = Configure::read('PROFILE_BG_CLR');
            $random_bgclr = $t_clr[array_rand($t_clr, 1)];
            $ret_colr = $random_bgclr;
            if (!isset($_SESSION['user_profile_colr'])) {
                $_SESSION['user_profile_colr'] = [];
                $_SESSION['user_profile_colr'][$uid] = $random_bgclr;
            } else {
                if (!array_key_exists($uid, $_SESSION['user_profile_colr'])) {
                    $_SESSION['user_profile_colr'][$uid] = $random_bgclr;
                } else {
                    $ret_colr = $_SESSION['user_profile_colr'][$uid];
                }
            }

            return $ret_colr;
        }
    }
    /*
     * Author Satyajeet
     * To get the number of week in a month
     */
    public function weekOfMonth($date)
    {
        //Get the first day of the month.
        $firstOfMonth = strtotime(date('Y-m-01', $date));

        //Apply formula (Week of the month = Week of the year - Week of the year of first day of month + 1).
        return intval(date('W', $date)) - intval(date('W', $firstOfMonth)) + 1;
    }

    public function imageTypeIcon($format)
    {
        $iconsArr = ['gd', 'db', 'od', 'zip', 'xls', 'doc', 'jpg', 'png', 'bmp', 'pdf', 'tif', 'txt', 'psd', 'video', 'ppt', 'sql', 'csv'];
        $format = strtolower($format);
        if ($format == 'xlsx') {
            $format = 'xls';
        } elseif ($format == 'docx' || $format == 'rtf' || $format == 'odt') {
            $format = 'doc';
        } elseif ($format == 'jpeg') {
            $format = 'jpg';
        } elseif ($format == 'gif') {
            $format = 'png';
        } elseif ($format == 'rar' || $format == 'gz' || $format == 'bz2') {
            $format = 'zip';
        } elseif ($format == 'mp4' || $format == '3gp' || $format == 'mpeg4' || $format == 'mkv') {
            $format = 'video';
        }
        if (!in_array($format, $iconsArr)) {
            $format = 'html';
        }

        return $format;
    }


    public function format_second_hrmin($totalsecs = '')
    {
        $hours = $mins = '00';
        if (!empty($totalsecs)) {
            $hours = floor($totalsecs / 3600) > 0 ? strval(floor($totalsecs / 3600)) : '00';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? strval(round(($totalsecs % 3600) / 60)) : '00';
        }

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }

    public function getworkhr($whl, $dt)
    {
        if (!empty($whl)) {
            foreach ($whl as $k => $v) {
                $logdt = date('Y-m-d', strtotime($k));
                if (strtotime($dt) >= strtotime($logdt)) {
                    return $v;
                }
            }
        } else {
            return 8;
        }

        return 8;
    }
    public function getPriority($proj_priority)
    {
        if ($proj_priority == 'NULL' || $proj_priority == '') {
            return;
        } elseif ($proj_priority == 0) {
            return 'high';
        } elseif ($proj_priority == 1) {
            return 'medium';
        } elseif ($proj_priority >= 2) {
            return 'low';
        }
    }
    public function showSubtaskTitle($title, $id, $related, $type = 0, $c_ase = [])
    {
        if ($type) {
            $title = '<a href="javascript:void(0);" data-href="' . HTTP_ROOT . 'dashboard#details/' . $c_ase['uniq_id'] . '" onclick="return switchtaskwithProject(this);" data-pid="' . $c_ase['uniq_id'] . '">#' . $c_ase['case_no'] . ': ' . $title . '</a>';
        } else {
            $title = '<a href="' . HTTP_ROOT . 'dashboard#details/' . $c_ase['uniq_id'] . '" class="cmn_link_color">' . $title . '</a>';
        }
        if (!empty($related['parent'][$id])) {
            $parent_id = $related['parent'][$id];
            if (!empty($related['parent'][$parent_id])) {
                $super_parent_id = $related['parent'][$parent_id];
                if ($related['client_status']['is_client'] && $related['client_status']['chekstatus'][$parent_id]) {
                } else {
                    if ($type) {
                        $title .= '<a href="javascript:void(0);" data-href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$parent_id]['uniq_id'] . '" onclick="return switchtaskwithProject(this);" data-pid="' . $related['data'][$parent_id]['uniq_id'] . '"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$parent_id]) . '</a>';
                    } else {
                        $title .= '<a href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$parent_id]['uniq_id'] . '" class="cmn_link_color"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$parent_id]) . '</a>';
                    }
                }
                if ($related['client_status']['is_client'] && $related['client_status']['chekstatus'][$super_parent_id]) {
                } else {
                    if ($type) {
                        $title .= '<a href="javascript:void(0);" data-href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$super_parent_id]['uniq_id'] . '" onclick="return switchtaskwithProject(this);" data-pid="' . $related['data'][$super_parent_id]['uniq_id'] . '"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$super_parent_id]) . '</a>';
                    } else {
                        $title .= '<a href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$super_parent_id]['uniq_id'] . '" class="cmn_link_color"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$super_parent_id]) . '</a>';
                    }
                }
            } else {
                if ($related['client_status']['is_client'] && $related['client_status']['chekstatus'][$parent_id]) {
                } else {
                    if ($type) {
                        $title .= '<a href="javascript:void(0);" data-href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$parent_id]['uniq_id'] . '" onclick="return switchtaskwithProject(this);" data-pid="' . $related['data'][$parent_id]['uniq_id'] . '"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$parent_id]) . '</a>';
                    } else {
                        $title .= '<a href="' . HTTP_ROOT . 'dashboard#details/' . $related['data'][$parent_id]['uniq_id'] . '" class="cmn_link_color"> <i class="material-icons">&#xE314;</i> ' . trim($related['task'][$parent_id]) . '</a>';
                    }
                }
            }
        }

        return ucfirst($title);
    }
    public function smart_wordwrap($string, $width = 75, $break = '<br>')
    {
        $pattern = sprintf('/([^ ]{%d,})/', $width);
        $output = '';
        $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            // normal behaviour, rebuild the string
            if (false !== strpos($word, ' ')) {
                $output .= $word;
            } else {
                // work out how many characters would be on the current line
                $wrapped = explode($break, wordwrap($output, $width, $break));
                $count = $width - (strlen(end($wrapped)) % $width);

                // fill the current line and add a break
                $output .= substr($word, 0, $count) . $break;

                // wrap any remaining characters from the problem word
                $output .= wordwrap(substr($word, $count), $width, $break, true);
            }
        }

        // wrap the final output
        return wordwrap($output, $width, $break);
    }
    public function gethh_mm($sec)
    {
        $t = round($sec);

        return sprintf('%02d:%02d', ($t / 3600), ($t / 60 % 60));
    }

    /* Wiki related code starts here */

    public function createTreeView($array, $currentParent, $firstWikiId, $currLevel = 0, $prevLevel = -1)
    {
        #			echo "<pre>";print_r($array);exit;
        foreach ($array as $categoryId => $category) {
            if ($currentParent == $category['parent_id']) {
                if ($currLevel > $prevLevel) {
                    if ($currLevel == 0) {
                        echo " <ul id='tree'> ";
                    } else {
                        echo ' <ul> ';
                    }
                }
                if ($currLevel == $prevLevel) {
                    echo ' </li> ';
                }

                if ($firstWikiId == $categoryId) {
                    $selectedMenu = 'style="color:#f5911c;text-decoration:underline"';
                } else {
                    $selectedMenu = '';
                }

                if ($currentParent == 0) {
                    if (strlen($category['name']) > 33) {
                        $newCatName = substr(htmlspecialchars($category['name']), 0, 33) . '&hellip;';
                        echo '<li><a href="javascript:void(0);" class="selectedCls_' . $categoryId . '" ' . $selectedMenu . ' rel="tooltip" title="' . htmlspecialchars($category['name']) . '" onclick="getWikiDetails(' . $categoryId . ')"><strong>' . $newCatName . '</strong></a>';
                    } else {
                        echo '<li><a href="javascript:void(0);" class="selectedCls_' . $categoryId . '" ' . $selectedMenu . ' onclick="getWikiDetails(' . $categoryId . ')"><strong>' . $category['name'] . '</strong></a>';
                    }
                } else {
                    if (strlen($category['name']) > 30) {
                        $newCatName = substr(htmlspecialchars($category['name']), 0, 30) . '&hellip;';
                        echo '<li><a href="javascript:void(0);" class="selectedCls_' . $categoryId . '" ' . $selectedMenu . ' rel="tooltip" title="' . htmlspecialchars($category['name']) . '" onclick="getWikiDetails(' . $categoryId . ')">' . $newCatName . '</a>';
                    } else {
                        echo '<li><a href="javascript:void(0);" class="selectedCls_' . $categoryId . '" ' . $selectedMenu . ' onclick="getWikiDetails(' . $categoryId . ')">' . $category['name'] . '</a>';
                    }
                }

                if ($currLevel > $prevLevel) {
                    $prevLevel = $currLevel;
                }

                $currLevel++;

                $this->createTreeView($array, $categoryId, $currLevel, $prevLevel);

                $currLevel--;
            }
        }

        if ($currLevel == $prevLevel) {
            echo ' </li>  </ul> ';
        }
    }

    public function getWikiCreatorName($WikiUserId)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $getWikiUsername = $usersTable->find()->where(['id' => $WikiUserId])->disableHydration()->first();

        return $getWikiUsername['User']['name'] ?? '';
    }

    public function chngdate($lgdt)
    {
        if ($GLOBALS['DateFormat'] == 2) {
            $date_format = 'd M, Y';
        } else {
            $date_format = 'M d, Y';
        }

        return date($date_format, strtotime($lgdt));
    }

    public function chngdate_csv($lgdt)
    {
        if ($GLOBALS['DateFormat'] == 2) {
            $date_format = 'd M Y';
        } else {
            $date_format = 'M d Y';
        }

        return date($date_format, strtotime($lgdt));
    }
    /*
     * Check the resource availability On
     */
    public function isWikiOn($sts = null)
    {
        return 1;
    }
    /* Wiki related code ends here */
    /* Check user role */
    public function isAllowed($action, $roleAccess = null, $project_id = 0, $company = 0)
    {
        if ((SES_TYPE == 2 || SES_TYPE == 1) && $action != 'Change Due Date Reason') {
            return true;
        }
        if (empty($roleAccess)) {
            $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
            $roleAccess = $roleInfo['roleAccess'];
        }

        if ($company != 0) {
            $project_id = 0;
        } else {
            $project_id = $_COOKIE['CPUID'] ?? 0;
            if (empty($project_id) || !isset($roleAccess[$project_id]) || empty($roleAccess[$project_id])) {
                $project_id = 0;
            }
        }

        if (array_key_exists($action, $roleAccess[$project_id])) {
            return $roleAccess[$project_id][$action] == 0 ? false : true;
        } else {
            return false;
        }
    }

    public function isAllowedModuleAction($module, $action, $roleAccess = null, $company = 0)
    {
        if (empty($roleAccess)) {
            $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
            $roleAccess = $roleInfo['roleAccess'] ?? [];
        }

        if ($company != 0) {
            $project_id = 0;
        } else {
            $project_id = $_COOKIE['CPUID'] ?? 0;
            if (empty($project_id) || !isset($roleAccess[$project_id]) || empty($roleAccess[$project_id])) {
                $project_id = 0;
            }
        }

        $scope = $roleAccess[$project_id] ?? [];
        $qualified = $module . '::' . $action;
        if (array_key_exists($qualified, $scope)) {
            return $scope[$qualified] != 0;
        }
        if (array_key_exists($action, $scope)) {
            return $scope[$action] != 0;
        }

        return false;
    }

    /* End*/
    public function getsubmenucolor($color)
    {
        $color = trim($color);
        $text_class = '';
        if (strpos($color, 'gradient-45deg-') !== false) {
            if ($color == 'gradient-45deg-white') {
                $text_class = str_replace('gradient-45deg-', '', $color);
                $text_class = $text_class . '-text';
            } else {
                $text_class = str_replace('gradient-45deg-', '', $color);
            }
        } else {
            $text_class = $color . '-text';
        }

        return $text_class;
    }

    /**
     * Check if Google Sync feature is enabled.
     *
     * @param int $company_id Company ID
     * @param int $user_id User ID
     * @param string $type Sync type
     * @return int 1 if enabled, 0 if disabled
     */
    public function isGoogleSyncOn($company_id, $user_id, $type = '1')
    {
        return 1;
    }

    public function isGitsyncOn($company_id, $chk = 0)
    {
        return 1;
    }
    public function getTaskPermalink($projShortName, $taskNo)
    {
        return $projShortName . '-' . $taskNo;
    }

    public function checkCustomMenuStatus($menu, $theme_settings, $page_array, $roleAccess, $pmethodology, $is_parent_menu, $url = '')
    {
        $returnArr = [];
        $pmethodology = strtolower($pmethodology);
        $returnArr['active_class'] = '';
        $returnArr['isAllow'] = false;
        $returnArr['dynamic_url'] = $returnArr['dynamic_a_click'] = $returnArr['dynamic_menu_name'] = '';
        $grad = ($is_parent_menu) ? (isset($theme_settings['sidebar_color']) ? $theme_settings['sidebar_color'] . ' gradient-shadow' : '') : '';

        // Open-source edition: restrict the left sidebar to the OSS feature set.
        // Everything else stays deny-by-default (hidden).
        // 'more' is an overflow container with nothing left to hold, and Kanban
        // is reachable as a tab on the Tasks page.
        $ossAllowedMenus = [
            'dashboard', 'projects', 'tasks',
            'time log', 'time log list view',
            'users',
        ];
        if (!in_array(strtolower($menu['name'] ?? ''), $ossAllowedMenus, true)) {
            return $returnArr;
        }

        switch (strtolower($menu['name'] ?? '')) {
            case 'dashboard':
                if (CONTROLLER == 'mydashboards' || (CONTROLLER == 'easycases' && (PAGE_NAME == 'mydashboardv2' || PAGE_NAME == 'mydashboard'))) {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                if ($this->isAllowed('View Dashboard', $roleAccess)) {
                    $returnArr['isAllow'] = true;
                }

                break;
            case 'projects':
                if (CONTROLLER == 'projects' && (PAGE_NAME == 'manage')) {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                if ($this->isAllowed('View Project', $roleAccess)) {
                    $returnArr['isAllow'] = true;
                }

                $type = $_COOKIE['PROJECTVIEW_TYPE'] ?? null;
                $type = $type ? explode('_', $type) : [];
                $projecturl = '';
                $projecturl = DEFAULT_PROJECTVIEW == 'manage' ? '/' : '/active-grid';
                $returnArr['dynamic_url'] = HTTP_ROOT . 'projects/manage' . $projecturl;

                break;
            #########################
            case 'tasks':
                // The Task Views page (CONTROLLER == 'taskviews') is a task view,
                // so the sidebar Tasks item highlights there too.
                if ((CONTROLLER == 'easycases' && PAGE_NAME == 'dashboard') || CONTROLLER == 'taskviews') {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                $returnArr['isAllow'] = true;

                $tskurl = '';
                $onclick = '';
                if (DEFAULT_VIEW_VALUE != 0) {
                    $tskurl = DEFAULT_VIEW_TASK;
                    if (DEFAULT_VIEW_TASK == 'tasks') {
                        $onclick = "checkHashLoad('tasks')";
                    } elseif (DEFAULT_VIEW_TASK == 'taskgroup') {
                        $tskurl = 'tasks';
                        $onclick = "return groupby('milestone')";
                    } elseif (DEFAULT_VIEW_TASK == 'taskgroups') {
                        $tskurl = 'taskgroups';
                        $onclick = "return ajaxCaseView('taskgroups')";
                    } else {
                        $onclick = "return checkHashLoad('milestonelist')";
                    }
                } else {
                    $tskurl = DEFAULT_TASKVIEW == 'milestonelist' ? 'milestonelist' : 'tasks';
                    if (DEFAULT_TASKVIEW == 'tasks') {
                        $onclick = "checkHashLoad('tasks')";
                    } elseif (DEFAULT_TASKVIEW == 'task_group') {
                        $onclick = "return groupby('milestone')";
                    } elseif (DEFAULT_TASKVIEW == 'taskgroups') {
                        $tskurl = 'taskgroups';
                        $onclick = "return ajaxCaseView('taskgroups')";
                    } else {
                        $onclick = "return checkHashLoad('milestonelist')";
                    }
                }
                if ($url == HTTP_ROOT . 'dashboard#/' . $tskurl) {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                $returnArr['url'] = $url;

                // Sidebar Tasks lands on the TaskViews app rather than the
                // legacy dashboard list. The legacy page renders a different
                // navigation strip, so routing there swapped the nav out
                // mid-session. To restore the old behaviour, put back:
                //   $returnArr['dynamic_url'] = HTTP_ROOT . 'dashboard#/' . $tskurl;
                //   $returnArr['dynamic_a_click'] = $onclick . ';';
                $returnArr['dynamic_url'] = HTTP_ROOT . 'task-views';
                $returnArr['dynamic_a_click'] = '';

                break;
            #########################
            case 'time log':
                if (CONTROLLER == 'easycases' && (PAGE_NAME == 'timelog')) {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                // Mirror the gating pattern used for 'projects' above: only
                // surface the Time Log menu when the role has at least one
                // Timelog-module action enabled. There's no single "View
                // Time Log" action in the actions table (closest are
                // Manual Time Entry / Start Timer for everyone, View All
                // Timelog for managers, View Resource Utilization for
                // resourcing). Any of these means the menu is useful;
                // none of them means it's an empty entry that 404s the
                // user when clicked. SES_TYPE 1/2 bypass via isAllowed.
                $hasTimelogAccess = $this->isAllowed('Manual Time Entry', $roleAccess)
                    || $this->isAllowed('Start Timer', $roleAccess)
                    || $this->isAllowed('View All Timelog', $roleAccess)
                    || $this->isAllowed('View Resource Utilization', $roleAccess)
                    || $this->isAllowed('View Resource Allocation Report', $roleAccess);
                if (!$hasTimelogAccess) {
                    return null;
                }
                $returnArr['isAllow'] = true;

                // The seeded menu row still carries the old AngularJS URL
                // (dashboard#/timelog). That page was removed from this
                // edition, so it hung on its loading placeholders forever
                // (public issue #13). Override it here rather than migrate the
                // menus table, so existing installs are fixed by the upgrade
                // itself.
                $returnArr['dynamic_url'] = HTTP_ROOT . 'log-times';

                if (CONTROLLER == 'log-times' || CONTROLLER == 'logtimes') {
                    $returnArr['active_class'] = 'active ' . $grad;
                }


                break;
            #########################
            case 'users':
                if (CONTROLLER == 'users' && (PAGE_NAME == 'manage')) {
                    $returnArr['active_class'] = 'active ' . $grad;
                }
                if ($this->isAllowed('View Users', $roleAccess)) {
                    $returnArr['isAllow'] = true;
                }

                break;
            #########################
            case 'time log list view':
                // Submenu under "Time Log" — gate on the same any-of
                // Timelog actions as the parent menu so a user without
                // Timelog access doesn't see the sub-item either.
                $hasTimelogListAccess = $this->isAllowed('Manual Time Entry', $roleAccess)
                    || $this->isAllowed('Start Timer', $roleAccess)
                    || $this->isAllowed('View All Timelog', $roleAccess)
                    || $this->isAllowed('View Resource Utilization', $roleAccess)
                    || $this->isAllowed('View Resource Allocation Report', $roleAccess);
                if (!$hasTimelogListAccess) {
                    return null;
                }
                $returnArr['isAllow'] = true;
                $returnArr['dynamic_url'] = HTTP_ROOT . 'log-times';

                break;
            #########################
            default:
                $menuMeta = json_decode($menu['meta'] ?? '', true);
                if ($menuMeta['superset_dashboard'] ?? null) {
                    $returnArr['isAllow'] = $this->checkSupersetPermission($menu, $roleAccess);
                }

                break;
        }

        return $returnArr;
    }
    public function displayKanbanOrBoard()
    {
        if (in_array($_SESSION['project_methodology'], ['simple', 'scrum', 'kanban'])) {
            return '<span class="kanban-or-board">' . __('Kanban') . '</span>';
        } else {
            return '<span class="kanban-or-board">' . __('Board') . '</span>';
        }
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

    public function format_second_hrmin_pad($totalsecs = '')
    {
        $hours = $mins = '00';

        if (!empty($totalsecs)) {
            $totalsecs = (int) $totalsecs;

            $hours = floor($totalsecs / 3600) > 0 ? strval(floor($totalsecs / 3600)) : '00';
            $mins = round(($totalsecs % 3600) / 60) > 0 ? strval(round(($totalsecs % 3600) / 60)) : '00';
        }

        // Ensure $hours and $mins are treated as strings
        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $mins = str_pad($mins, 2, '0', STR_PAD_LEFT);

        return $hours . ':' . $mins;
    }

    public function isAllowZapier($company_id)
    {
        return 0;
    }

    /**
     * Check if user has access to Defect module.
     *
     * @return int 1 if allowed, 0 if not allowed
     */
    public function isAllowedDefectModule()
    {
        return 1;
    }

    /**
     * Check if SSO (Single Sign-On) feature is enabled.
     *
     * @return int 1 if enabled, 0 if disabled
     */
    /**
     * Check if a company has active Developer API keys and API is enabled.
     *
     * @param int|null $companyId Company ID to check (defaults to session company)
     * @return bool True if API is active and company has active API keys, false otherwise
     */
    public function hasActiveApiKeys($companyId = null)
    {
        if(!Plugin::isLoaded('DeveloperApi')) {
            return false;
        }

        try {
            $companyId = $companyId ?? SES_COMP;

            if (empty($companyId)) {
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            // If plugin not loaded or table doesn't exist, return false
            return false;
        }
    }

    public function isZoomOn()
    {
        return 0;
    }
    public function getEpicId()
    {
        return TableRegistry::getTableLocator()->get('Types')->getEpicId();
    }
    public function getFeatureId()
    {
        return TableRegistry::getTableLocator()->get('Types')->getFeatureId();
    }
    public function fetchUsers($company_id)
    {
        $Project = TableRegistry::getTableLocator()->get('Projects');
        $allUsers = $Project->fetchCompUser($company_id);

        return $allUsers;
    }

    public function getProjects()
    {
        $projectTable = TableRegistry::getTableLocator()->get('Projects');
        $projectUserTable = TableRegistry::getTableLocator()->get('ProjectUsers');

        $projectUserData = $projectUserTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'project_id',
            'conditions' => ['ProjectUsers.user_id' => SES_ID, 'ProjectUsers.company_id' => SES_COMP],
        ])->toArray();

        $projectList = [];
        if (!empty($projectUserData)) {
            $projectConditions = [
                'Projects.id IN' => array_values($projectUserData),
                'Projects.company_id' => SES_COMP,
                'Projects.isactive' => 1,
            ];
            $projectList = $projectTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
                'conditions' => $projectConditions,
            ]);
        }

        return $projectList;
    }

    public function getDefectCases($projectId = null)
    {
        $easycaseTable = TableRegistry::getTableLocator()->get('Easycases');
        $projectId = $projectId ?? $GLOBALS['curProjId'] ?? null;
        if (!empty($projectId)) {
            $caseList = $easycaseTable->find('all', [
                'conditions' => [
                    'Easycases.project_id' => $projectId,
                    'istype' => 1,
                ],
            ])->disableHydration()->toArray();
        }

        return $caseList ?? [];
    }

    public function getDefectIssueType()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectSeverity()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectUserList($projectId = null): array
    {
        $projectUserTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $companyUserTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $userTable = TableRegistry::getTableLocator()->get('Users');

        $projectUserList = $projectUserTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'user_id',
            'conditions' => ['ProjectUsers.project_id' => $projectId ?? $GLOBALS['curProjId']],
        ])->toArray();

        $companyUserList = $companyUserTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'user_id',
            'conditions' => [
                'CompanyUsers.user_id IN' => $projectUserList,
                'CompanyUsers.company_id' => SES_COMP,
                'CompanyUsers.is_active' => 1,
                'CompanyUsers.user_type <=' => 3,
            ],
        ])->toArray();

        $userList = $userTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'name',
            'conditions' => ['Users.id IN' => $companyUserList],
        ])->toArray();

        return $userList;
    }

    public function getDefectPhase()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectCategories()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectOrigin()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectResolution()
    {
        return []; // OSS: bug tracking removed
    }

    public function getFixedVersion()
    {
        return []; // OSS: bug tracking removed
    }

    public function getAffectVersion()
    {
        return []; // OSS: bug tracking removed
    }

    public function getDefectRootCause()
    {
        return []; // OSS: bug tracking removed
    }

    public function isTestCaseManagerOn()
    {
        return array_key_exists('TestCaseManager', Configure::read('plugins', []));
    }

    public function isDmsOn()
    {
        if (!(defined('DMS_ENABLED') && DMS_ENABLED === true
            && array_key_exists('Dms', Configure::read('plugins', [])))) {
            return false;
        }
        // Owner and Admin always have access
        if (defined('SES_TYPE') && (int) SES_TYPE <= 2) {
            return true;
        }
        // Check module-level action AND plugin-level doc_view permission
        if (!$this->_hasModuleAction('view_documents')) {
            return false;
        }
        // Also check DMS-internal doc_view permission
        return $this->_hasDmsPermission('doc_view');
    }

    /**
     * Check if the current user's role has a specific DMS plugin permission.
     */
    private function _hasDmsPermission(string $permKey): bool
    {
        $roleId    = defined('SES_ROLE') ? (int) SES_ROLE : 0;
        $companyId = defined('SES_COMP') ? (int) SES_COMP : 0;
        if (!$roleId) return false;

        try {
            $conn = \Cake\Datasource\ConnectionManager::get('default');
            $stmt = $conn->execute(
                "SELECT is_allowed FROM dms_permissions
                 WHERE role_id = ? AND permission_key = ? AND company_id = ?
                 LIMIT 1",
                [$roleId, $permKey, $companyId]
            );
            $row = $stmt->fetch('assoc');
            if ($row) {
                return (int) $row['is_allowed'] === 1;
            }
            // No DB row should reach this point in normal operation because
            // RolesController::_seedPluginPermissionsForModules seeds rows
            // when a role is created/edited with the Documents module. If
            // we still land here (role inserted via raw SQL, migration
            // gap, etc.), deny conservatively — admin can fix via the
            // Settings → Permissions matrix.
            return false;
        } catch (\Exception $e) {
            return true; // Allow if table doesn't exist yet
        }
    }

    public function isRiskManagementOn()
    {
        if (!(defined('RISK_MANAGEMENT_ENABLED') && RISK_MANAGEMENT_ENABLED === true
            && array_key_exists('RiskManagement', Configure::read('plugins', [])))) {
            return false;
        }
        // Owner and Admin always have access
        if (defined('SES_TYPE') && (int) SES_TYPE <= 2) {
            return true;
        }
        // Check if user's role has "View Risk Management" permission
        return $this->_hasModuleAction('view_risk_management');
    }

    public function isAttendanceLeaveOn()
    {
        if (!(defined('ATTENDANCE_LEAVE_ENABLED') && ATTENDANCE_LEAVE_ENABLED === true
            && Plugin::isLoaded('AttendanceLeave'))) {
            return false;
        }
        return (new \AttendanceLeave\Service\PermissionService())->isModuleEnabledForRole();
    }

    public function isScaledAgileOn()
    {
        if (!(defined('SCALED_AGILE_ENABLED') && SCALED_AGILE_ENABLED === true
            && Plugin::isLoaded('ScaledAgile'))) {
            return false;
        }
        // Owner and Admin always have access
        if (defined('SES_TYPE') && (int) SES_TYPE <= 2) {
            return true;
        }
        // Delegate role/permission check to the plugin's own service —
        // never hardcode permission keys here (see ATTENDANCE_LEAVE_PR89_REVIEW_FIXES.md).
        return (new \ScaledAgile\Service\PermissionService())->isModuleEnabledForRole();
    }
    public function isVersionReleaseOn()
    {
        if (!(defined('VERSION_RELEASE_ENABLED') && VERSION_RELEASE_ENABLED === true
            && Plugin::isLoaded('VersionRelease'))) {
            return false;
        }
        // Owner and Admin always have access
        if (defined('SES_TYPE') && (int) SES_TYPE <= 2) {
            return true;
        }
        // Delegate role/permission check to the plugin's own service —
        // never hardcode permission keys here (see ATTENDANCE_LEAVE_PR89_REVIEW_FIXES.md).
        return (new \ScaledAgile\Service\PermissionService())->isModuleEnabledForRole();
        // Check if user's role has "View Versions & Releases" permission
        return $this->_hasModuleAction('view_version_release');
    }
    public function isOutlookIntegrationOn()
    {
        if (!Configure::read('OutlookIntegration.enabled') || !Plugin::isLoaded('OutlookIntegration')) {
            return false;
        }
        if (defined('SES_TYPE') && (int) SES_TYPE <= 2) {
            return true;
        }
        return $this->_hasModuleAction('view_outlook_integration');
    }

    /**
     * Check if the current user's role has a specific module-level action allowed.
     * Used by isDmsOn(), isRiskManagementOn(), isAttendanceLeaveOn(), isScaledAgileOn() to gate sidebar visibility.
     * Used by isDmsOn(), isRiskManagementOn(), isAttendanceLeaveOn(),
     * isOutlookIntegrationOn() to gate sidebar visibility.
     */
    private function _hasModuleAction(string $actionUniqId): bool
    {
        $roleId    = defined('SES_ROLE') ? (int) SES_ROLE : 0;
        $companyId = defined('SES_COMP') ? (int) SES_COMP : 0;
        if (!$roleId) return false;

        try {
            $conn = \Cake\Datasource\ConnectionManager::get('default');
            $stmt = $conn->execute(
                "SELECT ra.is_allowed FROM role_actions ra
                 JOIN actions a ON ra.action_id = a.id
                 WHERE a.uniq_id = ?
                   AND ra.role_id = ?
                   AND ra.company_id IN (0, ?)
                 ORDER BY ra.company_id DESC
                 LIMIT 1",
                [$actionUniqId, $roleId, $companyId]
            );
            $row = $stmt->fetch('assoc');
            return $row ? (int) $row['is_allowed'] === 1 : false;
        } catch (\Exception $e) {
            return true; // Allow if tables don't exist yet
        }
    }
    public function isWikiEnabled($companyId = null)
    {
        return false; // OSS: feature removed
        if (!defined('SES_COMP')) {
            return false;
        }

        $companyId ??= SES_COMP;
        if (Plugin::isLoaded('Wiki')) {
            try {
                $wikiInstancesTable = TableRegistry::getTableLocator()->get('Wiki.WikiInstances');
                $wikiApiKeysTable = TableRegistry::getTableLocator()->get('Wiki.WikiApiKeys');

                $hasWikiInstance = $wikiInstancesTable->exists(['company_id' => $companyId]);
                $hasWikiApiKey = $wikiApiKeysTable->exists(['company_id' => $companyId]);

                return $hasWikiInstance && $hasWikiApiKey;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function isEpicImportEnabled($companyId = null)
    {
        return true;
    }
    public function isAdvancedImportEnabled($companyId = null)
    {
        return true;
    }

    public function checkSupersetPermission($menu, $roleAccess)
    {
        return false; // OSS: feature removed
        // Restrict dashboard access to admin and owner roles only (SES_TYPE 1 or 2)
        if (!defined('SES_TYPE') || !in_array(SES_TYPE, [1, 2])) {
            return false;
        }

        $menuMeta = json_decode($menu['meta'] ?? '', true);
        $dashboardUUID = $menuMeta['superset_embed_uuid'] ?? null;
        if (!empty($dashboardUUID)) {
            $supersetDashboardsTable = TableRegistry::getTableLocator()->get('SuperSet.SupersetDashboards');
            // Check for shared dashboards (company_id = 0) or company-specific dashboards
            $dashboard = $supersetDashboardsTable->find()
                ->where([
                    'superset_embed_uuid' => $dashboardUUID,
                    'company_id IN' => [0, SES_COMP] // Shared (0) or company-specific
                ])
                ->first();

            // Allow access to shared dashboards or company-specific dashboards
            if (!empty($dashboard)) {
                return true;
            }
        }

        return false;
    }

    public function isGuestEnabled(): bool
    {
        return boolval(Configure::read('GuestAccess.enabled'));
    }

    public function isCriticalEnabled(): bool
    {
        return false; // OSS: feature removed — critical_path tables are dropped
    }


    public function customFieldLimit()
    {
        return Configure::read('customFieldLimit', '5');
    }
}
