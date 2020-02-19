/**
 * src/js/checkout.js
 *
 * Main checkout javascript
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
	this.cue = new CartsCue();

	// This is a single cart
	this.cart = new Cart(this.marketId, this.checkoutId, this.cashierId);
	this.cart.clear();

	this.totalInput = document.getElementById('checkout-total');
	this.changeInput = document.getElementById('checkout-change-value');
	this.codeInput = document.getElementById('checkout-code-input');
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

		this.setupBarcodeScanner();
		this.setupCameraBarcodeScanner();
		this.setupOnlineOffline();
		this.setupBeforeUnload();

		// TODO: See if we really don't need it, then we can remove self.onKeyUp
		// function as well
		// document.addEventListener('keyup', self.onKeyUp);

		document.forms.checkout.addEventListener('submit', function(ev) {
			ev.preventDefault();
			return false;
		});

		this.setupButtons();
		this.setupManualEntry();

		self.resurrect();
		self.createTableFromCart();
	};


	this.setupButtons = function() {
		this.submitCartsBtn.addEventListener('click', this.submitCarts);
		var buttons = document.querySelectorAll('.button-panel > .button');
		buttons.forEach(function(btn) {
			btn.addEventListener('click', self.onPanelButtonClicked);
		});
	};



	/**
	 * Warn user when trying to leave page
	 */
	this.setupBeforeUnload = function() {
		if (!window.location.href.match(/localhost/)) {
			window.onbeforeunload = function(e) {
				var mssg = 'Seite wirklich verlassen?';
				e.preventDefault();
				(e || window.event).returnValue =  mssg;
				return mssg;
			};
		}
	};


	/**
	 * Setup entry dialog to manually enter cart items 
	 */
	this.setupManualEntry = function() {
		self.validSellerNrs = [];
		document.querySelectorAll('#sellers > option').forEach(function(item) {
			self.validSellerNrs.push(parseInt(item.value));
		});

		var sellerNrInput = document.getElementById('manual-entry-seller-nr');
		var valueInput = document.getElementById('manual-entry-value'); 

		document.forms.manual_entry.addEventListener('input', function(ev) {
			var form = this;

			var sellerNr = parseInt(sellerNrInput.value);
			var value = parseInt(valueInput.value);

			var hasErrors = false;
			sellerNrInput.closest('.form-field').classList.remove('form-field--error');
			valueInput.closest('.form-field').classList.remove('form-field--error');

			if (self.validSellerNrs.indexOf(sellerNr) == -1) {
				sellerNrInput.closest('.form-field').classList.add('form-field--error');
				hasErrors = true;
			}

			if (value == 0 || value % 50 != 0) {
				valueInput.closest('.form-field').classList.add('form-field--error');
				hasErrors = true;
			}

			this.querySelector('button[type=submit]').disabled = hasErrors;
		});

		var manualEntryDlg = new Dialog('manual-entry-dlg');
		var manualEntryBtn = document.getElementById('manual-entry-btn');
		manualEntryBtn.addEventListener('click', function() {
			manualEntryDlg.dialog.querySelector('button[type=submit]').disabled = true;
			manualEntryDlg.run()
			.then(function(response) {

				switch (response.action) {
					case 'reject':
						break;

					default: 
						var item = new CartItem().newFromValues(self.marketId, self.checkoutId, response.data.get('manual_entry_seller_nr'), response.data.get('manual_entry_value'));
						self.cart.addItem(item);
						self.createTableFromCart();
						self.persist();
				}
				response.dialog.close();
				self.codeInput.focus();
				manualEntryBtn.disabled = false;
			});
		});
	};


	this.onKeyUp = function(ev) {
		ev.preventDefault();
		ev.stopPropagation();


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

			case 999:
				self.commitCart();
				// self.cancelCart();
				self.createTableFromCart();
				self.updateTotalTurnover();
				break;
		}
	};



	this.onPanelButtonClicked = function(ev) {
		ev.preventDefault();
		var action = this.dataset.action;
		switch (action) {
			case 'change':
				self.change(this.dataset.value);
				break;

			case 'change-custom':

				var changeCustomValue = document.getElementById('change-custom-value');
				new Dialog('change-custom-dlg')
				.run()
				.then(function(response) {
					if (response.action != 'reject') {
						var value = response.data.get('change_custom_value');
						value = parseInt(value.replace(/[^\d]/g, ''));
						self.change(isNaN(value) ? 0 : value);
					}
					response.dialog.close();
					self.codeInput.focus();
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
			response.dialog.close();
			self.codeInput.focus();
		});
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


	this.actionCommit = function() {
		self.commitCart();
		self.cart.clear();
		self.createTableFromCart();
		self.updateTotalTurnover();
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


	this.setupCameraBarcodeScanner = function() {
		// If camera is available, setup camera barcode scanner
		navigator.getMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
		navigator.getMedia({video: true}, function() {
			console.log("We have a camera, so go ahead!");
			this.cameraBarcodeScanner = new CameraBarcodeScanner();
			this.cameraBarcodeScanner.setup(document.getElementById('cam'),  function(result) {
				var item = new CartItem().newFromCode(result.codeResult.code, self.checkoutId);
				self.cart.addItem(item);
				self.persist();
			});
		}, function() {
			console.log("No camera available, never mind …");
		});

		// var chkbx = document.getElementById('js-toggle-camera-scanner');
		// if (chkbx) {
		// 	chkbx.addEventListener('change', function(e) {
        //
		// 		if (this.checked) {
		// 			console.log("Starting camera scanner");
		// 			self.cameraBarcodeScanner.turnOn();
		// 		}
		// 		else {
		// 			console.log("Stopping camera scanner");
		// 			self.cameraBarcodeScanner.turnOff();
		// 		}
		// 	});
		// }
	};


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
				var item = new CartItem().newFromCode(code, self.checkoutId);
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



	this.setupOnlineOffline = function() {
		window.addEventListener('online', function() {
			document.body.classList.add('is-online');
			window.setTimeout(self.submitCarts, 3000);
		});

		window.addEventListener('offline', function() {
			document.body.classList.remove('is-online');
		});
		document.body.classList.toggle('is-online', window.onLine);
	};



	this.cancelItem = function(i) {

		if (self.cart.items[i]) {

			item = self.cart.items[i];
			var valueFmt = (item.value / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });

			var dlg = new Dialog('cancel-item-dlg');
			dlg.setBody(`<p>Soll die Position #${i + 1} wirklich storniert werden?</p>` + 
			`<table><tr><td>Verkäufer-Nr:</td><td><b>${item.sellerNr}</b></td></tr>` + 
			`<tr><td>Betrag:</td><td><b>${valueFmt}</b></td></tr></table>`);
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
	};


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

		if (self.cart.items.length == 0) {
			console.log("Cart is empty, aborting commit");
			return;
		}

		self.cue.addCart(self.cart);

		if (navigator.onLine) {
			self.statusMessage("Bon wird übermittelt");
			self.submitCart(self.cart);
		}

		self.cart.clear();
		self.updateTotalTurnover();
		self.persist();
	};


	this.updateTotalTurnover = function() {
		var totalTurnover = self.cue.calcTotalTurnover() / 100;
		document.getElementById('js-total-turnover').innerText = totalTurnover.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
		document.getElementById('js-total-carts').innerText = self.cue.getLength();
		document.getElementById('js-total-carts-submitted').innerText = self.cue.countSubmittedCarts();
	};


	/**
	 * Save the current cart and cart cue to localStorage
	 */
	this.persist = function() {
		// window.localStorage.setItem('checkoutId', self.checkoutId);
		// window.localStorage.setItem('marketId', self.marketId);
		window.localStorage.setItem('cart', JSON.stringify(self.cart.getData()));
		window.localStorage.setItem('carts', JSON.stringify(self.cue.carts));
	};


	/**
	 * Restore current cart and cart cue from localStorage
	 */
	this.resurrect = function() {

		if ((cart = window.localStorage.getItem('cart')) != null) {
			self.cart = new Cart().setData(JSON.parse(cart));
			// self.createTableFromCart();
		}

		self.cue.clear();
		if ((carts = window.localStorage.getItem('carts')) != null) {
			JSON.parse(carts).forEach(function(cartData) {
				self.cue.addCart(new Cart().setData(cartData));
			});
			self.updateTotalTurnover();
		}
	};




	/**
	 * Submit a cart to the server
	 *
	 * @param Array 	The carts data
	 * @return void
	 */
	this.submitCart = function(cart) {

		document.body.classList.add('is-busy');

		cart.submit().then(function(response) {

			document.body.classList.remove('is-busy');
			self.statusMessage('Bon wurde erfolgreich übermittelt, ID: ' + response.cartId, 'success');

			if (self.cue.markSubmitted(response.cartId, response.cartTimestamp, response.cartCheckoutId)) {
				self.updateTotalTurnover();
				self.persist();
			}
		})
		.catch(function(mssg) {
			self.statusMessage(mssg, 'error');
			new Logger().log('Submitting cart failed: ' + mssg + ', data: ' + JSON.stringify(cart.getData()));
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
};
