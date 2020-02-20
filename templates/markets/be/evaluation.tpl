<link rel="stylesheet" type="text/css" href="/dist/css/market_be.css" />
<div class="market-evaluation">
	<input type="hidden" id="js-url" value="{VAR:loopUrl}" style="width: 100%;" />


	<h1>Auswertung für den Markt #{VAR:marketId} am {DATEFMT:"{VAR:market_begin}":%d.%m.%Y:de_DE.UTF-8}</h1>


	<div class="big-number">
		<span>Spendensumme</span><br>
		<span id="bounty-total-big">{PRINTF:"{VAR:bountyTotal}":%.2f:de_DE}&thinsp;&euro;</span>
	</div>

	<div class="figures">

		<!-- <figure class="chart chart&#45;&#45;pie"> -->
		<!-- 	<h3 class="chart__title">Test</h3> -->
		<!-- 	<div id="pie&#45;turnover" class="pie" data&#45;values="10 20 30" data&#45;colors="tomato skyblue purple"> </div> -->
		<!-- </figure> -->

		<figure class="chart chart--pie">
			<h3 class="chart__title">Umsatz</h3>
			<div id="pie-turnover" class="pie" data-values="{VAR:turnoverSellers} {VAR:turnoverEmployees}" data-colors="tomato skyblue"> </div>
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

		<!-- <figure class="chart chart&#45;&#45;pie"> -->
		<!-- 	<h3 class="chart__title">Spendensumme</h3> -->
		<!-- 	<div id="pie&#45;bounty" class="pie" data&#45;values="{VAR:bountySellers} {VAR:bountyEmployees}"> </div> -->
		<!-- 	<figcaption> -->
		<!-- 		<dl> -->
		<!-- 			<dt>Gesamt</dt> -->
		<!-- 			<dd id="bounty&#45;total">{PRINTF:"{VAR:bountyTotal}":%.2f:de_DE}&#38;thinsp;&#38;euro;<br></dd> -->
		<!-- 			<dt>Verkäufer</dt> -->
		<!-- 			<dd id="bounty&#45;sellers">{PRINTF:"{VAR:bountySellers}":%.2f:de_DE}&#38;thinsp;&#38;euro;</dd> -->
		<!-- 			<dt>Mitarbeiter</dt> -->
		<!-- 			<dd id="bounty&#45;employees">{PRINTF:"{VAR:bountyEmployees}":%.2f:de_DE}&#38;thinsp;&#38;euro;</dd> -->
		<!-- 		</dl> -->
		<!-- 	</figcaption> -->
		<!-- </figure> -->

		<figure class="chart chart--pie">
			<h3 class="chart__title">Kassen</h3>
			<div id="pie-checkouts" class="pie" data-values="{VAR:turnoverCheckout1} 100 200" data-colors="tomato skyblue purple"></div>

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
			<div id="pie-items" class="pie" data-values="{VAR:itemsSellers} {VAR:itemsEmployees}" data-colors="tomato skyblue"> </div>
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
	<p>
		<input type="checkbox" id="live-cbx" /><label for="live-cbx">Live Polling</label>
	</p>
	<!-- <div> -->
	<!-- 	<a class="button cmtButton" href="{SELFURL}&#38;action=sumsheets&#38;market_id={VAR:marketId}">Summenblätter</a>  -->
	<!-- </div> -->
	<div id="heartbeat">↻</div>
</div>
<script src="/dist/js/evaluation.js"></script>
