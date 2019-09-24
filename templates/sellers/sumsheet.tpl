<link rel="stylesheet" type="text/css" href="/dist/css/seller_be.css">

<div class="seller-sumsheet">
	<table>
		<thead>
			<tr>
				<th>Datum/Uhrzeit</th>
				<th>Kasse-Nr</th>
				<th>Code</th>
				<th>Betrag</th>
			</tr>
		</thead>
		{LOOP VAR(sellerItems)}
			<tr>
				<td>
					{VAR:dateTimeFmt}
				</td>
				<td>
					{VAR:checkoutId}
				</td>
				<td>
					{VAR:code}
				</td>
				<td>
					{PRINTF:"{VAR:valueEuro}":"%.2f"} EUR
				</td>
			</tr>
		{ENDLOOP VAR}
		<tfoot>
			<tr>
				<td colspan="3">Summe (brutto)</td>
				<td>{PRINTF:"{VAR:totalEuro}":"%.2f"} EUR</td>
			</tr>
			<tr>
				<td colspan="3">Abzgl. {VAR:discount} %</td>
				<td>{PRINTF:"{VAR:discountValueEuro}":"%.2f"} EUR</td>
			</tr>
			<tr>
				<td colspan="3">
					<b>Auszahlungsbetrag (netto)</b>
				</td>
				<td><b>{PRINTF:"{VAR:totalNetEuro}":"%.2f"} EUR</b></td>
			</tr>
		</tfoot>
	</table>

	{IF(!{ISSET:print:GETVAR})}
	<a class="cmtButton" href="{SELFURL}&action=edit&id[]={VAR:sellerId}&print=1" target="_blank">drucken</a>
	{ENDIF}
</div>
