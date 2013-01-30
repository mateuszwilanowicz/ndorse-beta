<h2>Service Providers</h2>
<div class="col2 col left-col">
	<div class="box top-box">
		<div class="request-list">
		<?php
			foreach($serviceProviders as $serviceProvider) {

			    $serviceProvider->loadAttributes();
                $serviceProvider->loadMembers();
            if(isset($args[1]))
                //pr($serviceProvider,false);
		?>
			<div class="serviceprovider" style="border: 1px solid black; padding: 10px">
			    <img src="<?= SITE_URL . 'images/' . $serviceProvider->logoURL ?>" border="0" />
				<h4><a href="<?= BASE_URL ?>serviceproviders/profile/<?= $serviceProvider->getID() ?>/"><?= $serviceProvider->name ?></a></h4>

				<p>
				    <?= $serviceProvider->description ?>
				</p>
				<p>
					<?= $serviceProvider->location ?>
				</p>

                <p> Industry <br />
                    <?php
                        foreach($serviceProvider->attributes as $attribute) {
                            echo $attribute . "<br />";
                        }
                    ?>

                </p>


				<?php if(count($serviceProvider->members)>0) { ?>
				<p> Members <br />
				    <?php
                        foreach($serviceProvider->members as $member) {
                         echo "<a href='" . BASE_URL . "members/profile/" . $member[0]->userID . "'>" . $member[0]->firstname . " " . $member[0]->lastname . "</a><br />";
                        }
                    ?>
				</p>
				<?php }
                    if(isset($args[1])) {
                ?>
    				<p>
                        <?= $serviceProvider->content ?>
                    </p>
    
                    <?php if($serviceProvider->documentID > 0 ) { ?>
                    <p>
                        <a href="<?= $serviceProvider->documentURL ?>"><?= $serviceProvider->documentURL ?></a>
                    </p>
    				<?php } ?>
    
                    <?php 
                    if($serviceProvider->twitterFeed > 0 && strlen($serviceProvider->twitterFeedUser) > 2 ) { 
                       
                        $count = 5;
                        $user = $serviceProvider->twitterFeedUser;
                        
                       
                        $tweet=json_decode(file_get_contents("http://api.twitter.com/1/statuses/user_timeline/".$user.".json?count=".$count ));
                        for ($i=1; $i <= $count; $i++){
                            //Assign feed to $feed
                        $feed = $tweet[($i-1)]->text;
                        //Find location of @ in feed
                        $feed = str_pad($feed, 3, ' ', STR_PAD_LEFT);   //pad feed     
                        $startat = stripos($feed, '@'); 
                        $numat = substr_count($feed, '@');
                        $numhash = substr_count($feed, '#'); 
                        $numhttp = substr_count($feed, 'http'); 
                        $feed = preg_replace("/(http:\/\/)(.*?)\/([\w\.\/\&\=\?\-\,\:\;\#\_\~\%\+]*)/", "<a href=\"\\0\">\\0</a>", $feed);
                        $feed = preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"http://www.twitter.com/\\1\">\\0</a>", $feed);
                        $feed = preg_replace('/(^|\s)#(\w+)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $feed);
                        echo "<div class='tweet'>".$feed.  "<div class='tweet_date'>". date("M \- j",strtotime($tweet[($i-1)]->created_at))."
                                </div></div>";      
                        }               
                        
                    }
                }
                ?>

			</div><br />
		<?php
			}
		?>
		</div>
	</div>
</div>
