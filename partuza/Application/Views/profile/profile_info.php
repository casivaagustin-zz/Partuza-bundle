<div style="text-align: center">
<?php
$thumb = PartuzaConfig::get('site_root') . '/images/people/' . $vars['person']['id'] . '.jpg';
if (! file_exists($thumb)) {
  $thumb = PartuzaConfig::get('site_root') . '/images/people/nophoto.gif';
}
$thumb = Image::by_size($thumb, 129, 225);
?><a href="<?php echo PartuzaConfig::get('web_prefix')?>/profile/<?php echo $vars['person']['id']?>" rel="me"><img src="<?php echo $thumb?>" /></a><br />
</div>
<div class="header" style="padding-left:12px">
<?php
echo $vars['person']['first_name'] . " " . $vars['person']['last_name'];
?>
<?php
  if ($vars['is_owner']) {
    echo "<div class=\"gadgets-gadget-title-button-bar\" style=\"margin-right:10px; margin-top: -2px;\"><a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/edit\" title=\"Edit your profile\"><span class=\"ui-icon ui-icon-pencil\"></span></a></div>";
  }
?>
</div>
<ul class="profileMenu" style="clear:both">
<?php
if ($vars['is_owner']) {
  echo "<li><a href=\"" . PartuzaConfig::get("web_prefix") . "/profile/messages\">Messages</a></li>\n";
} elseif (! isset($vars['is_friend']) || ! $vars['is_friend']) {
?><li><a href="<?php echo PartuzaConfig::get('web_prefix');?>/home/addfriend/<?php echo $vars['person']['id']?>">Add <?php echo $vars['person']['first_name']?> as friend</a></li>
<?php
}
if (!$vars['is_owner']) {
?>

<!--  TODO: hook this up properly:
<li><a href="<?php echo PartuzaConfig::get("web_prefix")?>/profile/messages/compose?to=<?php echo $vars['person']['id']?>">Send a message</a></li>
-->

<?php
}
?>
<li><a href="<?php echo PartuzaConfig::get("web_prefix")?>/profile/photos/<?php echo $vars['person']['id']?>">Photos</a></li>
<li><a href="<?php echo PartuzaConfig::get("web_prefix")?>/profile/friends/<?php echo $vars['person']['id']?>"><?php echo $vars['is_owner'] ? 'Your' : $vars['person']['first_name'] . "'s"?> friends</a></li>
<?php
if (!$vars['is_owner'] && isset($vars['is_friend']) && $vars['is_friend']) {
?><li><a href="#" id="removeButton">Remove from friends</a></li>
<script>
$('#removeButton').bind('click', function() {
	$("#dialog").dialog({
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
				$(this).dialog('destroy');
				window.location = '<?php echo PartuzaConfig::get('web_prefix');?>/home/removefriend/<?php echo $vars['person']['id']?>';
			},
			'No': function() {
				$(this).dialog('destroy');
			}
		}
	});
});
</script>
<div id="dialog" title="Remove from your friend list?" style="display:none">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to remove <?php echo $vars['person']['first_name'].' '.$vars['person']['last_name']?> from your friend list?</p>
</div>
<?php
}
 ?>
</ul>
<div class="header" style="padding-left:12px">Applications
<?php
if ($vars['is_owner']) {
    echo "<div class=\"gadgets-gadget-title-button-bar\" style=\"margin-right:10px; margin-top: -2px;\"><a href=\"" . PartuzaConfig::get("web_prefix") . "/profile/myapps\" title=\"Edit your applications\"><span class=\"ui-icon ui-icon-pencil\"></span></a></div>";
  }
?>
</div>
<ul class="profileMenu">
<?php
if (isset($vars['applications']) && count($vars['applications'])) {
  foreach ($vars['applications'] as $app) {
    $title = (! empty($app['directory_title']) ? $app['directory_title'] : $app['title']);
    $full_title = $title;
    if (strlen($title) > 21) {
      $full_title = $title;
      $title = substr($title, 0, 19)."..";
    }
    echo "<li><a title=\"$full_title\" href=\"" . PartuzaConfig::get('web_prefix') . "/profile/application/{$vars['person']['id']}/{$app['id']}/{$app['mod_id']}\">" . $title . "</a></li>";
  }
} elseif ($vars['is_owner']) {
  echo "<li><a href=\"" . PartuzaConfig::get("web_prefix") . "/profile/myapps\" title=\"Add applications\">Add applications</a></li>";
}
?></ul>
