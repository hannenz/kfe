<div>
	<h1>Auswertung</h1>

	{LOOP VAR(sellers)}
		<table>
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

		<h2>Verkäufe</h2>
		<table>
			<thead>
				<tr>
					<th>Datum/Uhrzeit</th>
					<th>Kassen-Nr</th>
					<th>Betrag</th>
				</tr>
			</thead>
			<tbody>
				{LOOP VAR(items)}
				<tr>
					<td>{VAR:datetime}</td>
					<td>{VAR:checkoutId}</td>
					<td>{VAR:valueFmt} &euro;</td>
				</tr>
				{ENDLOOP VAR}
			</tbody>
			<tfoot>
				<td colspan="2"><b>Summe</b></td>
				<td><b>{VAR:total}</b></td>
			</tfoot>
		</table>
	{ENDLOOP VAR}
</div>
