<div class="market-evaluation">
	<h1>Auswertung</h1>

	{LOOP VAR(sellers)}
		<table class="evaluation-seller-data">
			<tr>
				<th>Verkäufer-Nummer</th>
				<td>{VAR:seller_nr}</td>
			</tr>
			<tr>
				<th>Name</th>
				<td>{VAR:seller_firstname} {VAR:seller_lastname}</td>
			</tr>
			<tr>
				<th>E-Mail</th>
				<td>{VAR:seller_email}</td>
			</tr>
			<tr>
				<th>Telefon</th>
				<td>{VAR:seller_phone}</td>
			</tr>
		</table>

		<h2>Verkäufe ({VAR:itemsCount} Positionen)</h2>
		<table class="evaluation-seller-items">
			<thead>
				<tr>
					<th>Datum/Uhrzeit</th>
					<th>Kassen-Nr</th>
					<th class="currency">Betrag (EUR)</th>
				</tr>
			</thead>
			<tbody>
				{LOOP VAR(items)}
				<tr>
					<td>{VAR:datetime}</td>
					<td>{VAR:checkoutId}</td>
					<td class="currency">{VAR:valueFmt} &euro;</td>
				</tr>
				{ENDLOOP VAR}
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><b>Summe Brutto</b></td>
					<td class="currency"><b>{PRINTF:"{VAR:total}":"%.2f"} &euro;</b></td>
				</tr>
				<tr>
					<td colspan="2">Abzug ({VAR:discount} %)</td>
					<td class="currency">{PRINTF:"{VAR:discountValue}":"%.2f"} &euro;</td>
				</tr>
				<tr>
					<td colspan="2"><b>Summe Netto</b></td>
					<td class="currency">{PRINTF:"{VAR:totalNet}":"%.2f"} &euro;</td>
				</tr>
			</tfoot>
		</table>
	{ENDLOOP VAR}
</div>
