{IF({ISSET:errorSellerExists})}
<div class="message message--error">
	Die E-Mail-Adresse &lt;{VAR:email}&gt; wurde bereits für den Markt am
	{VAR:market_datetime} registriert. Vielleicht haben Sie die Registrierung
	noch nicht abgeschlossen? Wir haben einen Aktivierungs-Link an
	&lt;{VAR:email}&gt; gesendet. Wenn Sie keine E-Mail erhalten haben oder
	denken, dass hier ein Fehler vorliegt, kontaktieren Sie uns bitte unter
	&lt;info@kinderflohmarkt-erbach.de&gt;
</div>
{ENDIF}
{IF({ISSET:errorSellerExists})}
<div class="message message--error">
	Es ist ein interner Fehler aufgetreten. Bitte versuchen Sie es erneut oder
	nehmen Sie Kontakt mit uns auf 
</div>
{ENDIF}
<form action="" method="post" accept-charset="utf-8">

	<div class="form-field form-field--select">
		<label for="market_id">Markt</label>
		<select name="market_id" id="market_id">
			{LOOP VAR(markets)}
			<option value="{VAR:id}" {IF("{VAR:market_id}" == "{VAR:id}")}selected{ENDIF}>{VAR:market_datetime}</option>
			{ENDLOOP VAR}
		</select>
	</div>

	<div class="form-field">
		<label for="email">E-Mail</label>
		<input name="email" id="email" value="{VAR:email}" />
	</div>

	<div class="action-area">
		<button type="submit">Registrieren</button>
	</div>
	
</form>
