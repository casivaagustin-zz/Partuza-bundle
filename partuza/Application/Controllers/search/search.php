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

class searchController extends baseController {

  public function index($params) {
    $error = false;
    $results = array();
    $friends = array();
    if (! empty($_GET['q'])) {
      $people = $this->model('people');
      $friends = $people->get_friends($_SESSION['id']);
      $results = $people->search($_GET['q']);
    } else {
      $error = 'no search phrase given';
    }
    $this->template('search/search.php', array('results' => $results, 'friends' => $friends, 
        'error' => $error));
  }
}
