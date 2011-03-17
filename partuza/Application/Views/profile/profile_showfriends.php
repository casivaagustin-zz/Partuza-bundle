<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">
<?php
$this->template('profile/profile_info.php', $vars);
?>
</div>
<div id="profileContentWide">
	<div class="gadgets-gadget-chrome" style="width: 790px; border-bottom: 1px solid #E5ECF9;">
		<div class="gadgets-gadget-title-bar"><span class="gadgets-gadget-title"><?php echo $vars['is_owner'] ? 'Your' : $vars['person']['first_name']."'s"?> friends (<?php echo $vars['friends_count']?>)</span></div>
<?php
foreach ($vars['friends'] as $friend) {
    $thumb = PartuzaConfig::get('site_root') . '/images/people/' . $friend['id'] . '.jpg';
    if (! file_exists($thumb)) {
      $thumb = PartuzaConfig::get('site_root') . '/images/people/nophoto.gif';
    }
    $thumb = Image::by_size($thumb, 50, 50);
    echo "
  <div class=\"friendEntry\" id=\"friendEntry{$friend['id']}\" style=\"height:56px;clear:both; border-bottom: 1px solid #E5ECF9;\">
    <div class=\"thumb\" style=\"float:left; margin-left:3px; margin-top:3px; width:50px; height:50px; background-image: url('$thumb') ; background-repeat: no-repeat; background-position: center center;\"></div>
    <div style=\"float:left; margin: 6px;\"><a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/{$friend['id']}\" rel=\"friend\" >{$friend['first_name']} {$friend['last_name']}</a></div>";
  if ($vars['is_owner']) {
    echo "<div style=\"float:right; margin: 6px;\" class=\"ui-state-default ui-corner-all\"><a href=\"javascript: void(0);\" id=\"removeButton{$friend['id']}\"><span class=\"ui-icon ui-icon-closethick\"></span></a></div>";
?>
    <script type="text/javascript">
    	$('#removeButton<?php echo $friend['id']?>').bind('click', function() {
    		$("#dialog<?php echo $friend['id']?>").dialog({
    			bgiframe: false,
    			resizable: false,
    			height:140,
    			modal: true,
    			closeOnEscape: true,
    			overlay: {
    				backgroundColor: '#000',
    				opacity: 0.5
    			},
    			buttons: {
    				'Remove': function() {
    					$(this).dialog('destroy');
    					window.location = '<?php echo PartuzaConfig::get('web_prefix');?>/home/removefriend/<?php echo $friend['id']?>';
    				},
    				Cancel: function() {
    					$(this).dialog('destroy');
    				}
    			}
    		});
    		return false;
    	});
    </script>
    <div id="dialog<?php echo $friend['id']?>" title="Remove from your friend list?" style="display:none">
    	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to remove <? echo $friend['first_name'].' '.$friend['last_name']?> from your friend list?</p>
    </div>
<?php
  }
  echo "</div> ";
}
  ?>
<script>
$('.friendEntry').hover(
	    function() { $(this).addClass('message-hover'); },
	    function() { $(this).removeClass('message-hover'); }
);
$('.friendEntry').bind('click', function() {
	window.location = '/profile/'+ ($(this).attr('id').replace('friendEntry', '') * 1);
	return false;
});
</script>
    <div style="clear: both"></div>
    </div>
<?php
$page = $vars['page'];
$pages = $vars['pages'];
if ($pages && $pages > 1 && $page > 0) {
  echo "<div style=\"text-align:center; font-weight: bold\">";
  $baseUrl = (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) ? substr($_SERVER['REQUEST_URI'], 0, $pos) : $_SERVER['REQUEST_URI'];
  if ($page > 1) {
    echo "<a href=\"$baseUrl?page=".($page - 1)."\">< previous</a> ";
    $startIndex = $page > 4 ? $page - 4 : 1;
    $endIndex = ($pages - $page) > 3 ? $page - 1 : $pages;
    for ($index = $startIndex ; $index <= $endIndex && $index != $page ; $index ++) {
      echo "<a href=\"{$baseUrl}?page=$index\">$index</a> ";
    }
  }
  echo "$page ";
  if ($page < $pages && $pages > 1) {
    $startIndex = $page + 1;
    $endIndex = ($pages - $page) > 4 ? $page + 4 : $pages;
    for ($index = $startIndex ; $index <= $endIndex; $index ++) {
      echo "<a href=\"{$baseUrl}?page=$index\">$index</a> ";
    }
    echo "<a href=\"$baseUrl?page=".($page + 1)."\"> next ></a> ";
  }
  echo "</div>";
}

?>
  <div style="clear: both"></div>
</div>
<?php
$this->template('/common/footer.php');
