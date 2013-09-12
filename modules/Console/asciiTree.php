<?php

// Parse URI
// - subject (The thing to print)
($subject = @$URI[0]) || ($subject = $URI);

// How wide is an indent?
$tab = "    ";
// The current indent level
$indent = "";
// Get the width of the longest key
$keyPadding = max(array_map('strlen', array_keys($subject)));
// Backlog of things to print
$backlog = array(array($indent, (array) $subject, $keyPadding));

// Loop while there are items in the backlog (condom: 2500)
for($i=0; $backlog && ($i<2500); ++$i) {
	list($indent, $subject, $keyPadding) = array_pop($backlog);
	// Find out how far out the next array is
	$nextArray = array_search(1, array_values(array_map('is_array', $subject)));
	// Remove key padding if this key is an array
	$currentKeyPadding = $nextArray === false ? $keyPadding : $nextArray > 0 ? $keyPadding : 0;
	// If we're not padding this key, then recalculate the padding for the next group
	if(!$currentKeyPadding) $keyPadding = @max(array_map('strlen', array_keys($subject)));
	// Grab the next key/value pair
	list($name, $value) = each($subject);
	// Since we're sub-iterating through subject, remove the current item from $subject
	unset($subject[$name]);
	// If there are still items in $subject, then put it back in the backlog
	if($subject) $backlog[] = array($indent, $subject, $keyPadding);
	// Indent and print the name
	printf("$indent%-{$currentKeyPadding}s: ", htmlentities($name));
	
	// If scalar, then print the value
	if(is_scalar($value)) echo htmlentities("$value\n");
	
	else {
		// If this is an empty array, then use braces to illustrate that
		if(!count($value)) echo "[ ]";
		// End this line
		echo "\n";
		// Get the width of the longest key
		$keyPadding = @max(array_map('strlen', array_keys($value)));
		// 
		if($value) $backlog[] = array("$indent$tab", (array)$value, $keyPadding);
	}
}
