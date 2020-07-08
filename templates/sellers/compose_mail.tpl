<form name="sellersMailForm" action="{SELFURL}" method="POST">

	<p>Verfassen Sie eine Rundmail an alle {COUNT:sellers} Verkäufer von Markt #{VAR:sellerMarketId} (inkl. Mitarbeiter)</p>

	<details>
		<summary>Details und individuelle Auswahl</summary> 
		<div>
			<table>
				<thead>
					<tr>
						<th></th>
						<th>Verk-Nr.</th>
						<th>E-Mail</th>
						<th>Vorname</th>
						<th>Nachname</th>
					</tr>
				</thead>
				{LOOP VAR(sellers)}
				<tr>
					<td><input type="checkbox" value="{VAR:id}" name="id[]" checked /></td>
					<td>{VAR:seller_nr}</td>
					<td>{VAR:seller_email}</td>
					<td>{VAR:seller_firstname}</td>
					<td>{VAR:seller_lastname}</td>
				</tr>
				{ENDLOOP VAR}
			</table>
		</div>
	</details>


	<input type="hidden" value="sendMail" name="action" id="action" />

	<div class="form-field">
		<label for="subject">Betreff</label>
		<input type="text" id="subject" name="subject" value="{VAR:subject}" />
	</div>

	<div class="form-field">
		<label for="message">Text</label>
		<textarea name="message" id="message" rows="10" cols="100">{VAR:message}</textarea>
	</div>

	<div class="form-field">
		<button type="submit">Senden</button>
	</div>

	<div class="live-area" aria-live>
		<progress id="js-progressbar" value="0">0 von <span id="js-total">0</span> Mails versendet</progress>
	</div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {

	var progressbar = document.getElementById('js-progressbar');
	var total = document.getElementById('js-total');


});
</script>

