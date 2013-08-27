<?php

//  ________________________________________________
// / Timezone                                       \

// Get the timezone setting
$timezone = Jackal::setting("jackal/timezone");
// Try to set the timezone
if(@date_default_timezone_set($timezone)) $timezoneStatus = array("class" => "ok", "status" => "OK", "message" => "");
// Invalid timezone
else $timezoneStatus = array("class" => "error", "status" => "ERROR", "message" => "Timezone \"$timezone\" is invalid");

// \________________________________________________/


//  ________________________________________________
// / Error Log                                      \

// Get the error log setting from Jackal
$errorSetting = Jackal::setting("jackal/error-log");
// No setting, therefore disabled and OK
if(!$errorSetting) $errorStatus = "OK: Disabled";

// Setting present. Let's see what it means
else {
	// Resolve the path of the log folder
	$errorFile = Jackal::expandPath("$errorSetting");
	// Make sure errorFile is an actual file
	if(!pathInfo($errorFile, PATHINFO_EXTENSION)) $errorFile .= "/error.log";
	// Find the error file
	@list($file) = Jackal::files($errorFile);
	// See if the file exists
	if(!$file) $errorStatus = "ERROR: Error log doesn't exist";
	// Error file exists, is it writeable
	else if(!is_writeable($file)) $errorStatus = "ERROR: Error log isn't writeable";
	// Looks good
	else $errorStatus = "OK: Enabled and working properly";
}

// Setup HTML stuff for the error setting
list($errorStatus, $errorMessage) = explode(":", $errorStatus);
// The class should be the lowercase version of the status
$errorStatus = array(
	"class"     => strtolower($errorStatus),
	"status"    => $errorStatus,
	"message"   => $errorMessage,
	"fixButton" => ($errorStatus == "OK") ? "" : "<a href='".Jackal::siteURL("Admin/fixErrorLog")."'>Fix</a>",
);

// \________________________________________________/

// Show the form
echo "
	<fieldset>
		<label>
			<h3>Timezone</h3>
			<input type='text' name='jackal/timezone' value='$timezone' />
			<p class='status $timezoneStatus[class]'><b>$timezoneStatus[status]</b> $timezoneStatus[message]</p>
		</label>
	</fieldset>
	<fieldset>
		<label>
			<h3>Error Log</h3>
			<input type='text' name='jackal/error-log' value='$errorSetting' />
			<p class='status $errorStatus[class]'><b>$errorStatus[status]</b> $errorStatus[message] $errorStatus[fixButton]</p>
		</label>
	</fieldset>";

