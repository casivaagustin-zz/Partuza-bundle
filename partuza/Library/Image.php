<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

class Image {

  static public function by_size($file_path, $width, $height, $force = false) {
    $width = (empty($width) || ! is_numeric($width)) ? '96' : $width;
    $height = (empty($height) || ! is_numeric($height)) ? '96' : $height;
    $thumb = self::normalize_thumb($file_path, $width, $height);
    if ($force) {
      $prefix = substr($file_path, 0, strrpos($file_path, '.'));
      $ext = substr($file_path, strrpos($file_path, '.') + 1);
      // remove all cached thumbnails so they get regenerated
      foreach (glob("$prefix*.*x*.$ext") as $file) {
        @unlink($file);
      }
    }
    $file = str_replace('//', '/', dirname($file_path) . "/" . basename($thumb));
    if (! file_exists($thumb)) {
      if (! Image::thumbnail($file_path, $width, $height)) {
        return false;
      }
    }
    return str_replace(PartuzaConfig::get('site_root'), '', $file);
  }

  static public function thumbnail($file_path, $desired_width = 96, $desired_height = 96) {
    $ext = substr($file_path, strrpos($file_path, '.') + 1);
    $thumb = self::normalize_thumb($file_path, $desired_width, $desired_height);
    if (! file_exists($file_path)) {
      return false;
    }
    // These are the ratio calculations
    if (! $size = @GetImageSize($file_path)) {
      return false;
    }
    $width = $size[0];
    $height = $size[1];
    if ($width > 0 && $height > 0) {
      $wfactor = $desired_width / $width;
      $hfactor = $desired_height / $height;
      if ($wfactor < $hfactor) {
        $factor = $wfactor;
      } else {
        $factor = $hfactor;
      }
    }
    if (isset($factor) && $factor < 1) {
      $twidth = ceil($factor * $width);
      $theight = ceil($factor * $height);
      Image::convert($file_path, $thumb, $twidth, $theight);
    } else {
      if (file_exists($thumb)) {
        @unlink($thumb);
      }
      if (function_exists('symlink')) {
        if (! symlink($file_path, $thumb)) {
          die("Permission denied on generating thumbnail symlink ($file, $thumb)");
        }
      } else {
        // php on windows doesn't know how to symlink so copy instead
        if (! copy($file_path, $thumb)) {
          die("Permission denied on generating thumbnail copy ($file,$thumb)");
        }
      }
    }
    return true;
  }

  static public function convert($source, $destination, $desired_width = null, $desired_height = null) {
    Image::createImage($source, $destination, $desired_width, $desired_height);
    if (file_exists($destination)) {
      @chmod($destination, 0664);
    } else {
      //die("Failed to generate thumbnail, check directory permissions and the availability of <b>gd.</b>");
    }
  }

  static public function createImage($source, $destination, $desired_width, $desired_height) {
    // Capture the original size of the uploaded image
    if (! $info = getimagesize($source)) {
      return false;
    }
    $src = false;
    switch ($info['mime']) {
      case 'image/jpeg':
        $src = imagecreatefromjpeg($source);
        break;
      case 'image/gif':
        $src = imagecreatefromgif($source);
        break;
      case 'image/png':
        $src = imagecreatefrompng($source);
        break;
    }
    if (! $src) {
      return false;
    }
    if ($desired_width && $desired_height) {
	    $tmp_image = @imagecreatetruecolor($desired_width, $desired_height);
	    // this line actually does the image resizing
	    // copying from the original image into the $tmp_image image
	    if (! @imagecopyresampled($tmp_image, $src, 0, 0, 0, 0, $desired_width, $desired_height, $info[0], $info[1])) {
	      @imagedestroy($src);
	      return false;
	    }
	    @unlink($destination);
      @imagedestroy($src);
      $src = &$tmp_image;
    }
    switch ($info['mime']) {
      case 'image/jpeg':
        $ret = @imagejpeg($src, $destination);
        break;
      case 'image/gif':
        imagetruecolortopalette($src, true, 256);
        $ret = @imagegif($src, $destination);
        break;
      case 'image/png':
        $ret = @imagepng($src, $destination);
        break;
    }
    @imagedestroy($src);
    if (! $ret) {
      return false;
    }
    return true;
  }

  static private function normalize_thumb($file_path, $width, $height) {
    $suffix = "{$width}x{$height}";
    // parse the file name so that '1.jpg' and '1.128x128.jpg' both become '1.96x96.jpg'
    $ext = substr($file_path, strrpos($file_path, '.') + 1);
    $file_name = basename($file_path);
    $base_name = substr($file_name, 0, strpos($file_name, '.'));
    // we now have the base component (1), the suffix (96x96) and the extension (jpg), assemble that into the proper file name
    $thumb = dirname($file_path) . '/' . $base_name . '.' . $suffix . '.' . $ext;
    //die("thumb: $thumb");
    return $thumb;
  }
}
