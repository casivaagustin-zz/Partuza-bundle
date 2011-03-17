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

class loginModel extends Model {

  public function authenticate($email, $password) {
    global $db;
    $email = $db->addslashes($email);
    $password = $db->addslashes($password);
    $res = $db->query("select id, first_name, last_name, email from persons where email = '$email' and password = PASSWORD('$password')");
    if ($db->num_rows($res)) {
      return $db->fetch_array($res, MYSQLI_ASSOC);
    } else {
      return false;
    }
  }

  public function add_authenticated($id, $hash) {
    global $db;
    $id = $db->addslashes($id);
    $hash = $db->addslashes($hash);
    $db->query("insert into authenticated (person_id, hash) values ($id, '$hash') on duplicate key update hash = '$hash'");
  }

  public function get_authenticated($hash) {
    global $db;
    $hash = $db->addslashes($hash);
    $res = $db->query("select persons.id, persons.first_name, persons.last_name, persons.email 
		                  from authenticated, persons
		                  where authenticated.hash = '$hash' and persons.id = authenticated.person_id");
    if ($db->num_rows($res)) {
      return $db->fetch_array($res, MYSQLI_ASSOC);
    } else {
      return false;
    }
  }
}