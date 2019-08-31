<section class="market market--detail">

	<h1 class="market__title">{VAR:market_datetime} {VAR:market_location}</h1>
	
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
