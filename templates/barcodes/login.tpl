<section>
	{IF({ISSET:error})}
		{SWITCH("{VAR:errorCode}")}
			{CASE("errorLoginFailed")}
				<div class="message message--error">Ungültige Kombination aus Verkäufer-Nummer und E-Mail Adresse</div>
			{BREAK}
			{CASE("errorNoMarkets")}
				<div class="message message--error">Im Moment gibt es keinen Flohmarkt</div>
			{BREAK}
		{ENDSWITCH}
	{ENDIF}
	<form action="{PAGEURL}" class="form stack" method="post" accept-charset="utf-8">

			<h4>Anmeldung für Verkäufer</h4>

			<input type="hidden" name="market_id" value="{VAR:market_id}" />
			<div>Flohmarkt am {DATEFMT:"{VAR:market_datetime}":"%d. %B %Y":de_DE.utf8}</div>

			<div class="form-field">
				<label for="seller_nr">Verkäufer-Nummer</label>
				<input {IF({ISSET:errorNoMarkets})}disabled{ENDIF} type="text" value="{VAR:seller_nr}" name="seller_nr" id="seller_nr" pattern="[0-9]{1,3}"  autofocus />
			</div>

			<div class="form-field">
				<label for="seller_email">E-Mail Adresse (bei Registrierung)</label>
				<input {IF({ISSET:errorNoMarkets})}disabled{ENDIF} type="email" value="{VAR:seller_email}" name="seller_email" id="seller_email"/>
			</div>

			<div class="form-field">
				<button {IF({ISSET:errorNoMarkets})}disabled{ENDIF} class="button" type="submit">Anmelden</button>
			</div>
			<div class="form--field">
				<p>
					Noch keine Verkäufernummer? <a href="{PAGEURL:7}?market_id={VAR:market_id}">Jetzt registrieren</a><br>
					Verkäufernummer vergessen? <a href="">Hier klicken</a>
				</p>
			</div>
	</form>
	
</section>
