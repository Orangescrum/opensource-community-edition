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

use Cake\Controller\Component;
use Cake\Core\Configure;

/**
 * Image component
 */
class ImageComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    protected $save_to_file = true;
    protected $image_type = -1;
    protected $quality = 100;
    protected $max_x = 0;
    protected $max_y = 0;
    protected $cut_x = 0;
    protected $cut_y = 0;

    public StorageComponent $Storage;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        if (!empty(Configure::read('Storage'))) {
            $this->Storage = $this->getController()->loadComponent('Storage');
        }
    }

    public function SaveImage($im, $filename)
    {

        $res = null;

        // ImageGIF is not included into some GD2 releases, so it might not work
        // output png if gifs are not supported
        if (($this->image_type == 1)  && !function_exists('imagegif')) {
            $this->image_type = 3;
        }

        switch ($this->image_type) {
            case 1:
                if ($this->save_to_file) {
                    //$res = \imagegif($im,$filename);
                    $res = \imagegif($im, null);
                } else {
                    header('Content-type: image/gif');
                    $res = \imagegif($im);
                }
                break;
            case 2:
                if ($this->save_to_file) {
                    $res = \imagejpeg($im, null, $this->quality);
                } else {
                    header('Content-type: image/jpeg');
                    $res = \imagejpeg($im, null, $this->quality);
                }
                break;
            case 3:
                if (PHP_VERSION >= '5.1.2') {
                    // Convert to PNG quality.
                    // PNG quality: 0 (best quality, bigger file) to 9 (worst quality, smaller file)
                    $quality = intval(9 - min(round($this->quality / 10), 9));
                    if ($this->save_to_file) {
                        $res = \imagepng($im, null, $quality);
                    } else {
                        header('Content-type: image/png');
                        $res = \imagepng($im, null, $quality);
                    }
                } else {
                    if ($this->save_to_file) {
                        $res = \imagepng($im, $filename);
                    } else {
                        header('Content-type: image/png');
                        $res = \imagepng($im);
                    }
                }
                break;
        }

        return $res;
    }

    public function ImageCreateFromType($type, $filename)
    {
        $im = null;
        switch ($type) {
            case 1:
                $im = imagecreatefromgif($filename);
                break;
            case 2:
                $im = imagecreatefromjpeg($filename);
                break;
            case 3:
                $im = imagecreatefrompng($filename);
                break;
        }
        return $im;
    }

    // generate thumb from image and save it
    //function GenerateThumbFile($from_name, $to_name, $max_x, $max_y) {
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
    public function GenerateThumbFile($from_name1, $to_name, $max_x, $max_y, $filename)
    {

        $is_storage = !empty(Configure::read('Storage'));

        // if src is URL then download file first
        $from_name = ($is_storage && $filename != 'user.png') ? $this->Storage->generateTemporaryURL($from_name1) : $from_name1;
        $temp = false;
        if (substr($from_name, 0, 7) == 'https://') {
            $tmpfname = tempnam('tmp/', 'TmP-');
            $temp = @fopen($tmpfname, 'w');
            if ($temp) {
                @fwrite($temp, @file_get_contents($from_name)) or die('Cannot download image');
                @fclose($temp);
                $from_name = $tmpfname;
            } else {
                die('Cannot create temp file');
            }
        }

        // check if file exists
        $file_mime = '';
        if ($is_storage && $filename != 'user.png') {
            if (stristr($from_name1, 'case_editor_files')) {
                $info = $this->Storage->getObjectInfo(DIR_CASE_FILES_EDITOR_S3_FOLDER_T . $filename);
            } else {
                $info = $this->Storage->getObjectInfo(DIR_USER_PHOTOS_S3_FOLDER . $filename);
            }
            $file_mime = $info['type'] ?? '';
        } elseif (file_exists($from_name)) {
            $info = 1;
        } elseif (file_exists(DIR_USER_PHOTOS . 'user.png')) {
            $from_name = DIR_USER_PHOTOS . 'user.png';
            $info = 1;
        }
        if ($info) {
            // get source image size (width/height/type)
            // orig_img_type 1 = GIF, 2 = JPG, 3 = PNG

            $getimagesize = @getimagesize($from_name);
            $orig_x = $getimagesize[0];
            $orig_y = $getimagesize[1];
            $orig_img_type = $getimagesize['2'];
            if (!$file_mime) {
                $file_mime = $getimagesize['mime'];
            }

            // cut image if specified by user
            if ($this->cut_x > 0) {
                $orig_x = min($this->cut_x, $orig_x);
            }
            if ($this->cut_y > 0) {
                $orig_y = min($this->cut_y, $orig_y);
            }
            // should we override thumb image type?
            $this->image_type = ($this->image_type != -1 ? $this->image_type : $orig_img_type);

            // check for allowed image types
            if ($orig_img_type < 1 or $orig_img_type > 3) {
                die('Image type not supported');
            }

            if ($orig_x > $max_x or $orig_y > $max_y) {
                if (!$file_mime) {
                    $file_mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $from_name);
                }
                if ($file_mime) {
                    header("Content-Type:$file_mime");
                }
                // resize
                $per_x = $orig_x / $max_x;
                $per_y = $orig_y / $max_y;
                if ($per_y > $per_x) {
                    $max_x = $orig_x / $per_y;
                } else {
                    $max_y = $orig_y / $per_x;
                }
            } elseif ($orig_x < $max_x or $orig_y < $max_y) {
                $max_x = $orig_x;
                $max_y = $orig_y;

                if (!$file_mime) {
                    $file_mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $from_name);
                }
                if ($file_mime) {
                    header("Content-Type:$file_mime");
                }
            } else {
                // keep original sizes, i.e. just copy
                if ($this->save_to_file) {
                    @copy($from_name, $to_name);
                } else {
                    switch ($this->image_type) {
                        case 1:
                            header('Content-type: image/gif');
                            readfile($from_name);
                            break;
                        case 2:
                            header('Content-type: image/jpeg');
                            readfile($from_name);
                            break;
                        case 3:
                            header('Content-type: image/png');
                            readfile($from_name);
                            break;
                    }
                }
                return;
            }

            $max_x = intval($max_x);
            $max_y = intval($max_y);
            if ($this->image_type == 1) {
                // should use this function for gifs (gifs are palette images)
                $ni = \imagecreate($max_x, $max_y);
            } else {
                // Create a new true color image
                $ni = \imagecreatetruecolor($max_x, $max_y);
            }

            // Fill image with white
            $white = imagecolorallocate($ni, 255, 255, 255);

            imagefilledrectangle($ni, 0, 0, $max_x, $max_y, $white);
            // Create a new image from source file
            $im = $this->ImageCreateFromType($orig_img_type, $from_name);
            // Copy the palette from one image to another
            imagepalettecopy($ni, $im);
            // Copy and resize part of an image with resampling
            imagecopyresampled(
                $ni,
                $im,             // destination, source
                0,
                0,
                0,
                0,           // dstX, dstY, srcX, srcY
                $max_x,
                $max_y,       // dstW, dstH
                $orig_x,
                $orig_y
            );    // srcW, srcH

            // save thumb file
            $this->SaveImage($ni, $to_name);

            if ($temp) {
                unlink($tmpfname); // this removes the file
            }
        } else {
            //File doesn't exists
            echo 'Source image does not exist!';
            exit;
        }
    }
}
