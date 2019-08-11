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
	this.cart = [];
	this.checkoutId = 1;
	this.changeDiv = document.getElementById('checkout-change-value');
	this.codeInput = document.getElementById('checkout-code-input');

	this.init = function() {
		console.log("Checkout::init");
		document.addEventListener('DOMContentLoaded', self.setup);
	}

	this.setup = function() {

		if (!document.getElementById('cam')) {
			return;
		}

		document.addEventListener('keyup', self.onKeyUp);

		console.log("Checkout::setup");

		window.addEventListener('beforeunload', function(e) {
			console.log("About to unload the page");
			var mssg = 'Seite wirklich verlassen?';
			e.preventDefault();
			(e || window.event).returnValue =  mssg;
			return mssg;
		});

		document.forms.checkout.addEventListener('submit', function(e) {
			e.preventDefault();
			return false;
		});

		self.codeInput.addEventListener('blur', function(ev) {
			self.codeInput.focus();
		});

		self.setupBarcodeScanner();
		// self.setupCameraBarcodeScanner();
		self.camDiv = document.getElementById('cam');

		var chkbx = document.getElementById('js-toggle-camera-scanner');
		chkbx.addEventListener('change', function(e) {

			if (this.checked) {
				console.log("Starting camera scanner");
				// self.camDiv.style.display = 'block';
				self.setupCameraBarcodeScanner();
			}
			else {
				console.log("Stopping camera scanner");
				Quagga.stop();
				// self.camDiv.style.display = 'none';
			}
		});


		var buttons = document.querySelectorAll('.button-panel > .button');
		for (var i = 0; i < buttons.length; i++) {
			var btn = buttons[i];
			btn.addEventListener('click', self.onPanelButtonClicked);
		}

		self.createTableFromCart();
	};

	this.onKeyUp = function(ev) {
		ev.preventDefault();
		console.log(ev.keyCode);
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
		}
	};

	this.onPanelButtonClicked = function(ev) {
		var action = this.dataset.action;
		switch (action) {
			case 'change':
				self.change(this.dataset.value);
				break;

			default:
				console.log("action: " + action + " to be implemented yet");
				break;
		}

		self.codeInput.focus();
	};


	/**
	 * Calc and display change money for a given value
	 *
	 * @param int value
	 * @return void
	 */
	this.change = function(value) {
		self.changeDiv.innerText = ((value - (self.getCartTotal())) / 100).toFixed(2);
	}

	this.setupCameraBarcodeScanner = function() {

		Quagga.init({
			inputStream: {
				name: "Live",
				type: "LiveStream",
				target: document.getElementById('cam'),
				constraints: {
					width: 640,
					height: 480,
					facingMode: "environment"
				},
				singleChannel: false
			},
			decoder: {
				readers: ["code_128_reader"]
			},
			numOfWorkers: navigator.hardwareConcurrency,
			locate : false
		}, function(err) {
			if (err) {
				console.log(err);
				return;
			}
			// console.log("Quagga successfully initialised, now starting up");
			// Quagga.start();
		});

		Quagga.onDetected(function(result) {
			var item = self.getItemFromCode(result.codeResult.code);
			self.addToCart(item);
			
			// throttle somehow?
		});

		self.main();
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

		self.codeInput.addEventListener('keyup', function(ev) {

			var cleanval = '';
			for (var i = 0; i < this.value.length; i++) {
				if (this.value[i] - '0' >= 0 && this.value[i] -'0' <= 9) {
					cleanval += this.value[i];
				}
			}

			this.value = cleanval;

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


	this.cancelItem = function(i) {

		if (self.cart[i]) {

			item = self.cart[i];
			var mssg = "Sind Sie sicher, diese Position zu stornieren?\n#" + i + "\nVerkäufer-Nr: " + item.sellerId + "\nBetrag: " + (item.value / 100).toFixed(2) + "EUR";
			console.log(mssg);
			if (!window.confirm(mssg)) {
				return;
			}

			self.cart.splice(i, 1);
		}
		self.createTableFromCart();
		self.codeInput.focus();
	}


	this.addToCart = function(item) {
		self.cart.push(item);
		console.log(self.cart);
		self.createTableFromCart();

		// Play a sound
	}

	this.main = function() {
		console.log("main");
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
		for (var i = 0; i < self.cart.length; i++) {
			var item = self.cart[i];

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

		document.querySelector('.checkout-total').value = (total / 100).toFixed(2) + ' €';
	}


	this.getCartTotal = function() {
		var total = 0;
		self.cart.forEach(function(item) {
			total += item.value;
		});

		return total;
	};
};

var chk = new Checkout();
chk.init();
