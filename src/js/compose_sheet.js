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
	// var emailInput;
	// var sellerNrInput;
	// var marketInput;
	// var sheetInfo;

	this.init = function() {
		if (document.forms.composeform) {
			self.setup();
		}
	}

	this.setup = function() {

		self.sheetInfo = document.getElementById('sheet-info');
		button = document.querySelector('button[type=submit]');
		inputs = document.querySelectorAll('[name^=amount]');
		self.update();

		document.forms.composeform.addEventListener('input', self.update);

		// self.emailInput = document.getElementById('sellerEmail');
		// self.sellerNrInput = document.getElementById('sellerNr');
		// self.emailInput.addEventListener('keyup', self.validateSeller);
		// self.marketInput = document.getElementById('marketId');
		// self.sellerNrInput.addEventListener('keyup', self.validateSeller);

		// self.validateSeller();
	};
	
	this.update = function() {
		var labelsPerPage = 12;
		var total = self.calcTotal();
		console.log('total', total);
		var pages = parseInt(total / labelsPerPage);
		if (total % labelsPerPage > 0) {
			pages++;
		}

		if (total > 0) {
			button.disabled = false;
			button.innerText = 'PDF erstellen';
			self.sheetInfo.innerHTML = "&rarr; " + total + ' Etiketten auf ' + pages + ' Seiten'
		}
		else {
			button.disabled = true;
			button.innerText = 'Wählen Sie mindestens 1 Etikett aus';
			self.sheetInfo.innerHTML = "&rarr; " + total + ' Etiketten auf ' + pages + ' Seiten'
		}

		var sheets = document.querySelector('.sheets');
		sheets.innerHTML = '';
		var sheet = null;
	
		for (var i = 0, j = 0; i < inputs.length; i++) {

			var input = inputs[i];

			if (input.value == 0) {
				continue;
			}

			for (k = 0; k < parseInt(input.value); k++) {
				var value = 0;
				var label = document.createElement('div');
				label.className = 'label';
				if (input.name.match(/custom/)) {
					var valueInput = input.parentNode.querySelector('input[name^=value]');
					value = parseFloat(valueInput.value.replace(',', '.'));
					console.log(value * 100);
					if (isNaN(value) || ((value * 100) % 50 != 0)) {
						continue;
					}
				}
				else {
					value = parseFloat(input.id.match(/_(\d+)$/)[1]) / 100.0;
				}
				label.innerText = value.toLocaleString('de', { style: 'currency', currency: 'EUR'});

				if (j++ % 12 == 0) {
					sheet = document.createElement('li');
					sheet.className = 'sheet';
					sheets.appendChild(sheet);
					var sheetInner = document.createElement('div');
					sheet.appendChild(sheetInner);
				}
				sheetInner.appendChild(label);
			}
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
		var url = document.forms.composeform.getAttribute('action') + '?action=validateSeller&sellerNr=' + self.sellerNrInput.value + '&email=' + self.emailInput.value + '&marketId=' + self.marketInput.value;
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
