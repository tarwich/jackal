<?php

//  __________________________________________________
// / Parse URI                                        \

@( ($shortPath = $URI["shortPath"]) || ($shortPath = $URI["shortpath"]) );
@( ($path = $URI["path"]) );

// \__________________________________________________/


// Get the setting that tells us where test folders should be placed
$testPath = Jackal::setting("testing/test-path");
$classPath = Jackal::setting("class-path");
// Get the file for this test
@list($file) = Jackal::files("$classPath/$path");

if($file) {
	// Get the name of the test 
	preg_match('_(?:/tests/([^/]+)/([^/]+)\.php|/([^/]+)/tests/([^/]+)\.php)_', $file, $matches);
	@list(, $moduleA, $nameA, $moduleB, $nameB) = $matches;
	$this->testModule = $moduleA.$moduleB;
	$this->testName = $nameA.$nameB;
	
	include($file);
	$this->endSubTest();
}
