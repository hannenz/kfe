<!doctype html>
<html class="no-js" lang="{PAGELANG}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>KASSE {VAR:checkoutId} </title>
	<meta name="description" content="{PAGEVAR:cmt_meta_description:recursive}">
	<meta name="keywords" content="{PAGEVAR:cmt_meta_keywords:recursive}">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" href="/favicon.png" />

	<link rel="stylesheet" type="text/css" href="/dist/css/main.css" />

	<script src="/dist/js/vendor/quagga.min.js"></script>
</head>
<body class="checkout-page">

	<section class="main-content">

		<form class="checkout" id="checkout">
			<input type="hidden" value="{VAR:marketId}" name="marketId" id="marketId" />
			<input type="hidden" value="{VAR:checkoutId}" name="checkoutId" id="checkoutId" />

			<div class="checkout__cart">

				<table id="js-cart" class="cart"> </table>

			</div>

			<div class="checkout__displays">
				<div class="checkout-display">
					<label for="checkout-total">Summe</label>
					<input id="checkout-total" class="checkout-total" type="text" name="checkout-total" readonly value="0,00 &euro;" />
				</div>
				<div class="checkout-display">
					<label for="checkout-change">Rückgeld</label>
					<input id="checkout-change-value" class="checkout-change" type="text" readonly value="-,-- &euro;" />
				</div>
				<div class="checkout-display">
					<label for="checkout-code-finput">Code</label>
					<input id="checkout-code-input" class="checkout-code" type="text" name="code" value="" autofocus />
				</div>
			</div>

			<div class="checkout__button-panel">

				<div class="button-panel">
					<button class="button" data-action="change" data-value="500">5,00</button>
					<button class="button" data-action="change" data-value="1000">10,00</button>
					<button class="button" data-action="change" data-value="2000">20,00</button>
					<button class="button" data-action="change" data-value="5000">50,00</button>
					<button class="button" data-action="change" data-value="10000">100,00</button>
					<button class="button" data-action="change" data-value="20000">200,00</button>
					<button class="button" data-action="change-custom" data-value="">Anderer Betrag</button>
					<button class="button" data-action="cancel-last">Storno Letzte</button>
					<button class="button" data-action="cancel">Storno Gesamt</button>
					<button class="button" data-action="commit" type="submit">Fertig</button>
				</div>
			</div>

			<div class="checkout__footer">
				<div class="statusbar">
					<div>{VAR:user_alias} [{VAR:user_name}]</div>
					<div>Markt: #{VAR:marketId} -- {VAR:market_begin}</div>
					<div>Kasse: #{VAR:checkoutId}</div>
					<div>Gesamtumsatz: <b id="js-total-turnover">0,00</b> &euro; (<span id="js-total-carts">0</span> Vorgänge)</div>
					<input type="checkbox" id="js-toggle-camera-scanner"><label for="js-toggle-camera-scanner">Scanner an/aus</label>
					<div id="cam"></div>
					<a href="{CONSTANT:PAGEURL}?{SID}&launch={VAR:applicationId}" class="button" onclick="return window.confirm('Sicher?')">Zurück</a>
					<a href="/admin/index.php?{SID}&action=logout" class="button" onclick="return window.confirm('Sicher?')">Abmelden</a>
					<div class="statusbar__message" id="statusbar-message">MSSG</div>
				</div>
			</div>
		</form>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var chk = new Checkout();
				chk.init();
			});
		</script>
	</section>

	<script src="/dist/js/main.min.js"></script>
</body>
</html>

