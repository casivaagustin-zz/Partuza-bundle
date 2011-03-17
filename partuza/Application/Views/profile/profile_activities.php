<?php
if (! count($vars['activities'])) {
  echo "No activities yet.";
} else {
  $first = true;
  foreach ($vars['activities'] as $activity) {
    $add = $first ? ' first' : '';
    $first = false;
    echo "<div class=\"activity$add\">\n";
    echo "<a href=\"/profile/{$activity['person_id']}\">{$activity['person_name']}</a> ";
    echo $activity['title'] . "<br />\n";
    if (count($activity['media_items'])) {
      echo "<div style=\"clear:both\">";
      foreach ($activity['media_items'] as $mediaItem) {
        if ($mediaItem['type'] == 'IMAGE') {
          echo "<div class=\" ui-corner-all\" style=\"float:left\"><img src=\"" . $mediaItem['url'] . "\" width=\"50\"></img></div>";
        }
      }
      echo "</div>";
    }
    echo "{$activity['body']}\n";
    echo "</div>";
    echo "<div style=\"clear:both\"></div>\n";
  }
}
