<?php

// Parse URI
// - subject (The thing to print)
($subject = @$URI[0]) || ($subject = $URI);
// - indent
($indent = @$URI[1]) || ($indent = "\t");

// Buffer output to make it easier to return the content
ob_start();
// Initialize key calculations to zero
$shortest = 32767;
$longest = 0;

// Calculate key variance
foreach($subject as $name=>$value) {
	// See if this array has children
	$hasChildren |= !is_scalar($value);
	// Get the length of this key
	$length = strlen($name);
	// Calculate the shortest key
	$shortest = min($shortest, $length);
	// Calculate the longest key
	$longest = max($longest, $length);
}

// Don't pad the keys if there are children
$keyPadding = $hasChildren ? 0 : $longest;

// Print each item
foreach($subject as $name=>$value) {
	// Indent and print the name
	printf("$indent%-{$keyPadding}s: ", $name);
	
	// If scalar, then print the value
	if(is_scalar($value)) echo htmlentities($value) . "\n";
	
	else {
		// End this line
		echo "\n";
		echo $this->asciiTree((array) $value, "\t$indent");
	}
}

// End output buffering, get the contents, and return them
return ob_get_clean();