<?php
/* Copyright (c) 2007 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Author: Eric Bidelman <e.bidelman@google.com>
 *         Jacky Wang <jacky.chao.wang@gmail.com>
 */

// Loads OAuth, OpenID, and common utility functions.
require_once 'common.inc.php';
require_once 'config.php';

$SIG_METHOD = new OAuthSignatureMethod_HMAC_SHA1();
$peopleUri = "{$OPENSOCIAL_ENDPOINT}/social/rest/people/@me/@self";
$SCOPES = array(
  $peopleUri
);

$openid_params = array(
  'openid.ns'              => 'http://specs.openid.net/auth/2.0',
  'openid.claimed_id'      => @$_REQUEST['openid_identifier'],
  'openid.identity'        => @$_REQUEST['openid_identifier'],
  'openid.return_to'       => "http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}",
  'openid.realm'           => "http://{$_SERVER['SERVER_NAME']}",
  'openid.mode'            => @$_REQUEST['openid_mode'],
  'openid.ns.oauth'        => 'http://specs.openid.net/extensions/oauth/1.0',
  'openid.oauth.consumer'  => $CONSUMER_KEY,
  'openid.oauth.scope'     => implode(',', $SCOPES)
);

// Setup OAuth consumer with our "credentials"
$consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET, NULL);

$request_token = @$_REQUEST['openid_oauth_request_token'];
if ($request_token) {
  $data = array();
  $access_token = getAccessToken($request_token);

  // Query OpenSocial People API ======================================
  $req = OAuthRequest::from_consumer_and_token($consumer, $access_token, 'GET',
                                               $peopleUri, NULL);
  $req->sign_request($SIG_METHOD, $consumer, $access_token);

  // OpenSocial People API isn't GData, but we can use send_signed_request() from
  // common.inc.php to make an authenticated request.
  $data['people_api'] = send_signed_request($req->get_normalized_http_method(),
                                        $peopleUri, $req->to_header(), NULL, false);
  // ===========================================================================
}

switch(@$_REQUEST['openid_mode']) {
  case 'checkid_setup':
  case 'checkid_immediate':
    $identifier = $_REQUEST['openid_identifier'];
    if ($identifier) {
      $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
      list($normalized_identifier, $endpoints) =
          Auth_OpenID_discover($identifier, $fetcher);

      if (!$endpoints) {
        debug('No OpenID endpoint found.');
      }

      $uri = '';
      foreach ($openid_params as $key => $param) {
        $uri .= $key . '=' . urlencode($param) . '&';
      }
      $header_content = 'Location: ' . $endpoints[0]->server_url . '?' . rtrim($uri, '&');
      header($header_content);
    } else {
      debug('No OpenID endpoint found.');
    }
    break;
  case 'cancel':
    debug('Sign-in was cancelled.');
    break;
  case 'associate':
    // TODO
    break;
}

/**
 * Upgrades an OAuth request token to an access token.
 *
 * @param string $request_token_str An authorized OAuth request token
 * @return string The access token
 */
function getAccessToken($request_token_str) {
  global $consumer, $SIG_METHOD, $OAUTH_ENDPOINT;

  $token = new OAuthToken($request_token_str, NULL);

  $token_endpoint = "{$OAUTH_ENDPOINT}/oauth/access_token";
  $request = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET',
                                                   $token_endpoint);
  $request->sign_request($SIG_METHOD, $consumer, $token);

  $response = send_signed_request($request->get_normalized_http_method(),
                                  $token_endpoint, $request->to_header(), NULL,
                                  false);

  // Parse out oauth_token (access token) and oauth_token_secret
  $matches = array();
  @parse_str($response, $matches);
  $access_token = new OAuthToken(urldecode($matches['oauth_token']), urldecode($matches['oauth_token_secret']));

  return $access_token;
}

?>

<html>
<head>
<title>OpenSocial Hybrid Protocol Demo (OpenID + OAuth)</title>
<link href="hybrid.css" type="text/css" rel="stylesheet"/>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script type="text/javascript">
function toggle(id, type) {
  if (type === 'list') {
    $('pre.' + id).hide();
    $('div.' + id).show();
  } else {
    $('div.' + id).hide();
    $('pre.' + id).show();
  }
}
</script>
</head>
<body>

<h3>OpenSocial Hybrid Protocol (<a href="http://openid.net">OpenID</a> + <a href="http://oauth.net">OAuth</a>) Demo</h3>

<div style="float:left;"><img src="hybrid_logo.png"/></div>
<div>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<fieldset><legend><small><b>Enter an OpenID:</b></small></legend>
  <input type="hidden" name="openid_mode" value="checkid_setup">
  <input type="text" name="openid_identifier" id="openid_identifier" size="40" value="http://partuza/profile/1" /> <input type="submit" value="login" />
</fieldset>
</form>
</div>

<?php if(@$_REQUEST['openid_mode'] === 'id_res'): ?>
  <p>Welcome!</p>
<?php endif; ?>

<div style="margin-left:140px;">
<?php if ($request_token && $access_token): ?>
  Access token: <?php echo $access_token->key; ?><br>
<?php else: ?>
  <h4 style="margin-top:5.5em;">You are not authenticated</h4>
<?php endif; ?>

<?php if (@$data['people_api']): ?>
  <h4>Your OpenSocial Portable Contacts Data:</h4>
  <pre class="data_area"><?php echo json_pp($data['people_api']); ?></pre>
<?php endif; ?>
</div>
</body>
</html>
