/**
 * src/js/dialog.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2020-02-14
 */


/**
 * A class to display dialogs around the native <dialog> element (HTML5)
 * makes use of a polyfill.
 * The dialogs must be present in the markup (they are invisible by default)
 * with a markup like this:
 *
 * ```
 * <dialog id="my-dialog" class="dialog">
 * 	<form>
 * 		<header class="dialog__header">My Dialog's  title</header>
 * 		<div class="dialog__body"></div>
 * 		<div class="dialog__action-area">
 * 			<button class="button" name="dialogAction" value="reject">Cancel</button>
 * 			<button class="button" name="dialogAction" value="accept">OK</button>
 * 		</div>
 * 	</form>
 * </dialog>
 *
 * @class Dialog
 * @param string 		ID of the <dialog /> element
 * @return this
 */
var Dialog = function(id) {

	this.dialog = document.getElementById(id);
	if (!this.dialog) {
		return;
	}

	dialogPolyfill.registerDialog(this.dialog);
	return this;
};


/**
 * Run the dialog, e.g. wait until the inner form has been submitted
 *
 * @return Promise, resolves with an object:
 * 					- action: 'reject' or 'accept'
 * 					- data: The form's data as FormData object
 * 					- dialog: The dialog object
 */
Dialog.prototype.run = function() {
	this.form = this.dialog.querySelector('form');
	if (!this.form) {
		return;
	}

	this.form.reset();

	this.dialog.showModal();

	var input = this.form.querySelector('input');
	if (input) {
		input.focus();
	}


	var promise = new Promise(function(resolve, reject) {
		this.form.onsubmit = function(ev) {
			ev.preventDefault();
			resolve({
				action: ev.explicitOriginalTarget.value,
				data: new FormData(this.form),
				dialog: this.dialog
			});
		}.bind(this);
	}.bind(this));

	return promise
};



/**
 * Close the dialog
 *
 * @return this
 */
Dialog.prototype.close = function() {
	this.dialog.close();
	return this;
};



/**
 * Set the body text (HTML)
 *
 * @param string 		HTML string
 * @return this
 */
Dialog.prototype.setBody = function(bodyHTML) {
	this.dialog.querySelector('.dialog__body').innerHTML = bodyHTML;
	return this;
};
