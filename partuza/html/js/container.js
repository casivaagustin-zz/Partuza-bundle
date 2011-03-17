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
 * This class implements the basic OpenSocia container functionality, see the RPC service hooks in the init function as reference
 */
var Container = Class.extend({
	
	maxHeight: 4096,
	
	init: function() {
		// rpc services our container supports
		gadgets.rpc.register('resize_iframe', this.setHeight);
		gadgets.rpc.register('set_pref', this.setUserPref);
		gadgets.rpc.register('set_title', this.setTitle);
		gadgets.rpc.register('requestNavigateTo', this.requestNavigateTo);
	},
	
	/**
	 * Changes the height of the iframe that contains the gadget
	 */
	setHeight: function(height) {
		var elm = $('#' + this.f);
		if (elm != undefined) {
			// compensate for margin/padding offsets in some browsers (ugly hack but functional)
			height += 28;
			// change the height of the gadget iframe, limit to maxHeight height
			if (height > gadgets.container.maxHeight) {
				height = gadgets.container.maxHeight;
			}
			elm.height(height);
		}
	},
	
	/**
	 * Internal function that retrieves the query params from the iframe (used to retrieve the security token in setUserPref)
	 */
	_parseIframeUrl: function(url) {
		// parse the iframe url to extract the key = value pairs from it
		var ret = new Object();
		var hashParams = url.replace(/#.*$/, '').split('&');
		var param = key = val = '';
		for (i = 0 ; i < hashParams.length; i++) {
			param = hashParams[i];
			key = param.substr(0, param.indexOf('='));
			val = param.substr(param.indexOf('=') + 1);
			ret[key] = val;
		}
		return ret;
	},
	
	/**
	 * Sets a gadget preference, this makes an ajax call to partuza's 'setpref' event using the gadgets security token to identify where it came from (and that it's valid)
	 */
	setUserPref: function(editToken, name, value) {
		var elm = $('#' + this.f);
		// we use the security token to tell our backend who this is (app/mod/viewer)
		// since it's the only fail safe way of doing so
		if (elm != undefined) {
			var params = gadgets.container._parseIframeUrl(elm.attr('src'));
			//TODO use params.st to make the store request, it holds the owner / viewer / app id / mod id required
			var ret = $.ajax({
				type: "GET",
				dataType: 'html',
				cache: false,
				url : '/prefs/set',
				data : { name : name, value : value, st : params.st }
			});
		}
	},
	
	/**
	 * Changes the title that's situated above the gadget's iframe to the desired title
	 */
	setTitle: function(title) {
		var element = $('#' + this.f + '_title');
		if (element != undefined) {
			// update the title, and make sure we don't break it's html
			element.text(title.replace(/&/g, '&amp;').replace(/</g, '&lt;'));
		}
	},
	
	/**
	 * Internal function that returns the correct URL prefix based on the requested view, used by requestNagivateTo
	 */
	_getUrlForView: function(view, person, app, mod) {
		if (view.indexOf('home') == 0) {
			return '/home' + '?view=' + view + '&mod=' + mod;
		} else if (view.indexOf('profile') == 0) {
			return '/profile/' + person + '?view=' + view + '&mod=' + mod;
		} else if (view.indexOf('canvas') == 0) {
			return '/profile/application/' + person + '/' + app + '/' + mod + '?view=' + view + '&mod=' + mod;
		} else {
			return null;
		}
	},
	
	/**
	 * Internal function that returns an object with surface/secondary for view.
	 */
	_getViewFromString: function(view) {
		if (view.indexOf('.') == -1) {
			return { surface : view };
		}
		var viewArray = view.split('.');
		return { surface : viewArray[0], secondary : viewArray[1] };
	},
	
	/**
	 * Called when a gadget does a requestNavigateTo call, contains a view name and optional params that need to be parsed to the new view
	 */
	requestNavigateTo: function(view, opt_params) {
		var elm = $('#' + this.f);
		if (elm != undefined) {
			var params = gadgets.container._parseIframeUrl(elm.attr('src'));
			var fromView = gadgets.container._getViewFromString(params.view);
			var toView = gadgets.container._getViewFromString(view);
			if (toView.surface == fromView.surface) {
				var iframe = $('#remote_iframe_' + params.mid);
				var url = elm.attr('src');
				url = url.replace('view=' + params.view, 'view=' + view);
				if (opt_params) {
					var params_str = encodeURIComponent($.toJSON(opt_params));
					if (url.indexOf('view-params=') == -1) {
						url += '&view-params=' + params_str;
					} else {
						// Replace old view-params with opt_param and keep other params.
						url = url.replace(/([?&])view-params=.*?(&|$)/, '$1view-params=' + params_str + '$2')
					}
				}
				iframe.attr('src', url);
				return;
			}
			var url = gadgets.container._getUrlForView(view, params.owner, params.aid, params.mid);
			if (opt_params) {
				var paramStr = $.toJSON(opt_params);
				if (paramStr.length > 0) {
					url += '&appParams=' + encodeURIComponent(paramStr);
				}
			}
			if (url) {
	 			document.location.href = url;
			}
		}
	}
}); 

/**
 * Create the container class on page load
 */
$(document).ready(function() {
  gadgets.container = new Container();
  
  // Also set mouse-over events for the various icons and profile menu items. This is not a part of anything OpenSocial related, just some UI stuff
  $('div.gadgets-gadget-title-button-bar, .profileMenu li, .button, .submit').hover(
    function() { $(this).addClass('ui-state-hover'); },
    function() { $(this).removeClass('ui-state-hover'); }
  );

  // make the profile li click go to it's child a.href element
  $(".profileMenu li").each(function(element) {
	  $(this).bind('click', function() {
		  window.location = $(this).children()[0].href;
	  });
  });
});
