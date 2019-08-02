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

		self.setupCameraBarcodeScanner();
		self.setupBarcodeScanner();
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
				alert(err);
				return;
			}
			console.log("Quagga successfully initialised, now starting up");
			Quagga.start();
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
			if (this.value.length == 16) {
				var code = this.value;
				var item = self.getItemFromCode(code);
				self.addToCart(item);

				this.value = '';
				this.focus();
			}
		});
	};

	this.getItemFromCode = function(code) {
		var item = {
			marketId: code.substring(0, 8),
			sellerId: parseInt(code.substring(8, 11)),
			value: parseInt(code.substring(11)),
			ts: Date.now(),
			checkoutId: this.checkoutId
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
