<!-- TODO Schema.org / JSON+LD -->

<script  type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "event",
		"@name": "Kinderflohmarkt Erbach",
		"startDate": "{DATEFMT:'{VAR:market_begin}':'%Y-%m-%dT%H:%M'}",
		"endDate": "{DATEFMT:'{VAR:market_end}':'%Y-%m-%dT%H:%M'}",
		"location": {
			"@type": "Place",
			"name":  "Erlenbachhalle",
			"address": {
				"@type": "PostalAddress",
				"streetAddress": "Jahnstraße",
				"postalCode": "89155",
				"addressLocality": "Erbach",
				"addressRegion": "Baden-Württemberg",
				"addressCountry": "DE"
			}
		}
	}
</script>

<section class="market market--detail">

	<div class="callout">
		<div class="callout__head">
			{DATEFMT:"{VAR:market_begin}":"%a, %d. %B %Y":de_DE.utf-8}
		</div>
		<div class="callout__subline">
			<br>
			{DATEFMT:"{VAR:market_begin}":"%k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_end}":"%k:%M Uhr":de_DE.utf-8}<br>
			{VAR:market_location:nl2br}	
		</div>
	</div>

	
	<div class="market__description body-text">
		{VAR:market_description}
	</div>

	<div class="market__remark body-text">
		{VAR:market_remark}
	</div>

	<h4>Termine</h4>
	{IF("{VAR:marketNumberAssignmentIsUpcoming}" == "1")}
		<table>
			<tr>
				<td>Nummernvergabe</td>
				<td>
					<p>
						{DATEFMT:"{VAR:market_number_assignment_begin}":"%a %d.%m.%Y ab %k:%M":de_DE.utf-8} Uhr<br>
						<b>Die Anzahl der Verkäufer-Nummern ist begrenzt!<br></b>
						Die Nummernvergabe endet, sobald alle Nummern<br> vergeben sind.
					</p>
				</td>
			</tr>
			<tr>
				<td>Warenabgabe</td>
				<td>{DATEFMT:"{VAR:market_submission_begin}":"%a %d.%m.%Y %k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_submission_end}":"%k:%M":de_DE.utf-8} Uhr</td>
			</tr>
			<tr>
				<td>Warenabholung</td>
				<td>{DATEFMT:"{VAR:market_pickup_begin}":"%a %d.%m.%Y %k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_pickup_end}":"%k:%M":de_DE.utf-8} Uhr</td>
			</tr>
		</table>
		
	{ELSE}
		<div class="">Die Nummernvergabe für diesen Markt ist geschlossen</div>
	{ENDIF}

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
