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

	<link rel="stylesheet" type="text/css" href="{ASSET:dist/css/main.css}" />

	{LAYOUTMODE_STARTSCRIPT}
	{IF (!{LAYOUTMODE})}
	<script type="text/javascript" src="/js/vendor/modernizr/modernizr.custom.js"></script>
	<script src="/js/vendor/jquery/jquery-3.1.0.min.js"></script>
	<script src="/js/vendor/quagga.min.js"></script>
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
		{LOOP CONTENT(1)}{ENDLOOP CONTENT}
	</section>

	{INCLUDE:PATHTOWEBROOT.'templates/partials/footer.tpl'}

	{IF(!{LAYOUTMODE})}
		<script src="{ASSET:dist/js/main.js}"></script>
	{ENDIF}
	{LAYOUTMODE_ENDSCRIPT}
</body>
</html>
