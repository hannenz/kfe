/**
 * src/js/checkout/carts_cue.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version date
 */
var CartsCue = function() {
	this.carts = [];
};


CartsCue.prototype.clear = function() {
	this.carts = [];
}


CartsCue.prototype.calcTotalTurnover = function() {
	var turnover = 0;
	for (var i = 0; i < this.carts.length; i++) {
		for (var j = 0; j < this.carts[i].items.length; j++) {
			turnover += this.carts[i].items[j].value;
		}
	}
	return turnover;
};


CartsCue.prototype.submitCarts = function() {
	this.carts.forEach(function(cart) {
		cart.submit();
	});
};



/**
 * Add a cart to the carts cue
 * Always adds a clone
 *
 * @param Cart
 */
CartsCue.prototype.addCart = function(cart) {
	this.carts.push(cart);
}


CartsCue.prototype.getLength = function() {
	return this.carts.length;
}


CartsCue.prototype.countSubmittedCarts = function() {
	var n = 0;
	this.carts.forEach(function(cart) {
		n += (cart.submitted) ? 1 : 0;
	});
	return n;
};

