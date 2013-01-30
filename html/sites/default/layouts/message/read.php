<h2>Message</h2>
<div class="box top-box">
	From: <?= $message->senderName ?><br />
	Sent: <?= $message->dateSent ?><br />
	Subject: <?= $message->subject ?><br /><br />
	<p>
		<?= $message->message ?>
	</p>

	<div class="buttonbar">
		<form method="post" action="<?= BASE_URL ?>messages/reply/">
			<input type="hidden" name="messageID" value="<?= $message->getID() ?>" />
<?php
	switch($message->type) {
		case Ndoorse_Message::TYPE_REQUEST_RECOMMENDATION:
?>
			<a href="<?= BASE_URL ?>requests/request/<?= $message->data ?>/" class="button"><span>View Details</span></a>
<?php
			break;
		case Ndoorse_Message::TYPE_REQUEST_RESPONSE:
?>
			<a href="<?= BASE_URL ?>requests/request/<?= $message->data ?>/" class="button"><span>View Details</span></a>
<?php
			break;
		case Ndoorse_Message::TYPE_JOB_RESPONSE:
?>
			<a href="<?= BASE_URL ?>jobs/applications/<?= $message->data ?>/" class="button"><span>View Application</span></a>
<?php
			break;
		case Ndoorse_Message::TYPE_JOB_RECOMMENDATION:
?>
<a href="<?= BASE_URL ?>jobs/view/<?= $message->data ?>/" class="button"><span>View Details</span></a>
<?php
			break;
        case Ndoorse_Message::TYPE_RECRUITER_CONTACT:
?>
        <button id="btn_accept" type="submit" name="acceptcontact"><span>Share My Profile</span></button>
        <button id="btn_reject" type="submit" name="rejectcontact"><span>Ignore</span></button>
<?php
            break;
        case Ndoorse_Message::TYPE_SERVICEPROVIDER_INVITE:
?>
        <button id="btn_accept_sp_invite" type="submit" name="accept_sp_invite"><span>Accept</span></button>
        <button id="btn_reject_sp_invite" type="submit" name="ignore_sp_invite"><span>Ignore</span></button>
<?php
            break;
		default:
?>
			<input type="hidden" name="senderID" value="<?= $message->senderID ?>" />
			<input type="hidden" name="subject" value="<?= $message->subject ?>" />
			<button id="btn_reply" type="submit" name="reply"><span>Reply</span></button>
<?php
	}
?>
			<button id="btn_delete" type="submit" name="delete"><span>Delete</span></button>
			<a href="<?= BASE_URL ?>messages/inbox/" class="button"><span>Return to Inbox</span></a>
		</form>
	</div>
</div>
<script type="text/javascript">
	$('#btn_delete').click(function() {
		return confirm('Are you sure you want to delete this message?');
	});
</script>