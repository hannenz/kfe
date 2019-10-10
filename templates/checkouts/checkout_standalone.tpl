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

		<form class="checkout" id="checkout" autocomplete="off">
			<input type="hidden" value="{VAR:marketId}" name="marketId" id="marketId" />
			<input type="hidden" value="{VAR:checkoutId}" name="checkoutId" id="checkoutId" />
			<input type="hidden" value="{VAR:user_id}" name="cashierId" id="cashierId" />

			<header class="checkout__header">
				<div>Kasse {VAR:checkoutId}</div>
				<div>Markt #{VAR:marketId} am {VAR:marketDate}</div>
				<div>{VAR:user_name} <a href="/admin/index.php?{SID}&action=logout" class="no-button">Abmelden</a> </div>
			</header>

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
					<button class="button" data-action="cancel-last">Storno<br>Letzte</button>
					<button class="button" data-action="cancel">Storno Gesamt</button>
					<button class="button" data-action="change" data-value="500">5,00</button>
					<button class="button" data-action="change" data-value="1000">10,00</button>
					<button class="button" data-action="change" data-value="2000">20,00</button>
					<button class="button" data-action="change" data-value="5000">50,00</button>
					<button class="button" data-action="change" data-value="10000">100,00</button>
					<!-- <button class="button" data&#45;action="change" data&#45;value="20000">200,00</button> -->
					<button class="button" data-action="change-custom" data-value="">Anderer Betrag</button>
					<button class="button" data-action="commit" type="submit">Fertig</button>
				</div>
			</div>

			<div class="checkout__footer">
				<div class="statusbar">
					<div>Gesamtumsatz: <b id="js-total-turnover">0,00 &euro;</b> (<span id="js-total-carts">0</span> Vorgänge)</div>
					<a id="submit-carts-btn" href="javascript:;">Submit</a>
					<!-- <input type="checkbox" id="js&#45;toggle&#45;camera&#45;scanner"><label for="js&#45;toggle&#45;camera&#45;scanner">Scanner an/aus</label> -->
					<!-- <div id="cam"></div> -->
					<!-- <a href="{CONSTANT:PAGEURL}?{SID}&#38;launch={VAR:applicationId}" class="button">Zurück</a> -->
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

	<script src="/dist/js/checkout.js"></script>
</body>
</html>

