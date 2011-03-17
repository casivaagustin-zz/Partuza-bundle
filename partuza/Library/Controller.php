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

class Controller {

  // Input and output filtering to prevent SQL injection and XSS where required
  public function filter_output($string) {
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
  }

  public function filter_input($string) {
    global $db;
    return $db->addslashes($string);
  }

  // These functions wrap the nasty (but fast) global variables and objects
  public function set_modified($timestamp) {
    global $dispatcher;
    $dispatcher->last_modified = max($dispatcher->last_modified, $timestamp);
  }

  public function set_content_type($content_type) {
    global $dispatcher;
    $dispatcher->content_type = $content_type;
  }

  public function set_charset($charset) {
    global $dispatcher;
    $dispatcher->charset = $charset;
  }

  public function model($model) {
    include_once PartuzaConfig::get('models_root') . "/$model/$model.php";
    $model = "{$model}Model";
    return new $model();
  }

  public function template($template, $vars = array()) {
    // scope the $vars into local name space
    foreach ($vars as $key => $val) {
      $$key = $val;
    }
    // We also poke the modified time to when this template was changed, so that even
    // for 'static content' the last-modified time is always correct
    $this->set_modified(filemtime(PartuzaConfig::get('views_root') . "/$template"));
    include PartuzaConfig::get('views_root') . "/$template";
  }
}