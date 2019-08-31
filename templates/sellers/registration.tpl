{IF({ISSET:errorSellerExists})}
<div class="message message--error">
	Die E-Mail-Adresse &lt;{VAR:seller_email}&gt; wurde bereits für den Markt am
	{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"} registriert. Vielleicht hast du die Registrierung
	noch nicht abgeschlossen? Wir haben einen Aktivierungs-Link an
	&lt;{VAR:seller_email}&gt; gesendet. Wenn du keine E-Mail erhalten hast oder
	denkst, dass hier ein Fehler vorliegt, kontaktiere uns bitte unter
	<a href="mailto:info@kinderflohmarkt-erbach.de">&lt;info@kinderflohmarkt-erbach.de&gt;</a>
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
	Die eingegebenen E-Mail-Adressen stimmen nicht überein
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


<form id="registration" name="registration" class="form stack" action="{PAGEURL}" method="post" accept-charset="utf-8" novalidate>

	<p>Registrierung als Verkäufer für den Erbacher Kinderflohmarkt am {DATEFMT:"{VAR:market_datetime}":"%a, %d. %B %Y"}</p>
	<p><em><b>Hinweis: </b>Die mit einem <b>*</b> gekennzeichneten Felder müssen ausgefüllt werden</em></p>

	<div class="stack-item">
		<div class="form-field form-field--select">
			<input type="hidden" value="{VAR:market_id}" name="market_id" />
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

<script charset="utf-8">
	document.addEventListener('DOMContentLoaded', function() {
		
		var fields = document.querySelectorAll('input[name^=seller_]');
		fields.forEach(function(field) {

			field.addEventListener('blur', function() {
				var data = new FormData(document.forms.registration);
				data.append('fieldName', this.getAttribute('name'));
				// data.append('fieldValue', this.value);
				data.append('action', 'validateField');

				var xhr = new XMLHttpRequest();
				xhr.open('POST', '{PAGEURL}');
				xhr.onload = function() {
					if (this.status >= 200 && this.status < 400) {
						var data = JSON.parse(this.response);
						field.parentNode.classList.add('ssv');
						field.parentNode.classList.toggle('ssv-valid', data.success);
					}
				}
				xhr.send(data);
			});
		});

		setInterval(updateAvailableSellerNrs, 10000);

		var selectEl = document.querySelector('[name=seller_nr]');
		var marketId = parseInt(document.querySelector('[name=market_id]').value);

		function updateAvailableSellerNrs() {
			
			var xhr = new XMLHttpRequest();
			xhr.open('GET', '{PAGEURL}?action=updateAvailableSellerNrs&marketId=' + marketId);
			xhr.onload = function() {
				if (this.status >= 200 && this.status < 400) {
					var data = JSON.parse(this.response);
					var numbers = Object.keys(data).map(function(key) { return data[key]; });

					// var selectedNr = select.querySelector('[selected]').value;
					selectEl.innerHTML = '';
					for (var i = 0; i < numbers.length; i++) {
						nr = numbers[i];
						var optionEl = document.createElement('option');
						optionEl.setAttribute('value', nr);
						optionEl.innerText = nr;
						// if (nr == selectedNr) {
						// 	option.selected = true;
						// }
						selectEl.appendChild(optionEl);
					}
				}
			}
			xhr.send();
		}

	});
</script>
