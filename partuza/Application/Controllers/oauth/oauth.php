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

require_once (PartuzaConfig::get('library_root') . "/OAuth.php");
require_once (PartuzaConfig::get('site_root') . "/../Shindig/PartuzaOAuthDataStore.php");

class oauthController extends baseController {
  
  private $oauthDataStore;

  public function __construct() {
    parent::__construct();
    $this->oauthDataStore = new PartuzaOAuthDataStore();
  }

  public function request_token($params) {
    try {
          var_dump($params);
      $server = new OAuthServer($this->oauthDataStore);
      $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
      $server->add_signature_method(new OAuthSignatureMethod_PLAINTEXT());
      $request = OAuthRequest::from_request();
      $token = $server->fetch_request_token($request);
      if ($token) {
        echo $token->to_string();
      }
    } catch (OAuthException $e) {
      $this->sendServerError(401, $e->getMessage());
     var_dump(      $e->getTrace());
    } catch (Exception $e) {
      $this->sendServerError(400, $e->getMessage());
    }
  }

  public function access_token($params) {
    try {
      $server = new OAuthServer($this->oauthDataStore);
      $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
      $server->add_signature_method(new OAuthSignatureMethod_PLAINTEXT());
      $request = OAuthRequest::from_request();
      $token = $server->fetch_access_token($request);
      if ($token) {
        echo $token->to_string();
      }
    } catch (OAuthException $e) {
      $this->sendServerError(401, $e->getMessage());
    } catch (Exception $e) {
      $this->sendServerError(400, $e->getMessage());
    }
  }

  public function authorize($params) {
    if (!isset($_SESSION['id'])) {
      header("Location: /login?redirect=".urlencode($_SERVER['REQUEST_URI']));
      die();
    }
    $request = OAuthRequest::from_request();
    $token = $request->get_parameter('oauth_token');
    $callback = $request->get_parameter('oauth_callback');
    if (! $token) {
      $this->sendServerError('400', 'Bad Request - missing oauth_token');
      return;
    }
    $this->template('oauth/authorize.php', array('oauth_token' => $token, 'oauth_callback' => $callback));
  }

  public function approveAuthorization($params) {
    $oauth_token = $_REQUEST['oauth_token'];
    $oauth_callback = $_REQUEST['oauth_callback'];
    $ds = $this->oauthDataStore;
    $ds->authorize_request_token($oauth_token);
    // if callback was provided, append token and return to the callback
    if ($oauth_callback) {
      $oauth_callback .= (strpos($oauth_callback, '?') === false ? '?' : '&');
      $oauth_callback .= 'oauth_token=' . urlencode($oauth_token);
      $this->redirectTo($oauth_callback);
    } else {
      echo "Your application is now authorized.";
    }
  }

  /* --- internal helper functions  */
  
  private function sendServerError($code, $message) {
    header("HTTP/1.0 $code $message", true);
    echo "<html><body><h1>$code - $message</h1></body></html>";
  }

  private function redirectTo($uri) {
    header("Location: $uri");
  }
}
