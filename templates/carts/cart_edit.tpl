<link rel="stylesheet" type="text/css" href="{PATHTOWEBROOT}dist/css/vendor/jquery.appendGrid-1.7.1.min.css"/>
<style type="text/css">
#tblConfigGrid {
	width: 100%;
	max-width: 700px;
}
#tblConfigGrid td {
	border: 0;
}
#tblConfigGrid td, #tblConfigGrid th {
	padding: 1px;
}
#tblConfigGrid tr {
	border-bottom: 1px solid rgba(100, 100, 100, 0.3);
}
#tblConfigGrid input {
	appearance: none;
	border: 0;
	box-shadow: none;
}
#tblConfigGrid tr > td:first-child {
	width: 40px;
}
#tblConfigGrid .ui-widget-header {
	background-image: none;
	background-color: #f6f6f6;
}
#tblConfigGrid .cmtButton.cmtButtonAdd {
	background-image: url('/admin/templates/default/administration/img/icons/add_large.png');
}
#tblConfigGrid .cmtButton.cmtButtonMoveUp {
	background-image: url('/admin/templates/default/administration/img/icons/up_large.png');
}
#tblConfigGrid .cmtButton.cmtButtonMoveDown {
	background-image: url('/admin/templates/default/administration/img/icons/down_large.png');
}
#tblConfigGrid .cmtButton.cmtButtonDelete {
	background-image: url('/admin/templates/default/administration/img/icons/delete_large.png');
}
</style>
<script src="{PATHTOWEBROOT}dist/js/vendor/jquery.appendGrid-1.7.1.min.js"></script>
<script>
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
				{ name: 'datetime', display: 'Datum/Uhrzeit', value: new Date().toString(), type: 'text' },
				{ name: 'marketId', display: 'Markt ID', type: 'text' },
				{ name: 'checkoutId', display: 'Kassen Nr', type: 'text' },
				{ name: 'sellerNr', display: 'Verkäufer Nr', type: 'text' },
				{ name: 'value', display: 'Betrag (Cent)', type: 'text' }
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
</script>


