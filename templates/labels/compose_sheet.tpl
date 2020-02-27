<section>
	<form id="composeform" class="form stack" action="{PAGEURL}" method="post" accept-charset="utf-8">
		<header class="form-header">
			<h2>Etiketten-Generator</h2>
		</header>

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


		<div class="stack columns amounts">
			<div class="form-field form-field--amount">
				<input id="amount_50" type="number" min="0" max="50" value="{IF({ISSET:amount_50:VAR})}{VAR:amount_50}{ELSE}0{ENDIF}" name="amount_50" id="amount_50">
				<label for="amount_50"> &times; 0,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_100" type="number" min="0" max="50" value="{IF({ISSET:amount_100:VAR})}{VAR:amount_100}{ELSE}0{ENDIF}" name="amount_100" id="amount_100">
				<label for="amount_100"> &times; 1,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_150" type="number" min="0" max="50" value="{IF({ISSET:amount_150:VAR})}{VAR:amount_150}{ELSE}0{ENDIF}" name="amount_150" id="amount_150">
				<label for="amount_150"> &times; 1,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_200" type="number" min="0" max="50" value="{IF({ISSET:amount_200:VAR})}{VAR:amount_200}{ELSE}0{ENDIF}" name="amount_200">
				<label for="amount_200"> &times; 2,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_250" type="number" min="0" max="50" value="{IF({ISSET:amount_250:VAR})}{VAR:amount_250}{ELSE}0{ENDIF}" name="amount_250">
				<label for="amount_250"> &times; 2,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_300" type="number" min="0" max="50" value="{IF({ISSET:amount_300:VAR})}{VAR:amount_300}{ELSE}0{ENDIF}" name="amount_300">
				<label for="amount_300"> &times; 3,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_350" type="number" min="0" max="50" value="{IF({ISSET:amount_350:VAR})}{VAR:amount_350}{ELSE}0{ENDIF}" name="amount_350">
				<label for="amount_350"> &times; 3,50 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_400" type="number" min="0" max="50" value="{IF({ISSET:amount_400:VAR})}{VAR:amount_400}{ELSE}0{ENDIF}" name="amount_400">
				<label for="amount_400"> &times; 4,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_500" type="number" min="0" max="50" value="{IF({ISSET:amount_500:VAR})}{VAR:amount_500}{ELSE}0{ENDIF}" name="amount_500">
				<label for="amount_500"> &times; 5,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_600" type="number" min="0" max="50" value="{IF({ISSET:amount_600:VAR})}{VAR:amount_600}{ELSE}0{ENDIF}" name="amount_600">
				<label for="amount_600"> &times; 6,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_700" type="number" min="0" max="50" value="{IF({ISSET:amount_700:VAR})}{VAR:amount_700}{ELSE}0{ENDIF}" name="amount_700">
				<label for="amount_700"> &times; 7,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount">
				<input id="amount_1000" type="number" min="0" max="50" value="{IF({ISSET:amount_1000:VAR})}{VAR:amount_1000}{ELSE}0{ENDIF}" name="amount_1000">
				<label for="amount_1000"> &times; 10,00 &euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_1" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_1:VAR})}{VAR:amount_custom_1}{ELSE}0{ENDIF}" name="amount_custom_1">
				<label for="amount_custom_1"> &times; </label>
				<input id="value_custom_1" type="text" name="value_custom_1" value="{IF({ISSET:value_custom_1:VAR})}{VAR:value_custom_1}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_1">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_2" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_2:VAR})}{VAR:amount_custom_2}{ELSE}0{ENDIF}" name="amount_custom_2">
				<label for="amount_custom_2"> &times; </label>
				<input id="value_custom_2" type="text" name="value_custom_2" value="{IF({ISSET:value_custom_2:VAR})}{VAR:value_custom_2}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_2">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_3" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_3:VAR})}{VAR:amount_custom_3}{ELSE}0{ENDIF}" name="amount_custom_3">
				<label for="amount_custom_3"> &times; </label>
				<input id="value_custom_3" type="text" name="value_custom_3"  value="{IF({ISSET:value_custom_3:VAR})}{VAR:value_custom_3}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_3">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_4" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_4:VAR})}{VAR:amount_custom_4}{ELSE}0{ENDIF}" name="amount_custom_4">
				<label for="amount_custom_4"> &times; </label>
				<input id="value_custom_4" type="text" name="value_custom_4"  value="{IF({ISSET:value_custom_4:VAR})}{VAR:value_custom_4}{ENDIF}" placeholder="Betrag"  pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_4">&euro;</label>
			</div>


			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_5" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_5:VAR})}{VAR:amount_custom_5}{ELSE}0{ENDIF}" name="amount_custom_5">
				<label for="amount_custom_5"> &times; </label>
				<input id="value_custom_5" type="text" name="value_custom_5" value="{IF({ISSET:value_custom_5:VAR})}{VAR:value_custom_5}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_5">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_6" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_6:VAR})}{VAR:amount_custom_6}{ELSE}0{ENDIF}" name="amount_custom_6">
				<label for="amount_custom_6"> &times; </label>
				<input id="value_custom_6" type="text" name="value_custom_6" value="{IF({ISSET:value_custom_6:VAR})}{VAR:value_custom_6}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_6">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_7" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_7:VAR})}{VAR:amount_custom_7}{ELSE}0{ENDIF}" name="amount_custom_7">
				<label for="amount_custom_7"> &times; </label>
				<input id="value_custom_7" type="text" name="value_custom_7"  value="{IF({ISSET:value_custom_7:VAR})}{VAR:value_custom_7}{ENDIF}" placeholder="Betrag" pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_7">&euro;</label>
			</div>

			<div class="form-field form-field--amount form-field--custom-amount">
				<input id="amount_custom_8" type="number" min="0" max="50" value="{IF({ISSET:amount_custom_8:VAR})}{VAR:amount_custom_8}{ELSE}0{ENDIF}" name="amount_custom_8">
				<label for="amount_custom_8"> &times; </label>
				<input id="value_custom_8" type="text" name="value_custom_8"  value="{IF({ISSET:value_custom_8:VAR})}{VAR:value_custom_8}{ENDIF}" placeholder="Betrag"  pattern="^[0-9]{0,3}(,((0|5)0?))?$" />
				<label for="value_custom_8">&euro;</label>
			</div>
		</div>


		<div class="action-area">
			<div class="form-info" id="sheet-info">&nbsp;</div>
			<button class="button" type="submit">PDF erzeugen</button>
		</div>

		<input type="hidden" value="composeSheet" name="action" />
		<input type="hidden" name="marketId" value="{SESSIONVAR:seller_market_id}" />
		<input type="hidden" name="sellerNr" value="{SESSIONVAR:seller_nr}" />
		<input type="hidden" name="sellerEmail" value="{SESSIONVAR:seller_email}" />

		<h2 class="headline">Vorschau</h2>
		<ul class="sheets">
		</ul>

	</form>
</section>
