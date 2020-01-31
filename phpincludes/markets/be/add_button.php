<?php
$button = sprintf('<a href="%s&action=evaluate&marketId=%u" class="cmtIcon reporting-icon-reject" title="Auswertung für diesen Markt anzeigen"></a>', SELFURL, $cmtTableData['id']);
array_push($cmt_functions, $button);
?>
