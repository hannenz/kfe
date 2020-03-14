<form action="{SELFURL}" method="POST">
	<input type="hidden" value="sendMail" name="action" id="action" />
	<div class="form-field">
		<label for="subject">Betreff</label>
		<input type="text" id="subject" name="subject" value="{VAR:subject}" />
	</div>
	<div class="form-field">
		<label for="message">Text</label>
		<textarea name="message" id="message">{VAR:message}</textarea>
	</div>
	<div class="form-field">
		<button type="submit">Senden</button>
	</div>
</form>
