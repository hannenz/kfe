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
			<div>Markt: #{VAR:marketId} -- {VAR:market_datetime}</div>
			<div>Kasse: #{VAR:checkoutId}</div>
			<div>Gesamtumsatz: <b id="js-total-turnover">0,00</b> &euro; (<span id="js-total-carts">0</span> Vorgänge)</div>
			<input type="checkbox" id="js-toggle-camera-scanner"><label for="js-toggle-camera-scanner">Scanner an/aus</label>
			<div id="cam"></div>
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
