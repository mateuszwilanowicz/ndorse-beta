<h2>
	Events for <?= date('F Y', mktime(0,0,0,$m,1,$y)) ?>
	<div class="pagingControl">
		&nbsp;
		<?php
			if(($m >= date('m') && $y == date('Y')) || $y > date('Y')) {
				$newMonth = $m == 1 ? 12 : $m - 1;
				$newYear = $m == 1 ? $y - 1 : $y;
					echo '<a href="?month=' . $newMonth . '&year=' . $newYear . '"><span class="prevButton"></span></a>&nbsp;';
			}
			if($y >= date('Y') && $y <= date('Y') + 1 && !($y == date('Y') + 1 && $m == 12)) {
				$newMonth = $m == 12 ? 1 : $m + 1;
				$newYear = $m == 12 ? $y + 1 : $y;
					echo '<a href="?month=' . $newMonth . '&year=' . $newYear . '"><span class="nextButton"></span></a>';
			}
		?>
	</div>
</h2>
<div class="box">
	<table class="events calendar">
		<thead>
			<tr>
				<th>Monday</th>
				<th>Tuesday</th>
				<th>Wednesday</th>
				<th>Thursday</th>
				<th>Friday</th>
				<th>Saturday</th>
				<th>Sunday</th>
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($month as $day) {
			if($day->format('N') == 1) {
				echo '<tr>';
			}
			echo '<td';
			if($day->format('m') != $m) {
				echo ' class="other-month"';
			}
			echo '><span class="calendar-date">' . $day->format('j') . '</span>';
			if(array_key_exists($day->format('Y-m-d'), $events)) {
				foreach($events[$day->format('Y-m-d')] as $event) {
					$startDate = getdate(strtotime($event->startDate));
					$endDate = getDate(strtotime($event->endDate));
						$dateDescription = sprintf("%02d", $startDate['hours']) . ':' . sprintf("%02d", $startDate['minutes']);
					if($endDate['year'] != '1970') {
						if($endDate['year'] == $startDate['year'] && $endDate['mon'] == $startDate['mon'] && $endDate['mday'] == $startDate['mday']) {
							$dateDescription .= ' - ' . sprintf("%02d", $endDate['hours']) . ':' . sprintf("%02d", $endDate['minutes']);
						} else {
							$dateDescription .= ' - ' . $endDate['mday'] . '/' . $endDate['month'] . '/' . $endDate['year'] . ' '  . sprintf("%02d", $endDate['hours']) . ':' . sprintf("%02d", $endDate['minutes']);
						}
					}
						echo '<a href="' . BASE_URL . 'events/view/' . $event->getID() . '/" title="' . $dateDescription . '" class="' . $event->memberLevel . '" target="_blank">' . $event->title . '</a>';
				}
			}
				if($day->format('N') == 7) {
				echo '</tr>';
			}
		}
	?>
	</tbody>
	</table>
	<div class="calendar-buttons">
		<a href="<?= BASE_URL ?>events/post/" class="button default-button"><span>Post a new event</span></a>
	</div>
</div>