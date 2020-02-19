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
	<link rel="stylesheet" type="text/css" href="/dist/css/vendor/dialog-polyfill.css" />

	<script src="/dist/js/vendor/quagga.min.js"></script>
	<script src="/dist/js/vendor/dialog-polyfill.js"></script>
	<script src="/dist/js/vendor/sprintf.min.js"></script>
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
				<div style="white-space: nowrap">{VAR:user_name} <a href="/admin/index.php?{SID}&action=logout" class="no-button">Abmelden</a> </div>
			</header>

			<div class="checkout__cart">

				<table id="js-cart" class="cart"> </table>

			</div>

			<div class="checkout__controls">
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

				<button id="manual-entry-btn" class="button">Manuelle Eingabe</button>


				<div class="checkout__button-panel">

					<div class="button-panel">
						<button class="button" data-action="edit-cart">Edit</button>
						<!-- <button class="button" data&#45;action="show&#45;last">show last</button> -->
						<button class="button" data-action="cancel-last">Storno<br>Letzte</button>
						<button class="button" data-action="cancel">Storno Gesamt</button>
						<button class="button" data-action="change" data-value="500">5,00</button>
						<button class="button" data-action="change" data-value="1000">10,00</button>
						<button class="button" data-action="change" data-value="2000">20,00</button>
						<button class="button" data-action="change" data-value="5000">50,00</button>
						<button class="button" data-action="change" data-value="10000">100,00</button>
						<!-- <button class="button" data&#45;action="change" data&#45;value="20000">200,00</button> -->
						<button class="button" data-action="change-custom" data-value="999999">Anderer Betrag</button>
						<button class="button" data-action="commit" type="submit">Buchen</button>
					</div>
				</div>
			</div>

			<div class="checkout__footer">
				<div class="statusbar">
					<div>Gesamtumsatz: <b id="js-total-turnover">0,00 &euro;</b> (<span id="js-total-carts">0</span> Vorgänge, <span id="js-total-carts-submitted">0</span> übermittelt) <a id="submit-carts-btn" href="javascript:;">Submit</a></div>
					<div class="statusbar__message" id="statusbar-message">MSSG</div>
				</div>
				<div class="busy">
					↻
				</div>
				<div class="online-offline">
					
					<svg width="26" height="26" version="1.1" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg">
						<g id="online">
							<path d="m23.26 1.78c-0.24542 2.007e-4 -0.49158 0.094579-0.67971 0.28271l-3.9339 3.9339c-1.7827-1.1577-4.191-0.9577-5.7545 0.60553l-1.7865 1.7865-1.3935-1.3955c-0.37627-0.37627-0.98316-0.37426-1.3594 0.0020054-0.37627 0.37627-0.37627 0.98115 0 1.3574l9.2934 9.2934c0.37627 0.37627 0.98316 0.37627 1.3594 0 0.37627-0.37627 0.37627-0.98116 0-1.3574l-1.3935-1.3935 1.7865-1.7865c1.5635-1.5637 1.7638-3.9717 0.60553-5.7545l3.9339-3.9339c0.37627-0.37627 0.37627-0.98316 0-1.3594-0.18814-0.18813-0.43229-0.28091-0.67771-0.28071z"/>
							<path d="m7.6731 8.0718c-0.24542 2.008e-4 -0.48957 0.092573-0.67771 0.28071-0.37627 0.37627-0.37827 0.98316-0.0020054 1.3594l1.3955 1.3935-1.7865 1.7865c-1.5632 1.5635-1.7632 3.9718-0.60553 5.7545l-3.9339 3.9339c-0.37627 0.37627-0.37827 0.98116-2e-3 1.3574 0.37627 0.37627 0.98316 0.37627 1.3594 0l3.9339-3.9339c1.7828 1.1583 4.1908 0.95797 5.7545-0.60553l1.7865-1.7865 1.3935 1.3935c0.37627 0.37627 0.98116 0.37627 1.3574 0 0.37627-0.37627 0.37627-0.98316 0-1.3594l-9.2934-9.2934c-0.18813-0.18814-0.4343-0.28091-0.67971-0.28071z" />
						</g>
						<g id="offline">
							<path d="m24.688 0.40039c-0.23296 1.8788e-4 -0.46595 0.088994-0.64453 0.26758l-3.7344 3.7344c-1.6922-1.0984-3.9771-0.90749-5.4609 0.57617l-1.6973 1.6953-1.3223-1.3242c-0.35717-0.35717-0.9319-0.35522-1.2891 0.0019531-0.35717 0.35717-0.35912 0.93189-0.001953 1.2891l8.8223 8.8223c0.35717 0.35717 0.93385 0.35717 1.291 0 0.35717-0.35717 0.35717-0.93385 0-1.291l-1.3242-1.3223 1.6973-1.6953c1.4839-1.4841 1.6732-3.7707 0.57422-5.4629l3.7344-3.7344c0.35717-0.35717 0.35717-0.93189 0-1.2891-0.17858-0.17858-0.41157-0.26777-0.64453-0.26758z"/>
							<path d="m5.9961 10.27c-0.23296 1.88e-4 -0.46595 0.090947-0.64453 0.26953-0.35717 0.35717-0.35912 0.9319-0.0019531 1.2891l1.3242 1.3223-1.6953 1.6973c-1.4837 1.4839-1.6746 3.7688-0.57617 5.4609l-3.7344 3.7344c-0.35717 0.35717-0.35717 0.93189 0 1.2891s0.93189 0.35717 1.2891 0l3.7344-3.7344c1.6922 1.0989 3.9787 0.9097 5.4629-0.57422l1.6953-1.6973 1.3223 1.3242c0.35717 0.35717 0.93385 0.35717 1.291 0 0.35717-0.35717 0.35717-0.93385 0-1.291l-8.8223-8.8223c-0.17858-0.17858-0.41157-0.26777-0.64453-0.26758z"/>
						</g>
					</svg>
				</div>
			</div>
		</form>
	</section>



	<dialog id="manual-entry-dlg" class="dialog">
		<form name="manual_entry" id="manual-entry">
			<header class="dialog__header">Manuelle Eingabe</header>
			<div class="dialog__body stack">
				<div class="form-field">
					<label for="manual-entry-seller-nr">Verkäufer-Nr</label>
					<input name="manual_entry_seller_nr" id="manual-entry-seller-nr" pattern="[0-9]{2,3}" list="sellers" />
					<datalist id="sellers">
						{LOOP VAR(sellers)}
							<option value="{VAR:seller_nr}">{VAR:seller_nr}</option>
						{ENDLOOP VAR}
					</datalist>
				</div>
				<div class="form-field">
					<label for="manual-entry-value">Betrag (in Cent)</label>
					<input name="manual_entry_value" id="manual-entry-value" pattern="[0-9]+" autocomplete="off" />
				</div>
			</div>
			<div class="dialog__action-area">
				<button class="button" name="dialogAction" value="reject">Abbrechen</button>
				<button class="button" name="dialogAction" value="accept" type="submit">OK</button>
			</div>
		</form>
	</dialog>

	<dialog id="change-custom-dlg" class="dialog">
		<form name="change_custom" id="change-custom">
			<header class="dialog__header">Herausgeben auf &hellip;</header>
				<div class="dialog__body stack">
					<div class="form-field">
						<label for="change-custom-value">Betrag in Cent</label>
						<input name="change_custom_value" id="change-custom-value" pattern="[0-9]+" />
					</div>
				</div>
				<div class="dialog__action-area">
					<button class="button" name="dialogAction" value="reject">Abbrechen</button>
					<button class="button" name="dialogAction" value="submit" type="accept">OK</button>
				</div>
			</div>
		</form>
	</dialog>

	<dialog id="cancel-item-dlg" class="dialog">
		<form name="cancel_item" id="cancel-item">
			<header class="dialog__header">Storno</header>
			<div class="dialog__body">
			</div>
			<div class="dialog__action-area">
				<button class="button" name="dialogAction" value="reject">Abbrechen</button>
				<button class="button" name="dialogAction" value="submit" type="accept">OK</button>
			</div>
		</form>
	</dialog>

	<dialog id="cancel-cart-dlg" class="dialog">
		<form name="cancel_cart" id="cancel-cart">
			<header class="dialog__header">Storno</header>
			<div class="dialog__body">
				Soll der gesamte Vorgang storniert werden?
			</div>
			<div class="dialog__action-area">
				<button class="button" name="dialogAction" value="reject">Abbrechen</button>
				<button class="button" name="dialogAction" value="submit" type="accept">OK</button>
			</div>
		</form>
	</dialog>

	<dialog id="edit-cart-dlg" class="dialog">
		<form name="cancel_cart" id="cancel-cart">
			<header class="dialog__header">Vorgang berbeiten</header>
			<div class="dialog__body">

			</div>
			<div class="dialog__action-area">
				<button class="button" name="dialogAction" value="reject">Abbrechen</button>
				<button class="button" name="dialogAction" value="submit" type="accept">OK</button>
			</div>
		</form>
	</dialog>


	<script src="/dist/js/checkout.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var chk = new Checkout();
			chk.init();
		});
	</script>


</body>
</html>
