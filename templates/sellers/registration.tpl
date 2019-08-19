{IF({ISSET:errorSellerExists})}
<div class="message message--error">
	Die E-Mail-Adresse &lt;{VAR:seller_email}&gt; wurde bereits für den Markt am
	{VAR:market_datetime} registriert. Vielleicht haben Sie die Registrierung
	noch nicht abgeschlossen? Wir haben einen Aktivierungs-Link an
	&lt;{VAR:seller_email}&gt; gesendet. Wenn Sie keine E-Mail erhalten haben oder
	denken, dass hier ein Fehler vorliegt, kontaktieren Sie uns bitte unter
	&lt;info@kinderflohmarkt-erbach.de&gt;
</div>
{ENDIF}
{IF({ISSET:errorUnknown})}
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
{IF({ISSET:errorSellerNrAlreadyAllocated})}
<div class=" class="message message--error"">
	Die Verkäufer-Nummer ist bereits vergeben.
</div>
{ENDIF}
{IF({ISSET:hasValidationErrors})}
<div class="message message--error">
	Bitte prüfe die rot markierten Felder
</div>
{ENDIF}
{IF({ISSET:error_agree})}
<div class="message message--error">
	Du musst unsere Datenschutzbestimmungen akzeptieren, damit wir Deine Registrierung aufnehmen dürfen.
</div>
{ENDIF}


<form class="form stack" action="{PAGEURL}" method="post" accept-charset="utf-8" novalidate>

	<div class="stack-item">
		<div class="form-field form-field--select">
			<label for="market_id">Ich registriere mich als Verkäufer für den Erbacher Kinderflohmarkt am</label>
			<select not-disabled name="market_id" id="market_id">
				{LOOP VAR(markets)}
					<option value="{VAR:id}" {IF("{VAR:market_id}" == "{VAR:id}")}selected{ENDIF}>{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"}</option>
				{ENDLOOP VAR}
			</select>
		</div>
	</div>

	<div class="stack-item">
		<div class="form-field form-field--input form-field--required {IF({ISSET:error_seller_firstname})}form-field--error{ENDIF}">
			<label for="firstname">Vorname</label>
			<input type="text" value="{VAR:seller_firstname}" name="seller_firstname" id="firstname" required autofocus />
		</div>
		<div class="form-field form-field--input form-field--required {IF({ISSET:error_seller_lastname})}form-field--error{ENDIF}">
			<label for="lastname">Nachname</label>
			<input type="text" value="{VAR:seller_lastname}" name="seller_lastname" id="lastname" required />
		</div>
	</div>

	<div class="stack-item">

		<div class="form-field form-field--input form-field--required {IF({ISSET:error_seller_email})}form-field--error{ENDIF}">
			<label for="email">E-Mail</label>
			<input type="email" name="seller_email" id="email" value="{VAR:seller_email}" required />
		</div>

		<div class="form-field form-field--input form-field--required {IF({ISSET:error_seller_email_confirm})}form-field--error{ENDIF}">
			<label for="email_confirm">E-Mail wiederholen</label>
			<input type="email" name="seller_email_confirm" id="email_confirm" value="{VAR:seller_email_confirm}" required />
		</div>
	</div>

	<div class="stack-item">
		<div class="form-field form-field--input">
			<label for="phone">Telefon (für Rückfragen)</label>
			<input type="text" value="{VAR:seller_phone}" name="seller_phone" id="phone" />
		</div>
	</div>

	<div class="stack-item number-select">
		<div class="form-field form-field--select">
			<label for="seller_nr">Verkäufer-Nummer</label>
			<select name="seller_nr" id="seller_nr">
			{LOOP VAR(availableNumbers)}
				<option value="{VAR:nr}" {IF("{VAR:seller_nr}" == "{VAR:nr}")}selected{ENDIF}>{VAR:nr}</option>
				<!-- <div class="number&#45;select&#45;option"> -->
					<!-- <input type="radio" name="seller_nr" value="{VAR:nr}" id="seller&#45;nr&#45;{VAR:nr}" {IF("{VAR:seller_nr}" == "{VAR:nr}")}checked{ENDIF}/> -->
				<!-- 	<label for="seller&#45;nr&#45;{VAR:nr}">{VAR:nr}</label> -->
				<!-- </div> -->
			{ENDLOOP VAR}
			</select>
		</div>
	</div>

	<div class="stack-item">
		<div class="form-field form-field--checkbox {IF({ISSET:error_agree})}form-field--error{ENDIF}">
			<input type="checkbox" name="agree" value="agreed" id="agree" {IF("{VAR:agree}" == "agreed")}checked{ENDIF} />
			<label for="agree">Ich habe die <a href="{PAGEURL:11}" target="_blank">Datenschutzbestimmungen</a> gelesen und erkläre mich einverstanden
		</div>
	</div>

	<div class="stack-item">
		<div class="action-area">
			<button type="submit" class="button">Registrieren</button>
		</div>
	</div>
	
</form>
