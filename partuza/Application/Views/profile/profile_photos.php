<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">
<?php
$this->template('profile/profile_info.php', $vars);

?>
</div>
<div id="profileContentWide" style="position:relative">
  <div class="photo_normal">
    <h2 style="color: #3366CC;">Photos</h2>
<?php
if ($is_owner) {
?>
    <div style="position: absolute; z-index: 1000; right: 9px; top: 6px;">
    <input id="Compose" type="button" onclick="saveAlbumDialog(<?php echo $person['id'];?>, null, 'addAlbumDiv', 250);" class="button" value="Add Album" style="border: 1px solid #3366CC" />
    </div>
<?php
}
?>
  </div>
<?php
if ($is_owner) {
?>
  <div id="addAlbumDiv" title="Add Album" style="margin:10px; padding:2px; zoom:1; display:none">
    <div class="form_entry">
      <div class="form_label"><label for="title_null">title</label></div>
      <input type="text" name="title_null" id="title_null" value="<?php echo isset($album['title']) ? $album['title'] : ''?>" style="width:264px" />
    </div>
    <div class="form_entry">
      <div class="form_label"><label for="description_null">description</label></div>
      <textarea name="description_null" id="description_null"><?php echo isset($album['description']) ? $album['description'] : ''?></textarea>
    </div>
  </div>
<?php
}
?>
  <div id="photoTabs">
    <!--
    <ul>
      <li><a href="#self_<?php echo $person['id']?>">My Album</a></li>
      <li><a href="#friend_<?php echo $person['id']?>">Friend Album</a></li>
    </ul>
    -->
    <div id="self_<?php echo $person['id']?>"></div>
    <!-- <div id="friend_<?php echo $person['id']?>"></div> -->
  </div>
</div>
<div style="clear: both"></div>
<script type="text/javascript">
/**
 * Setup the tabs and load the photo content on document load
 */
$(document).ready(function() {
	/**
  var $tabs = $('#photoTabs').tabs({
    select: function(e, ui){
      var tmpStr = ui.tab.toString();
      tmpStr = tmpStr.substr(tmpStr.lastIndexOf('_')+1);
      selectPhotosTab(ui.index, tmpStr);
    }
  });
  // populate the current live tab
  var selected = $tabs.data('selected.tabs');
  */
  selectPhotosTab(0, <?php echo $person['id']?>);
});
</script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/photos.js"></script>
<?php
$this->template('/common/footer.php');
?>