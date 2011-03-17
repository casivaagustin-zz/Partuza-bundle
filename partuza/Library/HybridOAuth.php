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
 *
 * Hybrid OAuth Authentication/Authorization extension, which is documented in:
 * <http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html>
 *
 * This module contains object representing OAuth extension
 * requests and responses that can be used with both OpenID relying
 * parties and OpenID providers.
 *
 * 1. The relying party creates a request object and adds it to the
 * {@link HybridOAuthRequest} object before making the
 * checkid request to the OpenID provider:
 *
 *   $oauth_req = new HybridOAuthRequest('consumer', 'scope');
 *   $auth_request->addExtension($oauth_req);
 *
 *
 * 2. The OpenID provider extracts the OAuth extension request
 * from the OpenID request using {@link
 * HybridOAuthRequest::fromOpenIDRequest}, gets the user's
 * approval and data, creates an {@link HybridOAuthResponse}
 * object and adds it to the id_res response:
 *
 *   $oauth_req = HybridOAuthRequest::fromOpenIDRequest(
 *                                  $checkid_request);
 *   // [ get the user's approval and data, informing the user that
 *   //   the RP will request the resources specified in scope ]
 *   $oauth_resp = HybridOAuthResponse::buildResponse($oauth_req,
 *                                  $request_token, $accepted_scopes);
 *   $oauth_resp->toMessage($openid_response->fields);
 *
 * 3. The relying party uses {@link
 * HybridOAuthResponse::fromSuccessResponse} to extract the data
 * from the OpenID response:
 *
 *   $oauth_resp = HybridOAuthResponse::fromSuccessResponse(
 *                                  $success_response);
 *
 */

/**
 * Import message and extension internals.
 */
require_once 'Auth/OpenID/Message.php';
require_once 'Auth/OpenID/Extension.php';

// URI used in the wild for Yadis documents advertising support
// <http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html>
define('HYBRID_OAUTH_NS_URI_DRAFT', 'http://specs.openid.net/extensions/oauth/1.0');

// This attribute will always hold the preferred URI to use when
// adding sreg support to an XRDS file or in an OpenID namespace
// declaration.
define('HYBRID_OAUTH_NS_URI', HYBRID_OAUTH_NS_URI_DRAFT);

// Prefered namespace alias used for hybrid OAuth.
define('HYBRID_OAUTH_NS_ALIAS', 'oauth');

Auth_OpenID_registerNamespaceAlias(HYBRID_OAUTH_NS_URI, HYBRID_OAUTH_NS_ALIAS);

/**
 * Does the given endpoint advertise support for simple
 * registration?
 *
 * $endpoint: The endpoint object as returned by OpenID discovery.
 * returns whether an sreg type was advertised by the endpoint
 */
function Auth_OpenID_supportsHybridOAuth(&$endpoint) {
  return ($endpoint->usesExtension(HYBRID_OAUTH_NS_URI));
}

/**
 * A base class for classes dealing with Hybrid OAuth protocol
 * messages.
 */
class HybridOAuthBase extends Auth_OpenID_Extension {

  var $ns_alias = HYBRID_OAUTH_NS_ALIAS;

  /**
   * Extract the hybrid OAuth namespace URI from the given
   * OpenID message. Handles OpenID 1 and 2, as well as both OAuth
   * namespace URIs found in the wild, as well as missing namespace
   * definitions (for OpenID 1)
   *
   * $message: The OpenID message from which to parse hybrid OAuth
   * fields. This may be a request or response message.
   *
   * Returns the hybrid OAuth namespace URI for the supplied message. The
   * message may be modified to define a hybrid OAuth namespace.
   *
   * @access protected
   */
  protected static function getOAuthNS(&$message) {
    $alias = null;

    // See if there exists an alias.
    $oauth_ns_uri = HYBRID_OAUTH_NS_URI;
    $alias = $message->namespaces->getAlias($oauth_ns_uri);

    if ($alias === null) {
      // There is no alias , so try to add one.
      if ($message->namespaces->addAlias(HYBRID_OAUTH_NS_URI, 'oauth') === null) {
        // An alias for the string 'oauth' already exists, but
        // it's defined for something other than hybrid OAuth
        return null;
      }
    }

    return $oauth_ns_uri;
  }
}

/**
 * An object to hold the state of a hybrid OAuth request.
 *
 * consumer: consumer_key for OAuth in this request
 *
 * scope: A string that encodes, in a way possibly specific
 *        to the Combined Provider, one or more scopes for
 *        the OAuth token expected in the authentication
 *        response.
 */
class HybridOAuthRequest extends HybridOAuthBase {

  public $consumer = null;
  public $scope = null;

  /**
   * Initialize an empty simple registration request.
   */
  function HybridOAuthRequest($consumer = null, $scope = null, $oauth_ns_uri = HYBRID_OAUTH_NS_URI) {
    $this->consumer = $consumer;
    $this->scope = $scope;
    $this->ns_uri = $oauth_ns_uri;
  }

  /**
   * Create a hybrid OAuth request that contains the fields
   * that were requested in the OpenID request with the given
   * arguments.  It's a factory function.
   *
   * $request: The OpenID authentication request from which to
   * extract a hybrid OAuth request.
   *
   * $cls: name of class to use when creating hybrid OAuth request object.
   * Used for testing.
   *
   * Returns the newly created hybrid OAuth request
   */
  public static function fromOpenIDRequest($request) {
    $obj = new HybridOAuthRequest();

    // Since we're going to mess with namespace URI mapping, don't
    // mutate the object that was passed in.
    $msg = $request->message;

    $obj->ns_uri = parent::getOAuthNS($msg);
    $args = $msg->getArgs($obj->ns_uri);

    if ($args == null || Auth_OpenID::isFailure($args)) {
      return null;
    }

    // parse extension arguments into the request object body
    $obj->parseExtensionArgs($args);

    return $obj;
  }

  /**
   * Parse the unqualified hybrid OAuth request parameters
   * and add them to this object.
   *
   * This method is essentially the inverse of
   * getExtensionArgs. This method restores the serialized hybrid
   * OAuth request fields.
   *
   * If you are extracting arguments from a standard OpenID
   * checkid_* request, you probably want to use fromOpenIDRequest,
   * which will extract the oauth namespace and arguments from the
   * OpenID request. This method is intended for cases where the
   * OpenID server needs more control over how the arguments are
   * parsed than that method provides.
   *
   * $args = $message->getArgs($ns_uri);
   * $request->parseExtensionArgs($args);
   *
   * $args: The unqualified hybrid OAuth arguments
   */
  public function parseExtensionArgs($args) {
    $this->consumer = Auth_OpenID::arrayGet($args, 'consumer');
    $this->scope = Auth_OpenID::arrayGet($args, 'scope');
    return true;
  }

  /**
   * Get a dictionary of unqualified hybrid OAuth arguments
   * representing this request.
   *
   * This method is essentially the inverse of
   * C{L{parseExtensionArgs}}. This method serializes the simple
   * registration request fields.
   */
  public function getExtensionArgs() {
    $args = array();

    if ($this->consumer) {
      $args['consumer'] = $this->consumer;
    }

    if ($this->scope) {
      $args['scope'] = $this->scope;
    }

    return $args;
  }
}

/**
 * Represents the data returned in a hybrid OAuth response
 * inside of an OpenID C{id_res} response. This object will be created
 * by the OpenID server, added to the C{id_res} response object, and
 * then extracted from the C{id_res} message by the Consumer.
 */
class HybridOAuthResponse extends HybridOAuthBase {

  public $request_token = null;
  public $scope = null;

  function HybridOAuthResponse($request_token = null, $scope = null, $oauth_ns_uri = HYBRID_OAUTH_NS_URI) {
    $this->request_token = $request_token;
    $this->scope = $scope;
    $this->ns_uri = $oauth_ns_uri;
  }

  /**
   * Take a cunsumer key and an array of user accepted scopes values and
   * generates a C{L{HybridOAuthResponse}} object, which contains user
   * approved OAuth Request Token.  And this token will be exchanged for
   * Access Token by RP in the future.
   *
   * request: The hybrid OAuth request, which contains consumer_key, etc.
   *
   * request_token: The request token generated by OAuth service
   *
   * accept_scopes: The user accepted scopes which the generated request
   *                token will cover
   */
  public static function buildResponse($request, $request_token, $accepted_scopes) {
    $obj = new HybridOAuthResponse($request_token, $accepted_scopes, $request->ns_uri);
    return $obj;
  }

  /**
   * Create a C{L{HybridOAuthResponse}} object from a successful OpenID
   * library response
   * (C{L{openid.consumer.consumer.SuccessResponse}}) response
   * message
   *
   * success_response: A SuccessResponse from consumer.complete()
   *
   * signed_only: Whether to process only data that was
   * signed in the id_res message from the server.
   *
   * Returns a hybrid OAuth response containing the user granted access
   * token that was supplied with the C{id_res} response.
   */
  public static function fromSuccessResponse(&$success_response, $signed_only = true) {
    $msg = $success_response->message;
    $ns_uri = parent::_getOAuthNS($msg);

    if ($signed_only) {
      $args = $success_response->getSignedNS($ns_uri);
    } else {
      $args = $msg->getArgs($ns_uri);
    }

    if ($args == null || Auth_OpenID::isFailure($args)) {
      return null;
    }

    // parse extension arguments into the request object body
    $obj->parseExtensionArgs($args);

    return $obj;
  }

  /**
   * Parse the unqualified hybrid OAuth response parameters
   * and add them to this object.
   *
   * This method is essentially the inverse of
   * getExtensionArgs. This method restores the serialized hybrid
   * OAuth response fields.
   *
   * If you are extracting arguments from a standard OpenID
   * checkid_* response, you probably want to use fromSuccessResponse,
   * which will extract the oauth namespace and arguments from the
   * successful OpenID response. This method is intended for cases where the
   * OpenID server needs more control over how the arguments are
   * parsed than that method provides.
   *
   * $args = $message->getArgs($ns_uri);
   * $response->parseExtensionArgs($args);
   *
   * $args: The unqualified hybrid OAuth arguments
   */
  public function parseExtensionArgs($args) {
    $this->request_token = Auth_OpenID::arrayGet($args, 'request_token');
    $this->scope = Auth_OpenID::arrayGet($args, 'scope');
    return true;
  }

  public function getExtensionArgs() {
    $args = array();

    if ($this->request_token) {
      $args['request_token'] = $this->request_token;
    }

    if ($this->scope) {
      $args['scope'] = $this->scope;
    }

    return $args;
  }
}
