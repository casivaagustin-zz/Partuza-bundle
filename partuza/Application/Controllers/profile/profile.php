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

class profileController extends baseController {

  public function index($params) {
    $id = isset($params[2]) && is_numeric($params[2]) ? $params[2] : false;
    if (! $id) {
      //TODO add a proper 404 / profile not found here
      header("Location: " . PartuzaConfig::get('web_prefix') . "/");
      die();
    }
    $people = $this->model('people');
    $person = $people->get_person($id, true);
    $activities = $this->model('activities');
    $is_friend = isset($_SESSION['id']) ? ($_SESSION['id'] == $id ? true : $people->is_friend($id, $_SESSION['id'])) : false;
    $person_activities = $activities->get_person_activities($id, 10);
    $friends = $people->get_friends($id);
    $friend_requests = isset($_SESSION['id']) && $_SESSION['id'] == $id ? $people->get_friend_requests($_SESSION['id']) : array();
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($id);
    $person_apps = null;
    if (isset($_SESSION['id']) && $_SESSION['id'] != $id) {
      $person_apps = $apps->get_person_applications($_SESSION['id']);
    }
    $this->template('profile/profile.php', array('activities' => $person_activities, 'applications' => $applications, 'person' => $person, 'friend_requests' => $friend_requests, 'friends' => $friends,
      'is_friend' => $is_friend, 'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $id) : false, 'person_apps' => $person_apps));
  }

  public function friends($params) {
    if (! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
    $people = $this->model('people');
    $person = isset($_SESSION['id']) ? $people->get_person($params[3], true) : false;
    $friends_count = $people->get_friends_count($params[3]);
    if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $friends_count) {
      $page = intval($_GET['page']);
    } else {
      $page = 1;
    }
    $start = ($page - 1) * 8;
    $count = 8;
    $pages = ceil($friends_count / 8);
    $friends = $people->get_friends($params[3], "$start, $count");
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($params[3]);
    $is_friend = isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3] ? true : $people->is_friend($params[3], $_SESSION['id'])) : false;
    $this->template('profile/profile_showfriends.php', array('is_friend' => $is_friend, 'page' => $page, 'pages' => $pages, 'friends_count' => $friends_count, 'friends' => $friends, 'applications' => $applications,
        'person' => $person, 'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3]) : false));
  }

  public function message_inbox($type) {
    $start = 0;
    $count = 20;
    if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
      $start = ($_GET['page'] - 1) * 20;
    }
    $messages = $this->model('messages');
    if ($type == 'inbox') {
      $messages = $messages->get_inbox($_SESSION['id'], $start, $count);
    } elseif ($type == 'sent') {
      $messages = $messages->get_sent($_SESSION['id'], $start, $count);
    } else {
      die("invalid type");
    }
    $this->template('profile/profile_show_messages.php', array('messages' => $messages, 'type' => $type));
  }

  public function message_notifications() {
    die('Not implemented, at some point this will container friend requests and requestShareApp notifications');
  }

  public function message_delete($message_id) {
    $messages = $this->model('messages');
    $message = $messages->get_message($message_id);
    // silly special case if you send a message to your self
    if ($message['to'] == $_SESSION['id'] && $message['from'] == $_SESSION['id']) {
      $messages->delete_message($message_id, 'to');
      $messages->delete_message($message_id, 'from');
      return;
    }
    if ($message['to'] == $_SESSION['id']) {
      $type = 'to';
    } elseif ($message['from'] == $_SESSION['id']) {
      $type = 'from';
    } else {
      die('This is not the message your looking for');
      return;
    }
    $messages->delete_message($message_id, $type);
  }

  public function message_get() {
    $messageId = isset($_GET['messageId']) ? intval($_GET['messageId']) : false;
    $messageType = isset($_GET['messageType']) && ($_GET['messageType'] == 'inbox' || $_GET['messageType'] == 'sent') ? $_GET['messageType'] : false;
    if (!$messageId || !$messageType) {
      die('This is not the message your looking for');
    }
    $messages = $this->model('messages');
    $message = $messages->get_message($messageId);
    if (isset($message['status']) && $message['status'] == 'new') {
      $messages->mark_read($messageId);
    }
    $this->template('/profile/profile_show_message.php', array('message' => $message, 'messageId' => $messageId, 'messageType' => $messageType));
  }

  public function message_compose() {
    $people = $this->model('people');
    $friends = $people->get_friends($_SESSION['id']);
    $this->template('/profile/profile_compose_message.php', array('friends' => $friends));
  }

  public function message_send() {
    $to = isset($_POST['to']) ? $_POST['to'] : false;
    $subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : false;
    $body = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';
    if (!$to || !$subject) {
      die('Uh what?');
    }
    $messages = $this->model('messages');
    $messages->send_message($_SESSION['id'], $to, $subject, $body);
  }

  public function messages($params) {
    if (! isset($_SESSION['id'])) {
      header("Location: /");
    }
    if (isset($params[3])) {
      switch ($params[3]) {
        case 'inbox':
          $this->message_inbox('inbox');
          break;
        case 'sent':
          $this->message_inbox('sent');
          break;
        case 'notifications':
          $this->message_notifications();
          break;
        case 'delete':
          $this->message_delete($params[4]);
          break;
        case 'get':
          $this->message_get();
          break;
        case 'compose':
          $this->message_compose();
          break;
        case 'send':
          $this->message_send();
          break;
      }
    } else {
      $people = $this->model('people');
      $apps = $this->model('applications');
      $applications = $apps->get_person_applications($_SESSION['id']);
      $person = $people->get_person($_SESSION['id'], true);
      $this->template('profile/profile_messages.php', array('person' => $person, 'applications' => $applications, 'is_owner' => true));
    }
  }

  public function photos($params) {
    if (! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
    $people = $this->model('people');
    $person = isset($params[3]) ? $people->get_person($params[3], true) : false;
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($params[3]);
    $is_friend = isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3] ? true : $people->is_friend($params[3], $_SESSION['id'])) : false;
    $this->template('profile/profile_photos.php', 
      array('is_friend' => $is_friend, 
            'applications' => $applications, 
            'person' => $person, 
            'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3]) : false));
  }

  /**
   * save an album item add or update.
   */
  public function photos_save($params) {
    if (! isset($_SESSION['id']) || $_SESSION['id'] != $params[3] || ! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
    $album = $this->model('albums');
    $album_item = array();
    if (isset($_POST['title'])) $album_item['title'] = $_POST['title'];
    if (isset($_POST['description'])) $album_item['description'] = $_POST['description'];
    $album_item['owner_id'] = $_SESSION['id'];
    $album_item['media_mime_type'] = 'image';
    $album_item['media_type'] = 'IMAGE';
    $album_item['app_id'] = 0;
    // if $params[4] have not set, it's add an album item.
    // if $params[4] set, it's update an album item.
    if (! isset($params[4]) || ! is_numeric($params[4])) {
      $album_item['created'] = time();
      $album_item['modified'] = time();
      $album_item['media_count'] = 0;
      $album_id = $album->add_album($album_item);
      die($album_id);
    } else {
      $album_item['modified'] = time();
      $album_id = $params[4];
      $album_id = $album->update_album($album_id, $album_item);
      die($album_id);
    }
  }

  /*
   * save an media item update.
   */
  public function photo_save($params) {
    if (! isset($_SESSION['id']) || $_SESSION['id'] != $params[3] || ! isset($params[3]) || 
      ! is_numeric($params[3]) || ! isset($params[4]) || ! is_numeric($params[4])) {
      header("Location: /");
      die();
    }
    $media = $this->model('medias');
    $media_item = array();
    // if thumbnial_url set, will update album table, set the media_id to album table.
    if (isset($_POST['thumbnail_url'])) {
      $media_items = $media->get_media($params[4]);
      $album_item = array();
      $album_item['thumbnail_url'] = isset($media_items['thumbnail_url']) ? $media_items['thumbnail_url'] : $media_items['url'];
      $album_item['media_id'] = $media_items['id'];
      $album = $this->model('albums');
      $album->update_album($media_items['album_id'], $album_item);
    }
    
    if (isset($_POST['title'])) $media_item['title'] = $_POST['title'];
    if (isset($_POST['description'])) $media_item['description'] = $_POST['description'];
    $media_item['app_id'] = 0;
    $media_item['last_updated'] = time();
    $media_id = $media->update_media($params[4], $media_item);
    die($media_id);
  }

  /*
   * when $_SESSION['id'] not set, display album albums have not 'edit' and 'delete' button. so $is_owner be used in this view.
   * $_GET['s'] is start index for album items;
   * $_GET['c'] is one page display num.
   */
  public function photos_list($params) {
    if (! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
    if (isset($_GET['c']) && is_numeric($_GET['c'])) {
      $items_to_show = $_GET['c'];
    } else {
      $items_to_show = 10;
    }
    if (isset($_GET['s']) && is_numeric($_GET['s'])) {
      $start_index = intval($_GET['s']);
      $start_index = floor($start_index/$items_to_show) * $items_to_show;
    } else {
      $start_index = 0;
    }
    if (isset($params[4]) && $params[4] == 'self') {
      $albums = $this->model('albums');
      try {
        $albums = $albums->get_albums($params[3], $start_index, $items_to_show);
      }
      catch (DBException $e) {
        $message = 'Error saving information (' . $e->getMessage() . ')';
        die($message);
      }
      $this->template('profile/profile_photos_list.php', 
        array('albums' => $albums,
              'person_id' => $params[3],
              'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3]) : false,
              'page' => array('start_index'=>$start_index, 'items_to_show'=>$items_to_show, 'items_count'=>$albums['found_rows'])));  
    }
    else {
      $albums = $this->model('albums');
      echo 'not implemented';
    }
  }
  /*
   * when $_SESSION['id'] not set, display album photos have not 'edit' and 'delete' button. so $is_owner be used in this view.
   * $_GET['s'] is start index for album items;
   * $_GET['c'] is one page display num.
   */
  public function photos_view($params) {
    if (! isset($params[3]) || ! is_numeric($params[3]) || ! isset($params[4]) || ! is_numeric($params[4])) {
      header("Location: /");
      die();
    }
    if (isset($_GET['c']) && is_numeric($_GET['c'])) {
      $items_to_show = $_GET['c'];
    } else {
      $items_to_show = 12;
    }
    if (isset($_GET['s']) && is_numeric($_GET['s'])) {
      $start_index = intval($_GET['s']);
      $start_index = floor($start_index/$items_to_show) * $items_to_show;
    } else {
      $start_index = 0;
    }

    $people = $this->model('people');
    $person = isset($_SESSION['id']) ? $people->get_person($params[3], true) : false;
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($params[3]);
    $is_friend = isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3] ? true : $people->is_friend($params[3], $_SESSION['id'])) : false;
    $album = $this->model('albums');
    $albums = $album->get_album($params[4]);
    if ($start_index > $albums['media_count']) $start_index = floor($albums['media_count']/$items_to_show)*$items_to_show;
    $media = $this->model('medias');
    
    $medias = $media->get_medias($params[3], $params[4], $start_index, $items_to_show);
    if (! isset($albums['id'])) $albums['id'] = '';
    if (! isset($albums['owner_id'])) $albums['owner_id'] = '';
    if (! isset($albums['title'])) $albums['title'] = '';
    if (! isset($albums['description'])) $albums['description'] = '';
    if (! isset($medias['found_rows'])) $medias['found_rows'] = 0;
    $this->template('profile/profile_photos_view.php', 
      array('is_friend' => $is_friend, 
            'applications' => $applications, 
            'person' => $person, 
            'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3]) : false,
            'medias' => $medias,
            'albums' => $albums,
            'page' => array('start_index'=>$start_index, 'items_to_show'=>$items_to_show, 'items_count'=>$medias['found_rows'])));
  }

  /*
   * $params[3] owner_id, $params[4] albums_id, $params[5] media_id.
   */
  public function photo_view($params) {
    if (! isset($params[3]) || ! is_numeric($params[3])
      || !isset($params[4]) || ! is_numeric($params[4])
      || !isset($params[5]) || ! is_numeric($params[5])) {
      header("Location: /");
      die();
    }
    $people = $this->model('people');
    $person = isset($_SESSION['id']) ? $people->get_person($params[3], true) : false;
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($params[3]);
    $is_friend = isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3] ? true : $people->is_friend($params[3], $_SESSION['id'])) : false;
    $media = $this->model('medias');
    $medias = $media->get_media_has_order($params[4], $params[5]);
    $album = $this->model('albums');
    $albums = $album->get_album($params[4]);
    $item_order = $medias['item_order'];
    $item_count = $medias['found_rows'];
    if ($item_order == 1) {
      $current_media = $medias[0];
    } else {
      $current_media = $medias[1];
    }
    if (! isset($albums['id'])) $albums['id'] = '';
    if (! isset($albums['title'])) $albums['title'] = '';
    if (! isset($albums['description'])) $albums['description'] = '';
    if (! isset($albums['media_count'])) $albums['media_count'] = 0;
    if (! isset($current_media['title'])) $current_media['title'] = '';
    if (! isset($current_media['description'])) $current_media['description'] = '';
    if (! isset($current_media['item_order'])) $current_media['item_order'] = '';
    $tmp_pos = strrpos($current_media['url'], '.');
    $current_media['original_url'] = $current_media['url'];
    $current_media['url'] = substr($current_media['url'], 0, $tmp_pos) . '.600x600' . substr($current_media['url'], $tmp_pos);
    unset($medias['item_order']);
    unset($medias['found_rows']);
    $this->template('profile/profile_photo_view.php', 
      array('is_friend' => $is_friend, 
            'applications' => $applications, 
            'person' => $person, 
            'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $params[3]) : false,
            'medias' => $medias,
            'albums' => $albums,
            'current_media' => $current_media,
            'item_order' => $item_order,
            'item_count' => $item_count));
  }

  public function photo_show($params) {
  	if (! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
  	$media = $this->model('medias');
  	$media_item = $media->get_media($params[3]);
  	$file_path = str_replace(PartuzaConfig::get('partuza_url'), PartuzaConfig::get('site_root').'/', $media_item['url']);
    die();
  }

  public function photo_upload($params) {
    if (! isset($params[3]) || ! is_numeric($params[3]) || $_SESSION['id'] != $params[3]
      || !isset($params[4]) || ! is_numeric($params[4])) {
      header("Location: /");
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }

    $people = $this->model('people');
    $person = $people->get_person_fields($_SESSION['id'], array('uploaded_size'));

    // upload file info is empty
    if (! isset($_FILES['uploadPhoto']) || empty($_FILES['uploadPhoto']['name'])) {
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }
    
    // upload file size over quota.
    $file = $_FILES['uploadPhoto'];
    if (PartuzaConfig::get('upload_quota') - $person['uploaded_size'] < $file['size']) {
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }
    if (substr($file['type'], 0, strlen('image/')) != 'image/') {
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }
    $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
    // it's a file extention that we accept too (not that means anything really)
    $accepted = array('gif', 'jpg', 'jpeg', 'png');
    if (!in_array($ext, $accepted) || $file['size'] < 4) {
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }

    // it's upload file path.
    $album_id = $params[4];
    $tmp_dir = array('/images', '/albums', '/'.$album_id);
    $album_dir = '/images/albums/'.$album_id;
    $file_dir = PartuzaConfig::get('site_root');
    foreach($tmp_dir as $val) {
    	$file_dir .= $val;
      if (!file_exists($file_dir)) {
        if (!@mkdir($file_dir, 0775, true)) {
          $this->template('profile/profile_photos_upload.php', array('result' => false));
          die();
        }
      }
    }
    $title = (!empty($_POST['title'])) ? $_POST['title'] : substr($file['name'], 0, strrpos($file['name'], '.'));
    $media = $this->model('medias');
    $media_item = array(
      'album_id' => $album_id,
      'owner_id' => $_SESSION['id'],
      'mime_type' => '',
      'title' => $title,
      'file_size' => $file['size'],
      'created' => time(),
      'last_updated' => time(),
      'type' => 'IMAGE',
      'url' => '',
      'app_id' => 0,
    );
    
    // if insert data failed, throw an error.
    try {
      $media_id = $media->add_media($media_item);
    } catch (DBException $e) {
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    }

    $file_path = $file_dir . '/' . $media_id . '.' . $ext;
    if (! Image::createImage($file['tmp_name'], $file_path, null, null)) {
    	// if move file failed, need delete the item in media_item table.
      $media->delete_media($params[3], $media_id);
      $this->template('profile/profile_photos_upload.php', array('result' => false));
      die();
    } else {
    	// insert and upload file is success.
      $media_item = array();
      $media_item['url'] = $album_dir . '/' . $media_id . '.' . $ext;
      $media_item['thumbnail_url'] = Image::by_size($file_path, 220, 220);
      $media_id = $media->update_media($media_id, $media_item);
      $album = $this->model('albums');
      $album_record = $album->get_album($params[4]);
      $album_item = array();
      $album_item["media_count"] = $album_record["media_count"]  + 1;
      $album_item["modified"] = time();
      if (empty($album_record['media_id'])) {
        $album_item['media_id'] = $media_id;
        $album_item['thumbnail_url'] = $media_item['thumbnail_url'];
      }
      $album_id = $album->update_album($params[4], $album_item);
      $person = $people->literal_set_person_fields($params[3], array('uploaded_size' => "uploaded_size + {$file['size']}"));
      $this->template('profile/profile_photos_upload.php', array('result' => true));
      Image::by_size($file_path, 600, 600);
      die();
    }
  }

  public function album_delete($params) {
    if (! isset($_SESSION['id']) || $_SESSION['id'] != $params[3] 
      || ! isset($params[3]) || ! is_numeric($params[3]) || ! isset($params[4]) || ! is_numeric($params[4])) {
      header("Location: /");
    }
    $album = $this->model('albums');
    $album->delete_album($params[3], $params[4]);
    die('success');
  }

  public function media_delete($params) {
    if (! isset($_SESSION['id']) || $_SESSION['id'] != $params[3] 
      || ! isset($params[3]) || ! is_numeric($params[3]) || ! isset($params[4]) || ! is_numeric($params[4])) {
      header("Location: /");
    }
    $media = $this->model('medias');
    $media_item = $media->get_media($params[4]);
    $albums = $this->model('albums');

    $people = $this->model('people');
    $person = $people->literal_set_person_fields($params[3], array('uploaded_size' => "uploaded_size - {$media_item['file_size']}"));
    if (!empty($media_item['album_id'])) {
    	// delete one media item, update album item and person item.
      $album_item = $albums->get_album($media_item['album_id']);
      $album_record = array();
      if (($album_item['media_id'] == $media_item['id'])) {
      	// if the delete one is a cover picture.
        $media_tmp = $media->get_media_previous($media_item['album_id'], $media_item['id']);
        if (empty($media_tmp)) $media_tmp = $media->get_media_next($media_item['album_id'], $media_item['id']);
        if (empty($media_tmp)) {
          $album_record['thumbnail_url'] = null;
          $album_record['media_id'] = null;
        } else {
          $album_record = array();
          $album_record['thumbnail_url'] = empty($media_tmp['thumbnail_url']) ? $media_tmp['url'] : $media_tmp['thumbnail_url'];
          $album_record['media_id'] = $media_tmp['id'];
        }
      }
      $album_record['media_count'] = $album_item['media_count'] - 1;
      $albums->update_album($album_item['id'], $album_record);
    }
    $media->delete_media($params[3], $params[4]);
    die('success');
  }

  public function edit($params) {
    if (! isset($_SESSION['id'])) {
      header("Location: /");
    }
    $message = '';
    $people = $this->model('people');
    if (count($_POST)) {
      if (! empty($_POST['date_of_birth_month']) && ! empty($_POST['date_of_birth_day']) && ! empty($_POST['date_of_birth_year']) && is_numeric($_POST['date_of_birth_month']) && is_numeric($_POST['date_of_birth_day']) && is_numeric($_POST['date_of_birth_year'])) {
        $_POST['date_of_birth'] = mktime(0, 0, 1, $_POST['date_of_birth_month'], $_POST['date_of_birth_day'], $_POST['date_of_birth_year']);
      }
      if (isset($_FILES['profile_photo']) && ! empty($_FILES['profile_photo']['name'])) {
        //TODO quick and dirty profile photo support, should really seperate this out and make a proper one
        $file = $_FILES['profile_photo'];
        // make sure the browser thought this was an image
        if (substr($file['type'], 0, strlen('image/')) == 'image/' && $file['error'] == UPLOAD_ERR_OK) {
          $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
          // it's a file extention that we accept too (not that means anything really)
          $accepted = array('gif', 'jpg', 'jpeg', 'png');
          if (in_array($ext, $accepted)) {
            if (! move_uploaded_file($file['tmp_name'], PartuzaConfig::get('site_root') . '/images/people/' . $_SESSION['id'] . '.' . $ext)) {
              die("no permission to images/people dir, or possible file upload attack, aborting");
            }
            // thumbnail the image to 96x96 format (keeping the original)
            $thumbnail_url = Image::by_size(PartuzaConfig::get('site_root') . '/images/people/' . $_SESSION['id'] . '.' . $ext, 96, 96, true);
            $people->set_profile_photo($_SESSION['id'], $thumbnail_url);
          }
        }
      }
      try {
        $people->save_person($_SESSION['id'], $_POST);
        $message = 'Saved information';
      } catch (DBException $e) {
        $message = 'Error saving information (' . $e->getMessage() . ')';
      }
    }
    $oauth = $this->model('oauth');
    $oauth_consumer = $oauth->get_person_consumer($_SESSION['id']);
    $person = $people->get_person($_SESSION['id'], true);
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($_SESSION['id']);
    $this->template('profile/profile_edit.php', array('message' => $message, 'applications' => $applications, 'person' => $person, 'oauth' => $oauth_consumer, 'is_owner' => true));
  }

  public function preview($params) {
    if (! isset($params[3]) || ! is_numeric($params[3])) {
      header("Location: /");
      die();
    }
    $app_id = intval($params[3]);
    $people = $this->model('people');
    $person = isset($_SESSION['id']) ? $people->get_person($_SESSION['id'], true) : false;
    $apps = $this->model('applications');
    $application = $apps->get_application_by_id($app_id);
    $applications = isset($_SESSION['id']) ? $apps->get_person_applications($_SESSION['id']) : array();
    $this->template('applications/application_preview.php', array('applications' => $applications, 'application' => $application, 'person' => $person, 'is_owner' => true));
  }

  public function application($params) {
    $id = isset($params[3]) && is_numeric($params[3]) ? $params[3] : false;
    if (! $id || (! isset($params[4]) || ! is_numeric($params[4])) || (! isset($params[5]) || ! is_numeric($params[5]))) {
      header("Location: /");
      die();
    }
    $app_id = intval($params[4]);
    $mod_id = intval($params[5]);
    $people = $this->model('people');
    $person = $people->get_person($id, true);
    $friends = $people->get_friends($id);
    $friend_requests = isset($_SESSION['id']) ? $people->get_friend_requests($_SESSION['id']) : array();
    $apps = $this->model('applications');
    $application = $apps->get_person_application($id, $app_id, $mod_id);
    $this->template('applications/application_canvas.php', array('application' => $application, 'person' => $person, 'friend_requests' => $friend_requests, 'friends' => $friends,
        'is_owner' => isset($_SESSION['id']) ? ($_SESSION['id'] == $id) : false));
  }

  public function myapps($param) {
    if (! isset($_SESSION['id'])) {
      header("Location: /");
    }
    $id = $_SESSION['id'];
    $people = $this->model('people');
    $apps = $this->model('applications');
    $applications = $apps->get_person_applications($_SESSION['id']);
    $person = $people->get_person($id, true);
    $this->template('applications/applications_manage.php', array('person' => $person, 'is_owner' => true, 'applications' => $applications));
  }

  public function appgallery($params) {
    if (! isset($_SESSION['id'])) {
      header("Location: /");
    }
    $id = $_SESSION['id'];
    $people = $this->model('people');
    $apps = $this->model('applications');
    $app_gallery = $apps->get_all_applications();
    $applications = $apps->get_person_applications($_SESSION['id']);
    $person = $people->get_person($id, true);
    $this->template('applications/applications_gallery.php', array('person' => $person, 'is_owner' => true, 'applications' => $applications, 'app_gallery' => $app_gallery));
  }

  public function addapp($params) {
    if (! isset($_SESSION['id']) || ! isset($_GET['appUrl'])) {
      header("Location: /");
    }
    $url = trim(urldecode($_GET['appUrl']));
    $apps = $this->model('applications');
    $ret = $apps->add_application($_SESSION['id'], $url);
    if ($ret['app_id'] && $ret['mod_id'] && ! $ret['error']) {
      // App added ok, goto app settings
      header("Location: " . PartuzaConfig::get("web_prefix") . "/profile/application/{$_SESSION['id']}/{$ret['app_id']}/{$ret['mod_id']}");
    } else {
      // Using the home controller to display the error on the person's home page
      include_once PartuzaConfig::get('controllers_root') . "/home/home.php";
      $homeController = new homeController();
      $message = "<b>Could not add application:</b><br/> {$ret['error']}";
      $homeController->index($params, $message);
    }
  }

  public function removeapp($params) {
    if (! isset($_SESSION['id']) || (! isset($params[3]) || ! is_numeric($params[3])) || (! isset($params[4]) || ! is_numeric($params[4]))) {
      header("Location: /");
    }
    $app_id = intval($params[3]);
    $mod_id = intval($params[4]);
    $apps = $this->model('applications');
    if ($apps->remove_application($_SESSION['id'], $app_id, $mod_id)) {
      $message = 'Application removed';
    } else {
      $message = 'Could not remove application, invalid id';
    }
    header("Location: /profile/myapps");
  }

  public function appsettings($params) {
    if (! isset($_SESSION['id']) || (! isset($params[3]) || ! is_numeric($params[3])) || (! isset($params[4]) || ! is_numeric($params[4]))) {
      header("Location: /");
    }
    $app_id = intval($params[3]);
    $mod_id = intval($params[4]);
    $apps = $this->model('applications');
    $people = $this->model('people');
    $person = $people->get_person($_SESSION['id'], true);
    $friends = $people->get_friends($_SESSION['id']);
    $friend_requests = isset($_SESSION['id']) ? $people->get_friend_requests($_SESSION['id']) : array();
    $app = $apps->get_person_application($_SESSION['id'], $app_id, $mod_id);
    $applications = $apps->get_person_applications($_SESSION['id']);
    if (count($_POST)) {
      $settings = unserialize($app['settings']);
      if (is_object($settings)) {
        foreach ($_POST as $key => $value) {
          // only store if the gadget indeed knows this setting, otherwise it could be abuse..
          if (isset($settings->$key)) {
            $apps->set_application_pref($_SESSION['id'], $app_id, $key, $value);
          }
        }
      }
      header("Location: " . PartuzaConfig::get("web_prefix") . "/profile/application/{$_SESSION['id']}/$app_id/$mod_id");
      die();
    }
    $this->template('applications/application_settings.php', array('applications' => $applications, 'application' => $app, 'person' => $person, 'friend_requests' => $friend_requests, 'friends' => $friends, 'is_owner' => true));
  }
}