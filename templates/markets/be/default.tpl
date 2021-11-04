<div class="tableHeadlineContainer">
	<div class="tableIcon">
		<img src="templates/default/administration/img/default_table_icon_xlarge.png" alt="">
	</div>
	<h1>Marktauswertung</h1>
</div>
<form action="" method="post">
	<input type="hidden" name="action" value="evaluate" />

	<div class="serviceContainer">
		<p>Die Marktauswertung lässt sich auch über die Tabelle "Märkte" starten</p>
	</div>

	<br>

	<div class="cmtEditEntryRow cmtEditEntryRow1">
		<div class="serviceContainer">
			<div class="form-field">
				<label for="market_id">Markt auswählen</label><br>
				<select name="marketId" id="market_id">
					{LOOP VAR(markets)}
					<option value="{VAR:id}">{DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}</option>
					{ENDLOOP VAR}
				</select>
				<button type="submit" class="cmtButton">Auswertung</button>
			</div>
		</div>

	</div>
</form>
