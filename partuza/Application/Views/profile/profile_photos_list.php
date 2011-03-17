<h2>Albums<span> (<?php echo $albums['found_rows']; ?>) </span></h2>
<div class="photo_normal align_right">
<?php
$start_index = $page['start_index'];
$items_to_show = $page['items_to_show'];
$items_count = $page['items_count'];
if ($items_to_show < $items_count) {
	$page_count = floor(($items_count + $items_to_show - 1) / $items_to_show);
  echo "Showing page " . ($start_index/$items_to_show+1) . " of " . $page_count . " &nbsp;&nbsp;&nbsp;&nbsp;";
  if ($start_index > 0) {
    echo "<a href=\"javascript:void(0)\" onclick=\"selectPhotosTab(0, ".$person_id.", " . ($start_index-$items_to_show) . ", ". $items_to_show ."); return false;\">< previous</a> ";
  }
  $start = ($start_index-$items_to_show*4) > 0 ? ($start_index-$items_to_show*4) : 0;
  for ($tmp_index=$start;$tmp_index<$start_index;$tmp_index+=$items_to_show) {
    echo "<a href=\"javascript:void(0)\" onclick=\"selectPhotosTab(0, ".$person_id.", " . $tmp_index . ", ". $items_to_show ."); return false;\">" . ($tmp_index/$items_to_show+1) . "</a> ";
  }
  echo ($start_index/$items_to_show+1).' ';
  $end = (($start_index+$items_to_show*4) > $items_count) ? $items_count : $start_index+$items_to_show*4;
  for ($tmp_index=$start_index+$items_to_show;$tmp_index<$end;$tmp_index+=$items_to_show) {
    echo "<a href=\"javascript:void(0)\" onclick=\"selectPhotosTab(0, ".$person_id.", " . $tmp_index . ", ". $items_to_show ."); return false;\">" . ($tmp_index/$items_to_show+1) . "</a> ";
  }
  if ($start_index+$items_to_show < $items_count) {
    echo "<a href=\"javascript:void(0)\" onclick=\"selectPhotosTab(0, ".$person_id.", " . ($start_index+$items_to_show) . ", ". $items_to_show ."); return false;\">next ></a> ";
  }  
}
?>
</div>
<?php
unset($albums['found_rows']);
foreach ($albums as $album) {
  if (!isset($album['id'])) $album['id'] = '';
  if (!isset($album['title'])) $album['id'] = '';
  if (!isset($album['thumbnail_url'])) $album['thumbnail_url'] = '/images/albums/default/noalbum160.png';
?>
<div class="listdivi height1"/>
  <div id="album_<?php echo $album['id'];?>" class="listlight" style="background-color:#eff7ff; margin: 5px 0px 0px 0px;" >
    <div class="listdivi" style="height:10px;"></div>
    <div style="float:left; text-align:center; ">
      <div style="margin: -5px 15px 5px; overflow: hidden;">
        <a href="/profile/photos_view/<?php echo $album['owner_id'];?>/<?php echo $album['id'];?>">
          <img alt="<?php echo $album['title'];?>" title="<?php echo $album['title'];?>" src="<?php echo $album['thumbnail_url'];?>">
        </a>
      </div>
    </div>
    <div style="float:right; width: 68%;">
      <a href="/profile/photos_view/<?php echo $album['owner_id'];?>/<?php echo $album['id'];?>">
        <span style="font-size: 1.1em; font-weight: bold;" id="staticTitle_<?php echo $album['id'];?>"><?php echo $album['title'];?></span>
        <span>&nbsp;(<?php echo $album['media_count'];?> photos)</span>
      </a>
      <p id="staticDescription_<?php echo $album['id'];?>"><?php echo $album['description'];?></p>
      <div class="listdivi"></div>
      <span>created: <?php echo date('Y-m-d', $album['created']);?></span>
      <span style="color:#999">&nbsp;|&nbsp;</span>
      <span>lasted: <?php echo date('Y-m-d', $album['modified']);?></span>
      <div class="listdivi" style="height:10px;"></div>
      <div style="margin-bottom:0;padding-bottom:0">
        <nobr><span class="button"><a style="color : #3366cc;" href="/profile/photos_view/<?php echo $album['owner_id'];?>/<?php echo $album['id'];?>" >Show album</a></span></nobr>
<?php
if ($is_owner) {
?>
        <a style="color : #3366cc;" href="javascript:void(0);" onclick="saveAlbumDialog(<?php echo $album['owner_id'];?>, <?php echo $album['id'];?>, 'updateAlbumDiv_<?php echo $album['id'];?>');" class="button">Edit album</a>
        <a style="color : #3366cc;" href="javascript:void(0);" onclick="deleteType('album_delete', <?php echo $album['owner_id']?>, <?php echo $album['id'];?>, 'album_<?php echo $album['id'];?>');" class="button">Delete album</a>
        <div id="updateAlbumDiv_<?php echo $album['id'];?>" style="float:right; width: 70%; display:none">
          <div class="form_entry">
            <div class="form_label"><label for="title_<?php echo $album['id'];?>">title</label></div>
            <input type="text" name="title_<?php echo $album['id'];?>" id="title_<?php echo $album['id'];?>" value="<?php echo isset($album['title']) ? $album['title'] : ''?>" style="width:264px" />
          </div>
          <div class="form_entry">
            <div class="form_label"><label for="description_<?php echo $album['id'];?>">description</label></div>
            <textarea name="description_<?php echo $album['id'];?>" id="description_<?php echo $album['id'];?>"><?php echo isset($album['description']) ? $album['description'] : ''?></textarea>
          </div>
        </div>
<?php
}
?>
        <div id="dialog_<?php echo $album['id']?>" title="Delete album" style="display:none">
          <p><span id="dialogSpan_<?php echo $album['id']?>" class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete this album?</p>
        </div>
      </div>
      <div class="listdivi" style="height:5px;"></div>
    </div>
  </div>
</div>
<?php
}
?>
