<?php

// Load every javascript formatter into one javascript file

// Get the path
$path = dirname(__FILE__)."/js/formatter";
		
// Get the files
$files = Jackal::files("$path/*.js");

// The core most be included first
include("$path/shCore.js");

// Now include all the javascript files
foreach ($files as $file) {
	$fileName = basename($file);
	if ($fileName=="shCore.js") continue;
	echo "/* $file.js */\n";
	@include($file);
	echo "\n\n\n";
}

?>