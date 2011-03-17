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

final class Language {

  static function time($timestamp) {
    return strftime('%X', $timestamp);
  }

  static function date($timestamp) {
    return strftime('%x', $timestamp);
  }

  static function date_overview($timestamp) {
    return strftime('%b %d %Y', $timestamp);
  }

  static function datetime($timestamp) {
    return trim(str_replace('CET', '', str_replace('CEST', '', strftime('%c', $timestamp))));
  }

  static function number_format($number) {
    global $language_number_format_locale;
    if (! isset($language_number_format_locale)) {
      $language_number_format_locale = localeconv();
    }
    return number_format($number, $language_number_format_locale['frac_digits'], $language_number_format_locale['decimal_point'], $language_number_format_locale['thousands_sep']);
  }

  static function money_format($number) {
    return money_format("%i", $number);
  }

  static function get_supported() {
    global $cache_language_get_supported;
    if (! isset($cache_language_get_supported)) {
      $languages = array('en');
      if ($languages != '') {
        $languages = explode(',', $languages);
        if (count($languages)) {
          reset($languages);
          while (list($key, $val) = each($languages)) {
            $languages[$key] = trim($val);
          }
        }
      } else {
        $languages = array();
      }
      $cache_language_get_supported = $languages;
    }
    return $cache_language_get_supported;
  }

  static function set($lang) {
    global $language, $language_base, $root;
    $language = $lang;
    $domain = 'messages';
    putenv("LANG=$language");
    setlocale(LC_ALL, $language);
    if (function_exists('bindtextdomain')) bindtextdomain($domain, "$root/share/locale/");
    if (function_exists('textdomain')) textdomain($domain);
    if ($language != 'C' && $language != '') {
      if (($pos = strpos($language, '_')) !== false) {
        $language = substr($language, 0, $pos);
      }
      if (($pos = strpos($language, '-')) !== false) {
        $language = substr($language, 0, $pos);
      }
      if (($pos = strpos($language, '.')) !== false) {
        $language = substr($language, 0, $pos);
      }
      $language_base = $language;
      header("Content-Language: $language_base");
    } else {
      $language_base = 'C';
    }
  }

  static function get_languages() {
    global $language_base;
    if (file_exists('/usr/share/locale/all_languages')) {
      $fp = fopen('/usr/share/locale/all_languages', 'r');
      $current_lang = '';
      $languages = array();
      while (! feof($fp)) {
        $str = trim(fgets($fp));
        if (substr($str, 0, 1) == '[') {
          $current_lang = str_replace('[', '', str_replace(']', '', $str));
        } elseif (substr($str, 0, 5) == 'Name=' && (strpos($str, '[') === false)) {
          if (! isset($languages[$current_lang])) {
            $languages[$current_lang] = substr($str, 5);
          }
        } elseif (substr($str, 0, 5) == 'Name[') {
          $str = str_replace('Name[', '', $str);
          $lang = substr($str, 0, strpos($str, ']'));
          if (strtolower($lang) == strtolower($language_base)) {
            $languages[$current_lang] = substr($str, strlen($lang) + 2);
          }
        }
      }
      fclose($fp);
      return $languages;
    }
    return false;
  }
}
