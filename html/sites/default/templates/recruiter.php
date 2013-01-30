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
        <?= NavControl::recruiter(); ?>
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
    <div class="content">
        <ul class="footer_col">
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
        <ul class="footer_col">
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
        <ul class="footer_col">
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
        <img class="footlogo" src="<?= SITE_URL ?>images/logo_foot.png" alt="ndoorse, invite-only professional network" />
    </div>
</div>