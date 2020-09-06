<script src="{PATHTOWEBROOT}/dist/js/sellers_mail.js"></script>
<script src="{PATHTOWEBROOT}/dist/js/vendor/tabulator.min.js"></script>
<link rel="stylesheet" type="text/css" href="{PATHTOWEBROOT}/dist/css/vendor/tabulator.min.css" />
<link rel="stylesheet" type="text/css" href="{PATHTOWEBROOT}/dist/css/sellers_mail.css" />


<section id="sellers-mail">

	<h1>Rundmail verfassen</h1>

	<form id="sellers-mail-form" name="sellersMailForm" method="post" action="{SELFURL}">
		<input type="hidden" name="action" value="sendMail" />

		<fieldset class="recipients">
			<legend><span id="recipients-count">0</span> Empfänger</legend>

			<table id="recipients-table"> </table>

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
				<details class="info">
					<summary>[?] Info</summary>
					<p>Der Text wird geparsed und es stehen folgende Makros zur Verfügung:</p>  
					<dl>
						<dt><code>&#123;VAR:seller_firstname&#125;</code></dt>
						<dd>Verkäufer: Vorname</dd>
						<dt><code>&#123;VAR:seller_lastname&#125;</code></dt>
						<dd>Verkäufer: Vorname</dd>
						<dt><code>&#123;VAR:seller_nr&#125;</code></dt>
						<dd>Verkäufer-Nummer</dd>
						<dt><code>&#123;VAR:seller_email&#125;</code></dt>
						<dd>Verkäufer: E-Mail Adresse</dd>
					</dl>
				</details>
			</div>
			<div class="form-field">

				<div class="progress">
					<div class="progress-wrapper">
						<div id="js-progress" class="progress" data-label="">
							<span class="progress-value" style="width: 0%"></span>
						</div>
						<button id="form-button" class="cmtButton">Senden</button>
					</div>
				</div>

			</div>
		</fieldset>

		<fieldset class="settings">
			<legend>Einstellungen</legend>

			<div class="form-field">
				<label for="settings-batch-size">Serienlänge</label>
				<input type="text" name="batch_size" id="settings-batch-size" value="{VAR:batchSize}" />
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
	</form>
</section>
