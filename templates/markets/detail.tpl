<!-- Foo Bar -->

<script  type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "event",
		"name": "Kinderflohmarkt Erbach",
		"description": "{VAR:market_description:strip_tags}",
		"startDate": "{VAR:marketBeginISO8601}",
		"endDate": "{VAR:marketEndISO8601}",
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

<section class="market market--detail stack">

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

	<div class="stack">
		<div class="number-assignment-info-box">

			{IF("{VAR:marketNumberAssignmentIsRunning}" == "1")}
				{IF("{COUNT:availableNumbers}" != "0")}
					<p>
						<a class="button" href="{VAR:registrationUrl}">Nummernvergabe: Hier klicken</a>
					</p>
					<p>
						<b>Die Anzahl der Verkäufer-Nummern ist begrenzt!<br></b>
						Die Nummernvergabe endet, sobald alle Nummern<br> vergeben sind.
					</p>
				{ELSE}
					<p>
						<strong>Für diesen Markt sind leider bereits alle Nummern vergeben.</strong><br>
						Nochmal vorbeischauen lohnt sich, manchmal werden Nummern wieder frei!
					</p>
				{ENDIF}
			{ELSE}
				<p>
					Die Nummernvergabe startet am<br>
					<strong> {DATEFMT:"{VAR:market_number_assignment_begin}":"%a. %d.%m.%Y ab %k:%M Uhr":de_DE.utf-8}</strong><br>
					Ausschliesslich online!<br>
					Keine Nummernvergabe per E-Mail oder Telefon!
				</p>
			{ENDIF}
		</div>
	</div>
	
	<div class="market__description body-text">
		{VAR:market_description}
	</div>

	<div class="market__remark body-text">
		{VAR:market_remark}
	</div>


	<table class="market-dates">
		{IF("{VAR:market_submission_begin}" != "0000-00-00 00:00:00")}
		<tr>
			<td>Warenabgabe</td>
			<td>{DATEFMT:"{VAR:market_submission_begin}":"%a. %d.%m.%Y":de_DE.utf-8}</td>
			<td>{DATEFMT:"{VAR:market_submission_begin}":"%k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_submission_end}":"%k:%M":de_DE.utf-8} Uhr</td>
		</tr>
		{ENDIF}
		{IF("{VAR:market_submission_end}" != "0000-00-00 00:00:00")}
		<tr>
		<tr>
			<td>Warenabholung</td>
			<td>{DATEFMT:"{VAR:market_pickup_begin}":"%a. %d.%m.%Y":de_DE.utf-8}</td>
			<td>{DATEFMT:"{VAR:market_pickup_begin}":"%k:%M":de_DE.utf-8}&thinsp;&ndash;&thinsp;{DATEFMT:"{VAR:market_pickup_end}":"%k:%M":de_DE.utf-8} Uhr</td>
		</tr>
		{ENDIF}
	</table>

	<div class="market_media">
		{IF({COUNT:marketDocuments})}
		<h2 class="headline">Downloads</h2>
		<ul class="market-decouments">
			{LOOP VAR(marketDocuments)}
				<li>
					{VAR:media_title} <br>
					<a href="/media/markets/{VAR:media_document_file_internal}" download>
						{VAR:media_document_file}
					</a>
				</li>
			{ENDLOOP VAR}
		</ul>
		{ENDIF}

		{IF({COUNT:marketMaps})}
		<h2 class="headline">Anfahrt</h2>
		<div class="market-maps">
			{LOOP VAR(marketMaps)}
					<figure>
						<div class="project-map" data-lat="{VAR:media_map_latitude}" data-lon="{VAR:media_map_longitude}" data-zoom="{VAR:media_map_zoom}" data-title="{VAR:media_title}" data-description="{VAR:media_description}" style="min-height: 360px;"></div>
					</figure>
			{ENDLOOP VAR}
		</div>
		{ENDIF}

	</div>
</section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js" integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var maps = document.querySelectorAll('.project-map');
	for (var i = 0; i < maps.length; i++) {
		var mapEl = maps[i];
		var map = L.map(mapEl).setView([mapEl.dataset.lat, mapEl.dataset.lon], mapEl.dataset.zoom);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		L.marker([mapEl.dataset.lat, mapEl.dataset.lon]).addTo(map)
			.bindPopup('<strong>' + mapEl.dataset.title + '</strong><br>' + mapEl.dataset.description)
		    .openPopup();
	}
});
</script>
