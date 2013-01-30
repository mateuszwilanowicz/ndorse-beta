<h2>Upgrade</h2>
<div class="box top-box">
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pharetra neque vel tortor blandit dapibus. Phasellus eget odio felis. Sed faucibus imperdiet placerat. Cras volutpat rhoncus mauris ut adipiscing. In molestie suscipit tincidunt. Nullam consectetur, felis sagittis interdum molestie, neque neque interdum massa, ac dictum tortor libero non tellus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam porttitor porta commodo.
	</p>

	<div class="box">
		<h3>Features</h3>
		<table class="upgrade-options">
			<tr>
				<th>&nbsp;</th>
			<?php
				for($i=0; $i<$numLevels;++$i) {
			?>
				<th>
					<?= $levels[$i]->title ?>
				</th>
			<?php
				}
			?>
			</tr>
			<tr>
				<th>Recommendations</th>
			<?php
				for($i=0; $i<$numLevels;++$i) {
			?>
				<td>
					<?= $levels[$i]->recommendations ?>
				</td>
			<?php
				}
			?>
			</tr>
			<?php
				foreach($attributes as $key=>$name) {
			?>
			<tr>
				<th><?= $name ?></th>
			<?php
					for($i=0;$i<$numLevels;++$i) {
						foreach($levels[$i]->attributes as $att) {
							if($att->key == $key) {
			?>
				<td><?= $att->value ?></td>
			<?php
								break;
							}
						}
					}
			?>
			</tr>
			<?php
				}
			?>
			<tr>
				<th></th>
			<?php
				for($i=0; $i<$numLevels;++$i) {
			?>
				<td>
					&pound;<?= $levels[$i]->priceMonth ?>/month<br />
					&pound;<?= $levels[$i]->priceYear ?>/year
				</td>
			<?php
				}
			?>
			</tr>
			<tr class="upgrade-prices">
				<td></td>
			<?php
				for($i=0; $i<$numLevels;++$i) {
			?>
				<td>
			<?php
					if($_SESSION['user']->level < $levels[$i]->levelID) {
			?>
					<a href="<?= BASE_URL ?>members/upgrade/<?= $levels[$i]->levelID ?>/" class="button"><span>Upgrade</span></a>
			<?php
					}
			?>
				</td>
			<?php
				}
			?>
			</tr>
		</table>
	</div>
</div>