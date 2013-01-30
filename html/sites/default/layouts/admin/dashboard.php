<div class="col col2 left-col">
	<div class="box top-box">
		<h3>Site Statistics</h3>
		<table class="admin-stats">
			<tr>
				<th></th>
				<th>Members</th>
				<th>Jobs</th>
				<th>Events</th>
				<th>Requests</th>
			</tr>
			<tr>
				<th>Current</th>
				<td>
					<a href="<?= ADMIN_URL ?>members/?status=2">
					<?= $stats['members']['active'] ?></td>
					</a>
				<td>
					<a href="<?= ADMIN_URL ?>jobs/?status=2">
					<?= $stats['jobs']['active'] ?></td>
					</a>
				<td>
					<a href="<?= ADMIN_URL ?>events/?status=2">
					<?= $stats['events']['active'] ?></td>
					</a>
				<td>
					<a href="<?= ADMIN_URL ?>requests/?status=2">
					<?= $stats['requests']['active'] ?></td>
					</a>
			</tr>
			<tr>
				<th>Pending</th>
				<td>
					<a href="<?= ADMIN_URL ?>members/?status=1">
					<?= $stats['members']['pending'] ?>
					</a>
				</td>
				<td>
					<a href="<?= ADMIN_URL ?>jobs/?status=1">
					<?= $stats['jobs']['pending'] ?>
					</a>
				</td>
				<td>
					<a href="<?= ADMIN_URL ?>jobs/?status=1">
					<?= $stats['events']['pending'] ?>
					</a>
				</td>
				<td>
					<a href="<?= ADMIN_URL ?>requests/?status=1">
					<?= $stats['requests']['pending'] ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="col1 col">
	<div class="box top-box">
		<h3>This Week</h3>
		<p>
			New Members: <?= $stats['members']['recent'] ?>
		</p>
		<p>
			Log-Ins: <?= $stats['members']['loggedIn'] ?>
		</p>
		<p>
			New Jobs: <?= $stats['jobs']['recent'] ?>
		</p>
		<p>
			New Events: <?= $stats['events']['recent'] ?>
		</p>
		<p>
			New Requests: <?= $stats['requests']['recent'] ?>
		</p>
	</div>
</div>