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
 *
 * @param Cart
 */
CartsCue.prototype.addCart = function(cart) {
	this.carts.push(cart.getData());
};


CartsCue.prototype.getLength = function() {
	return this.carts.length;
};

CartsCue.prototype.markSubmitted = function(id, timestamp, checkoutId) {
	var flag = false;
	for (var i = 0; i < this.carts.length; i++) {
		if (parseInt(this.carts[i].timestamp)  == parseInt(timestamp) &&
			parseInt(this.carts[i].checkoutId) == parseInt(checkoutId)) {

			console.log("Found cart in cue, un-cueing it!", i);

			this.carts[i].submitted = true;
			this.carts[i].submittedTimestamp = new Date();
			this.carts[i].id = id;

			flag = true;
			break;
		}
	}
	return flag;
}



CartsCue.prototype.countSubmittedCarts = function() {
	var n = 0;
	this.carts.forEach(function(cart) {
		n += (cart.submitted) ? 1 : 0;
	});
	return n;
};



/**
 * Get the last (latest) cart in the cue
 *
 * @return Object 	cart
 */
CartsCue.prototype.getLast = function() {
	if (this.carts.length == 0) {
		return null;
	}

	return new Cart().setData(this.carts[this.carts.length - 1]);
};


/**
 * Get a specific cart in the cue
 *
 * @param int 		id
 * @return Object 	cart
 */
CartsCue.prototype.getCartById = function(id) {
	console.log('Looking for cart with id ' + id);
	var cart = null;
	for (var i = 0; i < this.carts.length; i++) {
		if (this.carts[i].id == id) {
			cart = new Cart().setData(this.carts[i]);
			console.log('found', cart);
		}
	}

	return cart;
};
