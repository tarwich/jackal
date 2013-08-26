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
	// Actually output the section item
	echo "
		<span class='Admin-overview-item'>$sectionName<i></i></span>";
}

echo "
	</span>";
