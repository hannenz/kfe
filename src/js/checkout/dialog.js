/**
 * src/js/dialog.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2020-02-14
 */

var Dialog = function(id, options) {

	this.dlg = document.getElementById(id);
	if (!this.dlg) {
		return;
	}

	dialogPolyfill.registerDialog(this.dlg);
};


/**
 * Run the dialog, e.g. wait until the inner form has been submitted
 *
 * @return Promise
 */
Dialog.prototype.run = function() {
	this.form = this.dlg.querySelector('form');
	this.form.reset();
	this.form.querySelector('input').focus();
	this.dlg.showModal();

	var promise = new Promise(function(resolve, reject) {
		this.form.onsubmit = function(ev) {
			ev.preventDefault();
			resolve({
				action: ev.explicitOriginalTarget.value,
				data: new FormData(this.form)
			});
		}.bind(this);
	}.bind(this));

	return promise
};

Dialog.prototype.close = function() {
	this.dlg.close();
}
