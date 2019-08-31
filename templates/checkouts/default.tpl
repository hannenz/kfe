{IF({ISSET:error:VAR})}
<div class="cmtMessage cmtErrorMessage">
	{VAR:error}
</div>
{ENDIF}

<form action="" method="post" accept-charset="utf-8">
	<div class="form-field">
		<label for="checkoutId">Kassen-Nr (Checkout-ID)</label><br>
		<input type="number" value="{IF({ISSET:checkoutId})}{VAR:checkoutId}{ELSE}1{ENDIF}" name="checkoutId" id="checkoutId" />
	</div>
	<div class="form-field">
		<label for="marketId">Markt</label><br>
		<!-- <input type="number" value="{VAR:marketId}" name="marketId" id="marketId" /> -->
		<select name="marketId" id="marketId">
			{LOOP VAR(markets)}
			<option value="{VAR:id}">{VAR:market_datetime}</option>
			{ENDLOOP VAR}
		</select>
	</div>
	<div class="form-field form-field--submit">
		<button class="cmtButton">Los</button>
	</div>
</form>
