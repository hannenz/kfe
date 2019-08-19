/**
 * Compose sheet functions
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
function Composer() {

	var self = this;
	var inputs; 
	var button;
	var emailInput;
	var sellerIdInput;

	this.init = function() {
		if (document.forms.composeform) {
			self.setup();
		}
	}

	this.setup = function() {

		button = document.querySelector('button[type=submit]');
		inputs = document.querySelectorAll('[name^=amount]');
		self.update();

		for (var i = 0; i < inputs.length; i++) {
			inputs[i].addEventListener('change', function(ev) {
				self.update();
			});
		}

		self.emailInput = document.getElementById('sellerEmail');
		self.sellerIdInput = document.getElementById('sellerId');
		self.emailInput.addEventListener('keyup', self.validateSeller);
		self.sellerIdInput.addEventListener('keyup', self.validateSeller);

		self.validateSeller();
	};
	
	this.update = function() {
		var total = self.calcTotal();
		var pages = parseInt(total / 24);
		if (total % 24 > 0) {
			pages++;
		}

		if (total > 0) {
			button.disabled = false;
			button.innerText = 'PDF erzeugen: ' + total + ' Etiketten auf ' + pages + ' Seiten';
		}
		else {
			button.disabled = true;
			button.innerText = 'Wählen Sie mindestens 1 Etikett aus';
		}
	};

	this.calcTotal = function() {
		var total = 0;
		for (var i = 0; i < inputs.length; i++) {
			var v = parseInt(inputs[i].value);
			if (!isNaN(v)) {
				total += v; //parseInt(inputs[i].value);
			}
		}
		return total;
	};

	this.validateSeller = function() {
		var xhr = new XMLHttpRequest();
		var url = document.forms.composeform.getAttribute('action') + '?action=validateSeller&sellerId=' + self.sellerIdInput.value + '&email=' + self.emailInput.value;
		xhr.open('GET', url, true);
		xhr.onload = function() {
			if (this.status >= 200 && this.status < 400) {
				var resp = JSON.parse(this.response);
				button.disabled = !resp.success;
			}
		}
		xhr.send();
	}
}

var composer = new Composer()
composer.init();