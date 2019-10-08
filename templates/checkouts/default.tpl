<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>

	<link rel="stylesheet" href="/dist/css/main.css" type="text/css" />
	
</head>
<body class="checkout-login">

	{IF({ISSET:error:VAR})}
	<div class="messages stack">
		<div class="cmtMessage cmtErrorMessage">
			{VAR:error}
		</div>
	</div>
	{ENDIF}

	<form class="checkout-login-form form stack" action="" method="post" accept-charset="utf-8">

		<div class="form-field form-field--text">
			<label for="checkoutId">Kassen-Nr</label>
			<input type="number" value="{IF({ISSET:checkoutId})}{VAR:checkoutId}{ELSE}1{ENDIF}" name="checkoutId" id="checkoutId" autofocus />
		</div>

		<div class="form-field form-field--select">
			<label for="marketId">Markt</label>
			<!-- <input type="number" value="{VAR:marketId}" name="marketId" id="marketId" /> -->
			<select name="marketId" id="marketId">
				{LOOP VAR(markets)}
				<option value="{VAR:id}">{DATEFMT:"{VAR:market_begin}":"%d.%m.%Y"}</option>
				{ENDLOOP VAR}
			</select>
		</div>

		<div class="form-field form-field--submit">
			<button class="button">Los</button>
		</div>
	</form>
</body>
</html>
