<?php
if ((int)$cmtTableData['seller_is_activated'] == 0 && filter_var($cmtTableData['seller_email'], FILTER_VALIDATE_EMAIL)) {
	$button = sprintf('<a href="%s&launch=147&action=sendActivationMail&sellerId=%u" class="cmtIcon" style="background-image:url(/admin/templates/default/administration/img/icons/email_xlarge.png); background-size: contain;" title="Aktivierungs-Link per E-Mail versenden"></a>', SELFURL, $cmtTableData['id']);
	array_push($cmt_functions, $button);
}
?>
