/**
 * src/js/checkout.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @version 2019-07-15
 * @copyright , 15 Juli, 2019
 * @package kfe
 */

function Checkout() {

	var self = this;

	this.marketId = document.getElementById('marketId').value;
	this.checkoutId = document.getElementById('checkoutId').value;

	this.carts = [];
	this.cart = {
		timestamp: Date.now(),
		checkoutId: this.checkoutId,
		marketId: this.marketId,
		submitted: false,
		items: []
	};

	console.log(self.cart);

	this.totalInput = document.getElementById('checkout-total');
	this.changeInput = document.getElementById('checkout-change-value');
	this.codeInput = document.getElementById('checkout-code-input');



	this.init = function() {
		// console.log("Checkout::init");
		// document.addEventListener('DOMContentLoaded', self.setup);
		self.setup();
	}




	this.setup = function() {

		// If camera is available, setup camera barcode scanner
		navigator.getMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia); 
		navigator.getMedia({video: true}, function() {
			console.log("We have a camera, so go ahead!");
			this.cameraBarcodeScanner = new CameraBarcodeScanner();
			this.cameraBarcodeScanner.setup(document.getElementById('cam'),  function(result) {
				var item = self.getItemFromCode(result.codeResult.code);
				self.addToCart(item);
			});
		}, function() {
			console.log("No camera available, never mind …");
		});	


		window.addEventListener('online', function() {
			document.body.classList.add('is-online');
			self.submitCarts();
		});
		window.addEventListener('offline', function() {
			document.body.classList.remove('is-online');
		});
		document.body.classList.toggle('is-online', window.onLine);

		document.addEventListener('keyup', self.onKeyUp);

		console.log("Checkout::setup");

		// window.addEventListener('beforeunload', function(e) {
		// 	console.log("About to unload the page");
		// 	var mssg = 'Seite wirklich verlassen?';
		// 	e.preventDefault();
		// 	(e || window.event).returnValue =  mssg;
		// 	return mssg;
		// });

		document.forms.checkout.addEventListener('submit', function(e) {
			e.preventDefault();
			return false;
		});

		self.codeInput.addEventListener('blur', function(ev) {
			self.codeInput.focus();
		});

		self.setupBarcodeScanner();
		self.camDiv = document.getElementById('cam');

		var chkbx = document.getElementById('js-toggle-camera-scanner');
		chkbx.addEventListener('change', function(e) {

			if (this.checked) {
				console.log("Starting camera scanner");
				// self.camDiv.style.display = 'block';
				self.cameraBarcodeScanner.turnOn();
			}
			else {
				console.log("Stopping camera scanner");
				self.cameraBarcodeScanner.turnOff();
				// self.camDiv.style.display = 'none';
			}
		});


		var buttons = document.querySelectorAll('.button-panel > .button');
		for (var i = 0; i < buttons.length; i++) {
			var btn = buttons[i];
			btn.addEventListener('click', self.onPanelButtonClicked);
		}

		// Periodically try to submit carts to server
		// window.setInterval(self.submitCarts, 5000);

		self.resurrect();
		self.createTableFromCart();
	};

	this.onKeyUp = function(ev) {
		ev.preventDefault();
		switch (ev.keyCode) {
			case 65: // Q
				self.change(500);
				break;
			case 83: // W
				self.change(1000);
				break;
			case 68: // E
				self.change(2000);
				break;
			case 70: // R
				self.change(5000);
				bre6k;
			case 71: // T
				self.change(10000);
				break;
			case 72: // Y
				self.change(20000);
				break;
			case 74: // U
				break;
			case 61:
				self.submitCarts();
				break;
			case 85:
				self.showCarts();
				break;


			case 999:
				self.commitCart();
				self.cancelCart();
				self.createTableFromCart();
				self.updateTotalTurnover();
				break;
		}
	};

	this.onPanelButtonClicked = function(ev) {
		ev.preventDefault();
		var action = this.dataset.action;
		console.log(action);
		switch (action) {
			case 'change':
				self.change(this.dataset.value);
				break;

			case 'change-custom':
				var value = window.prompt("Herausgeben auf ...");
				value = value.replace(/[^\d]/g, '');
				self.change(parseInt(value));
				break;

			case 'cancel-last':
				if (self.cart.items.length == 0) {
					return;
				}

				self.cancelLast();
				self.createTableFromCart();
				break;

			case 'cancel':
				if (self.cart.items.length == 0) {
					return;
				}

				if (window.confirm("Sind Sie sicher, dass sie den gesamten Vorgang stornieren möchten?")) {
					self.cancelCart();
					self.createTableFromCart();
				}
				break;

			case 'commit':
				self.commitCart();
				self.cancelCart();
				self.createTableFromCart();
				self.updateTotalTurnover();
				break;

			default:
				console.log("action: " + action + " to be implemented yet");
				break;
		}

		self.codeInput.focus();
		return false;
	};


	/**
	 * Calc and display change money for a given value
	 *
	 * @param int value
	 * @return void
	 */
	this.change = function(value) {
		self.changeInput.value = ((value - (self.getCartTotal())) / 100).toFixed(2) + ' €';
	}


	this.setupBarcodeScanner = function() {
		self.codeInput.focus();
		self.codeInput.addEventListener('keydown', function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();
				return false;
			}
			return true;
		});

		self.codeInput.addEventListener('input', function(ev) {

			this.value = this.value.replace(/[^\d]/g, ''); 

			if (this.value.length >= 16) {
				var code = this.value;
				var item = self.getItemFromCode(code);
				if (item != null) {
					self.addToCart(item);
				}
				else {
					alert ("Invalid code: " + code);
				}

				this.value = '';
				this.focus();
			}
		});
	};

	this.getItemFromCode = function(code) {

		var marketId = code.substring(0, 8);
		var sellerId = parseInt(code.substring(8, 11));
		var value = parseInt(code.substring(11));

		if (!marketId.match(/^\d{4}\d{2}\d{2}$/)) {
			console.log("Invalid code", code);
			return null;
		}
		if (Number.isNaN(sellerId)) {
			console.log("Invalid code", code);
			return null;
		}
		if (Number.isNaN(value)) {
			console.log("Invalid code", code);
			return null;
		}

		var item = {
			marketId: marketId,
			sellerId: sellerId,
			value: value,
			ts: Date.now(),
			checkoutId: this.checkoutId,
			code: code
		}
		return item;
	}


	this.cancelLast = function() {
		var i;
		if ((i = self.cart.items.length - 1) > 0) {
			self.cancelItem(i);
		}
	};

	this.cancelItem = function(i) {

		if (self.cart.items[i]) {

			item = self.cart.items[i];
			var mssg = "Sind Sie sicher, diese Position zu stornieren?\n#" + (i + 1)  + "\nVerkäufer-Nr: " + item.sellerId + "\nBetrag: " + (item.value / 100).toFixed(2) + "EUR";
			console.log(mssg);
			if (!window.confirm(mssg)) {
				return;
			}

			self.cart.items.splice(i, 1);
		}
		self.createTableFromCart();
		self.codeInput.focus();
		self.persist();
	}


	this.addToCart = function(item) {
		self.cart.items.push(item);
		self.createTableFromCart();
		self.persist();
		// Play a sound
	}

	this.createTableFromCart = function() {
		var table = document.getElementById('js-cart');
		table.innerHTML = '';

		var row = document.createElement('tr');
		var th = document.createElement('th');
		th.innerText = '#';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Verkäufer-Nr';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Betrag';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = '';
		row.appendChild(th);

		table.appendChild(row);


		var total = 0;
		for (var i = 0; i < self.cart.items.length; i++) {
			var item = self.cart.items[i];

			var row = document.createElement('tr');
			var td1 = document.createElement('td');
			td1.innerText = (i + 1);
			row.appendChild(td1);
			var td2 = document.createElement('td');
			td2.innerText = item.sellerId;
			row.appendChild(td2);

			var td3 = document.createElement('td');
			td3.innerText = (item.value / 100).toFixed(2);
			row.appendChild(td3);

			var td4 = document.createElement('td');
			var btn = document.createElement('button');
			td4.appendChild(btn);
			row.appendChild(td4);

			btn.innerHTML = 'Stornieren';
			btn.addEventListener('click', function(ev) {
				var tr = this.parentNode.parentNode;
				var children = tr.parentNode.childNodes;
				for (n = 0; n < children.length; n++) {
					if (children[n] == tr) {
						break;
					}
				}

				if (n > 0) {
					console.log("Cancel button has been clicked");
					self.cancelItem(n - 1);
				}
			});

			table.appendChild(row);
			total += item.value;
		}

		self.totalInput.value = (total / 100).toFixed(2) + ' €';
		self.changeInput.value = '-,-- €';

		if (self.cart.items.length == 0) {
			document.querySelector('[data-action=cancel]').setAttribute('disabled', true);
			document.querySelector('[data-action=commit]').setAttribute('disabled', true);
		}
		else {
			document.querySelector('[data-action=cancel]').removeAttribute('disabled');
			document.querySelector('[data-action=commit]').removeAttribute('disabled');
		}
	}


	this.getCartTotal = function() {
		var total = 0;
		self.cart.items.forEach(function(item) {
			total += item.value;
		});

		return total;
	};

	
	/**
	 * Committing a cart means finishing it and add it to the cue / list of
	 * carts. If we are online the cart is also submitted to the server
	 */
	this.commitCart = function() {
		console.log("Committing cart");

		if (self.cart.items.length == 0) {
			console.log("Cart is empty, aborting commit");
			return;
		}


		// The cart needs to be cloned before pushed to the stack,
		// this is a simple method to clone a Javascript object:
		var clone = JSON.parse(JSON.stringify(self.cart));
		self.carts.push(clone);

		if (navigator.onLine) {
			console.log("ONLINE: Submitting");
			self.submitCart(clone);
		}

		self.updateTotalTurnover();
		self.persist();
	};



	this.updateTotalTurnover = function() {
		var totalTurnover = self.calcTotalTurnover() / 100;
		document.getElementById('js-total-turnover').innerText = totalTurnover.toFixed(2);
		document.getElementById('js-total-carts').innerText = self.carts.length;
	}



	/**
	 * This will delete the current cart and reset, e.g.
	 * preapre for a new one
	 */
	this.cancelCart = function() {
		console.log("Cancelling cart");
		self.cart.timestamp = Date.now();
		self.cart.marketId = self.marketId;
		self.cart.checkoutId = self.checkoutId;
		self.cart.items = [];
		self.cart.submitted = false;

		self.persist();
	}



	/**
	 * Save the current cart and cart cue to localStorage
	 */
	this.persist = function() {
		// window.localStorage.setItem('checkoutId', self.checkoutId);
		// window.localStorage.setItem('marketId', self.marketId);
		window.localStorage.setItem('cart', JSON.stringify(self.cart));
		window.localStorage.setItem('carts', JSON.stringify(self.carts));
	};



	/**
	 * Restore current cart and cart cue from localStorage
	 */
	this.resurrect = function() {
		var checkoutId, marketId, cart;

		// if ((checkoutId = window.localStorage.getItem('checkoutId')) != null) {
		// 	console.log("resurrecting checkoutId", checkoutId);
		// 	self.checkoutId = checkoutId;
		// }

		// if ((marketId = window.localStorage.getItem('marketId')) != null) {
		// 	self.marketId = marketId;
		// }

		if ((cart = window.localStorage.getItem('cart')) != null) {
			self.cart = JSON.parse(cart);
			self.createTableFromCart();
		}

		if ((carts = window.localStorage.getItem('carts')) != null) {
			self.carts = JSON.parse(carts);
			self.updateTotalTurnover();
		}
	};

	this.calcTotalTurnover = function() {
		var turnover = 0;
		for (var i = 0; i < self.carts.length; i++) {
			for (var j = 0; j < self.carts[i].items.length; j++) {
				turnover += self.carts[i].items[j].value;
			}
		}
		return turnover;
	};


	this.submitCarts = function() {
		self.carts.forEach(function(cart, i) {
			self.submitCart(cart);
		});
	};


	/**
	 * Submit a cart to the server
	 *
	 * @param Array 	The carts data
	 * @return void
	 */
	this.submitCart = function(cart) {
		if (cart.submitted) {
			console.log("Cart has been submitted yet, aborting");
			return;
		}
		
		console.log("submitting cart: ", cart);

		var data = new FormData();
		data.append('action',  'add');
		data.append('timestamp', cart.timestamp);
		data.append('marketId', cart.marketId);
		data.append('checkoutId', cart.checkoutId);
		data.append('items', JSON.stringify(cart.items));
		data.append('total', self.getCartTotal());

		var xhr = new XMLHttpRequest();
		xhr.addEventListener('load', function() {
			var response = JSON.parse(this.responseText);
			if (response.success) {
				cart.submitted = true;
			}
		});
		xhr.addEventListener('error', function() {

		});
		xhr.open('POST', '/de/9/carts.html');
		xhr.send(data);
	};


	this.showCarts = function() {
		console.log("Showiong carts");
		var containerEl = document.createElement('ul');

		self.carts.forEach(function(cart) {
			console.log(cart.timestamp);
			var itemEl = document.createElement('li');

		});
	}
};

// var chk = new Checkout();
// chk.init();
