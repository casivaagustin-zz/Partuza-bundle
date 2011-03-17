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
if ($vars['error_message']) {
	echo "<div class=\"ui-state-error\" style=\"margin-bottom:20px;margin-top:10px;margin-right:14px;padding:20px;\">{$vars['error_message']}</div>";
}
?>

<?php
$this->template('profile/profile_friendrequests.php', $vars);
?>
<div class="gadgets-gadget-chrome">
<div class="gadgets-gadget-title-bar"><span class="gadgets-gadget-title">Friend's
activities</span></div>
	<?php
$this->template('profile/profile_activities.php', $vars);
?>
</div>
<?php
if (! empty($_SESSION['message'])) {
  echo "<b>{$_SESSION['message']}</b><br /><br />";
  unset($_SESSION['message']);
}
foreach ($vars['applications'] as $gadget) {
  $width = 488;
  $view = 'home';
  $this->template('/gadget/gadget.php', array('width' => $width, 'gadget' => $gadget,
      'person' => $vars['person'], 'view' => $view));
}
?>
</div>
<div id="profileRight">
<?php
$this->template('profile/profile_friends.php', $vars);
?>
</div>

<div style="clear: both"></div>

<?php
$this->template('/common/footer.php');
?>