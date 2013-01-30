<div class="account">
	<div class="content">
		<?= NavControl::accountbar() ?>
	</div>
</div>
<div class="header">
	<div class="content">
		<h1>
			<a href="<?= BASE_URL ?>">
				<img src="<?= SITE_URL ?>images/logo.png" alt="ndoorse - a professional network based on talent" />
			</a>
			<span class="alt">ndoorse</span>
		</h1>
	</div>
</div>
<div class="navigation">
	<div class="content">
		<?= NavControl::render(); ?>
	</div>
</div>
<div class="content">
<?php
	echo PageMessageControl::errors();
	echo PageMessageControl::messages();
	echo PageMessageControl::ticker();
?>
	<div class="page">
		<?= Page::getBlock('main'); ?>
		<div class="clearer"></div>
	</div>
</div>
<div class="footer">
	<div class="footer-nav">
		<div class="content">
			<div class="footer-social">
				Stay Connected:&nbsp;
				<a href="http://www.facebook.com"><span class="footer-social-facebook"></span></a>&nbsp;
				<a href="http://www.twitter.com"><span class="footer-social-twitter"></span></a>
			</div>
			<ul>
				<li>
					<a href="">Contact Us</a>
				</li>
				<li>
					<a href="">Contact Us</a>
				</li>
				<li>
					<a href="">Contact Us</a>
				</li>
				<li>
					<a href="">Contact Us</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="content">
		<img class="footlogo" src="<?= SITE_URL ?>images/logo_foot.png" alt="ndoorse, invite-only professional network" />
	</div>
	<div class="footer-copyright">
		<div class="content">
			&copy; ndoorse 2012&nbsp;&nbsp;|&nbsp;&nbsp;<a href="">Terms &amp; Conditions</a>
		</div>
	</div>
</div>