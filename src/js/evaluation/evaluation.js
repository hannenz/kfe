/**
 * src/js/evaluation/evaluation.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2020-02-18
 */


(function() {
	document.addEventListener('DOMContentLoaded', function() {
		Evaluation();
	});

	var Evaluation = function() {
		setupLiveMode();
		updatePieCharts();
	};


	function updatePieCharts() {
		var pies = document.querySelectorAll('.pie');
		pies.forEach(function(el) {
			new PieChart(el);
		});
	}


	function setupLiveMode() {
		var url = document.getElementById('js-url').value;

		var pieTurnover = document.getElementById('pie-turnover');
		var pieBounty = document.getElementById('pie-bounty');
		var pieCheckouts = document.getElementById('pie-checkouts');
		var pieItems = document.getElementById('pie-items');
		var localeStringOptions = {
			style: 'currency',
			currency: 'EUR',
			currencyDisplay: 'symbol'
		};

		var src = null;

		document.getElementById('live-cbx').addEventListener('change', function() {
			if (this.checked) {
				console.log("Starintg live poll");
				src = new EventSource(url);
				src.addEventListener('error', function(ev) {
					console.log("Event Stream Error");
				});
				src.addEventListener('ping', onPing);
			}
			else {
				console.log("Stopping live poll");
				src.removeEventListener('ping', onPing);
				src.close();
			}
		});

		function onPing(event) {
			var data = JSON.parse(event.data);
			console.log(data);

			pieTurnover.dataset.values = data.turnoverSellers + ' ' + data.turnoverEmployees;
			document.getElementById('turnover-total').innerHTML = (data.turnoverSellers + data.turnoverEmployees).toLocaleString('de', localeStringOptions);
			document.getElementById('turnover-sellers').innerHTML = data.turnoverSellers.toLocaleString('de', localeStringOptions);
			document.getElementById('turnover-employees').innerHTML = data.turnoverEmployees.toLocaleString('de', localeStringOptions);

			// pieBounty.dataset.values = data.bountySellers + ' ' + data.bountyEmployees;
			// document.getElementById('bounty-total').innerHTML = (data.bountySellers + data.bountyEmployees).toLocaleString('de', localeStringOptions);
			// document.getElementById('bounty-sellers').innerHTML = data.bountySellers.toLocaleString('de', localeStringOptions);
			// document.getElementById('bounty-employees').innerHTML = data.bountyEmployees.toLocaleString('de', localeStringOptions);

			pieCheckouts.dataset.values = data.turnoverCheckout1 + ' ' + data.turnoverCheckout2 + ' ' + data.turnoverCheckout3;
			document.getElementById('checkout-1').innerHTML = data.turnoverCheckout1.toLocaleString('de', localeStringOptions);
			document.getElementById('checkout-2').innerHTML = data.turnoverCheckout2.toLocaleString('de', localeStringOptions);
			document.getElementById('checkout-3').innerHTML = data.turnoverCheckout3.toLocaleString('de', localeStringOptions);

			pieItems.dataset.values = data.itemsSellers + ' ' + data.itemsEmployees;
			document.getElementById('items-total').innerHTML = data.itemsTotal;
			document.getElementById('items-sellers').innerHTML = data.itemsSellers;
			document.getElementById('items-employees').innerHTML = data.itemsEmployees;

			document.getElementById('bounty-total-big').innerText = data.bountyTotal.toLocaleString('de', localeStringOptions);


			/**
			 * Update the pie cahrts
			 */
			// updatePieCharts();


			/* Indicate that we are still live and receive messages from the
			 * server
			 */
			document.getElementById('heartbeat').animate([
				{ transform: 'rotate(0turn)' },
				{ transform: 'rotate(1turn)' }
			], {
				'duration': 500,
				'iterations': 1
			});
		}
	}
})();

