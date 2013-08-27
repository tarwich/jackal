<?php

// Get the self-test that needs to be run from the URI
($target = @$URI["test"]) || ($target = @$URI[0]);

// Load the sections
$sections = $this->_getSections();
// Get the section for which we're testing
$sections = $sections[$target];
// Prepare the results array
$results = array(
	"ERROR" => array(),
	"WARNING" => array(),
	"INFO" => array(),
);

// Run all the tests in this section
foreach($sections as $name=>$subSections) foreach($subSections as $section) {
	// Run this test
	$results = array_merge_recursive($results, Jackal::call($section["self-test"]));
}

// Get only the warnings and errors
$results = array_intersect_key($results, array("WARNING" => array(), "ERROR" => array()));
// Count the number of children
$count = count($results, COUNT_RECURSIVE) - count($results);
// Display the output
echo "<span class='result'>($count)</span>";

