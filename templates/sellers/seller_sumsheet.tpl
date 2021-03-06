<div class="sumsheet" style="font-family:sans-serif; font-size: 14px;">

	<!-- <div class="sumsheet__header"> -->
	<!-- 	<p><b>{PRINTF:{VAR:seller_nr}:%03u}</b> {VAR:seller_lastname}, {VAR:seller_firstname}</p> -->
	<!-- </div> -->

	{IF({COUNT:sales} == 0)}
		<p>Leider nichts verkauft &hellip; </p>
	{ELSE}

	<p>{COUNT:sales} Verkäufe</p>

	<table class="sumsheet__table" style="width: 100%; border-collapse: collapse; font-size: 10px">
		<thead>
			<tr style="font-weight: bold">
				<td style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px;">Datum/Uhrzeit</td>
				<td style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px;">Kasse</td>
				<td style="font-size: 10px; text-align: right; vertical-align: top; line-height: 22px;">Betrag</td>
			</tr>
		</thead>
		<tbody>
			{LOOP VAR(sales)}
			<tr>
				<td style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px; border-top-width: 0.25px; border-top-style: solid; border-top-color: #a0a0a0;">{VAR:dateTimeFmt}</td>
				<td style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px; border-top-width: 0.25px; border-top-style: solid; border-top-color: #a0a0a0;">{VAR:checkoutId}</td>
				<td style="font-size: 10px; text-align: right;vertical-align: top; line-height: 22px; border-top-width: 0.25px; border-top-style: solid; border-top-color: #a0a0a0;">{VAR:valueEuroFmt} &euro;</td>
			</tr>
			{ENDLOOP VAR}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2" style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px; border-top-style: solid; border-top-width: 0.5px; border-top-color: #404040; font-weight: bold;">Summe</td>
				<td  style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px; border-top-style: solid; border-top-width: 0.25px; border-top-color: #404040; text-align: right; font-weight: bold;">{VAR:salesTotalEuroFmt} &euro;</td>
			</tr>
			<tr>
				<td colspan="2" style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px;">Abzgl. {VAR:discountPercent} %</td>
				<td style="font-size: 10px; text-align: left; vertical-align: top; line-height: 22px; text-align: right">- {VAR:discountValueEuroFmt} &euro;</td>
			</tr>
			<tr>
				<td colspan="2">Auszahlungsbetrag</td>
				<td style="text-align: right; font-size: 20px; line-height: 30px; font-weight: bold">{VAR:grossValueEuroFmt} &euro;</td>
			</tr>
		</tfoot>
	</table>
	{ENDIF}
</div>
