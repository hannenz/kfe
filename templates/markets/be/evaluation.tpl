<link rel="stylesheet" type="text/css" href="/dist/css/market_be.css" />
<div class="market-evaluation">
	<h1>Auswertung für Markt #{VAR:marketId} am {DATEFMT:"{VAR:market_begin}":%d.%m.%Y:de_DE.UTF-8}</h1>

	<div class="figures">
		<figure class="chart chart--pie">
			<h3 class="chart__title">Umsatz</h3>
			<div class="pie" data-values="{VAR:turnoverSellers} {VAR:turnoverEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd>{PRINTF:"{VAR:turnoverTotal}":%.2f:de_DE}&thinsp;&euro;<br></dd>
					<dt>Verkäufer</dt>
					<dd>{PRINTF:"{VAR:turnoverSellers}":%.2f:de_DE}&thinsp;&euro;</dd>
					<dt>Mitarbeiter</dt>
					<dd>{PRINTF:"{VAR:turnoverEmployees}":%.2f:de_DE}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Spendensumme</h3>
			<div class="pie" data-values="{VAR:bountySellers} {VAR:bountyEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd>{PRINTF:"{VAR:bountyTotal}":%.2f:de_DE}&thinsp;&euro;<br></dd>
					<dt>Verkäufer</dt>
					<dd>{PRINTF:"{VAR:bountySellers}":%.2f:de_DE}&thinsp;&euro;</dd>
					<dt>Mitarbeiter</dt>
					<dd>{PRINTF:"{VAR:bountyEmployees}":%.2f:de_DE}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Kassen</h3>
			<div class="pie" data-values="{VAR:turnoverCheckout1} {VAR:turnoverCheckout2} {VAR:turnoverCheckout3}"></div>

			<figcaption>
				<dl>
					<dt>Kasse 1</dt>
					<dd>{PRINTF:{VAR:turnoverCheckout1}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
					<dt>Kasse 2</dt>
					<dd>{PRINTF:{VAR:turnoverCheckout2}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
					<dt>Kasse 3</dt>
					<dd>{PRINTF:{VAR:turnoverCheckout3}:%.2f:de_DE.UTF-8}&thinsp;&euro;</dd>
				</dl>
			</figcaption>
		</figure>
		<figure class="chart chart--pie">
			<h3 class="chart__title">Verkaufte Artikel</h3>
			<div class="pie" data-values="{VAR:itemsSellers} {VAR:itemsEmployees}"> </div>
			<figcaption>
				<dl>
					<dt>Gesamt</dt>
					<dd>{VAR:itemsTotal}</dd>
					<dt>Verkäufer</dt>
					<dd>{VAR:itemsSellers}</dd>
					<dt>Mitarbeiter</dt>
					<dd>{VAR:itemsEmployees}</dd>
				</dl>
			</figcaption>
		</figure>
		
	</div>
	<div>
		<a class="button cmtButton" href="{SELFURL}&action=sumsheets&market_id={VAR:marketId}">Summenblätter</a> 
	</div>
</div>
<script>
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
		el.appendChild(svg);
	});
</script>
