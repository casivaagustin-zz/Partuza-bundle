<?php
$this->template('/common/header.php');
?>

<div id="profileInfo" class="blue">
	<?php
$this->template('profile/profile_info.php', $vars);
?>
</div>

<div id="profileContentWide">
<p><b>Manage Applications</b></p>
<a href="<?php echo PartuzaConfig::get('web_prefix');?>/profile/appgallery">Browse
the application directory >></a> <br />
<br />

Or add an application by url:<br />
<form method="get"
	action="<?php echo PartuzaConfig::get('web_prefix');?>/profile/addapp"><input
	type="text" name="appUrl" size="35" /> <input class="submit"
	type="submit" value="Add Application" /></form>
<hr>
<b>Your Applications:</b><br />
<br />
		<?php
  if (! count($vars['applications'])) {
    echo "You have not yet added any applications to your profile";
  } else {
    foreach ($vars['applications'] as $app) {
      // This makes it more compatible with iGoogle type gadgets
      // since they didn't have directory titles it seems
      if (empty($app['directory_title']) && ! empty($app['title'])) {
        $app['directory_title'] = $app['title'];
      }
      echo "<div class=\"app\"><div class=\"options\">";
      if (is_object(unserialize($app['settings']))) {
        echo "<a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/appsettings/{$app['id']}/{$app['mod_id']}\">Settings</a><br />";
      }
      echo "<a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/removeapp/{$app['id']}/{$app['mod_id']}\">Remove</a></div>
				<div class=\"app_thumbnail\">";
      if (! empty($app['thumbnail'])) {
        // ugly hack to make it work with iGoogle images
        if (substr($app['thumbnail'], 0, strlen('/ig/')) == '/ig/') {
          $app['thumbnail'] = 'http://www.google.com' . $app['thumbnail'];
        }
        echo "<img src=\"" . PartuzaConfig::get('gadget_server') . "/gadgets/proxy?url=" . urlencode($app['thumbnail']) . "\" />";
      }
      echo "</div><b>{$app['directory_title']}</b><br />{$app['description']}<br />";
      $app['author'] = trim($app['author']);
      if (! empty($app['author_email']) && !empty($app['author'])) {
        $app['author'] = "<a href=\"mailto: {$app['author_email']}\">{$app['author']}</a>";
      }
      if (! empty($app['author'])) {
        //echo "By {$app['author']}";
      }
      echo "<br /><div class=\"oauth\">This gadget's OAuth Consumer Key: <i>{$app['oauth']['consumer_key']}</i> and secret: <i>{$app['oauth']['consumer_secret']}</i></div>";
      echo "</div>";
    }
  }
  ?>
</div>

<div style="clear: both"></div>

<?php
$this->template('/common/footer.php');
?>