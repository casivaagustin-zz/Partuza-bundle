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

class applicationsModel extends Model {
  public $cachable = array('get_person_applications', 'get_all_applications', 'get_application_prefs',
      'get_person_application', 'get_application_by_id', 'get_application');

  public function load_get_person_applications($id) {
    global $db;
    $this->add_dependency('person_applications', $id);
    $this->add_dependency('person_application_prefs', $id);
    $ret = array();
    include_once PartuzaConfig::get('models_root') . "/oauth/oauth.php";
    $oauth = new oauthModel();
    $id = $db->addslashes($id);
    $res = $db->query("select applications.*, person_applications.id as mod_id from person_applications, applications where person_applications.person_id = $id and applications.id = person_applications.application_id");
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $this->add_dependency('applications', $row['id']);
      $row['user_prefs'] = $this->get_application_prefs($id, $row['id']);
      $row['oauth'] = $oauth->get_gadget_consumer($row['id']);
      $ret[] = $row;
    }
    return $ret;
  }

  public function load_get_all_applications() {
    global $db;
    include_once PartuzaConfig::get('models_root') . "/oauth/oauth.php";
    $oauth = new oauthModel();
    $ret = array();
    $res = $db->query("select * from applications where approved = 'Y' order by directory_title, title");
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $this->add_dependency('applications', $row['id']);
      $row['user_prefs'] = array();
      $row['oauth'] = $oauth->get_gadget_consumer($row['id']);
      $ret[] = $row;
    }
    return $ret;
  }

  public function set_application_pref($person_id, $app_id, $key, $value) {
    global $db;
    $this->invalidate_dependency('person_application_prefs', $person_id);
    $person_id = $db->addslashes($person_id);
    $app_id = $db->addslashes($app_id);
    $key = $db->addslashes($key);
    $value = $db->addslashes($value);
    $db->query("insert into application_settings (application_id, person_id, name, value) values ($app_id, $person_id, '$key', '$value')
					on duplicate key update value = '$value'");
  }

  public function load_get_application_prefs($person_id, $app_id) {
    global $db;
    $this->add_dependency('person_application_prefs', $person_id);
    $person_id = $db->addslashes($person_id);
    $app_id = $db->addslashes($app_id);
    $prefs = array();
    $res = $db->query("select name, value from application_settings where application_id = $app_id and person_id = $person_id");
    while (list($name, $value) = $db->fetch_row($res)) {
      $prefs[$name] = $value;
    }
    return $prefs;
  }

  public function load_get_person_application($person_id, $app_id, $mod_id) {
    global $db;
    $this->add_dependency('person_application_prefs', $person_id);
    $this->add_dependency('person_applications', $person_id);
    $this->add_dependency('applications', $app_id);
    $ret = array();
    $person_id = $db->addslashes($person_id);
    $app_id = $db->addslashes($app_id);
    $mod_id = $db->addslashes($mod_id);
    $res = $db->query("select url from applications where id = $app_id");
    if ($db->num_rows($res)) {
      list($app_url) = $db->fetch_row($res);
      $ret = $this->get_application($app_url);
      $ret['mod_id'] = $mod_id;
      $ret['user_prefs'] = $this->get_application_prefs($person_id, $app_id);
    }
    return $ret;
  }

  private function fetch_gadget_metadata($app_url, $securityToken) {
    
    foreach (array('profile', 'home', 'canvas', 'default') as $view) {
	    $request = json_encode(array(
	        'context' => array('country' => 'US', 'language' => 'en', 'view' => $view, 'container' => 'partuza'),
	        'gadgets' => array(array('url' => $app_url, 'moduleId' => '1'))));
	    $ch = curl_init();
	    $url = PartuzaConfig::get('gadget_server') . '/gadgets/metadata?st=' . urlencode(/*base64_encode(*/$securityToken->toSerialForm()/*)*/);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, 'request=' . urlencode($request));
	    $content = @curl_exec($ch);
	    
	    curl_close($ch);
	    $ret = json_decode($content);	    
	    if (!isset($ret->gadgets[0]->errors[0])) {
	    	break;
	    }
    }
    return $ret;
  }

  public function load_get_application_by_id($id) {
    global $db;
    $this->add_dependency('person_applications', $id);
    $this->add_dependency('applications', $id);
    $id = $db->addslashes($id);
    $res = $db->query("select url from applications where id = $id");
    if ($db->num_rows($res)) {
      list($url) = $db->fetch_row($res);
      return $this->get_application($url);
    }
    return false;
  }

  // This function either returns a valid applications record or
  // the error (string) that occured in ['error'].
  // After this function you can assume there is a valid, and up to date gadget metadata
  // record in the database.
  public function load_get_application($app_url) {    
    global $db;
    $error = false;
    $info = array();
    // see if we have up-to-date info in our db. Cut-off time is 1 day (aka refresh module info once a day)
    $time = $_SERVER['REQUEST_TIME'] - (24 * 60 * 60);
    $url = $db->addslashes($app_url);
    $res = $db->query("select * from applications where url = '$url' and modified > $time");
    if ($db->num_rows($res)) {
      // we have an entry with up-to-date info
      $info = $db->fetch_array($res, MYSQLI_ASSOC);
    } else {
      // Either we dont have a record of this module or its out of date, so we retrieve the app meta data.
      // Create a fake security token so that gadgets with signed preloading don't fail to load
      $securityToken = BasicSecurityToken::createFromValues(1, 1, 0, PartuzaConfig::get('container'), urlencode($app_url), 0, 1);
      $response = $this->fetch_gadget_metadata($app_url, $securityToken);
      if (! is_object($response) && ! is_array($response)) {
        // invalid json object, something bad happened on the shindig metadata side.
        $error = 'An error occured while retrieving the gadget information';
      } else {
        // valid response, process it
        $gadget = $response->gadgets[0];
        if (isset($gadget->errors) && ! empty($gadget->errors[0])) {
          // failed to retrieve gadget, or failed parsing it
          $error = $gadget->errors[0];
        } else {
          // retrieved and parsed gadget ok, store it in db
          $info['url'] = $db->addslashes($gadget->url);
          $info['title'] = isset($gadget->title) ? $gadget->title : '';
          $info['directory_title'] = isset($gadget->directoryTitle) ? $gadget->directoryTitle : '';
          $info['height'] = isset($gadget->height) ? $gadget->height : '';
          $info['screenshot'] = isset($gadget->screenshot) ? $gadget->screenshot : '';
          $info['thumbnail'] = isset($gadget->thumbnail) ? $gadget->thumbnail : '';
          $info['author'] = isset($gadget->author) ? $gadget->author : '';
          $info['author_email'] = isset($gadget->authorEmail) ? $gadget->authorEmail : '';
          $info['description'] = isset($gadget->description) ? $gadget->description : '';
          $info['settings'] = isset($gadget->userPrefs) ? serialize($gadget->userPrefs) : '';
          $info['views'] = isset($gadget->views) ? serialize($gadget->views) : '';
          if ($gadget->scrolling == 'true') {
            $gadget->scrolling = 1;
          }
          $info['scrolling'] = ! empty($gadget->scrolling) ? $gadget->scrolling : '0';
          $info['height'] = ! empty($gadget->height) ? $gadget->height : '0';
          // extract the version from the iframe url
          $iframe_url = $gadget->iframeUrl;
          $iframe_params = array();
          parse_str($iframe_url, $iframe_params);
          $info['version'] = isset($iframe_params['v']) ? $iframe_params['v'] : '';
          $info['modified'] = $_SERVER['REQUEST_TIME'];
          // Insert new application into our db, or if it exists (but had expired info) update the meta data
          $db->query("insert into applications
								(id, url, title, directory_title, screenshot, thumbnail, author, author_email, description, settings, views, version, height, scrolling, modified)
								values
								(
									0,
									'" . $db->addslashes($info['url']) . "',
									'" . $db->addslashes($info['title']) . "',
									'" . $db->addslashes($info['directory_title']) . "',
									'" . $db->addslashes($info['screenshot']) . "',
									'" . $db->addslashes($info['thumbnail']) . "',
									'" . $db->addslashes($info['author']) . "',
									'" . $db->addslashes($info['author_email']) . "',
									'" . $db->addslashes($info['description']) . "',
									'" . $db->addslashes($info['settings']) . "',
									'" . $db->addslashes($info['views']) . "',
									'" . $db->addslashes($info['version']) . "',
									'" . $db->addslashes($info['height']) . "',
									'" . $db->addslashes($info['scrolling']) . "',
									'" . $db->addslashes($info['modified']) . "'
								) on duplicate key update
									url = '" . $db->addslashes($info['url']) . "',
									title = '" . $db->addslashes($info['title']) . "',
									directory_title = '" . $db->addslashes($info['directory_title']) . "',
									screenshot = '" . $db->addslashes($info['screenshot']) . "',
									thumbnail = '" . $db->addslashes($info['thumbnail']) . "',
									author = '" . $db->addslashes($info['author']) . "',
									author_email = '" . $db->addslashes($info['author_email']) . "',
									description = '" . $db->addslashes($info['description']) . "',
									settings = '" . $db->addslashes($info['settings']) . "',
									views = '" . $db->addslashes($info['views']) . "',
									version = '" . $db->addslashes($info['version']) . "',
									height = '" . $db->addslashes($info['height']) . "',
									scrolling = '" . $db->addslashes($info['scrolling']) . "',
									modified = '" . $db->addslashes($info['modified']) . "'
								");
          $res = $db->query("select id from applications where url = '" . $db->addslashes($info['url']) . "'");
          if (! $db->num_rows($res)) {
            $error = "Could not store application in registry";
          } else {
            list($id) = $db->fetch_row($res);
            $info['id'] = $id;
            $this->invalidate_dependency('applications', $id);
          }
        }
      }
    }
    if (! $error) {
      $this->add_dependency('applications', $info['id']);
    }
    $info['error'] = $error;
    return $info;
  }

  public function add_application($person_id, $app_url) {
    global $db;
    $mod_id = false;    
    $app = $this->get_application($app_url);
    $app_id = isset($app['id']) ? $app['id'] : false;    
    $error = $app['error'];    
    if ($app_id && ! $error) {
      // we now have a valid gadget record in $info, with no errors occured, proceed to add it to the person
      // keep in mind a person -could- have two the same apps on his page (though with different module_id's) so no
      // unique check is done.
      $person_id = $db->addslashes($person_id);
      $app_id = $db->addslashes($app_id);
      $db->query("insert into person_applications (id, person_id, application_id) values (0, $person_id, $app_id)");
      $mod_id = $db->insert_id();
      $this->invalidate_dependency('person_applications', $person_id);
      $this->invalidate_dependency('person_application_prefs', $person_id);
    }
    return array('app_id' => $app_id, 'mod_id' => $mod_id, 'error' => $app['error']);
  }

  public function remove_application($person_id, $app_id, $mod_id) {
    global $db;
    $person_id = $db->addslashes($person_id);
    $app_id = $db->addslashes($app_id);
    $mod_id = $db->addslashes($mod_id);
    $db->query("delete from person_applications where id = $mod_id and person_id = $person_id and application_id = $app_id");
    $this->invalidate_dependency('person_applications', $person_id);
    return ($db->affected_rows() != 0);
  }
}