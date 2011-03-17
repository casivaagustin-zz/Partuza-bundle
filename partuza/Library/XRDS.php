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

header("Content-Type: application/xrds+xml");

if ($_SERVER['REQUEST_URI'] == '/openidxrds') {
  $openid_xrds = '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS
    xmlns:xrds="xri://$xrds"
    xmlns:openid="http://openid.net/xmlns/1.0"
    xmlns="xri://$xrd*($v*2.0)">
  <XRD>
    <Service priority="0">
      <Type>http://specs.openid.net/auth/2.0/server</Type>
      <Type>http://specs.openid.net/extensions/pape/1.0</Type>
      <Type>http://openid.net/sreg/1.0</Type>
      <Type>http://openid.net/extensions/sreg/1.1</Type>
      <URI>{host}/openid/auth</URI>
    </Service>
  </XRD>
</xrds:XRDS>';
  $openid_xrds = str_replace('{host}', 'http://' . $_SERVER['HTTP_HOST'], $openid_xrds);
  echo $openid_xrds;
} else {
  $xrds = '<?xml version="1.0" encoding="UTF-8"?>
  <XRDS xmlns="xri://$xrds">
      <XRD xml:id="oauth" xmlns:simple="http://xrds-simple.net/core/1.0" xmlns="xri://$XRD*($v*2.0)" version="2.0">
        <Type>xri://$xrds*simple</Type>
        <Expires>' . date('Y-m-d\TH:i:s\Z', time() + 30 * 24 * 60 * 60) . '</Expires>
        <Service priority="10">
          <Type>http://oauth.net/core/1.0/endpoint/request</Type>
          <Type>http://oauth.net/core/1.0/parameters/auth-header</Type>
          <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
          <Type>http://oauth.net/core/1.0/signature/PLAINTEXT</Type>
          <URI>{host}/oauth/request_token</URI>
        </Service>
        <Service priority="10">
          <Type>http://oauth.net/core/1.0/endpoint/authorize</Type>
          <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
          <URI>{host}/oauth/authorize</URI>
        </Service>
        <Service priority="10">
          <Type>http://oauth.net/core/1.0/endpoint/access</Type>
          <Type>http://oauth.net/core/1.0/parameters/auth-header</Type>
          <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
          <Type>http://oauth.net/core/1.0/signature/PLAINTEXT</Type>
          <URI>{host}/oauth/access_token</URI>
        </Service>
        <Service priority="10">
          <Type>http://oauth.net/core/1.0/endpoint/resource</Type>
          <Type>http://oauth.net/core/1.0/parameters/auth-header</Type>
          <Type>http://oauth.net/core/1.0/parameters/uri-query</Type>
          <Type>http://oauth.net/core/1.0/signature/HMAC-SHA1</Type>
        </Service>
        <Service priority="10">
          <Type>http://oauth.net/discovery/1.0/consumer-identity/static</Type>
          <LocalID>0685bd9184jfhq22</LocalID>
        </Service>
      </XRD>
      <XRD xmlns:simple="http://xrds-simple.net/core/1.0" xmlns="xri://$XRD*($v*2.0)" version="2.0">
          <Type>xri://$xrds*simple</Type>
          <Expires>' . date('Y-m-d\TH:i:s\Z', time() + 30 * 24 * 60 * 60) . '</Expires>
          <Service priority="0">
            <Type>http://specs.openid.net/auth/2.0/signon</Type>
            <Type>http://openid.net/signon/1.1</Type>
            <URI>{host}/openid/auth</URI>
          </Service>
          <Service>
            <Type>http://portablecontacts.net/spec/1.0</Type>
            <Type>http://ns.opensocial.org/people/0.8</Type>
            <URI>{url}/social/rest/people</URI>
          </Service>
          <Service>
            <Type>http://ns.opensocial.org/activities/0.8</Type>
            <URI>{url}/social/rest/activities</URI>
          </Service>
          <Service>
            <Type>http://ns.opensocial.org/appdata/0.8</Type>
            <URI>{url}/social/rest/activities</URI>
          </Service>
          <Service>
            <Type>http://ns.opensocial.org/messages/0.8</Type>
            <URI>{url}/social/rest/messages</URI>
          </Service>
          <Service>
            <Type>http://ns.opensocial.org/rpc/0.8</Type>
            <URI>{url}/social/rpc</URI>
          </Service>
          <Service priority="10">
            <Type>http://oauth.net/discovery/1.0</Type>
            <URI>#oauth</URI>
          </Service>
      </XRD>
  </XRDS>';
  // output the XRDS document with the correct URL to our gadget server filled in
  $xrds = str_replace('{url}', PartuzaConfig::get('gadget_server'), $xrds);
  $xrds = str_replace('{host}', 'http://' . $_SERVER['HTTP_HOST'], $xrds);
  echo $xrds;
}
