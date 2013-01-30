<h2>Recruiters Dashboard - Jobs</h2>
<div class="col left-col col1">
    <div class="request-filter box">
        <h3>Filter</h3>
        <?= JobsFilterControl::render($args); ?>
    </div>
</div>
<div class="col col2">
    <div class="box">
<?php
    $form = new FormControl(RECRUITERS_URL . 'jobs/post/', 'recruiters_jobs');
    $form->html(JobListControl::render($filteredjobs,$args));
    $form->submit('delete','delete',true,array("value"=>"delete"));
    $form->submit('share','share',true,array("value"=>"share"));
    $form->submit('match','match members',true,array("value"=>"match"));    
    $form->html("<a href='" . BASE_URL . "jobs/post/' class='button'>create</a>");
    echo $form->render();
?>
    </div>
</div>

<?php 

/*
<div class="col1">
    <?php
        if(count($requests) > 0) {
    ?>
    <div class="box">
        <h3>Your Connection Requests</h3>
        <?php

            foreach($requests as $key=>$connection) {
                if($connection['respondentID'] != $_SESSION['user']->getID()) {
                    $connectionUserID = $connection['respondentID'];
                } else {
                    $connectionUserID = $connection['requesterID'];
                }
                echo '<a href="' . BASE_URL . 'members/profile/' . $connectionUserID . '/">' . $connection['name'] . "</a>";
                echo "<a href='". BASE_URL ."members/confirm/" . $connectionUserID . "' class='button'>Confirm</a>";
                echo "<a href='". BASE_URL ."members/ignore/" . $connectionUserID . "' class='button'>Ignore</a>";
                echo "<br />";

            }
        ?>
    </div>
    <?php
        }

        if(count($current) > 0) {
    ?>
    <div class="box">
        <h3>Your Current Connections</h3>
        <?php
            //pr($current,false);
            foreach($current as $key=>$connection) {
                if($connection['respondentID'] != $_SESSION['user']->getID()) {
                    echo '<a href="' . BASE_URL . 'members/profile/' . $connection['respondentID'] . '/">' . $connection['name'] . "</a><br />";
                } else {
                    echo '<a href="' . BASE_URL . 'members/profile/' . $connection['requesterID'] . '/">' . $connection['name'] . "</a><br />";
                }
            }
        ?>
    </div>
    <?php
        }

        if(count($pending) > 0) {
    ?>
    <div class="box">
        <h3>Your Pending Requests</h3>
        <?php
            foreach($pending as $key=>$connection) {
                if($connection['respondentID'] != $_SESSION['user']->getID()) {
                    $connectionUserID = $connection['respondentID'];
                } else {
                    $connectionUserID = $connection['requesterID'];
                }
                echo '<a href="' . BASE_URL . 'members/profile/' . $connectionUserID . '/">' . $connection['name'] . "</a><br />";
            }
        ?>
    </div>
    <?php
        }

        if(count($connections) == 0) {
?>
    <div class="box">
        <h3>Your Connections</h3>
        <p>
            You currently have no connections.
        </p>
    </div>
<?php
        }
    ?>
</div>
<div class="col2">
    <div class="box">
        <?= ConnectControl::render($args); ?>
    </div>
    <div class="box">
        <?= InviteControl::render($args,$fieldErrors); ?>
    </div>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
    $(function() {
        autocompleter($('#respondent'), $('#userID'), "<?= BASE_URL ?>ajax/name/autocomplete/");
    });
</script>
  
 */ ?>