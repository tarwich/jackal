<?php

// Get the self-test that needs to be run from the URI
($target = @$URI["test"]) || ($target = @$URI[0]);
// Get the destination for the data derived from the test
($destination = @$URI["destination"]) || ($destination = @$URI[1]);

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

// If the data is going back to the section area, then we need to send the messages
if($destination === "section") {
    // Get only the warnings and errors
    $results = array_intersect_key($results, array("WARNING" => array(), "ERROR" => array()));
    // Go through all of the warnings and errors
    foreach($results as $level => $messages){
        // Go through all of the messages in the respective error levels
        foreach($messages as $ignore=>$message){
            // Lowercase the level so that we can use it as the class of the li
            $class = strtolower($level);
            // Display each message as a list item
            echo "<li class='$class'>$level: $message</li>";
        }
    }
}

// Otherwise, just send back the count of warnings and errors
else {
    // Get only the warnings and errors
    $results = array_intersect_key($results, array("WARNING" => array(), "ERROR" => array()));
    // Count the number of children
    $count = count($results, COUNT_RECURSIVE) - count($results);
    // Display the output
    echo "<span class='result'>($count)</span>";
}

