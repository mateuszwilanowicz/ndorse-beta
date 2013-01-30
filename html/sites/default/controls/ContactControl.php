<?php
	class ContactControl {

		public static function render($notifications, $member) {
			ob_start();
?>
<h3>Contact Preferences</h3>
<p>
	Select the way in which you prefer to be contacted using the controls below. Your email address is never shared with other members, please see our <a href="">privacy policy</a> for details.
</p>
<p>
	Hover over each heading for more information.
</p>

<form method="post" action="">
	<input type="hidden" name="section" value="contactprefs" />

	<table class="contact-options">
		<tr>
			<td></td>
			<td colspan="4">
				Who can contact me?
			</td>
<?php /*
			<td colspan="2">
				How am I notified?
			</td>
*/ ?>
		</tr>
		<tr class="contact-permission-labels">
			<th></th>
			<th title="Members to whom you are connected">Network</th>
			<th title="ndoorse members">Members</th>
			<th title="Registered recruiters">Recruiters</th>
			<th title="Administrative notifications from ndoorse staff">ndoorse</th>
<?php /*
			<th title="Internal site messages">
				Message
				<input type="checkbox" id="contact_message" name="contact_message" value="1" checked="checked" title="Enable/disable all" />
			</th>
			<th title="Email notifications to your registered email address">
				Email
				<input type="checkbox" id="contact_email" name="contact_email" value="1" checked="checked" title="Enable/disable all" />
			</th>
*/ ?>
		</tr>
		<tr>
			<th class="contact-message-type" title="Internal site messages sent between members">Messaging</th>
			<td>
				<input type="checkbox" id="contact_network_message" name="contact_network_message" value="1" <?= $notifications->checkType('message', Ndoorse_Notification::ACCESS_NETWORK) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_member_message" name="contact_member_message" value="1" <?= $notifications->checkType('message', Ndoorse_Notification::ACCESS_MEMBER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_recruiter_message" name="contact_recruiter_message" value="1" <?= $notifications->checkType('message', Ndoorse_Notification::ACCESS_RECRUITER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_admin_message" name="contact_admin_message" value="1" <?= $notifications->checkType('message', Ndoorse_Notification::ACCESS_ADMIN) ? 'checked="checked"' : '' ?> />
			</td>
<?php /*
			<td>
				<input type="checkbox" id="contact_message_message" name="contact_message_message" value="1" checked="checked" disabled="disabled" />
			</td>
			<td>
				<input type="checkbox" id="contact_message_email" name="contact_message_email" value="1" checked="checked" />
			</td>
*/ ?>
		</tr>
		<tr>
			<th class="contact-message-type" title="Receive recommendations of content you may find interesting">Recommendations</th>
			<td>
				<input type="checkbox" id="contact_network_recommend" name="contact_network_recommend" value="1" <?= $notifications->checkType('recommend', Ndoorse_Notification::ACCESS_NETWORK) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_member_recommend" name="contact_member_recommend" value="1" <?= $notifications->checkType('recommend', Ndoorse_Notification::ACCESS_MEMBER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_recruiter_recommend" name="contact_recruiter_recommend" value="1" <?= $notifications->checkType('recommend', Ndoorse_Notification::ACCESS_RECRUITER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_admin_recommend" name="contact_admin_recommend" value="1" <?= $notifications->checkType('recommend', Ndoorse_Notification::ACCESS_ADMIN) ? 'checked="checked"' : '' ?> />
			</td>
<?php /*
			<td>
				<input type="checkbox" id="contact_recommend_message" name="contact_recommend_message" value="1" checked="checked" />
			</td>
			<td>
				<input type="checkbox" id="contact_recommend_email" name="contact_recommend_email" value="1" checked="checked" />
			</td>
*/ ?>
		</tr>
		<tr>
			<th class="contact-message-type" title="Receive notifications of jobs for which you may be suitable, and invitations to apply">Jobs</th>
			<td>
				<input type="checkbox" id="contact_network_job" name="contact_network_job" value="1" <?= $notifications->checkType('job', Ndoorse_Notification::ACCESS_NETWORK) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_member_job" name="contact_member_job" value="1" <?= $notifications->checkType('job', Ndoorse_Notification::ACCESS_MEMBER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_recruiter_job" name="contact_recruiter_job" value="1" <?= $notifications->checkType('job', Ndoorse_Notification::ACCESS_RECRUITER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_admin_job" name="contact_admin_job" value="1" <?= $notifications->checkType('job', Ndoorse_Notification::ACCESS_ADMIN) ? 'checked="checked"' : '' ?> />
			</td>
<?php /*
			<td>
				<input type="checkbox" id="contact_job_message" name="contact_job_message" value="1" checked="checked" />
			</td>
			<td>
				<input type="checkbox" id="contact_job_email" name="contact_job_email" value="1" checked="checked" />
			</td>
*/ ?>
		</tr>
		<tr>
			<th class="contact-message-type" title="Receive event invitations and notifications">Events</th>
			<td>
				<input type="checkbox" id="contact_network_event" name="contact_network_event" value="1" <?= $notifications->checkType('event', Ndoorse_Notification::ACCESS_NETWORK) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_member_event" name="contact_member_event" value="1" <?= $notifications->checkType('event', Ndoorse_Notification::ACCESS_MEMBER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_recruiter_event" name="contact_recruiter_event" value="1" <?= $notifications->checkType('event', Ndoorse_Notification::ACCESS_RECRUITER) ? 'checked="checked"' : '' ?> />
			</td>
			<td>
				<input type="checkbox" id="contact_admin_event" name="contact_admin_event" value="1" <?= $notifications->checkType('event', Ndoorse_Notification::ACCESS_ADMIN) ? 'checked="checked"' : '' ?> />
			</td>
<?php /*
			<td>
				<input type="checkbox" id="contact_event_message" name="contact_event_message" value="1" checked="checked" />
			</td>
			<td>
				<input type="checkbox" id="contact_event_email" name="contact_event_email" value="1" checked="checked" />
			</td>
*/ ?>
		</tr>
	</table>
</form>
<?php
			$output = ob_get_clean();
			return $output;
		}

	}
?>