<?php

/**
 * This test checks to see if showOverviewAlerts is working correctly
 *
 * This test checks the response of showOverViewAlerts by creating a test module with 2 sub-modules that have
 * self-tests of "getSampleMessages," which returns an array with 3 messages, 1 of each priority level.
 * Since showOverviewAlerts only considers "errors" and "warnings," it expects to receive an html element with text
 * equaling 4.
 *
 * @return void
 */

// Set the title of the sub-test
$this->startSubTest(array("Verify correct response from Admin/runTests/getSampleMessages"));
// Set the timezone to something invalid, and create a test module that will run the getSampleMessages test
Jackal::putSettings("
admin:
	modules:
		subsection1:
			-
				name     : Foo / Bar
				self-test: Admin/getSampleMessages
		subsection2:
			-
				name     : Foo / Bin
				self-test: Admin/getSampleMessages
jackal:
    timezone: Foo/Bar
");
// Run the self-test for all sub-modules in the Foo module
$results = Jackal::returnCall("Admin/showOverviewAlerts/Foo");
// Strip the html tags to isolate the number of results
$results = strip_tags($results);
// Our expected result is 4, since we only want Errors and Warnings,
// and because both sub-sections call getSampleMessages
$expected = 4;
// Compare what we got vs what we expected
$this->assertEquals(array($expected, $results));
