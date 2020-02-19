/**
 * src/js/cart.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-08
 */
var Cart = function(marketId, checkoutId, cashierId) {
	this.marketId = parseInt(marketId);
	this.checkoutId = parseInt(checkoutId);
	this.cashierId = parseInt(cashierId);
	this.timestamp = Date.now();
	this.submitted = false;
	this.submittedTimestamp = null;
	this.id = null;
	this.items = [];
}


Cart.prototype.clear = function() {
	this.timestamp = Date.now();
	this.submitted = false;
	this.submittedTimestamp = null;
	this.id = null;
	this.items = [];
};


Cart.prototype.addItem = function(item) {
	this.items.push(item);
};


Cart.prototype.getItem = function(i) {
	return this.items[i];
};


Cart.prototype.getTotal = function() {
	var total = 0;
	this.items.forEach(function(item) {
		total += item.value;
	});
	return total;
};


Cart.prototype.removeItem = function(i) {
	if (this.items[i]) {
		this.items.splice(i, 1);
	}
};


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
 * Submit a cart to the server
 *
 * @return Promise, resolves to JSON data of submitted cart
 */
Cart.prototype.submit = function() {

	this.log();

	return new Promise(function(resolve, reject) {

		if (this.submitted) {
			console.log("submtited, slkip ...");
			reject("Cart has been submitted yet, aborting");
		}

		var data = new FormData();
		data.append('action',  'add');
		data.append('marketId', this.marketId);
		data.append('checkoutId', this.checkoutId);
		data.append('cashierId', this.cashierId);
		data.append('timestamp', this.timestamp);
		data.append('items', JSON.stringify(this.items));


		var total = 0;
		this.items.forEach(function(item) {
			total += item.value;
		});
		data.append('total', total);

		var xhr = new XMLHttpRequest();
		xhr.open('POST', '/de/9/carts.html');
		xhr.addEventListener('load', function() {
			var response = JSON.parse(xhr.responseText);
			if (response.success) {
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
 * Safety log
 * Every submission will be logged in local storage
 * just in case anything goes wrong we have some kind of backup here
 */
Cart.prototype.log = function() {
	this.items.forEach(function(item) {
		new Logger().log(JSON.stringify(item));
	});
};
