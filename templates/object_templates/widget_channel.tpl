{IF (!{LAYOUTMODE})}
	{EVAL}
	if (!empty($cmt_content['head1'])) {
		include(PATHTOWEBROOT."phpincludes/widgets/widgets_controller.php");
	}
	{ENDEVAL}
{ELSE}
	<div>
		<label for="channel{UNIQUEID:new}">Kanal verwenden<label>
		<select name="channel_name" id="channel{UNIQUEID}" onchange="document.getElementById('selectedWidgetInclude{UNIQUEID}').innerHTML = this.value">
			<option value="">-- Widget-Kanal aussuchen --</option>
			{LOOP TABLE(cmt_widgets_channels:ORDER BY channel_title)}<option value="{FIELD:id}"{IF ("{HEAD:1}" == "{FIELD:id}")} selected="selected"{ENDIF}>{FIELD:channel_title}</option>{ENDLOOP TABLE}
		</select>
	</div>

	<div id="selectedWidgetInclude{UNIQUEID}" style="display: none;">{HEAD:1}</div>

	<script type="text/javascript">
		var select = document.getElementById('channel{UNIQUEID}');
		var div = document.getElementById('selectedWidgetInclude{UNIQUEID}');
		var channelId = parseInt(div.innerText);
		select.value = channelId;
	</script>
{ENDIF}

