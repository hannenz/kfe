<!doctype html>
<html class="no-js" lang="{PAGELANG}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>{PAGETITLE} - {CONSTANT:WEBNAME}</title>
	<meta name="description" content="{PAGEVAR:cmt_meta_description:recursive}">
	<meta name="keywords" content="{PAGEVAR:cmt_meta_keywords:recursive}">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" href="/dist/img/favicons/favicon-16x16.png" />

	<link rel="stylesheet" type="text/css" href="/dist/css/main.css" />

	{LAYOUTMODE_STARTSCRIPT}
	{IF (!{LAYOUTMODE})}
	<!-- <script src="/dist/js/vendor/quagga.min.js"></script> -->
	{ENDIF}
</head>
<body>

	<div class="isolate">
		<div class="noisy">Make some noise!</div>
		<div class="overlay"> </div>
	</div>

	{INCLUDE:PATHTOWEBROOT.'templates/partials/header.tpl'}

	<section class="main-content">
		<div class="inner-bound columns-container">
			<div class="main">
				<div class="stack stack--large">
					{IF("{HASFLASHMESSAGE}" == "1")}
					<div class="flash-message">{FLASHMESSAGE}</div>
					{ENDIF}
					{LOOP CONTENT(1)}{ENDLOOP CONTENT}
				</div>
			</div>
			<aside class="sidebar">
				<div class="stack stack--large">
					{LOOP CONTENT(2)}{ENDLOOP CONTENT}
				</div>
			</aside>
		</div>
	</section>

	{INCLUDE:PATHTOWEBROOT.'templates/partials/footer.tpl'}

	{IF(!{LAYOUTMODE})}
		<script src="/dist/js/main.min.js"></script>
	{ENDIF}
	{LAYOUTMODE_ENDSCRIPT}
</body>
</html>
