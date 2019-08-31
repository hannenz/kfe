
<ul class="markets markets--upcoming">
	{LOOP VAR(markets)}
		<li>
			<div class="market__datetime">{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"}</div>
			<div class="market__location">{VAR:market_location}</div>
			<a href="{VAR:detailUrl}">Details</a> 
			{IF("{VAR:marketNumberAssignmentIsRunning}" == "1")}
				<a href="{VAR:registrationUrl}">Nummernvergabe</a>
			{ENDIF}
		</li>
	{ENDLOOP VAR}
</ul>
