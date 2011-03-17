<?php
if (count($messages)) {
  foreach ($messages as $message) {
    $created = strftime('%B %e, %Y at %H:%M', $message['created']);
    $userId = $type == 'inbox' ? $message['from'] : $message['to'];
    if (empty($message['thumbnail'])) {
      $thumb = PartuzaConfig::get('site_root') . '/images/people/nophoto.gif';
    } else {
      $thumb = PartuzaConfig::get('site_root'). $message['thumbnail'];
    }
    $thumb = Image::by_size($thumb, 50, 50);
    $title = substr($message['title'], 0, 20);
    $message['title'] = strip_tags($message['title']);
    $preview = substr(strip_tags($message['body']), 0, 80).'..';
    //TODO add script to the onLoad function to hook up the delete buttons, and also add the link to view message to each subject and body
    $readStyle = isset($message['read']) && $message['read'] == 'no' ? ' style="font-weight:bold"' : '';
    echo "<div class=\"message\" id=\"message{$message['id']}\">".
         "<div style=\"float:right; margin: 6px;\" class=\"ui-state-default ui-corner-all\"><a href=\"javascript: void(0);\" id=\"removeButton{$message['id']}\"><span id=\"removeIcon{$message['id']}\" class=\"ui-icon ui-icon-closethick\"></span></a></div>".
         "<div class=\"who\"><div class=\"thumb\" style=\"float:left; margin-right: 6px; width:50px; height:50px; background-image: url('$thumb') ; background-repeat: no-repeat; background-position: center center;\"></div>{$message['name']}</a><br /><span>$created</span></div>".
         "<div class=\"preview\"$readStyle>$title<br />$preview</div>".
         "</div>".
    	 "<div id=\"dialog{$message['id']}\" title=\"Delete message?\" style=\"display:none\">".
		 "<p><span id=\"dialogSpan{$message['id']}\"class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>Are you sure you want to delete this message?</p>".
		 "</div>";
  }
} else {
  echo "No messages..";
}
