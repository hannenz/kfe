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

	console.log('SellersMail::init', this.baseUrl);

	this.setupRecipientsTable.bind(this)();

	this.marketIdSelect = document.getElementById('market-id');
	this.recipientsCount = document.getElementById('recipients-count');

	var addRecipientsBtn = document.getElementById('js-add-recipients-btn');
	var addRecipientsByMarketBtn = document.getElementById('js-add-recipients-by-market-btn');
	var addRecipientsEmployeesBtn = document.getElementById('js-add-recipients-employees-btn');

	addRecipientsBtn.addEventListener('click', this.onAddRecipientsBtnClicked.bind(this));
	addRecipientsByMarketBtn.addEventListener('click', this.onAddRecipientsByMarketBtnClicked.bind(this));
	addRecipientsEmployeesBtn.addEventListener('click', this.onAddRecipientsEmployeesBtnClicked.bind(this));
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

SellersMail.prototype.setupRecipientsTable = function() {

	console.log('setupRecipientsTable');

	this.recipientsTable = new Tabulator('#recipients-table', {
		columns: [
			{ title: "ID", field: "id", sorter: "number" },
			{ title: "Verk.Nr", field: "seller_nr", sorter: "number" },
			{ title: "E-Mail", field: "seller_email", sorter: "string" },
			{ title: "Nachname", field: "seller_lastname", sorter: "string" },
			{ title: "Vorname", field: "seller_firstname", sorter: "string" },
		],
		height: 300,
		layout: 'fitDataStretch'
	});
};


$(function() {
	var sm = new SellersMail();
});
