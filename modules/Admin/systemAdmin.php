<?php

/**
 * Shows the settings specific to the System section.
 *
 * This method will populate the form that was started in showSection with the value of current settings.
 * It provides the needed form controls to allow an admin to change the settings.
 *
 * @return void
 */

// Process URI
// - section
($section = @$URI["section"]) || ($section = @$URI[0]);

if($section == "localization") {
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
}

elseif($section == "database") {
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
	// \________________________________________________/

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
}

elseif($section == "htaccess") {
	// If mod_rewrite is available
	if(preg_grep('/mod_rewrite/', apache_get_modules())) {
		// Get the leading url
		$url = dirname($_SERVER["SCRIPT_NAME"]);
		// Remove jackal from end of url
		$url = preg_replace('~/jackal$~', '', $url);
		if(trim($url)) $RewriteBase = "RewriteBase $url";
		$htaccess = <<<END
Options +FollowSymlinks
RewriteEngine On
$RewriteBase
RewriteRule (.*) index.php [L]
END;
		echo "
			<p>By default, Jackal will use site.com/?/Foo/bar to direct users to different sections (The Foo.bar section) of your
			site. If you want it too look like this: site.com/Foo/bar, then you must use .htaccess ModRewrite.</p>
			<p>Insert this code into your .htaccess file to get ModRewrite working:</p>
			<textarea>$htaccess</textarea>";
	} else {
		echo "<p>mod_rewrite is not available on your server</p>";
	}
}
