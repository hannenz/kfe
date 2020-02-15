/**
 * src/js/dialog.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2020-02-14
 */

var Dialog = function(title, message, options) {

	this.dlg = document.createElement('dialog');
	var header = document.createElement('header');
	var body = document.createElement('div');
	var actionArea = document.createElement('div');

	this.dlg.className = 'dialog';

	header.className = 'dialog__header';
	header.innerHTML = title;

	body.innerHTML = message;
	body.className = 'dialog__body';

	actionArea.className = 'dialog__action-area';
	this.dlg.style.left = '50%';
	this.dlg.style.top = '50%';
	this.dlg.style.transform = 'translate(-50%, -50%)';

	options.actions.forEach(function(action) {
		var btn = document.createElement('button');
		btn.innerText = action.text;
		btn.className = 'button';
		btn.addEventListener('click', action.click);
		actionArea.appendChild(btn);
		btn.focus();
	});

	this.dlg.appendChild(header);
	this.dlg.appendChild(body);
	this.dlg.appendChild(actionArea);

	document.body.appendChild(this.dlg);

	dialogPolyfill.registerDialog(this.dlg);

	this.open();
};

Dialog.prototype.open = function() {
	this.dlg.showModal();
};

Dialog.prototype.close = function() {
	this.dlg.close();
	document.body.removeChild(this.dlg);
}
