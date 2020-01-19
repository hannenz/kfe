<form action="{SELFURL}" method="post">
	<input type="hidden" name="action" value="evaluate" />
	<div class="form-field">
		<label for="market_id">Markt auswählen</label>
		<select name="market_id" id="market_id">
			{LOOP VAR(markets)}
				<option value="{VAR:id}">{DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}</option>
			{ENDLOOP VAR}
		</select>
	</div>
	<div class="form-field form-field--select">
		<button type="submit" class="cmtButton">Auswertung</button>
	</div>
</form>

<!-- <a target="_blank" class="cmtButton" href="{SELFURL}&#38;action=evaluate&#38;id={VAR:marketId}">Auswertung</a> -->
