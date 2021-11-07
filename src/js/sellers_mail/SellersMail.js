/**
 * src/js/sellers_mail/SellersMail.js
 *
 * @author Johannes Braun <hannenz@posteo.de>
 * @package kfe
 * @version 2021-11-06
 */

class SellersMail {

	constructor() {
		this.baseUrl = document.forms.sellersMailForm.getAttribute('action');
		this.form = document.forms.sellersMailForm;
		this.formButton = document.getElementById('form-button');
		this.progressbar = document.getElementById('js-progressbar');
		this.progresslabel = document.querySelector('[for=js-progressbar]');
		this.isBusy = false;

		this.updateFormButton();
		this.form.addEventListener('change', this.updateFormButton);
		this.form.addEventListener('submit', function(ev) {
			ev.preventDefault();
			let formData = new URLSearchParams(new FormData(this.form));
			// 1. Get it running by "fetching the forms target" (submit post via AJAX
			this.formButton.disabled = true;
			this.isBusy = true;
			this.form.classList.add('is-busy');
			fetch(this.baseUrl, {
				method: 'POST',
				body: formData
			}).then(() => {
				// 2. When done, Connect to SSE to receive updates / progress
				let src = new EventSource(this.baseUrl + '&action=sendLoop');
				src.addEventListener('error', () => console.error('Event Stream Error'));
				src.addEventListener('mailSent', this.onMailSent.bind(this));
			});
		}.bind(this));

	}

	updateFormButton(e) {
		let n = this.form.querySelectorAll('[name="id[]"]:checked').length;
		this.formButton.querySelector('span').innerText = `An ${n} Empfänger senden`;
	}

	onMailSent(event) {
		let data = JSON.parse(event.data),
			iter = parseInt(data.iter),
			total = parseInt(data.total),
			value = iter / total;

		this.progressbar.value = value;
		this.progresslabel.innerText = `${data.fake ? "Faking" : "Sending"} to <${data.recipient}> ${data.success ? "succeeded" : "failed"}`;
		if (iter >= total) {
			event.target.close();
			this.formButton.disabled = false;
			this.isBusy = false;
			this.form.classList.remove('is-busy');
		}
	}
}

document.addEventListener('DOMContentLoaded', () => new SellersMail());
