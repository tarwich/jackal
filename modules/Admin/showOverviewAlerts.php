<?php

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