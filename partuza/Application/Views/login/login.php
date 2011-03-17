<?php
$this->template('/common/header.php');
?>
<div id="profileInfo" class="blue">Join the party with Partuza!<br />
<br />
Get in touch with your friends and share the fun.</div>
<div id="profileContentWide">
<div class="gadgets-gadget-chrome" style="width: 790px">
<div class="gadgets-gadget-title-bar"><b>Login</b></div>
<?php
if (! empty($vars['error'])) {
?>
<div style="padding: 12px">
      <div style="color: red"><b>Error : <?php echo $vars['error']?></b><br />
</div>
<?php
}
?>    
<form action="<?php echo PartuzaConfig::get('web_prefix');?>/login<?php if (isset($_GET['redirect'])) { echo "?redirect=".urlencode($_GET['redirect']); }?>" method="post" id="register">
<div class="form_entry">
<div class="form_label"><label for="login_email">email</label></div>
<input type="text" name="login_email" id="login_email"
  value="<?php echo isset($_POST['login_email']) ? $_POST['login_email'] : ''?>" />
</div>

<div class="form_entry">
<div class="form_label"><label for="login_password">password</label></div>
<input type="password" name="login_password" id="login_password"
  value="<?php echo isset($_POST['login_password']) ? $_POST['login_password'] : ''?>" />
</div>
<div><input class="submit" type="submit" value="Login" /></div>
</form>
</div>
</div>
<?php
$this->template('/common/footer.php');
?>
