<script src="{PATHTOWEBROOT}/dist/js/sellers_mail.js"></script>
<script src="{PATHTOWEBROOT}/dist/js/vendor/tabulator.min.js"></script>
<link rel="stylesheet" type="text/css" href="{PATHTOWEBROOT}/dist/css/vendor/tabulator.min.css" />
<style type="text/css">

#sellers-mail-form {
	display: grid;
	grid-template-columns: repeat(12, 1fr);
}

fieldset.recipients {
	grid-column: 1 / 8;
}

fieldset.message {
	grid-column: 8 / -1;
}

fieldset.settings {
	grid-column: 1 / -1;
	grid-row: 2;
}

fieldset.progress {
	grid-column: 1 / -1;
}

input, textarea {
	width: 100%;
}

#sellers-mail-form {
	transition: 150ms ease-out;
}

#sellers-mail-form.is-busy {
	opacity: 0.2;
	pointer-events: none;
}

					.progress-wrapper {
						display: flex;
					}

</style>


<section id="sellers-mail">

	<h1>Rundmail verfassen</h1>

	<form id="sellers-mail-form" name="sellersMailForm" method="post" action="{SELFURL}">
		<input type="hidden" name="action" value="sendMail" />

		<fieldset class="recipients">
			<legend><span id="recipients-count">0</span> Empfänger</legend>

			<table id="recipients-table">
				<!-- <tr class="recipient"> -->
				<!-- 	<td>{VAR:id}</td> -->
				<!-- 	<td>{VAR:seller_nr}</td> -->
				<!-- 	<td>{VAR:seller_email}</td> -->
				<!-- 	<td>{VAR:seller_lastname}</td> -->
				<!-- 	<td>{VAR:seller_firstname}</td> -->
				<!-- </tr> -->
			</table>

			<div class="action-area button-bar">
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
				<textarea name="text" id="text" cols="" rows="20" required>{VAR:text}</textarea>
			</div>
			<div class="form-field">
				<button class="cmtButton">Senden</button>
			</div>
		</fieldset>

		<fieldset class="settings">
			<legend>Einstellungen</legend>

			<div class="form-field">
				<label for="settings-batch-size">Serienlänge</label>
				<input type="number" min="1" max="100" name="batch_size" id="settings-batch-size" value="{VAR:batchSize}" />
			</div>

			<div class="form-field">
				<label for="settings-batch-pause">Pause zwischen Versand (in Sekunden)</label>
				<input type="text" min="1" max="100" name="batch_pause" id="settings-batch-pause" value="{VAR:batchPause}" />
			</div>

			<div class="form-field">
				<label for="sender-mail">Absender E-Mail Adresse</label>
				<input type="text" name="sender_email" value="{VAR:senderEmail}" />
			</div>

		</fieldset>
		<fieldset class="progress">
			<legend>Versand-Fortschritt</legend>
			<div class="progress-wrapper">
				<progress value="0" id="js-progress" style="width: 100%"></progress>
				<button id="cancel">Abbrechen</button>
			</div>
		</fieldset>
	</form>
</section>
