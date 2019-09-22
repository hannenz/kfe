function Registration() {

	var self = this;
	var updateInterval;
	var fields;

	var form;
	var url;

	var selectEl;
	var marketId;

	this.init = function() {
		if (document.forms.registration) {
			self.form = document.forms.registration;
			self.url = self.form.getAttribute('action');
			document.addEventListener('DOMContentLoaded', self.setup);
		}
	};

	this.setup = function() {
		self.updateInterval = 10;
		self.selectEl = document.querySelector('[name=seller_nr]');	
		self.marketId = parseInt(document.querySelector('[name=market_id]').value);
		self.fields = document.querySelectorAll('input[name^=seller_]');
		self.fields.forEach(self.onFieldChange);

		return;
		self.form.addEventListener('submit', function(ev) {
			ev.preventDefault();

			var xhr = new XMLHttpRequest();
			xhr.open('POST', self.url);
			xhr.onload = function() {
				if (this.status < 200 || this.status >= 400) {
					window.alert("Es ist ein Fehler aufgetreten :-/");
					return;
				}
				
			}
			var formData = new FormData(self.form);
			xhr.send(formData);

			return false;


		});

		setInterval(self.updateAvailableSellerNrs, self.updateInterval * 1000);
	}


	this.onFieldChange = function(field) {
		field.addEventListener('change', function() {
			var data = new FormData(self.form);
			data.append('fieldName', field.getAttribute('name'));
			// data.append('fieldValue', this.value);
			data.append('action', 'validateField');

			var xhr = new XMLHttpRequest();
			xhr.open('POST', self.url);
			xhr.onload = function() {
				if (this.status >= 200 && this.status < 400) {
					var data = JSON.parse(this.response);
					field.parentNode.classList.add('ssv');
					field.parentNode.classList.toggle('ssv-valid', data.success);
				}
			}
			xhr.send(data);
		});
	};

	this.updateAvailableSellerNrs = function() {
		
		var xhr = new XMLHttpRequest();
		xhr.open('GET', self.url + '?action=updateAvailableSellerNrs&marketId=' + self.marketId);
		xhr.onload = function() {
			if (this.status >= 200 && this.status < 400) {
				var data = JSON.parse(this.response);
				var numbers = Object.keys(data).map(function(key) { return data[key]; });

				var selectedNr = self.selectEl.querySelector(':checked').value;
				self.selectEl.innerHTML = '';
				for (var i = 0; i < numbers.length; i++) {
					nr = numbers[i];
					var optionEl = document.createElement('option');
					optionEl.setAttribute('value', nr);
					optionEl.innerText = nr;
					self.selectEl.appendChild(optionEl);
					if (nr == selectedNr) {
						optionEl.selected = true;
					}
				}
			}
		}
		xhr.send();
	};
};

var reg = new Registration();
reg.init();
