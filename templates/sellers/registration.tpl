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
{IF({ISSET:errorInvalidEmail})}
<div class="message message--error">
	Bitte geben Sie eine gültige E-Mail-Adresse ein
</div>
{ENDIF}
{IF({ISSET:errorEmailsDontMatch})}
<div class="message message--error">
	Die E-Mail-Adressen stimmen nicht überein
</div>
{ENDIF}
<form class="form stack" action="{PAGEURL}" method="post" accept-charset="utf-8">

	<div class="form-field form-field--select stack-item">
		<label for="market_id">Markt</label>
		<select disabled name="market_id" id="market_id">
			{LOOP VAR(markets)}
				<option value="{VAR:id}" {IF("{VAR:market_id}" == "{VAR:id}")}selected{ENDIF}>{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"}</option>
			{ENDLOOP VAR}
		</select>
	</div>

	<div class="form-field stack-item">
		<label for="email">E-Mail</label>
		<input type="text" name="email" id="email" value="{VAR:email}" />
	</div>

	<div class="form-field stack-item">
		<label for="email_confirm">E-Mail wiederholen</label>
		<input type="text" name="email_confirm" id="email_confirm" value="" />
	</div>

	<div class="action-area stack-item">
		<button type="submit" class="button">Registrieren</button>
	</div>
	
</form>
