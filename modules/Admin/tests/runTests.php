<?php

/**
 * This test checks to see if runTests is working correctly
 *
 * This test works by creating a testModule with a self-test that runs getSampleMessages.
 * It then compares the results of that self-test with the array of messages that
 * we expected to receive. If they match, then the test was successful. Otherwise,
 * the test was unsuccessful
 *
 * @return void
 */

// Create a test module with multiple subsections
Jackal::putSettings("
admin:
	modules:
		testModule:
			-
				name     : Foo / Bar
				self-test: Admin/getSampleMessages
			-
				name     : Foo / Bin
				self-test: Admin/getSampleMessages
");

//  __________________________________________________
// / Test single   sub-section                        \

// Set the title for this test
$this->startSubTest(array("Run a sample test on a specific sub-module"));
// Run just the Bar submodule
$results = Jackal::call("Admin/runTests/Foo/Bar");
// Create our array of expected messages
$expected = array(
    "ERROR"   => array("error test"),
    "WARNING" => array("warning test"),
    "INFO"    => array("info test"),
);
// Compare our results with our expected results
$this->assertEquals(array($expected, $results));

// \__________________________________________________/

//  __________________________________________________
// / Test multiple sub-sections                       \

// Set the title for the sub-test
$this->startSubTest(array("Run sample tests on all of a module's sub-modules"));
// Run all tests in the foo section
$results = Jackal::call("Admin/runTests/Foo");
// Create our array of expected messages. Both sub-modules call getSampleMessages, so we need 2 of each error message
$expected = array(
    "ERROR"   => array(
        "error test",
        "error test"
    ),
    "WARNING" => array(
        "warning test",
        "warning test"
    ),
    "INFO"    => array(
        "info test",
        "info test"
    ),
);
// Compare our results with our expected results
$this->assertEquals(array($expected, $results));
// \__________________________________________________/