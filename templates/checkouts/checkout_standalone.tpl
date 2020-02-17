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
					<svg width="516.73px" height="516.73px" enable-background="new 0 0 516.727 516.727" version="1.1" viewBox="0 0 516.73 516.73" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m516.73 266.7c-0.665-34.825-8.221-69.54-22.175-101.28-13.908-31.771-34.094-60.551-58.876-84.333-24.767-23.8-54.139-42.615-85.929-55.027-31.773-12.46-65.937-18.412-99.687-17.69-33.755 0.668-67.36 8.007-98.091 21.539-30.754 13.488-58.615 33.058-81.632 57.071-23.033 24.001-41.229 52.452-53.222 83.229-12.038 30.76-17.775 63.811-17.055 96.494 0.67 32.688 7.793 65.182 20.903 94.899 13.067 29.738 32.019 56.681 55.266 78.931 23.234 22.268 50.766 39.846 80.528 51.417 29.749 11.616 61.69 17.136 93.303 16.419 31.62-0.671 63.001-7.58 91.707-20.268 28.724-12.646 54.747-30.979 76.231-53.461 21.503-22.469 38.461-49.08 49.611-77.827 6.79-17.427 11.396-35.624 13.824-54.062 0.649 0.037 1.302 0.063 1.96 0.063 18.409 0 33.333-14.923 33.333-33.333 0-0.936-0.049-1.861-0.124-2.777zm-52.965 88.514c-12.226 27.71-29.94 52.812-51.655 73.532-21.703 20.732-47.396 37.076-75.127 47.807-27.724 10.77-57.443 15.859-86.919 15.146-29.481-0.677-58.644-7.154-85.323-18.997-26.692-11.806-50.877-28.901-70.83-49.849-19.968-20.938-35.691-45.711-46.001-72.427-10.349-26.712-15.223-55.321-14.512-83.728 0.678-28.413 6.941-56.465 18.361-82.131 11.384-25.677 27.863-48.943 48.045-68.13 20.172-19.202 44.026-34.307 69.726-44.195 25.697-9.928 53.195-14.587 80.534-13.877 27.343 0.68 54.286 6.728 78.939 17.726 24.662 10.963 47.008 26.824 65.429 46.241 18.436 19.405 32.922 42.341 42.391 67.025 9.504 24.684 13.948 51.072 13.241 77.342h0.125c-0.076 0.916-0.125 1.841-0.125 2.777 0 17.193 13.018 31.34 29.732 33.137-3.242 18.138-8.609 35.844-16.031 52.601z"/></svg>
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
					<input name="manual_entry_seller_nr" id="manual-entry-seller-nr" pattern="[0-9]{3}" list="sellers" />
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
				<button class="button" name="dialogAction" value="reject" type="reset">Abbrechen</button>
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


	<script src="/dist/js/checkout.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var chk = new Checkout();
			chk.init();
		});
	</script>


</body>
</html>
