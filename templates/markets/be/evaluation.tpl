<link rel="stylesheet" type="text/css" href="/dist/css/market_be.css" />
<div class="market-evaluation">

	<input type="hidden" id="js-url" value="{VAR:loopUrl}" style="width: 100%;" />

	<h1>Auswertung für Markt #{VAR:marketId} am {DATEFMT:"{VAR:market_begin}":%d.%m.%Y:de_DE.UTF-8}</h1>

	<div class="figures">
		<figure class="chart chart--pie">
			<h3 class="chart__title">Umsatz</h3>
			<div id="pie-turnover" class="pie" data-values="{VAR:turnoverSellers} {VAR:turnoverEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd id="turnover-total">{PRINTF:"{VAR:turnoverTotal}":%.2f:de_DE}&thinsp;&euro;<br></dd>
					<dt>Verkäufer</dt>
					<dd id="turnover-sellers">{PRINTF:"{VAR:turnoverSellers}":%.2f:de_DE}&thinsp;&euro;</dd>
					<dt>Mitarbeiter</dt>
					<dd id="turnover-employees">{PRINTF:"{VAR:turnoverEmployees}":%.2f:de_DE}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Spendensumme</h3>
			<div id="pie-bounty" class="pie" data-values="{VAR:bountySellers} {VAR:bountyEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd id="bounty-total">{PRINTF:"{VAR:bountyTotal}":%.2f:de_DE}&thinsp;&euro;<br></dd>
					<dt>Verkäufer</dt>
					<dd id="bounty-sellers">{PRINTF:"{VAR:bountySellers}":%.2f:de_DE}&thinsp;&euro;</dd>
					<dt>Mitarbeiter</dt>
					<dd id="bounty-employees">{PRINTF:"{VAR:bountyEmployees}":%.2f:de_DE}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Kassen</h3>
			<div id="pie-checkouts" class="pie" data-values="{VAR:turnoverCheckout1} {VAR:turnoverCheckout2} {VAR:turnoverCheckout3}"></div>

			<figcaption>
				<dl>
					<dt>Kasse 1</dt>
					<dd id="checkout-1">{PRINTF:{VAR:turnoverCheckout1}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
					<dt>Kasse 2</dt>
					<dd id="checkout-2">{PRINTF:{VAR:turnoverCheckout2}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
					<dt>Kasse 3</dt>
					<dd id="checkout-3">{PRINTF:{VAR:turnoverCheckout3}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Verkaufte Artikel</h3>
			<div id="pie-items" class="pie" data-values="{VAR:itemsSellers} {VAR:itemsEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd id="items-total">{VAR:itemsTotal}</dd>
					<dt>Verkäufer</dt>
					<dd id="items-sellers">{VAR:itemsSellers}</dd>
					<dt>Mitarbeiter</dt>
					<dd id="items-employees">{VAR:itemsEmployees}</dd>
				</dl>
			</figcaption>
		</figure>
		
	</div>
	<!-- <div> -->
	<!-- 	<a class="button cmtButton" href="{SELFURL}&#38;action=sumsheets&#38;market_id={VAR:marketId}">Summenblätter</a>  -->
	<!-- </div> -->
</div>
<script>
	(function() {

		function updatePies() {
			var pies = document.querySelectorAll('.pie');
			pies.forEach(function(el) {
				var values = el.dataset.values.split(/\s/);

				var total = 0;
				values.forEach(function(v) {
					total += parseFloat(v);
				});
				var perc1 = values[0] / total * 100;
				var perc2 = values[1] / total * 100;
				if (values.length == 3) {
					var perc3 = values[2] / total * 100;
				}

				var NS = 'http://www.w3.org/2000/svg';
				var svg = document.createElementNS(NS, 'svg');
				var circle = document.createElementNS(NS, 'circle');
				circle.setAttribute('r', 16);
				circle.setAttribute('cx', 16);
				circle.setAttribute('cy', 16);
				circle.setAttribute('stroke-dasharray', perc1 + ' 100');
				svg.appendChild(circle);

				if (values.length == 3) {
					circle = document.createElementNS(NS, 'circle');
					circle.setAttribute('r', 16);
					circle.setAttribute('cx', 16);
					circle.setAttribute('cy', 16);
					circle.setAttribute('stroke-dasharray', perc2 + ' 100');
					circle.setAttribute('stroke-dashoffset', -perc1);
					svg.appendChild(circle);
				}

				svg.classList.add('pie-chart');
				svg.setAttribute('viewBox', '0 0 32 32');
				el.innerHTML = '';
				el.appendChild(svg);
			});
		}

		updatePies();

		var url = document.getElementById('js-url').value;

		var pieTurnover = document.getElementById('pie-turnover');
		var pieBounty = document.getElementById('pie-bounty');
		var pieCheckouts = document.getElementById('pie-checkouts');
		var pieItems = document.getElementById('pie-items');
		var src = new EventSource(url);
		var localeStringOptions = {
			style: 'currency',
			currency: 'EUR',
			currencyDisplay: 'symbol'
		};

		src.addEventListener('ping', function(event) {
			var data = JSON.parse(event.data);
			console.log(data);

			pieTurnover.dataset.values = data.turnoverSellers + ' ' + data.turnoverEmployees;
			document.getElementById('turnover-total').innerHTML = (data.turnoverSellers + data.turnoverEmployees).toLocaleString('de', localeStringOptions);
			document.getElementById('turnover-sellers').innerHTML = data.turnoverSellers.toLocaleString('de', localeStringOptions);
			document.getElementById('turnover-employees').innerHTML = data.turnoverEmployees.toLocaleString('de', localeStringOptions);

			pieBounty.dataset.values = data.bountySellers + ' ' + data.bountyEmployees;
			document.getElementById('bounty-total').innerHTML = (data.bountySellers + data.bountyEmployees).toLocaleString('de', localeStringOptions);
			document.getElementById('bounty-sellers').innerHTML = data.bountySellers.toLocaleString('de', localeStringOptions);
			document.getElementById('bounty-employees').innerHTML = data.bountyEmployees.toLocaleString('de', localeStringOptions);

			pieCheckouts.dataset.values = data.turnoverCheckout1 + ' ' + data.turnoverCheckout2 + ' ' + data.turnoverCheckout3;
			document.getElementById('checkout-1').innerHTML = data.turnoverCheckout1.toLocaleString('de', localeStringOptions);
			document.getElementById('checkout-2').innerHTML = data.turnoverCheckout2.toLocaleString('de', localeStringOptions);
			document.getElementById('checkout-3').innerHTML = data.turnoverCheckout3.toLocaleString('de', localeStringOptions);

			pieItems.dataset.values = data.itemsTotal + ' ' + data.itemsEmployees;
			document.getElementById('items-total').innerHTML = data.itemsTotal;
			document.getElementById('items-sellers').innerHTML = data.itemsSellers;
			document.getElementById('items-employees').innerHTML = data.itemsEmployees;

			updatePies();
		});
	})();
</script>
