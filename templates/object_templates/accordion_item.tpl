{IF({LAYOUTMODE})}
<div>
	<div><h3 class="headline">{HEAD:1}</h3></div>
	<div>
		{HTML:1}
	</div>
</div>
{ELSE}
<details class="accordion-item">
	<summary class="accordion-item__header">{HEAD:1}</summary>
	<div class="accordion-item__body body-text">{HTML:1}</div>
</details>
{ENDIF}
