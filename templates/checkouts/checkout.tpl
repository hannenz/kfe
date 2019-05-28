<form class="checkout" id="checkout">
	<input type="text" class="checkout-total" name="checkout-total" readonly value="0.00 &euro;" />
	<input class="checkout-code-input" type="text" name="code[]" valu="" autofocus />
	<input class="checkout-price-input" type="text" name="price[]" value="" />
	<button type="submit">
</form>

<table class="checkout-table">
</table>

<script>
	document.addEventListener('DOMContentLoaded', init);
	function init() {

		var total = 0;

		var codeInput = document.querySelector('.checkout-code-input');
		var priceInput = document.querySelector('.checkout-code-input');
		var table = document.querySelector('.checkout-table');
		var totalInput = document.querySelector('.checkout-total');

		codeInput.focus();
		codeInput.select();
		codeInput.addEventListener('keyup', onInput);

		function onInput(ev) {

			var price = 3.78;
			console.log(this.value);

			var row = document.createElement('tr');
			var td1 = document.createElement('td');
			var td2 = document.createElement('td');
			var td3 = document.createElement('td');

			row.appendChild(td1);
			row.appendChild(td2);
			row.appendChild(td3);

			td1.innerText = this.value;
			td2.innerText = price;

			table.appendChild(row);

			total += price;
			totalInput.value = total;
			codeInput.select();

		}
	}
</script>
