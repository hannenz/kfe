<div class="cmtLayer">
	<div class="cmtLayerHandle" id="cmtLayerSearchAndSort">
		<div class="cmt-layer-icon cmt-font-icon cmt-icon-filter"></div>
		<div class="cmt-layer-open-close cmt-font-icon cmt-icon-open-close"></div>
		Zeige Verkäufer
		{IF("{SESSIONVAR:sellerMarketId}" == "0")}
			die keinem speziellen Markt zugeordnet sind (Mitarbeiter)
		{ENDIF}
		{IF("{SESSIONVAR:sellerMarketId}" == "9999")}
			für alle Märkte
		{ENDIF}
		{LOOP VAR(markets)}
			{IF("{VAR:id}" == "{SESSIONVAR:sellerMarketId}")}
				für den Markt am {DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}
			{ENDIF}
		{ENDLOOP VAR}
	</div>
	<div class="cmtLayerContent">
		<form name="selectTableForm" action="{SELFURL}" method="post">
			<div class="serviceContainer">
				<span class="serviceText">Markt wählen</span>&nbsp;
				<select name="sellerMarketId">
					<option value="0" {IF("{VAR:sellerMarketId}" == "0")}selected="selected"{ENDIF}>Keinem speziellen Markt zugeordnet (Mitarbeiter, 0)</optiona>
					{LOOP VAR(markets)}
						<option
							value="{VAR:id}"
							{IF("{SESSIONVAR:sellerMarketId}" == "{VAR:id}")}selected="selected"{ENDIF}>
							Markt am {DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"} ({VAR:id})
						</option>
					{ENDLOOP VAR}
					<option value="9999" {IF("{SESSIONVAR:sellerMarketId}" == "9999")}selected="selected"{ENDIF}>Alle</option>
				</select>
				&nbsp;<button class="cmtButton" type="submit" name="action" value="default">anzeigen</button>
				<button class="cmtButton" type="submit" name="action" value="export">Exportieren: CSV</button>
				<button class="cmtButton" type="submit" name="action" value="sumsheets">Summenblätter</button>
			</div>
		</form>
	</div>
</div>
