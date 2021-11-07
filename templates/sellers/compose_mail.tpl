<style>
[name=sellersMailForm] {
	width: 70rem;
}

details {
	margin: 3rem 0 1.5rem 0;
}

input[type=text], textarea {
	width: 100%;
	border-radius: 0;
	box-shadow: none;
	border: 1px solid rgba(0, 0, 0, 0.2);
	padding: 0.5em;
}

button[type=submit] {
	font-family: inherit;
	font-size: inherit;
	border: 0;
	border-radius: 0;
	padding: 0.5rem 1rem;
	background-color: #4d7013;
	color: #fff;
}

button[type=submit] > svg,
button[type=submit] > span {
	vertical-align: middle;
}

button[type=submit] > svg {
	width: 1.5rem;
	margin-right: 0.5rem;
}

progress {
	width: 100%;
}

progress[value] {
	background-color: #4d7013;
}

.form-field + .form-field {
	margin-top: 1.5rem;
}

#recipients-table {
	border-collapse: collapse;
		width: 100%;
}

#recipients-table td {
	padding: 0.75rem 0;
	vertical-align: middle;
	text-align: left;
	background-color: #fcfdfc;
	border-top: 1px solid rgba(0, 0, 0, 0.1);
}


</style>
<section style="padding: 1.5rem">
	<h2>Rundmail Markt #{VAR:id}</h2>
	<p>Verfassen Sie eine Rundmail an alle {COUNT:sellers} Verkäufer des Marktes #{VAR:id} am {DATEFMT:"{VAR:market_begin}"} inklusive Mitarbeiter</p>

	<form style="margin-top: 1.5rem" name="sellersMailForm" action="{SELFURL}" method="POST">

		<details xopen>
			<summary>Details und individuelle Empfängerauswahl</summary> 
			<div style="max-height: 30rem; overflow: auto">
				<table id="recipients-table">
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


		<input type="hidden" value="send" name="action" id="action" />

		<div class="form-field">
			<label for="subject">Absender: E-Mail-Adresse</label><br>
			<input type="text" id="subject" name="senderMail" value="{VAR:senderMail}" />
		</div>

		<div class="form-field">
			<label for="subject">Absender: Name</label><br>
			<input type="text" id="subject" name="senderName" value="{VAR:senderName}" />
		</div>

		<div class="form-field">
			<label for="subject">Betreff</label><br>
			<input type="text" id="subject" name="subject" value="{VAR:subject}" />
		</div>

		<div class="form-field">
			<label for="message">Text</label><br>
			<textarea name="message" id="message" rows="10" cols="100">{VAR:message}

--
{CONSTANT:WEBNAME}
https://{ENVVAR:SERVER_NAME}</textarea>
		</div>

		<div class="form-field">
			<button id="form-button" type="submit">
				{INCLUDE:INCLUDEPATHTOADMIN.'templates/default/administration/img/icons/feather/send.svg'}
				<span id="form-button-label">Senden</span>
			</button>
		</div>

		<div class="live-area" aria-live>
			<progress id="js-progressbar" min="0" value="0.6">0 von <span id="js-total">0</span> Mails versendet</progress>
			<label for="js-progressbar"></label>
		</div>
	</form>
</section>
<script src="/dist/js/SellersMail.js"> </script>
