<?php
//  ________________________________________________
// / Database Settings                              \

// Load the database settings
$settings = Jackal::setting("database");
// Extract the host
$host = $settings['host'];
// Extract the username
$user = $settings['username'];
// Extract the password
$password = $settings['password'];
// Extract the database name
$database = $settings['database'];
// Extract the port
$port = $settings['port'];
// Extract the socket
$socket = $settings['socket'];
// Obscure the password
$password = str_repeat("*", strlen($password));

// Show the form
echo "
	<fieldset>
		<label>
			<h3>Host</h3>
			<input type='text' name='database/host' value='$host' />
		</label>
		<label>
			<h3>Username</h3>
			<input type='text' name='database/username' value='$user' />
		</label>
		<label>
			<h3>Password</h3>
			<input type='password' name='database/password' value='$password' />
		</label>
		<label>
			<h3>Database</h3>
			<input type='text' name='database/database' value='$database' />
		</label>
		<label>
			<h3>Port</h3>
			<input type='text' name='database/port' value='$port' />
		</label>
		<label>
			<h3>Socket</h3>
			<input type='text' name='database/socket' value='$socket' />
		</label>
	</fieldset>";