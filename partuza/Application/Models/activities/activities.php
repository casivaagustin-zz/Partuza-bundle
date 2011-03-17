<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *
 */

class activitiesModel extends Model {
  public $_cachable = array('get_person_activities', 'get_friend_activities');

  public function load_get_person_activities($id, $limit) {
    global $db;
    $this->add_dependency('activities', $id);
    $id = $db->addslashes($id);
    $limit = $db->addslashes($limit);
    $ret = array();
    $res = $db->query("
		select
			activities.title as title,
			activities.body as body,
			activities.created as created,
			persons.id as person_id,
			concat(persons.first_name,' ',persons.last_name) as person_name,
			applications.id as app_id,
			applications.title as app_title,
			applications.directory_title as app_directory_title,
			applications.url as app_url,
      activities.id as activity_id
		from ( activities, persons )
  	left join applications on applications.id = activities.app_id
		where
			activities.person_id = $id and
			persons.id = activities.person_id and
			activities.title not like '[ACT%'
		order by
			created desc
		limit
			$limit
		");
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $this->add_dependency('activities', $row['person_id']);
      $row['media_items'] = $this->load_media_items($row['activity_id']);
      $ret[] = $row;
    }
    return $ret;
  }

  public function load_media_items($activity_id) {
    global $db;
    $activity_id = $db->addslashes($activity_id);
    $ret = array();
    $query = "select * from media_items where activity_id = $activity_id";
    $res = $db->query($query);
    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $ret[] = $row;
    }
    return $ret;
  }

  public function load_get_friend_activities($id, $limit) {
    global $db;
    $this->add_dependency('activities', $id);
    $id = $db->addslashes($id);
    $limit = $db->addslashes($limit);
    $ret = array();
    $res = $db->query("
		select
			activities.title as title,
			activities.body as body,
			activities.created as created,
			persons.id as person_id,
			concat(persons.first_name,' ',persons.last_name) as person_name,
			applications.id as app_id,
			applications.title as app_title,
			applications.directory_title as app_directory_title,
			applications.url as app_url,
			activities.id as activity_id
		from ( activities, persons )
		left join applications on applications.id = activities.app_id
		where
		(
			activities.person_id in (
				select friend_id from friends where person_id = $id
			) or
			activities.person_id in (
				select person_id from friends where friend_id = $id
			)
		) and
			persons.id = activities.person_id and
			activities.title not like '[ACT%'
		order by
			created desc
		limit
			$limit
		");

    while ($row = $db->fetch_array($res, MYSQLI_ASSOC)) {
      $this->add_dependency('activities', $row['person_id']);
      $row['media_items'] = $this->load_media_items($row['activity_id']);
      $ret[] = $row;
    }
    return $ret;
  }

}
