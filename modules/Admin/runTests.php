<?php

/**
 * Runs one or more self-tests for an admin section, as defined in the module's config.
 *
 * If only a section name is provided, this method will call the self-test file for each one of its
 * sub-sections.
 * If both a section and subsection name are provided, this method will run just the self-test for the
 * specified subsection
 *
 * To set the self-test behavior, you will need to modify the module's config and set the "self-test" to
 * the name of the file that you desire.
 *
 * Returns an array containing all of the errors, warnings, and any other messages that the test generates
 *
 * <code type='yaml'>
 * 	admin:
 * 		modules:
 * 			your-module:
 * 				-
 * 					name     : Your Section / Your Subsection
 * 					self-test: your-module/yourSelfTest
 * 				-   # You can insert subsections into other admin pages like this
 * 					name     : Other Section / Your Subsection
 * 					self-test: your-module/aDifferentSelfTest
 * </code>
 *
 * Segments: section / subsection
 * @param string $section    The name of the section whose tests need to be run.
 * @param string $subsection The name of the subsection within the $section whose test to run.
 *
 *
 * @return array
 */

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
		// Only run tests if there are tests
		if(@$target["self-test"]) 
        	// Run this test
			$results = array_merge_recursive($results, (array) Jackal::call($target["self-test"]));
}
// If no subsection was provided, then run the tests for all subsections
else {
    // Run all the tests in this section
    foreach($sections as $name=>$subSections) foreach($subSections as $section)
		// Only run tests if there are tests
		if(@$section["self-test"]) 
	        // Run this test
	        $results = array_merge_recursive($results, (array) Jackal::call($section["self-test"]));
}

return $results;