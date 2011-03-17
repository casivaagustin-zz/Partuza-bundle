<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue"><?php
$this->template('profile/profile_info.php', $vars);
$date_of_birth_month = date('n', $vars['person']['date_of_birth']);
$date_of_birth_day = date('j', $vars['person']['date_of_birth']);
$date_of_birth_year = date('Y', $vars['person']['date_of_birth']);
?></div>
<div id="profileContentWide">
<div id="editTabs">
  <ul>
  	<li><a href="#basic">Basic</a></li>
  	<li><a href="#contact">Contact</a></li>
  	<li><a href="#relationship">Relationship</a></li>
  	<li><a href="#personal">Personal</a></li>
  	<li><a href="#education">Education</a></li>
  	<li><a href="#work">Work</a></li>
  	<li><a href="#oauth">OAuth</a></li>
  	<li><a href="#picture">Picture</a></li>
  </ul>
  <form method="post" enctype="multipart/form-data">

  <div id="basic">
    <div class="form_entry">
    <div class="form_label"><label for="first_name">first name</label></div>
    <input type="text" name="first_name" id="first_name"
    	value="<?php echo isset($vars['person']['first_name']) ? $vars['person']['first_name'] : ''?>" />
    </div>

    <div class="form_entry">
    <div class="form_label"><label for="last_name">last name</label></div>
    <input type="text" name="last_name" id="last_name"
    	value="<?php echo isset($vars['person']['last_name']) ? $vars['person']['last_name'] : ''?>" />
    </div>

    <div class="form_entry">
    <div class="form_label"><label for="nickname">nickname</label></div>
    <input type="text" name="nickname" id="nickname"
    	value="<?php echo isset($vars['person']['nickname']) ? $vars['person']['nickname'] : ''?>" />
    </div>

    <div class="form_entry">
    <div class="form_label"><label for="gender">gender</label></div>
    <select name="gender" id="gender">
    	<option value="-">-</option>
    	<option value='FEMALE'
    		<?php echo $vars['person']['gender'] == 'FEMALE' ? ' SELECTED' : ''?>>Female</option>
    	<option value='MALE'
    		<?php echo $vars['person']['gender'] == 'MALE' ? ' SELECTED' : ''?>>Male</option>
    </select></div>

    <div class="form_entry">
    <div class="form_label"><label for="date_of_birth_month">date of birth</label></div>
    <select name="date_of_birth_month" id="date_of_birth_month"
    	style="width: auto">
    	<option value="-">-</option>
    					<?php
        for ($month = 1; $month <= 12; $month ++) {
          $sel = $month == $date_of_birth_month && $vars['person']['date_of_birth'] != 0 ? ' SELECTED' : '';
          echo "<option value=\"$month\"$sel>$month</option>\n";
        }
        ?>
    					</select> <select name="date_of_birth_day" id="date_of_birth_day"
    	style="width: auto">
    	<option value="-">-</option>
    					<?php
        for ($day = 1; $day <= 31; $day ++) {
          $sel = $day == $date_of_birth_day && $vars['person']['date_of_birth'] != 0 ? ' SELECTED' : '';
          echo "<option value=\"$day\"$sel>$day</option>\n";
        }
        ?>
    					</select> <select name="date_of_birth_year" id="date_of_birth_year"
    	style="width: auto">
    	<option value="-">-</option>
    					<?php
        for ($year = 1940; $year <= 2008; $year ++) {
          $sel = $year == $date_of_birth_year && $vars['person']['date_of_birth'] != 0 ? ' SELECTED' : '';
          echo "<option value=\"$year\"$sel>$year</option>\n";
        }
        ?>
    					</select></div>

    <div class="form_entry">
    <div class="form_label"><label for="political_views">political views</label></div>
    <input type="text" name="political_views" id="political_views"
    	value="<?php echo isset($vars['person']['political_views']) ? $vars['person']['political_views'] : ''?>" />
    </div>

    <div class="form_entry">
    <div class="form_label"><label for="religion">religion</label></div>
    <input type="text" name="religion" id="religion"
    	value="<?php echo isset($vars['person']['religion']) ? $vars['person']['religion'] : ''?>" />
    </div>

    <div class="form_entry">
    <div class="form_label"><label for="children">children</label></div>
    <select name="children" id="children">
    	<option value="-">-</option>
    	<option value="none"
    		<?php echo $vars['person']['children'] == 'none' ? ' SELECTED' : ''?>>none</option>
    					<?php
        for ($children = 1; $children <= 4; $children ++) {
          $sel = $vars['person']['children'] == $children ? ' SELECTED' : '';
          echo "<option value=\"$children\"$sel>$children</option>\n";
        }
        ?>
    					<option value="more then 4">more then 4</option>
    </select></div>

    <div class="form_entry">
    <div class="form_label"><label for="drinker">drinker</label></div>
    <select name="drinker" id="drinker">
    	<option value="-">-</option>
    	<option value='HEAVILY'
    		<?php echo $vars['person']['drinker'] == 'HEAVILY' ? ' SELECTED' : ''?>>Heavily</option>
    	<option value='NO'
    		<?php echo $vars['person']['drinker'] == 'NO' ? ' SELECTED' : ''?>>No</option>
    	<option value='OCCASIONALLY'
    		<?php echo $vars['person']['drinker'] == 'OCCASIONALLY' ? ' SELECTED' : ''?>>Occasionally</option>
    	<option value='QUIT'
    		<?php echo $vars['person']['drinker'] == 'QUIT' ? ' SELECTED' : ''?>>Quit</option>
    	<option value='QUITTING'
    		<?php echo $vars['person']['drinker'] == 'QUITTING' ? ' SELECTED' : ''?>>Quitting</option>
    	<option value='REGULARLY'
    		<?php echo $vars['person']['drinker'] == 'REGULARLY' ? ' SELECTED' : ''?>>Regularly</option>
    	<option value='SOCIALLY'
    		<?php echo $vars['person']['drinker'] == 'SOCIALLY' ? ' SELECTED' : ''?>>Socially</option>
    	<option value='YES'
    		<?php echo $vars['person']['drinker'] == 'YES' ? ' SELECTED' : ''?>>Yes</option>
    </select></div>

    <div class="form_entry">
    <div class="form_label"><label for="smoker">smoker</label></div>
    <select name="smoker" id="smoker">
    	<option value="-">-</option>
    	<option value='HEAVILY'
    		<?php echo $vars['person']['smoker'] == 'HEAVILY' ? ' SELECTED' : ''?>>Heavily</option>
    	<option value='NO'
    		<?php echo $vars['person']['smoker'] == 'NO' ? ' SELECTED' : ''?>>No</option>
    	<option value='OCCASIONALLY'
    		<?php echo $vars['person']['smoker'] == 'OCCASIONALLY' ? ' SELECTED' : ''?>>Ocasionally</option>
    	<option value='QUIT'
    		<?php echo $vars['person']['smoker'] == 'QUIT' ? ' SELECTED' : ''?>>Quit</option>
    	<option value='QUITTING'
    		<?php echo $vars['person']['smoker'] == 'QUITTING' ? ' SELECTED' : ''?>>Quitting</option>
    	<option value='REGULARLY'
    		<?php echo $vars['person']['smoker'] == 'REGULARLY' ? ' SELECTED' : ''?>>Regularly</option>
    	<option value='SOCIALLY'
    		<?php echo $vars['person']['smoker'] == 'SOCIALLY' ? ' SELECTED' : ''?>>Socially</option>
    	<option value='YES'
    		<?php echo $vars['person']['smoker'] == 'YES' ? ' SELECTED' : ''?>>Yes</option>
    </select></div>

    <div class="form_entry">
    <div class="form_label"><label for="ethnicity">ethnicity</label></div>
    <select id="ethnicity" name="ethnicity">
    	<option value="-">-</option>
    	<option value="african american (black)"
    		<?php echo $vars['person']['ethnicity'] == 'african american (black)' ? ' SELECTED' : ''?>>african
    	american (black)</option>
    	<option value="asian"
    		<?php echo $vars['person']['ethnicity'] == 'asian' ? ' SELECTED' : ''?>>asian</option>
    	<option value="caucasian (white)"
    		<?php echo $vars['person']['ethnicity'] == 'caucasian (white)' ? ' SELECTED' : ''?>>caucasian
    	(white)</option>
    	<option value="east indian"
    		<?php echo $vars['person']['ethnicity'] == 'east indian' ? ' SELECTED' : ''?>>east
    	indian</option>
    	<option value="hispanic/latino"
    		<?php echo $vars['person']['ethnicity'] == 'hispanic/latino' ? ' SELECTED' : ''?>>hispanic/latino</option>
    	<option value="middle eastern"
    		<?php echo $vars['person']['ethnicity'] == 'middle eastern' ? ' SELECTED' : ''?>>middle
    	eastern</option>
    	<option value="native american"
    		<?php echo $vars['person']['ethnicity'] == 'native american' ? ' SELECTED' : ''?>>native
    	american</option>
    	<option value="pacific islander"
    		<?php echo $vars['person']['ethnicity'] == 'pacific islander' ? ' SELECTED' : ''?>>pacific
    	islander</option>
    	<option value="multi-ethnic"
    		<?php echo $vars['person']['ethnicity'] == 'multi-ethnic' ? ' SELECTED' : ''?>>multi-ethnic</option>
    	<option value="other"
    		<?php echo $vars['person']['ethnicity'] == 'other' ? ' SELECTED' : ''?>>other</option>
    </select></div>
  </div>

  <div id="contact">emails<br />
    addresses<br />
    <br />
  </div>

  <div id="relationship">
    <div class="form_entry">
    <div class="form_label"><label for="relationship_status">relationship
    status</label></div>
    <select name="relationship_status" id="relationship_status">
    	<option value="-">-</option>
    	<option value="Single"
    		<?php echo $vars['person']['relationship_status'] == 'Single' ? ' SELECTED' : ''?>>Single</option>
    	<option value="In a relationship"
    		<?php echo $vars['person']['relationship_status'] == 'In a relationship' ? ' SELECTED' : ''?>>In
    	a relationship</option>
    	<option value="Engaged"
    		<?php echo $vars['person']['relationship_status'] == 'Engaged' ? ' SELECTED' : ''?>>Engaged</option>
    	<option value="Married"
    		<?php echo $vars['person']['relationship_status'] == 'Married' ? ' SELECTED' : ''?>>Married</option>
    	<option value="It's complicated"
    		<?php echo $vars['person']['relationship_status'] == 'It\'s complicated' ? ' SELECTED' : ''?>>It's
    	complicated</option>
    	<option value="In an open relationship"
    		<?php echo $vars['person']['relationship_status'] == 'In an open relationship' ? ' SELECTED' : ''?>>In
    	an open relationship</option>
    </select></div>
    <div class="form_entry">
    <div class="form_label"><label for="looking_for">looking for</label></div>
    <select name="looking_for" id="looking_for">
    	<option value="-">-</option>
    	<option value="Dating"
    		<?php echo $vars['person']['looking_for'] == 'Dating' ? ' SELECTED' : ''?>>Dating</option>
    	<option value="Friends"
    		<?php echo $vars['person']['looking_for'] == 'Friends' ? ' SELECTED' : ''?>>Friends</option>
    	<option value="Relationship"
    		<?php echo $vars['person']['looking_for'] == 'Relationship' ? ' SELECTED' : ''?>>Relationship</option>
    	<option value="Networking"
    		<?php echo $vars['person']['looking_for'] == 'Networking' ? ' SELECTED' : ''?>>Networking</option>
    	<option value="Activity partners"
    		<?php echo $vars['person']['looking_for'] == 'Activity partners' ? ' SELECTED' : ''?>>Activity
    	partners</option>
    </select></div>
    <div class="form_entry">
    <div class="form_label"><label for="living_arrangement">living
    arrangement</label></div>
    <select name="living_arrangement" id="living_arrangement">
    	<option value="-">-</option>
    	<option value="Alone"
    		<?php echo $vars['person']['living_arrangement'] == 'Alone' ? ' SELECTED' : ''?>>Alone</option>
    	<option value="With roommate(s)"
    		<?php echo $vars['person']['living_arrangement'] == 'With roommate(s)' ? ' SELECTED' : ''?>>With
    	roommate(s)</option>
    	<option value="With partner"
    		<?php echo $vars['person']['living_arrangement'] == 'With partner' ? ' SELECTED' : ''?>>With
    	partner</option>
    	<option value="With kid(s)"
    		<?php echo $vars['person']['living_arrangement'] == 'With kid(s)' ? ' SELECTED' : ''?>>With
    	kid(s)</option>
    	<option value="With pet(s)"
    		<?php echo $vars['person']['living_arrangement'] == 'With pet(s)' ? ' SELECTED' : ''?>>With
    	pet(s)</option>
    	<option value="With parent(s)"
    		<?php echo $vars['person']['living_arrangement'] == 'With parent(s)' ? ' SELECTED' : ''?>>With
    	parent(s)</option>
    </select></div>
  </div>

  <div id="personal">
    <div class="form_entry">
    <div class="form_label"><label for="about_me">about me</label></div>
    <textarea name="about_me" id="about_me"><?php echo isset($vars['person']['about_me']) ? $vars['person']['about_me'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="fashion">fashion</label></div>
    <textarea name="fashion" id="fashion"><?php echo isset($vars['person']['fashion']) ? $vars['person']['fashion'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="happiest_when">happiest when</label></div>
    <textarea name="happiest_when" id="happiest_when"><?php echo isset($vars['person']['happiest_when']) ? $vars['person']['happiest_when'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="humor">humor</label></div>
    <textarea name="humor" id="humor"><?php echo isset($vars['person']['humor']) ? $vars['person']['humor'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="job_interests">job interests</label></div>
    <textarea name="job_interests" id="job_interests"><?php echo isset($vars['person']['job_interests']) ? $vars['person']['job_interests'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="pets">pets</label></div>
    <textarea name="pets" id="pets"><?php echo isset($vars['person']['pets']) ? $vars['person']['pets'] : ''?></textarea>
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="scared_of">scared of</label></div>
    <textarea name="scared_of" id="scared_of"><?php echo isset($vars['person']['scared_of']) ? $vars['person']['scared_of'] : ''?></textarea>
    </div>
  </div>

  <div id="education">Schools here<br />
  </div>

  <div id="work">Jobs here<br />
  </div>

  <div id="picture">
    <div>
    <div class="friend" style="margin-right: 12px">
    <div class="thumb">
    <center><img
    	src="<?php echo Image::by_size(PartuzaConfig::get('site_root') . (! empty($vars['person']['thumbnail_url']) ? $vars['person']['thumbnail_url'] : '/images/people/nophoto.gif'), 64, 64)?>" /></center>
    </div>
    <p class="uname">Current profile photo</p>
    </div>
    Select a new photo to upload<br />
    <input type="hidden" name="MAX_FILE_SIZE" value="6000000" /> <input
    	type="file" name="profile_photo" />
    <div style="clear: both"></div>
    </div>
  </div>

  <div id="oauth">
    <div class="form_entry"><br />
    <i>The OAuth consumer key and secret are automatically generated and
    unique for your profile. Normally these would be created for a registered developer account where you register your site and/or purpose but are listed here for developer convenience.<br /><br />
    They can be used to develop a REST + (3 legged) OAuth client, if your not developing
    an Auth consuming mobile application or website, feel free to ignore these values :-)<br /><br />
    If you're developing a gadget that uses the REST and/or RPC interface, you should be using the <a href="http://sites.google.com/site/oauthgoog/2leggedoauth/2opensocialrestapi" style="color:#3366CC">2-legged oauth</a>
    tokens which you can find in the 'edit applications' overview of your profile.<br />
    </i>
    <br />
    </div>
    <div class="form_entry">
    <div class="form_label"><label for="oauth_consumer_key">oauth consumer
    key</label></div>
    					<?php echo $vars['oauth']['consumer_key']?>
    				</div>
    <div class="form_entry">
    <div class="form_label"><label for="oauth_consumer_secret">oauth
    consumer secret</label></div>
    					<?php echo $vars['oauth']['consumer_secret']?>
    				</div>
  </div>
  <br />
  <div style="margin-left:12px;"><input type="submit" class="submit" value="save" /></div>
  </form>
  </div>
</div>
<script>
$(document).ready(function() {
	$('#editTabs').tabs();
});
</script>
<div style="clear: both"></div>
<?php
$this->template('/common/footer.php');
?>