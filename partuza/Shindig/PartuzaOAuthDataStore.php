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
 * Partuza's implementation of the OAuthDataStore
 */
class PartuzaOAuthDataStore extends OAuthDataStore {
  private $db;

  public function __construct() {
    global $db;
    // this class is used in 2 different contexts, either through partuza where we have a Db class
    // or through Shindig's social API, in which case we have to create our own db handle
    if (isset($db) && $db instanceof DB) {
      // running in partuza's context
      $this->db = $db->get_handle();
    } else {
      // running in shindig's context
      // one of the class paths should point to partuza's document root, abuse that fact to find our config
      $extension_class_paths = Config::get('extension_class_paths');
      foreach (explode(',', $extension_class_paths) as $path) {
        if (file_exists($path . "/PartuzaDbFetcher.php")) {
          $configFile = $path . '/../html/config.php';
          if (file_exists($configFile)) {
            //NOTE: since $config is not in the $global scope, this inclusion won't overwrite any global $config's
            include $configFile;
            break;
          }
        }
      }
      if (! isset($config)) {
        throw new Exception("Could not locate partuza's configuration file while scanning extension_class_paths ({$extension_class_paths})");
      }
      $this->db = mysqli_connect($config['db_host'], $config['db_user'], $config['db_passwd'], $config['db_database']);
      mysqli_select_db($this->db, $config['db_database']);
    }
  }

  public function lookup_consumer($consumer_key) {
    $consumer_key = mysqli_real_escape_string($this->db, trim($consumer_key));
    $res = mysqli_query($this->db, "select user_id, app_id, consumer_key, consumer_secret from oauth_consumer where consumer_key = '$consumer_key'");
    if (mysqli_num_rows($res)) {
      $ret = mysqli_fetch_array($res, MYSQLI_ASSOC);
      return new OAuthConsumer($ret['consumer_key'], $ret['consumer_secret'], null);
    }
    return null;
  }

  public function lookup_token($consumer, $token_type, $token) {
    $token_type = mysqli_real_escape_string($this->db, $token_type);
    $consumer_key = mysqli_real_escape_string($this->db, $consumer->key);
    $token = mysqli_real_escape_string($this->db, $token);
    $res = mysqli_query($this->db, "select * from oauth_token where type = '$token_type' and consumer_key = '{$consumer_key}' and token_key = '$token'");
    if (mysqli_num_rows($res)) {
      $ret = mysqli_fetch_array($res, MYSQLI_ASSOC);
      return new OAuthToken($ret['token_key'], $ret['token_secret']);
    }
    throw new OAuthException("Unexpected token type ($token_type) or unknown token");
  }

  public function lookup_nonce($consumer, $token, $nonce, $timestamp) {
    $timestamp = mysqli_real_escape_string($this->db, $timestamp);
    $nonce = mysqli_real_escape_string($this->db, $nonce);
    $res = mysqli_query($this->db, "select nonce from oauth_nonce where nonce_timestamp = $timestamp and nonce = '$nonce'");
    if (! mysqli_num_rows($res)) {
      $nonce = mysqli_real_escape_string($this->db, $nonce);
      mysqli_query($this->db, "insert into oauth_nonce (nonce, nonce_timestamp) values ('$nonce', $timestamp)");
      return null;
    }
    $ret = mysqli_fetch_array($res, MYSQLI_ASSOC);
    return $ret['nonce'];
  }

  public function new_request_token($consumer, $token_secret = null) {
    $consumer_key = mysqli_real_escape_string($this->db, $consumer->key);
    $consumer_secret = mysqli_real_escape_string($this->db, $consumer->secret);
    $res = mysqli_query($this->db, "select user_id from oauth_consumer where consumer_key = '$consumer_key' and consumer_secret = '$consumer_secret'");
    if (mysqli_num_rows($res)) {
      $ret = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $user_id = mysqli_real_escape_string($this->db, $ret['user_id']);
      if ($token_secret === null) {
        $token_secret = md5(uniqid(rand(), true));
      }
      $token = new OAuthToken($this->genGUID(), $token_secret);
      $token_key = mysqli_real_escape_string($this->db, $token->key);
      $token_secret = mysqli_real_escape_string($this->db, $token->secret);
      mysqli_query($this->db, "insert into oauth_token 
              (consumer_key, type, token_key, token_secret, user_id) 
              values 
              ('$consumer_key', 'request', '$token_key', '$token_secret', $user_id)");
      return $token;
    } else {
      throw new OAuthException("Invalid consumer key ($consumer_key)");
    }
  }

  public function new_access_token($oauthToken, $consumer) {
    $org_token_key = $token_key = mysqli_real_escape_string($this->db, $oauthToken->key);
    $res = mysqli_query($this->db, "select * from oauth_token where type = 'request'
            and token_key = '$token_key'");
    if (mysqli_num_rows($res)) {
      $ret = mysqli_fetch_array($res, MYSQLI_ASSOC);
      if ($ret['authorized']) {
        $token = new OAuthToken($this->genGUID(), md5(uniqid(rand(), true)));
        $token_key = mysqli_real_escape_string($this->db, $token->key);
        $token_secret = mysqli_real_escape_string($this->db, $token->secret);
        $consumer_key = mysqli_real_escape_string($this->db, $ret['consumer_key']);
        $user_id = mysqli_real_escape_string($this->db, $ret['user_id']);
        @mysqli_query($this->db, "insert into oauth_token (consumer_key, type, token_key, token_secret, user_id) values ('$consumer_key', 'access', '$token_key', '$token_secret', $user_id)");
        mysqli_query($this->db, "delete from oauth_token where type = 'request' and token_key = '$org_token_key'");
        return $token;
      }
    }
    return null;
  }

  public function authorize_request_token($token) {
    $token = mysqli_real_escape_string($this->db, $token);
    $user_id = mysqli_real_escape_string($this->db, $_SESSION['id']);
    mysqli_query($this->db, "update oauth_token set authorized = 1, user_id = $user_id where token_key = '$token'");
  }

  public function get_user_id($token) {
    $token_key = mysqli_real_escape_string($this->db, $token->key);
    $res = mysqli_query($this->db, "select user_id from oauth_token where token_key = '$token_key'");
    if (mysqli_num_rows($res)) {
      list($user_id) = mysqli_fetch_row($res);
      return $user_id;
    }
    return null;
  }

  public function get_app_id($token) {
    $token_key = mysqli_real_escape_string($this->db, $token->key);
    $res = mysqli_query($this->db, "select app_id from oauth_consumer where consumer_key = '$token_key'");
    $ret = 0;
    if (mysqli_num_rows($res)) {
      list($ret) = mysqli_fetch_row($res);
    }
    return $ret;
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
