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

class Dispatcher {
  public $url;
  public $last_modified;
  public $sitename;
  public $content_type = 'text/html';
  public $charset = 'UTF-8';
  public $_no_headers = false;

  public function __construct($url) {
    global $dispatcher;
    // since we 'run' the dispatcher from the constructor, it can't be put in the global
    //  address space before execution unless we do it in this constructor.
    $dispatcher = $this;
    $this->url = $url;
    $this->sitename = $_SERVER['SERVER_NAME'];
    // Run the application
    $this->run();
  }

  public function __destruct() {
    // header magic for all events, adds content size header, cache controls
    // plus it generates last-modified and etag headers, and if the browser already has the page
    // in cache (if-modified-since or etag matching) then send a 304 : not modified instead of the whole page
    //return;
    if (! $this->_no_headers) {
      // Promote our XRDS location
      header("X-XRDS-Location: http://{$_SERVER['HTTP_HOST']}/xrds");
      // first send all the headers that help the browser understand this page, length, content type, charset, etc
      header("Content-Type: $this->content_type; charset={$this->charset}");
      header('Connection: Keep-Alive');
      header('Keep-Alive: timeout=15, max=30');
      header('Accept-Ranges: bytes');
      // Normally i would put a 5 min caching here at least, but on social sites data tends to
      // change quite rapidly, so ... no cache it is
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate, private');
      header('Pragma: no-cache');
      header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");
      $content = ob_get_contents();
      // Obey browsers (or proxy's) request to send a fresh copy if we recieve a no-cache pragma or cache-control request
      if (! isset($_SERVER['HTTP_PRAGMA']) || ! strstr(strtolower($_SERVER['HTTP_PRAGMA']), 'no-cache') && (! isset($_SERVER['HTTP_CACHE_CONTROL']) || ! strstr(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache'))) {
        // If the browser send us a E-TAG check if it matches (sha1 sum of content), if so send a not modified header instead of content
        $etag = md5($content);
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
          header("ETag: \"$etag\"");
          if ($this->last_modified) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->last_modified) . ' GMT', true);
          }
          header("HTTP/1.1 304 Not Modified", true);
          header('Content-Length: 0', true);
          ob_end_clean();
          die();
        }
        header("ETag: \"$etag\"");
        // If no etag is present, then check if maybe this browser supports if_modified_since tags,
        // check it against our last_modified (if it's set)
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $this->last_modified && ! isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
          $if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
          if ($this->last_modified <= $if_modified_since) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->last_modified) . ' GMT', true);
            header("HTTP/1.1 304 Not Modified", true);
            header('Content-Length: 0', true);
            ob_end_clean();
            die();
          }
        }
        if ($this->last_modified) {
          header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->last_modified) . ' GMT');
        }
      }
    }
  }

  public function run() {
    global $controller;
    // To do etag etc support, we need output buffering, try to use compressed output where possible
    ob_start();
    $params = explode('/', str_replace(PartuzaConfig::get('web_prefix'), '', $this->url));
    // Run the application, dispatch the control to the correct Controller (or default to Home if no URL is given)
    if (! empty($params[1])) {
      $action = $params[1];
    } else {
      $params = array();
      $action = 'home';
    }
    $show404 = true;
    if (file_exists(PartuzaConfig::get('controllers_root') . "/$action/{$action}.php")) {
      include_once PartuzaConfig::get('controllers_root') . "/$action/{$action}.php";
      $controller = $action . 'Controller';
      if (class_exists($controller, false)) {
        $controller = new $controller($params);
        if (! empty($params[2]) && is_callable(array($controller, $params[2]))) {
          $show404 = false;
          $controller->$params[2]($params);
        } elseif (((isset($params[1]) && $params[1] == 'profile') || empty($params[2])) && is_callable(array(
            $controller, 'index'))) {
          $show404 = false;
          $controller->index($params);
        }
      }
    }
    if ($show404) {
      header("HTTP/1.0 404 Not Found", true);
      echo "<html><body><h1>404 - Not Found</h1></body></html>";
    }
  }
}