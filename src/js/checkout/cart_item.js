/**
 * src/js/cart_item.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-08
 */
var CartItem = function() {
	var marketId = null;
	var sellerId = null;
	var sellerNr = null;
	var value = null;
	var ts = null;
	var checkoutId = null;
	var code = null;
}



CartItem.prototype.newFromCode = function(code, checkoutId) {

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

	this.marketId = marketId;
	this.sellerId = sellerId;
	this.sellerNr = sellerNr;
	this.value = value;
	this.ts = Date.now();
	this.checkoutId = checkoutId;
	this.code = code;

	return this;
}



/**
 * Create a new cart item from values
 *
 * @param int 	marketId
 * @param int 	checkoutId
 * @param int 	sellerNr
 * @param int 	value (in cent)
 */
CartItem.prototype.newFromValues = function(marketId, checkoutId, sellerNr, value) {

	this.marketId = parseInt(marketId);
	this.sellerId = null;
	this.sellerNr = parseInt(sellerNr);
	this.value = parseInt(value);
	this.ts = Date.now();
	this.checkoutId = parseInt(checkoutId);
	this.code = '';
	
	return this;
};
