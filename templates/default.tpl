<!doctype html>
<html class="no-js" lang="{PAGELANG}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>{PAGETITLE} - </title>
	<meta name="description" content="{PAGEVAR:cmt_meta_description:recursive}">
	<meta name="keywords" content="{PAGEVAR:cmt_meta_keywords:recursive}">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" href="/favicon.png" />

	<link rel="stylesheet" type="text/css" href="dist/css/main.css" />

	{LAYOUTMODE_STARTSCRIPT}
	{IF (!{LAYOUTMODE})}
	<script type="text/javascript" src="/js/vendor/modernizr/modernizr.custom.js"></script>
	<script src="/js/vendor/jquery/jquery-3.1.0.min.js"></script>
	<script src="/dist/js/vendor/quagga.min.js"></script>
	{ENDIF}
</head>
<body>
	<!-- Inject SVG sprites -->
	<object 
		type="image/svg+xml" 
		data="/img/icons.svg" 
		onload="this.parentNode.replaceChild(this.getSVGDocument().childNodes[0], this)">
	</object>

	{INCLUDE:PATHTOWEBROOT.'templates/partials/header.tpl'}

	<section class="main-content">
		<div class="inner-bound">
			<div class="callout">
				<div class="callout__head">
					Samstag, 16. März 2019<br>
					9.30 &ndash; 11.30 Uhr
				</div>
				<div class="callout__subline">
					Einlass für Schwangere <br>
					(+1 Begleitperson) ab 9.00 Uhr
				</div>
			</div>
			{LOOP CONTENT(1)}{ENDLOOP CONTENT}
		</div>
	</section>

	{INCLUDE:PATHTOWEBROOT.'templates/partials/footer.tpl'}

	{IF(!{LAYOUTMODE})}
		<script src="dist/js/main.js"></script>
	{ENDIF}
	{LAYOUTMODE_ENDSCRIPT}
</body>
</html>
