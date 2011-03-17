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

class albumsModel extends Model {
  
  // album table supported fields
  public $supported_fields = array('id', 'title', 'description', 'address_id', 'owner_id', 'media_mime_type',
    'media_type', 'thumbnail_url', 'app_id', 'created', 'modified', 'media_count', 'media_id');

  public function add_album($album) {
    global $db;
    foreach ($album as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        if (is_null($val)) {
          $adds[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $adds[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($adds)) {
      $query = "insert into albums set " . implode(', ', $adds);
      $db->query($query);
      echo $db->insert_id();
    }
  }

  public function get_album($album_id) {
    global $db;
    $album_id = $db->addslashes($album_id);
    $query = "
      select
        albums.id,
        albums.title,
        albums.description,
        albums.address_id,
        albums.owner_id,
        albums.media_mime_type,
        albums.media_type,
        albums.thumbnail_url,
        albums.app_id,
        albums.created,
        albums.modified,
        albums.media_count,
        albums.media_id,
        concat(persons.first_name, ' ', persons.last_name) as name
      from
        albums, persons
      where
        albums.id = '$album_id' and
        persons.id = albums.owner_id
      limit 1";
    $res = $db->query($query);
    $ret = $db->fetch_array($res, MYSQLI_ASSOC);
    return $ret;
  }
  
  public function get_albums($owner_id, $start = false, $count = false) {
    global $db;
    $owner_id = $db->addslashes($owner_id);
    $start = $db->addslashes($start);
    $count = $db->addslashes($count);
    if (! $start) $start = '0';
    if (! $count) $count = 10;
    $where = "albums.owner_id = '$owner_id'";
    $limit = "$start, $count";
    $query = "
      select
        SQL_CALC_FOUND_ROWS
        albums.id,
        albums.title,
        albums.description,
        albums.address_id,
        albums.owner_id,
        albums.media_mime_type,
        albums.media_type,
        albums.thumbnail_url,
        albums.app_id,
        albums.created,
        albums.modified,
        albums.media_count,
        albums.media_id,
        concat(persons.first_name, ' ', persons.last_name) as name
      from
        albums, persons
      where
        $where and
        persons.id = albums.owner_id
      order by
        albums.id desc
      limit
        $limit";
    $res = $db->query($query);
    $cres = $db->query('select FOUND_ROWS();');
    $ret = array();
    while ($album = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $album;
    }
    $rows = $db->fetch_array($cres, MYSQLI_NUM);
    $ret['found_rows'] = $rows[0];
    return $ret;
  }

  public function update_album($album_id, $album) {
    global $db;
    $album_id = $db->addslashes($album_id);
    foreach ($album as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        if (is_null($val)) {
          $updates[] = "`" . $db->addslashes($key) . "` = null";
        } else {
          $updates[] = "`" . $db->addslashes($key) . "` = '" . $db->addslashes($val) . "'";
        }
      }
    }
    if (count($updates)) {
      $query = "update albums set " . implode(', ', $updates) . " where id = '$album_id'";
      $db->query($query);
      return $album_id;
    }
  }

  /*
   * update media table use literal word, so do not need to escape update code.
   * for example update albums set media_count = media_count + 1;
   */
  public function literal_update_album($album_id, $album) {
    global $db;
    $album_id = $db->addslashes($album_id);
    foreach ($album as $key => $val) {
      if (in_array($key, $this->supported_fields)) {
        $updates[] = "`" . $db->addslashes($key) . "` = $val";
      }
    }
    if (count($updates)) {
      $query = "update albums set " . implode(', ', $updates) . " where id = '$album_id'";
      $db->query($query);
      return $album_id;
    }
  }

  /*
   * delete album record.
   */
  public function delete_album($owner_id, $album_id) {
    global $db;
    $owner_id = $db->addslashes($owner_id);
    $album_id = $db->addslashes($album_id);
    $query = "delete from albums where owner_id = '$owner_id' and id = '$album_id'";
    $db->query($query);
  }
}
