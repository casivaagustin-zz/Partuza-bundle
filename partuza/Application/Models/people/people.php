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

class peopleModel extends Model {
  public $cachable = array('is_friend', 'get_person', 'get_person_info', 'get_friends', 'get_friends_count', 'get_friend_requests');
  // persons table supported fields.
  public $supported_fields = array('id','email','password','about_me','age','children','date_of_birth','drinker',
    'ethnicity','fashion','gender','happiest_when','humor','job_interests','living_arrangement','looking_for',
    'nickname','pets','political_views','profile_song','profile_url','profile_video','relationship_status',
    'religion','romance','scared_of','sexual_orientation','smoker','status','thumbnail_url','time_zone',
    'first_name','last_name','uploaded_size');

  public function load_is_friend($person_id, $friend_id) {
    global $db;
    $this->add_dependency('people', $person_id);
    $this->add_dependency('people', $friend_id);
    $person_id = $db->addslashes($person_id);
    $friend_id = $db->addslashes($friend_id);
    $res = $db->query("select * from friends where (person_id = $person_id and friend_id = $friend_id) or (person_id = $friend_id and friend_id = $person_id)");
    // return 0 instead of false, not to trip up the caching layer (who does a binary === false compare on data, so 0 == false but not === false)
    return $db->num_rows($res) != 0 ? true : 0;
  }

  public function remove_friend($person_id, $friend_id) {
    global $db;
    $this->invalidate_dependency('people', $person_id);
    $this->invalidate_dependency('people', $friend_id);
    $person_id = $db->addslashes($person_id);
    $friend_id = $db->addslashes($friend_id);
    $res = $db->query("delete from friends where (person_id = $person_id and friend_id = $friend_id) or (person_id = $friend_id and friend_id = $person_id)");
    return $db->affected_rows($res) != 0;
  }

  public function set_profile_photo($id, $url) {
    global $db;
    $this->invalidate_dependency('people', $id);
    $id = $db->addslashes($id);
    $url = $db->addslashes($url);
    $db->query("update persons set thumbnail_url = '$url' where id = $id");
  }

  public function save_person($id, $person) {
    global $db;
    $this->invalidate_dependency('people', $id);
    $id = $db->addslashes($id);
    foreach ($person as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        if ($val == '-') {
          $updates[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $updates[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($updates)) {
      $query = "update persons set " . implode(', ', $updates) . " where id = $id";
      $db->query($query);
    }
  }

  // if extended = true, it also queries all child tables
  // defaults to false since its a hell of a presure on the database.
  // remove once we add some proper caching
  public function load_get_person($id, $extended = false) {
    global $db;
    $this->add_dependency('people', $id);
    $id = $db->addslashes($id);
    $res = $db->query("select * from persons where id = $id");
    if (! $db->num_rows($res)) {
      throw new Exception("Invalid person");
    }
    $person = $db->fetch_array($res, MYSQLI_ASSOC);
    //TODO missing : person_languages_spoken, need to add table with ISO 639-1 codes
    $tables_addresses = array('person_addresses', 'person_current_location');
    $tables_organizations = array('person_jobs', 'person_schools');
    $tables = array('person_activities', 'person_body_type', 'person_books', 'person_cars',
        'person_emails', 'person_food', 'person_heroes', 'person_movies',
        'person_interests', 'person_music', 'person_phone_numbers', 'person_quotes',
        'person_sports', 'person_tags', 'person_turn_offs', 'person_turn_ons',
        'person_tv_shows', 'person_urls');
    foreach ($tables as $table) {
      $person[$table] = array();
      $res = $db->query("select * from $table where person_id = $id");
      while ($data = $db->fetch_array($res, MYSQLI_ASSOC)) {
        $person[$table][] = $data;
      }
    }
    foreach ($tables_addresses as $table) {
      $res = $db->query("select addresses.* from addresses, $table where $table.person_id = $id and addresses.id = $table.address_id");
      while ($data = $db->fetch_array($res)) {
        $person[$table][] = $data;
      }
    }
    foreach ($tables_organizations as $table) {
      $res = $db->query("select organizations.* from organizations, $table where $table.person_id = $id and organizations.id = $table.organization_id");
      while ($data = $db->fetch_array($res)) {
        $person[$table][] = $data;
      }
    }
    return $person;
  }

  /*
   * doing a select * on a large table is way to IO and memory expensive to do
   * for all friends/people on a page. So this gets just the basic fields required
   * to build a person expression:
   * id, email, first_name, last_name, thumbnail_url and profile_url
   */
  public function load_get_person_info($id) {
    global $db;
    $this->add_dependency('people', $id);
    $id = $db->addslashes($id);
    $res = $db->query("select id, email, first_name, last_name, thumbnail_url, profile_url from persons where id = $id");
    if (! $db->num_rows($res)) {
      throw new Exception("Invalid person");
    }
    return $db->fetch_array($res, MYSQLI_ASSOC);
  }

  public function load_get_friends($id, $limit = false) {
    global $db;
    $this->add_dependency('people', $id);
    $ret = array();
    $limit = $limit ? ' limit ' . $db->addslashes($limit) : '';
    $person_id = $db->addslashes($id);
    $res = $db->query("select person_id, friend_id from friends where person_id = $person_id or friend_id = $person_id $limit");
    while (list($p1, $p2) = $db->fetch_row($res)) {
      // friend requests are made both ways, so find the 'friend' in the pair
      $friend = $p1 != $person_id ? $p1 : $p2;
      $ret[$friend] = $this->get_person_info($friend);
    }
    return $ret;
  }

  public function load_get_friends_count($id) {
    global $db;
    $this->add_dependency('people', $id);
    $ret = array();
    $person_id = $db->addslashes($id);
    $res = $db->query("select count(person_id) from friends where person_id = $person_id or friend_id = $person_id");
    list($ret) = $db->fetch_row($res);
    return $ret;
  }

  public function add_friend_request($id, $friend_id) {
    global $db;
    try {
      $this->invalidate_dependency('friendrequest', $id);
      $this->invalidate_dependency('friendrequest', $friend_id);
      $person_id = $db->addslashes($id);
      $friend_id = $db->addslashes($friend_id);
      $db->query("insert into friend_requests values ($person_id, $friend_id)");
    } catch (DBException $e) {
      return false;
    }
    return true;
  }

  public function accept_friend_request($id, $friend_id) {
    global $db;
    $person_id = $db->addslashes($id);
    $friend_id = $db->addslashes($friend_id);
    try {
      // double check if a friend request actually exists (reversed friend/person since the request came from the other party)
      $db->query("delete from friend_requests where person_id = $friend_id and friend_id = $person_id");
      // -1 = sql error, 0 = no request was made, so can't accept it since the other party never gave permission
      if ($db->affected_rows() < 1) {
        die("couldnt delete friend request, means there was none?");
        return false;
      }
      // make sure there's not already a connection between the two the other way around
      $res = $db->query("select friend_id from friends where person_id = $friend_id and friend_id = $person_id");
      if ($db->num_rows($res)) {
        die("the relation already exists the other way around,bailing");
        return false;
      }
      $db->query("insert into friends values ($person_id, $friend_id)");

      //FIXME quick hack to put in befriending activities, move this to its own class/function soon
      // We want to create the friend activities on both people so we do this twice
      $time = $_SERVER['REQUEST_TIME'];
      foreach (array($friend_id => $person_id, $person_id => $friend_id) as $key => $val) {
        $res = $db->query("select concat(first_name, ' ', last_name) from persons where id = $key");
        list($name) = $db->fetch_row($res);
        $db->query("insert into activities (person_id, app_id, title, body, created) values ($val, 0, 'and <a href=\"/profile/$key\" rel=\"friend\">$name</a> are now friends.', '', $time)");
        $this->invalidate_dependency('activities', $key);
      }
    } catch (DBException $e) {
      die("sql error: " . $e->getMessage());
      return false;
    }
    $this->invalidate_dependency('friendrequest', $id);
    $this->invalidate_dependency('friendrequest', $friend_id);
    $this->invalidate_dependency('people', $id);
    $this->invalidate_dependency('people', $friend_id);
    return true;
  }

  public function reject_friend_request($id, $friend_id) {
    global $db;
    $this->invalidate_dependency('friendrequest', $id);
    $this->invalidate_dependency('friendrequest', $friend_id);
    $person_id = $db->addslashes($id);
    $friend_id = $db->addslashes($friend_id);
    try {
      $db->query("delete from friend_requests where person_id = $friend_id and friend_id = $person_id");
    } catch (DBException $e) {
      return false;
    }
    return true;
  }

  public function load_get_friend_requests($id) {
    global $db;
    $this->add_dependency('friendrequest', $id);
    $requests = array();
    $friend_id = $db->addslashes($id);
    $res = $db->query("select person_id from friend_requests where friend_id = $friend_id");
    while (list($friend_id) = $db->fetch_row($res)) {
      $requests[$friend_id] = $this->get_person($friend_id, false);
    }
    return $requests;
  }

  public function search($name) {
    global $db;
    $name = $db->addslashes($name);
    $ret = array();
    $res = $db->query("select id, email, first_name, last_name from persons where concat(first_name, ' ', last_name) like '%$name%' or email like '%$name%'");
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $row;
    }
    return $ret;
  }

  /*
   * get person info, need set field which we need.
   */
  public function get_person_fields($id, $fields) {
    global $db;
    $id = $db->addslashes($id);
    foreach ($fields as $val) {
      if (in_array($val, $this->supported_fields)) {
        $fields_adds[] = "`" . $db->addslashes($val) . "`";
      }
    }
    $res = $db->query("select " . implode(', ', $fields_adds) . " from persons where id = $id");
    if (! $db->num_rows($res)) {
      throw new Exception("Invalid person");
    }
    return $db->fetch_array($res, MYSQLI_ASSOC);
  }

  /*
   * set person info, need set field which we need.
   */
  public function set_person_fields($id, $fields) {
    global $db;
    $id = $db->addslashes($id);
    foreach ($fields as $key => $val) {
    	if (in_array($key, $this->supported_fields)) {
        if (is_null($val)) {
          $updates[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $updates[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($updates)) {
      $query = "update persons set " . implode(', ', $updates) . " where id = $id";
      $db->query($query);
      return $id;
    }
  }

  /*
   * if we can promise our code is safe, we can do it.
   * update media table use literal word, so do not escape update code.
   * for example update albums set uploaded_size = uploaded_size+1000; it will be easy.
   */
  public function literal_set_person_fields($id, $fields) {
    global $db;
    $id = $db->addslashes($id);
    foreach ($fields as $key => $val) {
    	if (in_array($key, $this->supported_fields)) {
    		$updates[] = "`" . $db->addslashes($key) . "` = " . $val ;
    	}
    }
      
    if (count($updates)) {
      $query = "update persons set " . implode(', ', $updates) . " where id = $id";
      $db->query($query);
      return $id;
    }
  }
}
