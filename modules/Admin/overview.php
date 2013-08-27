<?php

// Get the sections for which we're going to show overview nodes
$modules = Jackal::setting("admin/modules");
// Prepare the sections array to hold the sections that we find
$sections = array();

// Go through all the modules and look for ones with tests
foreach($modules as $moduleName=>$group) foreach($group as $module) {
	// If this module has any tests, then add it to the array of things to show
	if(@$module["self-test"]) {
		// Get the section name
		@list($sectionName, $subSectionName) = explode("/", $module["name"], 3);
		// Add this module to the list
		$sections[$sectionName][] = $module;
	}
}

// Show the modules list
echo "
	<span class='Admin-overview'>
		<h1>Admin Overview</h1>";

// Output each section
foreach($sections as $sectionName=>$modules) {
    // Trim whitespace from sectionName
    $sectionName = trim($sectionName);
    // Store the URL in a variable to make the output cleaner
    $url = Jackal::siteURL("Admin/section/$sectionName");
	// Actually output the section item
	echo "
		<span class='Admin-overview-item'><a href='$url/ .Admin-section' \$='.Admin-content'>$sectionName</a><i></i>
		    <span class='test-list'>
		        <b>Tests</b>";

    // Loop through the modules to find all of the self-tests
    foreach($modules as $module) {
        // Get the name of the test
        $testName = $module['self-test'];
        // Replace all '/'s with '.'s
        $testName = str_replace("/", ".", $testName);
        // Create a URL to the test
        $url = Jackal::siteURL("Admin/tester/$testName");
        // Re-set the testName so it will display in the html correctly
        $testName = $module['self-test'];
        echo "
                <p class='test' testURL='$url'>$testName</p>";
    }

    echo "
            </span>
            <span class='test-messages'></span>
        </span>";
}

echo "
    </span>";