<?php

/**
 * This test calls overview and looks to see if our two test modules are present
 *
 * This test works by first inserting 2 test modules into the admin settings. It then calls sidebar,
 * which returns a list of all of the Modules. It strips the html tags, breaks up the resulting string into an
 * array of whitespace separated words, and loops through them to see if our 2 test modules are in the array.
 *
 * @return void
 */

// Set the title for this test
$this->startSubTest(array("Check if test modules are present in resulting html"));
// Add a test module
Jackal::putSettings("
admin:
	modules:
		testModule1:
			-
				name     : Foo / Bar
				self-test: Admin/getSampleMessages
        testModule2:
			-
				name     : Bin / Baz
				self-test: Admin/getSampleMessages

");
// Call sidebar
$results = Jackal::returnCall("Admin/sidebar");
// Strip html elements out
$results = strip_tags($results);
// Split the string of modules into an array.
$results = preg_split('/\s+/', $results);
// Initialize an array to hold the modules we're looking for
$modules = array();
// See if our test module is present
foreach($results as $ignore=>$result)
    // Check if this result matches one of our test modules
    if(($result === "Foo") || ($result === "Bin"))
        // Add to our modules array
        array_push($modules, $result);
// Create our expected result. We inserted 2 modules named "Foo" and "Bin" into the admin settings, so we expect
// that our $modules array will contain 2 elements: "Foo" and "Bin". We don't care about the other elements in the
// $results array, because those could change.
$expected = array(
    "Foo",
    "Bin"
);
// See if we got what we expected
$this->assertEquals(array($expected, $modules));
