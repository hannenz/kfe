
<ul class="markets markets--upcoming">
	{LOOP VAR(markets)}
		<li>
			<div class="market__datetime">{VAR:market_datetime}</div>
			<div class="market__location">{VAR:market_location}</div>
			{IF("{VAR:marketNumberAssignmentIsRunning}" == "1")}
				<a href="{VAR:marketNumberAssignmentUrl}">Nummernvergabe</a>
			{ENDIF}
		</li>
	{ENDLOOP VAR}
</ul>
