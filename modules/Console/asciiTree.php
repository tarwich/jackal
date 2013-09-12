<?php

// Parse URI
// - subject (The thing to print)
($subject = @$URI[0]) || ($subject = $URI);

// How wide is an indent?
$tab = "    ";
// Buffer for returning the result
$result = "";
// The current indent level
$indent = "";
// Get the width of the longest key
$keyPadding = max(array_map('strlen', array_keys($subject)));
// If any of the children have children, then keyPadding should be 0
if(max(array_map('is_array', $subject))) $keyPadding = 0;
// Backlog of things to print
$backlog = array(array($indent, (array) $subject, $keyPadding));

while($backlog) {
	list($indent, $subject, $keyPadding) = array_pop($backlog);
	list($name, $value) = each($subject);
	unset($subject[$name]);
	if(++$i > 20) break;
	if($subject) $backlog[] = array($indent, $subject, $keyPadding);
	
	// If scalar, then print the value
	if(is_scalar($value)) {
		// Indent and print the name
		$result .= sprintf("$indent%-{$keyPadding}s: ", $name);
		$result .= $value . "\n";
	}
	
	else {
		// Indent and print the name
		$result .= sprintf("$indent%-{$keyPadding}s: ", $name);
		// End this line
		if(!count($value)) $result .= "[ ]";
		$result .= "\n";
		// Get the width of the longest key
		$keyPadding = max(array_map('strlen', array_keys($value)));
		// If any of the children have children, then keyPadding should be 0
		if(max(array_map('is_array', $value))) $keyPadding = 0;
		if($value) $backlog[] = array("$indent$tab", (array)$value, $keyPadding);
	}
}

$result .= "DONE";
return htmlentities($result);