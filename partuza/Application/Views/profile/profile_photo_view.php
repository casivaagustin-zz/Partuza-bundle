<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">
<?php
$this->template('profile/profile_info.php', $vars);

?>
</div>
<div id="profileContentWide">
  <div class="photo_normal listlight">
    <div>
      <h1 class="oh">
        <?php echo $current_media['title']; ?>
      </h1>
    </div>
  </div>
  <div class="photo_normal">
  	<div style="float:left">
      <a href="/profile/photos_view/<?php echo $albums['owner_id'];?>/<?php echo $albums['id'];?>">Return to overview</a> | 
      <a href="<?php echo $current_media['original_url'];?>">View in original size</a>
    </div>
  	<div style="float:left; margin:0px 100px;">
<?php
if ($item_order != 1) {
  echo '<a href="/profile/photo_view/'.$medias[0]['owner_id'].'/'.$medias[0]['album_id'].'/'.$medias[0]['id'].'" ><img id="nav_left" style="cursor: pointer;" src="/images/b_left.gif" /></a>';
} else {
  echo '<img id="nav_left" style="cursor: default;" src="/images/b_left_disable.gif" /></a>';
}
if ($item_order != $item_count) {
  $next_order = ($item_order == 1) ? 1 : 2;
  echo '<a href="/profile/photo_view/'.$medias[$next_order]['owner_id'].'/'.$medias[$next_order]['album_id'].'/'.$medias[$next_order]['id'].'" ><img id="nav_left" style="cursor: pointer;" src="/images/b_right.gif" /></a>';
} else {
  echo '<img id="nav_right" style="cursor: default;" src="/images/b_right_disable.gif" />';
}
?>
    </div>
    <div style="float:right">
<?php
if ($item_count > 1) {
  echo "Viewing picture $item_order of $item_count";
}
?>
    </div>
    <div class="listdivi"></div>
  </div>
  <div class="photo_normal align_center">
   	<div style="position: relative; text-align:center; padding:20px;">
      <img style="cursor: default;" id="media_<?php echo $current_media['id'];?>" alt="<?php echo $current_media['title'];?>" src="<?php echo $current_media['url'];?>" />
    </div>
    <div class="photo_normal align_center">
      <span id="staticTitle_<?php echo $current_media['id']?>"><?php echo $current_media['title'];?></span><br/>
      <span id="staticDescription_<?php echo $current_media['id']?>"><?php echo $current_media['description'];?></span>
    </div>
<?php
if ($is_owner) {
?>
    <div class="photo_normal align_center">
      <nobr><span class="button"><a href="javascript:void(0);" onclick="saveMediaDialog(<?php echo $current_media['owner_id']?>, <?php echo $current_media['id'];?>, 'editMedia_<?php echo $current_media['id'];?>');">edit</a></span>&nbsp;&nbsp;</nobr>
      <nobr><span class="button"><a href="javascript:void(0);" onclick="deleteMedia('media_delete', <?php echo $current_media['owner_id'];?>, <?php echo $current_media['album_id']?>, <?php echo $current_media['id']?>)">delete</a></span></nobr>
    </div>
    <div class="photo_normal">
    	<div id="editMedia_<?php echo $current_media['id'];?>" title="Edit Media" style="display:none" />
        <div class="form_entry">
          <div class="form_label_photo"><label for="title_<?php echo $current_media['id'];?>">title</label></div>
          <input type="text" name="title_<?php echo $current_media['id'];?>" id="title_<?php echo $current_media['id'];?>" value="<?php echo $current_media['title'];?>" style="width:140px" />
        </div>
        <div class="form_entry">
          <div class="form_label_photo"><label for="thumbnail_url_<?php echo $current_media['id'];?>">set thumbnail</label></div>
          <input type="checkbox" name="thumbnail_url_<?php echo $current_media['id'];?>" id="thumbnail_url_<?php echo $current_media['id'];?>" value="true" style="width:10px" />
        </div>
      </div>
      <div id="dialog_<?php echo $current_media['id'];?>" title="Delete media" style="display:none">
        <p><span id="dialogSpan_<?php echo $current_media['id']?>" class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete this item?</p>
      </div>
    </div>
<?php
}
?>
  </div>
</div>
<div style="clear: both"></div>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/photos.js"></script>
<?php
$this->template('/common/footer.php');
?>
