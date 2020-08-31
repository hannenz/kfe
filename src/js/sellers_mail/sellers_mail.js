/**
 * src/js/sellers_mail/sellers_mail.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2020-07-05
 */

var SellersMail = function() {

	this.recipientsTable;
	this.baseUrl = $('#sellers-mail-form').attr('action');
	this.form = document.forms.sellersMailForm;
	this.form.addEventListener('submit', function(ev) {
		ev.preventDefault();
		this.send();
	}.bind(this));

	this.setupRecipientsTable.bind(this)();

	this.marketIdSelect = document.getElementById('market-id');
	this.recipientsCount = document.getElementById('recipients-count');
	this.progressbar = document.getElementById('js-progress');

	var addRecipientsByMarketBtn = document.getElementById('js-add-recipients-by-market-btn');
	var addRecipientsEmployeesBtn = document.getElementById('js-add-recipients-employees-btn');
	var removeAllRecipientsBtn = document.getElementById('js-remove-all-recipients-btn');

	addRecipientsByMarketBtn.addEventListener('click', this.onAddRecipientsByMarketBtnClicked.bind(this));
	addRecipientsEmployeesBtn.addEventListener('click', this.onAddRecipientsEmployeesBtnClicked.bind(this));
	removeAllRecipientsBtn.addEventListener('click', this.onRemoveAllRecipientsBtnClicked.bind(this));
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

	ev.preventDefault();

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
			{ title: "E-Mail", field: "seller_email", sorter: "string" },
			{ title: "Nachname", field: "seller_lastname", sorter: "string" },
			{ title: "Vorname", field: "seller_firstname", sorter: "string" }
		],
		height: 300,
		layout: 'fitDataTable',
		layoutColumnsOnNewData: true,
	});
};



SellersMail.prototype.send = function() {

	this.batchPause = parseInt(document.querySelector('[name=batch_pause]').value);

	this.form.classList.add('is-busy');

	var data = new FormData(this.form);
	var url = this.form.getAttribute('action');
	var tableData = this.recipientsTable.getSelectedData();
	var ids = tableData.reduce((acc, cur) => {
		return acc.concat(cur.id);
	}, []);

	data.set('id', ids);

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
		var ret = JSON.parse(response);

		this.progressbar.innerText = "Sent " + ret.count + " of " + ret.total + " E-mails";
		this.progressbar.value = ret.count / ret.total;

		if (ret.count < ret.total) {

			// var seconds = this.batchPause;
			// var iv = window.setInterval(function() {
            //
			// 	this.progressbar.innerText = "Waiting " + (seconds) + " seconds …";
            //
			// 	if (seconds-- < 0) {
			// 		this.sendBatch().bind(this);
			// 		window.clearInterval(iv);
			// 	}
			// }.bind(this), 1000);

			window.setTimeout(this.sendBatch.bind(this), this.batchPause * 1000);
		}
		else {
			this.form.classList.remove('is-busy');
		}
	}.bind(this));
};


$(function() {
	var sm = new SellersMail();
});
