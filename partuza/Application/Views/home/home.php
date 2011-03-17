<?php
// only add the openid login dialog if extension_loaded('bcmath') || extension_loaded('gmp') else it exceptions
$this->template('/common/header.php');
?>
<div style="float: right; width: 500px;">
<p style="color: #3366CC; font-weight: bold">Please login by entering
your email and password in the top or <a
	href="<?php
echo PartuzaConfig::get('web_prefix')?>/register">register</a> to
continue.</p>
<p>Partuza is an example Open Social - Social Network Site that uses <a
	href="http://incubator.apache.org/shindig/">Apache Shindig.</a></p>
<p>The goals of Partuza are to:</p>
<ul>
	<li>Allow open social gadget developers to develop quickly and in
	private on their local, open social compliant servers.</li>
	<li>Serve as an example of how to implement open social support using
	shindig in your own social site.</li>
</ul>
<p>For people already comfortable with how to set up a website on a
linux/unix env there are 2 quick guides to get started:


<ul>
	<li><a href="http://code.google.com/p/partuza/wiki/GettingStarted">http://code.google.com/p/partuza/wiki/GettingStarted</a>
	</li>
	<li><a
		href="http://code.google.com/p/partuza/wiki/SettingUpShindigAndPartuza">http://code.google.com/p/partuza/wiki/SettingUpShindigAndPartuza</a>
	</li>
</ul>
</p>
<p>There are 2 simple-to-follow guides for getting shindig and partuza
up and running on a windows pc at:


<ul>
	<li><a href="http://chabotc.com/guides/shindig_install/">http://chabotc.com/guides/shindig_install/</a>
	</li>
	<li><a href="http://chabotc.com/guides/partuza_install/" rel="nofollow">http://chabotc.com/guides/partuza_install/</a>
	</li>
</ul>
</p>
<p>And for Mac OS X (leopard):


<ul>
	<li><a href="http://www.chabotc.com/guides/shindig_and_partuza_on_mac/">http://www.chabotc.com/guides/shindig_and_partuza_on_mac/</a></li>
</ul>
<p>And we have a developers and support mailing list up at:


<ul>
	<li><a href="http://groups.google.com/group/partuza">http://groups.google.com/group/partuza</a></li>
</ul>
</p>
</div>
<br />
<img src="/images/partuza-home.jpg" align="left" />
<?php
$this->template('/common/footer.php');
?>
