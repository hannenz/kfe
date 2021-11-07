/**
 * src/js/circulars/Circulars.js
 *
 * @author Johannes Braun <hannenz@posteo.de>
 * @package kfe
 * @version 2021-11-07
 */

class Circulars {

	constructor() {
		console.log("Circulars.js");

		var sendButtons = document.querySelectorAll('.circularSendBtn');
		sendButtons.forEach((btn) => {
			btn.addEventListener('click', (ev) => {
				ev.preventDefault();
				this.onSendBtnClicked(btn);
			});
		});
	}
	
	onSendBtnClicked(btn) {
		let id = btn.dataset.id;
		fetch(btn.href).then(() => {
			// 2. When done, Connect to SSE to receive updates / progress
			let url = `https://kfe.hannenz.localhost/contentomat/ApplicationLauncher.php?sid=945db35b1a929ee37212e1e2d8f84bf9&cmtApplicationID=153&launch=0&cmt_action=sendLoop&id=${id}`
			let src = new EventSource(url);
			src.addEventListener('error', () => console.error('Event Stream Error'));
			src.addEventListener('mailSent', this.onMailSent.bind(this));
			src.addEventListener('done', () => {
				console.log("done.");
				src.close();
				// todo: clean-up;
				console.log("Reloading");
				window.location.reload();
			});
		});
	}


	onMailSent(event) {
		let data = JSON.parse(event.data),
			iter = parseInt(data.iter),
			total = parseInt(data.total),
			value = iter / total;
	}
}

document.addEventListener('DOMContentLoaded', () => new Circulars());

