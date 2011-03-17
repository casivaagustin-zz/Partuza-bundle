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

class messagesModel extends Model {

  public function send_message($from, $to, $subject, $body) {
    global $db;
    $from = intval($from);
    $to = intval($to);
    $subject = $db->addslashes($subject);
    $body = $db->addslashes($body);
    $created = $_SERVER['REQUEST_TIME'];
    $db->query("insert into messages (`from`, `to`, title, body, app_id, updated, created) values ($from, $to, '$subject', '$body', 0, $created, $created)");
  }

  public function delete_message($message_id, $to_or_from) {
    global $db;
    $message_id = intval($message_id);
    if ($to_or_from == 'to') {
      $field = 'to_deleted';
    } elseif ($to_or_from == 'from') {
      $field = 'from_deleted';
    } else {
      die('eeek!');
    }
    $query = "update messages set $field = 'yes' where id = $message_id";
    $db->query($query);
  }

  public function get_message($message_id, $type = 'inbox') {
    global $db;
    if ($type != 'inbox' && $type != 'sent') {
      die('eeek!');
    }
    $type = $type == 'inbox' ? 'to' : 'from';
    $res = $db->query("select messages.*, concat(persons.first_name, ' ' , persons.last_name) as name, persons.thumbnail_url as thumbnail from messages, persons where persons.id = messages.`$type` and messages.id = " . $db->addslashes(intval($message_id)));
    $ret = $db->fetch_array($res, MYSQLI_ASSOC);
    return $ret;
  }

  public function mark_read($message_id) {
    global $db;
    $message_id = intval($message_id);
    $db->query("update messages set `status` = 'read' where id = $message_id");
  }

  public function get_inbox($userId, $start = false, $count = false) {
    global $db;
    $userId = $db->addslashes($userId);
    $start = $db->addslashes($start);
    $count = $db->addslashes($count);
    if (! $start) $start = '0';
    if (! $count) $count = 20;
    $limit = "$start, $count";
    $query = "
    	select
    		messages.id,
    		messages.`from`,
    		messages.`to`,
    		messages.title,
    		messages.body,
    		messages.created,
    		messages.status,
    		concat(persons.first_name, ' ' , persons.last_name) as name,
    		persons.thumbnail_url as thumbnail
    	from
    		messages, persons
        where
            messages.`to` = $userId and
            persons.id = messages.`from` and
            to_deleted = 'no'
		order by
			created desc
		limit
			$limit";
    $res = $db->query($query);
    $ret = array();
    while ($message = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $message['read'] = $message['status'] == 'read' ? 'yes' : 'no'; 
      $ret[] = $message;
    }
    return $ret;
  }

  public function get_sent($userId, $start = false, $count = false) {
    global $db;
    $userId = $db->addslashes($userId);
    $start = $db->addslashes($start);
    $count = $db->addslashes($count);
    if (! $start) $start = '0';
    if (! $count) $count = 20;
    $limit = "$start, $count";
    $query = "
    	select
    		messages.id,
    		messages.`from`,
    		messages.`to`,
    		messages.title,
    		messages.body,
    		messages.created,
    		concat(persons.first_name, ' ' , persons.last_name) as name,
    		persons.thumbnail_url as thumbnail
    	from
    		messages, persons
        where
            messages.`from` = $userId and
            persons.id = messages.`to` and
            from_deleted = 'no'
		order by
			created desc
		limit
			$limit";
    $res = $db->query($query);
    $ret = array();
    while ($message = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $message;
    }
    return $ret;
  }
}
