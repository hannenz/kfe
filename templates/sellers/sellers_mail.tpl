<script src="{PATHTOWEBROOT}/dist/js/sellers_mail.js"></script>
<script src="{PATHTOWEBROOT}/dist/js/vendor/tabulator.min.js"></script>
<link rel="stylesheet" type="text/css" href="{PATHTOWEBROOT}/dist/css/vendor/tabulator.min.css" />
<style type="text/css">

#sellers-mail-form {
	display: grid;
	grid-template-columns: repeat(12, 1fr);
}

fieldset.recipients {
	grid-column: 1 / 6;
}

fieldset.message {
	grid-column: 6 / -1;
}

fieldset.settings {
	grid-column: 1 / -1;
	grid-row: 2;
}

</style>


<section id="sellers-mail">

	<h1>Rundmail</h1>

	<form id="sellers-mail-form" name="sellersMailForm" method="post" action="{SELFURL}">
		<input type="hidden" name="action" value="sendMail" />

		<fieldset class="recipients">
			<legend><span id="recipients-count">0</span> Empfänger</legend>

			<table id="recipients-table">
				<tr class="recipient">
					<td>{VAR:id}</td>
					<td>{VAR:seller_nr}</td>
					<td>{VAR:seller_email}</td>
					<td>{VAR:seller_lastname}</td>
					<td>{VAR:seller_firstname}</td>
				</tr>
			</table>

			<div class="action-area button-bar">
				<button id="js-add-recipients-btn" class="cmtButton cmtButtonAdd">Empfänger hinzufügen</button>
				<select id="market-id">
					{LOOP VAR(markets)}
						<option value="{VAR:id}">#{VAR:id} &ndash; {DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}</option>
					{ENDLOOP VAR}
				</select>
				<button id="js-add-recipients-by-market-btn" class="cmtButton cmtButtonAdd">Alle Verkäufer eines Marktes hinzufügen</button>
				<button id="js-add-recipients-employees-btn" class="cmtButton cmtButtonAdd">Alle Mitarbeiter hinzufügen</button>
				<button id="js-remove-all-recipients-btn" class="cmtButton cmtButtonDelete">Alle Empfänger entfernen</button>
			</div>
		</fieldset>



		<fieldset class="message">
			<legend>Nachricht verfassen</legend>
			<div class="form-field">
				<label for="subject">Betreff</label><br>
				<input type="text" name="subject" id="subject" value="{VAR:subject}" required />
			</div>
			<div class="form-field">
				<label for="text">Text</label><br>
				<textarea name="text" id="text" cols="100" rows="20" required>{VAR:text}</textarea>
			</div>
			<div class="form-field">
				<button class="cmtButton">Senden</button>
			</div>
		</fieldset>

		<fieldset class="settings">
			<legend>Einstellungen</legend>

			<div class="form-field">
				<label for="settings-batch-size">Serienlänge</label>
				<input type="number" min="1" max="10" name="batch_size" id="settings-batch-size" value="{VAR:batchSize}" />
			</div>

			<div class="form-field">
				<label for="sender-mail">Absender E-Mail Adresse</label>
				<input type="text" name="sender_email" value="{VAR:senerEmail}" />
			</div>

		</fieldset>
		<fieldset>
			<legend>Versand-Fortschritt</legend>
			<progress id="js-progress"></progress>
		</fieldset>
	</form>
</section>
