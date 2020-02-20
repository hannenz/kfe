/**
 * src/js/cart.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-08
 */


/**
 * Class that represents a single cart ("Vorgang", "Bon")
 *
 * @class Cart
 */
var Cart = function(marketId, checkoutId, cashierId) {
	console.log('Creating a new cart');
	this.marketId = parseInt(marketId);
	this.checkoutId = parseInt(checkoutId);
	this.cashierId = parseInt(cashierId);
	this.timestamp = Date.now();
	this.submitted = false;
	this.submittedTimestamp = null;
	this.id = null;
	this.items = [];
}


/**
 * Clear the cart
 *
 * @return Object 		Cart (self)
 */
Cart.prototype.clear = function() {
	console.log('Clearing cart');
	this.timestamp = Date.now();
	this.submitted = false;
	this.submittedTimestamp = null;
	this.id = null;
	this.items = [];

	return this;
};


/**
 * Add an item to the cart
 *
 * @param Object 		An item object
 * @return Object 		Cart (self)
 */
Cart.prototype.addItem = function(item) {
	this.items.push(item);
	return this;
};


/**
 * Get an item from the cart
 *
 * @param int 			Index of the item to get
 * @return Object 		Cart (self)
 */
Cart.prototype.getItem = function(i) {
	return this.items[i];
};


/**
 * Get the total of the cart (in Cent)
 *
 * @return int 			The cart's total
 */
Cart.prototype.getTotal = function() {
	var total = 0;
	this.items.forEach(function(item) {
		total += item.value;
	});
	return total;
};


/**
 * Removes an item from the cart
 *
 * @param int 			index of the item to remove
 * @return Object 		Cart (self)
 */
Cart.prototype.removeItem = function(i) {
	if (this.items[i]) {
		this.items.splice(i, 1);
	}
	return this;
};


/**
 * Get a data representation of the cart
 *
 * @return Object
 */
Cart.prototype.getData = function() {
	return {
		marketId: this.marketId,
		checkoutId: this.checkoutId,
		cashierId: this.cashierId,
		timestamp: this.timestamp,
		submitted: this.submitted,
		submittedTimestamp: this.submittedTimestamp,
		id: this.id,
		items: this.items
	};
};


/**
 * Set the cart's properties from a data representation
 *
 * @param Object 		data object 
 * @return Object 		Cart (self)
 */
Cart.prototype.setData = function(data) {
	this.marketId = parseInt(data.marketId);
	this.checkoutId = parseInt(data.checkoutId);
	this.cashierId = parseInt(data.cashierId);
	this.timestamp = data.timestamp;
	this.submitted = data.submitted;
	this.submittedTimestamp = data.submittedTimestamp;
	this.id = parseInt(data.id);
	this.items = data.items;
	return this;
};


/**
 * Submit the cart to the server
 *
 * @return Promise, resolves to JSON data of submitted cart
 */
Cart.prototype.submit = function() {

	this.log();

	return new Promise(function(resolve, reject) {

		if (this.submitted) {
			console.log("cart has been submitted already, skip ...");
			reject("Cart has been submitted yet, aborting");
		}

		var data = new FormData();
		data.append('action',  'add');
		data.append('marketId', this.marketId);
		data.append('checkoutId', this.checkoutId);
		data.append('cashierId', this.cashierId);
		data.append('timestamp', this.timestamp);
		data.append('items', JSON.stringify(this.items));
		data.append('total', this.getTotal());

		var xhr = new XMLHttpRequest();
		xhr.open('POST', '/de/9/carts.html');
		xhr.addEventListener('load', function() {
			var response = JSON.parse(xhr.responseText);
			if (response.success) {
				console.log(response);
				resolve(response);
			}
			else {
				reject('Failed to submit cart');
			}
		});
		xhr.addEventListener('error', function() {
			reject('XHR request failed');
		});
		xhr.send(data);
	}.bind(this));
};

/**
 * Re-Submit the cart to the server
 *
 * @return Promise, resolves to JSON data of submitted cart
 */
Cart.prototype.resubmit = function() {

	this.log();

	if (!this.submitted || !this.id) {
		console.log("This cart is not submitted or has no ID yet, aborting");
		return;
	}

	return new Promise(function(resolve, reject) {

		var data = new FormData();
		data.append('action',  'update');
		data.append('cartId', this.id);
		data.append('marketId', this.marketId);
		data.append('checkoutId', this.checkoutId);
		data.append('cashierId', this.cashierId);
		data.append('timestamp', this.timestamp);
		data.append('items', JSON.stringify(this.items));
		data.append('total', this.getTotal());

		var xhr = new XMLHttpRequest();
		xhr.open('POST', '/de/9/carts.html');
		xhr.addEventListener('load', function() {
			var response = JSON.parse(xhr.responseText);
			if (response.success) {
				console.log(response);
				resolve(response);
			}
			else {
				reject('Failed to resubmit cart');
			}
		});
		xhr.addEventListener('error', function() {
			reject('XHR request failed');
		});
		xhr.send(data);
	}.bind(this));
};


/**
 * Safety log
 * Every submission will be logged in local storage
 * just in case anything goes wrong we have some kind of backup here
 */
Cart.prototype.log = function() {
	this.items.forEach(function(item) {
		new Logger().log(JSON.stringify(item));
	});
};
