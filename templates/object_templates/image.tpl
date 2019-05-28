<figure>
	{IMAGE:1:media}
	{IF({LAYOUTMODE} == true || {ISSET:head2:CONTENT})}
	{IF ({LAYOUTMODE} == true || {ISSET:head1:CONTENT})}{IF({LAYOUTMODE})}<label>Bildunterschrift</label>{ENDIF}<figcaption>{HEAD:1}</figcaption>{ENDIF}
	<footer>{IF({LAYOUTMODE})}<label>Copyright</label><br>{ENDIF}<small class="image-copyright">{HEAD:2}</small></footer>{ENDIF}
</figure>
