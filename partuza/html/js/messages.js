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
 * Code for dealing with partuza's messaging UI. Only to be included from the profile_messages.php template(!)
 */


/**
 * Shows the loader message in the selected tab's content
 */
function showLoader(messageType) {
	$('#'+messageType).html('<div class="loader"><img src="/images/loader.gif" alt="Loading.." /><br /><br />Loading..</div>');
}

/**
 * Shows the selected message
 */
function showMessage(messageType, messageId) {
	showLoader(messageType);
	jQuery.get( '/profile/messages/get', {'messageId' : messageId, 'messageType' : messageType}, function(data, textStatus) {
		// assign the message html to the content area
		$('#' + messageType).html(data);
		// set the hover event for the back button
		$('#' + messageType + ' .button').hover(
				function() { $(this).addClass('ui-state-hover'); },
				function() { $(this).removeClass('ui-state-hover'); }
		);
		// bind the back button to load the tab's content
		$('#' + messageType + ' .button').bind('click', function() {
			selectMessagesTab(0, messageType);
		});
	});
}

/**
 * Shows the messages for the selected tab
 */
function selectMessagesTab(index, type) {
	// 0 = inbox, 1 = sent, 2 = notifications
	var messageType = '';
	if (type != undefined) messageType = type;
	else if (index == 0) messageType = 'inbox';
	else if (index == 1) messageType = 'sent';
	else if (index == 2) messageType = 'notifications';

	// set loading message
	showLoader(messageType);

	// and load the tab's content (we reload every time so new messages pop up right away, it's the easy way out ok!:)
	jQuery.get( '/profile/messages/'+messageType, null,
		function(data, textStatus) {
			// assign the recieved html to the tab's content div
			$('#'+messageType).html(data);

			// add hover to the entire message div
			$('#'+messageType+' .message').hover(
				    function() { $(this).addClass('message-hover'); },
				    function() { $(this).removeClass('message-hover'); }
			);

			// add hover to the icons
			$('#'+messageType+' .message .ui-state-default').hover(
					    function() { $(this).addClass('ui-state-hover'); },
					    function() { $(this).removeClass('ui-state-hover'); }
			);

			// hook up the delete icon to a confirmation dialog, and the removal code to it's 'remove' button event
			$('#'+messageType+' .message .ui-icon-closethick').bind('click', function() {
				// get the message id from the span's id and cast it as a int (* 1)
				var messageId = $(this).attr('id').replace('removeIcon', '') * 1;
				$("#dialog" + messageId).dialog({
					bgiframe: true,
					resizable: false,
					height:140,
					modal: true,
					closeOnEscape: true,
					overlay: {
						backgroundColor: '#000',
						opacity: 0.5
					},
					buttons: {
						'Remove': function() {
							showLoader(messageType);
    						$(this).dialog('destroy');
    						jQuery.get( '/profile/messages/delete/'+messageId, null,	function(data, textStatus) {
    							selectMessagesTab(index, messageType);
    						});
						},
						'No': function() {
							$(this).dialog('destroy');
						}
					}
				});
				return false;
			});
			
			// The click on the persons name, handled in javascript since otherwise we can't stop the event propergation to the parent div click event
			$('#'+messageType+' .message a').bind('click', function() {
				window.location = $(this).attr('href');
				return false;
			});
			
			// Hook up the actual message click (aka: read message)
			$('#'+messageType+' .message').bind('click', function() {
				var messageId = $(this).attr('id').replace('message', '') * 1;
				showMessage(messageType, messageId);
				return false;
			});
		});
}

/**
 * Setup the tabs and load the messaging content on document load
 */
$(document).ready(function() {
	
	// create the tabs and call selectMessagesTab on click
	var $tabs = $('#messageTabs').tabs({
			select: function(e, ui) {
				selectMessagesTab(ui.index);
			}
	});
	
	// populate the current live tab
	var selected = $tabs.data('selected.tabs');
	selectMessagesTab(selected);

	// hook up the compose button
	$('#messageCompose').bind('click', function() {
		jQuery.get( '/profile/messages/compose', null,
			function(data, textStatus) {
				// assign the recieved html to the tab's content div
				var index = $tabs.data('selected.tabs');
				var messageType = '';
					 if (index == 0) messageType = 'inbox';
				else if (index == 1) messageType = 'sent';
				else if (index == 2) messageType = 'notifications';
				$('#'+messageType).html(data);
				$('#compose_send').bind('click', function() {
					var to = $("select#to").val();
					var subject = $("input#subject").val();
					var message = $('textarea#message').val();
					if (to == '' || subject == '' || message == '') {
						alert('Select a recipient and fill in a subject before sending');
					} else {
						showLoader(messageType);
						jQuery.post('/profile/messages/send', {'to' : to, 'subject' : subject, 'message' : message}, function() {
							selectMessagesTab(index);
						});
					}
				});
				$('#compose_cancel').bind('click', function() {
					selectMessagesTab(index);
				});
				$('#compose_send, #compose_cancel').hover(
						function() { $(this).addClass('ui-state-hover'); },
						function() { $(this).removeClass('ui-state-hover'); }
				);
			});
		});
});










