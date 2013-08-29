<?php

/**
 * Shows the total count of errors and warnings for all of the self-tests for a given Admin section.
 *
 * This method calls the runTests method and filters out all of the messages that are not of type "WARNING"
 * or "ERROR".
 *
 * Segments: target
 * @param string $target The name of the section whose self-test data we want to retrieve.
 *
 * @return void
 */

// Get the self-test that needs to be run from the URI
($target = @$URI["test"]) || ($target = @$URI[0]);
// Execute the test and get the results
$results = $this->runTests(array($target));
// Get only the warnings and errors
$results = array_intersect_key($results, array("WARNING" => array(), "ERROR" => array()));
// Count the number of children
$count = count($results, COUNT_RECURSIVE) - count($results);
// Display the output if the count is greater than zero
if($count > 0) echo "<span class='result'>($count)</span>";