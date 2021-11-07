<div class="no-cmtLayer">
	<div class="no-cmtLayerHandle">
		<div class="cmt-layer-icon cmt-font-icon cmt-icon-filter"></div>
		<div class="cmt-layer-open-close cmt-font-icon cmt-icon-open-close"></div>
		<!-- Zeige Verkäufer -->
		<!-- {IF("{SESSIONVAR:sellerMarketId}" == "0")} -->
		<!-- 	die keinem speziellen Markt zugeordnet sind (Mitarbeiter) -->
		<!-- {ENDIF} -->
		<!-- {IF("{SESSIONVAR:sellerMarketId}" == "9999")} -->
		<!-- 	für alle Märkte -->
		<!-- {ENDIF} -->
		<!-- {LOOP VAR(markets)} -->
		<!-- 	{IF("{VAR:id}" == "{SESSIONVAR:sellerMarketId}")} -->
		<!-- 		für den Markt am {DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"} -->
		<!-- 	{ENDIF} -->
		<!-- {ENDLOOP VAR} -->
	</div>
	<div class="no-cmtLayerContent">
		<form name="selectTableForm" action="{SELFURL}" method="post">
			<div class="serviceContainer">
				<div class="serviceContainerInner">
					<div class="serviceElementContainer">
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
					</div>
					<div class="serviceElementContainer">
						<select name="exportType">
							<option value="ssv">SSV (für Microsoft Excel)</option>
							<option value="csv">CSV</option>
						</select>
						<button class="cmtButton" type="submit" name="action" value="export">Exportieren</button>
					</div>
					<!-- <div class="serviceElementContainer"> -->
					<!-- 	<button class="cmtButton" type="submit" name="action" value="sumsheets">Summenblätter</button> -->
					<!-- </div> -->
				</div>
			</div>
		</form>
	</div>
</div>
<div class="cmtDialog" id="confirmSendActivationLink">
</div>
