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

class openidModel extends Model {

  public function getOpenIdServer() {
    static $server = null;
    if (! isset($server)) {
      $server = new Auth_OpenID_Server($this->getOpenIdStore(), $this->buildURL());
    }
    return $server;
  }

  public function idUrl($id) {
    return str_replace('//profile', '/profile', PartuzaConfig::get('partuza_url') . '/profile/' . $id);
  }

  public function buildURL($action = null, $escaped = true) {
    $url = $this->getServerURL();
    if ($action) {
      $url .= '/' . $action;
    }
    return $escaped ? htmlspecialchars($url, ENT_QUOTES) : $url;
  }

  public function authCancel($info) {
    if ($info) {
      $this->setRequestInfo();
      $url = $info->getCancelURL();
    } else {
      $url = $this->getServerURL();
    }
    header('Location:' . $url);
  }

  public function setRequestInfo($info = null) {
    if (! isset($info)) {
      unset($_SESSION['request']);
    } else {
      $_SESSION['request'] = serialize($info);
    }
  }

  public function getRequestInfo() {
    return isset($_SESSION['request']) ? unserialize($_SESSION['request']) : false;
  }

  private function getServerURL() {
    return str_replace('//openid', '/openid', PartuzaConfig::get('partuza_url') . '/openid/auth');
  }

  private function getOpenIdStore() {
    require_once "Auth/OpenID/FileStore.php";
    return new Auth_OpenID_FileStore("/tmp/openid/server");
  }
}
