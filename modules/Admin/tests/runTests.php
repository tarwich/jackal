<?php

$this->startSubTest("All Foo");
// Backup the admin settings
$oldSettings = Jackal::setting("admin");
// Make a copy of the settings to change
$settings = $oldSettings;
// Set the modules
Jackal::putSettings("
admin:
	modules:
		testModule:
			- 
				name     : Foo / Bar
				callback : bin/Baz
				self-test: Testing/returnValue/11.22
");
// Run all tests in the foo section
$results = Jackal::call("Admin/runTests/Foo");

$expected = array(
	"ERROR"   => array(),
	"WARNING" => array(),
	"INFO"    => array("11.22"),	
);

$actual = $results;

$this->assertEquals(array($expected, $actual));
