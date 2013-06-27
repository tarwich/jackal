<?php

/**
 * Allows the creation of a function with the standard function() {} notation.
 * Example: function($a, $b) { return $a + $b; } 
 * 
 * @param $text The function to parse
 * @return resource Handle of the newly created function
 */
function &create_function2($text) {
	// See if we've already made the function
	if($handle = @$GLOBALS["lambda/functions/".$text]) return $handle;
	// Parse the function text
	preg_match('/^\s*function\s*\((?<arguments>.*)\)\s*\{(?<body>.*)\}\s*$/s', $text, $matches);
	
//	if(@$matches["body"]) {
		// Actually make the function
		$handle = create_function($matches["arguments"], $matches["body"]);
		// Cache the function
		$GLOBALS["lambda/functions/".$text] = $handle;
		
		return $handle;
//	} else {
//		return $text;
//	}
}
