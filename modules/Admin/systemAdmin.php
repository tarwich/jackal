<?php

// Get the timezone setting
$timezone = Jackal::setting("jackal/timezone");
// Get the current path to the error log to display
$errorSetting = Jackal::setting("jackal/error-log");

// Show the form
echo "
	<fieldset>
		<label>
			<h3>Timezone</h3>
			<input type='text' name='jackal/timezone' value='$timezone' />
		</label>
	</fieldset>
	<fieldset>
		<label>
			<h3>Error Log</h3>
			<input type='text' name='jackal/error-log' value='$errorSetting' />
		</label>
	</fieldset>";