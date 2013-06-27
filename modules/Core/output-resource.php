<?php 

$delimiter = "|";

// Get application document root path
$root = dirname($_SERVER["SCRIPT_FILENAME"]);

$files = @$URI["files"];

if (!count($files)) return;

// Get javascript files
foreach($files as $f) {
	$f = str_replace($delimiter, "/", $f);
	$file = Jackal::getModuleDir($f);
	echo "/* $file */\r\n";
	if (file_exists($file)) {
		include($file);
	}
	echo "\r\n\r\n";
}

?>