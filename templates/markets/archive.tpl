<ul class="markets markets--archive">
	{LOOP VAR(markets)}
		<li>
			<h3 class="headline">{DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}</h3>
			<div>{VAR:market_charity}</div>
		</li>
	{ENDLOOP VAR}
</ul>
