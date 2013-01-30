<div class="col3">
    <div class="box">
        <h2>Share Jobs</h2>
<?php
    $form = new FormControl(RECRUITERS_URL . 'jobs/post/', 'recruiters_jobs');
    $form->html(JobListControl::render($jobs,$args));
    echo $form->render();
    //echo '';
?>
    </div>
</div>
<div class="col2">
    <div class="box">
        <?= ShareJobControl::render($args); ?>
    </div>
</div>

<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
    $(function() {
        autocompleter($('#respondent'), $('#userID'), "<?= BASE_URL ?>ajax/name/autocomplete/");
    });
</script>