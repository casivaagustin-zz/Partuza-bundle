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
 * Implementation of supported services backed using Partuza's DB Fetcher
 */
class PartuzaService implements ActivityService, PersonService, AppDataService, MessagesService, AlbumService, MediaItemService {

  private $partuzaConfig;

  private function checkPartuzaConfig() {
    if (!isset($this->partuzaConfig)) {
      $this->initializeConfig();
    }
  }

  /**
   * Initializes the partuza config and includes the required library file.
   */
  private function initializeConfig() {
    // Uses the location of PartuzaService.php to find the config.php and Image.php files.
    $extension_class_paths = Config::get('extension_class_paths');
    foreach (explode(',', $extension_class_paths) as $path) {
      if (file_exists($path . "/PartuzaService.php")) {
        $configFile = $path . '/../html/config.php';
        $imageLibrary = $path . '/../Library/Image.php';
        require $configFile;
        require $imageLibrary;
        if (! isset($config)) {
          throw new Exception("Could not locate partuza's configuration file while scanning extension_class_paths ({$extension_class_paths})");
        }
        $this->partuzaConfig = $config;
        // Removes the last '/' if it is the last character.
        if ($this->partuzaConfig['partuza_url'][strlen($this->partuzaConfig['partuza_url']) - 1] == '/') {
          $this->partuzaConfig['partuza_url'] = substr($this->partuzaConfig['partuza_url'], 0, strlen($this->partuzaConfig['partuza_url']) - 1);
        }
        if ($this->partuzaConfig['site_root'][strlen($this->partuzaConfig['site_root']) - 1] == '/') {
          $this->partuzaConfig['site_root'] = substr($this->partuzaConfig['site_root'], 0, strlen($this->partuzaConfig['site_root']) - 1);
        }
      }
    }
  }

  public function getPerson($userId, $groupId, $fields, SecurityToken $token) {
    if (! is_object($userId)) {
      $userId = new UserId('userId', $userId);
      $groupId = new GroupId('self', 'all');
    }
    $person = $this->getPeople($userId, $groupId, new CollectionOptions(), $fields, $token);
    if (is_array($person->getEntry())) {
      $person = $person->getEntry();
      if (is_array($person) && count($person) == 1) {
        return array_pop($person);
      }
    }
    throw new SocialSpiException("Person not found", ResponseError::$BAD_REQUEST);
  }

  public function getPeople($userId, $groupId, CollectionOptions $options, $fields, SecurityToken $token) {
    $ids = $this->getIdSet($userId, $groupId, $token);
    $allPeople = PartuzaDbFetcher::get()->getPeople($ids, $fields, $options, $token);
    $totalSize = $allPeople['totalSize'];
    $people = array();
    foreach ($ids as $id) {
      $person = null;
      if (is_array($allPeople) && isset($allPeople[$id])) {
        $person = $allPeople[$id];
        if (! $token->isAnonymous() && $id == $token->getViewerId()) {
          $person->setIsViewer(true);
        }
        if (! $token->isAnonymous() && $id == $token->getOwnerId()) {
          $person->setIsOwner(true);
        }
        if (! in_array('@all', $fields)) {
          $newPerson = array();
          $newPerson['isOwner'] = $person->isOwner;
          $newPerson['isViewer'] = $person->isViewer;
          $newPerson['displayName'] = $person->displayName;
          // Force these fields to always be present
          $fields[] = 'id';
          $fields[] = 'displayName';
          $fields[] = 'thumbnailUrl';
          $fields[] = 'profileUrl';
          foreach ($fields as $field) {
            if (isset($person->$field) && ! isset($newPerson[$field])) {
              $newPerson[$field] = $person->$field;
            }
          }
          $person = $newPerson;
        }
        array_push($people, $person);
      }
    }
    $sorted = $this->sortPersonResults($people, $options);
    $collection = new RestfulCollection($people, $options->getStartIndex(), $totalSize);
    $collection->setItemsPerPage($options->getCount());
    if (! $sorted) {
      $collection->setSorted(false); // record that we couldn't sort as requested
    }
    if ($options->getUpdatedSince()) {
      $collection->setUpdatedSince(false); // we can never process an updatedSince request
    }
    return $collection;
  }

  public function deletePersonData($userId, GroupId $groupId, $appId, $fields, SecurityToken $token) {
    $ids = $this->getIdSet($userId, $groupId, $token);
    if (count($ids) < 1) {
      throw new InvalidArgumentException("No userId specified");
    } elseif (count($ids) > 1) {
      throw new InvalidArgumentException("Multiple userIds not supported");
    }
    $userId = $ids[0];
    if ($fields == null) {
      if (! PartuzaDbFetcher::get()->deleteAppData($userId, '*', $appId)) {
        throw new SocialSpiException("Internal server error", ResponseError::$INTERNAL_ERROR);
      }
    } else {
      foreach ($fields as $key) {
        if (! self::isValidKey($key) && $key != '*') {
          throw new SocialSpiException("The person app data key had invalid characters", ResponseError::$BAD_REQUEST);
        }
      }
      foreach ($fields as $key) {
        if (! PartuzaDbFetcher::get()->deleteAppData($userId, $key, $appId)) {
          throw new SocialSpiException("Internal server error", ResponseError::$INTERNAL_ERROR);
        }
      }
    }
  }

  public function getPersonData($userId, GroupId $groupId, $appId, $fields, SecurityToken $token) {
    $ids = $this->getIdSet($userId, $groupId, $token);
    $data = PartuzaDbFetcher::get()->getAppData($ids, $fields, $appId);
    // If the data array is empty, return empty DataCollection.
    return new DataCollection($data);
  }

  public function updatePersonData(UserId $userId, GroupId $groupId, $appId, $fields, $values, SecurityToken $token) {
    if ($userId->getUserId($token) == null) {
      throw new SocialSpiException("Unknown person id.", ResponseError::$NOT_FOUND);
    }
    foreach ($fields as $key) {
      if (! self::isValidKey($key)) {
        throw new SocialSpiException("The person app data key had invalid characters", ResponseError::$BAD_REQUEST);
      }
    }
    switch ($groupId->getType()) {
      case 'self':
        foreach ($fields as $key) {
          $value = isset($values[$key]) ? $values[$key] : null;
          if (! PartuzaDbFetcher::get()->setAppData($userId->getUserId($token), $key, $value, $appId)) {
            throw new SocialSpiException("Internal server error", ResponseError::$INTERNAL_ERROR);
          }
        }
        break;
      default:
        throw new SocialSpiException("We don't support updating data in batches yet", ResponseError::$NOT_IMPLEMENTED);
        break;
    }
  }

  public function getActivity($userId, $groupId, $appdId, $fields, $activityId, SecurityToken $token) {
    $activities = $this->getActivities($userId, $groupId, $appdId, null, null, null, null, 0, 20, $fields, array($activityId), $token);
    if ($activities instanceof RestFulCollection) {
      $activities = $activities->getEntry();
      foreach ($activities as $activity) {
        if ($activity->getId() == $activityId) {
          return $activity;
        }
      }
    }
    throw new SocialSpiException("Activity not found", ResponseError::$NOT_FOUND);
  }

  public function getActivities($userIds, $groupId, $appId, $sortBy, $filterBy, $filterOp, $filterValue, $startIndex, $count, $fields, $activityIds, $token) {
    $ids = $this->getIdSet($userIds, $groupId, $token);
    $activities = PartuzaDbFetcher::get()->getActivities($ids, $appId, $sortBy, $filterBy, $filterOp, $filterValue, $startIndex, $count, $fields, $activityIds);
    if ($activities) {
      return $this->getRestfulCollection($activities);
    } else {
      throw new SocialSpiException("Invalid activity specified", ResponseError::$NOT_FOUND);
    }
  }

  public function createActivity($userId, $groupId, $appId, $fields, $activity, SecurityToken $token) {
    try {
      if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
        throw new SocialSpiException("Create activity permission denied.", ResponseError::$UNAUTHORIZED);
      }
      PartuzaDbFetcher::get()->createActivity($userId->getUserId($token), $activity, $token->getAppId());
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Invalid create activity request: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function deleteActivities($userId, $groupId, $appId, $activityIds, SecurityToken $token) {
    $ids = $this->getIdSet($userId, $groupId, $token);
    if (count($ids) < 1 || count($ids) > 1) {
      throw new SocialSpiException("Invalid user id or count", ResponseError::$BAD_REQUEST);
    }
    if (! PartuzaDbFetcher::get()->deleteActivities($ids[0], $appId, $activityIds)) {
      throw new SocialSpiException("Invalid activity id(s)", ResponseError::$NOT_FOUND);
    }
  }

  public function createMessage($userId, $msgCollId, $message, $token) {
    $from = $userId->getUserId($token);
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $from) {
      throw new SocialSpiException("Create message permission denied.", ResponseError::$UNAUTHORIZED);
    }
    if (in_array($from, $message['recipients'])) {
      throw new SocialSpiException("Can't send message to myself.", ResponseError::$BAD_REQUEST);
    }
    try {
      PartuzaDbFetcher::get()->createMessage($from, $token->getAppId(), $msgCollId, $message);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Invalid create message request: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function deleteMessages($userId, $msgCollId, $messageIds, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Delete message permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->deleteMessages($userId->getUserId($token), $token->getAppId(), $msgCollId, $messageIds);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Server error: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function updateMessage($userId, $msgCollId, $message, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Delete message permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->updateMessage($userId->getUserId($token), $token->getAppId(), $msgCollId, $message);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Server error: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  private function getRestfulCollection($results) {
    $totalResults = $results['totalResults'];
    $startIndex = $results['startIndex'];
    $count = $results['count'];
    unset($results['totalResults']);
    unset($results['startIndex']);
    unset($results['count']);
    $ret = new RestfulCollection($results, $startIndex, $totalResults);
    $ret->setItemsPerPage($count);
    return $ret;
  }

  public function getMessages($userId, $msgCollId, $fields, $msgIds, $options, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Get message permission denied.", ResponseError::$UNAUTHORIZED);
    }
    $messages = PartuzaDbFetcher::get()->getMessages($userId->getUserId($token), $msgCollId, $fields, $msgIds, $options);
    if ($messages) {
      return $this->getRestfulCollection($messages);
    } else {
      throw new SocialSpiException("Message not found.", ResponseError::$NOT_FOUND);
    }
  }

  public function createMessageCollection($userId, $msgCollection, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Create message collection permission denied.", ResponseError::$UNAUTHORIZED);
    }
    return PartuzaDbFetcher::get()->createMessageCollection($userId->getUserId($token), $token->getAppId(), $msgCollection);
  }

  public function updateMessageCollection($userId, $msgCollection, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Update message collection permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->updateMessageCollection($userId->getUserId($token), $token->getAppId(), $msgCollection);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Server error: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function deleteMessageCollection($userId, $msgCollId, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Delete message collection permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->deleteMessageCollection($userId->getUserId($token), $token->getAppId(), $msgCollId);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Server error: " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function getMessageCollections($userId, $fields, $options, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Get message collection permission denied.", ResponseError::$UNAUTHORIZED);
    }
    $messageCollections = PartuzaDbFetcher::get()->getMessageCollections($userId->getUserId($token), $token->getAppId(), $fields, $options);
    if ($messageCollections) {
      return $this->getRestfulCollection($messageCollections);
    } else {
      throw new SocialSpiException("Message collection not found.", ResponseError::$NOT_FOUND);
    }
  }

  public function getAlbums($userId, $groupId, $albumIds, $options, $fields, $token) {
    try {
      $albums = PartuzaDbFetcher::get()->getAlbums($userId->getUserId($token), $groupId, $token->getAppId(), $albumIds, $options, $fields);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to fetch album." . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
    if ($albums) {
      return $this->getRestfulCollection($albums);
    } else {
      throw new SocialSpiException("Albums not found.", ResponseError::$NOT_FOUND);
    }
  }

  public function createAlbum($userId, $groupId, $album, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Create album permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      return PartuzaDbFetcher::get()->createAlbum($userId->getUserId($token), $groupId, $token->getAppId(), $album);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to create album. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function updateAlbum($userId, $groupId, $album, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Update album permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->updateAlbum($userId->getUserId($token), $groupId, $token->getAppId(), $album);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to update the album. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function deleteAlbum($userId, $groupId, $albumIds, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Delete album permission denied.", ResponseError::$UNAUTHORIZED);
    }
    foreach ($albumIds as $albumId) {
	    try {
	      PartuzaDbFetcher::get()->deleteAlbum($userId->getUserId($token), $groupId, $token->getAppId(), $albumId);
	      // Deletes the uploaded files that is associated with the media items in the album.
	      $this->checkPartuzaConfig();
	      $files = glob($this->getAlbumsPath() . '/' . intval($albumId) . '/*');
	      $size = 0;
	      foreach ($files as $file) {
	        $size += filesize($file);
	        @unlink($file);
	      }
	      $currentSize = PartuzaDbFetcher::get()->getUploadedSize($userId->getUserId($token));
	      $newSize = $currentSize - $size > 0 ? $currentSize - $size : 0;
	      PartuzaDbFetcher::get()->setUploadedSize($userId->getUserId($token), $newSize);
	      @rmdir($this->getAlbumsPath() . '/' . intval($albumId));
	    } catch (SocialSpiException $e) {
	      throw $e;
	    } catch (Exception $e) {
	      throw new SocialSpiException("Unable to delete the album. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
	    }
    }
  }

  public function getMediaItems($userId, $groupId, $albumId, $mediaItemIds, $options, $fields, $token) {
    try {
      $mediaItems = PartuzaDbFetcher::get()->getMediaItems($userId->getUserId($token), $groupId, $token->getAppId(), $albumId, $mediaItemIds, $options, $fields);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to fetch media items. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
    if ($mediaItems) {
      return $this->getRestfulCollection($mediaItems);
    } else {
      throw new SocialSpiException("media items not found.", ResponseError::$NOT_FOUND);
    }
  }


  private function checkUploadQuota($userId, $addedSize) {
    $this->checkPartuzaConfig();
    $quota = isset($this->partuzaConfig['upload_quota']) ? $this->partuzaConfig['upload_quota'] : 0;
    $currentSize = PartuzaDbFetcher::get()->getUploadedSize($userId);
    if (($currentSize + $addedSize) > $quota) {
      throw new SocialSpiException("The upload_quota $quota is exceeded.", ResponseError::$REQUEST_TOO_LARGE);
    }
  }

  /**
   * Makes sure the file meta data is valid and checks the user quota.
   */
  private function checkFile($file) {
    if (!isset($file['tmp_name']) || !file_exists($file['tmp_name']) || substr($file['type'], 0, strlen('image/')) != 'image/') {
      throw new SocialSpiException("We only support images types i.e. gif, jpg and png.", ResponseError::$NOT_IMPLEMENTED);
    }
    $ext = $this->getExtensionName($file['type']);
    $accepted = array('gif', 'jpg', 'jpeg', 'png');
    if (!in_array($ext, $accepted)) {
      throw new SocialSpiException("We only support images types i.e. gif, jpg and png.", ResponseError::$NOT_IMPLEMENTED);
    }
  }

  /**
   * Uses the image libary to read the user uploaded file and move the file to the proper location.
   * The path for the file is $site_root/images/albums/$albumsId/$mediaItem['id'].$fileExtensionName
   */
  private function moveFile($userId, $file, $mediaItem) {
    $this->checkPartuzaConfig();

    if ($mediaItem && isset($mediaItem['id']) && isset($file['tmp_name'])) {
      $path = $this->getAlbumsPath() . '/' . $mediaItem['albumId'];
      if (!is_dir($path) && !@mkdir($path, 0775, true)) {
        throw new SocialSpiException("Couldn't create the directory for the uploaded files.", ResponseError::$INTERNAL_ERROR);
      }
      $fileName = $path . '/' . $mediaItem['id'] . '.' . $this->getExtensionName($file['type']);
      Image::convert($file['tmp_name'], $fileName);
      $currentSize = PartuzaDbFetcher::get()->getUploadedSize($userId);
      PartuzaDbFetcher::get()->setUploadedSize($userId, $currentSize + $file['size']);
    }
  }
  
  /**
   * Returns the base path of the albums in the server.
   */
  private function getAlbumsPath() {
    $this->checkPartuzaConfig();
    return $this->partuzaConfig['site_root'] . '/images/albums';
  }
  
  /**
   * Returns the public accessible URL base of the albums directory.
   */
  private function getAlbumsUrl() {
    $this->checkPartuzaConfig();
    return $this->partuzaConfig['partuza_url'] . '/images/albums';
  }
  
  /**
   * Given the content type returns the file extension name.
   */
  private function getExtensionName($type) {
    $ext = strtolower(substr($type, strpos($type, '/') + 1));
    if (strpos($ext, ';') !== false) {
      $ext = substr($ext, 0, strpos($ext, ';'));
    }
    return trim($ext);
  }

  /**
   * Creates the media item and the image file. Currently only accepts the gif, jpg, jpeg and png image file type.
   * The location of the created image file is $siteRoot/images/albums/$albumId/($mediaItemId . $fileExtensionName).
   */
  public function createMediaItem($userId, $groupId, $mediaItem, $file, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Create media item permission denied.", ResponseError::$UNAUTHORIZED);
    }
    if (!empty($file)) {
      $this->checkUploadQuota($userId->getUserId($token), $file['size']);
      $this->checkFile($file);
      // The url will be overwritten to the fully qualified url.
      $mediaItem['url'] = '@field:' . $file['name'];
      $mediaItem['mimeType'] = $file['type'];
    }
    try {
      $ret = PartuzaDbFetcher::get()->createMediaItem($userId->getUserId($token), $groupId, $token->getAppId(), $mediaItem);
      if (!empty($file)) {
        $this->moveFile($userId->getUserId($token), $file, $ret);
        $ext = $this->getExtensionName($file['type']);
        $this->checkPartuzaConfig();
        $url = $this->getAlbumsUrl() . '/' . $ret['albumId'] . '/' . $ret['id'] . ".$ext";
        PartuzaDbFetcher::get()->updateMediaItemUrl($ret['id'], $url);
        $ret['url'] = $url;
      }
      return $ret;
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to create media item. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function updateMediaItem($userId, $groupId, $mediaItem, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Update media item permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->updateMediaItem($userId->getUserId($token), $groupId, $token->getAppId(), $mediaItem);
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to update the media item. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  public function deleteMediaItems($userId, $groupId, $albumId, $mediaItemIds, $token) {
    if ($token->getOwnerId() != $token->getViewerId() || $token->getViewerId() != $userId->getUserId($token)) {
      throw new SocialSpiException("Delete media items permission denied.", ResponseError::$UNAUTHORIZED);
    }
    try {
      PartuzaDbFetcher::get()->deleteMediaItems($userId->getUserId($token), $groupId, $token->getAppId(), $albumId, $mediaItemIds);
      // Deletes the uploaded file that is associated with the media item.
      $this->checkPartuzaConfig();
      foreach ($mediaItemIds as $mediaItemId) {
        $files = glob($this->getAlbumsPath() . '/' . intval($albumId) . "/$mediaItemId.*");
        if (count($files) == 1) {
          $size = filesize($files[0]);
          $currentSize = PartuzaDbFetcher::get()->getUploadedSize($userId->getUserId($token));
          $newSize = $currentSize - $size > 0 ? $currentSize - $size : 0;
          PartuzaDbFetcher::get()->setUploadedSize($userId->getUserId($token), $newSize);
          @unlink($files[0]);
        }
      }
    } catch (SocialSpiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new SocialSpiException("Unable to delete the media items. " . $e->getMessage(), ResponseError::$INTERNAL_ERROR);
    }
  }

  /**
   * Get the set of user id's from a user or collection of users, and group
   */
  private function getIdSet($user, GroupId $group, SecurityToken $token) {
    $ids = array();
    if ($user instanceof UserId) {
      $userId = $user->getUserId($token);
      if ($group == null) {
        return array($userId);
      }
      switch ($group->getType()) {
        case 'all':
        case 'friends':
        case 'groupId':
          $friendIds = PartuzaDbFetcher::get()->getFriendIds($userId);
          if (is_array($friendIds) && count($friendIds)) {
            $ids = $friendIds;
          }
          break;
        case 'self':
          $ids[] = $userId;
          break;
      }
    } elseif (is_array($user)) {
      $ids = array();
      foreach ($user as $id) {
        $ids = array_merge($ids, $this->getIdSet($id, $group, $token));
      }
    }
    return $ids;
  }

  /**
   * Determines whether the input is a valid key. Valid keys match the regular
   * expression [\w\-\.]+.
   *
   * @param key the key to validate.
   * @return true if the key is a valid appdata key, false otherwise.
   */
  public static function isValidKey($key) {
    if (empty($key)) {
      return false;
    }
    for ($i = 0; $i < strlen($key); ++ $i) {
      $c = substr($key, $i, 1);
      if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || ($c >= '0' && $c <= '9') || ($c == '-') || ($c == '_') || ($c == '.')) {
        continue;
      }
      return false;
    }
    return true;
  }

  private function sortPersonResults(&$people, $options) {
    if (! $options->getSortBy()) {
      return true; // trivially sorted
    }
    // for now, partuza can only sort by displayName, which also demonstrates returning sorted: false
    if ($options->getSortBy() != 'displayName') {
      return false;
    }
    usort($people, array($this, 'comparator'));
    if ($options->getSortOrder() != CollectionOptions::SORT_ORDER_ASCENDING) {
      $people = array_reverse($people);
    }
    return true;
  }

  private function comparator($person, $person1) {
    $name = ($person instanceof Person ? $person->getDisplayName() : $person['displayName']);
    $name1 = ($person1 instanceof Person ? $person1->getDisplayName() : $person1['displayName']);
    return strnatcasecmp($name, $name1);
  }
}
