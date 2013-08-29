<?php

// Get the section that needs to be run from the URI
($section = @$URI["test"]) || ($section = @$URI[0]);
// Get the sub-section, if provided
($subsection = @$URI['subsection']) || ($subsection = @$URI[1]);
// Load the sections
$sections = $this->_getSections();
// Get the section for which we're testing
$sections = $sections[$section];
// Prepare the results array
$results = array(
	"ERROR" => array(),
	"WARNING" => array(),
	"INFO" => array(),
);

// If a subsection was provided, then just run the test for that specific subsection
if(!empty($subsection)) {
    // Get the subsection we need to run tests on
    $subsection = $sections[$subsection];
    // Run the test(s) in this subsection
    foreach($subsection as $target)
        // Run this test
        $results = array_merge_recursive($results, Jackal::call($target["self-test"]));
}
// If no subsection was provided, then run the tests for all subsections
else {
    // Run all the tests in this section
    foreach($sections as $name=>$subSections) foreach($subSections as $section)
        // Run this test
        $results = array_merge_recursive($results, Jackal::call($section["self-test"]));
}

return $results;