<?php

/**
 * This method tests if showOverviewAlerts is working correctly
 *
 * This method checks the number of alerts returned from showOverViewAlerts by creating a test
 * module with a self-test of "getSampleMessages," which returns an array with one message of
 * each priority level. Since showOverviewAlerts only considers "errors" and "warnings," it expects
 * to receive an alert of 2.
 *
 * @return void
 */

$this->startSubTest(array("testWritten"));

// Backup the admin settings
$oldSettings = Jackal::setting("admin");
// Make a copy of the settings to change
$settings = $oldSettings;
// Set the timezone to something invalid
Jackal::putSettings("
admin:
    modules:
            testModule:
                -
                    name     : Foo / Bar
                    callback : Bin / Baz
                    self-test: Admin/getSampleMessages
jackal:
    timezone: Foo/Bar
");
// Run the localization test. At least 1 test should fail (the timezone)
$results = Jackal::returnCall("Admin/showOverviewAlerts/Foo");
// Strip the html tags
$results = strip_tags($results);
// Create our expected result -- This method only counts errors and warnings, so given one of each
// message type, our expected result is 2.
$expected = "2";
// Compare what we got vs what we expected
$this->assertTrue(array(($results === $expected) ? true : false));