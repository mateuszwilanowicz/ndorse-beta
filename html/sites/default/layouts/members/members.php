<h2>Members Dashboard</h2>
<div class="col col2 left-col">
	<?php
		if(count($current) > 0) {
	    /*<div class="box<?= count($requests) == 0 ? ' top-box' : '' ?>"> */
	?>

    <div class="box top-box">
		<h3>Your Current Connections</h3>
		<div id="graph" class="graph"></div>
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
		} else {
    ?>
    <div class="box top-box">
        <h3>Your Connections</h3>
        <p>
            You currently have no connections.
        </p>
    </div>
    <?php
		}

	?>
</div>
<div class="col col1">

	<div class="box top-box">
		<h3>Connect</h3>
		<p>
			<?= ConnectControl::render($args); ?>
		</p>
		<p>
			<?= InviteControl::render($args,$fieldErrors); ?>
		</p>
	</div>
	 <?php
        if(count($requests) > 0) {
    ?>
    <div class="box box2">
        <h3>Your Connection Requests</h3>
        <?php

            foreach($requests as $key=>$connection) {
                if($connection['respondentID'] != $_SESSION['user']->getID()) {
                    $connectionUserID = $connection['respondentID'];
                } else {
                    $connectionUserID = $connection['requesterID'];
                }
                echo '<p><a href="' . BASE_URL . 'members/profile/' . $connectionUserID . '/">' . $connection['name'] . '</a></p>';
                echo '<p><a href="'. BASE_URL . 'members/confirm/' . $connectionUserID . '" class="button default-button"><span>Confirm</span></a>';
                echo '<a href="'. BASE_URL . 'members/ignore/' . $connectionUserID . '" class="button"><span>Ignore</span></a>';
                echo '</p>';
            }
        ?>
    </div><br />
    <?php
        }
        if(count($pending) > 0) {
    ?>
    <div class="box<?= count($requests) == 0 && count($current) == 0 ? ' top-box' : '' ?>">
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

    ?>

</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/sigma.min.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/sigma.parseGexf.js"></script>
<script type="text/javascript">
$(function() {
    var sigInst = sigma.init(document.getElementById('graph')).drawingProperties({
    defaultLabelColor: '#000',
    defaultLabelSize: 14,
    defaultLabelBGColor: '#3fb',
    defaultLabelHoverColor: '#000',
    labelThreshold: 5,
    defaultEdgeType: 'curve'
  }).graphProperties({
    minNodeSize: 0.5,
    maxNodeSize: 5,
    minEdgeSize: 1,
    maxEdgeSize: 1
  }).mouseProperties({
    maxRatio: 8
  });

  // Parse a GEXF encoded file to fill the graph
  // (requires "sigma.parseGexf.js" to be included)
  sigInst.parseGexf('<?= SITE_URL ?>data/les_miserables.gexf');

  // Bind events :
  var greyColor = '#cbced0';
  sigInst.bind('overnodes',function(event){
    var nodes = event.content;
    var neighbors = {};
    sigInst.iterEdges(function(e){
      if(nodes.indexOf(e.source)<0 && nodes.indexOf(e.target)<0){
        if(!e.attr['grey']){
          e.attr['true_color'] = e.color;
          e.color = greyColor;
          e.attr['grey'] = 1;
        }
      }else{
        e.color = e.attr['grey'] ? e.attr['true_color'] : e.color;
        e.attr['grey'] = 0;

        neighbors[e.source] = 1;
        neighbors[e.target] = 1;
      }
    }).iterNodes(function(n){
      if(!neighbors[n.id]){
        if(!n.attr['grey']){
          n.attr['true_color'] = n.color;
          n.color = greyColor;
          n.attr['grey'] = 1;
        }
      }else{
        n.color = n.attr['grey'] ? n.attr['true_color'] : n.color;
        n.attr['grey'] = 0;
      }
    }).draw(2,2,2);
  }).bind('outnodes',function(){
    sigInst.iterEdges(function(e){
      e.color = e.attr['grey'] ? e.attr['true_color'] : e.color;
      e.attr['grey'] = 0;
    }).iterNodes(function(n){
      n.color = n.attr['grey'] ? n.attr['true_color'] : n.color;
      n.attr['grey'] = 0;
    }).draw(2,2,2);
  });

  // Draw the graph :
  sigInst.draw();



    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", init, false);
    } else {
      window.onload = init;
    };
});
</script>
