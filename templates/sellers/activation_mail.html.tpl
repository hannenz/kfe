<div style="color: #231f20; font-family: sans-serif; font-size: 18px; line-height: 21px;">
	<p>
		Hallo {VAR:seller_firstname} {VAR:seller_lastname},
	</p>

	<p>
		Vielen Dank für deine Registrierung für den Erbacher Kinderflohmarkt<br>
		am {DATEFMT:"{VAR:market_begin}":"%a, %d. %B %Y":de_DE.utf-8}
	</p>

	<p>
		Klicke hier, um die Registrierung abzuschliessen
	</p>

	<p style="text-align: center;">
		<div style="margin-left: auto; margin-right: auto; text-align: center; display: inline-block; background-color: #51369b; padding: 4px 8px;">
			<a style="color: #fff; text-decoration: none; font-weight: bold;" href="{VAR:activationUrl}">Verkäufernummer aktivieren</a>
		</div>
	</p>

	<!--
	<p>
		(oder kopiere die gesamte URL in die Adresszeile deines Browsers)
	</p>

	<details>
		<summary>
			URL (zum kopieren)
		</summary>
		<p style="font-family: monospace; word-break:break-all; overflow-wrap: break-word; word-wrap: break-word;">{VAR:activationUrl}</p>
	</details>
	-->

	<p>
		Dein Erbacher Kinderflohmarkt-Team
	</p>
</div>
