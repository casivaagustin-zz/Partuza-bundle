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

class loginController extends baseController {

  private function redirect() {
    $location = isset($_GET['redirect']) ? $_GET['redirect'] : '/';
    header('Location: ' . $location);
    die();
  }

  public function index($params) {
    $error = '';
    if (isset($_SESSION['id'])) {
      $this->redirect();
    }
    if (count($_POST)) {
      if (! empty($_POST['login_email']) && ! empty($_POST['login_password'])) {
        // registration went ok, set up the session (and cookie)
        if ($this->authenticate($_POST['login_email'], $_POST['login_password'])) {
          $this->redirect();
        } else {
          $error = 'Invalid email / password combination.';
        }
      } else {
        $error = 'Please fill in your email and password.';
      }
    }
    $this->template('login/login.php', array('error' => $error));
  }
}
