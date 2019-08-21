<section>
	<form id="composeform" class="form stack" action="{PAGEURL}" method="post" accept-charset="utf-8">

		<h2 class="noheadline">Etiketten drucken</h2>

		<input type="hidden" value="composeSheet" name="action" />

		<div class="info">
			Wählen Sie hier, wie viele Etiketten Sie benötigen. Anschliessend erhalten Sie eine PDF Datei zum Ausdrucken.<br>
			<!-- Die Etiketten haben links und rechts 1cm Rand zum Lochen, bitte achten Sie jedoch bem Lochen darauf, nicht den Strichcode zu lochen -->
			<!-- Der Strichcode beinhaltet Ihre Verkäufer&#45;Nummer, das Datum des Flohmarkts und den Wert (in Cent). Bitte beachten Sie, dass die Etiketten nur für den ausgewählten Flohmarkt gelten. -->
			<!-- Alte Etiketten von vergangenen FLohmärkten können <b>nicht</b> benutzt werden. -->
		</div>

		{IF({ISSET:errorIllegalSellerNr})}
			<div class="message message--error">
				Ungültige Verkäufer-Nummer: {VAR:sellerNr}
			</div>
		{ENDIF}

		{IF({ISSET:errorIllegalMarketId})}
			<div class="message message--error">
				Ungültige Flohmarkt-ID: {VAR:marketId}
			</div>
		{ENDIF}

		{IF({ISSET:errorEmailMismatch})}
			<div class="message message--error">
				Verkäufer-Nummer und E-Mail stimmen nicht überein
			</div>
		{ENDIF}

		<fieldset class="stack">
			<div class="form-field form-field--select">
				<label for="marketId">Markt</label>
				<select name="marketId" id="marketId">
					{LOOP VAR(markets)}
					<option value="{VAR:id}" {IF("{VAR:id}" == "{VAR:marketId}")}selected{ENDIF}>{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"}: {VAR:market_location}</option>
					{ENDLOOP VAR}
				</select>
			</div>

			<div class="form-field form-field--input">
				<label for="sellerNr">Verkäufer-Nummer</label>
				<input id="sellerNr" type="text" value="{VAR:sellerNr}" name="sellerNr" autofocus pattern="[0-9]{1,3}" />
				<div class="form-field__info">
					<a href="{PAGEURL:999}">Verkäufer-Nr vergessen</a>
				</div>
			</div>

			<div class="form-field form-field--input">
				<label for="sellerEmail">E-Mail-Adresse</label>
				<input id="sellerEmail" type="email" value="{VAR:sellerEmail}" name="sellerEmail" />
				<div class="form-field__info">
					Geben Sie zum Abgleich die E-Mail Adresse ein, mit der Sie sich zur Nummernvergabe registriert haben.
				</div>
			</div>
		</fieldset>

		<fieldset class="stack columns amounts">
			<div class="form-field form-field--amount">
				<input id="amount_50" type="number" min="0" max="50" value="{IF({ISSET:amount_50:VAR})}{VAR:amount_50}{ELSE}3{ENDIF}" name="amount_50" id="amount_50">
				<label for="amount_50"> &times; 0,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_100" type="number" min="0" max="50" value="{IF({ISSET:amount_100:VAR})}{VAR:amount_100}{ELSE}3{ENDIF}" name="amount_100" id="amount_100">
				<label for="amount_100"> &times; 1,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_150" type="number" min="0" max="50" value="{IF({ISSET:amount_150:VAR})}{VAR:amount_150}{ELSE}3{ENDIF}" name="amount_150" id="amount_150">
				<label for="amount_150"> &times; 1,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_200" type="number" min="0" max="50" value="{IF({ISSET:amount_200:VAR})}{VAR:amount_200}{ELSE}3{ENDIF}" name="amount_200">
				<label for="amount_200"> &times; 2,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_250" type="number" min="0" max="50" value="{IF({ISSET:amount_250:VAR})}{VAR:amount_250}{ELSE}3{ENDIF}" name="amount_250">
				<label for="amount_250"> &times; 2,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_300" type="number" min="0" max="50" value="{IF({ISSET:amount_300:VAR})}{VAR:amount_300}{ELSE}3{ENDIF}" name="amount_300">
				<label for="amount_300"> &times; 3,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_350" type="number" min="0" max="50" value="{IF({ISSET:amount_350:VAR})}{VAR:amount_350}{ELSE}3{ENDIF}" name="amount_350">
				<label for="amount_350"> &times; 3,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_400" type="number" min="0" max="50" value="{IF({ISSET:amount_400:VAR})}{VAR:amount_400}{ELSE}3{ENDIF}" name="amount_400">
				<label for="amount_400"> &times; 4,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_500" type="number" min="0" max="50" value="{IF({ISSET:amount_500:VAR})}{VAR:amount_500}{ELSE}3{ENDIF}" name="amount_500">
				<label for="amount_500"> &times; 5,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_600" type="number" min="0" max="50" value="{IF({ISSET:amount_600:VAR})}{VAR:amount_600}{ELSE}3{ENDIF}" name="amount_600">
				<label for="amount_600"> &times; 6,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_700" type="number" min="0" max="50" value="{IF({ISSET:amount_700:VAR})}{VAR:amount_700}{ELSE}3{ENDIF}" name="amount_700">
				<label for="amount_700"> &times; 7,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_1000" type="number" min="0" max="50" value="{IF({ISSET:amount_1000:VAR})}{VAR:amount_1000}{ELSE}3{ENDIF}" name="amount_1000">
				<label for="amount_1000"> &times; 10,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_1" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_1:VAR})}{VAR:amount_custom_1}{ELSE}0{ENDIF}" name="amount_custom_1">
				<label for="amount_custom_1"> &times; </label>
				<input id="value_custom_1" type="text" name="value_custom_1" value="{IF({ISSET:value_custom_1:VAR})}{VAR:value_custom_1}{ENDIF}" placeholder="Betrag" />
				<label for="value_custom_1">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_2" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_2:VAR})}{VAR:amount_custom_2}{ELSE}0{ENDIF}" name="amount_custom_2">
				<label for="amount_custom_2"> &times; </label>
				<input id="value_custom_2" type="text" name="value_custom_2" value="{IF({ISSET:value_custom_2:VAR})}{VAR:value_custom_2}{ENDIF}" placeholder="Betrag" />
				<label for="value_custom_2">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_3" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_3:VAR})}{VAR:amount_custom_3}{ELSE}0{ENDIF}" name="amount_custom_3">
				<label for="amount_custom_3"> &times; </label>
				<input id="value_custom_3" type="text" name="value_custom_3"  value="{IF({ISSET:value_custom_3:VAR})}{VAR:value_custom_3}{ENDIF}" placeholder="Betrag" />
				<label for="value_custom_3">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_4" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_4:VAR})}{VAR:amount_custom_4}{ELSE}0{ENDIF}" name="amount_custom_4">
				<label for="amount_custom_4"> &times; </label>
				<input id="value_custom_4" type="text" name="value_custom_4"  value="{IF({ISSET:value_custom_4:VAR})}{VAR:value_custom_4}{ENDIF}" placeholder="Betrag" />
				<label for="value_custom_4">&euro;</label>
			</div>
		</fieldset>

		<div class="action-area">
			<button class="button" type="submit">Gib mir die Codes!</button>
		</div>
	</form>
</section>
