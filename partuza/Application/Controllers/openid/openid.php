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

// The OpenID library is full of warnings and notices, so to suppress
// those we force E_ERROR only for our OpenID event
error_reporting(E_ERROR);

require_once PartuzaConfig::get('library_root')."/Auth/OpenID/Server.php";
require_once PartuzaConfig::get('library_root')."/Auth/OpenID/SReg.php";
require_once PartuzaConfig::get('library_root')."/HybridOAuth.php";
require_once PartuzaConfig::get('library_root')."/OAuth.php";
require_once PartuzaConfig::get('site_root')."/../Shindig/PartuzaOAuthDataStore.php";

class openidController extends baseController {

  public function __construct() {
    parent::__construct();
    $this->openid = $this->model('openid');
  }

  public function auth() {
    $server = &$this->openid->getOpenIdServer();
    $request = $server->decodeRequest();
    $this->openid->setRequestInfo($request);
    if (in_array($request->mode, array('checkid_immediate', 'checkid_setup'))) {
      // Among all steps, only the authentication one involves user interaction, thus it need to be handles seperated here.
      if ($request->idSelect()) {
        // Perform IDP-driven identifier selection
        // TODO: container should implement it by themselves!
        if ($request->mode == 'checkid_immediate') {
          $response = & $request->answer(false);
        } else {
          return $this->trust_render($request);
        }
      } else if ((! $request->identity) && (! $request->idSelect())) {
        // No identifier used or desired; display a page saying so.
        return noIdentifier_render();
      } else if ($request->immediate) {
        $response = &$request->answer(false, $this->openid->buildURL());
      } else {
        // Handles the real authentications
        if (! isset($_SESSION['id'])) {
          $this->login_render();
          return;
        }
        return $this->trust_render($request);
      }
    } else {
      $response = &$server->handleRequest($request);
    }
    $webresponse = &$server->encodeResponse($response);
    if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
      header(sprintf("HTTP/1.1 %d ", $webresponse->code), true, $webresponse->code);
    }
    foreach ($webresponse->headers as $k => $v) {
      header("$k: $v");
    }
    header(header_connection_close);
    print $webresponse->body;
    exit(0);
  }

  public function login() {
    if (! isset($_SESSION['id'])) {
      $this->login_render();
      return;
    }
    $info = $this->openid->getRequestInfo();
    return $this->doAuth($info);
  }

  public function trust() {
    $info = $this->openid->getRequestInfo();
    $trusted = isset($_POST['trust']);
    $accepted_scopes = implode(',', $_POST['scope']);
    return $this->doAuth($info, $trusted, true, @$_POST['idSelect'], $accepted_scopes);
  }

  private function doAuth($info, $trusted = null, $fail_cancels = false, $idpSelect = null, $accepted_scopes = null) {
    if (! $info) {
      // There is no authentication information, so bail
      return $this->openid->authCancel(null);
    }
    if ($info->idSelect()) {
      if ($idpSelect) {
        $req_url = $this->openid->idURL($idpSelect);
      } else {
        $trusted = false;
      }
    } else {
      $req_url = $info->identity;
    }
    $id_url = $this->openid->idUrl($_SESSION['id']);
    $this->openid->setRequestInfo($info);
    if ((! $info->idSelect()) && ($req_url != $id_url)) {
      return $this->openid->authCancel($info);
    }
    if ($trusted) {
      // prepare the OpenID response object
      $this->openid->setRequestInfo();
      $server = &$this->openid->getOpenIdServer();
      $response = &$info->answer(true, null, $req_url);
      
      // Simple Registration Extension
      $people = $this->model('people');
      $person = $people->get_person($_SESSION['id'], true);
      $date_of_birth_month = date('n', $person['date_of_birth']);
      $date_of_birth_day = date('j', $person['date_of_birth']);
      $date_of_birth_year = date('y', $person['date_of_birth']);
      // Answer with Simple Registration data.
      $sreg_data = array(
          'fullname' => $person['first_name'] . ' ' . $person['last_name'], 
          'nickname' => $person['first_name'], 
          'dob' => $date_of_birth_year . '-' . $date_of_birth_month . '-' . $date_of_birth_day, 
          'email' => $person['email'],
          'gender' => $person['gender'], 
          'country' => 'US',
          'language' => 'en');
      // Add the simple registration response values to the OpenID
      // response message.
      $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($info);
      $sreg_response = Auth_OpenID_SRegResponse::extractResponse($sreg_request, $sreg_data);
      $sreg_response->toMessage($response->fields);
      
      // Hybrid OAuth Extension
      $oauth_request = HybridOAuthRequest::fromOpenIDRequest($info);
      // handle accepted scopes (generates OAuth token)
      if ($oauth_request !== null) {
        $oauthDataStore = new PartuzaOAuthDataStore();
        $consumer = $oauthDataStore->lookup_consumer($oauth_request->consumer);
        $token = $oauthDataStore->new_request_token($consumer, '');
        $oauthDataStore->authorize_request_token($token->key);
        $oauth_response = HybridOAuthResponse::buildResponse($oauth_request, $token->key, $accepted_scopes);
        $oauth_response->toMessage($response->fields);
      }
      
      // Generate a response to send to the user agent.
      $webresponse = &$server->encodeResponse($response);
      header('Location: ' . $webresponse->headers['location']);
      header('Connection: close');
      echo $webresponse->body;
    } elseif ($fail_cancels) {
      return $this->openid->authCancel($info);
    } else {
      return $this->trust_render($info);
    }
  }

  /*
   * User has logged-in.  Just ask whether the RP could see user's info.
   */
  private function trust_render($info) {
    $oauth = HybridOAuthRequest::fromOpenIDRequest($info);
    $GLOBALS['render'] = array('info' => serialize($info), 'oauth' => serialize($oauth));
    $this->template('openid/trust.php');
  }

  /*
   * Render login page for user, in order to do the authentication.
   */
  private function login_render() {
    $GLOBALS['render'] = array('openid' => 'login');
    $this->template('home/home.php');
  }
}
