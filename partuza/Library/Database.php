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

class DBException extends Exception {
}

class DB {
  private $db = false;
  private $host;
  private $port;
  private $user;
  private $password;
  private $database;
  private $socket;

  public function __construct($host, $port, $user, $password, $database, $socket) {
    $this->host = $host;
    $this->port = $port;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    $this->socket = $socket;
  }

  public function __destruct() {
    if ($this->db && is_object($this->db)) {
      mysqli_close($this->db);
    }
  }

  public function check() {
    if (! (isset($this->db) && is_object($this->db))) {
      $this->connect();
    }
  }

  public function data_seek($res, $index) {
    mysqli_data_seek($res, $index);
  }

  public function get_handle() {
    $this->check();
    return $this->db;
  }

  public function addslashes($string) {
    return $this->escape_string($string);
  }

  public function escape_string($string) {
    $this->check();
    if (get_magic_quotes_gpc()) {
      $string = stripslashes($string);
    }
    return mysqli_real_escape_string($this->db, $string);
  }

  public function connect() {
    if ($this->db && is_object($this->db)) {
      $this->close();
    }
    if (! $this->db = mysqli_connect($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket)) {
      throw new DBException("Could not connect to DB Host: " . mysqli_connect_error());
    }
    return true;
  }

  public function close() {
    if ($this->db) {
      mysqli_close($this->db);
      $this->db = false;
    }
    return true;
  }

  public function change_user($user, $password) {
    $this->check();
    $this->user = $user;
    $this->password = $password;
    if (! @mysqli_change_user($user, $password)) {
      elog(LWARNING, "Error in DB Change User user: $user, MySql Error: " . mysqli_error($this->db));
      return false;
    }
    return true;
  }

  public function select_db($db) {
    $this->check();
    if (! @mysqli_select_db($db, $this->db)) {
      throw new DBException("Could not Select DB: $db. MySql Error: " . $this->error());
    }
    return true;
  }

  public function error() {
    return @mysqli_error($this->db);
  }

  public function prepare($statement) {
    $this->check();
    if (! $ret = @mysqli_prepare($this->db, $statement)) {
      throw new DBException("Error in Prepare statement on db {$this->database}, MySql Error: " . mysqli_error($this->db) . "<br>Statement: $statement<br>");
    }
    return $ret;
  }

  public function execute($res) {
    if (! $ret = @mysqli_stmt_execute($res)) {
      throw new DBException("Error in Execute statement on db {$this->database}, MySql Error: " . mysqli_error($this->db));
    }
    return $ret;
  }

  public function query($query) {
    $this->check();
    if (! $ret = @mysqli_query($this->db, $query)) {
      throw new DBException("Error in Query on db {$this->database}, MySql Error: " . mysqli_error($this->db) . "<br>Query: $query<br>");
    }
    return $ret;
  }

  public function fetch_row($res) {
    return @mysqli_fetch_row($res);
  }

  public function fetch_array($res, $result_type = MYSQLI_BOTH) {
    return @mysqli_fetch_array($res, $result_type);
  }

  public function num_rows($res) {
    return @mysqli_num_rows($res);
  }

  public function num_fields($res) {
    return @mysqli_num_fields($res);
  }

  public function affected_rows() {
    return @mysqli_affected_rows($this->db);
  }

  public function insert_id() {
    if (! $ret = mysqli_insert_id($this->db)) {
      throw new DBException("Error in insert_id, MySql Error: " . $this->error());
    }
    return $ret;
  }

  public function field_len($res, $field) {
    return @mysqli_field_len($res, $field);
  }

  public function field_name($res, $field) {
    return @mysqli_field_name($res, $field);
  }

  public function field_type($res, $field) {
    return @mysqli_field_type($res, $field);
  }

  public function list_fields($db, $table) {
    $this->check();
    return @mysqli_list_fields($db, $table, $this->db);
  }

  public function list_tables($db) {
    $this->check();
    return @mysqli_list_tables($db, $this->db);
  }

  public function list_dbs() {
    $this->check();
    return @mysqli_list_dbs($this->db);
  }

  public function name($res) {
    return @mysqli_name($res);
  }

  public function tablename($res, $row) {
    return @mysqli_tablename($res, $row);
  }

  public function seek($res, $pos) {
    if (! $ret = @mysqli_data_seek($res, $pos)) {
      throw new DBException("Error in Seek, MySql Error: " . $this->error());
    }
    return $ret;
  }

  public function free_result($res) {
    @mysqli_free_result($res);
  }

  public function has_table($table) {
    $this->check();
    return @mysqli_query("desc $table", $this->db);
  }

  public function drop_db($db) {
    $this->check();
    elog(LNOTICE, "Droping DB: $db");
    return @mysqli_drop_db($db, $this->db);
  }

  public function create_db($db) {
    $this->check();
    elog(LNOTICE, "Creating DB: $db");
    $ret = mysqli_create_db($db, $this->db);
    return $ret;
  
  }
}
