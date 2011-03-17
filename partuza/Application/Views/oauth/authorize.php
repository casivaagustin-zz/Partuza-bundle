<?php
$this->template('/common/header.php');
?>
<h1>Grant access to your private information?</h1>

<p>An application is requesting access to your information. You should
only approve this request if you trust the application.</p>

<form
	action="<?=PartuzaConfig::get('web_prefix');?>/oauth/approveAuthorization"
	method="post"><input type="hidden" name="oauth_token"
	value="<?=htmlspecialchars($vars['oauth_token'])?>" /> <input
	type="hidden" name="oauth_callback"
	value="<?=htmlspecialchars($vars['oauth_callback'])?>" /> <input
	type="submit" value="Approve" /> <input type="button" value="Decline"
	onclick="location.href='/'" /></form>

<div style="clear: both"></div>
<?php
$this->template('/common/footer.php');
