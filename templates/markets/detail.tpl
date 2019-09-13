<section class="market market--detail">

	<div class="callout">
		<div class="callout__head">
			{DATEFMT:"{VAR:market_datetime}":"%a, %d. %B %Y<br>%H:%M Uhr"}
		</div>
		<div class="callout__subline">
			{VAR:market_location}	
		</div>
	</div>

	
	<div class="market__description">
		{VAR:market_description}
	</div>

	<div class="market__remark">
		{VAR:market_remark}
	</div>

	{IF("{VAR:marketNumberAssignmentIsRunning}" == "1")}
		<div class="market__number-assignment">
			<a href="{VAR:registrationUrl}">Nummernvergabe</a>
		</div>
	{ELSE}
		<div class="">Die Nummernvergabe für diesen Markt ist geschlossen</div>
	{ENDIF}

	<div class="market_media">
		<ul>
			{LOOP VAR(marketDocuments)}
				<li>
					<a href="{VAR:media_document_internal_file}" download>{VAR:media_document_file}</a>
				</li>
			{ENDLOOP VAR}
		</ul>
	</div>
</section>
