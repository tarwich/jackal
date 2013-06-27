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
	"jquery.pageHeight"
);


// Now include all the CSS
foreach ($css as $file) {
//	ob_start();
//	echo "/* $file */\n";
	@include("$path/$file.js");
//	echo "\n\n";
//	$buffer = ob_get_contents();
//	ob_end_clean();
//	
//	// Compress and output
//	$buffer = $minify->minify($buffer);
//	echo $buffer;
}

?>