<link rel="stylesheet" type="text/css" href="/dist/css/market_be.css" />

<div class="market-evaluation">

	<input type="hidden" id="js-url" value="{VAR:loopUrl}" style="width: 100%;" />

	<h1>Auswertung für den Markt #{VAR:marketId} am {DATEFMT:"{VAR:market_begin}":%d.%m.%Y:de_DE.UTF-8}</h1>

	<div class="big-number">
		<span>Spendensumme</span><br>
		<span id="bounty-total-big">{PRINTF:"{VAR:bountyTotal}":%.2f:de_DE}&thinsp;&euro;</span>
	</div>

	<div class="figures">

		<figure class="chart chart--pie">
			<h3 class="chart__title">Umsatz</h3>
			<div id="pie-turnover" class="pie" data-values="{VAR:turnoverSellers} {VAR:turnoverEmployees}" data-colors="#93b527 #447604"> </div>
			<figcaption>
				<table>
					<tr>
						<td>Gesamt</td>
						<td id="turnover-total">{PRINTF:"{VAR:turnoverTotal}":%.2f:de_DE}&thinsp;&euro;</td>
					</tr>
					<tr data-circle="0">
						<td>Verkäufer</td>
						<td id="turnover-sellers">{PRINTF:"{VAR:turnoverSellers}":%.2f:de_DE}&thinsp;&euro;</td>
					</tr>
					<tr data-circle="1">
						<td>Mitarbeiter</td>
						<td id="turnover-employees">{PRINTF:"{VAR:turnoverEmployees}":%.2f:de_DE}&thinsp;&euro;</td>
					</tr>
				</table>
			</figcaption>
		</figure>

		<figure class="chart chart--pie">
			<h3 class="chart__title">Kassen</h3>
			<div id="pie-checkouts" class="pie" data-values="{VAR:turnoverCheckout1} {VAR:turnoverCheckout2} {VAR:turnoverCheckout3}" data-colors="#93b527 #447604 #56445d"></div>

			<figcaption>
				<table>
					<tr data-circle="0">
						<td>Kasse 1</td>
						<td id="checkout-1">{PRINTF:{VAR:turnoverCheckout1}:%.2f:de_DE.UTF-8}&thinsp;&euro;</td>
					</tr>
					<tr data-circle="1">
						<td>Kasse 2</td>
						<td id="checkout-2">{PRINTF:{VAR:turnoverCheckout2}:%.2f:de_DE.UTF-8}&thinsp;&euro;</td>
					</tr>
					<tr data-circle="2">
						<td>Kasse 3</td>
						<td id="checkout-3">{PRINTF:{VAR:turnoverCheckout3}:%.2f:de_DE.UTF-8}&thinsp;&euro;</td>
					</tr>
				</table>
			</figcaption>
		</figure>

		<figure class="chart chart--pie">
			<h3 class="chart__title">Verkaufte Artikel</h3>
			<div id="pie-items" class="pie" data-values="{VAR:itemsSellers} {VAR:itemsEmployees}" data-colors="#93b527 #447604"> </div>
			<figcaption>
				<table>
					<tr>
						<td>Gesamt</td>
						<td id="items-total">{VAR:itemsTotal}</td>
					</tr>
					<tr data-circle="0">
						<td>Verkäufer</td>
						<td id="items-sellers">{VAR:itemsSellers}</td>
					</tr>
					<tr data-circle="1">
						<td>Mitarbeiter</td>
						<td id="items-employees">{VAR:itemsEmployees}</td>
					</tr>
				</table>
			</figcaption>
		</figure>
		
	</div>

	<p>
		<input type="checkbox" id="live-cbx" checked /><label for="live-cbx">Live Monitor</label>
	</p>

	<div id="heartbeat">↻</div>
</div>
<script src="/dist/js/evaluation.js"></script>
