<style>
	.code-label {
		margin: 1rem 0;
		border: 1px solid #111;
		border-left: 0;
		background-color: #fff;
		padding: 20px;
		position: relative;
		height: 200px;
		display: inline-block;
		margin-left: 100px;
	}
	.code-label::before {
		content: '';
		background-color: #fff;
		border: 1px solid #111;
		height: 140px;
		width: 140px;
		transform: rotate(45deg);
		position: absolute;
		left: -70px;
		top: 30px;
		z-index: -1;
	}

</style>

<figure class="code-label">
	{VAR:barcode}
	<figcaption>
		{VAR:code} <b>{PRINTF:"{VAR:value}":"%.2f"} &euro;</b> 
	</figcaption>
</figure>
