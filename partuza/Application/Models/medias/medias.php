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

class mediasModel extends Model {
	
	// media_items table supproted fields;
	public $supported_fields = array('id','activity_id','album_id','owner_id','mime_type','file_size',
    'duration','created','last_updated','language','address_id','num_comments','num_views',
    'num_votes','rating','start_time','title','description','tagged_people','tags',
    'thumbnail_url','type','url','app_id');

  public function add_media($media) {
    global $db;
    foreach ($media as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        if (is_null($val)) {
          $adds[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $adds[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($adds)) {
      $query = "insert into media_items set " . implode(', ', $adds);
      $db->query($query);
      return $db->insert_id();
    }
  }
  
  public function get_media($media_id) {
    global $db;
    $media_id = $db->addslashes($media_id);
    $query = "
      select
        media_items.id,    
        media_items.activity_id,
        media_items.album_id,
        media_items.owner_id,
        media_items.mime_type,
        media_items.file_size,
        media_items.duration,
        media_items.created,
        media_items.last_updated,
        media_items.language,
        media_items.address_id,
        media_items.num_comments,
        media_items.num_views,
        media_items.num_votes,
        media_items.rating,
        media_items.start_time,
        media_items.title,
        media_items.description,
        media_items.tagged_people,
        media_items.tags,
        media_items.thumbnail_url,
        media_items.type,
        media_items.url,
        media_items.app_id
      from
        media_items
      where
        media_items.id = '$media_id'";
    $res = $db->query($query);
    $ret = $db->fetch_array($res, MYSQLI_ASSOC);
    return $ret;
  }
  
  public function get_medias($owner_id, $album_id, $start = false, $count = false) {
    global $db;
    $owner_id = $db->addslashes($owner_id);
    $album_id = $db->addslashes($album_id);
    $start = $db->addslashes($start);
    $count = $db->addslashes($count);
    if (! $start) $start = '0';
    if (! $count) $count = 20;
    $where = is_numeric($album_id) ? "media_items.album_id = '$album_id'" : "media_items.owner_id = '$owner_id'";
    $order = "media_items.id asc";
    $limit = "$start, $count";
    global $db;
    $query = "
      select
        SQL_CALC_FOUND_ROWS
        media_items.id,    
        media_items.activity_id,
        media_items.album_id,
        media_items.owner_id,
        media_items.mime_type,
        media_items.file_size,
        media_items.duration,
        media_items.created,
        media_items.last_updated,
        media_items.language,
        media_items.address_id,
        media_items.num_comments,
        media_items.num_views,
        media_items.num_votes,
        media_items.rating,
        media_items.start_time,
        media_items.title,
        media_items.description,
        media_items.tagged_people,
        media_items.tags,
        media_items.thumbnail_url,
        media_items.type,
        media_items.url,
        media_items.app_id
      from
        media_items
      where
        $where and
        media_items.type = 'IMAGE'
      order by
        $order
      limit
        $limit";
    $res = $db->query($query);
    $cres = $db->query('SELECT FOUND_ROWS();');
    $ret = array();
    while ($media = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $media;
    }
    $rows = $db->fetch_array($cres, MYSQLI_NUM);
    $ret['found_rows'] = $rows[0];
    return $ret;
  }

  public function update_media($media_id, $media) {
    global $db;
    $media_id = $db->addslashes($media_id);
    foreach ($media as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        if (is_null($val)) {
          $updates[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $updates[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($updates)) {
      $query = "update media_items set " . implode(', ', $updates) . " where id = '$media_id'";
      $db->query($query);
      return $media_id;
    }
  }

  /*
   * update media table using literal word, so do not need to escape update code.
   * for example update media_items set num_media = num_media + 1;
   */
  public function literal_update_media($media_id, $media) {
    global $db;
    $media_id = $db->addslashes($media_id);
    foreach ($media as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        $updates[] = "`" . $db->addslashes($key) . "` = $val";
      }
    }
    if (count($updates)) {
      $query = "update media_items set " . implode(', ', $updates) . " where id = '$media_id'";
      $db->query($query);
      return $media_id;
    }
  }

  public function delete_media($owner_id, $media_id) {
    global $db;
    $query = "delete from media_items where owner_id = '" . $db->addslashes($owner_id) . 
      "' and id = '" . $db->addslashes($media_id) ."'";
    $db->query($query);
  }

  /**
   * get media in album before $media_id ;
   */
  public function get_media_previous($album_id, $media_id) {
    global $db;
    $query = "select * from media_items where album_id = '" . $db->addslashes($album_id) . 
      "' and id > '" . $db->addslashes($media_id) . "' order by id desc limit 1";
    $res = $db->query($query);
    $ret = $db->fetch_array($res, MYSQLI_ASSOC);
    return $ret;
  }

  /**
   * get media in album next to $media_id ;
   */
  public function get_media_next($album_id, $media_id) {
    global $db;
    $query = "select * from media_items where album_id = '" . $db->addslashes($album_id) . 
      "' and id < '" . $db->addslashes($media_id) . "' order by id desc limit 1";
    $res = $db->query($query);
    $ret = $db->fetch_array($res, MYSQLI_ASSOC);
    return $ret;
  }
  
  /**
   * get media in album, results are previous one, current one and next one. 
   */
  public function get_media_has_order($album_id, $media_id) {
    global $db;
    $ret = array();
    // get previous one, whose id is less than current one
    $query = "select SQL_CALC_FOUND_ROWS * from media_items where album_id = '" . $db->addslashes($album_id) .
      "' and id < '" . $db->addslashes($media_id) . "' order by id desc limit 1";
    $res = $db->query($query);
    $row = $db->fetch_array($res, MYSQLI_ASSOC);
    if (!empty($row)) {
      $ret[] = $row;
    }
    $cres = $db->query('SELECT FOUND_ROWS();');
    $rows = $db->fetch_array($cres, MYSQLI_NUM);
    $found_rows = $rows[0];
    
    // get current and next one
    $query = "select SQL_CALC_FOUND_ROWS * from media_items where album_id = '" . $db->addslashes($album_id) .
      "' and id >= '" . $db->addslashes($media_id) . "' order by id asc limit 2";
    $res = $db->query($query);
    $cres = $db->query('SELECT FOUND_ROWS();');
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $row;
    }
    $rows = $db->fetch_array($cres, MYSQLI_NUM);
    $ret['found_rows'] = $rows[0] + $found_rows;
    $ret['item_order'] = 1 + $found_rows;
    return $ret;
  }
}
