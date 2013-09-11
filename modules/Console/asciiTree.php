<?php

// Parse URI
// - subject (The thing to print)
($subject = @$URI[0]) || ($subject = $URI);
// - indent
($indent = @$URI[1]) || ($indent = "");

// Initialize key calculations to zero
$shortest = 32767;
$longest = 0;
$hasChildren = false;
// Buffer for returning the result
$result = "";

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

$backlog = array(array("", (array) $subject));
$i = 0;

while($backlog) {
	list($indent, $subject) = array_pop($backlog);
	list($name, $value) = each($subject);
	unset($subject[$name]);
	if(++$i > 20) break;
	// Indent and print the name
	$result .= sprintf("$indent%-{$keyPadding}s: ", $name);
	if($subject) $backlog[] = array($indent, $subject);
	
	// If scalar, then print the value
	if(is_scalar($value)) $result .= $value . "\n";
	
	else {
		// End this line
		$result .= "\n";
		if($value) $backlog[] = array("\t$indent", (array)$value);
		// $result .= $this->asciiTree((array) $value, "\t$indent");
	}
}

$result .= "DONE";
return htmlentities($result);