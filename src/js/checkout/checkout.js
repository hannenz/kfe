/**
 * src/js/checkout.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @version 2019-07-15
 * @package kfe
 */

function Checkout() {

	var self = this;

	this.marketId = document.getElementById('marketId').value;
	this.checkoutId = document.getElementById('checkoutId').value;
	this.cashierId = document.getElementById('cashierId').value;

	// This is the carts cue
	this.carts = [];

	// This is a single cart
	this.cart = Object.create(Cart);
	this.cart.init(this.marketId, this.checkoutId, this.cashierId);
	this.cart.clear();

	this.totalInput = document.getElementById('checkout-total');
	this.changeInput = document.getElementById('checkout-change-value');
	this.codeInput = document.getElementById('checkout-code-input');
	this.cue = document.getElementById('js-cue');
	this.cueLabel = document.getElementById('js-cue-label');
	this.submitCartsBtn = document.getElementById('submit-carts-btn');

	/**
	 * Init
	 */
	this.init = function() {
		self.statusMessage("Bereit.");
		self.setup();
	};


	/**
	 * Setup
	 */
	this.setup = function() {

		// If camera is available, setup camera barcode scanner
		navigator.getMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
		navigator.getMedia({video: true}, function() {
			console.log("We have a camera, so go ahead!");
			this.cameraBarcodeScanner = new CameraBarcodeScanner();
			this.cameraBarcodeScanner.setup(document.getElementById('cam'),  function(result) {
				var item = self.getItemFromCode(result.codeResult.code);
				self.cart.addItem(item);
				self.persist();
			});
		}, function() {
			console.log("No camera available, never mind …");
		});


		window.addEventListener('online', function() {
			document.body.classList.add('is-online');
			window.setTimeout(self.submitCarts, 3000);
		});

		window.addEventListener('offline', function() {
			document.body.classList.remove('is-online');
		});
		document.body.classList.toggle('is-online', window.onLine);

		document.addEventListener('keyup', self.onKeyUp);

		console.log("Checkout::setup");

		window.onbeforeunload = function(e) {
			var mssg = 'Seite wirklich verlassen?';
			e.preventDefault();
			(e || window.event).returnValue =  mssg;
			return mssg;
		};

		document.forms.checkout.addEventListener('submit', function(e) {
			e.preventDefault();
			return false;
		});

		self.codeInput.addEventListener('blur', function(ev) {
			if (self.hasDialog) {
				setTimeout(function() {
					self.codeInput.focus();
				}, 10);
			}
		});

		self.setupBarcodeScanner();

		self.camDiv = document.getElementById('cam');

		var chkbx = document.getElementById('js-toggle-camera-scanner');
		if (chkbx) {
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
		}


		var buttons = document.querySelectorAll('.button-panel > .button');
		for (var i = 0; i < buttons.length; i++) {
			var btn = buttons[i];
			btn.addEventListener('click', self.onPanelButtonClicked);
		}

		this.submitCartsBtn.addEventListener('click', this.submitCarts);

		// Periodically try to submit carts to server
		// window.setInterval(self.submitCarts, 5000);

		self.resurrect();
		self.createTableFromCart();




		// Validate manual entry form
		document.forms.manual_entry.addEventListener('submit', function(ev) {
			var sellerNrInput = document.getElementById('manual-entry-seller-nr');
			var valueInput = document.getElementById('manual-entry-value'); 
			var sellerNr = sellerNrInput.value;
			var value = parseInt(valueInput.value);

			var hasErrors = false;
			sellerNrInput.closest('.form-field').classList.remove('form-field--error');
			valueInput.closest('.form-field').classList.remove('form-field--error');

			if (value == 0 || value % 50 != 0) {
				valueInput.closest('.form-field').classList.add('form-field--error');
				hasErrors = true;
			}


			// Validate server-side
			var sid = window.location.search.match(/sid=([a-z0-9]+)/)[1];
			var url = '/admin/cmt_applauncher.php?sid=' + sid + '&launch=149&action=validateManualEntry&sellerNr=' + sellerNr + '&marketId=' + self.marketId;;

			fetch(url)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						if (!hasErrors) {
							var item = new CartItem().newFromValues(self.marketId, self.checkoutId, sellerNr, value);
							self.cart.addItem(item);
							self.createTableFromCart();
							self.persist();
							manualEntryBtn.disabled = false;
							self.codeInput.focus();
							manualEntryDlg.close();
							self.codeInput.focus();
						}
					}
					else {
						sellerNrInput.closest('.form-field').classList.add('form-field--error');
					}
				})
				.catch(error => {
					alert(error);
				});

			console.log(!hasErrors);
			return (!hasErrors);
		});

		var manualEntryDlg = new Dialog('manual-entry-dlg');
		var manualEntryBtn = document.getElementById('manual-entry-btn');
		manualEntryBtn.addEventListener('click', function() {

			manualEntryDlg.run()
			.then(function(response) {

				switch (response.action) {
					case 'reject':
						manualEntryDlg.close();
						break;

					default: 
				}
			});
		});
	};


	this.onKeyUp = function(ev) {
		ev.preventDefault();
		ev.stopPropagation();

		// console.log(ev.keyCode);

		switch (ev.keyCode) {

			case 8: // Backspace
				// if (ev.target.id != 'manual-entry-seller-nr' && ev.target.id != 'manual-entry-value') {
				// 	self.actionCancelLast();
				// }
				break;

			case 13: // Return
				// self.actionCommit();
				break;

			case 27: // Escape
				// self.closeDialog();
				break;

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
		// console.log(action);
		switch (action) {
			case 'change':
				self.change(this.dataset.value);
				break;

			case 'change-custom':

				var dlg = document.getElementById('change-custom-dlg');
				var form = document.forms.change_custom;

				form.reset();
				form.querySelector('input').focus();
				dialogPolyfill.registerDialog(dlg);
				dlg.showModal();

				var changeCustomValue = document.getElementById('change-custom-value');

				form.addEventListener('submit', function(ev) {
					ev.preventDefault();

					dlg.close();
					self.codeInput.focus();

					var value = changeCustomValue.value.replace(/[^\d]/g, '');
					self.change(parseInt(value));

					return false;
				});

				break;

			case 'cancel-last':
				self.actionCancelLast();
				break;

			case 'cancel':
				self.actionCancel();
				break;

			case 'commit':
				self.actionCommit();
				break;

			default:
				console.log("Unknown action: " + action);
				break;
		}

		self.codeInput.focus();
		return false;
	};


	this.actionCancel = function() {
		if (self.cart.items.length == 0) {
			return;
		}

		new Dialog('cancel-cart-dlg')
		.run()
		.then(function(response) {
			if (response.action != 'reject') {
				self.cart.clear();
				self.persist();
				self.createTableFromCart();
			}
			response.dlg.close();
			self.codeInput.focus();
		});
	}

	this.actionCommit = function() {
		self.commitCart();
		self.cart.clear();
		self.createTableFromCart();
		self.updateTotalTurnover();
	}

	this.actionCancelLast = function() {
		if (self.cart.items.length == 0) {
			return;
		}

		var i;
		if ((i = self.cart.items.length - 1) >= 0) {
			self.cancelItem(i);
			self.createTableFromCart();
		}
	}



	/**
	 * Calc and display change money for a given value
	 *
	 * @param int value
	 * @return void
	 */
	this.change = function(value) {
		self.changeInput.value = ((value - (self.cart.getTotal())) / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
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

			if (this.value.length >= 24) {
				var code = this.value;
				var item = self.getItemFromCode(code);
				if (item != null) {
					self.cart.addItem(item);
					self.createTableFromCart();
					self.persist();
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

		var item = {
			marketId: marketId,
			sellerId: sellerId,
			sellerNr: sellerNr,
			value: value,
			ts: Date.now(),
			checkoutId: this.checkoutId,
			code: code
		}
		return item;
	}


	this.cancelLast = function() {
	};


	this.cancelItem = function(i) {

		if (self.cart.items[i]) {

			item = self.cart.items[i];
			var valueFmt = sprintf('%.2f', item.value / 100);

			var dlg = new Dialog('cancel-item-dlg');
			dlg.setBody(`Soll die Position #${i} wirklich storniert werden?<br>` + 
			`Verkäufer-Nr: ${item.sellerNr}<br>` + 
			`Betrag: ${valueFmt} &euro;`);
			dlg.run()
			.then(function(response) {
				if (response.action != 'reject') {
					self.cart.removeItem(i);
					self.createTableFromCart();
					self.codeInput.focus();
					self.persist();
				}
				dlg.close();
				self.codeInput.focus();
			});
		}
	}


	// this.addToCart = function(item) {
	// 	self.cart.addItem(item);
	// 	self.createTableFromCart();
	// 	self.persist();
	// 	// Play a sound
	// }

	this.createTableFromCart = function() {
		var table = document.getElementById('js-cart');
		table.innerHTML = '';

		var row = document.createElement('tr');
		var th = document.createElement('th');
		th.innerText = '#';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Code';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Verkäufer-ID';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Verkäufer-Nr';
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = 'Betrag';
		th.classList.add('currency');
		row.appendChild(th);
		th = document.createElement('th');
		th.innerText = '';
		row.appendChild(th);

		table.appendChild(row);


		var total = 0;
		for (var i = 0; i < self.cart.items.length; i++) {
			var item = self.cart.items[i];

			var row = document.createElement('tr');
			var td = document.createElement('td');
			td.innerText = (i + 1);
			row.appendChild(td);
			td = document.createElement('td');
			td.innerText = item.code;
			row.appendChild(td);
			td = document.createElement('td');
			td.innerText = item.sellerId;
			row.appendChild(td);
			td = document.createElement('td');
			var sellerNrTxt = item.sellerNr;
			if (item.sellerNr < 10) {
				sellerNrTxt = '00' + item.sellerNr;
			}
			else if (item.sellerNr < 100) {
				sellerNrTxt = '0' + item.sellerNr;
			}
			td.innerText = sellerNrTxt;
			row.appendChild(td);

			td = document.createElement('td');
			td.innerText = (item.value / 100).toLocaleString('de-DE', {style: 'currency', currency: 'EUR'});
			td.classList.add('currency');
			row.appendChild(td);

			td = document.createElement('td');
			td.classList.add('action');
			var btn = document.createElement('a');
			td.appendChild(btn);
			row.appendChild(td);

			btn.innerHTML = '&times; stornieren';
			btn.className = 'cancel-link';
			btn.addEventListener('click', function(ev) {
				var tr = this.parentNode.parentNode;
				var children = tr.parentNode.childNodes;
				for (n = 0; n < children.length; n++) {
					if (children[n] == tr) {
						break;
					}
				}

				if (n > 0) {
					// console.log("Cancel button has been clicked");
					self.cancelItem(n - 1);
				}
			});

			table.appendChild(row);
			total += item.value;
		}

		self.totalInput.value = (total / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
		self.changeInput.value = '-,-- €';

		if (self.cart.items.length == 0) {
			document.querySelector('[data-action=cancel]').setAttribute('disabled', true);
			document.querySelector('[data-action=cancel-last]').setAttribute('disabled', true);
			document.querySelector('[data-action=commit]').setAttribute('disabled', true);
		}
		else {
			document.querySelector('[data-action=cancel]').removeAttribute('disabled');
			document.querySelector('[data-action=cancel-last]').removeAttribute('disabled');
			document.querySelector('[data-action=commit]').removeAttribute('disabled');
		}



		document.querySelectorAll('[data-action^=change]').forEach(function(btn) {
			if (btn.dataset.value < total || total == 0) {
				btn.setAttribute('disabled', true);
			}
			else {
				btn.removeAttribute('disabled');
			}
		});


	}


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
		// var clone = JSON.parse(JSON.stringify(self.cart));
		var cartData = self.cart.getData();

		self.carts.push(cartData);

		if (navigator.onLine) {
			self.statusMessage("Bon wird übermittelt");
			self.submitCart(cartData);
		}

		self.cart.clear();

		self.updateTotalTurnover();
		self.persist();
	};



	this.updateTotalTurnover = function() {
		var totalTurnover = self.calcTotalTurnover() / 100;
		document.getElementById('js-total-turnover').innerText = totalTurnover.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
		document.getElementById('js-total-carts').innerText = self.carts.length;
		document.getElementById('js-total-carts-submitted').innerText = self.countSubmittedCarts();
	};


	this.countSubmittedCarts = function() {
		var n = 0;
		this.carts.forEach(function(cart) {
			if (cart.submitted) {
				n++;
			}
		});
		return n;
	}



	/**
	 * This will delete the current cart and reset, e.g.
	 * preapre for a new one
	 */
	this.cancelCart = function() {
		// console.log("Cancelling cart");
		self.cart.timestamp = Date.now();
		self.cart.marketId = self.marketId;
		self.cart.checkoutId = self.checkoutId;
		self.cart.cashierId = self.cashierId;
		self.cart.items = [];
		self.cart.submitted = false;
		self.cart.submittedTimestamp = null;

		self.persist();
	}



	/**
	 * Save the current cart and cart cue to localStorage
	 */
	this.persist = function() {
		// window.localStorage.setItem('checkoutId', self.checkoutId);
		// window.localStorage.setItem('marketId', self.marketId);
		window.localStorage.setItem('cart', JSON.stringify(self.cart.getData()));
		window.localStorage.setItem('carts', JSON.stringify(self.carts));
	};



	/**
	 * Restore current cart and cart cue from localStorage
	 */
	this.resurrect = function() {
		// var checkoutId, marketId, cart;

		if ((cart = window.localStorage.getItem('cart')) != null) {
			self.cart.setData(JSON.parse(cart));
			// self.createTableFromCart();
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
		console.log(cart.items);

		var data = new FormData();
		data.append('action',  'add');
		data.append('marketId', cart.marketId);
		data.append('checkoutId', cart.checkoutId);
		data.append('cashierId', cart.cashierId);
		data.append('timestamp', cart.timestamp);
		data.append('items', JSON.stringify(cart.items));
		var total = 0;
		cart.items.forEach(function(item) {
			total += item.value;
		});
		data.append('total', total);

		var xhr = new XMLHttpRequest();
		xhr.addEventListener('load', function() {
			var response = JSON.parse(this.responseText);
			if (response.success) {
				// cart.submitted = true;
				// console.log(response.cartId);
				self.statusMessage('Bon wurde erfolgreich übermittelt, ID: ' + response.cartId, 'success');
				document.body.classList.remove('is-busy');

				// Try to find this cart in the cue and if found, remove it
				// self.carts.forEach(function(cueCart, i) {
				console.log(response.cartTimestamp);
				for (var i = 0; i < self.carts.length; i++) {
					if (parseInt(self.carts[i].timestamp) == parseInt(response.cartTimestamp) &&
						parseInt(self.checkoutId) == parseInt(response.cartCheckoutId)) {
						console.log("Found cart in cue, un-cueing it!", i);
						self.carts[i].submitted = true;
						self.carts[i].submittedTimestamp = new Date();
						self.carts[i].id = response.cartId

						self.updateTotalTurnover();

						// self.carts.splice(i, 1);
						self.persist();
						break;
					}
				}
			}

		});
		xhr.addEventListener('error', function() {
			self.statusMessage('Übermittlung gescheitert!', 'error');

		});
		xhr.open('POST', '/de/9/carts.html');
		xhr.send(data);

		document.body.classList.add('is-busy');
	};


	this.showCarts = function() {
		console.log("Showing carts");
		var containerEl = document.createElement('ul');

		self.carts.forEach(function(cart) {
			console.log(cart.timestamp);
			var itemEl = document.createElement('li');

		});
	};


	this.statusMessage = function(mssg, type) {
		if (!type) {
			type = 'info';
		}
		var el = document.getElementById('statusbar-message');
		el.innerHTML = mssg;
		el.classList.add(type);
	};


	this.updateCue = function() {

		self.cueLabel.innerText = self.carts.length + " Vorgänge in der Warteschlange";
		
	}
};
