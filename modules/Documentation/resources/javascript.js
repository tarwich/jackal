<?php

// Load javascript compressor
//$minify = Jackal::loadLibrary("JSMin");

// This is not implemented yet, but I would like to implement something like this
// at some point in time.
		
//
// This file will load all UI javascript files specified below
//

// Path to include other styles
$path = dirname(__FILE__)."/js";

// List of files to include
$css = array(
	"url",
	"jquery-1.4.1.min",
	"jquery.searches",
	"jquery.pageHeight"
);

// Now include all the javascript files
foreach ($css as $file) {
	echo "/* $file.js */\n";
	@include("$path/$file.js");
	echo "\n\n\n";
}

?>