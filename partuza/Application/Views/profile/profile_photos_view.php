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
    <div>
      <h2 style="color: #3366CC;">
        <?php echo $albums['title']; ?>
        <span class="headernote"> (<?php echo $medias['found_rows'];?> photos) </span>
      </h1>
    </div>
<?php
if ($is_owner) {
?>
    <div style="position: absolute; z-index: 1000; right: 9px; top: 6px;">
      <input id="Compose" type="button" onclick="uploadMediaDialog(); return false;" class="button" value="Upload Photo" style="border: 1px solid #3366CC" />
    </div>
<?php
}
?>
  </div>
<?php
if ($is_owner) {
?>
  <div id="uploadMediaDialog" title="Upload Photo" style="margin:10px; padding:2px; zoom:1; display:none">
  	<span class="photo_normal"> Valid file formats are .png, .jpg and .gif, max upload size is 20MB. You can upload it. </span>
    <form id="upload_form_1" method="post" enctype="multipart/form-data" target="_self" action="/profile/photo_upload/<?php echo $albums['owner_id']?>/<?php echo $albums['id'];?>" class="photo_normal">
    	<div class="form_entry">
        <input name="uploadPhoto" id="upload_form_1_file" style="width:100px;" type="file" />
      </div>
      <div class="form_entry">
        <input style="width:132px;" name="title" id="title" value="" style="float:left" />
        &nbsp;&nbsp;<label for="title">title</label>
      </div>
    </form>
    <div class="listdivi" style="height: 10px;"></div>
    <span id="upload_form_1_span"></span>
    <div class="listdivi" style="height: 10px;"></div>
    <span id="uploadProgress" style="float: left; display: none;"><img src="/images/progress-loader.gif"/></span>
    <span id="uploadFinished" style="float: left; margin: 0 0 0 15px; display: none;"><a href="/profile/photos_view/<?php echo $albums['owner_id']?>/<?php echo $albums['id']?>">refresh album</a></span>
    <div class="listdivi"></div>
  </div>
<?php
}
?>
  <div class="photo_normal">
    <div class="photo_normal"><?php echo $albums['description'];?></div>
    <div class="photo_normal listlight">
      <span style="float:left">
        <a href="/profile/photos/<?php echo $albums['owner_id'];?>">Back to albums</a>
      </span>
      <span style="float:right; margin: 0px 10px 0px 0px;">
<?php
$start_index = $page['start_index'];
$items_to_show = $page['items_to_show'];
$items_count = $page['items_count'];
if ($items_to_show < $items_count) {
  $base_url = (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) ? substr($_SERVER['REQUEST_URI'], 0, $pos) : $_SERVER['REQUEST_URI'];
  $items_to_show_inurl = ($items_to_show != 12) ? "&c=$items_to_show" : "";
  $page_count = floor(($items_count + $items_to_show - 1) / $items_to_show);
  echo "Showing page " . ($start_index/$items_to_show+1) . " of " . $page_count . " &nbsp;&nbsp;&nbsp;&nbsp;";
  if ($start_index > 0) {
    echo "<a href=\"$base_url?s=".($start_index-$items_to_show).$items_to_show_inurl."\">< previous</a> ";
  }
  $start = ($start_index-$items_to_show*4) > 0 ? ($start_index-$items_to_show*4) : 0;
  for ($tmp_index=$start;$tmp_index<$start_index;$tmp_index+=$items_to_show) {
    echo "<a href=\"$base_url?s=".$tmp_index.$items_to_show_inurl."\">".($tmp_index/$items_to_show+1)."</a> ";
  }
  echo ($start_index/$items_to_show+1).' ';
  $end = (($start_index+$items_to_show*4) > $items_count) ? $items_count : $start_index+$items_to_show*4;
  for ($tmp_index=$start_index+$items_to_show;$tmp_index<$end;$tmp_index+=$items_to_show) {
    echo "<a href=\"$base_url?s=".$tmp_index.$items_to_show_inurl."\">".($tmp_index/$items_to_show+1)."</a> ";
  }
  if ($start_index+$items_to_show < $items_count) {
    echo "<a href=\"$base_url?s=".($start_index+$items_to_show).$items_to_show_inurl."\">next ></a>";
  }  
}
?>
      </span>
    </div>
    <div class="listdivi"></div>
  </div>
  <div class="photo_normal" style="background-color: #e5ecf9;">
    <div class="listlight">
    	<div class="listdivi" style="height: 5px;"></div>
<?php 
$i = 0;
unset($medias['found_rows']);
foreach ($medias as $media) {
  if (! isset($media['id'])) $media['id'] = 0;
  if (! isset($media['thumbnail_url'])) $media['thumbnail_url'] = $media['url'];
?>
      <div id="media_<?php echo $media['id'];?>" class="triplel align_center" style="width:32%;">
        <div class="media_item">
        	<a href="/profile/photo_view/<?php echo $media['owner_id']?>/<?php echo $media['album_id']?>/<?php echo $media['id']?>">
          <div class="media_picture" style="background-image: url(<?php echo $media['thumbnail_url'];?>)"></div></a>
          <a href="/profile/photo_view/<?php echo $media['owner_id']?>/<?php echo $media['album_id']?>/<?php echo $media['id']?>"><span id="staticTitle_<?php echo $media['id']?>"><?php echo $media['title'];?></span></a>
        </div>
        <div class="listdivi" style="height: 5px;"></div>
<?php
if ($is_owner) {
?>
        <div id="updateMediaDiv_<?php echo $media['id'];?>" style="width:100%; text-align:center;">
          <nobr><span class="button"><a href="javascript:void(0);" onclick="saveMediaDialog(<?php echo $media['owner_id']?>, <?php echo $media['id'];?>, 'editMedia_<?php echo $media['id'];?>');">edit</a></span>&nbsp;&nbsp;</nobr>
          <nobr><span class="button"><a href="javascript:void(0);" onclick="deleteType('media_delete', <?php echo $media['owner_id']?>, <?php echo $media['id'];?>, 'media_<?php echo $media['id'];?>');">delete</a></span></nobr>
          <div class="listdivi" style="height: 2px;"></div>
          <div id="editMedia_<?php echo $media['id'];?>" title="Edit Media" style="display:none">
            <div class="form_entry">
              <div class="form_label_photo"><label for="title_<?php echo $media['id'];?>">title</label></div>
              <input style="width:140px;" type="text" name="title_<?php echo $media['id'];?>" id="title_<?php echo $media['id'];?>" value="<?php echo $media['title'];?>">
            </div>
            <div class="form_entry">
              <div class="form_label_photo"><label for="thumbnail_url_<?php echo $media['id'];?>">set cover</label></div>
              <input style="width:10px;" type="checkbox" name="thumbnail_url_<?php echo $media['id'];?>" id="thumbnail_url_<?php echo $media['id'];?>" value="true"/>
            </div>
            <!--
            <div class="form_entry" style="margin-top:12px">
              <div class="form_label"></div>
              <input type="button" id="save" onclick="saveMedia(<?php echo $media['owner_id']?>, <?php echo $media['id']?>, 'title|title_<?php echo $media['id']?>', 'thumbnail_url|thumbnail_url_<?php echo $media['id'];?>|checkbox');" value="save" style="width:auto" class="button" />
              <input type="button" id="cancel" onclick="hideDiv('editMedia_<?php echo $media['id']?>');" value="cancel" style="width:auto" class="button" />
            </div>
            -->
          </div>
          <div id="dialog_<?php echo $media['id'];?>" title="Delete media" style="display:none">
            <p><span id="dialogSpan_<?php echo $media['id']?>" class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete this media?</p>
          </div>
        </div>
        <div class="listdivi" style="height: 5px;"></div>
<?php
}
?>
      </div>
<?php
  $i++;
  if (($i%3) == 0) {
    echo '</div><div class="listlight">';
  }
}
?>
    </div>
  </div>
</div>
<div style="clear: both"></div>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/photos.js"></script>
<iframe id="uploadFileIframe" name="uploadFileIframe" style="width:0px; height:0px; margin:0px; padding:0px; display:none;"></iframe>
<?php
$this->template('/common/footer.php');
?>
