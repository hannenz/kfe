<?php
$button1 = sprintf('<a href="%s&action=evaluate&marketId=%u" class="cmtIcon" style="background-image:url(/dist/img/icon_diagram.png);" title="Auswertung für diesen Markt anzeigen"></a>', SELFURL, $cmtTableData['id']);
$button2 = sprintf('<a href="%s&launch=147&action=sumsheets&marketId=%u" class="cmtIcon" style="background-image:url(/dist/img/icon_pdf.png);" title="Summenblätter für diesen Markt erzeugen"></a>', SELFURL, $cmtTableData['id']);
array_push($cmt_functions, $button1);
array_push($cmt_functions, $button2);
?>
