<?php

// Parse URI
// - user
($user = @$URI["user"]) || ($user = @$URI[0]);
// - password
($password = @$URI["password"]) || ($password = @$URI[1]);

// Completely disable output buffering
while(ob_get_level()) ob_end_clean();
// Make everything auto-flush
ob_implicit_flush();

// Get the setting that tells us where the error log should be
$errorSetting = Jackal::setting("jackal/error-log");
// No setting, therefore disabled and OK
if(!$errorSetting) status("Error log is disabled, nothing to do") . result("OK");

// Setting present. Let's see what it means
else {
	// Resolve the path of the log folder
	$path = Jackal::expandPath("$errorSetting");
	// Make sure errorFile is an actual file
	if(!pathInfo($path, PATHINFO_EXTENSION)) $path .= "/error.log";
	// Get the name of the actual log
	$basename = basename($path);
	status("Searching for file $basename").result();
	// List of folders that are going to need created
	$backlog = array();
	
	// Search upward for path	
	while(!Jackal::files($path)) {
		status($path);
		result("NOT FOUND");
		array_unshift($backlog, basename($path));
		$path = dirname($path);
	}
	
	list($folder) = Jackal::files($path);
	
	status($folder);
	result("FOUND");
	
	foreach($backlog as $subFolder) {
		if($subFolder == $basename) break;
		status("mkdir $folder/$subFolder");
		
		if(@mkdir($folder = "$folder/$subFolder")) {
			result("OK");
		}
		
		else {
			// Require username and password
			if(!($user) || !($password)) {
			}
		}
	}
	
	touch("$folder/$basename");
	
	// // Resolve the path of the log folder
	// $errorFile = Jackal::expandPath("$errorSetting");
	// // Make sure errorFile is an actual file
	// if(!pathInfo($errorFile, PATHINFO_EXTENSION)) $errorFile .= "/error.log";
	// // Get the name of the actual file
	// $basename = basename($errorFile);
	// status("Searching for file <i title='$errorFile'>$basename</i>");
	// // Find the error file
	// @list($file) = Jackal::files($errorFile);
	// // See if the file exists
	// if(!$file) {
	// 	result("ERROR", "File not found");
	// 	status("Checking for folder");
	// 	
	// 	if(file_exists(dirname($errorFile))) {
	// 		echo "Exists";
	// 	}
	// }
	// // Error file exists, is it writeable
	// else if(!is_writeable($file)) $errorStatus = "ERROR: Error log isn't writeable";
	// // Looks good
	// else $errorStatus = "OK: Enabled and working properly";
}

function status($message)  { 
	echo "<div class='status'>$message "; 
}

function result($result="", $message="") {
	if($result) $result = "[$result]";
	echo "<b>$result</b>$message</div>"; 
}
