<?php
$button1 = sprintf('<a href="%s&launch=151&action=evaluate&marketId=%u" class="cmtIcon" style="background-image:url(/dist/img/icon_diagram.png); background-size: contain;" title="Auswertung für diesen Markt anzeigen"></a>', SELFURL, $cmtTableData['id']);
$button2 = sprintf('<a href="%s&launch=147&action=sumsheets&sellerMarketId=%u" class="cmtIcon" style="background-image:url(/dist/img/icon_pdf.png); background-size: contain;" title="Summenblätter für diesen Markt erzeugen"></a>', SELFURL, $cmtTableData['id']);
$button3 = sprintf('<a href="%s&launch=152&action=compose&marketId=%u" class="cmtIcon" style="background-image:url(/contentomat/templates/default/dist/img/default/email_xlarge.png); background-size: contain;" title="Summenblätter für diesen Markt erzeugen"></a>', SELFURL, $cmtTableData['id']);
array_push($cmt_functions, $button1);
array_push($cmt_functions, $button2);
array_push($cmt_functions, $button3);
?>
