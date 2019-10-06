/**
 * src/js/cart_edit.js
 *
 * Display cart items as table with jQuery.appendGrid library (older version: 1.7)
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2019-10-06
 */

$(function () {

	var configField = $("[name='cart_items']");
	var config = configField.text ();

	var loadGridValues = function (gridID, config) {
		if (config) {
			var values = $.evalJSON (config);
		}
		else {
			var values = {}
		}

		values.forEach(function(o) {
			o.datetime = new Date(o.ts).toLocaleString('de-DE', {
				day: 'numeric',
				month: '2-digit',
				year: 'numeric',
				hour: 'numeric',
				minute: '2-digit',
				second: '2-digit'
			});
		});
		console.log(values);

		$(gridID).appendGrid ('load', values);
		updateConfig (gridID);
	};

	var updateConfig = function (gridID) {
		var data = $(gridID).appendGrid ('getAllValue');
		$(configField).val ($.toJSON (data));
	};

	var initGridFields = function (gridID) {
		var form = $(gridID).parent ();
		$(form).find(':input').change (function () {
			updateConfig (gridID);
		});
	};

	var initGrid = function (gridID) {
		$(gridID).appendGrid ('init', {

			caption: '',
			initRows: 1,
			maxRowsAllowed: 0,

			// caption: function(cell) {
			// 	$(cell).css('font-size', '20px').text("Positionen auf diesem Bon");
			// },

			// maxNumRowsReached: function () {
			// 	alert ('Max. 20 Einträge möglich');
			// },

			columns: [
				{ name: 'datetime', display: 'Datum/Uhrzeit', value: new Date().toString(), type: 'text', ctrlAttr: {readonly: 'readonly'} },
				{ name: 'marketId', display: 'Markt ID', type: 'text', ctrlAttr: {readonly: 'readonly'} },
				{ name: 'checkoutId', display: 'Kassen Nr', type: 'text', ctrlAttr: {readonly: 'readonly'} },
				{ name: 'sellerNr', display: 'Verkäufer Nr', type: 'text', ctrlAttr: {readonly: 'readonly'} },
				{ name: 'value', display: 'Betrag (Cent)', type: 'text', ctrlAttr: {readonly: 'readonly'} }
				// { name: 'sellerId', display: 'Verkäufer ID', type: 'text' },
				// { name: 'code', display: 'Barcode', type: 'text' },
			],

			buttonClasses: {
				append: 'cmtButton cmtButtonAdd',
				insert: 'cmtButton cmtButtonAdd',
				remove: 'cmtButton cmtButtonDelete',
				removeLast: 'cmtButton cmtButtonDelete',
				moveUp: 'cmtButton cmtButtonMoveUp',
				moveDown: 'cmtButton cmtButtonMoveDown'
			},

			hideButtons: {
				append: false,
				removeLast: true,
				insert: true
			},


			useSubPanel: false,
			// subPanelBuilder: function (cell, uniqueIndex) {
			// 	$('<span />').text ('Beschreibung').appendTo (cell);
			// 	$('<textarea />').attr({ id: 'desc-' + uniqueIndex, name: 'desc-' + uniqueIndex, rows: 3, cols: 40}).appendTo (cell);
			// },

			subPanelGetter: function (uniqueIndex) {
				return { 'desc': $('#desc-' + uniqueIndex).val() };
			},

			rowDataLoaded: function (caller, record, rowIndex, uniqueIndex) {
				if (record.desc) {
					var elem = document.getElementById ('desc-' + uniqueIndex);
					elem.value = record.desc;
				}
			},

			afterRowAppended: function (caller, parentRowIndex, addedRowIndex) {
				var gridID = '#' + caller.id;
				initGridFields (gridID);
			}, 

			afterRowRemoved: function (caller, rowIndex) {
				var gridID = '#' + caller.id;
				initGridFields (gridID);
				updateConfig (gridID)
			},

			afterRowSwapped: function (caller, oldRowIndex, newRowIndex) {
				var gridID = '#' + caller.id;
				updateConfig (gridID)
			},

		});
	};


	var init = function () {
		configField
			.hide ()
			.after ($('<form>').attr({id: 'formConfig', method:'post', action: ''})
			.append ($('<table>').attr({id: 'tblConfigGrid'})));


		initGrid ('#tblConfigGrid');
		initGridFields ('#formConfig');
		loadGridValues ('#tblConfigGrid', config);
	};

	init ();

});

