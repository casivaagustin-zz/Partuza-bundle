<?php
  $this->template('/common/header.php');
?>
<?php
  $info = unserialize($GLOBALS['render']['info']);
  $oauth = unserialize($GLOBALS['render']['oauth']);
?>

<h1>Trust This Site?</h1>
<div class="form">
<form method="post" action="/openid/trust">

<!-- OpenID request -->
<p>Do you wish to confirm your identity (<code>
  <a href="<?php echo $info->identity?>">
  <?php echo $info->identity?>
  </a>
</code>) with <code>
  <?php echo $info->trust_root?>
</code>?</p>

<!-- Hybrid OAuth request -->
<?php
if ($oauth !== null) {
  echo "<p>You may also like to check the following properties to share with the application $oauth->consumer:</p>";
  echo "<p>";
  $array = explode(',', $oauth->scope);
  foreach ($array as $scope) {
    echo "<input type=checkbox name=scope[] value=$scope checked=true />$scope";
  }
  echo "</p>";
}
?>

<!-- Submit with selections -->
<input type="submit" name="trust" value="Confirm" />

<!-- Cancel all -->
<input type="submit" value="Do not confirm" />

</form>
</div>
<?php
$this->template('/common/footer.php');
?>