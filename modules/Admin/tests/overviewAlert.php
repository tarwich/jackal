<?php

/**
 * This test checks to see if showOverviewAlerts is working correctly
 *
 * This test checks the response of showOverViewAlerts by creating a test
 * module with a self-test of "getSampleMessages," which returns an array with one message of
 * each priority level. Since showOverviewAlerts only considers "errors" and "warnings," it expects
 * to receive an html element with text equaling 2.
 *
 * @return void
 */

// Set the title of the sub-test
$this->startSubTest(array("Verify correct response from Admin/runTests/getSampleMessages"));
// Set the timezone to something invalid, and create a test module that will run the getSampleMessages test
Jackal::putSettings("
admin:
    modules:
            testModule:
                -
                    name     : Foo / Bar
                    self-test: Admin/getSampleMessages
jackal:
    timezone: Foo/Bar
");
// Get the overview alerts for the Foo module. It should return an html element with text
// containing the number of errors/warnings.
$results = Jackal::returnCall("Admin/showOverviewAlerts/Foo");
// Strip the html tags to isolate the number of results
$results = strip_tags($results);
// Our expected result is 2, since we are only considering errors and warnings.
$expected = 2;
// Compare what we got vs what we expected
$this->assertEquals(array($expected, $results));
