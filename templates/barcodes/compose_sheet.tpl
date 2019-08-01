<section>
	<form action="{PAGEURL}" method="post" accept-charset="utf-8">

		<input type="text" value="composeSheet" name="action" />

		<div class="form-field">
			<label for="marketId">Markt</label><br>
			<select name="marketId" id="marketId">
				{LOOP VAR(markets)}
					<option value="{VAR:id}">{DATEFMT:"{VAR:market_datetime}":"%d.%m.%Y"}: {VAR:market_location}</option>
				{ENDLOOP VAR}
			</select>
		</div>

		<div>
			
			<input type="text" value="{VAR:userId}" name="userId" id="userId" readonly />
			<input type="text" value="{VAR:userEmail}" name="userEmail" id="userEmail" readonly />
		</div>

		<div>
			<input type="number" min="0" max="50" value="5" name="amount-50" id="amount-50">
			<label for="amount-50"> &times; 50 Cent</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="5" name="amount-100" id="amount-100">
			<label for="amount-100"> &times; 1,00 &euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="5" name="amount-150" id="amount-150">
			<label for="amount-150"> &times; 1,50 &euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="5" name="amount-200">
			<label for="amount-200"> &times; 2,00 &euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="5" name="amount-250">
			<label for="amount-200"> &times; 2,50 &euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="0" name="amount-custom-1">
			<label for="amount-custom-1"> &times; </label>
			<input type="text" name="value-custom-1" />
			<label for="value-custom-1">&euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="0" name="amount-custom-2">
			<label for="amount-custom-2"> &times; </label>
			<input type="text" name="value-custom-2" />
			<label for="value-custom-2">&euro;</label>
		</div>

		<div>
			<input type="number" min="0" max="50" value="0" name="amount-custom-3">
			<label for="amount-custom-3"> &times; </label>
			<input type="text" name="value-custom-3" />
			<label for="value-custom-3">&euro;</label>
		</div>

		<div class="action-area">
			<button type="submit">Gib mir die Codes!</button>
		</div>
		
	</form>
	
</section>
