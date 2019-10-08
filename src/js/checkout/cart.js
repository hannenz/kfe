/**
 * src/js/cart.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-08
 */
var Cart = {

	marketId: null,
	checkoutId: null,
	cashierId: null,
	timestamp: null,
	submitted: false,
	submittedTimestamp: null,
	id: null,
	items: [],

	init: function(marketId, checkoutId, cashierId) {
		this.marketId = marketId;
		this.checkoutId = checkoutId;
		this.cashierId = cashierId;
	},

	clear: function() {
		this.timestamp = Date.now();
		this.submitted = false;
		this.submittedTimestamp = null;
		this.id = null;
		this.items = [];
	},

	addItem: function(item) {
		this.items.push(item);
	},

	getItem: function(i) {
		return this.items[i];
	},

	getTotal: function() {
		var total = 0;
		this.items.forEach(function(item) {
			total += item.value;
		});
		return total;
	},

	removeItem: function(i) {
		if (this.items[i]) {
			this.items.splice(i, 1);
		}
	},

	getData: function() {
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
	},

	setData: function(data) {
		this.marketId = data.marketId;
		this.checkoutId = data.checkoutId;
		this.cashierId = data.cashierId;
		this.timestamp = data.timestamp;
		this.submitted = data.submitted;
		this.submittedTimestamp = data.submittedTimestamp;
		this.id = data.id;
		this.items = data.items;
	}
};
