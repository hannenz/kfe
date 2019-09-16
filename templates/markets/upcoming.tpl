<ul class="markets markets--upcoming">
	{LOOP VAR(markets)}
		<li>
			<div class="market__datetime"><b>{DATEFMT:"{VAR:market_begin}":"%d.%m %Y":de_DE.utf-8}</b>{DATEFMT:"{VAR:market_begin}":"%k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_end}":"%k:%M":de_DE.utf-8} Uhr</div>
			<div class="market__location">{VAR:market_location:nl2br}</div>
			<a href="{VAR:detailUrl}">Details</a> 
			{IF("{VAR:marketNumberAssignmentIsRunning}" == "1")}
				<a href="{VAR:registrationUrl}">Nummernvergabe</a>
			{ENDIF}
		</li>
	{ENDLOOP VAR}
</ul>
