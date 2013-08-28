<?php

//  ________________________________________________
// / System User                                    \

$systemUser = Jackal::setting("jackal/system/user");
$systemPassword = Jackal::setting("jackal/system/password");
$systemStars = str_repeat("*", strlen($systemPassword));

// \________________________________________________/

// Get the timezone setting
$timezone = Jackal::setting("jackal/timezone");
// Get the current path to the error log to display
$errorSetting = Jackal::setting("jackal/error-log");
// Run the test for this section and get the test's errors / warnings
$messages = Jackal::returnCall("Admin/tester/", array("test" => "System", "destination" => "section"));

// Show the form
echo "
    <span class='message-area'>
        <ul class='messages'>
        $messages
        </ul>
	</span>
	<fieldset>
		<label>
			<h3>System User</h3>
			<input type='text' name='jackal/system/user' value='$systemUser' />
		</label>
		<label>
			<h3>System Password</h3>
			<input type='password' name='jackal/system/password' value='$systemStars' />
		</label>
	</fieldset>
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