<div class="col3">
    <div class="box">
        <h2><?= $job->title; ?> Job Matches</h2>
        <?php
            //pr($args);
            if(isset($args[1]) && isset($args[2])) {
                $postURL = RECRUITERS_URL . 'match/' . $args[1] . '/' . $args[2] . '/';
            } else {
                $postURL = RECRUITERS_URL;
            }
            $form = new FormControl( $postURL, 'recruiters_match');
            $form->html(MatchListControl::render($matches,$args));
            $form->submit('message','request profile access',true,array("value"=>"message"));
            $form->submit('remove','remove from list',true,array("value"=>"remove"));
            echo $form->render();
        ?>
        
    </div>
</div>

<div class="col2">
    <div class="request-filter box">
        <h3>Filter</h3>
        <?= MatchFilterControl::render($args); ?>
    </div>
</div>