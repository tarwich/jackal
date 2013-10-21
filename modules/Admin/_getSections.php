<?php

/**
 * Returns the sections from the admin config
 * 
 * This method iterates through all the admin/modules settings and looks for sections. It returns an array grouped by 
 * section and subsection, which is dictated by the name.
 * 
 * @example Sample admin.yaml
 * <code type='yaml'>
 * admin:
 * 	modules:
 * 		foo:
 * 			- 
 * 				name: Foo / Bar
 * 		bin:
 * 			- 
 * 				name: Bin / Baz
 * </code>
 * 
 * @example The resulting array from _getSections
 * <code language='php'>
 * 	$sections = $this->_getSections();
 * 	// Returns the following:
 * 	array(
 * 		"Foo" => array(
 * 			"Bar" => array()
 * 		),
 * 		"Bin" => array(
 * 			"Baz" => array()
 * 		),
 * 	);
 * </code>
 * 
 * 
 */

// Prepare the sections array
$sections = array();

// Go through all the modules in the config
foreach((array) Jackal::setting("admin/modules") as $module) {
    // Go through all the sections in this module
    foreach((array) @$module as $section) {
        // Break apart the name of this section by '/'
        @list($a, $b) = explode('/', (string) @$section["name"]);
        // Add this section to the results
        $sections[trim($a)][trim($b)][] = $section;
    }
}

return $sections;
