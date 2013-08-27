<?php

// Get the timezone setting
$timezone = addslashes((string) Jackal::setting("jackal/timezone", ""));

// Show the form
echo "
	<fieldset>
		<label>
			<b>Timezone</b>
			<input type='text' name='jackal/timezone' value='$timezone' />
		</label>
		<p>(Something about editing error.log)</p>
	</fieldset>";

