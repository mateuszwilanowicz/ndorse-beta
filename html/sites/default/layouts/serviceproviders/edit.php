<div class="page">
	<div class="box">
		<h2>Edit Serviceprovider</h2>
		<?= ServiceProviderEditControl::render($serviceProvider); ?>
	</div>
	<div class="box">
        <h2>Service Provider Members</h2>
	    <?php
            $serviceProvider->loadMembers();
            foreach ($serviceProvider->members as $m) {
                echo '<a href="'.BASE_URL.'members/profile/'.$m[0]->userID.'">'.$m[0]->firstname.' '.$m[0]->lastname.'</a> '.Ndoorse_Serviceprovider::getUserStatus($m[1]).' ';
                if($m[0]->userID != $_SESSION['user']->userID)
                    echo '<a class="button" href="'.BASE_URL.'serviceproviders/remove/'.$serviceProvider->serviceproviderID.'/'.$m[0]->userID.'/"><span>Remove</span></a>';
                echo '<br />';
            }
	    ?>
        <div class="box">
            <?= ServiceProviderInviteControl::render($args, $inviteFormErrors); ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
        mode : "textareas",
        theme : "advanced"
    });
    
	$(function() {
        autocompleter($('#respondent'), $('#userID'), "<?= BASE_URL ?>ajax/name/autocomplete/");
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>
