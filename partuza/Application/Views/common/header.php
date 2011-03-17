<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Partuza</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="<?php echo PartuzaConfig::get('web_prefix')?>/css/container.css?v=5" rel="stylesheet" type="text/css">
<link type="text/css" href="<?php echo PartuzaConfig::get('web_prefix')?>/css/jquery.css?v=5" rel="Stylesheet" />
<script type="text/javascript" src="<?php echo PartuzaConfig::get('gadget_server')?>/gadgets/js/rpc.js?c=1"></script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('gadget_server')?>/gadgets/files/container/osapi.js"></script>
<!--  the below was concated and compressed with yuicompressor using: java -jar {$path}/yuicompressor-2.3.5.jar -o {$file}-min.js {$file}.js -->
<!--  script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/jquery-1.3.js"></script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/jquery.ui.all.js"></script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/jquery.class.js"></script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/jquery.json-1.3.js"></script -->
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/jquery.all.js"></script>
<script type="text/javascript" src="<?php echo PartuzaConfig::get('web_prefix')?>/js/container.js"></script>
<link rel="openid2.provider openid.server" href="http://<?php echo $_SERVER['HTTP_HOST'];?>/openid/auth">
<?php if($this instanceof profileController) { ?>
<meta http-equiv="X-XRDS-Location" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/openidxrds" />
<?php } else { ?>
<meta http-equiv="X-XRDS-Location" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/xrds" />
<?php } ?>
</head>
<body>
<div id="headerDiv" class="ui-dark-widget-header ui-corner-all">
<?php
if (isset($_SESSION['username'])) {
?>
	<div id="searchDiv">
<form method="get" action="<?php echo PartuzaConfig::get('web_prefix')?>/search">|
<label for="search_q">search</label> <input type="text" id="search_q"
	name="q"> <input class="button" type="submit" value="Go" /></form>
</div>
<?php
}
?>
	<div id="userMenuDiv"<?php echo ! isset($_SESSION['username'])? ' style="margin-right:12px"' : '' ?>>
		<?php
  if (isset($_SESSION['username'])) {
    echo "<a href=\"" . PartuzaConfig::get('web_prefix') . "/home\">home</a> | <a href=\"" . PartuzaConfig::get('web_prefix') . "/profile/{$_SESSION['id']}\">profile</a> | <a href=\"" . PartuzaConfig::get('web_prefix') . "/logout\">logout</a>&nbsp;";
  } else {
    define('login_form',
    '<form method="post" action="%s"><a style="text-decoration:underline" href="' . PartuzaConfig::get('web_prefix') . '/register" >
    <span style="text-decoration:underline">register</span></a>, or <a style="text-decoration:underline" href="' . PartuzaConfig::get('web_prefix') . '/login" >
    <span style="text-decoration:underline">login</span></a> with <label for="email">e-mail</label>
    <input type="text" name="email" id="email" /> and <label for="password">password</label>
    <input type="password" name="password" id="password" />
    <input class="button" type="submit" value="Go" /></form>&nbsp;');

    if (isset($GLOBALS['render']) && isset($GLOBALS['render']['openid']) && $GLOBALS['render']['openid'] == 'login') {
      $action = '/openid/login';
    } else {
      $action = $_SERVER['REQUEST_URI'];
    }
    echo sprintf(login_form, $action);
  }
  ?>
	</div>
<span id="headerLogo"> <a	href="<?php echo PartuzaConfig::get("web_prefix")?>/home">Partuza</a> </span></div>
<div id="contentDiv">
