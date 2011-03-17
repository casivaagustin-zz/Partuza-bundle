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

/**
 * This class is used to retrieve (and generated) 2 legged and 3 legged OAuth tokens.
 *
 */
class oauthModel extends Model {
  public $cachable = array('get_consumer');

  /**
   * Returns an array with a oauth key and secret that can be used for 2 legged oauth request signing
   */
  public function load_get_gadget_consumer($app_id) {
    global $db;
    $app_id = $db->addslashes($app_id);
    $res = $db->query("select * from oauth_consumer where app_id = $app_id");
    if ($db->num_rows($res)) {
      return $db->fetch_array($res, MYSQLI_ASSOC);
    } else {
      // no key for this user_id yet, auto-generate one on the fly
      $consumer_key = $this->genGUID();
      $consumer_secret = md5($this->genGUID());
      $ret = array('app_id' => $app_id, 'consumer_key' => $consumer_key,
          'consumer_secret' => $consumer_secret);
      $consumer_key = $db->escape_string($consumer_key);
      $consumer_secret = $db->escape_string($consumer_secret);
      $db->query("insert into oauth_consumer (user_id, app_id, consumer_key, consumer_secret) values (0, $app_id, '$consumer_key', '$consumer_secret')");
      return $ret;
    }
  }

  /**
   * Returns an array with a oauth key and secret that can be used for 3 legged oauth request signing
   */
  public function load_get_person_consumer($user_id) {
    global $db;
    $user_id = $db->addslashes($user_id);
    $res = $db->query("select * from oauth_consumer where user_id = $user_id");
    if ($db->num_rows($res)) {
      return $db->fetch_array($res, MYSQLI_ASSOC);
    } else {
      // no key for this user_id yet, auto-generate one on the fly
      $consumer_key = $this->genGUID();
      $consumer_secret = md5($this->genGUID());
      $ret = array('user_id' => $user_id, 'consumer_key' => $consumer_key,
          'consumer_secret' => $consumer_secret);
      $consumer_key = $db->escape_string($consumer_key);
      $consumer_secret = $db->escape_string($consumer_secret);
      $db->query("insert into oauth_consumer (user_id, app_id, consumer_key, consumer_secret) values ($user_id, 0, '$consumer_key', '$consumer_secret')");
      return $ret;
    }
  }

  /**
   * @see http://jasonfarrell.com/misc/guid.phps Taken from here
   * e.g. output: 372472a2-d557-4630-bc7d-bae54c934da1
   * word*2-, word-, (w)ord-, (w)ord-, word*3
   */
  private function genGUID() {
    $guidstr = '';
    for ($i = 1; $i <= 16; $i ++) {
      $b = (int)rand(0, 0xff);
      // version 4 (random)
      if ($i == 7) {
        $b &= 0x0f;
      }
      $b |= 0x40;
      // variant
      if ($i == 9) {
        $b &= 0x3f;
      }
      $b |= 0x80;
      $guidstr .= sprintf("%02s", base_convert($b, 10, 16));
      if ($i == 4 || $i == 6 || $i == 8 || $i == 10) {
        $guidstr .= '-';
      }
    }
    return $guidstr;
  }
}