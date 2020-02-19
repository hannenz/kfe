/**
 * src/js/checkout.js
 *
 * Main checkout javascript
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @version 2019-07-15
 * @package kfe
 */
var Checkout = function() {

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


	this.flag = false;
	this.tmp = null;
};

/**
 * Init
 */
Checkout.prototype.init = function() {
	this.statusMessage("Bereit.");
	this.setup();
};


/**
 * Setup
 */
Checkout.prototype.setup = function() {

	this.setupBarcodeScanner();
	this.setupCameraBarcodeScanner();
	this.setupOnlineOffline();
	this.setupBeforeUnload();

	// TODO: See if we really don't need it, then we can remove this.onKeyUp
	// function as well
	// document.addEventListener('keyup', this.onKeyUp);

	document.forms.checkout.addEventListener('submit', function(ev) {
		ev.preventDefault();
		return false;
	});

	this.setupButtons();
	this.setupManualEntry();

	this.resurrect();
	this.createTableFromCart();
};


Checkout.prototype.setupButtons = function() {
	this.submitCartsBtn.addEventListener('click', this.submitCarts);
	var buttons = document.querySelectorAll('.button-panel > .button');
	buttons.forEach(function(btn) {
		btn.addEventListener('click', this.onPanelButtonClicked.bind(this));
	}.bind(this));
};



/**
 * Warn user when trying to leave page
 */
Checkout.prototype.setupBeforeUnload = function() {
	if (window.location.href.match(/localhost/)) {
		return;
	}

	window.onbeforeunload = function(e) {
		var mssg = 'Seite wirklich verlassen?';
		e.preventDefault();
		(e || window.event).returnValue =  mssg;
		return mssg;
	};
};


/**
 * Setup entry dialog to manually enter cart items 
 */
Checkout.prototype.setupManualEntry = function() {
	this.validSellerNrs = [];
	document.querySelectorAll('#sellers > option').forEach(function(item) {
		this.validSellerNrs.push(parseInt(item.value));
	}.bind(this));

	var sellerNrInput = document.getElementById('manual-entry-seller-nr');
	var valueInput = document.getElementById('manual-entry-value'); 

	document.forms.manual_entry.addEventListener('input', function(ev) {
		var form = this;

		var sellerNr = parseInt(sellerNrInput.value);
		var value = parseInt(valueInput.value);

		var hasErrors = false;
		sellerNrInput.closest('.form-field').classList.remove('form-field--error');
		valueInput.closest('.form-field').classList.remove('form-field--error');

		if (this.validSellerNrs.indexOf(sellerNr) == -1) {
			sellerNrInput.closest('.form-field').classList.add('form-field--error');
			hasErrors = true;
		}

		if (value == 0 || value % 50 != 0) {
			valueInput.closest('.form-field').classList.add('form-field--error');
			hasErrors = true;
		}

		document.forms.manual_entry.querySelector('button[type=submit]').disabled = hasErrors;
	}.bind(this));

	var manualEntryDlg = new Dialog('manual-entry-dlg');
	var manualEntryBtn = document.getElementById('manual-entry-btn');
	manualEntryBtn.addEventListener('click', function() {
		manualEntryDlg.dialog.querySelector('button[type=submit]').disabled = true;
		manualEntryDlg.run()
		.then(function(response) {
			console.log(response);

			switch (response.action) {
				case 'reject':
					break;

				default: 
					var item = new CartItem().newFromValues(this.marketId, this.checkoutId, response.data.get('manual_entry_seller_nr'), response.data.get('manual_entry_value'));
					this.cart.addItem(item);
					this.createTableFromCart();
					this.persist();
			}
			response.dialog.close();
			this.codeInput.focus();
			manualEntryBtn.disabled = false;
		}.bind(this));
	}.bind(this));
};



Checkout.prototype.onKeyUp = function(ev) {
	ev.preventDefault();
	ev.stopPropagation();

	switch (ev.keyCode) {

		case 8: // Backspace
			break;
		case 13: // Return
			// this.actionCommit();
			break;
		case 27: // Escape
			break;
		case 65: // Q
			this.calcChange(500);
			break;
		case 83: // W
			this.calcChange(1000);
			break;
		case 68: // E
			this.calcChange(2000);
			break;
		case 70: // R
			this.calcChange(5000);
			break;
		case 71: // T
			this.calcChange(10000);
			break;
		case 72: // Y
			this.calcChange(20000);
			break;
		case 74: // U
			break;
		case 61:
			this.submitCarts();
			break;
		case 999:
			this.commitCart();
			// this.cancelCart();
			this.createTableFromCart();
			this.updateTotalTurnover();
			break;
	}
};



Checkout.prototype.onPanelButtonClicked = function(ev) {

	ev.preventDefault();
	var btn = ev.target;
	var action = btn.dataset.action;

	switch (action) {
		case 'change':
			this.calcChange(btn.dataset.value);
			this.codeInput.focus();
			break;

		case 'change-custom':

			var changeCustomValue = document.getElementById('change-custom-value');
			new Dialog('change-custom-dlg')
			.run()
			.then(function(response) {
				if (response.action != 'reject') {
					var value = response.data.get('change_custom_value');
					value = parseInt(value.replace(/[^\d]/g, ''));
					this.calcChange(isNaN(value) ? 0 : value);
				}
				response.dialog.close();
				this.codeInput.focus();
			}.bind(this));
			break;

		case 'cancel-last':
			this.actionCancelLast();
			break;

		case 'cancel':
			this.actionCancel();
			break;

		case 'commit':
			this.actionCommit();
			break;

		case 'show-last':
			if (this.flag = !this.flag) {
				this.tmp = this.cart.getData();
				this.cart.clear();
				this.cart.setData(this.cue.getLast());
				this.createTableFromCart();
				btn.innerText = 'return';
			}
			else {
				this.cart.clear();
				this.cart.setData(this.tmp);
				this.createTableFromCart();
				btn.innerText = 'Show last';
			}
			this.codeInput.focus();
			break;

		case 'edit-cart':

			if (!this.flag) {
				var dlg = new Dialog('edit-cart-dlg');
				var html = '<select name="id">';
				this.cue.carts.slice().reverse().forEach(cartData => {
					var cart = new Cart().setData(cartData);
					var total = (cart.getTotal() / 100).toLocaleString('de', { style: 'currency', currency: 'EUR' });
					var n = cart.items.length;
					var datetime = new Date(cart.timestamp).toLocaleString();
					if (!isNaN(cart.id)) {
						html += `<option value="${cart.id}">[#${cart.id}] ${datetime}: ${n} Positionen: ${total}</option>`
					}
				});
				html += '</select>';
				dlg.setBody(html);
				dlg.run().then(function(response) {
					var id = response.data.get('id');
					if (response.action != 'reject') {
						this.flag = true;
						document.body.classList.add('is-editing-old-cart');
						this.tmp = this.cart.getData();
						this.cart.clear();
						this.cart.setData(this.cue.getCartById(id));
						this.createTableFromCart();
						btn.innerText = 'return';
					}
					response.dialog.close();
				}.bind(this));
			}
			else {
				this.cart.clear();
				this.cart.setData(this.tmp);
				this.createTableFromCart();
				btn.innerText = 'Edit';
				this.flag = false;
				document.body.classList.remove('is-editing-old-cart');
			}

			this.codeInput.focus();
			break;

		default:
			console.log("Unknown action: " + action);
			break;
	}

	return false;
};


Checkout.prototype.actionCancel = function() {
	if (this.cart.items.length == 0) {
		return;
	}

	new Dialog('cancel-cart-dlg')
	.run()
	.then(function(response) {
		if (response.action != 'reject') {
			this.cart.clear();
			this.persist();
			this.createTableFromCart();
		}
		response.dialog.close();
		this.codeInput.focus();
	}.bind(this));
};


Checkout.prototype.actionCancelLast = function() {
	if (this.cart.items.length == 0) {
		return;
	}

	var i;
	if ((i = this.cart.items.length - 1) >= 0) {
		this.cancelItem(i);
		this.createTableFromCart();
	}
};


Checkout.prototype.actionCommit = function() {
	this.commitCart();
	this.cart.clear();
	this.createTableFromCart();
	this.updateTotalTurnover();
	this.codeInput.focus();
};


	/**
	 * Calc and display change money for a given value
	 *
	 * @param int value
	 * @return void
	 */
Checkout.prototype.calcChange = function(value) {
	this.changeInput.value = ((value - (this.cart.getTotal())) / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
};


Checkout.prototype.setupCameraBarcodeScanner = function() {
	// If camera is available, setup camera barcode scanner
	navigator.getMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
	navigator.getMedia({video: true}, function() {
		console.log("We have a camera, so go ahead!");
		this.cameraBarcodeScanner = new CameraBarcodeScanner();
		this.cameraBarcodeScanner.setup(document.getElementById('cam'),  function(result) {
			var item = new CartItem().newFromCode(result.codeResult.code, this.checkoutId);
			this.cart.addItem(item);
			this.persist();
		}.bind(this));
	}, function() {
		console.log("No camera available, never mind …");
	});
};


Checkout.prototype.setupBarcodeScanner = function() {

	this.codeInput.focus();
	this.codeInput.addEventListener('keydown', function(ev) {
		if (ev.keyCode == 13) {
			ev.preventDefault();
			return false;
		}
		return true;
	}.bind(this));

	this.codeInput.addEventListener('input', function(ev) {

		var value = ev.target.value;
		value = value.replace(/[^\d]/g, ''); 

		if (value.length >= 24) {
			var code = value;
			var item = new CartItem().newFromCode(code, this.checkoutId);
			if (item != null) {
				this.cart.addItem(item);
				this.createTableFromCart();
				this.persist();
			}
			else {
				alert ("Invalid code: " + code);
			}

			ev.target.value = '';
			ev.target.focus();
		}
	}.bind(this));
};



Checkout.prototype.setupOnlineOffline = function() {
	window.addEventListener('online', function() {
		document.body.classList.add('is-online');
		window.setTimeout(this.submitCarts, 3000);
	}.bind(this));

	window.addEventListener('offline', function() {
		document.body.classList.remove('is-online');
	});
	document.body.classList.toggle('is-online', window.onLine);
};



Checkout.prototype.cancelItem = function(i) {

	if (this.cart.items[i]) {

		item = this.cart.items[i];
		var valueFmt = (item.value / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });

		var dlg = new Dialog('cancel-item-dlg');
		dlg.setBody(`<p>Soll die Position #${i + 1} wirklich storniert werden?</p>` + 
		`<table><tr><td>Verkäufer-Nr:</td><td><b>${item.sellerNr}</b></td></tr>` + 
		`<tr><td>Betrag:</td><td><b>${valueFmt}</b></td></tr></table>`);
		dlg.run()
		.then(function(response) {
			if (response.action != 'reject') {
				this.cart.removeItem(i);
				this.createTableFromCart();
				this.codeInput.focus();
				this.persist();
			}
			dlg.close();
			this.codeInput.focus();
		}.bind(this));
	}
};


Checkout.prototype.createTableFromCart = function() {
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
	for (var i = 0; i < this.cart.items.length; i++) {
		var item = this.cart.items[i];

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
			var tr = ev.target.parentNode.parentNode;
			var children = tr.parentNode.childNodes;
			for (n = 0; n < children.length; n++) {
				if (children[n] == tr) {
					break;
				}
			}

			if (n > 0) {
				// console.log("Cancel button has been clicked");
				this.cancelItem(n - 1);
			}
		}.bind(this));

		table.appendChild(row);
		total += item.value;
	}

	this.totalInput.value = (total / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
	this.changeInput.value = '-,-- €';

	if (this.cart.items.length == 0) {
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
};


/**
 * Committing a cart means finishing it and add it to the cue / list of
 * carts. If we are online the cart is also submitted to the server
 */
Checkout.prototype.commitCart = function() {

	if (this.cart.items.length == 0) {
		console.log("Cart is empty, aborting commit");
		return;
	}

	this.cue.addCart(this.cart);

	if (navigator.onLine) {
		this.statusMessage("Bon wird übermittelt");
		this.submitCart(this.cart);
	}

	this.cart.clear();
	this.updateTotalTurnover();
	this.persist();
};


Checkout.prototype.updateTotalTurnover = function() {
	var totalTurnover = this.cue.calcTotalTurnover() / 100;
	document.getElementById('js-total-turnover').innerText = totalTurnover.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
	document.getElementById('js-total-carts').innerText = this.cue.getLength();
	document.getElementById('js-total-carts-submitted').innerText = this.cue.countSubmittedCarts();
};


/**
 * Save the current cart and cart cue to localStorage
 */
Checkout.prototype.persist = function() {
	// window.localStorage.setItem('checkoutId', this.checkoutId);
	// window.localStorage.setItem('marketId', this.marketId);
	window.localStorage.setItem('cart', JSON.stringify(this.cart.getData()));
	window.localStorage.setItem('carts', JSON.stringify(this.cue.carts));
};


/**
 * Restore current cart and cart cue from localStorage
 */
Checkout.prototype.resurrect = function() {

	if ((cart = window.localStorage.getItem('cart')) != null) {
		this.cart = new Cart().setData(JSON.parse(cart));
		// this.createTableFromCart();
	}

	this.cue.clear();
	if ((carts = window.localStorage.getItem('carts')) != null) {
		JSON.parse(carts).forEach(function(cartData) {
			this.cue.addCart(new Cart().setData(cartData));
		}.bind(this));
		this.updateTotalTurnover();
	}
};




/**
 * Submit a cart to the server
 *
 * @param Array 	The carts data
 * @return void
 */
Checkout.prototype.submitCart = function(cart) {

	document.body.classList.add('is-busy');

	cart.submit().then(function(response) {

		document.body.classList.remove('is-busy');
		this.statusMessage('Bon wurde erfolgreich übermittelt, ID: ' + response.cartId, 'success');

		if (this.cue.markSubmitted(response.cartId, response.cartTimestamp, response.cartCheckoutId)) {
			this.updateTotalTurnover();
			this.persist();
		}
	}.bind(this))
	.catch(function(mssg) {
		this.statusMessage(mssg, 'error');
		new Logger().log('Submitting cart failed: ' + mssg + ', data: ' + JSON.stringify(cart.getData()));
	}.bind(this));
};



Checkout.prototype.statusMessage = function(mssg, type) {
	if (!type) {
		type = 'info';
	}
	var el = document.getElementById('statusbar-message');
	el.innerHTML = mssg;
	el.classList.add(type);
};
