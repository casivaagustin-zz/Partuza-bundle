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

class baseController extends Controller {

  public function __construct() {
    @session_start();
    // allow logins anywhere in the site
    if (! isset($_SESSION['id']) && ! empty($_POST['email']) && ! empty($_POST['password'])) {
      if ($this->authenticate($_POST['email'], $_POST['password'])) {
        // Redirect to self, but without post to prevent posting if the user refreshes the page
        // Login request to /openid/login page should not be redirected.
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/openid/login') {
          header("Location: {$_SERVER['REQUEST_URI']}");
          die();
        }
      }
    }
    if (! isset($_SESSION['username']) && isset($_COOKIE['authenticated'])) {
      // user is not logged in yet, but we do have a authenticated cookie, see if it's valid
      // and if so setup the session
      $login = $this->model('login');
      if (($user = $login->get_authenticated($_COOKIE['authenticated'])) !== false) {
        $this->set_session($user);
      }
    }
  }

  public function authenticate($email, $password) {
    $login = $this->model('login');
    if (($user = $login->authenticate($email, $password)) !== false) {
      // setup the session
      $this->set_session($user);
      // and set a cookie and store the authenticated state in the authenticated table
      $hash = sha1($email . $password);
      $login->add_authenticated($user['id'], $hash);
      // remeber cookie for 30 days, after which we'd like the user to authenticate again
      setcookie("authenticated", $hash, $_SERVER['REQUEST_TIME'] + (30 * 24 * 60 * 60), '/');
      return true;
    }
    return false;
  }

  private function set_session($user) {
    $_SESSION['id'] = $user['id'];
    $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
  }
}
