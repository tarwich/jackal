<?php

// Get the self-test that needs to be run from the URI
($selfTest = @$URI['self-test']) || ($selfTest = @$URI[0]);

if($selfTest) {
    // Replace any '.'s with '/'s to set the path to the test
    $selfTest = str_replace(".", "/", $selfTest);
    // Run the test and collect any output
    $results = Jackal::call($selfTest);
	// Get only the warnings and errors
	$results = array_intersect_key($results, array("WARNING" => array(), "ERROR" => array()));
	// Count the number of children
	$count = count($results, COUNT_RECURSIVE) - count($results);
    // Display the output
    echo "<span class='result'>($count)</span>";
}
echo "<span class='result'></span>";
