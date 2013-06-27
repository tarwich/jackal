<?php

//  __________________________________________________
// / Parse URI                                        \

@( ($branch = $URI["branch"]) );

// \__________________________________________________/




// Get the setting that tells us where test folders should be placed
$testPath = Jackal::setting("testing/test-path");
// Get the file for this test
$files = Jackal::files("$testPath/$branch/*.php");

foreach($files as $file) {
	$this->runTest(array("path" => $file));
}

