<div id="profileRight">
<div class="gadgets-gadget-chrome">
<div class="gadgets-gadget-title-bar">
<div class="gadgets-gadget-title-button-bar"><a href="<?php echo PartuzaConfig::get("web_prefix")?>/profile/friends/<?php echo $vars['person']['id']?>" title="View all.."><span class="ui-icon ui-icon ui-icon-carat-1-e" ></span></div>
<span class="gadgets-gadget-title"><?php echo $vars['person']['first_name']?>'s friends (<?php echo count($vars['friends'])?>)</span>
</div>
<div style="margin-left: 6px">
<?php
$cnt = 0;
foreach ($vars['friends'] as $friend) {
  $thumb = PartuzaConfig::get('site_root') . '/images/people/' . $friend['id'] . '.jpg';
  if (! file_exists($thumb)) {
    $thumb = PartuzaConfig::get('site_root') . '/images/people/nophoto.gif';
  }
  $thumb = Image::by_size($thumb, 50, 50);
  echo "<div class=\"friend\">
					<div class=\"thumb\">
							<a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/{$friend['id']}\" rel=\"friend\" >
								<img src=\"$thumb\" alt=\"{$friend['first_name']} {$friend['last_name']}\" title=\"{$friend['first_name']} {$friend['last_name']}\" />
							</a>
					</div>
					<p class=\"uname\">
						<a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/{$friend['id']}\" rel=\"friend\">{$friend['first_name']} {$friend['last_name']}</a>
					</p>
			</div>";
  $cnt ++;
  if ($cnt == 8) {
    break;
  }
}
?>
</div>
	</div>
<div style="clear: both"></div>
<br />
<div class="gadgets-gadget-chrome">
<div class="gadgets-gadget-title-bar">
<?php
  if ($vars['is_owner']) {
    echo "<div class=\"gadgets-gadget-title-button-bar\"><a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/edit\" title=\"Edit your profile\"><span class=\"ui-icon ui-icon-pencil\"></span></a></div>";
  }
?>

<span class="gadgets-gadget-title">Information</span></div>
<div style="margin: 6px">
<div class="form_entry">
<div class="info_detail"><?php echo $vars['person']['first_name'] . " " . $vars['person']['last_name']?></div> name</div>
<?php
  if (! empty($vars['person']['gender'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['gender'] == 'MALE' ? 'Male' : 'Female'?></div>
gender</div> <?php
  }

  if (! empty($vars['person']['date_of_birth'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo strftime('%B %e, %Y', $vars['person']['date_of_birth'])?></div>
birthday</div> <?php
  }

  if (! empty($vars['person']['relationship_status'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['relationship_status']?></div>
relationship</div> <?php
  }

  if (! empty($vars['person']['looking_for'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['looking_for']?></div>
looking for</div> <?php
  }

  if (! empty($vars['person']['political_views'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['political_views']?></div>
political views</div> <?php
  }

  if (! empty($vars['person']['religion'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['religion']?></div>
religion</div> <?php
  }

  if (! empty($vars['person']['children'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['children']?></div>
children</div> <?php
  }

  if (! empty($vars['person']['drinker'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo ucwords(strtolower($vars['person']['drinker']))?></div>
drinker</div> <?php
  }

  if (! empty($vars['person']['smoker'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo ucwords(strtolower($vars['person']['smoker']))?></div>
smoker</div> <?php
  }

  if (! empty($vars['person']['ethnicity'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo ucwords($vars['person']['ethnicity'])?></div>
ethnicity</div> <?php
  }

  if (! empty($vars['person']['about_me'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['about_me']?></div>
about me</div> <?php
  }

  if (! empty($vars['person']['fashion'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['fashion']?></div>
fashion</div> <?php
  }

  if (! empty($vars['person']['happiest_when'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['happiest_when']?></div>
happiest when</div> <?php
  }

  if (! empty($vars['person']['humor'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['humor']?></div>
humor</div> <?php
  }

  if (! empty($vars['person']['job_interests'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['job_interests']?></div>
job interests</div> <?php
  }

  if (! empty($vars['person']['pets'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['pets']?></div>
pets</div> <?php
  }

  if (! empty($vars['person']['scared_of'])) {
    ?><div class="form_entry">
<div class="info_detail"><?php
    echo $vars['person']['scared_of']?></div>
scared of</div> <?php
  }
  ?>
		</div>

</div>
</div>