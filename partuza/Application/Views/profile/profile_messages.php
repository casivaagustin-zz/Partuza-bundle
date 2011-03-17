<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">
<?php
$this->template('profile/profile_info.php', $vars);
?>
</div>
<div id="profileContentWide">

  <div id="messageTabs" style="position:relative">
  <div style="position:absolute; z-index:1000; right: 9px; top: 6px;"><input id="messageCompose" type="button" class="button" value="Compose" style="border: 1px solid #3366CC" /></div>
    <ul>
    	<li><a href="#inbox">Inbox</a></li>
    	<li><a href="#sent">Sent</a></li>
    	<li><a href="#notifications">Notications</a></li>
    </ul>
    <div id="inbox"></div>
    <div id="sent"></div>
    <div id="notifications"></div>
  </div>

</div>
<div style="clear: both"></div>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/messages.js"></script>
<?php
$this->template('/common/footer.php');
?>