/**
 * src/js/sellers_mail/sellers_mail.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2020-07-05
 */

const State = {
	INITIAL: 'initial',
	RUNNING: 'running',
	PAUSED: 'paused',
	ABORTED: 'aborted',
	FINISHED: 'finished'
};

var SellersMail = function() {

	this.recipientsTable;
	this.baseUrl = $('#sellers-mail-form').attr('action');
	this.form = document.forms.sellersMailForm;

	this.formButton = document.getElementById('form-button');

	this.formButton.disabled = true;

	this.state = State.INITIAL;
	this.isBusy = false;

	this.formButton.addEventListener('click', function(ev) {
	// this.form.addEventListener('submit', function(ev) {
		ev.preventDefault();
		this.onFormButtonClicked();
	}.bind(this));

	this.setupRecipientsTable.bind(this)();

	this.marketIdSelect = document.getElementById('market-id');
	this.recipientsCount = document.getElementById('recipients-count');
	this.progressbar = document.getElementById('js-progress');

	var addRecipientsByMarketBtn = document.getElementById('js-add-recipients-by-market-btn');
	var addRecipientsEmployeesBtn = document.getElementById('js-add-recipients-employees-btn');
	var removeAllRecipientsBtn = document.getElementById('js-remove-all-recipients-btn');

	addRecipientsByMarketBtn.addEventListener('click', this.onAddRecipientsByMarketBtnClicked.bind(this));
	addRecipientsEmployeesBtn.addEventListener('click', (ev) => {
		ev.preventDefault();
		this.onAddRecipientsEmployeesBtnClicked().bind(this);
	});

	// For testing only!!
	this.onAddRecipientsEmployeesBtnClicked();

	removeAllRecipientsBtn.addEventListener('click', this.onRemoveAllRecipientsBtnClicked.bind(this));
}

SellersMail.prototype.onFormButtonClicked = function() {
	switch (this.state) {
		case State.INITIAL:
			this.isBusy = true;
			this.form.classList.add('is-busy');
			this.send();
			this.state = State.RUNNING;
			break;
		case State.RUNNING:
			this.isBusy = false;
			// this.form.classList.remove('is-busy');
			this.state = State.ABORTED;
			this.progressbar.dataset.label = "Aborted";

			// We keep the UI disabled
			// this.enableAll();
			this.formButton.disabled = true;
			break;
	}
}


SellersMail.prototype.onAddRecipientsByMarketBtnClicked = function(ev) {

	ev.preventDefault();

	var url = this.baseUrl + "&action=addRecipientsByMarket&marketId=" + this.marketIdSelect.value; 
	$.get(url, function(response) {
		var data = JSON.parse(response);
		this.recipientsTable.addData(data);
		this.recipientsCount.innerText = this.recipientsTable.rowManager.rows.length;
	}.bind(this));
};



SellersMail.prototype.onAddRecipientsEmployeesBtnClicked = function(ev) {


	$.get(this.baseUrl + '&action=addRecipientsEmployees',  function(response) {
		var data = JSON.parse(response);
		this.recipientsTable.addData(data);
		this.recipientsCount.innerText = this.recipientsTable.rowManager.rows.length;
	}.bind(this));
};


SellersMail.prototype.onRemoveAllRecipientsBtnClicked = function(ev) {
	ev.preventDefault();

	this.recipientsTable.clearData();
};


SellersMail.prototype.setupRecipientsTable = function() {

	this.recipientsTable = new Tabulator('#recipients-table', {
		columns: [
			{ formatter: "rowSelection", titleFormatter: "rowSelection", align: "center", headerSort: false },
			{ title: "ID", field: "id", sorter: "number", "visible": false  },
			{ title: "Verk.Nr", field: "seller_nr", sorter: "number" },
			{ title: "Nachname", field: "seller_lastname", sorter: "string" },
			{ title: "Vorname", field: "seller_firstname", sorter: "string" },
			{ title: "E-Mail", field: "seller_email", sorter: "string" }
		],
		height: 300,
		layout: 'fitDataStretch',
		layoutColumnsOnNewData: true,
		// En-/Disable submit button depending on nr. of selected sellers
		rowSelectionChanged: function(data, rows) {
			this.formButton.disabled = (rows.length == 0);
		}.bind(this)
	});
};



SellersMail.prototype.send = function() {

	this.state = State.RUNNING;

	this.progressbar.dataset.label = "Sending next batch of E-Mails";
	this.batchPause = parseInt(document.querySelector('[name=batch_pause]').value);

	// this.form.classList.add('is-busy');

	var data = new FormData(this.form);
	var url = this.form.getAttribute('action');
	var tableData = this.recipientsTable.getSelectedData();
	var ids = tableData.reduce((acc, cur) => {
		return acc.concat(cur.id);
	}, []);

	data.set('id', ids);

	this.disableAll();

	var req = new XMLHttpRequest();
	req.open('POST', url);
	req.onload = function(ev) {

		if (req.status >= 200 && req.status < 400) {
			// TODO: Evaluate successful load
			this.sendBatch();
		}

	}.bind(this);
	req.send(data);
};


SellersMail.prototype.sendBatch = function() {

	$.get(window.location + '&action=sendMailBatch', function(response) {
		if (this.state != State.RUNNING) {
			return;
		}

		var ret = JSON.parse(response);

		this.progressbar.dataset.label = "Sent " + ret.count + " of " + ret.total + " E-mails";
		this.progressbar.querySelector('.progress-value').style.width = ret.count / ret.total * 100 + '%';

		if (ret.count < ret.total) {

			var seconds = this.batchPause;
			var iv = window.setInterval((that) => {

				if (this.state != State.RUNNING) {
					window.clearInterval(iv);
					return;
				}

				if (seconds <= 0) {
					that.sendBatch();
					window.clearInterval(iv);
					return;
				}

				var message = "Waiting " + (seconds) + " seconds …";
				this.progressbar.dataset.label =  message;
				seconds--;

			}, 1000, this);
		}
		else {
			if (this.state!= State.ABORTED) {
				// DONE!
				this.enableAll();
				this.state = State.FINISHED;
				this.progressbar.dataset.label = "Done.";
			}
		}
	}.bind(this));
};

SellersMail.prototype.disableAll = function() {
	var inputs = this.form.querySelectorAll('input, textarea');
	inputs.forEach( (el) => {
		el.setAttribute('disabled', 'disabled');
	});
}

SellersMail.prototype.enableAll = function() {
	var inputs = this.form.querySelectorAll('input, textarea');
	inputs.forEach( (el) => {
		el.removeAttribute('disabled');
	});
}


$(function() {
	var sm = new SellersMail();
});
