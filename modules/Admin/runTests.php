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

return $results;