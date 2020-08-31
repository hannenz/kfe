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

	console.log('SellersMail::init', this.baseUrl);

	this.setupRecipientsTable.bind(this)();

	this.marketIdSelect = document.getElementById('market-id');
	this.recipientsCount = document.getElementById('recipients-count');
	this.progressbar = document.getElementById('js-progress');

	var addRecipientsBtn = document.getElementById('js-add-recipients-btn');
	var addRecipientsByMarketBtn = document.getElementById('js-add-recipients-by-market-btn');
	var addRecipientsEmployeesBtn = document.getElementById('js-add-recipients-employees-btn');
	var removeAllRecipientsBtn = document.getElementById('js-remove-all-recipients-btn');

	addRecipientsBtn.addEventListener('click', this.onAddRecipientsBtnClicked.bind(this));
	addRecipientsByMarketBtn.addEventListener('click', this.onAddRecipientsByMarketBtnClicked.bind(this));
	addRecipientsEmployeesBtn.addEventListener('click', this.onAddRecipientsEmployeesBtnClicked.bind(this));
	removeAllRecipientsBtn.addEventListener('click', this.onRemoveAllRecipientsBtnClicked.bind(this));

}



SellersMail.prototype.onAddRecipientsBtnClicked = function(ev) {
	ev.preventDefault();
	console.log('add recipient btn clicked');
};



SellersMail.prototype.onAddRecipientsByMarketBtnClicked = function(ev) {

	ev.preventDefault();

	var url = this.baseUrl + "&action=addRecipientsByMarket&marketId=" + this.marketIdSelect.value; 
	$.get(url, function(response) {
		this.recipientsTable.addData(JSON.parse(response));
		this.recipientsCount.innerText = this.recipientsTable.rowManager.rows.length;
	}.bind(this));
};



SellersMail.prototype.onAddRecipientsEmployeesBtnClicked = function(ev) {

	ev.preventDefault();

	$.get(this.baseUrl + '&action=addRecipientsEmployees',  function(response) {
		this.recipientsTable.addData(JSON.parse(response));
		this.recipientsCount.innerText = this.recipientsTable.rowManager.rows.length;
	}.bind(this));
};

SellersMail.prototype.onRemoveAllRecipientsBtnClicked = function() {

	this.recipientsTable.clearData();
};


SellersMail.prototype.setupRecipientsTable = function() {

	this.recipientsTable = new Tabulator('#recipients-table', {
		columns: [
			{ title: "ID", field: "id", sorter: "number", "visible": false  },
			{ title: "Verk.Nr", field: "seller_nr", sorter: "number" },
			{ title: "E-Mail", field: "seller_email", sorter: "string" },
			{ title: "Nachname", field: "seller_lastname", sorter: "string" },
			{ title: "Vorname", field: "seller_firstname", sorter: "string" },
			{ title: "Gesendet", type: "checkbox" }
		],
		height: 300,
		layout: 'fitDataStretch',
		layoutColumnsOnNewData: true
	});
};



SellersMail.prototype.send = function() {

	console.log("** Send **");

	var data = new FormData(this.form);
	var url = this.form.getAttribute('action');
	var tableData = this.recipientsTable.getData();
	var ids = tableData.reduce((acc, cur) => {
		return acc.concat(cur.id);
	}, []);

	SellersMail.total = ids.length;

	data.set('id', ids);

	this.Send

	var req = new XMLHttpRequest();
	req.open('POST', url);
	req.onload = function(ev) {

		if (req.status >= 200 && req.status < 400) {
			// TODO: Evaluate successful load
			console.log("Status OK");
			this.sendBatch();
		}

	}.bind(this);
	req.send(data);
};


SellersMail.prototype.sendBatch = function() {

	console.log("** SendBatch **");

	$.get(window.location + '&action=sendMailBatch', function(response) {
		var ret = JSON.parse(response);

		console.log("Sent " + ret.count + " of " + ret.total + " E-mails");
		this.progressbar.value = ret.count / ret.total;

		if (false && ret.count < ret.total) {
			window.setTimeout(this.sendBatch.bind(this), 1500);
		}
		else {
			console.log('** Done **');
		}
	}.bind(this));
};


$(function() {
	var sm = new SellersMail();
});
