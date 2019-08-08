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

	this.init = function() {
		if (!document.getElementById('cam')) {
			return;
		}
		console.log("Checkout::init");
		document.addEventListener('DOMContentLoaded', self.setup);
	}

	this.setup = function() {

		console.log("Checkout::setup");

		document.forms.checkout.addEventListener('submit', function(e) {
			e.preventDefault();
			return false;
		});

		var inp = document.querySelector('.checkout-code-input');
		inp.addEventListener('blur', function(ev) {
			inp.focus();
		});

		window.addEventListener('beforeunload', function(event) {
			console.log("About to close the page");
			return 'Seite wirklich verlassen?';
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
	};

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
			console.log("Code has been detected", result.codeResult.code);
			var item = self.getItemFromCode(result.codeResult.code);
			self.addToCart(item);
			
			// throttle somehow?
		});

		self.main();
	}

	this.setupBarcodeScanner = function() {
		var codeInput = document.querySelector('.checkout-code-input');
		codeInput.focus();
		codeInput.addEventListener('keyup', function(ev) {
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
		console.log(marketId,sellerId,value);

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
		var table = document.querySelector('.checkout-table');
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

			table.appendChild(row);
			total += item.value;
		}

		document.querySelector('.checkout-total').value = (total / 100).toFixed(2) + ' €';
	}
};

var chk = new Checkout();
chk.init();
