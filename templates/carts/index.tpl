<h2 class="headline">Alle Vorgänge</h2>
<section>
	<table>
		<thead>
			<tr>
				<th>Buchung</th>
				<th>Markt</th>
				<th>Anzahl Posten</th>
				<th>Summe</th>
				<th>Kasse</th>
				<th>Kassierer-ID</th>
			</tr>
		</thead>
		{LOOP VAR(carts)}
		<tr>
			<td>{DATEFMT:"{VAR:cart_submitted_datetime}":"%d.%m.%Y %T":"de_DE.UTF8"}</td>
			<td>{VAR:cart_market_id}</td>
			<td>{VAR:cart_items_count}</td>
			<td>{VAR:cart_total}</td>
			<td>{VAR:cart_checkout_id}</td>
			<td>{VAR:cart_cashier_id}</td>
		</tr>
		{ENDLOOP VAR}
	</table>
	
</section>
