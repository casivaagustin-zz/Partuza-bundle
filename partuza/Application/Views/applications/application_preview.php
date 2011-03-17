<?php
$this->template('/common/header.php');
?>

<div id="profileInfo" class="blue">
<?php
$this->template('profile/profile_info.php', $vars);
?>
</div>

<div id="profileContent">
<?php
$gadget = $vars['application'];
$gadget['user_prefs'] = array();
$gadget['mod_id'] = 0;
$width = 488;
$view = 'preview';
$this->template('/gadget/gadget.php', array('width' => $width, 'gadget' => $gadget, 'person' => $vars['person'], 
    'view' => $view));

?>
</div>
<div id="profileRight" class="gadgets-gadget-chrome">
<div class="gadgets-gadget-title-bar"><span class="gadgets-gadget-title"><?php echo ! empty($gadget['directory_title']) ? $gadget['directory_title'] : (isset($gadget['title']) ? $gadget['title'] : '')?></span></div>
<div>
<?php
echo "	<div class=\"preview_thumbnail\">";
if (! empty($gadget['thumbnail'])) {
  // ugly hack to make it work with iGoogle images
  if (substr($gadget['thumbnail'], 0, strlen('/ig/')) == '/ig/') {
    $gadget['thumbnail'] = 'http://www.google.com' . $gadget['thumbnail'];
  }
  echo "		<img src=\"{$gadget['thumbnail']}\" />";
}
?>
	</div>
<div class="preview_section">
		<?php echo isset($gadget['description']) ? $gadget['description'] : ''?>
	</div>
<?php
if (isset($gadget['url'])) {
  ?>
<div class="preview_section"><br />
<div class="preview_add"><a
	href="<?php echo PartuzaConfig::get('web_prefix');?>/profile/addapp?appUrl=<?php echo urlencode($gadget['url'])?>">Add
to my profile</a></div>
<br />
<small>Note: By installing this application you will be allowing it to
access your profile data and friends list.</small> <br />
<br />
<?php
}
?>
</div>
<div class="preview_section">
<?php
if (! empty($gadget['author'])) {
  echo "By {$gadget['author']}<br />";
}
if (! empty($gadget['author_email'])) {
  echo "<a href=\"mailto: {$gadget['author_email']}\">{$gadget['author_email']}</a>";
}
?>
	</div>
</div>
<div style="clear: both"></div>

<?php
$this->template('/common/footer.php');
?>