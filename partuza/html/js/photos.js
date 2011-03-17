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
 * file summary
 */

/**
 * Shows the loader photo in the selected tab's content
 */
function showLoader(_photoType) {
  $('#'+_photoType).html('<div class="loader"><img src="/images/loader.gif" alt="Loading.." /><br /><br />Loading..</div>');
}

/**
 * Shows the photos for the selected tab
 */
function selectPhotosTab(index, personId, start, count) {

  // 0 = my album, 1 = friend album, 2 = not implemented
  var photoType = '', _photoType = '', tmpQueryString = '', tmpUrl = '';
  if (index == 0) photoType = 'self';
  else if (index == 1) photoType = 'friend';
  _photoType = photoType + '_' + personId;

  // set loading message
  showLoader(_photoType);
  
  // if start is int or count is int.
  start = parseInt(start);
  count = parseInt(count);
  if (!isNaN(start)) tmpQueryString += '&s='+start;
  if (!isNaN(count)) tmpQueryString += '&c='+count;
  tmpQueryString = (tmpQueryString == '') ? '' : '?' + tmpQueryString.substr(1);
  tmpUrl = '/profile/photos_list/' + personId + '/' + photoType + tmpQueryString;
  // and load the tab's content (we reload every time so new messages pop up right away, it's the easy way out ok!:)
  jQuery.get( '/profile/photos_list/' + personId + '/' + photoType + tmpQueryString, null,
    function(data, textStatus) {
      // assign the recieved html to the tab's content div
      $('#'+photoType+'_'+personId).html(""+data);
      //$('#'+photoType+'_'+personId).html("<p>asdfadf</p>");
    });
}

function saveAlbumDialog(ownerId, itemId, dialogId, height){
  $("#" + dialogId).dialog({
    bgiframe: true,
    resizable: false,
    height:height,
    modal: true,
    overlay: {
      backgroundColor: '#000',
      opacity: 0.5
    },
    dialogClass: 'deletedialog',
    buttons: {
      'Save': function() {
      	saveAlbum(ownerId, itemId, 'title|title_'+itemId, 'description|description_'+itemId+'|checkbox');
        $(this).dialog('destroy');
      },
      'Cancel': function() {
        $(this).dialog('destroy');
      }
    }
  });
}
function saveMediaDialog(ownerId, itemId, dialogId){
  $("#" + dialogId).dialog({
    bgiframe: true,
    resizable: false,
    height:140,
    modal: true,
    overlay: {
      backgroundColor: '#000',
      opacity: 0.5
    },
    dialogClass: 'deletedialog',
    buttons: {
      'Save': function() {
      	saveMedia(ownerId, itemId, 'title|title_'+itemId, 'thumbnail_url|thumbnail_url_'+itemId+'|checkbox');
        $(this).dialog('destroy');
      },
      'Cancel': function() {
        $(this).dialog('destroy');
      }
    }
  });
}

function deleteType(typeId, ownerId, itemId, elementId){
  $("#dialog_" + itemId).dialog({
    bgiframe: true,
    resizable: false,
    height:140,
    modal: true,
    overlay: {
      backgroundColor: '#000',
      opacity: 0.5
    },
    dialogClass: 'deletedialog',
    buttons: {
      'Remove': function() {
        $(this).dialog('destroy');
        jQuery.post('/profile/' + typeId + '/' + ownerId + '/' + itemId, null,  function(data, textStatus) {
          if (data == 'success') {
          	document.location = document.location.href;
            //$('#'+elementId).hide();
          } else {
            alert('error');
          }
        });
      },
      'Cancel': function() {
        $(this).dialog('destroy');
      }
    }
  });
}

function deleteMedia(typeId, ownerId, albumId, mediaId) {
  $("#dialog_" + mediaId).dialog({
    bgiframe: true,
    resizable: false,
    height:140,
    modal: true,
    overlay: {
      backgroundColor: '#000',
      opacity: 0.5
    },
    dialogClass: 'deletedialog',
    buttons: {
      'Remove': function() {
        $(this).dialog('destroy');
        jQuery.post('/profile/' + typeId + '/' + ownerId + '/' + mediaId, null, function(data, textStatus) {
          if (data == 'success') {
            document.location = '/profile/photos_view/' + ownerId + '/' + albumId;
          } else {
            alert('error');
          }
        });
      },
      'Cancel': function() {
        $(this).dialog('destroy');
      }
    }
  }); 
}

function uploadMediaDialog() {
  $("#uploadMediaDialog").dialog({
    bgiframe: true,
    resizable: false,
    height: 225,
    modal: true,
    overlay: {
      backgroundColor: '#000',
      opacity: 0.5
    },
    dialogClass: 'deletedialog',
    buttons: {
      'Upload': function() {
        return photoUploadStart();
      },
      'Cancel': function() {
        $(this).dialog('destroy');
      }
    }
  }); 
}

function saveAlbum() {
  if (arguments[0] == null) {
    alert('error');
  }
  var tmpUrl = (arguments[1] == null) ? '/profile/photos_save/' + arguments[0] : '/profile/photos_save/' + arguments[0] + '/' + arguments[1];
  var postData = {};
  for (var i=2; i<arguments.length;i++) {
    var args = arguments[i].split('|');
    postData[args[0]] = $('#'+args[1]).val();
  }
  
  var tmpArguments = arguments;
  jQuery.post(tmpUrl, postData, function(data, textStatus) {
    if (tmpArguments[1] == null) {
      if (parseInt(data, 10) > 0) { 
        document.location = '/profile/photos_view/' + tmpArguments[0] + '/' + data;
      } else {
        alert('error');
      }
    } else {
      if (parseInt(data, 10) > 0) {
        for (var i=2; i<tmpArguments.length; i++) {
          var args = tmpArguments[i].split('|');
          $('#static'+firstUpperCase(args[1])).html($('#'+args[1]).val());
        }
        hideDiv('updateAlbumDiv_'+tmpArguments[1]);
      } else {
        alert('error');
      }
    }
  });
}

function saveMedia() {
  if (arguments[0] == null) {
    alert('error');
  }
  var tmpUrl = '/profile/photo_save/' + arguments[0] + '/' + arguments[1];
  var postData = {};
  for (var i=2; i<arguments.length;i++) {
    var args = arguments[i].split('|');
    if (args[2] == 'checkbox') {
      if ($('#'+args[1]).attr("checked")==true) {
        postData[args[0]] = 'thumbnail|set';
      }
    } else {
      postData[args[0]] = $('#'+args[1]).val();
    }
  }
  var tmpArguments = arguments;
  jQuery.post(tmpUrl, postData, function(data, textStatus) {
    if (parseInt(data, 10) > 0) {
      for (var i=2; i<tmpArguments.length; i++) {
        var args = tmpArguments[i].split('|');
        try {
          $('#static'+firstUpperCase(args[1])).html($('#'+args[1]).val());
        } catch(e) {}
      }
      hideDiv('editMedia_'+tmpArguments[1]);
    } else {
      alert('error');
    }
  });
}

function showDiv(elementId) {
  $("#"+elementId).show();
}

function hideDiv(elementId) {
  $("#"+elementId).hide();
}

function firstUpperCase(str) {
  return str.substr(0,1).toUpperCase() + str.substr(1).toLowerCase();
}

function photoUploadStart() {
  if ($('#upload_form_1_file').val() == "") {
    alert("You need to select a file before clicking Upload");
    return false;
  }
  $('#uploadProgress').css('display', 'block');
  $('#upload_form_1').css('display', 'none');
  $('#upload_form_1').attr('target', 'uploadFileIframe');
  $('#upload_form_1').submit();
}

function photoUploadEnd() {
  $('#uploadProgress').css('display', 'none');
}

function photoUploadSuccessCall() {
  $('#uploadProgress').css('display', 'none');
  $('#uploadFinished').css('display', 'block');
  $('#upload_form_1_span').html('<font color="green">files upload success.</font>');
  $('#uploadMediaDialog').dialog('destroy');
  document.location = document.location;
}

function photoUploadFailedCall() {
  $('#upload_form_1').css('display', 'block');
  $('#uploadProgress').css('display', 'none');
  $('#uploadFinished').css('display', 'none');
  $('#upload_form_1_span').html('<font color="red">files upload failed!</font>');
}
