<?php
/*
 * TODO: Add confirm dialog!!
 */
if (
	// Seller is not activated, has a valid email and has a valid Hash.. ?
	// Then we can send an activation link...
	(int)$cmtTableDataRaw['seller_is_activated'] == 0 &&
	filter_var($cmtTableDataRaw['seller_email'], FILTER_VALIDATE_EMAIL)&&
	preg_match('/[a-fA-F0-9]{64}/', $cmtTableDataRaw['seller_activation_hash'])
) {
	$button = sprintf('<a onclick="confirm(\"Aktivierungslink schicken?\")" href="%s&launch=147&action=sendActivationMail&sellerId=%u" data-dialog-content-id="confirmSendActivationLink" data-id="%u" class="cmtIcon" style="background-image:url(/admin/templates/default/administration/img/icons/email_xlarge.png); background-size: contain;" title="Aktivierungs-Link per E-Mail versenden"></a>', SELFURL, $cmtTableData['id'], $cmtTableData['id']);
	array_push($cmt_functions, $button);
}
?>
