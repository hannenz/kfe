<form class="checkout" id="checkout">

	<div class="checkout__cart">

		<table id="js-cart" class="cart"> </table>

	</div>

	<div class="checkout__display">
		<input type="text" class="checkout-total" name="checkout-total" readonly value="0.00 &euro;" />
		<!-- <input class="checkout&#45;price&#45;input" type="text" name="price[]" value="" /> -->
		<div class="checkout-change" name="checkout-change" readonly value="">
			<span class="checkout-change__label">Rückgeld</span>
			<span id="checkout-change-value" class="checkout-change__value">&nbsp;</span>
		</div>
		<label for="checkout-code-input">Code</label>
		<input id="checkout-code-input" class="checkout-code-input" type="text" name="code[]" value="" autofocus />
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
			<button class="button" data-action="cancel">Storno</button>
			<button class="button" data-action="commit" type="submit">Fertig</button>
			<input name="change-custom" value="" />
		</div>
	</div>

	<div class="checkout__footer">
		<div>Markt: #{VAR:marketId} -- {VAR:market_datetime}</div>
		<div>Kasse: #{VAR:checkoutId}</div>
		<div>Gesamtumsatz: <b id="js-total-turnover">0,00</b> &euro; (<span id="js-total-carts">0</span> Vorgänge)</div>
		<input type="checkbox" id="js-toggle-camera-scanner"><label for="js-toggle-camera-scanner">Scanner an/aus</label>
		<div id="cam"></div>
	</div>
</form>
