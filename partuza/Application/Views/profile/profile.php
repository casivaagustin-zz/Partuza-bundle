<?php
  $this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">
<?php
  $this->template('profile/profile_info.php', $vars);
?>
</div>
<div id="profileContent">
<div class="gadgets-gadget-chrome">
<?php
if (! empty($_SESSION['message'])) {
  echo "
     <div class=\"ui-state-highlight ui-corner-all\" style=\"padding: 0 .7em;\">
       <p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>
	   <strong>{$_SESSION['message']}</strong></p>
     </div><br />\n";
  unset($_SESSION['message']);
}

?>
<!--  <div class="gadgets-gadget-title-button-bar"><a href="<?php echo PartuzaConfig::get("web_prefix")?>/profile/activities/<?php echo $vars['person']['id']?>" title="View all.."><span class="ui-icon ui-icon ui-icon-carat-1-e"></span></div>  -->
<div class="gadgets-gadget-title-bar"><span class="gadgets-gadget-title"><?php echo $vars['person']['first_name']?>'s activities</span></div>
<?php
  $this->template('profile/profile_activities.php', $vars);
?>
</div>
<?php
  $this->template('profile/profile_content.php', $vars);
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
