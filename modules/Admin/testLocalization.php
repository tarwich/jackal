<?php

// Create an array that we will populate with messages generated from the test(s)
$messages = array(
    "WARNING" => array(),
    "ERROR"   => array(),
    "OK"      => array(),
);

//  ________________________________________________
// / Error Log                                      \

// Get the error log setting from Jackal
$errorSetting = Jackal::setting("jackal/error-log");
// No setting, therefore disabled and OK
if(!$errorSetting) $messages["OK"][] = "OK: Error log disabled";

// Setting present. Let's see what it means
else {
    // Resolve the path of the log folder
    $errorFile = Jackal::expandPath("$errorSetting");
    // Make sure errorFile is an actual file
    if(!pathInfo($errorFile, PATHINFO_EXTENSION)) $errorFile .= "/error.log";
    // Find the error file
    @list($file) = Jackal::files($errorFile);
    // See if the file exists
    if(!$file) $messages["ERROR"][] = "Error log doesn't exist";
    // Error file exists, is it writeable
    else if(!is_writeable($file)) $messages["ERROR"][] = "Error log isn't writeable";
    // Looks good
    else $messages["OK"][] = "Error log enabled and working properly";
}

// \________________________________________________/

//  ________________________________________________
// / Timezone                                       \

// Get the timezone setting
$timezone = Jackal::setting("jackal/timezone");
// Try to set the timezone
if(@date_default_timezone_set($timezone)) $messages["OK"][] = "Timezone successfully set";
// Invalid timezone
else $messages["ERROR"][] = "Timezone $timezone is invalid";

// \________________________________________________/

// Return the array of messages from the tests in this file
return $messages;