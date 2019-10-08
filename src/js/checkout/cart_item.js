/**
 * src/js/cart_item.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-08
 */
function CartItem() {

	var self = this;

	var marketId = null;
	var sellerId = null;
	var sellerNr = null;
	var value = null;
	var ts = null;
	var checkoutId = null;
	var code = null;


	function newFromCode(code, checkoutId) {

		var marketId = parseInt(code.substring(0, 4));
		var marketDate = code.substring(4, 12);
		var sellerId = parseInt(code.substring(12, 16));
		var sellerNr = parseInt(code.substring(16, 19));
		var value = parseInt(code.substring(19));

		if (Number.isNaN(marketId)) {
			console.log("Invalid code", code);
			return null;
		}
		if (!marketDate.match(/^\d{4}\d{2}\d{2}$/)) {
			console.log("Invalid code", code);
			return null;
		}
		if (Number.isNaN(sellerId)) {
			console.log("Invalid code", code);
			return null;
		}
		if (Number.isNaN(sellerNr)) {
			console.log("Invalid code", code);
			return null;
		}
		if (Number.isNaN(value)) {
			console.log("Invalid code", code);
			return null;
		}

		item.marketId = marketId;
		item.sellerId = sellerId;
		item.sellerNr = sellerNr;
		item.value = value;
		item.ts = Date.now();
		item.checkoutId = checkoutId;
		item.code = code;

		return this;
	}

};

